<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 6/5/18
 * Time: 2:15 PM
 */

namespace App\Models;

use Cache;
use Database\Model;
use Database\QueryException;
use DB;
use File;
use Filesystem\FileNotFoundException;
use Helpers\Arrays;
use Helpers\Data;
use Helpers\Parsedown;
use Helpers\Spyc;
use Helpers\Tools;
use Helpers\UsfmParser;
use Helpers\ZipStream\Exception;
use ZipArchive;
use SplFileObject;


class ApiModel extends Model
{
    private $wordsDatabase = null;
    private $wordsDictionary = null;

    public  function __construct()
    {
        parent::__construct();
    }

    public function downloadAndExtractSourceScripture($bookProject, $sourceLang = "en")
    {
        $url = "";
        $filepath = "../app/Templates/Default/Assets/source/".$sourceLang."_".$bookProject.".zip";
        $folderpath = "../app/Templates/Default/Assets/source/".$sourceLang."_".$bookProject;

        $catalog = $this->getCachedFullCatalog();
        if(empty($catalog)) return false;

        foreach($catalog->languages as $language)
        {
            if($language->identifier == $sourceLang)
            {
                foreach($language->resources as $resource)
                {
                    if($resource->identifier == $bookProject)
                    {
                        foreach($resource->formats as $format)
                        {
                            $url = $format->url;
                            break 3;
                        }
                    }
                }
            }
        }

        if($url == "") return false;

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        $zip = curl_exec($ch);

        if(curl_errno($ch))
        {
            return "error: " . curl_error($ch);
        }

        curl_close($ch);

        File::put($filepath, $zip);

        if(File::exists($filepath))
        {
            $zip = new ZipArchive();
            $res = $zip->open($filepath);
            $sourceFolder = $sourceLang."_".$bookProject;
            $archFolder = "";

            if($res)
            {
                $index = preg_replace("/\/$/", "", $zip->getNameIndex(0));
                $archFolder = $index;
            }

            $zip->extractTo("../app/Templates/Default/Assets/source/");
            $zip->close();

            File::delete($filepath);

            if($sourceFolder != $archFolder)
            {
                File::deleteDirectory("../app/Templates/Default/Assets/source/".$sourceFolder);
                File::move("../app/Templates/Default/Assets/source/".$archFolder, "../app/Templates/Default/Assets/source/".$sourceFolder);
            }
        }

        return $folderpath;
    }


    /**
     * Get book source from unfolding word api
     * @param string $bookProject
     * @param string $bookCode
     * @param string $sourceLang
     * @param int $bookNum
     * @return mixed
     */
    public function getSourceBookFromApi($bookProject, $bookCode, $sourceLang = "en", $bookNum = 0)
    {
        $source = "";
        $filepath = "../app/Templates/Default/Assets/source/".$sourceLang."_".$bookProject."/".sprintf("%02d", $bookNum)."-".strtoupper($bookCode).".usfm";

        if(File::exists($filepath))
        {
            $source = File::get($filepath);
        }
        else
        {
            $folderpath = $this->downloadAndExtractSourceScripture($bookProject, $sourceLang);
            if(!$folderpath) return $source;

            $files = File::allFiles($folderpath);
            foreach($files as $file)
            {
                preg_match("/([0-9]{2,3})-(.*).usfm$/", $file, $matches);

                if(!isset($matches[1]) || !isset($matches[2])) continue;

                if((integer)$matches[1] == $bookNum && strtolower($matches[2]) == $bookCode)
                {
                    $source = File::get($file);
                    break;
                }
            }
        }

        return $source;
    }


    public function getCachedSourceBookFromApi($bookProject, $bookCode, $sourceLang = "en", $bookNum = 0)
    {
        $cache_keyword = $bookCode."_".$sourceLang."_".$bookProject."_usfm";
        $usfm = false;
        if(Cache::has($cache_keyword))
        {
            $source = Cache::get($cache_keyword);
            $usfm = json_decode($source, true);
        }
        else
        {
            $source = $this->getSourceBookFromApi($bookProject, $bookCode, $sourceLang, $bookNum);
            if($source)
            {
                $usfm = UsfmParser::parse($source);
                if(!empty($usfm))
                    Cache::add($cache_keyword, json_encode($usfm), 60*24*365);
            }
        }

        return $usfm;
    }


    /**
     * Get source text for chapter
     * @param $event Event data
     * @param $chapter Chapter number
     * @return array|null
     */
    public function getBookText($bookData, $chapter)
    {
        $usfm = $this->getCachedSourceBookFromApi(
            $bookData["sourceBible"],
            $bookData["bookCode"],
            $bookData["sourceLangID"],
            $bookData["abbrID"]);

        if($usfm && !empty($usfm["chapters"]))
        {
            $data = [];

            foreach ($usfm["chapters"][$chapter] as $section) {
                foreach ($section as $v => $text) {
                    $data["text"][$v] = $text;
                }
            }

            $arrKeys = array_keys($data["text"]);
            $lastVerse = explode("-", end($arrKeys));
            $lastVerse = $lastVerse[sizeof($lastVerse)-1];
            $data["totalVerses"] = !empty($data["text"]) ?  $lastVerse : 0;

            return $data;
        }

        return null;
    }


