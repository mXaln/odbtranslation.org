<?php
namespace Controllers;

use Core\Controller;
use Core\View;
use Helpers\Csrf;
use Helpers\Data;
use Helpers\Gump;
use Helpers\Password;
use Helpers\PhpMailer\Mail;
use Helpers\ReCaptcha\ReCaptcha;
use Helpers\Session;
use Helpers\Url;

class MembersController extends Controller
{

    private $_model;
    private $_lang;

    public function __construct()
    {
        parent::__construct();
        $this->_lang = isset($_COOKIE['lang']) ? $_COOKIE['lang'] : 'en';
        $this->language->load('Members', $this->_lang);
        $this->_model = new \Models\MembersModel();
    }

    public function index()
    {
        $data['lang'] = $this->_lang;

        if (Session::get('loggedin') == true)
        {
            $data['title'] = $this->language->get('welcome_title');

            View::renderTemplate('header', $data);
            View::render('members/index', $data);
            View::renderTemplate('footer', $data);
        }
        else
        {
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
                    $check = $this->_model->getMember('memberID,email', array('email' => array("=", $email)));
                    if (strtolower($check[0]->email) == strtolower($email))
                    {
                        $error[] = $this->language->get('email_taken_error');
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

                $recaptcha = new ReCaptcha('6Lf_dBYTAAAAAEql0Tky7_CCARCHAdUwR99TX_f1');
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
                        'activationToken' => $activationToken
                    );

                    $id = $this->_model->createMember($postdata);
                    $link = DIR . "members/activate/$id/$activationToken";

                    $mail = new Mail();
                    $mail->setFrom('noreply@v-mast.com');
                    $mail->addAddress($email);
                    $mail->subject($this->language->get('activate_account_title'));
                    $mail->body($this->language->get('activation_link_message', array($link, $link)));
                    $mail->send();

                    $msg = $this->language->get('registration_success_message');
                    Session::set('success', $msg);

                    Url::redirect('members/success');
                }
            }

            $data['csrf_token'] = Csrf::makeToken();

            View::renderTemplate('header', $data);
            View::render('members/signup', $data, $error);
            View::renderTemplate('footer', $data);
        }
    }

    public function activate($memberID, $activationToken)
    {
        if (Session::get('loggedin') == true)
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

    public function login()
    {
        if (Session::get('loggedin') == true)
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
                    $recaptcha = new ReCaptcha('6Lf_dBYTAAAAAEql0Tky7_CCARCHAdUwR99TX_f1');
                    $resp = $recaptcha->verify($_POST['g-recaptcha-response'], $_SERVER['REMOTE_ADDR']);

                    if (!$resp->isSuccess())
                    {
                        $error[] = $this->language->get('captcha_wrong');
                    }
                }
            }

            if(!isset($error))
            {
                $data = $this->_model->getMember('*', array(
                    'email' => array("=", $_POST['email']),
                    'userName' => array("=", $_POST['email'], "OR")));

                if (Password::verify($_POST['password'], $data[0]->password))
                {
                    $authToken = md5(uniqid(rand(), true));
                    $updated = $this->_model->updateMember(array('authToken' => $authToken), array('memberID' => $data[0]->memberID));

                    if($updated === 1)
                    {
                        Session::set('memberID', $data[0]->memberID);
                        Session::set('userName', $data[0]->userName);
                        Session::set('firstName', $data[0]->firstName);
                        Session::set('lastName', $data[0]->lastName);
                        Session::set('churchName', $data[0]->churchName);
                        Session::set('position', $data[0]->position);
                        Session::set('expYears', $data[0]->expYears);
                        Session::set('education', $data[0]->education);
                        Session::set('educationPlace', $data[0]->educationPlace);
                        Session::set('authToken', $authToken);
                        Session::set('verified', $data[0]->verified);
                        Session::set('isAdmin', $data[0]->isAdmin == 1);
                        Session::set('isSuperAdmin', $data[0]->isSuperAdmin == 1);
                        Session::set('loggedin', true);

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
                        $error[] = $this->language->get('update_table_error');
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
        if (Session::get('loggedin') == true)
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

            $recaptcha = new ReCaptcha('6Lf_dBYTAAAAAEql0Tky7_CCARCHAdUwR99TX_f1');
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

                    $mail = new Mail();
                    $mail->addAddress($email);
                    $mail->subject($this->language->get('passwordreset_title'));
                    $mail->body($this->language->get('passwordreset_link_message', array($link, $link)));
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
        if (Session::get('loggedin') == true)
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
    public function rpcAuth($memberID, $tID, $authToken) {

        $translationModel = new \Models\TranslationsModel();

        $translation = $translationModel->getTranslation('memberID,bID', array(
            'tID' => array('=', $tID)));

        if(!empty($translation))
        {
            $member = $this->_model->getMember('memberID,firstName,lastName,userType,verified', array(
                'memberID' => array("=", $memberID),
                'authToken' => array("=", $authToken)
            ));

            if(!empty($member))
            {
                $member[0]->bID = $translation[0]->bID;

                if($member[0]->userType == 'checker' || $member[0]->userType == 'both')
                {
                    echo json_encode($member[0]);
                }
                else
                {
                    if($translation[0]->memberID == $memberID)
                    {
                        echo json_encode($member[0]);
                    }
                }
            }
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