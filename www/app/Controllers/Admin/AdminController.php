<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\EventsModel;
use App\Models\MembersModel;
use App\Models\NewsModel;
use App\Models\SailDictionaryModel;
use App\Models\TranslationsModel;
use File;
use Helpers\Data;
use View;
use Config\Config;
use Helpers\Password;
use Helpers\Constants\EventMembers;
use Helpers\Constants\EventStates;
use Helpers\Constants\EventSteps;
use Helpers\Gump;
use Helpers\Session;
use Helpers\Url;
use Helpers\UsfmParser;
use Shared\Legacy\Error;
use Support\Facades\Cache;
use Support\Facades\Input;
use ZipArchive;

class AdminController extends Controller {

    private $_membersModel;
    private $_eventsModel;
    private $_translationModel;
    private $_saildictModel;
    private $_newsModel;
    protected $layout = "admin";

    public function __construct()
    {
        parent::__construct();

        if(Config::get("app.isMaintenance")
            && !in_array($_SERVER['REMOTE_ADDR'], Config::get("app.ips")))
        {
            Url::redirect("maintenance");
        }

        $this->_membersModel = new MembersModel();
        $this->_eventsModel = new EventsModel();
        $this->_translationModel = new TranslationsModel();
        $this->_saildictModel = new SailDictionaryModel();
        $this->_newsModel = new NewsModel();
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

        $catalog = $this->_eventsModel->getCachedFullCatalog();

        $data["gwProjects"] = $this->_eventsModel->getGatewayProject();
        $data["gwLangs"] = $this->_eventsModel->getAllLanguages(true);
        $data["projects"] = $this->_eventsModel->getProjects(Session::get("memberID"));
        $data["sourceTranslations"] = $this->_translationModel->getSourceTranslations($catalog);

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
            $otDone = 0;
            $ntDone = 0;
            $data["OTprogress"] = 0;
            $data["NTprogress"] = 0;

            foreach ($data["events"] as $event)
            {
                if($event->abbrID < 41) // Old testament
                {
                    if(!empty($event->state) &&
                        EventStates::enum($event->state) >= EventStates::enum(EventStates::TRANSLATED))
                    {
                        $otDone++;
                    }
                }
                else // New testament
                {
                    if(!empty($event->state) &&
                        EventStates::enum($event->state) >= EventStates::enum(EventStates::TRANSLATED))
                    {
                        $ntDone++;
                    }
                }
            }

            $data["OTprogress"] = 100*$otDone/39;
            $data["NTprogress"] = 100*$ntDone/27;
        }

