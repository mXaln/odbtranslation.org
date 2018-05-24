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
                        if(!in_array($chunk->bookProject, ["tn"]))
                        {
                            $level = " [".($chapter["l2checked"] ? "L2" : "L1")."]";
                        }

                        $data['book'] .= $chunk->chapter > 0
                            ? '<h2 class="chapter_title">'.__("chapter", [$chunk->chapter]).$level.'</h2>'
                            : '<h2 class="chapter_title">'.__("front").'</h2>';
                    }

                    // Start of chunk
                    $data['book'] .= '<p>';

                    if(!in_array($chunk->bookProject, ["tn"]))
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

                        if($currChunk[0] != $currChunk[sizeof($currChunk)-1])
                            $versesLabel = __("chunk_verses", $currChunk[0] . "-" . $currChunk[sizeof($currChunk)-1]);
                        else
                            if($currChunk[0] == 0)
                                $versesLabel = __("intro");
                            else
                                $versesLabel = __("chunk_verses", $currChunk[0]);

                        $data["mode"] = $chunk->bookProject;

                        $parsedown = new Parsedown();
                        $text = $parsedown->text($verses->checker->verses);

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
            $book = $this->_model->getTranslation($lang, $bookProject, $bookCode);

            if(!empty($book))
            {
                $data["usfm"] = "";
                $lastChapter = 0;
                $chapterStarted = false;

                $data["usfm"] = "\\id ".strtoupper($book[0]->bookCode)." ".__($book[0]->bookProject)."\n";
                $data["usfm"] .= "\\ide UTF-8 \n";
                $data["usfm"] .= "\\h ".mb_strtoupper(__($book[0]->bookCode))."\n";
                $data["usfm"] .= "\\toc1 ".__($book[0]->bookCode)."\n";
                $data["usfm"] .= "\\toc2 ".__($book[0]->bookCode)."\n";
                $data["usfm"] .= "\\toc3 ".ucfirst($book[0]->bookCode)."\n";
                $data["usfm"] .= "\\mt1 ".mb_strtoupper(__($book[0]->bookCode))."\n\n\n\n";

                foreach ($book as $chunk) {
                    $verses = json_decode($chunk->translatedVerses);

                    if($chunk->chapter != $lastChapter)
                    {
                        $data["usfm"] .= "\\s5 \n";
                        $data["usfm"] .= "\\c ".$chunk->chapter." \n";
                        $data["usfm"] .= "\\p \n";

                        $lastChapter = $chunk->chapter;
                        $chapterStarted = true;
                    }

                    // Start of chunk
                    if(!$chapterStarted)
                        $data["usfm"] .= "\\s5 \n";

                    $chapterStarted = false;

                    if(!empty($verses->{EventMembers::L3_CHECKER}->verses))
                    {
                        foreach ($verses->{EventMembers::L3_CHECKER}->verses as $verse => $text) {
                            $data["usfm"] .= "\\v ".$verse." ".html_entity_decode($text, ENT_QUOTES)."\n";
                        }
                    }
                    elseif (!empty($verses->{EventMembers::L2_CHECKER}->verses))
                    {
                        foreach ($verses->{EventMembers::L2_CHECKER}->verses as $verse => $text) {
                            $data["usfm"] .= "\\v ".$verse." ".html_entity_decode($text, ENT_QUOTES)."\n";
                        }
                    }
                    else
                    {
                        foreach ($verses->{EventMembers::TRANSLATOR}->verses as $verse => $text) {
                            $data["usfm"] .= "\\v ".$verse." ".html_entity_decode($text, ENT_QUOTES)."\n";
                        }
                    }

                    // End of chunk
                    $data["usfm"] .= "\n\n";
                }

                header('Content-type: text/plain');
                header("Content-Disposition: attachment; filename=".$book[0]->abbrID."-".strtoupper($book[0]->bookCode).".usfm");
                echo $data["usfm"];
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
            $book = $this->_model->getTranslation($lang, $bookProject, $bookCode);
            $lastChapter = -1;
            $chapter = [];

            if(!empty($book) && isset($book[0]))
            {
                $zip = new ZipStream("tn.zip");
                $root = "".$book[0]->targetLang."_tn/".$book[0]->bookCode;

                foreach ($book as $chunk) {
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
                    
                    $chapPath = $chunk->chapter > 0 ? sprintf("%02d", $chunk->chapter) : "front";
                    $chunkPath = $currChunk[0] > 0 ? sprintf("%02d", $currChunk[0]) : "intro";
                    $filePath = $root."/".$chapPath."/".$chunkPath.".md";

                    $text = $verses->checker->verses;

                    $zip->addFile($filePath, $text);
                }

                $zip->finish();
            }
        }

        echo "An error ocurred! Contact administrator.";
    }
}