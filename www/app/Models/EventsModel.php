<?php
/**
 * Created by PhpStorm.
 * User: Maxim
 * Date: 05 Feb 2016
 * Time: 19:57
 */

namespace App\Models;

use Database\Model;
use Helpers\Constants\BookSources;
use Helpers\Constants\EventMembers;
use Helpers\Constants\EventStates;
use Helpers\Data;
use Helpers\Session;
use Helpers\Url;
use PDO;
use DB;

class EventsModel extends Model
{
    protected $table = 'events';
    protected $primaryKey = 'eventID';

    public  function __construct()
    {
        parent::__construct();
    }

    /**
     * Get gateway project
     * @param array $select An array of fields
     * @param array $where Single/Multidimentional array with where params (field, operator, value, logical)
     * @return array|static[]
     */
    public function getGatewayProject($select = array("*"), $where = array())
    {
        $builder = $this->db->table("gateway_projects");

        foreach ($where as $item) {
            if(is_array($item))
            {
                call_user_func_array(array($builder, "where"), $item);
            }
            else
            {
                call_user_func_array(array($builder, "where"), $where);
                break;
            }
        }

        return $builder
            ->leftJoin("languages", "gateway_projects.gwLang", "=", "languages.langID")
            ->select($select)->get();
    }


    /**
     * Get project
     * @param array $select An array of fields
     * @param array $where Single/Multidimentional array with where params (field, operator, value, logical)
     * @return array|static[]
     */
    public function getProject(array $select, array $where)
    {
        $builder = $this->db->table("projects");

        foreach ($where as $item) {
            if(is_array($item))
            {
                call_user_func_array(array($builder, "where"), $item);
            }
            else
            {
                call_user_func_array(array($builder, "where"), $where);
                break;
            }
        }

        return $builder
            ->leftJoin("languages", "languages.langID", "=", "projects.targetLang")
            ->select($select)->get();
    }

    public function getProjects($memberID, $isSuperAdmin, $projectID = null)
    {
        $sql = "SELECT ".PREFIX."projects.*, tLang.gwLang, tLang.langName as tLang, sLang.langName as sLang ".
            "FROM ".PREFIX."projects ".
            "LEFT JOIN ".PREFIX."languages AS tLang ON ".PREFIX."projects.targetLang = tLang.langID ".
            "LEFT JOIN ".PREFIX."languages AS sLang ON ".PREFIX."projects.sourceLangID = sLang.langID ";

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

        $sql .= " ORDER BY ".PREFIX."projects.targetLang";

        return $this->db->select($sql, $prepare);
    }

    /**
     * Get Event Data by eventID OR by projectID and bookCode
     * @param $eventID
     * @param $projectID
     * @param $bookCode
     * @param bool $countMembers
     * @return array
     */
    public function getEvent($eventID = null, $projectID, $bookCode, $countMembers = false)
    {
        $builder = $this->db->table("events");
        $select = ["events.*"];
        if($countMembers)
        {
            $select[] = $this->db->raw("COUNT(DISTINCT ".PREFIX."translators.memberID) AS translators");
            $select[] = $this->db->raw("COUNT(DISTINCT ".PREFIX."checkers_l2.memberID) AS checkers_l2");
            $select[] = $this->db->raw("COUNT(DISTINCT ".PREFIX."checkers_l3.memberID) AS checkers_l3");

            $builder
                ->leftJoin("translators", "events.eventID", "=", "translators.eventID")
                ->leftJoin("checkers_l2", "events.eventID", "=", "checkers_l2.eventID")
                ->leftJoin("checkers_l3", "events.eventID", "=", "checkers_l3.eventID");
        }

        if($eventID)
            $builder->where("events.eventID", $eventID);
        else
            $builder->where("events.projectID", $projectID)
                ->where("events.bookCode", $bookCode);

        return $builder->select($select)->get();
    }

