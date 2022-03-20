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
use Helpers\Tools;
use Helpers\ZipStream\Exception;
use ZipArchive;


class ApiModel extends Model
{
    public  function __construct()
    {
        parent::__construct();
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
            File::delete("../app/Templates/Default/Assets/source/catalog_dcs.json");
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

    public function processMillSource($path, $lang, $slug)
    {
        try {
            $target = "../app/Templates/Default/Assets/source/" . $lang . "_" . $slug;

            if(!File::isDirectory($target)) {
                File::makeDirectory($target, 0755, true);
            }

            $dirRegex = "/^\d+\.\d+\.\d+\.(?:c|e|m|d|s)$/";
            $fileRegex = "/^\d+\.md$/";

            $allDirs = File::directories($path, "<0");
            foreach ($allDirs as $dirPath) {
                $dir = new \SplFileInfo($dirPath);
                if(preg_match($dirRegex, $dir->getBasename())) {
                    $files = File::allFiles($dirPath);
                    foreach ($files as $file)
                    {
                        if(preg_match($fileRegex, $file->getBasename()))
                        {
                            $targetSubdir = $target . "/" . $dir->getBasename();
                            if(!File::isDirectory($targetSubdir)) {
                                File::makeDirectory($targetSubdir, 0755, true);
                            }

                            File::copy($file, $targetSubdir . "/" . $file->getFilename());
                        }
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
}