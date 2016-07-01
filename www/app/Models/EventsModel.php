<?php
/**
 * Created by PhpStorm.
 * User: Maxim
 * Date: 05 Feb 2016
 * Time: 19:57
 */

namespace Models;

use Core\Model;
use Helpers\Constants\BookSources;
use Helpers\Constants\EventMembers;
use Helpers\Data;
use Helpers\Session;
use Helpers\Url;
use PDO;

class EventsModel extends Model
{
    public  function __construct()
    {
        parent::__construct();
    }

    /**
     * For getting data of a gateway project
     * @param $fields Requested fields could be * for all or comma separated list
     * @param $where array Example: array('id' => array('=', 1), 'name' => array('!=', 'John'))
     * @return array
     */
    public function getGatewayProject($fields, $where)
    {
        $sql = "SELECT $fields FROM ".PREFIX."gateway_projects ".
            "LEFT JOIN ".PREFIX."languages ".
            "ON ".PREFIX."gateway_projects.gwLang = ".PREFIX."languages.langID".
            " WHERE";
        $prepare = array();
        $i=0;

        foreach($where as $key=>$value)
        {
            $sql .= ($i>0 ? " AND " : " ")."$key ".$value[0]." :".preg_replace("/.*\./", "", $key);
            $prepare[':'.preg_replace("/.*\./", "", $key)] = $value[1];
            $i++;
        }

        return $this->db->select($sql, $prepare);
    }

    /**
     * For getting data of a sub event
     * @param $fields Requested fields could be * for all or comma separated list
     * @param $where array Example: array('id' => array('=', 1), 'name' => array('!=', 'John'))
     * @return array
     */
    public function getProject($fields, $where)
    {
        $sql = "SELECT $fields FROM ".PREFIX."projects ".
            "LEFT JOIN ".PREFIX."languages ".
            "ON ".PREFIX."projects.targetLang = ".PREFIX."languages.langID ".
            "WHERE ";
        $prepare = array();
        $i=0;

        foreach($where as $key=>$value)
        {
            $sql .= ($i>0 ? " AND " : " ")."$key ".$value[0]." :".preg_replace("/.*\./", "", $key);
            $prepare[':'.preg_replace("/.*\./", "", $key)] = $value[1];
            $i++;
        }

        return $this->db->select($sql, $prepare);
    }

    public function getProjects($memberID, $isSuperAdmin, $projectID = null)
    {
        $sql = "SELECT * FROM ".PREFIX."projects ".
            "LEFT JOIN ".PREFIX."languages ".
            "ON ".PREFIX."projects.targetLang = ".PREFIX."languages.langID ";

        $where = !$isSuperAdmin || $projectID != null ? "WHERE " : "";

        $sql .= $where;

        $prepare = array();

        if(!$isSuperAdmin)
        {
            $sql .= PREFIX."projects.gwProjectID IN ".
                "(SELECT gwProjectID FROM ".PREFIX."gateway_projects WHERE admins LIKE :memberID) ";
            $prepare[":memberID"] = '%"'.$memberID.'"%';
        }

        if($projectID != null)
        {
            $sql .= !$isSuperAdmin ? " AND " : " ";
            $sql .= PREFIX."projects.projectID=:projectID";
            $prepare[":projectID"] = $projectID;
        }

        return $this->db->select($sql, $prepare);
    }

    /**
     * Get Event Data
     * @param $projectID
     * @param $bookCode
     * @param bool $countMembers
     * @return array
     */
    public function getEvent($projectID, $bookCode, $countMembers = false)
    {
        $addFields = "";

        if($countMembers)
        {
            $addFields .= ", COUNT(DISTINCT vm_translators.memberID) AS translators, ".
                "COUNT(DISTINCT vm_checkers_l2.memberID) AS checkers_l2, COUNT(DISTINCT vm_checkers_l3.memberID) AS checkers_l3";
        }

        $sql = "SELECT ".PREFIX."events.*" . $addFields . " FROM ".PREFIX."events ".
            ($countMembers ?
            "LEFT JOIN ".PREFIX."translators ON ".PREFIX."translators.eventID=".PREFIX."events.eventID ".
            "LEFT JOIN ".PREFIX."checkers_l2 ON ".PREFIX."checkers_l2.eventID=".PREFIX."events.eventID ".
            "LEFT JOIN ".PREFIX."checkers_l3 ON ".PREFIX."checkers_l3.eventID=".PREFIX."events.eventID "
            : "").
            "WHERE projectID=:projectID AND bookCode=:bookCode";
        $prepare = array(":projectID" => $projectID, ":bookCode" => $bookCode);

        return $this->db->select($sql, $prepare);
    }

