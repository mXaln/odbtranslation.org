<?php
namespace Controllers;

use Core\Controller;
use Helpers\ReCaptcha\Response;
use Helpers\UsfmParser;
use Models\EventsModel;
use Core\Error;
use Core\Language;
use Core\View;
use Helpers\Constants\EventMembers;
use Helpers\Constants\EventStates;
use Helpers\Constants\EventSteps;
use Helpers\Data;
use Helpers\Gump;
use Helpers\Session;
use Helpers\Url;
use Models\MembersModel;
use Models\TranslationsModel;
use phpFastCache\CacheManager;

class EventsController extends Controller
{
    private $_model;
    private $_lang;
    private $_notifications;

    public function __construct()
    {
        parent::__construct();
        $this->_lang = isset($_COOKIE['lang']) ? $_COOKIE['lang'] : 'en';
        $this->language->load('Events', $this->_lang);
        $this->_model = new EventsModel();

        $config = array(
            "storage"   =>  "files",
            "path"      =>  ROOT . "cache"
        );
        CacheManager::setup($config);

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
        $data['title'] = $this->language->get('events_title');

        $data["projects"] = $this->_model->getProjects(Session::get("memberID"), true);
        $data["notifications"] = $this->_notifications;
        View::renderTemplate('header', $data);
        View::render('events/index', $data, $error);
        View::renderTemplate('footer', $data);
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

        $data['title'] = $data["project"][0]->langName . " [".Language::show($data["project"][0]->bookProject, "Events")."]";
        $data["notifications"] = $this->_notifications;
        View::renderTemplate('header', $data);
        View::render('events/project', $data);
        View::renderTemplate('footer', $data);
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

        if(!empty($data["event"]))
        {
            $data['title'] = $data["event"][0]->name ." - ". $data["event"][0]->tLang ." - ". $this->language->get($data["event"][0]->bookProject);

            if($data["event"][0]->state == EventStates::TRANSLATING) {

                switch ($data["event"][0]->step) {
                    case EventSteps::PRAY:

                        if (isset($_POST) && !empty($_POST)) {
                            if ($_POST["confirm_step"]) {
                                $this->_model->updateTranslator(array("step" => EventSteps::CONSUME), array("trID" => $data["event"][0]->trID));
                                Url::redirect('events/translator/' . $data["event"][0]->eventID);
                                exit;
                            }
                        }

                        View::renderTemplate('header', $data);
                        View::render('events/translator', $data, $error);
                        View::render('events/pray', $data, $error);
                        break;

                    case EventSteps::CONSUME:

                        // TODO This just temorarily untill uW pass to USFM format
                        if($data["event"][0]->bookProject != "rsb")
                            $sourceText = $this->getSourceText($data);
                        else
                            $sourceText = $this->getSourceTextUSFM($data);

                        if (!array_key_exists("error", $sourceText)) {
                            $data = $sourceText;
                        } else {
                            $error[] = $sourceText["error"];
                        }

                        if (isset($_POST) && !empty($_POST)) {
                            if ($_POST["confirm_step"]) {
                                //if ($data["event"][0]->cotrStep != EventSteps::PRAY) {
                                $postdata = array(
                                    "step" => EventSteps::DISCUSS,
                                    "currentChapter" => $sourceText["currentChapter"],
                                    "currentChunk" => $sourceText["currentChunk"]
                                );

                                setcookie("temp_tutorial", false, time() - 3600);
                                $this->_model->updateTranslator($postdata, array("trID" => $data["event"][0]->trID));
                                Url::redirect('events/translator/' . $data["event"][0]->eventID);
                                exit;
                                //} else {
                                //    $error[] = $this->language->get("cotranslator_not_ready_error");
                                //}
                            }
                        }

                        View::renderTemplate('header', $data);
                        View::render('events/translator', $data, $error);
                        View::render('events/consume', $data, $error);
                        break;

                    case EventSteps::DISCUSS:

                        $data["cotr_ready"] = $data["event"][0]->cotrStep != EventSteps::PRAY &&
                            $data["event"][0]->cotrStep != EventSteps::CONSUME;

                        if (isset($_POST) && !empty($_POST)) {
                            if ($_POST["confirm_step"]) {
                                if($data["cotr_ready"])
                                {
                                    setcookie("temp_tutorial", false, time() - 3600);
                                    $this->_model->updateTranslator(array("step" => EventSteps::PRE_CHUNKING), array("trID" => $data["event"][0]->trID));
                                    Url::redirect('events/translator/' . $data["event"][0]->eventID);
                                }
                                else {
                                    $error[] = $this->language->get("partner_not_ready_error");
                                }
                            }
                        }

                        // TODO This just temorarily untill uW pass to USFM format
                        if($data["event"][0]->bookProject != "rsb")
                            $sourceText = $this->getSourceText($data);
                        else
                            $sourceText = $this->getSourceTextUSFM($data);

                        if (!array_key_exists("error", $sourceText)) {
                            $data = $sourceText;
                        } else {
                            $error[] = $sourceText["error"];
                        }

                        View::renderTemplate('header', $data);
                        View::render('events/translator', $data, $error);
                        View::render('events/discuss', $data, $error);
                        break;

                    case EventSteps::PRE_CHUNKING:
                        // TODO This just temorarily untill uW pass to USFM format
                        if($data["event"][0]->bookProject != "rsb")
                            $sourceText = $this->getSourceText($data);
                        else
                            $sourceText = $this->getSourceTextUSFM($data);

                        if (!array_key_exists("error", $sourceText)) {
                            $data = $sourceText;
                        } else {
                            $error[] = $sourceText["error"];
                        }

                        if (isset($_POST) && !empty($_POST)) {
                            if ($_POST["confirm_step"]) {
                                setcookie("temp_tutorial", false, time() - 3600);
                                //if ($data["event"][0]->cotrStep != EventSteps::DISCUSS) {
                                $_POST = Gump::xss_clean($_POST);

                                $chunks = json_decode($_POST["chunks_array"]);
                                if($this->testChunks($chunks, $sourceText["totalVerses"]))
                                {
                                    $chapters = json_decode($data["event"][0]->chapters, true);
                                    $chapters[$sourceText["currentChapter"]]["chunks"] = $chunks;

                                    if($this->_model->updateEvent(array("chapters" => json_encode($chapters)), array("eventID" => $data["event"][0]->eventID)))
                                    {
                                        $this->_model->updateTranslator(array("step" => EventSteps::CHUNKING), array("trID" => $data["event"][0]->trID));
                                        Url::redirect('events/translator/' . $data["event"][0]->eventID);
                                        exit;
                                    }
                                    else
                                    {
                                        $error[] = $this->language->get("error_ocured");
                                    }
                                }
                                else
                                {
                                    $error[] = $this->language->get("wrong_chunks_error");
                                }

                                //} else {
                                //    $error[] = $this->language->get("cotranslator_not_ready_error");
                                //}
                            }
                        }

                        View::renderTemplate('header', $data);
                        View::render('events/translator', $data, $error);
                        View::render('events/pre_chunking', $data, $error);
                        break;

                    case EventSteps::CHUNKING:

                        if (isset($_POST) && !empty($_POST)) {
                            if ($_POST["confirm_step"]) {
                                setcookie("temp_tutorial", false, time() - 3600);
                                //if ($data["event"][0]->cotrStep != EventSteps::DISCUSS) {
                                $nextStep = EventSteps::BLIND_DRAFT;
                                if ($data["event"][0]->gwLang == $data["event"][0]->targetLang)
                                    $nextStep = EventSteps::SELF_CHECK;

                                $this->_model->updateTranslator(array("step" => $nextStep), array("trID" => $data["event"][0]->trID));
                                Url::redirect('events/translator/' . $data["event"][0]->eventID);
                                exit;
                                //} else {
                                //    $error[] = $this->language->get("cotranslator_not_ready_error");
                                //}
                            }
                        }

                        // TODO This just temorarily untill uW pass to USFM format
                        if($data["event"][0]->bookProject != "rsb")
                            $sourceText = $this->getSourceText($data, true);
                        else
                            $sourceText = $this->getSourceTextUSFM($data, true);

                        if (!array_key_exists("error", $sourceText)) {
                            $data = $sourceText;
                        } else {
                            $error[] = $sourceText["error"];
                        }

                        View::renderTemplate('header', $data);
                        View::render('events/translator', $data, $error);
                        View::render('events/chunking', $data, $error);
                        break;

                    case EventSteps::BLIND_DRAFT:
                        // TODO This just temorarily untill uW pass to USFM format
                        if($data["event"][0]->bookProject != "rsb")
                            $sourceText = $this->getSourceText($data, true);
                        else
                            $sourceText = $this->getSourceTextUSFM($data, true);

                        if (!array_key_exists("error", $sourceText)) {
                            $data = $sourceText;
                        } else {
                            $error[] = $sourceText["error"];
                        }

                        $shouldUpdate = false;

                        if($data["event"][0]->lastTID > 0)
                        {
                            $translationData = $this->_model->getTranslation($data["event"][0]->trID, $data["event"][0]->lastTID);

                            if(!empty($translationData))
                            {
                                if($translationData[0]->chapter == $data["event"][0]->currentChapter &&
                                    $translationData[0]->chunk == $data["event"][0]->currentChunk)
                                {
                                    $translationVerses = json_decode($translationData[0]->translatedVerses, true);
                                    $data["blindDraftText"] = $translationVerses["translator"]["blind"];
                                    $shouldUpdate = true;
                                }
                            }
                        }

                        if (isset($_POST) && !empty($_POST)) {
                            $_POST = Gump::xss_clean($_POST);

                            if ($_POST["confirm_step"]) {
                                if(trim($_POST["draft"]) != "")
                                {
                                    if(!$shouldUpdate)
                                    {
                                        $translationVerses = array(
                                            EventMembers::TRANSLATOR => array(
                                                "blind" => trim($_POST["draft"]),
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
                                        );

                                        $tID = $this->_model->createTranslation($trData);
                                    }
                                    else
                                    {
                                        $translationVerses["translator"]["blind"] = trim($_POST["draft"]);

                                        $trData = array(
                                            "firstvs"           => $sourceText["chunk"][0],
                                            "translatedVerses"  => json_encode($translationVerses),
                                            "dateUpdate"        => date('Y-m-d H:i:s')
                                        );

                                        if($this->_model->updateTranslation($trData, array("trID" => $data["event"][0]->trID, "tID" => $data["event"][0]->lastTID)))
                                            $tID = $data["event"][0]->lastTID;
                                    }

                                    if((integer)$tID)
                                    {
                                        setcookie("temp_tutorial", false, time() - 3600);
                                        $this->_model->updateTranslator(array("step" => EventSteps::SELF_CHECK, "lastTID" => $tID), array("trID" => $data["event"][0]->trID));
                                        Url::redirect('events/translator/' . $data["event"][0]->eventID);
                                    }
                                    else
                                    {
                                        $error[] = $this->language->get("translation_not_created_error");
                                    }
                                }
                                else
                                {
                                    $error[] = $this->language->get("empty_draft_verses_error");
                                }
                            }
                        }

                        View::renderTemplate('header', $data);
                        View::render('events/translator', $data, $error);
                        View::render('events/blind_draft', $data, $error);
                        break;

                    case EventSteps::SELF_CHECK:
                        $data["comments"] = $this->getComments($data["event"][0]->eventID, $data["event"][0]->currentChapter);

                        $shouldUpdate = false;

                        if($data["event"][0]->lastTID > 0)
                        {
                            $translationData = $this->_model->getTranslation($data["event"][0]->trID, $data["event"][0]->lastTID);

                            if(!empty($translationData))
                            {
                                if($translationData[0]->chapter == $data["event"][0]->currentChapter &&
                                    $translationData[0]->chunk == $data["event"][0]->currentChunk)
                                {
                                    $translationVerses = json_decode($translationData[0]->translatedVerses, true);
                                    $data["blindDraftText"] = $translationVerses[EventMembers::TRANSLATOR]["blind"];
                                    $data["verses"] = $translationVerses[EventMembers::TRANSLATOR]["verses"];
                                    $shouldUpdate = true;
                                }
                            }
                        }

                        // TODO This just temorarily untill uW pass to USFM format
                        if($data["event"][0]->bookProject != "rsb")
                            $sourceText = $this->getSourceText($data, true);
                        else
                            $sourceText = $this->getSourceTextUSFM($data, true);

                        if (!array_key_exists("error", $sourceText)) {
                            $data = $sourceText;
                        } else {
                            $error[] = $sourceText["error"];
                        }

                        if (isset($_POST) && !empty($_POST)) {
                            $_POST = Gump::xss_clean($_POST);

                            if ($_POST["confirm_step"]) {
                                foreach ($_POST["verses"] as $verse) {
                                    if(trim($verse) == "")
                                    {
                                        $error[] = $this->language->get("empty_verses_error");
                                        break;
                                    }
                                }

                                if(!isset($error))
                                {
                                    //$prevStep = EventSteps::BLIND_DRAFT;
                                    //if ($data["event"][0]->gwLang == $data["event"][0]->targetLang)
                                    //    $prevStep = EventSteps::CHUNKING;

                                    //if ($data["event"][0]->cotrStep != $prevStep) {
                                    $verses = array_map("trim", $_POST["verses"]);
                                    $verses = array_combine($sourceText["chunk"], $verses);

                                    if(!$shouldUpdate)
                                    {
                                        $translationVerses = array(
                                            EventMembers::TRANSLATOR => array(
                                                "blind" => "",
                                                "verses" => $verses
                                            ),
                                            EventMembers::L2_CHECKER => array(
                                                "verses" => array()
                                            ),
                                            EventMembers::L3_CHECKER => array(
                                                "verses" => array()
                                            ),
                                        );

                                        $trData = array(
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
                                            "translateDone"     => true,
                                            "dateCreate"        => date('Y-m-d H:i:s')
                                        );

                                        $tID = $this->_model->createTranslation($trData);
                                    }
                                    else
                                    {
                                        $translationVerses[EventMembers::TRANSLATOR]["verses"] = $verses;

                                        $trData = array(
                                            "translatedVerses"  => json_encode($translationVerses),
                                            "translateDone" => true
                                        );

                                        if($this->_model->updateTranslation($trData, array("trID" => $data["event"][0]->trID, "tID" => $data["event"][0]->lastTID)))
                                            $tID = $data["event"][0]->lastTID;
                                    }

                                    if($tID)
                                    {
                                        $postdata = array("lastTID" => $tID);

                                        // Check if chapter is finished
                                        if(array_key_exists($data["event"][0]->currentChunk + 1, $sourceText["chunks"]))
                                        {
                                            // Current chunk is finished, go to next chunk
                                            $postdata["currentChunk"] = $data["event"][0]->currentChunk + 1;
                                            $postdata["step"] = EventSteps::CHUNKING;
                                        }
                                        else
                                        {
                                            // Go to PEER CHECK
                                            $postdata["step"] = EventSteps::PEER_REVIEW;
                                        }

                                        setcookie("temp_tutorial", false, time() - 3600);
                                        $this->_model->updateTranslator($postdata, array("trID" => $data["event"][0]->trID));
                                        Url::redirect('events/translator/' . $data["event"][0]->eventID);
                                    }
                                    else
                                    {
                                        $error[] = $this->language->get("translation_not_created_error");
                                    }
                                    //} else {
                                    //    $error[] = $this->language->get("cotranslator_not_ready_error");
                                    //}
                                }
                            }
                        }

                        View::renderTemplate('header', $data);
                        View::render('events/translator', $data, $error);
                        View::render('events/self_check', $data, $error);
                        break;

                    case EventSteps::PEER_REVIEW:
                        $data["comments"] = $this->getComments($data["event"][0]->eventID, $data["event"][0]->currentChapter);
                        $data["comments_cotr"] = $this->getComments($data["event"][0]->eventID, $data["event"][0]->cotrCurrentChapter);

                        $translationData = $this->_model->getTranslation($data["event"][0]->trID, null, $data["event"][0]->currentChapter);
                        $translation = array();

                        foreach ($translationData as $tv) {
                            $arr = json_decode($tv->translatedVerses, true);
                            $arr["tID"] = $tv->tID;
                            $translation[] = $arr;
                        }

                        // TODO This just temorarily untill uW pass to USFM format
                        if($data["event"][0]->bookProject != "rsb") {
                            $sourceText = $this->getSourceText($data);
                            $cotrSourceText = $this->getSourceText($data, false, true);
                        }
                        else
                        {
                            $sourceText = $this->getSourceTextUSFM($data);
                            $cotrSourceText = $this->getSourceTextUSFM($data, false, true);
                        }

                        if (!array_key_exists("error", $sourceText)) {
                            $data = $sourceText;
                            $data["cotrData"] = $cotrSourceText;

                            $data["translation"] = $translation;

                            $coTranslationTemp = $this->_model->getTranslation($data["event"][0]->cotrID, null, $data["event"][0]->cotrCurrentChapter);
                            $coTranslation = array();

                            $cotrReady = true;

                            if(empty($coTranslationTemp))
                                $cotrReady = false;

                            foreach ($coTranslationTemp as $tv) {
                                $tmp = json_decode($tv->translatedVerses, true);
                                $tmp["tID"] = $tv->tID;
                                $coTranslation[] = $tmp;

                                if(empty($tmp[EventMembers::TRANSLATOR]["verses"]))
                                    $cotrReady = false;
                            }

                            if(sizeof($data["chapters"][$data["event"][0]->cotrCurrentChapter]["chunks"]) > sizeof($coTranslation))
                                $cotrReady = false;

                            $data["cotrData"]["cotrReady"] = $cotrReady;
                            $data["cotrData"]["translation"] = $coTranslation;
                        } else {
                            $error[] = $sourceText["error"];
                        }

                        if (isset($_POST) && !empty($_POST)) {
                            $_POST = Gump::xss_clean($_POST);

                            if($_POST["save"])
                            {
                                foreach ($_POST["chunks"] as $key => $chunk) {
                                    $_POST["chunks"][$key]['verses'] = array_map("trim", $chunk["verses"]);

                                    foreach ($chunk["verses"] as $v => $verse) {
                                        if(trim($verse) == "")
                                        {
                                            $error[] = $this->language->get("empty_verses_error");
                                            break 2;
                                        }
                                    }
                                }

                                if(!isset($error))
                                {
                                    if(!empty($translation))
                                    {
                                        foreach ($translation as $key => $chunk) {
                                            $shouldUpdate = false;
                                            $i=0;
                                            foreach ($chunk[EventMembers::TRANSLATOR]["verses"] as $v => $verse) {
                                                if($verse != $_POST["chunks"][$key]['verses'][$i])
                                                    $shouldUpdate = true;

                                                $translation[$key][EventMembers::TRANSLATOR]["verses"][$v] = $_POST["chunks"][$key]["verses"][$i];
                                                $i++;
                                            }

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
                                if ($_POST["confirm_step"]) {
                                    if($cotrReady)
                                    {
                                        setcookie("temp_tutorial", false, time() - 3600);
                                        $step = $data["event"][0]->translateDone ? EventSteps::FINISHED : EventSteps::KEYWORD_CHECK;
                                        $hideChkNotif = $step != EventSteps::KEYWORD_CHECK;

                                        $this->_model->updateTranslator(array("step" => $step, "hideChkNotif" => $hideChkNotif), array("trID" => $data["event"][0]->trID));
                                        Url::redirect('events/translator/' . $data["event"][0]->eventID);
                                    }
                                    else
                                    {
                                        $error[] = $this->language->get("partner_not_ready_error");
                                    }
                                }
                            }
                        }

                        View::renderTemplate('header', $data);
                        View::render('events/translator', $data, $error);
                        View::render('events/peer_review', $data, $error);
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

                            if($_POST["save"])
                            {
                                if(!$data["event"][0]->checkDone)
                                {
                                    foreach ($_POST["chunks"] as $key => $chunk) {
                                        $_POST["chunks"][$key]['verses'] = array_map("trim", $chunk["verses"]);

                                        foreach ($chunk["verses"] as $v => $verse) {
                                            if(trim($verse) == "")
                                            {
                                                $error[] = $this->language->get("empty_verses_error");
                                                break 2;
                                            }
                                        }
                                    }

                                    if(!isset($error))
                                    {
                                        if(!empty($translation))
                                        {
                                            foreach ($translation as $key => $chunk) {
                                                $shouldUpdate = false;
                                                $i=0;
                                                foreach ($chunk[EventMembers::TRANSLATOR]["verses"] as $v => $verse) {
                                                    if($verse != $_POST["chunks"][$key]['verses'][$i])
                                                        $shouldUpdate = true;

                                                    $translation[$key][EventMembers::TRANSLATOR]["verses"][$v] = $_POST["chunks"][$key]["verses"][$i];
                                                    $i++;
                                                }

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
                                    $error[] = $this->language->get("not_possible_to_save_error");
                                }
                            }
                            else
                            {
                                if ($_POST["confirm_step"]) {
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
                                        $error[] = $this->language->get("checker_not_ready_error");
                                    }
                                }
                            }
                        }

                        // TODO This just temorarily untill uW pass to USFM format
                        if($data["event"][0]->bookProject != "rsb")
                            $sourceText = $this->getSourceText($data);
                        else
                            $sourceText = $this->getSourceTextUSFM($data);

                        if (!array_key_exists("error", $sourceText)) {
                            $data = $sourceText;
                            $data["translation"] = $translation;
                        } else {
                            $error[] = $sourceText["error"];
                        }

                        View::renderTemplate('header', $data);
                        View::render('events/translator', $data, $error);
                        View::render('events/keyword_check', $data, $error);
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

                        // TODO This just temorarily untill uW pass to USFM format
                        if($data["event"][0]->bookProject != "rsb")
                            $sourceText = $this->getSourceText($data);
                        else
                            $sourceText = $this->getSourceTextUSFM($data);

                        if (!array_key_exists("error", $sourceText)) {
                            $data = $sourceText;
                            $data["translation"] = $translation;
                        } else {
                            $error[] = $sourceText["error"];
                        }

                        if (isset($_POST) && !empty($_POST)) {
                            $_POST = Gump::xss_clean($_POST);

                            if($_POST["save"])
                            {
                                if(!$data["event"][0]->checkDone)
                                {
                                    foreach ($_POST["chunks"] as $key => $chunk) {
                                        $_POST["chunks"][$key]['verses'] = array_map("trim", $chunk["verses"]);

                                        foreach ($chunk["verses"] as $v => $verse) {
                                            if(trim($verse) == "")
                                            {
                                                $error[] = $this->language->get("empty_verses_error");
                                                break 2;
                                            }
                                        }
                                    }

                                    if(!isset($error))
                                    {
                                        if(!empty($translation))
                                        {
                                            foreach ($translation as $key => $chunk) {
                                                $shouldUpdate = false;
                                                $i=0;
                                                foreach ($chunk[EventMembers::TRANSLATOR]["verses"] as $v => $verse) {
                                                    if($verse != $_POST["chunks"][$key]['verses'][$i])
                                                        $shouldUpdate = true;

                                                    $translation[$key][EventMembers::TRANSLATOR]["verses"][$v] = $_POST["chunks"][$key]["verses"][$i];
                                                    $i++;
                                                }

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
                                    $error[] = $this->language->get("not_possible_to_save_error");
                                }
                            }
                            else
                            {
                                if ($_POST["confirm_step"]) {
                                    if($data["event"][0]->checkDone)
                                    {
                                        // Check what is the next step
                                        $currentChapter = $data["event"][0]->currentChapter;
                                        $nextChapter = $currentChapter;
                                        $chaptersNum = 0;
                                        $cotrChaptersNum = 0;
                                        foreach ($sourceText["chapters"] as $chapter => $chunks) {
                                            //if($currentChapter >= $chapter) continue;

                                            if($chunks["trID"] == $data["event"][0]->trID)
                                            {
                                                if($currentChapter < $chapter && $currentChapter == $nextChapter)
                                                    $nextChapter = $chapter;
                                                $chaptersNum++;
                                                //break;
                                            }
                                            else if($chunks["trID"] == $data["event"][0]->cotrID)
                                            {
                                                $cotrChaptersNum++;
                                            }
                                        }

                                        if($nextChapter != $currentChapter)
                                        {
                                            // Current chapter is finished, go to the next chapter
                                            $postdata["currentChapter"] = $nextChapter;
                                            $postdata["currentChunk"] = 0;
                                            $postdata["step"] = EventSteps::CONSUME;
                                            $postdata["checkerID"] = 0;
                                            $postdata["checkDone"] = false;
                                            $postdata["hideChkNotif"] = false;
                                        }
                                        else
                                        {
                                            // All chapters are finished
                                            // Check what is the next step for partner
                                            $postdata["translateDone"] = true;
                                            $postdata["checkerID"] = 0;
                                            $postdata["checkDone"] = true;
                                            $postdata["hideChkNotif"] = true;
                                            if($cotrChaptersNum > $chaptersNum)
                                            {
                                                // co-translator has more chapters to translate
                                                // then go to peer review
                                                $postdata["step"] = EventSteps::PEER_REVIEW;
                                            }
                                            else
                                            {
                                                $postdata["step"] = EventSteps::FINISHED;
                                            }
                                        }

                                        setcookie("temp_tutorial", false, time() - 3600);
                                        $this->_model->updateTranslator($postdata, array("trID" => $data["event"][0]->trID));
                                        Url::redirect('events/translator/' . $data["event"][0]->eventID);
                                        exit;
                                    }
                                    else
                                    {
                                        $error[] = $this->language->get("checker_not_ready_error");
                                    }
                                }
                            }
                        }

                        View::renderTemplate('header', $data);
                        View::render('events/translator', $data, $error);
                        View::render('events/content_review', $data, $error);
                        break;

                    case EventSteps::FINISHED:
                        $data["success"] = $this->language->get("you_event_finished_success");

                        View::renderTemplate('header', $data);
                        View::render('events/translator', $data, $error);
                        View::render('events/finished', $data, $error);
                        break;
                }
            }
            else
            {
                $data["error"] = true;
                $error[] = $this->language->get("wrong_event_state_error");
                View::renderTemplate('header', $data);
                View::render('events/translator', $data, $error);
            }
        }
        else
        {
            $error[] = $this->language->get("not_in_event_error");

            View::renderTemplate('header', $data);
            View::render('events/translator', $data, $error);
        }

        View::renderTemplate('footer', $data);
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
            $error[] = $this->language->get("account_not_verirfied_error");
        }

        if(!isset($error))
        {
            $data["event"] = $this->_model->getMemberEventsForChecker(Session::get("memberID"), $eventID, $memberID);
            
            if(!empty($data["event"]))
            {
                if($data["event"][0]->step != EventSteps::FINISHED && !$data["event"][0]->translateDone)
                {
                    if($data["event"][0]->step == EventSteps::KEYWORD_CHECK || $data["event"][0]->step == EventSteps::CONTENT_REVIEW)
                    {
                        $data["comments"] = $this->getComments($data["event"][0]->eventID, $data["event"][0]->currentChapter);

                        if (isset($_POST) && !empty($_POST)) {
                            $_POST = Gump::xss_clean($_POST);

                            if ($_POST["confirm_step"]) {
                                $postdata = array("checkDone" => true);

                                if($data["event"][0]->step == EventSteps::KEYWORD_CHECK)
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
                            $data["success"] = $this->language->get("checker_translator_finished_error");
                        }
                        else
                        {
                            // TODO This just temorarily untill uW pass to USFM format
                            if($data["event"][0]->bookProject != "rsb")
                                $sourceText = $this->getSourceText($data);
                            else
                                $sourceText = $this->getSourceTextUSFM($data);

                            if (!array_key_exists("error", $sourceText)) {
                                $data = $sourceText;

                                $translationTemp = $this->_model->getTranslation($data["event"][0]->trID, null, $data["event"][0]->currentChapter);
                                $translation = array();

                                foreach ($translationTemp as $tv) {
                                    $tmp = json_decode($tv->translatedVerses, true);
                                    $tmp["tID"] = $tv->tID;
                                    $translation[] = $tmp;
                                }

                                $data["translation"] = $translation;
                                $data["keywords"] = $this->getKeyWords($data["event"][0]->bookCode, $data["event"][0]->sourceLangID, $data["event"][0]->currentChapter, $data["totalVerses"]);

                            } else {
                                $error[] = $sourceText["error"];
                            }
                        }
                    }
                    else
                    {
                        $error[] = $this->language->get("checker_translator_not_ready_error");
                    }
                }
                else
                {
                    $data["success"] = $this->language->get("translator_event_finished_success");
                    $data["error"] = "";
                }

                $data['title'] = $data["event"][0]->bookName ." - ". $data["event"][0]->tLang ." - ". $this->language->get($data["event"][0]->bookProject);
            }
            else
            {
                $error[] = $this->language->get("checker_event_error");
                $data['title'] = "Error";
            }
        }

        $data["notifications"] = $this->_notifications;
        View::renderTemplate('header', $data);
        View::render('events/translator', $data, $error);
        View::render('events/checker', $data, $error);
        View::renderTemplate('footer', $data);
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
            $error[] = $this->language->get("account_not_verirfied_error");
        }

        $data["title"] = "Event Information";
        $data["event"] = $this->_model->getEventMember($eventID, Session::get("memberID"), true);
        $data["isAdmin"] = false;

        $memberModel = new MembersModel();

        if(!empty($data["event"]) && $data["event"][0]->state != "started")
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
                            break;
                        }
                    }

                    if(!$data["isAdmin"])
                    {
                        $error[] = $this->language->get("empty_or_not_permitted_event_error");
                    }
                }
                else
                {
                    $error[] = $this->language->get("empty_or_not_permitted_event_error");
                }
            }
            else
            {
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
        }
        else
        {
            $error[] = $this->language->get("empty_or_not_permitted_event_error");
        }

        if(!isset($error))
        {
            $data["chapters"] = json_decode($data["event"][0]->chapters, true);
			
            $translationModel = new TranslationsModel();
            $chunks = $translationModel->getTranslationByEventID($data["event"][0]->eventID);
            $members = array();

            $pairMembers = array();
            $i = 0;
            foreach ($chunks as $index => $chunk) {
                if($chunk->chapter === null)
                {
                    if($chunk->currentChapter > 0)
						$data["chapters"][$chunk->currentChapter]["peer"]["checkerID"] = $chunk->pairMemberID;
                    $pairMembers[$chunk->memberID] = $chunk->pairMemberID;

                    continue;
                }

                if($i < $chunk->chapter)
                {
                    $chunk->kwCheck = (array)json_decode($chunk->kwCheck, true);
                    $chunk->crCheck = (array)json_decode($chunk->crCheck, true);

                    // Peer Check
                    $data["chapters"][$chunk->chapter]["peer"]["checkerID"] = $chunk->pairMemberID;
                    $pairMembers[$chunk->memberID] = $chunk->pairMemberID;
                    if(array_key_exists($chunk->chapter, $chunk->kwCheck))
                    {
                        $data["chapters"][$chunk->chapter]["peer"]["state"] = "finished";
                    }
                    else
                    {
                        if($chunk->step == EventSteps::KEYWORD_CHECK)
                        {
                            $data["chapters"][$chunk->chapter]["peer"]["state"] = "finished";
                        }
                        elseif($chunk->step == EventSteps::PEER_REVIEW)
                        {
                            $data["chapters"][$chunk->chapter]["peer"]["state"] = "in_progress";
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
                            if($chunk->checkerID > 0)
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
                        $members[$chunk->crCheck[$chunk->chapter]] = "";
                    }
                    else
                    {
                        if($chunk->chapter == $chunk->currentChapter)
                        {
                            if($chunk->checkerID > 0 && $data["chapters"][$chunk->chapter]["kwc"]["state"] != "in_progress")
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
            }

            foreach ($data["chapters"] as $key => $chapter) {
                $members[$chapter["memberID"]] = "";



                $data["chapters"][$key]["progress"] = 0;

                if(sizeof($chapter["chunks"]) > 0)
                {
                    // Total translated chunks are 25% of all chapter progress
                    $data["chapters"][$key]["progress"] += sizeof($chapter["chunksData"]) * 25 / sizeof($chapter["chunks"]);
                }


                if(!array_key_exists("peer", $chapter))
                {
                    $data["chapters"][$key]["peer"]["state"] = "not_started";
                    $data["chapters"][$key]["peer"]["checkerID"] = $pairMembers[$chapter["memberID"]];
                }
                else
                {
                    if(!array_key_exists("state", $chapter["peer"]))
                    {
                        $data["chapters"][$key]["peer"]["state"] = "not_started";
                    }
                    else
                    {
                        if($data["chapters"][$key]["peer"]["state"] == "finished")
                            $data["chapters"][$key]["progress"] += 25;
                    }
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
            }

            $adminMembers = $memberModel->getAdminsByGwProject($data["event"][0]->gwProjectID);
            $adminsArr = json_decode($adminMembers[0]->admins, true);
            $empty = array_fill(0, sizeof($adminsArr), "");
            $admins = array_combine($adminsArr, $empty);

            $members += $admins;

            $membersArray = (array)$memberModel->getMembers(array_keys($members));

            foreach ($membersArray as $member) {
                $members[$member->memberID] = $member->userName;
            }

            $members["na"] = "N/A";

            $data["admins"] = $adminsArr;
            $data["members"] = $members;
        }

        $data["notifications"] = $this->_notifications;
        View::renderTemplate('header', $data);
        View::render('events/information', $data, $error);
        View::renderTemplate('footer', $data);
    }


    public function demo($page)
    {
        $data["title"] = $this->language->get("demo");

        View::renderTemplate('header', $data);

        $data["step"] = "";

        switch ($page)
        {
            case "pray":
                View::render('events/demo/2_pray');
                $data["step"] = "pray";
                break;

            case "consume":
                View::render('events/demo/3_consume');
                $data["step"] = "consume";
                break;

            case "verbalize":
                View::render('events/demo/4_verbalize');
                $data["step"] = "discuss";
                break;

            case "prep_chunks":
                View::render('events/demo/5_prep_chunks');
                $data["step"] = "chunking";
                break;

            case "read_chunk":
                View::render('events/demo/6_read_chunk');
                $data["step"] = "chunking";
                break;

            case "blind_draft":
                View::render('events/demo/7_blind_draft');
                $data["step"] = "blind-draft";
                break;

            case "self_check":
                View::render('events/demo/7_self_check');
                $data["step"] = "self-check";
                break;

            case "draft_self_check":
                View::render('events/demo/7_draft_self_check');
                $data["step"] = "self-check";
                break;

            case "peer_review":
                View::render('events/demo/8_peer_review');
                $data["step"] = "peer-review";
                break;

            case "keyword_check":
                View::render('events/demo/9_keyword_check');
                $data["step"] = "keyword-check";
                break;

            case "keyword_check_checker":
                View::render('events/demo/9_keyword_check_checker');
                $data["step"] = "keyword-check";
                break;

            case "content_review":
                View::render('events/demo/10_content_review');
                $data["step"] = "content-review";
                break;

            case "content_review_checker":
                View::render('events/demo/10_content_review_checker');
                $data["step"] = "content-review";
                break;
        }

        View::render('events/demo/demo_header', $data);
        View::renderTemplate('footer', $data);
    }

    public function applyEvent()
    {
        if (!Session::get('loggedin'))
        {
            return;
        }

        if (!Session::get('verified'))
        {
            $error[] = $this->language->get("account_not_verirfied_error");
            echo json_encode(array("error" => Error::display($error)));
            return;
        }

        $data["errors"] = array();
        $profile = Session::get("profile");

        $_POST = Gump::xss_clean($_POST);

        $bookCode = isset($_POST['book_code']) && $_POST['book_code'] != "" ? $_POST['book_code'] : null;
        $projectID = isset($_POST['projectID']) && $_POST['projectID'] != "" ? (integer)$_POST['projectID'] : null;
        $userType = isset($_POST['userType']) && $_POST['userType'] != "" ? $_POST['userType'] : null;

        $education = isset($_POST["education"]) && !empty($_POST["education"]) ? (array)$_POST["education"] : null;
        $ed_area = isset($_POST["ed_area"]) && !empty($_POST["ed_area"]) ? (array)$_POST["ed_area"] : array();
        $ed_place = isset($_POST["ed_place"]) && trim($_POST["ed_place"]) != "" ? trim($_POST["ed_place"]) : "";
        $hebrew_knwlg = isset($_POST["hebrew_knwlg"]) && preg_match("/^[0-4]{1}$/", $_POST["hebrew_knwlg"]) ? $_POST["hebrew_knwlg"] : 0;
        $greek_knwlg = isset($_POST["greek_knwlg"]) && preg_match("/^[0-4]{1}$/", $_POST["greek_knwlg"]) ? $_POST["greek_knwlg"] : 0;
        $church_role = isset($_POST["church_role"]) && !empty($_POST["church_role"]) ? (array)$_POST["church_role"] : array();

        if($bookCode == null)
        {
            $error[] = $this->language->get('wrong_book_code');
            echo json_encode(array("error" => Error::display($error)));
            return;
        }

        if($projectID == null)
        {
            $error[] = $this->language->get('wrong_project_id');
            echo json_encode(array("error" => Error::display($error)));
            return;
        }

        if($userType == null || !preg_match("/^(".EventMembers::TRANSLATOR."|".EventMembers::L2_CHECKER."|".EventMembers::L3_CHECKER.")$/", $userType))
        {
            $error[] = $this->language->get("wrong_usertype_error");
            echo json_encode(array("error" => Error::display($error)));
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
            $event = $this->_model->getEvent($projectID, $bookCode, true);

            if(empty($event))
            {
                $error[] = $this->language->get("event_notexist_error");
                echo json_encode(array("error" => Error::display($error)));
                return;
            }

            $exists = $this->_model->getEventMember($event[0]->eventID, Session::get("memberID"));

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
                        if($exists[0]->translators == null &&
                            $exists[0]->checkers_l2 == null && $exists[0]->checkers_l3 == null)
                        {
                            $trData = array(
                                "memberID" => Session::get("memberID"),
                                "eventID" => $event[0]->eventID
                            );
                            $trID = $this->_model->addTranslator($trData, ($event[0]->translators%2) > 0, $event[0]->lastTrID);

                            if(is_numeric($trID))
                            {
                                $eventData = array();

                                // Change state of event when all translators applied
                                if($this->checkStateFinished($event[0], EventMembers::TRANSLATOR))
                                    $eventData["state"] = EventStates::TRANSLATING;

                                // If translators applied is even add last trID
                                if(($event[0]->translators%2) <= 0)
                                    $eventData["lastTrID"] = $trID;

                                // Assign chapters and chunks to added translator
                                //$eventData["chapters"] = json_encode($this->assignChaptersChunks($event, $trID));
                                $eventData["chapters"] = json_encode($this->assignChapters($event, $trID, Session::get("memberID")));

                                $this->_model->updateEvent($eventData, array("eventID" => $event[0]->eventID));

                                echo json_encode(array("success" => $this->language->get("successfully_applied")));
                            }
                            else
                            {
                                $error[] = $this->language->get("error_ocured", array($trID));
                            }
                        }
                        else
                        {
                            $error[] = $this->language->get("error_member_in_event");
                        }
                    }
                    else
                    {
                        $error[] = $this->language->get("no_translators_available_error");
                    }
                    break;

                case EventMembers::L2_CHECKER:
                    if($event[0]->checkers_l2 < $event[0]->l2CheckersNum)
                    {
                        if($exists[0]->translators == null &&
                            $exists[0]->checkers_l2 == null && $exists[0]->checkers_l3 == null)
                        {
                            $l2Data = array(
                                "memberID" => Session::get("memberID"),
                                "eventID" => $event[0]->eventID
                            );
                            $l2ID = $this->_model->addL2Checker($l2Data, $checkerData);

                            if(is_numeric($l2ID))
                            {
                                /*if($this->checkStateFinished($event[0], EventMembers::L2_CHECKER))
                                {
                                    $this->_model->updateEvent(array("state" => EventStates::L2_CHECK), array("eventID" => $event[0]->eventID));
                                }*/
                                echo json_encode(array("success" => $this->language->get("successfully_applied")));
                            }
                            else
                            {
                                $error[] = $this->language->get("error_ocured", array($l2ID));
                            }
                        }
                        else
                        {
                            $error[] = $this->language->get("error_member_in_event");
                        }
                    }
                    else
                    {
                        $error[] = $this->language->get("no_l2_checkers_available_error");
                    }
                    break;

                case EventMembers::L3_CHECKER:
                    if($event[0]->checkers_l3 < $event[0]->l3CheckersNum)
                    {
                        if($exists[0]->translators == null &&
                            $exists[0]->checkers_l2 == null && $exists[0]->checkers_l3 == null)
                        {
                            $l3Data = array(
                                "memberID" => Session::get("memberID"),
                                "eventID" => $event[0]->eventID
                            );
                            $l3ID = $this->_model->addL3Checker($l3Data, $checkerData);

                            if(is_numeric($l3ID))
                            {
                                /*if($this->checkStateFinished($event[0], EventMembers::L3_CHECKER))
                                {
                                    $this->_model->updateEvent(array("state" => EventStates::L3_CHECK), array("eventID" => $event[0]->eventID));
                                }*/
                                echo json_encode(array("success" => $this->language->get("successfully_applied")));
                            }
                            else
                            {
                                $error[] = $this->language->get("error_ocured", array($l3ID));
                            }
                        }
                        else
                        {
                            $error[] = $this->language->get("error_member_in_event");
                        }
                    }
                    else
                    {
                        $error[] = $this->language->get("no_l3_checkers_available_error");
                    }
                    break;
            }

            if(isset($error))
            {
                echo json_encode(array("error" => Error::display($error)));
            }
        }
        else
        {
            $error[] = $this->language->get('required_fields_empty_error');
            echo json_encode(array("error" => Error::display($error), "errors" => $data["errors"]));
        }
    }


    public function autosaveChunk()
    {
        $response = array("success" => false);

        if (!Session::get('loggedin'))
        {
            $response["errorType"] = "logout";
            $response["error"] = $this->language->get("not_loggedin_error");
            echo json_encode($response);
            return;
        }

        if (!Session::get('verified'))
        {
            $response["errorType"] = "verify";
            $response["error"] = $this->language->get("account_not_verirfied_error");
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
                parse_str($formData, $post);

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
                        if(is_array($post["verses"]) && !empty($post["verses"]))
                        {
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

                            $verses = array_map("trim", $post["verses"]);
                            $verses = array_combine($chunk, $verses);

                            if(!$shoudUpdate)
                            {
                                $translationVerses = array(
                                    EventMembers::TRANSLATOR => array(
                                        "blind" => "",
                                        "verses" => $verses
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
                                foreach ($verses as $key => $verse) {
                                    if(!array_key_exists($key, $translationVerses[EventMembers::TRANSLATOR]["verses"]))
                                    {
                                        $translationVerses[EventMembers::TRANSLATOR]["verses"][$key] = $verse;
                                    }
                                    else
                                    {
                                        if(trim($verse) != "") {
                                            $translationVerses[EventMembers::TRANSLATOR]["verses"][$key] = $verse;
                                        }
                                    }
                                }

                                $trData = array(
                                    "translatedVerses"  => json_encode($translationVerses),
                                );

                                $this->_model->updateTranslation($trData, array("trID" => $event[0]->trID, "tID" => $event[0]->lastTID));
                                $response["success"] = true;
                            }
                        }
                        break;

                    case EventSteps::PEER_REVIEW:
                    case EventSteps::KEYWORD_CHECK:
                    case EventSteps::CONTENT_REVIEW:
                        if(is_array($post["chunks"]) && !empty($post["chunks"]))
                        {
                            $translationData = $this->_model->getTranslation($event[0]->trID, null, $event[0]->currentChapter);
                            $translation = array();

                            foreach ($translationData as $tv) {
                                $arr = json_decode($tv->translatedVerses, true);
                                $arr["tID"] = $tv->tID;
                                $translation[] = $arr;
                            }

                            if(!empty($translation))
                            {
                                foreach ($post["chunks"] as $key => $chunk) {
                                    $post["chunks"][$key]['verses'] = array_map("trim", $chunk["verses"]);
                                }

                                $updated = 0;
                                foreach ($translation as $key => $chunk) {
                                    $shouldUpdate = false;
                                    $i=0;
                                    foreach ($chunk[EventMembers::TRANSLATOR]["verses"] as $v => $verse) {
                                        if($post["chunks"][$key]['verses'][$i] != "" && $verse != $post["chunks"][$key]['verses'][$i])
                                            $shouldUpdate = true;

                                        $translation[$key][EventMembers::TRANSLATOR]["verses"][$v] = $post["chunks"][$key]["verses"][$i];
                                        $i++;
                                    }

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
            $response["error"] = $this->language->get("not_loggedin_error");
            echo json_encode($response);
            return;
        }

        if (!Session::get('verified'))
        {
            $response["error"] = $this->language->get("account_not_verirfied_error");
            echo json_encode($response);
            return;
        }

        $_POST = Gump::xss_clean($_POST);

        $eventID = isset($_POST["eventID"]) && $_POST["eventID"] != "" ? (integer)$_POST["eventID"] : null;
        $chapter = isset($_POST["chapter"]) && $_POST["chapter"] != "" ? (integer)$_POST["chapter"] : null;
        $verse = isset($_POST["verse"]) && $_POST["verse"] != "" ? (integer)$_POST["verse"] : null;
        $comment = isset($_POST["comment"]) ? $_POST["comment"] : "";
        $memberID = Session::get("memberID");

        if($eventID != null && $chapter != null && $verse != null)
        {
            $memberInfo = (array)$this->_model->getEventMemberInfo($eventID, $memberID);

            if(!empty($memberInfo) && ($memberInfo[0]->translator == $memberID ||
                    $memberInfo[0]->l2checker == $memberID || $memberInfo[0]->l3checker == $memberID))
            {
                $transModel = new TranslationsModel();
                $commentDB = (array)$transModel->getComment($eventID, $chapter, $verse, Session::get("memberID"));

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
                        "verse" => $verse,
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

    public function saveCommentAlt()
    {
        $response = array("success" => false);

        if (!Session::get('loggedin'))
        {
            $response["error"] = $this->language->get("not_loggedin_error");
            echo json_encode($response);
            return;
        }

        if (!Session::get('verified'))
        {
            $response["error"] = $this->language->get("account_not_verirfied_error");
            echo json_encode($response);
            return;
        }

        $_POST = Gump::xss_clean($_POST);

        $tID = isset($_POST["tID"]) && $_POST["tID"] != "" ? (integer)$_POST["tID"] : null;
        $verse = isset($_POST["verse"]) && $_POST["verse"] != "" ? (integer)$_POST["verse"] : null;
        $comment = isset($_POST["comment"]) ? trim($_POST["comment"]) : null;

        if($tID !== null && $verse !== null && $comment !== null)
        {
            $dbComment = "@".Session::get("userName").": ".$comment;

            $memberID = Session::get("memberID");
            $translation = $this->_model->getTranslationCheckers($tID, $memberID);

            if($translation[0]->checkerID == $memberID || $translation[0]->pairMemberID == $memberID ||
                $translation[0]->l2memberID == $memberID || $translation[0]->l3memberID == $memberID)
            {
                $translation = json_decode($translation[0]->translatedVerses, true);

                if(array_key_exists(EventMembers::TRANSLATOR, $translation) &&
                    array_key_exists($verse, $translation[EventMembers::TRANSLATOR]["comments_alt"]))
                {
                    $translation[EventMembers::TRANSLATOR]["comments_alt"][$verse] = $dbComment;

                    $upd = $this->_model->updateTranslation(array("translatedVerses" => json_encode($translation)), array("tID" => $tID));

                    if($upd)
                    {
                        $response["success"] = true;
                        $response["text"] = $comment;
                    }
                }
            }
        }

        echo json_encode($response);
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
            $error[] = $this->language->get("account_not_verirfied_error");
            echo json_encode(array("error" => Error::display($error)));
            return;
        }

        $canApply = false;

        $profile = Session::get("profile");
        $langs = array();
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
            $postdata = array("checkerID" => Session::get("memberID"), "hideChkNotif" => true);
            $this->_model->updateTranslator($postdata, array("eventID" => $eventID, "memberID" => $memberID));
            Url::redirect('events/checker/'.$eventID.'/'.$memberID);
            exit;
        }
        else
        {
            $error[] = $this->language->get("cannot_apply_checker");
        }

        $data["title"] = $this->language->get("apply_checker_l1");
        $data["notifications"] = $this->_notifications;
        View::renderTemplate('header', $data);
        View::render('events/checker_apply', $data, $error);
        View::renderTemplate('footer', $data);
    }


    public function getPartnerTranslation()
    {
        if (!Session::get('loggedin'))
        {
            echo $this->language->get("not_loggedin_error");
            return;
        }

        if (!Session::get('verified'))
        {
            echo $this->language->get("account_not_verirfied_error");
            return;
        }

        $_POST = Gump::xss_clean($_POST);

        $eventID = isset($_POST["eventID"]) && $_POST["eventID"] != "" ? (integer)$_POST["eventID"] : null;

        if($eventID !== null)
        {
            $data["event"] = $this->_model->getMemberEvents(Session::get("memberID"), EventMembers::TRANSLATOR, $eventID);

            if(!empty($data["event"]))
            {
                $data["comments_cotr"] = $this->getComments($data["event"][0]->eventID, $data["event"][0]->cotrCurrentChapter);

                // TODO Remove this when site will fully migrate to USFM format
                if($data["event"][0]->bookProject != "rsb")
                    $cotrSourceText = $this->getSourceText($data, false, true);
                else
                    $cotrSourceText = $this->getSourceTextUSFM($data, false, true);

                //$cotrSourceText = $this->getSourceText($data, false, true);

                if (!array_key_exists("error", $cotrSourceText)) {
                    $data["cotrData"] = $cotrSourceText;

                    $coTranslationTemp = $this->_model->getTranslation($data["event"][0]->cotrID, null, $data["event"][0]->cotrCurrentChapter);
                    $coTranslation = array();

                    $cotrReady = true;

                    if(empty($coTranslationTemp))
                        $cotrReady = false;

                    foreach ($coTranslationTemp as $tv) {
                        $tmp = json_decode($tv->translatedVerses, true);
                        $tmp["tID"] = $tv->tID;
                        $coTranslation[] = $tmp;

                        if(empty($tmp["translator"]["verses"]))
                            $cotrReady = false;
                    }

                    if(sizeof($data["chapters"][$data["event"][0]->cotrCurrentChapter]["chunks"]) > sizeof($coTranslation))
                        $cotrReady = false;

                    $data["cotrData"]["cotrReady"] = $cotrReady;
                    $data["cotrData"]["translation"] = $coTranslation;

                    if($data["cotrData"]["cotrReady"]) {
                        $i=2;
                        foreach($data["cotrData"]["translation"] as $key => $chunk) {
                            $count = 0;
                            foreach($chunk["translator"]["verses"] as $verse => $text) {
                                $verses = explode("-", $data["cotrData"]["text"][$i - 1]);
                                if ($count == 0) {
                                    echo '<div class="row">' .
                                        '<div class="col-sm-6">' .
                                        '<p><strong><sup>' . $data["cotrData"]["text"][$i - 1] . '</sup></strong> ' . $data["cotrData"]["text"][$i] . '</p>' .
                                        '</div>' .
                                        '<div class="col-sm-6 verse_with_note">' .
                                        '<p>';
                                }
                                echo '<strong><sup>' . $verse . '</sup></strong>';
                                echo $text;
                                $count++;

                                if ($count == sizeof($verses)) {
                                    $i += 2;
                                    $count = 0;
                                    echo '</p>';
                                    echo '<div class="comments_number">'.
											(array_key_exists($data["cotrData"]["currentChapter"], $data["comments_cotr"]) && array_key_exists($verse, $data["comments_cotr"][$data["cotrData"]["currentChapter"]]) ?
												sizeof($data["comments_cotr"][$data["cotrData"]["currentChapter"]][$verse]) : "").
										 '</div>';
									echo '<img class="editComment" data="'.$data["cotrData"]["currentChapter"].":".$verse.'" width="16px" '.
												'src="' . \Helpers\Url::templatePath() . 'img/'.(trim($commentAlt) == "" ? "edit" : "edit_done").'.png" title="write note"/>' .
											'<div class="comments">';
												if(array_key_exists($data["cotrData"]["currentChapter"], $data["comments_cotr"]) && array_key_exists($verse, $data["comments_cotr"][$data["cotrData"]["currentChapter"]])) 
												{
													foreach($data["comments_cotr"][$data["cotrData"]["currentChapter"]][$verse] as $comment)
													{
														if($comment->memberID == $data["event"][0]->myMemberID)
														{
															echo '<div class="my_comment">'.$comment->text.'</div>';
														}
														else
														{
															echo '<div class="other_comments"><span>'.$comment->userName.':</span> '.$comment->text.'</div>';
														}
													}
												}
										echo '</div>'.
                                        '</div>' .
                                        '</div>';
                                }
                            }
                        }
                        echo '<div class="chunk_divider col-sm-12"></div>';
                    } else {
                        echo '<div class="row">'.
                            '<div class="col-sm-12 cotr_not_ready" style="color: #ff0000;">'.Language::show("partner_not_ready_message", "Events").'</div>'.
                            '</div>';
                    }
                }
            }
        }
    }

    public function checkEvent()
    {
        $response = array("success" => false);

        if (!Session::get('loggedin'))
        {
            $response["error"] = $this->language->get("not_loggedin_error");
            echo json_encode($response);
            return;
        }

        if (!Session::get('verified'))
        {
            $response["error"] = $this->language->get("account_not_verirfied_error");
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
                    $type = $notification->step == EventSteps::KEYWORD_CHECK ? "kw_checker" : "cont_checker";
                    $text = $this->language->get("checker_apply", array(
                        $notification->userName,
                        $notification->bookName,
                        $notification->currentChapter,
                        $notification->tLang,
                        $this->language->get($notification->bookProject)
                    ));

                    $note["link"] = "/events/checker/".$notification->eventID."/".$notification->memberID."/apply";
                    $note["anchor"] = "check:".$notification->eventID.":".$notification->memberID;
                    $note["text"] = $text;
                    $data["notifs"][] = $note;
                }
            }
            else
            {
                $data["noNotifs"] = $this->language->get("no_notifs_msg");
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

        $data["title"] = $this->language->get("all_notifications_title");
        $data["notifications"] = $this->_notifications;

        $profile = Session::get("profile");
        $langs = array();
        foreach ($profile["languages"] as $lang => $item) {
            $langs[] = $lang;
        }

        $data["all_notifications"] = $this->_model->getAllNotifications($langs);
        $data["all_notifications"] += $this->_notifications;

        View::renderTemplate('header', $data);
        View::render('events/notifications', $data, $error);
        View::renderTemplate('footer', $data);
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

    private function assignChapters($event, $trID, $memberID)
    {
        $chapters = json_decode($event[0]->chapters, true);
        $totalNum = $event[0]->translatorsNum;
        $currentNum = $event[0]->translators + 1;
        $isCurrentEven = $currentNum % 2 == 0;

        $chaptersNum = sizeof($chapters);
        $val = round($chaptersNum/$totalNum);
        for($i=0;$i<$totalNum;$i++) {
            $arr[$i] = $val;
        }
        $arr[sizeof($arr)-1] += $chaptersNum - array_sum($arr);
        $arr = $this->reassignChapters($arr);

        $chaptersCount = $arr[$currentNum-1];

        foreach ($chapters as $chapIndex => $chapter) {
            if($chaptersCount > 0)
            {
                if(empty($chapter))
                {
                    $chapters[$chapIndex]["trID"] = $trID;
                    $chapters[$chapIndex]["memberID"] = $memberID;
                    $chapters[$chapIndex]["chunks"] = array();
                }
                else
                {
                    continue;
                }

                $chaptersCount--;
            }
        }

        return $chapters;
    }

    private function assignChaptersChunks($event, $trID)
    {
        $chapters = json_decode($event[0]->chapters, true);
        $totalNum = $event[0]->translatorsNum;
        $currentNum = $event[0]->translators + 1;
        $isCurrentEven = $currentNum % 2 == 0;

        $pairs = $totalNum/2;
        $chaptersNum = sizeof($chapters);
        $val = round($chaptersNum/$pairs);
        for($i=0;$i<$pairs;$i++) {
            $arr[$i] = $val;
        }
        $arr[sizeof($arr)-1] += $chaptersNum - array_sum($arr);

        $arr = $this->reassignChapters($arr);

        $currentPairNum = round($currentNum/2);
        $chaptersCountForPair = $arr[$currentPairNum-1];

        foreach ($chapters as $chapIndex => $chapter) {
            if($chaptersCountForPair > 0)
            {
                $chapKeys = array_values($chapter);
                $checkIndex = $isCurrentEven ? 1 : 0;

                if($chapKeys[$checkIndex] == 0)
                {
                    $i=0;
                    foreach ($chapter as $chunk => $translator) {
                        if($i % 2 == $checkIndex)
                        {
                            $chunkIndex = sprintf("%02d-%02d", $chapIndex, (integer)preg_replace("/\d+-/", "", $chunk));
                            $chapters[$chapIndex][$chunkIndex] = $trID;
                        }

                        $i++;
                    }
                }
                else
                {
                    continue;
                }

                $chaptersCountForPair--;
            }
        }

        return $chapters;
    }

    private function reassignChapters($arr, $index = 2)
    {
        $max = max($arr);
        $min = min($arr);
        $average = round(($max+$min)/2);

        //echo "Max: ".$max . ", Min: ".$min.", Avg.: ".$average."<br>";

        if($average < $max)
        {
            if($arr[sizeof($arr)-1] < $arr[sizeof($arr)-$index])
            {
                $arr[sizeof($arr)-$index]--;
                $arr[sizeof($arr)-1]++;
            }
            else
            {
                $arr[sizeof($arr)-$index]++;
                $arr[sizeof($arr)-1]--;
            }

            $index++;
            return $this->reassignChapters($arr, $index);
        }
        else
        {
            return $arr;
        }
    }

    /**
     * Get source of current chapter or chunk
     * @param array $data
     * @param bool $getChunk
     * @return array
     */
    private function getSourceText($data, $getChunk = false, $isCoTranslator = false)
    {
        $currentChapter = !$isCoTranslator ? $data["event"][0]->currentChapter : $data["event"][0]->cotrCurrentChapter;
        $currentChunk = !$isCoTranslator ? $data["event"][0]->currentChunk : $data["event"][0]->cotrCurrentChunk;
        $eventTrID = !$isCoTranslator ? $data["event"][0]->trID : $data["event"][0]->cotrID;

        $cache_keyword = $data["event"][0]->bookCode."_".$data["event"][0]->sourceLangID."_".$data["event"][0]->bookProject;
        $source = CacheManager::get($cache_keyword);

        if(is_null($source))
        {
            $source = $this->_model->getSourceBookFromApi($data["event"][0]->bookCode, $data["event"][0]->sourceLangID, $data["event"][0]->bookProject);
            $json = json_decode($source, true);

            if(!empty($json))
                CacheManager::set($cache_keyword, $source, 60*60*24*7);
        }
        else
        {
            $json = json_decode($source, true);
        }

        if(!empty($json))
        {
            $currentChapterText = "";
            $currentChunkText = "";
            $totalVerses = 0;

            $chapters = json_decode($data["event"][0]->chapters, true);

            if($currentChapter == 0)
            {
                foreach ($chapters as $chapter => $chunks) {
                    if($chunks["trID"] == $eventTrID)
                    {
                        $currentChapter = $chapter;
                        break;
                    }
                }
            }

            foreach ($json["chapters"][$currentChapter - 1]["frames"] as $frame) {
                $data["text"] .= $frame["text"];
            }

            $data["text"] = preg_replace("/<\/?para.*>/", "", $data["text"]);
            $data["text"] = preg_split("/<verse\D+(\d+(?:-\d+)?)\D+>/", $data["text"], -1, PREG_SPLIT_DELIM_CAPTURE);
            $lastVerse = explode("-", $data["text"][sizeof($data["text"])-2]);
            $lastVerse = $lastVerse[sizeof($lastVerse)-1];
            $data["totalVerses"] = !empty($data["text"]) ?  $lastVerse/*(sizeof($data["text"])-1)/2*/ : 0;
            $data["currentChapter"] = $currentChapter;
            $data["currentChunk"] = $currentChunk;
            $data["chapters"] = $chapters;

            if($getChunk)
            {
                $chunks = $chapters[$currentChapter]["chunks"];
                $chunk = $chunks[$currentChunk];
                $fv = $chunk[0];
                $lv = $chunk[sizeof($chunk)-1];

                for($i=2; $i <= sizeof($data["text"]); $i+=2)
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
                }

                $data["chunks"] = $chunks;
                $data["chunk"] = $chunk;
                $data["totalVerses"] = sizeof($chunk);

                $data["text"] = $currentChunkText;
            }

            return $data;
        }
        else
        {
            return array("error" => $this->language->get("no_source_error"));
        }
    }


    private function getSourceTextUSFM($data, $getChunk = false, $isCoTranslator = false)
    {
        $currentChapter = !$isCoTranslator ? $data["event"][0]->currentChapter : $data["event"][0]->cotrCurrentChapter;
        $currentChunk = !$isCoTranslator ? $data["event"][0]->currentChunk : $data["event"][0]->cotrCurrentChunk;
        $eventTrID = !$isCoTranslator ? $data["event"][0]->trID : $data["event"][0]->cotrID;

        $cache_keyword = $data["event"][0]->bookCode."_".$data["event"][0]->sourceLangID."_".$data["event"][0]->bookProject;
        $source = CacheManager::get($cache_keyword);

        if(is_null($source))
        {
            $source = $this->_model->getSourceBookFromApiUSFM($data["event"][0]->bookProject, $data["event"][0]->abbrID, $data["event"][0]->bookCode, $data["event"][0]->sourceLangID);

            $usfm = UsfmParser::parse($source);

            if(!empty($usfm))
                CacheManager::set($cache_keyword, $source, 60*60*24*7);
        }
        else
        {
            $usfm = UsfmParser::parse($source);
        }

        if(!empty($usfm))
        {
            $currentChapterText = "";
            $currentChunkText = "";
            $totalVerses = 0;

            $chapters = json_decode($data["event"][0]->chapters, true);

            if($currentChapter == 0)
            {
                foreach ($chapters as $chapter => $chunks) {
                    if($chunks["trID"] == $eventTrID)
                    {
                        $currentChapter = $chapter;
                        break;
                    }
                }
            }

            $data["text"][] = ""; // For compatibility with usx parser
            foreach ($usfm["chapters"][$currentChapter] as $section) {
                foreach ($section as $v => $text) {
                    $data["text"][] = $v;
                    $data["text"][] = $text;
                }
            }

            $lastVerse = explode("-", $data["text"][sizeof($data["text"])-2]);
            $lastVerse = $lastVerse[sizeof($lastVerse)-1];
            $data["totalVerses"] = !empty($data["text"]) ?  $lastVerse : 0;
            $data["currentChapter"] = $currentChapter;
            $data["currentChunk"] = $currentChunk;
            $data["chapters"] = $chapters;

            if($getChunk)
            {
                $chunks = $chapters[$currentChapter]["chunks"];
                $chunk = $chunks[$currentChunk];
                $fv = $chunk[0];
                $lv = $chunk[sizeof($chunk)-1];

                for($i=2; $i <= sizeof($data["text"]); $i+=2)
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
                }

                $data["chunks"] = $chunks;
                $data["chunk"] = $chunk;
                $data["totalVerses"] = sizeof($chunk);

                $data["text"] = $currentChunkText;
            }

            return $data;
        }
        else
        {
            return array("error" => $this->language->get("no_source_error"));
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
            $commentsFinal[$comment->chapter][$comment->verse][] = $comment;
        }

        unset($comments);

        return $commentsFinal;
    }

    private function getKeyWords($book, $lang = "en", $chapter, $versesCount)
    {
        $result = array();

        // Get catalog
        $cat_cache_keyword = "catalog_".$book."_".$lang;
        $cat_source = CacheManager::get($cat_cache_keyword);

        if(is_null($cat_source))
        {
            $cat_source = $this->_model->getTWcatalog($book, $lang);
            $cat_json = json_decode($cat_source, true);

            if(!empty($cat_json))
                CacheManager::set($cat_cache_keyword, $cat_source, 60*60*24*7);
        }
        else
        {
            $cat_json = json_decode($cat_source, true);
        }

        // Get keywords
        $tw_cache_keyword = "tw_".$lang;
        $tw_source = CacheManager::get($tw_cache_keyword);

        if(is_null($tw_source))
        {
            $tw_source = $this->_model->getTWords($lang);
            $tw_json = json_decode($tw_source, true);

            if(!empty($tw_json))
                CacheManager::set($tw_cache_keyword, $tw_source, 60*60*24*7);
        }
        else
        {
            $tw_json = json_decode($tw_source, true);
        }

        if(!empty($cat_json) && !empty($tw_json))
        {
            $i=0;
            foreach ($cat_json["chapters"][$chapter - 1]["frames"] as $key => $frame) {
                $result[$key]["id"] = (integer)$frame["id"];

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