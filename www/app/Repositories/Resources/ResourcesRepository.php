<?php


namespace App\Repositories\Resources;


use DOMDocument;
use File;
use Helpers\Parsedown;
use Helpers\UsfmParser;
use SplFileObject;
use Support\Collection;
use Cache;
use Support\Str;
use ZipArchive;

class ResourcesRepository implements IResourcesRepository {
    private $rootPath = "../app/Templates/Default/Assets/source/";

    private $wacsCatalogUrl = "https://api.bibletranslationtools.org/v3/catalog.json";
    private $dcsCatalogUrl = "https://api.door43.org/v3/catalog.json";

    private $wacsCatalogPath;
    private $dcsCatalogPath;

    private $rubricUrl = "https://v-raft.com/api/rubric/";

    private $wordsDatabase = null;
    private $wordsDictionary = null;

    public function __construct() {
        $this->wacsCatalogPath = $this->rootPath . "catalog.json";
        $this->dcsCatalogPath = $this->rootPath . "catalog_dcs.json";
    }

    /**
     * Get Scripture
     * @param string $lang
     * @param string $resource ulb or udb
     */
    public function getScripture($lang, $resource, $bookSlug, $bookNum, $chapter = null)
    {
        $scripture_cache_key = $lang . "_" . $resource . "_" . $bookSlug;
        if (Cache::has($scripture_cache_key)) {
            $source = Cache::get($scripture_cache_key);
            $book = json_decode($source, true);
        } else {
            $book = $this->parseScripture($lang, $resource, $bookSlug, $bookNum);
            if ($book && !empty($book["chapters"])) {
                Cache::add($scripture_cache_key, json_encode($book), 365 * 24 * 7);
            }
        }

        if ($chapter !== null) {
            return $book[$chapter] ?? [];
        }

        return $book;
    }

    public function getMdResource($lang, $resource, $bookSlug, $chapter = null, $toHtml = false)
    {
        $resource_cache_key = $lang . "_" . $resource . "_" . $bookSlug . ($toHtml ? "_html" : "");

        if (Cache::has($resource_cache_key)) {
            $source = Cache::get($resource_cache_key);
            $book = json_decode($source, true);
        } else {
            $book = $this->parseMdResource($lang, $resource, $bookSlug, $toHtml);
            if (!empty($book)) {
                Cache::add($resource_cache_key, json_encode($book), 365 * 24 * 7);
            }
        }

        if ($chapter !== null) {
            return $book[$chapter] ?? [];
        }

        return $book;
    }

    public function getTw($lang, $category, $toHtml = false) {
        $resource_cache_key = $lang . "_tw_" . $category . ($toHtml ? "_html" : "");

        if (Cache::has($resource_cache_key)) {
            $source = Cache::get($resource_cache_key);
            $book = json_decode($source, true);
        } else {
            $book = $this->parseTw($lang, $category, $toHtml);
            if (!empty($book)) {
                Cache::add($resource_cache_key, json_encode($book), 365 * 24 * 7);
            }
        }

        return $book;
    }

    public function getRubric($lang) {
        $rubric_cache_key = $lang . "_rubric_rubric";

        if (Cache::has($rubric_cache_key)) {
            $source = Cache::get($rubric_cache_key);
            $rubric = json_decode($source);
        } else {
            $rubric = $this->parseRubric($lang);
            if ($rubric) {
                Cache::add($rubric_cache_key, json_encode($rubric), 365 * 24 * 7);
            }
        }

        return $rubric;
    }

    /**
     * Get ODB/RAD resource
     * @param string $lang
     * @param string $resource
     * @param string $bookSlug
     * @return array|mixed
     */
    public function getJsonResource($lang, $resource, $bookSlug) {
        $resource_cache_key = $lang . "_" . $resource . "_" . $bookSlug;

        if (Cache::has($resource_cache_key)) {
            $source = Cache::get($resource_cache_key);
            $book = json_decode($source, true);
        } else {
            $book = $this->parseJsonResource($lang, $resource, $bookSlug);
            if ($book) {
                Cache::add($resource_cache_key, json_encode($book), 365 * 24 * 7);
            }
        }

        return $book;
    }