    /**
     * Get all books with events of a project
     * @param $projectID
     * @return array
     */
    public function getEventsByProject($projectID)
    {
        $sql = "SELECT ".PREFIX."abbr.*, ".PREFIX."events.*, COUNT(DISTINCT ".PREFIX."translators.memberID) AS translators, ".
            "COUNT(DISTINCT ".PREFIX."checkers_l2.memberID) AS checkers_l2, COUNT(DISTINCT ".PREFIX."checkers_l3.memberID) AS checkers_l3 ".
            "FROM ".PREFIX."abbr ".
            "LEFT JOIN ".PREFIX."events ON vm_abbr.code=vm_events.bookCode AND (vm_events.projectID=:projectID OR vm_events.projectID is NULL) ".
            "LEFT JOIN ".PREFIX."translators ON ".PREFIX."translators.eventID=".PREFIX."events.eventID ".
            "LEFT JOIN ".PREFIX."checkers_l2 ON ".PREFIX."checkers_l2.eventID=".PREFIX."events.eventID ".
            "LEFT JOIN ".PREFIX."checkers_l3 ON ".PREFIX."checkers_l3.eventID=".PREFIX."events.eventID ".
            "GROUP BY ".PREFIX."abbr.abbrID ORDER BY ".PREFIX."abbr.abbrID";

        $prepare = array(":projectID" => $projectID);

        return $this->db->select($sql, $prepare);
    }

