<?php
namespace App\Controllers;

use View;
use App\Core\Controller;
use Helpers\Data;
use Helpers\Session;
use Helpers\Url;

/**
 * Sample controller showing a construct and 2 methods and their typical usage.
 */
class MainController extends Controller
{
    /**
     * Call the parent construct
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function before()
    {
        return parent::before();
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

            Url::redirect("events");
        }

        $data['menu'] = 1;
        $data["isMain"] = true;

        return View::make('Main/Index')
            ->shares("title", __("welcome_text"))
            ->shares("data", $data);
    }

    /**
     * About Us Page
     */
    public function about()
    {
        $data['title'] = __('about_title');
        $data['menu'] = 6;

        return View::make('Main/About')
            ->shares("title", __("about_title"))
            ->shares("data", $data);
    }

    /**
     * Contact Us page
     */
    public function contactUs()
    {
        $data['menu'] = 5;

        return View::make('Main/ContactUs')
            ->shares("title", __("contact_us_title"))
            ->shares("data", $data);
    }
}