    /**
     * Get all books with events of a project
     * @param $projectID
     * @return array
     */
    public function getEventsByProject($projectID)
    {
        $sql = "SELECT vm_abbr.*, vm_events.*, COUNT(DISTINCT vm_translators.memberID) AS translators, ".
            "COUNT(DISTINCT vm_checkers_l2.memberID) AS checkers_l2, COUNT(DISTINCT vm_checkers_l3.memberID) AS checkers_l3 ".
            "FROM vm_abbr ".
            "LEFT JOIN vm_events ON vm_abbr.code=vm_events.bookCode AND (vm_events.projectID=:projectID OR vm_events.projectID is NULL) ".
            "LEFT JOIN vm_translators ON vm_translators.eventID=vm_events.eventID ".
            "LEFT JOIN vm_checkers_l2 ON vm_checkers_l2.eventID=vm_events.eventID ".
            "LEFT JOIN vm_checkers_l3 ON vm_checkers_l3.eventID=vm_events.eventID ".
            "GROUP BY vm_abbr.abbrID ORDER BY vm_abbr.abbrID";

        $prepare = array(":projectID" => $projectID);

        return $this->db->select($sql, $prepare);
    }

    /**
     * Get member with the event info
     * @param int $eventID
     * @param int $memberID
     * @return array
     */
    public function getEventMember($eventID, $memberID, $getInfo = false)
    {
        /*$sql = "SELECT cotrMember.memberID AS cotrMemberID, ".PREFIX."translators.memberID AS translators, "
            .PREFIX."checkers_l2.memberID AS checkers_l2, ".PREFIX."checkers_l3.memberID AS checkers_l3 "
            ."FROM vm_events "
            ."LEFT JOIN ".PREFIX."translators ON ".PREFIX."translators.eventID = ".PREFIX."events.eventID AND ".PREFIX."translators.memberID = :memberID "
            ."LEFT JOIN ".PREFIX."checkers_l2 ON ".PREFIX."checkers_l2.eventID = ".PREFIX."events.eventID AND ".PREFIX."checkers_l2.memberID = :memberID "
            ."LEFT JOIN ".PREFIX."checkers_l3 ON ".PREFIX."checkers_l3.eventID = ".PREFIX."events.eventID AND ".PREFIX."checkers_l3.memberID = :memberID "
            ."LEFT JOIN ".PREFIX."translators AS cotrMember ON ".PREFIX."translators.pairID = cotrMember.trID "
            ."WHERE ".PREFIX."events.eventID = :eventID";
        */

        $sql = "SELECT cotrMember.memberID AS cotrMemberID, ".PREFIX."translators.memberID AS translator, "
            ."checkers.checkerID AS checker, "
            .PREFIX."projects.projectID, ".PREFIX."projects.bookProject, ".PREFIX."projects.sourceLangID, ".PREFIX."projects.gwLang, ".PREFIX."projects.targetLang, ".PREFIX."projects.gwProjectID "
            .($getInfo ?
                ", ".PREFIX."events.eventID, ".PREFIX."events.state, ".PREFIX."events.bookCode, ".PREFIX."events.chapters, "
                ."t_lang.langName as tLang, s_lang.langName as sLang, ".PREFIX."abbr.name, ".PREFIX."abbr.abbrID " : "")
            ."FROM vm_events "
            ."LEFT JOIN ".PREFIX."translators ON ".PREFIX."translators.eventID = ".PREFIX."events.eventID AND ".PREFIX."translators.memberID = :memberID "
            ."LEFT JOIN ".PREFIX."translators AS checkers ON checkers.eventID = ".PREFIX."events.eventID AND checkers.checkerID = :memberID "
            ."LEFT JOIN ".PREFIX."translators AS cotrMember ON ".PREFIX."translators.pairID = cotrMember.trID "
            ."LEFT JOIN ".PREFIX."projects ON ".PREFIX."events.projectID = ".PREFIX."projects.projectID "
            .($getInfo ?
                "LEFT JOIN ".PREFIX."languages AS t_lang ON ".PREFIX."projects.targetLang = t_lang.langID ".
                "LEFT JOIN ".PREFIX."languages AS s_lang ON ".PREFIX."projects.sourceLangID = s_lang.langID ".
                "LEFT JOIN ".PREFIX."abbr ON ".PREFIX."events.bookCode = ".PREFIX."abbr.code " : "")
            ."WHERE ".PREFIX."events.eventID = :eventID";

        $prepare = array(":eventID" => $eventID, ":memberID" => $memberID);

        return $this->db->select($sql, $prepare);
    }

