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
use DB;
use File;
use Helpers\Data;
use Helpers\Parsedown;
use Helpers\Spyc;
use Helpers\UsfmParser;
use ZipArchive;
use SplFileObject;


class ApiModel extends Model
{
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
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
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
     * @param string $bookCode
     * @param string $sourceLang
     * @param string $bookProject
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

    public function downloadRubricFromApi($lang = "en") {
        $folderPath = "../app/Templates/Default/Assets/source/".$lang."_rubric/";
        $filepath = $folderPath . "rubric.json";
        $url = "https://v-raft.com/api/rubric/" . $lang;

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
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


    public function insertLangsFromTD()
    {
        $response = ["success" => false];

        $langs = [];
        $langsFinal = [];
        for($i=0; $i < 81; $i++)
        {
            $url = "http://td.unfoldingword.org/uw/ajax/languages/?".
                "draw=7&columns%5B0%5D%5Bdata%5D=0&columns%5B0%5D%5Bname%5D=&columns%5B0%5D%5Bsearchable%5D=true&".
                "columns%5B0%5D%5Borderable%5D=true&columns%5B0%5D%5Bsearch%5D%5Bvalue%5D=&".
                "columns%5B0%5D%5Bsearch%5D%5Bregex%5D=false&columns%5B1%5D%5Bdata%5D=1&columns%5B1%5D%5Bname%5D=&".
                "columns%5B1%5D%5Bsearchable%5D=true&columns%5B1%5D%5Borderable%5D=true&".
                "columns%5B1%5D%5Bsearch%5D%5Bvalue%5D=&columns%5B1%5D%5Bsearch%5D%5Bregex%5D=false&".
                "columns%5B2%5D%5Bdata%5D=2&columns%5B2%5D%5Bname%5D=&columns%5B2%5D%5Bsearchable%5D=true&".
                "columns%5B2%5D%5Borderable%5D=true&columns%5B2%5D%5Bsearch%5D%5Bvalue%5D=&".
                "columns%5B2%5D%5Bsearch%5D%5Bregex%5D=false&columns%5B3%5D%5Bdata%5D=3&columns%5B3%5D%5Bname%5D=&".
                "columns%5B3%5D%5Bsearchable%5D=true&columns%5B3%5D%5Borderable%5D=true&".
                "columns%5B3%5D%5Bsearch%5D%5Bvalue%5D=&columns%5B3%5D%5Bsearch%5D%5Bregex%5D=false&".
                "columns%5B4%5D%5Bdata%5D=4&columns%5B4%5D%5Bname%5D=&columns%5B4%5D%5Bsearchable%5D=true&".
                "columns%5B4%5D%5Borderable%5D=true&columns%5B4%5D%5Bsearch%5D%5Bvalue%5D=&".
                "columns%5B4%5D%5Bsearch%5D%5Bregex%5D=false&columns%5B5%5D%5Bdata%5D=5&columns%5B5%5D%5Bname%5D=&".
                "columns%5B5%5D%5Bsearchable%5D=true&columns%5B5%5D%5Borderable%5D=true&".
                "columns%5B5%5D%5Bsearch%5D%5Bvalue%5D=&columns%5B5%5D%5Bsearch%5D%5Bregex%5D=false&".
                "columns%5B6%5D%5Bdata%5D=6&columns%5B6%5D%5Bname%5D=&columns%5B6%5D%5Bsearchable%5D=true&".
                "columns%5B6%5D%5Borderable%5D=true&columns%5B6%5D%5Bsearch%5D%5Bvalue%5D=&".
                "columns%5B6%5D%5Bsearch%5D%5Bregex%5D=false&columns%5B7%5D%5Bdata%5D=7&columns%5B7%5D%5Bname%5D=&".
                "columns%5B7%5D%5Bsearchable%5D=true&columns%5B7%5D%5Borderable%5D=true&".
                "columns%5B7%5D%5Bsearch%5D%5Bvalue%5D=&columns%5B7%5D%5Bsearch%5D%5Bregex%5D=false&".
                "order%5B0%5D%5Bcolumn%5D=0&order%5B0%5D%5Bdir%5D=asc&start=".($i*100)."&length=100&".
                "search%5Bvalue%5D=&search%5Bregex%5D=false&_=1507210697041";

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $cat = curl_exec($ch);
            curl_close($ch);
            $arr = json_decode($cat);

            $langs = array_merge($langs, $arr->data);

        }

        if(!empty($langs))
        {
            if(!File::exists("../app/Templates/Default/Assets/source/langnames.json"))
            {
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, "http://td.unfoldingword.org/exports/langnames.json");
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                $languages = curl_exec($ch);
                curl_close($ch);

                File::put("../app/Templates/Default/Assets/source/langnames.json", $languages);
            }
            else
            {
                $languages = File::get("../app/Templates/Default/Assets/source/langnames.json");
            }

            $languages = json_decode($languages, true);

            foreach($langs as $lang)
            {
                $tmp = [];
                preg_match('/>(.+)<\//', $lang[0], $matches);
                $tmp["langID"] = $matches[1];
                $tmp["langName"] = $lang[2];
                $tmp["angName"] = $lang[4];
                $tmp["isGW"] = preg_match("/success/", $lang[7]);
                $tmp["gwLang"] = $tmp["isGW"] ? $tmp["langName"] : $lang[6];

                if($tmp["gwLang"] == null)
                    $tmp["gwLang"] = "English";

                foreach($languages as $ln)
                {
                    if($ln["lc"] == $tmp["langID"])
                    {
                        $tmp["direction"] = $ln["ld"];
                        break;
                    }
                    else
                    {
                        $tmp["direction"] = "ltr";
                    }
                }

                $langsFinal[] = $tmp;
            }

            if(!empty($langsFinal))
                $this->db->table("languages")
                    ->delete();

            foreach($langsFinal as $lnf)
            {
                $this->db->table("languages")
                    ->insert($lnf);
            }

            $response["success"] = true;
        }

        return $response;
    }


