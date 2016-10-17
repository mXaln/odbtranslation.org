<?php
namespace App\Models;

use Database\Model;
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
            ->groupBy("translations.targetLang")->get();
    }

    public function getTranslationProjects($lang)
    {
        return $this->db->table("translations")
            ->select("translations.targetLang", "languages.langName", "languages.angName", "translations.bookProject")
            ->leftJoin("languages", "translations.targetLang","=", "languages.langID")
            ->where("translations.targetLang", $lang)
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
            ->groupBy("translations.bookProject")->get();
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
                "translations.chapter", "translations.translatedVerses")
            ->leftJoin("languages", "translations.targetLang","=", "languages.langID")
            ->leftJoin("abbr", "translations.bookCode","=", "abbr.code")
            ->where("translations.targetLang", $lang)
            ->where("translations.bookProject", $bookProject)
            ->where("translations.bookCode", $bookCode)
            ->orderBy("translations.chapter")
            ->orderBy("translations.chunk")->get();
    }

    /** Get translations information
     * @param $eventID
     * @return array
     */
    public function getTranslationByEventID($eventID)
    {
        $sql = "SELECT trs.chapter, trs.chunk, trs.translateDone, ".
            "trs.firstvs, ts.memberID, prs.memberID AS pairMemberID, ts.step, ts.kwCheck, ts.crCheck, ".
            "ts.currentChapter, ts.checkerID ".
            "FROM ".PREFIX."translators AS ts ".
            "LEFT JOIN ".PREFIX."translations AS trs ON trs.trID = ts.trID ".
            "LEFT JOIN ".PREFIX."translators AS prs ON prs.trID = ts.pairID ".
            "WHERE ts.eventID = :eventID ".
            "ORDER BY trs.chapter, trs.chunk";

        return $this->db->select($sql, [":eventID" => $eventID]);
    }

    public function getComment($eventID, $chapter, $verse, $memberID)
    {
        return $this->db->table("comments")
            ->where("eventID", $eventID)
            ->where("chapter", $chapter)
            ->where("verse", $verse)
            ->where("memberID", $memberID)->get();
    }

    public function getCommentsByEvent($eventID, $chapter = null)
    {
        $builder = $this->db->table("comments")
            ->select("comments.*", "members.userName")
            ->leftJoin("members", "comments.memberID", "=", "members.memberID")
            ->where("comments.eventID", $eventID)
            ->orderBy("comments.chapter")
            ->orderBy("comments.verse")
            ->orderBy("comments.cID");

        if($chapter != null)
            $builder->where("comments.chapter", $chapter);

        return $builder->get();
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