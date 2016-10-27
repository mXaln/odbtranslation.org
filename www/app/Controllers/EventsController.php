<?php
namespace App\Controllers;

use App\Core\Controller;
use View;
use Cache;
use Helpers\Tools;
use Helpers\UsfmParser;
use Helpers\Constants\EventMembers;
use Helpers\Constants\EventStates;
use Helpers\Constants\EventSteps;
use Helpers\Data;
use Helpers\Gump;
use Helpers\Session;
use Helpers\Url;
use Shared\Legacy\Error;
use App\Models\EventsModel;
use App\Models\MembersModel;
use App\Models\TranslationsModel;

class EventsController extends Controller
{
    private $_model;
    private $_notifications;

    public function __construct()
    {
        parent::__construct();
        $this->_model = new EventsModel();

        if(Session::get("loggedin"))
            $this->_notifications = $this->_model->getNotifications();
    }

    public function index()
    {
        if (!Session::get('loggedin'))
        {
            Url::redirect('members/login');
        }

        if(empty(Session::get("profile")))
        {
            Url::redirect("members/profile");
        }

        $data['menu'] = 4;

        $data["projects"] = $this->_model->getProjects(Session::get("memberID"), true);
        $data["notifications"] = $this->_notifications;

        return View::make('Events/Index')
            ->shares("title", __("events_title"))
            ->shares("data", $data);
    }

    public function project($projectID)
    {
        if (!Session::get('loggedin'))
        {
            Session::set('redirect', 'admin');
            Url::redirect('members/login');
        }

        if(empty(Session::get("profile")))
        {
            Url::redirect("members/profile");
        }

        $data['menu'] = 4;

        $data["project"] = $this->_model->getProjects(Session::get("memberID"), true, $projectID);
        $data["events"] = array();
        if(!empty($data["project"]))
        {
            $data["events"] = $this->_model->getEventsByProject($projectID);
        }

        $data["notifications"] = $this->_notifications;

        return View::make('Events/Project')
            ->shares("title", $data["project"][0]->tLang . " [".__($data["project"][0]->bookProject)."]")
            ->shares("data", $data);
    }

