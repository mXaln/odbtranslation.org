<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\TranslationsModel;
use App\Models\EventsModel;
use Helpers\Constants\EventMembers;
use Shared\Legacy\Error;
use View;
use Config\Config;
use Helpers\Session;
use Helpers\Url;
use Helpers\Parsedown;
use Helpers\ZipStream\ZipStream;

class TranslationsController extends Controller
{
    private $_model;
    private $_eventModel;

    public function __construct()
    {
        parent::__construct();

        if(Config::get("app.isMaintenance")
            && !in_array($_SERVER['REMOTE_ADDR'], Config::get("app.ips")))
        {
            Url::redirect("maintenance");
        }

        $this->_model = new TranslationsModel();
        $this->_eventModel = new EventsModel();

        if (!Session::get('loggedin'))
        {
            Url::redirect('members/login');
        }

        if(!Session::get("verified"))
        {
            Url::redirect("members/error/verification");
        }

        if(Session::get("isDemo"))
            Url::redirect('events/demo');

        if(empty(Session::get("profile")))
        {
            Url::redirect("members/profile");
        }
    }

    public function index($lang = null, $bookProject = null, $bookCode = null)
    {
        $data['menu'] = 3;

        if($lang == null)
        {
            $data['title'] = __('choose_language');
            $data["languages"] = $this->_model->getTranslationLanguages();
        }
        else if($bookProject == null)
        {
            $data['title'] = __('choose_book');
            $data['bookProjects'] = $this->_model->getTranslationProjects($lang);
        }
        elseif($bookCode == null)
        {
            $data['title'] = __('choose_book');
            $data['books'] = $this->_model->getTranslationBooks($lang, $bookProject);
            $data["mode"] = "bible";

            if(sizeof($data['books']) > 0)
            {
                $data["mode"] = $data['books'][0]->bookProject;
            }
        }
        else
        {
            $book = $this->_model->getTranslation($lang, $bookProject, $bookCode);

            if(!empty($book))
            {
                $data["data"] = $book[0];
                $data['title'] = $data['data']->bookName;
                $data['book'] = "";
                $data["mode"] = "bible";
                $lastChapter = -1;
                $chapter = [];

                foreach ($book as $chunk) {
                    $verses = json_decode($chunk->translatedVerses);

                    if($verses == null) continue;

                    if($chunk->chapter != $lastChapter)
                    {
                        $lastChapter = $chunk->chapter;

                        $chapters = $this->_eventModel->getChapters(
                            $chunk->eventID,
                            null,
                            $chunk->chapter
                        );
                        $chapter = $chapters[0];

                        $level = "";
                        if(!in_array($chunk->bookProject, ["tn","tq","tw"]))
                        {
                            $level = " [".($chapter["l2checked"] ? "L2" : "L1")."]";
                        }

                        $data['book'] .= $chunk->bookProject != "tw" ? ($chunk->chapter > 0
                            ? '<h2 class="chapter_title">'.__("chapter", [$chunk->chapter]).$level.'</h2>'
                            : '<h2 class="chapter_title">'.__("front").'</h2>') : "";
                    }

                    // Start of chunk
                    $data['book'] .= '<p>';

                    if(!in_array($chunk->bookProject, ["tn","tq","tw"]))
                    {
                        if(!empty($verses->{EventMembers::L3_CHECKER}->verses))
                        {
                            foreach ($verses->{EventMembers::L3_CHECKER}->verses as $verse => $text) {
                                $data['book'] .= '<strong><sup>'.$verse.'</sup></strong> '.$text." ";
                            }
                        }
                        elseif (!empty($verses->{EventMembers::L2_CHECKER}->verses))
                        {
                            foreach ($verses->{EventMembers::L2_CHECKER}->verses as $verse => $text) {
                                $data['book'] .= '<strong><sup>'.$verse.'</sup></strong> '.$text." ";
                            }
                        }
                        else
                        {
                            foreach ($verses->{EventMembers::TRANSLATOR}->verses as $verse => $text) {
                                $data['book'] .= '<strong><sup>'.$verse.'</sup></strong> '.$text." ";
                            }
                        }
                    }
                    else
                    {
                        $chunks = (array)json_decode($chapter["chunks"], true);
                        $currChunk = isset($chunks[$chunk->chunk]) ? $chunks[$chunk->chunk] : 1;

                        $versesLabel = "";
                        if($chunk->bookProject != "tw")
                        {
                            if($currChunk[0] != $currChunk[sizeof($currChunk)-1])
                                $versesLabel = __("chunk_verses", $currChunk[0] . "-" . $currChunk[sizeof($currChunk)-1]);
                            else
                                if($currChunk[0] == 0)
                                    $versesLabel = __("intro");
                                else
                                    $versesLabel = __("chunk_verses", $currChunk[0]);
                        }

                        $data["mode"] = $chunk->bookProject;

                        $parsedown = new Parsedown();

                        if($chunk->bookProject == "tn")
                            $text = $parsedown->text($verses->checker->verses);
                        else
                            $text = $parsedown->text($verses->translator->verses);

                        $data['book'] .= '<br><strong class="note_chunk_verses">'.$versesLabel.'</strong> '.$text." ";
                    }

                    // End of chunk
                    $data['book'] .= '</p>';
                }
            }
        }

        return View::make('Translations/Index')
            ->shares("title", __("translations"))
            ->shares("data", $data);
    }