    /**
     * Get FND/BIB/THEO resource
     * @param string $lang
     * @param string $resource
     * @param string $bookSlug
     * @return array|mixed
     */
    public function getMillResource($lang, $resource, $bookSlug) {
        $resource_cache_key = $lang . "_" . $resource . "_" . $bookSlug;

        if (Cache::has($resource_cache_key)) {
            $source = Cache::get($resource_cache_key);
            $book = json_decode($source, true);
        } else {
            $book = $this->parseMillResource($lang, $resource, $bookSlug);
            if ($book) {
                Cache::add($resource_cache_key, json_encode($book), 365 * 24 * 7);
            }
        }

        return $book;
    }

    /**
     * Update resource
     * @param string $lang
     * @param string $slug
     * @return bool
     */
    public function refreshResource($lang, $slug)
    {
        $this->forgetCatalog($this->wacsCatalogPath);
        $this->forgetCatalog($this->dcsCatalogPath);

        $this->forgetResource($lang, $slug);

        switch ($slug) {
            case "rad":
            case "odb":
            case "fnd":
            case "bib":
            case "theo":
                return false;
            default:
                if ($this->downloadResource($lang, $slug)) return true;
                break;
        }

        return false;
    }

    /**
     * Remove downloaded resource and cache
     * @param string $lang
     * @param string $resource
     */
    public function forgetResource($lang, $resource) {
        $cacheKey = $lang . "_" . $resource;
        Cache::forget($cacheKey);

        $folderPath = $this->rootPath . $lang . "_" . $resource;
        File::deleteDirectory($folderPath);
    }

    /**
     * Get parsed catalog
     * @param string $path
     * @param string $url
     * @return mixed
     */
    private function getCatalog($path, $url) {
        $filepath = $path;
        if(!File::exists($filepath)) {
            $catalog = $this->downloadCatalog($url);

            if($catalog)
                File::put($filepath, $catalog);
            else
                $catalog = "[]";
        } else {
            $catalog = File::get($filepath);
        }

        return json_decode($catalog);
    }

    /**
     * Update catalog
     * @param string $path
     * @param string $url
     */
    private function refreshCatalog($path, $url) {
        $this->forgetCatalog($path);
        $this->getCatalog($path, $url);
    }

    /**
     * Remove downloaded catalog
     * @param string $path
     */
    private function forgetCatalog($path) {
        File::delete($path);
    }

    /**
     * Download catalog
     * @param string $url
     * @return bool|string
     */
    private function downloadCatalog($url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $cat = curl_exec($ch);

        if(curl_errno($ch)) {
            return false;
        }

        curl_close($ch);
        return $cat;
    }

    /**
     * Download resource and extract it.
     * If url is not provided, will try to find in WACS/DCS catalogs
     * @param string $lang
     * @param string $resource
     * @param string $url
     * @return null|string
     */
    private function downloadResource($lang, $resource, $url = null) {
        $folderPath = $this->rootPath . $lang . "_" . $resource;

        if(!File::exists($folderPath)) {
            $result = $this->fetchResource($lang, $resource, $url);
            if ($result) {
                $extension = $result["pathinfo"]["extension"] ?? "json";
                $content = $result["content"] ?? null;

                if ($extension == "zip") {
                    $filePath = $folderPath . ".zip";
                    File::put($filePath, $content);

                    if(File::exists($filePath))
                    {
                        $zip = new ZipArchive();
                        $zip->open($filePath);
                        $zip->extractTo($this->rootPath);
                        $zip->close();

                        File::delete($filePath);
                    }
                } else {
                    $filePath = $folderPath . "/" . $resource . "." . $extension;
                    if (File::makeDirectory($folderPath, 0755, true)) {
                        File::put($filePath, $content);
                    } else {
                        $folderPath = null;
                    }
                }
            } else {
                $folderPath = null;
            }
        }

        return $folderPath;
    }

