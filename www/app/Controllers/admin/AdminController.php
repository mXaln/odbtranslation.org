<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\TranslationsModel;
use Shared\Legacy\Error;
use View;
use Helpers\Data;
use Helpers\Gump;
use Helpers\Session;
use Helpers\Url;
use App\Models\EventsModel;
use App\Models\MembersModel;

class AdminController extends Controller {

    private $_model;
    private $_membersModel;
    private $_translationModel;
    protected $layout = "admin";

    public function __construct()
    {
        parent::__construct();
        $this->_model = new EventsModel();
        $this->_membersModel = new MembersModel();
        $this->_translationModel = new TranslationsModel();
    }

    public  function index() {

        if (!Session::get('loggedin'))
        {
            Session::set('redirect', 'admin');
            Url::redirect('members/login');
        }

        if(!Session::get('isSuperAdmin'))
        {
            Url::redirect('');
        }

        $data['menu'] = 1;

        if(Session::get("isSuperAdmin"))
        {
            $data["gwProjects"] = $this->_model->getGatewayProject();
            $data["gwLangs"] = $this->_model->getAllLanguages(true);
            $data["projects"] = $this->_model->getProjects(Session::get("memberID"), Session::get("isSuperAdmin"));
            $data["memberGwLangs"] = Session::get("isSuperAdmin") ? $data["gwLangs"] :
                $this->_model->getMemberGwLanguages(Session::get("memberID"));
            $data["sourceTranslations"] = $this->_translationModel->getSourceTranslations();

            for($i=0; $i< sizeof($data["memberGwLangs"]); $i++){
                unset($data["memberGwLangs"][$i]->admins);
            }
        }

        return View::make('Admin/Main/Index')
            ->shares("title", __("admin_project_title"))
            ->shares("data", $data);
    }

    public function project($projectID)
    {
        if (!Session::get('loggedin'))
        {
            Session::set('redirect', 'admin');
            Url::redirect('members/login');
        }

        if(!Session::get('isSuperAdmin'))
        {
            Url::redirect('');
        }

        $data['menu'] = 1;
        $data["project"] = $this->_model->getProjects(Session::get("memberID"), Session::get("isSuperAdmin"), $projectID);
        $data["events"] = array();
        if(!empty($data["project"]))
        {
            $data["events"] = $this->_model->getEventsByProject($projectID);
        }

        return View::make('Admin/Main/Project')
            ->shares("title", __("admin_events_title"))
            ->shares("data", $data);
    }

    public function getGwProject()
    {
        if (!Session::get('loggedin'))
        {
            Session::set('redirect', 'admin');
            Url::redirect('members/login');
        }

        if(!Session::get('isSuperAdmin'))
        {
            return;
        }

        $_POST = Gump::xss_clean($_POST);

        $gwLang = isset($_POST['gwLang']) && $_POST['gwLang'] != "" ? $_POST['gwLang'] : null;

        if($gwLang == null)
        {
            $error[] = __('choose_gateway_language_error');
        }

        if(!isset($error))
        {
            $gwProject = $this->_model->getGatewayProject(["*"],
                ["gateway_projects.gwLang", $gwLang]);

            $members = [];
            $membersArray = (array)$this->_membersModel->getMembers(json_decode($gwProject[0]->admins));

            foreach ($membersArray as $member) {
                $members[$member->memberID] = $member->userName;
            }

            $gwProject[0]->admins = $members;

            echo json_encode($gwProject);
        }
    }

