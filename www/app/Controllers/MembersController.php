<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\CloudModel;
use App\Models\NewsModel;
use Helpers\Constants\EventStates;
use View;
use Helpers\Constants\EventMembers;
use Helpers\Csrf;
use Helpers\Gump;
use Mailer;
use Config\Config;
use Helpers\Password;
use Helpers\ReCaptcha;
use Helpers\Session;
use Helpers\Url;
use App\Models\EventsModel;
use App\Models\MembersModel;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer\Hmac\Sha256;

class MembersController extends Controller
{
    private $_model;
    private $_eventModel;
    private $_newsModel;
    private $_notifications;
    private $_news;
    private $_newNewsCount;

    public function __construct()
    {
        parent::__construct();

        if(Config::get("app.isMaintenance")
            && !in_array($_SERVER['REMOTE_ADDR'], Config::get("app.ips")))
        {
            Url::redirect("maintenance");
        }

        $this->_model = new MembersModel();

        $this->_eventModel = new EventsModel();
        $this->_newsModel = new NewsModel();

        if(Session::get("loggedin"))
        {
            $this->_notifications = $this->_eventModel->getNotifications();
            $this->_news = $this->_newsModel->getNews();
            $this->_newNewsCount = 0;
            foreach ($this->_news as $news) {
                if (!isset($_COOKIE["newsid" . $news->id]))
                    $this->_newNewsCount++;
            }
        }
    }

    /**
     * Show member's dashboard view
     * @return mixed
     */
    public function index()
    {
        $data["menu"] = 1;

        if (!Session::get('loggedin'))
        {
            Url::redirect("members/login");
        }

        if(Session::get("isDemo"))
        {
            Url::redirect('events/demo');
        }

        if(empty(Session::get("profile")))
        {
            Url::redirect("members/profile");
        }

        Url::redirect("events");
    }