    public function getFullCatalog()
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.door43.org/v3/catalog.json");

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


    public function downloadAndExtractNotes($lang = "en", $update = false)
    {
        $filepath = "../app/Templates/Default/Assets/source/".$lang."_notes.zip";
        $folderpath = "../app/Templates/Default/Assets/source/".$lang."_tn";

        if(!File::exists($folderpath) || $update)
        {
            if($lang == "en")
            {
                // Do not get notes from catalog, instead get it from git.door43.org
                // Should be temporarily
                $url = "https://git.door43.org/WycliffeAssociates/en_tn/archive/master.zip";
            }
            else
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
            }

            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, $url);

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
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
            preg_match("/[1-3a-z]{3}$/", $dir, $matches);
            if($matches[0] == $book)
            {
                $bookFolderPath = $dir;
                break;
            }
        }
        $folderpath = $bookFolderPath;

        if(!$folderpath) return [];

        $parsedown = new Parsedown();

        $result = [];
        $files = File::allFiles($folderpath);
        foreach($files as $file)
        {
            preg_match("/([0-9]{2,3}|front)\/([0-9]{2,3}|intro|index).md$/", $file, $matches);

            if(!isset($matches[1]) || !isset($matches[2])) return false;

            if($matches[2] == "index")
                continue;

            if($matches[1] == "front")
                $matches[1] = 0;

            if($matches[2] == "intro")
                $matches[2] = 0;

            $chapter = (int)$matches[1];
            $chunk = (int)$matches[2];

            if(!isset($result[$chapter]))
                $result[$chapter] = [];
            if(isset($result[$chapter]) && !isset($result[$chapter][$chunk]))
                $result[$chapter][$chunk] = [];

            $md = File::get($file);
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
     * Download tWords from DCS and extract them
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
            if($lang == "en")
            {
                // Do not get notes from catalog, instead get it from git.door43.org
                // Should be temporarily
                $url = "https://git.door43.org/WycliffeAssociates/en_tw/archive/master.zip";
            }
            else
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
            }

            if($url == "") return false;

            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, $url);

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
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

        // Parses csv file and returns an array of words
        // Each word array has 6 elements
        // 1 - book code (gen)
        // 2 - chapter
        // 3 - verse
        // 4 - term (ex. Heavens)
        // 5 - category (ex. kt, other, names)
        // 6 - reference name (ex. heaven)
        $words = new SplFileObject("../app/Templates/Default/Assets/source/words_db.csv");
        $words->setFlags(SplFileObject::READ_CSV);

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
                    $filtered["words"][$word[5]]["term"] = $word[3];

                $filtered["words"][$word[5]]["verses"][] = (int)$word[2];
            }
        }

        $parsedown = new Parsedown();
        $files = File::allFiles($folderpath);

        foreach ($filtered["words"] as $key => &$word) {
            $word["range"] = $this->getRanges($word["verses"]);

            foreach ($files as $file) {
                if(preg_match("/".$key.".md$/", $file))
                {
                    $md = File::get($file);
                    $html = $parsedown->text($md);
                    $html = preg_replace("//", "", $html);

                    $word["text"] = $html;
                }
            }
        }

        return $filtered;
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
            curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
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
     * Parses .md files of specified category and returns array
     * @param $category
     * @param $lang
     * @param $onlyNames
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
            if(preg_match("/bible\/".$category."/", $file))
            {
                $word = [];

                if(!$onlyNames)
                {
                    $md = File::get($file);
                    $html = $md;

                    if($parse)
                    {
                        $html = $parsedown->text($md);
                        $html = preg_replace("//", "", $html);
                    }
                    $word["text"] = $html;
                }

                preg_match("/\/([0-9a-z-_]+).md$/", $file, $matches);
                $word["word"] = $matches[1];
                $words[] = $word;
            }
        }

        usort($words, function($a, $b) {
            return strcmp($a["word"], $b["word"]);
        });

        return $words;
    }


    /**
     * Parses .md files of specified book and returns array
     * @param $book
     * @param $lang
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
            preg_match("/[1-3a-z]{3}$/", $dir, $matches);
            if($matches[0] == $book)
            {
                $bookFolderPath = $dir;
                break;
            }
        }

        $folderpath = $bookFolderPath;

        if(!$folderpath) return [];

        $parsedown = new Parsedown();

        $result = [];
        $files = File::allFiles($folderpath);
        foreach($files as $file)
        {
            preg_match("/([0-9]{2,3})\/([0-9]{2,3}).md$/", $file, $matches);

            if(!isset($matches[1]) || !isset($matches[2])) return false;

            $chapter = (int)$matches[1];
            $chunk = (int)$matches[2];

            if(!isset($result[$chapter]))
                $result[$chapter] = [];
            if(isset($result[$chapter]) && !isset($result[$chapter][$chunk]))
                $result[$chapter][$chunk] = [];

            $md = File::get($file);
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
            $dirs = File::directories($folderpath);
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
                                    $text = "\c ".$chapter." ".$text;
                                }
                            }

                            File::append($filepath, "\s5\n" . $text);
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
    public function processDCSUrl($url)
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
}