    /**
     * Get all events of a member or specific event
     * @param int $memberID
     * @param int $memberType
     * @param int null $eventID
     * @return array
     */
    public function getMemberEvents($memberID, $memberType, $eventID = null)
    {
        $events = array();
        $sql = "SELECT ".($memberType == EventMembers::TRANSLATOR ? PREFIX."translators.trID, "
                .PREFIX."translators.memberID AS myMemberID, ".PREFIX."translators.step, ".PREFIX."translators.checkerID, ".PREFIX."translators.checkDone, "
                .PREFIX."translators.currentChunk, ".PREFIX."translators.currentChapter, ".PREFIX."translators.translateDone, ".PREFIX."translators.lastTID, "
                ."cotranslator.trID AS cotrID, cotranslator.step AS cotrStep, cotranslator.currentChunk AS cotrCurrentChunk, "
                ."cotranslator.currentChapter AS cotrCurrentChapter, cotranslator.translateDone AS cotrTranslateDone, cotranslator.lastTID AS cotrLastTID, "
                ."mems.userName AS pairName, mems2.userName AS checkerName, " : "")
            .PREFIX."events.eventID, ".PREFIX."events.state, ".PREFIX."events.bookCode, ".PREFIX."events.chapters, "
            .PREFIX."projects.projectID, ".PREFIX."projects.bookProject, ".PREFIX."projects.sourceLangID, ".PREFIX."projects.gwLang, ".PREFIX."projects.targetLang, "
            ."t_lang.langName as tLang, s_lang.langName as sLang, ".PREFIX."abbr.name, ".PREFIX."abbr.abbrID FROM ";
        $mainTable = "";

        switch($memberType)
        {
            case EventMembers::TRANSLATOR:
                $mainTable = PREFIX."translators ";
                break;

            case EventMembers::L2_CHECKER:
                $mainTable = PREFIX."checkers_l2 ";
                break;

            case EventMembers::L3_CHECKER:
                $mainTable = PREFIX."checkers_l3 ";
                break;
        }

        $sql .= $mainTable.
            ($memberType == EventMembers::TRANSLATOR ?
                "LEFT JOIN ".PREFIX."translators AS cotranslator ON ".PREFIX."translators.pairID = cotranslator.trID ".
                "LEFT JOIN ".PREFIX."members AS mems ON cotranslator.memberID = mems.memberID ".
                "LEFT JOIN ".PREFIX."members AS mems2 ON ".PREFIX."translators.checkerID = mems2.memberID ": "").
            "LEFT JOIN ".PREFIX."events ON ".$mainTable.".eventID = ".PREFIX."events.eventID ".
            "LEFT JOIN ".PREFIX."projects ON ".PREFIX."events.projectID = ".PREFIX."projects.projectID ".
            "LEFT JOIN ".PREFIX."languages AS t_lang ON ".PREFIX."projects.targetLang = t_lang.langID ".
            "LEFT JOIN ".PREFIX."languages AS s_lang ON ".PREFIX."projects.sourceLangID = s_lang.langID ".
            "LEFT JOIN ".PREFIX."abbr ON ".PREFIX."events.bookCode = ".PREFIX."abbr.code ".
            "WHERE ".$mainTable.".memberID = :memberID ".
            (!is_null($eventID) ? " AND ".$mainTable.".eventID=:eventID" : "");

        $prepare = array();
        $prepare[":memberID"] = $memberID;

        if(!is_null($eventID))
            $prepare[":eventID"] = $eventID;

        return $this->db->select($sql, $prepare);
    }