    public function translator($eventID)
    {
        if (!Session::get('loggedin'))
        {
            Url::redirect('members/login');
        }

        if(empty(Session::get("profile")))
        {
            Url::redirect("members/profile");
        }

        $data['menu'] = 4;
        $data["notifications"] = $this->_notifications;
        $data["event"] = $this->_model->getMemberEvents(Session::get("memberID"), EventMembers::TRANSLATOR, $eventID);

        //$data["event"][0]->currentChapter = 1;
        //$data["event"][0]->bookCode = "act";
        //$data["event"][0]->abbrID = 45;

        $title = "";

        if(!empty($data["event"]))
        {
            $title = $data["event"][0]->name ." - ". $data["event"][0]->tLang ." - ". __($data["event"][0]->bookProject);

            if($data["event"][0]->state == EventStates::TRANSLATING) {

                if($data["event"][0]->step == EventSteps::NONE)
                    Url::redirect("events/information/".$eventID);

                $turnSecret = $this->_model->getTurnSecret();
                $turnUsername = (time() + 3600) . ":vmast";
                $turnPassword = "";

                if(!empty($turnSecret))
                {
                    if(($turnSecret[0]->expire - time()) < 0)
                    {
                        $pass = $this->_model->generateStrongPassword(22);
                        if($this->_model->updateTurnSecret(["value" => $pass, "expire" => time() + (30*24*3600)])) // Update turn secret each month
                        {
                            $turnSecret[0]->value = $pass;
                        }
                    }

                    $turnPassword = hash_hmac("sha1", $turnUsername, $turnSecret[0]->value, true);
                }

                $data["turn"][] = $turnUsername;
                $data["turn"][] = base64_encode($turnPassword);

                switch ($data["event"][0]->step) {
                    case EventSteps::PRAY:

                        if (isset($_POST) && !empty($_POST)) {
                            if (isset($_POST["confirm_step"])) {
                                $this->_model->updateTranslator(["step" => EventSteps::CONSUME], ["trID" => $data["event"][0]->trID]);
                                Url::redirect('events/translator/' . $data["event"][0]->eventID);
                            }
                        }

                        // Check if translator just started translating of this book
                        $data["event"][0]->justStarted = $data["event"][0]->verbCheck == "";

                        return View::make('Events/Translator')
                            ->nest('page', 'Events/Pray')
                            ->shares("title", $title)
                            ->shares("data", $data)
                            ->shares("error", @$error);

                    case EventSteps::CONSUME:
                        $sourceText = $this->getSourceTextUSFM($data);

                        if($sourceText !== false)
                        {
                            if (!array_key_exists("error", $sourceText)) {
                                $data = $sourceText;
                            } else {
                                $error[] = $sourceText["error"];
                            }
                        }

                        if (isset($_POST) && !empty($_POST)) {
                            if (isset($_POST["confirm_step"])) {
                                $postdata = [
                                    "step" => EventSteps::VERBALIZE,
                                    "hideChkNotif" => false,
                                    "currentChapter" => $sourceText["currentChapter"],
                                    "currentChunk" => $sourceText["currentChunk"]
                                ];

                                setcookie("temp_tutorial", false, time() - 3600);
                                $this->_model->updateTranslator($postdata, ["trID" => $data["event"][0]->trID]);
                                Url::redirect('events/translator/' . $data["event"][0]->eventID);
                            }
                        }

                        return View::make('Events/Translator')
                            ->nest('page', 'Events/Consume')
                            ->shares("title", $title)
                            ->shares("data", $data)
                            ->shares("error", @$error);
                        break;

                    case EventSteps::VERBALIZE:
                        if (isset($_POST) && !empty($_POST)) {
                            if (isset($_POST["confirm_step"])) {
                                if($data["event"][0]->checkDone)
                                {
                                    setcookie("temp_tutorial", false, time() - 3600);
                                    $postdata = [
                                        "step" => EventSteps::CHUNKING,
                                        "checkerID" => 0,
                                        "checkDone" => false,
                                        "hideChkNotif" => true
                                    ];
                                    $this->_model->updateTranslator($postdata, ["trID" => $data["event"][0]->trID]);
                                    Url::redirect('events/translator/' . $data["event"][0]->eventID);
                                }
                                else {
                                    $error[] = __("checker_not_ready_error");
                                }
                            }
                        }

                        $sourceText = $this->getSourceTextUSFM($data);

                        if (!array_key_exists("error", $sourceText)) {
                            $data = $sourceText;
                        } else {
                            $error[] = $sourceText["error"];
                        }

                        return View::make('Events/Translator')
                            ->nest('page', 'Events/Verbalize')
                            ->shares("title", $title)
                            ->shares("data", $data)
                            ->shares("error", @$error);
                        break;

                    case EventSteps::CHUNKING:
                        $sourceText = $this->getSourceTextUSFM($data);

                        if (!array_key_exists("error", $sourceText)) {
                            $data = $sourceText;
                        } else {
                            $error[] = $sourceText["error"];
                        }

                        if (isset($_POST) && !empty($_POST)) {
                            if (isset($_POST["confirm_step"])) {
                                setcookie("temp_tutorial", false, time() - 3600);

                                $_POST = Gump::xss_clean($_POST);

                                $chunks = json_decode($_POST["chunks_array"]);
                                if($this->testChunks($chunks, $sourceText["totalVerses"]))
                                {
                                    $chapters = json_decode($data["event"][0]->chapters, true);
                                    $chapters[$sourceText["currentChapter"]]["chunks"] = $chunks;

                                    if($this->_model->updateEvent(["chapters" => json_encode($chapters)], ["eventID" => $data["event"][0]->eventID]))
                                    {
                                        $this->_model->updateTranslator(["step" => EventSteps::READ_CHUNK], ["trID" => $data["event"][0]->trID]);
                                        Url::redirect('events/translator/' . $data["event"][0]->eventID);
                                        exit;
                                    }
                                    else
                                    {
                                        $error[] = __("error_ocured");
                                    }
                                }
                                else
                                {
                                    $error[] = __("wrong_chunks_error");
                                }
                            }
                        }

                        return View::make('Events/Translator')
                            ->nest('page', 'Events/Chunking')
                            ->shares("title", $title)
                            ->shares("data", $data)
                            ->shares("error", @$error);
                        break;

                    case EventSteps::READ_CHUNK:

                        if (isset($_POST) && !empty($_POST)) {
                            if (isset($_POST["confirm_step"])) {
                                setcookie("temp_tutorial", false, time() - 3600);
                                $this->_model->updateTranslator(["step" => EventSteps::BLIND_DRAFT], ["trID" => $data["event"][0]->trID]);
                                Url::redirect('events/translator/' . $data["event"][0]->eventID);
                            }
                        }

                        $sourceText = $this->getSourceTextUSFM($data, true);

                        if (!array_key_exists("error", $sourceText)) {
                            $data = $sourceText;
                        } else {
                            $error[] = $sourceText["error"];
                        }

                        return View::make('Events/Translator')
                            ->nest('page', 'Events/ReadChunk')
                            ->shares("title", $title)
                            ->shares("data", $data)
                            ->shares("error", @$error);
                        break;

                    case EventSteps::BLIND_DRAFT:
                        $sourceText = $this->getSourceTextUSFM($data, true);

                        if (!array_key_exists("error", $sourceText)) {
                            $data = $sourceText;
                        } else {
                            $error[] = $sourceText["error"];
                        }

                        if (isset($_POST) && !empty($_POST)) {
                            $_POST = Gump::xss_clean($_POST);

                            if (isset($_POST["confirm_step"])) {
                                if(trim($_POST["draft"]) != "")
                                {
                                    $translationVerses = [
                                        EventMembers::TRANSLATOR => [
                                            "blind" => trim($_POST["draft"]),
                                            "verses" => array()
                                        ],
                                        EventMembers::L2_CHECKER => [
                                            "verses" => array()
                                        ],
                                        EventMembers::L3_CHECKER => [
                                            "verses" => array()
                                        ],
                                    ];

                                    $trData = [
                                        "projectID"         => $data["event"][0]->projectID,
                                        "eventID"           => $data["event"][0]->eventID,
                                        "trID"              => $data["event"][0]->trID,
                                        "targetLang"        => $data["event"][0]->targetLang,
                                        "bookProject"       => $data["event"][0]->bookProject,
                                        "abbrID"            => $data["event"][0]->abbrID,
                                        "bookCode"          => $data["event"][0]->bookCode,
                                        "chapter"           => $data["event"][0]->currentChapter,
                                        "chunk"             => $data["event"][0]->currentChunk,
                                        "firstvs"           => $sourceText["chunk"][0],
                                        "translatedVerses"  => json_encode($translationVerses),
                                        "dateCreate"        => date('Y-m-d H:i:s')
                                    ];

                                    $tID = $this->_model->createTranslation($trData); Data::pr($tID);

                                    if($tID)
                                    {
                                        $postdata["step"] = EventSteps::SELF_CHECK;

                                        // If chapter is finished go to SELF_EDIT, otherwise go to the next chunk
                                        if(array_key_exists($data["event"][0]->currentChunk + 1, $sourceText["chunks"]))
                                        {
                                            // Current chunk is finished, go to next chunk
                                            $postdata["currentChunk"] = $data["event"][0]->currentChunk + 1;
                                            $postdata["step"] = EventSteps::READ_CHUNK;
                                        }
                                        Data::pr($postdata);
                                        setcookie("temp_tutorial", false, time() - 3600);
                                        $upd = $this->_model->updateTranslator($postdata, ["trID" => $data["event"][0]->trID]); Data::pr($upd);
                                        Url::redirect('events/translator/' . $data["event"][0]->eventID);
                                    }
                                    else
                                    {
                                        $error[] = __("error_ocured", array($tID));
                                    }
                                }
                                else
                                {
                                    $error[] = __("empty_draft_verses_error");
                                }
                            }
                        }

                        return View::make('Events/Translator')
                            ->nest('page', 'Events/BlindDraft')
                            ->shares("title", $title)
                            ->shares("data", $data)
                            ->shares("error", @$error);
                        break;

                    case EventSteps::SELF_CHECK:
                        $data["comments"] = $this->getComments($data["event"][0]->eventID, $data["event"][0]->currentChapter);

                        $translationData = $this->_model->getTranslation($data["event"][0]->trID, null, $data["event"][0]->currentChapter);
                        $translation = array();

                        foreach ($translationData as $tv) {
                            $arr = json_decode($tv->translatedVerses, true);
                            $arr["tID"] = $tv->tID;
                            $translation[] = $arr;
                        }

                        $sourceText = $this->getSourceTextUSFM($data);

                        if (!array_key_exists("error", $sourceText)) {
                            $data = $sourceText;
                            $data["translation"] = $translation;
                        } else {
                            $error[] = $sourceText["error"];
                        }

                        if (isset($_POST) && !empty($_POST)) {
                            $_POST = Gump::xss_clean($_POST);

                            if(isset($_POST["save"]))
                            {
                                $_POST["chunks"] = array_map("trim", $_POST["chunks"]);
                                $_POST["chunks"] = array_filter($_POST["chunks"], function($v) {
                                    return !empty($v);
                                });

                                if(sizeof($_POST["chunks"]) < sizeof($data["chapters"][$data["currentChapter"]]["chunks"]))
                                    $error[] = __("empty_verses_error");

                                if(!isset($error))
                                {
                                    if(!empty($translation))
                                    {
                                        foreach ($translation as $key => $chunk) {
                                            $shouldUpdate = false;
                                            if($chunk[EventMembers::TRANSLATOR]["blind"] != $_POST["chunks"][$key])
                                                $shouldUpdate = true;

                                            $translation[$key][EventMembers::TRANSLATOR]["blind"] = $_POST["chunks"][$key];

                                            if($shouldUpdate)
                                            {
                                                $tID = $translation[$key]["tID"];
                                                unset($translation[$key]["tID"]);
                                                $trData = array(
                                                    "translatedVerses"  => json_encode($translation[$key])
                                                );
                                                $this->_model->updateTranslation($trData, array("trID" => $data["event"][0]->trID, "tID" => $tID));
                                            }
                                        }
                                    }
                                }
                            }
                            else
                            {
                                if (isset($_POST["confirm_step"])) {
                                    setcookie("temp_tutorial", false, time() - 3600);
                                    $postdata = [
                                        "step" => EventSteps::PEER_REVIEW,
                                        "hideChkNotif" => false,
                                    ];
                                    $this->_model->updateTranslator($postdata, ["trID" => $data["event"][0]->trID]);
                                    Url::redirect('events/translator/' . $data["event"][0]->eventID);
                                }
                            }
                        }

                        return View::make('Events/Translator')
                            ->nest('page', 'Events/SelfCheck')
                            ->shares("title", $title)
                            ->shares("data", $data)
                            ->shares("error", @$error);
                        break;

                    case EventSteps::PEER_REVIEW:
                        $data["comments"] = $this->getComments($data["event"][0]->eventID, $data["event"][0]->currentChapter);

                        $sourceText = $this->getSourceTextUSFM($data);
                        if($sourceText !== false)
                        {
                            if (!array_key_exists("error", $sourceText)) {
                                $translationData = $this->_model->getTranslation($data["event"][0]->trID, null, $data["event"][0]->currentChapter);
                                $translation = array();

                                foreach ($translationData as $tv) {
                                    $arr = json_decode($tv->translatedVerses, true);
                                    $arr["tID"] = $tv->tID;
                                    $translation[] = $arr;
                                }

                                $data = $sourceText;
                                $data["translation"] = $translation;
                            } else {
                                $error[] = $sourceText["error"];
                            }
                        }

                        if (isset($_POST) && !empty($_POST)) {
                            $_POST = Gump::xss_clean($_POST);

                            if(isset($_POST["save"]))
                            {
                                if(!$data["event"][0]->checkDone)
                                {
                                    $_POST["chunks"] = array_map("trim", $_POST["chunks"]);
                                    $_POST["chunks"] = array_filter($_POST["chunks"], function($v) {
                                        return !empty($v);
                                    });

                                    if(sizeof($_POST["chunks"]) < sizeof($data["chapters"][$data["currentChapter"]]["chunks"]))
                                        $error[] = __("empty_verses_error");

                                    if(!isset($error))
                                    {
                                        if(!empty($translation))
                                        {
                                            foreach ($translation as $key => $chunk) {
                                                $shouldUpdate = false;
                                                if($chunk[EventMembers::TRANSLATOR]["blind"] != $_POST["chunks"][$key])
                                                    $shouldUpdate = true;

                                                $translation[$key][EventMembers::TRANSLATOR]["blind"] = $_POST["chunks"][$key];

                                                if($shouldUpdate)
                                                {
                                                    $tID = $translation[$key]["tID"];
                                                    unset($translation[$key]["tID"]);
                                                    $trData = array(
                                                        "translatedVerses"  => json_encode($translation[$key])
                                                    );
                                                    $this->_model->updateTranslation($trData, array("trID" => $data["event"][0]->trID, "tID" => $tID));
                                                }
                                            }
                                        }
                                    }
                                }
                                else
                                {
                                    $error[] = __("not_possible_to_save_error");
                                }
                            }
                            else
                            {
                                if (isset($_POST["confirm_step"])) {
                                    if($data["event"][0]->checkDone)
                                    {
                                        setcookie("temp_tutorial", false, time() - 3600);
                                        $postdata = [
                                            "step" => EventSteps::KEYWORD_CHECK,
                                            "checkerID" => 0,
                                            "checkDone" => false,
                                            "hideChkNotif" => false
                                        ];
                                        $this->_model->updateTranslator($postdata, ["trID" => $data["event"][0]->trID]);
                                        Url::redirect('events/translator/' . $data["event"][0]->eventID);
                                    }
                                    else {
                                        $error[] = __("checker_not_ready_error");
                                    }
                                }
                            }
                        }

                        return View::make('Events/Translator')
                            ->nest('page', 'Events/PeerReview')
                            ->shares("title", $title)
                            ->shares("data", $data)
                            ->shares("error", @$error);
                        break;

                    case EventSteps::KEYWORD_CHECK:
                        $data["comments"] = $this->getComments($data["event"][0]->eventID, $data["event"][0]->currentChapter);

                        $translationData = $this->_model->getTranslation($data["event"][0]->trID, null, $data["event"][0]->currentChapter);
                        $translation = array();

                        foreach ($translationData as $tv) {
                            $arr = json_decode($tv->translatedVerses, true);
                            $arr["tID"] = $tv->tID;
                            $translation[] = $arr;
                        }

                        if (isset($_POST) && !empty($_POST)) {
                            $_POST = Gump::xss_clean($_POST);

                            if(isset($_POST["save"]))
                            {
                                if(!$data["event"][0]->checkDone)
                                {
                                    $_POST["chunks"] = array_map("trim", $_POST["chunks"]);
                                    $_POST["chunks"] = array_filter($_POST["chunks"], function($v) {
                                        return !empty($v);
                                    });

                                    $chapters = json_decode($data["event"][0]->chapters, true);

                                    if(sizeof($_POST["chunks"]) < sizeof($chapters[$data["event"][0]->currentChapter]["chunks"]))
                                        $error[] = __("empty_verses_error");

                                    if(!isset($error))
                                    {
                                        if(!empty($translation))
                                        {
                                            foreach ($translation as $key => $chunk) {
                                                $shouldUpdate = false;
                                                if($chunk[EventMembers::TRANSLATOR]["blind"] != $_POST["chunks"][$key])
                                                    $shouldUpdate = true;

                                                $translation[$key][EventMembers::TRANSLATOR]["blind"] = $_POST["chunks"][$key];

                                                if($shouldUpdate)
                                                {
                                                    $tID = $translation[$key]["tID"];
                                                    unset($translation[$key]["tID"]);
                                                    $trData = array(
                                                        "translatedVerses"  => json_encode($translation[$key])
                                                    );
                                                    $this->_model->updateTranslation($trData, array("trID" => $data["event"][0]->trID, "tID" => $tID));
                                                }
                                            }
                                        }
                                    }
                                }
                                else
                                {
                                    $error[] = __("not_possible_to_save_error");
                                }
                            }
                            else
                            {
                                if (isset($_POST["confirm_step"])) {
                                    if($data["event"][0]->checkDone)
                                    {
                                        setcookie("temp_tutorial", false, time() - 3600);
                                        $postdata = array(
                                            "step" => EventSteps::CONTENT_REVIEW,
                                            "checkerID" => 0,
                                            "checkDone" => false,
                                            "hideChkNotif" => false
                                        );
                                        $this->_model->updateTranslator($postdata, array("trID" => $data["event"][0]->trID));
                                        Url::redirect('events/translator/' . $data["event"][0]->eventID);
                                        exit;
                                    }
                                    else
                                    {
                                        $error[] = __("checker_not_ready_error");
                                    }
                                }
                            }
                        }

                        $sourceText = $this->getSourceTextUSFM($data);

                        if (!array_key_exists("error", $sourceText)) {
                            $data = $sourceText;
                            $data["translation"] = $translation;
                        } else {
                            $error[] = $sourceText["error"];
                        }

                        return View::make('Events/Translator')
                            ->nest('page', 'Events/KeywordCheck')
                            ->shares("title", $title)
                            ->shares("data", $data)
                            ->shares("error", @$error);
                        break;

                    case EventSteps::CONTENT_REVIEW:
                        $data["comments"] = $this->getComments($data["event"][0]->eventID, $data["event"][0]->currentChapter);

                        $translationData = $this->_model->getTranslation($data["event"][0]->trID, null, $data["event"][0]->currentChapter);
                        $translation = array();

                        foreach ($translationData as $tv) {
                            $arr = json_decode($tv->translatedVerses, true);
                            $arr["tID"] = $tv->tID;
                            $translation[] = $arr;
                        }

                        $sourceText = $this->getSourceTextUSFM($data);

                        if (!array_key_exists("error", $sourceText)) {
                            $data = $sourceText;
                            $data["translation"] = $translation;
                        } else {
                            $error[] = $sourceText["error"];
                        }

                        if (isset($_POST) && !empty($_POST)) {
                            $_POST = Gump::xss_clean($_POST);

                            if(isset($_POST["save"]))
                            {
                                if(!$data["event"][0]->checkDone)
                                {
                                    $_POST["chunks"] = array_map("trim", $_POST["chunks"]);
                                    $_POST["chunks"] = array_filter($_POST["chunks"], function($v) {
                                        return !empty($v);
                                    });

                                    $chapters = json_decode($data["event"][0]->chapters, true);

                                    if(sizeof($_POST["chunks"]) < sizeof($chapters[$data["event"][0]->currentChapter]["chunks"]))
                                        $error[] = __("empty_verses_error");

                                    if(!isset($error))
                                    {
                                        if(!empty($translation))
                                        {
                                            foreach ($translation as $key => $chunk) {
                                                $shouldUpdate = false;
                                                if($chunk[EventMembers::TRANSLATOR]["blind"] != $_POST["chunks"][$key])
                                                    $shouldUpdate = true;

                                                $translation[$key][EventMembers::TRANSLATOR]["blind"] = $_POST["chunks"][$key];

                                                if($shouldUpdate)
                                                {
                                                    $tID = $translation[$key]["tID"];
                                                    unset($translation[$key]["tID"]);
                                                    $trData = array(
                                                        "translatedVerses"  => json_encode($translation[$key])
                                                    );
                                                    $this->_model->updateTranslation($trData, ["trID" => $data["event"][0]->trID, "tID" => $tID]);
                                                }
                                            }
                                        }
                                    }
                                }
                                else
                                {
                                    $error[] = __("not_possible_to_save_error");
                                }
                            }
                            else
                            {
                                if (isset($_POST["confirm_step"]))
                                {
                                    if($data["event"][0]->checkDone)
                                    {
                                        setcookie("temp_tutorial", false, time() - 3600);
                                        $postdata = array(
                                            "step" => EventSteps::FINAL_REVIEW,
                                            "checkerID" => 0,
                                            "checkDone" => false,
                                            "hideChkNotif" => true
                                        );
                                        $this->_model->updateTranslator($postdata, ["trID" => $data["event"][0]->trID]);
                                        Url::redirect('events/translator/' . $data["event"][0]->eventID);
                                    }
                                    else
                                    {
                                        $error[] = __("checker_not_ready_error");
                                    }
                                }
                            }
                        }

                        return View::make('Events/Translator')
                            ->nest('page', 'Events/ContentReview')
                            ->shares("title", $title)
                            ->shares("data", $data)
                            ->shares("error", @$error);
                        break;

                    case EventSteps::FINAL_REVIEW:
                        $data["comments"] = $this->getComments($data["event"][0]->eventID, $data["event"][0]->currentChapter);

                        $translationData = $this->_model->getTranslation($data["event"][0]->trID, null, $data["event"][0]->currentChapter);
                        $translation = array();

                        foreach ($translationData as $tv) {
                            $arr = json_decode($tv->translatedVerses, true);
                            $arr["tID"] = $tv->tID;
                            $translation[] = $arr;
                        }

                        $sourceText = $this->getSourceTextUSFM($data);

                        if (!array_key_exists("error", $sourceText)) {
                            $data = $sourceText;
                            $data["translation"] = $translation;
                        } else {
                            $error[] = $sourceText["error"];
                        }

                        if (isset($_POST) && !empty($_POST)) {
                            $_POST = Gump::xss_clean($_POST);

                            if (isset($_POST["confirm_step"]))
                            {
                                $_POST["chunks"] = array_map("trim", $_POST["chunks"]);
                                $_POST["chunks"] = array_filter($_POST["chunks"], function($v) {
                                    return !empty($v);
                                });

                                $chapters = json_decode($data["event"][0]->chapters, true);

                                if(sizeof($_POST["chunks"]) < sizeof($chapters[$data["event"][0]->currentChapter]["chunks"]))
                                    $error[] = __("empty_verses_error");

                                if(!isset($error))
                                {
                                    $versesCombined = [];
                                    foreach ($_POST["chunks"] as $key => $chunk) {
                                        $verses = preg_split("/\|([0-9]+)\|/", $chunk, -1, PREG_SPLIT_NO_EMPTY);

                                        if(sizeof($chapters[$data["event"][0]->currentChapter]["chunks"][$key]) !=
                                            sizeof($verses))
                                        {
                                            $error[] = __("not_equal_verse_markers");
                                            break;
                                        }

                                        $versesCombined[$key] = array_combine($chapters[$data["event"][0]->currentChapter]["chunks"][$key], $verses);
                                    }

                                    if(!isset($error))
                                    {
                                        foreach ($versesCombined as $key => $chunk) {
                                            $translation[$key][EventMembers::TRANSLATOR]["verses"] = $chunk;

                                            $tID = $translation[$key]["tID"];
                                            unset($translation[$key]["tID"]);
                                            $trData = array(
                                                "translatedVerses"  => json_encode($translation[$key]),
                                                "translateDone" => true
                                            );
                                            $this->_model->updateTranslation($trData, array("trID" => $data["event"][0]->trID, "tID" => $tID));
                                        }

                                        // Check if the member has another chapter to translate
                                        $nextChapter = 0;
                                        foreach ($sourceText["chapters"] as $key => $chapter) {
                                            if(empty($chapter)) continue;

                                            // If the member has chapter with empty chunks, set that chapter as next chapter to translate
                                            if($chapter["trID"] == $data["event"][0]->trID && empty($chapter["chunks"]))
                                            {
                                                $nextChapter = $key;
                                                break;
                                            }
                                        }

                                        $postdata = [
                                            "step" => EventSteps::FINISHED,
                                            "currentChapter" => 0,
                                            "currentChunk" => 0,
                                            "translateDone" => true
                                        ];

                                        if($nextChapter > 0)
                                        {
                                            $postdata["step"] = EventSteps::PRAY;
                                            $postdata["currentChapter"] = $nextChapter;
                                            $postdata["translateDone"] = false;
                                        }

                                        setcookie("temp_tutorial", false, time() - 3600);
                                        $this->_model->updateTranslator($postdata, ["trID" => $data["event"][0]->trID]);
                                        Url::redirect('events/translator/' . $data["event"][0]->eventID);
                                    }
                                }
                            }
                        }

                        return View::make('Events/Translator')
                            ->nest('page', 'Events/FinalReview')
                            ->shares("title", $title)
                            ->shares("data", $data)
                            ->shares("error", @$error);
                        break;

                    case EventSteps::FINISHED:
                        $data["success"] = __("you_event_finished_success");

                        return View::make('Events/Translator')
                            ->nest('page', 'Events/Finished')
                            ->shares("title", $title)
                            ->shares("data", $data)
                            ->shares("error", @$error);
                        break;
                }
            }
            else
            {
                $data["error"] = true;
                $error[] = __("wrong_event_state_error");

                return View::make('Events/Translator')
                    ->shares("title", $title)
                    ->shares("data", $data)
                    ->shares("error", @$error);
            }
        }
        else
        {
            $error[] = __("not_in_event_error");
            $title = "Error";

            return View::make('Events/Translator')
                ->shares("title", $title)
                ->shares("data", $data)
                ->shares("error", @$error);
        }
    }