    /**
     * Show profile view with form
     * @return mixed
     */
    public function profile()
    {
        if (!Session::get('loggedin'))
        {
            Url::redirect('members/login');
        }

        if(Session::get("isDemo"))
        {
            Url::redirect('events/demo');
        }

        $data["languages"] = $this->_eventModel->getAllLanguages();
        $data["menu"] = 1;
        $data["errors"] = array();

        $profile = Session::get("profile");
        $data["profile"] = $profile;

        if(!empty($_POST))
        {
            $_POST = Gump::xss_clean($_POST);

            $userName = isset($_POST["userName"]) ? $_POST['userName'] : "";
            $firstName = isset($_POST["firstName"]) ? $_POST['firstName'] : "";
            $lastName = isset($_POST["lastName"]) ? $_POST['lastName'] : "";

            $avatar = isset($_POST["avatar"]) && preg_match("/^([f|m][1-9]|[f|m]1[0-9]|[f|m]20)$/", $_POST["avatar"]) ? $_POST["avatar"] : "m1";
            $prefered_roles = isset($_POST["prefered_roles"]) && !empty($_POST["prefered_roles"]) ? (array)$_POST["prefered_roles"] : null;
            $langs = isset($_POST["langs"]) && !empty($_POST["langs"]) ? (array)$_POST["langs"] : null;
            $bbl_trans_yrs = isset($_POST["bbl_trans_yrs"]) && preg_match("/^[1-4]{1}$/", $_POST["bbl_trans_yrs"]) ? $_POST["bbl_trans_yrs"] : null;
            $othr_trans_yrs = isset($_POST["othr_trans_yrs"]) && preg_match("/^[1-4]{1}$/", $_POST["othr_trans_yrs"]) ? $_POST["othr_trans_yrs"] : null;
            $bbl_knwlg_degr = isset($_POST["bbl_knwlg_degr"]) && preg_match("/^[1-4]{1}$/", $_POST["bbl_knwlg_degr"]) ? $_POST["bbl_knwlg_degr"] : null;
            $mast_evnts = isset($_POST["mast_evnts"]) && preg_match("/^[1-4]{1}$/", $_POST["mast_evnts"]) ? $_POST["mast_evnts"] : null;
            $mast_role = isset($_POST["mast_role"]) && !empty($_POST["mast_role"]) ? (array)$_POST["mast_role"] : ($mast_evnts > 1 ? null : array());
            $teamwork = isset($_POST["teamwork"]) && preg_match("/^[1-4]{1}$/", $_POST["teamwork"]) ? $_POST["teamwork"] : null;

            $mast_facilitator = isset($_POST["mast_facilitator"]) && preg_match("/^[0-1]{1}$/", $_POST["mast_facilitator"]) ? $_POST["mast_facilitator"] : 0;
            $org = isset($_POST["org"]) && preg_match("/^(Other|WA EdServices)$/", $_POST["org"]) ? $_POST["org"] : ($mast_facilitator ? null : "");
            $ref_person = isset($_POST["ref_person"]) && trim($_POST["ref_person"]) != "" ? trim($_POST["ref_person"]) : ($mast_facilitator ? null : "");
            $ref_email = isset($_POST["ref_email"]) && trim($_POST["ref_email"]) != "" ? trim($_POST["ref_email"]) : ($mast_facilitator ? null : "");

            $church_role = isset($_POST["church_role"]) && !empty($_POST["church_role"]) ? (array)$_POST["church_role"] : array();
            $hebrew_knwlg = isset($_POST["hebrew_knwlg"]) && preg_match("/^[0-4]{1}$/", $_POST["hebrew_knwlg"]) ? $_POST["hebrew_knwlg"] : 0;
            $greek_knwlg = isset($_POST["greek_knwlg"]) && preg_match("/^[0-4]{1}$/", $_POST["greek_knwlg"]) ? $_POST["greek_knwlg"] : 0;
            $education = isset($_POST["education"]) && !empty($_POST["education"]) ? (array)$_POST["education"] : array();
            $ed_area = isset($_POST["ed_area"]) && !empty($_POST["ed_area"]) ? (array)$_POST["ed_area"] : array();
            $ed_place = isset($_POST["ed_place"]) && trim($_POST["ed_place"]) != "" ? trim($_POST["ed_place"]) : "";

            if(!preg_match("/^[a-z]+[a-z0-9]*$/i", $userName))
            {
                $data["errors"]['userName'] = true;
            }

            if (strlen($userName) < 5 || strlen($userName) > 20)
            {
                $data["errors"]['userName'] = true;
            }

            if (mb_strlen($firstName) < 2 || mb_strlen($firstName) > 20)
            {
                $data["errors"]['firstName'] = true;
            }

            if (mb_strlen($lastName) < 2 || mb_strlen($lastName) > 20)
            {
                $data["errors"]['lastName'] = true;
            }

            if($prefered_roles == null)
            {
                $data["errors"]["prefered_roles"] = true;
            }

            if($langs !== null)
            {
                $languages = array();
                $langArr = array();
                foreach ($langs as $lang) {
                    $arr = explode(":", $lang);
                    $arr[2] = 4; // To support old version, when geo years were saved

                    if(sizeof($arr) != 3) continue;

                    $langID = preg_match("/^[0-9A-Za-z-]{2,40}$/", $arr[0]) ? $arr[0] : null;

                    if($langID === null || (integer)$arr[1] < 0 || (integer)$arr[2] == 0) continue;
                    if((integer)$arr[1] > 5 || (integer)$arr[2] > 4) continue;

                    $languages[$langID] = array((integer)$arr[1], (integer)$arr[2]);

                    $langArr[$langID]["lang_fluency"] = (integer)$arr[1];
                    $langArr[$langID]["geo_lang_yrs"] = (integer)$arr[2];
                }

                if(sizeof($languages) <= 0)
                {
                    $data["errors"]["langs"] = true;
                }
            }
            else
            {
                $data["errors"]["langs"] = true;
            }

            if($bbl_trans_yrs === null)
                $data["errors"]["bbl_trans_yrs"] = true;

            if($othr_trans_yrs === null)
                $data["errors"]["othr_trans_yrs"] = true;

            if($bbl_knwlg_degr === null)
                $data["errors"]["bbl_knwlg_degr"] = true;

            if($mast_evnts === null)
                $data["errors"]["mast_evnts"] = true;

            if($mast_role === null)
                $data["errors"]["mast_role"] = true;
            else
            {
                foreach ($mast_role as $item) {
                    if(!preg_match("/^(translator|facilitator|l2_checker|l3_checker)$/", $item))
                    {
                        $data["errors"]["mast_role"] = true;
                        break;
                    }
                }
            }

            if($teamwork === null)
            {
                $data["errors"]["teamwork"] = true;
            }

            if(!empty($education))
            {
                foreach ($education as $item) {
                    if(!preg_match("/^(BA|MA|PHD)$/", $item))
                    {
                        $data["errors"]["education"] = true;
                        break;
                    }
                }
            }

            if($org === null)
                $data["errors"]["org"] = true;

            if($ref_person === null)
                $data["errors"]["ref_person"] = true;

            if($ref_email === null)
                $data["errors"]["ref_email"] = true;
            elseif($ref_email != "" && !filter_var($ref_email, FILTER_VALIDATE_EMAIL))
                $data["errors"]["ref_email"] = true;

            if($ed_area === null)
                $data["errors"]["ed_area"] = true;
            else
            {
                foreach ($ed_area as $item) {
                    if(!preg_match("/^(Theology|Pastoral Ministry|Bible Translation|Exegetics)$/", $item))
                    {
                        $data["errors"]["ed_area"] = true;
                        break;
                    }
                }
            }

            if($ed_place === null)
                $data["errors"]["ed_place"] = true;

            if($hebrew_knwlg === null)
                $data["errors"]["hebrew_knwlg"] = true;

            if($greek_knwlg === null)
                $data["errors"]["greek_knwlg"] = true;

            if($church_role === null)
                $data["errors"]["church_role"] = true;
            else
            {
                foreach ($church_role as $item) {
                    if(!preg_match("/^(Elder|Bishop|Pastor|Teacher|Denominational Leader|Seminary Professor)$/", $item))
                    {
                        $data["errors"]["church_role"] = true;
                        break;
                    }
                }
            }

            if(empty($data["errors"]))
            {
                $this->_model->updateMember([
                    "userName" => $userName,
                    "firstName" => $firstName,
                    "lastName" => $lastName
                ], [
                    "memberID" => Session::get("memberID")
                ]);

                Session::set("userName", $userName);
                Session::set("firstName", $firstName);
                Session::set("lastName", $lastName);

                $postdata = array(
                    "avatar" => $avatar,
                    "prefered_roles" => json_encode($prefered_roles),
                    "languages" => json_encode($languages),
                    "bbl_trans_yrs" => $bbl_trans_yrs,
                    "othr_trans_yrs" => $othr_trans_yrs,
                    "bbl_knwlg_degr" => $bbl_knwlg_degr,
                    "mast_evnts" => $mast_evnts,
                    "mast_role" => json_encode($mast_role),
                    "teamwork" => $teamwork,
                    "mast_facilitator" => $mast_facilitator,
                    "org" => $org,
                    "ref_person" => $ref_person,
                    "ref_email" => $ref_email,
                    "education" => json_encode($education),
                    "ed_area" => json_encode($ed_area),
                    "ed_place" => $ed_place,
                    "hebrew_knwlg" => $hebrew_knwlg,
                    "greek_knwlg" => $greek_knwlg,
                    "church_role" => json_encode($church_role),
                    "complete" => true
                );

                $this->_model->updateProfile($postdata, array("pID" => $profile["pID"]));

                $postdata["pID"] = $profile["pID"];
                $postdata["languages"] = $langArr;
                $postdata["prefered_roles"] = $prefered_roles;
                $postdata["mast_role"] = $mast_role;
                $postdata["education"] = $education;
                $postdata["ed_area"] = $ed_area;
                $postdata["church_role"] = $church_role;

                $profile = $postdata;

                Session::set("profile", $profile);
                Session::set("success", __("update_profile_success"));

                Url::redirect("members/profile");
            }
            else
            {
                $error[] = __('required_fields_empty_error');
            }
        }

        $data["notifications"] = $this->_notifications;
        $data["newNewsCount"] = $this->_newNewsCount;
        $data['csrfToken'] = Csrf::makeToken();

        return View::make('Members/Profile')
            ->shares("title", __("profile_message"))
            ->shares("data", $data)
            ->shares("error", @$error);
    }

