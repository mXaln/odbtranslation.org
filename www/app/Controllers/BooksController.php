<?php
namespace Controllers;

use Core\Controller;
use Core\View;
use Helpers\Data;
use Helpers\Gump;
use Helpers\Session;
use Helpers\Url;

class BooksController extends Controller
{
    private $_model;
    private $_lang;

    public function __construct()
    {
        parent::__construct();
        $this->_lang = isset($_COOKIE['lang']) ? $_COOKIE['lang'] : 'en';
        $this->language->load('Books', $this->_lang);
        $this->_model = new \Models\BooksModel();
    }

    public function index($gl = null, $bookProject = null, $bookID = null)
    {
        if (!Session::get('loggedin'))
        {
            Url::redirect('members/login');
        }

        $data['menu'] = 2;

        if($gl == null)
        {
            $data['title'] = $this->language->get('Choose gateway language');
            $data['gl'] = array('en','ru');
        }
        elseif($bookProject == null)
        {
            $data['title'] = $this->language->get('Choose book type');
            $data['bookProject'] = array('udb', 'ulb');
        }
        elseif($bookID == null)
        {
            $data['title'] = $this->language->get('Choose book');
            $data['books'] = $this->_model->getBook('DISTINCT(bookName),bookID,bookProject', array('bookProject' => array('=', $bookProject)));
        }
        else
        {
            $translationModel = new \Models\TranslationsModel();

            $data['title'] = $this->language->get('Choose verses');
            $data['verses'] = $this->_model->getBook('*', array(
                'bookID' => array('=', $bookID),
                'bookProject' => array('=', $bookProject)));

            $translatedVerses = $translationModel->getTranslation('translatedVerses', array(
                'bID' => array('=', $data['verses'][0]->bID)
            ));

            $array = array();

            foreach ($translatedVerses as $translatedVerse) {
                $array += json_decode($translatedVerse->translatedVerses, true);
            }

            $data['translatedVerses'] = $array;

            if(isset($_POST['submit']))
            {
                $_POST = Gump::xss_clean($_POST);

                if(!empty($_POST['verses'])) {
                    $jsonVerses = array();
                    $testVerse = $_POST['verses'][0] - 1;

                    foreach ($_POST['verses'] as $verse) {
                        if (($verse - $testVerse) > 1) {
                            $error[] = "Wrong order";
                            break;
                        }

                        $testVerse = $verse;

                        if (isset($array[$verse])) {
                            $error[] = "Some of chosen verses are already being translated";
                            break;
                        }

                        $v = array('text' => '', 'comment' => '');
                        $jsonVerses[$verse] = $v;
                    }

                    if (!isset($error)) {
                        $postdata = array(
                            'memberID' => Session::get('memberID'),
                            'bID' => $data['verses'][0]->bID,
                            'translatedVerses' => json_encode($jsonVerses)
                        );

                        $tID = $translationModel->createTranslation($postdata);

                        if (!$tID) {
                            $error[] = 'Translation create error';
                        } else {
                            Url::redirect("members");
                        }
                    }
                }
                else
                {
                    $error[] = 'Choose at least 1 verse to start translation';
                }
            }
        }

        View::renderTemplate('header', $data);
        View::render('books/index', $data, $error);
        View::renderTemplate('footer', $data);
    }
}