    /**
     * Get member with the event in which he is participating
     * @param int $eventID
     * @param int $memberID
     * @return array
     */
    public function getEventMember($eventID, $memberID, $getInfo = false)
    {
        $sql = "SELECT cotrMember.memberID AS cotrMemberID, ".PREFIX."translators.memberID AS translator, "
            ."checkers.checkerID AS checker, "
            .PREFIX."checkers_l2.memberID AS checker_l2, ".PREFIX."checkers_l3.memberID AS checker_l3, "
            .PREFIX."projects.projectID, ".PREFIX."projects.bookProject, ".PREFIX."projects.sourceLangID, ".PREFIX."projects.gwLang, ".PREFIX."projects.targetLang, ".PREFIX."projects.gwProjectID "
            .($getInfo ?
                ", ".PREFIX."events.eventID, ".PREFIX."events.state, ".PREFIX."events.bookCode, ".PREFIX."events.chapters, "
                ."t_lang.langName as tLang, s_lang.langName as sLang, ".PREFIX."abbr.name, ".PREFIX."abbr.abbrID " : "")
            ."FROM vm_events "
            ."LEFT JOIN ".PREFIX."translators ON ".PREFIX."translators.eventID = ".PREFIX."events.eventID AND ".PREFIX."translators.memberID = :memberID "
            ."LEFT JOIN ".PREFIX."translators AS checkers ON checkers.eventID = ".PREFIX."events.eventID AND checkers.checkerID = :memberID "
            ."LEFT JOIN ".PREFIX."translators AS cotrMember ON ".PREFIX."translators.pairID = cotrMember.trID "
            ."LEFT JOIN ".PREFIX."checkers_l2 ON ".PREFIX."checkers_l2.eventID = ".PREFIX."events.eventID AND ".PREFIX."checkers_l2.memberID = :memberID "
            ."LEFT JOIN ".PREFIX."checkers_l3 ON ".PREFIX."checkers_l3.eventID = ".PREFIX."events.eventID AND ".PREFIX."checkers_l3.memberID = :memberID "
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
     * @param bool true $includeFinished
     * @return array
     */
    public function getMemberEvents($memberID, $memberType, $eventID = null, $includeFinished = true)
    {
        $events = array();
        $sql = "SELECT ".($memberType == EventMembers::TRANSLATOR ? PREFIX."translators.trID, "
                .PREFIX."translators.memberID AS myMemberID, ".PREFIX."translators.step, ".PREFIX."translators.checkerID, ".PREFIX."translators.checkDone, "
                .PREFIX."translators.currentChunk, ".PREFIX."translators.currentChapter, ".PREFIX."translators.peerReady, ".PREFIX."translators.peerChapter, "
                .PREFIX."translators.translateDone, ".PREFIX."translators.lastTID, "
                ."cotranslator.memberID AS cotrMemberID, cotranslator.trID AS cotrID, cotranslator.step AS cotrStep, cotranslator.currentChunk AS cotrCurrentChunk, "
                ."cotranslator.currentChapter AS cotrCurrentChapter, cotranslator.peerReady AS cotrPeerReady, cotranslator.peerChapter AS cotrPeerChapter, "
                ."cotranslator.translateDone AS cotrTranslateDone, cotranslator.lastTID AS cotrLastTID, "
                ."mems.userName AS pairName, mems2.userName AS checkerName, "
                ."(SELECT COUNT(*) FROM ".PREFIX."translators AS all_trs WHERE all_trs.eventID = ".PREFIX."translators.eventID ) AS currTrs, ": "")
            .PREFIX."events.eventID, ".PREFIX."events.state, ".PREFIX."events.bookCode, ".PREFIX."events.chapters, ".PREFIX."events.dateFrom, ".PREFIX."events.dateTo, ".PREFIX."events.translatorsNum, "
            .PREFIX."events.adminID, facilitator.userName AS facilUname, facilitator.firstName AS facilFname, facilitator.lastName AS facilLname, "
            .PREFIX."projects.projectID, ".PREFIX."projects.bookProject, ".PREFIX."projects.sourceLangID, ".PREFIX."projects.gwLang, ".PREFIX."projects.targetLang, "
            .PREFIX."projects.sourceBible, t_lang.langName as tLang, s_lang.langName as sLang, ".PREFIX."abbr.name, ".PREFIX."abbr.abbrID FROM ";
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
                "LEFT JOIN ".PREFIX."translators AS cotranslator ON cotranslator.trID = ".PREFIX."translators.pairID ".
                "LEFT JOIN ".PREFIX."members AS mems ON cotranslator.memberID = mems.memberID ".
                "LEFT JOIN ".PREFIX."members AS mems2 ON mems2.memberID = ".PREFIX."translators.checkerID " : "").
            "LEFT JOIN ".PREFIX."events ON ".$mainTable.".eventID = ".PREFIX."events.eventID ".
            "LEFT JOIN ".PREFIX."members AS facilitator ON facilitator.memberID = ".PREFIX."events.adminID ".
            "LEFT JOIN ".PREFIX."projects ON ".PREFIX."events.projectID = ".PREFIX."projects.projectID ".
            "LEFT JOIN ".PREFIX."languages AS t_lang ON ".PREFIX."projects.targetLang = t_lang.langID ".
            "LEFT JOIN ".PREFIX."languages AS s_lang ON ".PREFIX."projects.sourceLangID = s_lang.langID ".
            "LEFT JOIN ".PREFIX."abbr ON ".PREFIX."events.bookCode = ".PREFIX."abbr.code ".
            "WHERE ".$mainTable.".memberID = :memberID ".
            (!is_null($eventID) ? " AND ".$mainTable.".eventID=:eventID " : " ").
            ($memberType == EventMembers::TRANSLATOR && !$includeFinished ? " AND ".PREFIX."translators.step != 'finished' " : " ").
            "ORDER BY tLang, ".PREFIX."abbr.abbrID";

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

        $sql = "SELECT trs.*, ".PREFIX."members.userName, cotr.peerChapter AS cotrPeerChapter, ".PREFIX."events.bookCode, ".PREFIX."events.chapters, ".
                "t_lang.langName AS tLang, s_lang.langName AS sLang, ".PREFIX."abbr.name AS bookName, ".PREFIX."abbr.abbrID, ".
                PREFIX."events.adminID, facilitator.userName AS facilUname, facilitator.firstName AS facilFname, facilitator.lastName AS facilLname, ".
                PREFIX."projects.sourceLangID, ".PREFIX."projects.bookProject, ".PREFIX."projects.sourceBible, ".PREFIX."projects.gwLang, ".PREFIX."projects.targetLang ".
            "FROM ".PREFIX."translators AS trs ".
                "LEFT JOIN ".PREFIX."translators AS cotr ON trs.pairID = cotr.trID ".
                "LEFT JOIN ".PREFIX."members ON trs.memberID = ".PREFIX."members.memberID ".
                "LEFT JOIN ".PREFIX."events ON ".PREFIX."events.eventID = trs.eventID ".
                "LEFT JOIN ".PREFIX."members AS facilitator ON facilitator.memberID = ".PREFIX."events.adminID ".
                "LEFT JOIN ".PREFIX."projects ON ".PREFIX."projects.projectID = ".PREFIX."events.projectID ".
                "LEFT JOIN ".PREFIX."languages AS t_lang ON ".PREFIX."projects.targetLang = t_lang.langID ".
                "LEFT JOIN ".PREFIX."languages AS s_lang ON ".PREFIX."projects.sourceLangID = s_lang.langID ".
                "LEFT JOIN ".PREFIX."abbr ON ".PREFIX."events.bookCode = ".PREFIX."abbr.code ".
            "WHERE trs.checkerID = :memberID AND trs.checkDone = false ".
                ($eventID ? "AND trs.eventID = :eventID " : " ").
                ($trMemberID ? "AND trs.memberID = :trMemberID " : " ").
            "ORDER BY tLang, ".PREFIX."abbr.abbrID";

        return $this->db->select($sql, $prepare);
    }

