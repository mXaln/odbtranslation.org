<?php
namespace Controllers;

use Core\Controller;
use Core\View;
use Helpers\Constants\EventMembers;
use Helpers\Csrf;
use Helpers\Data;
use Helpers\Gump;
use Helpers\Password;
use Helpers\PhpMailer\Mail;
use Helpers\PhpMailer\PhpMailer;
use Helpers\ReCaptcha\ReCaptcha;
use Helpers\Session;
use Helpers\Url;
use Models\EventsModel;
use Models\MembersModel;

class MembersController extends Controller
{

    private $_model;
    private $_lang;
    private $_notifications;

    public function __construct()
    {
        parent::__construct();
        $this->_lang = isset($_COOKIE['lang']) ? $_COOKIE['lang'] : 'en';
        $this->language->load('Members', $this->_lang);
        $this->_model = new MembersModel();

        $evntModel = new EventsModel();
        if(Session::get("loggedin"))
            $this->_notifications = $evntModel->getNotifications();
    }

    public function index()
    {
        $data['lang'] = $this->_lang;

        if (Session::get('loggedin') == true)
        {
            if(empty(Session::get("profile")))
            {
                Url::redirect("members/profile");
            }

            $data['title'] = $this->language->get('welcome_title');

            $eventModel = new EventsModel();

            if(Session::get("isAdmin"))
                $data["myFacilitatorEvents"] = $eventModel->getMemberEventsForAdmin(Session::get("memberID"));

            $data["myTranslatorEvents"] = $eventModel->getMemberEvents(Session::get("memberID"), EventMembers::TRANSLATOR);
            $data["myCheckerL1Events"] = $eventModel->getMemberEventsForChecker(Session::get("memberID"));
            $data["myCheckerL2Events"] = $eventModel->getMemberEvents(Session::get("memberID"), EventMembers::L2_CHECKER);
            $data["myCheckerL3Events"] = $eventModel->getMemberEvents(Session::get("memberID"), EventMembers::L3_CHECKER);

            $data["notifications"] = $this->_notifications;
            View::renderTemplate('header', $data);
            View::render('members/index', $data);
            View::renderTemplate('footer', $data);
        }
        else
        {
            // Registration
            $data['title'] = $this->language->get('signup');

            if (isset($_POST['submit']))
            {
                $_POST = Gump::xss_clean($_POST);

                $_POST = Gump::filter_input($_POST, array(
                    'userName' => 'trim',
                    'firstName' => 'trim',
                    'lastName' => 'trim',
                    'email' => 'trim',
                    'password' => 'trim'
                ));

                $userName = $_POST['userName'];
                $firstName = $_POST['firstName'];
                $lastName = $_POST['lastName'];
                $email = $_POST['email'];
                $password = $_POST['password'];
                $passwordConfirm = $_POST['passwordConfirm'];
                //$userType = $_POST['userType'];
                $tou = (int)$_POST['tou'] == 1;
                $sof = (int)$_POST['sof'] == 1;

                if(!preg_match("/^[a-z]+[a-z0-9]*$/i", $userName))
                {
                    $error[] = $this->language->get('userName_characters_error');
                }

                if (strlen($userName) < 5 || strlen($userName) > 20)
                {
                    $error[] = $this->language->get('userName_length_error');
                }

                if (mb_strlen($firstName) < 2 || mb_strlen($firstName) > 20)
                {
                    $error[] = $this->language->get('firstName_length_error');
                }

                if (mb_strlen($lastName) < 2 || mb_strlen($lastName) > 20)
                {
                    $error[] = $this->language->get('lastName_length_error');
                }

                if (!filter_var($email, FILTER_VALIDATE_EMAIL))
                {
                    $error[] = $this->language->get('enter_valid_email_error');
                }
                else
                {
                    $check = $this->_model->getMember('memberID,userName,email', array('email' => array("=", $email), "userName" => array("=", $userName, "OR")));

                    foreach ($check as $item) {
                        if (strtolower($item->email) == strtolower($email))
                        {
                            $error[] = $this->language->get('email_taken_error');
                        }
                        if (strtolower($item->userName) == strtolower($userName))
                        {
                            $error[] = $this->language->get('username_taken_error');
                        }
                    }
                }

                if (strlen($password) < 5)
                {
                    $error[] = $this->language->get('password_short_error');
                }
                elseif ($password != $passwordConfirm)
                {
                    $error[] = $this->language->get('passwords_notmatch_error');
                }

                // local: 6Lf_dBYTAAAAAEql0Tky7_CCARCHAdUwR99TX_f1
                // remote: 6LdVdhYTAAAAAMjHKiMZLVIAmF5nZnQj-WpPGWT4
                // remote test: 6LebmSgTAAAAAJCWPkx4rH4fhJIzVNpP_RTvmsap

                $recaptcha = new ReCaptcha('6LdVdhYTAAAAAMjHKiMZLVIAmF5nZnQj-WpPGWT4');
                $resp = $recaptcha->verify($_POST['g-recaptcha-response'], $_SERVER['REMOTE_ADDR']);

                if (!$resp->isSuccess())
                {
                    $error[] = $this->language->get('captcha_wrong');
                }

                if(!$tou)
                {
                    $error[] = $this->language->get('tou_accept_error');
                }

                if(!$sof)
                {
                    $error[] = $this->language->get('sof_accept_error');
                }

                /*if (!preg_match("/^(translator|checker|both)$/", $userType))
                {
                    $error[] = $this->language->get('userType_wrong_error');
                }*/

                if (!isset($error))
                {
                    $activationToken = md5(uniqid(rand(), true));

                    $hash = Password::make($password);

                    //insert
                    $postdata = array(
                        'userName' => $userName,
                        'firstName' => $firstName,
                        'lastName' => $lastName,
                        'email' => $email,
                        'password' => $hash,
                        //'userType' => $userType,
                        'activationToken' => $activationToken,
                        //'active' => true
                    );

                    $id = $this->_model->createMember($postdata);
                    $link = DIR . "members/activate/$id/$activationToken";

                    $mail = new PhpMailer();
                    $mail->isSendmail();
                    $mail->setFrom('noreply@v-mast.com');
                    $mail->addAddress($email);
                    $mail->Subject = $this->language->get('activate_account_title');
                    $mail->msgHTML($this->language->get('activation_link_message', array($link, $link)));
                    $mail->send();

                    $msg = $this->language->get('registration_success_message');
                    Session::set("success", $msg);
                    Session::set("activation_email", $email);

                    Url::redirect('members/success');
                }
            }

            $data['csrf_token'] = Csrf::makeToken();

            View::renderTemplate('header', $data);
            View::render('members/signup', $data, $error);
            View::renderTemplate('footer', $data);
        }
    }


