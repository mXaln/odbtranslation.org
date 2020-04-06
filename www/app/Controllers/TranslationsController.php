<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\ApiModel;
use App\Models\CloudModel;
use App\Models\TranslationsModel;
use App\Models\EventsModel;
use Helpers\Constants\EventMembers;
use Helpers\Constants\EventStates;
use Helpers\Constants\OdbSections;
use Helpers\Constants\RadioSections;
use Helpers\Manifest\Normal\Project;
use Helpers\ProjectFile;
use Helpers\Spyc;
use Helpers\Tools;
use Shared\Legacy\Error;
use View;
use Config\Config;
use Helpers\Session;
use Helpers\Url;
use Helpers\Parsedown;
use File;

class TranslationsController extends Controller
{
    private $_model;
    private $_eventModel;
    private $_apiModel;

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
        $this->_apiModel = new ApiModel();

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

    public function index($lang = null, $bookProject = null, $sourceBible = null, $bookCode = null)
    {
        $data['menu'] = 3;

        if($lang == null)
        {
            $data['title'] = __('choose_language');
            $data["languages"] = $this->_model->getTranslationLanguages();
        }
        else if($bookProject == null && $sourceBible == null)
        {
            $data['title'] = __('choose_book');
            $data['bookProjects'] = $this->_model->getTranslationProjects($lang);
            $data['language'] = $this->_model->getLanguageInfo($lang);
        }
        elseif($bookCode == null)
        {
            $data['title'] = __('choose_book');
            $data['books'] = $this->_model->getTranslationBooks($lang, $bookProject, $sourceBible);
            $data['language'] = $this->_model->getLanguageInfo($lang);
            $data['project'] = ["bookProject" => $bookProject, "sourceBible" => $sourceBible];
            $data["mode"] = "bible";

            if(sizeof($data['books']) > 0)
            {
                $data["mode"] = $data['books'][0]->bookProject;
            }
        }
        else
        {
            $book = $this->_model->getTranslation($lang, $bookProject, $sourceBible, $bookCode);
            $data['language'] = $this->_model->getLanguageInfo($lang);
            $data['project'] = ["bookProject" => $bookProject, "sourceBible" => $sourceBible];
            $data['bookInfo'] = $this->_model->getBookInfo($bookCode);
            $data['book'] = "";

            if(!empty($book))
            {
                $data["data"] = $book[0];
                $data['title'] = $data['data']->bookName;
                $data["mode"] = "bible";
                $lastChapter = -1;
                $chapter = [];

                $odbBook = [];
                $radioBook = [];

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

                        if($chunk->sourceBible == "odb")
                        {
                            $odbBook[$lastChapter] = [];
                        }
                        elseif ($chunk->sourceBible == "rad")
                        {
                            $radioBook[$lastChapter] = [];
                        }
                        else
                        {
                            if(in_array($chunk->bookProject, ["tn","tq","tw"]))
                            {
                                $level = " - ".($chapter["l3checked"] ? "L3" : ($chapter["checked"] ? "L2" : "L1"));
                            }
                            else
                            {
                                $level = " - ".($chapter["l3checked"] ? "L3" : ($chapter["l2checked"] ? "L2" : "L1"));
                            }

                            $data['book'] .= $chunk->bookProject != "tw" ? ($chunk->chapter > 0
                                ? '<h2 class="chapter_title">'.__("chapter", [$chunk->chapter]).$level.'</h2>'
                                : '<h2 class="chapter_title">'.__("front").$level.'</h2>') : "";
                        }
                    }

                    // Start of chunk
                    $data['book'] .= '<p>';

                    if(in_array($chunk->bookProject, ["tn","tq","tw"]))
                    {
                        $chunks = (array)json_decode($chapter["chunks"], true);
                        $currChunk = isset($chunks[$chunk->chunk]) ? $chunks[$chunk->chunk] : [$chunk->chunk];

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
                    else
                    {
                        if(!empty($verses->{EventMembers::L3_CHECKER}->verses))
                        {
                            foreach ($verses->{EventMembers::L3_CHECKER}->verses as $verse => $text) {
                                // Footnotes
                                $replacement = " <span data-toggle=\"tooltip\" data-placement=\"auto auto\" title=\"$2\" class=\"booknote mdi mdi-bookmark\"></span> ";
                                $text = preg_replace("/\\\\f[+\s]+(.*)\\\\ft[+\s]+(.*)\\\\f\\*/Uui", $replacement, $text);
                                $text = preg_replace("/\\\\[a-z0-9-]+\\s?\\\\?\\*?/", "", $text);
                                $data['book'] .= '<strong><sup>'.$verse.'</sup></strong> '.$text." ";
                            }
                        }
                        elseif (!empty($verses->{EventMembers::L2_CHECKER}->verses))
                        {
                            foreach ($verses->{EventMembers::L2_CHECKER}->verses as $verse => $text) {
                                // Footnotes
                                $replacement = " <span data-toggle=\"tooltip\" data-placement=\"auto auto\" title=\"$2\" class=\"booknote mdi mdi-bookmark\"></span> ";
                                $text = preg_replace("/\\\\f[+\s]+(.*)\\\\ft[+\s]+(.*)\\\\f\\*/Uui", $replacement, $text);
                                $text = preg_replace("/\\\\[a-z0-9-]+\\s?\\\\?\\*?/", "", $text);
                                $data['book'] .= '<strong><sup>'.$verse.'</sup></strong> '.$text." ";
                            }
                        }
                        else
                        {
                            if($chunk->bookProject == "rad")
                            {
                                $translation = isset($verses->{EventMembers::CHECKER}->verses)
                                && !empty($verses->{EventMembers::CHECKER}->verses)
                                    ? $verses->{EventMembers::CHECKER}->verses
                                    : $verses->{EventMembers::TRANSLATOR}->verses;

                                if(!is_object($translation))
                                {
                                    $radioBook[$lastChapter][RadioSections::enum(RadioSections::TITLE)] = $translation;
                                }
                                else
                                {
                                    $tmp = [];
                                    $tmp["name"] = $translation->name;
                                    $tmp["text"] = $translation->text;
                                    $radioBook[$lastChapter][RadioSections::enum(RadioSections::SPEAKERS)][] = $tmp;
                                }
                            }
                            else
                            {
                                foreach ($verses->{EventMembers::TRANSLATOR}->verses as $verse => $text) {
                                    if($chunk->sourceBible == "odb")
                                    {
                                        if($verse >= OdbSections::CONTENT)
                                        {
                                            $odbBook[$lastChapter][OdbSections::enum($verse)][] = $text;
                                        }
                                        else
                                        {
                                            $odbBook[$lastChapter][OdbSections::enum($verse)] = $text;
                                        }
                                    }
                                    else
                                    {
                                        // Footnotes
                                        $replacement = " <span data-toggle=\"tooltip\" data-placement=\"auto auto\" title=\"$2\" class=\"booknote mdi mdi-bookmark\"></span> ";
                                        $text = preg_replace("/\\\\f[+\s]+(.*)\\\\ft[+\s]+(.*)\\\\f\\*/Uui", $replacement, $text);
                                        $text = preg_replace("/\\\\[a-z0-9-]+\\s?\\\\?\\*?/", "", $text);
                                        $data['book'] .= '<strong><sup>'.$verse.'</sup></strong> '.$text." ";
                                    }
                                }
                            }
                        }
                    }

                    // End of chunk
                    $data['book'] .= '</p>';
                }

                // Render ODB book
                if(!empty($odbBook))
                {
                    foreach ($odbBook as $chapter => $topic) {
                        $data["book"] .= '<h2 class="chapter_title">'.__("devotion_number", ["devotion" => $chapter]).'</h2>';

                        if(trim($topic[OdbSections::enum(OdbSections::TITLE)]) != "")
                            $data["book"] .= '<p class="odb_section">'.$topic[OdbSections::enum(OdbSections::TITLE)].'</p>';

                        if(trim($topic[OdbSections::enum(OdbSections::PASSAGE)]) != "")
                            $data["book"] .= '<p class="odb_section">'.$topic[OdbSections::enum(OdbSections::PASSAGE)].'</p>';

                        if(trim($topic[OdbSections::enum(OdbSections::PASSAGE)]) != "")
                            $data["book"] .= '<p class="odb_section">'.$topic[OdbSections::enum(OdbSections::VERSE)].'</p>';

                        foreach ($topic[OdbSections::enum(OdbSections::CONTENT)] as $key => $p) {
                            if(trim($p) != "")
                                $data["book"] .= '<p '.($key == 0 ? 'class="odb_section"' : '').'>'.$p.'</p>';
                        }

                        if(trim($topic[OdbSections::enum(OdbSections::AUTHOR)]) != "")
                            $data["book"] .= '<p class="odb_section">'.$topic[OdbSections::enum(OdbSections::AUTHOR)].'</p>';

                        if(trim($topic[OdbSections::enum(OdbSections::BIBLE_IN_A_YEAR)]) != "")
                            $data["book"] .= '<p class="odb_section">'.$topic[OdbSections::enum(OdbSections::BIBLE_IN_A_YEAR)].'</p>';

                        if(trim($topic[OdbSections::enum(OdbSections::THOUGHT)]) != "")
                            $data["book"] .= '<p class="odb_section">'.$topic[OdbSections::enum(OdbSections::THOUGHT)].'</p>';
                    }
                }
                elseif (!empty($radioBook)) // Render Radio book
                {
                    foreach ($radioBook as $chapter => $topic) {
                        $data["book"] .= '<h2 class="chapter_title">'.__("broadcast_number", ["broadcast" => $chapter]).'</h2>';

                        if(trim($topic[RadioSections::enum(RadioSections::TITLE)]) != "")
                            $data["book"] .= '<p class="radio_section chapter_title">'.$topic[RadioSections::enum(RadioSections::TITLE)].'</p>';

                        foreach ($topic[RadioSections::enum(RadioSections::SPEAKERS)] as $p) {
                            $data["book"] .= '<div class="radio_section">';
                            foreach ($p as $key => $item) {
                                if(trim($item) != "")
                                    if($key == "name")
                                        $data["book"] .= '<p><strong>'.$item.'</strong></p>';
                                    else
                                        $data["book"] .= '<p>'.$item.'</p>';
                            }
                            $data["book"] .= "<div>";
                        }
                    }
                }
            }
        }

