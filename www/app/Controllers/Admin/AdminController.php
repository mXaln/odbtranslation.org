<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\ApiModel;
use App\Models\EventsModel;
use App\Models\MembersModel;
use App\Models\NewsModel;
use App\Models\SailDictionaryModel;
use App\Models\TranslationsModel;
use App\Repositories\Resources\IResourcesRepository;
use Config\Config;
use Database\QueryException;
use File;
use Helpers\Constants\ChunkSections;
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
use \stdClass;

class AdminController extends Controller {

    private $_membersModel;
    private $_eventsModel;
    private $_apiModel;
    private $_translationModel;
    private $_saildictModel;
    private $_newsModel;
    protected $layout = "admin";

    protected $resourcesRepo = null;

    public function __construct(IResourcesRepository $resourcesRepo)
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

        $this->resourcesRepo = $resourcesRepo;
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
        $data["sources"] = $this->_translationModel->getSources();

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
        $data["events"] = [];
        if(!empty($data["project"]))
        {
            if (in_array($data["project"][0]->sourceBible, ["odb","fnd","bib","theo"]))
            {
                $category = $data["project"][0]->sourceBible;
            }
            else
            {
                $category = "bible";
            }

            $otDone = 0;
            $ntDone = 0;
            $odbDone = 0;
            $millDone = 0;

            $data["events"] = $this->_eventsModel->getEventsByProject($projectID, $category);

            foreach ($data["events"] as $event)
            {
                if($event->category == "bible" && $event->abbrID < 41) // Old testament
                {
                    if(!empty($event->state) &&
                        EventStates::enum($event->state) >= EventStates::enum(EventStates::TRANSLATED))
                    {
                        $otDone++;
                    }
                }
                else if($event->category == "bible" && $event->abbrID >= 41) // New testament
                {
                    if(!empty($event->state) &&
                        EventStates::enum($event->state) >= EventStates::enum(EventStates::TRANSLATED))
                    {
                        $ntDone++;
                    }
                }
                else if(in_array($event->category, ["odb","fnd","bib","theo"])) // Non-scripture books
                {
                    if(!empty($event->state) &&
                        EventStates::enum($event->state) >= EventStates::enum(EventStates::TRANSLATED))
                    {
                        $odbDone++;
                        $millDone++;
                    }
                }
            }

            $data["OTprogress"] = 100*$otDone/39;
            $data["NTprogress"] = 100*$ntDone/27;
            $data["ODBprogress"] = 0;
            $data["MILLprogress"] = 0;

            if($data["project"][0]->sourceBible == "odb")
            {
                $count = $this->_eventsModel->getAbbrByCategory("odb", true);
                if($count > 0)
                    $data["ODBprogress"] = 100*$odbDone/$count;
            } elseif (in_array($data["project"][0]->sourceBible, ["fnd","bib","theo"])) {
                $count = $this->_eventsModel->getAbbrByCategory($data["project"][0]->sourceBible, true);
                if($count > 0)
                    $data["MILLprogress"] = 100*$millDone/$count;
            }
        }

        $page = 'Admin/Main/Project';
        if(!empty($data["project"]))
        {
            if ($data["project"][0]->sourceBible == "odb")
            {
                $page = 'Admin/Main/ProjectODB';
            } elseif (in_array($data["project"][0]->sourceBible, ["fnd","bib","theo"])) {
                $page = 'Admin/Main/ProjectMill';
            }
        }

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
        $type = isset($_POST['type']) && $_POST['type'] != "" ? $_POST['type'] : "wacs";
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
                case "wacs":
                    $path = $this->_apiModel->processRemoteUrl($import);