    /**
     * Get event/list of events for facilitator
     * @param $memberID
     * @param $eventID
     * @return array
     */
    public function getMemberEventsForAdmin($memberID, $eventID = null)
    {
        $sql = "SELECT evnt.*, proj.bookProject, proj.sourceBible, proj.sourceLangID, tLang.langName, sLang.langName AS sLang, abbr.abbrID, abbr.name, ".
            "(SELECT COUNT(*) FROM ".PREFIX."translators AS all_trs WHERE all_trs.eventID = evnt.eventID) AS trsCnt, ".
            "(SELECT COUNT(*) FROM ".PREFIX."checkers_l2 AS all_chl2 WHERE all_chl2.eventID = evnt.eventID) AS chl2Cnt, ".
            "(SELECT COUNT(*) FROM ".PREFIX."checkers_l3 AS all_chl3 WHERE all_chl3.eventID = evnt.eventID) AS chl3Cnt ".
            "FROM ".PREFIX."events AS evnt ".
            "LEFT JOIN ".PREFIX."projects AS proj ON proj.projectID = evnt.projectID ".
            "LEFT JOIN ".PREFIX."gateway_projects AS gwProj ON gwProj.gwProjectID = proj.gwProjectID ".
            "LEFT JOIN ".PREFIX."abbr AS abbr ON evnt.bookCode = abbr.code ".
            "LEFT JOIN ".PREFIX."languages AS tLang ON proj.targetLang = tLang.langID ".
            "LEFT JOIN ".PREFIX."languages AS sLang ON proj.sourceLangID = sLang.langID ".
            "WHERE gwProj.admins LIKE :memberID ".
            ($eventID ? "AND evnt.eventID = :eventID " : "").
            "ORDER BY evnt.state, tLang.langName, abbr.abbrID";

        $prepare = array(":memberID" => '%\"'.$memberID.'"%');
        if($eventID) $prepare[":eventID"] = $eventID;

        return $this->db->select($sql, $prepare);
    }

    public function getNewEvents($langs, $memberID = null)
    {
        $arr = array();

        if(is_array($langs) && !empty($langs)) {
            $in = DB::quoteArray($langs);

            $sql = "SELECT evnt.*, proj.bookProject, proj.sourceLangID, tLang.langName AS tLang, sLang.langName AS sLang, abbr.abbrID, abbr.name, ".
                "(SELECT COUNT(*) FROM ".PREFIX."translators AS all_trs WHERE all_trs.eventID = evnt.eventID) AS trsCnt, ".
                "(SELECT COUNT(*) FROM ".PREFIX."checkers_l2 AS all_chl2 WHERE all_chl2.eventID = evnt.eventID) AS chl2Cnt, ".
                "(SELECT COUNT(*) FROM ".PREFIX."checkers_l3 AS all_chl3 WHERE all_chl3.eventID = evnt.eventID) AS chl3Cnt, ".
                "evnt.adminID, facilitator.userName AS facilUname, facilitator.firstName AS facilFname, facilitator.lastName AS facilLname ".
                "FROM ".PREFIX."events AS evnt ".
                "LEFT JOIN ".PREFIX."projects AS proj ON proj.projectID = evnt.projectID ".
                "LEFT JOIN ".PREFIX."abbr AS abbr ON evnt.bookCode = abbr.code ".
                "LEFT JOIN ".PREFIX."languages AS tLang ON proj.targetLang = tLang.langID ".
                "LEFT JOIN ".PREFIX."languages AS sLang ON proj.sourceLangID = sLang.langID ".
                "LEFT JOIN ".PREFIX."members AS facilitator ON facilitator.memberID = evnt.adminID ".
                ($memberID ?
                    "LEFT JOIN ".PREFIX."translators AS trs ON (trs.eventID = evnt.eventID AND trs.memberID = :memberID) ".
                    "LEFT JOIN ".PREFIX."checkers_l2 AS chl2 ON (chl2.eventID = evnt.eventID AND chl2.memberID = :memberID) ".
                    "LEFT JOIN ".PREFIX."checkers_l3 AS chl3 ON (chl3.eventID = evnt.eventID AND chl3.memberID = :memberID) " : "").
                "WHERE (evnt.state = :state OR evnt.state = :state2 OR evnt.state = :state3) ".
                    "AND (proj.gwLang IN ($in) OR proj.targetLang IN ($in)) ".
                ($memberID ?
                    "AND (trs.memberID IS NULL AND chl2.memberID IS NULL AND chl3.memberID IS NULL) " : "").
            "ORDER BY evnt.state, abbr.abbrID";

            $prepare = array(
                ":state" => EventStates::STARTED,
                ":state2" => EventStates::L2_RECRUIT,
                ":state3" => EventStates::L3_RECRUIT,
            );

            if($memberID)
                $prepare[":memberID"] = $memberID;

            $arr = $this->db->select($sql, $prepare);
        }

        return $arr;
    }