    /**
     * Show public profile view
     * @return mixed
     */
    public function publicProfile($memberID)
    {
        if (!Session::get('loggedin')) {
            Url::redirect('members/login');
        }

        if (Session::get("isDemo")) {
            Url::redirect('events/demo');
        }

        if (!Session::get("isAdmin") && !Session::get("isSuperAdmin")) {
            Url::redirect('events');
        }

        $data["menu"] = 1;
        $data["errors"] = array();
        $memberProfile = $this->_model->getMemberWithProfile($memberID);

        $profile = array();
        $data["profile"] = [];

        $profile["pID"] = $memberProfile[0]->pID;
        $profile["memberID"] = $memberProfile[0]->mID;
        $profile["projects"] = (array)json_decode($memberProfile[0]->projects, true);
        $profile["proj_lang"] = null;
        $profile["avatar"] = $memberProfile[0]->avatar;
        $profile["username"] = $memberProfile[0]->userName;
        $profile["fullname"] = $memberProfile[0]->firstName . " " . $memberProfile[0]->lastName;
        $profile["prefered_roles"] = (array)json_decode($memberProfile[0]->prefered_roles, true);
        $profile["bbl_trans_yrs"] = $memberProfile[0]->bbl_trans_yrs;
        $profile["othr_trans_yrs"] = $memberProfile[0]->othr_trans_yrs;
        $profile["bbl_knwlg_degr"] = $memberProfile[0]->bbl_knwlg_degr;
        $profile["mast_evnts"] = $memberProfile[0]->mast_evnts;
        $profile["mast_role"] = (array)json_decode($memberProfile[0]->mast_role, true);
        $profile["teamwork"] = $memberProfile[0]->teamwork;
        $profile["org"] = $memberProfile[0]->org;
        $profile["ref_person"] = $memberProfile[0]->ref_person;
        $profile["ref_email"] = $memberProfile[0]->ref_email;
        $profile["mast_facilitator"] = $memberProfile[0]->mast_facilitator == 1;
        $profile["education"] = (array)json_decode($memberProfile[0]->education, true);
        $profile["ed_area"] = (array)json_decode($memberProfile[0]->ed_area, true);
        $profile["ed_place"] = $memberProfile[0]->ed_place;
        $profile["hebrew_knwlg"] = $memberProfile[0]->hebrew_knwlg;
        $profile["greek_knwlg"] = $memberProfile[0]->greek_knwlg;
        $profile["church_role"] = (array)json_decode($memberProfile[0]->church_role, true);
        $profile["complete"] = $memberProfile[0]->complete;

        $arr = (array)json_decode($memberProfile[0]->languages, true);
        $languages = array();
        foreach ($arr as $i => $item) {
            $languages[$i]["lang_fluency"] = $item[0];
            $languages[$i]["geo_lang_yrs"] = $item[1];
        }
        $profile["languages"] = $languages;

        $proj_lang = $this->_eventModel->getAllLanguages(null, [$memberProfile[0]->proj_lang]);
        if (!empty($proj_lang))
            $profile["proj_lang"] = $proj_lang;

        $data["profile"] = $profile;

        $langs = $this->_eventModel->getAllLanguages(null, array_keys($languages));
        $data["languages"] = [];
        foreach ($langs as $lang) {
            $data["languages"][$lang->langID]["langName"] = $lang->langName;
            $data["languages"][$lang->langID]["angName"] = $lang->angName;
        }

        $data["facilitation_activities"] = $this->_eventModel->getMemberEventsForAdmin($memberID);
        $data["translation_activities"] = $this->_eventModel->getMemberEvents($memberID, EventMembers::TRANSLATOR, null, true);


        $l2_check_activities = $this->_eventModel->getMemberEvents($memberID, EventMembers::L2_CHECKER, null, true);
        $l3_check_activities = $this->_eventModel->getMemberEvents($memberID, EventMembers::L3_CHECKER, null, true);

        $data["checking_activities"] = [];

        // Translation and level 1 check (ulb, udb, notes, tq, tw, sun)
        foreach ($data["translation_activities"] as $translation_activity) {
            $chapters = $this->_eventModel->getChapters($translation_activity->eventID, $memberProfile[0]->mID);

            $chaps = [];
            foreach ($chapters as $chapter) {
                $chaps[] = $chapter["chapter"];
            }
            $chaps = array_map(function ($elm) {
                return $elm > 0 ? $elm : __("intro");
            }, $chaps);

            $translation_activity->chapters = join(", ", array_values($chaps));

            $checking = $this->_eventModel->getMemberEvents(null, EventMembers::TRANSLATOR, $translation_activity->eventID, true);
            $chaps = [];

            foreach ($checking as $check) {
                if (in_array($check->bookProject, ["ulb"])) {
                    // Level 1 ulb checking
                    $verbCheck = (array)json_decode($check->verbCheck, true);
                    $peerCheck = (array)json_decode($check->peerCheck, true);
                    $kwCheck = (array)json_decode($check->kwCheck, true);
                    $crCheck = (array)json_decode($check->crCheck, true);

                    foreach ($verbCheck as $chapter => $memID)
                        if ($memberID == $memID)
                            $chaps[] = $chapter;

                    foreach ($peerCheck as $chapter => $memID)
                        if ($memberID == $memID)
                            $chaps[] = $chapter;

                    foreach ($kwCheck as $chapter => $memID)
                        if ($memberID == $memID)
                            $chaps[] = $chapter;

                    foreach ($crCheck as $chapter => $memID)
                        if ($memberID == $memID)
                            $chaps[] = $chapter;
                } else {
                    $peerCheck = (array)json_decode($check->peerCheck, true);
                    $kwCheck = (array)json_decode($check->kwCheck, true);
                    $crCheck = (array)json_decode($check->crCheck, true);

                    foreach ($peerCheck as $chapter => $member_data)
                        if ($memberID == $member_data["memberID"])
                            $chaps[] = $chapter;

                    foreach ($kwCheck as $chapter => $member_data)
                        if ($memberID == $member_data["memberID"])
                            $chaps[] = $chapter;

                    foreach ($crCheck as $chapter => $member_data)
                        if ($memberID == $member_data["memberID"])
                            $chaps[] = $chapter;
                }
            }

            $chaps = array_unique($chaps);
            sort($chaps);
            $chaps = array_map(function ($elm) {
                return $elm > 0 ? $elm : __("intro");
            }, $chaps);

            if (!empty($chaps)) {
                $checking[0]->chapters = join(", ", array_values($chaps));
                $data["checking_activities"][] = $checking[0];
            }
        }

        // Level 2 checking (ulb, udb)
        foreach ($l2_check_activities as $checking_activity) {
            $chapters = $this->_eventModel->getChapters($checking_activity->eventID, $memberProfile[0]->mID, null, "l2");

            // First checker
            $chaps = [];
            foreach ($chapters as $chapter) {
                $chaps[] = $chapter["chapter"];
            }

            // Second checker
            $checking = $this->_eventModel->getMemberEvents(null, EventMembers::L2_CHECKER, $checking_activity->eventID, true);
            foreach ($checking as $check) {
                $sndCheck = (array)json_decode($check->sndCheck, true);
                $peer1Check = (array)json_decode($check->peer1Check, true);
                $peer2Check = (array)json_decode($check->peer2Check, true);

                foreach ($sndCheck as $chapter => $member_data)
                    if ($memberID == $member_data["memberID"])
                        $chaps[] = $chapter;

                foreach ($peer1Check as $chapter => $member_data)
                    if ($memberID == $member_data["memberID"])
                        $chaps[] = $chapter;

                foreach ($peer2Check as $chapter => $member_data)
                    if ($memberID == $member_data["memberID"])
                        $chaps[] = $chapter;
            }

            $chaps = array_unique($chaps);
            sort($chaps);

            $checking_activity->chapters = join(", ", array_values($chaps));
            $data["checking_activities"][] = $checking_activity;
        }

        // Checking level 3 check (ulb, udb, notes, tq, tw)
        foreach ($l3_check_activities as $checking_activity) {
            $chapters = $this->_eventModel->getChapters($checking_activity->eventID, $memberProfile[0]->mID, null, "l3");

            // First checker
            $chaps = [];
            foreach ($chapters as $chapter) {
                $chaps[] = $chapter["chapter"];
            }

            // Second checker
            $checking = $this->_eventModel->getMemberEvents(null, EventMembers::L3_CHECKER, $checking_activity->eventID, true);
            foreach ($checking as $check) {
                $peerCheck = (array)json_decode($check->peerCheck, true);

                foreach ($peerCheck as $chapter => $member_data)
                    if($memberID == $member_data["memberID"])
                        $chaps[] = $chapter;
            }

            $chaps = array_unique($chaps);
            sort($chaps);
            $chaps = array_map(function ($elm) {
                return $elm > 0 ? $elm : __("intro");
            }, $chaps);

            $checking_activity->chapters = join(", ", array_values($chaps));
            $data["checking_activities"][] = $checking_activity;
        }

        $data["notifications"] = $this->_notifications;
        $data["newNewsCount"] = $this->_newNewsCount;

        return View::make('Members/PublicProfile')
            ->shares("title", __("member_profile_message"))
            ->shares("data", $data)
            ->shares("error", @$error);
    }


