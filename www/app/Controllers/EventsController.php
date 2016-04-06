<?php
namespace Controllers;

use Core\Controller;
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

    public function __construct()
    {
        parent::__construct();
        $this->_lang = isset($_COOKIE['lang']) ? $_COOKIE['lang'] : 'en';
        $this->language->load('Events', $this->_lang);
        $this->_model = new \Models\EventsModel();

        $config = array(
            "storage"   =>  "files",
            "path"      =>  ROOT . "cache"
        );
        CacheManager::setup($config);
    }

    public function index()
    {
        if (!Session::get('loggedin'))
        {
            Url::redirect('members/login');
        }

        $data['menu'] = 4;

        $data["projects"] = $this->_model->getProjects(Session::get("userName"), true);

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

        $data["event"] = $this->_model->getMemberEvents(Session::get("memberID"), EventMembers::TRANSLATOR, $eventID);

        if(!empty($data["event"]))
        {
            $data['title'] = $data["event"][0]->name ." - ". $data["event"][0]->langName ." - ". $this->language->get($data["event"][0]->bookProject);

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

                        if (isset($_POST) && !empty($_POST)) {
                            if ($_POST["confirm_step"]) {
                                if ($data["event"][0]->cotrStep != EventSteps::PRAY) {
                                    $this->_model->updateTranslator(array("step" => EventSteps::DISCUSS, "currentChunk" => Session::get("currentChunk")), array("trID" => $data["event"][0]->trID));
                                    Url::redirect('events/translator/' . $data["event"][0]->eventID);
                                    exit;
                                } else {
                                    $error[] = $this->language->get("cotranslator_not_ready_error");
                                }
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
                        View::render('events/consume', $data, $error);
                        break;

                    case EventSteps::DISCUSS:

                        if (isset($_POST) && !empty($_POST)) {
                            if ($_POST["confirm_step"]) {
                                if ($data["event"][0]->cotrStep != EventSteps::CONSUME) {
                                    $this->_model->updateTranslator(array("step" => EventSteps::PRE_CHUNKING), array("trID" => $data["event"][0]->trID));
                                    Url::redirect('events/translator/' . $data["event"][0]->eventID);
                                    exit;
                                } else {
                                    $error[] = $this->language->get("cotranslator_not_ready_error");
                                }
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
                        if (isset($_POST) && !empty($_POST)) {
                            if ($_POST["confirm_step"]) {
                                if ($data["event"][0]->cotrStep != EventSteps::DISCUSS) {
                                    $this->_model->updateTranslator(array("step" => EventSteps::CHUNKING), array("trID" => $data["event"][0]->trID));
                                    Url::redirect('events/translator/' . $data["event"][0]->eventID);
                                    exit;
                                } else {
                                    $error[] = $this->language->get("cotranslator_not_ready_error");
                                }
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
                        View::render('events/pre_chunking', $data, $error);
                        break;

                    case EventSteps::CHUNKING:

                        if (isset($_POST) && !empty($_POST)) {
                            if ($_POST["confirm_step"]) {
                                if ($data["event"][0]->cotrStep != EventSteps::DISCUSS) {
                                    $nextStep = EventSteps::BLIND_DRAFT;
                                    if ($data["event"][0]->gwLang == $data["event"][0]->targetLang)
                                        $nextStep = EventSteps::SELF_CHECK;

                                    $this->_model->updateTranslator(array("step" => $nextStep), array("trID" => $data["event"][0]->trID));
                                    Url::redirect('events/translator/' . $data["event"][0]->eventID);
                                    exit;
                                } else {
                                    $error[] = $this->language->get("cotranslator_not_ready_error");
                                }
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
                        if (isset($_POST) && !empty($_POST)) {
                            $_POST = Gump::xss_clean($_POST);

                            if ($_POST["confirm_step"]) {
                                if ($data["event"][0]->cotrStep != EventSteps::CHUNKING) {
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

                                        $chunkVerses = json_decode(Session::get("chunk_verses"));

                                        $trData = array(
                                            "projectID"         => $data["event"][0]->projectID,
                                            "eventID"           => $data["event"][0]->eventID,
                                            "trID"              => $data["event"][0]->trID,
                                            "targetLang"        => $data["event"][0]->targetLang,
                                            "bookProject"       => $data["event"][0]->bookProject,
                                            "abbrID"            => $data["event"][0]->abbrID,
                                            "bookCode"          => $data["event"][0]->bookCode,
                                            "chapter"           => (integer)preg_replace("/-\d+/", "", Session::get("currentChunk")),
                                            "chunk"             => $data["event"][0]->currentChunk,
                                            "firstvs"           => $chunkVerses[0],
                                            "translatedVerses"  => json_encode($translation),
                                            "dateCreate"        => "CURRENT_TIMESTAMP"
                                        );

                                        if($this->_model->createTranslation($trData))
                                        {
                                            $this->_model->updateTranslator(array("step" => EventSteps::SELF_CHECK), array("trID" => $data["event"][0]->trID));
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
                                } else {
                                    $error[] = $this->language->get("cotranslator_not_ready_error");
                                }
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
                        View::render('events/blind_draft', $data, $error);
                        break;

                    case EventSteps::SELF_CHECK:

                        // Get blind draft text
                        $blindDraftText = "";
                        $data["blindDraftText"] = "";
                        if ($data["event"][0]->gwLang != $data["event"][0]->targetLang)
                        {
                            $translationData = $this->_model->getTranslation($data["event"][0]->trID);

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
                                    $prevStep = EventSteps::BLIND_DRAFT;
                                    if ($data["event"][0]->gwLang == $data["event"][0]->targetLang)
                                        $prevStep = EventSteps::CHUNKING;

                                    if ($data["event"][0]->cotrStep != $prevStep) {
                                        $chunkVerses = json_decode(Session::get("chunk_verses"));
                                        $verses = array_map("trim", $_POST["verses"]);
                                        $verses = array_combine($chunkVerses, $verses);
                                        $comments = array_combine($chunkVerses, $_POST["comments"]);

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
                                                "chapter"           => (integer)preg_replace("/-\d+/", "", Session::get("currentChunk")),
                                                "chunk"             => $data["event"][0]->currentChunk,
                                                "firstvs"           => $chunkVerses[0],
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

                                            $tID = $this->_model->updateTranslation($trData, array("trID" => $data["event"][0]->trID));
                                        }

                                        if($tID)
                                        {
                                            $this->_model->updateTranslator(array("step" => EventSteps::PEER_REVIEW), array("trID" => $data["event"][0]->trID));
                                            Url::redirect('events/translator/' . $data["event"][0]->eventID);
                                            exit;
                                        }
                                        else
                                        {
                                            $error[] = $this->language->get("translation_not_created_error");
                                        }
                                    } else {
                                        $error[] = $this->language->get("cotranslator_not_ready_error");
                                    }
                                }
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
                        View::render('events/self_check', $data, $error);
                        break;

                    case EventSteps::PEER_REVIEW:

                        $translationData = $this->_model->getTranslation($data["event"][0]->trID);

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
                                    if ($data["event"][0]->cotrStep != EventSteps::SELF_CHECK) {
                                        $verses = array_map("trim", $_POST["verses"]);
                                        $comments = array_map("trim", $_POST["comments"]);

                                        if(!empty($translationData))
                                        {
                                            $translationVerses = json_decode($translationData[0]->translatedVerses, true);
                                            if(is_array($translationVerses) && !empty($translationVerses))
                                            {
                                                $i=0;
                                                foreach($translationVerses["translator"]["verses"] as $verse => $text)
                                                {
                                                    $translationVerses["translator"]["verses"][$verse] = $verses[$i];
                                                    $translationVerses["translator"]["comments"][$verse] = $comments[$i];
                                                    $i++;
                                                }
                                            }
                                        }

                                        $trData = array(
                                            "translatedVerses"  => json_encode($translationVerses)
                                        );

                                        if($this->_model->updateTranslation($trData, array("trID" => $data["event"][0]->trID)))
                                        {
                                            //$this->_model->updateTranslator(array("step" => EventSteps::CHUNKING), array("trID" => $data["event"][0]->trID));
                                            Url::redirect('events/translator/' . $data["event"][0]->eventID);
                                            exit;
                                        }
                                        else
                                        {
                                            $error[] = $this->language->get("translation_not_created_error");
                                        }
                                    } else {
                                        $error[] = $this->language->get("cotranslator_not_ready_error");
                                    }
                                }
                            }
                        }

                        $sourceText = $this->getSourceText($data, true);
                        $cotrSourceText = $this->getSourceText($data, true, true);

                        if (!array_key_exists("error", $sourceText)) {
                            $data = $sourceText;
                            $data["cotrData"] = $cotrSourceText;

                            $translation = json_decode($translationData[0]->translatedVerses, true);
                            $data["translation"] = $translation;

                            $coTranslationTemp = $this->_model->getTranslation($data["event"][0]->cotrID);
                            $coTranslation = (array)json_decode($coTranslationTemp[0]->translatedVerses, true);
                            $data["cotrData"]["translation"] = $coTranslation;
                        } else {
                            $error[] = $sourceText["error"];
                        }

                        View::renderTemplate('header', $data);
                        View::render('events/translator', $data, $error);
                        View::render('events/peer_review', $data, $error);
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

    public function checker_l2($eventID)
    {
        echo $eventID;
    }

    public function checker_l3($eventID)
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
        $eventCurrentChunk = !$isCoTranslator ? $data["event"][0]->currentChunk : $data["event"][0]->cotrCurrentChunk;
        $eventTrID = !$isCoTranslator ? $data["event"][0]->trID : $data["event"][0]->cotrID;
        $currentChapter = 0;

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
            $currentChunk = "";
            $currentChapterText = "";
            $totalVerses = 0;
            $currentChunkText = "";

            if($eventCurrentChunk == "")
            {
                foreach (json_decode($data["event"][0]->chapters) as $chapter => $chunks) {
                    if($chunks->trID == $eventTrID)
                    {
                        $currentChapter = $chapter;
                        break;
                    }
                }

                $currentChunk = $currentChapter.":"."1-1";
            }
            else
            {
                $currentChunk = $eventCurrentChunk;
                $split = explode(":", $currentChunk);
                $currentChapter = (integer)$split[0];
            }

            if(!$isCoTranslator)
                Session::set("currentChunk", $currentChunk);

            if(!$getChunk)
            {
                foreach ($json["chapters"][$currentChapter - 1]["frames"] as $frame) {
                    $currentChapterText .= $frame["text"];
                }

                $data["text"] = $currentChapterText;
            }
            else
            {
                foreach ($json["chapters"][$currentChapter - 1]["frames"] as $frame) {
                    if($frame["id"] == $currentChunk)
                    {
                        $currentChunkText = $frame["text"];
                        break;
                    }
                }

                $matches = array();
                preg_match_all("/<verse\D+(\d+)\D+>/", $currentChunkText, $matches);
                $data["totalVerses"] = $matches[1][0]."-".$matches[1][sizeof($matches[1])-1];

                if(!$isCoTranslator)
                    Session::set("chunk_verses", json_encode($matches[1]));

                $data["text"] = $currentChunkText;
            }

            $data["text"] = preg_replace("/<\/?para.*>/", "", $data["text"]);
            //$data["text"] = preg_replace("/<verse\D+(\d+)\D+>/", ':delimiter:<strong><sup>${1}</sup></strong> ', $data["text"], -1, $totalVerses);
            $data["text"] = preg_split("/<verse\D+(\d+)\D+>/", $data["text"], -1, PREG_SPLIT_DELIM_CAPTURE);
            //$data["text"] = explode(":delimiter:", $data["text"]);
            $totalVerses = !empty($data["text"]) ? (sizeof($data["text"])-1)/2 : 0;

            $data["currentChapter"] = $currentChapter;
            $data["totalVerses"] = !$getChunk ? "1-".$totalVerses : $data["totalVerses"];

            return $data;
        }
        else
        {
            return array("error" => $this->language->get("no_source_error"));
        }
    }
}