    public function getMembersForEvent($eventID)
    {
        $this->db->setFetchMode(PDO::FETCH_ASSOC);
        $res = $this->db->table("translators")
            ->select("translators.*", "members.userName")
            ->leftJoin("members", "translators.memberID", "=", "members.memberID")
            ->where("translators.eventID", $eventID)
            ->orderBy("members.userName")->get();

        $this->db->setFetchMode(PDO::FETCH_CLASS);

        return $res;
    }

    /**
     * Get notifications for assigned events
     * @return array
     */
    public function getNotifications()
    {
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

        return DB::select($sql, array(":memberID" => Session::get("memberID")));
    }


    public function getAllNotifications($langs = array("en")) {

        if(is_array($langs) && !empty($langs))
        {
            $in = $this->db->quoteArray($langs);

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
        $builder = $this->db->table("languages");

        if($isGW !== null)
        {
            $builder->where("isGW", $isGW);
        }

        return $builder->select("languages.langID", "languages.langName", "languages.angName", "gateway_projects.gwProjectID")
            ->leftJoin("gateway_projects", "languages.langID", "=", "gateway_projects.gwLang")
            ->orderBy("languages.langID")->get();
    }

    /**
     * Get Gateway languages assigned to admin
     * @param string $memberID
     * @return array
     */
    public function getMemberGwLanguages($memberID)
    {
        return $this->db->table("gateway_projects")
            ->leftJoin("languages", "gateway_projects.gwLang", "=", "languages.langID")
            ->where("gateway_projects.admins", "LIKE", "%$memberID%")
            ->groupBy("gateway_projects.gwLang")
            ->orderBy("languages.langID")->get();
    }


    /**
     * Used just for testing
     */
    public function test()
    {
        $builder = $this->db->table("languages")
            ->leftJoin("gateway_projects", "languages.langID", "=", "gateway_projects.gwLang")
            ->where("gateway_projects.admins", "LIKE", "%5%");

        Data::pr($builder->toSql());
    }

    /**
     * Get list of other languages
     * @param string $memberID
     * @param string $gwLang
     * @return array
     */
    public function getTargetLanguages($memberID, $gwLang)
    {
        $builder = $this->db->table("languages, gateway_projects")
            ->where("languages.gwLang", function ($query ) use ($gwLang)
            {
                $query->select("gwLang")->from("languages")
                    ->where("langID", $gwLang);
            })
            ->where("gateway_projects.gwLang", $gwLang)
            ->select(array("languages.langID", "languages.langName"));

        if(!Session::get("isSuperAdmin"))
            $builder->where("gateway_projects.admins", "LIKE", "%$memberID%");

        return $builder->get();
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
        $sql = "SELECT trs.memberID AS translator, chk7_8.checkerID AS checker7_8, l2.memberID AS l2checker, l3.memberID AS l3checker ".
            "FROM ".PREFIX."events AS evnt ".
            "LEFT JOIN ".PREFIX."translators AS trs ON evnt.eventID = trs.eventID AND trs.memberID = :memberID ".
            "LEFT JOIN ".PREFIX."translators AS chk7_8 ON evnt.eventID = chk7_8.eventID AND chk7_8.checkerID = :memberID ".
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

    public function getBookInfo($bookCode)
    {
        return $this->db->table("abbr")
            ->where("code", $bookCode)->get();
    }

    /**
     * Create gateway project
     * @param array $data
     * @return string
     */
    public function createGatewayProject($data)
    {
        return $this->db->table("gateway_projects")
            ->insertGetId($data);
    }

    /**
     * Create gateway project
     * @param array $data
     * @return string
     */
    public function updateGatewayProject($data, $where)
    {
        return $this->db->table("gateway_projects")
            ->where($where)
            ->update($data);
    }

    /**
     * Create project
     * @param array $data
     * @return string
     */
    public function createProject($data)
    {
        return $this->db->table("projects")
            ->insertGetId($data);
    }

    /**
     * Create event
     * @param array $data
     * @return string
     */
    public function createEvent($data)
    {
        return $this->db->table("events")
            ->insertGetId($data);
    }

    /**
     * Add member as new translator for event
     * @param array $data
     * @param bool $addPair
     * @param int $lastTrID
     * @return string
     */
    public function addTranslator($data)
    {
        /*try
        {
            $this->db->insert(PREFIX."translators",$data);
        } catch(\PDOException $e)
        {
            return $e->getMessage();
        }

        $trID = $this->db->lastInsertId('trID');
        return $trID;*/

        return $this->db->table("translators")
            ->insertGetId($data);
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

        $this->db->table("profile")
            ->where(array("mID" => Session::get("memberID")))
            ->update($checkerData);

        $profile = Session::get("profile");

        foreach ($oldData as $key => $value)
            $profile[$key] = $value;

        Session::set("profile", $profile);

        /*try
        {
            $this->db->insert(PREFIX."checkers_l2",$data);
        } catch(\PDOException $e)
        {
            return $e->getMessage();
        }
        return $this->db->lastInsertId('l2chID');*/

        return $this->db->table("checkers_l2")
            ->insertGetId($data);
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

        $this->db->table("profile")
            ->where(array("mID" => Session::get("memberID")))
            ->update($checkerData);

        $profile = Session::get("profile");

        foreach ($oldData as $key => $value)
            $profile[$key] = $value;

        Session::set("profile", $profile);

        /*try
        {
            $this->db->insert(PREFIX."checkers_l3",$data);
        } catch(\PDOException $e)
        {
            return $e->getMessage();
        }
        return $this->db->lastInsertId('l3chID');*/

        return $this->db->table("checkers_l3")
            ->insertGetId($data);
    }

    public function setTranslatorsPairOrder($pairOrder, $eventID, $members)
    {
        $rows = $this->db->table("translators")
            ->where("eventID", $eventID)
            ->where("memberID", $members[0]["memberID"])
            ->update(array("pairOrder" => $pairOrder, "pairID" => $members[1]["trID"]));

        $rows += $this->db->table("translators")
            ->where("eventID", $eventID)
            ->where("memberID", $members[1]["memberID"])
            ->update(array("pairOrder" => $pairOrder, "pairID" => $members[0]["trID"]));

        return $rows;
    }


    /**
     * Create translation record
     * @param array $data
     * @return string
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

    /**
     * Update event
     * @param array $data
     * @param array $where
     * @return int
     */
    public function updateEvent($data, $where)
    {
        return $this->db->table("events")
            ->where($where)
            ->update($data);
    }

    /**
     * Update translator
     * @param array $data
     * @param array $where
     * @return int
     */
    public function updateTranslator($data, $where)
    {
        return $this->db->table("translators")
            ->where($where)
            ->update($data);
    }

    public function getTurnSecret()
    {
        $this->db->setTablePrefix("");
        $builder = $this->db->table("turn_secret")
            ->where("realm", "v-mast.com");

        $res = $builder->get();

        $this->db->setTablePrefix("vm_");

        return $res;
    }

    public function updateTurnSecret($data)
    {
        $this->db->setTablePrefix("");
        $upd = $this->db->table("turn_secret")
            ->where("realm", "v-mast.com")
            ->update($data);

        $this->db->setTablePrefix("vm_");

        return $upd;
    }

    public function generateStrongPassword($length = 9, $add_dashes = false, $available_sets = 'luds')
    {
        $sets = array();
        if(strpos($available_sets, 'l') !== false)
            $sets[] = 'abcdefghjkmnpqrstuvwxyz';
        if(strpos($available_sets, 'u') !== false)
            $sets[] = 'ABCDEFGHJKMNPQRSTUVWXYZ';
        if(strpos($available_sets, 'd') !== false)
            $sets[] = '23456789';
        if(strpos($available_sets, 's') !== false)
            $sets[] = '!@#$%&*?-';
        $all = '';
        $password = '';
        foreach($sets as $set)
        {
            $password .= $set[array_rand(str_split($set))];
            $all .= $set;
        }
        $all = str_split($all);
        for($i = 0; $i < $length - count($sets); $i++)
            $password .= $all[array_rand($all)];
        $password = str_shuffle($password);
        if(!$add_dashes)
            return $password;
        $dash_len = floor(sqrt($length));
        $dash_str = '';
        while(strlen($password) > $dash_len)
        {
            $dash_str .= substr($password, 0, $dash_len) . '-';
            $password = substr($password, $dash_len);
        }
        $dash_str .= $password;
        return $dash_str;
    }
}