    public function checker($eventID, $memberID)
    {
        $response = array("success" => false, "errors" => "");

        if (!Session::get('loggedin'))
        {
            Url::redirect('members/login');
        }

        if(empty(Session::get("profile")))
        {
            Url::redirect("members/profile");
        }

        if (!Session::get('verified'))
        {
            $error[] = __("account_not_verirfied_error");
        }

        $title = "";

        if(!isset($error))
        {
            $data["event"] = $this->_model->getMemberEventsForChecker(Session::get("memberID"), $eventID, $memberID);

            if(!empty($data["event"]))
            {
                if($data["event"][0]->step != EventSteps::FINISHED && !$data["event"][0]->translateDone)
                {
                    if(in_array($data["event"][0]->step, [EventSteps::VERBALIZE, EventSteps::PEER_REVIEW, EventSteps::KEYWORD_CHECK, EventSteps::CONTENT_REVIEW]))
                    {
                        $turnSecret = $this->_model->getTurnSecret();
                        $turnUsername = (time() + 3600) . ":vmast";
                        $turnPassword = "";

                        if(!empty($turnSecret))
                        {
                            if(($turnSecret[0]->expire - time()) < 0)
                            {
                                $pass = $this->_model->generateStrongPassword(22);
                                if($this->_model->updateTurnSecret(array("value" => $pass, "expire" => time() + (30*24*3600)))) // Update turn secret each month
                                {
                                    $turnSecret[0]->value = $pass;
                                }
                            }

                            $turnPassword = hash_hmac("sha1", $turnUsername, $turnSecret[0]->value, true);
                        }

                        $data["turn"][] = $turnUsername;
                        $data["turn"][] = base64_encode($turnPassword);

                        $data["comments"] = $this->getComments($data["event"][0]->eventID, $data["event"][0]->currentChapter);

                        if (isset($_POST) && !empty($_POST)) {
                            $_POST = Gump::xss_clean($_POST);

                            if (isset($_POST["confirm_step"])) {
                                $postdata = array("checkDone" => true);

                                if($data["event"][0]->step == EventSteps::VERBALIZE)
                                {
                                    $verbCheck = (array)json_decode($data["event"][0]->verbCheck, true);
                                    if(!array_key_exists($data["event"][0]->currentChapter, $verbCheck))
                                    {
                                        $verbCheck[$data["event"][0]->currentChapter] = Session::get("memberID");
                                        $postdata["verbCheck"] = json_encode($verbCheck);
                                    }
                                }
                                elseif($data["event"][0]->step == EventSteps::PEER_REVIEW)
                                {
                                    $peerCheck = (array)json_decode($data["event"][0]->peerCheck, true);
                                    if(!array_key_exists($data["event"][0]->currentChapter, $peerCheck))
                                    {
                                        $peerCheck[$data["event"][0]->currentChapter] = Session::get("memberID");
                                        $postdata["peerCheck"] = json_encode($peerCheck);
                                    }
                                }
                                elseif($data["event"][0]->step == EventSteps::KEYWORD_CHECK)
                                {
                                    $kwCheck = (array)json_decode($data["event"][0]->kwCheck, true);
                                    if(!array_key_exists($data["event"][0]->currentChapter, $kwCheck))
                                    {
                                        $kwCheck[$data["event"][0]->currentChapter] = Session::get("memberID");
                                        $postdata["kwCheck"] = json_encode($kwCheck);
                                    }
                                }
                                else
                                {
                                    $crCheck = (array)json_decode($data["event"][0]->crCheck, true);
                                    if(!array_key_exists($data["event"][0]->currentChapter, $crCheck))
                                    {
                                        $crCheck[$data["event"][0]->currentChapter] = Session::get("memberID");
                                        $postdata["crCheck"] = json_encode($crCheck);
                                    }
                                }

                                $this->_model->updateTranslator($postdata, array("trID" => $data["event"][0]->trID));

                                $response["success"] = true;
                                echo json_encode($response);

                                //Url::redirect('members');
                                exit;
                            }
                        }

                        if($data["event"][0]->checkDone)
                        {
                            $data["success"] = __("checker_translator_finished_error");
                        }
                        else
                        {
                            $sourceText = $this->getSourceTextUSFM($data);

                            if (!array_key_exists("error", $sourceText)) {
                                $data = $sourceText;
                            } else {
                                $error[] = $sourceText["error"];
                            }
                        }
                    }
                    else
                    {
                        $error[] = __("checker_translator_not_ready_error");
                    }
                }
                else
                {
                    $data["success"] = __("translator_event_finished_success");
                    $data["error"] = "";
                }

                $title = $data["event"][0]->bookName ." - ". $data["event"][0]->tLang ." - ". __($data["event"][0]->bookProject);
            }
            else
            {
                $error[] = __("checker_event_error");
                $title = "Error";
            }
        }

        $data['menu'] = 4;
        $data["isCheckerPage"] = true;
        $data["notifications"] = $this->_notifications;

        $page = "CheckerVerbalize";
        if(!isset($error))
        {
            if($data["event"][0]->step != EventSteps::VERBALIZE)
            {
                $sourceText = $this->getSourceTextUSFM($data);
                if($sourceText !== false)
                {
                    if (!array_key_exists("error", $sourceText)) {
                        $translationData = $this->_model->getTranslation($data["event"][0]->trID, null, $data["event"][0]->currentChapter);
                        $translation = array();

                        foreach ($translationData as $tv) {
                            $arr = json_decode($tv->translatedVerses, true);
                            $arr["tID"] = $tv->tID;
                            $translation[] = $arr;
                        }

                        $data = $sourceText;
                        $data["translation"] = $translation;
                    } else {
                        $error[] = $sourceText["error"];
                    }
                }
            }

            switch ($data["event"][0]->step)
            {
                case EventSteps::PEER_REVIEW:
                    $page = "CheckerPeerReview";
                    break;

                case EventSteps::KEYWORD_CHECK:
                    $page = "CheckerKeywordCheck";
                    break;

                case EventSteps::CONTENT_REVIEW:
                    $page = "CheckerContentReview";
                    break;

                default:
                    $page = "CheckerVerbalize";
                    break;
            }
        }

        return View::make('Events/Translator')
            ->nest('page', 'Events/'.$page)
            ->shares("title", $title)
            ->shares("data", $data)
            ->shares("error", @$error);
    }

    public function checkerL2($eventID)
    {
        if (!Session::get('loggedin'))
        {
            Url::redirect('members/login');
        }

        if(empty(Session::get("profile")))
        {
            Url::redirect("members/profile");
        }

        $data['menu'] = 4;

        echo $eventID;
    }

    public function checkerL3($eventID)
    {
        if (!Session::get('loggedin'))
        {
            Url::redirect('members/login');
        }

        if(empty(Session::get("profile")))
        {
            Url::redirect("members/profile");
        }

        $data['menu'] = 4;

        echo $eventID;
    }