    public function profile()
    {
        if (!Session::get('loggedin'))
        {
            Url::redirect('members/login');
        }

        $eventModel = new EventsModel();

        $data["languages"] = $eventModel->getAllLanguages();
        $data["errors"] = array();

        $profile = Session::get("profile");
        $data["profile"] = $profile;

        if(!empty($_POST))
        {
            $_POST = Gump::xss_clean($_POST);

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

            if($langs !== null)
            {
                $languages = array();
                $langArr = array();
                foreach ($langs as $lang) {
                    $arr = explode(":", $lang);

                    if(sizeof($arr) != 3) continue;

                    $langID = preg_match("/^[a-z-]{2,12}$/", $arr[0]) ? $arr[0] : null;

                    if($langID === null || (integer)$arr[1] == 0 || (integer)$arr[2] == 0) continue;
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
                $postdata = array(
                    "mID" => Session::get("memberID"),
                    "bbl_trans_yrs" => $bbl_trans_yrs,
                    "othr_trans_yrs" => $othr_trans_yrs,
                    "bbl_knwlg_degr" => $bbl_knwlg_degr,
                    "languages" => json_encode($languages),
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
                    "church_role" => json_encode($church_role)
                );

                if(empty($profile))
                {
                    // Create new profile
                    $pID = $this->_model->createProfile($postdata);

                    if($pID)
                    {
                        $postdata["pID"] = $profile["pID"];
                        $postdata["languages"] = $langArr;
                        $postdata["mast_role"] = $mast_role;
                        $postdata["education"] = $education;
                        $postdata["ed_area"] = $ed_area;
                        $postdata["church_role"] = $church_role;

                        $profile = $postdata;

                        Session::set("profile", $profile);
                        Session::set("success", $this->language->get("update_profile_success"));

                        Url::redirect("members/profile");
                    }
                    else
                    {
                        $error[] = $this->language->get('update_profile_error');
                    }
                }
                else
                {
                    // Update profile
                    $this->_model->updateProfile($postdata, array("pID" => $profile["pID"]));

                    $postdata["pID"] = $profile["pID"];
                    $postdata["languages"] = $langArr;
                    $postdata["mast_role"] = $mast_role;
                    $postdata["education"] = $education;
                    $postdata["ed_area"] = $ed_area;
                    $postdata["church_role"] = $church_role;

                    $profile = $postdata;

                    Session::set("profile", $profile);
                    Session::set("success", $this->language->get("update_profile_success"));

                    Url::redirect("members/profile");
                }
            }
            else
            {
                $error[] = $this->language->get('required_fields_empty_error');
            }
        }

        View::renderTemplate('header', $data);
        View::render('members/profile', $data, $error);
        View::renderTemplate('footer', $data);
    }


    public function activate($memberID, $activationToken)
    {
        if (Session::get('loggedin'))
        {
            Url::redirect('members');
        }

        if (($memberID > 0) && (strlen($activationToken) == 32))
        {
            $user = $this->_model->getMember('memberID,active', array(
                'memberID' => array("=", $memberID),
                'active' => array("=", false),
                'activationToken' => array("=", $activationToken)));

            if ($user[0]->memberID == 0)
            {
                $error[] = $this->language->get('no_account_error');
            }
            elseif ($user[0]->active == true)
            {
                $error[] = $this->language->get('account_activated_error');
            }
            else
            {
                $postdata = array('active' => true, 'activationToken' => null);
                $where = array('memberID' => $memberID);
                $this->_model->updateMember($postdata, $where);

                $msg = $this->language->get('account_activated_success', array(DIR . 'members/login'));
                Session::set('success', $msg);

                Session::destroy("activation_email");

                Url::redirect('members/success');
            }
        }
        else
        {
            $error[] = $this->language->get('invalid_link_error');
        }

        $data['title'] = $this->language->get('activate_account_title');

        View::renderTemplate('header', $data);
        View::render('members/activate', $data, $error);
        View::renderTemplate('footer', $data);
    }

    public function resendActivation($email)
    {
        $data["member"] = $this->_model->getMember('memberID,email,activationToken', array('email' => array("=", $email), 'active' => array("!=", 1)));
        $data["title"] = $this->language->get("resend_activation_title");

        if(!empty($data["member"]))
        {
            $link = DIR . "members/activate/".$data["member"][0]->memberID."/".$data["member"][0]->activationToken;

            $mail = new PhpMailer();
            $mail->isSendmail();
            $mail->setFrom('noreply@v-mast.com');
            $mail->addAddress($email);
            $mail->Subject = $this->language->get('activate_account_title');
            $mail->msgHTML($this->language->get('activation_link_message', array($link, $link)));
            $mail->send();

            $msg = $this->language->get('resend_activation_success_message');
            Session::set('success', $msg);
        }
        else
        {
            $error[] = $this->language->get("wrong_activation_email");
        }

        View::renderTemplate('header', $data);
        View::render('members/email_activation', $data, $error);
        View::renderTemplate('footer', $data);
    }

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

            $loginTry = Session::get('loginTry');
            if($loginTry == null) $loginTry = 0;
            $loginTry++;

            Session::set('loginTry', $loginTry);

            if($loginTry >= 3)
            {
                if($loginTry > 3)
                {
                    // local: 6Lf_dBYTAAAAAEql0Tky7_CCARCHAdUwR99TX_f1
                    // remote: 6LdVdhYTAAAAAMjHKiMZLVIAmF5nZnQj-WpPGWT4

                    $recaptcha = new ReCaptcha('6LdVdhYTAAAAAMjHKiMZLVIAmF5nZnQj-WpPGWT4');
                    $resp = $recaptcha->verify($_POST['g-recaptcha-response'], $_SERVER['REMOTE_ADDR']);

                    if (!$resp->isSuccess())
                    {
                        $error[] = $this->language->get('captcha_wrong');
                    }
                }
            }

            if(!isset($error))
            {
                $data = $this->_model->getMemberWithProfile($_POST['email']);

                if (Password::verify($_POST['password'], $data[0]->password))
                {
                    $authToken = md5(uniqid(rand(), true));
                    $updated = $this->_model->updateMember(array('authToken' => $authToken), array('memberID' => $data[0]->memberID));

                    if($updated === 1)
                    {
                        $profile = array();
                        if($data[0]->pID != null)
                        {
                            $profile["pID"] = $data[0]->pID;
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

                            $arr = (array)json_decode($data[0]->languages, true);
                            $languages = array();
                            foreach ($arr as $i => $item) {
                                $languages[$i]["lang_fluency"] = $item[0];
                                $languages[$i]["geo_lang_yrs"] = $item[1];
                            }
                            $profile["languages"] = $languages;

                            $adminMember = $this->_model->getAdminMember($data[0]->memberID);
                            foreach ($adminMember as $item) {
                                if(!array_key_exists($item->gwLang, $profile["languages"]))
                                {
                                    $profile["languages"][$item->gwLang] = array("isAdmin" => true);
                                }
                            }
                        }

                        Session::set('memberID', $data[0]->memberID);
                        Session::set('userName', $data[0]->userName);
                        Session::set('firstName', $data[0]->firstName);
                        Session::set('lastName', $data[0]->lastName);
                        Session::set('authToken', $authToken);
                        Session::set('verified', $data[0]->verified);
                        Session::set('isAdmin', $data[0]->isAdmin == 1);
                        Session::set('isSuperAdmin', $data[0]->isSuperAdmin == 1);
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
                        $error[] = $this->language->get('user_login_error');
                    }
                }
                else
                {
                    $error[] = $this->language->get('wrong_credentials_error');
                }
            }
        }

