<?php
namespace Controllers;

use Core\Controller;
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

        $data['menu'] = 4;

        $data["projects"] = $this->_model->getProjects(Session::get("userName"), true);
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

        $data['menu'] = 4;
        $data['title'] = $this->language->get('project_title');

        $data["project"] = $this->_model->getProjects(Session::get("userName"), true, $projectID);
        $data["events"] = array();
        if(!empty($data["project"]))
        {
            $data["events"] = $this->_model->getEventsByProject($projectID);
        }

        $data["notifications"] = $this->_notifications;
        View::renderTemplate('header', $data);
        View::render('events/project', $data);
        View::renderTemplate('footer', $data);
    }

    public function translator($eventID)
    {
        if (!Session::get('loggedin'))
        {
            Session::set('redirect', 'admin');
            Url::redirect('members/login');
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

                        $sourceText = $this->getSourceText($data);

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

                        if (isset($_POST) && !empty($_POST)) {
                            if ($_POST["confirm_step"]) {
                                //if ($data["event"][0]->cotrStep == EventSteps::DISCUSS || $data["event"][0]->cotrStep == EventSteps::PRE_CHUNKING) {
                                    $this->_model->updateTranslator(array("step" => EventSteps::PRE_CHUNKING), array("trID" => $data["event"][0]->trID));
                                    Url::redirect('events/translator/' . $data["event"][0]->eventID);
                                    exit;
                                //} else {
                                //    $error[] = $this->language->get("cotranslator_not_ready_error");
                                //}
                            }
                        }

                        $sourceText = $this->getSourceText($data);

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
                        $sourceText = $this->getSourceText($data);

                        if (!array_key_exists("error", $sourceText)) {
                            $data = $sourceText;
                        } else {
                            $error[] = $sourceText["error"];
                        }

                        if (isset($_POST) && !empty($_POST)) {
                            if ($_POST["confirm_step"]) {
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

                        $sourceText = $this->getSourceText($data, true);

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
                        $sourceText = $this->getSourceText($data, true);

                        if (!array_key_exists("error", $sourceText)) {
                            $data = $sourceText;
                        } else {
                            $error[] = $sourceText["error"];
                        }

                        if (isset($_POST) && !empty($_POST)) {
                            $_POST = Gump::xss_clean($_POST);

                            if ($_POST["confirm_step"]) {
                                //if ($data["event"][0]->cotrStep != EventSteps::CHUNKING) {
                                    if(trim($_POST["draft"]) != "")
                                    {
                                        $translation = array(
                                            EventMembers::TRANSLATOR => array(
                                                "blind" => trim($_POST["draft"]),
                                                "verses" => array(),
                                                "comments" => array()
                                            ),
                                            EventMembers::L2_CHECKER => array(
                                                "verses" => array(),
                                                "comments" => array()
                                            ),
                                            EventMembers::L3_CHECKER => array(
                                                "verses" => array(),
                                                "comments" => array()
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
                                            "translatedVerses"  => json_encode($translation),
                                            "dateCreate"        => "CURRENT_TIMESTAMP"
                                        );

                                        $tID = $this->_model->createTranslation($trData);

                                        if($tID)
                                        {
                                            $this->_model->updateTranslator(array("step" => EventSteps::SELF_CHECK, "lastTID" => $tID), array("trID" => $data["event"][0]->trID));
                                            Url::redirect('events/translator/' . $data["event"][0]->eventID);
                                            exit;
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
                                //} else {
                                //    $error[] = $this->language->get("cotranslator_not_ready_error");
                                //}
                            }
                        }

                        View::renderTemplate('header', $data);
                        View::render('events/translator', $data, $error);
                        View::render('events/blind_draft', $data, $error);
                        break;

                    case EventSteps::SELF_CHECK:

                        // Get blind draft text
                        $blindDraftText = "";
                        $data["blindDraftText"] = "";
                        if ($data["event"][0]->gwLang != $data["event"][0]->targetLang)
                        {
                            $translationData = $this->_model->getTranslation($data["event"][0]->trID, $data["event"][0]->lastTID);

                            if(!empty($translationData))
                            {
                                $translationVerses = json_decode($translationData[0]->translatedVerses, true);
                                if(is_array($translationVerses) && !empty($translationVerses))
                                {
                                    $blindDraftText = $translationVerses["translator"]["blind"];
                                    $data["blindDraftText"] = $blindDraftText;
                                }
                            }
                        }

                        $sourceText = $this->getSourceText($data, true);

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
                                        $comments = array_combine($sourceText["chunk"], $_POST["comments"]);

                                        $translation = array(
                                            EventMembers::TRANSLATOR => array(
                                                "blind" => $blindDraftText,
                                                "verses" => $verses,
                                                "comments" => $comments
                                            ),
                                            EventMembers::L2_CHECKER => array(
                                                "verses" => array(),
                                                "comments" => array()
                                            ),
                                            EventMembers::L3_CHECKER => array(
                                                "verses" => array(),
                                                "comments" => array()
                                            ),
                                        );

                                        $tID = null;

                                        if($data["event"][0]->gwLang == $data["event"][0]->targetLang)
                                        {
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
                                                "translatedVerses"  => json_encode($translation),
                                                "dateCreate"        => "CURRENT_TIMESTAMP"
                                            );

                                            $tID = $this->_model->createTranslation($trData);
                                        }
                                        else
                                        {
                                            $trData = array(
                                                "translatedVerses"  => json_encode($translation)
                                            );

                                            $tID = $this->_model->updateTranslation($trData, array("trID" => $data["event"][0]->trID, "tID" => $data["event"][0]->lastTID));
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

                                            $this->_model->updateTranslator($postdata, array("trID" => $data["event"][0]->trID));
                                            Url::redirect('events/translator/' . $data["event"][0]->eventID);
                                            exit;
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
                                                $translation[$key][EventMembers::TRANSLATOR]["comments"][$v] = $_POST["chunks"][$key]["comments"][$i];
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
                                    $step = $data["event"][0]->translateDone ? EventSteps::FINISHED : EventSteps::KEYWORD_CHECK;
                                    $hideChkNotif = $step != EventSteps::KEYWORD_CHECK;

                                    $this->_model->updateTranslator(array("step" => $step, "hideChkNotif" => $hideChkNotif), array("trID" => $data["event"][0]->trID));
                                    Url::redirect('events/translator/' . $data["event"][0]->eventID);
                                    exit;
                                }
                            }
                        }

                        $sourceText = $this->getSourceText($data);
                        $cotrSourceText = $this->getSourceText($data, false, true);

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

                                $coTranslation[] = $tmp;

                                if(empty($tmp["translator"]["verses"]))
                                    $cotrReady = false;
                            }

                            if(sizeof($data["chapters"][$data["event"][0]->cotrCurrentChapter]["chunks"]) > sizeof($coTranslation))
                                $cotrReady = false;

                            $data["cotrData"]["cotrReady"] = $cotrReady;
                            $data["cotrData"]["translation"] = $coTranslation;
                        } else {
                            $error[] = $sourceText["error"];
                        }

                        View::renderTemplate('header', $data);
                        View::render('events/translator', $data, $error);
                        View::render('events/peer_review', $data, $error);
                        break;

                    case EventSteps::KEYWORD_CHECK:

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
                                                    $translation[$key][EventMembers::TRANSLATOR]["comments"][$v] = $_POST["chunks"][$key]["comments"][$i];
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
                                        $this->_model->updateTranslator(array("step" => EventSteps::CONTENT_REVIEW, "checkDone" => false), array("trID" => $data["event"][0]->trID));
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

                        $sourceText = $this->getSourceText($data);

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

                        $translationData = $this->_model->getTranslation($data["event"][0]->trID, null, $data["event"][0]->currentChapter);
                        $translation = array();

                        foreach ($translationData as $tv) {
                            $arr = json_decode($tv->translatedVerses, true);
                            $arr["tID"] = $tv->tID;
                            $translation[] = $arr;
                        }

                        $sourceText = $this->getSourceText($data);

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
                                                    $translation[$key][EventMembers::TRANSLATOR]["comments"][$v] = $_POST["chunks"][$key]["comments"][$i];
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
                                        $chaptersNum = 0;
                                        $cotrChaptersNum = 0;
                                        foreach ($sourceText["chapters"] as $chapter => $chunks) {
                                            //if($currentChapter >= $chapter) continue;

                                            if($chunks["trID"] == $data["event"][0]->trID)
                                            {
                                                $currentChapter = $chapter;
                                                $chaptersNum++;
                                                //break;
                                            }
                                            else if($chunks["trID"] == $data["event"][0]->cotrID)
                                            {
                                                $cotrChaptersNum++;
                                            }

                                        }

                                        if($currentChapter != $data["event"][0]->currentChapter)
                                        {
                                            // Current chapter is finished, go to next chapter
                                            $postdata["currentChapter"] = $currentChapter;
                                            $postdata["currentChunk"] = 0;
                                            $postdata["step"] = EventSteps::CONSUME;
                                            $postdata["checkDone"] = false;
                                        }
                                        else
                                        {
                                            // All chapters are finished
                                            // Check what is the next step for partner
                                            $postdata["translateDone"] = true;
                                            if($cotrChaptersNum > $chaptersNum)
                                            {
                                                // co-translator has more chapters to translate
                                                // then got to peer review
                                                $postdata["step"] = EventSteps::PEER_REVIEW;
                                            }
                                            else
                                            {
                                                $postdata["step"] = EventSteps::FINISHED;
                                            }
                                        }

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
        if (!Session::get('loggedin'))
        {
            Session::set('redirect', 'admin');
            Url::redirect('members/login');
        }

        if (!Session::get('verified'))
        {
            $error[] = $this->language->get("account_not_verirfied_error");
        }

        if(!isset($error))
        {
            $data["event"] = $this->_model->getMemberCheckerEvents(Session::get("memberID"), $eventID, $memberID);

            if(!empty($data["event"]))
            {
                if($data["event"][0]->step != EventSteps::FINISHED && !$data["event"][0]->translateDone)
                {
                    if($data["event"][0]->step == EventSteps::KEYWORD_CHECK || $data["event"][0]->step == EventSteps::CONTENT_REVIEW)
                    {
                        if (isset($_POST) && !empty($_POST)) {
                            $_POST = Gump::xss_clean($_POST);

                            if ($_POST["confirm_step"]) {
                                $this->_model->updateTranslator(array("checkDone" => true), array("trID" => $data["event"][0]->trID));

                                if($data["event"][0]->step == EventSteps::KEYWORD_CHECK)
                                    Url::redirect('events/checker/' . $data["event"][0]->eventID . "/" . $data["event"][0]->memberID);
                                else
                                    Url::redirect('members');
                                exit;
                            }
                        }

                        if($data["event"][0]->checkDone)
                        {
                            if($data["event"][0]->step == EventSteps::KEYWORD_CHECK)
                            {
                                $data["event"][0]->step = EventSteps::CONTENT_REVIEW;
                                $data["success"] = $this->language->get("checker_translator_not_ready_error");
                            }
                            else
                            {
                                $data["success"] = $this->language->get("checker_translator_finished_error");
                            }
                        }
                        else
                        {
                            $sourceText = $this->getSourceText($data);

                            if (!array_key_exists("error", $sourceText)) {
                                $data = $sourceText;
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
        echo $eventID;
    }

    public function checkerL3($eventID)
    {
        echo $eventID;
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

        $_POST = Gump::xss_clean($_POST);

        $bookCode = isset($_POST['book_code']) && $_POST['book_code'] != "" ? $_POST['book_code'] : null;
        $projectID = isset($_POST['projectID']) && $_POST['projectID'] != "" ? (integer)$_POST['projectID'] : null;
        $userType = isset($_POST['userType']) && $_POST['userType'] != "" ? $_POST['userType'] : null;
        $churchName = isset($_POST['churchName']) && $_POST['churchName'] != "" ? $_POST['churchName'] : null;
        $position = isset($_POST['position']) && $_POST['position'] != "" ? $_POST['position'] : null;
        $expYears = isset($_POST['expYears']) && $_POST['expYears'] != "" ? (integer)$_POST['expYears'] : null;
        $education = isset($_POST['education']) && $_POST['education'] != "" ? $_POST['education'] : null;
        $educationPlace = isset($_POST['educationPlace']) && $_POST['educationPlace'] != "" ? $_POST['educationPlace'] : null;

        if($bookCode == null)
        {
            $error[] = $this->language->get('wrong_book_code');
        }

        if($projectID == null)
        {
            $error[] = $this->language->get('wrong_project_id');
        }

        if($userType == null || !preg_match("/^(".EventMembers::TRANSLATOR."|".EventMembers::L2_CHECKER."|".EventMembers::L3_CHECKER.")$/", $userType))
        {
            $error[] = $this->language->get("wrong_usertype_error");
            echo json_encode(array("error" => Error::display($error)));
            return;
        }

        if($userType == EventMembers::L2_CHECKER || $userType == EventMembers::L3_CHECKER)
        {
            if($churchName == null)
            {
                $error[] = $this->language->get("wrong_church_name_error");
            }

            if($position == null)
            {
                $error[] = $this->language->get("wrong_position_error");
            }

            if($expYears == null)
            {
                $error[] = $this->language->get("wrong_exp_years_error");
            }

            if($education == null)
            {
                $error[] = $this->language->get("wrong_education_error");
            }

            if($educationPlace == null)
            {
                $error[] = $this->language->get("wrong_education_place_error");
            }
        }

        if(!isset($error))
        {
            $event = $this->_model->getEvent($projectID, $bookCode, true);

            if(empty($event))
            {
                $error[] = $this->language->get("event_notexist_error");
                echo json_encode(array("error" => Error::display($error)));
                return;
            }

            $exists = $this->_model->getEventMember($event[0]->eventID, Session::get("memberID"));

            $oldCheckerData = array(
                "churchName" => Session::get("churchName"),
                "position" => Session::get("position"),
                "expYears" => Session::get("expYears"),
                "education" => Session::get("education"),
                "educationPlace" => Session::get("educationPlace")
            );

            $checkerData = array(
                "churchName" => $churchName,
                "position" => $position,
                "expYears" => $expYears,
                "education" => $education,
                "educationPlace" => $educationPlace
            );

            $shouldUpdateChecker = !empty(array_diff_assoc($checkerData, $oldCheckerData));

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
                                $eventData["chapters"] = json_encode($this->assignChapters($event, $trID));

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
                            $l2ID = $this->_model->addL2Checker($l2Data, $checkerData, $shouldUpdateChecker);

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
                            $l3ID = $this->_model->addL3Checker($l3Data, $checkerData, $shouldUpdateChecker);

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
            echo json_encode(array("error" => Error::display($error)));
        }
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

        foreach ($this->_notifications as $notification) {
            if($eventID == $notification->eventID && $memberID == $notification->memberID)
            {
                if($notification->checkerID == 0 || $notification->checkerID == Session::get("memberID"))
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

    private function assignChapters($event, $trID)
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

            if($currentChapter == 0)
            {
                foreach (json_decode($data["event"][0]->chapters) as $chapter => $chunks) {
                    if($chunks->trID == $eventTrID)
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
            $data["text"] = preg_split("/<verse\D+(\d+)\D+>/", $data["text"], -1, PREG_SPLIT_DELIM_CAPTURE);
            $totalVerses = !empty($data["text"]) ? (sizeof($data["text"])-1)/2 : 0;
            $data["totalVerses"] = $totalVerses;
            $data["currentChapter"] = $currentChapter;
            $data["currentChunk"] = $currentChunk;

            $chapters = json_decode($data["event"][0]->chapters, true);
            $data["chapters"] = $chapters;

            if($getChunk)
            {

                $chunks = $chapters[$currentChapter]["chunks"];
                $chunk = $chunks[$currentChunk];
                $fv = $chunk[0];
                $lv = $chunk[sizeof($chunk)-1];

                for($i=2; $i <= sizeof($data["text"]); $i+=2)
                {
                    if(($i/2) >= $fv && ($i/2) <= $lv)
                    {
                        $currentChunkText[] = "<strong><sup>".($i/2)."</sup></strong> ".$data["text"][$i];
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
}