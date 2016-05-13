<?php
namespace Controllers\admin;

use Core\Error;
use Core\View,
    Core\Controller;
use Helpers\Data;
use Helpers\Gump;
use Helpers\Session;
use Helpers\Url;
use Models\EventsModel;
use Models\MembersModel;
use phpFastCache\CacheManager;

class AdminController extends Controller {

    private $_model;
    private $_lang;

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
    }

    public  function index() {

        if (!Session::get('loggedin'))
        {
            Session::set('redirect', 'admin');
            Url::redirect('members/login');
        }

        if(!Session::get('isAdmin'))
        {
            Url::redirect('');
        }

        $data['menu'] = 1;
        $data['title'] = $this->language->get('admin_events_title');

        if(Session::get("isSuperAdmin"))
        {
            $data["gwProjects"] = $this->_model->getGatewayProject("*", array(
                "gwProjectID" => array(">", 0)));
            $data["gwLangs"] = $this->_model->getAllLanguages(true);
        }

        if(Session::get("isAdmin"))
        {
            $data["projects"] = $this->_model->getProjects(Session::get("userName"), Session::get("isSuperAdmin"));
            $data["memberGwLangs"] = Session::get("isSuperAdmin") ? $data["gwLangs"] :
                $this->_model->getMemberGwLanguages(Session::get("userName"));
            $data["sourceTranslations"] = $this->_model->getSourceTranslations();

            for($i=0; $i< sizeof($data["memberGwLangs"]); $i++){
                unset($data["memberGwLangs"][$i]->admins);
            }
        }

        View::renderTemplate('headerAdmin', $data);
        View::render('admin/main/index', $data);
        View::renderTemplate('footer', $data);
    }

    public function project($projectID)
    {
        if (!Session::get('loggedin'))
        {
            Session::set('redirect', 'admin');
            Url::redirect('members/login');
        }

        if(!Session::get('isAdmin'))
        {
            Url::redirect('');
        }

        $data['menu'] = 1;
        $data['title'] = $this->language->get('admin_project_title');

        $data["project"] = $this->_model->getProjects(Session::get("userName"), Session::get("isSuperAdmin"), $projectID);
        $data["events"] = array();
        if(!empty($data["project"]))
        {
            $data["events"] = $this->_model->getEventsByProject($projectID);
        }

        View::renderTemplate('headerAdmin', $data);
        View::render('admin/main/project', $data);
        View::renderTemplate('footer', $data);
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
            $error[] = $this->language->get('choose_gateway_language_error');
        }

        if(!isset($error))
        {
            $gwProject = $this->_model->getGatewayProject("*", array(
                PREFIX."gateway_projects.gwLang" => array("=", $gwLang)
            ));

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
            $error[] = $this->language->get('choose_gateway_language_error');
        }

        if($admins == null)
        {
            $error[] = $this->language->get("no_admins_error");
        }

        if(!isset($error))
        {
            $exist = $this->_model->getGatewayProject(PREFIX."gateway_projects.gwProjectID", array(
                PREFIX."gateway_projects.gwLang" => array("=", $gwLang)
            ));

            switch($act)
            {
                case "create":
                    if(!empty($exist))
                    {
                        $error[] = $this->language->get("gw_project_exists_error");
                        echo json_encode(array("error" => Error::display($error)));
                        return;
                    }
                    break;

                case "edit":
                    if(empty($exist))
                    {
                        $error[] = $this->language->get("gw_project_not_exists_error");
                        echo json_encode(array("error" => Error::display($error)));
                        return;
                    }
                    break;
            }

            $memberModel = new MembersModel();
            foreach ($admins as $admin) {
                $memberModel->updateMember(array("isAdmin" => true), array("userName" => $admin));
            }

            $postdata = array(
                "admins" => json_encode($admins)
            );

            $msg = "";

            if($act == "create")
            {
                $postdata["gwLang"] = $gwLang;
                $id = $this->_model->createGatewayProject($postdata);
                $msg = json_encode(array("success" => $this->language->get("successfully_created")));
            }
            else if($act == "edit")
            {
                $this->_model->updateGatewayProject($postdata, array("gwLang" => $gwLang));
                $msg = json_encode(array("success" => $this->language->get("successfully_updated")));
                $id = true;
            }

            if($id)
                echo $msg;
            else
            {
                $error[] = $this->language->get("error_ocured");
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

        if(!Session::get('isAdmin'))
        {
            echo json_encode(array());
            exit;
        }

        $_POST = Gump::xss_clean($_POST);

        $subGwLangs = isset($_POST['subGwLangs']) && $_POST['subGwLangs'] != "" ? $_POST['subGwLangs'] : null;
        $targetLang = isset($_POST['targetLangs']) && $_POST['targetLangs'] != "" ? $_POST['targetLangs'] : null;
        $sourceTranslation = isset($_POST['sourceTranslation']) && $_POST['sourceTranslation'] != "" ? $_POST['sourceTranslation'] : null;

        if($subGwLangs == null)
        {
            $error[] = $this->language->get('choose_gateway_language');
        }

        if($targetLang == null)
        {
            $error[] = $this->language->get("choose_target_language");
        }

        if($sourceTranslation == null)
        {
            $error[] = $this->language->get("choose_source_translation");
        }

        if(!isset($error))
        {
            $sourceTrPair = explode("|", $sourceTranslation);
            $gwLangsObj = json_decode(base64_decode($subGwLangs));

            if(!isset($gwLangsObj->gwProjectID))
            {
                $error[] = $this->language->get("super_admin_cannot_create_sub_event_for_now");
                echo json_encode(array("error" => Error::display($error)));
                return;
            }

            $exist = $this->_model->getProject(PREFIX."projects.projectID", array(
                PREFIX."projects.gwLang" => array("=", $gwLangsObj->langID),
                PREFIX."projects.targetLang" => array("=", $targetLang),
                PREFIX."projects.bookProject" => array("=", $sourceTrPair[0]),
            ));

            if(!empty($exist))
            {
                $error[] = $this->language->get("event_exists");
                echo json_encode(array("error" => Error::display($error)));
                return;
            }

            $postdata = array(
                "gwProjectID" => $gwLangsObj->gwProjectID,
                "gwLang" => $gwLangsObj->langID,
                "targetLang" => $targetLang,
                "bookProject" => $sourceTrPair[0],
                "sourceLangID" => $sourceTrPair[1],
            );

            $id = $this->_model->createProject($postdata);

            if($id)
                echo json_encode(array("success" => $this->language->get("successfully_created")));
            else
            {
                $error[] = $this->language->get("error_ocured");
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
            $admins = $this->_model->getMembers($_POST['search']);

            echo json_encode($admins);
        }
    }

    public function getTargetLanguagesByGwLanguage()
    {
        if (!Session::get('loggedin'))
        {
            echo json_encode(array("login" => true));
            exit;
        }

        if(!Session::get('isAdmin'))
        {
            echo json_encode(array());
            exit;
        }

        $gwLang = isset($_POST["gwLang"]) && $_POST["gwLang"] != "" ? $_POST["gwLang"] : null;

        if($gwLang)
        {
            $langs = json_decode(base64_decode($gwLang));
            $sourceLangID = isset($langs->sourceLangID) ? $langs->sourceLangID : "en";
            $bookProject = isset($langs->bookProject) ? $langs->bookProject : "udb";
            $response = array();

            $response['targetLangs'] = $this->_model->getTargetLanguages(Session::get("userName"), $langs->langID);
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

        if(!Session::get('isAdmin'))
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
            $error[] = $this->language->get('wrong_book_code');
        }

        if($projectID == null)
        {
            $error[] = $this->language->get('wrong_project_id');
        }

        if($translators == null || $translators <= 0)
        {
            $error[] = $this->language->get('enter_translators');
        }
        else if($translators%2 > 0)
        {
            $error[] = $this->language->get('not_even_translators');
        }

        if($checkers_l2 == null || $checkers_l2 <= 0)
        {
            $error[] = $this->language->get('enter_checkers_l2');
        }

        if($checkers_l3 == null || $checkers_l3 <= 0)
        {
            $error[] = $this->language->get('enter_checkers_l3');
        }

        if($dateFrom == null || !preg_match("/^(19|20)\d\d-(0[1-9]|1[012])-(0[1-9]|[12][0-9]|3[01])$/", $dateFrom))
        {
            $error[] = $this->language->get('wrong_date_from');
        }

        if($dateTo == null || !preg_match("/^(19|20)\d\d-(0[1-9]|1[012])-(0[1-9]|[12][0-9]|3[01])$/", $dateTo))
        {
            $error[] = $this->language->get('wrong_date_to');
        }

        if(!isset($error))
        {
            $event = $this->_model->getEvent($projectID, $bookCode);

            if(empty($event))
            {
                $project = $this->_model->getProjects(Session::get("userName"), Session::get("isSuperAdmin"), $projectID);

                if(!empty($project))
                {
                    $postdata = array(
                        "projectID" => $projectID,
                        "bookCode" => $bookCode,
                        "translatorsNum" => $translators,
                        "l2CheckersNum" => $checkers_l2,
                        "l3CheckersNum" => $checkers_l3,
                        "dateFrom" => $dateFrom,
                        "dateTo" => $dateTo,
                    );

                    $cache_keyword = $bookCode."_".$project[0]->sourceLangID."_".$project[0]->bookProject;
                    $source = CacheManager::get($cache_keyword);

                    if(is_null($source))
                    {
                        $source = $this->_model->getSourceBookFromApi($bookCode, $project[0]->sourceLangID, $project[0]->bookProject);
                        $json = json_decode($source);

                        if(!empty($json))
                            CacheManager::set($cache_keyword, $source, 60*60*24*7);
                    }
                    else
                    {
                        $json = json_decode($source);
                    }

                    if(!empty($json))
                    {
                        $chapters = array();

                        foreach ($json->chapters as $chapter) {
                            foreach ($chapter->frames as $frame) {
                                $chapters[(integer)$chapter->number] = array();
                            }
                        }

                        if(sizeof($chapters) >= $translators)
                        {
                            $postdata["chapters"] = json_encode($chapters);

                            $eventID = $this->_model->createEvent($postdata);

                            if($eventID)
                                echo json_encode(array("success" => $this->language->get("successfully_created")));
                        }
                        else
                        {
                            $error[] = $this->language->get("too_many_translators_error", array("chap_number" => sizeof($chapters)));
                            echo json_encode(array("error" => Error::display($error)));
                        }
                    }
                    else
                    {
                        $error[] = $this->language->get("no_source_error");
                        echo json_encode(array("error" => Error::display($error)));
                    }
                }
                else
                {
                    $error[] = $this->language->get("no_project_no_rights");
                    echo json_encode(array("error" => Error::display($error)));
                }
            }
            else
            {
                $error[] = $this->language->get("event_already_exists");
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
            $source = CacheManager::get($cache_keyword);

            if(is_null($source))
            {
                $source = $this->_model->getSourceBookFromApi($bookCode, $sourceLangID, $bookProject);
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
