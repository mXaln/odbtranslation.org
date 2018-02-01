<?php
/**
 * Created by mXaln
 */

namespace App\Controllers;

use View;
use Helpers\Url;
use Helpers\Data;
use Helpers\Gump;
use Helpers\Session;
use App\Core\Controller;
use Support\Facades\Cache;
use App\Models\EventsModel;
use App\Models\MembersModel;
use App\Modules\Alma\Models\Word;
use App\Models\TranslationsModel;
use Helpers\Constants\EventSteps;
use Helpers\Constants\EventCheckSteps;
use Helpers\Constants\StepsStates;
use Helpers\Constants\EventStates;
use Helpers\Constants\EventMembers;

class EventsController extends Controller
{
    private $_model;
    private $_translationModel;
    private $_membersModel;
    private $_notifications;

    public function __construct()
    {
        parent::__construct();
        $this->_model = new EventsModel();
        $this->_translationModel = new TranslationsModel();
        $this->_membersModel = new MembersModel();
        
        if (!Session::get('loggedin'))
        {
            if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                $response["errorType"] = "logout";
                $response["error"] = __("not_loggedin_error");
                echo json_encode($response);
                exit;
            }
            else
            {
                Url::redirect('members/login');
            }
        }

        if(!Session::get("verified"))
        {
            Url::redirect("members/error/verification");
        }

        if(Session::get("isDemo"))
        {
            if(!preg_match("/^\\/events\\/demo/", $_SERVER["REQUEST_URI"]))
                Url::redirect('events/demo');
        }
        elseif(empty(Session::get("profile")))
        {
            Url::redirect("members/profile");
        }

