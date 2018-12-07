<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\ApiModel;
use App\Models\EventsModel;
use App\Models\MembersModel;
use App\Models\NewsModel;
use App\Models\SailDictionaryModel;
use App\Models\TranslationsModel;
use Config\Config;
use File;
use Helpers\Constants\EventMembers;
use Helpers\Constants\EventStates;
use Helpers\Constants\EventSteps;
use Helpers\Gump;
use Helpers\Password;
use Helpers\Session;
use Helpers\Url;
use Helpers\UsfmParser;
use Shared\Legacy\Error;
use Support\Facades\Cache;
use Support\Facades\Input;
use Support\Facades\View;
use ZipArchive;

class AdminController extends Controller {

    private $_membersModel;
    private $_eventsModel;
    private $_apiModel;
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
        $this->_apiModel = new ApiModel();
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

        $catalog = $this->_apiModel->getCachedFullCatalog();

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
            $twDone = 0;
            $data["OTprogress"] = 0;
            $data["NTprogress"] = 0;
            $data["TWprogress"] = 0;

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
                else if($event->abbrID < 68) // New testament
                {
                    if(!empty($event->state) &&
                        EventStates::enum($event->state) >= EventStates::enum(EventStates::TRANSLATED))
                    {
                        $ntDone++;
                    }
                }
                else if($event->abbrID < 71)
                {
                    if(!empty($event->state) &&
                        EventStates::enum($event->state) >= EventStates::enum(EventStates::TRANSLATED))
                    {
                        $twDone++;
                    }
                }
            }

            $data["OTprogress"] = 100*$otDone/39;
            $data["NTprogress"] = 100*$ntDone/27;
            $data["TWprogress"] = 100*$twDone/3;
        }

        $page = 'Admin/Main/Project';
        if(!empty($data["project"]) && $data["project"][0]->bookProject == "tw")
            $page = 'Admin/Main/ProjectTW';

        return View::make($page)
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
        $projectID = isset($_POST['projectID']) && $_POST['projectID'] != "" ? (integer)$_POST['projectID'] : 0;
        $eventID = isset($_POST['eventID']) && $_POST['eventID'] != "" ? (integer)$_POST['eventID'] : 0;
        $bookCode = isset($_POST['bookCode']) && $_POST['bookCode'] != "" ? $_POST['bookCode'] : null;
        $bookProject = isset($_POST['bookProject']) && $_POST['bookProject'] != "" ? $_POST['bookProject'] : null;
        $importLevel = isset($_POST['importLevel']) && $_POST['importLevel'] != "" ? (integer)$_POST['importLevel'] : 1;
        $importProject = isset($_POST['importProject']) && $_POST['importProject'] != "" ? $_POST['importProject'] : null;

        if($import !== null && $bookCode != null && $bookProject != null && $importProject != null)
        {
            switch ($type)
            {
                case "dcs":
                    $path = $this->_apiModel->processDCSUrl($import);

                    switch ($importProject)
                    {
                        case "ulb":
                        case "udb":
                            $usfm = $this->_apiModel->compileUSFMProject($path);

                            if($usfm != null)
                            {
                                $response = $this->importScriptureToEvent($usfm, $projectID, $eventID, $bookCode, $importLevel);
                            }
                            break;
                        case "tn":
                            $tn = $this->_apiModel->getTranslationNotes($bookCode, null, false, $path);
                            if(!empty($tn))
                            {
                                $response = $this->importTnToEvent($tn, $projectID, $eventID, $bookCode);
                            }
                            else
                            {
                                $response["error"] = __("usfm_not_valid_error");
                            }
                            break;
                        case "tq":
                            $response["error"] = __("not_implemented");
                            break;
                        case "tw":
                            $response["error"] = __("not_implemented");
                            break;
                        default:
                            $response["error"] = __("not_implemented");
                    }
                    break;

                case "usfm":
                    if(File::extension($import["name"]) == "usfm"
                        || File::extension($import["name"]) == "txt")
                    {
                        $usfm = File::get($import["tmp_name"]);
                        $response = $this->importScriptureToEvent($usfm, $projectID, $eventID, $bookCode, $importLevel);
                    }
                    else
                    {
                        $response["error"] = __("usfm_not_valid_error");
                    }
                    break;

                case "ts":
                    if(File::extension($import["name"]) == "tstudio")
                    {
                        if(!in_array($bookProject, ["tn","tq","tw"]))
                        {
                            $path = $this->_apiModel->processZipFile($import);
                            $usfm = $this->_apiModel->compileUSFMProject($path);

                            if($usfm != null)
                            {
                                $response = $this->importScriptureToEvent($usfm, $projectID, $eventID, $bookCode, $importLevel);
                            }
                            else
                            {
                                $response["error"] = __("usfm_not_valid_error");
                            }
                        }
                        else
                        {
                            $response["error"] = __("not_implemented");
                        }
                    }
                    else
                    {
                        $response["error"] = __("usfm_not_valid_error");
                    }
                    break;

                case "zip":
                    if(File::extension($import["name"]) == "zip")
                    {
                        $path = $this->_apiModel->processZipFile($import);
                        $tn = $this->_apiModel->getTranslationNotes($bookCode, null, false, $path);

                        if(!empty($tn))
                        {
                            $response = $this->importTnToEvent($tn, $projectID, $eventID, $bookCode);
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
                if(EventStates::enum($event[0]->state) >= EventStates::enum(EventStates::L2_CHECKED))
                {
                    $admins = $event[0]->admins_l3;
                }
                elseif(EventStates::enum($event[0]->state) >= EventStates::enum(EventStates::TRANSLATED))
                {
                    if(in_array($event[0]->bookProject, ["tn","tq","tw"]))
                        $admins = $event[0]->admins_l3;
                    else
                        $admins = $event[0]->admins_l2;
                }
                else
                {
                    $admins = $event[0]->admins;
                }

                $members = [];
                $membersArray = (array)$this->_membersModel->getMembers(json_decode($admins));

                foreach ($membersArray as $member) {
                    $members[$member->memberID] = "{$member->firstName} "
                        .mb_substr($member->lastName, 0, 1)
                        .". ({$member->userName})";
                }

                if(in_array($event[0]->bookProject, ["tn","tq","tw"]))
                {
                    $ulbEvent = $this->_eventsModel->getEventByBookAndLanguage($event[0]->bookCode, $event[0]->targetLang);
                    if(!empty($ulbEvent))
                        $response["ulb"] = $ulbEvent[0];
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
        $mode = isset($_POST['mode']) && $_POST['mode'] != "" ? $_POST['mode'] : "ulb";

        if($eventID == null)
        {
            $response["error"] = __('wrong_parameters_error');
        }

        if(!isset($response["error"]))
        {
            // L1 event for ulb, udb projects
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

                        if(in_array($mode, ["tn", "sun", "tw", "tq"]))
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
                if (in_array($mode, ["udb","ulb"]))
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
                else
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
                            $peerCheck = (array)json_decode($translator->peerCheck);
                            $kwCheck = (array)json_decode($translator->kwCheck);
                            $crCheck = (array)json_decode($translator->crCheck);
                            $otherCheck = (array)json_decode($translator->otherCheck);

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
            }
            elseif ($level == 3)
            {
                if ($mode == "sun")
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
                            $kwCheck = (array)json_decode($translator->kwCheck);
                            $crCheck = (array)json_decode($translator->crCheck);

                            $checkersArr = array_merge($checkersArr, array_values(array_map(function($elm) {
                                return $elm->memberID;
                            }, $kwCheck)));
                            $checkersArr = array_merge($checkersArr, array_values(array_map(function($elm) {
                                return $elm->memberID;
                            }, $crCheck)));
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
                else
                {
                    // Level 3 events for ulb, udb, tn, tq, tw
                }
            }
        }

        echo json_encode($response);
    }

    public function getProjectContributors() {
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

        $projectID = isset($_POST['projectID']) && $_POST['projectID'] != "" ? (integer)$_POST['projectID'] : null;

        if($projectID == null)
        {
            $response["error"] = __('wrong_parameters_error');
        }

        if(!isset($response["error"]))
        {

            $project = $this->_eventsModel->getProjectWithContributors($projectID);

            if (!empty($project)) {

                $contributors = [];
                $contributorsIDs = [];

                $mode = $project[0]->bookProject;

                // Checkers
                foreach ($project as $participant) {
                    // Facilitators
                    $contributorsIDs = array_merge($contributorsIDs, (array)json_decode($participant->admins));
                    $contributorsIDs = array_merge($contributorsIDs, (array)json_decode($participant->admins_l2));

                    $verbCheck = (array)json_decode($participant->verbCheck);
                    $peerCheck = (array)json_decode($participant->peerCheck);
                    $kwCheck = (array)json_decode($participant->kwCheck);
                    $crCheck = (array)json_decode($participant->crCheck);
                    $otherCheck = (array)json_decode($participant->otherCheck);
                    $sndCheck = (array)json_decode($participant->sndCheck);
                    $peer1Check = (array)json_decode($participant->peer1Check);
                    $peer2Check = (array)json_decode($participant->peer2Check);

                    // Resource Checkers
                    if(in_array($mode, ["tn", "sun", "tw", "tq"]))
                    {
                        $contributorsIDs = array_merge($contributorsIDs, array_values(array_map(function($elm) {
                            return $elm->memberID;
                        }, $peerCheck)));
                        $contributorsIDs = array_merge($contributorsIDs, array_values(array_map(function($elm) {
                            return $elm->memberID;
                        }, $kwCheck)));
                        $contributorsIDs = array_merge($contributorsIDs, array_values(array_map(function($elm) {
                            return $elm->memberID;
                        }, $crCheck)));
                        $contributorsIDs = array_merge($contributorsIDs, array_values(array_map(function($elm) {
                            return $elm->memberID;
                        }, $otherCheck)));
                    }
                    else
                    {
                        // Scripture Checkers
                        $contributorsIDs = array_merge($contributorsIDs, array_values($verbCheck));
                        $contributorsIDs = array_merge($contributorsIDs, array_values($peerCheck));
                        $contributorsIDs = array_merge($contributorsIDs, array_values($kwCheck));
                        $contributorsIDs = array_merge($contributorsIDs, array_values($crCheck));

                        $contributorsIDs = array_merge($contributorsIDs, array_values(array_map(function($elm) {
                            return $elm->memberID;
                        }, $sndCheck)));
                        $contributorsIDs = array_merge($contributorsIDs, array_values(array_map(function($elm) {
                            return $elm->memberID;
                        }, $peer1Check)));
                        $contributorsIDs = array_merge($contributorsIDs, array_values(array_map(function($elm) {
                            return $elm->memberID;
                        }, $peer2Check)));
                    }

                    // Translators/L2 checkers
                    $chapters = $this->_eventsModel->getChapters($participant->eventID, null, null, null);

                    foreach ($chapters as $chapter) {
                        if ($chapter["memberID"] != null) {
                            $contributorsIDs[] = $chapter["memberID"];
                        }
                        if ($chapter["l2memberID"] != null) {
                            $contributorsIDs[] = $chapter["l2memberID"];
                        }
                    }
                }
                $contributorsIDs = array_unique($contributorsIDs);

                $filteredNumeric = array_filter($contributorsIDs, function($elm) {
                    return is_numeric($elm) && $elm > 0;
                });

                $contributors = array_merge($contributors, array_filter($contributorsIDs, function($elm) {
                    return !is_numeric($elm);
                }));

                $membersArray = (array)$this->_membersModel->getMembers($filteredNumeric, true, true);

                foreach ($membersArray as $member) {
                    if(in_array($member->memberID, $filteredNumeric))
                    {
                        $church_role = (array)json_decode($member->church_role);

                        if (in_array("Pastor", $church_role))
                            $church_role = "Pastor";
                        elseif (in_array("Seminary Professor", $church_role))
                            $church_role = "Seminary Professor";
                        elseif (in_array("Denominational Leader", $church_role))
                            $church_role = "Denominational Leader";
                        elseif (in_array("Bishop", $church_role))
                            $church_role = "Bishop";
                        elseif (in_array("Elder", $church_role))
                            $church_role = "Elder";
                        elseif (in_array("Teacher", $church_role))
                            $church_role = "Teacher";
                        else
                            $church_role = "";

                        $contributors[] = $member->firstName . " " . $member->lastName .
                            ($church_role != "" ? " (".$church_role.")" : "");
                    }
                }

                $contributors = array_map(function ($elm) {
                    return mb_convert_case ($elm, MB_CASE_TITLE, 'UTF-8');
                }, $contributors);
                $contributors = array_unique($contributors);
                sort($contributors);

                $response["contributors"] = $contributors;
                $response["success"] = true;
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

        $projectMode = isset($_POST['projectMode']) && preg_match("/(bible|tn|tq|tw)/", $_POST['projectMode']) ? $_POST['projectMode'] : "bible";
        $subGwLangs = isset($_POST['subGwLangs']) && $_POST['subGwLangs'] != "" ? $_POST['subGwLangs'] : null;
        $targetLang = isset($_POST['targetLangs']) && $_POST['targetLangs'] != "" ? $_POST['targetLangs'] : null;
        $sourceTranslation = isset($_POST['sourceTranslation']) && $_POST['sourceTranslation'] != "" ? $_POST['sourceTranslation'] : null;
        $sourceTranslationNotes = isset($_POST['sourceTranslationNotes']) && $_POST['sourceTranslationNotes'] != "" ? $_POST['sourceTranslationNotes'] : null;
        $sourceTranslationQuestions = isset($_POST['sourceTranslationQuestions']) && $_POST['sourceTranslationQuestions'] != "" ? $_POST['sourceTranslationQuestions'] : null;
        $sourceTranslationWords = isset($_POST['sourceTranslationWords']) && $_POST['sourceTranslationWords'] != "" ? $_POST['sourceTranslationWords'] : null;
        $projectType = isset($_POST['projectType']) && $_POST['projectType'] != "" ? $_POST['projectType'] : null;
        $resSourceTranslation = null;

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
            if($projectMode != "tq" && $projectMode != "tw")
                $error[] = __("choose_source_trans");
        }

        if($projectType == null)
        {
            if($projectMode != "tq" && $projectMode != "tw")
                $error[] = __("choose_project_type");
        }

        if($projectMode == "tn" && $sourceTranslationNotes == null)
        {
            $error[] = __("choose_source_notes");
        }
        else if($projectMode == "tq" && $sourceTranslationQuestions == null)
        {
            $error[] = __("choose_source_questions");
        }
        else if($projectMode == "tw" && $sourceTranslationWords == null)
        {
            $error[] = __("choose_source_words");
        }

        if($projectMode == "tq" || $projectMode == "tw")
        {
            $sourceTranslation = "ulb|en";
            $projectType = "ulb";
            if($projectMode == "tq")
                $resSourceTranslation = $sourceTranslationQuestions;
            elseif($projectMode == "tw")
                $resSourceTranslation = $sourceTranslationWords;
        }
        elseif($projectMode == "tn")
        {
            $resSourceTranslation = $sourceTranslationNotes;
        }

        if(!isset($error))
        {
            $sourceTrPair = explode("|", $sourceTranslation);
            $gwLangsPair = explode("|", $subGwLangs);

            $projType = in_array($projectMode, ['tn','tq','tw']) ?
                $projectMode : $projectType;
            
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
                "resLangID" => $resSourceTranslation
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
            $response["count"] = $this->_membersModel->searchMembers($name, $role, $language, true, false, true);
            $response["members"] = $this->_membersModel->searchMembers($name, $role, $language, false, true, true, $page);
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

        $source = $this->_apiModel->getCachedSourceBookFromApi(
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

                $source = $this->_apiModel->getCachedSourceBookFromApi(
                        $sourceBible,
                        $bookCode, 
                        $sourceLangID,
                        $abbrID);

                if($source)
                {
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
            return;

        $_POST = Gump::xss_clean($_POST);

        $bookCode = isset($_POST['book_code']) && $_POST['book_code'] != "" ? $_POST['book_code'] : null;
        $projectID = isset($_POST['projectID']) && $_POST['projectID'] != "" ? (integer)$_POST['projectID'] : null;
        $eventLevel = isset($_POST['eventLevel']) && $_POST['eventLevel'] != "" ? (integer)$_POST['eventLevel'] : 1;
        $admins = isset($_POST['admins']) && !empty($_POST['admins']) ? array_unique($_POST['admins']) : [];
        $act = isset($_POST['act']) && preg_match("/^(create|edit|delete)$/", $_POST['act']) ? $_POST['act'] : "create";

        if($bookCode == null)
            $error[] = __('wrong_book_code');

        if($projectID == null)
            $error[] = __('wrong_project_id');

        if(!isset($error))
        {
            $exist = $this->_eventsModel->getEvent(null, $projectID, $bookCode);
            $project = $this->_eventsModel->getProject(
                ["projects.sourceLangID", "projects.sourceBible",
                    "projects.bookProject", "projects.gwProjectID", "projects.gwLang",
                    "projects.targetLang"],
                ["projectID", $projectID]
            );

            $postdata = [];
            
            switch($act)
            {
                case "create":
                    if(empty($admins))
                    {
                        $error[] = __('enter_admins');
                        echo json_encode(array("error" => Error::display($error)));
                        return;
                    }

                    // Check if the event is ready for Level 2, Level 3 check
                    switch ($eventLevel)
                    {
                        case 1:
                            if(!empty($exist))
                            {
                                $error[] = __("event_already_exists");
                                echo json_encode(array("error" => Error::display($error)));
                                return;
                            }
                            break;
                        case 2:
                            if (in_array($project[0]->bookProject, ["ulb","udb"]))
                            {
                                if(empty($exist) || $exist[0]->state != EventStates::TRANSLATED)
                                {
                                    $error[] = __("l2_l3_create_event_error");
                                    echo json_encode(array("error" => Error::display($error)));
                                    return;
                                }
                            }
                            elseif (in_array($project[0]->bookProject, ["tn","tq"]))
                            {
                                if(!empty($exist))
                                {
                                    $error[] = __("event_already_exists");
                                    echo json_encode(array("error" => Error::display($error)));
                                    return;
                                }
                            }
                            break;
                        case 3:
                            if (in_array($project[0]->bookProject, ["ulb","udb"]))
                            {
                                if(empty($exist) || $exist[0]->state != EventStates::L2_CHECKED)
                                {
                                    $error[] = __("l2_l3_create_event_error");
                                    echo json_encode(array("error" => Error::display($error)));
                                    return;
                                }
                            }
                            elseif (in_array($project[0]->bookProject, ["tn","tq"]))
                            {
                                // Check if related ulb event is complete (level 3 checked)
                                $ulbProject = $this->_eventsModel->getProject(["projects.projectID"],[
                                    ["projects.gwProjectID", $project[0]->gwProjectID],
                                    ["projects.gwLang", $project[0]->gwLang],
                                    ["projects.targetLang", $project[0]->targetLang],
                                    ["projects.bookProject", "ulb"]
                                ]);

                                if(!empty($ulbProject))
                                    $ulbEvent = $this->_eventsModel->getEvent(null, $ulbProject[0]->projectID, $bookCode);

                                if(empty($exist) || $exist[0]->state != EventStates::TRANSLATED
                                    || empty($ulbEvent) || $ulbEvent[0]->state != EventStates::COMPLETE)
                                {
                                    $error[] = __("l2_l3_create_event_error");
                                    echo json_encode(array("error" => Error::display($error)));
                                    return;
                                }
                            }
                            elseif ($project[0]->bookProject == "sun" && !empty($exist))
                            {
                                $error[] = __("event_already_exists");
                                echo json_encode(array("error" => Error::display($error)));
                                return;
                            }
                            break;
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
                            $usfm = $this->_apiModel->getCachedSourceBookFromApi(
                                $project[0]->sourceBible, 
                                $bookInfo[0]->code, 
                                $project[0]->sourceLangID,
                                $bookInfo[0]->abbrID);
                                
                            if(!$usfm || empty($usfm))
                            {
                                $error[] = __("no_source_error");
                                echo json_encode(array("error" => Error::display($error)));
                                return;
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
                                    if(in_array($project[0]->bookProject, ["tn","tq"]))
                                    {
                                        $postdata["admins_l3"] = json_encode($admins);
                                        $postdata["state"] = EventStates::L3_RECRUIT;
                                    }
                                    else
                                    {
                                        $postdata["admins_l2"] = json_encode($admins);
                                        $postdata["state"] = EventStates::L2_RECRUIT;
                                    }
                                }
                                else
                                {
                                    $postdata["admins_l3"] = json_encode($admins);
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

                    if(empty($admins))
                    {
                        $error[] = __('enter_admins');
                    }

                    $superadmins = (array)json_decode($exist[0]->superadmins, true);
                    if(!in_array(Session::get("memberID"), $superadmins))
                    {
                        $error[] = __("wrong_project_id");
                        echo json_encode(array("error" => Error::display($error)));
                        return;
                    }

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
                    else
                    {
                        $dbAdmins = (array)json_decode($exist[0]->admins_l3, true);
                        $postdata["admins_l3"] = json_encode($admins);
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

                    //if ($exist[0]->state == EventStates::STARTED || $exist[0]->state == EventStates::TRANSLATING)
                    //{
                    $this->_eventsModel->deleteEvent(["eventID" => $exist[0]->eventID]);
                    echo json_encode(array("success" => __("successfully_deleted")));
                    //}

                    break;
            }
        }
        else
        {
            echo json_encode(array("error" => Error::display($error)));
        }
    }


    public function createEventTw()
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
        $eventLevel = isset($_POST['eventLevel']) && $_POST['eventLevel'] != "" ? (integer)$_POST['eventLevel'] : 1;
        $admins = isset($_POST['admins']) && !empty($_POST['admins']) ? array_unique($_POST['admins']) : [];
        $act = isset($_POST['act']) && preg_match("/^(create|edit|delete)$/", $_POST['act']) ? $_POST['act'] : "create";

        if($bookCode == null)
            $error[] = __('wrong_book_code');

        if($projectID == null)
            $error[] = __('wrong_project_id');

        if(empty($admins))
            $error[] = __('enter_admins');

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
                    // Check if the event is ready for Level 2, Level 3 check
                    switch ($eventLevel)
                    {
                        case 2:
                            if(!empty($exist))
                            {
                                $error[] = __("event_already_exists");
                                echo json_encode(array("error" => Error::display($error)));
                                return;
                            }
                            break;
                        case 3:
                            if(empty($exist) || $exist[0]->state != EventStates::TRANSLATED)
                            {
                                $error[] = __("l2_l3_create_event_error");
                                echo json_encode(array("error" => Error::display($error)));
                                return;
                            }
                            break;
                    }

                    $postdata["projectID"] = $projectID;
                    $postdata["bookCode"] = $bookCode;

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
                        // Create(change state) L3 event
                        if($exist[0]->state == EventStates::TRANSLATED)
                        {
                            $postdata["admins_l3"] = json_encode($admins);
                            $postdata["state"] = EventStates::L3_RECRUIT;
                        }
                        $eventID = $this->_eventsModel->updateEvent($postdata, ["projectID" => $projectID, "bookCode" => $bookCode]);
                    }

                    if($eventID)
                        echo json_encode(array("success" => __("successfully_created")));
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

                    if(EventStates::enum($exist[0]->state) <= EventStates::enum(EventStates::TRANSLATED))
                    {
                        $dbAdmins = (array)json_decode($exist[0]->admins, true);
                        $postdata["admins"] = json_encode($admins);
                    }
                    else
                    {
                        $dbAdmins = (array)json_decode($exist[0]->admins_l3, true);
                        $postdata["admins_l3"] = json_encode($admins);
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

                    if ($exist[0]->state == EventStates::STARTED || $exist[0]->state == EventStates::TRANSLATING)
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
                $source = $this->_apiModel->getSourceBookFromApi($bookCode, $sourceLangID, $bookProject);
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


    private function importScriptureToEvent($usfm, $projectID, $eventID, $bookCode, $level)
    {
        $response = ["success" => false];
        $usfmData = UsfmParser::parse($usfm);

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

        $project = $this->_eventsModel->getProject(["projects.sourceLangID", "projects.sourceBible",
            "projects.bookProject", "projects.gwProjectID", "projects.gwLang",
            "projects.targetLang"],["projectID", $projectID]);
        $ulbProjectID = $projectID;

        if($project[0]->bookProject == "sun")
        {
            $response["error"] = __("not_allowed_action");
            return $response;
        }

        if(in_array($project[0]->bookProject, ["tn","tq","tw"]))
        {
            $ulbProject = $this->_eventsModel->getProject(["projects.projectID"],[
                ["projects.gwProjectID", $project[0]->gwProjectID],
                ["projects.gwLang", $project[0]->gwLang],
                ["projects.targetLang", $project[0]->targetLang],
                ["projects.bookProject", "ulb"]
            ]);

            // Create ulb project if it doesn't exist
            if(empty($ulbProject))
            {
                $ulbProjectID = $this->_eventsModel->createProject([
                    "gwProjectID" => $project[0]->gwProjectID,
                    "gwLang" => $project[0]->gwLang,
                    "targetLang" => $project[0]->targetLang,
                    "bookProject" => "ulb",
                    "sourceLangID" => $project[0]->sourceLangID,
                    "sourceBible" => $project[0]->sourceBible
                ]);
            }
            else
            {
                $ulbProjectID = $ulbProject[0]->projectID;
            }

            $event = $this->_eventsModel->getEvent(null, $ulbProjectID, $bookCode);
            if(!empty($event))
                $eventID = $event[0]->eventID;
        }
        else
        {
            $event = $this->_eventsModel->getEvent($eventID);
        }

        // Create event if it doesn't exist
        if(empty($event))
        {
            $newEventID = $this->_eventsModel->createEvent([
                "projectID" => $ulbProjectID,
                "bookCode" => $bookCode,
                "state" => EventStates::STARTED,
                "admins" => json_encode([$mid]),
            ]);

            $event = $this->_eventsModel->getEvent($newEventID);
            $eventID = $newEventID;
        }

        if(!empty($event))
        {
            if(isset($usfmData["chapters"]) && sizeof($usfmData["chapters"]) == $event[0]->chaptersNum)
            {
                // Check if there are translations of this event in database
                $trans = $this->_translationModel->getEventTranslationByEventID($eventID);
                if(empty($trans))
                {
                    if($level == 3)
                    {
                        $response["error"] = __("not_allowed_action");
                        return $response;
                    }

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

                    $l2chID = 0;
                    if($level == 2)
                    {
                        // Create new checker
                        $sndCheckData = [];
                        $peerCheckData = [];
                        for($i=1; $i<=$event[0]->chaptersNum; $i++)
                        {
                            $sndCheckData[$i] = ["memberID" => $mid, "done" => 2];
                            $peerCheckData[$i] = ["memberID" => $mid, "done" => 1];
                        }

                        $l2chData = array(
                            "memberID" => $mid,
                            "eventID" => $eventID,
                            "step" => EventSteps::NONE,
                            "currentChapter" => 0,
                            "sndCheck" => json_encode($sndCheckData),
                            "peer1Check" => json_encode($peerCheckData),
                            "peer2Check" => json_encode($peerCheckData)
                        );
                        $l2chID = $this->_eventsModel->addL2Checker($l2chData);
                    }

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
                                    "verses" => $level == 2 ? $chunk : array()
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
                                "l2chID" => $l2chID,
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
                            "l2memberID" => $level == 2 ? $mid : 0,
                            "trID" => $trID,
                            "l2chID" => $l2chID,
                            "chapter" => $key,
                            "chunks" => json_encode($chunks),
                            "done" => true,
                            "l2checked" => $level == 2 ? true : false
                        ]);

                        $this->_eventsModel->updateEvent([
                            "state" => $level == 2 ? EventStates::L2_CHECKED : EventStates::TRANSLATED,
                            "admins_l2" => $level == 2 ? json_encode([$mid]) : $event[0]->admins_l2
                        ], [
                            "eventID" => $eventID
                        ]);
                    }

                    $response["success"] = true;
                    $response["message"] = __("import_successful_message");
                }
                else
                {
                    if(in_array($event[0]->state, [EventStates::TRANSLATED,EventStates::L2_CHECKED]))
                    {
                        if(($event[0]->state == EventStates::TRANSLATED && $level == 3) ||
                            $event[0]->state == EventStates::L2_CHECKED && $level == 1)
                        {
                            $response["error"] = __("not_allowed_action");
                            return $response;
                        }

                        foreach ($usfmData["chapters"] as $chapter => $chunks) {
                            $new_chapter = [];
                            foreach ($chunks as $chunk => $verses) {
                                foreach ($verses as $verse => $text) {
                                    $new_chapter[$verse] = $text;
                                }
                            }
                            $usfmData["chapters"][$chapter] = $new_chapter;
                        }

                        foreach ($trans as $tran) {
                            $verses = (array)json_decode($tran->translatedVerses, true);

                            foreach ($verses[EventMembers::TRANSLATOR]["verses"] as $verse => $text) {
                                if(isset($usfmData["chapters"][$tran->chapter]) &&
                                    isset($usfmData["chapters"][$tran->chapter][$verse]) &&
                                    trim($usfmData["chapters"][$tran->chapter][$verse]) != "")
                                {
                                    switch ($level)
                                    {
                                        case 1:
                                            $verses[EventMembers::TRANSLATOR]["verses"][$verse] = $usfmData["chapters"][$tran->chapter][$verse];
                                            break;
                                        case 2:
                                            $verses[EventMembers::L2_CHECKER]["verses"][$verse] = $usfmData["chapters"][$tran->chapter][$verse];
                                            break;
                                        case 3:
                                            $verses[EventMembers::L3_CHECKER]["verses"][$verse] = $usfmData["chapters"][$tran->chapter][$verse];
                                            break;
                                    }
                                }
                            }

                            $this->_translationModel->updateTranslation([
                                "translatedVerses" => json_encode($verses),
                                "l2chID" => ($tran->l2chID == 0 && $level == 2 ? $mid : $tran->l2chID),
                                "l3chID" => $tran->l3chID == 0 && $level == 3 ? $mid : $tran->l3chID
                            ], ["tID" => $tran->tID]);
                        }

                        $chapters = $this->_eventsModel->getChapters($eventID);

                        if($event[0]->state == EventStates::TRANSLATED && $level == 2)
                        {
                            // Create new level 2 checker
                            $sndCheckData = [];
                            $peerCheckData = [];
                            for($i=1; $i<=$event[0]->chaptersNum; $i++)
                            {
                                $sndCheckData[$i] = ["memberID" => $mid, "done" => 2];
                                $peerCheckData[$i] = ["memberID" => $mid, "done" => 1];
                            }

                            $l2chData = array(
                                "memberID" => $mid,
                                "eventID" => $eventID,
                                "step" => EventSteps::NONE,
                                "currentChapter" => 0,
                                "sndCheck" => json_encode($sndCheckData),
                                "peer1Check" => json_encode($peerCheckData),
                                "peer2Check" => json_encode($peerCheckData)
                            );
                            $l2chID = $this->_eventsModel->addL2Checker($l2chData);

                            // Assign chapters to new level 2 checker
                            foreach ($chapters as $chapter) {
                                $this->_eventsModel->updateChapter([
                                    "l2memberID" => $mid,
                                    "l2chID" => $l2chID,
                                    "l2checked" => true
                                ],["chapterID" => $chapter["chapterID"]]);
                            }
                        }

                        if($event[0]->state == EventStates::L2_CHECKED && $level == 3)
                        {
                            // Create new level 3 checker
                            $peerCheckData = [];
                            for($i=1; $i<=$event[0]->chaptersNum; $i++)
                            {
                                $peerCheckData[$i] = ["memberID" => $mid, "done" => 2];
                            }

                            $l3chData = array(
                                "memberID" => $mid,
                                "eventID" => $eventID,
                                "step" => EventSteps::NONE,
                                "currentChapter" => 0,
                                "peerCheck" => json_encode($peerCheckData)
                            );
                            $l3chID = $this->_eventsModel->addL3Checker($l3chData);

                            // Assign chapters to new level 3 checker
                            foreach ($chapters as $chapter) {
                                $this->_eventsModel->updateChapter([
                                    "l3memberID" => $mid,
                                    "l3chID" => $l3chID,
                                    "l3checked" => true
                                ],["chapterID" => $chapter["chapterID"]]);
                            }
                        }

                        $this->_eventsModel->updateEvent([
                            "state" => $level == 2 ? EventStates::L2_CHECKED : ($level == 3 ? EventStates::COMPLETE : EventStates::TRANSLATED),
                            "admins_l2" => $level == 2 ? json_encode([$mid]) : $event[0]->admins_l2,
                            "admins_l3" => $level == 3 ? json_encode([$mid]) : $event[0]->admins_l3
                        ], [
                            "eventID" => $eventID
                        ]);

                        $response["success"] = true;
                        $response["message"] = __("import_successful_message");
                    }
                    else
                    {
                        $response["error"] = __("event_has_translations_error");
                    }
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


    private function importTnToEvent($notes, $projectID, $eventID, $bookCode)
    {
        $response = ["success" => false];

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

        $project = $this->_eventsModel->getProject(["*"],["projectID", $projectID]);
        $event = $this->_eventsModel->getEvent($eventID);

        // Create event if it doesn't exist
        if(empty($event))
        {
            $newEventID = $this->_eventsModel->createEvent([
                "projectID" => $project[0]->projectID,
                "bookCode" => $bookCode,
                "state" => EventStates::TRANSLATED,
                "admins" => json_encode([$mid]),
            ]);

            $event = $this->_eventsModel->getEvent($newEventID);
            $eventID = $newEventID;
        }

        if(!empty($event))
        {
            if($event[0]->state == EventStates::TRANSLATED)
            {
                // Check if there are translations of this event in database
                $trans = $this->_translationModel->getEventTranslationByEventID($eventID);
                if(empty($trans))
                {
                    // Create new translator
                    $peerCheckData = [];
                    $otherCheckData = [];

                    $trData = array(
                        "memberID" => $mid,
                        "eventID" => $eventID,
                        "step" => EventSteps::NONE,
                        "currentChapter" => 0
                    );
                    $trID = $this->_eventsModel->addTranslator($trData);

                    foreach ($notes as $chapter => $chunks)
                    {
                        $peerCheckData[$chapter] = ["memberID" => $mid, "done" => 1];
                        $otherCheckData[$chapter] = ["memberID" => $mid, "done" => 6];

                        $chunkKey = 0;
                        foreach ($chunks as $firstvs => $chunk) {
                            $translationVerses = [
                                EventMembers::TRANSLATOR => [
                                    "verses" => $chunk[0]
                                ],
                                EventMembers::CHECKER => [
                                    "verses" => $chunk[0]
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
                                "chapter" => $chapter,
                                "chunk" => $chunkKey,
                                "firstvs" => $firstvs,
                                "translatedVerses" => json_encode($translationVerses),
                                "translateDone" => true
                            ]);

                            $chunkKey++;
                        }

                        if($chapter > 0)
                        {
                            // Get related Scripture to define total verses of the chapter
                            $relatedScripture = $this->_apiModel->getBookText([
                                "sourceBible" => $project[0]->sourceBible,
                                "bookCode" => $event[0]->bookCode,
                                "sourceLangID" => $project[0]->sourceLangID,
                                "abbrID" => $event[0]->abbrID
                            ], $chapter);

                            if(empty($relatedScripture))
                                $relatedScripture = $this->_apiModel->getBookText([
                                    "sourceBible" => "ulb",
                                    "bookCode" => $event[0]->bookCode,
                                    "sourceLangID" => "en",
                                    "abbrID" => $event[0]->abbrID
                                ], $chapter);

                            if(empty($relatedScripture))
                                $response["warning"] = true;

                            $notes = [
                                "notes" => $chunks,
                                "totalVerses" => isset($relatedScripture) ? $relatedScripture["totalVerses"] : 0];
                            $tn_chunks = $this->_apiModel->getNotesChunks($notes);
                        }
                        else
                        {
                            $tn_chunks = [[0]];
                        }

                        // Assign chapters to new translator
                        $this->_eventsModel->assignChapter([
                            "eventID" => $eventID,
                            "memberID" => $mid,
                            "trID" => $trID,
                            "chapter" => $chapter,
                            "chunks" => json_encode($tn_chunks),
                            "done" => true,
                            "checked" => true,
                        ]);

                        $this->_eventsModel->updateEvent([
                            "state" => EventStates::TRANSLATED,
                            "admins" => json_encode([$mid])
                        ], [
                            "eventID" => $eventID
                        ]);
                    }

                    $this->_eventsModel->updateTranslator([
                        "peerCheck" => json_encode($peerCheckData),
                        "otherCheck" => json_encode($otherCheckData)
                    ], ["trID" => $trID]);

                    $response["success"] = true;
                    $response["message"] = __("import_successful_message");
                }
                else
                {
                    $contentChunks = array_reduce($notes, function ($arr, $elm) {
                        return array_merge((array)$arr, array_keys($elm));
                    });

                    if(sizeof($contentChunks) == sizeof($trans))
                    {
                        foreach ($trans as $tran) {
                            $verses = (array)json_decode($tran->translatedVerses, true);

                            if(isset($notes[$tran->chapter]) &&
                                isset($notes[$tran->chapter][$tran->firstvs]) &&
                                trim($notes[$tran->chapter][$tran->firstvs][0]) != "")
                            {
                                $verses[EventMembers::CHECKER]["verses"] = $notes[$tran->chapter][$tran->firstvs][0];
                            }

                            $this->_translationModel->updateTranslation([
                                "translatedVerses" => json_encode($verses),
                            ], ["tID" => $tran->tID]);
                        }

                        $response["success"] = true;
                        $response["message"] = __("import_successful_message");
                    }
                    else
                    {
                        $response["message"] = __("content_chunks_not_equal_error");
                    }
                }
            }
            else
            {
                $response["error"] = __("event_has_translations_error");
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

        $result = $this->_apiModel->insertLangsFromTD();

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

        if(trim($title) != "" && trim($text) != "" && preg_match("/^common|vmast|vsail|level2|notes|questions|words$/", $category))
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
    private function migrateChapters()
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