    public function createGwProject()
    {
        if (!Session::get('loggedin'))
        {
            Session::set('redirect', 'admin');
            Url::redirect('members/login');
        }

        if(!Session::get('isSuperAdmin'))
        {
            return;
        }

        $_POST = Gump::xss_clean($_POST);

        $gwLang = isset($_POST['gwLang']) && $_POST['gwLang'] != "" ? $_POST['gwLang'] : null;
        $act = isset($_POST['act']) && $_POST['act'] != "" ? $_POST['act'] : "create";
        $admins = isset($_POST['admins']) && !empty($_POST['admins']) ? array_unique($_POST['admins']) : null;

        if($gwLang == null)
        {
            $error[] = __('choose_gateway_language_error');
        }

        if($admins == null)
        {
            $error[] = __("no_admins_error");
        }

        if(!isset($error))
        {
            $exist = $this->_model->getGatewayProject(array("gateway_projects.gwProjectID"),
                array("gateway_projects.gwLang", $gwLang));

            switch($act)
            {
                case "create":
                    if(!empty($exist))
                    {
                        $error[] = __("gw_project_exists_error");
                        echo json_encode(array("error" => Error::display($error)));
                        return;
                    }
                    break;

                case "edit":
                    if(empty($exist))
                    {
                        $error[] = __("gw_project_not_exists_error");
                        echo json_encode(array("error" => Error::display($error)));
                        return;
                    }
                    break;
            }

            foreach ($admins as $admin) {
                $this->_membersModel->updateMember(array("isAdmin" => true), array("memberID" => $admin));
            }

            $postdata = array(
                "admins" => json_encode($admins)
            );

            $msg = "";

            if($act == "create")
            {
                $postdata["gwLang"] = $gwLang;
                $id = $this->_model->createGatewayProject($postdata);
                $msg = json_encode(array("success" => __("successfully_created")));
            }
            else if($act == "edit")
            {
                $this->_model->updateGatewayProject($postdata, array("gwLang" => $gwLang));
                $msg = json_encode(array("success" => __("successfully_updated")));
                $id = true;
            }

            if($id)
                echo $msg;
            else
            {
                $error[] = __("error_ocured");
                echo json_encode(array("error" => Error::display($error)));
            }
        }
        else
        {
            echo json_encode(array("error" => Error::display($error)));
        }
    }

    public function createProject()
    {
        if (!Session::get('loggedin'))
        {
            echo json_encode(array("login" => true));
            exit;
        }

        if(!Session::get('isSuperAdmin'))
        {
            echo json_encode(array());
            exit;
        }

        $_POST = Gump::xss_clean($_POST);

        $subGwLangs = isset($_POST['subGwLangs']) && $_POST['subGwLangs'] != "" ? $_POST['subGwLangs'] : null;
        $targetLang = isset($_POST['targetLangs']) && $_POST['targetLangs'] != "" ? $_POST['targetLangs'] : null;
        $sourceTranslation = isset($_POST['sourceTranslation']) && $_POST['sourceTranslation'] != "" ? $_POST['sourceTranslation'] : null;
        $projectType = isset($_POST['projectType']) && $_POST['projectType'] != "" ? $_POST['projectType'] : null;

        if($subGwLangs == null)
        {
            $error[] = __('choose_gateway_language');
        }

        if($targetLang == null)
        {
            $error[] = __("choose_target_language");
        }

        if($sourceTranslation == null)
        {
            $error[] = __("choose_source_translation");
        }
        else
        {
            if(($sourceTranslation != "ulb|en" && $sourceTranslation != "udb|en") && $projectType == null)
            {
                $error[] = __("choose_project_type");
            }
        }

        if(!isset($error))
        {
            $sourceTrPair = explode("|", $sourceTranslation);
            $gwLangsPair = explode("|", $subGwLangs);

            $projType = $sourceTrPair[0] != "ulb" && $sourceTrPair[0] != "udb" ?
                $projectType : $sourceTrPair[0];

            $exist = $this->_model->getProject(["projects.projectID"], [
                ["projects.gwLang", $gwLangsPair[0]],
                ["projects.targetLang", $targetLang],
                ["projects.bookProject", $projType]
            ]);

            if(!empty($exist))
            {
                $error[] = __("project_exists");
                echo json_encode(array("error" => Error::display($error)));
                return;
            }

            $postdata = array(
                "gwProjectID" => $gwLangsPair[1],
                "gwLang" => $gwLangsPair[0],
                "targetLang" => $targetLang,
                "bookProject" => $projType,
                "sourceBible" => $sourceTrPair[0],
                "sourceLangID" => $sourceTrPair[1]
            );

            $id = $this->_model->createProject($postdata);

            if($id)
                echo json_encode(array("success" => __("successfully_created")));
            else
            {
                $error[] = __("error_ocured");
                echo json_encode(array("error" => Error::display($error)));
            }
        }
        else
        {
            echo json_encode(array("error" => Error::display($error)));
        }
    }

