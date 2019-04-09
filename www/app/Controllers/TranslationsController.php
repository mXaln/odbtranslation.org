<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\TranslationsModel;
use App\Models\EventsModel;
use Helpers\Constants\EventMembers;
use Helpers\Constants\EventStates;
use Helpers\Date;
use Helpers\Manifest;
use Helpers\Spyc;
use Shared\Legacy\Error;
use View;
use Config\Config;
use Helpers\Session;
use Helpers\Url;
use Helpers\Parsedown;
use Helpers\ZipStream\ZipStream;
use File;

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

                        if(in_array($chunk->bookProject, ["tn","tq","tw"]))
                        {
                            $level = " [".($chapter["l3checked"] ? "L3" : ($chapter["checked"] ? "L2" : "L1"))."]";
                        }
                        else
                        {
                            $level = " [".($chapter["l3checked"] ? "L3" : ($chapter["l2checked"] ? "L2" : "L1"))."]";
                        }

                        $data['book'] .= $chunk->bookProject != "tw" ? ($chunk->chapter > 0
                            ? '<h2 class="chapter_title">'.__("chapter", [$chunk->chapter]).$level.'</h2>'
                            : '<h2 class="chapter_title">'.__("front").$level.'</h2>') : "";
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

                        if(!empty($verses->{EventMembers::L3_CHECKER}->verses))
                        {
                            $text = $parsedown->text($verses->{EventMembers::L3_CHECKER}->verses);
                        }
                        elseif (!empty($verses->{EventMembers::CHECKER}->verses))
                        {
                            $text = $parsedown->text($verses->{EventMembers::CHECKER}->verses);
                        }
                        else
                        {
                            $text = $parsedown->text($verses->{EventMembers::TRANSLATOR}->verses);
                        }

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
                switch ($books[0]->state)
                {
                    case EventStates::STARTED:
                    case EventStates::TRANSLATING:
                        $chk_lvl = 0;
                        break;
                    case EventStates::TRANSLATED:
                    case EventStates::L2_RECRUIT:
                    case EventStates::L2_CHECK:
                        $chk_lvl = 1;
                        break;
                    case EventStates::L2_CHECKED:
                    case EventStates::L3_RECRUIT:
                    case EventStates::L3_CHECK:
                        $chk_lvl = 2;
                        break;
                    case EventStates::COMPLETE:
                        $chk_lvl = 3;
                        break;
                    default:
                        $chk_lvl = 0;
                }

                $manifest = new Manifest();

                $manifest->setCreator("vMAST");
                $manifest->setFormat("text/usfm");
                $manifest->setIdentifier($bookProject);
                $manifest->setIssued(date("Y-m-d", time()));
                $manifest->setModified(date("Y-m-d", time()));
                $manifest->setLanguage(new Manifest\Language(
                    $books[0]->direction,
                    $books[0]->targetLang,
                    $books[0]->langName));
                $manifest->setRelation([
                    $lang."/tw",
                    $lang."/tq",
                    $lang."/tn"
                ]);
                $manifest->setSource([
                    new Manifest\Source(
                        $books[0]->sourceBible,
                        $books[0]->sourceLangID,
                        "?"
                    )
                ]);
                $manifest->setSubject("Bible");
                $manifest->setTitle(__($bookProject));
                $manifest->setType("bundle");
                $manifest->setCheckingEntity(["Wycliffe Associates"]);
                $manifest->setCheckingLevel($chk_lvl);

                // Set contrubutor list from entire project contributors
                if($bookCode == null)
                {
                    $manifest->setContributor($this->_eventModel->getProjectContributors($books[0]->projectID, false, false));
                }

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

                        // Set contributor list from book contributors
                        if($bookCode != null)
                        {
                            $contributors = $this->_eventModel->getEventContributors($chunk->eventID, $chk_lvl, $chunk->bookProject);
                            foreach ($contributors as $cat => $list)
                            {
                                if($cat == "admins") continue;
                                foreach ($list as $contributor)
                                {
                                    $manifest->addContributor($contributor["name"]);
                                }
                            }
                        }
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

                    if(!$manifest->getProject($chunk->bookCode))
                    {
                        $manifest->addProject(new Manifest\Project(
                            $chunk->bookName,
                            $chunk->sourceBible,
                            $chunk->bookCode,
                            (int)$chunk->abbrID,
                            "./".(sprintf("%02d", $chunk->abbrID))."-".(strtoupper($chunk->bookCode)).".usfm",
                            ["bible-".($chunk->abbrID < 41 ? "ot" : "nt")]
                        ));
                    }
                }

                $yaml = Spyc::YAMLDump($manifest->output(), 4, 0);

                $zip = new ZipStream($books[0]->targetLang . "_" . $bookProject . ($bookCode ? "_".$bookCode : "") . ".zip");
                foreach ($usfm_books as $filename => $content)
                {
                    $filePath = $filename.".usfm";
                    $zip->addFile($filePath, $content);
                }
                $zip->addFile("manifest.yaml", $yaml);
                $zip->finish();
            }
            else
            {
                echo "There is no such book translation.";
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
                switch ($books[0]->state)
                {
                    case EventStates::STARTED:
                        $chk_lvl = 0;
                        break;
                    case EventStates::TRANSLATING:
                        $chk_lvl = 1;
                        break;
                    case EventStates::TRANSLATED:
                    case EventStates::L3_CHECK:
                        $chk_lvl = 2;
                        break;
                    case EventStates::COMPLETE:
                        $chk_lvl = 3;
                        break;
                    default:
                        $chk_lvl = 0;
                }

                $manifest = new Manifest();

                $manifest->setCreator("vMAST");
                $manifest->setFormat("text/markdown");
                $manifest->setIdentifier($bookProject);
                $manifest->setIssued(date("Y-m-d", time()));
                $manifest->setModified(date("Y-m-d", time()));
                $manifest->setLanguage(new Manifest\Language(
                    $books[0]->direction,
                    $books[0]->targetLang,
                    $books[0]->langName));
                $manifest->setRelation([
                    $lang."/ulb",
                    $lang."/udb"
                ]);
                $manifest->setSource([
                    new Manifest\Source(
                        $bookProject,
                        $books[0]->resLangID,
                        "?"
                    )
                ]);
                $manifest->setSubject(__($bookProject));
                $manifest->setTitle(__($bookProject));
                $manifest->setType("help");
                $manifest->setCheckingEntity(["Wycliffe Associates"]);
                $manifest->setCheckingLevel($chk_lvl);

                // Set contributor list from entire project contributors
                if($bookCode == null)
                {
                    $manifest->setContributor($this->_eventModel->getProjectContributors($books[0]->projectID, false, false));
                }

                $zip = new ZipStream($books[0]->targetLang."_" . $bookProject . ($bookCode != null ? "_".$books[0]->bookCode : "") . ".zip");
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

                    if(!empty($verses->{EventMembers::L3_CHECKER}->verses))
                    {
                        $text = $verses->{EventMembers::L3_CHECKER}->verses;
                    }
                    elseif (!empty($verses->{EventMembers::CHECKER}->verses))
                    {
                        $text = $verses->{EventMembers::CHECKER}->verses;
                    }
                    else
                    {
                        $text = $verses->{EventMembers::TRANSLATOR}->verses;
                    }

                    $zip->addFile($filePath, $text);

                    if(!$manifest->getProject($chunk->bookCode))
                    {
                        // Set contributor list from book contributors
                        if($bookCode != null)
                        {
                            $contributors = $this->_eventModel->getEventContributors($chunk->eventID, $chk_lvl, $chunk->bookProject);
                            foreach ($contributors as $cat => $list)
                            {
                                if($cat == "admins") continue;
                                foreach ($list as $contributor)
                                {
                                    $manifest->addContributor($contributor["name"]);
                                }
                            }
                        }

                        $manifest->addProject(new Manifest\Project(
                            $chunk->bookName . " " . __($bookProject),
                            "",
                            $chunk->bookCode,
                            (int)$chunk->abbrID,
                            "./".$chunk->bookCode,
                            []
                        ));
                    }
                }

                $yaml = Spyc::YAMLDump($manifest->output(), 4, 0);
                $zip->addFile($root . "/manifest.yaml", $yaml);
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

            if(!empty($books) && isset($books[0]))
            {
                switch ($books[0]->state)
                {
                    case EventStates::STARTED:
                        $chk_lvl = 0;
                        break;
                    case EventStates::TRANSLATING:
                        $chk_lvl = 1;
                        break;
                    case EventStates::TRANSLATED:
                    case EventStates::L3_CHECK:
                        $chk_lvl = 2;
                        break;
                    case EventStates::COMPLETE:
                        $chk_lvl = 3;
                        break;
                    default:
                        $chk_lvl = 0;
                }

                $manifest = new Manifest();

                $manifest->setCreator("vMAST");
                $manifest->setFormat("text/markdown");
                $manifest->setIdentifier($books[0]->bookProject);
                $manifest->setIssued(date("Y-m-d", time()));
                $manifest->setModified(date("Y-m-d", time()));
                $manifest->setLanguage(new Manifest\Language(
                    $books[0]->direction,
                    $books[0]->targetLang,
                    $books[0]->langName));
                $manifest->setRelation([
                    $lang."/ulb",
                    $lang."/udb",
                    $lang."/obs",
                    $lang."/tn",
                    $lang."/tq"
                ]);
                $manifest->setSource([
                    new Manifest\Source(
                        $books[0]->bookProject,
                        $books[0]->resLangID,
                        "?"
                    )
                ]);
                $manifest->setSubject(__($books[0]->bookProject));
                $manifest->setTitle(__($books[0]->bookProject));
                $manifest->setType("dict");
                $manifest->setCheckingEntity(["Wycliffe Associates"]);
                $manifest->setCheckingLevel($chk_lvl);

                // Set contributor list from entire project contributors
                $manifest->setContributor($this->_eventModel->getProjectContributors($books[0]->projectID, false, false));
                $manifest->addProject(new Manifest\Project(
                    __($books[0]->bookProject),
                    "",
                    "bible",
                    0,
                    "./bible",
                    []
                ));

                $zip = new ZipStream($lang . "_tw" .($bookCode != null ? "_".$books[0]->bookName : ""). ".zip");
                $root = $books[0]->targetLang."_tw/bible";

                foreach ($books as $chunk) {
                    $verses = json_decode($chunk->translatedVerses);
                    $words = (array) json_decode($chunk->words, true);

                    $currWord = isset($words[$chunk->chunk]) ? $words[$chunk->chunk] : null;

                    if(!$currWord) continue;

                    $bookPath = $chunk->bookName;
                    $chunkPath = $currWord;
                    $filePath = $root. "/" . $bookPath ."/". $chunkPath.".md";

                    if(!empty($verses->{EventMembers::L3_CHECKER}->verses))
                    {
                        $text = $verses->{EventMembers::L3_CHECKER}->verses;
                    }
                    else
                    {
                        $text = $verses->{EventMembers::TRANSLATOR}->verses;
                    }

                    $zip->addFile($filePath, $text);
                }

                $yaml = Spyc::YAMLDump($manifest->output(), 4, 0);
                $zip->addFile($books[0]->targetLang."_tw/manifest.yaml", $yaml);
                $zip->finish();
            }
        }

        echo "An error ocurred! Contact administrator.";
    }


    private function makeManifestArray()
    {
        $manifest = [];
    }
}