    /**
     * Parse .usfm file of scripture and return array
     * @param string $lang
     * @param string $folderPath
     * @return array
     **/
    private function parseScripture($lang, $resource, $bookSlug, $bookNum)
    {
        $book = [];

        $folderPath = $this->downloadResource($lang, $resource);
        if(!$folderPath) return $book;

        if ($bookSlug && $bookNum) {
            $filePath = $folderPath . "/" . sprintf("%02d", $bookNum) . "-" . strtoupper($bookSlug) . ".usfm";

            if (!File::exists($filePath)) return [];

            $source = File::get($filePath);
            $usfm = UsfmParser::parse($source);

            if ($usfm && isset($usfm["chapters"])) {
                $book["id"] = $usfm["id"] ?? "";
                $book["ide"] = $usfm["ide"] ?? "";
                $book["h"] = $usfm["h"] ?? "";
                $book["toc1"] = $usfm["toc1"] ?? "";
                $book["toc2"] = $usfm["toc2"] ?? "";
                $book["toc3"] = $usfm["toc3"] ?? "";
                $book["mt"] = $usfm["toc3"] ?? "";
                $book["chapters"] = $usfm["chapters"];

                foreach ($usfm["chapters"] as $chap => $chunks) {
                    if (!isset($book[$chap])) {
                        $book[$chap] = ["text" => []];
                    }

                    foreach ($chunks as $chunk) {
                        foreach ($chunk as $v => $text) {
                            $book[$chap]["text"][$v] = $text;
                        }
                    }

                    $arrKeys = array_keys($book[$chap]["text"]);
                    $lastVerse = explode("-", end($arrKeys));
                    $lastVerse = $lastVerse[sizeof($lastVerse)-1];
                    $book[$chap]["totalVerses"] = !empty($book[$chap]["text"]) ? $lastVerse : 0;
                }
            }
        }

        return $book;
    }

    public function parseMdResource($lang, $resource, $bookSlug, $toHtml = false, $folderPath = null) {
        $book = [];

        if (!$folderPath) {
            $folderPath = $this->downloadResource($lang, $resource);
        }

        if (!$folderPath) return $book;

        // Get book folder
        $dirs = File::directories($folderPath);

        $bookFolderPath = null;
        foreach($dirs as $dir)
        {
            preg_match("/[1-3a-z]{3}$/i", $dir, $matches);
            if(isset($matches[0]) && strtolower($matches[0]) == $bookSlug)
            {
                $bookFolderPath = $dir;
                break;
            }
        }

        if($bookFolderPath != null)
            $folderPath = $bookFolderPath;

        if(!$folderPath) return $book;

        $files = File::allFiles($folderPath);
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

            if(!isset($book[$chapter]))
                $book[$chapter] = [];
            if(isset($book[$chapter]) && !isset($book[$chapter][$chunk]))
                $book[$chapter][$chunk] = [];

            $content = File::get($file);

            if($ext == "txt")
            {
                $content = $this->jsonToMarkdown($content);
            }

            if($toHtml)
            {
                $parsedown = new Parsedown();
                $content = $parsedown->text($content);
                $content = preg_replace("//", "", $content);
            }

            $book[$chapter][$chunk][] = $content;
        }

        ksort($book);
        $book = array_map(function ($elm) {
            ksort($elm);
            return $elm;
        }, $book);

