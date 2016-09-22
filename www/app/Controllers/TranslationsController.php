<?php
namespace Controllers;

use Core\Controller;
use Core\Language;
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
            $book = $this->_model->getTranslation($lang, $bookProject, $bookCode);
            $data["data"] = $book[0];
            $data['title'] = $data['data']->bookName;
            $data['book'] = "";
            $lastChapter = 0;

            foreach ($book as $chunk) {
                $verses = json_decode($chunk->translatedVerses);

                if($verses == null) continue;

                if($chunk->chapter != $lastChapter)
                {
                    $data['book'] .= '<h2>'.$this->language->get("chapter", array($chunk->chapter)).'</h2>';
                    $lastChapter = $chunk->chapter;
                }

                // Start of chunk
                $data['book'] .= '<p>';

                foreach ($verses->translator->verses as $verse => $text) {
                    $data['book'] .= '<strong><sup>'.$verse.'</sup></strong> '.$text." ";
                }
                // End of chunk
                $data['book'] .= '</p>';
            }
        }

        View::renderTemplate('header', $data);
        View::render('translations/index', $data);
        View::renderTemplate('footer', $data);
    }

    public function getUsfm($lang, $bookProject, $bookCode)
    {
        if($lang != null && $bookProject != null && $bookCode != null)
        {
            $book = $this->_model->getTranslation($lang, $bookProject, $bookCode);
            $data["usfm"] = "";
            $lastChapter = 0;
            $chapterStarted = false;

            $data["usfm"] = "\\id ".strtoupper($book[0]->bookCode)." ".Language::show($book[0]->bookProject, "Events", null, "en")."\n";
            $data["usfm"] .= "\\ide UTF-8 \n";
            $data["usfm"] .= "\\h ".strtoupper($book[0]->bookName)."\n";
            $data["usfm"] .= "\\toc1 ".$book[0]->bookName."\n";
            $data["usfm"] .= "\\toc2 ".$book[0]->bookName."\n";
            $data["usfm"] .= "\\toc3 ".ucfirst($book[0]->bookCode)."\n";
            $data["usfm"] .= "\\mt1 ".strtoupper($book[0]->bookName)."\n\n\n\n";

            foreach ($book as $chunk) {
                $verses = json_decode($chunk->translatedVerses);

                if($chunk->chapter != $lastChapter)
                {
                    $data["usfm"] .= "\\s5 \n";
                    $data["usfm"] .= "\\c ".$chunk->chapter." \n";

                    $lastChapter = $chunk->chapter;
                    $chapterStarted = true;
                }

                // Start of chunk
                if(!$chapterStarted)
                    $data["usfm"] .= "\\s5 \n";

                $chapterStarted = false;

                foreach ($verses->translator->verses as $verse => $text) {
                    $data["usfm"] .= "\\v ".$verse." ".html_entity_decode($text, ENT_QUOTES)."\n";
                }
                // End of chunk
                $data["usfm"] .= "\n\n";
            }

            header('Content-type: text/plain');
            header("Content-Disposition: attachment; filename=".$book[0]->abbrID."-".strtoupper($book[0]->bookCode).".usfm");
            echo $data["usfm"];
        }
    }
}