    public function getMemberEventsForChecker($memberID, $eventID = null, $trMemberID = null)
    {
        $prepare = array(":memberID" => $memberID);
        if($eventID)
            $prepare[":eventID"] = $eventID;
        if($trMemberID)
            $prepare[":trMemberID"] = $trMemberID;

        $sql = "SELECT trs.*, ".PREFIX."members.userName, ".PREFIX."events.bookCode, ".PREFIX."events.chapters, ".
                "t_lang.langName AS tLang, s_lang.langName AS sLang, ".PREFIX."abbr.name AS bookName, ".PREFIX."abbr.abbrID, ".
                PREFIX."projects.sourceLangID, ".PREFIX."projects.bookProject ".
            "FROM ".PREFIX."translators AS trs ".
                "LEFT JOIN ".PREFIX."members ON trs.memberID = ".PREFIX."members.memberID ".
                "LEFT JOIN ".PREFIX."events ON ".PREFIX."events.eventID = trs.eventID ".
                "LEFT JOIN ".PREFIX."projects ON ".PREFIX."projects.projectID = ".PREFIX."events.projectID ".
                "LEFT JOIN ".PREFIX."languages AS t_lang ON ".PREFIX."projects.targetLang = t_lang.langID ".
                "LEFT JOIN ".PREFIX."languages AS s_lang ON ".PREFIX."projects.sourceLangID = s_lang.langID ".
                "LEFT JOIN ".PREFIX."abbr ON ".PREFIX."events.bookCode = ".PREFIX."abbr.code ".
            "WHERE trs.checkerID = :memberID AND trs.checkDone = false ".
                ($eventID ? "AND trs.eventID = :eventID " : " ").
                ($trMemberID ? "AND trs.memberID = :trMemberID " : " ");

        return $this->db->select($sql, $prepare);
    }


    public function getMemberEventsForAdmin($memberID)
    {
        $sql = "SELECT evnt.eventID, proj.bookProject, tLang.langName, abbr.name ".
            "FROM ".PREFIX."events AS evnt ".
                "LEFT JOIN ".PREFIX."projects AS proj ON proj.projectID = evnt.projectID ".
                "LEFT JOIN ".PREFIX."gateway_projects AS gwProj ON gwProj.gwProjectID = proj.gwProjectID ".
                "LEFT JOIN ".PREFIX."abbr AS abbr ON evnt.bookCode = abbr.code ".
                "LEFT JOIN ".PREFIX."languages AS tLang ON proj.targetLang = tLang.langID ".
            "WHERE gwProj.admins LIKE :memberID";

        return $this->db->select($sql, array(":memberID" => '%\"'.$memberID.'"%'));
    }