    public function information($eventID)
    {
        if (!Session::get('loggedin'))
        {
            Url::redirect('members/login');
        }

        if(empty(Session::get("profile")))
        {
            Url::redirect("members/profile");
        }

        if (!Session::get('verified'))
        {
            $error[] = __("account_not_verirfied_error");
        }

        $data['menu'] = 4;
        $data["event"] = $this->_model->getEventMember($eventID, Session::get("memberID"), true);
        $data["isAdmin"] = false;

        $canViewInfo = false;

        $memberModel = new MembersModel();

        if(!empty($data["event"]))
        {
            if($data["event"][0]->translator === null && $data["event"][0]->checker === null)
            {
                // If member is not a participant of the event, check if he is a facilitator
                if(Session::get("isAdmin"))
                {
                    $admin = $memberModel->getAdminMember(Session::get("memberID"));

                    foreach ($admin as $item) {
                        if($item->gwLang == $data["event"][0]->gwLang)
                        {
                            $data["isAdmin"] = true;
                            $canViewInfo = true;
                            break;
                        }
                    }

                    if(!$data["isAdmin"])
                    {
                        $error[] = __("empty_or_not_permitted_event_error");
                    }
                }
                else
                {
                    $error[] = __("empty_or_not_permitted_event_error");
                }
            }
            else
            {
                $canViewInfo = true;

                if(Session::get("isAdmin"))
                {
                    $admin = $memberModel->getAdminMember(Session::get("memberID"));

                    foreach ($admin as $item) {
                        if($item->gwLang == $data["event"][0]->gwLang)
                        {
                            $data["isAdmin"] = true;
                            break;
                        }
                    }
                }
            }

            if($data["event"][0]->state == "started" && $canViewInfo)
            {
                $error[] = __("not_started_event_error", array($data["event"][0]->eventID));
            }
        }
        else
        {
            $error[] = __("empty_or_not_permitted_event_error");
        }

        if(!isset($error))
        {
            $data["chapters"] = json_decode($data["event"][0]->chapters, true);

            $translationModel = new TranslationsModel();
            $chunks = $translationModel->getTranslationByEventID($data["event"][0]->eventID);
            $members = [];

            $memberSteps = [];
            $currentChecker = "na";
            $currentCheckState = "not_started";

            $i = 0;
            foreach ($chunks as $chunk) {
                // If member hasn't started translating, show current step and checker
                if($chunk->chapter === null)
                {
                    $memberSteps[$chunk->memberID] = $chunk->step;
                    if($chunk->checkerID > 0)
                    {
                        $currentCheckState = "in_progress";
                        $currentChecker = $chunk->checkerID;
                        $members[$currentChecker] = "";
                    }
                    elseif ($chunk->verbCheck != "")
                    {
                        $verbCheck = (array)json_decode($chunk->verbCheck, true);
                        if(array_key_exists($chunk->currentChapter, $verbCheck))
                        {
                            $currentCheckState = "finished";
                            $currentChecker = $verbCheck[$chunk->currentChapter];
                            $members[$currentChecker] = "";
                        }
                    }
                    continue;
                }
                else
                {
                    $memberSteps[$chunk->memberID] = $chunk->step;
                }

                if($i < $chunk->chapter)
                {
                    $chunk->verbCheck = (array)json_decode($chunk->verbCheck, true);
                    $chunk->peerCheck = (array)json_decode($chunk->peerCheck, true);
                    $chunk->kwCheck = (array)json_decode($chunk->kwCheck, true);
                    $chunk->crCheck = (array)json_decode($chunk->crCheck, true);

                    // Verbalize Check
                    if(array_key_exists($chunk->chapter, $chunk->verbCheck))
                    {
                        $data["chapters"][$chunk->chapter]["verb"]["state"] = "finished";
                        $data["chapters"][$chunk->chapter]["verb"]["checkerID"] = $chunk->verbCheck[$chunk->chapter];
                        $members[$chunk->verbCheck[$chunk->chapter]] = "";
                    }
                    else
                    {
                        if($chunk->chapter == $chunk->currentChapter)
                        {
                            if($chunk->step == EventSteps::VERBALIZE && $chunk->checkerID > 0)
                            {
                                $data["chapters"][$chunk->chapter]["verb"]["state"] = "in_progress";
                                $data["chapters"][$chunk->chapter]["verb"]["checkerID"] = $chunk->checkerID;
                                $members[$chunk->checkerID] = "";
                            }
                        }
                    }

                    // Peer Check
                    if(array_key_exists($chunk->chapter, $chunk->peerCheck))
                    {
                        $data["chapters"][$chunk->chapter]["peer"]["state"] = "finished";
                        $data["chapters"][$chunk->chapter]["peer"]["checkerID"] = $chunk->peerCheck[$chunk->chapter];
                        $members[$chunk->peerCheck[$chunk->chapter]] = "";
                    }
                    else
                    {
                        if($chunk->chapter == $chunk->currentChapter)
                        {
                            if($chunk->step == EventSteps::PEER_REVIEW && $chunk->checkerID > 0)
                            {
                                $data["chapters"][$chunk->chapter]["peer"]["state"] = "in_progress";
                                $data["chapters"][$chunk->chapter]["peer"]["checkerID"] = $chunk->checkerID;
                                $members[$chunk->checkerID] = "";
                            }
                        }
                    }

                    // Keyword Check
                    if(array_key_exists($chunk->chapter, $chunk->kwCheck))
                    {
                        $data["chapters"][$chunk->chapter]["kwc"]["state"] = "finished";
                        $data["chapters"][$chunk->chapter]["kwc"]["checkerID"] = $chunk->kwCheck[$chunk->chapter];
                        $members[$chunk->kwCheck[$chunk->chapter]] = "";
                    }
                    else
                    {
                        if($chunk->chapter == $chunk->currentChapter)
                        {
                            if($chunk->step == EventSteps::KEYWORD_CHECK && $chunk->checkerID > 0)
                            {
                                $data["chapters"][$chunk->chapter]["kwc"]["state"] = "in_progress";
                                $data["chapters"][$chunk->chapter]["kwc"]["checkerID"] = $chunk->checkerID;
                                $members[$chunk->checkerID] = "";
                            }
                        }
                    }


                    // Content Review (Verse by Verse) Check
                    if(array_key_exists($chunk->chapter, $chunk->crCheck))
                    {
                        $data["chapters"][$chunk->chapter]["crc"]["state"] = "finished";
                        $data["chapters"][$chunk->chapter]["crc"]["checkerID"] = $chunk->crCheck[$chunk->chapter];
                        $data["chapters"][$chunk->chapter]["step"] = $chunk->chapter == $chunk->currentChapter
                            ? $chunk->step : EventSteps::FINISHED;
                        $members[$chunk->crCheck[$chunk->chapter]] = "";
                    }
                    else
                    {
                        if($chunk->chapter == $chunk->currentChapter)
                        {
                            if($chunk->step == EventSteps::CONTENT_REVIEW && $chunk->checkerID > 0 &&
                                $data["chapters"][$chunk->chapter]["kwc"]["state"] != "in_progress")
                            {
                                $data["chapters"][$chunk->chapter]["crc"]["state"] = "in_progress";
                                $data["chapters"][$chunk->chapter]["crc"]["checkerID"] = $chunk->checkerID;
                                $members[$chunk->checkerID] = "";
                            }
                        }
                    }

                    $i = $chunk->chapter;
                }

                $data["chapters"][$chunk->chapter]["chunksData"][] = $chunk;
                if(!isset($data["chapters"][$chunk->chapter]["step"]))
                    $data["chapters"][$chunk->chapter]["step"] = $chunk->step;
            }

            $overallProgress = 0;

            foreach ($data["chapters"] as $key => $chapter) {
                if(empty($chapter)) continue;

                $data["chapters"][$key]["progress"] = 0;

                if(empty($chapter["chunks"]) || !isset($chapter["chunksData"]))
                {
                    $data["chapters"][$key]["step"] = $memberSteps[$chapter["memberID"]];
                    $data["chapters"][$key]["verb"]["state"] = $currentCheckState;
                    $data["chapters"][$key]["verb"]["checkerID"] = $currentChecker;
                    $data["chapters"][$key]["peer"]["state"] = "not_started";
                    $data["chapters"][$key]["peer"]["checkerID"] = "na";
                    $data["chapters"][$key]["kwc"]["state"] = "not_started";
                    $data["chapters"][$key]["kwc"]["checkerID"] = "na";
                    $data["chapters"][$key]["crc"]["state"] = "not_started";
                    $data["chapters"][$key]["crc"]["checkerID"] = "na";

                    $data["chapters"][$key]["chunksData"] = [];
                    $members[$chapter["memberID"]] = "";
                    continue;
                }

                $members[$chapter["memberID"]] = "";

                if(sizeof($chapter["chunks"]) > 0)
                {
                    // Total translated chunks are 25% of all chapter progress
                    $data["chapters"][$key]["progress"] += sizeof($chapter["chunksData"]) * 25 / sizeof($chapter["chunks"]);
                    if(!isset($data["chapters"][$key]["step"]))
                        $data["chapters"][$key]["step"] = EventSteps::READ_CHUNK;
                }

                if(!array_key_exists("verb", $chapter))
                {
                    $data["chapters"][$key]["verb"]["state"] = "not_started";
                    $data["chapters"][$key]["verb"]["checkerID"] = "na";
                }

                if(!array_key_exists("peer", $chapter))
                {
                    $data["chapters"][$key]["peer"]["state"] = "not_started";
                    $data["chapters"][$key]["peer"]["checkerID"] = "na";
                }
                else
                {
                    // Add 25% of progress when peer check done
                    if(array_key_exists("state", $chapter["peer"]) && $data["chapters"][$key]["peer"]["state"] == "finished")
                        $data["chapters"][$key]["progress"] += 25;
                }

                if(!array_key_exists("kwc", $chapter))
                {
                    $data["chapters"][$key]["kwc"]["state"] = "not_started";
                    $data["chapters"][$key]["kwc"]["checkerID"] = "na";
                }
                else
                {
                    // Add 25% of progress when keyword check done
                    if(array_key_exists("state", $chapter["kwc"]) && $data["chapters"][$key]["kwc"]["state"] == "finished")
                        $data["chapters"][$key]["progress"] += 25;
                }

                if(!array_key_exists("crc", $chapter))
                {
                    $data["chapters"][$key]["crc"]["state"] = "not_started";
                    $data["chapters"][$key]["crc"]["checkerID"] = "na";
                }
                else
                {
                    // Add 25% of progress when verse by verse check done
                    if(array_key_exists("state", $chapter["crc"]) && $data["chapters"][$key]["crc"]["state"] == "finished")
                        $data["chapters"][$key]["progress"] += 25;
                }

                $overallProgress += $data["chapters"][$key]["progress"];
            }

            $data["overall_progress"] = $overallProgress / sizeof($data["chapters"]);

            $adminMembers = $memberModel->getAdminsByGwProject($data["event"][0]->gwProjectID);
            $adminsArr = json_decode($adminMembers[0]->admins, true);
            $empty = array_fill(0, sizeof($adminsArr), "");
            $admins = array_combine($adminsArr, $empty);

            $members += $admins;
            $membersArray = (array)$memberModel->getMembers(array_filter(array_keys($members)));

            foreach ($membersArray as $member) {
                $members[$member->memberID] = $member->userName;
            }

            $members["na"] = __("not_available");
            $members = array_filter($members);

            $data["admins"] = $adminsArr;
            $data["members"] = $members;
        }

        $data["notifications"] = $this->_notifications;
        return View::make('Events/Information')
            ->shares("title", __("event_info"))
            ->shares("data", $data)
            ->shares("error", @$error);
    }


    public function manage($eventID)
    {
        if (!Session::get('loggedin'))
        {
            Url::redirect('members/login');
        }

        if(empty(Session::get("profile")))
        {
            Url::redirect("members/profile");
        }

        if (!Session::get('verified'))
        {
            $error[] = __("account_not_verirfied_error");
        }

        $data['menu'] = 4;
        $data["event"] = $this->_model->getMemberEventsForAdmin(Session::get("memberID"), $eventID);

        if(!empty($data["event"]))
        {
            $data["chapters"] = json_decode($data["event"][0]->chapters, true);
            $data["members"] = $this->_model->getMembersForEvent($data["event"][0]->eventID);

            if (isset($_POST) && !empty($_POST)) {
                if(!empty(array_filter($data["chapters"])))
                {
                    $updated = $this->_model->updateEvent(array("state" => EventStates::TRANSLATING), array("eventID" => $eventID));
                    if($updated)
                        Url::redirect("events/manage/".$eventID);
                }
                else
                {
                    $error[] = __("event_chapters_error");
                }
            }
        }
        else
        {
            $error[] = __("empty_or_not_permitted_event_error");
        }

        $data["notifications"] = $this->_notifications;
        return View::make('Events/Manage')
            ->shares("title", __("manage_event"))
            ->shares("data", $data)
            ->shares("error", @$error);
    }


    public function demo($page = null)
    {
        if (!Session::get('loggedin'))
        {
            Url::redirect('members/login');
        }

        if(empty(Session::get("profile")))
        {
            Url::redirect("members/profile");
        }

        if (!Session::get('verified'))
        {
            $error[] = __("account_not_verirfied_error");
        }

        if(!isset($page))
            Url::redirect("events/demo/pray");

        $notifObj = new \stdClass();
        $notifObj->step = EventSteps::KEYWORD_CHECK;
        $notifObj->currentChapter = 2;
        $notifObj->userName = "Gen2Pet";
        $notifObj->bookCode = "2ti";
        $notifObj->bookProject = "ulb";
        $notifObj->tLang = "Русский";
        $notifObj->bookName = "2 Timothy";

        $notifications[] = $notifObj;
        $data["notifications"] = $notifications;
        $data["isDemo"] = true;
        $data['menu'] = 4;

        $view = View::make("Events/Demo/DemoHeader");
        $data["step"] = "";

        switch ($page)
        {
            case "pray":
                $view->nest("page", "Events/Demo/Pray");
                $data["step"] = EventSteps::PRAY;
                break;

            case "consume":
                $view->nest("page", "Events/Demo/Consume");
                $data["step"] = EventSteps::CONSUME;
                break;

            case "verbalize":
                $view->nest("page", "Events/Demo/Verbalize");
                $data["step"] = EventSteps::VERBALIZE;
                break;

            case "verbalize_checker":
                $view->nest("page", "Events/Demo/VerbalizeChecker");
                $data["step"] = EventSteps::VERBALIZE;
                $data["isCheckerPage"] = true;
                break;

            case "chunking":
                $view->nest("page", "Events/Demo/Chunking");
                $data["step"] = EventSteps::CHUNKING;
                break;

            case "read_chunk":
                $view->nest("page", "Events/Demo/ReadChunk");
                $data["step"] = EventSteps::READ_CHUNK;
                break;

            case "blind_draft":
                $view->nest("page", "Events/Demo/BlindDraft");
                $data["step"] = EventSteps::BLIND_DRAFT;
                break;

            case "self_check":
                $view->nest("page", "Events/Demo/SelfCheck");
                $data["step"] = EventSteps::SELF_CHECK;
                break;

            case "peer_review":
                $view->nest("page", "Events/Demo/PeerReview");
                $data["step"] = EventSteps::PEER_REVIEW;
                break;

            case "peer_review_checker":
                $view->nest("page", "Events/Demo/PeerReviewChecker");
                $data["step"] = EventSteps::PEER_REVIEW;
                $data["isCheckerPage"] = true;
                break;

            case "keyword_check":
                $view->nest("page", "Events/Demo/KeywordCheck");
                $data["step"] = EventSteps::KEYWORD_CHECK;
                break;

            case "keyword_check_checker":
                $view->nest("page", "Events/Demo/KeywordCheckChecker");
                $data["step"] = EventSteps::KEYWORD_CHECK;
                $data["isCheckerPage"] = true;
                break;

            case "content_review":
                $view->nest("page", "Events/Demo/ContentReview");
                $data["step"] = EventSteps::CONTENT_REVIEW;
                break;

            case "content_review_checker":
                $view->nest("page", "Events/Demo/ContentReviewChecker");
                $data["step"] = EventSteps::CONTENT_REVIEW;
                $data["isCheckerPage"] = true;
                break;

            case "final_review":
                $view->nest("page", "Events/Demo/FinalReview");
                $data["step"] = EventSteps::FINAL_REVIEW;
                break;
        }

        return $view
            ->shares("title", __("demo"))
            ->shares("data", $data);

        //View::render('events/demo/demo_header', $data);
        //View::renderTemplate('footer', $data);
    }