    public function search()
    {
        if (!Session::get('loggedin'))
        {
            Url::redirect('members/login');
        }

        if(Session::get("isDemo"))
        {
            Url::redirect('events/demo');
        }

        if(Session::get("isSuperAdmin") && !isset($_POST["ext"]))
        {
            Url::redirect('admin/members');
        }

        if(!Session::get("isSuperAdmin") && !Session::get("isAdmin") && !isset($_POST["ext"]))
        {
            Url::redirect('events');
        }

        $data["menu"] = 4;

        $arr = $this->_eventModel->getAdminLanguages(Session::get("memberID"));
        $admLangs = [];
        foreach ($arr as $item) {
            $admLangs[] = $item->gwLang;
            $admLangs[] = $item->targetLang;
        }
        $admLangs = array_unique($admLangs);

        if(!empty($_POST))
        {
            $response = ["success" => false];

            $_POST = Gump::xss_clean($_POST);

            $name = isset($_POST["name"]) && $_POST["name"] != "" ? $_POST["name"] : false;
            $role = isset($_POST["role"]) && preg_match("/^(translators|facilitators|all)$/", $_POST["role"]) ? $_POST["role"] : "all";
            $language = isset($_POST["language"]) && $_POST["language"] != "" ? $_POST["language"] : false;
            $page = isset($_POST["page"]) ? (integer)$_POST["page"] : 1;
            $searchExt = isset($_POST["ext"]) ? $_POST["ext"] : false;
            $verified = isset($_POST["verified"]) ? $_POST["verified"] : false;

            if(empty($admLangs) && !$searchExt)
            {
                $response["error"] = __("not_enough_rights_error");
                echo json_encode($response);
                exit;
            }

            if($name || $role || $language)
            {
                $count = 0;
                $members = [];

                if($language)
                {
                    if(in_array($language, $admLangs))
                    {
                        $count = $this->_model->searchMembers(
                            $name,
                            $role,
                            [$language],
                            true,
                            false,
                            $verified
                        );
                        $members = $this->_model->searchMembers(
                            $name,
                            $role,
                            [$language],
                            false,
                            true,
                            $verified,
                            $page
                        );
                    }
                }
                else
                {
                    if($searchExt) $admLangs = [];
                    $count = $this->_model->searchMembers(
                        $name,
                        $role,
                        $admLangs,
                        true,
                        false,
                        $verified
                    );
                    $members = $this->_model->searchMembers(
                        $name,
                        $role,
                        $admLangs,
                        false,
                        true,
                        $verified,
                        $page
                    );
                }

                $response["success"] = true;
                $response["count"] = $count;
                $response["members"] = $members;
            }
            else
            {
                $response["error"] = __("choose_filter_option");
            }

            echo json_encode($response);
            exit;
        }
        else
        {
            if(empty($admLangs))
            {
                Url::redirect('events');
            }
        }

        $data["languages"] = $this->_eventModel->getAllLanguages(null, $admLangs);

        $data["count"] = $this->_model->searchMembers(null, "all", $admLangs, true);
        $data["members"] = $this->_model->searchMembers(null, "all", $admLangs, false, true);

        $data["notifications"] = $this->_notifications;
        $data["newNewsCount"] = $this->_newNewsCount;

        return View::make('Members/Search')
            ->shares("title", __("admin_members_title"))
            ->shares("data", $data);
    }

