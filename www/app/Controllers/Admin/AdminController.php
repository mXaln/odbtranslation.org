<?php
namespace App\Controllers\Admin;

use Helpers\UsfmParser;
use Support\Facades\Cache;
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
use Helpers\Password;

class AdminController extends Controller {

    private $_model;
    private $_membersModel;
    private $_eventsModel;
    private $_translationModel;
    protected $layout = "admin";

    public function __construct()
    {
        parent::__construct();
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
		
        $data["gwProjects"] = $this->_eventsModel->getGatewayProject();
        $data["gwLangs"] = $this->_eventsModel->getAllLanguages(true);
        $data["projects"] = $this->_eventsModel->getProjects(Session::get("memberID"));
        $data["sourceTranslations"] = $this->_translationModel->getSourceTranslations();
        
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
        $data["project"] = $this->_eventsModel->getProjects(Session::get("memberID"), $projectID);
        $data["events"] = array();
        if(!empty($data["project"]))
        {
            $data["events"] = $this->_eventsModel->getEventsByProject($projectID);
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
        $data["languages"] = $this->_eventsModel->getAllLanguages();

        // New members
        $data["newMembers"] = $this->_membersModel->getMember(["*"], [
            ["active", false],
            ["verified", false, "=", "OR"]
        ], true);

        // All members
        $data["count"] = $this->_membersModel->searchMembers(null, "all", null, true);
        $data["members"] = $this->_membersModel->searchMembers(null, "all", null, false);

        // All books
        $data["books"] = [];
        $list = $this->_eventsModel->getBooksOfTranslators();
        foreach($list as $item)
        {
            if(!isset($data["books"][$item->userName]))
            {
                $tmp = [];
                $tmp["firstName"] = $item->firstName;
                $tmp["lastName"] = $item->lastName;
                $data["books"][$item->userName] = $tmp;
            }

            if(!isset($members[$item->userName]["books"]))
            {
                $tmp = [];
                $tmp["name"] = $item->name;
                $tmp["chapters"] = [];
                $data["books"][$item->userName]["books"][$item->code] = $tmp;
            }

            if(!isset($members[$item->userName]["books"][$item->code]))
            {
                $tmp = [];
                $tmp["name"] = $item->name;
                $tmp["chapters"] = [];
                $data["books"][$item->userName]["books"][$item->code] = $tmp;
            }

            if(!isset($data["books"][$item->userName]["books"][$item->code]["chapters"]))
            {
                $data["books"][$item->userName]["books"][$item->code]["chapters"][$item->chapter] = $item->done;
            }
            else
            {
                $data["books"][$item->userName]["books"][$item->code]["chapters"][$item->chapter] = $item->done;
            }
        }

        return View::make('Admin/Members/Index')
            ->shares("title", __("admin_members_title"))
            ->shares("data", $data);
    }

    /**
     * Get event information with facilitators list
     */
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
                $members[$member->memberID] = "{$member->firstName} "
                    .mb_substr($member->lastName, 0, 1)
                    .". ({$member->userName})";
            }

