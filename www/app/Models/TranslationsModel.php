<?php
namespace Models;

use Core\Model;
use Helpers\Data;

class TranslationsModel extends Model
{
    public  function __construct()
    {
        parent::__construct();
    }


    public function getTranslationLanguages()
    {
        $sql = "SELECT trs.targetLang, t_lang.langName, t_lang.angName FROM ".PREFIX."translations AS trs ".
            "LEFT JOIN ".PREFIX."languages AS t_lang ON trs.targetLang = t_lang.langID ".
            "GROUP BY trs.targetLang";

        return $this->db->select($sql);
    }

    public function getTranslationProjects($lang)
    {
        $sql = "SELECT trs.targetLang, t_lang.langName, t_lang.angName, trs.bookProject FROM ".PREFIX."translations AS trs ".
            "LEFT JOIN ".PREFIX."languages AS t_lang ON trs.targetLang = t_lang.langID ".
            "WHERE trs.targetLang = :lang ".
            "GROUP BY trs.bookProject";

        return $this->db->select($sql, array(":lang" => $lang));
    }

    public function getTranslationBooks($lang, $bookProject)
    {
        $sql = "SELECT trs.targetLang, t_lang.langName, t_lang.angName, trs.bookProject, trs.bookCode, abbr.name AS bookName ".
            "FROM ".PREFIX."translations AS trs ".
            "LEFT JOIN ".PREFIX."languages AS t_lang ON trs.targetLang = t_lang.langID ".
            "LEFT JOIN ".PREFIX."abbr AS abbr ON trs.bookCode = abbr.code ".
            "WHERE trs.targetLang = :lang AND trs.bookProject = :bookProject ".
            "GROUP BY trs.bookCode";

        return $this->db->select($sql, array(":lang" => $lang, ":bookProject" => $bookProject));
    }

    /**
     * For getting data of a translation
     * @param $fields Requested fields could be * for all or comma separated list
     * @param $where array Example: array('id' => array('=', 1), 'name' => array('!=', 'John'))
     * @return array
     */
    public function getTranslation($lang, $bookProject, $bookCode)
    {
        $sql = "SELECT trs.targetLang, t_lang.langName, t_lang.angName, trs.bookProject, trs.bookCode, abbr.name AS bookName, ".
                "trs.chapter, trs.translatedVerses ".
            "FROM ".PREFIX."translations AS trs ".
            "LEFT JOIN ".PREFIX."languages AS t_lang ON trs.targetLang = t_lang.langID ".
            "LEFT JOIN ".PREFIX."abbr AS abbr ON trs.bookCode = abbr.code ".
            "WHERE trs.targetLang = :lang AND trs.bookProject = :bookProject AND trs.bookCode = :bookCode ".
            "ORDER BY trs.chapter, trs.chunk";

        return $this->db->select($sql, array(":lang" => $lang, ":bookProject" => $bookProject, ":bookCode" => $bookCode));
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

        return $this->db->select($sql, array(":eventID" => $eventID));
    }

    public function getComment($eventID, $chapter, $verse, $memberID)
    {
        $sql = "SELECT * FROM ".PREFIX."comments ".
            "WHERE eventID = :eventID AND chapter = :chapter AND verse = :verse AND memberID = :memberID";

        $prepare = array(
            ":eventID" => $eventID,
            ":chapter" => $chapter,
            ":verse" => $verse,
            ":memberID" => $memberID
        );

        return $this->db->select($sql, $prepare);
    }

    public function getCommentsByEvent($eventID, $chapter = null)
    {
        $sql = "SELECT cmts.*, mems.userName FROM ".PREFIX."comments AS cmts ".
            "LEFT JOIN ".PREFIX."members AS mems ON mems.memberID = cmts.memberID ".
            "WHERE cmts.eventID = :eventID ".
            ($chapter != null ? "AND cmts.chapter = :chapter " : "").
            "ORDER BY cmts.chapter, cmts.verse, cmts.cID";

        $prepare = array(":eventID" => $eventID);

        if($chapter != null)
            $prepare[":chapter"] = $chapter;

        return $this->db->select($sql, $prepare);
    }

    public function createComment($data)
    {
        $this->db->insert(PREFIX."comments",$data);
        return $this->db->lastInsertId('cID');
    }

    public function updateComment($data, $where)
    {
        return $this->db->update(PREFIX."comments", $data, $where);
    }

    public function deleteComment($where)
    {
        return $this->db->delete(PREFIX."comments", $where);
    }

}