    /**
     * Show activation view
     * @param $memberID
     * @param $activationToken
     * @return mixed
     */
    public function activate($memberID, $activationToken)
    {
        if (Session::get('loggedin'))
        {
            Url::redirect('members');
        }

        if (($memberID > 0) && (strlen($activationToken) == 32))
        {
            $user = $this->_model->getMember(["memberID", "active"], [
                ["memberID", $memberID],
                ["active", false],
                ["activationToken", $activationToken]
            ]);

            if (!$user || $user[0]->memberID == 0)
            {
                $error[] = __('no_account_error');
            }
            elseif ($user[0]->active == true)
            {
                $error[] = __('account_activated_error');
            }
            else
            {
                $postdata = [
                    "active" => true,
                    "verified" => true,
                    "activationToken" => null
                ];
                $where = array('memberID' => $memberID);
                $this->_model->updateMember($postdata, $where);

                $msg = __('account_activated_success');
                Session::set('success', $msg);

                Session::destroy("activation_email");

                Url::redirect('members/success');
            }
        }
        else
        {
            $error[] = __('invalid_link_error');
        }

        return View::make('Members/Activate')
            ->shares("title", __("activate_account_title"))
            ->shares("error", @$error);
    }

    /**
     * Show resend activation instructions view with form
     * @param $email
     * @return mixed
     */
    public function resendActivation($email)
    {
        $data = $this->_model->getMember(array("memberID", "email", "userName"),
            array(
                array("email", $email),
                array("active", "!=", true)
        ));

        if(!empty($data))
        {
            $activationToken = md5(uniqid(rand(), true));
            $this->_model->updateMember(["activationToken" => $activationToken], ["email" => $email]);

            Mailer::send(
                'Emails/Auth/Activate',
                ["memberID" => $data[0]->memberID, "token" => $activationToken],
                function($message) use($data)
                {
                    $message->to($data[0]->email, $data[0]->userName)
                        ->subject(__('activate_account_title'));
                }
            );

            $msg = __('resend_activation_success_message');
            Session::set('success', $msg);
        }
        else
        {
            $error[] = __("wrong_activation_email");
        }

        $data['menu'] = 5;

        return View::make('Members/EmailActivation')
            ->shares("title", __("resend_activation_title"))
            ->shares("data", $data)
            ->shares("error", @$error);
    }

    /**
     * Show login view with form
     * @return mixed
     */
    public function login()
    {
        if (Session::get('loggedin'))
        {
            Url::redirect('members');
        }

        if (isset($_POST['submit']))
        {
            if (!Csrf::isTokenValid())
            {
                Url::redirect('members/login');
            }

            if(Config::get("app.type") == "remote")
            {
                $loginTry = Session::get('loginTry');
                if($loginTry == null) $loginTry = 0;
                $loginTry++;

                Session::set('loginTry', $loginTry);

                if($loginTry >= 3)
                {
                    if($loginTry > 3)
                    {
                        if (!ReCaptcha::check())
                        {
                            $error[] = __('captcha_wrong');
                        }
                    }
                }
            }

            if(!isset($error))
            {
                $data = $this->_model->getMemberWithProfile($_POST['email'], true);

                if(!empty($data))
                {
                    if($data[0]->blocked) Url::redirect('members/login');

                    if (Password::verify($_POST['password'], $data[0]->password) ||
                        (Config::get("app.type") == "local" && !$data[0]->isSuperAdmin))
                    {
                        if($data[0]->active)
                        {
                            $authToken = md5(uniqid(rand(), true));
                            $updateData = [
                                "authToken" => $authToken,
                                "logins" => $data[0]->logins + 1
                            ];
                            $updated = $this->_model->updateMember($updateData, ['memberID' => $data[0]->memberID]);

                            if($updated === 1)
                            {
                                $profile = array();
                                $profile["pID"] = $data[0]->pID;
                                $profile["projects"] = json_decode($data[0]->projects, true);
                                $profile["proj_lang"] = $data[0]->proj_lang;
                                $profile["avatar"] = $data[0]->avatar;
                                $profile["prefered_roles"] = json_decode($data[0]->prefered_roles, true);
                                $profile["bbl_trans_yrs"] = $data[0]->bbl_trans_yrs;
                                $profile["othr_trans_yrs"] = $data[0]->othr_trans_yrs;
                                $profile["bbl_knwlg_degr"] = $data[0]->bbl_knwlg_degr;
                                $profile["mast_evnts"] = $data[0]->mast_evnts;
                                $profile["mast_role"] = (array)json_decode($data[0]->mast_role, true);
                                $profile["teamwork"] = $data[0]->teamwork;
                                $profile["org"] = $data[0]->org;
                                $profile["ref_person"] = $data[0]->ref_person;
                                $profile["ref_email"] = $data[0]->ref_email;
                                $profile["mast_facilitator"] = $data[0]->mast_facilitator == 1;
                                $profile["education"] = (array)json_decode($data[0]->education, true);
                                $profile["ed_area"] = (array)json_decode($data[0]->ed_area, true);
                                $profile["ed_place"] = $data[0]->ed_place;
                                $profile["hebrew_knwlg"] = $data[0]->hebrew_knwlg;
                                $profile["greek_knwlg"] = $data[0]->greek_knwlg;
                                $profile["church_role"] = (array)json_decode($data[0]->church_role, true);
                                $profile["complete"] = $data[0]->complete;

                                $arr = (array)json_decode($data[0]->languages, true);
                                $languages = array();
                                foreach ($arr as $i => $item) {
                                    $languages[$i]["lang_fluency"] = $item[0];
                                    $languages[$i]["geo_lang_yrs"] = $item[1];
                                }
                                $profile["languages"] = $languages;

                                Session::set('memberID', $data[0]->memberID);
                                Session::set('userName', $data[0]->userName);
                                Session::set('firstName', $data[0]->firstName);
                                Session::set('lastName', $data[0]->lastName);
                                Session::set('email', $data[0]->email);
                                Session::set('authToken', $authToken);
                                Session::set('verified', $data[0]->verified);
                                Session::set('isAdmin', $data[0]->isAdmin);
                                Session::set('isSuperAdmin', $data[0]->isSuperAdmin);
                                Session::set('isDemo', $data[0]->isDemo);
                                Session::set('loggedin', true);
                                Session::set("profile", $profile);

                                Session::destroy('loginTry');

                                if(Session::get('redirect') != null)
                                {
                                    Url::redirect(Session::get('redirect'));
                                }
                                else
                                {
                                    Url::redirect('members');
                                }
                            }
                            else
                            {
                                $error[] = __('user_login_error');
                            }
                        }
                        else
                        {
                            $error[] = __('not_activated_email', ["email" => $data[0]->email]);
                        }
                    }
                    else
                    {
                        $error[] = __('wrong_credentials_error');
                    }
                }
                else
                {
                    $error[] = __('wrong_credentials_error');
                }
            }
        }

        $data["menu"] = 5;
        $data['csrfToken'] = Csrf::makeToken();

        return View::make('Members/Login')
            ->shares("title", __("login_title"))
            ->shares("data", $data)
            ->shares("error", @$error);
    }