    public function getUsfm($lang, $bookProject, $bookCode)
    {
        if($lang != null && $bookProject != null && $bookCode != null)
        {
            // If bookcode is "dl" then include all the books in archive
            $bookCode = $bookCode != "dl" ? $bookCode : null;

            $books = $this->_model->getTranslation($lang, $bookProject, $bookCode);

            if(!empty($books) && isset($books[0]))
            {
                $usfm_books = [];
                $lastChapter = 0;
                $lastCode = null;
                $chapterStarted = false;

                foreach ($books as $chunk) {
                    $code = sprintf('%02d', $chunk->abbrID)."-".strtoupper($chunk->bookCode);

                    if($code != $lastCode)
                    {
                        $lastChapter = 0;
                        $chapterStarted = false;
                    }

                    if(!isset($usfm_books[$code]))
                    {
                        $usfm_books[$code] = "\\id ".strtoupper($chunk->bookCode)." ".__($chunk->bookProject)."\n";
                        $usfm_books[$code] .= "\\ide UTF-8 \n";
                        $usfm_books[$code] .= "\\h ".mb_strtoupper(__($chunk->bookCode))."\n";
                        $usfm_books[$code] .= "\\toc1 ".__($chunk->bookCode)."\n";
                        $usfm_books[$code] .= "\\toc2 ".__($chunk->bookCode)."\n";
                        $usfm_books[$code] .= "\\toc3 ".ucfirst($chunk->bookCode)."\n";
                        $usfm_books[$code] .= "\\mt1 ".mb_strtoupper(__($chunk->bookCode))."\n\n\n\n";
                    }

                    $verses = json_decode($chunk->translatedVerses);

                    if($chunk->chapter != $lastChapter)
                    {
                        $usfm_books[$code] .= "\\s5 \n";
                        $usfm_books[$code] .= "\\c ".$chunk->chapter." \n";
                        $usfm_books[$code] .= "\\p \n";

                        $lastChapter = $chunk->chapter;
                        $chapterStarted = true;
                    }

                    // Start of chunk
                    if(!$chapterStarted)
                        $usfm_books[$code] .= "\\s5 \n";

                    $chapterStarted = false;

                    if(!empty($verses->{EventMembers::L3_CHECKER}->verses))
                    {
                        foreach ($verses->{EventMembers::L3_CHECKER}->verses as $verse => $text) {
                            $usfm_books[$code] .= "\\v ".$verse." ".html_entity_decode($text, ENT_QUOTES)."\n";
                        }
                    }
                    elseif (!empty($verses->{EventMembers::L2_CHECKER}->verses))
                    {
                        foreach ($verses->{EventMembers::L2_CHECKER}->verses as $verse => $text) {
                            $usfm_books[$code] .= "\\v ".$verse." ".html_entity_decode($text, ENT_QUOTES)."\n";
                        }
                    }
                    else
                    {
                        foreach ($verses->{EventMembers::TRANSLATOR}->verses as $verse => $text) {
                            $usfm_books[$code] .= "\\v ".$verse." ".html_entity_decode($text, ENT_QUOTES)."\n";
                        }
                    }

                    // End of chunk
                    $usfm_books[$code] .= "\n\n";

                    $lastCode = $code;
                }

                if($bookCode)
                {
                    $filename = array_keys($usfm_books)[0];

                    header('Content-type: text/plain');
                    header("Content-Disposition: attachment; filename=".$filename.".usfm");
                    echo $usfm_books[$filename];
                }
                else
                {
                    $zip = new ZipStream($books[0]->targetLang . "_" . $bookProject . ".zip");

                    foreach ($usfm_books as $filename => $content)
                    {
                        $filePath = $filename.".usfm";
                        $zip->addFile($filePath, $content);
                    }

                    $zip->finish();
                }
            }
            else
            {
                echo "An error occurred";
            }
        }
    }