                    switch ($importProject)
                    {
                        case "ulb":
                            $usfm = $this->_apiModel->compileUSFMProject($path);

                            if($usfm != null)
                            {
                                $response = $this->importScriptureToEvent($usfm, $projectID, $eventID, $bookCode, $importLevel);
                            }
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
                        $path = $this->_apiModel->processZipFile($import);
                        if(in_array($bookProject, ["ulb"]))
                        {
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
                        // $path = $this->_apiModel->processZipFile($import);

                        switch ($importProject)
                        {
                            case "ulb":
                                $response["error"] = __("not_implemented");
                                break;
                            default:
                                $response["error"] = __("not_implemented");
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
        curl_setopt($ch, CURLOPT_URL, "https://content.bibletranslationtools.org/api/v1/repos/search?limit=50&q=" . $q);
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

        // All members with their translation events
        $data["all_members"] = [];
        $list = $this->_eventsModel->getBooksOfTranslators();
        foreach($list as $item)
        {
            if(!isset($data["all_members"][$item->userName]))
            {
                $tmp = [];
                $tmp["firstName"] = $item->firstName;
                $tmp["lastName"] = $item->lastName;
                $data["all_members"][$item->userName] = $tmp;
            }

            if(!isset($data["all_members"][$item->userName]["books"]))
            {
                $tmp = [];
                $tmp["name"] = $item->name;
                $tmp["project"] = "(".$item->bookProject.") ".__($item->bookProject);
                $tmp["lang"] = "[".$item->targetLang."] ".$item->angName
                    .($item->angName != $item->langName ? " (".$item->langName.")" : "");
                $tmp["chapters"] = [];
                $data["all_members"][$item->userName]["books"][$item->code] = $tmp;
            }

            if(!isset($data["all_members"][$item->userName]["books"][$item->code]))
            {
                $tmp = [];
                $tmp["name"] = $item->name;
                $tmp["project"] = "(".$item->bookProject.") ".__($item->bookProject);
                $tmp["lang"] = "[".$item->targetLang."] ".$item->angName
                    .($item->angName != $item->langName ? " (".$item->langName.")" : "");
                $tmp["chapters"] = [];
                $data["all_members"][$item->userName]["books"][$item->code] = $tmp;
            }

            if(!isset($data["all_members"][$item->userName]["books"][$item->code]["chapters"]))
                $data["all_members"][$item->userName]["books"][$item->code]["chapters"] = [];

            $data["all_members"][$item->userName]["books"][$item->code]["chapters"][$item->chapter]["done"] = $item->done;
        }

        return View::make('Admin/Members/Index')
            ->shares("title", __("admin_members_title"))
            ->shares("data", $data);
    }

    public function toolsCommon()
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

        $data["faqs"] = $this->_newsModel->getFaqs();

        return View::make('Admin/Main/ToolsCommon')
            ->shares("title", __("admin_tools_title"))
            ->shares("data", $data);
    }

    public function toolsVsun()
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

        return View::make('Admin/Main/ToolsVsun')
            ->shares("title", __("admin_tools_title"))
            ->shares("data", $data);
    }

    public function toolsFaq()
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

        $data["faqs"] = $this->_newsModel->getFaqs();