    public function applyEvent()
    {
        if (!Session::get('loggedin'))
        {
            return;
        }

        if (!Session::get('verified'))
        {
            $error[] = __("account_not_verirfied_error");
            echo json_encode(array("error" => Error::display($error)));
            return;
        }

        $data["errors"] = array();
        $profile = Session::get("profile");

        $_POST = Gump::xss_clean($_POST);

        $eventID = isset($_POST['eventID']) && $_POST['eventID'] != "" ? $_POST['eventID'] : null;
        $userType = isset($_POST['userType']) && $_POST['userType'] != "" ? $_POST['userType'] : null;

        $education = isset($_POST["education"]) && !empty($_POST["education"]) ? (array)$_POST["education"] : null;
        $ed_area = isset($_POST["ed_area"]) && !empty($_POST["ed_area"]) ? (array)$_POST["ed_area"] : array();
        $ed_place = isset($_POST["ed_place"]) && trim($_POST["ed_place"]) != "" ? trim($_POST["ed_place"]) : "";
        $hebrew_knwlg = isset($_POST["hebrew_knwlg"]) && preg_match("/^[0-4]{1}$/", $_POST["hebrew_knwlg"]) ? $_POST["hebrew_knwlg"] : 0;
        $greek_knwlg = isset($_POST["greek_knwlg"]) && preg_match("/^[0-4]{1}$/", $_POST["greek_knwlg"]) ? $_POST["greek_knwlg"] : 0;
        $church_role = isset($_POST["church_role"]) && !empty($_POST["church_role"]) ? (array)$_POST["church_role"] : array();

        if($eventID == null)
        {
            $error[] = __('wrong_event_parameters');
            echo json_encode(array("error" => $error));
            return;
        }

        if($userType == null || !preg_match("/^(".EventMembers::TRANSLATOR."|".EventMembers::L2_CHECKER."|".EventMembers::L3_CHECKER.")$/", $userType))
        {
            $error[] = __("wrong_event_parameters");
            echo json_encode(array("error" => $error));
            return;
        }

        if($userType == EventMembers::L2_CHECKER || $userType == EventMembers::L3_CHECKER)
        {
            if($education === null) {
                //$data["errors"]["education"] = true;
            }
            else
            {
                foreach ($education as $item) {
                    if(!preg_match("/^(BA|MA|PHD)$/", $item))
                    {
                        $data["errors"]["education"] = true;
                        break;
                    }
                }
            }

            if($ed_area === null)
                $data["errors"]["ed_area"] = true;
            else
            {
                foreach ($ed_area as $item) {
                    if(!preg_match("/^(Theology|Pastoral Ministry|Bible Translation|Exegetics)$/", $item))
                    {
                        $data["errors"]["ed_area"] = true;
                        break;
                    }
                }
            }

            if($ed_place === null)
                $data["errors"]["ed_place"] = true;

            if($hebrew_knwlg === null)
                $data["errors"]["hebrew_knwlg"] = true;

            if($greek_knwlg === null)
                $data["errors"]["greek_knwlg"] = true;

            if($church_role === null)
                $data["errors"]["church_role"] = true;
            else
            {
                foreach ($church_role as $item) {
                    if(!preg_match("/^(Elder|Bishop|Pastor|Teacher|Denominational Leader|Seminary Professor)$/", $item))
                    {
                        $data["errors"]["church_role"] = true;
                        break;
                    }
                }
            }
        }

        if(empty($data["errors"]))
        {
            $event = $this->_model->getEvent($eventID, null, null, true);

            if(empty($event))
            {
                $error[] = __("event_notexist_error");
                echo json_encode(array("error" => $error));
                return;
            }

            $exists = $this->_model->getEventMember($event[0]->eventID, Session::get("memberID"));

            //Data::pr($exists);

            $checkerData = array(
                "education" => $education,
                "ed_area" => $ed_area,
                "ed_place" => $ed_place,
                "hebrew_knwlg" => $hebrew_knwlg,
                "greek_knwlg" => $greek_knwlg,
                "church_role" => $church_role
            );

            switch($userType)
            {
                case EventMembers::TRANSLATOR:

                    if($event[0]->translators < $event[0]->translatorsNum)
                    {
                        if(!empty(array_filter(json_decode($event[0]->chapters, true), function($v) { return empty($v);})))
                        {
                            if($exists[0]->translator == null &&
                                $exists[0]->checker_l2 == null && $exists[0]->checker_l3 == null)   // can apply as translator only if not checker l2, l3
                            {
                                $trData = array(
                                    "memberID" => Session::get("memberID"),
                                    "eventID" => $event[0]->eventID
                                );
                                $trID = $this->_model->addTranslator($trData);

                                if(is_numeric($trID))
                                {
                                    echo json_encode(array("success" => __("successfully_applied")));
                                }
                                else
                                {
                                    $error[] = __("error_ocured", array($trID));
                                }
                            }
                            else
                            {
                                $error[] = __("error_member_in_event");
                            }
                        }
                        else
                        {
                            $error[] = __("no_translators_available_error");
                        }
                    }
                    else
                    {
                        $error[] = __("no_translators_available_error");
                    }
                    break;

                case EventMembers::L2_CHECKER:
                    if($event[0]->checkers_l2 < $event[0]->l2CheckersNum)
                    {
                        if($exists[0]->translator == null && $exists[0]->checker == null &&
                            $exists[0]->checker_l2 == null && $exists[0]->checker_l3 == null)   // can apply as checker L2 only if not translator or checker 7/8
                        {
                            $l2Data = array(
                                "memberID" => Session::get("memberID"),
                                "eventID" => $event[0]->eventID
                            );
                            $l2ID = $this->_model->addL2Checker($l2Data, $checkerData);

                            if(is_numeric($l2ID))
                            {
                                echo json_encode(array("success" => __("successfully_applied")));
                            }
                            else
                            {
                                $error[] = __("error_ocured", array($l2ID));
                            }
                        }
                        else
                        {
                            $error[] = __("error_member_in_event");
                        }
                    }
                    else
                    {
                        $error[] = __("no_l2_3_checkers_available_error", array(2));
                    }
                    break;

                case EventMembers::L3_CHECKER:
                    if($event[0]->checkers_l3 < $event[0]->l3CheckersNum)
                    {
                        if($exists[0]->translator == null && $exists[0]->checker == null &&
                            $exists[0]->checker_l2 == null && $exists[0]->checker_l3 == null)   // can apply as checker L3 only if not translator or checker 7/8
                        {
                            $l3Data = array(
                                "memberID" => Session::get("memberID"),
                                "eventID" => $event[0]->eventID
                            );
                            $l3ID = $this->_model->addL3Checker($l3Data, $checkerData);

                            if(is_numeric($l3ID))
                            {
                                echo json_encode(array("success" => __("successfully_applied")));
                            }
                            else
                            {
                                $error[] = __("error_ocured", array($l3ID));
                            }
                        }
                        else
                        {
                            $error[] = __("error_member_in_event");
                        }
                    }
                    else
                    {
                        $error[] = __("no_l2_3_checkers_available_error", array(3));
                    }
                    break;
            }

            if(isset($error))
            {
                echo json_encode(array("error" => $error));
            }
        }
        else
        {
            $error[] = __('required_fields_empty_error');
            echo json_encode(array("error" => $error, "errors" => $data["errors"]));
        }
    }


    public function autosaveChunk()
    {
        $response = array("success" => false);

        if (!Session::get('loggedin'))
        {
            $response["errorType"] = "logout";
            $response["error"] = __("not_loggedin_error");
            echo json_encode($response);
            return;
        }

        if (!Session::get('verified'))
        {
            $response["errorType"] = "verify";
            $response["error"] = __("account_not_verirfied_error");
            echo json_encode($response);
            return;
        }

        $_POST = Gump::xss_clean($_POST);

        $eventID = isset($_POST["eventID"]) && is_numeric($_POST["eventID"]) ? $_POST["eventID"] : null;
        $formData = isset($_POST["formData"]) && $_POST["formData"] != "" ? $_POST["formData"] : null;
        $shoudUpdate = false;

        if($eventID !== null && $formData !== null)
        {
            $event = $this->_model->getMemberEvents(Session::get("memberID"), EventMembers::TRANSLATOR, $eventID);

            if(!empty($event))
            {
                $post = array();
                parse_str(htmlspecialchars_decode($formData, ENT_QUOTES), $post);

                $chapters = json_decode($event[0]->chapters, true);
                $chunks = $chapters[$event[0]->currentChapter]["chunks"];
                $chunk = $chunks[$event[0]->currentChunk];

                switch($event[0]->step)
                {
                    case EventSteps::BLIND_DRAFT:
                        if(trim($post["draft"]) != "") {
                            if($event[0]->lastTID > 0)
                            {
                                $translationData = $this->_model->getTranslation($event[0]->trID, $event[0]->lastTID);

                                if(!empty($translationData))
                                {
                                    if($translationData[0]->chapter == $event[0]->currentChapter &&
                                        $translationData[0]->chunk == $event[0]->currentChunk)
                                    {
                                        $translationVerses = json_decode($translationData[0]->translatedVerses, true);
                                        $shoudUpdate = true;
                                    }
                                }
                            }

                            if(!$shoudUpdate)
                            {
                                $translationVerses = array(
                                    EventMembers::TRANSLATOR => array(
                                        "blind" => trim($post["draft"]),
                                        "verses" => array()
                                    ),
                                    EventMembers::L2_CHECKER => array(
                                        "verses" => array()
                                    ),
                                    EventMembers::L3_CHECKER => array(
                                        "verses" => array()
                                    ),
                                );

                                $trData = array(
                                    "projectID" => $event[0]->projectID,
                                    "eventID" => $event[0]->eventID,
                                    "trID" => $event[0]->trID,
                                    "targetLang" => $event[0]->targetLang,
                                    "bookProject" => $event[0]->bookProject,
                                    "abbrID" => $event[0]->abbrID,
                                    "bookCode" => $event[0]->bookCode,
                                    "chapter" => $event[0]->currentChapter,
                                    "chunk" => $event[0]->currentChunk,
                                    "firstvs" => $chunk[0],
                                    "translatedVerses" => json_encode($translationVerses),
                                    "dateCreate" => date('Y-m-d H:i:s')
                                );

                                $tID = $this->_model->createTranslation($trData);

                                if ($tID) {
                                    $this->_model->updateTranslator(array("lastTID" => $tID), array("trID" => $event[0]->trID));
                                    $response["success"] = true;
                                }
                            }
                            else
                            {
                                $translationVerses[EventMembers::TRANSLATOR]["blind"] = trim($post["draft"]);

                                $trData = array(
                                    "translatedVerses"  => json_encode($translationVerses),
                                );

                                $this->_model->updateTranslation($trData, array("trID" => $event[0]->trID, "tID" => $event[0]->lastTID));
                                $response["success"] = true;
                            }
                        }
                        break;

                    case EventSteps::SELF_CHECK:
                    case EventSteps::PEER_REVIEW:
                    case EventSteps::KEYWORD_CHECK:
                    case EventSteps::CONTENT_REVIEW:
                        if(is_array($post["chunks"]) && !empty($post["chunks"]))
                        {
                            if($event[0]->step == EventSteps::KEYWORD_CHECK || $event[0]->step == EventSteps::CONTENT_REVIEW)
                            {
                                if($event[0]->checkDone)
                                {
                                    $response["errorType"] = "checkDone";
                                    $response["error"] = __("not_possible_to_save_error");
                                    echo json_encode($response);
                                    exit;
                                }
                            }

                            $translationData = $this->_model->getTranslation($event[0]->trID, null, $event[0]->currentChapter);
                            $translation = array();

                            foreach ($translationData as $tv) {
                                $arr = json_decode($tv->translatedVerses, true);
                                $arr["tID"] = $tv->tID;
                                $translation[] = $arr;
                            }

                            if(!empty($translation))
                            {
                                $post["chunks"] = array_map("trim", $post["chunks"]);
                                $post["chunks"] = array_filter($post["chunks"], function($v) {
                                    return !empty($v);
                                });

                                $updated = 0;
                                foreach ($translation as $key => $chunk) {
                                    if(!isset($post["chunks"][$key])) continue;

                                    $shouldUpdate = false;
                                    if($chunk[EventMembers::TRANSLATOR]["blind"] != $post["chunks"][$key])
                                        $shouldUpdate = true;

                                    $translation[$key][EventMembers::TRANSLATOR]["blind"] = $post["chunks"][$key];

                                    if($shouldUpdate)
                                    {
                                        $tID = $translation[$key]["tID"];
                                        unset($translation[$key]["tID"]);
                                        $trData = array(
                                            "translatedVerses"  => json_encode($translation[$key])
                                        );
                                        $this->_model->updateTranslation($trData, array("trID" => $event[0]->trID, "tID" => $tID));
                                        $updated++;
                                    }
                                }

                                if($updated)
                                    $response["success"] = true;
                                else
                                {
                                    $response["errorType"] = "noChange";
                                    $response["error"] = "no_change";
                                }
                            }
                        }
                        break;
                }
            }
        }

        echo json_encode($response);
    }


