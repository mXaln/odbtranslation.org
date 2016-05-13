<?php
namespace Controllers;

use Core\View;
use Core\Controller;
use Helpers\Data;
use Helpers\Session;
use Helpers\Url;

/**
 * Sample controller showing a construct and 2 methods and their typical usage.
 */
class MainController extends Controller
{
    private $_lang;

    /**
     * Call the parent construct
     */
    public function __construct()
    {
        parent::__construct();
        $this->_lang = isset($_COOKIE['lang']) ? $_COOKIE['lang'] : 'en';
        $this->language->load('Main', $this->_lang);
    }

    /**
     * Define Index page title and load template files
     */
    public function index()
    {
        if(Session::get('loggedin'))
        {
            if(empty(Session::get("profile")))
            {
                Url::redirect("members/profile");
            }

            Url::redirect("members");
        }

        $data['title'] = $this->language->get('welcome_text');
        $data['menu'] = 1;
        $data['welcome_message'] = $this->language->get('welcome_message');

        View::renderTemplate('header', $data);
        View::render('main/index', $data);
        View::renderTemplate('footer', $data);
    }

    /**
     * About Us Page
     */
    public function about()
    {
        $data['title'] = $this->language->get('about_title');
        $data['menu'] = 6;
        $data['welcome_message'] = $this->language->get('welcome_message');

        View::renderTemplate('header', $data);
        View::render('main/about', $data);
        View::renderTemplate('footer', $data);
    }

    /**
     * Contact Us page
     */
    public function contactUs()
    {
        $data['title'] = $this->language->get('contact_us_title');
        $data['menu'] = 5;
        $data['welcome_message'] = $this->language->get('welcome_message');

        View::renderTemplate('header', $data);
        View::render('main/contact_us', $data);
        View::renderTemplate('footer', $data);
    }

    /**
     * Change locale of the site
     */
    public function lang($lang)
    {
        switch($lang)
        {
            case 'ru':
                setcookie('lang', 'ru', time()+60*60*24*1000, '/');
                break;

            default:
                setcookie('lang', 'en', time()+60*60*24*1000, '/');
                break;

        }

        Url::previous();
    }
}