    /**
     * Show signup view with form
     * @return mixed
     */
    public function signup()
    {
        // Registration
        $data["menu"] = 5;
        $data["languages"] = $this->_eventModel->getAllLanguages();

        if (Session::get('loggedin')) {
            Url::redirect("events");
        }

        if (isset($_POST['submit']))
        {
            $_POST = Gump::xss_clean($_POST);

            $_POST = Gump::filter_input($_POST, [
                'userName' => 'trim',
                'firstName' => 'trim',
                'lastName' => 'trim',
                'email' => 'trim',
                'password' => 'trim'
            ]);

            $userName = $_POST['userName'];
            $firstName = $_POST['firstName'];
            $lastName = $_POST['lastName'];
            $email = $_POST['email'];
            $password = $_POST['password'];
            $passwordConfirm = $_POST['passwordConfirm'];
            $tou = isset($_POST['tou']) ? (int)$_POST['tou'] == 1 : false;
            $sof = isset($_POST['sof']) ? (int)$_POST['sof'] == 1 : false;
            $projects = $_POST['projects'];
            $projLang = $_POST['proj_lang'];

            if(!preg_match("/^[a-z]+[a-z0-9]*$/i", $userName))
            {
                $error['userName'] = __('userName_characters_error');
            }

            if (strlen($userName) < 5 || strlen($userName) > 20)
            {
                $error['userName'] = __('userName_length_error');
            }

            if (mb_strlen($firstName) < 2 || mb_strlen($firstName) > 20)
            {
                $error['firstName'] = __('firstName_length_error');
            }

            if (mb_strlen($lastName) < 2 || mb_strlen($lastName) > 20)
            {
                $error['lastName'] = __('lastName_length_error');
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL))
            {
                $error['email'] = __('enter_valid_email_error');
            }
            else
            {
                $check = $this->_model->getMember(array("memberID", "userName", "email"),
                    array(
                        array("email", $email),
                        array("userName", $userName, "=", "OR")
                ));

                foreach ($check as $item) {
                    if (strtolower($item->email) == strtolower($email))
                    {
                        $error['email'] = __('email_taken_error');
                    }
                    if (strtolower($item->userName) == strtolower($userName))
                    {
                        $error['email'] = __('username_taken_error');
                    }
                }
            }

            if (strlen($password) < 6)
            {
                $error['password'] = __('password_short_error');
            }
            elseif ($password != $passwordConfirm)
            {
                $error['confirm'] = __('passwords_notmatch_error');
            }

            if(Config::get("app.type") == "remote")
            {
                if (!ReCaptcha::check())
                {
                    $error['recaptcha'] = __('captcha_wrong');
                }
            }

            if(!$tou)
            {
                $error['tou'] = __('tou_accept_error');
            }

            if(!$sof)
            {
                $error['sof'] = __('sof_accept_error');
            }

            if(!in_array($projects, ["mast","vsail","odb","l2","l3","tn","tq","tw"])) {
                $error["projects"] = __("projects_empty_error");
            }

            if(!preg_match("/^[0-9A-Za-z-]{2,40}$/", $projLang)) {
                $error["proj_lang"] = __("proj_lang_empty_error");
            }

            if (!isset($error))
            {
                $activationToken = md5(uniqid(rand(), true));

                $hash = Password::make($password);

                //insert
                $postdata = [
                    "userName" => $userName,
                    "firstName" => $firstName,
                    "lastName" => $lastName,
                    "email" => $email,
                    "password" => $hash,
                    "activationToken" => $activationToken,
                ];

                if(Config::get("app.type") == "local")
                {
                    $postdata["active"] = true;
                    $postdata["verified"] = true;
                }

                $data = [
                    "userName" => $userName,
                    "email" => $email
                ];

                $id = $this->_model->createMember($postdata);

                $profiledata = [
                    "mID" => $id,
                    "projects" => json_encode([$projects]),
                    "proj_lang" => $projLang
                ];
                if(Config::get("app.type") != "remote") {
                    $profiledata["prefered_roles"] = json_encode(["translator"]);
                    $profiledata["languages"] = '{"en":[3,3]}';
                    $profiledata["complete"] = true;
                }

                $this->_model->createProfile($profiledata);

                if(Config::get("app.type") == "remote")
                {
                    Mailer::send(
                        'Emails/Auth/Activate',
                        ["memberID" => $id, "token" => $activationToken],
                        function($message) use($data)
                        {
                            $message->to($data["email"], $data["userName"])
                                ->subject(__('activate_account_title'));
                        }
                    );

                    // Project language for email message
                    $proj_languages = $this->_eventModel->getAllLanguages(null, [$projLang]);
                    $proj_lang = "";
                    if (!empty($proj_languages))
                    {
                        $pl = $proj_languages[0];
                        $proj_lang = "[".$pl->langID."] " . $pl->langName .
                            ($pl->angName != "" && $pl->angName != $pl->langName ? " (".$pl->angName.")" : "");
                    }

                    // Projects list for email message
                    switch ($projects) {
                        case "mast":
                            $projects = __("8steps_mast");
                            break;
                        case "l2":
                            $projects = __("l2_3_events", ["level" => 2]);
                            break;
                        case "l3":
                            $projects = __("l2_3_events", ["level" => 3]);
                            break;
                        default:
                            $projects = __($projects);
                    }

                    $adminEmail = Config::get("app.email");
                    Mailer::send(
                        'Emails/Common/NotifyRegistration',
                        [
                            "userName" => $userName,
                            "name" => $firstName." ".$lastName,
                            "id" => $id,
                            "projectLanguage" => $proj_lang,
                            "projects" => $projects
                        ],
                        function($message) use($adminEmail)
                        {
                            $message->to($adminEmail)
                                ->subject($this->_model->translate("new_account_title", "En"));
                        }
                    );

                    Session::set("success", __('registration_success_message'));
                    Session::set("activation_email", $email);

                    Url::redirect('members/success');
                }
                else
                {
                    Session::set("success", __('registration_local_success_message'));
                    Url::redirect('members/login');
                }
            }
        }