    /**
     * Get notifications for assigned events
     * @return array
     */
    public function getNotifications()
    {
        /*$sql = "SELECT trs.*, ".PREFIX."members.userName, ".PREFIX."events.bookCode, ".PREFIX."projects.bookProject, ".
                "t_lang.langName AS tLang, s_lang.langName AS sLang, ".PREFIX."abbr.name AS bookName ".
            "FROM ".PREFIX."translators AS trs ".
                "LEFT JOIN ".PREFIX."members ON trs.memberID = ".PREFIX."members.memberID ".
                "LEFT JOIN ".PREFIX."events ON ".PREFIX."events.eventID = trs.eventID ".
                "LEFT JOIN ".PREFIX."projects ON ".PREFIX."projects.projectID = ".PREFIX."events.projectID ".
                "LEFT JOIN ".PREFIX."languages AS t_lang ON ".PREFIX."projects.targetLang = t_lang.langID ".
                "LEFT JOIN ".PREFIX."languages AS s_lang ON ".PREFIX."projects.sourceLangID = s_lang.langID ".
                "LEFT JOIN ".PREFIX."abbr ON ".PREFIX."events.bookCode = ".PREFIX."abbr.code ".
            "WHERE trs.eventID IN( SELECT eventID FROM ".PREFIX."translators WHERE memberID = :memberID ) ".
                "AND trs.memberID != :memberID AND trs.trID NOT IN (SELECT pairID FROM ".PREFIX."translators WHERE memberID = :memberID) ".
                "AND (trs.step = 'keyword-check' OR trs.step = 'content-review') AND trs.checkerID = 0";*/

        $sql = "SELECT trs.*, ".PREFIX."members.userName, ".PREFIX."events.bookCode, ".PREFIX."projects.bookProject, ".
                "t_lang.langName AS tLang, s_lang.langName AS sLang, ".PREFIX."abbr.name AS bookName ".
                //"l2ch.memberID AS l2mID, l3ch.memberID AS l3mID ".
            "FROM ".PREFIX."translators AS trs ".
                "LEFT JOIN ".PREFIX."members ON trs.memberID = ".PREFIX."members.memberID ".
                "LEFT JOIN ".PREFIX."events ON ".PREFIX."events.eventID = trs.eventID ".
                "LEFT JOIN ".PREFIX."checkers_l2 AS l2ch ON l2ch.memberID = :memberID AND trs.eventID = l2ch.eventID ".
                "LEFT JOIN ".PREFIX."checkers_l3 AS l3ch ON l2ch.memberID = :memberID AND trs.eventID = l3ch.eventID ".
                "LEFT JOIN ".PREFIX."projects ON ".PREFIX."projects.projectID = ".PREFIX."events.projectID ".
                "LEFT JOIN ".PREFIX."languages AS t_lang ON ".PREFIX."projects.targetLang = t_lang.langID ".
                "LEFT JOIN ".PREFIX."languages AS s_lang ON ".PREFIX."projects.sourceLangID = s_lang.langID ".
                "LEFT JOIN ".PREFIX."abbr ON ".PREFIX."events.bookCode = vm_abbr.code ".
            "WHERE (trs.eventID IN(SELECT eventID FROM ".PREFIX."translators WHERE memberID = :memberID) ".
                "OR trs.eventID IN(SELECT eventID FROM ".PREFIX."checkers_l2 WHERE memberID = :memberID) ".
                "OR trs.eventID IN(SELECT eventID FROM ".PREFIX."checkers_l3 WHERE memberID = :memberID)) ".
            "AND trs.memberID != :memberID ".
            //"AND trs.trID NOT IN(SELECT pairID FROM ".PREFIX."translators WHERE memberID = :memberID) ".
            "AND(trs.step = 'keyword-check' OR trs.step = 'content-review') ".
            "AND trs.checkerID = 0";

        return $this->db->select($sql, array(":memberID" => Session::get("memberID")));
    }


    public function getAllNotifications($langs = array("en")) {

        if(is_array($langs) && !empty($langs))
        {
            foreach($langs as &$val)
                $val = $this->db->quote($val);
            $in = implode(',',$langs);

            $sql = "SELECT trs.*, ".PREFIX."members.userName, ".PREFIX."events.bookCode, ".PREFIX."projects.bookProject, ".
                "t_lang.langName AS tLang, s_lang.langName AS sLang, ".PREFIX."abbr.name AS bookName ".
                //"l2ch.memberID AS l2mID, l3ch.memberID AS l3mID ".
                "FROM ".PREFIX."translators AS trs ".
                "LEFT JOIN ".PREFIX."members ON trs.memberID = ".PREFIX."members.memberID ".
                "LEFT JOIN ".PREFIX."events ON ".PREFIX."events.eventID = trs.eventID ".
                //"LEFT JOIN ".PREFIX."checkers_l2 AS l2ch ON l2ch.memberID = :memberID AND trs.eventID = l2ch.eventID ".
                //"LEFT JOIN ".PREFIX."checkers_l3 AS l3ch ON l2ch.memberID = :memberID AND trs.eventID = l3ch.eventID ".
                "LEFT JOIN ".PREFIX."projects ON ".PREFIX."projects.projectID = ".PREFIX."events.projectID ".
                "LEFT JOIN ".PREFIX."languages AS t_lang ON ".PREFIX."projects.targetLang = t_lang.langID ".
                "LEFT JOIN ".PREFIX."languages AS s_lang ON ".PREFIX."projects.sourceLangID = s_lang.langID ".
                "LEFT JOIN ".PREFIX."abbr ON ".PREFIX."events.bookCode = vm_abbr.code ".
                "WHERE (".PREFIX."projects.gwLang IN($in) OR ".PREFIX."projects.targetLang IN($in)) ".
                "AND trs.memberID != :memberID ".
                //"AND trs.trID NOT IN(SELECT pairID FROM ".PREFIX."translators WHERE memberID = :memberID) ".
                "AND(trs.step = 'keyword-check' OR trs.step = 'content-review') ".
                "AND trs.checkerID = 0";

            return $this->db->select($sql, array(":memberID" => Session::get("memberID")));
        }
    }