            $event[0]->admins = $members;
            $response["success"] = true;
            $response["event"] = $event[0];
        }

        echo json_encode($response);

    }


    /**
     * Get event contributors (translators, facilitators, checkers) list
     */
    public function getEventContributors()
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
            $event = $this->_eventsModel->getEventWithContributors($eventID);

            if(!empty($event))
            {
                $admins = [];
                $translators = [];
                $checkers = [];

                // Facilitators
                $adminsArr = (array)json_decode($event[0]->admins);

                // Checkers
                $checkersArr = [];
                foreach ($event as $translator) {
                    $verbCheck = (array)json_decode($translator->verbCheck);
                    $peerCheck = (array)json_decode($translator->peerCheck);
                    $kwCheck = (array)json_decode($translator->kwCheck);
                    $crCheck = (array)json_decode($translator->crCheck);

                    $checkersArr = array_merge($checkersArr, array_values($verbCheck));
                    $checkersArr = array_merge($checkersArr, array_values($peerCheck));
                    $checkersArr = array_merge($checkersArr, array_values($kwCheck));
                    $checkersArr = array_merge($checkersArr, array_values($crCheck));
                }
                $checkersArr = array_unique($checkersArr);

                // Translators
                $translatorsArr = [];

                // Chapters
                $data["chapters"] = [];
                for($i=1; $i <= $event[0]->chaptersNum; $i++)
                {
                    $data["chapters"][$i] = [];
                }

                $chapters = $this->_eventsModel->getChapters($event[0]->eventID);

                foreach ($chapters as $chapter) {
                    $tmp["trID"] = $chapter["trID"];
                    $tmp["memberID"] = $chapter["memberID"];
                    $tmp["chunks"] = json_decode($chapter["chunks"], true);
                    $tmp["done"] = $chapter["done"];

                    $data["chapters"][$chapter["chapter"]] = $tmp;
                }

                foreach ($data["chapters"] as $chapter) {
                    if(!empty($chapter))
						$translatorsArr[] = $chapter["memberID"];
                }
                $translatorsArr = array_unique($translatorsArr);

                $allMembers = array_unique(array_merge($adminsArr, $checkersArr, $translatorsArr));
                $membersArray = (array)$this->_membersModel->getMembers($allMembers, true);

                foreach ($membersArray as $member) {
                    if(in_array($member->memberID, $adminsArr))
                    {
                        $admins[$member->memberID]["userName"] = $member->userName;
                        $admins[$member->memberID]["name"] = $member->firstName . " " . mb_substr($member->lastName, 0, 1).".";
                    }
                    if(in_array($member->memberID, $checkersArr))
                    {
                        $checkers[$member->memberID]["userName"] = $member->userName;
                        $checkers[$member->memberID]["name"] = $member->firstName . " " . mb_substr($member->lastName, 0, 1).".";
                    }
                    if(in_array($member->memberID, $translatorsArr))
                    {
                        $translators[$member->memberID]["userName"] = $member->userName;
                        $translators[$member->memberID]["name"] = $member->firstName . " " . mb_substr($member->lastName, 0, 1).".";
                    }
                }

                $response["success"] = true;
                $response["admins"] = $admins;
                $response["checkers"] = $checkers;
                $response["translators"] = $translators;
            }
            else
            {
                $response["error"] = __('wrong_parameters_error');
            }
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
            $exist = $this->_eventsModel->getGatewayProject(array("gateway_projects.gwProjectID"),
                array("gateway_projects.gwLang", $gwLang));

            if(!empty($exist))
            {
                $error[] = __("gw_project_exists_error");
                echo json_encode(array("error" => Error::display($error)));
                return;
            }

            $id = $this->_eventsModel->createGatewayProject(["gwLang" => $gwLang]);
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

        $projectMode = isset($_POST['projectMode']) && preg_match("/(bible|tn)/", $_POST['projectMode']) ? $_POST['projectMode'] : "bible";
        $subGwLangs = isset($_POST['subGwLangs']) && $_POST['subGwLangs'] != "" ? $_POST['subGwLangs'] : null;
        $targetLang = isset($_POST['targetLangs']) && $_POST['targetLangs'] != "" ? $_POST['targetLangs'] : null;
        $sourceTranslation = isset($_POST['sourceTranslation']) && $_POST['sourceTranslation'] != "" ? $_POST['sourceTranslation'] : null;
        $sourceTranslationNotes = isset($_POST['sourceTranslationNotes']) && $_POST['sourceTranslationNotes'] != "" ? $_POST['sourceTranslationNotes'] : null;
        $projectType = isset($_POST['projectType']) && $_POST['projectType'] != "" ? $_POST['projectType'] : null;
        
        if($subGwLangs == null)
        {
            $error[] = __('choose_gw_lang');
        }

        if($targetLang == null)
        {
            $error[] = __("choose_target_lang");
        }

        if($sourceTranslation == null)
        {
            $error[] = __("choose_source_trans");
        }
        else
        {
            if(($sourceTranslation != "ulb|en" && $sourceTranslation != "udb|en") && $projectType == null)
            {
                $error[] = __("choose_project_type");
            }
        }

        if($projectMode == "tn" && $sourceTranslationNotes == null)
        {
            $error[] = __("choose_source_notes");
        }

        if(!isset($error))
        {
            $sourceTrPair = explode("|", $sourceTranslation);
            $gwLangsPair = explode("|", $subGwLangs);

            $projType = in_array($projectMode, ['tn']) ?
                $projectMode : $sourceTrPair[0];
            
            $exist = $this->_eventsModel->getProject(["projects.projectID"], [
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
                "sourceLangID" => $sourceTrPair[1],
                "notesLangID" => $sourceTranslationNotes
            );
            
            $id = $this->_eventsModel->createProject($postdata);

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
                $tmp["text"] = "{$admin->firstName} "
                    .mb_substr($admin->lastName, 0, 1)
                    .". ({$admin->userName})";

                $arr[] = $tmp;
            }
            echo json_encode($arr);
        }
    }

    public function searchMembers()
    {
        $response = ["success" => false];

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

        $name = isset($_POST["name"]) && $_POST["name"] != "" ? $_POST["name"] : false;
        $role = isset($_POST["role"]) && preg_match("/^(translators|facilitators|all)$/", $_POST["role"]) ? $_POST["role"] : "all";
        $language = isset($_POST["language"]) && $_POST["language"] != "" ? [$_POST["language"]] : null;
        $page = isset($_POST["page"]) ? (integer)$_POST["page"] : 1;

        if($name || $role || $language)
        {
            $response["success"] = true;
            $response["count"] = $this->_membersModel->searchMembers($name, $role, $language, true);
            $response["members"] = $this->_membersModel->searchMembers($name, $role, $language, false, $page);
        }
        else
        {
            $response["error"] = __("choose_filter_option");
        }

        echo json_encode($response);
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

    public function clearCache()
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

        $abbrID = isset($_POST["abbrID"]) ? $_POST["abbrID"] : null;
        $bookCode = isset($_POST["bookCode"]) ? $_POST["bookCode"] : null;
        $sourceLangID = isset($_POST["sourceLangID"]) ? $_POST["sourceLangID"] : null;
        $bookProject = isset($_POST["bookProject"]) ? $_POST["bookProject"] : null;

        // Book source
        $cache_keyword = $bookCode."_".$sourceLangID."_".$bookProject."_usfm";

        if(Cache::has($cache_keyword))
        {
            Cache::forget($cache_keyword);

            $this->_eventsModel->getCachedSourceBookFromApi(
                $bookProject, 
                $bookCode, 
                $sourceLangID,
                $abbrID);
        }

        // Words source
        $cat_lang = $sourceLangID;
        if($sourceLangID == "ceb")
            $cat_lang = "en";

        // Get catalog
        $cat_cache_keyword = "catalog_".$bookCode."_".$cat_lang;
        if(Cache::has($cat_cache_keyword))
        {
            Cache::forget($cat_cache_keyword);
            $cat_source = $this->_model->getTWcatalog($bookCode, $cat_lang);
            $cat_json = json_decode($cat_source, true);

            if(!empty($cat_json))
                Cache::add($cat_cache_keyword, $cat_source, 60*24*7);
        }

        // Get keywords
        $tw_cache_keyword = "tw_".$sourceLangID;
        if(Cache::has($tw_cache_keyword))
        {
            Cache::forget($tw_cache_keyword);
            $tw_source = $this->_model->getTWords($sourceLangID);
            $tw_json = json_decode($tw_source, true);

            if(!empty($tw_json))
                Cache::add($tw_cache_keyword, $tw_source, 60*24*7);
        }

        $response["success"] = true;
        echo json_encode($response);
    }

    public function blockMember()
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
            $member = $this->_membersModel->getMember(
                ["blocked"],
                ["memberID", $memberID]
            );

            if(!empty($member))
            {
                $this->_membersModel->updateMember(
                    ["blocked" => !$member[0]->blocked],
                    ["memberID" => $memberID]);

                $response["success"] = true;
                $response["blocked"] = !$member[0]->blocked;
                echo json_encode($response);
            }
            else
            {
                $response["error"] = "no_member";
                echo json_encode($response);
                exit;
            }
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
            $response['targetLangs'] = $this->_eventsModel->getTargetLanguages($gwLang);
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
        //$dateFrom = isset($_POST['cal_from']) && $_POST['cal_from'] != "" ? $_POST['cal_from'] : null;
        //$dateTo = isset($_POST['cal_to']) && $_POST['cal_to'] != "" ? $_POST['cal_to'] : null;
        $admins = isset($_POST['admins']) && !empty($_POST['admins']) ? array_unique($_POST['admins']) : [];
        $act = isset($_POST['act']) && preg_match("/^(create|edit|delete)$/", $_POST['act']) ? $_POST['act'] : "create";

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

        /*if($dateFrom == null || $dateFrom === false)
        {
            $error[] = __('wrong_date_from');
        }

        if($dateTo == null || $dateTo === false)
        {
            $error[] = __('wrong_date_to');
        }*/

        if(empty($admins))
        {
            $error[] = __('enter_admins');
        }

        if(!isset($error))
        {
            $exist = $this->_eventsModel->getEvent(null, $projectID, $bookCode);
            $project = $this->_eventsModel->getProject(
                ["sourceLangID", "sourceBible"],
                ["projectID", $projectID]
            );

            $postdata = [
                "translatorsNum" => $translators,
                "l2CheckersNum" => $checkers_l2,
                "l3CheckersNum" => $checkers_l3,
                "dateFrom" => date("Y-m-d H:i:s", strtotime("0000-00-00")),
                "dateTo" => date("Y-m-d H:i:s", strtotime("0000-00-00")),
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
                    $postdata["bookCode"] = $bookCode;

                    $bookInfo = $this->_translationModel->getBookInfo($bookCode);
                    
                    if(!empty($bookInfo))
                    {
                        // Book source
                        $cache_keyword = $bookCode."_".$project[0]->sourceLangID."_".$project[0]->sourceBible."_usfm";

                        if(!Cache::has($cache_keyword))
                        {
                            $usfm = $this->_eventsModel->getCachedSourceBookFromApi(
                                $project[0]->sourceBible, 
                                $bookInfo[0]->code, 
                                $project[0]->sourceLangID,
                                $bookInfo[0]->abbrID);
                                
                            if(!$usfm || empty($usfm))
                            {
                                $error[] = __("no_source_error");
                                echo json_encode(array("error" => Error::display($error)));
                            }
                        }

                        if(!isset($error))
                        {
                            foreach ($admins as $admin) {
                                $this->_membersModel->updateMember(array("isAdmin" => true), array("memberID" => $admin));
                            }
    
                            $eventID = $this->_eventsModel->createEvent($postdata);
    
                            if($eventID)
                                echo json_encode(array("success" => __("successfully_created")));
                        }
                    }
                    else
                    {
                        $error[] = __("wrong_book_error");
                        echo json_encode(array("error" => Error::display($error)));
                    }
                    break;

                case "edit":
                    if(empty($exist))
                    {
                        $error[] = __("event_not_exists_error");
                        echo json_encode(array("error" => Error::display($error)));
                        return;
                    }

                    $superadmins = (array)json_decode($exist[0]->superadmins, true);
                    if(!in_array(Session::get("memberID"), $superadmins))
                    {
                        $error[] = __("wrong_project_id");
                        echo json_encode(array("error" => Error::display($error)));
                        return;
                    }

                    $oldAmins = (array)json_decode($exist[0]->admins, true);
                    $deletedAdmins = array_diff($oldAmins, $admins);
                    $addedAdmins = array_diff($admins, $oldAmins);

                    // Remove facilitator role from member if he is not in any events
                    foreach ($deletedAdmins as $admin) {
                        $events = $this->_eventsModel->getMemberEventsForAdmin($admin);
                        if(sizeof($events) == 1)
                            $this->_membersModel->updateMember(array("isAdmin" => false), array("memberID" => $admin));
                    }

                    // Assign facilitator role to added member
                    foreach ($addedAdmins as $admin) {
                        $this->_membersModel->updateMember(array("isAdmin" => true), array("memberID" => $admin));
                    }

                    $this->_eventsModel->updateEvent($postdata, ["projectID" => $projectID, "bookCode" => $bookCode]);
                    echo json_encode(array("success" => __("successfully_updated")));
                    break;

                case "delete":
                    if(empty($exist))
                    {
                        $error[] = __("event_not_exists_error");
                        echo json_encode(array("error" => Error::display($error)));
                        return;
                    }

                    $superadmins = (array)json_decode($exist[0]->superadmins, true);
                    if(!in_array(Session::get("memberID"), $superadmins))
                    {
                        $error[] = __("wrong_project_id");
                        echo json_encode(array("error" => Error::display($error)));
                        return;
                    }

                    $this->_eventsModel->deleteEvent(["projectID" => $projectID, "bookCode" => $bookCode]);
                    echo json_encode(array("success" => __("successfully_deleted")));
                    break;
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
                $source = $this->_eventsModel->getSourceBookFromApi($bookCode, $sourceLangID, $bookProject);
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

    // ----------------- Migration functions -------------------- //

    /**
     * Move chapters from "events" table to "chapters" chapters
     * @return mixed
     */
    public function migrateChapters()
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

        $events = $this->_eventsModel->getEvents();

        foreach ($events as $event) {
            $chapters = (array)json_decode($event->chapters, true);

            foreach ($chapters as $key => $chapter) {
                if(!empty($chapter))
                {
                    $postdata = [
                        "eventID" => $event->eventID,
                        "trID" => $chapter["trID"],
                        "memberID" => $chapter["memberID"],
                        "chunks" => json_encode($chapter["chunks"]),
                        "chapter" => $key,
                        "done" => isset($chapter["done"]) ? $chapter["done"] : false
                    ];

                    $this->_eventsModel->assignChapter($postdata);
                }
            }
        }

        echo "<h2>Done</h2>";
        echo "<a href='/admin'>Go Back</a>";
    }
}
