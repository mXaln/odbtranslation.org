<?php
namespace App\Controllers;

use App\Models\EventsModel;
use Helpers\Csrf;
use Helpers\Gump;
use View;
use App\Core\Controller;
use Helpers\Session;
use Helpers\Url;
use Config\Config;
use Mailer;

/**
 * Sample controller showing a construct and 2 methods and their typical usage.
 */
class MainController extends Controller
{
    private $_eventModel;

    /**
     * Call the parent construct
     */
    public function __construct()
    {
        parent::__construct();

        $this->_eventModel = new EventsModel();
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
        if(Config::get("app.isMaintenance")
            && !in_array($_SERVER['REMOTE_ADDR'], Config::get("app.ips")))
        {
            Url::redirect("maintenance");
        }

        if(Session::get('loggedin'))
        {
            if(empty(Session::get("profile")))
            {
                Url::redirect("members/profile");
            }

            Url::redirect("events");
        }

        $data['menu'] = 5;
        $data["isMain"] = true;

        return View::make('Main/Index')
            ->shares("title", __("welcome_text"))
            ->shares("data", $data);
    }

    public function maintenance()
    {
        if(!Config::get("app.isMaintenance"))
        {
            Url::redirect("/");
        }

        return View::make('Main/Maintenance')
            ->shares("title", __("maintenance_work"));
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
        $data['title'] = __('contact_us_title');
        $data["languages"] = $this->_eventModel->getAllLanguages();
        $data['csrfToken'] = Csrf::makeToken();

        $error = [];

        if (isset($_POST['submit']))
        {
            $gump = new Gump();
            $gump->validation_rules([
                'name' => 'required',
                'email' => 'required|valid_email',
                'message' => 'required|max_len,2000',
                'lang' => 'required'
            ]);

            $gump->filter_rules([
                'name' => 'trim|sanitize_string',
                'email' => 'trim|sanitize_email',
                'message' => 'trim|sanitize_string',
                'lang' => 'trim|sanitize_string'
            ]);

            $valid_data = $gump->run($_POST);

            $adminEmail = Config::get("app.email");

            if($valid_data)
            {
                if(Config::get("app.type") == "remote")
                {
                    Mailer::send(
                        'Emails/Common/NotifyContactUs',
                        [
                            "name" => $valid_data["name"],
                            "email" => $valid_data["email"],
                            "language" => $valid_data["lang"],
                            "userMessage" => $valid_data["message"]
                        ],
                        function($message) use($adminEmail)
                        {
                            $message->to($adminEmail)
                                ->subject("Contact Form Notification");
                        }
                    );
                }

                $_POST = [];
                $data["success"] = __("contact_us_successful");
            }
            else
            {
                $error[] = __("required_fields_empty_error");
            }
        }

        return View::make('Main/ContactUs')
            ->shares("title", __("contact_us_title"))
            ->shares("error", $error)
            ->shares("data", $data);
    }
}