        return View::make('Admin/Main/Project')
            ->shares("title", __("admin_events_title"))
            ->shares("data", $data);
    }

    public function import()
    {
        $response = [
            "success" => false,
            "error" => __("unknown_import_type_error")
        ];

        if (!Session::get('loggedin'))
        {
            return;
        }

        if(!Session::get('isSuperAdmin'))
        {
            return;
        }

        $_POST = Gump::xss_clean($_POST);
        $_FILES = Gump::xss_clean($_FILES);

        $import = isset($_FILES['import']) && $_FILES['import'] != "" ? $_FILES['import']
                : (isset($_POST['import']) && $_POST['import'] != "" ? $_POST['import'] : null);
        $type = isset($_POST['type']) && $_POST['type'] != "" ? $_POST['type'] : "dcs";
        $eventID = isset($_POST['eventID']) && $_POST['eventID'] != "" ? (integer)$_POST['eventID'] : null;

        if($import !== null)
        {
            if($eventID !== null)
            {
                switch ($type)
                {
                    case "dcs":
                        $usfm = $this->processDCSUrl($import);

                        if($usfm != null)
                        {
                            $response = $this->importProjectToEvent($usfm, $eventID);
                        }
                        break;

                    case "usfm":
                        if(File::extension($import["name"]) == "usfm"
                            || File::extension($import["name"]) == "txt")
                        {
                            $usfm = File::get($import["tmp_name"]);
                            $response = $this->importProjectToEvent($usfm, $eventID);
                        }
                        else
                        {
                            $response["error"] = __("usfm_not_valid_error");
                        }
                        break;

                    case "ts":
                        if(File::extension($import["name"]) == "tstudio")
                        {
                            $usfm = $this->processTStudioProject($import);

                            if($usfm != null)
                            {
                                $response = $this->importProjectToEvent($usfm, $eventID);
                            }
                            else
                            {
                                $response["error"] = __("usfm_not_valid_error");
                            }
                        }
                        else
                        {
                            $response["error"] = __("usfm_not_valid_error");
                        }
                        break;

                    default:
                        $response["error"] = __("unknown_import_type_error");
                        break;
                }
            }
            else
            {
                $response["error"] = __("event_does_not_exist_error");
            }
        }
        else
        {
            $response["error"] = __('unknown_import_type_error');
        }

        echo json_encode($response);
    }

    public function repos_search($q)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://git.door43.org/api/v1/repos/search?limit=50&q=" . $q);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $data = curl_exec($ch);

        if(curl_errno($ch))
        {
            return false;
        }

        curl_close($ch);

        echo $data;
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
        $data["members"] = $this->_membersModel->searchMembers(null, "all", null, false, true);

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

            if(!isset($data["books"][$item->userName]["books"]))
            {
                $tmp = [];
                $tmp["name"] = $item->name;
                $tmp["chapters"] = [];
                $data["books"][$item->userName]["books"][$item->code] = $tmp;
            }

            if(!isset($data["books"][$item->userName]["books"][$item->code]))
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

    public function tools()
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

        $data["menu"] = 3;

        $data["saildict"] = $this->_saildictModel->getSunDictionary();

        return View::make('Admin/Main/Tools')
            ->shares("title", __("admin_tools_title"))
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

            if(!empty($event))
            {
                $admins = $event[0]->admins;

                if(EventStates::enum($event[0]->state) >= EventStates::enum(EventStates::TRANSLATED))
                {
                    $admins = $event[0]->admins_l2;
                }

                $members = [];
                $membersArray = (array)$this->_membersModel->getMembers(json_decode($admins));

                foreach ($membersArray as $member) {
                    $members[$member->memberID] = "{$member->firstName} "
                        .mb_substr($member->lastName, 0, 1)
                        .". ({$member->userName})";
                }

                $response["success"] = true;
                $response["admins"] = $members;
                $response["event"] = $event[0];
            }
            else
            {
                $response["error"] = __('wrong_parameters_error');
            }
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
        $level = isset($_POST['level']) && $_POST['level'] != "" ? (integer)$_POST['level'] : 1;

        if($eventID == null)
        {
            $response["error"] = __('wrong_parameters_error');
        }

        if(!isset($response["error"]))
        {
            if($level == 1)
            {
                $event = $this->_eventsModel->getEventWithContributors($eventID);

                if(!empty($event))
                {
                    $mode = $event[0]->bookProject;

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
                        $otherCheck = (array)json_decode($translator->otherCheck);

                        if(in_array($mode, ["tn", "sun"]))
                        {
                            $checkersArr = array_merge($checkersArr, array_values(array_map(function($elm) {
                                return $elm->memberID;
                            }, $peerCheck)));
                            $checkersArr = array_merge($checkersArr, array_values(array_map(function($elm) {
                                return $elm->memberID;
                            }, $kwCheck)));
                            $checkersArr = array_merge($checkersArr, array_values(array_map(function($elm) {
                                return $elm->memberID;
                            }, $crCheck)));
                            $checkersArr = array_merge($checkersArr, array_values(array_map(function($elm) {
                                return $elm->memberID;
                            }, $otherCheck)));
                        }
                        else
                        {
                            $checkersArr = array_merge($checkersArr, array_values($verbCheck));
                            $checkersArr = array_merge($checkersArr, array_values($peerCheck));
                            $checkersArr = array_merge($checkersArr, array_values($kwCheck));
                            $checkersArr = array_merge($checkersArr, array_values($crCheck));
                        }
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
                        $tmp["memberID"] = $chapter["memberID"];
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
            elseif ($level == 2)
            {
                $event = $this->_eventsModel->getEventWithContributorsL2($eventID);

                if(!empty($event))
                {
                    $admins = [];
                    $translators = [];
                    $checkers = [];

                    // Facilitators
                    $adminsArr = (array)json_decode($event[0]->admins_l2);

                    // Checkers
                    $checkersArr = [];
                    foreach ($event as $translator) {
                        $sndCheck = (array)json_decode($translator->sndCheck);
                        $peer1Check = (array)json_decode($translator->peer1Check);
                        $peer2Check = (array)json_decode($translator->peer2Check);

                        $sndMems = [];
                        foreach ($sndCheck as $item) {
                            $sndMems[] = $item->memberID;
                        }

                        $p1Mems = [];
                        foreach ($peer1Check as $item) {
                            $p1Mems[] = $item->memberID;
                        }

                        $p2Mems = [];
                        foreach ($peer2Check as $item) {
                            $p2Mems[] = $item->memberID;
                        }

                        $checkersArr = array_merge($checkersArr, $sndMems);
                        $checkersArr = array_merge($checkersArr, $p1Mems);
                        $checkersArr = array_merge($checkersArr, $p2Mems);
                    }

                    // Chapters
                    $data["chapters"] = [];
                    for($i=1; $i <= $event[0]->chaptersNum; $i++)
                    {
                        $data["chapters"][$i] = [];
                    }

                    $chapters = $this->_eventsModel->getChapters($event[0]->eventID, null, null, "l2");

                    foreach ($chapters as $chapter) {
                        $tmp["l2memberID"] = $chapter["l2memberID"];
                        $data["chapters"][$chapter["chapter"]] = $tmp;
                    }

                    foreach ($data["chapters"] as $chapter) {
                        if(!empty($chapter))
                            $checkersArr[] = $chapter["l2memberID"];
                    }
                    $checkersArr = array_unique($checkersArr);

                    $allMembers = array_unique(array_merge($adminsArr, $checkersArr));
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
        /*else
        {
            if(($sourceTranslation != "ulb|en" && $sourceTranslation != "udb|en") && $projectType == null)
            {
                $error[] = __("choose_project_type");
            }
        }*/

        if($projectType == null)
        {
            $error[] = __("choose_project_type");
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
                $projectMode . ($projectType == "sun" ? "_sun" : "") : $projectType;
            
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
            $response["members"] = $this->_membersModel->searchMembers($name, $role, $language, false, true, $page);
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
        $sourceBible = isset($_POST["sourceBible"]) ? $_POST["sourceBible"] : null;

        // Book source
        $cache_keyword = $bookCode."_".$sourceLangID."_".$sourceBible."_usfm";

        if(Cache::has($cache_keyword))
            Cache::forget($cache_keyword);

        $source = $this->_eventsModel->getCachedSourceBookFromApi(
                $sourceBible,
                $bookCode, 
                $sourceLangID,
                $abbrID);

        if($source)
            $response["success"] = true;

        echo json_encode($response);
    }
    
    public function updateAllBooksCache()
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

        $sourceLangID = isset($_POST["sourceLangID"]) ? $_POST["sourceLangID"] : null;
        $sourceBible = isset($_POST["sourceBible"]) ? $_POST["sourceBible"] : null;
        
        $booksUpdated = 0;

        if($sourceLangID && $sourceBible)
        {
            $books = $this->_eventsModel->getBooks();

            $renDir = "../app/Templates/Default/Assets/source/".$sourceLangID."_".$sourceBible."_tmp";
            $origDir = "../app/Templates/Default/Assets/source/".$sourceLangID."_".$sourceBible;

            //File::deleteDirectory($renDir);
            if(File::exists($origDir))
                File::move($origDir, $renDir);

            foreach ($books as $book)
            {
                $bookCode = $book->code;
                $abbrID = $book->abbrID;
                
                // Book source
                $cache_keyword = $bookCode."_".$sourceLangID."_".$sourceBible."_usfm";

                if(Cache::has($cache_keyword))
                    Cache::forget($cache_keyword);

                $source = $this->_eventsModel->getCachedSourceBookFromApi(
                        $sourceBible,
                        $bookCode, 
                        $sourceLangID,
                        $abbrID);

                if($source)
                {
                    // Words source
                    $cat_lang = $sourceLangID;
                    if($sourceLangID == "ceb")
                        $cat_lang = "en";

                    // Get catalog
                    $cat_cache_keyword = "catalog_".$bookCode."_".$cat_lang;
                    if(Cache::has($cat_cache_keyword))
                        Cache::forget($cat_cache_keyword);

                    $cat_source = $this->_eventsModel->getTWcatalog($bookCode, $cat_lang);
                    $cat_json = json_decode($cat_source, true);

                    if(!empty($cat_json))
                        Cache::add($cat_cache_keyword, $cat_source, 365*24*60);

                    // Get keywords
                    $tw_cache_keyword = "tw_".$sourceLangID;
                    if(Cache::has($tw_cache_keyword))
                        Cache::forget($tw_cache_keyword);

                    $tw_source = $this->_eventsModel->getTWords($sourceLangID);
                    $tw_json = json_decode($tw_source, true);

                    if(!empty($tw_json))
                        Cache::add($tw_cache_keyword, $tw_source, 365*24*60);

                    $response["success"] = true;
                    $booksUpdated++;
                }
            }

            if($booksUpdated > 0)
            {
                File::deleteDirectory($renDir);

            }
            else
            {
                File::move($renDir, $origDir);
            }
        }
        
        $response["booksUpdated"] = $booksUpdated;
        
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

            $postdata = [];
            
            switch($act)
            {
                case "create":
                    if(!empty($exist) && 
                            $exist[0]->state != EventStates::TRANSLATED &&
                            $exist[0]->state != EventStates::L2_CHECKED)
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

                            if(empty($exist))
                            {
                                $postdata["admins"] = json_encode($admins);
                                $postdata["dateFrom"] = date("Y-m-d H:i:s", strtotime("0000-00-00"));
                                $postdata["dateTo"] = date("Y-m-d H:i:s", strtotime("0000-00-00"));
                                $eventID = $this->_eventsModel->createEvent($postdata);
                                
                            }
                            else
                            {
                                // Create(change state) L2 event
                                if($exist[0]->state == EventStates::TRANSLATED)
                                {
                                    $postdata["admins_l2"] = json_encode($admins);
                                    $postdata["state"] = EventStates::L2_RECRUIT;
                                }
                                else
                                {
                                    $postdata["state"] = EventStates::L3_RECRUIT;
                                }
                                $eventID = $this->_eventsModel->updateEvent($postdata, ["projectID" => $projectID, "bookCode" => $bookCode]);
                            }
                            
    
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

                    $dbAdmins = [];
                    if(EventStates::enum($exist[0]->state) <= EventStates::enum(EventStates::TRANSLATED))
                    {
                        $dbAdmins = (array)json_decode($exist[0]->admins, true);
                        $postdata["admins"] = json_encode($admins);
                    }
                    elseif(EventStates::enum($exist[0]->state) <= EventStates::enum(EventStates::L2_CHECKED))
                    {
                        $dbAdmins = (array)json_decode($exist[0]->admins_l2, true);
                        $postdata["admins_l2"] = json_encode($admins);
                    }

                    $oldAdmins = $dbAdmins;
                    $deletedAdmins = array_diff($oldAdmins, $admins);
                    $addedAdmins = array_diff($admins, $oldAdmins);

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

                    // Check the level of the event
                    if($exist[0]->state == EventStates::L2_CHECK || $exist[0]->state == EventStates::L2_RECRUIT)
                    {
                        // Set to previous state
                        $this->_eventsModel->updateEvent(["state" => EventStates::TRANSLATED, "admins_l2" => ""],
                            ["eventID" => $exist[0]->eventID]);

                        // Delete L2 checkers
                        $this->_eventsModel->deleteL2Checkers(["eventID" => $exist[0]->eventID]);

                        // Remove L2 checkers from chapters
                        $this->_eventsModel->updateChapter([
                            "l2memberID" => 0,
                            "l2chID" => 0,
                            "l2checked" => false
                        ], [
                            "eventID" => $exist[0]->eventID
                        ]);

                        // Remove L2 translations
                        $translatons = $this->_translationModel->getEventTranslationByEventID(
                            $exist[0]->eventID
                        );

                        foreach ($translatons as $trans)
                        {
                            $verses = (array)json_decode($trans->translatedVerses, true);

                            if(!empty($verses[EventMembers::L2_CHECKER]))
                            {
                                $verses[EventMembers::L2_CHECKER] = ["verses" => []];

                                $this->_translationModel->updateTranslation([
                                    "translatedVerses" => json_encode($verses)
                                ], [
                                    "tID" => $trans->tID
                                ]);
                            }
                        }

                        echo json_encode(array("success" => __("successfully_deleted")));
                    }
                    elseif ($exist[0]->state == EventStates::STARTED || $exist[0]->state == EventStates::TRANSLATING)
                    {
                        $this->_eventsModel->deleteEvent(["eventID" => $exist[0]->eventID]);
                        echo json_encode(array("success" => __("successfully_deleted")));
                    }

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


    /**
     * Clones repository
     * @param $url
     * @return USFM file
     */
    private function processDCSUrl($url)
    {
        $usfm = null;

        $folderpath = "/tmp/".uniqid();

        shell_exec("/usr/bin/git clone ". $url ." ".$folderpath." 2>&1");

        $usfm = $this->compileUSFMProject($folderpath);

        return $usfm;
    }

    /**
     * Exctracts .tstudio file
     * @param $file
     * @return USFM file
     */
    private function processTStudioProject($file)
    {
        $usfm = null;

        $folderpath = "/tmp/".uniqid();

        $zip = new ZipArchive();
        $zip->open($file["tmp_name"]);
        $zip->extractTo($folderpath);
        $zip->close();
        $dirs = File::directories($folderpath);

        foreach ($dirs as $dir) {
            if(File::isDirectory($dir))
            {
                $usfm = $this->compileUSFMProject($dir);
                break;
            }
        }

        return $usfm;
    }


    /**
     * Compiles all the chunks into a single usfm file
     * @param $folderpath
     * @return null
     */
    private function compileUSFMProject($folderpath)
    {
        $usfm = null;

        if(File::exists($folderpath))
        {
            $filepath = $folderpath . "/tmpfile";

            $files = File::files($folderpath);
            foreach ($files as $file) {
                if(preg_match("/\.usfm$/", $file))
                {
                    // If repository contains only one usfm with entire book
                    $usfm = File::get($file);
                    File::deleteDirectory($folderpath);
                    return $usfm;
                }
            }

            // Iterate through all the chapters and chunks
            $dirs = File::directories($folderpath);
            sort($dirs);
            foreach($dirs as $dir)
            {
                if(preg_match("/[0-9]{2,3}$/", $dir, $chapters))
                {
                    $chapter = (integer)$chapters[0];

                    $files = File::allFiles($dir);
                    sort($files);
                    foreach($files as $file)
                    {
                        if(preg_match("/[0-9]{2,3}.txt$/", $file, $chunks))
                        {
                            $chunk = (integer)$chunks[0];
                            $text = File::get($file);
                            if($chunk == 1)
                            {
                                // Fix usfm with missed chapter number tags
                                if(!preg_match("/^\\\\c/", $text))
                                {
                                    $text = "\c ".$chapter." ".$text;
                                }
                            }

                            File::append($filepath, "\s5\n" . $text);
                        }
                    }
                }
            }

            if(File::exists($filepath))
            {
                $usfm = File::get($filepath);
                File::deleteDirectory($folderpath);
            }
        }

        return $usfm;
    }


    private function importProjectToEvent($usfm, $eventID)
    {
        $response = ["success" => false];
        $usfmData = UsfmParser::parse($usfm);

        $event = $this->_eventsModel->getEvent($eventID);
        if(!empty($event))
        {
            if(isset($usfmData["chapters"]) && sizeof($usfmData["chapters"]) == $event[0]->chaptersNum)
            {
                // Check if a "fake" user exists
                $member = $this->_membersModel->getMemberWithProfile("spec");
                if(empty($member))
                {
                    $mid = $this->_membersModel->createMember([
                        "userName" => "spec",
                        "firstName" => "Special",
                        "lastName" => "User",
                        "password" => "none",
                        "email" => "none",
                        "active" => true,
                        "verified" => true
                    ]);

                    $this->_membersModel->createProfile([
                        "mID" => $mid
                    ]);
                }
                else
                {
                    $mid = $member[0]->memberID;
                }

                // Check if there are translations of this event in database
                $trans = $this->_translationModel->getEventTranslationByEventID($eventID);
                if(empty($trans))
                {
                    // Create new translator
                    $chkData = [];
                    for($i=1; $i<=$event[0]->chaptersNum; $i++)
                    {
                        $chkData[$i] = $mid;
                    }

                    $trData = array(
                        "memberID" => $mid,
                        "eventID" => $eventID,
                        "step" => EventSteps::NONE,
                        "currentChapter" => 0,
                        "verbCheck" => json_encode($chkData),
                        "peerCheck" => json_encode($chkData),
                        "kwCheck" => json_encode($chkData),
                        "crCheck" => json_encode($chkData)
                    );
                    $trID = $this->_eventsModel->addTranslator($trData);

                    foreach ($usfmData["chapters"] as $key => $chapter)
                    {
                        $chunks = [];
                        foreach ($chapter as $chunkkey => $chunk) {
                            $chunks[] = array_keys($chunk);

                            $translationVerses = [
                                EventMembers::TRANSLATOR => [
                                    "blind" => "",
                                    "verses" => $chunk
                                ],
                                EventMembers::L2_CHECKER => [
                                    "verses" => array()
                                ],
                                EventMembers::L3_CHECKER => [
                                    "verses" => array()
                                ],
                            ];

                            // Create new translations
                            $this->_translationModel->createTranslation([
                                "projectID" => $event[0]->projectID,
                                "eventID" => $eventID,
                                "trID" => $trID,
                                "targetLang" => $event[0]->targetLang,
                                "bookProject" => $event[0]->bookProject,
                                "abbrID" => $event[0]->abbrID,
                                "bookCode" => $event[0]->bookCode,
                                "chapter" => $key,
                                "chunk" => $chunkkey,
                                "firstvs" => key($chunk),
                                "translatedVerses" => json_encode($translationVerses),
                                "translateDone" => true
                            ]);
                        }

                        // Assign chapters to new translator
                        $this->_eventsModel->assignChapter([
                            "eventID" => $eventID,
                            "memberID" => $mid,
                            "trID" => $trID,
                            "chapter" => $key,
                            "chunks" => json_encode($chunks),
                            "done" => true
                        ]);

                        $this->_eventsModel->updateEvent([
                            "state" => EventStates::TRANSLATED
                        ], [
                            "eventID" => $eventID
                        ]);

                        $response["success"] = true;
                        $response["message"] = __("import_successfull_massage");
                    }
                }
                else
                {
                    $response["error"] = __("event_has_translations_error");
                }
            }
            else
            {
                $response["error"] = __("usfm_not_valid_error");
            }
        }
        else
        {
            $response["error"] = __("event_notexist_error");
        }

        return $response;
    }

    public function updateLanguages()
    {
        $result = ["success" => false];

        if (!Session::get('loggedin'))
        {
            $result["error"] = __("not_loggedin_error");
            echo json_encode($result);
            exit;
        }

        if(!Session::get('isSuperAdmin'))
        {
            $result["error"] = __("not_enough_rights_error");
            echo json_encode($result);
            exit;
        }

        $result = $this->_eventsModel->insertLangsFromTD();

        echo json_encode($result);
    }

    public function createMultipleUsers()
    {
        $result = ["success" => false];

        if (!Session::get('loggedin'))
        {
            $result["error"] = __("not_loggedin_error");
            echo json_encode($result);
            exit;
        }

        if(!Session::get('isSuperAdmin'))
        {
            $result["error"] = __("not_enough_rights_error");
            echo json_encode($result);
            exit;
        }

        $amount = (integer)Input::get("amount", "50");
        $langs = (string)Input::get("langs", "en");
        $password = (string)Input::get("password", "");

        if(mb_strlen(trim($password)) < 6)
        {
            $result["error"] = __("password_short_error");
            echo json_encode($result);
            exit;
        }

        if($amount <= 0)
            $amount = 50;

        if($langs == "" || !preg_match("/[a-z\-]+,?\s?/", $langs))
            $langs = "en";

        $ilangs = explode(",", $langs);

        $langs = [];

        foreach ($ilangs as $lang) {
            $lang = trim($lang);

            $langs[$lang] = [3,3];
        }

        $password = Password::make($password);

        $res = $this->_membersModel->createMultipleMembers(
            $amount,
            $langs,
            $password);

        $result["success"] = true;
        $result["msg"] = $res;

        echo json_encode($result);
    }


    public function deleteSailWord()
    {
        $result = ["success" => false];

        if (!Session::get('loggedin'))
        {
            $result["error"] = __("not_loggedin_error");
            echo json_encode($result);
            exit;
        }

        if(!Session::get('isSuperAdmin'))
        {
            $result["error"] = __("not_enough_rights_error");
            echo json_encode($result);
            exit;
        }

        $word = Input::get("word", "");

        if(trim($word) != "")
        {
            if($this->_saildictModel->deleteSunWord(["word" => $word]))
            {
                $result["success"] = true;
            }
        }

        echo json_encode($result);
    }


    public function createSailWord()
    {
        $result = ["success" => false];

        if (!Session::get('loggedin'))
        {
            $result["error"] = __("not_loggedin_error");
            echo json_encode($result);
            exit;
        }

        if(!Session::get('isSuperAdmin'))
        {
            $result["error"] = __("not_enough_rights_error");
            echo json_encode($result);
            exit;
        }

        $word = Input::get("word", "");
        $symbol = Input::get("symbol", "");

        if(trim($word) != "" && trim($symbol) != "")
        {
            $data = [
                "word" => $word,
                "symbol" => $symbol
            ];

            $exist = $this->_saildictModel->getSunWord(["word" => $word]);

            if(empty($exist))
            {
                if($this->_saildictModel->createSunWord($data))
                {
                    $li = '<li class="sun_content" id="'.$word.'">
                            <div class="tools_delete_word glyphicon glyphicon-remove" title="'.__("delete").'">
                                <img src="'.template_url("img/loader.gif").'">
                            </div>

                            <div class="sail_word">'.$word.'</div>
                            <div class="sail_symbol">'.$symbol.'</div>
                            <input type="text" value="'.$symbol.'" />
                            <div class="clear"></div>
                        </li>';

                    $result["success"] = true;
                    $result["li"] = $li;
                }
            }
            else
            {
                $result["error"] = __("sail_word_exists");
            }
        }

        echo json_encode($result);
    }


    public function createNews()
    {
        $result = ["success" => false];

        if (!Session::get('loggedin'))
        {
            $result["error"] = __("not_loggedin_error");
            echo json_encode($result);
            exit;
        }

        if(!Session::get('isSuperAdmin'))
        {
            $result["error"] = __("not_enough_rights_error");
            echo json_encode($result);
            exit;
        }

        $title = Input::get("title", "");
        $category = Input::get("category", "common");
        $text = Input::get("text", "");

        if(trim($title) != "" && trim($text) != "" && preg_match("/^common|vmast|vsail|level2|notes$/", $category))
        {
            $data = [
                "title" => $title,
                "category" => $category,
                "text" => $text,
                "created_at" => date("Y-m-d H:i:s", time())
            ];

            if($this->_newsModel->createNews($data))
            {
                $result["success"] = true;
                $result["msg"] = __("successfully_created");
            }
            else
            {
                $result["error"] = __("error_ocured");
            }
        }
        else
        {
            $result["error"] = __("wrong_parameters");
        }

        echo json_encode($result);
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