    public function getMembers()
    {
        if (!Session::get('loggedin'))
        {
            echo json_encode(array("login" => true));
            exit;
        }

        if(!Session::get('isSuperAdmin'))
        {
            echo json_encode(array());
            exit;
        }

        $_POST = Gump::xss_clean($_POST);

        if(isset($_POST['search']) && $_POST['search'] != "")
        {
            $admins = $this->_membersModel->getMembersByTerm($_POST['search']);
            $arr = array();

            foreach ($admins as $admin) {
                $tmp = array();
                $tmp["value"] = $admin->memberID;
                $tmp["text"] = $admin->userName;

                $arr[] = $tmp;
            }
            echo json_encode($arr);
        }
    }

    public function getTargetLanguagesByGwLanguage()
    {
        if (!Session::get('loggedin'))
        {
            echo json_encode(array("login" => true));
            exit;
        }

        if(!Session::get('isSuperAdmin'))
        {
            echo json_encode(array());
            exit;
        }

        $gwLang = isset($_POST["gwLang"]) && $_POST["gwLang"] != "" ? $_POST["gwLang"] : null;

        if($gwLang)
        {
            $gwLang = explode("|", $gwLang)[0];
            $response['targetLangs'] = $this->_model->getTargetLanguages(Session::get("memberID"), $gwLang);
            echo json_encode($response);
        }
    }