    public function saveComment()
    {
        $response = array("success" => false);

        if (!Session::get('loggedin'))
        {
            $response["error"] = __("not_loggedin_error");
            echo json_encode($response);
            return;
        }

        if (!Session::get('verified'))
        {
            $response["error"] = __("account_not_verirfied_error");
            echo json_encode($response);
            return;
        }

        $_POST = Gump::xss_clean($_POST);

        $eventID = isset($_POST["eventID"]) && $_POST["eventID"] != "" ? (integer)$_POST["eventID"] : null;
        $chapter = isset($_POST["chapter"]) && $_POST["chapter"] != "" ? (integer)$_POST["chapter"] : null;
        $chunk = isset($_POST["chunk"]) && $_POST["chunk"] != "" ? (integer)$_POST["chunk"] : null;
        $comment = isset($_POST["comment"]) ? $_POST["comment"] : "";
        $memberID = Session::get("memberID");

        if($eventID !== null && $chapter !== null && $chunk !== null)
        {
            $memberInfo = (array)$this->_model->getEventMemberInfo($eventID, $memberID);

            if(!empty($memberInfo) && ($memberInfo[0]->translator == $memberID ||
                    $memberInfo[0]->checker7_8 == $memberID ||
                    $memberInfo[0]->l2checker == $memberID || $memberInfo[0]->l3checker == $memberID))
            {
                $transModel = new TranslationsModel();
                $commentDB = (array)$transModel->getComment($eventID, $chapter, $chunk, Session::get("memberID"));

                $postdata = array(
                    "text" => $comment,
                );

                $result = false;

                if(!empty($commentDB))
                {
                    if($comment == "")
                    {
                        $result = $transModel->deleteComment(array("cID" => $commentDB[0]->cID));
                    }
                    else
                    {
                        $result = $transModel->updateComment($postdata,  array("cID" => $commentDB[0]->cID));
                    }
                }
                else
                {
                    $postdata += array(
                        "eventID" => $eventID,
                        "chapter" => $chapter,
                        "chunk" => $chunk,
                        "memberID" => Session::get("memberID")
                    );

                    $result = $transModel->createComment($postdata);
                }

                if($result)
                {
                    $response["success"] = true;
                    $response["text"] = $comment;
                }
            }
        }

        echo json_encode($response);
    }



    public function getInfoUpdate($eventID)
    {
        if (!Session::get('loggedin'))
        {
            echo "login";
            exit;
        }

        if(empty(Session::get("profile")))
        {
            echo "profile";
            exit;
        }

        if (!Session::get('verified'))
        {
            echo "not_verified";
            exit;
        }

        $data["event"] = $this->_model->getEventMember($eventID, Session::get("memberID"), true);
        $data["isAdmin"] = false;

        $canViewInfo = false;

        $memberModel = new MembersModel();

        if(!empty($data["event"]))
        {
            if($data["event"][0]->translator === null && $data["event"][0]->checker === null)
            {
                // If member is not a participant of the event, check if he is a facilitator
                if(Session::get("isAdmin"))
                {
                    $admin = $memberModel->getAdminMember(Session::get("memberID"));

                    foreach ($admin as $item) {
                        if($item->gwLang == $data["event"][0]->gwLang)
                        {
                            $data["isAdmin"] = true;
                            $canViewInfo = true;
                            break;
                        }
                    }

                    if(!$data["isAdmin"])
                    {
                        echo "empty_no_permission";
                        exit;
                    }
                }
                else
                {
                    echo "empty_no_permission";
                    exit;
                }
            }
            else
            {
                $canViewInfo = true;

                if(Session::get("isAdmin"))
                {
                    $admin = $memberModel->getAdminMember(Session::get("memberID"));

                    foreach ($admin as $item) {
                        if($item->gwLang == $data["event"][0]->gwLang)
                        {
                            $data["isAdmin"] = true;
                            break;
                        }
                    }
                }
            }

            if($data["event"][0]->state == "started" && $canViewInfo)
            {
                echo "not_started";
                exit;
            }
        }
        else
        {
            echo "empty_no_permission";
            exit;
        }

        if(!isset($error))
        {
            $data["chapters"] = json_decode($data["event"][0]->chapters, true);

            $translationModel = new TranslationsModel();
            $chunks = $translationModel->getTranslationByEventID($data["event"][0]->eventID);
            $members = [];

			$memberSteps = [];
            $currentChecker = "na";
            $currentState = "not_started";

            $i = 0;
            foreach ($chunks as $index => $chunk) {
                if($chunk->chapter === null)
                {
                    $memberSteps[$chunk->memberID] = $chunk->step;
                    if($chunk->checkerID > 0)
                    {
                        $currentState = "in_progress";
                        $currentChecker = $chunk->checkerID;
                        $members[$currentChecker] = "";
                    }
                    elseif ($chunk->verbCheck != "")
                    {
                        $verbCheck = (array)json_decode($chunk->verbCheck, true);
                        if(array_key_exists($chunk->currentChapter, $verbCheck))
                        {
                            $currentState = "finished";
                            $currentChecker = $verbCheck[$chunk->currentChapter];
                            $members[$currentChecker] = "";
                        }
                    }
					continue;
                }

                if($i < $chunk->chapter)
                {
                    $chunk->verbCheck = (array)json_decode($chunk->verbCheck, true);
                    $chunk->peerCheck = (array)json_decode($chunk->peerCheck, true);
                    $chunk->kwCheck = (array)json_decode($chunk->kwCheck, true);
                    $chunk->crCheck = (array)json_decode($chunk->crCheck, true);

                    // Verbalize Check
                    if(array_key_exists($chunk->chapter, $chunk->verbCheck))
                    {
                        $data["chapters"][$chunk->chapter]["verb"]["state"] = "finished";
                        $data["chapters"][$chunk->chapter]["verb"]["checkerID"] = $chunk->verbCheck[$chunk->chapter];
                        $members[$chunk->verbCheck[$chunk->chapter]] = "";
                    }
                    else
                    {
                        if($chunk->chapter == $chunk->currentChapter)
                        {
                            if($chunk->step == EventSteps::VERBALIZE && $chunk->checkerID > 0)
                            {
                                $data["chapters"][$chunk->chapter]["verb"]["state"] = "in_progress";
                                $data["chapters"][$chunk->chapter]["verb"]["checkerID"] = $chunk->checkerID;
                                $members[$chunk->checkerID] = "";
                            }
                        }
                    }

                    // Peer Check
                    if(array_key_exists($chunk->chapter, $chunk->peerCheck))
                    {
                        $data["chapters"][$chunk->chapter]["peer"]["state"] = "finished";
                        $data["chapters"][$chunk->chapter]["peer"]["checkerID"] = $chunk->peerCheck[$chunk->chapter];
                        $members[$chunk->peerCheck[$chunk->chapter]] = "";
                    }
                    else
                    {
                        if($chunk->chapter == $chunk->currentChapter)
                        {
                            if($chunk->step == EventSteps::PEER_REVIEW && $chunk->checkerID > 0)
                            {
                                $data["chapters"][$chunk->chapter]["peer"]["state"] = "in_progress";
                                $data["chapters"][$chunk->chapter]["peer"]["checkerID"] = $chunk->checkerID;
                                $members[$chunk->checkerID] = "";
                            }
                        }
                    }

                    // Keyword Check
                    if(array_key_exists($chunk->chapter, $chunk->kwCheck))
                    {
                        $data["chapters"][$chunk->chapter]["kwc"]["state"] = "finished";
                        $data["chapters"][$chunk->chapter]["kwc"]["checkerID"] = $chunk->kwCheck[$chunk->chapter];
                        $members[$chunk->kwCheck[$chunk->chapter]] = "";
                    }
                    else
                    {
                        if($chunk->chapter == $chunk->currentChapter)
                        {
                            if($chunk->step == EventSteps::KEYWORD_CHECK && $chunk->checkerID > 0)
                            {
                                $data["chapters"][$chunk->chapter]["kwc"]["state"] = "in_progress";
                                $data["chapters"][$chunk->chapter]["kwc"]["checkerID"] = $chunk->checkerID;
                                $members[$chunk->checkerID] = "";
                            }
                        }
                    }


                    // Content Review Check
                    if(array_key_exists($chunk->chapter, $chunk->crCheck))
                    {
                        $data["chapters"][$chunk->chapter]["crc"]["state"] = "finished";
                        $data["chapters"][$chunk->chapter]["crc"]["checkerID"] = $chunk->crCheck[$chunk->chapter];
                        $data["chapters"][$chunk->chapter]["step"] = EventSteps::FINISHED;
                        $members[$chunk->crCheck[$chunk->chapter]] = "";
                    }
                    else
                    {
                        if($chunk->chapter == $chunk->currentChapter)
                        {
                            if($chunk->step == EventSteps::CONTENT_REVIEW && $chunk->checkerID > 0 &&
                                $data["chapters"][$chunk->chapter]["kwc"]["state"] != "in_progress")
                            {
                                $data["chapters"][$chunk->chapter]["crc"]["state"] = "in_progress";
                                $data["chapters"][$chunk->chapter]["crc"]["checkerID"] = $chunk->checkerID;
                                $members[$chunk->checkerID] = "";
                            }
                        }
                    }

                    $i = $chunk->chapter;
                }

                $data["chapters"][$chunk->chapter]["chunksData"][] = $chunk;
                if(!isset($data["chapters"][$chunk->chapter]["step"]))
                    $data["chapters"][$chunk->chapter]["step"] = $chunk->step;
            }

            $overallProgress = 0;

            foreach ($data["chapters"] as $key => $chapter) {
                if(empty($chapter)) continue;

                $data["chapters"][$key]["progress"] = 0;

				if(empty($chapter["chunks"]) || !isset($chapter["chunksData"]))
                {
                    $data["chapters"][$key]["step"] = $memberSteps[$chapter["memberID"]];
                    $data["chapters"][$key]["verb"]["state"] = $currentState;
                    $data["chapters"][$key]["verb"]["checkerID"] = $currentChecker;
                    $data["chapters"][$key]["peer"]["state"] = "not_started";
                    $data["chapters"][$key]["peer"]["checkerID"] = "na";
                    $data["chapters"][$key]["kwc"]["state"] = "not_started";
                    $data["chapters"][$key]["kwc"]["checkerID"] = "na";
                    $data["chapters"][$key]["crc"]["state"] = "not_started";
                    $data["chapters"][$key]["crc"]["checkerID"] = "na";

                    $data["chapters"][$key]["chunksData"] = [];
                    $members[$chapter["memberID"]] = "";
                    continue;
                }
				
				$members[$chapter["memberID"]] = "";
				
                if(sizeof($chapter["chunks"]) > 0)
                {
                    // Total translated chunks are 25% of all chapter progress
                    $data["chapters"][$key]["progress"] += sizeof($chapter["chunksData"]) * 25 / sizeof($chapter["chunks"]);
                    if(!isset($data["chapters"][$key]["step"]))
                        $data["chapters"][$key]["step"] = EventSteps::READ_CHUNK;
                }

                if(!array_key_exists("verb", $chapter))
                {
                    $data["chapters"][$key]["verb"]["state"] = "not_started";
                    $data["chapters"][$key]["verb"]["checkerID"] = "na";
                }

                if(!array_key_exists("peer", $chapter))
                {
                    $data["chapters"][$key]["peer"]["state"] = "not_started";
                    $data["chapters"][$key]["peer"]["checkerID"] = "na";
                }
                else
                {
                    // Add 25% of progress when peer check done
                    if(array_key_exists("state", $chapter["peer"]) && $data["chapters"][$key]["peer"]["state"] == "finished")
                        $data["chapters"][$key]["progress"] += 25;
                }

                if(!array_key_exists("kwc", $chapter))
                {
                    $data["chapters"][$key]["kwc"]["state"] = "not_started";
                    $data["chapters"][$key]["kwc"]["checkerID"] = "na";
                }
                else
                {
                    if(array_key_exists("state", $chapter["kwc"]) && $data["chapters"][$key]["kwc"]["state"] == "finished")
                        $data["chapters"][$key]["progress"] += 25;
                }

                if(!array_key_exists("crc", $chapter))
                {
                    $data["chapters"][$key]["crc"]["state"] = "not_started";
                    $data["chapters"][$key]["crc"]["checkerID"] = "na";
                }
                else
                {
                    if(array_key_exists("state", $chapter["crc"]) && $data["chapters"][$key]["crc"]["state"] == "finished")
                        $data["chapters"][$key]["progress"] += 25;
                }

                $overallProgress += $data["chapters"][$key]["progress"];
            }

            $data["overall_progress"] = $overallProgress / sizeof($data["chapters"]);

            $adminMembers = $memberModel->getAdminsByGwProject($data["event"][0]->gwProjectID);
            $adminsArr = json_decode($adminMembers[0]->admins, true);
            $empty = array_fill(0, sizeof($adminsArr), "");
            $admins = array_combine($adminsArr, $empty);

            $members += $admins;
            $membersArray = (array)$memberModel->getMembers(array_filter(array_keys($members)));

            foreach ($membersArray as $member) {
                $members[$member->memberID] = $member->userName;
            }

            $members["na"] = __("not_available");
            $members = array_filter($members);

            $data["admins"] = $adminsArr;
            $data["members"] = $members;
        }

        $data["notifications"] = $this->_notifications;

        $this->layout = "dummy";
        return View::make("Events/GetInfo")
            ->shares("data", $data);
    }