        return View::make('Admin/Main/ToolsFaq')
            ->shares("title", __("admin_tools_title"))
            ->shares("data", $data);
    }

    public function toolsNews()
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

        return View::make('Admin/Main/ToolsNews')
            ->shares("title", __("admin_tools_title"))
            ->shares("data", $data);
    }

    public function toolsSource()
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

        $arr = $this->_eventsModel->getSuperadminLanguages(Session::get("memberID"));
        $adminLangs = [];
        foreach ($arr as $item)
        {
            $adminLangs[] = $item->gwLang;
        }

        $data["gwLangs"] = $this->_eventsModel->getAllLanguages(true, $adminLangs);
        $data["sources"] = $this->_translationModel->getSources();
        $data["sourceTypes"] = $this->_translationModel->getKnownSourceTypes();

        // Filter resources of the languages that this user donsn't have access to
        $tmp = [];
        foreach ($data["sources"] as $source) {
            if(in_array($source->langID, $adminLangs)) {
                $tmp[] = $source;
            }
        }
        $data["sources"] = $tmp;

        return View::make('Admin/Main/ToolsSource')
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

    public function getProject()
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

        $projectID = isset($_POST['projectID']) && $_POST['projectID'] != "" ? (integer)$_POST['projectID'] : null;

        if($projectID == null)
        {
            $response["error"] = __('wrong_parameters_error');
        }

        if(!isset($response["error"]))
        {
            $project = $this->_eventsModel->getProject(["projects.*"], [["projectID", $projectID]]);

            if(!empty($project))
            {
                $response["success"] = true;
                $response["project"] = $project[0];
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
            $contributors = $this->_eventsModel->getEventContributors($eventID, $level, $mode);

            if(!empty($contributors))
            {
                $response["success"] = true;
                $response = array_merge($response, $contributors);
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
            $response["contributors"] = $this->_eventsModel->getProjectContributors($projectID);;
            $response["success"] = true;
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

            $id = $this->_eventsModel->createGatewayProject([
                "gwLang" => $gwLang,
                "admins" => json_encode([Session::get("memberID")])
            ]);
            $msg = json_encode(array("success" => __("successfully_created")));

            if($id)
                echo $msg;
            else
            {
                $error[] = __("error_occurred");
                echo json_encode(array("error" => Error::display($error)));
            }
        }
        else
        {
            echo json_encode(array("error" => Error::display($error)));
        }
    }

    public function getSuperAdmins()
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

        $gwProjectID = isset($_POST['gwProjectID']) && $_POST['gwProjectID'] != "" ? (integer)$_POST['gwProjectID'] : 0;

        $gwProject = $this->_eventsModel->getGatewayProject(["admins"], ["gwProjectID", "=", $gwProjectID]);

        if(!empty($gwProject))
        {
            $admins = (array) json_decode($gwProject[0]->admins, true);

            if(!in_array(Session::get("memberID"), $admins))
            {
                echo json_encode(array("error" => __("not_enough_rights_error")));
                return;
            }

            $members = [];
            $membersArray = (array)$this->_membersModel->getMembers(json_decode($gwProject[0]->admins));

            foreach ($membersArray as $member) {
                $members[$member->memberID] = "{$member->firstName} "
                    .mb_substr($member->lastName, 0, 1)
                    .". ({$member->userName})";
            }

            $response["success"] = true;
            $response["admins"] = $members;
        }
        else
        {
            $response["error"] = __("gw_project_not_exist");
        }

        echo json_encode($response);
    }


    public function editSuperAdmins()
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

        $gwProjectID = isset($_POST['gwProjectID']) && $_POST['gwProjectID'] != "" ? (integer)$_POST['gwProjectID'] : 0;
        $superadmins = isset($_POST['superadmins']) && !empty($_POST['superadmins']) ? array_unique($_POST['superadmins']) : [];

        $gwProject = $this->_eventsModel->getGatewayProject(["admins"], [
            ["gwProjectID", $gwProjectID]
        ]);

        $admins = (array) json_decode($gwProject[0]->admins, true);

        if(!in_array(Session::get("memberID"), $admins))
        {
            echo json_encode(array("error" => __("not_enough_rights_error")));
            return;
        }

        $superadmins = array_filter($superadmins, function($elm) {
            return is_numeric($elm);
        });
        $superadmins = array_values($superadmins);

        foreach ($superadmins as $admin) {
            $this->_membersModel->updateMember(array("isAdmin" => true, "isSuperAdmin" => true), array("memberID" => $admin));
        }

        $this->_eventsModel->updateGatewayProject(["admins" => json_encode($superadmins)], ["gwProjectID" => $gwProjectID]);

        $response["success"] = true;

        echo json_encode($response);
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

        $projectMode = isset($_POST['projectMode']) && preg_match("/(bible|odb|fnd|bib|theo)/", $_POST['projectMode']) ? $_POST['projectMode'] : "bible";
        $subGwLangs = isset($_POST['subGwLangs']) && $_POST['subGwLangs'] != "" ? $_POST['subGwLangs'] : null;
        $targetLang = isset($_POST['targetLangs']) && $_POST['targetLangs'] != "" ? $_POST['targetLangs'] : null;
        $sourceTranslation = isset($_POST['sourceTranslation']) && $_POST['sourceTranslation'] != "" ? $_POST['sourceTranslation'] : null;
        $sourceTools = isset($_POST['sourceTools']) && $_POST['sourceTools'] != "" ? $_POST['sourceTools'] : null;
        $toolsTn = isset($_POST['toolsTn']) && $_POST['toolsTn'] != "" ? $_POST['toolsTn'] : null;
        $toolsTq = isset($_POST['toolsTq']) && $_POST['toolsTq'] != "" ? $_POST['toolsTq'] : null;
        $toolsTw = isset($_POST['toolsTw']) && $_POST['toolsTw'] != "" ? $_POST['toolsTw'] : null;
        $projectType = isset($_POST['projectType']) && $_POST['projectType'] != "" ? $_POST['projectType'] : null;
        $act = isset($_POST['act']) && $_POST['act'] != "" ? $_POST['act'] : "create";
        $projectID = isset($_POST['projectID']) && $_POST['projectID'] != "" ? $_POST['projectID'] : null;

        if($act == "create")
        {
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
                if(!in_array($projectMode, ["odb"]))
                    $error[] = __("choose_source_trans");
            }

            if($projectType == null)
            {
                $error[] = __("choose_project_type");
            }

            if($projectMode == "odb")
            {
                $sourceTranslation = $projectMode . "|en";
            }

            if(!isset($error))
            {
                $sourceTrPair = explode("|", $sourceTranslation);
                $gwLangsPair = explode("|", $subGwLangs);

                $gwProject = $this->_eventsModel->getGatewayProject(["admins"], [
                    ["gwProjectID", $gwLangsPair[1]]
                ]);
                $admins = (array) json_decode($gwProject[0]->admins, true);

                if(!in_array(Session::get("memberID"), $admins))
                {
                    $error[] = __("not_enough_rights_error");
                    echo json_encode(array("error" => Error::display($error)));
                    return;
                }

                $projType = $projectType == "mill" ? $projectMode : $projectType;

                $search = [
                    ["projects.gwLang", $gwLangsPair[0]],
                    ["projects.targetLang", $targetLang],
                    ["projects.bookProject", $projType]
                ];

                if(in_array($projectMode, ["odb","fnd","bib","theo"]))
                {
                    $search[] = ["projects.sourceBible", $projectMode];
                }

                $exist = $this->_eventsModel->getProject(["projects.projectID"], $search);

                if(!empty($exist))
                {
                    $error[] = __("project_exists");
                    echo json_encode(array("error" => Error::display($error)));
                    return;
                }

                if (in_array($projectMode, ["fnd","bib","theo"])) {
                    $sourceTrPair[0] = $projectMode;
                }

                $postdata = array(
                    "gwProjectID" => $gwLangsPair[1],
                    "gwLang" => $gwLangsPair[0],
                    "targetLang" => $targetLang,
                    "bookProject" => $projType,
                    "sourceBible" => $sourceTrPair[0],
                    "sourceLangID" => $sourceTrPair[1],
                    "resLangID" => $sourceTools
                );

                if($toolsTn)
                    $postdata["tnLangID"] = $toolsTn;
                if($toolsTq)
                    $postdata["tqLangID"] = $toolsTq;
                if($toolsTw)
                    $postdata["twLangID"] = $toolsTw;

                $id = $this->_eventsModel->createProject($postdata);

                if($id)
                    echo json_encode(array("success" => __("successfully_created")));
                else
                {
                    $error[] = __("error_occurred");
                    echo json_encode(array("error" => Error::display($error)));
                }
            }
            else
            {
                echo json_encode(array("error" => Error::display($error)));
            }
        }
        elseif($act == "edit")
        {
            $project = $this->_eventsModel->getProject(["*"], [
                ["projectID", $projectID]
            ]);

            if(empty($project))
            {
                $error[] = __("error_occurred");
                echo json_encode(array("error" => Error::display($error)));
                exit;
            }

            $projectMode = $project[0]->bookProject;

            if($sourceTranslation == null)
            {
                if(!in_array($projectMode, ["odb"]))
                    $error[] = __("choose_source_trans");
            }

            if($projectMode == "odb")
            {
                $sourceTranslation = $projectMode. "|en";
            }

            if(!isset($error))
            {
                $gwProject = $this->_eventsModel->getGatewayProject(["admins"], [
                    ["gwProjectID", $project[0]->gwProjectID]
                ]);
                $admins = (array) json_decode($gwProject[0]->admins, true);

                if(!in_array(Session::get("memberID"), $admins))
                {
                    $error[] = __("not_enough_rights_error");
                    echo json_encode(array("error" => Error::display($error)));
                    return;
                }

                $sourceTrPair = explode("|", $sourceTranslation);

                if(in_array($projectMode, ["fnd","bib","theo"]))
                {
                    $sourceTrPair[0] = $project[0]->bookProject;
                }

                $postdata = array(
                    "sourceBible" => $sourceTrPair[0],
                    "sourceLangID" => $sourceTrPair[1],
                    "resLangID" => $sourceTools
                );

                if($toolsTn)
                    $postdata["tnLangID"] = $toolsTn;
                if($toolsTq)
                    $postdata["tqLangID"] = $toolsTq;
                if($toolsTw)
                    $postdata["twLangID"] = $toolsTw;

                $this->_eventsModel->updateProject($postdata, ["projectID" => $projectID]);

                echo json_encode(array("success" => __("successfully_updated")));
            }
            else
            {
                echo json_encode(array("error" => Error::display($error)));
            }
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

        $abbrID = $_POST["abbrID"] ?? null;
        $bookCode = $_POST["bookCode"] ?? null;
        $sourceLangID = $_POST["sourceLangID"] ?? null;
        $sourceBible = $_POST["sourceBible"] ?? null;

        // Book source
        $cache_keyword = $sourceLangID."_".$sourceBible."_".$bookCode;

        if(Cache::has($cache_keyword))
            Cache::forget($cache_keyword);

        $source = $this->resourcesRepo->getScripture(
            $sourceLangID,
            $sourceBible,
            $bookCode,
            $abbrID
        );

        if(!empty($source))
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
            return;

        $_POST = Gump::xss_clean($_POST);

        $bookCode = isset($_POST['book_code']) && $_POST['book_code'] != "" ? $_POST['book_code'] : null;
        $projectID = isset($_POST['projectID']) && $_POST['projectID'] != "" ? (integer)$_POST['projectID'] : null;
        $eventLevel = isset($_POST['eventLevel']) && $_POST['eventLevel'] != "" ? (integer)$_POST['eventLevel'] : 1;
        $langInput = isset($_POST['langInput']) && $_POST['langInput'] != "" ? true : false;
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

                    if($project[0]->bookProject != "ulb" || $eventLevel != 1)
                    {
                        if($langInput)
                        {
                            $error[] = __('lang_input_not_allowed');
                            echo json_encode(array("error" => Error::display($error)));
                            return;
                        }
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
                            if (in_array($project[0]->bookProject, ["ulb"]))
                            {
                                if(empty($exist) || $exist[0]->state != EventStates::TRANSLATED)
                                {
                                    $error[] = __("l2_l3_create_event_error");
                                    echo json_encode(array("error" => Error::display($error)));
                                    return;
                                }
                            }
                            break;
                        case 3:
                            if (in_array($project[0]->bookProject, ["ulb"]))
                            {
                                if(empty($exist) || $exist[0]->state != EventStates::L2_CHECKED)
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
                        if($bookInfo[0]->category == "odb")
                        {
                            $odb = $this->resourcesRepo->getJsonResource($project[0]->sourceLangID, "odb", $bookInfo[0]->code);
                            if(empty($odb))
                            {
                                $error[] = __("no_source_error");
                                echo json_encode(array("error" => Error::display($error)));
                                return;
                            }
                        }
                        elseif(in_array($bookInfo[0]->category, ["fnd","bib","theo"]))
                        {
                            $mill = $this->resourcesRepo->getMillResource(
                                $project[0]->sourceLangID,
                                $bookInfo[0]->category,
                                $bookInfo[0]->code);

                            if(empty($mill))
                            {
                                $error[] = __("no_source_error");
                                echo json_encode(array("error" => Error::display($error)));
                                return;
                            }
                        }
                        else
                        {
                            // Book source
                            $cache_keyword = $project[0]->sourceLangID."_".$project[0]->sourceBible."_".$bookCode;

                            if(!Cache::has($cache_keyword)) {
                                $usfm = $this->resourcesRepo->getScripture(
                                    $project[0]->sourceLangID,
                                    $project[0]->sourceBible,
                                    $bookInfo[0]->code,
                                    $bookInfo[0]->abbrID
                                );

                                if(empty($usfm)) {
                                    $error[] = __("no_source_error");
                                    echo json_encode(array("error" => Error::display($error)));
                                    return;
                                }
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
                                $postdata["dateFrom"] = date("Y-m-d H:i:s", time());
                                $postdata["dateTo"] = date("Y-m-d H:i:s", time());
                                $postdata["langInput"] = $langInput;
                                $eventID = $this->_eventsModel->createEvent($postdata);
                            }
                            else
                            {
                                $superadmins = (array)json_decode($exist[0]->superadmins, true);
                                if(!in_array(Session::get("memberID"), $superadmins))
                                {
                                    $error[] = __("wrong_project_id");
                                    echo json_encode(array("error" => Error::display($error)));
                                    return;
                                }

                                // Create(change state) L2 event
                                if($exist[0]->state == EventStates::TRANSLATED)
                                {
                                    $postdata["admins_l2"] = json_encode($admins);
                                    $postdata["state"] = EventStates::L2_RECRUIT;
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

                    $this->_eventsModel->deleteEvent(["eventID" => $exist[0]->eventID]);
                    echo json_encode(array("success" => __("successfully_deleted")));

                    break;
            }
        }
        else
        {
            echo json_encode(array("error" => Error::display($error)));
        }
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

        $event = $this->_eventsModel->getEvent($eventID);

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
                                    ChunkSections::BLIND_DRAFT => "",
                                    ChunkSections::VERSES => $chunk
                                ],
                                EventMembers::L2_CHECKER => [
                                    ChunkSections::VERSES => $level == 2 ? $chunk : array()
                                ],
                                EventMembers::L3_CHECKER => [
                                    ChunkSections::VERSES => array()
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

                            foreach ($verses[EventMembers::TRANSLATOR][ChunkSections::VERSES] as $verse => $text) {
                                if(isset($usfmData["chapters"][$tran->chapter]) &&
                                    isset($usfmData["chapters"][$tran->chapter][$verse]) &&
                                    trim($usfmData["chapters"][$tran->chapter][$verse]) != "")
                                {
                                    switch ($level)
                                    {
                                        case 1:
                                            $verses[EventMembers::TRANSLATOR][ChunkSections::VERSES][$verse] = $usfmData["chapters"][$tran->chapter][$verse];
                                            break;
                                        case 2:
                                            $verses[EventMembers::L2_CHECKER][ChunkSections::VERSES][$verse] = $usfmData["chapters"][$tran->chapter][$verse];
                                            break;
                                        case 3:
                                            $verses[EventMembers::L3_CHECKER][ChunkSections::VERSES][$verse] = $usfmData["chapters"][$tran->chapter][$verse];
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

        $reDownload = Input::get("download", false);

        $result = $this->_apiModel->insertLangsFromTD($reDownload);

        echo json_encode($result);
    }

    public function updateCatalog()
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

        $reDownload = Input::get("download", false);

        $this->_apiModel->insertSourcesFromCatalog($reDownload);

        $result["success"] = true;
        echo json_encode($result);
    }

    public function clearAllCache()
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

        $this->_apiModel->clearAllCache();
        $result["success"] = true;

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


    public function uploadSunFont()
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

        $font_file = Input::file("file");

        if($font_file != null && $font_file->isValid())
        {
            $ext = $font_file->getClientOriginalExtension();
            if($ext == "woff")
            {
                $name = $font_file->getClientOriginalName();
                $destinationPath = "../app/Templates/Default/Assets/fonts/";

                if(preg_match("/backsun/i", $name))
                {
                    $fileName = "BackSUN.woff";
                    $font_file->move($destinationPath, $fileName);

                    if(File::exists(join("/", [$destinationPath, $fileName])))
                    {
                        $result["success"] = true;
                        $result["message"] = __("font_uploaded", $fileName);
                        echo json_encode($result);
                        exit;
                    }
                }
                elseif(preg_match("/sun/i", $name))
                {
                    $fileName = "SUN.woff";
                    $font_file->move($destinationPath, $fileName);

                    if(File::exists(join("/", [$destinationPath, $fileName])))
                    {
                        $result["success"] = true;
                        $result["message"] = __("font_uploaded", $fileName);
                        echo json_encode($result);
                        exit;
                    }
                }
                else
                {
                    $result["error"] = __("font_name_error");
                    echo json_encode($result);
                    exit;
                }
            }
            else
            {
                $result["error"] = __("font_format_error");
                echo json_encode($result);
                exit;
            }
        }
        else
        {
            $result["error"] = __("error_occurred", "Empty input!");
            echo json_encode($result);
            exit;
        }
    }

    public function uploadSunDict() {
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

        $dict_file = Input::file("file");

        if($dict_file != null && $dict_file->isValid())
        {
            $ext = $dict_file->getClientOriginalExtension();
            if($ext == "csv")
            {
                $new_dict = [];

                $file = $dict_file->openFile();
                while (!$file->eof()) {
                    $pair = $file->fgetcsv();
                    if(isset($pair[0]) && isset($pair[1])
                        && !empty($pair[0]) && !empty($pair[1]))
                    {
                        $wordObj = new stdClass();
                        $wordObj->symbol = $pair[0];
                        $wordObj->word = $pair[1];
                        $new_dict[] = $wordObj;
                    }
                }

                if(sizeof($new_dict) > 0)
                {
                    $this->_saildictModel->deleteAllWords();

                    foreach ($new_dict as $word)
                    {
                        $this->_saildictModel->createSunWord([
                            "symbol" => $word->symbol,
                            "word" => $word->word
                        ]);
                    }

                    $result["success"] = true;
                    $result["message"] = __("dictionary_updated");
                    echo json_encode($result);
                    exit;
                }
                else
                {
                    $result["error"] = __("empty_dictionary");
                    echo json_encode($result);
                    exit;
                }
            }
            else
            {
                $result["error"] = __("not_csv_format_error");
                echo json_encode($result);
                exit;
            }
        }
        else
        {
            $result["error"] = __("error_occurred", "Empty input!");
            echo json_encode($result);
            exit;
        }
    }

    public function uploadImage()
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

        $image_file = Input::file("image");

        if($image_file->isValid())
        {
            $mime = $image_file->getMimeType();
            if(in_array($mime, ["image/jpeg", "image/png", "image/gif", "application/pdf"]))
            {
                $fileExtension = $image_file->getClientOriginalExtension();
                $fileName = uniqid().".".$fileExtension;
                $destinationPath = "../app/Templates/Default/Assets/faq/";
                $image_file->move($destinationPath, $fileName);

                if(File::exists(join("/", [$destinationPath, $fileName])))
                {
                    $result["success"] = true;
                    $result["ext"] = $fileExtension;
                    $result["url"] = template_url("faq/".$fileName);
                    echo json_encode($result);
                    exit;
                }
            }
            else
            {
                $result["error"] = __("wrong_image_format_error");
                echo json_encode($result);
                exit;
            }
        }
        else
        {
            $result["error"] = __("error_occurred");
            echo json_encode($result);
            exit;
        }
    }

    public function uploadSource() {
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

        $src = Input::get("src", "");
        $sourceZip = Input::file("file");

        if(isset($sourceZip) && $sourceZip->isValid() && trim($src) != "")
        {
            $srcArr = explode("|", $src);
            if(sizeof($srcArr) == 2)
            {
                $lang = $srcArr[0];
                $slug = $srcArr[1];

                $arr = $this->_eventsModel->getSuperadminLanguages(Session::get("memberID"));
                $adminLangs = [];
                foreach ($arr as $item)
                {
                    $adminLangs[] = $item->gwLang;
                }

                if(in_array($lang, $adminLangs))
                {
                    $mime = $sourceZip->getMimeType();
                    if($mime == "application/zip")
                    {
                        $path = $this->_apiModel->processSourceZipFile($sourceZip);
                        $this->resourcesRepo->forgetResource($lang, $slug);

                        if(in_array($slug, ["tn","tq","tw"]))
                        {
                            $result["success"] = $this->_apiModel->processMdSource($path, $lang, $slug);
                        }
                        elseif (in_array($slug, ["odb"]))
                        {
                            $result["success"] = $this->_apiModel->processJsonSource($path, $lang, $slug);
                        }
                        elseif (in_array($slug, ["fnd","bib","theo"]))
                        {
                            $result["success"] = $this->_apiModel->processMillSource($path, $lang, $slug);
                        }
                        else
                        {
                            $result["success"] = $this->_apiModel->processUsfmSource($path, $lang, $slug);
                        }

                        $result["message"] = "Uploaded!";
                    }
                    else
                    {
                        $result["error"] = __("error_zip_file_required");
                    }
                }
                else
                {
                    $result["error"] = __("not_enough_lang_rights_error");
                }
            }
            else
            {
                $result["error"] = __("wrong_parameters");
            }
        }
        else
        {
            $result["error"] = __("wrong_parameters");
        }

        echo json_encode($result);
        exit;
    }

    public function createFaq()
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

        $question = Input::get("question", "");
        $category = Input::get("category", "common");
        $answer = Input::get("answer", "");

        if(trim($question) != "" && trim($answer) != ""
            && preg_match("/^common|mast|vsail|level2|level3|notes|questions|words|lang_input$/", $category))
        {
            $data = [
                "title" => $question,
                "text" => $answer,
                "category" => $category
            ];

            $id = $this->_newsModel->createFaqs($data);

            if($id)
            {
                $li = '<li class="faq_content" id="'.$id.'">
                            <div class="tools_delete_faq">
                                <span>'.__("delete").'</span>
                                <img src="'.template_url("img/loader.gif").'">
                            </div>

                            <div class="faq_question">'.$question.'</div>
                            <div class="faq_answer">'.$answer.'</div>
                            <div class="faq_cat">'.__($category).'</div>
                        </li>';

                $result["success"] = true;
                $result["li"] = $li;
            }
        }

        echo json_encode($result);
    }


    public function deleteFaq()
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

        $questionID = Input::get("id", 0);

        if($questionID)
        {
            if($this->_newsModel->deleteFaqs(["id" => $questionID]))
            {
                $result["success"] = true;
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

        if(trim($title) != "" && trim($text) != ""
            && preg_match("/^common|mast|vsail|level2|level3|notes|questions|words|lang_input$/", $category))
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
                $result["error"] = __("error_occurred");
            }
        }
        else
        {
            $result["error"] = __("wrong_parameters");
        }

        echo json_encode($result);
    }


    public function getEventProgress($eventID) {
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

        $result["success"] = true;
        $result["progress"] = $this->_eventsModel->calculateEventProgress($eventID);

        echo json_encode($result);

    }

    public function createCustomSource() {
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

        $lang = Input::get("lang", "");
        $type = Input::get("type", "");

        if(trim($lang) != "" && trim($type))
        {
            $arr = $this->_eventsModel->getSuperadminLanguages(Session::get("memberID"));
            $adminLangs = [];
            foreach ($arr as $item)
            {
                $adminLangs[] = $item->gwLang;
            }

            if(in_array($lang, $adminLangs))
            {
                $typeArr = explode("|", $type);
                if(sizeof($typeArr) == 2) {
                    $slug = $typeArr[0];
                    $name = $typeArr[1];

                    if(preg_match("/[a-z-]+/", $slug))
                    {
                        try {
                            $insert = $this->_apiModel->insertSource($lang, $slug, $name);
                            if($insert)
                            {
                                $result["success"] = true;
                                $result["message"] = __("successfully_created");
                            }
                            else
                            {
                                $result["error"] = __("error_occurred", ["insert failed"]);
                            }
                        } catch(QueryException $e) {
                            $result["success"] = true;
                        }
                    }
                    else
                    {
                        $result["error"] = __("Only english letters and hyphens are allowed for the source slug");
                    }
                }
                else
                {
                    $result["error"] = __("wrong_parameters");
                }
            }
            else
            {
                $result["error"] = __("not_enough_lang_rights_error");
            }
        }
        else
        {
            $result["error"] = __("wrong_parameters");
        }

        echo json_encode($result);
    }
}