        return View::make('Translations/Index')
            ->shares("title", __("translations"))
            ->shares("data", $data);
    }

    public function downloadUsfm($lang, $bookProject, $sourceBible, $bookCode)
    {
        if($lang != null && $bookProject != null && $sourceBible != null && $bookCode != null)
        {
            // If bookcode is "dl" then include all the books in archive
            $bookCode = $bookCode != "dl" ? $bookCode : null;

            $books = $this->_model->getTranslation($lang, $bookProject, $sourceBible, $bookCode);

            if(!empty($books) && isset($books[0]))
            {
                $projectFiles = $this->getUsfmProjectFiles($books, $bookCode != null);
                $filename = $books[0]->targetLang . "_" . $bookProject . ($bookCode ? "_".$bookCode : "") . ".zip";
                $this->_model->generateZip($filename, $projectFiles, true);
            }
            else
            {
                echo "There is no such book translation.";
            }
        }
    }

    public function downloadTs($lang, $bookProject, $sourceBible, $bookCode)
    {
        if($lang != null && $bookProject != null && $sourceBible != null && $bookCode != null)
        {
            if($bookCode == "dl")
            {
                echo "Not Implemented!";
                exit;
            }

            $book = $this->_model->getTranslation($lang, $bookProject, $sourceBible, $bookCode);

            if(!empty($book) && isset($book[0]))
            {
                $root = $book[0]->targetLang."_".$book[0]->bookCode."_text_".$book[0]->bookProject;
                $projectFiles = $this->getTsProjectFiles($book);
                $this->_model->generateZip($root . ".tstudio", $projectFiles, true);
            }
            else
            {
                echo "There is no such book translation.";
            }
        }
    }

    public function downloadJson($lang, $bookProject, $sourceBible, $bookCode)
    {
        if($lang != null && $bookProject != null && $sourceBible != null && $bookCode != null)
        {
            // If bookcode is "dl" then include all the books in archive
            $bookCode = $bookCode != "dl" ? $bookCode : null;

            $books = $this->_model->getTranslation($lang, $bookProject, $sourceBible, $bookCode);

            if(!empty($books) && isset($books[0]))
            {
                $projectFiles = $this->getJsonProjectFiles($books, $bookCode == null);
                $filename = $books[0]->targetLang . "_" . $bookProject . ($bookCode ? "_".$bookCode : "") . ".zip";
                $this->_model->generateZip($filename, $projectFiles, true);
            }
            else
            {
                echo "There is no such book translation.";
            }
        }
    }

    public function downloadMd($lang, $bookProject, $sourceBible, $bookCode)
    {
        if($lang != null && $bookProject != null && $sourceBible != null && $bookCode != null)
        {
            // If bookcode is "dl" then include all the books in archive
            $bookCode = $bookCode != "dl" ? $bookCode : null;

            $books = $this->_model->getTranslation($lang, $bookProject, $sourceBible, $bookCode);

            if(!empty($books) && isset($books[0]))
            {
                $projectFiles = $this->getMdProjectFiles($books, $bookCode == null);
                $filename = $books[0]->targetLang."_" . $bookProject . ($bookCode != null ? "_".$books[0]->bookCode : "") . ".zip";
                $this->_model->generateZip($filename, $projectFiles, true);
            }
        }

        echo "An error occurred! Contact administrator.";
    }

    public function downloadMdTw($lang, $sourceBible, $bookCode)
    {
        if($lang != null && $sourceBible != null && $bookCode != null)
        {
            // If bookcode is "dl" then include all the books in archive
            $bookCode = $bookCode != "dl" ? $bookCode : null;

            $books = $this->_model->getTranslation($lang, "tw", $sourceBible, $bookCode);

            if(!empty($books) && isset($books[0]))
            {
                $projectFiles = $this->getMdTwProjectFiles($books);
                $filename = $lang . "_tw" .($bookCode != null ? "_".$books[0]->bookName : ""). ".zip";
                $this->_model->generateZip($filename, $projectFiles, true);
            }
        }

        echo "An error occurred! Contact administrator.";
    }


    public function export($lang, $bookProject, $sourceBible, $bookCode, $server)
    {
        $response = ["success" => false];

        // Check if user is logged in to the server
        if(Session::exists($server))
        {
            $repoName = null;
            $projectFiles = [];

            if(in_array($bookProject, ["tn", "tq", "tw"]))
            {
                $repoName = "{$lang}_{$bookCode}_{$bookProject}";
                if($bookProject == "tw")
                {
                    $books = $this->_model->getTranslation($lang, "tw", $sourceBible, $bookCode);
                    if(!empty($books) && isset($books[0]))
                    {
                        $projectFiles = $this->getMdTwProjectFiles($books, true);
                    }
                }
                else
                {
                    $books = $this->_model->getTranslation($lang, $bookProject, $sourceBible, $bookCode);
                    if(!empty($books) && isset($books[0]))
                    {
                        $projectFiles = $this->getMdProjectFiles($books, false, true);
                    }
                }
            }
            elseif (in_array($bookProject, ["ulb", "udb", "sun"]))
            {
                if($sourceBible != "odb")
                {
                    $repoName = "{$lang}_{$bookCode}_text_{$bookProject}";
                    $books = $this->_model->getTranslation($lang, $bookProject, $sourceBible, $bookCode);
                    if(!empty($books) && isset($books[0]))
                    {
                        $projectFiles = $this->getUsfmProjectFiles($books);
                    }
                }
            }

            $cloudModel = new CloudModel($server, Session::get($server)["username"], null, "", Session::get($server)["token"]);
            $result = $cloudModel->uploadRepo($repoName, $projectFiles);

            if($result["success"])
            {
                $response["success"] = true;
                $response["url"] = $result["message"]["html_url"];
            }
            else
            {
                $response["error"] = $result["message"];
            }
        }
        else
        {
            $response["authenticated"] = false;
            $response["server"] = $server;
        }

        echo json_encode($response);
    }


    private function getUsfmProjectFiles($books, $all = false)
    {
        $projectFiles = [];

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

        $manifest = $this->_model->generateManifest($books[0]);
        $manifest->setCheckingLevel($chk_lvl);

        // Set contributor list from entire project contributors
        if($all)
        {
            $manifest->setContributor(array_map(function($contributor) {
                return $contributor["fname"] . (!empty($contributor["lname"]) ? " ".$contributor["lname"] : "");
            }, $this->_eventModel->getProjectContributors($books[0]->projectID, false, false)));
        }
        else
        { // Only for specific book
            $contributors = $this->_eventModel->getEventContributors($books[0]->eventID, $manifest->getCheckingLevel(), $books[0]->bookProject, false);
            foreach ($contributors as $cat => $list)
            {
                if($cat == "admins") continue;
                foreach ($list as $contributor)
                {
                    $manifest->addContributor($contributor["fname"] . " " . $contributor["lname"]);
                }
            }
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
            {
                $usfm_books[$code] .= "\\s5\n";
                $usfm_books[$code] .= "\\p\n";
            }

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
                $manifest->addProject(new Project(
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

        foreach ($usfm_books as $filename => $content)
        {
            $filePath = $filename.".usfm";
            $projectFiles[] = ProjectFile::withContent($filePath, $content);
        }
        $projectFiles[] = ProjectFile::withContent("manifest.yaml", $yaml);

        return $projectFiles;
    }


    private function getTsProjectFiles($book)
    {
        $projectFiles = [];

        switch ($book[0]->state)
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

        $manifest = $this->_model->generateTstudioManifest($book[0]);

        // Set translators/checkers
        $contributors = $this->_eventModel->getEventContributors($book[0]->eventID, $chk_lvl, $book[0]->bookProject, false);
        foreach ($contributors as $cat => $list)
        {
            if($cat == "admins") continue;
            foreach ($list as $contributor)
            {
                $manifest->addTranslator($contributor["fname"] . " " . $contributor["lname"]);
            }
        }

        $manifest->setFinishedChunks([]);

        $packageManifest = $this->_model->generatePackageManifest($book[0]);
        $root = $packageManifest->getRoot();

        $bookChunks = $this->_apiModel->getPredefinedChunks($book[0]->bookCode, $book[0]->sourceLangID, $book[0]->sourceBible);

        foreach ($book as $chunk) {
            $verses = json_decode($chunk->translatedVerses, true);

            if(!empty($verses[EventMembers::L3_CHECKER]["verses"]))
            {
                $chunkVerses = $verses[EventMembers::L3_CHECKER]["verses"];
            }
            elseif (!empty($verses[EventMembers::L2_CHECKER]["verses"]))
            {
                $chunkVerses = $verses[EventMembers::L2_CHECKER]["verses"];
            }
            else
            {
                $chunkVerses = $verses[EventMembers::TRANSLATOR]["verses"];
            }

            foreach ($chunkVerses as $vNum => $vText)
            {
                if(array_key_exists($chunk->chapter, $bookChunks))
                {
                    foreach ($bookChunks[$chunk->chapter] as $index => $chk)
                    {
                        if(array_key_exists($vNum, $chk))
                        {
                            $bookChunks[$chunk->chapter][$index][$vNum] = "\\v $vNum ".$vText;
                        }
                    }
                }
            }
        }

        foreach ($bookChunks as $cNum => $chap)
        {
            foreach ($chap as $chk)
            {
                $format = "%02d";
                $chapPath = sprintf($format, $cNum);
                reset($chk);
                $chunkPath = sprintf($format, key($chk));
                $filePath = $root. "/" . $chapPath . "/" . $chunkPath . ".txt";

                $t = join(" ", $chk);

                $projectFiles[] = ProjectFile::withContent($filePath, $t);

                $manifest->addFinishedChunk($chapPath."-".$chunkPath);
            }
        }

        // Add git initial files
        $tmpDir = "/tmp";
        if(Tools::unzip("../app/Templates/Default/Assets/.git.zip", $tmpDir))
        {
            foreach (Tools::iterateDir($tmpDir . "/.git/") as $file)
            {
                $projectFiles[] = ProjectFile::withFile($root . "/.git/" . $file["rel"], $file["abs"]);
            }
            File::delete($tmpDir . "/.git");
        }

        // Add license file
        $license = File::get("../app/Templates/Default/Assets/LICENSE.md");
        $projectFiles[] = ProjectFile::withContent($root . "/LICENSE.md", $license);
        // Add package manifest
        $packageManifestContent = json_encode($packageManifest->output(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $projectFiles[] = ProjectFile::withContent("manifest.json", $packageManifestContent);
        // Add project manifest
        $manifestContent = json_encode($manifest->output(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $projectFiles[] = ProjectFile::withContent($root . "/manifest.json", $manifestContent);

        return $projectFiles;
    }


    private function getJsonProjectFiles($books, $all = false)
    {
        $projectFiles = [];

        switch ($books[0]->state)
        {
            case EventStates::STARTED:
            case EventStates::TRANSLATING:
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

        $manifest = $this->_model->generateManifest($books[0]);

        // Set contributor list from entire project contributors
        if($all)
        {
            $manifest->setContributor(array_map(function($contributor) {
                return $contributor["fname"] . (!empty($contributor["lname"]) ? " ".$contributor["lname"] : "");
            }, $this->_eventModel->getProjectContributors($books[0]->projectID, false, false)));
        }

        $json_books = [];
        $lastChapter = 0;
        $lastCode = null;

        foreach ($books as $chunk) {
            $code = strtoupper($chunk->bookCode);

            if($code != $lastCode)
            {
                $lastChapter = 0;
            }

            if(!isset($json_books[$code]))
            {
                $json_books[$code] = ["root" => []];

                // Set contributor list from book contributors
                if(!$all)
                {
                    $contributors = $this->_eventModel->getEventContributors($chunk->eventID, $chk_lvl, $chunk->bookProject, false);
                    foreach ($contributors as $cat => $list)
                    {
                        if($cat == "admins") continue;
                        foreach ($list as $contributor)
                        {
                            $manifest->addContributor($contributor["fname"] . " " . $contributor["lname"]);
                        }
                    }
                }
            }

            $verses = json_decode($chunk->translatedVerses);

            if($chunk->chapter != $lastChapter)
            {
                $lastChapter = $chunk->chapter;
                $json_books[$code]["root"][$lastChapter-1] = [];
            }

            if(!empty($verses->{EventMembers::L3_CHECKER}->verses))
            {
                foreach ($verses->{EventMembers::L3_CHECKER}->verses as $verse => $text) {
                    $json_books[$code]["root"][$lastChapter-1][OdbSections::enum($verse)] = html_entity_decode($text, ENT_QUOTES);
                }
            }
            elseif (!empty($verses->{EventMembers::L2_CHECKER}->verses))
            {
                foreach ($verses->{EventMembers::L2_CHECKER}->verses as $verse => $text) {
                    $json_books[$code]["root"][$lastChapter-1][OdbSections::enum($verse)] = html_entity_decode($text, ENT_QUOTES);
                }
            }
            else
            {
                if($chunk->bookProject == "rad")
                {
                    $translation = isset($verses->{EventMembers::CHECKER}->verses)
                        && !empty($verses->{EventMembers::CHECKER}->verses)
                        ? $verses->{EventMembers::CHECKER}->verses
                        : $verses->{EventMembers::TRANSLATOR}->verses;

                    if(!is_object($translation))
                    {
                        $json_books[$code]["root"][$lastChapter-1][RadioSections::enum(RadioSections::TITLE)] = html_entity_decode($translation, ENT_QUOTES);
                    }
                    else
                    {
                        $tmp = [];
                        $tmp["name"] = $translation->name;
                        $tmp["text"] = $translation->text;
                        $json_books[$code]["root"][$lastChapter-1][RadioSections::enum(RadioSections::SPEAKERS)][] = $tmp;
                    }
                }
                else
                {
                    foreach ($verses->{EventMembers::TRANSLATOR}->verses as $verse => $text) {
                        if($verse >= OdbSections::CONTENT)
                        {
                            $json_books[$code]["root"][$lastChapter-1][OdbSections::enum($verse)][] = html_entity_decode($text, ENT_QUOTES);
                        }
                        else
                        {
                            $json_books[$code]["root"][$lastChapter-1][OdbSections::enum($verse)] = html_entity_decode($text, ENT_QUOTES);
                        }
                    }
                }
            }

            $lastCode = $code;

            if(!$manifest->getProject($chunk->bookCode))
            {
                $manifest->addProject(new Project(
                    $chunk->bookName,
                    $chunk->sourceBible,
                    $chunk->bookCode,
                    (int)$chunk->abbrID,
                    "./".(strtoupper($chunk->bookCode)).".json",
                    ["rad"]
                ));
            }
        }

        foreach ($json_books as $filename => $content)
        {
            $filePath = $filename.".json";
            $content = json_encode($content, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
            $projectFiles[] = ProjectFile::withContent($filePath, $content);
        }

        $yaml = Spyc::YAMLDump($manifest->output(), 4, 0);
        $projectFiles[] = ProjectFile::withContent("manifest.yaml", $yaml);

        return $projectFiles;
    }


    private function getMdProjectFiles($books, $all = false, $upload = false)
    {
        $projectFiles = [];
        $lastChapter = -1;
        $chapter = [];

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

        $manifest = $this->_model->generateManifest($books[0]);
        $manifest->setCheckingLevel($chk_lvl);

        // Set contributor list from entire project contributors
        if($all)
        {
            $manifest->setContributor(array_map(function($contributor) {
                return $contributor["fname"] . (!empty($contributor["lname"]) ? " ".$contributor["lname"] : "");
            }, $this->_eventModel->getProjectContributors($books[0]->projectID, false, false)));
        }

        $root = !$upload ? $books[0]->targetLang."_".$books[0]->bookProject . "/" : "";

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
            $format = $chunk->bookCode == "psa" ? "%03d" : "%02d";
            $chapPath = $chunk->chapter > 0 ? sprintf($format, $chunk->chapter) : "front";
            $chunkPath = $currChunk[0] > 0 ? sprintf($format, $currChunk[0]) : "intro";
            $filePath = $root . $bookPath . "/" . $chapPath . "/" . $chunkPath . ".md";

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

            $projectFiles[] = ProjectFile::withContent($filePath, $text);

            if(!$manifest->getProject($chunk->bookCode))
            {
                // Set contributor list from book contributors
                if(!$all)
                {
                    $contributors = $this->_eventModel->getEventContributors($chunk->eventID, $manifest->getCheckingLevel(), $chunk->bookProject, false);
                    foreach ($contributors as $cat => $list)
                    {
                        if($cat == "admins") continue;
                        foreach ($list as $contributor)
                        {
                            $manifest->addContributor($contributor["fname"] . " " . $contributor["lname"]);
                        }
                    }
                }

                $manifest->addProject(new Project(
                    $chunk->bookName . " " . __($books[0]->bookProject),
                    "",
                    $chunk->bookCode,
                    (int)$chunk->abbrID,
                    "./".$chunk->bookCode,
                    []
                ));
            }
        }

        $yaml = Spyc::YAMLDump($manifest->output(), 4, 0);
        $projectFiles[] = ProjectFile::withContent($root . "manifest.yaml", $yaml);

        return $projectFiles;
    }


    private function getMdTwProjectFiles($books, $upload = false)
    {
        $projectFiles = [];

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

        $manifest = $this->_model->generateManifest($books[0]);
        $manifest->setCheckingLevel($chk_lvl);

        // Set contributor list from entire project contributors
        $manifest->setContributor(array_map(function($contributor) {
            return $contributor["fname"] . (!empty($contributor["lname"]) ? " ".$contributor["lname"] : "");
        }, $this->_eventModel->getProjectContributors($books[0]->projectID, false, false)));

        $manifest->addProject(new Project(
            __($books[0]->bookProject),
            "",
            "bible",
            0,
            "./bible",
            []
        ));

        $root = !$upload ? $books[0]->targetLang."_tw/" : "";

        foreach ($books as $chunk) {
            $verses = json_decode($chunk->translatedVerses);
            $words = (array) json_decode($chunk->words, true);

            $currWord = isset($words[$chunk->chunk]) ? $words[$chunk->chunk] : null;

            if(!$currWord) continue;

            $bookPath = $chunk->bookName;
            $chunkPath = $currWord;
            $filePath = $root. "bible/" . $bookPath ."/". $chunkPath.".md";

            if(!empty($verses->{EventMembers::L3_CHECKER}->verses))
            {
                $text = $verses->{EventMembers::L3_CHECKER}->verses;
            }
            else
            {
                $text = $verses->{EventMembers::TRANSLATOR}->verses;
            }

            $projectFiles[] = ProjectFile::withContent($filePath, $text);
        }

        $yaml = Spyc::YAMLDump($manifest->output(), 4, 0);
        $projectFiles[] = ProjectFile::withContent($root."manifest.yaml", $yaml);

        return $projectFiles;
    }
}