    public function createEvent()
    {
        if (!Session::get('loggedin'))
        {
            Session::set('redirect', 'admin');
            Url::redirect('members/login');
        }

        if(!Session::get('isSuperAdmin'))
        {
            return;
        }

        $_POST = Gump::xss_clean($_POST);

        $bookCode = isset($_POST['book_code']) && $_POST['book_code'] != "" ? $_POST['book_code'] : null;
        $projectID = isset($_POST['projectID']) && $_POST['projectID'] != "" ? (integer)$_POST['projectID'] : null;
        $translators = isset($_POST['translators']) && $_POST['translators'] != "" ? (integer)$_POST['translators'] : null;
        $checkers_l2 = isset($_POST['checkers_l2']) && $_POST['checkers_l2'] != "" ? (integer)$_POST['checkers_l2'] : null;
        $checkers_l3 = isset($_POST['checkers_l3']) && $_POST['checkers_l3'] != "" ? (integer)$_POST['checkers_l3'] : null;
        $dateFrom = isset($_POST['cal_from']) && $_POST['cal_from'] != "" ? $_POST['cal_from'] : null;
        $dateTo = isset($_POST['cal_to']) && $_POST['cal_to'] != "" ? $_POST['cal_to'] : null;

        if($bookCode == null)
        {
            $error[] = __('wrong_book_code');
        }

        if($projectID == null)
        {
            $error[] = __('wrong_project_id');
        }

        if($translators == null || $translators <= 0)
        {
            $error[] = __('enter_translators');
        }
        else if($translators%2 > 0)
        {
            $error[] = __('not_even_translators');
        }

        if($checkers_l2 == null || $checkers_l2 <= 0)
        {
            $error[] = __('enter_checkers_l2');
        }

        if($checkers_l3 == null || $checkers_l3 <= 0)
        {
            $error[] = __('enter_checkers_l3');
        }

        if($dateFrom == null || $dateFrom === false)
        {
            $error[] = __('wrong_date_from');
        }

        if($dateTo == null || $dateTo === false)
        {
            $error[] = __('wrong_date_to');
        }

        if(!isset($error))
        {
            $event = $this->_model->getEvent(null, $projectID, $bookCode);

            if(empty($event))
            {
                $project = $this->_model->getProjects(Session::get("memberID"), Session::get("isSuperAdmin"), $projectID);

                if(!empty($project))
                {
                    $postdata = array(
                        "projectID" => $projectID,
                        "adminID" => Session::get("memberID"),
                        "bookCode" => $bookCode,
                        "translatorsNum" => $translators,
                        "l2CheckersNum" => $checkers_l2,
                        "l3CheckersNum" => $checkers_l3,
                        "dateFrom" => date("Y-m-d H:i:s", strtotime($dateFrom)),
                        "dateTo" => date("Y-m-d H:i:s", strtotime($dateTo)),
                    );

                    $bookInfo = $this->_translationModel->getBookInfo($bookCode);

                    if(!empty($bookInfo))
                    {
                        $chapters = array();

                        for($i=1; $i<=$bookInfo[0]->chaptersNum; $i++)
                        {
                            $chapters[$i] = array();
                        }

                        if(sizeof($chapters) >= $translators)
                        {
                            $postdata["chapters"] = json_encode($chapters);

                            $eventID = $this->_model->createEvent($postdata);

                            if($eventID)
                                echo json_encode(array("success" => __("successfully_created")));
                        }
                        else
                        {
                            $error[] = __("too_many_translators_error", array("chap_number" => sizeof($chapters)));
                            echo json_encode(array("error" => Error::display($error)));
                        }
                    }
                    else
                    {
                        $error[] = __("wrong_book_error");
                        echo json_encode(array("error" => Error::display($error)));
                    }
                }
                else
                {
                    $error[] = __("no_project_no_rights");
                    echo json_encode(array("error" => Error::display($error)));
                }
            }
            else
            {
                $error[] = __("event_already_exists");
                echo json_encode(array("error" => Error::display($error)));
            }
        }
        else
        {
            echo json_encode(array("error" => Error::display($error)));
        }
    }


    public function getSource()
    {
        $response = array();
        $_POST = Gump::xss_clean($_POST);

        $bookCode = isset($_POST["bookCode"]) && $_POST["bookCode"] != "" ? $_POST["bookCode"] : null;
        $sourceLangID = isset($_POST["sourceLangID"]) && $_POST["sourceLangID"] != "" ? $_POST["sourceLangID"] : null;
        $bookProject = isset($_POST["bookProject"]) && $_POST["bookProject"] != "" ? $_POST["bookProject"] : null;

        if($bookCode && $sourceLangID && $bookProject)
        {
            $cache_keyword = $bookCode."_".$sourceLangID."_".$bookProject;

            if(Cache::has($cache_keyword))
            {
                $source = Cache::get($cache_keyword);
                $json = json_decode($source, true);
            }
            else
            {
                $source = $this->_model->getSourceBookFromApi($bookCode, $sourceLangID, $bookProject);
                $json = json_decode($source, true);

                if(!empty($json))
                    Cache::add($cache_keyword, $source, 60*24*7);
            }

            if(!empty($json))
            {
                $response["chaptersNum"] = sizeof($json["chapters"]);

                $text = "";

                foreach ($json["chapters"] as $chapter) {
                    foreach ($chapter["frames"] as $frame) {
                        $text .= $frame["text"];
                    }
                }

                $text = preg_split("/<verse\D+(\d+)\D+>/", $text, -1, PREG_SPLIT_DELIM_CAPTURE);
                $response["versesNum"] = !empty($text) ? (sizeof($text)-1)/2 : 0;
            }
        }

        echo json_encode($response);
    }
}
