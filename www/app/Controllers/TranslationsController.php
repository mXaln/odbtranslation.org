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

    public function index($bookProject = null, $bookID = null, $chapter = null)
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

        if($bookProject == null)
        {
            $data['title'] = $this->language->get('Choose book type');
            $data['bookProject'] = array('udb', 'ulb');
        }
        elseif($bookID == null)
        {
            $data['title'] = $this->language->get('Choose book');
            $data['books'] = $this->_model->getTranslationWithBook(PREFIX.'books.bookID,'.PREFIX.'books.bookName,'.PREFIX.'books.bookProject', array(
                PREFIX.'books.bookProject' => array('=', $bookProject)
            ), 'books.bookID');
        }
        elseif($chapter == null)
        {
            $data['title'] = $this->language->get('Choose chapter');
            $data['chapters'] = $this->_model->getTranslationWithBook(PREFIX.'books.bookID,'.PREFIX.'books.bookName,'.PREFIX.'books.bookProject,'.PREFIX.'books.chapter', array(
                PREFIX.'books.bookID' => array('=', $bookID),
                PREFIX.'books.bookProject' => array('=', $bookProject)), 'books.bID');
        }
        else
        {
            $data['title'] = $this->language->get('Choose segment');
            $data['verses'] = $this->_model->getTranslationWithBook('*', array(
                PREFIX.'books.bookID' => array('=', $bookID),
                PREFIX.'books.chapter' => array('=', $chapter),
                PREFIX.'books.bookProject' => array('=', $bookProject)));
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

        $data['verses'] = $this->_model->getTranslationWithBook('*', array(
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

        $data['verses'] = $this->_model->getTranslationWithBook('*', array(
            PREFIX.'books.bookID' => array('=', $bookID),
            PREFIX.'books.chapter' => array('=', $chapter),
            PREFIX.'books.bookProject' => array('=', $bookProject)));

        View::renderTemplate('header', $data);
        View::render('translations/edit', $data, $error);
        View::renderTemplate('footer', $data);
    }
}