        return $book;
    }

    public function parseTw($lang, $bookSlug, $toHtml = true, $folderPath = null)
    {
        $book = [];

        if(!$folderPath)
            $folderPath = $this->downloadResource($lang, "tw");

        if (!$folderPath) return $book;

        $files = File::allFiles($folderPath);

        $words = [];

        foreach ($files as $file) {
            $filename = $file->getBasename('.' . $file->getExtension());
            if($this->getTwBookByWord($filename) == $bookSlug)
            {
                preg_match("/\/([0-9a-z-_]+).(md|txt)$/i", $file, $matches);

                if(!isset($matches[1]) || !isset($matches[2])) continue;

                $word_name = $matches[1];
                $ext = strtolower($matches[2]);

                $word = [];

                $content = File::get($file);

                if($ext == "txt")
                {
                    $content = $this->jsonToMarkdown($content);
                }

                if($toHtml)
                {
                    $parsedown = new Parsedown();
                    $content = $parsedown->text($content);
                    $content = preg_replace("//", "", $content);
                }

                $word["text"] = $content;
                $word["word"] = $word_name;
                $words[] = $word;
            }
        }

        usort($words, function($a, $b) {
            return strcmp($a["word"], $b["word"]);
        });

        return $words;
    }

    public function parseTwByBook($lang, $bookSlug, $chapter, $toHtml = false) {
        $folderPath = $this->downloadResource($lang, "tw");

        if (!$folderPath) return [];

        $wordDatabase = $this->getWordsDatabase();
        $filtered = [];

        foreach ($wordDatabase as $word)
        {
            if($bookSlug == $word[0] && $chapter == $word[1])
            {
                if(!isset($filtered[$word[5]]))
                    $filtered[$word[5]] = [];

                if(!isset($filtered[$word[5]]["verses"]))
                    $filtered[$word[5]]["verses"] = [];

                if(!isset($filtered[$word[5]]["term"]))
                {
                    $filtered[$word[5]]["term"] = $word[3];
                    $filtered[$word[5]]["name"] = $word[3];
                }

                $filtered[$word[5]]["verses"][] = (int)$word[2];
            }
        }

        $files = File::allFiles($folderPath);

        foreach ($filtered as $key => &$word) {
            $word["range"] = $this->getTwRange($word["verses"]);

            foreach ($files as $file) {
                if(preg_match("/".$key.".md$/i", $file))
                {
                    $content = File::get($file);

                    if ($toHtml) {
                        $parsedown = new Parsedown();
                        $dom = new DOMDocument();

                        $content = $parsedown->text($this->removeUtf8Bom($content));
                        $content = preg_replace("//", "", $content);
                        $dom->loadHTML($content);

                        $headers = $dom->getElementsByTagName("h1");
                        if(!empty($headers))
                        {
                            $word["name"] = $headers[0]->nodeValue;
                        }
                    }
                    $word["text"] = $content;
                }
            }
        }

        return $filtered;
    }

    private function parseRubric($lang) {
        $url = $this->rubricUrl . $lang;

        $folderPath = $this->downloadResource($lang, "rubric", $url);
        if (!$folderPath) return [];

        $filePath = $folderPath . "/rubric.json";

        $source = File::get($filePath);

        return json_decode($source);

    }

    /**
     * Parse odb|rad book sources from local file
     * @param $lang
     * @param $resource
     * @param $bookSlug
     * @return array
     */
    private function parseJsonResource($lang, $resource, $bookSlug) {
        $book = [];

        $filePath = $this->rootPath . $lang . "_".$resource . "/" . strtoupper($bookSlug) . ".json";

        if(File::exists($filePath)) {
            $sourceData = File::get($filePath);
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
                $book["chapters"] = $chapters;
            }
        }

        return $book;
    }

    /**
     * Parse FND/BIB/THEO book sources from local file
     * @param $lang
     * @param $resource
     * @param $bookSlug
     * @return array
     */
    private function parseMillResource($lang, $resource, $bookSlug) {
        $book = [];
        $folderPath = $this->rootPath . $lang . "_".$resource . "/" . $bookSlug;

        if(File::exists($folderPath))
        {
            $files = File::allFiles($folderPath);

            foreach($files as $file)
            {
                preg_match("/(\d+).md$/", $file, $matches);

                if(!isset($matches[1])) continue;

                $chapter = (integer)$matches[1];
                $content = File::get($file);

                $book[$chapter] = $this->parseMillMd($content);
            }
        }
        ksort($book);
        return $book;
    }

    private function parseMillMd($content) {
        $lines = preg_split("/\n/", $content);
        $lines = array_filter($lines, function($line) {
            return !empty(trim($line));
        });
        $lines = array_values($lines);
        return array_combine(range(1, count($lines)), $lines);
    }

    /**
     * Fetch resource from remote url
     * If url is not provided, will try to find in WACS/DCS catalogs
     * @param string $lang
     * @param string $resource
     * @param string $url
     * @return array|null
     */
    private function fetchResource($lang, $resource, $url = null) {
        if (!$url) {
            // Find resource on DCS first, if not there find in WACS
            $catalog = $this->getCatalog($this->dcsCatalogPath, $this->dcsCatalogUrl);
            $url = $this->getResourceUrl($catalog, $lang, $resource);

            $code = $this->fetchHttpCode($url);

            if ($url == "" || $code != 200) {
                $catalog = $this->getCatalog($this->wacsCatalogPath, $this->wacsCatalogUrl);
                $url = $this->getResourceUrl($catalog, $lang, $resource);
            }
        }

        if($url == "") return null;

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        $resource = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if(curl_errno($ch))
        {
            return null;
        }

        curl_close($ch);

        return [
            "code" => $httpcode,
            "content" => $resource,
            "pathinfo" => pathinfo($url)
        ];
    }

    /**
     * Fetch http code
     * @param $url
     * @return void
     */
    private function fetchHttpCode($url) {
        if ($url == "") return 404;

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch, CURLOPT_TIMEOUT,10);
        curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $httpCode;
    }

    private function getResourceUrl($catalog, $lang, $res) {
        $url = "";

        foreach($catalog->languages as $language)
        {
            if($language->identifier == $lang)
            {
                foreach($language->resources as $resource)
                {
                    if($resource->identifier == $res)
                    {
                        if (isset($resource->formats)) {
                            foreach ($resource->formats as $format) {
                                if (Str::endsWith($format->url, ".zip")) {
                                    $url = $format->url;
                                    break 3;
                                }
                            }
                        }

                        if (isset($resource->projects)) {
                            foreach ($resource->projects as $project) {
                                foreach($project->formats as $format)
                                {
                                    if (Str::endsWith($format->url, ".zip")) {
                                        $url = $format->url;
                                        break 4;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        return $url;
    }

    /**
     * Get tW database
     * @return SplFileObject
     */
    private function getWordsDatabase()
    {
        // Parses csv file and returns an array of words
        // Each word array has 6 elements
        // 0 - book code (gen)
        // 1 - chapter
        // 2 - verse
        // 3 - term (ex. Heavens)
        // 4 - category (ex. kt, other, names)
        // 5 - reference name (ex. heaven)

        if ($this->wordsDatabase == null) {
            $words = new SplFileObject($this->rootPath . "words_db.csv");
            $words->setFlags(SplFileObject::READ_CSV);
            $this->wordsDatabase = $words;
        }

        return $this->wordsDatabase;
    }

    /**
     * Get tW dictionary
     * @return SplFileObject
     */
    private function getWordsDictionary()
    {
        // Parses csv file and returns an array of words
        // Each word array has 2 elements
        // 0 - reference name (ex. heaven)
        // 1 - category (ex. kt, other, names)

        if ($this->wordsDictionary == null) {
            $words = new SplFileObject($this->rootPath . "words_dict.csv");
            $words->setFlags(SplFileObject::READ_CSV);
            $this->wordsDictionary = $words;
        }

        return $this->wordsDictionary;
    }

    private function getTwBookByWord($word)
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

    private function jsonToMarkdown($json) {
        $data = (array)json_decode($json);
        $md = "";
        foreach ($data as $item) {
            $md .= "# ".$item->title."  \n\n";
            $md .= $item->body."  \n\n";
        }
        return $md;
    }

    private function getTwRange($verses)
    {
        if(count($verses) == 1)
            return [$verses[0]];

        $range = [];
        for ($i = 0; $i < count($verses); $i++) {
            $rStart = $verses[$i];
            $rEnd = $rStart;

            if(!isset($verses[$i]))
            {
                $range[] = $rStart == $rEnd ? $rStart : $rStart . '-' . $rEnd;
                continue;
            }

            while (isset($verses[$i + 1]) && ($verses[$i + 1] - $verses[$i]) == 1) {
                $rEnd = $verses[$i + 1];
                $i++;
            }
            $range[] = $rStart == $rEnd ? $rStart : $rStart . '-' . $rEnd;
        }
        return $range;
    }

    private function removeUtf8Bom($text)
    {
        $bom = pack('H*','EFBBBF');
        $text = preg_replace("/^$bom/", '', $text);
        return $text;
    }
}