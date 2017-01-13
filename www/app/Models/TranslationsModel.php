<?php
namespace App\Models;

use Database\Model;
use Helpers\Constants\BookSources;
use Helpers\Constants\EventSteps;
use Helpers\Data;

class TranslationsModel extends Model
{
    public  function __construct()
    {
        parent::__construct();
    }


    public function getTranslationLanguages()
    {
        return $this->db->table("translations")
            ->select("translations.targetLang", "languages.langName", "languages.angName")
            ->leftJoin("languages", "translations.targetLang","=", "languages.langID")
            ->where("translations.translateDone", true)
            ->groupBy("translations.targetLang")->get();
    }

    public function getTranslationProjects($lang)
    {
        return $this->db->table("translations")
            ->select("translations.targetLang", "languages.langName", "languages.angName", "translations.bookProject")
            ->leftJoin("languages", "translations.targetLang","=", "languages.langID")
            ->where("translations.targetLang", $lang)
            ->where("translations.translateDone", true)
            ->groupBy("translations.bookProject")->get();
    }

    public function getTranslationBooks($lang, $bookProject)
    {
        return $this->db->table("translations")
            ->select("translations.targetLang", "languages.langName", "languages.angName", "translations.bookProject",
                "abbr.name AS bookName", "translations.bookCode")
            ->leftJoin("languages", "translations.targetLang","=", "languages.langID")
            ->leftJoin("abbr", "translations.bookCode","=", "abbr.code")
            ->where("translations.targetLang", $lang)
            ->where("translations.bookProject", $bookProject)
            ->where("translations.translateDone", true)
            ->groupBy("translations.bookCode")->get();
    }

    /**
     * For getting data of a translation
     * @param $fields Requested fields could be * for all or comma separated list
     * @param $where array Example: array('id' => array('=', 1), 'name' => array('!=', 'John'))
     * @return array
     */
    public function getTranslation($lang, $bookProject, $bookCode)
    {
        return $this->db->table("translations")
            ->select("translations.targetLang", "languages.langName", "languages.angName",
                "translations.bookProject", "translations.bookCode", "abbr.name AS bookName", "abbr.abbrID",
                "translations.chapter", "translations.translatedVerses", "languages.direction")
            ->leftJoin("languages", "translations.targetLang","=", "languages.langID")
            ->leftJoin("abbr", "translations.bookCode","=", "abbr.code")
            ->where("translations.targetLang", $lang)
            ->where("translations.bookProject", $bookProject)
            ->where("translations.bookCode", $bookCode)
            ->where("translations.translateDone", true)
            ->orderBy("translations.chapter")
            ->orderBy("translations.chunk")->get();
    }

    /**
     * Get translations information
     * @param $eventID
     * @param null $chapter
     * @return array|static[]
     */
    public function getTranslationByEventID($eventID, $chapter = null)
    {
        $builder = $this->db->table("translators");

        $builder->select("translations.chapter", "translations.chunk", "translations.translateDone",
                "translations.firstvs", "translators.memberID", "translators.step", "translators.verbCheck",
                "translators.peerCheck", "translators.kwCheck", "translators.crCheck",
                "translators.currentChapter", "translators.checkerID")
            ->leftJoin("translations", "translators.trID", "=", "translations.trID")
            ->where("translators.eventID", $eventID)
            //->where("translators.step", "!=", EventSteps::NONE) TODO check for bugs
            ->orderBy("translations.chapter")
            ->orderBy("translations.chunk");

        if($chapter != null)
            $builder->where("translations.chapter", $chapter);

        return $builder->get();
    }


    /** Get translation of translator in event
     * (all - if tID and chapter null, chunk - if tID not null, chapter - if chapter not null)
     * @param int $trID
     * @param int $tID
     * @param int $chapter
     * @return array
     */
    public function getEventTranslation($trID, $tID = null, $chapter = null)
    {
        $builder = $this->db->table("translations")
            ->where("trID", $trID);

        if($tID) {
            $builder->where("tID", $tID);
        }
        else
        {
            if($chapter) {
                $builder->where("chapter", $chapter);
            }
        }

        return $builder->get();
    }

    /** Get all translations with event
     * @return array|static[]
     */
    public function getAllTranslations()
    {
        return $this->db->table("events")
            ->select([
                "events.eventID",
                "events.chapters",
                "translations.translateDone",
                "translations.abbrID",
                "translations.chapter",
                "translations.chunk",
                "events.state"])
            ->leftJoin("translations", "translations.eventID", "=", "events.eventID")
            ->orderBy("events.eventID")
            ->orderBy("translations.abbrID")
            ->orderBy("translations.chapter")
            ->orderBy("translations.chunk")
            ->get();
    }

    /**
     * Get source translations
     * @return array
     */
    public function getSourceTranslations()
    {
        $langNames = $this->db->table("languages")
            ->whereIn("langID", array_keys(BookSources::catalog))
            ->select("langID", "langName")->get();

        $langs = array();
        foreach ($langNames as $langName) {
            $langs[$langName->langID] = $langName->langName;
        }

        $sls = array();
        foreach (BookSources::catalog as $lang => $books) {
            foreach ($books as $book) {
                $elm = new \stdClass();
                $elm->langID = $lang;
                $elm->langName = $langs[$lang];
                $elm->bookProject = $book;

                $sls[] = $elm;
            }
        }

        return $sls;
    }


    public function getBookInfo($bookCode)
    {
        return $this->db->table("abbr")
            ->where("code", $bookCode)->get();
    }

    /**
     * Create translation record
     * @param array $data
     * @return int
     */
    public function createTranslation($data)
    {
        return $this->db->table("translations")
            ->insertGetId($data);
    }

    /** Update translation
     * @param array $data
     * @param array $where
     * @return int
     */
    public function updateTranslation($data, $where)
    {
        return $this->db->table("translations")
            ->where($where)
            ->update($data);
    }

    public function getComment($eventID, $chapter, $chunk, $memberID)
    {
        return $this->db->table("comments")
            ->where("eventID", $eventID)
            ->where("chapter", $chapter)
            ->where("chunk", $chunk)
            ->where("memberID", $memberID)->get();
    }

    public function getCommentsByEvent($eventID, $chapter = null)
    {
        $builder = $this->db->table("comments")
            ->select("comments.*", "members.userName")
            ->leftJoin("members", "comments.memberID", "=", "members.memberID")
            ->where("comments.eventID", $eventID)
            ->orderBy("comments.chapter")
            ->orderBy("comments.chunk")
            ->orderBy("comments.cID");

        if($chapter != null)
            $builder->where("comments.chapter", $chapter);

        return $builder->get();
    }

    public function getKeywords($where)
    {
        return $this->db->table("keywords")
            ->select()
            ->where($where)
            ->orderBy("chapter")
            ->orderBy("chunk")
            ->orderBy("verse")->get();
    }

    public function deleteKeyword($kID)
    {
        return $this->db->table("keywords")
            ->where("kID", $kID)
            ->delete();
    }

    public function createKeyword($data)
    {
        return $this->db->table("keywords")
            ->insertGetId($data);
    }

    public function createComment($data)
    {
        return $this->db->table("comments")
            ->insertGetId($data);
    }

    public function updateComment($data, $where)
    {
        return $this->db->table("comments")
            ->where($where)
            ->update($data);
    }

    public function deleteComment($where)
    {
        return $this->db->table("comments")
            ->where($where)
            ->delete();
    }

}