    /** Get list of all languages
     * @param null $isGW (true - gateway, false - other, null - all)
     * @return array
     */
    public function getAllLanguages($isGW = null)
    {
        $where = "";
        $prepare = array();
        if($isGW !== null)
        {
            $where = "WHERE isGW = :isGW";
            $prepare[":isGW"] = $isGW;
        }

        $sql = "SELECT langID, langName FROM ".PREFIX."languages $where ORDER BY `langID` ASC";
        return $this->db->select($sql, $prepare);
    }

    /**
     * Get Gateway languages assigned to admin
     * @param string $memberID
     * @return array
     */
    public function getMemberGwLanguages($memberID)
    {
        $sql = "SELECT * FROM ".PREFIX."gateway_projects LEFT JOIN ".PREFIX."languages ".
            "ON ".PREFIX."gateway_projects.gwLang=".PREFIX."languages.langID ".
            "WHERE ".PREFIX."gateway_projects.admins LIKE :memberID ".
            "GROUP BY ".PREFIX."gateway_projects.gwLang ORDER BY ".PREFIX."languages.langID";

        $prepare = array(":memberID" => '%"'.$memberID.'"%');

        return $this->db->select($sql, $prepare);
    }

    /**
     * Get list of other languages
     * @param string $memberID
     * @param string $gwLang
     * @return array
     */
    public function getTargetLanguages($memberID, $gwLang)
    {
        $adminPart = "WHERE ".PREFIX."languages.gwLang IN ".
            "(SELECT langName FROM ".PREFIX."languages WHERE langID IN ".
            "(SELECT gwLang FROM ".PREFIX."gateway_projects WHERE admins LIKE :memberID AND gwLang=:gwLang))";

        $sql = "SELECT * FROM vm_languages ".
            (Session::get("isSuperAdmin") ? "WHERE ".PREFIX."languages.gwLang IN (SELECT langName FROM ".PREFIX."languages WHERE langID=:gwLang)" : $adminPart).
            " ORDER BY langID";

        $prepare = array();
        $prepare[":gwLang"] = $gwLang;
        if(!Session::get("isSuperAdmin"))
        {
            $prepare[":memberID"] = '%"'.$memberID.'"%';
        }
        return $this->db->select($sql, $prepare);
    }

    /**
     * Get source translations
     * @return array
     */
    public function getSourceTranslations()
    {
        $in = "('" . join("', '", array_keys(BookSources::catalog)) . "')";
        $langNames = $this->db->select("SELECT langID, langName FROM ".PREFIX."languages WHERE langID IN $in", array(), PDO::FETCH_KEY_PAIR);

        $sls = array();
        foreach (BookSources::catalog as $lang => $books) {
            foreach ($books as $book) {
                $elm = new \stdClass();
                $elm->langID = $lang;
                $elm->langName = $langNames[$lang];
                $elm->bookProject = $book;

                $sls[] = $elm;
            }
        }

        return $sls;

        /*return $this->db->select("SELECT ".PREFIX."books.bookProject, ".PREFIX."languages.langName, ".PREFIX."languages.langID ".
            "FROM ".PREFIX."books
            LEFT JOIN ".PREFIX."languages ON ".PREFIX."books.gwLang=".PREFIX."languages.langID
            GROUP BY ".PREFIX."books.gwLang, ".PREFIX."books.bookProject ORDER BY ".PREFIX."languages.langName");*/
    }

    /**
     * Get members that can write notes on translation
     * @param int $tID
     * @param int $memberID
     * @return array
     */
    public function getTranslationCheckers($tID, $memberID)
    {
        $sql = "SELECT ts.translatedVerses, trs1.checkerID, trs2.memberID AS pairMemberID, l2.memberID AS l2memberID, l3.memberID AS l3memberID ".
                "FROM `vm_translations` AS ts ".
                "LEFT JOIN vm_translators AS trs1 ON ts.trID = trs1.trID ".
                "LEFT JOIN vm_translators AS trs2 ON trs1.pairID = trs2.trID ".
                "LEFT JOIN vm_checkers_l2 AS l2 ON ts.eventID = l2.eventID AND l2.memberID = :memberID ".
                "LEFT JOIN vm_checkers_l3 AS l3 ON ts.eventID = l3.eventID AND l3.memberID = :memberID ".
                "WHERE tID = :tID";

        $prepare = array(":memberID" => $memberID, ":tID" => $tID);

        return $this->db->select($sql, $prepare);
    }