        $this->_notifications = $this->_model->getNotifications();
        $this->_notifications = array_merge(
            $this->_notifications,
            $this->_model->getNotificationsNotes(),
            $this->_model->getNotificationsL2());
    }

    /**
     * Show member's dashboard view
     * @return mixed
     */
    public function index()
    {
        $data["menu"] = 1;

        if (Session::get('loggedin') !== true)
        {
            Url::redirect("members/login");
        }

        if(Session::get("isDemo"))
        {
            Url::redirect('events/demo');
        }

        if(empty(Session::get("profile")))
        {
            Url::redirect("members/profile");
        }
        
        if(Session::get("isAdmin"))
        {
            $myFacilitatorEvents = $this->_model->getMemberEventsForAdmin(Session::get("memberID"));
            $data["myFacilitatorEventsInProgress"] = [];
            $data["myFacilitatorEventsFinished"] = [];

            foreach ($myFacilitatorEvents as $myFacilitatorEvent) {
                // Level 1
                if(EventStates::enum($myFacilitatorEvent->state) <= EventStates::enum(EventStates::TRANSLATED))
                {
                    $adms = (array)json_decode($myFacilitatorEvent->admins, true);
                    if(!in_array(Session::get("memberID"), $adms)) continue;
                } // Level 2
                elseif(EventStates::enum($myFacilitatorEvent->state) <= EventStates::enum(EventStates::L2_CHECKED))
                {
                    $adms = (array)json_decode($myFacilitatorEvent->admins_l2, true);
                    if(!in_array(Session::get("memberID"), $adms)) continue;
                }

                if($myFacilitatorEvent->state == EventStates::TRANSLATED
                    || $myFacilitatorEvent->state == EventStates::L2_CHECKED)
                    $data["myFacilitatorEventsFinished"][] = $myFacilitatorEvent;
                else
                    $data["myFacilitatorEventsInProgress"][] = $myFacilitatorEvent;
            }
        }

        $myLangs = array_keys(Session::get("profile")["languages"]);
        
        $data["myTranslatorEventsdd"] = $this->_model->getMemberEvents(Session::get("memberID"), EventMembers::TRANSLATOR, null, false, false);
        $data["newEvents"] = $this->_model->getNewEvents($myLangs, Session::get("memberID"));
        $data["myCheckerL1Events"] = $this->_model->getMemberEventsForChecker(Session::get("memberID"));
        $notesCheckers = $this->_model->getMemberEventsForCheckerNotes(Session::get("memberID"));
        $data["myCheckerL1Events"] = array_merge($data["myCheckerL1Events"], $notesCheckers);
        $data["myCheckerL2Events"] = $this->_model->getMemberEventsForCheckerL2(Session::get("memberID"));

        $data["myCheckerL3Events"] = [];
        //$data["myCheckerL3Events"] = $this->_model->getMemberEvents(Session::get("memberID"), EventMembers::L3_CHECKER);

        // Extract facilitators from events
        $admins = [];
        foreach ($data["myTranslatorEvents"] as $key => $event) {
            if(in_array($event->bookProject, ["tn"]) && $event->stage == "checking")
            {
                $event->bookName = $event->name;
                $data["myCheckerL1Events"][] = $event;
                unset($data["myTranslatorEvents"][$key]);
                continue;
            }
            $admins = array_merge($admins, (array)json_decode($event->admins, true));
        }
        foreach ($data["newEvents"] as $event) {
            $admins = array_merge($admins, (array)json_decode($event->admins, true));
        }
        foreach ($data["myCheckerL1Events"] as $event) {
            $admins = array_merge($admins, (array)json_decode($event->admins, true));
        }
        foreach ($data["myCheckerL2Events"] as $event) {
            $admins = array_merge($admins, (array)json_decode($event->admins_l2, true));
        }
        foreach ($data["myCheckerL3Events"] as $event) {
            $admins = array_merge($admins, (array)json_decode($event->admins_l2, true));
        }
        
        $admins = array_unique($admins);
        $admins = (array)$this->_membersModel->getMembers(array_filter(array_values($admins)), true);

        $adminData = [];
        foreach ($admins as $member) {
            $adminData[$member->memberID]["userName"] = $member->userName;
            $adminData[$member->memberID]["avatar"] = $member->avatar;
            $adminData[$member->memberID]["name"] = $member->firstName . " " . mb_substr($member->lastName, 0, 1).".";
            $adminData[$member->memberID]["email"] = $member->email;
        }

        $data["admins"] = $adminData;
        $data["notifications"] = $this->_notifications;

        return View::make('Events/Index')
            ->shares("title", __("welcome_title"))
            ->shares("data", $data);
    }

    public function translator($eventID)
    {
        $data["menu"] = 1;
        $data["notifications"] = $this->_notifications;
        $data["event"] = $this->_model->getMemberEvents(Session::get("memberID"), EventMembers::TRANSLATOR, $eventID);

        $title = "";

        if(!empty($data["event"]))
        {
            $title = $data["event"][0]->name ." - ". $data["event"][0]->tLang ." - ". __($data["event"][0]->bookProject);

            if($data["event"][0]->state == EventStates::TRANSLATING || $data["event"][0]->state == EventStates::TRANSLATED)
            {
                if($data["event"][0]->step == EventSteps::NONE)
                    Url::redirect("events/information/".$eventID);

                $turnSecret = $this->_membersModel->getTurnSecret();
                $turnUsername = (time() + 3600) . ":vmast";
                $turnPassword = "";

                if(!empty($turnSecret))
                {
                    if(($turnSecret[0]->expire - time()) < 0)
                    {
                        $pass = $this->_membersModel->generateStrongPassword(22);
                        if($this->_membersModel->updateTurnSecret(["value" => $pass, "expire" => time() + (30*24*3600)])) // Update turn secret each month
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
                        $sourceText = $this->getSourceText($data);

                        if($sourceText !== false)
                        {
                            if (!array_key_exists("error", $sourceText))
                            {
                                $data = $sourceText;
                            }
                            else
                            {
                                $error[] = $sourceText["error"];
                                $data["error"] = $sourceText["error"];
                            }
                        }
                        else
                        {
                            $this->_model->updateTranslator(["step" => EventSteps::FINISHED, "translateDone" => true], ["trID" => $data["event"][0]->trID]);
                            Url::redirect('events/translator/' . $data["event"][0]->eventID);
                        }

                        if (isset($_POST) && !empty($_POST))
                        {
                            if (isset($_POST["confirm_step"]))
                            {
                                setcookie("temp_tutorial", false, time() - 24*3600, "/");
                                $postdata = [
                                    "step" => EventSteps::CONSUME,
                                    "currentChapter" => $sourceText["currentChapter"],
                                    "currentChunk" => $sourceText["currentChunk"]
                                ];
                                $this->_model->updateTranslator($postdata, ["trID" => $data["event"][0]->trID]);
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
                        $sourceText = $this->getSourceText($data);

                        if($sourceText !== false)
                        {
                            if (!array_key_exists("error", $sourceText))
                            {
                                $data = $sourceText;
                            }
                            else
                            {
                                $error[] = $sourceText["error"];
                                $data["error"] = $sourceText["error"];
                            }
                        }
                        else
                        {
                            $this->_model->updateTranslator(["step" => EventSteps::FINISHED, "translateDone" => true], ["trID" => $data["event"][0]->trID]);
                            Url::redirect('events/translator/' . $data["event"][0]->eventID);
                        }

                        if (isset($_POST) && !empty($_POST))
                        {
                            if (isset($_POST["confirm_step"]))
                            {
                                $postdata = [
                                    "step" => EventSteps::VERBALIZE,
                                    "hideChkNotif" => false
                                ];

                                setcookie("temp_tutorial", false, time() - 24*3600, "/");
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
                        $sourceText = $this->getSourceText($data);
                        if($sourceText !== false)
                        {
                            if (!array_key_exists("error", $sourceText))
                            {
                                $data = $sourceText;
                            }
                            else
                            {
                                $error[] = $sourceText["error"];
                                $data["error"] = $sourceText["error"];
                            }
                        }
                        else
                        {
                            $this->_model->updateTranslator(["step" => EventSteps::FINISHED, "translateDone" => true], ["trID" => $data["event"][0]->trID]);
                            Url::redirect('events/translator/' . $data["event"][0]->eventID);
                        }

                        $data["event"][0]->checkerName = null;
                        $verbCheck = (array)json_decode($data["event"][0]->verbCheck, true);
                        if(array_key_exists($data["event"][0]->currentChapter, $verbCheck))
                        {
                            if(!is_numeric($verbCheck[$data["event"][0]->currentChapter]))
                            {
                                $data["event"][0]->checkerName = $verbCheck[$data["event"][0]->currentChapter];
                            }
                            else
                            {
                                $data["event"][0]->checkerName = $data["event"][0]->checkerFName . " " . mb_substr($data["event"][0]->checkerLName, 0, 1).".";
                            }
                        }

                        if (isset($_POST) && !empty($_POST))
                        {
                            if (isset($_POST["confirm_step"]))
                            {
                                if($data["event"][0]->checkDone)
                                {
                                    setcookie("temp_tutorial", false, time() - 24*3600, "/");
                                    $postdata = [
                                        "step" => EventSteps::CHUNKING,
                                        "checkerID" => 0,
                                        "checkDone" => false,
                                        "hideChkNotif" => true
                                    ];
                                    $this->_model->updateTranslator($postdata, ["trID" => $data["event"][0]->trID]);
                                    Url::redirect('events/translator/' . $data["event"][0]->eventID);
                                }
                                else
                                {
                                    $error[] = __("verb_checker_not_ready_error");
                                }
                            }
                        }

                        return View::make('Events/Translator')
                            ->nest('page', 'Events/Verbalize')
                            ->shares("title", $title)
                            ->shares("data", $data)
                            ->shares("error", @$error);
                        break;

                    case EventSteps::CHUNKING:
                        $sourceText = $this->getSourceText($data);

                        if($sourceText !== false)
                        {
                            if (!array_key_exists("error", $sourceText))
                            {
                                $data = $sourceText;
                            }
                            else
                            {
                                $error[] = $sourceText["error"];
                                $data["error"] = $sourceText["error"];
                            }
                        }
                        else
                        {
                            $this->_model->updateTranslator(["step" => EventSteps::FINISHED, "translateDone" => true], ["trID" => $data["event"][0]->trID]);
                            Url::redirect('events/translator/' . $data["event"][0]->eventID);
                        }

                        if (isset($_POST) && !empty($_POST))
                        {
                            if (isset($_POST["confirm_step"]))
                            {
                                setcookie("temp_tutorial", false, time() - 24*3600, "/");

                                $_POST = Gump::xss_clean($_POST);

								$chunks = isset($_POST["chunks_array"]) ? $_POST["chunks_array"] : "";
                                $chunks = (array)json_decode($chunks);
                                if($this->testChunks($chunks, $sourceText["totalVerses"]))
                                {
                                    if($this->_model->updateChapter(["chunks" => json_encode($chunks)], ["eventID" => $data["event"][0]->eventID, "chapter" => $data["event"][0]->currentChapter]))
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
                        $sourceText = $this->getSourceText($data, true);

                        if($sourceText !== false)
                        {
                            if (!array_key_exists("error", $sourceText))
                            {
                                $data = $sourceText;
                            }
                            else
                            {
                                $error[] = $sourceText["error"];
                                $data["error"] = $sourceText["error"];
                            }
                        }
                        else
                        {
                            $this->_model->updateTranslator(["step" => EventSteps::FINISHED, "translateDone" => true], ["trID" => $data["event"][0]->trID]);
                            Url::redirect('events/translator/' . $data["event"][0]->eventID);
                        }

                        if (isset($_POST) && !empty($_POST))
                        {
                            if (isset($_POST["confirm_step"]))
                            {
                                setcookie("temp_tutorial", false, time() - 24*3600, "/");
                                $this->_model->updateTranslator(["step" => EventSteps::BLIND_DRAFT], ["trID" => $data["event"][0]->trID]);
                                Url::redirect('events/translator/' . $data["event"][0]->eventID);
                            }
                        }

                        return View::make('Events/Translator')
                            ->nest('page', 'Events/ReadChunk')
                            ->shares("title", $title)
                            ->shares("data", $data)
                            ->shares("error", @$error);
                        break;

                    case EventSteps::BLIND_DRAFT:
                        $sourceText = $this->getSourceText($data, true);

                        if($sourceText !== false)
                        {
                            if (!array_key_exists("error", $sourceText))
                            {
                                $data = $sourceText;

                                $translationData = $this->_translationModel
                                    ->getEventTranslation($data["event"][0]->trID, $data["event"][0]->currentChapter, $data["event"][0]->currentChunk);

                                if(!empty($translationData))
                                {
                                    $verses = json_decode($translationData[0]->translatedVerses, true);
                                    $data["blind"] = $verses[EventMembers::TRANSLATOR]["blind"];
                                }
                            }
                            else
                            {
                                $error[] = $sourceText["error"];
                                $data["error"] = $sourceText["error"];
                            }
                        }
                        else
                        {
                            $this->_model->updateTranslator(["step" => EventSteps::FINISHED, "translateDone" => true], ["trID" => $data["event"][0]->trID]);
                            Url::redirect('events/translator/' . $data["event"][0]->eventID);
                        }

                        if (isset($_POST) && !empty($_POST))
                        {
                            $_POST = Gump::xss_clean($_POST);
							$draft = isset($_POST["draft"]) ? $_POST["draft"] : "";
							$confirm_step = isset($_POST["confirm_step"]) ? $_POST["confirm_step"] : "";

                            if (isset($_POST["confirm_step"]))
                            {
                                if(trim($draft) != "")
                                {
                                    $draft = preg_replace("/[\\r\\n]/", " ", $draft);

                                    $translationVerses = [
                                        EventMembers::TRANSLATOR => [
                                            "blind" => trim($draft),
                                            "verses" => array()
                                        ],
                                        EventMembers::L2_CHECKER => [
                                            "verses" => array()
                                        ],
                                        EventMembers::L3_CHECKER => [
                                            "verses" => array()
                                        ],
                                    ];

                                    $encoded = json_encode($translationVerses);
                                    $json_error = json_last_error();
                                    
                                    if($json_error == JSON_ERROR_NONE)
                                    {
                                        if(!empty($translationData))
                                        {
                                            $tID = $translationData[0]->tID;
                                            $trData = [
                                                "translatedVerses"  => $encoded,
                                            ];
                                            $this->_translationModel->updateTranslation($trData, ["tID" => $tID]);
                                        }
                                        else
                                        {
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
                                                "translatedVerses"  => $encoded,
                                                "dateCreate"        => date('Y-m-d H:i:s')
                                            ];
    
                                            $tID = $this->_translationModel->createTranslation($trData);
                                        }
                                    }
                                    else 
                                    {
                                        $tID = "Json error: " . $json_error;
                                    }

                                    if(is_numeric($tID))
                                    {
                                        $postdata["step"] = EventSteps::SELF_CHECK;

                                        // If chapter is finished go to SELF_EDIT, otherwise go to the next chunk
                                        if(array_key_exists($data["event"][0]->currentChunk + 1, $sourceText["chunks"]))
                                        {
                                            // Current chunk is finished, go to next chunk
                                            $postdata["currentChunk"] = $data["event"][0]->currentChunk + 1;
                                            $postdata["step"] = EventSteps::READ_CHUNK;
                                        }

                                        setcookie("temp_tutorial", false, time() - 24*3600, "/");
                                        $upd = $this->_model->updateTranslator($postdata, ["trID" => $data["event"][0]->trID]);
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
                        $sourceText = $this->getSourceText($data);

                        if($sourceText !== false)
                        {
                            if (!array_key_exists("error", $sourceText))
                            {
                                $data = $sourceText;
                                $data["comments"] = $this->getComments($data["event"][0]->eventID, $data["event"][0]->currentChapter);

                                $translationData = $this->_translationModel->getEventTranslation($data["event"][0]->trID, $data["event"][0]->currentChapter);
                                $translation = array();

                                foreach ($translationData as $tv)
                                {
                                    $arr = json_decode($tv->translatedVerses, true);
                                    $arr["tID"] = $tv->tID;
                                    $translation[] = $arr;
                                }
                                $data["translation"] = $translation;

                                if($data["event"][0]->sourceBible == "rsb")
                                {
                                    $words = Word::with('translations')
                                        ->orderBy('title')
                                        ->get();

                                    $data["words"] = json_encode($words->toArray());
                                }
                            }
                            else
                            {
                                $error[] = $sourceText["error"];
                                $data["error"] = $sourceText["error"];
                            }
                        }
                        else
                        {
                            $this->_model->updateTranslator(["step" => EventSteps::FINISHED, "translateDone" => true], ["trID" => $data["event"][0]->trID]);
                            Url::redirect('events/translator/' . $data["event"][0]->eventID);
                        }

                        if (isset($_POST) && !empty($_POST))
                        {
                            $_POST = Gump::xss_clean($_POST);

                            if(isset($_POST["save"]))
                            {
								$chunks = isset($_POST["chunks"]) ? (array)$_POST["chunks"] : array();
                                $chunks = array_map("trim", $chunks);
                                $chunks = array_filter($chunks, function($v) {
                                    return !empty($v);
                                });

                                if(sizeof($chunks) < sizeof($data["chapters"][$data["currentChapter"]]["chunks"]))
                                    $error[] = __("empty_verses_error");

                                if(!isset($error))
                                {
                                    if(!empty($translation))
                                    {
                                        foreach ($translation as $key => $chunk)
                                        {
                                            $shouldUpdate = false;
                                            if($chunk[EventMembers::TRANSLATOR]["blind"] != $chunks[$key])
                                                $shouldUpdate = true;

                                            $translation[$key][EventMembers::TRANSLATOR]["blind"] = $chunks[$key];

                                            if($shouldUpdate)
                                            {
                                                $tID = $translation[$key]["tID"];
                                                unset($translation[$key]["tID"]);
                                                
                                                $encoded = json_encode($translation[$key]);
                                                $json_error = json_last_error();
                                                
                                                if($json_error == JSON_ERROR_NONE)
                                                {
                                                    $trData = array(
                                                        "translatedVerses"  => $encoded
                                                    );
                                                    $this->_translationModel->updateTranslation(
                                                        $trData, 
                                                        array(
                                                            "trID" => $data["event"][0]->trID, 
                                                            "tID" => $tID));
                                                }
                                                else 
                                                {
                                                    $error[] = __("error_ocured", array($tID));
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                            else
                            {
                                if (isset($_POST["confirm_step"]))
                                {
                                    setcookie("temp_tutorial", false, time() - 24*3600, "/");
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
                        $sourceText = $this->getSourceText($data);

                        if($sourceText !== false)
                        {
                            if (!array_key_exists("error", $sourceText))
                            {
                                $data = $sourceText;
                                $data["comments"] = $this->getComments($data["event"][0]->eventID, $data["event"][0]->currentChapter);

                                $translationData = $this->_translationModel->getEventTranslation($data["event"][0]->trID, $data["event"][0]->currentChapter);
                                $translation = array();

                                foreach ($translationData as $tv)
                                {
                                    $arr = json_decode($tv->translatedVerses, true);
                                    $arr["tID"] = $tv->tID;
                                    $translation[] = $arr;
                                }
                                $data["translation"] = $translation;
                            }
                            else
                            {
                                $error[] = $sourceText["error"];
                                $data["error"] = $sourceText["error"];
                            }
                        }
                        else
                        {
                            $this->_model->updateTranslator(["step" => EventSteps::FINISHED, "translateDone" => true], ["trID" => $data["event"][0]->trID]);
                            Url::redirect('events/translator/' . $data["event"][0]->eventID);
                        }

                        if (isset($_POST) && !empty($_POST)) {
                            $_POST = Gump::xss_clean($_POST);

                            if(isset($_POST["save"]))
                            {
                                if(!$data["event"][0]->checkDone)
                                {
									$chunks = isset($_POST["chunks"]) ? (array)$_POST["chunks"] : array();
                                    $chunks = array_map("trim", $chunks);
                                    $chunks = array_filter($chunks, function($v) {
                                        return !empty($v);
                                    });

                                    if(sizeof($chunks) < sizeof($data["chapters"][$data["currentChapter"]]["chunks"]))
                                        $error[] = __("empty_verses_error");

                                    if(!isset($error))
                                    {
                                        if(!empty($translation))
                                        {
                                            foreach ($translation as $key => $chunk)
                                            {
                                                $shouldUpdate = false;
                                                if($chunk[EventMembers::TRANSLATOR]["blind"] != $chunks[$key])
                                                    $shouldUpdate = true;

                                                $translation[$key][EventMembers::TRANSLATOR]["blind"] = $chunks[$key];

                                                if($shouldUpdate)
                                                {
                                                    $tID = $translation[$key]["tID"];
                                                    unset($translation[$key]["tID"]);
                                                    
                                                    $encoded = json_encode($translation[$key]);
                                                    $json_error = json_last_error();
                                                    
                                                    if($json_error == JSON_ERROR_NONE)
                                                    {
                                                        $trData = array(
                                                            "translatedVerses"  => $encoded
                                                        );
                                                        $this->_translationModel->updateTranslation(
                                                            $trData, 
                                                            array(
                                                                "trID" => $data["event"][0]->trID, 
                                                                "tID" => $tID));
                                                    }
                                                    else 
                                                    {
                                                        $error[] = __("error_ocured", array($tID));
                                                    }
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
                                        setcookie("temp_tutorial", false, time() - 24*3600, "/");
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
                        $sourceText = $this->getSourceText($data);

                        if($sourceText !== false)
                        {
                            if (!array_key_exists("error", $sourceText))
                            {
                                $data = $sourceText;
                                $data["comments"] = $this->getComments($data["event"][0]->eventID, $data["event"][0]->currentChapter);

                                $translationData = $this->_translationModel->getEventTranslation($data["event"][0]->trID, $data["event"][0]->currentChapter);
                                $translation = array();

                                foreach ($translationData as $tv)
                                {
                                    $arr = json_decode($tv->translatedVerses, true);
                                    $arr["tID"] = $tv->tID;
                                    $translation[] = $arr;
                                }
                                $data["translation"] = $translation;
                            }
                            else
                            {
                                $error[] = $sourceText["error"];
                                $data["error"] = $sourceText["error"];
                            }
                        }
                        else
                        {
                            $this->_model->updateTranslator(["step" => EventSteps::FINISHED, "translateDone" => true], ["trID" => $data["event"][0]->trID]);
                            Url::redirect('events/translator/' . $data["event"][0]->eventID);
                        }

                        if (isset($_POST) && !empty($_POST))
                        {
                            $_POST = Gump::xss_clean($_POST);

                            if(isset($_POST["save"]))
                            {
                                if(!$data["event"][0]->checkDone)
                                {
									$chunks = isset($_POST["chunks"]) ? (array)$_POST["chunks"] : array();
                                    $chunks = array_map("trim", $chunks);
                                    $chunks = array_filter($chunks, function($v) {
                                        return !empty($v);
                                    });

                                    if(sizeof($chunks) < sizeof($data["chapters"][$data["currentChapter"]]["chunks"]))
                                        $error[] = __("empty_verses_error");

                                    if(!isset($error))
                                    {
                                        if(!empty($translation))
                                        {
                                            foreach ($translation as $key => $chunk)
                                            {
                                                $shouldUpdate = false;
                                                if($chunk[EventMembers::TRANSLATOR]["blind"] != $chunks[$key])
                                                    $shouldUpdate = true;

                                                $translation[$key][EventMembers::TRANSLATOR]["blind"] = $chunks[$key];

                                                if($shouldUpdate)
                                                {
                                                    $tID = $translation[$key]["tID"];
                                                    unset($translation[$key]["tID"]);
                                                    
                                                    $encoded = json_encode($translation[$key]);
                                                    $json_error = json_last_error();
                                                    
                                                    if($json_error == JSON_ERROR_NONE)
                                                    {
                                                        $trData = array(
                                                            "translatedVerses"  => $encoded
                                                        );
                                                        $this->_translationModel->updateTranslation(
                                                            $trData, 
                                                            array(
                                                                "trID" => $data["event"][0]->trID, 
                                                                "tID" => $tID));
                                                    }
                                                    else 
                                                    {
                                                        $error[] = __("error_ocured", array($tID));
                                                    }
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
                                        setcookie("temp_tutorial", false, time() - 24*3600, "/");
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

                        return View::make('Events/Translator')
                            ->nest('page', 'Events/KeywordCheck')
                            ->shares("title", $title)
                            ->shares("data", $data)
                            ->shares("error", @$error);
                        break;

                    case EventSteps::CONTENT_REVIEW:
                        $sourceText = $this->getSourceText($data);

                        if($sourceText !== false)
                        {
                            if (!array_key_exists("error", $sourceText))
                            {
                                $data = $sourceText;
                                $data["comments"] = $this->getComments($data["event"][0]->eventID, $data["event"][0]->currentChapter);

                                $translationData = $this->_translationModel->getEventTranslation($data["event"][0]->trID, $data["event"][0]->currentChapter);
                                $translation = array();

                                foreach ($translationData as $tv) {
                                    $arr = json_decode($tv->translatedVerses, true);
                                    $arr["tID"] = $tv->tID;
                                    $translation[] = $arr;
                                }
                                $data["translation"] = $translation;
                            }
                            else
                            {
                                $error[] = $sourceText["error"];
                                $data["error"] = $sourceText["error"];
                            }
                        }
                        else
                        {
                            $this->_model->updateTranslator(["step" => EventSteps::FINISHED, "translateDone" => true], ["trID" => $data["event"][0]->trID]);
                            Url::redirect('events/translator/' . $data["event"][0]->eventID);
                        }

                        if (isset($_POST) && !empty($_POST))
                        {
                            $_POST = Gump::xss_clean($_POST);

                            if(isset($_POST["save"]))
                            {
                                if(!$data["event"][0]->checkDone)
                                {
									$chunks = isset($_POST["chunks"]) ? (array)$_POST["chunks"] : array();
                                    $chunks = array_map("trim", $chunks);
                                    $chunks = array_filter($chunks, function($v) {
                                        return !empty($v);
                                    });

                                    if(sizeof($chunks) < sizeof($data["chapters"][$data["currentChapter"]]["chunks"]))
                                        $error[] = __("empty_verses_error");

                                    if(!isset($error))
                                    {
                                        if(!empty($translation))
                                        {
                                            foreach ($translation as $key => $chunk)
                                            {
                                                $shouldUpdate = false;
                                                if($chunk[EventMembers::TRANSLATOR]["blind"] != $chunks[$key])
                                                    $shouldUpdate = true;

                                                $translation[$key][EventMembers::TRANSLATOR]["blind"] = $chunks[$key];

                                                if($shouldUpdate)
                                                {
                                                    $tID = $translation[$key]["tID"];
                                                    unset($translation[$key]["tID"]);
                                                    
                                                    $encoded = json_encode($translation[$key]);
                                                    $json_error = json_last_error();
                                                    
                                                    if($json_error == JSON_ERROR_NONE)
                                                    {
                                                        $trData = array(
                                                            "translatedVerses"  => $encoded
                                                        );
                                                        $this->_translationModel->updateTranslation(
                                                            $trData, 
                                                            array(
                                                                "trID" => $data["event"][0]->trID, 
                                                                "tID" => $tID));
                                                    }
                                                    else 
                                                    {
                                                        $error[] = __("error_ocured", array($tID));
                                                    }
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
                                        setcookie("temp_tutorial", false, time() - 24*3600, "/");
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
                        $sourceText = $this->getSourceText($data);

                        if($sourceText !== false)
                        {
                            if (!array_key_exists("error", $sourceText))
                            {
                                $data = $sourceText;
                                $data["comments"] = $this->getComments($data["event"][0]->eventID, $data["event"][0]->currentChapter);

                                $translationData = $this->_translationModel->getEventTranslation($data["event"][0]->trID, $data["event"][0]->currentChapter);
                                $translation = array();

                                foreach ($translationData as $tv) {
                                    $arr = json_decode($tv->translatedVerses, true);
                                    $arr["tID"] = $tv->tID;
                                    $translation[] = $arr;
                                }
                                $data["translation"] = $translation;
                            }
                            else
                            {
                                $error[] = $sourceText["error"];
                                $data["error"] = $sourceText["error"];
                            }
                        }
                        else
                        {
                            $this->_model->updateTranslator(["step" => EventSteps::FINISHED, "translateDone" => true], ["trID" => $data["event"][0]->trID]);
                            Url::redirect('events/translator/' . $data["event"][0]->eventID);
                        }

                        if (isset($_POST) && !empty($_POST))
                        {
                            $_POST = Gump::xss_clean($_POST);

                            if (isset($_POST["confirm_step"]))
                            {
								$chunks = isset($_POST["chunks"]) ? (array)$_POST["chunks"] : array();
                                $chunks = array_map("trim", $chunks);
                                $chunks = array_filter($chunks, function($v) {
                                    return !empty($v);
                                });

                                if(sizeof($chunks) < sizeof($data["chunks"]))
                                    $error[] = __("empty_verses_error");

                                if(!isset($error))
                                {
                                    $versesCombined = [];
                                    foreach ($chunks as $key => $chunk)
                                    {
                                        $verses = preg_split("/\|([0-9]+)\|/", $chunk, -1, PREG_SPLIT_NO_EMPTY);

                                        if(sizeof($data["chunks"][$key]) !=
                                            sizeof($verses))
                                        {
                                            $error[] = __("not_equal_verse_markers");
                                            break;
                                        }

                                        $versesCombined[$key] = array_combine($data["chunks"][$key], $verses);
                                    }

                                    if(!isset($error))
                                    {
                                        foreach ($versesCombined as $key => $chunk)
                                        {
                                            $translation[$key][EventMembers::TRANSLATOR]["verses"] = $chunk;

                                            $tID = $translation[$key]["tID"];
                                            unset($translation[$key]["tID"]);
                                            
                                            $encoded = json_encode($translation[$key]);
                                            $json_error = json_last_error();
                                            
                                            if($json_error == JSON_ERROR_NONE)
                                            {
                                                $trData = array(
                                                    "translatedVerses"  => $encoded,
                                                    "translateDone" => true
                                                );
                                                $this->_translationModel->updateTranslation(
                                                    $trData, 
                                                    array(
                                                        "trID" => $data["event"][0]->trID, 
                                                        "tID" => $tID));
                                            }
                                            else 
                                            {
                                                $error[] = __("error_ocured", array($tID));
                                            }
                                        }

                                        $chapters = [];
                                        for($i=1; $i <= $data["event"][0]->chaptersNum; $i++)
                                        {
                                            $data["chapters"][$i] = [];
                                        }

                                        $chaptersDB = $this->_model->getChapters($data["event"][0]->eventID);

                                        foreach ($chaptersDB as $chapter) {
                                            $tmp["trID"] = $chapter["trID"];
                                            $tmp["memberID"] = $chapter["memberID"];
                                            $tmp["chunks"] = json_decode($chapter["chunks"], true);
                                            $tmp["done"] = $chapter["done"];

                                            $chapters[$chapter["chapter"]] = $tmp;
                                        }

                                        $chapters[$data["event"][0]->currentChapter]["done"] = true;

                                        // Check if whole book is finished
                                        if($this->checkBookFinished($chapters, $data["event"][0]->chaptersNum))
                                            $this->_model->updateEvent([
                                                "state" => EventStates::TRANSLATED,
                                                "dateTo" => date("Y-m-d H:i:s", time())],
                                                ["eventID" => $data["event"][0]->eventID]);

                                        $this->_model->updateChapter(["done" => true], ["eventID" => $data["event"][0]->eventID, "chapter" => $data["event"][0]->currentChapter]);

                                        // Check if the member has another chapter to translate
                                        // then redirect to preparation page
                                        $nextChapter = 0;
                                        $nextChapterDB = $this->_model->getNextChapter($data["event"][0]->eventID, Session::get("memberID"));
                                        if(!empty($nextChapterDB))
                                            $nextChapter = $nextChapterDB[0]->chapter;

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

                                        setcookie("temp_tutorial", false, time() - 24*3600, "/");
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

    public function translatorNotes($eventID)
    {
        $data["menu"] = 1;
        $data["isCheckerPage"] = false;
        $data["notifications"] = $this->_notifications;
        $data["event"] = $this->_model->getMemberEvents(Session::get("memberID"), EventMembers::TRANSLATOR, $eventID);

        $title = "";

        if(!empty($data["event"]))
        {
            $title = $data["event"][0]->name ." - ". $data["event"][0]->tLang ." - ". __($data["event"][0]->bookProject);

            if(($data["event"][0]->state == EventStates::TRANSLATING 
                || $data["event"][0]->state == EventStates::TRANSLATED) 
                && $data["event"][0]->stage == "translation")
            {
                if($data["event"][0]->step == EventSteps::NONE)
                    Url::redirect("events/information-tn/".$eventID);

                $turnSecret = $this->_membersModel->getTurnSecret();
                $turnUsername = (time() + 3600) . ":vmast";
                $turnPassword = "";

                if(!empty($turnSecret))
                {
                    if(($turnSecret[0]->expire - time()) < 0)
                    {
                        $pass = $this->_membersModel->generateStrongPassword(22);
                        if($this->_membersModel->updateTurnSecret(["value" => $pass, "expire" => time() + (30*24*3600)])) // Update turn secret each month
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
                        
                        // Get scripture text
                        $sourceText = $this->getSourceText($data);
                        if($sourceText !== false && !array_key_exists("error", $sourceText))
                            $data = $sourceText;
                        
                        // Get notes
                        $sourceTextNotes = $this->getNotesSourceText($data);
                        
                        if($sourceTextNotes !== false)
                        {
                            if (!array_key_exists("error", $sourceTextNotes))
                            {
                                $data = $sourceTextNotes;
                            }
                            else
                            {
                                $error[] = $sourceTextNotes["error"];
                                $data["error"] = $sourceTextNotes["error"];
                            }
                        }
                        else
                        {
                            $this->_model->updateTranslator(["step" => EventSteps::FINISHED, "translateDone" => true], ["trID" => $data["event"][0]->trID]);
                            Url::redirect('events/translator-tn/' . $data["event"][0]->eventID);
                        }
                        
                        if (isset($_POST) && !empty($_POST))
                        {
                            if (isset($_POST["confirm_step"]))
                            {
                                setcookie("temp_tutorial", false, time() - 24*3600, "/");
                                
                                $postdata = [
                                    "step" => !$data["nosource"] ? EventSteps::CONSUME : EventSteps::READ_CHUNK,
                                    "currentChapter" => $data["currentChapter"],
                                    "currentChunk" => $data["currentChunk"]
                                ];
                                $this->_model->updateTranslator($postdata, ["trID" => $data["event"][0]->trID]);
                                
                                $nChunks = $this->getNotesChunks($sourceTextNotes);
                                
                                $this->_model->updateChapter(
                                    ["chunks" => json_encode($nChunks)], 
                                    [
                                        "eventID" => $data["event"][0]->eventID, 
                                        "chapter" => $data['currentChapter']
                                    ]
                                );
                                
                                Url::redirect('events/translator-tn/' . $data["event"][0]->eventID);
                            }
                        }

                        // Check if translator just started translating of this book
                        $data["event"][0]->justStarted = $data["event"][0]->otherCheck == "";
                        
                        return View::make('Events/Notes/Translator')
                            ->nest('page', 'Events/Notes/Pray')
                            ->shares("title", $title)
                            ->shares("data", $data)
                            ->shares("error", @$error);

                    case EventSteps::CONSUME: // Consume chapter
                        // Get scripture text
                        $sourceText = $this->getSourceText($data);
                        if($sourceText !== false && !array_key_exists("error", $sourceText))
                            $data = $sourceText;
                        
                        // Get notes
                        $sourceTextNotes = $this->getNotesSourceText($data);

                        if($sourceTextNotes !== false)
                        {
                            if (!array_key_exists("error", $sourceTextNotes))
                            {
                                $data = $sourceTextNotes;
                            }
                            else
                            {
                                $error[] = $sourceTextNotes["error"];
                                $data["error"] = $sourceTextNotes["error"];
                            }
                        }
                        else
                        {
                            $this->_model->updateTranslator(["step" => EventSteps::FINISHED, "translateDone" => true], ["trID" => $data["event"][0]->trID]);
                            Url::redirect('events/translator-tn/' . $data["event"][0]->eventID);
                        }
                        
                        if(isset($data["nosource"]) && $data["nosource"] === true)
                        {
                            $this->_model->updateTranslator(["step" => EventSteps::READ_CHUNK], ["trID" => $data["event"][0]->trID]);
                            Url::redirect('events/translator-tn/' . $data["event"][0]->eventID);
                        }

                        if (isset($_POST) && !empty($_POST))
                        {
                            if (isset($_POST["confirm_step"]))
                            {
                                if(empty($data["chunks"]))
                                {
                                    $nChunks = $this->getNotesChunks($sourceTextNotes);
                                    
                                    $this->_model->updateChapter(
                                        ["chunks" => json_encode($nChunks)], 
                                        [
                                            "eventID" => $data["event"][0]->eventID, 
                                            "chapter" => $data['currentChapter']
                                        ]
                                    );
                                }

                                $postdata = [
                                    "step" => EventSteps::READ_CHUNK,
                                ];

                                setcookie("temp_tutorial", false, time() - 24*3600, "/");
                                $this->_model->updateTranslator($postdata, ["trID" => $data["event"][0]->trID]);
                                Url::redirect('events/translator-tn/' . $data["event"][0]->eventID);
                            }
                        }

                        return View::make('Events/Notes/Translator')
                            ->nest('page', 'Events/Notes/Consume')
                            ->shares("title", $title)
                            ->shares("data", $data)
                            ->shares("error", @$error);
                        break;

                    case EventSteps::READ_CHUNK:
                        $sourceText = $this->getSourceText($data, true);
                        if($sourceText !== false && !array_key_exists("error", $sourceText))
                            $data = $sourceText;
                        
                        // Get notes
                        $sourceTextNotes = $this->getNotesSourceText($data, true);
                        
                        if($sourceTextNotes !== false)
                        {
                            if (!array_key_exists("error", $sourceTextNotes))
                            {
                                $data = $sourceTextNotes;
                            }
                            else
                            {
                                $error[] = $sourceTextNotes["error"];
                                $data["error"] = $sourceTextNotes["error"];
                            }
                        }
                        else
                        {
                            $this->_model->updateTranslator(["step" => EventSteps::FINISHED, "translateDone" => true], ["trID" => $data["event"][0]->trID]);
                            Url::redirect('events/translator-tn/' . $data["event"][0]->eventID);
                        }
                        
                        if (isset($_POST) && !empty($_POST))
                        {
                            if (isset($_POST["confirm_step"]))
                            {
                                setcookie("temp_tutorial", false, time() - 24*3600, "/");
                                $this->_model->updateTranslator(["step" => EventSteps::BLIND_DRAFT], ["trID" => $data["event"][0]->trID]);
                                Url::redirect('events/translator-tn/' . $data["event"][0]->eventID);
                            }
                        }

                        return View::make('Events/Notes/Translator')
                            ->nest('page', 'Events/Notes/ReadChunk')
                            ->shares("title", $title)
                            ->shares("data", $data)
                            ->shares("error", @$error);
                        break;

                    case EventSteps::BLIND_DRAFT: // Self-Check Notes
                        // Get scripture text
                        $sourceText = $this->getSourceText($data, true);
                        if($sourceText !== false && !array_key_exists("error", $sourceText))
                            $data = $sourceText;
                        
                        // Get notes
                        $sourceTextNotes = $this->getNotesSourceText($data, true);

                        if($sourceTextNotes !== false)
                        {
                            if (!array_key_exists("error", $sourceTextNotes))
                            {
                                $data = $sourceTextNotes;

                                $translationData = $this->_translationModel->getEventTranslation(
                                    $data["event"][0]->trID, 
                                    $data["event"][0]->currentChapter, 
                                    $data["event"][0]->currentChunk);
                                
                                if(!empty($translationData))
                                {
                                    $verses = json_decode($translationData[0]->translatedVerses, true);
                                    $data["translation"] = $verses[EventMembers::TRANSLATOR]["verses"];
                                }
                            }
                            else
                            {
                                $error[] = $sourceTextNotes["error"];
                                $data["error"] = $sourceTextNotes["error"];
                            }
                        }
                        else
                        {
                            $this->_model->updateTranslator(["step" => EventSteps::FINISHED, "translateDone" => true], ["trID" => $data["event"][0]->trID]);
                            Url::redirect('events/translator-tn/' . $data["event"][0]->eventID);
                        }
                        
                        if (isset($_POST) && !empty($_POST))
                        {
                            $chunk = isset($_POST["draft"]) ? $_POST["draft"] : "";
                            
							$confirm_step = isset($_POST["confirm_step"]) ? $_POST["confirm_step"] : "";
                            if (isset($_POST["confirm_step"]))
                            {
                                if(trim(strip_tags($chunk)) != "")
                                {
                                    $converter = new \Helpers\Markdownify\Converter;
                                    $chunk = $converter->parseString($chunk);

                                    $translationVerses = [
                                        EventMembers::TRANSLATOR => [
                                            "verses" => trim($chunk)
                                        ],
                                        EventMembers::CHECKER => [
                                            "verses" =>  array()
                                        ],
                                        EventMembers::L2_CHECKER => [
                                            "verses" => array()
                                        ],
                                        EventMembers::L3_CHECKER => [
                                            "verses" => array()
                                        ],
                                    ];

                                    $encoded = json_encode($translationVerses);
                                    $json_error = json_last_error();

                                    if($json_error == JSON_ERROR_NONE)
                                    {
                                        if(!empty($translationData))
                                        {
                                            $tID = $translationData[0]->tID;
                                            $trData = [
                                                "translatedVerses"  => $encoded,
                                            ];
                                            $this->_translationModel->updateTranslation(
                                                $trData, ["tID" => $tID]);
                                        }
                                        else
                                        {
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
                                                "firstvs"           => $data["event"][0]->currentChapter > 0 ? $data["chunk"][0] : 0,
                                                "translatedVerses"  => $encoded,
                                                "dateCreate"        => date('Y-m-d H:i:s')
                                            ];
    
                                            $tID = $this->_translationModel->createTranslation($trData);
                                        }
                                    }
                                    else 
                                    {
                                        $tID = "Json error: " . $json_error;
                                    }
                                    
                                    if(is_numeric($tID))
                                    {
                                        $postdata["step"] = EventSteps::SELF_CHECK;

                                        // If chapter is finished go to SELF_EDIT, otherwise go to the next chunk
                                        if(array_key_exists($data["event"][0]->currentChunk + 1, $data["chunks"]))
                                        {
                                            // Current chunk is finished, go to next chunk
                                            $postdata["currentChunk"] = $data["event"][0]->currentChunk + 1;
                                            $postdata["step"] = EventSteps::READ_CHUNK;
                                        }

                                        setcookie("temp_tutorial", false, time() - 24*3600, "/");
                                        $upd = $this->_model->updateTranslator($postdata, ["trID" => $data["event"][0]->trID]);
                                        Url::redirect('events/translator-tn/' . $data["event"][0]->eventID);
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

                        return View::make('Events/Notes/Translator')
                            ->nest('page', 'Events/Notes/BlindDraft')
                            ->shares("title", $title)
                            ->shares("data", $data)
                            ->shares("error", @$error);
                        break;

                    case EventSteps::SELF_CHECK:
                        // Get scripture text
                        $sourceText = $this->getSourceText($data);
                        if($sourceText !== false && !array_key_exists("error", $sourceText))
                            $data = $sourceText;
                        
                        // Get notes
                        $sourceTextNotes = $this->getNotesSourceText($data);

                        if($sourceTextNotes !== false)
                        {
                            if (!array_key_exists("error", $sourceTextNotes))
                            {
                                $data = $sourceTextNotes;
                                $data["comments"] = $this->getComments(
                                    $data["event"][0]->eventID, 
                                    $data["event"][0]->currentChapter);
                                
                                $translationData = $this->_translationModel->getEventTranslation(
                                    $data["event"][0]->trID, 
                                    $data["event"][0]->currentChapter);
                                $translation = array();

                                foreach ($translationData as $tv)
                                {
                                    $arr = json_decode($tv->translatedVerses, true);
                                    $arr["tID"] = $tv->tID;
                                    $translation[] = $arr;
                                }
                                $data["translation"] = $translation;
                            }
                            else
                            {
                                $error[] = $sourceTextNotes["error"];
                                $data["error"] = $sourceTextNotes["error"];
                            }
                        }
                        else
                        {
                            $this->_model->updateTranslator(["step" => EventSteps::FINISHED, "translateDone" => true], ["trID" => $data["event"][0]->trID]);
                            Url::redirect('events/translator-tn/' . $data["event"][0]->eventID);
                        }
                        
                        if (isset($_POST) && !empty($_POST))
                        {
                            if (isset($_POST["confirm_step"]))
                            {
								$chunks = isset($_POST["chunks"]) ? (array)$_POST["chunks"] : [];
                                $chunks = $this->testChunkNotes($chunks, $data["notes"], $data["currentChapter"]);
                                if(!$chunks === false)
                                {
                                    foreach ($chunks as $key => $chunk) 
                                    {
                                        if ($translation[$key][EventMembers::TRANSLATOR]["verses"] != $chunk) {
                                            $tID = $translation[$key]["tID"];
                                            unset($translation[$key]["tID"]);
                                            $translation[$key][EventMembers::TRANSLATOR]["verses"] = $chunk;
                                            
                                            $encoded = json_encode($translation[$key]);
                                            $json_error = json_last_error();
                                            
                                            if($json_error == JSON_ERROR_NONE)
                                            {
                                                $trData = array(
                                                    "translatedVerses"  => $encoded
                                                );
                                                $this->_translationModel->updateTranslation(
                                                    $trData, 
                                                    array(
                                                        "trID" => $data["event"][0]->trID, 
                                                        "tID" => $tID)
                                                );
                                            }
                                            else 
                                            {
                                                $tID = "Json error: " . $json_error;
                                            }

                                            if(!is_numeric($tID))
                                            {
                                                $error[] = __("error_ocured", array($tID));
                                            }
                                        }
                                    }
                                }
                                else
                                {
                                    $error[] = __("wrong_chunks_error");
                                }

                                if(!isset($error))
                                {
                                    $chapters = [];
                                    for($i=0; $i <= $data["event"][0]->chaptersNum; $i++)
                                    {
                                        $data["chapters"][$i] = [];
                                    }

                                    $chaptersDB = $this->_model->getChapters($data["event"][0]->eventID);
                                    
                                    foreach ($chaptersDB as $chapter) {
                                        $tmp["trID"] = $chapter["trID"];
                                        $tmp["memberID"] = $chapter["memberID"];
                                        $tmp["chunks"] = json_decode($chapter["chunks"], true);
                                        $tmp["done"] = $chapter["done"];

                                        $chapters[$chapter["chapter"]] = $tmp;
                                    }
                                    
                                    $chapters[$data["event"][0]->currentChapter]["done"] = true;
                                    $this->_model->updateChapter(["done" => true], ["eventID" => $data["event"][0]->eventID, "chapter" => $data["event"][0]->currentChapter]);

                                    // Check if the member has another chapter to translate
                                    // then redirect to preparation page
                                    $nextChapter = -1;
                                    $nextChapterDB = $this->_model->getNextChapter($data["event"][0]->eventID, Session::get("memberID"));
                                    
                                    if(!empty($nextChapterDB))
                                        $nextChapter = $nextChapterDB[0]->chapter;

                                    $otherCheck = (array)json_decode($data["event"][0]->otherCheck, true);
                                    if(!array_key_exists($data['currentChapter'], $otherCheck))
                                    {
                                        $otherCheck[$data['currentChapter']] = [
                                            "checkerID" => 0,
                                            "done" => 0
                                        ];
                                    }

                                    $postdata = [
                                        "step" => EventSteps::FINISHED,
                                        "currentChapter" => -1,
                                        "currentChunk" => 0,
                                        "translateDone" => true,
                                        "hideChkNotif" => false,
                                        "otherCheck" => json_encode($otherCheck)
                                    ];

                                    if($nextChapter > -1)
                                    {
                                        $postdata["step"] = EventSteps::PRAY;
                                        $postdata["currentChapter"] = $nextChapter;
                                        $postdata["translateDone"] = false;
                                    }

                                    setcookie("temp_tutorial", false, time() - 24*3600, "/");
                                    $upd = $this->_model->updateTranslator($postdata, ["trID" => $data["event"][0]->trID]);
                                    Url::redirect('events/translator-tn/' . $data["event"][0]->eventID);
                                }
                            }
                        }
                        
                        return View::make('Events/Notes/Translator')
                            ->nest('page', 'Events/Notes/SelfCheck')
                            ->shares("title", $title)
                            ->shares("data", $data)
                            ->shares("error", @$error);
                        break;

                    case EventSteps::FINISHED:
                        $data["success"] = __("you_event_finished_success");

                        return View::make('Events/Notes/Translator')
                            ->nest('page', 'Events/Notes/Finished')
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

                return View::make('Events/Notes/Translator')
                    ->shares("title", $title)
                    ->shares("data", $data)
                    ->shares("error", @$error);
            }
        }
        else
        {
            $error[] = __("not_in_event_error");
            $title = "Error";

            return View::make('Events/Notes/Translator')
                ->shares("title", $title)
                ->shares("data", $data)
                ->shares("error", @$error);
        }
    }

    public function checker($eventID, $memberID)
    {
        $response = array("success" => false, "errors" => "");

        $title = "";

        if(!isset($error))
        {
            $data["event"] = $this->_model->getMemberEventsForChecker(Session::get("memberID"), $eventID, $memberID);

            if(!empty($data["event"]))
            {
                if($data["event"][0]->step != EventSteps::FINISHED && !$data["event"][0]->translateDone)
                {
                    if(in_array($data["event"][0]->step, [EventSteps::PEER_REVIEW, EventSteps::KEYWORD_CHECK, EventSteps::CONTENT_REVIEW]))
                    {
                        $turnSecret = $this->_membersModel->getTurnSecret();
                        $turnUsername = (time() + 3600) . ":vmast";
                        $turnPassword = "";

                        if(!empty($turnSecret))
                        {
                            if(($turnSecret[0]->expire - time()) < 0)
                            {
                                $pass = $this->_membersModel->generateStrongPassword(22);
                                if($this->_membersModel->updateTurnSecret(array("value" => $pass, "expire" => time() + (30*24*3600)))) // Update turn secret each month
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

                                if($data["event"][0]->step == EventSteps::PEER_REVIEW)
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
                            $sourceText = $this->getSourceText($data);

                            if ($sourceText && !array_key_exists("error", $sourceText)) {
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

        $data["menu"] = 1;
        $data["isCheckerPage"] = true;
        $data["notifications"] = $this->_notifications;

        $page = "CheckerVerbalize";
        if(!isset($error))
        {
            if($data["event"][0]->step != EventSteps::VERBALIZE)
            {
                $sourceText = $this->getSourceText($data);
                if($sourceText !== false)
                {
                    if (!array_key_exists("error", $sourceText)) {
                        $translationData = $this->_translationModel->getEventTranslation($data["event"][0]->trID, $data["event"][0]->currentChapter);
                        $translation = array();

                        foreach ($translationData as $tv) {
                            $arr = json_decode($tv->translatedVerses, true);
                            $arr["tID"] = $tv->tID;
                            $translation[] = $arr;
                        }

                        $data = $sourceText;
                        $data["translation"] = $translation;

                        if($data["event"][0]->step == EventSteps::KEYWORD_CHECK)
                            $data["keywords"] = $this->getUwKeyWords($data["event"][0]->bookCode, $data["event"][0]->sourceLangID, $data["event"][0]->currentChapter, $data["totalVerses"]);
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

    public function checkerNotes($eventID)
    {
        $data["menu"] = 1;
        $data["isCheckerPage"] = true;
        $title = "";

        $data["event"] = $this->_model->getMemberEvents(
            Session::get("memberID"),
            EventMembers::TRANSLATOR,
            $eventID, 
            true, 
            false,
            true);

        if(!empty($data["event"]))
        {
            if($data["event"][0]->stage == "checking" && 
                $data["event"][0]->currentChapter > -1)
            {
                if($data["event"][0]->step == EventSteps::NONE)
                    Url::redirect("events/information-tn/".$eventID);

                $title = $data["event"][0]->name ." - ". $data["event"][0]->tLang ." - ". __($data["event"][0]->bookProject);

                $turnSecret = $this->_membersModel->getTurnSecret();
                $turnUsername = (time() + 3600) . ":vmast";
                $turnPassword = "";

                if(!empty($turnSecret))
                {
                    if(($turnSecret[0]->expire - time()) < 0)
                    {
                        $pass = $this->_membersModel->generateStrongPassword(22);
                        if($this->_membersModel->updateTurnSecret(["value" => $pass, "expire" => time() + (30*24*3600)])) // Update turn secret each month
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
                        
                        // Get scripture text
                        $sourceText = $this->getSourceText($data);
                        if($sourceText !== false && !array_key_exists("error", $sourceText))
                            $data = $sourceText;
                        
                        // Get notes
                        $sourceTextNotes = $this->getNotesSourceText($data);

                        if($sourceTextNotes !== false)
                        {
                            if (!array_key_exists("error", $sourceTextNotes))
                            {
                                $data = $sourceTextNotes;
                            }
                            else
                            {
                                $error[] = $sourceTextNotes["error"];
                                $data["error"] = $sourceTextNotes["error"];
                            }
                        }
                        else
                        {
                            $this->_model->updateTranslator(["step" => EventSteps::FINISHED, "translateDone" => true], ["trID" => $data["event"][0]->trID]);
                            Url::redirect('events/checker-tn/' . $data["event"][0]->eventID);
                        }
                        
                        if (isset($_POST) && !empty($_POST))
                        {
                            if (isset($_POST["confirm_step"]))
                            {
                                setcookie("temp_tutorial", false, time() - 24*3600, "/");
                                
                                $postdata = [
                                    "step" => !$data["nosource"] ? EventSteps::CONSUME : EventSteps::SELF_CHECK
                                ];
                                $this->_model->updateTranslator($postdata, ["trID" => $data["event"][0]->trID]);
                                
                                Url::redirect('events/checker-tn/' . $data["event"][0]->eventID);
                            }
                        }

                        $data["event"][0]->justStarted = true;
                        
                        return View::make('Events/Notes/Translator')
                            ->nest('page', 'Events/Notes/Pray')
                            ->shares("title", $title)
                            ->shares("data", $data)
                            ->shares("error", @$error);

                    case EventSteps::CONSUME: // Consume chapter
                        // Get scripture text
                        $sourceText = $this->getSourceText($data);
                        if($sourceText !== false && !array_key_exists("error", $sourceText))
                            $data = $sourceText;
                        
                        // Get notes
                        $sourceTextNotes = $this->getNotesSourceText($data);

                        if($sourceTextNotes !== false)
                        {
                            if (!array_key_exists("error", $sourceTextNotes))
                            {
                                $data = $sourceTextNotes;

                                if(isset($data["nosource"]) && $data["nosource"])
                                {
                                    $this->_model->updateTranslator(["step" => EventSteps::SELF_CHECK], ["trID" => $data["event"][0]->trID]);
                                    Url::redirect('events/checker-tn/' . $data["event"][0]->eventID);
                                }
                            }
                            else
                            {
                                $error[] = $sourceTextNotes["error"];
                                $data["error"] = $sourceTextNotes["error"];
                            }
                        }
                        else
                        {
                            $this->_model->updateTranslator(["step" => EventSteps::FINISHED, "translateDone" => true], ["trID" => $data["event"][0]->trID]);
                            Url::redirect('events/checker-tn/' . $data["event"][0]->eventID);
                        }
                        
                        if (isset($_POST) && !empty($_POST))
                        {
                            if (isset($_POST["confirm_step"]))
                            {
                                $postdata = [
                                    "step" => EventSteps::HIGHLIGHT,
                                ];

                                setcookie("temp_tutorial", false, time() - 24*3600, "/");
                                $this->_model->updateTranslator($postdata, ["trID" => $data["event"][0]->trID]);
                                Url::redirect('events/checker-tn/' . $data["event"][0]->eventID);
                            }
                        }

                        return View::make('Events/Notes/Translator')
                            ->nest('page', 'Events/Notes/Consume')
                            ->shares("title", $title)
                            ->shares("data", $data)
                            ->shares("error", @$error);
                        break;

                    case EventSteps::HIGHLIGHT: // Highlight chapter
                        // Get scripture text
                        $sourceText = $this->getSourceText($data);
                        if($sourceText !== false && !array_key_exists("error", $sourceText))
                            $data = $sourceText;
                        
                        // Get notes
                        $sourceTextNotes = $this->getNotesSourceText($data);

                        if($sourceTextNotes !== false)
                        {
                            if (!array_key_exists("error", $sourceTextNotes))
                            {
                                $data = $sourceTextNotes;
                                
                                if(isset($data["nosource"]) && $data["nosource"])
                                {
                                    $this->_model->updateTranslator(["step" => EventSteps::SELF_CHECK], ["trID" => $data["event"][0]->trID]);
                                    Url::redirect('events/checker-tn/' . $data["event"][0]->eventID);
                                }
                            }
                            else
                            {
                                $error[] = $sourceTextNotes["error"];
                                $data["error"] = $sourceTextNotes["error"];
                            }
                        }
                        else
                        {
                            $this->_model->updateTranslator(["step" => EventSteps::FINISHED, "translateDone" => true], ["trID" => $data["event"][0]->trID]);
                            Url::redirect('events/checker-tn/' . $data["event"][0]->eventID);
                        }
                        
                        if (isset($_POST) && !empty($_POST))
                        {
                            if (isset($_POST["confirm_step"]))
                            {
                                $postdata = [
                                    "step" => EventSteps::SELF_CHECK,
                                ];

                                setcookie("temp_tutorial", false, time() - 24*3600, "/");
                                $this->_model->updateTranslator($postdata, ["trID" => $data["event"][0]->trID]);
                                Url::redirect('events/checker-tn/' . $data["event"][0]->eventID);
                            }
                        }

                        return View::make('Events/Notes/Translator')
                            ->nest('page', 'Events/Notes/Highlight')
                            ->shares("title", $title)
                            ->shares("data", $data)
                            ->shares("error", @$error);
                        break;

                    case EventSteps::SELF_CHECK: // Criteria Check Notes
                        $sourceText = $this->getSourceText($data);
                        if($sourceText !== false && !array_key_exists("error", $sourceText))
                            $data = $sourceText;

                        // Get notes
                        $sourceTextNotes = $this->getNotesSourceText($data);

                        if($sourceTextNotes !== false)
                        {
                            if (!array_key_exists("error", $sourceTextNotes))
                            {
                                $data = $sourceTextNotes;
                                
                                $data["comments"] = $this->getComments(
                                    $data["event"][0]->eventID, 
                                    $data["event"][0]->currentChapter);

                                $translationData = $this->_translationModel->getEventTranslation(
                                    $data["event"][0]->nTranslator,
                                    $data["event"][0]->currentChapter);
                                
                                $translation = array();
                                
                                foreach ($translationData as $tv)
                                {
                                    $arr = json_decode($tv->translatedVerses, true);
                                    $arr["tID"] = $tv->tID;
                                    $translation[] = $arr;
                                }
                                $data["translation"] = $translation;
                            }
                            else
                            {
                                $error[] = $sourceTextNotes["error"];
                                $data["error"] = $sourceTextNotes["error"];
                            }
                        }
                        else
                        {
                            $this->_model->updateTranslator(["step" => EventSteps::FINISHED, "translateDone" => true], ["trID" => $data["event"][0]->trID]);
                            Url::redirect('events/checker-tn/' . $data["event"][0]->eventID);
                        }
                        
                        if (isset($_POST) && !empty($_POST))
                        {
                            $chunks = isset($_POST["chunks"]) ? (array)$_POST["chunks"] : [];
                            $chunks = $this->testChunkNotes($chunks, $data["notes"], $data["currentChapter"]);

                            if(!$chunks === false)
                            {
                                foreach ($chunks as $key => $chunk) 
                                {
                                    if ($translation[$key][EventMembers::CHECKER]["verses"] != $chunk) {
                                        $tID = $translation[$key]["tID"];
                                        unset($translation[$key]["tID"]);
                                        
                                        $translation[$key][EventMembers::CHECKER]["verses"] = $chunk;
                                        
                                        $encoded = json_encode($translation[$key]);
                                        $json_error = json_last_error();
                                        
                                        if($json_error == JSON_ERROR_NONE)
                                        {
                                            $trData = array(
                                                "translatedVerses"  => $encoded
                                            );
                                            $this->_translationModel->updateTranslation(
                                                $trData, 
                                                array(
                                                    "tID" => $tID)
                                            );
                                        }
                                        else 
                                        {
                                            $tID = "Json error: " . $json_error;
                                        }

                                        if(!is_numeric($tID))
                                        {
                                            $error[] = __("error_ocured", array($tID));
                                        }
                                    }
                                }
                            }
                            else
                            {
                                $error[] = __("wrong_chunks_error");
                            }

                            if (!isset($error) && isset($_POST["confirm_step"]))
                            {
                                $postdata["step"] = EventSteps::KEYWORD_CHECK;
                                if(isset($data["nosource"]) && $data["nosource"])
                                {
                                    $postdata["step"] = EventSteps::PEER_REVIEW;
                                    $postdata["checkerID"] = 0;
                                    $postdata["checkDone"] = false;
                                    $postdata["hideChkNotif"] = false;
                                }
                                
                                setcookie("temp_tutorial", false, time() - 24*3600, "/");
                                $this->_model->updateTranslator($postdata, ["trID" => $data["event"][0]->trID]);
                                Url::redirect('events/checker-tn/' . $data["event"][0]->eventID);
                            }
                        }

                        return View::make('Events/Notes/Translator')
                            ->nest('page', 'Events/Notes/SelfCheckChecker')
                            ->shares("title", $title)
                            ->shares("data", $data)
                            ->shares("error", @$error);
                        break;

                    case EventSteps::KEYWORD_CHECK: // Highlight Check Notes
                        $sourceText = $this->getSourceText($data);
                        if($sourceText !== false && !array_key_exists("error", $sourceText))
                            $data = $sourceText;

                        // Get notes
                        $sourceTextNotes = $this->getNotesSourceText($data);

                        if($sourceTextNotes !== false)
                        {
                            if (!array_key_exists("error", $sourceTextNotes))
                            {
                                $data = $sourceTextNotes;
                                
                                $data["comments"] = $this->getComments(
                                    $data["event"][0]->eventID, 
                                    $data["event"][0]->currentChapter);

                                $translationData = $this->_translationModel->getEventTranslation(
                                    $data["event"][0]->nTranslator,
                                    $data["event"][0]->currentChapter);
                                
                                $translation = array();
                                
                                foreach ($translationData as $tv)
                                {
                                    $arr = json_decode($tv->translatedVerses, true);
                                    $arr["tID"] = $tv->tID;
                                    $translation[] = $arr;
                                }
                                $data["translation"] = $translation;
                            }
                            else
                            {
                                $error[] = $sourceTextNotes["error"];
                                $data["error"] = $sourceTextNotes["error"];
                            }
                        }
                        else
                        {
                            $this->_model->updateTranslator(["step" => EventSteps::FINISHED, "translateDone" => true], ["trID" => $data["event"][0]->trID]);
                            Url::redirect('events/checker-tn/' . $data["event"][0]->eventID);
                        }
                        
                        if (isset($_POST) && !empty($_POST))
                        {
                            $chunks = isset($_POST["chunks"]) ? (array)$_POST["chunks"] : [];
                            $chunks = $this->testChunkNotes($chunks, $data["notes"], $data["currentChapter"]);
                            $confirm_step = isset($_POST["confirm_step"]) ? $_POST["confirm_step"] : "";

                            if(!$chunks === false)
                            {
                                foreach ($chunks as $key => $chunk) 
                                {
                                    if ($translation[$key][EventMembers::CHECKER]["verses"] != $chunk) {
                                        $tID = $translation[$key]["tID"];
                                        unset($translation[$key]["tID"]);
                                        
                                        $translation[$key][EventMembers::CHECKER]["verses"] = $chunk;
                                        
                                        $encoded = json_encode($translation[$key]);
                                        $json_error = json_last_error();
                                        
                                        if($json_error == JSON_ERROR_NONE)
                                        {
                                            $trData = array(
                                                "translatedVerses"  => $encoded
                                            );
                                            $this->_translationModel->updateTranslation(
                                                $trData, 
                                                array(
                                                    "tID" => $tID)
                                            );
                                        }
                                        else 
                                        {
                                            $tID = "Json error: " . $json_error;
                                        }

                                        if(!is_numeric($tID))
                                        {
                                            $error[] = __("error_ocured", array($tID));
                                        }
                                    }
                                }
                            }
                            else
                            {
                                $error[] = __("wrong_chunks_error");
                            }

                            if (!isset($error) && isset($_POST["confirm_step"]))
                            {
                                $postdata["step"] = EventSteps::PEER_REVIEW;
                                $postdata["checkerID"] = 0;
                                $postdata["checkDone"] = false;
                                $postdata["hideChkNotif"] = false;
                                
                                setcookie("temp_tutorial", false, time() - 24*3600, "/");
                                $upd = $this->_model->updateTranslator($postdata, ["trID" => $data["event"][0]->trID]);
                                Url::redirect('events/checker-tn/' . $data["event"][0]->eventID);
                            }
                        }
                        
                        return View::make('Events/Notes/Translator')
                            ->nest('page', 'Events/Notes/HighlightChecker')
                            ->shares("title", $title)
                            ->shares("data", $data)
                            ->shares("error", @$error);
                        break;

                    case EventSteps::PEER_REVIEW:
                        $sourceText = $this->getSourceText($data);
                        if($sourceText !== false && !array_key_exists("error", $sourceText))
                            $data = $sourceText;

                        // Get notes
                        $sourceTextNotes = $this->getNotesSourceText($data);

                        if($sourceTextNotes !== false)
                        {
                            if (!array_key_exists("error", $sourceTextNotes))
                            {
                                $data = $sourceTextNotes;
                                
                                $data["comments"] = $this->getComments(
                                    $data["event"][0]->eventID, 
                                    $data["event"][0]->currentChapter);

                                $translationData = $this->_translationModel->getEventTranslation(
                                    $data["event"][0]->nTranslator,
                                    $data["event"][0]->currentChapter);
                                
                                $translation = array();
                                
                                foreach ($translationData as $tv)
                                {
                                    $arr = json_decode($tv->translatedVerses, true);
                                    $arr["tID"] = $tv->tID;
                                    $translation[] = $arr;
                                }
                                $data["translation"] = $translation;
                            }
                            else
                            {
                                $error[] = $sourceTextNotes["error"];
                                $data["error"] = $sourceTextNotes["error"];
                            }
                        }
                        else
                        {
                            $this->_model->updateTranslator(["step" => EventSteps::FINISHED, "translateDone" => true], ["trID" => $data["event"][0]->trID]);
                            Url::redirect('events/checker-tn/' . $data["event"][0]->eventID);
                        }
                        
                        if (isset($_POST) && !empty($_POST)) {
                            if (isset($_POST["confirm_step"]))
                            {
                                if($data["event"][0]->checkDone)
                                {
                                    $translator = $this->_model->getEventTranslator(
                                        $data["event"][0]->eventID,
                                        $data["event"][0]->nTranslator
                                    );

                                    if(!empty($translator))
                                    {
                                        $otherCheck = (array)json_decode($translator[0]->otherCheck, true);
                                        $otherCheck[$data['currentChapter']]["done"] = 1;
                                        
                                        $this->_model->updateTranslator(
                                            ["otherCheck" => json_encode($otherCheck)], 
                                            ["trID" => $translator[0]->trID]);
                                    }

                                    $chapters = [];
                                    for($i=0; $i <= $data["event"][0]->chaptersNum; $i++)
                                    {
                                        $data["chapters"][$i] = [];
                                    }

                                    $chaptersDB = $this->_model->getChapters($data["event"][0]->eventID);

                                    foreach ($chaptersDB as $chapter) {
                                        $tmp["trID"] = $chapter["trID"];
                                        $tmp["memberID"] = $chapter["memberID"];
                                        $tmp["chunks"] = json_decode($chapter["chunks"], true);
                                        $tmp["done"] = $chapter["done"];
                                        $tmp["checked"] = $chapter["checked"];

                                        $chapters[$chapter["chapter"]] = $tmp;
                                    }
                                    
                                    $chapters[$data["event"][0]->currentChapter]["checked"] = true;
                                    $this->_model->updateChapter(["checked" => true], [
                                        "eventID" => $data["event"][0]->eventID, 
                                        "chapter" => $data["event"][0]->currentChapter]);
                                    
                                    // Set all event translations as Done
                                    foreach ($translationData as $key => $chunk) {
                                        $tID = $chunk->tID;
                                        $trID = $chunk->trID;
                                        $trData = array(
                                            "translateDone" => true
                                        );
                                        
                                        $this->_translationModel->updateTranslation(
                                            $trData, 
                                            array(
                                                "trID" => $trID, 
                                                "tID" => $tID));
                                    }
                                    
                                    // Check if whole book is finished
                                    if($this->checkBookFinished($chapters, $data["event"][0]->chaptersNum+1, true))
                                        $this->_model->updateEvent([
                                            "state" => EventStates::TRANSLATED,
                                            "dateTo" => date("Y-m-d H:i:s", time())],
                                            ["eventID" => $data["event"][0]->eventID]);

                                    setcookie("temp_tutorial", false, time() - 24*3600, "/");
                                    $postdata = [
                                        "step" => EventSteps::PRAY,
                                        "checkerID" => 0,
                                        "checkDone" => false,
                                        "stage" => "translation",
                                        "currentChapter" => -1,
                                        "currentChunk" => 0,
                                        "translateDone" => true,
                                        "nTranslator" => 0
                                    ];

                                    $this->_model->updateTranslator(
                                        $postdata, 
                                        ["trID" => $data["event"][0]->trID]);
                                    Url::redirect('events');
                                }
                                else {
                                    $error[] = __("checker_not_ready_error");
                                }
                            }
                        }

                        return View::make('Events/Notes/Translator')
                            ->nest('page', 'Events/Notes/PeerReview')
                            ->shares("title", $title)
                            ->shares("data", $data)
                            ->shares("error", @$error);
                        break;

                    case EventSteps::FINISHED:
                        $data["success"] = __("you_event_finished_success");
                        
                        return View::make('Events/Notes/Translator')
                            ->nest('page', 'Events/Notes/Finished')
                            ->shares("title", $title)
                            ->shares("data", $data)
                            ->shares("error", @$error);
                        break;
                }
            }
            else
            {
                $error[] = __("checker_event_error");
                $title = "Error";
            } 
        }
        else
        {
            $error[] = __("checker_event_error");
            $title = "Error";
        }

        $data["menu"] = 1;
        $data["notifications"] = $this->_notifications;

        return View::make('Events/Notes/Translator')
            ->shares("title", $title)
            ->shares("data", $data)
            ->shares("error", @$error);
    }

    public function checkerNotesPeer($eventID, $memberID)
    {
        $data["event"] = $this->_model->getMemberEventsForChecker(Session::get("memberID"), $eventID, $memberID);
        
        if(!empty($data["event"]))
        {
            if($data["event"][0]->step != EventSteps::FINISHED && !$data["event"][0]->checkDone)
            {
                if($data["event"][0]->step == EventSteps::PEER_REVIEW)
                {
                    $turnSecret = $this->_membersModel->getTurnSecret();
                    $turnUsername = (time() + 3600) . ":vmast";
                    $turnPassword = "";

                    if(!empty($turnSecret))
                    {
                        if(($turnSecret[0]->expire - time()) < 0)
                        {
                            $pass = $this->_membersModel->generateStrongPassword(22);
                            if($this->_membersModel->updateTurnSecret(array("value" => $pass, "expire" => time() + (30*24*3600)))) // Update turn secret each month
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

                            $peerCheck = (array)json_decode($data["event"][0]->peerCheck, true);
                            if(!array_key_exists($data["event"][0]->currentChapter, $peerCheck))
                            {
                                $peerCheck[$data["event"][0]->currentChapter] = Session::get("memberID");
                                $postdata["peerCheck"] = json_encode($peerCheck);
                            }

                            $this->_model->updateTranslator($postdata, array("trID" => $data["event"][0]->trID));

                            $response["success"] = true;
                            echo json_encode($response);
                            exit;
                        }
                    }

                    if($data["event"][0]->checkDone)
                    {
                        $data["success"] = __("checker_translator_finished_error");
                    }
                    else
                    {
                        $sourceText = $this->getSourceText($data);
                        if($sourceText !== false && !array_key_exists("error", $sourceText))
                            $data = $sourceText;
                        
                        // Get notes
                        $sourceTextNotes = $this->getNotesSourceText($data);

                        if($sourceTextNotes !== false)
                        {
                            if (!array_key_exists("error", $sourceTextNotes))
                            {
                                $data = $sourceTextNotes;

                                $data["comments"] = $this->getComments(
                                    $data["event"][0]->eventID, 
                                    $data["event"][0]->currentChapter);
                                
                                $translationData = $this->_translationModel->getEventTranslation(
                                    $data["event"][0]->nTranslator,
                                    $data["event"][0]->currentChapter);
                                
                                $translation = array();
                                
                                foreach ($translationData as $tv)
                                {
                                    $arr = (array)json_decode($tv->translatedVerses, true);
                                    $arr["tID"] = $tv->tID;
                                    $translation[] = $arr;
                                }
                                $data["translation"] = $translation;
                            }
                            else
                            {
                                $error[] = $sourceTextNotes["error"];
                            }
                        }
                        else
                        {
                            $error[] = $sourceTextNotes["error"];
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

        $data["menu"] = 1;
        $data["isCheckerPage"] = true;
        $data["isPeerPage"] = true;
        $data["notifications"] = $this->_notifications;

        return View::make('Events/Notes/Translator')
            ->nest('page', 'Events/Notes/CheckerPeerReview')
            ->shares("title", $title)
            ->shares("data", $data)
            ->shares("error", @$error);
    }

    /**
     * View for Level 2 check page
     * @url /events/check-l2
     * @param $eventID
     * @return View
     */
    public function checkerL2($eventID)
    {
        $data["menu"] = 1;
        $data["notifications"] = $this->_notifications;
        $data["event"] = $this->_model->getMemberEvents(Session::get("memberID"), EventMembers::L2_CHECKER, $eventID);
        
        $title = "";

        if(!empty($data["event"]))
        {
            $title = $data["event"][0]->name ." - ". $data["event"][0]->tLang ." - ". __($data["event"][0]->bookProject);

            if($data["event"][0]->state == EventStates::L2_CHECK || $data["event"][0]->state == EventStates::L2_CHECKED)
            {
                if($data["event"][0]->step == EventCheckSteps::NONE)
                    Url::redirect("events/information-l2/".$eventID);

                $turnSecret = $this->_membersModel->getTurnSecret();
                $turnUsername = (time() + 3600) . ":vmast";
                $turnPassword = "";

                if(!empty($turnSecret))
                {
                    if(($turnSecret[0]->expire - time()) < 0)
                    {
                        $pass = $this->_membersModel->generateStrongPassword(22);
                        if($this->_membersModel->updateTurnSecret(["value" => $pass, "expire" => time() + (30*24*3600)])) // Update turn secret each month
                        {
                            $turnSecret[0]->value = $pass;
                        }
                    }

                    $turnPassword = hash_hmac("sha1", $turnUsername, $turnSecret[0]->value, true);
                }

                $data["turn"][] = $turnUsername;
                $data["turn"][] = base64_encode($turnPassword);
                
                switch ($data["event"][0]->step) {
                    case EventCheckSteps::PRAY:
                        $sourceText = $this->getSourceText($data);

                        if($sourceText !== false)
                        {
                            if (!array_key_exists("error", $sourceText))
                            {
                                $data = $sourceText;
                            }
                            else
                            {
                                $error[] = $sourceText["error"];
                                $data["error"] = $sourceText["error"];
                            }
                        }
                        else
                        {
                            $this->_model->updateL2Checker([
                                "step" => EventCheckSteps::NONE
                            ], [
                                "trID" => $data["event"][0]->l2chID
                            ]);
                            Url::redirect('events/checker-l2/' . $data["event"][0]->eventID);
                        }

                        if (isset($_POST) && !empty($_POST))
                        {
                            if (isset($_POST["confirm_step"]))
                            {
                                setcookie("temp_tutorial", false, time() - 24*3600, "/");
                                $postdata = [
                                    "step" => EventCheckSteps::CONSUME,
                                    "currentChapter" => $sourceText["currentChapter"]
                                ];
                                $this->_model->updateL2Checker($postdata, [
                                    "l2chID" => $data["event"][0]->l2chID
                                ]);
                                Url::redirect('events/checker-l2/' . $data["event"][0]->eventID);
                            }
                        }

                        return View::make('Events/L2/Checker')
                            ->nest('page', 'Events/L2/Pray')
                            ->shares("title", $title)
                            ->shares("data", $data)
                            ->shares("error", @$error);

                    case EventCheckSteps::CONSUME:
                        $sourceText = $this->getSourceText($data);

                        if($sourceText !== false)
                        {
                            if (!array_key_exists("error", $sourceText))
                            {
                                $data = $sourceText;

                                $translationData = $this->_translationModel->getEventTranslationByEventID(
                                    $data["event"][0]->eventID,
                                    $data["event"][0]->currentChapter
                                );
                                $translation = array();

                                foreach ($translationData as $tv)
                                {
                                    $arr = (array)json_decode($tv->translatedVerses, true);
                                    $arr["tID"] = $tv->tID;
                                    $translation[] = $arr;
                                }
                                $data["translation"] = $translation;
                            }
                            else
                            {
                                $error[] = $sourceText["error"];
                                $data["error"] = $sourceText["error"];
                            }
                        }
                        else
                        {
                            $this->_model->updateL2Checker([
                                "step" => EventCheckSteps::NONE
                            ], [
                                "l2chID" => $data["event"][0]->l2chID
                            ]);
                            Url::redirect('events/checker-l2/' . $data["event"][0]->eventID);
                        }

                        if (isset($_POST) && !empty($_POST))
                        {
                            if (isset($_POST["confirm_step"]))
                            {
                                setcookie("temp_tutorial", false, time() - 24*3600, "/");
                                $this->_model->updateL2Checker([
                                    "step" => EventCheckSteps::FST_CHECK
                                ], [
                                    "l2chID" => $data["event"][0]->l2chID
                                ]);
                                Url::redirect('events/checker-l2/' . $data["event"][0]->eventID);
                            }
                        }

                        return View::make('Events/L2/Checker')
                            ->nest('page', 'Events/L2/Consume')
                            ->shares("title", $title)
                            ->shares("data", $data)
                            ->shares("error", @$error);
                        break;

                    case EventCheckSteps::FST_CHECK:
                        $sourceText = $this->getSourceText($data);

                        if($sourceText !== false)
                        {
                            if (!array_key_exists("error", $sourceText))
                            {
                                $data = $sourceText;

                                $data["comments"] = $this->getComments(
                                    $data["event"][0]->eventID,
                                    $data["event"][0]->currentChapter);

                                $translationData = $this->_translationModel->getEventTranslationByEventID(
                                    $data["event"][0]->eventID,
                                    $data["event"][0]->currentChapter
                                );
                                $translation = array();

                                foreach ($translationData as $tv)
                                {
                                    $arr = (array)json_decode($tv->translatedVerses, true);
                                    $arr["tID"] = $tv->tID;
                                    $translation[] = $arr;
                                }
                                $data["translation"] = $translation;
                            }
                            else
                            {
                                $error[] = $sourceText["error"];
                                $data["error"] = $sourceText["error"];
                            }
                        }
                        else
                        {
                            $this->_model->updateL2Checker([
                                "step" => EventCheckSteps::NONE
                            ], [
                                "l2chID" => $data["event"][0]->l2chID
                            ]);
                            Url::redirect('events/checker-l2/' . $data["event"][0]->eventID);
                        }

                        if (isset($_POST) && !empty($_POST))
                        {
                            if (isset($_POST["confirm_step"]))
                            {
                                $post = Gump::xss_clean($_POST);

                                if(is_array($post["chunks"]) && !empty($post["chunks"]))
                                {
                                    if(!empty($translation))
                                    {
                                        array_walk_recursive($post["chunks"], function(&$item) {
                                            $item = trim($item);
                                        });
                                        $post["chunks"] = array_filter($post["chunks"], function($chunk) {
                                            $verses = array_filter($chunk, function($v) {
                                                return !empty(strip_tags(trim($v)));
                                            });
                                            $isEqual = sizeof($chunk) == sizeof($verses);
                                            return !empty($chunk) && $isEqual;
                                        });

                                        if(sizeof($translation) == sizeof($post["chunks"]))
                                        {
                                            foreach ($translation as $key => $chunk) {
                                                if(!isset($post["chunks"][$key])) continue;

                                                $shouldUpdate = false;
                                                foreach ($post["chunks"][$key] as $verse => $vText)
                                                {
                                                    if(!isset($chunk[EventMembers::L2_CHECKER]["verses"][$verse])
                                                        || $chunk[EventMembers::L2_CHECKER]["verses"][$verse] != $vText)
                                                    {
                                                        $shouldUpdate = true;
                                                    }
                                                }

                                                $translation[$key][EventMembers::L2_CHECKER]["verses"] = $post["chunks"][$key];

                                                if($shouldUpdate)
                                                {
                                                    $tID = $translation[$key]["tID"];
                                                    unset($translation[$key]["tID"]);

                                                    $encoded = json_encode($translation[$key]);
                                                    $json_error = json_last_error();
                                                    if($json_error === JSON_ERROR_NONE)
                                                    {
                                                        $trData = array(
                                                            "translatedVerses"  => $encoded
                                                        );
                                                        $upd = $this->_translationModel->updateTranslation(
                                                            $trData,
                                                            array(
                                                                "tID" => $tID));

                                                        if(!is_numeric($upd))
                                                        {
                                                            $error[] = __("error_occurred", array($upd));
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                        else
                                        {
                                            $error[] = __("empty_draft_verses_error");
                                        }
                                    }
                                }

                                if(!isset($error))
                                {
                                    $chapters = [];
                                    for($i=1; $i <= $data["event"][0]->chaptersNum; $i++)
                                    {
                                        $data["chapters"][$i] = [];
                                    }

                                    $chaptersDB = $this->_model->getChapters($data["event"][0]->eventID);

                                    foreach ($chaptersDB as $chapter) {
                                        $tmp["trID"] = $chapter["trID"];
                                        $tmp["memberID"] = $chapter["memberID"];
                                        $tmp["chunks"] = json_decode($chapter["chunks"], true);
                                        $tmp["done"] = $chapter["done"];
                                        $tmp["l2memberID"] = $chapter["l2memberID"];
                                        $tmp["l2chID"] = $chapter["l2chID"];
                                        $tmp["l2checked"] = $chapter["l2checked"];

                                        $chapters[$chapter["chapter"]] = $tmp;
                                    }

                                    $this->_model->updateChapter([
                                        "l2checked" => true
                                    ], [
                                        "eventID" => $data["event"][0]->eventID,
                                        "chapter" => $data["event"][0]->currentChapter
                                    ]);

                                    $chapters[$data["event"][0]->currentChapter]["l2checked"] = true;

                                    // Check if the member has another chapter to check
                                    // then redirect to preparation page
                                    $nextChapter = 0;
                                    $nextChapterDB = $this->_model->getNextChapter(
                                        $data["event"][0]->eventID,
                                        $data["event"][0]->memberID,
                                        "l2");
                                    if(!empty($nextChapterDB))
                                        $nextChapter = $nextChapterDB[0]->chapter;

                                    $sndCheck = (array)json_decode($data["event"][0]->sndCheck, true);
                                    if(!array_key_exists($data["event"][0]->currentChapter, $sndCheck))
                                    {
                                        $sndCheck[$data["event"][0]->currentChapter] = [
                                            "memberID" => 0,
                                            "done" => 0
                                        ];
                                    }

                                    $postdata = [
                                        "step" => EventSteps::NONE,
                                        "currentChapter" => 0,
                                        "sndCheck" => json_encode($sndCheck)
                                    ];

                                    if($nextChapter > 0)
                                    {
                                        $postdata["step"] = EventSteps::PRAY;
                                        $postdata["currentChapter"] = $nextChapter;
                                    }

                                    setcookie("temp_tutorial", false, time() - 24*3600, "/");
                                    $this->_model->updateL2Checker($postdata, [
                                        "l2chID" => $data["event"][0]->l2chID
                                    ]);
                                    Url::redirect('events/checker-l2/' . $data["event"][0]->eventID);
                                }
                            }
                        }

                        return View::make('Events/L2/Checker')
                            ->nest('page', 'Events/L2/FstCheck')
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

                return View::make('Events/L2/Checker')
                    ->shares("title", $title)
                    ->shares("data", $data)
                    ->shares("error", @$error);
            }
        }
        else
        {
            $error[] = __("not_in_event_error");
            $title = "Error";

            return View::make('Events/L2/Checker')
                ->shares("title", $title)
                ->shares("data", $data)
                ->shares("error", @$error);
        }
    }

    /**
     * View for 2nd check and Peer check in Level2 event
     * @param $eventID
     * @param $memberID
     * @return View
     */
    public function checkerL2Continue($eventID, $memberID, $chapter)
    {
        $isAjax = false;
        if(!empty($_SERVER['HTTP_X_REQUESTED_WITH'])
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            $isAjax = true;
            $response["success"] = false;
        }

        $data["menu"] = 1;
        $data["notifications"] = $this->_notifications;
        $data["event"] = $this->_model->getMemberEventsForCheckerL2(
            Session::get("memberID"), $eventID, $memberID, $chapter);

        if(!empty($data["event"]))
        {
            $title = $data["event"][0]->name ." - ". $data["event"][0]->tLang ." - ". __($data["event"][0]->bookProject);

            if($data["event"][0]->state == EventStates::L2_CHECK || $data["event"][0]->state == EventStates::L2_CHECKED)
            {
                $turnSecret = $this->_membersModel->getTurnSecret();
                $turnUsername = (time() + 3600) . ":vmast";
                $turnPassword = "";

                if(!empty($turnSecret))
                {
                    if(($turnSecret[0]->expire - time()) < 0)
                    {
                        $pass = $this->_membersModel->generateStrongPassword(22);
                        if($this->_membersModel->updateTurnSecret(["value" => $pass, "expire" => time() + (30*24*3600)])) // Update turn secret each month
                        {
                            $turnSecret[0]->value = $pass;
                        }
                    }

                    $turnPassword = hash_hmac("sha1", $turnUsername, $turnSecret[0]->value, true);
                }

                $data["turn"][] = $turnUsername;
                $data["turn"][] = base64_encode($turnPassword);

                $chapters = $this->_model->getChapters($eventID, null, $chapter);
                $data["event"][0]->chunks = [];
                if(!empty($chapters))
                {
                    $data["event"][0]->chunks = $chapters[0]["chunks"];
                }

                switch ($data["event"][0]->step)
                {
                    case EventCheckSteps::SND_CHECK:
                        $sourceText = $this->getSourceText($data);

                        if($sourceText !== false)
                        {
                            if (!array_key_exists("error", $sourceText))
                            {
                                $data = $sourceText;

                                $data["comments"] = $this->getComments(
                                    $data["event"][0]->eventID,
                                    $data["event"][0]->currentChapter);

                                $translationData = $this->_translationModel->getEventTranslationByEventID(
                                    $data["event"][0]->eventID,
                                    $data["event"][0]->currentChapter
                                );
                                $translation = array();

                                foreach ($translationData as $tv)
                                {
                                    $arr = (array)json_decode($tv->translatedVerses, true);
                                    $arr["tID"] = $tv->tID;
                                    $translation[] = $arr;
                                }
                                $data["translation"] = $translation;
                            }
                            else
                            {
                                $error[] = $sourceText["error"];
                                $data["error"] = $sourceText["error"];
                            }
                        }
                        else
                        {
                            Url::redirect('events');
                        }

                        if (isset($_POST) && !empty($_POST))
                        {
                            if (isset($_POST["confirm_step"]))
                            {
                                $post = Gump::xss_clean($_POST);

                                if(is_array($post["chunks"]) && !empty($post["chunks"]))
                                {
                                    if(!empty($translation))
                                    {
                                        array_walk_recursive($post["chunks"], function(&$item) {
                                            $item = trim($item);
                                        });
                                        $post["chunks"] = array_filter($post["chunks"], function($chunk) {
                                            $verses = array_filter($chunk, function($v) {
                                                return !empty(strip_tags(trim($v)));
                                            });
                                            $isEqual = sizeof($chunk) == sizeof($verses);
                                            return !empty($chunk) && $isEqual;
                                        });

                                        if(sizeof($translation) == sizeof($post["chunks"]))
                                        {
                                            foreach ($translation as $key => $chunk) {
                                                if(!isset($post["chunks"][$key])) continue;

                                                $shouldUpdate = false;
                                                foreach ($post["chunks"][$key] as $verse => $vText)
                                                {
                                                    if(!isset($chunk[EventMembers::L2_CHECKER]["verses"][$verse])
                                                        || $chunk[EventMembers::L2_CHECKER]["verses"][$verse] != $vText)
                                                    {
                                                        $shouldUpdate = true;
                                                    }
                                                }

                                                $translation[$key][EventMembers::L2_CHECKER]["verses"] = $post["chunks"][$key];

                                                if($shouldUpdate)
                                                {
                                                    $tID = $translation[$key]["tID"];
                                                    unset($translation[$key]["tID"]);

                                                    $encoded = json_encode($translation[$key]);
                                                    $json_error = json_last_error();
                                                    if($json_error === JSON_ERROR_NONE)
                                                    {
                                                        $trData = array(
                                                            "translatedVerses"  => $encoded
                                                        );
                                                        $upd = $this->_translationModel->updateTranslation(
                                                            $trData,
                                                            array(
                                                                "tID" => $tID));

                                                        if(!is_numeric($upd))
                                                        {
                                                            $error[] = __("error_occurred", array($upd));
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                        else
                                        {
                                            $error[] = __("empty_draft_verses_error");
                                        }
                                    }
                                }

                                if(!isset($error))
                                {
                                    $sndCheck = (array)json_decode($data["event"][0]->sndCheck, true);
                                    if(array_key_exists($data["event"][0]->currentChapter, $sndCheck))
                                    {
                                        $sndCheck[$data["event"][0]->currentChapter]["done"] = 1;
                                    }

                                    $postdata = [
                                        "sndCheck" => json_encode($sndCheck)
                                    ];

                                    setcookie("temp_tutorial", false, time() - 24*3600, "/");
                                    $this->_model->updateL2Checker($postdata, [
                                        "l2chID" => $data["event"][0]->l2chID
                                    ]);
                                    Url::redirect('events/checker-l2/' . $data["event"][0]->eventID .
                                        "/".$data["event"][0]->memberID."/".$data["event"][0]->currentChapter);
                                }
                            }
                        }

                        return View::make('Events/L2/Checker')
                            ->nest('page', 'Events/L2/SndCheck')
                            ->shares("title", $title)
                            ->shares("data", $data)
                            ->shares("error", @$error);
                        break;

                    case EventCheckSteps::KEYWORD_CHECK_L2:
                        $sourceText = $this->getSourceText($data);

                        if($sourceText !== false)
                        {
                            if (!array_key_exists("error", $sourceText))
                            {
                                $data = $sourceText;

                                $data["comments"] = $this->getComments(
                                    $data["event"][0]->eventID,
                                    $data["event"][0]->currentChapter);

                                $translationData = $this->_translationModel->getEventTranslationByEventID(
                                    $data["event"][0]->eventID,
                                    $data["event"][0]->currentChapter
                                );
                                $translation = array();

                                foreach ($translationData as $tv)
                                {
                                    $arr = (array)json_decode($tv->translatedVerses, true);
                                    $arr["tID"] = $tv->tID;
                                    $translation[] = $arr;
                                }
                                $data["translation"] = $translation;
                            }
                            else
                            {
                                $error[] = $sourceText["error"];
                                $data["error"] = $sourceText["error"];
                            }
                        }
                        else
                        {
                            Url::redirect('events');
                        }

                        if (isset($_POST) && !empty($_POST))
                        {
                            if (isset($_POST["confirm_step"]))
                            {
                                $sndCheck = (array)json_decode($data["event"][0]->sndCheck, true);
                                if(array_key_exists($data["event"][0]->currentChapter, $sndCheck))
                                {
                                    $sndCheck[$data["event"][0]->currentChapter]["done"] = 2;
                                }
                                $peer1Check = (array)json_decode($data["event"][0]->peer1Check, true);
                                $peer2Check = (array)json_decode($data["event"][0]->peer2Check, true);
                                if(!array_key_exists($data["event"][0]->currentChapter, $peer1Check))
                                {
                                    $peer1Check[$data["event"][0]->currentChapter] = [
                                        "memberID" => 0,
                                        "done" => 0
                                    ];
                                    $peer2Check[$data["event"][0]->currentChapter] = [
                                        "memberID" => 0,
                                        "done" => 0
                                    ];
                                }

                                $postdata = [
                                    "sndCheck" => json_encode($sndCheck),
                                    "peer1Check" => json_encode($peer1Check),
                                    "peer2Check" => json_encode($peer2Check),
                                ];

                                setcookie("temp_tutorial", false, time() - 24*3600, "/");
                                $this->_model->updateL2Checker($postdata, [
                                    "l2chID" => $data["event"][0]->l2chID
                                ]);
                                Url::redirect('events/');
                            }
                        }

                        return View::make('Events/L2/Checker')
                            ->nest('page', 'Events/L2/KeywordCheck')
                            ->shares("title", $title)
                            ->shares("data", $data)
                            ->shares("error", @$error);
                        break;

                    case EventCheckSteps::PEER_REVIEW_L2:
                        $sourceText = $this->getSourceText($data);

                        if($sourceText !== false)
                        {
                            if (!array_key_exists("error", $sourceText))
                            {
                                $data = $sourceText;

                                $data["comments"] = $this->getComments(
                                    $data["event"][0]->eventID,
                                    $data["event"][0]->currentChapter);

                                $translationData = $this->_translationModel->getEventTranslationByEventID(
                                    $data["event"][0]->eventID,
                                    $data["event"][0]->currentChapter
                                );
                                $translation = array();

                                foreach ($translationData as $tv)
                                {
                                    $arr = (array)json_decode($tv->translatedVerses, true);
                                    $arr["tID"] = $tv->tID;
                                    $translation[] = $arr;
                                }
                                $data["translation"] = $translation;

                                if($data["event"][0]->peer == 2)
                                    $data["isCheckerPage"] = true;
                            }
                            else
                            {
                                $error[] = $sourceText["error"];
                                $data["error"] = $sourceText["error"];
                            }
                        }
                        else
                        {
                            Url::redirect('events');
                        }

                        if (isset($_POST) && !empty($_POST))
                        {
                            if (isset($_POST["confirm_step"]))
                            {
                                $peer1Check = (array)json_decode($data["event"][0]->peer1Check, true);
                                $peer2Check = (array)json_decode($data["event"][0]->peer2Check, true);

                                if(!isset($error))
                                {
                                    if($data["event"][0]->peer == 1)
                                    {
                                        if(array_key_exists($data["event"][0]->currentChapter, $peer2Check)
                                            && $peer2Check[$data["event"][0]->currentChapter]["done"] == 1)
                                        {
                                            $peer1Check[$data["event"][0]->currentChapter]["done"] = 1;
                                            $postdata = [
                                                "peer1Check" => json_encode($peer1Check)
                                            ];

                                            // Check if the whole book was checked and set its state to L2_CHECKED
                                            $chapters = [];
                                            $events = $this->_model->getMembersForL2Event($data["event"][0]->eventID);

                                            foreach ($events as $event)
                                            {
                                                $peer2 = (array)json_decode($event["peer2Check"], true);
                                                if(!empty($peer2))
                                                {
                                                    $chapters += $peer2;
                                                }
                                            }

                                            if(sizeof($chapters) == $data["event"][0]->chaptersNum)
                                            {
                                                $allDone = true;
                                                foreach ($chapters as $chapter)
                                                {
                                                    if($chapter["done"] == 0)
                                                    {
                                                        $allDone = false;
                                                        break;
                                                    }
                                                }

                                                if($allDone)
                                                {
                                                    $this->_model->updateEvent([
                                                        "state" => EventStates::L2_CHECKED
                                                    ], [
                                                        "eventID" => $data["event"][0]->eventID
                                                    ]);
                                                }
                                            }
                                        }
                                        else
                                        {
                                            $error[] = __("checker_not_ready_error");
                                        }
                                    }
                                    else
                                    {
                                        $keywords = $this->_translationModel->getKeywords([
                                            "eventID" => $data["event"][0]->eventID,
                                            "chapter" => $data["event"][0]->currentChapter
                                        ]);

                                        if(empty($keywords))
                                        {
                                            $peer2Check[$data["event"][0]->currentChapter]["done"] = 1;
                                            $postdata = [
                                                "peer2Check" => json_encode($peer2Check)
                                            ];
                                        }
                                        else
                                        {
                                            $error[] = __("keywords_still_exist_error");
                                        }
                                    }

                                    if(!isset($error))
                                    {
                                        setcookie("temp_tutorial", false, time() - 24*3600, "/");
                                        $this->_model->updateL2Checker($postdata, [
                                            "l2chID" => $data["event"][0]->l2chID
                                        ]);

                                        if(!$isAjax)
                                        {
                                            Url::redirect('events/');
                                        }
                                        else
                                        {
                                            $response["success"] = true;
                                            echo json_encode($response);
                                            exit;
                                        }
                                    }
                                    else
                                    {
                                        if($isAjax)
                                        {
                                            $response["errors"] = $error;
                                            echo json_encode($response);
                                            exit;
                                        }
                                    }
                                }
                            }
                        }

                        return View::make('Events/L2/Checker')
                            ->nest('page', 'Events/L2/PeerCheck')
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

                return View::make('Events/L2/Checker')
                    ->shares("title", $title)
                    ->shares("data", $data)
                    ->shares("error", @$error);
            }
        }
        else
        {
            $error[] = __("not_in_event_error");
            $title = "Error";

            return View::make('Events/L2/Checker')
                ->shares("title", $title)
                ->shares("data", $data)
                ->shares("error", @$error);
        }
    }

    public function checkerL3($eventID)
    {
        $data["menu"] = 1;

        echo $eventID;
    }


    public function information($eventID)
    {
        $data["menu"] = 1;
        $data["event"] = $this->_model->getEventMember($eventID, Session::get("memberID"), true);
        $data["isAdmin"] = false;
        $canViewInfo = false;
        $isAjax = false;

        if(!empty($_SERVER['HTTP_X_REQUESTED_WITH'])
                && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')
        {
            $isAjax = true;
            $response = ["success" => false];
        }

        if(!empty($data["event"]))
        {
            $admins = (array)json_decode($data["event"][0]->admins, true);

            if($data["event"][0]->translator === null && $data["event"][0]->checker === null
                && $data["event"][0]->checker_l2 === null && $data["event"][0]->checker_l3 === null)
            {
                // If member is not a participant of the event, check if he is a facilitator
                if(Session::get("isAdmin"))
                {
                    $data["isAdmin"] = $canViewInfo = in_array(Session::get("memberID"), $admins);

                    if(!$data["isAdmin"])
                    {
                        if(Session::get("isSuperAdmin")) // Or superadmin
                        {
                            $data["isAdmin"] = $canViewInfo = true;
                        }
                        else
                        {
                            if(!$isAjax)
                                $error[] = __("empty_or_not_permitted_event_error");
                            else
                            {
                                $response["errorType"] = "empty_no_permission";
                                echo json_encode($response);
                                exit;
                            }
                        }
                    }
                }
                else
                {
                    if(!$isAjax)
                        $error[] = __("empty_or_not_permitted_event_error");
                    else
                    {
                        $response["errorType"] = "empty_no_permission";
                        echo json_encode($response);
                        exit;
                    }
                }
            }
            else
            {
                $canViewInfo = true;
                if(Session::get("isAdmin"))
                {
                    $data["isAdmin"] = in_array(Session::get("memberID"), $admins)
                        || Session::get("isSuperAdmin");
                }
            }

            if($data["event"][0]->state == "started" && $canViewInfo)
            {
                if(!$isAjax)
                    $error[] = __("not_started_event_error", array($data["event"][0]->eventID));
                else
                {
                    $response["errorType"] = "not_started";
                    echo json_encode($response);
                    exit;
                }
            }
        }
        else
        {
            if(!$isAjax)
                $error[] = __("empty_or_not_permitted_event_error");
            else
            {
                $response["errorType"] = "empty_no_permission";
                echo json_encode($response);
                exit;
            }
        }

        if(!isset($error))
        {
            $data["chapters"] = [];
            for($i=1; $i <= $data["event"][0]->chaptersNum; $i++)
            {
                $data["chapters"][$i] = [];
            }

            $chapters = $this->_model->getChapters($data["event"][0]->eventID);

            foreach ($chapters as $chapter) {
                $tmp["trID"] = $chapter["trID"];
                $tmp["memberID"] = $chapter["memberID"];
                $tmp["chunks"] = json_decode($chapter["chunks"], true);
                $tmp["done"] = $chapter["done"];

                $data["chapters"][$chapter["chapter"]] = $tmp;
            }

            $members = [];
            $overallProgress = 0;

            $chunks = $this->_translationModel->getTranslationByEventID($data["event"][0]->eventID);

            $memberSteps = [];

            foreach ($chunks as $chunk) {
                if(!array_key_exists($chunk->memberID, $memberSteps))
                {
                    $memberSteps[$chunk->memberID]["step"] = $chunk->step;
                    $memberSteps[$chunk->memberID]["verbCheck"] = $chunk->verbCheck;
                    $memberSteps[$chunk->memberID]["peerCheck"] = $chunk->peerCheck;
                    $memberSteps[$chunk->memberID]["kwCheck"] = $chunk->kwCheck;
                    $memberSteps[$chunk->memberID]["crCheck"] = $chunk->crCheck;
                    $memberSteps[$chunk->memberID]["currentChapter"] = $chunk->currentChapter;
                    $memberSteps[$chunk->memberID]["checkerID"] = $chunk->checkerID;
                    $members[$chunk->memberID] = "";
                }

                if($chunk->chapter == null)
                    continue;

                $data["chapters"][$chunk->chapter]["chunksData"][] = $chunk;

                if(!isset($data["chapters"][$chunk->chapter]["lastEdit"]))
                {
                    $data["chapters"][$chunk->chapter]["lastEdit"] = $chunk->dateUpdate;
                }
                else
                {
                    $prevDate = strtotime($data["chapters"][$chunk->chapter]["lastEdit"]);
                    if($prevDate < strtotime($chunk->dateUpdate))
                        $data["chapters"][$chunk->chapter]["lastEdit"] = $chunk->dateUpdate;
                }
            }

            foreach ($data["chapters"] as $key => $chapter) {
                if(empty($chapter)) continue;

                $currentStep = EventSteps::PRAY;
                $consumeState = StepsStates::NOT_STARTED;
                $verbCheckState = StepsStates::NOT_STARTED;
                $chunkingState = StepsStates::NOT_STARTED;
                $blindDraftState = StepsStates::NOT_STARTED;

                $members[$chapter["memberID"]] = "";
                $data["chapters"][$key]["progress"] = 0;

                $currentChapter = $memberSteps[$chapter["memberID"]]["currentChapter"];
                $verbCheck = (array)json_decode($memberSteps[$chapter["memberID"]]["verbCheck"], true);
                $peerCheck = (array)json_decode($memberSteps[$chapter["memberID"]]["peerCheck"], true);
                $kwCheck = (array)json_decode($memberSteps[$chapter["memberID"]]["kwCheck"], true);
                $crCheck = (array)json_decode($memberSteps[$chapter["memberID"]]["crCheck"], true);
                $currentChecker = $memberSteps[$chapter["memberID"]]["checkerID"];

                // Set default values
                $data["chapters"][$key]["consume"]["state"] = StepsStates::NOT_STARTED;
                $data["chapters"][$key]["verb"]["state"] = StepsStates::NOT_STARTED;
                $data["chapters"][$key]["verb"]["checkerID"] = "na";
                $data["chapters"][$key]["chunking"]["state"] = StepsStates::NOT_STARTED;
                $data["chapters"][$key]["blindDraft"]["state"] = StepsStates::NOT_STARTED;
                $data["chapters"][$key]["selfEdit"]["state"] = StepsStates::NOT_STARTED;
                $data["chapters"][$key]["peer"]["state"] = StepsStates::NOT_STARTED;
                $data["chapters"][$key]["peer"]["checkerID"] = "na";
                $data["chapters"][$key]["kwc"]["state"] = StepsStates::NOT_STARTED;
                $data["chapters"][$key]["kwc"]["checkerID"] = "na";
                $data["chapters"][$key]["crc"]["state"] = StepsStates::NOT_STARTED;
                $data["chapters"][$key]["crc"]["checkerID"] = "na";
                $data["chapters"][$key]["finalReview"]["state"] = StepsStates::NOT_STARTED;

                // When no chunks created or translation not started
                if(empty($chapter["chunks"]) || !isset($chapter["chunksData"]))
                {
                    if($currentChapter == $key)
                    {
                        $currentStep = $memberSteps[$chapter["memberID"]]["step"];
                        if($currentChecker > 0)
                        {
                            $verbCheckState = StepsStates::IN_PROGRESS;
                            $consumeState = StepsStates::FINISHED;
                            $currentChecker = $memberSteps[$chapter["memberID"]]["checkerID"];
                            $members[$currentChecker] = "";
                        }
                        elseif(array_key_exists($key, $verbCheck))
                        {
                            $consumeState = StepsStates::FINISHED;
                            if(is_numeric($verbCheck[$key]))
                            {
                                $members[$verbCheck[$key]] = "";
                            }
                            else
                            {
                                $uniqID = uniqid("chk");
                                $members[$uniqID] = $verbCheck[$key];
                                $verbCheck[$key] = $uniqID;
                                $verbChecker = $uniqID;
                            }

                            if($currentStep == EventSteps::CHUNKING)
                            {
                                $verbCheckState = StepsStates::FINISHED;
                                $chunkingState = StepsStates::IN_PROGRESS;
                            }
                            elseif($currentStep == EventSteps::READ_CHUNK || $currentStep == EventSteps::BLIND_DRAFT)
                            {
                                $verbCheckState = StepsStates::FINISHED;
                                $chunkingState = StepsStates::FINISHED;
                                $blindDraftState = StepsStates::IN_PROGRESS;
                            }
                            else
                            {
                                $verbCheckState = StepsStates::CHECKED;
                            }
                        }
                        elseif($currentStep == EventSteps::VERBALIZE)
                        {
                            $verbCheckState = StepsStates::WAITING;
                            $consumeState = StepsStates::FINISHED;
                        }
                        elseif($currentStep == EventSteps::CONSUME)
                        {
                            $consumeState = StepsStates::IN_PROGRESS;
                        }
                    }
                    else
                    {
                        $currentChecker = 0;
                    }

                    $data["chapters"][$key]["step"] = $currentStep;
                    $data["chapters"][$key]["consume"]["state"] = $consumeState;
                    $data["chapters"][$key]["verb"]["state"] = $verbCheckState;
                    $data["chapters"][$key]["verb"]["checkerID"] = isset($verbChecker) ? $verbChecker : ($currentChecker > 0 ? $currentChecker : "na");
                    $data["chapters"][$key]["chunking"]["state"] = $chunkingState;
                    $data["chapters"][$key]["blindDraft"]["state"] = $blindDraftState;

                    // Pregress checks
                    if($data["chapters"][$key]["consume"]["state"] == StepsStates::FINISHED)
                        $data["chapters"][$key]["progress"] += 11;
                    if($data["chapters"][$key]["verb"]["state"] == StepsStates::CHECKED)
                        $data["chapters"][$key]["progress"] += 6;
                    if($data["chapters"][$key]["verb"]["state"] == StepsStates::FINISHED)
                        $data["chapters"][$key]["progress"] += 11;
                    if($data["chapters"][$key]["chunking"]["state"] == StepsStates::FINISHED)
                        $data["chapters"][$key]["progress"] += 11;

                    $data["chapters"][$key]["chunksData"] = [];
                    continue;
                }

                $currentStep = $memberSteps[$chapter["memberID"]]["step"];

                // Total translated chunks are 25% of all chapter progress
                $data["chapters"][$key]["progress"] += sizeof($chapter["chunksData"]) * 11 / sizeof($chapter["chunks"]);
                $data["chapters"][$key]["step"] = $currentChapter == $key ? $currentStep : EventSteps::FINISHED;

                // These steps are finished here by default
                $data["chapters"][$key]["consume"]["state"] = StepsStates::FINISHED;
                $data["chapters"][$key]["chunking"]["state"] = StepsStates::FINISHED;

                // Verbalize Check
                if(array_key_exists($key, $verbCheck))
                {
                    $data["chapters"][$key]["verb"]["state"] = StepsStates::FINISHED;

                    if(!is_numeric($verbCheck[$key]))
                    {
                        $uniqID = uniqid("chk");
                        $members[$uniqID] = $verbCheck[$key];
                        $verbCheck[$key] = $uniqID;
                        $data["chapters"][$key]["verb"]["checkerID"] = $verbCheck[$key];
                    }
                    else
                    {
                        $data["chapters"][$key]["verb"]["checkerID"] = $verbCheck[$key];
                        $members[$verbCheck[$key]] = "";
                    }
                }

                // Peer Check
                if(array_key_exists($key, $peerCheck))
                {
                    // These steps are finished here by default
                    $data["chapters"][$key]["blindDraft"]["state"] = StepsStates::FINISHED;
                    $data["chapters"][$key]["selfEdit"]["state"] = StepsStates::FINISHED;

                    if($key == $currentChapter && $currentStep == EventSteps::PEER_REVIEW)
                        $data["chapters"][$key]["peer"]["state"] = StepsStates::CHECKED;
                    else
                        $data["chapters"][$key]["peer"]["state"] = StepsStates::FINISHED;

                    $data["chapters"][$key]["peer"]["checkerID"] = $peerCheck[$key];
                    $members[$peerCheck[$key]] = "";

                    // Add 25% of progress when peer check done
                    //$data["chapters"][$key]["progress"] += 25;
                }
                else
                {
                    if($key == $currentChapter)
                    {
                        if($currentStep == EventSteps::PEER_REVIEW)
                        {
                            // These steps are finished here by default
                            $data["chapters"][$key]["blindDraft"]["state"] = StepsStates::FINISHED;
                            $data["chapters"][$key]["selfEdit"]["state"] = StepsStates::FINISHED;

                            if($currentChecker > 0)
                            {
                                $data["chapters"][$key]["peer"]["state"] = StepsStates::IN_PROGRESS;
                                $data["chapters"][$key]["peer"]["checkerID"] = $currentChecker;
                                $members[$currentChecker] = "";
                            }
                            else
                            {
                                $data["chapters"][$key]["peer"]["state"] = StepsStates::WAITING;
                                $data["chapters"][$key]["peer"]["checkerID"] = "na";
                            }
                        }
                        else
                        {
                            if($currentStep == EventSteps::SELF_CHECK)
                            {
                                $data["chapters"][$key]["blindDraft"]["state"] = StepsStates::FINISHED;
                                $data["chapters"][$key]["selfEdit"]["state"] = StepsStates::IN_PROGRESS;
                            }
                            else
                            {
                                $data["chapters"][$key]["blindDraft"]["state"] = StepsStates::IN_PROGRESS;
                            }
                        }
                    }
                }


                // Keyword Check
                if(array_key_exists($key, $kwCheck))
                {
                    if($key == $currentChapter && $currentStep == EventSteps::KEYWORD_CHECK)
                        $data["chapters"][$key]["kwc"]["state"] = StepsStates::CHECKED;
                    else
                        $data["chapters"][$key]["kwc"]["state"] = StepsStates::FINISHED;

                    $data["chapters"][$key]["kwc"]["checkerID"] = $kwCheck[$key];
                    $members[$kwCheck[$key]] = "";

                    // Add 25% of progress when keyword check done
                    //$data["chapters"][$key]["progress"] += 25;
                }
                else
                {
                    if($key == $currentChapter)
                    {
                        if($currentStep == EventSteps::KEYWORD_CHECK)
                        {
                            if($currentChecker > 0)
                            {
                                $data["chapters"][$key]["kwc"]["state"] = StepsStates::IN_PROGRESS;
                                $data["chapters"][$key]["kwc"]["checkerID"] = $currentChecker;
                                $members[$currentChecker] = "";
                            }
                            else
                            {
                                $data["chapters"][$key]["kwc"]["state"] = StepsStates::WAITING;
                                $data["chapters"][$key]["kwc"]["checkerID"] = "na";
                            }
                        }
                    }
                }


                // Content Review (Verse by Verse) Check
                if(array_key_exists($key, $crCheck))
                {
                    if($key == $currentChapter)
                    {
                        if($currentStep == EventSteps::CONTENT_REVIEW)
                            $data["chapters"][$key]["crc"]["state"] = StepsStates::CHECKED;
                        else
                        {
                            $data["chapters"][$key]["crc"]["state"] = StepsStates::FINISHED;
                            $data["chapters"][$key]["finalReview"]["state"] = StepsStates::IN_PROGRESS;
                        }
                    }
                    else
                    {
                        $data["chapters"][$key]["crc"]["state"] = StepsStates::FINISHED;
                        $data["chapters"][$key]["finalReview"]["state"] = StepsStates::FINISHED;
                    }

                    $data["chapters"][$key]["crc"]["checkerID"] = $crCheck[$key];
                    $data["chapters"][$key]["step"] = $key == $currentChapter
                        ? $currentStep : EventSteps::FINISHED;
                    $members[$crCheck[$key]] = "";

                    // Add 25% of progress when verse by verse check done
                    //$data["chapters"][$key]["progress"] += 25;
                }
                else
                {
                    if($key == $currentChapter)
                    {
                        if($currentStep == EventSteps::CONTENT_REVIEW)
                        {
                            if($currentChecker > 0)
                            {
                                $data["chapters"][$key]["crc"]["state"] = StepsStates::IN_PROGRESS;
                                $data["chapters"][$key]["crc"]["checkerID"] = $currentChecker;
                                $members[$currentChecker] = "";
                            }
                            else
                            {
                                $data["chapters"][$key]["crc"]["state"] = StepsStates::WAITING;
                                $data["chapters"][$key]["crc"]["checkerID"] = "na";
                            }
                        }
                    }
                }

                // Pregress checks
                if($data["chapters"][$key]["consume"]["state"] == StepsStates::FINISHED)
                    $data["chapters"][$key]["progress"] += 11;
                if($data["chapters"][$key]["verb"]["state"] == StepsStates::FINISHED)
                    $data["chapters"][$key]["progress"] += 11;
                if($data["chapters"][$key]["chunking"]["state"] == StepsStates::FINISHED)
                    $data["chapters"][$key]["progress"] += 11;
                if($data["chapters"][$key]["selfEdit"]["state"] == StepsStates::FINISHED)
                    $data["chapters"][$key]["progress"] += 11;
                if($data["chapters"][$key]["peer"]["state"] == StepsStates::CHECKED)
                    $data["chapters"][$key]["progress"] += 6;
                if($data["chapters"][$key]["peer"]["state"] == StepsStates::FINISHED)
                    $data["chapters"][$key]["progress"] += 11;
                if($data["chapters"][$key]["kwc"]["state"] == StepsStates::CHECKED)
                    $data["chapters"][$key]["progress"] += 6;
                if($data["chapters"][$key]["kwc"]["state"] == StepsStates::FINISHED)
                    $data["chapters"][$key]["progress"] += 11;
                if($data["chapters"][$key]["crc"]["state"] == StepsStates::CHECKED)
                    $data["chapters"][$key]["progress"] += 6;
                if($data["chapters"][$key]["crc"]["state"] == StepsStates::FINISHED)
                    $data["chapters"][$key]["progress"] += 11;
                if($data["chapters"][$key]["finalReview"]["state"] == StepsStates::FINISHED)
                    $data["chapters"][$key]["progress"] += 12;

                $overallProgress += $data["chapters"][$key]["progress"];
            }

            $data["overall_progress"] = $overallProgress / sizeof($data["chapters"]);

            $empty = array_fill(0, sizeof($admins), "");
            $adminsArr = array_combine($admins, $empty);

            $members += $adminsArr;
            $membersArray = (array)$this->_membersModel->getMembers(array_filter(array_keys($members)));

            foreach ($membersArray as $member) {
                $members[$member->memberID]["userName"] = $member->userName;
                $members[$member->memberID]["name"] = $member->firstName . " " . mb_substr($member->lastName, 0, 1).".";
                $members[$member->memberID]["avatar"] = $member->avatar;
            }

            foreach ($members as $key => $member) {
                if(!is_numeric($key) && $key != "na")
                {
                    $name = $members[$key];
                    $members[$key] = [];
                    $members[$key]["userName"] = $key;
                    $members[$key]["name"] = $name;
                    $members[$key]["avatar"] = "n1";
                }
            }

            $members["na"] = __("not_available");
            $members = array_filter($members);

            $data["admins"] = $admins;
            $data["members"] = $members;
        }

        $data["notifications"] = $this->_notifications;

        if(!$isAjax)
        {
            return View::make('Events/Information')
                ->shares("title", __("event_info"))
                ->shares("data", $data)
                ->shares("error", @$error);
        }
        else
        {
            $this->layout = "dummy";
            $response["success"] = true;
            $response["progress"] = $data["overall_progress"];
            $response["admins"] = $data["admins"];
            $response["members"] = $data["members"];
            $response["html"] = View::make("Events/GetInfo")
                ->shares("data", $data)
                ->renderContents();

            echo json_encode($response);
        }
    }

    public function informationNotes($eventID)
    {
        $data["menu"] = 1;
        $data["event"] = $this->_model->getEventMember($eventID, Session::get("memberID"), true);
        $data["isAdmin"] = false;
        $canViewInfo = false;
        $isAjax = false;

        if(!empty($_SERVER['HTTP_X_REQUESTED_WITH'])
                && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')
        {
            $isAjax = true;
            $response = ["success" => false];
        }

        if(!empty($data["event"]))
        {
            $admins = (array)json_decode($data["event"][0]->admins, true);

            if($data["event"][0]->translator === null && $data["event"][0]->checker === null
                && $data["event"][0]->checker_l2 === null && $data["event"][0]->checker_l3 === null)
            {
                // If member is not a participant of the event, check if he is a facilitator
                if(Session::get("isAdmin"))
                {
                    $data["isAdmin"] = $canViewInfo = in_array(Session::get("memberID"), $admins);

                    if(!$data["isAdmin"])
                    {
                        if(Session::get("isSuperAdmin")) // Or superadmin
                        {
                            $data["isAdmin"] = $canViewInfo = true;
                        }
                        else
                        {
                            if(!$isAjax)
                                $error[] = __("empty_or_not_permitted_event_error");
                            else
                            {
                                $response["errorType"] = "empty_no_permission";
                                echo json_encode($response);
                                exit;
                            }
                        }
                    }
                }
                else
                {
                    if(!$isAjax)
                        $error[] = __("empty_or_not_permitted_event_error");
                    else
                    {
                        $response["errorType"] = "empty_no_permission";
                        echo json_encode($response);
                        exit;
                    }
                }
            }
            else
            {
                $canViewInfo = true;
                if(Session::get("isAdmin"))
                {
                    $data["isAdmin"] = in_array(Session::get("memberID"), $admins)
                        || Session::get("isSuperAdmin");
                }
            }

            if($data["event"][0]->state == "started" && $canViewInfo)
            {
                if(!$isAjax)
                    $error[] = __("not_started_event_error", array($data["event"][0]->eventID));
                else
                {
                    $response["errorType"] = "not_started";
                    echo json_encode($response);
                    exit;
                }
            }
        }
        else
        {
            if(!$isAjax)
                $error[] = __("empty_or_not_permitted_event_error");
            else
            {
                $response["errorType"] = "empty_no_permission";
                echo json_encode($response);
                exit;
            }
        }
        
        if(!isset($error))
        {
            $data["chapters"] = [];
            for($i=0; $i <= $data["event"][0]->chaptersNum; $i++)
            {
                $data["chapters"][$i] = [];
            }
        
            $chapters = $this->_model->getChapters($data["event"][0]->eventID);
        
            foreach ($chapters as $chapter) {
                $tmp["trID"] = $chapter["trID"];
                $tmp["memberID"] = $chapter["memberID"];
                $tmp["chunks"] = json_decode($chapter["chunks"], true);
                $tmp["done"] = $chapter["done"];
        
                $data["chapters"][$chapter["chapter"]] = $tmp;
            }
        
            $members = [];
            $overallProgress = 0;

            $chunks = $this->_translationModel->getTranslationByEventID($data["event"][0]->eventID);
            
            $memberSteps = [];

            foreach ($chunks as $chunk) {
                if(!array_key_exists($chunk->memberID, $memberSteps))
                {
                    $memberSteps[$chunk->memberID]["step"] = $chunk->step;
                    $memberSteps[$chunk->memberID]["otherCheck"] = $chunk->otherCheck;
                    $memberSteps[$chunk->memberID]["currentChapter"] = $chunk->currentChapter;
                    $members[$chunk->memberID] = "";
                }
        
                if($chunk->chapter == null)
                    continue;
        
                $data["chapters"][$chunk->chapter]["chunksData"][] = $chunk;
        
                if(!isset($data["chapters"][$chunk->chapter]["lastEdit"]))
                {
                    $data["chapters"][$chunk->chapter]["lastEdit"] = $chunk->dateUpdate;
                }
                else
                {
                    $prevDate = strtotime($data["chapters"][$chunk->chapter]["lastEdit"]);
                    if($prevDate < strtotime($chunk->dateUpdate))
                        $data["chapters"][$chunk->chapter]["lastEdit"] = $chunk->dateUpdate;
                }
            }
            
            foreach ($data["chapters"] as $key => $chapter) {
                if(empty($chapter)) continue;
        
                $currentStep = EventSteps::PRAY;
                $consumeState = StepsStates::NOT_STARTED;
                $blindDraftState = StepsStates::NOT_STARTED;
        
                $members[$chapter["memberID"]] = "";
                $data["chapters"][$key]["progress"] = 0;
        
                $currentChapter = $memberSteps[$chapter["memberID"]]["currentChapter"];
                $otherCheck = (array)json_decode($memberSteps[$chapter["memberID"]]["otherCheck"], true);
        
                // Set default values
                $data["chapters"][$key]["consume"]["state"] = StepsStates::NOT_STARTED;
                $data["chapters"][$key]["blindDraft"]["state"] = StepsStates::NOT_STARTED;
                $data["chapters"][$key]["selfEdit"]["state"] = StepsStates::NOT_STARTED;

                $data["chapters"][$key]["consumeChk"]["state"] = StepsStates::NOT_STARTED;
                $data["chapters"][$key]["highlightChk"]["state"] = StepsStates::NOT_STARTED;
                $data["chapters"][$key]["selfEditChk"]["state"] = StepsStates::NOT_STARTED;
                $data["chapters"][$key]["kwc"]["state"] = StepsStates::NOT_STARTED;
                $data["chapters"][$key]["peerChk"]["state"] = StepsStates::NOT_STARTED;
                $data["chapters"][$key]["peerChk"]["checkerID"] = 'na';
                $data["chapters"][$key]["stepChk"] = EventSteps::PRAY;
                
                // When no chunks created or translation not started
                if(empty($chapter["chunks"]) || !isset($chapter["chunksData"]))
                {
                    if($currentChapter == $key)
                    {
                        $currentStep = $memberSteps[$chapter["memberID"]]["step"];
                        
                        if($currentStep == EventSteps::CONSUME)
                        {
                            $consumeState = StepsStates::IN_PROGRESS;
                        }
                        else if($currentStep == EventSteps::READ_CHUNK ||
                            $currentStep == EventSteps::BLIND_DRAFT)
                        {
                            $consumeState = StepsStates::FINISHED;
                            $blindDraftState = StepsStates::IN_PROGRESS;
                        }
                        else if($currentStep == EventSteps::SELF_CHECK)
                        {
                            $consumeState = StepsStates::FINISHED;
                            $blindDraftState = StepsStates::FINISHED;
                        }
                    }
        
                    $data["chapters"][$key]["step"] = $currentStep;
                    $data["chapters"][$key]["consume"]["state"] = $consumeState;
                    $data["chapters"][$key]["blindDraft"]["state"] = $blindDraftState;
        
                    // Progress checks
                    if($data["chapters"][$key]["consume"]["state"] == StepsStates::FINISHED)
                        $data["chapters"][$key]["progress"] += 17;
        
                    $data["chapters"][$key]["chunksData"] = [];
                    continue;
                }
        
                $currentStep = $memberSteps[$chapter["memberID"]]["step"];
        
                // Total translated chunks are 25% of all chapter progress
                $data["chapters"][$key]["progress"] += sizeof($chapter["chunksData"]) * 17 / sizeof($chapter["chunks"]);
                $data["chapters"][$key]["step"] = $currentChapter == $key ? $currentStep : EventSteps::FINISHED;

                // These steps are finished here by default
                $data["chapters"][$key]["consume"]["state"] = StepsStates::FINISHED;
        
                if($currentChapter == $key)
                {
                    if($currentStep == EventSteps::READ_CHUNK
                        || $currentStep == EventSteps::BLIND_DRAFT)
                    {
                        $data["chapters"][$key]["blindDraft"]["state"] = StepsStates::IN_PROGRESS;
                    }
                    else if($currentStep == EventSteps::SELF_CHECK)
                    {
                        $data["chapters"][$key]["blindDraft"]["state"] = StepsStates::FINISHED;
                        $data["chapters"][$key]["selfEdit"]["state"] = StepsStates::IN_PROGRESS;
                    }
                }

                // TranslationNotes Checking stage
                if(array_key_exists($key, $otherCheck))
                {
                    $data["chapters"][$key]["blindDraft"]["state"] = StepsStates::FINISHED;
                    $data["chapters"][$key]["selfEdit"]["state"] = StepsStates::FINISHED;
                    $data["chapters"][$key]["step"] = EventSteps::FINISHED;
                    
                    if($otherCheck[$key]["checkerID"] > 0)
                    {
                        $checker = $this->_model->getEventMember($eventID, $otherCheck[$key]["checkerID"]);

                        if(!empty($checker))
                        {
                            $peerCheck = (array)json_decode($checker[0]->peerCheck, true);
                            $data["chapters"][$key]["checkerID"] = $otherCheck[$key]["checkerID"];
                            
                            if($otherCheck[$key]["done"])
                            {
                                $data["chapters"][$key]["consumeChk"]["state"] = StepsStates::FINISHED;
                                $data["chapters"][$key]["highlightChk"]["state"] = StepsStates::FINISHED;
                                $data["chapters"][$key]["selfEditChk"]["state"] = StepsStates::FINISHED;
                                $data["chapters"][$key]["kwc"]["state"] = StepsStates::FINISHED;
                                $data["chapters"][$key]["peerChk"]["state"] = StepsStates::FINISHED;
                                $data["chapters"][$key]["peerChk"]["checkerID"] = $peerCheck[$key];
                                $members[$otherCheck[$key]["checkerID"]] = "";
                                $data["chapters"][$key]["stepChk"] = EventSteps::FINISHED;
                            }
                            else
                            {
                                $stepChk = $checker[0]->step;
                                $chapterChk = $checker[0]->currentChapter;
                                $checkerChk = $checker[0]->checkerID;
                                
                                $data["chapters"][$key]["stepChk"] = $stepChk;
    
                                if($stepChk == EventSteps::CONSUME)
                                {
                                    $data["chapters"][$key]["consumeChk"]["state"] = StepsStates::IN_PROGRESS;
                                }
                                else if($stepChk == EventSteps::HIGHLIGHT)
                                {
                                    $data["chapters"][$key]["consumeChk"]["state"] = StepsStates::FINISHED;
                                    $data["chapters"][$key]["highlightChk"]["state"] = StepsStates::IN_PROGRESS;
                                }
                                else if($stepChk == EventSteps::SELF_CHECK)
                                {
                                    $data["chapters"][$key]["consumeChk"]["state"] = StepsStates::FINISHED;
                                    $data["chapters"][$key]["highlightChk"]["state"] = StepsStates::FINISHED;
                                    $data["chapters"][$key]["selfEditChk"]["state"] = StepsStates::IN_PROGRESS;
                                }
                                else if($stepChk == EventSteps::KEYWORD_CHECK)
                                {
                                    $data["chapters"][$key]["consumeChk"]["state"] = StepsStates::FINISHED;
                                    $data["chapters"][$key]["highlightChk"]["state"] = StepsStates::FINISHED;
                                    $data["chapters"][$key]["selfEditChk"]["state"] = StepsStates::FINISHED;
                                    $data["chapters"][$key]["kwc"]["state"] = StepsStates::IN_PROGRESS;
                                }
                                else if($stepChk == EventSteps::PEER_REVIEW)
                                {
                                    $data["chapters"][$key]["consumeChk"]["state"] = StepsStates::FINISHED;
                                    $data["chapters"][$key]["highlightChk"]["state"] = StepsStates::FINISHED;
                                    $data["chapters"][$key]["selfEditChk"]["state"] = StepsStates::FINISHED;
                                    $data["chapters"][$key]["kwc"]["state"] = StepsStates::FINISHED;
                                    
                                    if(array_key_exists($key, $peerCheck))
                                    {
                                        $data["chapters"][$key]["peerChk"]["state"] = StepsStates::CHECKED;
                                        $data["chapters"][$key]["peerChk"]["checkerID"] = $peerCheck[$key];
                                        $members[$peerCheck[$key]] = "";
                                    }
                                    else if($checkerChk <= 0)
                                    {
                                        $data["chapters"][$key]["peerChk"]["state"] = StepsStates::WAITING;
                                    }
                                    else if($checkerChk > 0)
                                    {
                                        $data["chapters"][$key]["peerChk"]["state"] = StepsStates::IN_PROGRESS;
                                        $data["chapters"][$key]["peerChk"]["checkerID"] = $checkerChk;
                                        $members[$checkerChk] = "";
                                    }
                                }
                            }
                        }
                    }
                }
                else
                {
                    if($key == $currentChapter)
                    {
                        if($currentStep == EventSteps::SELF_CHECK)
                        {
                            $data["chapters"][$key]["blindDraft"]["state"] = StepsStates::FINISHED;
                            $data["chapters"][$key]["selfEdit"]["state"] = StepsStates::IN_PROGRESS;
                        }
                    }
                }
        
                // Count checked chunks
                if(!empty($data["chapters"][$key]["chunksData"]))
                {
                    $arr = [];
                    foreach ($data["chapters"][$key]["chunksData"] as $chunkData) {
                        $verses = (array)json_decode($chunkData->translatedVerses);
                        if(isset($verses["checker"]) && !empty($verses["checker"]->verses))
                            $arr[] = "";
                    }
                    
                    $data["chapters"][$key]["progress"] += sizeof($arr)*10/sizeof($chapter["chunks"]);
                }
                
                // Progress checks
                if($data["chapters"][$key]["consume"]["state"] == StepsStates::FINISHED)
                    $data["chapters"][$key]["progress"] += 17;
                if($data["chapters"][$key]["selfEdit"]["state"] == StepsStates::FINISHED)
                    $data["chapters"][$key]["progress"] += 17;
                if($data["chapters"][$key]["consumeChk"]["state"] == StepsStates::FINISHED)
                    $data["chapters"][$key]["progress"] += 10;
                if($data["chapters"][$key]["highlightChk"]["state"] == StepsStates::FINISHED)
                    $data["chapters"][$key]["progress"] += 10;
                if($data["chapters"][$key]["kwc"]["state"] == StepsStates::FINISHED)
                    $data["chapters"][$key]["progress"] += 10;
                if($data["chapters"][$key]["peerChk"]["state"] == StepsStates::CHECKED)
                    $data["chapters"][$key]["progress"] += 5;
                if($data["chapters"][$key]["peerChk"]["state"] == StepsStates::FINISHED)
                    $data["chapters"][$key]["progress"] += 9;
        
                $overallProgress += $data["chapters"][$key]["progress"];
            }
            
            $data["overall_progress"] = $overallProgress / sizeof($data["chapters"]);
        
            $empty = array_fill(0, sizeof($admins), "");
            $adminsArr = array_combine($admins, $empty);

            $members += $adminsArr;
            $membersArray = (array)$this->_membersModel->getMembers(array_filter(array_keys($members)));

            foreach ($membersArray as $member) {
                $members[$member->memberID]["userName"] = $member->userName;
                $members[$member->memberID]["name"] = $member->firstName . " " . mb_substr($member->lastName, 0, 1).".";
                $members[$member->memberID]["avatar"] = $member->avatar;
            }

            foreach ($members as $key => $member) {
                if(!is_numeric($key) && $key != "na")
                {
                    $name = $members[$key];
                    $members[$key] = [];
                    $members[$key]["userName"] = $key;
                    $members[$key]["name"] = $name;
                    $members[$key]["avatar"] = "n1";
                }
            }

            $members["na"] = __("not_available");
            $members = array_filter($members);

            $data["admins"] = $admins;
            $data["members"] = $members;
        }
        
        $data["notifications"] = $this->_notifications;
        
        if(!$isAjax)
        {
            return View::make('Events/Notes/Information')
                ->shares("title", __("event_info"))
                ->shares("data", $data)
                ->shares("error", @$error);
        }
        else
        {
            $this->layout = "dummy";
            $response["success"] = true;
            $response["progress"] = $data["overall_progress"];
            $response["admins"] = $data["admins"];
            $response["members"] = $data["members"];
            $response["html"] = View::make("Events/Notes/GetInfo")
                ->shares("data", $data)
                ->renderContents();

            echo json_encode($response);
        }
    }


    public function informationL2($eventID)
    {
        $data["menu"] = 1;
        $data["event"] = $this->_model->getEventMember($eventID, Session::get("memberID"), true);
        $data["isAdmin"] = false;
        $canViewInfo = false;
        $isAjax = false;

        if(!empty($_SERVER['HTTP_X_REQUESTED_WITH'])
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')
        {
            $isAjax = true;
            $response = ["success" => false];
        }

        if(!empty($data["event"]))
        {
            $admins = (array)json_decode($data["event"][0]->admins_l2, true);

            if($data["event"][0]->translator === null && $data["event"][0]->checker === null
                && $data["event"][0]->checker_l2 === null && $data["event"][0]->checker_l3 === null)
            {
                // If member is not a participant of the event, check if he is a facilitator
                if(Session::get("isAdmin"))
                {
                    $data["isAdmin"] = $canViewInfo = in_array(Session::get("memberID"), $admins);

                    if(!$data["isAdmin"])
                    {
                        if(Session::get("isSuperAdmin")) // Or superadmin
                        {
                            $data["isAdmin"] = $canViewInfo = true;
                        }
                        else
                        {
                            if(!$isAjax)
                                $error[] = __("empty_or_not_permitted_event_error");
                            else
                            {
                                $response["errorType"] = "empty_no_permission";
                                echo json_encode($response);
                                exit;
                            }
                        }
                    }
                }
                else
                {
                    if(!$isAjax)
                        $error[] = __("empty_or_not_permitted_event_error");
                    else
                    {
                        $response["errorType"] = "empty_no_permission";
                        echo json_encode($response);
                        exit;
                    }
                }
            }
            else
            {
                $canViewInfo = true;
                if(Session::get("isAdmin"))
                {
                    $data["isAdmin"] = in_array(Session::get("memberID"), $admins)
                        || Session::get("isSuperAdmin");
                }
            }

            if($data["event"][0]->state == "started" && $canViewInfo)
            {
                if(!$isAjax)
                    $error[] = __("not_started_event_error", array($data["event"][0]->eventID));
                else
                {
                    $response["errorType"] = "not_started";
                    echo json_encode($response);
                    exit;
                }
            }
        }
        else
        {
            if(!$isAjax)
                $error[] = __("empty_or_not_permitted_event_error");
            else
            {
                $response["errorType"] = "empty_no_permission";
                echo json_encode($response);
                exit;
            }
        }

        if(!isset($error))
        {
            $data["chapters"] = [];
            for($i=1; $i <= $data["event"][0]->chaptersNum; $i++)
            {
                $data["chapters"][$i] = [];
            }

            $chapters = $this->_model->getChapters(
                $data["event"][0]->eventID,
                null,
                null,
                "l2");

            foreach ($chapters as $chapter) {
                $tmp["l2chID"] = $chapter["l2chID"];
                $tmp["l2memberID"] = $chapter["l2memberID"];
                //$tmp["chunks"] = json_decode($chapter["chunks"], true);
                $tmp["l2checked"] = $chapter["l2checked"];
                $tmp["currentChapter"] = $chapter["currentChapter"];
                $tmp["step"] = $chapter["step"];
                $tmp["sndCheck"] = (array)json_decode($chapter["sndCheck"], true);
                $tmp["peer1Check"] = (array)json_decode($chapter["peer1Check"], true);
                $tmp["peer2Check"] = (array)json_decode($chapter["peer2Check"], true);

                $data["chapters"][$chapter["chapter"]] = $tmp;
            }

            $members = [];
            $overallProgress = 0;

            foreach ($data["chapters"] as $key => $chapter) {
                if($chapter["l2memberID"] == 0) continue;

                $snd = !empty($chapter["sndCheck"])
                    && array_key_exists($key, $chapter["sndCheck"]);
                $p1 = !empty($chapter["peer1Check"])
                    && array_key_exists($key, $chapter["peer1Check"])
                    && $chapter["peer1Check"][$key]["memberID"] > 0;
                $p2 = !empty($chapter["peer2Check"])
                    && array_key_exists($key, $chapter["peer2Check"])
                    && $chapter["peer2Check"][$key]["memberID"] > 0;

                $members[$chapter["l2memberID"]] = "";
                $data["chapters"][$key]["progress"] = 0;

                // Set default values
                $data["chapters"][$key]["consume"]["state"] = StepsStates::NOT_STARTED;
                $data["chapters"][$key]["fstChk"]["state"] = StepsStates::NOT_STARTED;

                $data["chapters"][$key]["sndChk"]["state"] = StepsStates::NOT_STARTED;
                $data["chapters"][$key]["keywordsChk"]["state"] = StepsStates::NOT_STARTED;
                $data["chapters"][$key]["sndChk"]["checkerID"] = 'na';

                $data["chapters"][$key]["peerChk"]["state"] = StepsStates::NOT_STARTED;
                $data["chapters"][$key]["peerChk"]["checkerID1"] = 'na';
                $data["chapters"][$key]["peerChk"]["checkerID2"] = 'na';

                $currentStep = $chapter["step"];

                if($snd)
                {
                    // First check
                    $data["chapters"][$key]["consume"]["state"] = StepsStates::FINISHED;
                    $data["chapters"][$key]["fstChk"]["state"] = StepsStates::FINISHED;

                    if($chapter["sndCheck"][$key]["memberID"] > 0)
                    {
                        $members[$chapter["sndCheck"][$key]["memberID"]] = "";
                        $data["chapters"][$key]["sndChk"]["checkerID"] = $chapter["sndCheck"][$key]["memberID"];

                        if($chapter["sndCheck"][$key]["done"] == 2)
                        {
                            // Second check
                            $data["chapters"][$key]["sndChk"]["state"] = StepsStates::FINISHED;
                            $data["chapters"][$key]["keywordsChk"]["state"] = StepsStates::FINISHED;

                            // Peer check
                            if($p1 && $p2)
                            {
                                $members[$chapter["peer1Check"][$key]["memberID"]] = "";
                                $members[$chapter["peer2Check"][$key]["memberID"]] = "";

                                $data["chapters"][$key]["peerChk"]["checkerID1"] = $chapter["peer1Check"][$key]["memberID"];
                                $data["chapters"][$key]["peerChk"]["checkerID2"] = $chapter["peer2Check"][$key]["memberID"];

                                if($chapter["peer2Check"][$key]["done"] == 1)
                                {
                                    if($chapter["peer1Check"][$key]["done"] == 1)
                                    {
                                        $data["chapters"][$key]["peerChk"]["state"] = StepsStates::FINISHED;
                                    }
                                    else
                                    {
                                        $data["chapters"][$key]["peerChk"]["state"] = StepsStates::CHECKED;
                                    }
                                }
                                else
                                {
                                    $data["chapters"][$key]["peerChk"]["state"] = StepsStates::IN_PROGRESS;
                                }
                            }
                            else if($p1 && !$p2)
                            {
                                $members[$chapter["peer1Check"][$key]["memberID"]] = "";
                                $data["chapters"][$key]["peerChk"]["checkerID1"] = $chapter["peer1Check"][$key]["memberID"];
                                $data["chapters"][$key]["peerChk"]["state"] = StepsStates::WAITING;
                            }
                            else
                            {
                                $data["chapters"][$key]["peerChk"]["state"] = StepsStates::WAITING;
                            }
                        }
                        else if($chapter["sndCheck"][$key]["done"] == 1)
                        {
                            $data["chapters"][$key]["sndChk"]["state"] = StepsStates::FINISHED;
                            $data["chapters"][$key]["keywordsChk"]["state"] = StepsStates::IN_PROGRESS;
                        }
                        else
                        {
                            $data["chapters"][$key]["sndChk"]["state"] = StepsStates::IN_PROGRESS;
                        }
                    }
                    else
                    {
                        $data["chapters"][$key]["sndChk"]["state"] = StepsStates::WAITING;
                    }
                }
                else
                {
                    if($currentStep == EventCheckSteps::CONSUME)
                    {
                        $data["chapters"][$key]["consume"]["state"] = StepsStates::IN_PROGRESS;
                    }
                    else if($currentStep == EventCheckSteps::FST_CHECK)
                    {
                        $data["chapters"][$key]["consume"]["state"] = StepsStates::FINISHED;
                        $data["chapters"][$key]["fstChk"]["state"] = StepsStates::IN_PROGRESS;
                    }
                }


                // Progress checks
                if($data["chapters"][$key]["consume"]["state"] == StepsStates::FINISHED)
                    $data["chapters"][$key]["progress"] += 20;
                if($data["chapters"][$key]["fstChk"]["state"] == StepsStates::FINISHED)
                    $data["chapters"][$key]["progress"] += 20;
                if($data["chapters"][$key]["sndChk"]["state"] == StepsStates::FINISHED)
                    $data["chapters"][$key]["progress"] += 20;
                if($data["chapters"][$key]["keywordsChk"]["state"] == StepsStates::FINISHED)
                    $data["chapters"][$key]["progress"] += 20;
                if($data["chapters"][$key]["peerChk"]["state"] == StepsStates::CHECKED)
                    $data["chapters"][$key]["progress"] += 10;
                if($data["chapters"][$key]["peerChk"]["state"] == StepsStates::FINISHED)
                    $data["chapters"][$key]["progress"] += 20;

                $overallProgress += $data["chapters"][$key]["progress"];
            }

            $data["overall_progress"] = $overallProgress / sizeof($data["chapters"]);

            $empty = array_fill(0, sizeof($admins), "");
            $adminsArr = array_combine($admins, $empty);

            $members += $adminsArr;
            $membersArray = (array)$this->_membersModel->getMembers(array_filter(array_keys($members)));

            foreach ($membersArray as $member) {
                $members[$member->memberID]["userName"] = $member->userName;
                $members[$member->memberID]["name"] = $member->firstName . " " . mb_substr($member->lastName, 0, 1).".";
                $members[$member->memberID]["avatar"] = $member->avatar;
            }

            foreach ($members as $key => $member) {
                if(!is_numeric($key) && $key != "na")
                {
                    $name = $members[$key];
                    $members[$key] = [];
                    $members[$key]["userName"] = $key;
                    $members[$key]["name"] = $name;
                    $members[$key]["avatar"] = "n1";
                }
            }

            $members["na"] = __("not_available");
            $members = array_filter($members);

            $data["admins"] = $admins;
            $data["members"] = $members;
        }

        $data["notifications"] = $this->_notifications;

        if(!$isAjax)
        {
            return View::make('Events/L2/Information')
                ->shares("title", __("event_info"))
                ->shares("data", $data)
                ->shares("error", @$error);
        }
        else
        {
            $this->layout = "dummy";
            $response["success"] = true;
            $response["progress"] = $data["overall_progress"];
            $response["admins"] = $data["admins"];
            $response["members"] = $data["members"];
            $response["html"] = View::make("Events/L2/GetInfo")
                ->shares("data", $data)
                ->renderContents();

            echo json_encode($response);
        }
    }

    public function manage($eventID)
    {
        if (!Session::get('isAdmin') && !Session::get("isSuperAdmin"))
        {
            Url::redirect("events");
        }

        $data["menu"] = 1;
        $data["event"] = $this->_model->getMemberEventsForAdmin(Session::get("memberID"), $eventID, Session::get("isSuperAdmin"));
        
        if(!empty($data["event"]))
        {
            $superadmins = (array)json_decode($data["event"][0]->superadmins, true);
            if(Session::get("isSuperAdmin") && !in_array(Session::get("memberID"), $superadmins))
                Url::redirect("events");

            if(!Session::get("isSuperAdmin"))
            {
                $adms = (array)json_decode($data["event"][0]->admins, true);
                if(!in_array(Session::get("memberID"), $adms))
                {
                    Url::redirect("/events");
                }
            }

            $data["chapters"] = [];
            if($data["event"][0]->bookProject == "tn")
                $data["chapters"][0] = [];
            
                for($i=1; $i <= $data["event"][0]->chaptersNum; $i++)
            {
                $data["chapters"][$i] = [];
            }

            $chapters = $this->_model->getChapters($data["event"][0]->eventID);

            foreach ($chapters as $chapter) {
                $tmp["trID"] = $chapter["trID"];
                $tmp["memberID"] = $chapter["memberID"];
                $tmp["chunks"] = json_decode($chapter["chunks"], true);
                $tmp["done"] = $chapter["done"];

                $data["chapters"][$chapter["chapter"]] = $tmp;
            }

            $data["members"] = $this->_model->getMembersForEvent($data["event"][0]->eventID, $data["event"][0]->bookProject);
            
            if (isset($_POST) && !empty($_POST)) {
                if(!empty(array_filter($data["chapters"])))
                {
                    $updated = $this->_model->updateEvent(
                        array(
                            "state" => EventStates::TRANSLATING,
                            "dateFrom" => date("Y-m-d H:i:s", time())),
                            array("eventID" => $eventID));
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
    
    
    public function manageL2($eventID)
    {
        if (!Session::get('isAdmin') && !Session::get("isSuperAdmin"))
        {
            Url::redirect("events");
        }

        $data["menu"] = 1;
        $data["notifications"] = $this->_notifications;
        $data["event"] = $this->_model->getMemberEventsForAdmin(Session::get("memberID"), $eventID, Session::get("isSuperAdmin"));

        if(!empty($data["event"]))
        {
            $superadmins = (array)json_decode($data["event"][0]->superadmins, true);
            if(Session::get("isSuperAdmin") && !in_array(Session::get("memberID"), $superadmins))
                Url::redirect("events");

            if(!Session::get("isSuperAdmin"))
            {
                $adms = (array)json_decode($data["event"][0]->admins_l2, true);
                if(!in_array(Session::get("memberID"), $adms))
                {
                    Url::redirect("/events");
                }
            }

            if($data["event"][0]->state != EventStates::L2_RECRUIT && 
                $data["event"][0]->state != EventStates::L2_CHECK &&
                $data["event"][0]->state != EventStates::L2_CHECKED)
            {
                Url::redirect("events");
            }
            
            $data["chapters"] = [];
            
            for($i=1; $i <= $data["event"][0]->chaptersNum; $i++)
            {
                $data["chapters"][$i] = [];
            }

            $chapters = $this->_model->getChapters($data["event"][0]->eventID, null, null, "l2");

            foreach ($chapters as $chapter) {
                if($chapter["l2memberID"] == 0) continue;
                
                $tmp["l2chID"] = $chapter["l2chID"];
                $tmp["l2memberID"] = $chapter["l2memberID"];
                $tmp["chunks"] = json_decode($chapter["chunks"], true);
                $tmp["l2checked"] = $chapter["l2checked"];
                $tmp["sndCheck"] = (array)json_decode($chapter["sndCheck"], true);
                $tmp["peer1Check"] = (array)json_decode($chapter["peer1Check"], true);
                $tmp["peer2Check"] = (array)json_decode($chapter["peer2Check"], true);

                $data["chapters"][$chapter["chapter"]] = $tmp;
            }

            $data["members"] = $this->_model->getMembersForL2Event($data["event"][0]->eventID, $data["event"][0]->bookProject);
            
            if (isset($_POST) && !empty($_POST)) {
                if(!empty(array_filter($data["chapters"])))
                {
                    $updated = $this->_model->updateEvent(
                        array(
                            "state" => EventStates::L2_CHECK,
                            /*"dateFrom" => date("Y-m-d H:i:s", time())*/),
                            array("eventID" => $eventID));
                    if($updated)
                        Url::redirect("events/manage-l2/".$eventID);
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

        return View::make('Events/L2/Manage')
            ->shares("title", __("manage_event"))
            ->shares("data", $data)
            ->shares("error", @$error);
    }


    public function moveStepBack()
    {
        $response = array("success" => false);

        if (!Session::get('isAdmin') && !Session::get('isSuperAdmin'))
        {
            $response["error"] = __("not_enough_rights_error");
            echo json_encode($response);
            return;
        }

        $_POST = Gump::xss_clean($_POST);

        $eventID = isset($_POST["eventID"]) && $_POST["eventID"] != "" ? (integer)$_POST["eventID"] : null;
        $memberID = isset($_POST["memberID"]) && $_POST["memberID"] != "" ? (integer)$_POST["memberID"] : null;
        $to_step = isset($_POST["to_step"]) && $_POST["to_step"] != "" ? $_POST["to_step"] : null;
        $prev_chunk = isset($_POST["prev_chunk"]) && $_POST["prev_chunk"] != "" ? filter_var($_POST["prev_chunk"], FILTER_VALIDATE_BOOLEAN) : false;
        $confirm = isset($_POST["confirm"]) && $_POST["confirm"] != "" ? filter_var($_POST["confirm"], FILTER_VALIDATE_BOOLEAN) : false;
        $tnChk = isset($_POST["chk"]) && $_POST["chk"] != "" ? filter_var($_POST["chk"], FILTER_VALIDATE_BOOLEAN) : false;
        $manageMode = isset($_POST["manageMode"]) && $_POST["manageMode"] != "" ? $_POST["manageMode"] : "l1";

        if($eventID !== null && $memberID !== null &&
            $to_step !== null)
        {
            $userType = EventMembers::TRANSLATOR;
            if($manageMode == "l2")
                $userType = EventMembers::L2_CHECKER;

            $member = $this->_model->getMemberEvents($memberID, $userType, $eventID, true, false, $tnChk);

            if(!empty($member))
            {
                if(EventStates::enum($member[0]->state) < EventStates::enum(EventStates::TRANSLATED))
                {
                    $mode = $member[0]->bookProject;
                    $chk = in_array($mode, ["tn"]) && $member[0]->stage == "checking"
                        && $member[0]->isChecker
                        && $member[0]->currentChapter > -1;
                    $member[0]->chk = $chk;

                    if(array_key_exists($to_step, EventSteps::enumArray($mode, $chk))
                        || array_key_exists($to_step, EventCheckSteps::enumArray("l2")))
                    {
                        $postData = $this->moveMemberStepBack(
                            $member[0],
                            $to_step,
                            $confirm,
                            $prev_chunk
                        );

                        if(!empty($postData))
                        {
                            if(in_array("hasTranslation", $postData, true))
                            {
                                $response["confirm"] = true;
                                $response["message"] = __("chapter_has_translation");
                                echo json_encode($response);
                                return;
                            }

                            if(array_key_exists("translations", $postData))
                            {
                                unset($postData["translations"]);
                                $this->_translationModel->deleteTranslation(["trID" => $member[0]->trID, "chapter" => $member[0]->currentChapter]);
                                $this->_translationModel->deleteCommentsByEvent($eventID, $member[0]->currentChapter);
                            }

                            if($manageMode == "l1")
                                $this->_model->updateTranslator($postData,
                                    ["trID" => $member[0]->trID]);
                            else
                                $this->_model->updateL2Checker($postData,
                                    ["l2chID" => $member[0]->l2chID]);

                            $response["success"] = true;
                            $response["message"] = $member[0]->step != $to_step
                                ? __("moved_back_success") : __("checker_removed_success");
                        }
                        else
                        {
                            $response["error"] = __("not_allowed_action");
                        }
                    }
                    else
                    {
                        $response["error"] = __("wrong_parameters");
                    }
                }
                else
                {
                    $response["error"] = __("event_is_finished");
                }
            }
            else
            {
                $response["error"] = __("wrong_parameters");
            }
        }

        echo json_encode($response);
    }

    public function moveStepBackL2()
    {
        $response = array("success" => false);

        if (!Session::get('isAdmin') && !Session::get('isSuperAdmin'))
        {
            $response["error"] = __("not_enough_rights_error");
            echo json_encode($response);
            return;
        }

        $_POST = Gump::xss_clean($_POST);

        $eventID = isset($_POST["eventID"]) && $_POST["eventID"] != "" ? (integer)$_POST["eventID"] : null;
        $memberID = isset($_POST["memberID"]) && $_POST["memberID"] != "" ? (integer)$_POST["memberID"] : null;
        $chapter = isset($_POST["chapter"]) && $_POST["chapter"] != "" ? (integer)$_POST["chapter"] : null;
        $mode = isset($_POST["mode"]) && $_POST["mode"] != "" ? $_POST["mode"] : "snd_checker";

        if($eventID !== null && $memberID !== null &&
            $chapter !== null)
        {
            $event = $this->_model->getEvent($eventID);

            if(!empty($event))
            {
                if(EventStates::enum($event[0]->state) < EventStates::enum(EventStates::L2_CHECKED))
                {
                    $chapters = $this->_model->getChapters($eventID, $memberID, $chapter, "l2");
                    $chap = [];

                    if(!empty($chapters))
                    {
                        $chap["l2chID"] = $chapters[0]["l2chID"];
                        $chap["l2memberID"] = $chapters[0]["l2memberID"];
                        $chap["chunks"] = json_decode($chapters[0]["chunks"], true);
                        $chap["l2checked"] = $chapters[0]["l2checked"];
                        $chap["sndCheck"] = (array)json_decode($chapters[0]["sndCheck"], true);
                        $chap["peer1Check"] = (array)json_decode($chapters[0]["peer1Check"], true);
                        $chap["peer2Check"] = (array)json_decode($chapters[0]["peer2Check"], true);

                        $p1 = !empty($chap["peer1Check"])
                            && array_key_exists($chapter, $chap["peer1Check"])
                            && $chap["peer1Check"][$chapter]["memberID"] > 0;
                        $p2 = !empty($chap["peer2Check"])
                            && array_key_exists($chapter, $chap["peer2Check"])
                            && $chap["peer2Check"][$chapter]["memberID"] > 0;

                        switch ($mode)
                        {
                            case "snd_checker":
                                if(!$p1)
                                {
                                    $chap["sndCheck"][$chapter]["memberID"] = 0;
                                    $chap["sndCheck"][$chapter]["done"] = 0;
                                    unset($chap["peer1Check"][$chapter]);
                                    unset($chap["peer2Check"][$chapter]);
                                }
                                else
                                {
                                    $response["error"] = __("wrong_parameters");
                                }
                                break;

                            case "p1_checker":
                                if(!$p2)
                                {
                                    $chap["peer1Check"][$chapter]["memberID"] = 0;
                                    $chap["peer1Check"][$chapter]["done"] = 0;
                                    $chap["peer2Check"][$chapter]["memberID"] = 0;
                                    $chap["peer2Check"][$chapter]["done"] = 0;
                                }
                                else
                                {
                                    $response["error"] = __("wrong_parameters");
                                }
                                break;

                            case "p2_checker":
                                if($p2)
                                {
                                    $chap["peer1Check"][$chapter]["done"] = 0;
                                    $chap["peer2Check"][$chapter]["memberID"] = 0;
                                    $chap["peer2Check"][$chapter]["done"] = 0;
                                }
                                else
                                {
                                    $response["error"] = __("wrong_parameters");
                                }
                                break;

                            default:
                                $response["error"] = __("wrong_parameters");
                                break;
                        }

                        if(!isset($response["error"]))
                        {
                            $postData = [
                                "sndCheck" => json_encode($chap["sndCheck"]),
                                "peer1Check" => json_encode($chap["peer1Check"]),
                                "peer2Check" => json_encode($chap["peer2Check"]),
                            ];

                            $this->_model->updateL2Checker($postData, [
                                "eventID" => $eventID,
                                "memberID" => $memberID
                            ]);

                            $response["message"] = __("checker_removed_success");
                            $response["success"] = true;
                        }
                    }
                    else
                    {
                        $response["error"] = __("wrong_parameters");
                    }
                }
                else
                {
                    $response["error"] = __("event_is_finished");
                }
            }
            else
            {
                $response["error"] = __("wrong_parameters");
            }
        }

        echo json_encode($response);
    }


    public function setTnChecker()
    {
        $response = array("success" => false);
        
        if (!Session::get('isAdmin') && !Session::get('isSuperAdmin'))
        {
            $response["error"] = __("not_enough_rights_error");
            echo json_encode($response);
            return;
        }

        $_POST = Gump::xss_clean($_POST);

        $eventID = isset($_POST["eventID"]) && $_POST["eventID"] != "" ? (integer)$_POST["eventID"] : null;
        $memberID = isset($_POST["memberID"]) && $_POST["memberID"] != "" ? (integer)$_POST["memberID"] : null;

        if($eventID !== null && $memberID !== null)
        {
            $member = $this->_model->getMemberEvents($memberID, EventMembers::TRANSLATOR, $eventID, true);
            
            if(!empty($member))
            {
                $isChecker = $member[0]->isChecker;

                if(!$isChecker)
                {
                    $this->_model->updateTranslator(
                        ["isChecker" => true], 
                        ["trID" => $member[0]->trID]);
                    $response["success"] = true;
                }
                else
                {
                    $response["error"] = __("cannot_undo_action");
                }
            }
            else
            {
                $response["error"] = __("wrong_parameters");
            }
        }

        echo json_encode($response);
    }


    public function demo($page = null)
    {
        if(!isset($page))
            Url::redirect("events/demo/pray");

        for($i=0; $i<3; $i++)
        {
            $notifObj = new \stdClass();

            if($i==0)
                $notifObj->step = EventSteps::PEER_REVIEW;
            elseif($i==1)
                $notifObj->step = EventSteps::KEYWORD_CHECK;
            else
                $notifObj->step = EventSteps::CONTENT_REVIEW;

            $notifObj->currentChapter = 2;
            $notifObj->firstName = "Mark";
            $notifObj->lastName = "Patton";
            $notifObj->bookCode = "2ti";
            $notifObj->bookProject = "ulb";
            $notifObj->tLang = "English";
            $notifObj->bookName = "2 Timothy";
            $notifObj->manageMode = "l1";

            $notifications[] = $notifObj;
        }

        $data["notifications"] = $notifications;
        $data["isDemo"] = true;
        $data["menu"] = 1;

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

            case "information":
                return View::make("Events/Demo/Information")
                    ->shares("title", __("event_info"));
                break;
        }

        return $view
            ->shares("title", __("demo"))
            ->shares("data", $data);
    }

    public function demoTn($page = null)
    {
        if(!isset($page))
            Url::redirect("events/demo-tn/pray");

        for($i=0; $i<2; $i++)
        {
            $notifObj = new \stdClass();

            if($i == 0)
            {
                $notifObj->notesChapter = 1;
                $notifObj->firstName = "Mark";
                $notifObj->lastName = "Patton";
                $notifObj->bookCode = "act";
                $notifObj->bookProject = "tn";
                $notifObj->tLang = "Bahasa Indonesia";
                $notifObj->bookName = "Acts";
            }
            else
            {
                $notifObj->step = EventSteps::PEER_REVIEW;
                $notifObj->currentChapter = 1;
                $notifObj->firstName = "Genry";
                $notifObj->lastName = "Miller";
                $notifObj->bookCode = "act";
                $notifObj->bookProject = "tn";
                $notifObj->tLang = "Bahasa Indonesia";
                $notifObj->bookName = "Acts";
                $notifObj->manageMode = "tn";
            }

            $notifications[] = $notifObj;
        }

        $data["notifications"] = $notifications;
        $data["isDemo"] = true;
        $data["menu"] = 1;

        $view = View::make("Events/Notes/Demo/DemoHeader");
        $data["step"] = "";
        $data["stage"] = "translation";

        switch ($page)
        {
            case "pray":
                $view->nest("page", "Events/Notes/Demo/Pray");
                $data["step"] = EventSteps::PRAY;
                break;

            case "consume":
                $view->nest("page", "Events/Notes/Demo/Consume");
                $data["step"] = EventSteps::CONSUME;
                break;

            case "read_chunk":
                $view->nest("page", "Events/Notes/Demo/ReadChunk");
                $data["step"] = EventSteps::READ_CHUNK;
                break;

            case "blind_draft":
                $view->nest("page", "Events/Notes/Demo/BlindDraft");
                $data["step"] = EventSteps::BLIND_DRAFT;
                break;

            case "self_check":
                $view->nest("page", "Events/Notes/Demo/SelfEdit");
                $data["step"] = EventSteps::SELF_CHECK;
                break;

            case "pray_chk":
                $view->nest("page", "Events/Notes/Demo/PrayChk");
                $data["step"] = EventSteps::PRAY;
                $data["stage"] = "checking";
                $data["isCheckerPage"] = true;
                break;

            case "consume_chk":
                $view->nest("page", "Events/Notes/Demo/ConsumeChk");
                $data["step"] = EventSteps::CONSUME;
                $data["stage"] = "checking";
                $data["isCheckerPage"] = true;
                break;

            case "highlight":
                $view->nest("page", "Events/Notes/Demo/Highlight");
                $data["step"] = EventSteps::HIGHLIGHT;
                $data["stage"] = "checking";
                $data["isCheckerPage"] = true;
                break;

            case "self_check_chk":
                $view->nest("page", "Events/Notes/Demo/SelfEditChk");
                $data["step"] = EventSteps::SELF_CHECK;
                $data["stage"] = "checking";
                $data["isCheckerPage"] = true;
                break;

            case "highlight_chk":
                $view->nest("page", "Events/Notes/Demo/HighlightChk");
                $data["step"] = EventSteps::KEYWORD_CHECK;
                $data["stage"] = "checking";
                $data["isCheckerPage"] = true;
                break;

            case "peer_review":
                $view->nest("page", "Events/Notes/Demo/PeerReview");
                $data["step"] = EventSteps::PEER_REVIEW;
                $data["stage"] = "checking";
                $data["isCheckerPage"] = true;
                break;

            case "peer_review_checker":
                $view->nest("page", "Events/Notes/Demo/PeerReviewChecker");
                $data["step"] = EventSteps::PEER_REVIEW;
                $data["stage"] = "checking";
                $data["isCheckerPage"] = true;
                $data["isPeerPage"] = true;
                break;

            case "information":
                return View::make("Events/Notes/Demo/Information")
                    ->shares("title", __("event_info"));
                break;
        }

        return $view
            ->shares("title", __("demo"))
            ->shares("data", $data);
    }

    public function demoL2($page = null)
    {
        if(!isset($page))
            Url::redirect("events/demo-l2/pray");

        for($i=0; $i<2; $i++)
        {
            $notifObj = new \stdClass();

            if($i==0)
                $notifObj->step = EventCheckSteps::SND_CHECK;
            else
                $notifObj->step = EventCheckSteps::PEER_REVIEW_L2;

            $notifObj->currentChapter = 2;
            $notifObj->firstName = "Mark";
            $notifObj->lastName = "Patton";
            $notifObj->bookCode = "2ti";
            $notifObj->bookProject = "ulb";
            $notifObj->tLang = "English";
            $notifObj->bookName = "2 Timothy";
            $notifObj->manageMode = "l2";

            $notifications[] = $notifObj;
        }

        $data["notifications"] = $notifications;
        $data["isDemo"] = true;
        $data["menu"] = 1;

        $view = View::make("Events/L2/Demo/DemoHeader");
        $data["step"] = "";

        switch ($page)
        {
            case "pray":
                $view->nest("page", "Events/L2/Demo/Pray");
                $data["step"] = EventCheckSteps::PRAY;
                break;

            case "consume":
                $view->nest("page", "Events/L2/Demo/Consume");
                $data["step"] = EventCheckSteps::CONSUME;
                break;

            case "fst_check":
                $view->nest("page", "Events/L2/Demo/FstCheck");
                $data["step"] = EventCheckSteps::FST_CHECK;
                break;

            case "snd_check":
                $view->nest("page", "Events/L2/Demo/SndCheck");
                $data["step"] = EventCheckSteps::SND_CHECK;
                break;

            case "keyword_check_l2":
                $view->nest("page", "Events/L2/Demo/KeywordCheck");
                $data["step"] = EventCheckSteps::KEYWORD_CHECK_L2;
                break;

            case "peer_review_l2":
                $view->nest("page", "Events/L2/Demo/PeerReview");
                $data["step"] = EventCheckSteps::PEER_REVIEW_L2;
                break;

            case "peer_review_l2_checker":
                $view->nest("page", "Events/L2/Demo/PeerReviewChecker");
                $data["step"] = EventCheckSteps::PEER_REVIEW_L2;
                $data["isCheckerPage"] = true;
                break;

            case "information":
                return View::make("Events/L2/Demo/Information")
                    ->shares("title", __("event_info"));
                break;
        }

        return $view
            ->shares("title", __("demo"))
            ->shares("data", $data);
    }

    public function applyEvent()
    {
        $data["errors"] = array();
        $profile = Session::get("profile");

        $_POST = Gump::xss_clean($_POST);

        $eventID = isset($_POST['eventID']) && $_POST['eventID'] != "" ? $_POST['eventID'] : null;
        $userType = isset($_POST['userType']) && $_POST['userType'] != "" ? $_POST['userType'] : null;
        $memberID = Session::get("memberID");

        if(Session::get("isAdmin"))
        {
            $memberID = isset($_POST['memberID']) && $_POST['memberID'] != "" ? $_POST['memberID'] : $memberID;
            if(isset($_POST['memberID']) && $_POST['memberID'] != "")
            {
                $pr = $this->_membersModel->getMemberWithProfile($_POST['memberID']);
                if(!empty($pr))
                    $profile = (array)$pr[0];
            }
        }
        
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
            if(!is_array($profile["education"]))
                $profile["education"] = (array)json_decode($profile["education"]);
            if($education === null && empty($profile["education"])) {
                $data["errors"]["education"] = true;
            }
            else
            {
                $education = $education == null ? $profile["education"] : $education;
                foreach ($education as $item) {
                    if(!preg_match("/^(BA|MA|PHD)$/", $item))
                    {
                        $data["errors"]["education"] = true;
                        break;
                    }
                }
            }
            
            if(!is_array($profile["ed_area"]))
                $profile["ed_area"] = (array)json_decode($profile["ed_area"]);
            if($ed_area === null && empty($profile["ed_area"]))
                $data["errors"]["ed_area"] = true;
            else
            {
                $ed_area = $ed_area == null ? $profile["ed_area"] : $ed_area;
                foreach ($ed_area as $item) {
                    if(!preg_match("/^(Theology|Pastoral Ministry|Bible Translation|Exegetics)$/", $item))
                    {
                        $data["errors"]["ed_area"] = true;
                        break;
                    }
                }
            }

            if($ed_place === null && empty($profile["ed_place"]))
                $data["errors"]["ed_place"] = true;

            if($hebrew_knwlg === null && empty($profile["hebrew_knwlg"]))
                $data["errors"]["hebrew_knwlg"] = true;

            if($greek_knwlg === null && empty($profile["greek_knwlg"]))
                $data["errors"]["greek_knwlg"] = true;

            if(!is_array($profile["church_role"]))
                $profile["church_role"] = (array)json_decode($profile["church_role"]);
            if($church_role === null && empty($profile["church_role"]))
                $data["errors"]["church_role"] = true;
            else
            {
                $church_role = $church_role == null ? $profile["church_role"] : $church_role;
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

            $mode = $event[0]->bookProject;
            $exists = $this->_model->getEventMember($event[0]->eventID, $memberID);
            
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

                    if($exists[0]->translator == null &&
                        $exists[0]->checker_l2 == null && $exists[0]->checker_l3 == null)   // can apply as translator only if not checker l2, l3
                    {
                        $chapter = in_array($mode, ["tn"]) ? -1 : 0;
                        $trData = array(
                            "memberID" => $memberID,
                            "eventID" => $event[0]->eventID,
                            "step" => EventSteps::NONE,
                            "currentChapter" => $chapter
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
                    break;

                case EventMembers::L2_CHECKER:
                    if($exists[0]->translator == null && $exists[0]->checker == null &&
                        $exists[0]->checker_l2 == null && $exists[0]->checker_l3 == null)   // can apply as checker L2 only if not translator or checker 7/8
                    {
                        $l2Data = array(
                            "memberID" => $memberID,
                            "eventID" => $event[0]->eventID,
                            "step" => EventSteps::NONE
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
                    break;

                case EventMembers::L3_CHECKER:
                    if($exists[0]->translator == null && $exists[0]->checker == null &&
                        $exists[0]->checker_l2 == null && $exists[0]->checker_l3 == null)   // can apply as checker L3 only if not translator or checker 7/8
                    {
                        $l3Data = array(
                            "memberID" => $memberID,
                            "eventID" => $event[0]->eventID,
                            "step" => EventSteps::NONE
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
                    break;
            }

            if(isset($error))
            {
                echo json_encode(array("error" => $error));
            }
        }
        else
        {
            $error[] = __('empty_profile_error');
            echo json_encode(array("error" => $error, "errors" => $data["errors"]));
        }
    }


    public function autosaveChunk()
    {
        $response = array("success" => false);

        $_POST = Gump::xss_clean($_POST);
        
        $eventID = isset($_POST["eventID"]) && is_numeric($_POST["eventID"]) ? $_POST["eventID"] : null;
        $formData = isset($_POST["formData"]) && $_POST["formData"] != "" ? $_POST["formData"] : null;

        if($eventID !== null && $formData !== null)
        {
            $post = array();
            parse_str(htmlspecialchars_decode($formData, ENT_QUOTES), $post);
            $tnChk = isset($post["chk"]) && $post["chk"] != "" ? filter_var($post["chk"], FILTER_VALIDATE_BOOLEAN) : false;
            $level = isset($post["level"]) && $post["level"] != "" ? $post["level"] : "l1";

            $memberType = EventMembers::TRANSLATOR;
            if($level == "l2")
                $memberType = EventMembers::L2_CHECKER;

            if($level != "l2continue")
                $event = $this->_model->getMemberEvents(Session::get("memberID"), $memberType, $eventID, false, false, $tnChk);
            else
                $event = $this->_model->getMemberEventsForCheckerL2(
                    Session::get("memberID"),
                    $eventID,
                    $post["memberID"],
                    $post["chapter"]
                );

            if(!empty($event))
            {
                $mode = $event[0]->bookProject;

                switch($event[0]->step)
                {
                    case EventSteps::BLIND_DRAFT:
                        if(trim(strip_tags($post["draft"])) != "") {
                            $chunks = json_decode($event[0]->chunks, true);
                            $chunk = $chunks[$event[0]->currentChunk];

                            $post["draft"] = preg_replace("/[\\r\\n]/", " ", $post["draft"]);

                            if(in_array($mode, ["tn"]))
                            {
                                $converter = new \Helpers\Markdownify\Converter;
                                $post["draft"] = $converter->parseString($post["draft"]);
                            }
                            
                            $role = EventMembers::TRANSLATOR;

                            if($event[0]->stage == "translation")
                            {
                                $trID = $event[0]->trID;
                                $translationData = $this->_translationModel->getLastEventTranslation($trID);
                            }
                            else
                            {
                                $trID = $event[0]->nTranslator;
                                $translationData = $this->_translationModel->getEventTranslation(
                                    $trID,
                                    $event[0]->currentChapter,
                                    $event[0]->currentChunk
                                );
                                $role = EventMembers::CHECKER;
                            }

                            $shoudUpdate = false;

                            if(!empty($translationData))
                            {
                                if($translationData[0]->chapter == $event[0]->currentChapter &&
                                    $translationData[0]->chunk == $event[0]->currentChunk)
                                {
                                    $translationVerses = json_decode($translationData[0]->translatedVerses, true);
                                    $shoudUpdate = true;
                                }
                            }
                            
                            if(!$shoudUpdate)
                            {
                                $trArr = ["blind" => [], "verses" => []];
                                if(in_array($mode, ["tn"]))
                                    $trArr["verses"] = trim($post["draft"]);
                                else 
                                    $trArr["blind"] = trim($post["draft"]);

                                $translationVerses = array(
                                    EventMembers::TRANSLATOR => $trArr,
                                    EventMembers::L2_CHECKER => array(
                                        "verses" => array()
                                    ),
                                    EventMembers::L3_CHECKER => array(
                                        "verses" => array()
                                    ),
                                );

                                if(in_array($mode, ["tn"]) && $event[0]->stage == "translation")
                                    $translationVerses[EventMembers::CHECKER] = [
                                        "verses" => array()
                                    ];
                                
                                $encoded = json_encode($translationVerses);
                                $json_error = json_last_error();
                                if($json_error === JSON_ERROR_NONE)
                                {
                                    $trData = array(
                                        "projectID" => $event[0]->projectID,
                                        "eventID" => $event[0]->eventID,
                                        "trID" => $trID,
                                        "targetLang" => $event[0]->targetLang,
                                        "bookProject" => $event[0]->bookProject,
                                        "abbrID" => $event[0]->abbrID,
                                        "bookCode" => $event[0]->bookCode,
                                        "chapter" => $event[0]->currentChapter,
                                        "chunk" => $event[0]->currentChunk,
                                        "firstvs" => $chunk[0],
                                        "translatedVerses" => $encoded,
                                        "dateCreate" => date('Y-m-d H:i:s')
                                    );
    
                                    $tID = $this->_translationModel->createTranslation($trData);
    
                                    if (is_numeric($tID)) {
                                        $response["chapter"] = $event[0]->currentChapter;
                                        $response["chunk"] = $event[0]->currentChunk;
                                        $response["success"] = true;
                                    }
                                }
                                else 
                                {
                                    $response["errorType"] = "json";
                                    $response["error"] = "Json error: " . $json_error;
                                }
                            }
                            else
                            {
                                if(in_array($mode, ["tn"]))
                                    $translationVerses[$role]["verses"] = trim($post["draft"]);
                                else
                                    $translationVerses[EventMembers::TRANSLATOR]["blind"] = trim($post["draft"]);

                                $encoded = json_encode($translationVerses);
                                $json_error = json_last_error();
                                if($json_error === JSON_ERROR_NONE)
                                {
                                    $trData = array(
                                        "translatedVerses"  => $encoded,
                                    );
    
                                    $this->_translationModel->updateTranslation($trData, array("tID" => $translationData[0]->tID));
                                    $response["success"] = true;
                                }
                                else 
                                {
                                    $response["errorType"] = "json";
                                    $response["error"] = "Json error: " . $json_error;
                                }
                            }
                        }
                        break;

                    case EventSteps::SELF_CHECK:
                    case EventSteps::PEER_REVIEW:
                    case EventSteps::KEYWORD_CHECK:
                    case EventSteps::CONTENT_REVIEW:
                        if(is_array($post["chunks"]) && !empty($post["chunks"]))
                        {
                            if($event[0]->step == EventSteps::PEER_REVIEW
                                || $event[0]->step == EventSteps::KEYWORD_CHECK 
                                || $event[0]->step == EventSteps::CONTENT_REVIEW)
                            {
                                if($event[0]->checkDone)
                                {
                                    $response["errorType"] = "checkDone";
                                    $response["error"] = __("not_possible_to_save_error");
                                    echo json_encode($response);
                                    exit;
                                }
                            }
                            
                            $role = EventMembers::TRANSLATOR;

                            if($event[0]->stage == "translation")
                            {
                                $trID = $event[0]->trID;
                            }
                            else
                            {
                                $trID = $event[0]->nTranslator;
                                $role = EventMembers::CHECKER;
                            }

                            $translationData = $this->_translationModel->getEventTranslation(
                                $trID, 
                                $event[0]->currentChapter);
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
                                    return !empty(strip_tags($v));
                                });
                                
                                $updated = 0;
                                foreach ($translation as $key => $chunk) {
                                    if(!isset($post["chunks"][$key])) continue;

                                    $section = "blind";

                                    if(in_array($mode, ["tn"]))
                                    {
                                        $converter = new \Helpers\Markdownify\Converter;
                                        $post["chunks"][$key] = $converter->parseString($post["chunks"][$key]);
                                        $section = "verses";
                                    }

                                    $shouldUpdate = false;
                                    if($chunk[$role][$section] != $post["chunks"][$key])
                                        $shouldUpdate = true;

                                    $translation[$key][$role][$section] = $post["chunks"][$key];

                                    if($shouldUpdate)
                                    {
                                        $tID = $translation[$key]["tID"];
                                        unset($translation[$key]["tID"]);

                                        $encoded = json_encode($translation[$key]);
                                        $json_error = json_last_error();
                                        if($json_error === JSON_ERROR_NONE)
                                        {
                                            $trData = array(
                                                "translatedVerses"  => $encoded
                                            );
                                            $this->_translationModel->updateTranslation(
                                                $trData, 
                                                array(
                                                    "trID" => $trID, 
                                                    "tID" => $tID));
                                            $updated++;
                                        }
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

                    case EventCheckSteps::FST_CHECK:
                    case EventCheckSteps::SND_CHECK:
                    case EventCheckSteps::PEER_REVIEW_L2:
                        if(is_array($post["chunks"]) && !empty($post["chunks"]))
                        {
                            if($event[0]->step == EventCheckSteps::PEER_REVIEW_L2)
                            {
                                if($event[0]->peer == 1)
                                {
                                    $peer2Check = (array)json_decode($event[0]->peer2Check, true);
                                    if(array_key_exists($event[0]->currentChapter, $peer2Check))
                                    {
                                        if($peer2Check[$event[0]->currentChapter]["done"] == 1)
                                        {
                                            $response["errorType"] = "checkDone";
                                            $response["error"] = __("not_possible_to_save_error");
                                            echo json_encode($response);
                                            exit;
                                        }
                                    }
                                    else
                                    {
                                        echo json_encode($response);
                                        exit;
                                    }
                                }
                                else
                                {
                                    echo json_encode($response);
                                    exit;
                                }
                            }

                            $translationData = $this->_translationModel->getEventTranslationByEventID(
                                $event[0]->eventID,
                                $event[0]->currentChapter);
                            $translation = array();

                            foreach ($translationData as $tv) {
                                $arr = json_decode($tv->translatedVerses, true);
                                $arr["tID"] = $tv->tID;
                                $translation[] = $arr;
                            }

                            if(!empty($translation))
                            {
                                array_walk_recursive($post["chunks"], function(&$item) {
                                    $item = trim($item);
                                });
                                $post["chunks"] = array_filter($post["chunks"], function($chunk) {
                                    $verses = array_filter($chunk, function($v) {
                                        return !empty(strip_tags(trim($v)));
                                    });
                                    $isEqual = sizeof($chunk) == sizeof($verses);
                                    return !empty($chunk) && $isEqual;
                                });

                                $updated = 0;
                                foreach ($translation as $key => $chunk) {
                                    if(!isset($post["chunks"][$key])) continue;

                                    $shouldUpdate = false;
                                    foreach ($post["chunks"][$key] as $verse => $vText)
                                    {
                                        if(!isset($chunk[EventMembers::L2_CHECKER]["verses"][$verse])
                                            || $chunk[EventMembers::L2_CHECKER]["verses"][$verse] != $vText)
                                        {
                                            $shouldUpdate = true;
                                        }

                                    }

                                    $translation[$key][EventMembers::L2_CHECKER]["verses"] = $post["chunks"][$key];

                                    if($shouldUpdate)
                                    {
                                        $tID = $translation[$key]["tID"];
                                        unset($translation[$key]["tID"]);

                                        $encoded = json_encode($translation[$key]);
                                        $json_error = json_last_error();
                                        if($json_error === JSON_ERROR_NONE)
                                        {
                                            $trData = array(
                                                "translatedVerses"  => $encoded
                                            );
                                            $this->_translationModel->updateTranslation(
                                                $trData,
                                                array(
                                                    "tID" => $tID));
                                            $updated++;
                                        }
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

    private function getComments($eventID, $chapter = null, $chunk = null)
    {
        $comments = $this->_translationModel->getCommentsByEvent($eventID, $chapter, $chunk);
        $commentsFinal = array();

        foreach ($comments as $comment) {
            $commentsFinal[$comment->chapter][$comment->chunk][] = $comment;
        }

        unset($comments);

        return $commentsFinal;
    }

    public function saveComment()
    {
        $response = array("success" => false);

        $_POST = Gump::xss_clean($_POST);

        $eventID = isset($_POST["eventID"]) && $_POST["eventID"] != "" ? (integer)$_POST["eventID"] : null;
        $chapter = isset($_POST["chapter"]) && $_POST["chapter"] != "" ? (integer)$_POST["chapter"] : null;
        $chunk = isset($_POST["chunk"]) && $_POST["chunk"] != "" ? (integer)$_POST["chunk"] : null;
        $comment = isset($_POST["comment"]) ? $_POST["comment"] : "";
        $level = isset($_POST["level"]) ? (integer)$_POST["level"] : 1;
        $memberID = Session::get("memberID");

        if($eventID !== null && $chapter !== null && $chunk !== null)
        {
            $memberInfo = (array)$this->_model->getEventMemberInfo($eventID, $memberID);

            if(!empty($memberInfo) && ($memberInfo[0]->translator == $memberID ||
                    $memberInfo[0]->checker == $memberID ||
                    $memberInfo[0]->l2checker == $memberID || $memberInfo[0]->l3checker == $memberID))
            {
                $commentDB = (array)$this->_translationModel->getComment(
                    $eventID,
                    $chapter,
                    $chunk,
                    Session::get("memberID"),
                    $level);

                $postdata = array(
                    "text" => $comment,
                );

                if(!empty($commentDB))
                {
                    if($comment == "")
                    {
                        $result = $this->_translationModel->deleteComment(array("cID" => $commentDB[0]->cID));
                    }
                    else
                    {
                        $result = $this->_translationModel->updateComment($postdata,  array("cID" => $commentDB[0]->cID));
                    }
                }
                else
                {
                    $postdata += array(
                        "eventID" => $eventID,
                        "chapter" => $chapter,
                        "chunk" => $chunk,
                        "memberID" => Session::get("memberID"),
                        "level" => $level
                    );

                    $result = $this->_translationModel->createComment($postdata);
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


    public function getKeywords()
    {
        $response = array("success" => false);

        $_POST = Gump::xss_clean($_POST);

        $eventID = isset($_POST["eventID"]) && $_POST["eventID"] != "" ? (integer)$_POST["eventID"] : null;
        $chapter = isset($_POST["chapter"]) && $_POST["chapter"] != "" ? (integer)$_POST["chapter"] : null;
        $memberID = Session::get("memberID");

        if($eventID !== null && $chapter !== null)
        {
            $keywords = $this->_translationModel->getKeywords([
                "eventID" => $eventID,
                "chapter" => $chapter
            ]);

            if(!empty($keywords))
            {
                $response["success"] = true;
                $response["text"] = $keywords;
            }
        }

        echo json_encode($response);
    }

    public function saveKeyword()
    {
        $response = array("success" => false);

        $_POST = Gump::xss_clean($_POST);

        $eventID = isset($_POST["eventID"]) && $_POST["eventID"] != "" ? (integer)$_POST["eventID"] : null;
        $chapter = isset($_POST["chapter"]) && $_POST["chapter"] != "" ? (integer)$_POST["chapter"] : null;
        $chunk = isset($_POST["chunk"]) && $_POST["chunk"] != "" ? (integer)$_POST["chunk"] : null;
        $index = isset($_POST["index"]) && $_POST["index"] != "" ? (integer)$_POST["index"] : null;
        $verse = isset($_POST["verse"]) ? $_POST["verse"] : "";
        $text = isset($_POST["text"]) ? $_POST["text"] : "";
        $remove = isset($_POST["remove"]) && $_POST["remove"] == "true";
        $memberID = Session::get("memberID");

        if($eventID !== null && $chapter !== null && $chunk !== null && $index > -1 && $verse != null)
        {
            $memberInfo = (array)$this->_model->getEventMemberInfo($eventID, $memberID);

            if(!empty($memberInfo))
            {
                $canKeyword = false;
                $canCreate = true;
                if(!in_array($memberInfo[0]->bookProject, ["tn"]))
                {
                    if($memberInfo[0]->checker == $memberID)
                    {
                        foreach ($memberInfo as $item) {
                            if($item->checkerStep == EventSteps::KEYWORD_CHECK)
                            {
                                if($chapter == $item->chkChapter)
                                {
                                    $canKeyword = true;
                                    break;			
                                }
                            }
                        }
                    }
                    else if($memberInfo[0]->l2checker == $memberID)
                    {
                        $canCreate = false;
                        $events = $this->_model->getMemberEventsForCheckerL2(
                            $memberID,
                            $eventID,
                            null,
                            $chapter
                        );

                        foreach ($events as $event) {
                            if($event->step == EventCheckSteps::KEYWORD_CHECK_L2
                                || $event->step == EventCheckSteps::PEER_REVIEW_L2)
                            {
                                if($chapter == $event->currentChapter)
                                {
                                    $canKeyword = true;
                                    break;
                                }
                            }
                        }
                    }
                }
                else
                {
                    foreach ($memberInfo as $item) {
                        if($item->isChecker
                            && $item->nStep == EventSteps::HIGHLIGHT 
                            && $chapter == $item->tnChapter
                            && $item->stage == "checking")
                        {
                            $canKeyword = true;
                            break;	
                        }
                    }
                }

                if($canKeyword)
                {
                    $result = null;

                    $keyword = $this->_translationModel->getKeywords([
                        "eventID" => $eventID,
                        "chapter" => $chapter,
                        "chunk" => $chunk,
                        "verse" => $verse,
                        "indexOrder" => $index,
                        "text" => $text
                    ]);
                    
                    if(!empty($keyword))
                    {
                        if($remove)
                        {
                            $response["type"] = "remove";
                            $result = $this->_translationModel->deleteKeyword($keyword[0]->kID);
                        }
                        else
                        {
                            $response["error"] = __("keyword_exists_error");
                            echo json_encode($response);
                            return;
                        }
                    }
                    else
                    {
                        if($canCreate)
                        {
                            $postdata = [
                                "eventID" => $eventID,
                                "chapter" => $chapter,
                                "chunk" => $chunk,
                                "verse" => $verse,
                                "indexOrder" => $index,
                                "text" => $text,
                                "memberID" => Session::get("memberID")
                            ];

                            $response["type"] = "add";
                            $result = $this->_translationModel->createKeyword($postdata);
                        }
                    }

                    if($result)
                    {
                        $response["success"] = true;
                        $response["text"] = $text;
                    }
                }
            }
        }

        echo json_encode($response);
    }


    public function applyChecker($eventID, $memberID, $step)
    {
        $canApply = false;

        $profile = Session::get("profile");
        $langs = [];
        foreach ($profile["languages"] as $lang => $item) {
            $langs[] = $lang;
        }

        $allNotifications = $this->_model->getAllNotifications($langs);
        $allNotifications = array_merge(array_values($allNotifications), array_values($this->_notifications));
        $notif = null;
        
        foreach ($allNotifications as $notification) {
            if($eventID == $notification->eventID
                && $memberID == $notification->memberID
                && $step == $notification->step)
            {
                if($notification->checkerID == 0)
                {
                    $canApply = true;
                    $notif = $notification;
                    break;
                }
            }
        }
        
        if($canApply && $notif)
        {
            $postdata = ["checkerID" => Session::get("memberID"), "hideChkNotif" => true];
            $this->_model->updateTranslator($postdata, array("eventID" => $eventID, "memberID" => $memberID));
            
            if(in_array($notif->bookProject, ["tn"]))
                Url::redirect('events/checker-tn/'.$eventID.'/'.$memberID);
            else
                Url::redirect('events/checker/'.$eventID.'/'.$memberID);
        }
        else
        {
            $error[] = __("cannot_apply_checker");
        }

        $data["menu"] = 1;
        $data["notifications"] = $this->_notifications;

        return View::make("Events/CheckerApply")
            ->shares("title", __("apply_checker_l1"))
            ->shares("data", $data)
            ->shares("error", @$error);
    }

    public function applyCheckerNotes($eventID, $memberID, $chapter)
    {
        $canApply = false;
        $isBusy = false;
        
        $myEvent = $this->_model->getEventMember(
            $eventID, 
            Session::get("memberID"));

        if(!empty($myEvent) && $myEvent[0]->step != EventSteps::NONE
            && $myEvent[0]->step != EventSteps::PRAY
            && $myEvent[0]->step != EventSteps::FINISHED)
        {
            $isBusy = true;
        }

        if(!$isBusy)
        {
            $profile = Session::get("profile");
            $langs = [];
            foreach ($profile["languages"] as $lang => $item) {
                $langs[] = $lang;
            }
            $allNotifications = $this->_model->getAllNotifications($langs);
            $allNotifications = array_merge(
                array_values($allNotifications),
                array_values($this->_notifications));
            
            $notif = null;
            $otherCheck = [];
            foreach ($this->_notifications as $event) {
                if($eventID == $event->eventID
                    && $memberID == $event->memberID
                    && $chapter == $event->notesChapter)
                {
                    $otherCheck = (array)json_decode($event->otherCheck, true);
                    foreach ($otherCheck as $chap => $data) {
                        if($chap != $chapter) continue;
                        if($chap == $event->currentChapter) continue;
    
                        if($data["checkerID"] <= 0)
                        {
                            $otherCheck[$chapter]["checkerID"] = Session::get("memberID");
                            $canApply = true;
                            $notif = $event;
                            break;
                        }
                    }
                }
            }
        }
        
        if($canApply && $notif !== null)
        {
            $postdata = [
                "step" => EventSteps::PRAY, 
                "currentChapter" => $chapter,
                "currentChunk" => 0,
                "nTranslator" => $notif->trID,
                "stage" => "checking"
            ];
            $this->_model->updateTranslator(
                $postdata, 
                array(
                    "eventID" => $eventID, 
                    "memberID" => Session::get("memberID")));

            $this->_model->updateTranslator(
                ["otherCheck" => json_encode($otherCheck)], 
                array(
                    "eventID" => $eventID, 
                    "memberID" => $memberID));

            Url::redirect('events/checker-tn/'.$eventID);
            exit;
        }
        else
        {
            $error[] = __("cannot_apply_checker");
        }

        $data["menu"] = 1;
        $data["notifications"] = $this->_notifications;

        return View::make("Events/CheckerApply")
            ->shares("title", __("apply_checker_l1"))
            ->shares("data", $data)
            ->shares("error", @$error);
        exit;
    }

    public function applyCheckerL2($eventID, $memberID, $step, $chapter)
    {
        $canApply = false;

        $profile = Session::get("profile");
        $langs = [];
        foreach ($profile["languages"] as $lang => $item) {
            $langs[] = $lang;
        }

        $allNotifications = $this->_model->getAllNotifications($langs);
        $allNotifications = array_merge(array_values($allNotifications), array_values($this->_notifications));
        $notif = null;

        foreach ($allNotifications as $notification) {
            if($eventID == $notification->eventID
                && $memberID == $notification->memberID
                && $step == $notification->step
                && $chapter == $notification->currentChapter)
            {
                if($step == EventCheckSteps::SND_CHECK)
                {
                    $sndCheck = (array)json_decode($notification->sndCheck, true);
                    if(isset($sndCheck[$chapter]) && $sndCheck[$chapter]["memberID"] == 0)
                    {
                        $sndCheck[$chapter]["memberID"] = Session::get("memberID");
                        $notification->sndCheck = json_encode($sndCheck);
                        $notif = $notification;
                        $canApply = true;
                    }
                }
                else if($step == EventCheckSteps::PEER_REVIEW_L2)
                {
                    $peer1Check = (array)json_decode($notification->peer1Check, true);
                    $peer2Check = (array)json_decode($notification->peer2Check, true);
                    if(isset($peer1Check[$chapter]))
                    {
                        if($peer1Check[$chapter]["memberID"] == 0)
                        {
                            $peer1Check[$chapter]["memberID"] = Session::get("memberID");
                            $notification->peer1Check = json_encode($peer1Check);
                            $notif = $notification;
                            $canApply = true;
                        }
                        else if($peer2Check[$chapter]["memberID"] == 0)
                        {
                            $peer2Check[$chapter]["memberID"] = Session::get("memberID");
                            $notification->peer2Check = json_encode($peer2Check);
                            $notif = $notification;
                            $canApply = true;
                        }
                    }
                }
            }
        }

        if($canApply && $notif)
        {
            $postdata = [
                "sndCheck" => $notif->sndCheck,
                "peer1Check" => $notif->peer1Check,
                "peer2Check" => $notif->peer2Check,
            ];
            $this->_model->updateL2Checker($postdata, [
                "eventID" => $eventID,
                "memberID" => $memberID
            ]);

            Url::redirect('events/checker-l2/'.$eventID.'/'.$memberID.'/'.$chapter);
        }
        else
        {
            $error[] = __("cannot_apply_checker");
        }

        $data["menu"] = 1;
        $data["notifications"] = $this->_notifications;

        return View::make("Events/CheckerApply")
            ->shares("title", __("apply_checker_l1"))
            ->shares("data", $data)
            ->shares("error", @$error);
    }

    public function applyVerbChecker()
    {
        $response = array("success" => false);

        $_POST = Gump::xss_clean($_POST);

        $eventID = isset($_POST["eventID"]) && $_POST["eventID"] != "" ? (integer)$_POST["eventID"] : null;
        $chkID = isset($_POST["chkID"]) && $_POST["chkID"] != "" ? (integer)$_POST["chkID"] : null;
        $chkName = isset($_POST["chkName"]) && preg_match("/^[^0-9!@#\$%\^&\*\(\)_\-\+=\.,\?\/\\\[\]\{\}\|\"]+$/", $_POST["chkName"]) ? trim($_POST["chkName"]) : null;
        $memberID = Session::get("memberID");

        if($eventID !== null && ($chkID != null || $chkName != null))
        {
            $event = $this->_model->getMemberEvents($memberID, EventMembers::TRANSLATOR, $eventID);
            if($chkID != null)
            {
                $chkMember = $this->_membersModel->getMembers([$chkID]);
                if(!empty($chkMember))
                    $chkName = $chkMember[0]->firstName . " " . mb_substr($chkMember[0]->lastName, 0, 1).".";
                else
                {
                    $chkID = null;
                    $chkName = null;
                }
            }

            if(!empty($event) && $chkName != null)
            {
                $verbCheck = (array)json_decode($event[0]->verbCheck, true);
                $checker = $chkID != null ? $chkID : $chkName;

                if($event[0]->step == EventSteps::VERBALIZE && $event[0]->checkDone == false
                    && !array_key_exists($event[0]->currentChapter, $verbCheck))
                {
                    $verbCheck[$event[0]->currentChapter] = $checker;
                    $postdata["verbCheck"] = json_encode($verbCheck);
                    $postdata["checkerID"] = $chkID;
                    $postdata["checkDone"] = true;

                    $upd = $this->_model->updateTranslator($postdata, array("eventID" => $eventID, "memberID" => $memberID));
                    if($upd)
                    {
                        $response["success"] = true;
                        $response["chkName"] = $chkName;
                    }
                    else
                    {
                        $response["error"] = "not_saved";
                    }
                }
                else
                {
                    $response["error"] = "wrong_step";
                }
            }
            else
            {
                $response["error"] = "wrong_event_or_member";
            }
        }
        else
        {
            $response["error"] = "forbidden_name_format";
        }

        echo json_encode($response);
    }

    public function getEventMembers()
    {
        $response = array("success" => false);

        if (!Session::get('isAdmin') && !Session::get('isSuperAdmin'))
        {
            $response["error"] = __("not_enough_rights_error");
            echo json_encode($response);
            return;
        }

        $_POST = Gump::xss_clean($_POST);

        $eventID = isset($_POST["eventID"]) && $_POST["eventID"] != "" ? (integer)$_POST["eventID"] : null;
        $mode = isset($_POST["mode"]) && $_POST["mode"] != "" ? $_POST["mode"] : "ulb";
        $memberIDs = isset($_POST["memberIDs"]) && $_POST["memberIDs"] != "" ? (array)$_POST["memberIDs"] : [];
        $manageMode = isset($_POST["manageMode"]) && $_POST["manageMode"] != "" ? $_POST["manageMode"] : "l1";
        
        if($eventID !== null && $memberIDs !== null)
        {
            $event = $this->_model->getEvent($eventID);

            if(!empty($event))
            {
                $admins = (array)json_decode($event[0]->admins, true);

                if(in_array(Session::get("memberID"), $admins) || Session::get('isSuperAdmin'))
                {
                    if($manageMode == "l1")
                        $members = $this->_model->getMembersForEvent($eventID);
                    else if($manageMode == "l2")
                        $members = $this->_model->getMembersForL2Event($eventID);
                    
                    foreach ($members as $key => $member) {
                        $members[$key]["name"] = $member["firstName"] . " " . mb_substr($member["lastName"], 0, 1).".";

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

    /**
     * Get notifications for user
     */
    public function getNotifications()
    {
        $data["notifs"] = array();

        if(!empty($this->_notifications))
        {
            foreach ($this->_notifications as $notification)
            {
                if(!in_array($notification->bookProject, ["tn"]) 
                    || !isset($notification->notesChapter))
                {
                    $notification->notesChapter = !isset($notification->notesChapter)
                        ? $notification->currentChapter : $notification->notesChapter;
                    
                    $text = __("checker_apply", [
                        $notification->firstName . " " . mb_substr($notification->lastName, 0, 1).".",
                        __($notification->step.(in_array($notification->bookProject, ["tn"]) ? "_tn" : "")),
                        $notification->bookName,
                        ($notification->currentChapter == 0 ? __("intro") : $notification->notesChapter),
                        $notification->tLang,
                        __($notification->bookProject)
                    ]);
    
                    $note["link"] = "/events/checker/".$notification->eventID."/"
                        .$notification->memberID."/".$notification->step."/"
                        .(isset($notification->manageMode) ? $notification->currentChapter."/" : "")
                        ."apply";
                    $note["anchor"] = "check:".$notification->eventID.":".$notification->memberID;
                    $note["text"] = $text;
                    $note["step"] = $notification->step;
                }
                else  
                {
                    $text = __("checker_notes_apply", [
                        $notification->firstName . " " . mb_substr($notification->lastName, 0, 1).".",
                        $notification->bookName,
                        ($notification->notesChapter == 0 ? __("front") : $notification->notesChapter),
                        $notification->tLang,
                        __($notification->bookProject)
                    ]);
    
                    $note["link"] = "/events/checker/".$notification->eventID."/"
                        .$notification->memberID."/notes/"
                        .$notification->notesChapter."/apply";
                    $note["anchor"] = "check:".$notification->eventID.":".$notification->memberID;
                    $note["text"] = $text;
                }
                
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

    
    /**
     * All notifications list page
     * @return mixed
     */
    public function allNotifications()
    {
        $data["notifications"] = $this->_notifications;

        $profile = Session::get("profile");
        $langs = array();
        foreach ($profile["languages"] as $lang => $item) {
            $langs[] = $lang;
        }

        $data["menu"] = 1;
        $data["all_notifications"] = $this->_model->getAllNotifications($langs);
        $data["all_notifications"] = array_merge($data["all_notifications"],
            $this->_notifications);

        return View::make("Events/Notifications")
            ->shares("title", __("all_notifications_title"))
            ->shares("data", $data);
    }

    /**
     * Add or remove chapter user translating
     */
    public function assignChapter()
    {
        $response = array("success" => false);

        if (!Session::get('isAdmin') && !Session::get('isSuperAdmin'))
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
        $manageMode = isset($_POST["manageMode"]) && $_POST["manageMode"] != "" ? $_POST["manageMode"] : "l1";

        if($eventID !== null && $chapter !== null && $memberID !== null && $action !== null)
        {
            $userType = EventMembers::TRANSLATOR;
            if($manageMode == "l2")
                $userType = EventMembers::L2_CHECKER;
            
            $data["event"] = $this->_model->getMemberEvents($memberID, $userType, $eventID, true);
            
            if(!empty($data["event"]))
            {
                $admins = (array)json_decode($data["event"][0]->admins, true);
                $mode = $data["event"][0]->bookProject;
                
                if(in_array(Session::get("memberID"), $admins) || Session::get('isSuperAdmin'))
                {
                    $data["chapters"] = [];
                    if($data["event"][0]->bookProject == "tn")
                        $data["chapters"][0] = [];

                    for($i=1; $i <= $data["event"][0]->chaptersNum; $i++)
                    {
                        $data["chapters"][$i] = [];
                    }

                    $chapters = $this->_model->getChapters($data["event"][0]->eventID);

                    foreach ($chapters as $chap) {
                        $tmp["trID"] = $chap["trID"];
                        $tmp["memberID"] = $chap["memberID"];
                        $tmp["chunks"] = json_decode($chap["chunks"], true);
                        $tmp["done"] = $chap["done"];
                        $tmp["l2memberID"] = $chap["l2memberID"];
                        $tmp["l2chID"] = $chap["l2chID"];
                        $tmp["l2checked"] = $chap["l2checked"];

                        $data["chapters"][$chap["chapter"]] = $tmp;
                    }
                    
                    if(isset($data["chapters"][$chapter]) && empty($data["chapters"][$chapter]))
                    {
                        if($action == "add")
                        {
                            $postdata = [
                                "eventID" => $eventID,
                                "trID" => $data["event"][0]->trID,
                                "memberID" => $data["event"][0]->myMemberID,
                                "chapter" => $chapter,
                                "chunks" => "[]",
                                "done" => false
                            ];
                            
                            $assignChapter = $this->_model->assignChapter($postdata);
                            $data["chapters"][$chapter] = $postdata;
                            
                            // Change translator's step to pray when at least one chapter is assigned to him or all chapters finished
                            if(sizeof(array_filter($data["chapters"], function ($v) use($data) {
                                    return isset($v["memberID"])
                                    && $v["memberID"] == $data["event"][0]->myMemberID;
                                })) == 1 || $data["event"][0]->step == EventSteps::FINISHED) {
                                $this->_model->updateTranslator(
                                    ["step" => EventSteps::PRAY, "translateDone" => false], 
                                    ["trID" => $data["event"][0]->trID]);
                            }

                            if($assignChapter)
                            {
                                $response["success"] = true;
                            }
                            else
                            {
                                $response["error"] = __("error_ocured", [$assignChapter]);
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
                            $translations = $this->_translationModel->getTranslationByEventID($eventID, $chapter);
                            // Check if chapter has translations
                            $hasTranslations = !empty($translations);
                            
                            // Check if chapter has L2 translations
                            if($manageMode == "l2")
                            {
                                $trVerses = (array) json_decode($translations[0]->translatedVerses);
                                $l2Verses = $trVerses[EventMembers::L2_CHECKER];
                                
                                $hasTranslations = !empty($l2Verses->verses);
                            }
                            
                            if(!$hasTranslations)
                            {
                                if($manageMode == "l1")
                                {
                                    if($data["chapters"][$chapter]["memberID"] == $memberID)
                                    {
                                        $removeChapter = $this->_model->removeChapter([
                                            "eventID" => $eventID,
                                            "memberID" => $memberID,
                                            "chapter" => $chapter]);
                                        $data["chapters"][$chapter] = [];

                                        $trPostData = [];

                                        $noMoreChapters = empty(array_filter($data["chapters"], function ($v) use($data) {
                                            return isset($v["memberID"])
                                            && $v["memberID"] == $data["event"][0]->myMemberID;
                                        }));

                                        // Clear translator data to default if current chapter was removed
                                        // Change translator's step to NONE when no chapter is assigned to him
                                        if($data["event"][0]->currentChapter == $chapter || $noMoreChapters)
                                        {
                                            $trPostData["step"] = $noMoreChapters ? EventSteps::NONE : EventSteps::PRAY;
                                            $trPostData["currentChapter"] = in_array($mode, ["tn"]) ? -1 : 0;
                                            $trPostData["currentChunk"] = 0;
                                            $trPostData["checkerID"] = 0;
                                            $trPostData["checkDone"] = 0;
                                            $trPostData["hideChkNotif"] = 1;

                                            $verbCheck = (array)json_decode($data["event"][0]->verbCheck, true);
                                            if(array_key_exists($chapter, $verbCheck))
                                            {
                                                unset($verbCheck[$chapter]);
                                                $trPostData["verbCheck"] = json_encode($verbCheck);
                                            }
                                        }

                                        if(!empty($trPostData))
                                        {
                                            $this->_model->updateTranslator($trPostData, 
                                                ["trID" => $data["event"][0]->trID]);
                                        }

                                        if($removeChapter)
                                        {
                                            $response["success"] = true;
                                        }
                                        else
                                        {
                                            $response["error"] = __("error_ocured", array($removeChapter));
                                        }
                                    }
                                    else
                                    {
                                        $response["error"] = __("error_ocured", array("wrong parameters"));
                                    }
                                }
                                else if($manageMode == "l2")
                                {
                                    if($data["chapters"][$chapter]["l2memberID"] == $memberID)
                                    {
                                        $removeChapter = $this->_model->updateChapter([
                                            "l2memberID" => 0,
                                            "l2chID" => 0
                                        ],[
                                            "eventID" => $eventID,
                                            "chapter" => $chapter
                                        ]);
                                        $data["chapters"][$chapter]["l2memberID"] = 0;
                                        $data["chapters"][$chapter]["l2chID"] = 0;

                                        $trPostData = [];

                                        $noMoreChapters = empty(array_filter($data["chapters"], function ($v) use($data) {
                                            return isset($v["l2memberID"])
                                            && $v["l2memberID"] == $data["event"][0]->memberID;
                                        }));

                                        // Clear checker's data to default if current chapter was removed
                                        // Change checker's step to NONE when no chapter is assigned to him
                                        if($data["event"][0]->currentChapter == $chapter || $noMoreChapters)
                                        {
                                            $trPostData["step"] = $noMoreChapters ? EventCheckSteps::NONE : EventCheckSteps::PRAY;
                                            $trPostData["currentChapter"] = 0;
                                        }

                                        if(!empty($trPostData))
                                        {
                                            $this->_model->updateL2Checker($trPostData, 
                                                ["l2chID" => $data["event"][0]->l2chID]);
                                        }

                                        if($removeChapter)
                                        {
                                            $response["success"] = true;
                                        }
                                        else
                                        {
                                            $response["error"] = __("error_ocured", array($removeChapter));
                                        }
                                    }
                                    else
                                    {
                                        $response["error"] = __("error_ocured", array("wrong parameters"));
                                    }
                                }
                            }
                            else
                            {
                                $response["error"] = __("event_translating_error");
                            }
                        }
                        else if($action == "add" && $manageMode == "l2")
                        {
                            if($data["chapters"][$chapter]["l2memberID"] == 0)
                            {
                                $postdata = [
                                    "l2chID" => $data["event"][0]->l2chID,
                                    "l2memberID" => $data["event"][0]->memberID
                                ];

                                $this->_model->updateChapter($postdata, [
                                    "eventID" => $eventID,
                                    "chapter" => $chapter
                                ]);

                                $this->_model->updateL2Checker([
                                    "step" => EventCheckSteps::PRAY,
                                ], [
                                    "eventID" => $eventID,
                                    "memberID" => $data["event"][0]->memberID
                                ]);

                                $response["success"] = true;
                            }
                            else
                            {
                                $response["error"] = __("chapter_aready_assigned_error");
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
        }
        else
        {
            $response["error"] = __("error_ocured", array("wrong parameters"));
        }

        echo json_encode($response);
    }

    /**
     * Delete user from event
     */
    public function deleteEventMember()
    {
        $response = array("success" => false);

        if (!Session::get('isAdmin') && !Session::get('isSuperAdmin'))
        {
            $response["error"] = __("not_enough_rights_error");
            echo json_encode($response);
            return;
        }

        $_POST = Gump::xss_clean($_POST);

        $eventID = isset($_POST["eventID"]) && $_POST["eventID"] != "" ? (integer)$_POST["eventID"] : null;
        $memberID = isset($_POST["memberID"]) && $_POST["memberID"] != "" ? (integer)$_POST["memberID"] : null;
        $mode = isset($_POST["mode"]) && $_POST["mode"] != "" ? $_POST["mode"] : "ulb";
        $manageMode = isset($_POST["manageMode"]) && $_POST["manageMode"] != "" ? $_POST["manageMode"] : "l1";

        if($eventID !== null && $memberID != null)
        {
            $event = $this->_model->getEvent($eventID);
            if(!empty($event))
            {
                $admins = (array)json_decode($event[0]->admins, true);

                if(in_array(Session::get("memberID"), $admins) || Session::get('isSuperAdmin'))
                {
                    $hasChapter = false;
                    $chapters = $this->_model->getChapters($event[0]->eventID);

                    foreach ($chapters as $chap) {
                        $index = "memberID";
                        if($manageMode == "l2")
                            $index = "l2memberID";
                        if($chap[$index] == $memberID)
                        {
                            $hasChapter = true;
                            break;
                        }
                    }

                    if(!$hasChapter)
                    {
                        if($manageMode == "l2")
                            $this->_model->deleteL2Checkers(["eventID" => $eventID, "memberID" => $memberID]);
                        else
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
        }
        else
        {
            $response["error"] = __("error_ocured", array("wrong parameters"));
        }

        echo json_encode($response);
    }

    public function checkBookFinished($chapters, $chaptersNum, $tn = false)
    {
        if(isset($chapters) && is_array($chapters) && !empty($chapters))
        {
            $chaptersDone = 0;
            foreach ($chapters as $chapter) {
                $chk = $tn ? "checked" : "done";
                if(!empty($chapter) && $chapter[$chk])
                    $chaptersDone++;
            }

            if($chaptersNum == $chaptersDone)
                return true;
        }

        return false;
    }

    public function checkInternet()
    {
        return time();
    }

    //-------------------- Private functions --------------------------//

    /**
     * Manually check and set status of event
     * Do not use it without understanding
     */
    private function setUpEventStatus()
    {
        exit;

        $trans = $this->_translationModel->getAllTranslations();

        $prevEvent = 0;
        $currentEvent = 0;
        $prevChapter = 0;
        $currentChapter = 0;
        $currentChunk = 0;
        $events = [];
        $chapters = [];
        $currentChapterDone = true;
        foreach ($trans as $tran) {
            if($tran->state == EventStates::STARTED
                || $tran->translateDone === null) continue;

            if($tran->eventID > $currentEvent)
            {
                if($prevEvent > 0)
                {
                    if($prevChapter > 0)
                    {
                        if($currentChapterDone && sizeof($chapters[$prevChapter]["chunks"]) == $currentChunk)
                        {
                            $chapters[$prevChapter]["done"] = true;
                        }
                    }
                    $events[$prevEvent] = $chapters;
                }

                $prevEvent = $tran->eventID;
                $currentEvent = $tran->eventID;
                $prevChapter = 0;
                $currentChapter = 0;
                $currentChapterDone = false;
                $chapters = (array)json_decode($tran->chapters, true);

                foreach ($chapters as $key => $chapter) {
                    if(empty($chapter)) continue;
                    $chapters[$key]["done"] = false;
                }
            }

            if($tran->chapter != $currentChapter)
            {
                if($prevChapter > 0)
                {
                    if($currentChapterDone && sizeof($chapters[$prevChapter]["chunks"]) == $currentChunk)
                    {
                        $chapters[$prevChapter]["done"] = true;
                    }
                }

                $currentChunk = 0;
                $prevChapter = $tran->chapter;
                $currentChapter = $tran->chapter;
                $currentChapterDone = true;
            }

            $currentChunk++;

            if(!$tran->translateDone)
                $currentChapterDone = false;
        }

        $bookDone = true;
        foreach ($events as $eventID => $chapters) {
            foreach ($chapters as $chapter) {
                if(empty($chapter) || !$chapter["done"])
                    $bookDone = false;
            }

            $postdata = [];
            $postdata["chapters"] = json_encode($chapters);
            if($bookDone)
            {
                echo $eventID." ";
                $postdata["state"] = EventStates::TRANSLATED;
            }

            $this->_model->updateEvent($postdata, ["eventID" => $eventID]);

            $bookDone = true;
        }
    }


    /**
     * Get source text for chapter or chunk
     * @param $data
     * @param bool $getChunk
     * @param bool $isCoTranslator
     * @return array
     */
    private function getSourceText($data, $getChunk = false)
    {
        $currentChapter = $data["event"][0]->currentChapter;
        $currentChunk = $data["event"][0]->state == EventStates::TRANSLATING
            ? $data["event"][0]->currentChunk : 0;

        $usfm = $this->_model->getCachedSourceBookFromApi(
            $data["event"][0]->sourceBible,
            $data["event"][0]->bookCode, 
            $data["event"][0]->sourceLangID,
            $data["event"][0]->abbrID);
        
        if($usfm && !empty($usfm["chapters"]))
        {
            $initChapter = $data["event"][0]->bookProject != "tn" ? 0 : -1;
            $currentChunkText = "";
            $chunks = json_decode($data["event"][0]->chunks, true);
            $data["chunks"] = $chunks;

            if($currentChapter == $initChapter)
            {
                $level = "l1";
                if($data["event"][0]->state == EventStates::L2_CHECK)
                {
                    $level = "l2";
                    $memberID = $data["event"][0]->memberID;
                }
                else
                {
                    $memberID = $data["event"][0]->myMemberID;
                }

                $nextChapter = $this->_model->getNextChapter(
                    $data["event"][0]->eventID,
                    $memberID,
                    $level);
                if(!empty($nextChapter))
                    $currentChapter = $nextChapter[0]->chapter;
            }

            if($currentChapter <= $initChapter) return false;

            if(!isset($usfm["chapters"][$currentChapter]))
            {
                return array("error" => __("no_source_error"));
            }

            foreach ($usfm["chapters"][$currentChapter] as $section) {
                foreach ($section as $v => $text) {
                    $data["text"][$v] = $text;
                }
            }

            $arrKeys = array_keys($data["text"]);
            $lastVerse = explode("-", end($arrKeys));
            $lastVerse = $lastVerse[sizeof($lastVerse)-1];
            $data["totalVerses"] = !empty($data["text"]) ?  $lastVerse : 0;
            $data["currentChapter"] = $currentChapter;
            $data["currentChunk"] = $currentChunk;

			
			// TODO write function to return chapters array
			$data["chapters"] = [];
            for($i=1; $i <= sizeof($usfm["chapters"]); $i++)
            {
                $data["chapters"][$i] = [];
            }

            $chapters = $this->_model->getChapters($data["event"][0]->eventID);

            foreach ($chapters as $chapter) {
                $tmp["trID"] = $chapter["trID"];
                $tmp["memberID"] = $chapter["memberID"];
                $tmp["chunks"] = json_decode($chapter["chunks"], true);
                $tmp["done"] = $chapter["done"];

                $data["chapters"][$chapter["chapter"]] = $tmp;
            }
			
            if($getChunk)
            {
                $chapData = $chunks;
                $chunk = $chapData[$currentChunk];
                $fv = $chunk[0];
                $lv = $chunk[sizeof($chunk)-1];

                $data["no_chunk_source"] = true;

                foreach ($data["text"] as $verse => $text) {
                    $v = explode("-", $verse);
                    $map = array_map(function($value) use ($fv, $lv) {
                        return $value >= $fv && $value <= $lv;
                    }, $v);
                    $map = array_unique($map);

                    if($map[0])
                    {
                        $currentChunkText[$verse] = $text;
                        $data["no_chunk_source"] = false;
                    }
                }

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

    public function getNotesSourceText($data, $getChunk = false)
    {
        $currentChapter = $data["event"][0]->currentChapter;
        $currentChunk = $data["event"][0]->currentChunk;
        $eventTrID = $data["event"][0]->trID;
        
        $notes = $this->_model->getTranslationNotes(
            $data["event"][0]->bookCode, 
            $data["event"][0]->notesLangID);

        if($notes)
        {
            if($currentChapter == -1)
            {
                $nextChapter = $this->_model->getNextChapter($data["event"][0]->eventID, $data["event"][0]->myMemberID);
                if(!empty($nextChapter))
                    $currentChapter = $nextChapter[0]->chapter;
            }

            if($currentChapter <= -1) return false;
            
            if(isset($notes[$currentChapter]))
            {
                ksort($notes[$currentChapter]);
                $data["notes"] = $notes[$currentChapter];
                $data["currentChapter"] = $currentChapter;
                $data["currentChunk"] = $currentChunk;
                
                $chunks = json_decode($data["event"][0]->chunks, true);
                $data["chunks"] = $chunks;
                
                if($currentChapter > 0)
                {
                    $chunk_keys = array_keys($notes[$currentChapter]);
                    $tmp = [];
                    $chunkNo = 0;

                    if(isset($data["text"]) && $data["text"] != "")
                    {
                        foreach($data["text"] as $v => $text)
                        {
                            if(!isset($chunk_keys[$chunkNo])) continue;
                            if($v >= $chunk_keys[$chunkNo] 
                                && (isset($chunk_keys[$chunkNo+1])
                                    && $v < $chunk_keys[$chunkNo+1]))
                                {
                                    $tmp[$chunk_keys[$chunkNo]][$v] = $text;
                                }
                            else
                            {
                                $chunkNo++;
                                if(!isset($chunk_keys[$chunkNo]))
                                    $chunkNo--;
                                $tmp[$chunk_keys[$chunkNo]][$v] = $text;
                            }
                        }
    
                        $data["text"] = $tmp;
                        $data["nosource"] = false;
                    }
                    else 
                    {
                        $data["no_chunk_source"] = true;
                        $data["nosource"] = true;
                    }
                }
                else {
                    $data["nosource"] = true;
                }

                if($getChunk)
                {
                    $keys = array_keys($notes[$currentChapter]);
                    $currentChunk = $keys[$currentChunk];
                    $data["chunk"][0] = $currentChunk;
                    $data["notes"] = $notes[$currentChapter][$currentChunk];
                }

                return $data;
            }
            else
            {
                return array("error" => __("no_source_error"));
            }
        }
        else
        {
            return array("error" => __("no_source_error"));
        }
    }


    private function getNotesChunks($notes)
    {
        $chunks = array_keys($notes["notes"]);
        $firstVerse = 0;
        $totalVerses = isset($notes["totalVerses"]) ? $notes["totalVerses"] : 0;
        $arr = [];
        $tmp = [];
        
        foreach ($chunks as $key => $chunk) {
            if(isset($chunks[$key + 1]))
            {
                for($i = $chunk; $i < $chunks[$key + 1]; $i++)
                {
                    $tmp[] = $i;
                }

                $arr[] = $tmp;
                $tmp = [];
            }
            else 
            {
                if($chunk <= $totalVerses)
                {
                    for($i = $chunk; $i <= $totalVerses; $i++)
                    {
                        $tmp[] = $i;
                    }
    
                    $arr[] = $tmp;
                    $tmp = [];
                }
            }
        }

        return $arr;
    }

    private function testChunks($chunks, $totalVerses)
    {
        if(!is_array($chunks) || empty($chunks)) return false;

        $lastVerse = 0;

        foreach ($chunks as $chunk) {
            if(!is_array($chunk) || empty($chunk)) return false;

            // Test if first verse is 1
            if($lastVerse == 0 && $chunk[0] != 1) return false;

			// Test if all verses are in right order
            foreach ($chunk as $verse) {
                if((integer)$verse > ($lastVerse+1)) return false;
                $lastVerse++;
            }
        }

        // Test if all verses added to chunks
        if($lastVerse != $totalVerses) return false;

        return true;
    }

    private function testChunkNotes($chunks, $notes, $chapter)
    {
        if(!is_array($chunks)/* || !is_array($notes[$chapter])*/)
            return false;
        
        $converter = new \Helpers\Markdownify\Converter;
        foreach ($chunks as $key => $chunk) {
            if(trim($chunk) == "")
                return false;

            $md = $converter->parseString($chunk);
            if(trim($md) == "")
                return false;

            $chunks[$key] = $md;
        }
        
        return $chunks;
    }

    private function getUwKeyWords($book, $lang = "en", $chapter, $versesCount)
    {
        $result = array();

        $cat_lang = $lang;

        if($lang == "ceb")
            $cat_lang = "en";

        // Get catalog
        $cat_cache_keyword = "catalog_".$book."_".$cat_lang;

        if(Cache::has($cat_cache_keyword))
        {
            $cat_source = Cache::get($cat_cache_keyword);
            $cat_json = json_decode($cat_source, true);
        }
        else
        {
            $cat_source = $this->_model->getTWcatalog($book, $cat_lang);
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
            foreach ($cat_json["chapters"][$chapter - 1]["frames"] as $key => $frame) {
                $result[$key]["id"] = (integer)$frame["id"];
                $result[$key]["terms"] = array();

                if(isset($result[$key-1]))
                    $result[$key-1]["id"] .= "-".((integer)$frame["id"] - 1);

                $i=0;
                foreach ($frame["items"] as $item) {
                    $term_index = array_search($item["id"], array_column($tw_json, "id"));
                    if($term_index)
                    {
                        $result[$key]["terms"][$i]["word"] = $tw_json[$term_index]["term"];
                        $result[$key]["terms"][$i]["def"] = $tw_json[$term_index]["def"];
                        $i++;
                    }
                }
            }

            $result[sizeof($result)-1]["id"] .= "-".$versesCount;
        }

        return $result;
    }

    private function moveMemberStepBack($member, $toStep, $confirm, $prevChunk = false)
    {
        $mode = $member->bookProject;
        $chk = $member->chk;
        $manageMode = "l1";

        if(isset($member->state) && $member->state == EventStates::L2_CHECK)
            $manageMode = "l2";

        $postData = [];

        // Level 2
        if($manageMode == "l2")
        {
            // do not allow move from "none" and "preparation" steps
            if(EventCheckSteps::enum($member->step, $manageMode) < 2)
                return [];

            // Do not allow to move back more than one step at a time
            if((EventCheckSteps::enum($member->step, $manageMode) - EventCheckSteps::enum($toStep, $manageMode)) > 1)
                return [];

            switch ($toStep)
            {
                case EventCheckSteps::PRAY:
                    $postData["step"] = EventSteps::PRAY;
                    break;

                case EventCheckSteps::CONSUME:
                    $postData["step"] = EventSteps::CONSUME;
                    break;
            }

            return $postData;
        }

        // Level 1
        // do not allow move from "none" and "preparation" steps
        if(EventSteps::enum($member->step, $mode, $chk) < 2)
            return [];

        // Do not allow to move back more than one step at a time
        if((EventSteps::enum($member->step, $mode, $chk) - EventSteps::enum($toStep, $mode, $chk)) > 1)
            return [];

        // Do not allow to move forward, exclusion from READ_CHUNK to BLIND_DRAFT of previous chunk
        if(EventSteps::enum($toStep, $mode, $chk) >= EventSteps::enum($member->step, $mode, $chk)
            && ($toStep == EventSteps::BLIND_DRAFT && !$prevChunk))
            return [];

        switch ($toStep)
        {
            case EventSteps::PRAY:
                $postData["step"] = EventSteps::PRAY;

                if(in_array($mode, ["tn"]))
                {
                    $otherCheck = (array)json_decode($member->otherCheck, true);
                    if(array_key_exists($member->currentChapter, $otherCheck))
                        unset($otherCheck[$member->currentChapter]);
                    $postData["otherCheck"] = json_encode($otherCheck);
                }
                break;

            case EventSteps::CONSUME:
                $postData["step"] = EventSteps::CONSUME;
                $postData["checkerID"] = 0;
                $postData["checkDone"] = false;
                $postData["hideChkNotif"] = true;

                if(!in_array($mode, ["tn"]))
                {
                    $verbCheck = (array)json_decode($member->verbCheck, true);
                    if(array_key_exists($member->currentChapter, $verbCheck))
                        unset($verbCheck[$member->currentChapter]);
                    $postData["verbCheck"] = json_encode($verbCheck);
                }
                else 
                {
                    $trans = $this->_translationModel->getEventTranslation($member->trID, $member->currentChapter);
                    
                    if(!empty($trans) && !$confirm)
                        return ["hasTranslation"];
                    
                    if($member->stage != "checking")
                        $this->_model->updateChapter(["chunks" => "[]"], ["eventID" => $member->eventID, "chapter" => $member->currentChapter]);
    
                    $postData["step"] = EventSteps::CONSUME;
                    $postData["currentChunk"] = 0;
                    $postData["translations"] = true;
                }
                break;

            case EventSteps::HIGHLIGHT:
                $postData["step"] = EventSteps::HIGHLIGHT;
                $postData["checkerID"] = 0;
                $postData["checkDone"] = false;
                $postData["hideChkNotif"] = true;
                
                if(in_array($mode, ["tn"]))
                {
                    $postData["currentChunk"] = 0;
                }
                break;

            case EventSteps::VERBALIZE:
                $postData["step"] = EventSteps::VERBALIZE;

                $verbCheck = (array)json_decode($member->verbCheck, true);
                if(array_key_exists($member->currentChapter, $verbCheck))
                {
                    $postData["checkerID"] = $verbCheck[$member->currentChapter];
                    unset($verbCheck[$member->currentChapter]);
                }
                $postData["verbCheck"] = json_encode($verbCheck);
                $postData["checkerID"] = 0;
                $postData["checkDone"] = false;
                break;

            case EventSteps::CHUNKING:
                $trans = $this->_translationModel->getEventTranslation($member->trID, $member->currentChapter);

                if(!empty($trans) && !$confirm)
                    return ["hasTranslation"];

				$this->_model->updateChapter(["chunks" => "[]"], ["eventID" => $member->eventID, "chapter" => $member->currentChapter]);

                $postData["step"] = EventSteps::CHUNKING;
                $postData["currentChunk"] = 0;
                $postData["translations"] = true;
                break;

            case EventSteps::READ_CHUNK:
                $postData["step"] = EventSteps::READ_CHUNK;
                break;

            case EventSteps::BLIND_DRAFT:
                $postData["step"] = EventSteps::BLIND_DRAFT;
                if($prevChunk)
                {
                    $chunk = $member->currentChunk-1;
                    $postData["currentChunk"] = max(0, $chunk);
                }
                break;

            case EventSteps::SELF_CHECK:
                $postData["step"] = EventSteps::SELF_CHECK;
                $postData["checkerID"] = 0;
                $postData["checkDone"] = false;
                $postData["hideChkNotif"] = true;

                $peerCheck = (array)json_decode($member->peerCheck, true);
                if(array_key_exists($member->currentChapter, $peerCheck))
                    unset($peerCheck[$member->currentChapter]);
                $postData["peerCheck"] = json_encode($peerCheck);
                break;

            case EventSteps::PEER_REVIEW:
                $postData["step"] = EventSteps::PEER_REVIEW;

                $kwCheck = (array)json_decode($member->kwCheck, true);
                if(array_key_exists($member->currentChapter, $kwCheck))
                    unset($kwCheck[$member->currentChapter]);
                $postData["kwCheck"] = json_encode($kwCheck);

                $peerCheck = (array)json_decode($member->peerCheck, true);
                if(array_key_exists($member->currentChapter, $peerCheck))
                {
                    $postData["checkerID"] = $peerCheck[$member->currentChapter];
                    unset($peerCheck[$member->currentChapter]);
                }
                $postData["peerCheck"] = json_encode($peerCheck);
                $postData["hideChkNotif"] = true;

                if($confirm)
                {
                    $postData["checkerID"] = 0;
                    $postData["hideChkNotif"] = false;
                }

                if(in_array($mode, ["tn"]))
                    $postData["translateDone"] = false;

                $postData["checkDone"] = false;
                break;

            case EventSteps::KEYWORD_CHECK:
                $postData["step"] = EventSteps::KEYWORD_CHECK;

                $crCheck = (array)json_decode($member->crCheck, true);
                if(array_key_exists($member->currentChapter, $crCheck))
                    unset($crCheck[$member->currentChapter]);
                $postData["crCheck"] = json_encode($crCheck);

                $kwCheck = (array)json_decode($member->kwCheck, true);
                if(array_key_exists($member->currentChapter, $kwCheck))
                {
                    $postData["checkerID"] = $kwCheck[$member->currentChapter];
                    unset($kwCheck[$member->currentChapter]);
                }
                $postData["kwCheck"] = json_encode($kwCheck);
                $postData["hideChkNotif"] = true;

                if(in_array($mode, ["tn"]))
                {
                    $peerCheck = (array)json_decode($member->peerCheck, true);
                    if(array_key_exists($member->currentChapter, $peerCheck))
                    {
                        $postData["checkerID"] = $peerCheck[$member->currentChapter];
                        unset($peerCheck[$member->currentChapter]);
                    }
                    $postData["peerCheck"] = json_encode($peerCheck);
                }
                
                if($confirm)
                {
                    $postData["checkerID"] = 0;
                    $postData["hideChkNotif"] = false;
                    
                    if(in_array($mode, ["tn"])) // In tN remove notification
                        $postData["hideChkNotif"] = true;
                }

                $postData["checkDone"] = false;
                break;

            case EventSteps::CONTENT_REVIEW:
                $postData["step"] = EventSteps::CONTENT_REVIEW;

                $crCheck = (array)json_decode($member->crCheck, true);
                if(array_key_exists($member->currentChapter, $crCheck))
                {
                    $postData["checkerID"] = $crCheck[$member->currentChapter];
                    unset($crCheck[$member->currentChapter]);
                }
                $postData["crCheck"] = json_encode($crCheck);
                $postData["hideChkNotif"] = true;

                if($confirm)
                {
                    $postData["checkerID"] = 0;
                    $postData["hideChkNotif"] = false;
                }

                $postData["checkDone"] = false;
                break;

            case EventSteps::FINAL_REVIEW:
                $crCheck = (array)json_decode($member->crCheck, true);
                end($crCheck);
                $lastChapter = key($crCheck);

                $translationData = $this->_translationModel->getLastEventTranslation($member->trID);
                $lastChunk = 0;

                if(!empty($translationData))
                    $lastChunk = $translationData[0]->chunk;

                $postData["step"] = EventSteps::FINAL_REVIEW;
                $postData["currentChapter"] = $lastChapter;
                $postData["currentChunk"] = $lastChunk;
                $postData["translateDone"] = false;

                $this->_model->updateChapter(["done" => false], ["eventID" => $member->eventID, "chapter" => $member->currentChapter]);
                break;
        }
        
        return $postData;
    }
}
