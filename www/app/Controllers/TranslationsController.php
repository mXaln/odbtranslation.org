<?php
namespace Controllers;

use Core\Controller;
use Core\View;
use Helpers\Data;
use Helpers\Gump;
use Helpers\Session;
use Helpers\Url;

class TranslationsController extends Controller
{
    private $_model;
    private $_lang;

    public function __construct()
    {
        parent::__construct();
        $this->_lang = isset($_COOKIE['lang']) ? $_COOKIE['lang'] : 'en';
        $this->language->load('Translations', $this->_lang);
        $this->_model = new \Models\TranslationsModel();
    }

    public function index($lang = null, $bookProject = null, $bookCode = null)
    {
        if (!Session::get('loggedin'))
        {
            Url::redirect('members/login');
        }

        $data['title'] = $this->language->get('Translations');
        $data['menu'] = 3;

        if(!Session::get('verified'))
        {
            $error[] = $this->language->get('verification_error');

            View::renderTemplate('header', $data);
            View::render('translations/error', $data, $error);
            View::renderTemplate('footer', $data);
            return;
        }

        if($lang == null)
        {
            $data['title'] = $this->language->get('Choose language');
            $data["languages"] = $this->_model->getTranslationLanguages();
        }
        else if($bookProject == null)
        {
            $data['title'] = $this->language->get('Choose book type');
            $data['bookProjects'] = $this->_model->getTranslationProjects($lang);
        }
        elseif($bookCode == null)
        {
            $data['title'] = $this->language->get('Choose book');
            $data['books'] = $this->_model->getTranslationBooks($lang, $bookProject);
        }
        else
        {
            $data['title'] = $this->language->get('Choose segment');

            $book = $this->_model->getTranslation($lang, $bookProject, $bookCode);
            $data["data"] = $book[0];
            $data['book'] = "";
            $lastChapter = 0;

            foreach ($book as $chunk) {
                $verses = json_decode($chunk->translatedVerses);

                if($chunk->chapter != $lastChapter)
                {
                    $data['book'] .= '<h2>Chapter '.$chunk->chapter.'</h2>';
                    $lastChapter = $chunk->chapter;
                }

                // Start of chunk
                $data['book'] .= '<p>';
                foreach ($verses->translator->verses as $verse => $text) {
                    $data['book'] .= '<strong><sup>'.$verse.'</sup></strong>'.$text." ";
                }
                // End of chunk
                $data['book'] .= '</p>';
            }
        }

        View::renderTemplate('header', $data);
        View::render('translations/index', $data);
        View::renderTemplate('footer', $data);
    }

    public function view($bookProject, $bookID, $chapter)
    {
        $data['title'] = "Transation of book";
        $error = array();

        if (!Session::get('loggedin'))
        {
            Url::redirect('members/login');
        }

        if(!Session::get('verified'))
        {
            $error[] = $this->language->get('verification_error');

            View::renderTemplate('header', $data);
            View::render('translations/error', $data, $error);
            View::renderTemplate('footer', $data);
            return;
        }

        $data['verses'] = $this->_model->getTranslationBooks('*', array(
            PREFIX.'books.bookID' => array('=', $bookID),
            PREFIX.'books.chapter' => array('=', $chapter),
            PREFIX.'books.bookProject' => array('=', $bookProject)));

        View::renderTemplate('header', $data);
        View::render('translations/view', $data, $error);
        View::renderTemplate('footer', $data);
    }

    public function edit($bookProject, $bookID, $chapter)
    {
        $data['title'] = "Transation of book";
        $error = array();

        if (!Session::get('loggedin'))
        {
            Url::redirect('members/login');
        }

        if(!Session::get('verified'))
        {
            $error[] = $this->language->get('verification_error');

            View::renderTemplate('header', $data);
            View::render('translations/error', $data, $error);
            View::renderTemplate('footer', $data);
            return;
        }

        $data['verses'] = $this->_model->getTranslationBooks('*', array(
            PREFIX.'books.bookID' => array('=', $bookID),
            PREFIX.'books.chapter' => array('=', $chapter),
            PREFIX.'books.bookProject' => array('=', $bookProject)));

        View::renderTemplate('header', $data);
        View::render('translations/edit', $data, $error);
        View::renderTemplate('footer', $data);
    }
}