    public function getEventMemberInfo($eventID, $memberID)
    {
        $sql = "SELECT trs.memberID AS translator, l2.memberID AS l2checker, l3.memberID AS l3checker ".
            "FROM ".PREFIX."events AS evnt ".
            "LEFT JOIN ".PREFIX."translators AS trs ON evnt.eventID = trs.eventID AND trs.memberID = :memberID ".
            "LEFT JOIN ".PREFIX."checkers_l2 AS l2 ON evnt.eventID = l2.eventID AND l2.memberID = :memberID ".
            "LEFT JOIN ".PREFIX."checkers_l3 AS l3 ON evnt.eventID = l3.eventID AND l3.memberID = :memberID ".
            "WHERE evnt.eventID = :eventID";

        $prepare = array(":memberID" => $memberID, ":eventID" => $eventID);

        return $this->db->select($sql, $prepare);
    }


    /**
     * Get book source from unfolding word api
     * @param string $bookCode
     * @param string $sourceLang
     * @param string $bookProject
     * @return mixed
     */
    public function getSourceBookFromApi($bookCode, $sourceLang, $bookProject)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.unfoldingword.org/ts/txt/2/".$bookCode."/".$sourceLang."/".$bookProject."/source.json");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $source = curl_exec($ch);
        curl_close($ch);
        return $source;
    }

    public function getSourceBookFromApiUSFM($bookProject, $bookNum, $bookCode, $sourceLang = "en")
    {
        $ch = curl_init();

        switch ($sourceLang)
        {
            case "ru":
                curl_setopt($ch, CURLOPT_URL, "https://api.unfoldingword.org/pdb/txt/1/rsb-ru/".sprintf("%02d", $bookNum)."-".strtoupper($bookCode).".usfm");
                break;

            case "ar":
                curl_setopt($ch, CURLOPT_URL, "https://api.unfoldingword.org/pdb/txt/1/avd-ar/".sprintf("%02d", $bookNum)."-".strtoupper($bookCode).".usfm");
                break;

            case "sr-Latn":
                curl_setopt($ch, CURLOPT_URL, "https://api.unfoldingword.org/pdb/txt/1/dkl-sr-Latn/".sprintf("%02d", $bookNum)."-".strtoupper($bookCode).".usfm");
                break;

            case "hu":
                curl_setopt($ch, CURLOPT_URL, "https://api.unfoldingword.org/pdb/txt/1/kar-hu/".sprintf("%02d", $bookNum)."-".strtoupper($bookCode).".usfm");
                break;

            default:
                curl_setopt($ch, CURLOPT_URL, "https://api.unfoldingword.org/".$bookProject."/txt/1/".$bookProject."-en/".sprintf("%02d", $bookNum)."-".strtoupper($bookCode).".usfm");
                break;
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $source = curl_exec($ch);
        curl_close($ch);
        return $source;
    }


    public function getTWcatalog($book, $lang = "en")
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.unfoldingword.org/ts/txt/2/".$book."/".$lang."/tw_cat.json");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $cat = curl_exec($ch);
        curl_close($ch);
        return $cat;
    }

    public function getTWords($lang = "en")
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.unfoldingword.org/ts/txt/2/bible/".$lang."/terms.json");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $cat = curl_exec($ch);
        curl_close($ch);
        return $cat;
    }


    /** Get translation of translator in event
     * (all - if tID and chapter null, chunk - if tID not null, chapter - if chapter not null)
     * @param int $trID
     * @param int $tID
     * @param int $chapter
     * @return array
     */
    public function getTranslation($trID, $tID = null, $chapter = null)
    {
        $where = " trID = :trID";
        $prepare = array(":trID" => $trID);
        if($tID) {
            $where .= " AND tID = :tID ";
            $prepare[":tID"] = $tID;
        }
        else
        {
            if($chapter) {
                $where .= " AND chapter = :chapter ";
                $prepare[":chapter"] = $chapter;
            }
        }

        return $this->db->select("SELECT * FROM ".PREFIX."translations WHERE".$where." ORDER BY chunk", $prepare);
    }

    public function getBookInfo($bookCode)
    {
        $sql = "SELECT * FROM ".PREFIX."abbr ".
            "WHERE code=:bookCode";

        return $this->db->select($sql, array(":bookCode" => $bookCode));
    }

    /**
     * Create gateway project
     * @param array $data
     * @return string
     */
    public function createGatewayProject($data)
    {
        $this->db->insert(PREFIX."gateway_projects",$data);
        return $this->db->lastInsertId('gwProjectID');
    }

    /**
     * Create gateway project
     * @param array $data
     * @return string
     */
    public function updateGatewayProject($data, $where)
    {
        return $this->db->update(PREFIX."gateway_projects", $data, $where);
    }

    /**
     * Create project
     * @param array $data
     * @return string
     */
    public function createProject($data)
    {
        $this->db->insert(PREFIX."projects",$data);
        return $this->db->lastInsertId('projectID');
    }

    /**
     * Create event
     * @param array $data
     * @return string
     */
    public function createEvent($data)
    {
        $this->db->insert(PREFIX."events",$data);
        return $this->db->lastInsertId('eventID');
    }

    /**
     * Add member as new translator for event
     * @param array $data
     * @param bool $addPair
     * @param int $lastTrID
     * @return string
     */
    public function addTranslator($data, $addPair = false, $lastTrID = 0)
    {
        try
        {
            $this->db->insert(PREFIX."translators",$data);
        } catch(\PDOException $e)
        {
            return $e->getMessage();
        }

        $trID = $this->db->lastInsertId('trID');

        if($addPair)
        {
            $this->db->update(PREFIX."translators", array("pairID" => $trID), array("trID" => $lastTrID));
            $this->db->update(PREFIX."translators", array("pairID" => $lastTrID), array("trID" => $trID));
        }
        return $trID;
    }

    /**
     * Add member as new Level 2 checker for event
     * @param array $data
     * @param array $checkerData
     * @param bool $shouldUpdateChecker
     * @return string
     */
    public function addL2Checker($data, $checkerData)
    {
        $oldData = $checkerData;

        $checkerData["education"] = json_encode($checkerData["education"]);
        $checkerData["ed_area"] = json_encode($checkerData["ed_area"]);
        $checkerData["church_role"] = json_encode($checkerData["church_role"]);

        $this->db->update(PREFIX."profile", $checkerData, array("mID" => Session::get("memberID")));
        $profile = Session::get("profile");

        foreach ($oldData as $key => $value)
            $profile[$key] = $value;

        Session::set("profile", $profile);

        try
        {
            $this->db->insert(PREFIX."checkers_l2",$data);
        } catch(\PDOException $e)
        {
            return $e->getMessage();
        }
        return $this->db->lastInsertId('l2chID');
    }

    /**
     * Add member as new Level 3 checker for event
     * @param array $data
     * @param array $checkerData
     * @param bool $shouldUpdateChecker
     * @return string
     */
    public function addL3Checker($data, $checkerData)
    {
        $oldData = $checkerData;

        $checkerData["education"] = json_encode($checkerData["education"]);
        $checkerData["ed_area"] = json_encode($checkerData["ed_area"]);
        $checkerData["church_role"] = json_encode($checkerData["church_role"]);

        $this->db->update(PREFIX."profile", $checkerData, array("mID" => Session::get("memberID")));
        $profile = Session::get("profile");

        foreach ($oldData as $key => $value)
            $profile[$key] = $value;

        Session::set("profile", $profile);

        try
        {
            $this->db->insert(PREFIX."checkers_l3",$data);
        } catch(\PDOException $e)
        {
            return $e->getMessage();
        }
        return $this->db->lastInsertId('l3chID');
    }

    /**
     * Create translation record
     * @param array $data
     * @return string
     */
    public function createTranslation($data)
    {
        $this->db->insert(PREFIX."translations",$data);
        return $this->db->lastInsertId('tID');
    }


    /** Update translation
     * @param array $data
     * @param array $where
     * @return int
     */
    public function updateTranslation($data, $where)
    {
        return $this->db->update(PREFIX."translations", $data, $where);
    }

    /**
     * Update event
     * @param array $data
     * @param array $where
     * @return int
     */
    public  function updateEvent($data, $where)
    {
        return $this->db->update(PREFIX."events", $data, $where);
    }

    /**
     * Update translator
     * @param array $data
     * @param array $where
     * @return int
     */
    public function updateTranslator($data, $where)
    {
        return $this->db->update(PREFIX."translators", $data, $where);
    }
}