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
    private $_eventsModel;
    private $_translationModel;
    protected $layout = "admin";

    public function __construct()
    {
        parent::__construct();
        $this->_model = new EventsModel();
        $this->_membersModel = new MembersModel();
        $this->_eventsModel = new EventsModel();
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

    public function members()
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

        $data['menu'] = 2;

        $data["members"] = $this->_membersModel->getMember(["*"], [
            ["active", false],
            ["verified", false, "=", "OR"]
        ]);

        return View::make('Admin/Members/Index')
            ->shares("title", __("admin_members_title"))
            ->shares("data", $data);
    }

    public function getEvent()
    {
        $response = ["success" => false];

        if (!Session::get('loggedin'))
        {
            $response["error"] = "login";
        }

        if(!Session::get('isSuperAdmin'))
        {
            $response["error"] = "admin";
        }

        $_POST = Gump::xss_clean($_POST);

        $eventID = isset($_POST['eventID']) && $_POST['eventID'] != "" ? (integer)$_POST['eventID'] : null;

        if($eventID == null)
        {
            $response["error"] = __('wrong_parameters_error');
        }

        if(!isset($response["error"]))
        {
            $event = $this->_eventsModel->getEvent($eventID);

            $members = [];
            $membersArray = (array)$this->_membersModel->getMembers(json_decode($event[0]->admins));

            foreach ($membersArray as $member) {
                $members[$member->memberID] = $member->userName;
            }

            $event[0]->admins = $members;
            $response["success"] = true;
            $response["event"] = $event[0];
        }

        echo json_encode($response);

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

        if($gwLang == null)
        {
            $error[] = __('choose_gateway_language_error');
        }

        if(!isset($error))
        {
            $exist = $this->_model->getGatewayProject(array("gateway_projects.gwProjectID"),
                array("gateway_projects.gwLang", $gwLang));

            if(!empty($exist))
            {
                $error[] = __("gw_project_exists_error");
                echo json_encode(array("error" => Error::display($error)));
                return;
            }

            $id = $this->_model->createGatewayProject(["gwLang" => $gwLang]);
            $msg = json_encode(array("success" => __("successfully_created")));

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

    public function verifyMember()
    {
        $response = ["success" => false];

        if (!Session::get('loggedin'))
        {
            $response["error"] = "not_loggedin";
            echo json_encode($response);
            exit;
        }

        if(!Session::get('isSuperAdmin'))
        {
            $response["error"] = "not_allowed";
            echo json_encode($response);
            exit;
        }

        $_POST = Gump::xss_clean($_POST);

        $memberID = isset($_POST["memberID"]) ? (integer)$_POST["memberID"] : null;

        if($memberID)
        {
            $this->_membersModel->updateMember(
                ["active" => true, "verified" => true, "activationToken" => null],
                ["memberID" => $memberID]);
            $response["success"] = true;
            echo json_encode($response);
        }
        else
        {
            $response["error"] = "wrong_parameters";
            echo json_encode($response);
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
        $admins = isset($_POST['admins']) && !empty($_POST['admins']) ? array_unique($_POST['admins']) : [];
        $act = isset($_POST['act']) && preg_match("/^(create|edit)$/", $_POST['act']) ? $_POST['act'] : "create";

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
            $exist = $this->_model->getEvent(null, $projectID, $bookCode);

            $postdata = [
                "translatorsNum" => $translators,
                "l2CheckersNum" => $checkers_l2,
                "l3CheckersNum" => $checkers_l3,
                "dateFrom" => date("Y-m-d H:i:s", strtotime($dateFrom)),
                "dateTo" => date("Y-m-d H:i:s", strtotime($dateTo)),
                "admins" => json_encode($admins),
            ];

            switch($act)
            {
                case "create":
                    if(!empty($exist))
                    {
                        $error[] = __("event_already_exists");
                        echo json_encode(array("error" => Error::display($error)));
                        return;
                    }

                    $postdata["projectID"] = $projectID;
                    $postdata["adminID"] = Session::get("memberID");
                    $postdata["bookCode"] = $bookCode;
                    break;

                case "edit":
                    if(empty($exist))
                    {
                        $error[] = __("event_not_exists_error");
                        echo json_encode(array("error" => Error::display($error)));
                        return;
                    }
                    break;
            }

            foreach ($admins as $admin) {
                $this->_membersModel->updateMember(array("isAdmin" => true), array("memberID" => $admin));
            }

            if($act == "create")
            {
                $bookInfo = $this->_translationModel->getBookInfo($bookCode);

                if(!empty($bookInfo))
                {
                    $chapters = array();

                    for($i=1; $i<=$bookInfo[0]->chaptersNum; $i++)
                        $chapters[$i] = array();

                    $postdata["chapters"] = json_encode($chapters);
                    $eventID = $this->_model->createEvent($postdata);

                    if($eventID)
                        echo json_encode(array("success" => __("successfully_created")));
                }
                else
                {
                    $error[] = __("wrong_book_error");
                    echo json_encode(array("error" => Error::display($error)));
                }
            }
            else if($act == "edit")
            {
                $this->_eventsModel->updateEvent($postdata, ["projectID" => $projectID, "bookCode" => $bookCode]);
                echo json_encode(array("success" => __("successfully_updated")));
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