        $data['csrfToken'] = Csrf::makeToken();

        return View::make('Members/Signup')
            ->shares("title", __("signup"))
            ->shares("data", $data)
            ->shares("error", @$error);
    }


    /**
     * Show password reset view with email form
     * @return mixed
     */
    public function passwordReset()
    {
        if (Session::get('loggedin'))
        {
            Url::redirect('members');
        }

        if (isset($_POST['submit']))
        {
            if (!Csrf::isTokenValid())
            {
                Url::redirect('members/passwordreset');
            }

            $email = $_POST['email'];

            if(Config::get("app.type") == "remote")
            {
                if (!ReCaptcha::check())
                {
                    $error[] = __('captcha_wrong');
                }
            }

            if(!filter_var($email, FILTER_VALIDATE_EMAIL))
            {
                $error[] = __('enter_valid_email_error');
            }

            if(!isset($error))
            {
                $data = $this->_model->getMember(array("memberID", "userName", "email"), array("email", $email));
                if(!empty($data))
                {
                    $resetToken = md5(uniqid(rand(), true));
                    $postdata = array('resetToken' => $resetToken, 'resetDate' => date('Y-m-d H:i:s',time()));
                    $this->_model->updateMember($postdata, array('email' => $email));

                    if(Config::get("app.type") == "remote")
                    {
                        Mailer::send(
                            'Emails/Auth/PasswordReset',
                            ["memberID" => $data[0]->memberID, "token" => $resetToken],
                            function($message) use($data)
                            {
                                $message->to($data[0]->email, $data[0]->userName)
                                    ->subject(__('passwordreset_title'));
                            }
                        );
                        $msg = __('pwresettoken_send_success');
                    }
                    else
                    {
                        $msg = __(
                            "passwordreset_link_message",
                            [
                                "link" => '<a href="'.site_url('members/resetpassword/'
                                        .$data[0]->memberID."/".$resetToken).'">'
                                        .site_url('members/resetpassword/'
                                        .$data[0]->memberID."/".$resetToken).'</a>'
                            ]
                        );
                    }

                    Session::set('success', $msg);

                    Url::redirect('members/success');
                }
                else
                {
                    $error[] = __('enter_valid_email_error');
                }
            }
        }

        $data['menu'] = 5;
        $data['csrfToken'] = Csrf::makeToken();

        return View::make('Members/PasswordReset')
            ->shares("title", __("passwordreset_title"))
            ->shares("data", $data)
            ->shares("error", @$error);
    }

    /**
     * Show password reset view with new password form
     * @param $memberID
     * @param $resetToken
     * @return mixed
     */
    public function resetPassword($memberID, $resetToken)
    {
        if (Session::get('loggedin'))
        {
            Url::redirect('members');
        }

        $data['step'] = 1;
        $data['menu'] = 5;

        if (($memberID > 0) && (strlen($resetToken) == 32))
        {
            $user = $this->_model->getMember(array("memberID", "resetDate"),
                array(
                    array("memberID", $memberID),
                    array("resetToken", $resetToken)
            ));

            if (!$user || $user[0]->memberID == 0)
            {
                $error[] = __('no_account_error');
            }
            elseif((time() - strtotime($user[0]->resetDate) > (60*60*24*3)))
            {
                $error[] = __('token_expired_error');
                $postdata = array('resetToken' => null);
                $where = array('memberID' => $memberID);
                $this->_model->updateMember($postdata, $where);
            }
            else
            {
                $data['step'] = 2;

                if(isset($_POST['submit']))
                {
                    if (!Csrf::isTokenValid())
                    {
                        Url::redirect(SITEURL."members/resetpassword/$memberID/$resetToken");
                    }

                    $_POST = Gump::filter_input($_POST, array(
                        'password' => 'trim'
                    ));

                    $password = $_POST['password'];
                    $passwordConfirm = $_POST['passwordConfirm'];

                    if (strlen($password) < 5)
                    {
                        $error[] = __('password_short_error');
                    }
                    elseif ($password != $passwordConfirm)
                    {
                        $error[] = __('passwords_notmatch_error');
                    }

                    if (empty($error))
                    {
                        $postdata = array('password' => Password::make($password), 'resetToken' => null);
                        $this->_model->updateMember($postdata, array('memberID' => $memberID));

                        $msg = __('password_reset_success');
                        Session::set('success', $msg);

                        Url::redirect('members/success');
                    }
                }
            }
        }
        else
        {
            $error[] = __('invalid_link_error');
        }

        $data['csrfToken'] = Csrf::makeToken();

        return View::make('Members/ResetPassword')
            ->shares("title", __("passwordreset_title"))
            ->shares("data", $data)
            ->shares("error", @$error);
    }

    /**
     * Make rpc call from nodejs and send json string back
     * @param $memberID
     * @param $eventID
     * @param $authToken
     */
    public function rpcAuth($memberID, $eventID, $authToken) {

        $event = $this->_eventModel->getEventMember($eventID, $memberID);

        if(!empty($event))
        {
            $member = $this->_model->getMember(
                ["memberID", "userName", "firstName", "lastName", "verified", "isAdmin", "isSuperAdmin"],
                [
                    ["memberID", $memberID],
                    ["authToken", $authToken]
            ]);

            $isAdmin = false;
            $isSuperAdmin = false;

            if(!empty($member))
            {
                $admins = array_merge([], (array)json_decode($event[0]->admins, true),
                    (array)json_decode($event[0]->admins_l2, true),
                    (array)json_decode($event[0]->admins_l3, true));

                if($event[0]->translator == null && $event[0]->checker == null
                    && $event[0]->checker_l2 == null && $event[0]->checker_l3 == null)
                {
                    if($member[0]->isAdmin)
                        $isAdmin = in_array($member[0]->memberID, $admins);
                    if ($member[0]->isSuperAdmin)
                        $isSuperAdmin = true;

                    if(!$isAdmin && !$isSuperAdmin)
                    {
                        echo json_encode(array());
                        return;
                    }
                }

                // Make sure that member is admin for this event
                if($member[0]->isAdmin && !$isAdmin)
                    $isAdmin = in_array($member[0]->memberID, $admins);

                $member[0]->isAdmin = $isAdmin;
                $member[0]->isSuperAdmin = $isSuperAdmin;
                $member[0]->lastName = mb_substr($member[0]->lastName, 0, 1).".";
                echo json_encode($member[0]);
            }
            else
            {
                echo json_encode(array());
            }
        }
        else
        {
            echo json_encode(array());
        }
    }


    public function sendMessageToAdmin()
    {
        $response = ["success" => false];

        if (!Session::get('loggedin'))
        {
            $response["errorType"] = "logout";
        }

        if(Session::get("isDemo"))
        {
            $response["errorType"] = "demo";
        }

        if(empty(Session::get("profile")))
        {
            $response["errorType"] = "profile";
        }

        if(Config::get("app.type") != "remote")
        {
            $response["errorType"] = "local";
            $response["error"] = __("local_use_restriction");
            echo json_encode($response);
            exit;
        }

        if(!empty($_POST))
        {
            $_POST =  Gump::xss_clean($_POST);

            $adminID = isset($_POST["adminID"]) && $_POST["adminID"] != "" ? (integer)$_POST["adminID"] : null;
            $subject = isset($_POST["subject"]) && $_POST["subject"] != "" ? $_POST["subject"] : null;
            $message = isset($_POST["message"]) && $_POST["message"] != "" ? $_POST["message"] : null;

            if($adminID != null && $subject != null && $message != null)
            {
                $admin = $this->_model->getMember(
                    ["memberID", "userName", "firstName", "lastName", "email", "isAdmin"],
                    ["memberID", $adminID],
                    true);

                if(!empty($admin) && $admin[0]->isAdmin)
                {
                    if($admin[0]->memberID != Session::get("memberID"))
                    {
                        $data["fName"] = $admin[0]->firstName . " " . $admin[0]->lastName;
                        $data["fEmail"] = $admin[0]->email;
                        $data["tMemberID"] = Session::get("memberID");
                        $data["tUserName"] = Session::get("userName");
                        $data["tName"] = Session::get("firstName") . " " . Session::get("lastName");
                        $data["tEmail"] = Session::get("email");
                        $data["subject"] = $subject;
                        $data["message"] = $message;

                        $firstLang = "en";
                        $languages = json_decode($admin[0]->languages, true);
                        if(is_array($languages) && !empty($languages))
                        {
                            $keys = array_keys($languages);
                            $firstLang = $keys[0];
                        }

                        $data["lang"] = ucfirst($firstLang);

                        Mailer::send(
                            'Emails/Common/Message',
                            ["data" => $data],
                            function($message) use($data)
                            {
                                $message->setReplyTo([$data["tEmail"] => $data["tName"]])
                                    ->setTo($data["fEmail"], $data["fName"])
                                    ->setSubject($data["subject"]);
                            }
                        );

                        $response["success"] = true;
                    }
                    else
                    {
                        $response["errorType"] = "data";
                        $response["error"] = __("facilitator_yourself_error");
                    }
                }
                else
                {
                    $response["errorType"] = "data";
                    $response["error"] = __("not_facilitator_error");
                }
            }
            else
            {
                $response["errorType"] = "data";
                $response["error"] = __("required_fields_empty_error");
            }
        }

        echo json_encode($response);
    }


    public function cloudLogin() {
        $response = ["success" => false];

        if(!empty($_POST)) {

            $_POST = Gump::xss_clean($_POST);

            $server = isset($_POST["server"]) && $_POST["server"] != "" ? $_POST["server"] : null;
            $username = isset($_POST["username"]) && $_POST["username"] != "" ? $_POST["username"] : null;
            $password = isset($_POST["password"]) && $_POST["password"] != "" ? $_POST["password"] : null;
            $otp = isset($_POST["otp"]) && $_POST["otp"] != "" ? $_POST["otp"] : "";

            if ($server != null && $username != null && $password != null)
            {
                $cloudModel = new CloudModel($server, $username, $password, $otp);
                $data = $cloudModel->getAccessTokens();
                $token = $cloudModel->getOdbAccessToken($data);

                if(empty($token) || $token["sha1"] == "")
                {
                    $data = $cloudModel->createAccessToken();
                    $token = $cloudModel->getOdbAccessToken($data);
                }

                if(!empty($token))
                {
                    Session::set($server, [
                        "username" => $username,
                        "token" => $token["sha1"]
                    ]);

                    $response["success"] = true;
                }
                else
                {
                    $response["error"] = "could_not_get_token";
                }
            }
        }

        echo json_encode($response);
    }

    /**
     * Show success veiw
     * @return mixed
     */
    public function success()
    {
        return View::make('Members/Success')
            ->shares("title", __("success"));
    }

    /**
     * Show verification error view
     * @return mixed
     */
    public function verificationError()
    {
        return View::make('Members/Verify')
            ->shares("title", __("verification_error_title"))
            ->shares("error", __("verification_error"));
    }

    /**
     * Logout of site
     */
    public function logout()
    {
        Session::destroy();
        Url::redirect('/', true);
    }
}