        $data['csrf_token'] = Csrf::makeToken();

        $data['title'] = $this->language->get('login_title');
        $data['lang'] = $this->_lang;

        View::renderTemplate('header', $data);
        View::render('members/login', $data, $error);
        View::renderTemplate('footer', $data);
    }

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

            $data = $this->_model->getMember('memberID,email', array('email' => array("=", $email)));

            $recaptcha = new ReCaptcha('6LdVdhYTAAAAAMjHKiMZLVIAmF5nZnQj-WpPGWT4');
            $resp = $recaptcha->verify($_POST['g-recaptcha-response'], $_SERVER['REMOTE_ADDR']);

            if (!$resp->isSuccess())
            {
                $error[] = $this->language->get('captcha_wrong');
            }

            if(!isset($error))
            {
                if($data[0]->email)
                {
                    $resetToken = md5(uniqid(rand(), true));
                    $postdata = array('resetToken' => $resetToken, 'resetDate' => date('Y-m-d H:i:s',time()));
                    $where = array('email' => $email);
                    $this->_model->updateMember($postdata, $where);

                    $link = DIR."members/resetpassword/".$data[0]->memberID."/$resetToken";

				    $mail = new PhpMailer();
                    $mail->isSendmail();
                    $mail->setFrom('noreply@v-mast.com');
                    $mail->addAddress($email);
                    $mail->Subject = $this->language->get('passwordreset_title');
                    $mail->msgHTML($this->language->get('passwordreset_link_message', array($link, $link)));
                    $mail->send();
					
                    $msg = $this->language->get('pwresettoken_send_success');
                    Session::set('success', $msg);

                    Url::redirect('members/success');
                }
                else
                {
                    $error[] = $this->language->get('enter_valid_email_error');
                }
            }
        }

        $data['csrf_token'] = Csrf::makeToken();

        $data['title'] = $this->language->get('passwordreset_title');
        $data['lang'] = $this->_lang;

        View::renderTemplate('header', $data);
        View::render('members/passwordreset', $data, $error);
        View::renderTemplate('footer', $data);
    }

    public function resetPassword($memberID, $resetToken)
    {
        if (Session::get('loggedin'))
        {
            Url::redirect('members');
        }

        $data['step'] = 1;

        if (($memberID > 0) && (strlen($resetToken) == 32))
        {
            $user = $this->_model->getMember('memberID,resetDate', array(
                'memberID' => array("=", $memberID),
                'resetToken' => array("=", $resetToken)));

            if ($user[0]->memberID == 0)
            {
                $error[] = $this->language->get('no_account_error');
            }
            elseif((time() - strtotime($user[0]->resetDate) > (60*60*24*3)))
            {
                $error[] = $this->language->get('token_expired_error');
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
                        Url::redirect(DIR."members/resetpassword/$memberID/$resetToken");
                    }

                    $_POST = Gump::filter_input($_POST, array(
                        'password' => 'trim'
                    ));

                    $password = $_POST['password'];
                    $passwordConfirm = $_POST['passwordConfirm'];

                    if (strlen($password) < 5)
                    {
                        $error[] = $this->language->get('password_short_error');
                    }
                    elseif ($password != $passwordConfirm)
                    {
                        $error[] = $this->language->get('passwords_notmatch_error');
                    }

                    if (!isset($error))
                    {
                        $postdata = array('password' => Password::make($password), 'resetToken' => null);
                        $where = array('memberID' => $memberID);
                        $this->_model->updateMember($postdata, $where);

                        $msg = $this->language->get('password_reset_success', array(DIR . 'members/login'));
                        Session::set('success', $msg);

                        Url::redirect('members/success');
                    }
                }
            }
        }
        else
        {
            $error[] = $this->language->get('invalid_link_error');
        }

        $data['title'] = $this->language->get('passwordreset_title');
        $data['lang'] = $this->_lang;

        $data['csrf_token'] = Csrf::makeToken();

        View::renderTemplate('header', $data);
        View::render('members/resetpassword', $data, $error);
        View::renderTemplate('footer', $data);
    }

    /**
     * Make rpc call from nodejs and send json string back
     * @param $memberID
     * @param $authToken
     */
    public function rpcAuth($memberID, $eventID, $authToken) {

        $eventsModel = new \Models\EventsModel();

        $event = $eventsModel->getEventMember($eventID, $memberID);

        if(!empty($event))
        {
            $member = $this->_model->getMember('memberID, userName, firstName, lastName, verified, isAdmin', array(
                'memberID' => array("=", $memberID),
                'authToken' => array("=", $authToken)
            ));

            $isAdmin = 0;

            if(!empty($member))
            {
                if($event[0]->translator == null && $event[0]->checker == null)
                {

                    if($member[0]->isAdmin)
                    {
                        $admin = $this->_model->getAdminMember($member[0]->memberID);

                        foreach ($admin as $item) {
                            if($item->gwLang == $event[0]->gwLang)
                            {
                                $isAdmin = true;
                                break;
                            }
                        }
                    }

                    if(!$isAdmin)
                    {
                        echo json_encode(array());
                        return;
                    }
                }

                if($member[0]->isAdmin && !$isAdmin)
                {
                    $admin = $this->_model->getAdminMember($member[0]->memberID);

                    foreach ($admin as $item) {
                        if($item->gwLang == $event[0]->gwLang)
                        {
                            $isAdmin = true;
                            break;
                        }
                    }
                }

                $member[0]->isAdmin = $isAdmin;
                $member[0]->cotrMemberID = $event[0]->cotrMemberID;
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

    public function success()
    {
        $data['title'] = $this->language->get('success');

        View::renderTemplate('header', $data);
        View::render('members/success', $data);
        View::renderTemplate('footer', $data);
    }

    public function logout()
    {
        Session::destroy();
        Url::redirect('/', true);
    }
}