    /**
     * Get odb book source from local file
     * @param string $bookProject
     * @param string $bookCode
     * @param string $sourceLang
     * @return mixed
     */
    public function getOtherSource($bookProject, $bookCode, $sourceLang = "en")
    {
        $source = [];
        $filepath = "../app/Templates/Default/Assets/source/".$sourceLang."_".$bookProject."/".strtoupper($bookCode).".json";

        if(File::exists($filepath))
        {
            $sourceData = File::get($filepath);
            $source = (array)json_decode($sourceData, true);
            $chapters = [];

            if(!empty($source) && isset($source["root"]))
            {
                foreach ($source["root"] as $i => $chapter) {
                    $chapters[$i+1] = [];
                    $k = 1;
                    foreach ($chapter as $section) {
                        if(!is_array($section))
                        {
                            $chapters[$i+1][$k] = $section;
                            $k++;
                        }
                        else
                        {
                            foreach ($section as $p) {
                                $chapters[$i+1][$k] = $p;
                                $k++;
                            }
                        }
                    }
                }
                return ["chapters" => $chapters];
            }
            else
            {
                return [];
            }
        }

        return $source;
    }


    public function downloadRubricFromApi($lang = "en") {
        $folderPath = "../app/Templates/Default/Assets/source/".$lang."_rubric/";
        $filepath = $folderPath . "rubric.json";
        $url = "https://v-raft.com/api/rubric/" . $lang;

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);

        $rubric = curl_exec($ch);

        if(curl_errno($ch))
        {
            curl_close($ch);
            return false;
        }

        curl_close($ch);

        if ($rubric != "[]" && File::makeDirectory($folderPath, 0755, true)) {
            File::put($filepath, $rubric);

            if(File::exists($filepath))
            {
                return $filepath;
            }
        }

