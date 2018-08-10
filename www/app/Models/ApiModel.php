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
            // Do not get notes from catalog, instead get it from git.door43.org
            // Should be temporarily

            // Get catalog
            /*$catalog = $this->getCachedFullCatalog();
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

            if($url == "") return false;*/

            $url = "https://git.door43.org/WycliffeAssociates/en_tn/archive/master.zip";

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
     * @param $book
     * @param $lang
     * @return  array
     **/
    public function getTranslationNotes($book, $lang ="en")
    {
        $folderpath = $this->downloadAndExtractNotes($lang);

        if(!$folderpath) return [];

        // Get book folder
        $dirs = File::directories($folderpath);
        foreach($dirs as $dir)
        {
            preg_match("/[1-3a-z]{3}$/", $dir, $matches);
            if($matches[0] == $book)
            {
                $folderpath = $dir;
                break;
            }
        }

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
            $html = $parsedown->text($md);
            $html = preg_replace("//", "", $html);

            $result[$chapter][$chunk][] = $html;
        }

        ksort($result);
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

        // Get config.yaml catalog
        $config = File::get($folderpath . "/bible/config.yaml");
        $words = Spyc::YAMLLoad($config);

        $filtered = [
            "book" => $book,
            "chapter" => $chapter,
            "words" => []
        ];

        foreach ($words as $word => $item)
        {
            foreach ($item as $key => $occurrence) {
                if($key == "false_positives" || $key == "occurrences") continue;

                preg_match("/([0-9a-z]{3})\/(\d+)\/(\d+)$/", $occurrence, $matches);

                if(!empty($matches))
                {
                    if($matches[1] == $book && (int)$matches[2] == $chapter)
                    {
                        if(!isset($filtered["words"][$word]))
                            $filtered["words"][$word] = [];

                        if(!isset($filtered["words"][$word]["verses"]))
                            $filtered["words"][$word]["verses"] = [];

                        $filtered["words"][$word]["verses"][] = (int)$matches[3];
                    }
                }
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
     * Parses .md files of specified category and returns array
     * @param $category
     * @param $onlyNames
     * @param $lang
     * @return  array
     **/
    public function getTranslationWordsByCategory($category, $lang = "en", $onlyNames = false)
    {
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

                    $html = $parsedown->text($md);
                    $html = preg_replace("//", "", $html);
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
     * Parses .md files of specified book and returns array
     * @param $book
     * @param $lang
     * @return  array
     **/
    public function getTranslationQuestions($book, $lang ="en")
    {
        $folderpath = $this->downloadAndExtractQuestions($lang);

        if(!$folderpath) return [];

        // Get book folder
        $dirs = File::directories($folderpath);
        foreach($dirs as $dir)
        {
            preg_match("/[1-3a-z]{3}$/", $dir, $matches);
            if($matches[0] == $book)
            {
                $folderpath = $dir;
                break;
            }
        }

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
            $html = $parsedown->text($md);
            $html = preg_replace("//", "", $html);

            $result[$chapter][$chunk][] = $html;
        }

        ksort($result);
        return $result;
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