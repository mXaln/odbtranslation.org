<?php
namespace Controllers;

use Core\Controller;
use Core\Error;
use Core\View;
use Helpers\Constants\EventMembers;
use Helpers\Constants\EventStates;
use Helpers\Data;
use Helpers\Gump;
use Helpers\Session;
use Helpers\Url;

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
                            $trID = $this->_model->addTranslator($trData);

                            if(is_numeric($trID))
                            {
                                if($this->checkStateFinished($event[0], EventMembers::TRANSLATOR))
                                {
                                    $this->_model->updateEvent(array("state" => EventStates::TRANSLATING), array("eventID" => $event[0]->eventID));
                                }
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
                                if($this->checkStateFinished($event[0], EventMembers::L2_CHECKER))
                                {
                                    $this->_model->updateEvent(array("state" => EventStates::L2_CHECK), array("eventID" => $event[0]->eventID));
                                }
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
                                if($this->checkStateFinished($event[0], EventMembers::L3_CHECKER))
                                {
                                    $this->_model->updateEvent(array("state" => EventStates::L3_CHECK), array("eventID" => $event[0]->eventID));
                                }
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
}