    public function applyChecker($eventID, $memberID)
    {
        if (!Session::get('loggedin'))
        {
            Session::set('redirect', 'admin');
            Url::redirect('members/login');
        }

        if (!Session::get('verified'))
        {
            $error[] = __("account_not_verirfied_error");
            echo json_encode(array("error" => Error::display($error)));
            return;
        }

        $canApply = false;

        $profile = Session::get("profile");
        $langs = [];
        foreach ($profile["languages"] as $lang => $item) {
            $langs[] = $lang;
        }

        $allNotifications = $this->_model->getAllNotifications($langs);
        $allNotifications += $this->_notifications;

        foreach ($allNotifications as $notification) {
            if($eventID == $notification->eventID && $memberID == $notification->memberID)
            {
                if($notification->checkerID == 0)
                {
                    $canApply = true;
                    break;
                }
            }
        }

        if($canApply)
        {
            $postdata = ["checkerID" => Session::get("memberID"), "hideChkNotif" => true];
            $this->_model->updateTranslator($postdata, array("eventID" => $eventID, "memberID" => $memberID));
            Url::redirect('events/checker/'.$eventID.'/'.$memberID);
            exit;
        }
        else
        {
            $error[] = __("cannot_apply_checker");
        }

        $data["menu"] = 4;
        $data["notifications"] = $this->_notifications;

        return View::make("Events/CheckerApply")
            ->shares("title", __("apply_checker_l1"))
            ->shares("data", $data)
            ->shares("error", @$error);
    }


    public function getPartnerTranslation()
    {
        //TODO move html code to the view
        if (!Session::get('loggedin'))
        {
            echo __("not_loggedin_error");
            return;
        }

        if (!Session::get('verified'))
        {
            echo __("account_not_verirfied_error");
            return;
        }

        $_POST = Gump::xss_clean($_POST);

        $eventID = isset($_POST["eventID"]) && $_POST["eventID"] != "" ? (integer)$_POST["eventID"] : null;

        if($eventID !== null)
        {
            $data["event"] = $this->_model->getMemberEvents(Session::get("memberID"), EventMembers::TRANSLATOR, $eventID);

            if(!empty($data["event"]))
            {
                $chapters = json_decode($data["event"][0]->chapters, true);
                $currentChapter = $data["event"][0]->peerChapter;
                foreach ($chapters as $chapter => $chapData) {
                    if($chapData["trID"] != $data["event"][0]->cotrID) continue;
                    if($chapter <= $currentChapter) continue;
                    $currentChapter = $chapter;
                    break;
                }

                $data["event"][0]->cotrCurrentChapter = $currentChapter;

                $data["comments_cotr"] = $this->getComments($data["event"][0]->eventID, $data["event"][0]->cotrCurrentChapter);

                $cotrSourceText = $this->getSourceTextUSFM($data, false, true);

                if (!array_key_exists("error", $cotrSourceText)) {
                    $data["cotrData"] = $cotrSourceText;

                    $coTranslationTemp = $this->_model->getTranslation($data["event"][0]->cotrID, null, $data["event"][0]->cotrCurrentChapter);
                    $coTranslation = array();

                    $cotrReady = $data["event"][0]->cotrPeerReady;

                    foreach ($coTranslationTemp as $tv) {
                        $tmp = json_decode($tv->translatedVerses, true);
                        $tmp["tID"] = $tv->tID;
                        $coTranslation[] = $tmp;
                    }

                    $data["cotrData"]["cotrReady"] = $cotrReady;
                    $data["cotrData"]["translation"] = $coTranslation;

                    if($data["cotrData"]["cotrReady"]) :
                        $sourceVerses = array_keys($data["cotrData"]["text"]);
                        $i=0;
                        foreach($data["cotrData"]["translation"] as $key => $chunk) :
                            $count = 0;
                            foreach($chunk["translator"]["verses"] as $verse => $text) :
                                $verses = Tools::parseCombinedVerses($sourceVerses[$i]);
                                if ($count == 0): ?>
                                <div class="row">
                                    <div class="col-sm-6">
                                        <p><strong><sup> <?php echo $sourceVerses[$i] ?></sup></strong> <?php echo $data["cotrData"]["text"][$sourceVerses[$i]] ?></p>
                                    </div>
                                    <div class="col-sm-6 verse_with_note">
                                <?php endif; ?>
                                        <div class="vnote">
                                            <strong><sup><?php echo $verse; ?></sup></strong>
                                            <?php echo $text; ?>

                                            <?php $hasCotrComments = array_key_exists($data["cotrData"]["currentChapter"], $data["comments_cotr"]) && array_key_exists($verse, $data["comments_cotr"][$data["cotrData"]["currentChapter"]]); ?>
                                            <div class="comments_number <?php echo $hasCotrComments ? "hasComment" : "" ?>">
                                                <?php echo $hasCotrComments ? sizeof($data["comments_cotr"][$data["cotrData"]["currentChapter"]][$verse]) : ""?>
                                            </div>
                                            <img class="editComment" data="<?php echo $data["cotrData"]["currentChapter"].":".$verse ?>" width="16px" src="<?php echo \Helpers\Url::templatePath() ?>img/edit.png" title="write note"/>

                                            <div class="comments">
                                                <?php if($hasCotrComments): ?>
                                                    <?php foreach($data["comments_cotr"][$data["cotrData"]["currentChapter"]][$verse] as $comment): ?>
                                                        <?php if($comment->memberID == $data["event"][0]->myMemberID): ?>
                                                            <div class="my_comment" data="<?php echo $data["cotrData"]["currentChapter"].":".$verse ?>"><?php echo $comment->text; ?></div>
                                                        <?php else: ?>
                                                            <div class="other_comments"><?php echo "<span>".$comment->userName.":</span> ".$comment->text; ?></div>
                                                        <?php endif; ?>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </div>
                                            <div class="clear"></div>
                                        </div>

                                <?php
                                $count++;

                                if ($count == sizeof($verses)) :
                                    $i += 1;
                                    $count = 0; ?>
                                    </div>
                                </div>
                                <?php endif; endforeach; ?>
                            <div class="chunk_divider col-sm-12"></div>
                            <?php endforeach; ?>
                        <?php else: ?>
                        <div class="row">
                            <div class="col-sm-12 cotr_not_ready" style="color: #ff0000;"><?php echo Language::show("partner_not_ready_message", "Events"); ?></div>
                        </div>
                    <?php endif;
                }
            }
        }
    }


    public function getEventMembers()
    {
        $response = array("success" => false);

        if (!Session::get('loggedin'))
        {
            $response["error"] = __("not_loggedin_error");
            echo json_encode($response);
            return;
        }

        if (!Session::get('verified'))
        {
            $response["error"] = __("account_not_verirfied_error");
            echo json_encode($response);
            return;
        }

        if (!Session::get('isAdmin'))
        {
            $response["error"] = __("not_enough_rights_error");
            echo json_encode($response);
            return;
        }

        $_POST = Gump::xss_clean($_POST);

        $eventID = isset($_POST["eventID"]) && $_POST["eventID"] != "" ? (integer)$_POST["eventID"] : null;
        $memberIDs = isset($_POST["memberIDs"]) && $_POST["memberIDs"] != "" ? (array)$_POST["memberIDs"] : null;

        if($eventID !== null && $memberIDs != null)
        {
            $members = $this->_model->getMembersForEvent($eventID);
            foreach ($members as $key => $member) {
                if(in_array($member["memberID"], $memberIDs))
                    unset($members[$key]);
            }

            $response["members"] = $members;
            $response["success"] = true;
        }
        else
        {
            $response["error"] = __("error_ocured", array("wrong parameters"));
        }

        echo json_encode($response);
    }


    public function deleteEventMember()
    {
        $response = array("success" => false);

        if (!Session::get('loggedin'))
        {
            $response["error"] = __("not_loggedin_error");
            echo json_encode($response);
            return;
        }

        if (!Session::get('verified'))
        {
            $response["error"] = __("account_not_verirfied_error");
            echo json_encode($response);
            return;
        }

        if (!Session::get('isAdmin'))
        {
            $response["error"] = __("not_enough_rights_error");
            echo json_encode($response);
            return;
        }

        $_POST = Gump::xss_clean($_POST);

        $eventID = isset($_POST["eventID"]) && $_POST["eventID"] != "" ? (integer)$_POST["eventID"] : null;
        $memberID = isset($_POST["memberID"]) && $_POST["memberID"] != "" ? (integer)$_POST["memberID"] : null;

        if($eventID !== null && $memberID != null)
        {
            $event = $this->_model->getEvent($eventID);
            if(!empty($event))
            {
                $hasChapter = false;
                $chapters = json_decode($event[0]->chapters, true);
                foreach ($chapters as $chapter) {
                    if(empty($chapter)) continue;
                    if($chapter['memberID'] == $memberID)
                    {
                        $hasChapter = true;
                        break;
                    }
                }

                if(!$hasChapter)
                {
                    $this->_model->deleteTranslators(["eventID" => $eventID, "memberID" => $memberID]);
                    $response["success"] = true;
                }
                else
                {
                    $response["error"] = __("translator_has_chapter");
                }
            }
            else
            {
                $response["error"] = __("error_ocured", array("wrong parameters"));
            }
        }
        else
        {
            $response["error"] = __("error_ocured", array("wrong parameters"));
        }

        echo json_encode($response);
    }

    public function checkEvent()
    {
        $response = array("success" => false);

        if (!Session::get('loggedin'))
        {
            $response["error"] = __("not_loggedin_error");
            echo json_encode($response);
            return;
        }

        if (!Session::get('verified'))
        {
            $response["error"] = __("account_not_verirfied_error");
            echo json_encode($response);
            return;
        }

        $_POST = Gump::xss_clean($_POST);

        $eventID = isset($_POST["eventID"]) && $_POST["eventID"] != "" ? (integer)$_POST["eventID"] : null;

        if($eventID !== null)
        {
            $data["event"] = $this->_model->getMemberEvents(Session::get("memberID"), EventMembers::TRANSLATOR, $eventID);

            if(!empty($data["event"]))
            {
                if($data["event"][0]->state != "started")
                {
                    $response["success"] = true;
                }
            }
        }

        echo json_encode($response);
    }