    public function getMd($lang, $bookProject, $bookCode)
    {
        if($lang != null && $bookProject != null && $bookCode != null)
        {
            // If bookcode is "dl" then include all the books in archive
            $bookCode = $bookCode != "dl" ? $bookCode : null;

            $books = $this->_model->getTranslation($lang, $bookProject, $bookCode);
            $lastChapter = -1;
            $chapter = [];

            if(!empty($books) && isset($books[0]))
            {
                $zip = new ZipStream($bookProject . ".zip");
                $root = "".$books[0]->targetLang."_" . $bookProject;

                foreach ($books as $chunk) {
                    $verses = json_decode($chunk->translatedVerses);

                    if($chunk->chapter != $lastChapter)
                    {
                        $lastChapter = $chunk->chapter;

                        $chapters = $this->_eventModel->getChapters(
                            $chunk->eventID,
                            null,
                            $chunk->chapter
                        );
                        $chapter = $chapters[0];
                    }

                    $chunks = (array)json_decode($chapter["chunks"], true);
                    $currChunk = isset($chunks[$chunk->chunk]) ? $chunks[$chunk->chunk] : 1;

                    $bookPath = $chunk->bookCode;
                    $chapPath = $chunk->chapter > 0 ? sprintf("%02d", $chunk->chapter) : "front";
                    $chunkPath = $currChunk[0] > 0 ? sprintf("%02d", $currChunk[0]) : "intro";
                    $filePath = $root. "/" . $bookPath . "/" . $chapPath . "/" . $chunkPath . ".md";

                    if($bookProject == "tn")
                        $text = $verses->checker->verses;
                    else
                        $text = $verses->translator->verses;

                    $zip->addFile($filePath, $text);
                }

                $zip->finish();
            }
        }

        echo "An error ocurred! Contact administrator.";
    }

    public function getMdTw($lang, $bookCode)
    {
        if($lang != null && $bookCode != null)
        {
            // If bookcode is "dl" then include all the books in archive
            $bookCode = $bookCode != "dl" ? $bookCode : null;

            $books = $this->_model->getTranslation($lang, "tw", $bookCode);
            $lastChapter = -1;
            $chapter = [];

            if(!empty($books) && isset($books[0]))
            {
                $zip = new ZipStream("tw.zip");
                $root = $books[0]->targetLang."_tw/bible";

                foreach ($books as $chunk) {
                    $verses = json_decode($chunk->translatedVerses);
                    $words = (array) json_decode($chunk->words, true);

                    $currWord = isset($words[$chunk->chunk]) ? $words[$chunk->chunk] : null;

                    if(!$currWord) continue;

                    $bookPath = $chunk->bookName;
                    $chunkPath = $currWord;
                    $filePath = $root. "/" . $bookPath ."/". $chunkPath.".md";

                    $text = $verses->translator->verses;

                    $zip->addFile($filePath, $text);
                }

                $zip->finish();
            }
        }

        echo "An error ocurred! Contact administrator.";
    }
}