        return false;
    }


    public function getRubricFromApi($lang = "en") {
        $rubric = false;
        $filepath = "../app/Templates/Default/Assets/source/".$lang."_rubric/rubric.json";

        if(File::exists($filepath))
        {
            $rubric = File::get($filepath);
        }
        else
        {
            if ($this->downloadRubricFromApi($lang)) {
                $rubric = File::get($filepath);
            }
        }

        return $rubric;
    }


    public function getCachedRubricFromApi($lang = "en") {
        $cache_keyword = $lang."_rubric";
        $rubric = [];
        if(Cache::has($cache_keyword))
        {
            $source = Cache::get($cache_keyword);
            $rubric = json_decode($source);
        }
        else
        {
            $source = $this->getRubricFromApi($lang);
            if($source)
            {
                Cache::add($cache_keyword, $source, 60*24*365);
                $rubric = json_decode($source);
            }
        }

        return $rubric;
    }


    public function insertLangsFromTD($reDownload)
    {
        $response = ["success" => false];

        $langs = [];

        try {
            if($reDownload)
            {
                $totalPages = 1;
                for($i=0; $i < $totalPages; $i++)
                {
                    $url = "http://td.unfoldingword.org/uw/ajax/languages/?draw=1&order[0][column]=0&".
                        "order[0][dir]=asc&start=".($i*500)."&length=500&search[value]=0";

                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $url);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                    $cat = curl_exec($ch);
                    curl_close($ch);
                    $arr = json_decode($cat);

                    if($arr != null)
                    {
                        if($i == 0)
                        {
                            $totalPages = ceil($arr->recordsTotal/500);
                        }

                        foreach ($arr->data as $lang) {
                            preg_match('/>(.+)<\//', $lang[0], $matches);
                            $lang[0] = $matches[1];
                            $langs[$matches[1]] = $lang[6];
                        }
                    }

                }

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, "http://td.unfoldingword.org/exports/langnames.json");
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                $languages = curl_exec($ch);

                if(curl_errno($ch)){
                    throw new \Exception(curl_error($ch));
                }

                curl_close($ch);

                File::put("../app/Templates/Default/Assets/source/langnames.json", $languages);
            }
            else
            {
                $languages = File::get("../app/Templates/Default/Assets/source/langnames.json");
            }

            $languages = json_decode($languages);
        } catch (FileNotFoundException $e) {
            $response["error"] = "File langnames.json not found";
        } catch (\Exception $e) {
            $response["error"] = "Couldn't download the file langnames.json";
        }

        if(isset($response["error"]))
            return $response;

        foreach ($languages as $language) {
            $tmp = [];
            $tmp["langID"] = $language->lc;
            $tmp["langName"] = $language->ln;
            $tmp["angName"] = $language->ang;
            $tmp["direction"] = $language->ld;
            $tmp["isGW"] = $language->gw;
            $tmp["gwLang"] = $language->gw ?
                $language->ln :
                (isset($langs[$language->lc]) && $langs[$language->lc] ? $langs[$language->lc] : "English");

            try {
                $this->db->table("languages")
                    ->insert($tmp);
            } catch (QueryException $e) {

            }
        }

        $response["success"] = true;

        return $response;
    }


    public function insertSourcesFromCatalog($reDownload) {
        if($reDownload)
        {
            File::delete("../app/Templates/Default/Assets/source/catalog.json");
        }

        $sourceLangs = $this->getSourceTranslations();

        foreach ($sourceLangs as $lang) {
            foreach ($lang["sources"] as $source) {
                if(trim($lang["langID"]) == "" || trim($source["slug"]) == "" || trim($source["name"]) == "")
                    continue;

                try {
                    $insert = [
                        "langID" => $lang["langID"],
                        "slug" => $source["slug"],
                        "name" => $source["name"],
                    ];
                    $this->db->table("sources")
                        ->insert($insert);
                } catch(QueryException $e) {
                    //pr($e->getMessage(),0);
                }
            }
        }
    }


    public function insertSource($lang, $slug, $name) {
        $insert = [
            "langID" => $lang,
            "slug" => $slug,
            "name" => $name,
        ];
        return $this->db->table("sources")
            ->insert($insert);
    }


    public function getFullCatalog()
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.bibletranslationtools.org/v3/catalog.json");

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $cat = curl_exec($ch);

        if(curl_errno($ch))
        {
            return false;
        }

        curl_close($ch);
        return $cat;

    }


    public function getCachedFullCatalog()
    {
        $filepath = "../app/Templates/Default/Assets/source/catalog.json";
        if(!File::exists($filepath))
        {
            $catalog = $this->getFullCatalog();

            if($catalog)
                File::put($filepath, $catalog);
            else
                $catalog = "[]";
        }
        else
        {
            $catalog = File::get($filepath);
        }

        $catalog = json_decode($catalog);

        return $catalog;
    }

    /**
     * Get source translations
     * @return array
     */
    public function getSourceTranslations()
    {
        $catalog = $this->getCachedFullCatalog();
        $sls = [];

        foreach ($catalog as $key => $data) {
            if($key == "languages")
            {
                foreach ($data as $lang)
                {
                    $tmp = [];
                    $tmp["langID"] = $lang->identifier;
                    $tmp["langName"] = $lang->title;
                    $tmp["sources"] = [];

                    foreach ($lang->resources as $resource) {
                        if(in_array($resource->identifier, [
                            "ta",
                            "obs",
                            "obs-tn",
                            "obs-tq"
                        ])) continue;

                        if(preg_match("/obs/i", $resource->title)) continue;

                        $res = [];
                        $res["slug"] = $resource->identifier;
                        $res["name"] = $resource->title;
                        $tmp["sources"][] = $res;
                    }

                    if(!empty($tmp["sources"]))
                        $sls[] = $tmp;
                }
            }
        }

        return $sls;
    }


    public function downloadAndExtractNotes($lang = "en", $update = false)
    {
        $filepath = "../app/Templates/Default/Assets/source/".$lang."_notes.zip";
        $folderpath = "../app/Templates/Default/Assets/source/".$lang."_tn";

        if(!File::exists($folderpath) || $update)
        {
            // Get catalog
            $catalog = $this->getCachedFullCatalog();
            if(empty($catalog)) return false;

            $url = "";

            foreach($catalog->languages as $language)
            {
                if($language->identifier == $lang)
                {
                    foreach($language->resources as $resource)
                    {
                        if($resource->identifier == "tn")
                        {
                            foreach($resource->formats as $format)
                            {
                                $url = $format->url;
                                break;
                            }
                        }
                    }
                }
            }

            if($url == "") return false;

            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, $url);

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
            $zip = curl_exec($ch);

            if(curl_errno($ch))
            {
                return "error: " . curl_error($ch);
            }

            curl_close($ch);

            File::put($filepath, $zip);

            if(File::exists($filepath))
            {
                $zip = new ZipArchive();
                $zip->open($filepath);
                $zip->extractTo("../app/Templates/Default/Assets/source/");
                $zip->close();

                File::delete($filepath);
            }
        }

        return $folderpath;
    }


    /**
     * Parses .md files of specified book and returns array
     * @param $book <p>Book code</p>
     * @param string $lang <p>Language code</p>
     * @param bool $parse <p>Whether to parse to html or leave it as markdown</p>
     * @param null $folderpath <p>Path to the notes directory</p>
     * @return array
     */
    public function getTranslationNotes($book, $lang ="en", $parse = true, $folderpath = null)
    {
        if($folderpath == null)
            $folderpath = $this->downloadAndExtractNotes($lang);

        if(!$folderpath) return [];

        // Get book folder
        $dirs = File::directories($folderpath);
        $bookFolderPath = null;
        foreach($dirs as $dir)
        {
            preg_match("/[1-3a-z]{3}$/i", $dir, $matches);
            if(isset($matches[0]) && strtolower($matches[0]) == $book)
            {
                $bookFolderPath = $dir;
                break;
            }
        }

        if($bookFolderPath != null)
            $folderpath = $bookFolderPath;

        if(!$folderpath) return [];

        $parsedown = new Parsedown();

        $result = [];
        $files = File::allFiles($folderpath);
        foreach($files as $file)
        {
            preg_match("/([0-9]{2,3}|front)\/([0-9]{2,3}|intro|index|title).(md|txt)$/i", $file, $matches);

            if(!isset($matches[1]) || !isset($matches[2])) continue;

            if($matches[2] == "index")
                continue;

            if($matches[1] == "front")
                $matches[1] = 0;

            if($matches[2] == "intro" || $matches[2] == "title")
                $matches[2] = 0;

            $chapter = (int)$matches[1];
            $chunk = (int)$matches[2];
            $ext = strtolower($matches[3]);

            if(!isset($result[$chapter]))
                $result[$chapter] = [];
            if(isset($result[$chapter]) && !isset($result[$chapter][$chunk]))
                $result[$chapter][$chunk] = [];

            $md = File::get($file);

            if($ext == "txt")
            {
                $data = (array)json_decode($md);
                $md = "";
                foreach ($data as $q) {
                    $md .= "# ".$q->title."  \n\n";
                    $md .= $q->body."  \n\n";
                }
            }

            $content = $md;

            if($parse)
            {
                $content = $parsedown->text($md);
                $content = preg_replace("//", "", $content);
            }

            $result[$chapter][$chunk][] = $content;
        }

        ksort($result);
        $result = array_map(function ($elm) {
            ksort($elm);
            return $elm;
        }, $result);
        return $result;
    }


    /**
     * Download tWords and extract them
     * @param string $lang
     * @param bool $update
     * @return bool|string
     */
    public function downloadAndExtractWords($lang = "en", $update = false)
    {
        $filepath = "../app/Templates/Default/Assets/source/".$lang."_words.zip";
        $folderpath = "../app/Templates/Default/Assets/source/".$lang."_tw";

        if(!File::exists($folderpath) || $update)
        {
            // Get catalog
            $catalog = $this->getCachedFullCatalog();
            if(empty($catalog)) return false;

            $url = "";

            foreach($catalog->languages as $language)
            {
                if($language->identifier == $lang)
                {
                    foreach($language->resources as $resource)
                    {
                        if($resource->identifier == "tw")
                        {
                            foreach ($resource->projects as $project)
                            {
                                foreach($project->formats as $format)
                                {
                                    $url = $format->url;
                                    if(!preg_match("/\.zip$/", $url)) continue;
                                    break;
                                }
                            }
                        }
                    }
                }
            }

            if($url == "") return false;

            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, $url);

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
            $zip = curl_exec($ch);

            if(curl_errno($ch))
            {
                return "error: " . curl_error($ch);
            }

            curl_close($ch);

            File::put($filepath, $zip);

            if(File::exists($filepath))
            {
                $zip = new ZipArchive();
                $res = $zip->open($filepath);
                $zip->extractTo("../app/Templates/Default/Assets/source/");
                $zip->close();

                File::delete($filepath);
            }
        }

        return $folderpath;
    }


    /**
     * Parses .md files of specified book and chapter and returns array
     * @param $book
     * @param $chapter
     * @param $lang
     * @return  array
     **/
    public function getTranslationWords($book, $chapter, $lang = "en")
    {
        $folderpath = $this->downloadAndExtractWords($lang);

        if(!$folderpath) return [];

        $words = $this->getWordsDatabase();
        $dom = new \DOMDocument();

        $filtered = [
            "book" => $book,
            "chapter" => $chapter,
            "words" => []
        ];

        foreach ($words as $word)
        {
            if($book == $word[0] && $chapter == $word[1])
            {
                if(!isset($filtered["words"][$word[5]]))
                    $filtered["words"][$word[5]] = [];

                if(!isset($filtered["words"][$word[5]]["verses"]))
                    $filtered["words"][$word[5]]["verses"] = [];

                if(!isset($filtered["words"][$word[5]]["term"]))
                {
                    $filtered["words"][$word[5]]["term"] = $word[3];
                    $filtered["words"][$word[5]]["name"] = $word[3];
                }

                $filtered["words"][$word[5]]["verses"][] = (int)$word[2];
            }
        }

        $parsedown = new Parsedown();
        $files = File::allFiles($folderpath);

        foreach ($filtered["words"] as $key => &$word) {
            $word["range"] = $this->getRanges($word["verses"]);

            foreach ($files as $file) {
                if(preg_match("/".$key.".md$/i", $file))
                {
                    $md = File::get($file);
                    $html = $parsedown->text($this->remove_utf8_bom($md));
                    $html = preg_replace("//", "", $html);

                    $dom->loadHTML($html);
                    $headers = $dom->getElementsByTagName("h1");
                    if(!empty($headers))
                    {
                        $word["name"] = $headers[0]->nodeValue;
                    }

                    $word["text"] = $html;
                }
            }
        }

        return $filtered;
    }

    /**
     * Parses .md files of specified category and returns array
     * @param $category
     * @param $lang
     * @param $onlyNames
     * @param $parse
     * @param $folderpath
     * @return  array
     **/
    public function getTranslationWordsByCategory($category, $lang = "en", $onlyNames = false, $parse = true, $folderpath = null)
    {
        if($folderpath == null)
            $folderpath = $this->downloadAndExtractWords($lang);

        if(!$folderpath) return [];

        $parsedown = new Parsedown();
        $files = File::allFiles($folderpath);

        $words = [];

        foreach ($files as $file) {
            $filename = $file->getBasename('.' . $file->getExtension());
            if($this->getCategoryByWord($filename) == $category)
            {
                preg_match("/\/([0-9a-z-_]+).(md|txt)$/i", $file, $matches);

                if(!isset($matches[1]) || !isset($matches[2])) continue;

                $word_name = $matches[1];
                $ext = strtolower($matches[2]);

                $word = [];

                if(!$onlyNames)
                {
                    $md = File::get($file);

                    if($ext == "txt")
                    {
                        $data = (array)json_decode($md);
                        $md = "";
                        foreach ($data as $q) {
                            $md .= "# ".$q->title."  \n\n";
                            $md .= $q->body."  \n\n";
                        }
                    }

                    $html = $md;

                    if($parse)
                    {
                        $html = $parsedown->text($md);
                        $html = preg_replace("//", "", $html);
                    }
                    $word["text"] = $html;
                }


                $word["word"] = $word_name;
                $words[] = $word;
            }
        }

        usort($words, function($a, $b) {
            return strcmp($a["word"], $b["word"]);
        });

        return $words;
    }

    public function getWordsDatabase()
    {
        // Parses csv file and returns an array of words
        // Each word array has 6 elements
        // 0 - book code (gen)
        // 1 - chapter
        // 2 - verse
        // 3 - term (ex. Heavens)
        // 4 - category (ex. kt, other, names)
        // 5 - reference name (ex. heaven)
        if($this->wordsDatabase == null)
        {
            $words = new SplFileObject("../app/Templates/Default/Assets/source/words_db.csv");
            $words->setFlags(SplFileObject::READ_CSV);
            $this->wordsDatabase = $words;
        }

        return $this->wordsDatabase;
    }

    public function getWordsDictionary()
    {
        // Parses csv file and returns an array of words
        // Each word array has 2 elements
        // 0 - reference name (ex. heaven)
        // 1 - category (ex. kt, other, names)
        if($this->wordsDictionary == null)
        {
            $words = new SplFileObject("../app/Templates/Default/Assets/source/words_dict.csv");
            $words->setFlags(SplFileObject::READ_CSV);
            $this->wordsDictionary = $words;
        }

        return $this->wordsDictionary;
    }

    public function getCategoryByWord($word)
    {
        $words = $this->getWordsDictionary();
        foreach ($words as $w) {
            if($w[0] == $word)
            {
                return $w[1];
            }
        }

        return "unknown";
    }


    /**
     * Download questions from DCS and extract them
     * @param string $lang
     * @param bool $update
     * @return bool|string
     */
    public function downloadAndExtractQuestions($lang = "en", $update = false)
    {
        $filepath = "../app/Templates/Default/Assets/source/".$lang."_questions.zip";
        $folderpath = "../app/Templates/Default/Assets/source/".$lang."_tq";

        if(!File::exists($folderpath) || $update)
        {
            // Get catalog
            $catalog = $this->getCachedFullCatalog();
            if(empty($catalog)) return false;

            $url = "";

            foreach($catalog->languages as $language)
            {
                if($language->identifier == $lang)
                {
                    foreach($language->resources as $resource)
                    {
                        if($resource->identifier == "tq")
                        {
                            foreach($resource->formats as $format)
                            {
                                $url = $format->url;
                                break;
                            }
                        }
                    }
                }
            }

            if($url == "") return false;

            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, $url);

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
            $zip = curl_exec($ch);

            if(curl_errno($ch))
            {
                return "error: " . curl_error($ch);
            }

            curl_close($ch);

            File::put($filepath, $zip);

            if(File::exists($filepath))
            {
                $zip = new ZipArchive();
                $res = $zip->open($filepath);
                $zip->extractTo("../app/Templates/Default/Assets/source/");
                $zip->close();

                File::delete($filepath);
            }
        }

        return $folderpath;
    }


    /**
     * Parses .md files of specified book and returns array
     * @param $book
     * @param $lang
     * @param $parse
     * @param $folderpath
     * @return  array
     **/
    public function getTranslationQuestions($book, $lang ="en", $parse = true, $folderpath = null)
    {
        if($folderpath == null)
            $folderpath = $this->downloadAndExtractQuestions($lang);

        if(!$folderpath) return [];

        // Get book folder
        $dirs = File::directories($folderpath);
        $bookFolderPath = null;
        foreach($dirs as $dir)
        {
            preg_match("/[1-3a-z]{3}$/i", $dir, $matches);
            if(!empty($matches) && strtolower($matches[0]) == $book)
            {
                $bookFolderPath = $dir;
                break;
            }
        }

        if($bookFolderPath != null)
            $folderpath = $bookFolderPath;

        if(!$folderpath) return [];

        $parsedown = new Parsedown();

        $result = [];
        $files = File::allFiles($folderpath);
        foreach($files as $file)
        {
            preg_match("/([0-9]{2,3})\/([0-9]{2,3}).(md|txt)$/i", $file, $matches);

            if(!isset($matches[1]) || !isset($matches[2])) continue;

            $chapter = (int)$matches[1];
            $chunk = (int)$matches[2];
            $ext = strtolower($matches[3]);

            if(!isset($result[$chapter]))
                $result[$chapter] = [];
            if(isset($result[$chapter]) && !isset($result[$chapter][$chunk]))
                $result[$chapter][$chunk] = [];

            $md = File::get($file);
            if($ext == "txt")
            {
                $data = (array)json_decode($md);
                $md = "";
                foreach ($data as $q) {
                    $md .= "# ".$q->title."  \n\n";
                    $md .= $q->body."  \n\n";
                }
            }

            $html = $md;

            if($parse)
            {
                $html = $parsedown->text($md);
                $html = preg_replace("//", "", $html);
            }

            $result[$chapter][$chunk][] = $html;
        }

        ksort($result);
        return $result;
    }

    private function downloadPredefinedChunks($book, $lang = "en", $project = "ulb")
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "https://api.unfoldingword.org/ts/txt/2/$book/$lang/$project/chunks.json");

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        $response = curl_exec($ch);

        if(curl_errno($ch))
        {
            return "error: " . curl_error($ch);
        }

        curl_close($ch);

        return $response;
    }

    public function getPredefinedChunks($book, $lang = "en", $project = "ulb")
    {
        try
        {
            $json = $this->downloadPredefinedChunks($book, $lang, $project);
            $chunks = json_decode($json, true);

            if($chunks == null)
            {
                $json = $this->downloadPredefinedChunks($book, "en", "ulb");
                $chunks = json_decode($json, true);
            }

            $book = [];

            foreach ($chunks as $chunk)
            {
                $id = $chunk["id"];
                $chapter = (int)preg_replace("/-[0-9]+$/", "", $id);

                if(!array_key_exists($chapter, $book))
                {
                    $book[$chapter] = [];
                }

                $range = range($chunk["firstvs"],$chunk["lastvs"]);
                $book[$chapter][] = array_fill_keys(array_values($range), '');
            }

            return $book;
        }
        catch (\Exception $e)
        {
            return [];
        }
    }


    /**
     * Compiles all the chunks into a single usfm file
     * @param $folderpath
     * @return null
     */
    public function compileUSFMProject($folderpath)
    {
        $usfm = null;

        if(File::exists($folderpath))
        {
            $filepath = $folderpath . "/tmpfile";

            $files = File::files($folderpath);
            foreach ($files as $file) {
                if(preg_match("/\.usfm$/", $file))
                {
                    // If repository contains only one usfm with entire book
                    $usfm = File::get($file);
                    File::deleteDirectory($folderpath);
                    return $usfm;
                }
            }

            // Iterate through all the chapters and chunks
            $dirs = File::directories($folderpath, "<0");
            sort($dirs);
            foreach($dirs as $dir)
            {
                if(preg_match("/[0-9]{2,3}$/", $dir, $chapters))
                {
                    $chapter = (integer)$chapters[0];

                    $files = File::allFiles($dir);
                    sort($files);
                    foreach($files as $file)
                    {
                        if(preg_match("/[0-9]{2,3}.txt$/", $file, $chunks))
                        {
                            $chunk = (integer)$chunks[0];
                            $text = File::get($file);
                            if($chunk == 1)
                            {
                                // Fix usfm with missed chapter number tags
                                if(!preg_match("/^\\\\c/", $text))
                                {
                                    $text = "\c ".$chapter." \n\n".$text;
                                }
                            }

                            File::append($filepath, "\n\s5\n" . $text);
                        }
                    }
                }
            }

            if(File::exists($filepath))
            {
                $usfm = File::get($filepath);
                File::deleteDirectory($folderpath);
            }
        }

        return $usfm;
    }



    /**
     * Clones repository into temporary directory
     * @param $url
     * @return string Path to directory
     */
    public function processRemoteUrl($url)
    {
        $folderpath = "/tmp/".uniqid();

        shell_exec("/usr/bin/git clone ". $url ." ".$folderpath." 2>&1");

        return $folderpath;
    }


    /**
     * Exctracts .zip (.tstudio file as well) file into temporary directory
     * @param $file
     * @return string Path to directory
     */
    public function processZipFile($file)
    {
        $folderpath = "/tmp/".uniqid();

        $zip = new ZipArchive();
        $zip->open($file["tmp_name"]);
        $zip->extractTo($folderpath);
        $zip->close();
        $dirs = File::directories($folderpath);

        foreach ($dirs as $dir) {
            if(File::isDirectory($dir))
            {
                $folderpath = $dir;
                break;
            }
        }

        return $folderpath;
    }


    /**
     * Exctracts .zip file into temporary directory
     * @param $file
     * @return string Path to directory
     */
    public function processSourceZipFile($file)
    {
        $folderpath = "/tmp/".uniqid();

        $zip = new ZipArchive();
        $zip->open($file);
        $zip->extractTo($folderpath);
        $zip->close();

        return $folderpath;
    }


    public function processUsfmSource($path, $lang, $slug)
    {
        try {
            $target = "../app/Templates/Default/Assets/source/" . $lang . "_" . $slug;

            if(!File::isDirectory($target)) {
                File::makeDirectory($target);
            }

            $notUsfm = false;

            $allFiles = File::allFiles($path);
            foreach ($allFiles as $file) {
                if($file->getExtension() == "usfm") {
                    File::copy($file, $target . "/" . $file->getFilename());
                    $cache_keyword = sprintf(
                        "%s_%s_%s_%s",
                        preg_replace(
                            "/^[0-9]+-/",
                            "",
                            strtolower($file->getBasename('.'.$file->getExtension()))
                        ),
                        $lang,
                        $slug,
                        "usfm");
                    Cache::forget($cache_keyword);
                }
                elseif($file->getExtension() == "txt")
                {
                    $notUsfm = true;
                    break;
                }
            }

            if($notUsfm) // possibly translationStudio project
            {
                $translationModel = new TranslationsModel();
                $allDirs = File::directories($path, "<0");
                foreach ($allDirs as $dirPath)
                {
                    $dir = new \SplFileInfo($dirPath);
                    if(preg_match("/^[1-3]?[a-z]{2,3}$/", $dir->getBasename()))
                    {
                        $usfm = $this->compileUSFMProject($dirPath);
                        $bookInfo = $translationModel->getBookInfo($dir->getBasename());
                        File::put($target . "/" . sprintf(
                            "%02d-%s",
                            $bookInfo[0]->abbrID,
                            strtoupper($bookInfo[0]->code)
                            ) . ".usfm", $usfm);
                        $cache_keyword = sprintf(
                            "%s_%s_%s_%s",
                            $dir->getBasename(),
                            $lang,
                            $slug,
                            "usfm");
                        Cache::forget($cache_keyword);
                    }
                }
            }

            File::deleteDirectory($path);
            return true;
        } catch (Exception $e) {

        }

        File::deleteDirectory($path);
        return false;
    }


    public function processMdSource($path, $lang, $slug)
    {
        try {
            $target = "../app/Templates/Default/Assets/source/" . $lang . "_" . $slug .
                ($slug == "tw" ? "/bible" : "");

            if(!File::isDirectory($target)) {
                File::makeDirectory($target, 0755, true);
            }

            $regex = $slug == "tw" ? "/^kt|names|other$/" : "/^[1-3]?[a-z]{2,3}$/";

            $allDirs = File::directories($path, "<0");
            foreach ($allDirs as $dirPath) {
                $dir = new \SplFileInfo($dirPath);
                if(preg_match($regex, $dir->getBasename()))
                {
                    $files = File::allFiles($dirPath);
                    foreach ($files as $file)
                    {
                        if($file->getExtension() == "txt") // possibly translationStudio project
                        {
                            $content = File::get($file);
                            $data = (array)json_decode($content);
                            $md = "";
                            foreach ($data as $q) {
                                $md .= "# ".$q->title."  \n\n";
                                $md .= $q->body."  \n\n";
                            }
                            File::put($file->getPathname(), $md);
                            File::move($file, preg_replace("/txt$/", "md", $file->getPathname()));
                        }
                    }
                    File::copyDirectory($dir, $target . "/" . $dir->getBasename());
                }
            }
            File::deleteDirectory($path);
            return true;
        } catch (Exception $e) {

        }

        File::deleteDirectory($path);
        return false;
    }


    public function processJsonSource($path, $lang, $slug)
    {
        try {
            $target = "../app/Templates/Default/Assets/source/" . $lang . "_" . $slug;

            if(!File::isDirectory($target)) {
                File::makeDirectory($target, 0755, true);
            }

            $allDirs = File::directories($path, "<0");
            foreach ($allDirs as $dirPath) {
                $files = File::allFiles($dirPath);
                foreach ($files as $file)
                {
                    if($file->getExtension() == "json")
                    {
                        File::move($file, $target . "/" . $file->getFilename());
                    }
                }
            }
            File::deleteDirectory($path);
            return true;
        } catch (Exception $e) {

        }

        File::deleteDirectory($path);
        return false;
    }


    public function getNotesChunks($notes)
    {
        $chunks = array_keys($notes["notes"]);
        $totalVerses = isset($notes["totalVerses"]) ? $notes["totalVerses"] : 0;
        $arr = [];
        $tmp = [];

        foreach ($chunks as $key => $chunk) {
            if(isset($chunks[$key + 1]))
            {
                for($i = $chunk; $i < $chunks[$key + 1]; $i++)
                {
                    $tmp[] = $i;
                }

                $arr[] = $tmp;
                $tmp = [];
            }
            else
            {
                if($chunk <= $totalVerses)
                {
                    for($i = $chunk; $i <= $totalVerses; $i++)
                    {
                        $tmp[] = $i;
                    }

                    $arr[] = $tmp;
                    $tmp = [];
                }
            }
        }

        return $arr;
    }

    public function getNotesVerses($notes)
    {
        $tnVerses = [];
        $fv = 1;
        $i = 0;
        foreach (array_keys($notes["notes"]) as $key) {
            $i++;
            if($key == 0)
            {
                $tnVerses[] = $key;
                continue;
            }

            if(($key - $fv) >= 1)
            {
                $tnVerses[$fv] = $fv != ($key - 1) ? $fv . "-" . ($key - 1) : $fv;
                $fv = $key;

                if($i == sizeof($notes["notes"]))
                    $tnVerses[$fv] = $fv != $notes["totalVerses"] ? $fv . "-" . $notes["totalVerses"] : $fv;
                continue;
            }
        }

        return $tnVerses;
    }

    public function getQuestionsChunks($questions)
    {
        $chunks = array_keys($questions["questions"]);

        $chunks = array_map(function ($elm) {
            return [$elm];
        }, $chunks);

        return $chunks;
    }


    public function testChunks($chunks, $totalVerses)
    {
        if(!is_array($chunks) || empty($chunks)) return false;

        $lastVerse = 0;

        foreach ($chunks as $chunk) {
            if(!is_array($chunk) || empty($chunk)) return false;

            // Test if first verse is 1
            if($lastVerse == 0 && $chunk[0] != 1) return false;

            // Test if all verses are in right order
            foreach ($chunk as $verse) {
                if((integer)$verse > ($lastVerse+1)) return false;
                $lastVerse++;
            }
        }

        // Test if all verses added to chunks
        if($lastVerse != $totalVerses) return false;

        return true;
    }

    public function testChunkNotes($chunks, $notes)
    {
        if(!is_array($chunks))
            return false;

        if(sizeof($chunks) != sizeof($notes))
            return false;

        $converter = new \Helpers\Markdownify\Converter;
        $converter->setKeepHTML(false);
        foreach ($chunks as $key => $chunk) {
            if(trim($chunk) == "")
                return false;

            $md = $converter->parseString($chunk);
            if(trim($md) == "")
                return false;

            $chunks[$key] = $md;
        }

        return $chunks;
    }

    public function testChunkQuestions($chunks, $questions)
    {
        if(!is_array($chunks))
            return false;

        if(sizeof($questions) != sizeof($chunks))
            return false;

        $converter = new \Helpers\Markdownify\Converter;
        $converter->setKeepHTML(false);
        foreach ($chunks as $key => $chunk) {
            if(trim($chunk) == "")
                return false;

            $md = $converter->parseString($chunk);
            if(trim($md) == "")
                return false;

            $chunks[$key] = $md;
        }

        return $chunks;
    }

    public function testChunkWords($chunks, $words)
    {
        if(!is_array($chunks))
            return false;

        if(sizeof($words) != sizeof($chunks))
            return false;

        $converter = new \Helpers\Markdownify\Converter;
        $converter->setKeepHTML(false);
        foreach ($chunks as $key => $chunk) {
            if(trim($chunk) == "")
                return false;

            $md = $converter->parseString($chunk);
            if(trim($md) == "")
                return false;

            $chunks[$key] = $md;
        }

        return $chunks;
    }

    public function testChunkRadio($postChunks, $radioChunks)
    {
        if(!is_array($postChunks))
            return false;

        if(sizeof($radioChunks) != sizeof($postChunks))
            return false;

        foreach ($postChunks as $key => $chunk) {
            if(Tools::has_empty($chunk))
                return false;

            $postChunks[$key] = $chunk;
        }

        return $postChunks;
    }

    public function getRanges($arr)
    {
        if(sizeof($arr) == 1)
            return [$arr[0]];

        $ranges = [];
        for ($i = 0; $i < sizeof($arr); $i++) {
            $rstart = $arr[$i];
            $rend = $rstart;

            if(!isset($arr[$i]))
            {
                $ranges[] = $rstart == $rend ? $rstart : $rstart . '-' . $rend;
                continue;
            }

            while (isset($arr[$i + 1]) && ($arr[$i + 1] - $arr[$i]) == 1) {
                $rend = $arr[$i + 1];
                $i++;
            }
            $ranges[] = $rstart == $rend ? $rstart : $rstart . '-' . $rend;
        }
        return $ranges;
    }

    public function clearAllCache() {
        Cache::flush();
    }

    function remove_utf8_bom($text)
    {
        $bom = pack('H*','EFBBBF');
        $text = preg_replace("/^$bom/", '', $text);
        return $text;
    }
}