    public function getNotifications()
    {
        if(Session::get("loggedin"))
        {
            $data["notifs"] = array();

            if(!empty($this->_notifications))
            {
                foreach ($this->_notifications as $notification)
                {
                    $text = __("checker_apply", [
                        $notification->userName,
                        __($notification->step),
                        $notification->bookName,
                        $notification->currentChapter,
                        $notification->tLang,
                        __($notification->bookProject)
                    ]);

                    $note["link"] = "/events/checker/".$notification->eventID."/".$notification->memberID."/apply";
                    $note["anchor"] = "check:".$notification->eventID.":".$notification->memberID;
                    $note["text"] = $text;
                    $data["notifs"][] = $note;
                }
            }
            else
            {
                $data["noNotifs"] = __("no_notifs_msg");
            }

            $data["success"] = true;
            echo json_encode($data);
        }
    }


    public function allNotifications()
    {
        if (!Session::get('loggedin'))
        {
            Session::set('redirect', 'admin');
            Url::redirect('members/login');
        }

        $data["notifications"] = $this->_notifications;

        $profile = Session::get("profile");
        $langs = array();
        foreach ($profile["languages"] as $lang => $item) {
            $langs[] = $lang;
        }

        $data["menu"] = 1;
        $data["all_notifications"] = $this->_model->getAllNotifications($langs);
        $data["all_notifications"] += $this->_notifications;

        return View::make("Events/Notifications")
            ->shares("title", __("all_notifications_title"))
            ->shares("data", $data);
    }


    public function assignChapter()
    {
        $response = array("success" => false);

        if (!Session::get('loggedin'))
        {
            $response["error"] = __("not_loggedin_error");
            echo json_encode($response);
            return;
        }

        if (!Session::get('verified'))
        {
            $response["error"] = __("account_not_verirfied_error");
            echo json_encode($response);
            return;
        }

        if (!Session::get('isAdmin'))
        {
            $response["error"] = __("not_enough_rights_error");
            echo json_encode($response);
            return;
        }

        $_POST = Gump::xss_clean($_POST);

        $eventID = isset($_POST["eventID"]) && $_POST["eventID"] != "" ? (integer)$_POST["eventID"] : null;
        $chapter = isset($_POST["chapter"]) && $_POST["chapter"] != "" ? (integer)$_POST["chapter"] : null;
        $memberID = isset($_POST["memberID"]) && $_POST["memberID"] != "" ? (integer)$_POST["memberID"] : null;
        $action = isset($_POST["action"]) && preg_match("/^(add|delete)$/", $_POST["action"]) ? $_POST["action"] : null;

        if($eventID !== null && $chapter != null && $memberID != null && $action != null)
        {
            $data["event"] = $this->_model->getMemberEvents($memberID, EventMembers::TRANSLATOR, $eventID);
            $isEventAdmin = $this->_model->getMemberEventsForAdmin(Session::get("memberID"), $eventID);

            if(!empty($data["event"]) && !empty($isEventAdmin))
            {
                $chapters = json_decode($data["event"][0]->chapters, true);
                if(isset($chapters[$chapter]) && empty($chapters[$chapter]))
                {
                    if($action == "add")
                    {
                        $chapters[$chapter] = [
                            "trID" => $data["event"][0]->trID,
                            "memberID" => $data["event"][0]->myMemberID,
                            "chunks" => array()
                        ];

                        $updateEvent = $this->_model->updateEvent(["chapters" => json_encode($chapters)], ["eventID" => $eventID]);

                        // Change translator's step to pray when at least one chapter is assigned to him
                        if(sizeof(array_filter($chapters, function ($v) use($memberID) {
                                return isset($v["memberID"]) && $v["memberID"] == $memberID;
                            })) == 1) {
                            $this->_model->updateTranslator(["step" => EventSteps::PRAY], ["trID" => $data["event"][0]->trID]);
                        }

                        if($updateEvent)
                        {
                            $response["success"] = true;
                        }
                        else
                        {
                            $response["error"] = __("error_ocured", [$updateEvent]);
                        }
                    }
                    else
                    {
                        $response["error"] = __("error_ocured", ["wrong parameters"]);
                    }
                }
                else
                {
                    if($action == "delete")
                    {
                        if($data["event"][0]->state == "started")
                        {
                            if($chapters[$chapter]["memberID"] == $memberID)
                            {
                                $chapters[$chapter] = array();
                                $updateEvent = $this->_model->updateEvent(array("chapters" => json_encode($chapters)), array("eventID" => $eventID));

                                // Change translator's step to none when no chapter is assigned to him
                                if(empty(array_filter($chapters, function ($v) use($memberID) {
                                    return isset($v["memberID"]) && $v["memberID"] == $memberID;
                                })))
                                {
                                    $this->_model->updateTranslator(["step" => EventSteps::NONE], ["trID" => $data["event"][0]->trID]);
                                }

                                if($updateEvent)
                                {
                                    $response["success"] = true;
                                }
                                else
                                {
                                    $response["error"] = __("error_ocured", array($updateEvent));
                                }
                            }
                            else
                            {
                                $response["error"] = __("error_ocured", array("wrong parameters"));
                            }
                        }
                        else
                        {
                            $response["error"] = __("event_translating_error");
                        }
                    }
                    else
                    {
                        $response["error"] = __("chapter_aready_assigned_error");
                    }
                }
            }
            else
            {
                $response["error"] = __("error_ocured", array("wrong parameters"));
            }
        }
        else
        {
            $response["error"] = __("error_ocured", array("wrong parameters"));
        }

        echo json_encode($response);
    }

    //-------------------- Private functions --------------------------//

    private function checkStateFinished($event, $memberType)
    {
        switch($memberType)
        {
            case EventMembers::TRANSLATOR:
                if(($event->translators + 1) >= $event->translatorsNum)
                {
                    return true;
                }
                break;

            case EventMembers::L2_CHECKER:
                if(($event->checkers_l2 + 1) >= $event->l2CheckersNum)
                {
                    return true;
                }
                break;

            case EventMembers::L3_CHECKER:
                if(($event->checkers_l3 + 1) >= $event->l3CheckersNum)
                {
                    return true;
                }
                break;
        }

        return false;
    }

    /**
     * Get source text for chapter or chunk from USFM format
     * @param $data
     * @param bool $getChunk
     * @param bool $isCoTranslator
     * @return array
     */
    private function getSourceTextUSFM($data, $getChunk = false, $isCoTranslator = false)
    {
        $currentChapter = !$isCoTranslator ? $data["event"][0]->currentChapter : $data["event"][0]->cotrCurrentChapter;
        $currentChunk = !$isCoTranslator ? $data["event"][0]->currentChunk : $data["event"][0]->cotrCurrentChunk;
        $eventTrID = !$isCoTranslator ? $data["event"][0]->trID : $data["event"][0]->cotrID;

        $cache_keyword = $data["event"][0]->bookCode."_".$data["event"][0]->sourceLangID."_".$data["event"][0]->bookProject."_usfm";

        if(Cache::has($cache_keyword))
        {
            $source = Cache::get($cache_keyword);
            $usfm = UsfmParser::parse($source);
        }
        else
        {
            $source = $this->_model->getSourceBookFromApiUSFM($data["event"][0]->bookProject, $data["event"][0]->abbrID, $data["event"][0]->bookCode, $data["event"][0]->sourceLangID);
            $usfm = UsfmParser::parse($source);

            if(!empty($usfm))
                Cache::add($cache_keyword, $source, 60*24*7);
        }

        if(!empty($usfm))
        {
            $currentChapterText = "";
            $currentChunkText = "";
            $totalVerses = 0;

            $chapters = json_decode($data["event"][0]->chapters, true);

            if($currentChapter == 0)
            {
                foreach ($chapters as $chapter => $chapData) {
                    if($chapData["trID"] == $eventTrID)
                    {
                        $currentChapter = $chapter;
                        break;
                    }
                }
            }

            if($currentChapter <= 0) return false;

            //$data["text"][] = ""; // For compatibility with usx parser
            foreach ($usfm["chapters"][$currentChapter] as $section) {
                foreach ($section as $v => $text) {
                    //$data["text"][] = $v;
                    $data["text"][$v] = $text;
                }
            }

            $arrKeys = array_keys($data["text"]);
            $lastVerse = explode("-", end($arrKeys));
            $lastVerse = $lastVerse[sizeof($lastVerse)-1];
            $data["totalVerses"] = !empty($data["text"]) ?  $lastVerse : 0;
            $data["currentChapter"] = $currentChapter;
            $data["currentChunk"] = $currentChunk;
            $data["chapters"] = $chapters;

            if($getChunk)
            {
                $chapData = $chapters[$currentChapter]["chunks"];
                $chunk = $chapData[$currentChunk];
                $fv = $chunk[0];
                $lv = $chunk[sizeof($chunk)-1];

                foreach ($data["text"] as $verse => $text) {
                    $v = explode("-", $verse);
                    $map = array_map(function($value) use ($fv, $lv) {
                        return $value >= $fv && $value <= $lv;
                    }, $v);
                    $map = array_unique($map);

                    if($map[0])
                    {
                        $currentChunkText[$verse] = $text;
                    }
                }


                /*for($i=2; $i <= sizeof($data["text"]); $i+=2)
                {
                    $verse = explode("-", $data["text"][$i-1]);
                    $map = array_map(function($value) use ($fv, $lv) {
                        return $value >= $fv && $value <= $lv;
                    }, $verse);
                    $map = array_unique($map);

                    if($map[0])
                    {
                        $tmp["verse"] = $data["text"][$i-1];
                        $tmp["content"] = $data["text"][$i];
                        $currentChunkText[] = $tmp;
                    }
                }*/

                $data["chunks"] = $chapData;
                $data["chunk"] = $chunk;
                $data["totalVerses"] = sizeof($chunk);

                $data["text"] = $currentChunkText;
            }

            return $data;
        }
        else
        {
            return array("error" => __("no_source_error"));
        }
    }

    private function testChunks($chunks, $totalVerses)
    {
        if(!is_array($chunks) || empty($chunks)) return false;

        $lastVerse = 0;

        foreach ($chunks as $chunk) {
            if(!is_array($chunk) || empty($chunk)) return false;

            // Test if first verse is 1
            if($lastVerse == 0 && $chunk[0] != 1) return false;

            foreach ($chunk as $verse) {
                if((integer)$verse > ($lastVerse+1)) return false;
                $lastVerse++;
            }
        }

        // Test if all verses added to chunks
        if($lastVerse != $totalVerses) return false;

        return true;
    }

    private function getComments($eventID, $chapter = null)
    {
        $translationModel = new TranslationsModel();
        $comments = $translationModel->getCommentsByEvent($eventID, $chapter);
        $commentsFinal = array();

        foreach ($comments as $comment) {
            $commentsFinal[$comment->chapter][$comment->chunk][] = $comment;
        }

        unset($comments);

        return $commentsFinal;
    }

    private function getKeyWords($book, $lang = "en", $chapter, $versesCount)
    {
        $result = array();

        // Get catalog
        $cat_cache_keyword = "catalog_".$book."_".$lang;

        if(Cache::has($cat_cache_keyword))
        {
            $cat_source = Cache::get($cat_cache_keyword);
            $cat_json = json_decode($cat_source, true);
        }
        else
        {
            $cat_source = $this->_model->getTWcatalog($book, $lang);
            $cat_json = json_decode($cat_source, true);

            if(!empty($cat_json))
                Cache::add($cat_cache_keyword, $cat_source, 60*24*7);
        }

        // Get keywords
        $tw_cache_keyword = "tw_".$lang;

        if(Cache::has($tw_cache_keyword))
        {
            $tw_source = Cache::get($tw_cache_keyword);
            $tw_json = json_decode($tw_source, true);
        }
        else
        {
            $tw_source = $this->_model->getTWords($lang);
            $tw_json = json_decode($tw_source, true);

            if(!empty($tw_json))
                Cache::add($tw_cache_keyword, $tw_source, 60*24*7);
        }

        if(!empty($cat_json) && !empty($tw_json))
        {
            $i=0;
            foreach ($cat_json["chapters"][$chapter - 1]["frames"] as $key => $frame) {
                $result[$key]["id"] = (integer)$frame["id"];
                $result[$key]["terms"] = array();

                if(isset($result[$key-1]))
                    $result[$key-1]["id"] .= "-".((integer)$frame["id"] - 1);

                foreach ($frame["items"] as $item) {
                    $term_index = array_search($item["id"], array_column($tw_json, "id"));
                    if($term_index)
                    {
                        $result[$key]["terms"][] = $tw_json[$term_index]["term"];
                    }
                }
            }

            $result[sizeof($result)-1]["id"] .= "-".$versesCount;
        }

        return $result;
    }
}