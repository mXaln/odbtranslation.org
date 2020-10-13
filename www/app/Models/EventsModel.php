<?php
/**
 * Created by PhpStorm.
 * User: Maxim
 * Date: 05 Feb 2016
 * Time: 19:57
 */

namespace App\Models;

use Database\Model;
use DB;
use Helpers\Arrays;
use Helpers\Constants\EventCheckSteps;
use Helpers\Constants\EventMembers;
use Helpers\Constants\EventStates;
use Helpers\Constants\EventSteps;
use Helpers\Constants\StepsStates;
use Helpers\Session;
use PDO;


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
            ->orderBy("langName")
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

    public function getProjects($memberID, $projectID = null)
    {
        $sql = "SELECT ".PREFIX."projects.*, ".
            "tLang.langName as tLang, tLang.angName as tAng, ".
            "sLang.langName as sLang, sLang.angName as sAng ".
            "FROM ".PREFIX."projects ".
            "LEFT JOIN ".PREFIX."languages AS tLang ON ".PREFIX."projects.targetLang = tLang.langID ".
            "LEFT JOIN ".PREFIX."languages AS sLang ON ".PREFIX."projects.gwLang = sLang.langID ".
            "WHERE ".PREFIX."projects.gwProjectID IN ".
                "(SELECT gwProjectID FROM ".PREFIX."gateway_projects WHERE admins LIKE :memberID) ";

        $prepare = array();
        $prepare[":memberID"] = '%"'.$memberID.'"%';

        if($projectID !== null)
        {
            $sql .= " AND ".PREFIX."projects.projectID=:projectID";
            $prepare[":projectID"] = $projectID;
        }

        $sql .= " ORDER BY ".PREFIX."projects.targetLang";

        return $this->db->select($sql, $prepare);
    }


    public function getAbbrByCategory($category, $count = false)
    {
        $builder = $this->db->table("abbr")
            ->where("category", $category);

        if($count)
            return $builder->count(["*"]);

        return $builder->get();
    }

    /**
     * Get all events
     * @return array|static[]
     */
    public function getEvents()
    {
        return $this->db->table("events")
            ->get();
    }

    /**
     * Get Event Data by eventID OR by projectID and bookCode
     * @param $eventID
     * @param $projectID
     * @param $bookCode
     * @param bool $countMembers
     * @return array
     */
    public function getEvent($eventID, $projectID = null, $bookCode = null, $countMembers = false)
    {
        $table = "translators";        
        $builder = $this->db->table("events");
        $select = ["events.*", "abbr.*", "gateway_projects.admins as superadmins", "projects.bookProject", "projects.targetLang"];
        if($countMembers)
        {
            $select[] = $this->db->raw("COUNT(DISTINCT ".PREFIX.$table.".memberID) AS translators");
            $select[] = $this->db->raw("COUNT(DISTINCT ".PREFIX."checkers_l2.memberID) AS checkers_l2");
            $select[] = $this->db->raw("COUNT(DISTINCT ".PREFIX."checkers_l3.memberID) AS checkers_l3");

            $builder
                ->leftJoin($table, "events.eventID", "=", $table.".eventID")
                ->leftJoin("checkers_l2", "events.eventID", "=", "checkers_l2.eventID")
                ->leftJoin("checkers_l3", "events.eventID", "=", "checkers_l3.eventID");
        }

        $builder->leftJoin("abbr", "events.bookCode", "=", "abbr.code")
            ->leftJoin("projects", "events.projectID", "=", "projects.projectID")
            ->leftJoin("gateway_projects", "projects.gwProjectID", "=", "gateway_projects.gwProjectID");

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
     * @param $category
     * @return array
     */
    public function getEventsByProject($projectID, $category)
    {
        $sql = "SELECT ".PREFIX."abbr.*, ".PREFIX."events.*, COUNT(DISTINCT ".PREFIX."translators.memberID) AS translators, ".
            "COUNT(DISTINCT ".PREFIX."checkers_l2.memberID) AS checkers_l2, COUNT(DISTINCT ".PREFIX."checkers_l3.memberID) AS checkers_l3 ".
            "FROM ".PREFIX."abbr ".
            "LEFT JOIN ".PREFIX."events ON ".PREFIX."abbr.code=".PREFIX."events.bookCode AND (".PREFIX."events.projectID=:projectID OR ".PREFIX."events.projectID is NULL) ".
            "LEFT JOIN ".PREFIX."translators ON ".PREFIX."translators.eventID=".PREFIX."events.eventID ".
            "LEFT JOIN ".PREFIX."checkers_l2 ON ".PREFIX."checkers_l2.eventID=".PREFIX."events.eventID ".
            "LEFT JOIN ".PREFIX."checkers_l3 ON ".PREFIX."checkers_l3.eventID=".PREFIX."events.eventID ".
            "WHERE ".PREFIX."abbr.category=:category ".
            "GROUP BY ".PREFIX."abbr.abbrID ORDER BY ".PREFIX."abbr.abbrID";

        $prepare = [":projectID" => $projectID, ":category" => $category];

        return $this->db->select($sql, $prepare);
    }

    /**
     * Get event by book code and target language
     * @param $bookCode
     * @param $langID
     * @return array
     */
    public function getEventByBookAndLanguage($bookCode, $langID)
    {
        return $this->db->table("events")
            ->leftJoin("projects", "projects.projectID", "=", "events.projectID")
            ->where("projects.targetLang", $langID)
            ->where("projects.bookProject", "ulb")
            ->where("events.bookCode", $bookCode)->get();
    }

    /**
     * Get member with the event in which he is participating
     * @param int $eventID
     * @param int $memberID
     * @param boolean $getInfo
     * @return array
     */
    public function getEventMember($eventID, $memberID, $getInfo = false)
    {
        $sql = "SELECT ".PREFIX."translators.memberID AS translator, "
            ."checkers.checkerID AS checker, evnt.admins, evnt.admins_l2, evnt.admins_l3, ".PREFIX."translators.step, "
            .PREFIX."translators.checkerID, ".PREFIX."translators.peerCheck, ".PREFIX."translators.currentChapter, "
            .PREFIX."checkers_l2.memberID AS checker_l2, ".PREFIX."checkers_l3.memberID AS checker_l3, "
            .PREFIX."projects.projectID, ".PREFIX."projects.bookProject, ".PREFIX."projects.sourceBible, ".PREFIX."projects.sourceLangID, "
            .PREFIX."projects.gwLang, ".PREFIX."projects.targetLang, ".PREFIX."projects.gwProjectID, evnt.state, evnt.langInput "
            .($getInfo ?
                ", evnt.eventID, evnt.bookCode, "
                ."t_lang.langName as tLang, s_lang.langName as sLang, ".PREFIX."abbr.name, ".PREFIX."abbr.abbrID, ".PREFIX."abbr.chaptersNum " : "")
            ."FROM ".PREFIX."events AS evnt "
            ."LEFT JOIN ".PREFIX."translators ON ".PREFIX."translators.eventID = evnt.eventID AND ".PREFIX."translators.memberID = :memberID "
            ."LEFT JOIN ".PREFIX."translators AS checkers ON checkers.eventID = evnt.eventID AND checkers.checkerID = :memberID "
            ."LEFT JOIN ".PREFIX."checkers_l2 ON ".PREFIX."checkers_l2.eventID = evnt.eventID AND ".PREFIX."checkers_l2.memberID = :memberID "
            ."LEFT JOIN ".PREFIX."checkers_l3 ON ".PREFIX."checkers_l3.eventID = evnt.eventID AND ".PREFIX."checkers_l3.memberID = :memberID "
            ."LEFT JOIN ".PREFIX."projects ON evnt.projectID = ".PREFIX."projects.projectID "
            .($getInfo ?
                "LEFT JOIN ".PREFIX."languages AS t_lang ON ".PREFIX."projects.targetLang = t_lang.langID ".
                "LEFT JOIN ".PREFIX."languages AS s_lang ON ".PREFIX."projects.sourceLangID = s_lang.langID ".
                "LEFT JOIN ".PREFIX."abbr ON evnt.bookCode = ".PREFIX."abbr.code " : "")
            ."WHERE evnt.eventID = :eventID";

        $prepare = array(":eventID" => $eventID, ":memberID" => $memberID);

        return $this->db->select($sql, $prepare);
    }


    /**
     * Get all events of a member or specific event
     * @param $memberID
     * @param $memberType
     * @param null $eventID
     * @param bool $includeFinished
     * @param bool $includeNone
     * @return array
     */
    public function getMemberEvents($memberID, $memberType, $eventID = null, $includeFinished = true, $includeNone = true)
    {
        $sql = "SELECT ".($memberType == EventMembers::TRANSLATOR
            ? PREFIX."translators.trID, "
                .PREFIX."translators.memberID AS myMemberID, ".PREFIX."translators.step, "
                .PREFIX."translators.checkerID, ".PREFIX."translators.checkDone, "
                .PREFIX."translators.currentChunk, ".PREFIX."translators.currentChapter, "
                .PREFIX."translators.translateDone, "
                .PREFIX."translators.verbCheck, ".PREFIX."translators.peerCheck, "
                .PREFIX."translators.kwCheck, ".PREFIX."translators.crCheck, "
                .PREFIX."translators.isChecker, "
                ."mems.userName AS checkerName, mems.firstName AS checkerFName, "
                ."mems.lastName AS checkerLName, "
                ."(SELECT COUNT(*) FROM ".PREFIX."translators AS all_trs WHERE all_trs.eventID = ".PREFIX."translators.eventID ) AS currTrs, " 
            : "").($memberType == EventMembers::L2_CHECKER 
            ? PREFIX."checkers_l2.l2chID, "
                .PREFIX."checkers_l2.memberID, ".PREFIX."checkers_l2.step, "
                .PREFIX."checkers_l2.currentChapter, ".PREFIX."checkers_l2.sndCheck, "
                .PREFIX."checkers_l2.peer1Check, ".PREFIX."checkers_l2.peer2Check, "
                ."(SELECT COUNT(*) FROM ".PREFIX."checkers_l2 AS all_chkrs WHERE all_chkrs.eventID = ".PREFIX."checkers_l2.eventID ) AS currChkrs, "
            : "").($memberType == EventMembers::L3_CHECKER
                ? PREFIX."checkers_l3.l3chID, "
                .PREFIX."checkers_l3.memberID, ".PREFIX."checkers_l3.step, "
                .PREFIX."checkers_l3.currentChapter, ".PREFIX."checkers_l3.peerCheck, "
                ."(SELECT COUNT(*) FROM ".PREFIX."checkers_l3 AS all_chkrs WHERE all_chkrs.eventID = ".PREFIX."checkers_l3.eventID ) AS currChkrs, "
                : "")
                ."evnt.eventID, evnt.state, evnt.bookCode, evnt.dateFrom, evnt.langInput, "
                ."evnt.dateTo, evnt.admins, evnt.admins_l2, evnt.admins_l3, "
                .PREFIX."projects.projectID, ".PREFIX."projects.bookProject, "
                .PREFIX."projects.sourceLangID, ".PREFIX."projects.gwLang, "
                .PREFIX."projects.tnLangID, ".PREFIX."projects.tqLangID, ".PREFIX."projects.twLangID, "
                .PREFIX."projects.targetLang, ".PREFIX."projects.gwProjectID, "
                .PREFIX."projects.sourceBible, t_lang.langName as tLang, chapters.chunks, "
                ."t_lang.direction as tLangDir, ".PREFIX."projects.resLangID, res_lang.direction as resLangDir, "
                ."s_lang.langName as sLang, s_lang.direction as sLangDir, ".
                PREFIX."abbr.name, ".PREFIX."abbr.abbrID, ".
                PREFIX."abbr.chaptersNum FROM ";
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
                "LEFT JOIN ".PREFIX."members AS mems ON mems.memberID = ".PREFIX."translators.checkerID ".
                "LEFT JOIN ".PREFIX."chapters AS chapters ON ".PREFIX."translators.eventID = chapters.eventID AND ".PREFIX."translators.currentChapter = chapters.chapter " : "").
            ($memberType == EventMembers::L2_CHECKER ?
                "LEFT JOIN ".PREFIX."chapters AS chapters ON ".PREFIX."checkers_l2.eventID = chapters.eventID AND ".PREFIX."checkers_l2.currentChapter = chapters.chapter " : "").
            ($memberType == EventMembers::L3_CHECKER ?
                "LEFT JOIN ".PREFIX."chapters AS chapters ON ".PREFIX."checkers_l3.eventID = chapters.eventID AND ".PREFIX."checkers_l3.currentChapter = chapters.chapter " : "").
            "LEFT JOIN ".PREFIX."events AS evnt ON ".$mainTable.".eventID = evnt.eventID ".
            "LEFT JOIN ".PREFIX."projects ON evnt.projectID = ".PREFIX."projects.projectID ".
            "LEFT JOIN ".PREFIX."languages AS t_lang ON ".PREFIX."projects.targetLang = t_lang.langID ".
            "LEFT JOIN ".PREFIX."languages AS s_lang ON ".PREFIX."projects.sourceLangID = s_lang.langID ".
            "LEFT JOIN ".PREFIX."languages AS res_lang ON ".PREFIX."projects.resLangID = res_lang.langID ".
            "LEFT JOIN ".PREFIX."abbr ON evnt.bookCode = ".PREFIX."abbr.code ".
            "WHERE ".$mainTable.".eventID !='' ".
            (!is_null($memberID) ? " AND ".$mainTable.".memberID = :memberID " : " ").
            (!is_null($eventID) ? " AND ".$mainTable.".eventID=:eventID " : " ").
            ($memberType == EventMembers::TRANSLATOR && !$includeNone ? "AND ".PREFIX."translators.step != 'none' " : "").
            ($memberType == EventMembers::TRANSLATOR && !$includeFinished ? " AND ".PREFIX."translators.step != 'finished' " : " ").
            "ORDER BY tLang, ".PREFIX."projects.bookProject, ".PREFIX."abbr.abbrID";

        $prepare = array();
        if(!is_null($memberID))
            $prepare[":memberID"] = $memberID;

        if(!is_null($eventID))
            $prepare[":eventID"] = $eventID;

        return $this->db->select($sql, $prepare);
    }

    /**
     * Get translator information
     * @param $memberID Checker member ID
     * @param null $eventID event ID
     * @param null $trMemberID Translator member ID
     * @return array
     */
    public function getMemberEventsForChecker($memberID, $eventID = null, $trMemberID = null)
    {
        $prepare = array(":memberID" => $memberID);
        if($eventID)
            $prepare[":eventID"] = $eventID;
        if($trMemberID)
            $prepare[":trMemberID"] = $trMemberID;

        $sql = "SELECT trs.*, ".PREFIX."members.userName, ".PREFIX."members.firstName, "
                .PREFIX."members.lastName, evnt.bookCode, evnt.admins, evnt.state, "
                ."t_lang.langName AS tLang, s_lang.langName AS sLang, "
                .PREFIX."abbr.name AS bookName, ".PREFIX."abbr.abbrID, "
                .PREFIX."projects.sourceLangID, ".PREFIX."projects.bookProject, "
                .PREFIX."projects.tnLangID, ".PREFIX."projects.tqLangID, ".PREFIX."projects.twLangID, "
                .PREFIX."projects.sourceBible, ".PREFIX."projects.gwLang, "
                .PREFIX."projects.targetLang, ".PREFIX."projects.resLangID, "
                ."t_lang.direction as tLangDir, s_lang.direction as sLangDir, evnt.langInput, "
                .PREFIX."chapters.chunks, ".PREFIX."projects.projectID ".
            "FROM ".PREFIX."translators AS trs ".
                "LEFT JOIN ".PREFIX."chapters ON trs.eventID = ".PREFIX."chapters.eventID AND trs.currentChapter = ".PREFIX."chapters.chapter ".
                "LEFT JOIN ".PREFIX."members ON trs.memberID = ".PREFIX."members.memberID ".
                "LEFT JOIN ".PREFIX."events AS evnt ON evnt.eventID = trs.eventID ".
                "LEFT JOIN ".PREFIX."projects ON ".PREFIX."projects.projectID = evnt.projectID ".
                "LEFT JOIN ".PREFIX."languages AS t_lang ON ".PREFIX."projects.targetLang = t_lang.langID ".
                "LEFT JOIN ".PREFIX."languages AS s_lang ON ".PREFIX."projects.sourceLangID = s_lang.langID ".
                "LEFT JOIN ".PREFIX."abbr ON evnt.bookCode = ".PREFIX."abbr.code ".
            "WHERE (trs.checkerID = :memberID AND trs.checkDone = false) ".
                ($eventID ? "AND trs.eventID = :eventID " : " ").
                ($trMemberID ? "AND trs.memberID = :trMemberID " : " ").
            "ORDER BY tLang, ".PREFIX."abbr.abbrID";

        return $this->db->select($sql, $prepare);
    }


    /**
     * Get L2 checker event/s
     * @param $memberID 2nd Checker member ID
     * @param null $eventID
     * @param null $chkMemberID 1st Checker member ID
     * @param null $chapter
     * @return array
     */
    public function getMemberEventsForCheckerL2($memberID, $eventID = null, $chkMemberID = null, $chapter = null)
    {
        $prepare = [];
        if($eventID)
            $prepare[":eventID"] = $eventID;
        if($chkMemberID)
            $prepare[":chkMemberID"] = $chkMemberID;

        $sql = "SELECT chks.*, ".PREFIX."members.userName, ".PREFIX."members.firstName, "
            .PREFIX."members.lastName, evnt.bookCode, evnt.admins_l2, evnt.state, "
            ."evnt.dateFrom, evnt.dateTo, "
            ."t_lang.langName AS tLang, s_lang.langName AS sLang, "
            .PREFIX."abbr.name AS name, ".PREFIX."abbr.abbrID, "
            .PREFIX."projects.sourceLangID, ".PREFIX."projects.bookProject, "
            .PREFIX."projects.tnLangID, ".PREFIX."projects.tqLangID, ".PREFIX."projects.twLangID, "
            .PREFIX."projects.sourceBible, ".PREFIX."projects.gwLang, "
            .PREFIX."projects.targetLang, ".PREFIX."projects.resLangID, ".
            "t_lang.direction as tLangDir, s_lang.direction as sLangDir, "
            .PREFIX."abbr.chaptersNum, ".PREFIX."projects.projectID ".
            "FROM ".PREFIX."checkers_l2 AS chks ".
            "LEFT JOIN ".PREFIX."members ON chks.memberID = ".PREFIX."members.memberID ".
            "LEFT JOIN ".PREFIX."events AS evnt ON evnt.eventID = chks.eventID ".
            "LEFT JOIN ".PREFIX."projects ON ".PREFIX."projects.projectID = evnt.projectID ".
            "LEFT JOIN ".PREFIX."languages AS t_lang ON ".PREFIX."projects.targetLang = t_lang.langID ".
            "LEFT JOIN ".PREFIX."languages AS s_lang ON ".PREFIX."projects.sourceLangID = s_lang.langID ".
            "LEFT JOIN ".PREFIX."abbr ON evnt.bookCode = ".PREFIX."abbr.code ".
            "WHERE 1 ".
            ($eventID ? "AND chks.eventID = :eventID " : " ").
            ($chkMemberID ? "AND chks.memberID = :chkMemberID " : " ").
            "ORDER BY tLang, ".PREFIX."abbr.abbrID";

        $events = $this->db->select($sql, $prepare);
        $filtered = [];

        foreach($events as $event)
        {
            // First Check events
            if($event->memberID == $memberID
                && $event->step != EventCheckSteps::NONE
                && ($chapter == null || $chapter == $event->currentChapter))
            {
                $filtered[] = $event;
            }

            // Second Check events
            $sndCheck = (array)json_decode($event->sndCheck, true);
            foreach ($sndCheck as $chap => $data) {
                if(!isset($chapter) || $chapter == $chap)
                {
                    if($data["memberID"] == $memberID && $data["done"] != 2)
                    {
                        $ev = clone $event;

                        $ev->step = $data["done"] == 0 ?
                            EventCheckSteps::SND_CHECK :
                            EventCheckSteps::KEYWORD_CHECK_L2;
                        $ev->currentChapter = $chap;
                        $ev->l2memberID = $ev->memberID;
                        $ev->myMemberID = 0;
                        $ev->myChkMemberID = $memberID;
                        $ev->isContinue = true; // Means not owner of chapter
                        $filtered[] = $ev;
                    }
                }
            }

            // Peer Check events
            $peer1Check = (array)json_decode($event->peer1Check, true);
            $peer2Check = (array)json_decode($event->peer2Check, true);
            foreach ($peer1Check as $chap => $data) {
                if(!isset($chapter) || $chapter == $chap)
                {
                    if($data["memberID"] == $memberID && $data["done"] == 0)
                    {
                        $ev = clone $event;
                        $checkerFName = null;
                        $checkerLName = null;
                        $checkerID = 0;

                        if($peer2Check[$chap]["memberID"] != 0)
                        {
                            $memberModel = new MembersModel();
                            $member = $memberModel->getMember([
                                "firstName",
                                "lastName"
                            ], ["memberID", $peer2Check[$chap]["memberID"]]);
                            if(!empty($member))
                            {
                                $checkerFName = $member[0]->firstName;
                                $checkerLName = $member[0]->lastName;
                                $checkerID = $peer2Check[$chap]["memberID"];
                            }
                        }

                        $ev->step = EventCheckSteps::PEER_REVIEW_L2;
                        $ev->currentChapter = $chap;
                        $ev->peer = 1;
                        $ev->l2memberID = $ev->memberID;
                        $ev->memberID = $memberID;
                        $ev->myMemberID = $peer1Check[$chap]["memberID"];
                        $ev->myChkMemberID = $memberID;
                        $ev->checkerFName = $checkerFName;
                        $ev->checkerLName = $checkerLName;
                        $ev->checkerID = $checkerID;
                        $ev->isContinue = true;
                        $filtered[] = $ev;
                    }
                }
            }

            foreach ($peer2Check as $chap => $data) {
                if(!isset($chapter) || $chapter == $chap)
                {
                    if($data["memberID"] == $memberID && $data["done"] == 0)
                    {
                        $ev = clone $event;
                        $checkerFName = null;
                        $checkerLName = null;
                        $checkerID = 0;

                        $memberModel = new MembersModel();
                        $member = $memberModel->getMember([
                            "firstName",
                            "lastName"
                        ], ["memberID", $peer1Check[$chap]["memberID"]]);
                        if(!empty($member))
                        {
                            $checkerFName = $member[0]->firstName;
                            $checkerLName = $member[0]->lastName;
                            $checkerID = $peer1Check[$chap]["memberID"];
                        }

                        $ev->step = EventCheckSteps::PEER_REVIEW_L2;
                        $ev->currentChapter = $chap;
                        $ev->peer = 2;
                        $ev->l2memberID = $ev->memberID;
                        $ev->memberID = $peer1Check[$chap]["memberID"];
                        $ev->myMemberID = $memberID;
                        $ev->myChkMemberID = $memberID;
                        $ev->checkerFName = $checkerFName;
                        $ev->checkerLName = $checkerLName;
                        $ev->checkerID = $checkerID;
                        $ev->isContinue = true;
                        $filtered[] = $ev;
                    }
                }
            }
        }

        return $filtered;
    }


    /**
     * Get L3 checker event/s
     * @param $memberID 1st Checker member ID
     * @param null $eventID
     * @param null $chkMemberID Peer checker
     * @param null $chapter
     * @return array
     */
    public function getMemberEventsForCheckerL3($memberID, $eventID = null, $chkMemberID = null, $chapter = null)
    {
        $prepare = [];
        if($eventID)
            $prepare[":eventID"] = $eventID;
        if($chkMemberID)
            $prepare[":chkMemberID"] = $chkMemberID;

        $sql = "SELECT chks.*, ".PREFIX."members.userName, ".PREFIX."members.firstName, "
            .PREFIX."members.lastName, evnt.bookCode, evnt.admins_l3, evnt.state, "
            ."evnt.dateFrom, evnt.dateTo, "
            ."t_lang.langName AS tLang, s_lang.langName AS sLang, "
            .PREFIX."abbr.name AS name, ".PREFIX."abbr.abbrID, "
            .PREFIX."projects.sourceLangID, ".PREFIX."projects.bookProject, "
            .PREFIX."projects.tnLangID, ".PREFIX."projects.tqLangID, ".PREFIX."projects.twLangID, "
            .PREFIX."projects.sourceBible, ".PREFIX."projects.gwLang, ".PREFIX."projects.gwProjectID, "
            .PREFIX."projects.targetLang, ".PREFIX."projects.resLangID, ".
            "t_lang.direction as tLangDir, s_lang.direction as sLangDir, "
            .PREFIX."abbr.chaptersNum, ".PREFIX."projects.projectID ".
            "FROM ".PREFIX."checkers_l3 AS chks ".
            "LEFT JOIN ".PREFIX."members ON chks.memberID = ".PREFIX."members.memberID ".
            "LEFT JOIN ".PREFIX."events AS evnt ON evnt.eventID = chks.eventID ".
            "LEFT JOIN ".PREFIX."projects ON ".PREFIX."projects.projectID = evnt.projectID ".
            "LEFT JOIN ".PREFIX."languages AS t_lang ON ".PREFIX."projects.targetLang = t_lang.langID ".
            "LEFT JOIN ".PREFIX."languages AS s_lang ON ".PREFIX."projects.sourceLangID = s_lang.langID ".
            "LEFT JOIN ".PREFIX."abbr ON evnt.bookCode = ".PREFIX."abbr.code ".
            "WHERE 1 ".
            ($eventID ? "AND chks.eventID = :eventID " : " ").
            ($chkMemberID ? "AND chks.memberID = :chkMemberID " : " ").
            "ORDER BY tLang, ".PREFIX."abbr.abbrID";

        $events = $this->db->select($sql, $prepare);
        $filtered = [];

        foreach($events as $event)
        {
            // First Checker events
            if($event->memberID == $memberID
                && $event->step != EventCheckSteps::NONE
                && ($chapter == null || $chapter == $event->currentChapter))
            {
                $filtered[] = $event;
            }

            // Peer Check events
            $peerCheck = (array)json_decode($event->peerCheck, true);
            foreach ($peerCheck as $chap => $data) {
                if(!isset($chapter) || $chapter == $chap)
                {
                    if($data["memberID"] == $memberID && $data["done"] != 2)
                    {
                        $ev = clone $event;

                        $memberModel = new MembersModel();
                        $member = $memberModel->getMember([
                            "firstName",
                            "lastName"
                        ], ["memberID", $ev->memberID]);
                        $checkerFName = $member[0]->firstName;
                        $checkerLName = $member[0]->lastName;

                        $ev->peerStep = $ev->step;
                        $ev->step = $data["done"] == 0 ?
                            EventCheckSteps::PEER_REVIEW_L3 :
                            EventCheckSteps::PEER_EDIT_L3;
                        $ev->currentChapter = $chap;
                        $ev->l3memberID = $ev->memberID;
                        $ev->myMemberID = 0;
                        $ev->myChkMemberID = $memberID;
                        $ev->chkMemberID = $ev->l3memberID;
                        $ev->checkerFName = $checkerFName;
                        $ev->checkerLName = $checkerLName;
                        $ev->isContinue = true; // Means not owner of chapter
                        $filtered[] = $ev;
                    }
                }
            }
        }

        return $filtered;
    }


    /**
     * Get SUN checker event/s
     * @param $checkerID Checker member ID
     * @param null $eventID event ID
     * @param null $memberID Translator member ID
     * @return array
     */
    public function getMemberEventsForCheckerSun($checkerID, $eventID = null, $memberID = null, $chapter = null)
    {
        $prepare = [];
        if($eventID)
            $prepare[":eventID"] = $eventID;
        if($memberID)
            $prepare[":memberID"] = $memberID;

        $sql = "SELECT trs.*, ".PREFIX."members.userName, ".PREFIX."members.firstName, "
            .PREFIX."members.lastName, evnt.bookCode, evnt.admins, evnt.state, "
            ."evnt.dateFrom, evnt.dateTo, "
            ."t_lang.langName AS tLang, s_lang.langName AS sLang, "
            .PREFIX."abbr.name AS name, ".PREFIX."abbr.abbrID, "
            .PREFIX."projects.sourceLangID, ".PREFIX."projects.bookProject, "
            .PREFIX."projects.sourceBible, ".PREFIX."projects.gwLang, "
            .PREFIX."projects.targetLang, ".PREFIX."projects.resLangID, ".
            "t_lang.direction as tLangDir, s_lang.direction as sLangDir, "
            .PREFIX."abbr.chaptersNum, ".PREFIX."projects.projectID ".
            "FROM ".PREFIX."translators AS trs ".
            "LEFT JOIN ".PREFIX."members ON trs.memberID = ".PREFIX."members.memberID ".
            "LEFT JOIN ".PREFIX."events AS evnt ON evnt.eventID = trs.eventID ".
            "LEFT JOIN ".PREFIX."projects ON ".PREFIX."projects.projectID = evnt.projectID ".
            "LEFT JOIN ".PREFIX."languages AS t_lang ON ".PREFIX."projects.targetLang = t_lang.langID ".
            "LEFT JOIN ".PREFIX."languages AS s_lang ON ".PREFIX."projects.sourceLangID = s_lang.langID ".
            "LEFT JOIN ".PREFIX."abbr ON evnt.bookCode = ".PREFIX."abbr.code ".
            "WHERE ".PREFIX."projects.bookProject = 'sun' AND trs.kwCheck != '' ".
            ($eventID ? "AND trs.eventID = :eventID " : " ").
            ($memberID ? "AND trs.memberID = :memberID " : " ").
            "ORDER BY tLang, ".PREFIX."abbr.abbrID";

        $events = $this->db->select($sql, $prepare);
        $filtered = [];

        foreach($events as $event)
        {
            // Theo Check events
            $kwCheck = (array)json_decode($event->kwCheck, true);
            foreach ($kwCheck as $chap => $data) {
                if(!isset($chapter) || $chapter == $chap)
                {
                    if($data["memberID"] == $checkerID && $data["done"] == 0)
                    {
                        $ev = clone $event;

                        $ev->step = EventSteps::THEO_CHECK;
                        $ev->currentChapter = $chap;
                        $ev->myMemberID = 0;
                        $ev->myChkMemberID = $checkerID;
                        $ev->isContinue = true; // Means not owner of chapter
                        $filtered[] = $ev;
                    }
                }
            }

            // Verse-by-verse Check events
            $crCheck = (array)json_decode($event->crCheck, true);
            foreach ($crCheck as $chap => $data) {
                if(!isset($chapter) || $chapter == $chap)
                {
                    $doneStatus = $event->sourceBible == "odb" ? 1 : 2;
                    if($data["memberID"] == $checkerID && $data["done"] != $doneStatus)
                    {
                        $ev = clone $event;

                        $ev->step = $data["done"] == 0 ?
                            EventSteps::CONTENT_REVIEW :
                            EventSteps::FINAL_REVIEW;
                        $ev->currentChapter = $chap;
                        $ev->myMemberID = 0;
                        $ev->myChkMemberID = $checkerID;
                        $ev->isContinue = true; // Means not owner of chapter
                        $filtered[] = $ev;
                    }
                }
            }
        }

        return $filtered;
    }


    /**
     * Get SUN checker event/s
     * @param $memberID SUN Checker member ID
     * @param null $eventID event ID
     * @param null $chkMemberID SUN translator member ID
     * @return array
     */
    public function getMemberEventsForSun($memberID, $eventID = null, $chkMemberID = null, $chapter = null)
    {
        $prepare = [];
        if($eventID)
            $prepare[":eventID"] = $eventID;
        if($chkMemberID)
            $prepare[":chkMemberID"] = $chkMemberID;

        $sql = "SELECT trs.*, ".PREFIX."members.userName, ".PREFIX."members.firstName, "
            .PREFIX."members.lastName, evnt.bookCode, evnt.admins, evnt.state, "
            ."evnt.dateFrom, evnt.dateTo, "
            ."t_lang.langName AS tLang, s_lang.langName AS sLang, "
            .PREFIX."abbr.name AS name, ".PREFIX."abbr.abbrID, "
            .PREFIX."projects.sourceLangID, ".PREFIX."projects.bookProject, "
            .PREFIX."projects.tnLangID, ".PREFIX."projects.tqLangID, ".PREFIX."projects.twLangID, "
            .PREFIX."projects.sourceBible, ".PREFIX."projects.gwLang, "
            .PREFIX."projects.targetLang, ".PREFIX."projects.resLangID, ".
            "t_lang.direction as tLangDir, s_lang.direction as sLangDir, "
            .PREFIX."abbr.chaptersNum, ".PREFIX."projects.projectID ".
            "FROM ".PREFIX."translators AS trs ".
            "LEFT JOIN ".PREFIX."members ON trs.memberID = ".PREFIX."members.memberID ".
            "LEFT JOIN ".PREFIX."events AS evnt ON evnt.eventID = trs.eventID ".
            "LEFT JOIN ".PREFIX."projects ON ".PREFIX."projects.projectID = evnt.projectID ".
            "LEFT JOIN ".PREFIX."languages AS t_lang ON ".PREFIX."projects.targetLang = t_lang.langID ".
            "LEFT JOIN ".PREFIX."languages AS s_lang ON ".PREFIX."projects.sourceLangID = s_lang.langID ".
            "LEFT JOIN ".PREFIX."abbr ON evnt.bookCode = ".PREFIX."abbr.code ".
            "WHERE ".PREFIX."projects.bookProject = 'sun' ".
            ($eventID ? "AND trs.eventID = :eventID " : " ").
            ($chkMemberID ? "AND trs.memberID = :chkMemberID " : " ").
            "ORDER BY tLang, ".PREFIX."abbr.abbrID";

        $events = $this->db->select($sql, $prepare);
        $filtered = [];

        foreach($events as $event)
        {
            // translation events
            if($event->memberID == $memberID
                && $event->step != EventCheckSteps::NONE
                && ($chapter == null || $chapter == $event->currentChapter))
            {
                $filtered[] = $event;
            }

            // Theo Check events
            $kwCheck = (array)json_decode($event->kwCheck, true);
            foreach ($kwCheck as $chap => $data) {
                if(!isset($chapter) || $chapter == $chap)
                {
                    if($data["memberID"] == $memberID && $data["done"] == 0)
                    {
                        $ev = clone $event;

                        $ev->step = EventSteps::THEO_CHECK;
                        $ev->currentChapter = $chap;
                        $ev->myMemberID = 0;
                        $ev->myChkMemberID = $memberID;
                        $ev->isContinue = true; // Means not owner of chapter
                        $filtered[] = $ev;
                    }
                }
            }

            // Verse-by-verse Check events
            $crCheck = (array)json_decode($event->crCheck, true);
            foreach ($crCheck as $chap => $data) {
                if(!isset($chapter) || $chapter == $chap)
                {
                    if($data["memberID"] == $memberID && $data["done"] != 2)
                    {
                        $ev = clone $event;

                        $ev->step = $data["done"] == 0 ?
                            EventSteps::CONTENT_REVIEW :
                            EventSteps::FINAL_REVIEW;
                        $ev->currentChapter = $chap;
                        $ev->memberID = $ev->memberID;
                        $ev->myMemberID = 0;
                        $ev->myChkMemberID = $memberID;
                        $ev->isContinue = true; // Means not owner of chapter
                        $filtered[] = $ev;
                    }
                }
            }
        }

        return $filtered;
    }

    /**
     * Get event/list of events for facilitator
     * @param $memberID
     * @param $eventID
     * @return array
     */
    public function getMemberEventsForAdmin($memberID, $eventID = null, $isSuperAdmin = false)
    {
        $sql = "SELECT evnt.*, proj.bookProject, proj.sourceBible, proj.sourceLangID, tLang.langName, sLang.langName AS sLang, ".
            "abbr.abbrID, abbr.name, abbr.chaptersNum, ".
            "(SELECT COUNT(*) FROM ".PREFIX."translators AS all_trs WHERE all_trs.eventID = evnt.eventID) AS trsCnt, ".
            "(SELECT COUNT(*) FROM ".PREFIX."checkers_l2 AS all_chl2 WHERE all_chl2.eventID = evnt.eventID) AS chl2Cnt, ".
            "(SELECT COUNT(*) FROM ".PREFIX."checkers_l3 AS all_chl3 WHERE all_chl3.eventID = evnt.eventID) AS chl3Cnt, ".
            "gwproj.admins AS superadmins, proj.resLangID ".
            "FROM ".PREFIX."events AS evnt ".
            "LEFT JOIN ".PREFIX."projects AS proj ON proj.projectID = evnt.projectID ".
            "LEFT JOIN ".PREFIX."gateway_projects AS gwproj ON proj.gwProjectID = gwproj.gwProjectID ".
            "LEFT JOIN ".PREFIX."abbr AS abbr ON evnt.bookCode = abbr.code ".
            "LEFT JOIN ".PREFIX."languages AS tLang ON proj.targetLang = tLang.langID ".
            "LEFT JOIN ".PREFIX."languages AS sLang ON proj.sourceLangID = sLang.langID ".
            (!$isSuperAdmin ? "WHERE (evnt.admins LIKE :memberID OR evnt.admins_l2 LIKE :memberID OR evnt.admins_l3 LIKE :memberID) " : "").
            ($isSuperAdmin && $eventID ? "WHERE " : (!$isSuperAdmin && $eventID ? "AND " : "")).
            ($eventID ? "evnt.eventID = :eventID " : "").
            "ORDER BY tLang.langName, proj.bookProject, abbr.abbrID";

        $prepare = [];
        if(!$isSuperAdmin) $prepare[":memberID"] = '%\"'.$memberID.'"%';
        if($eventID) $prepare[":eventID"] = $eventID;

        return $this->db->select($sql, $prepare);
    }

    public function getMembersForEvent($eventID)
    {       
        $this->db->setFetchMode(PDO::FETCH_ASSOC);
        $builder = $this->db->table("translators")
            ->select("translators.*", "members.userName", "members.firstName", "members.lastName", "chapters.chunks")
            ->leftJoin("members", "translators.memberID", "=", "members.memberID")
            ->leftJoin("chapters", function($join) {
                $join->on("translators.eventID", "=", "chapters.eventID")
                    ->on("translators.currentChapter", "=", "chapters.chapter");
            })
            ->where("translators.eventID", $eventID);

        $res = $builder->orderBy("members.userName")->get();
        $this->db->setFetchMode(PDO::FETCH_CLASS);

        return $res;
    }

    public function getMembersForProject($projectTypes) {
        return $this->db->table("translators")
            ->leftJoin("events", "events.eventID", "=", "translators.eventID")
            ->leftJoin("projects", "projects.projectID", "=", "events.projectID")
            ->whereIn("projects.bookProject", $projectTypes)
            ->orderBy("events.eventID")
            ->get();
    }
    
    public function getMembersForL2Event($eventID)
    {       
        $this->db->setFetchMode(PDO::FETCH_ASSOC);
        $builder = $this->db->table("checkers_l2")
            ->select("checkers_l2.*", "members.userName", "members.firstName", "members.lastName", "checkers_l2.peer2Check")
            ->leftJoin("members", "checkers_l2.memberID", "=", "members.memberID")
            ->where("checkers_l2.eventID", $eventID);

        $res = $builder->orderBy("members.userName")->get();
        $this->db->setFetchMode(PDO::FETCH_CLASS);

        return $res;
    }

    public function getMembersForL3Event($eventID)
    {
        $this->db->setFetchMode(PDO::FETCH_ASSOC);
        $builder = $this->db->table("checkers_l3")
            ->select("checkers_l3.*", "members.userName", "members.firstName", "members.lastName", "checkers_l3.peerCheck")
            ->leftJoin("members", "checkers_l3.memberID", "=", "members.memberID")
            ->where("checkers_l3.eventID", $eventID);

        $res = $builder->orderBy("members.userName")->get();
        $this->db->setFetchMode(PDO::FETCH_CLASS);

        return $res;
    }


    public function getEventWithContributors($eventID)
    {
        return $this->db->table("events")
            ->select([
                "events.eventID","events.admins",
                "translators.verbCheck","translators.peerCheck",
                "translators.kwCheck","translators.crCheck",
                "abbr.chaptersNum","projects.bookProject"
            ])
            ->leftJoin("translators", "events.eventID", "=", "translators.eventID")
            ->leftJoin("abbr", "events.bookCode", "=", "abbr.code")
            ->leftJoin("projects", "events.projectID", "=", "projects.projectID")
            ->where("events.eventID", $eventID)
            ->get();

    }


    public function getEventWithContributorsL2($eventID)
    {
        return $this->db->table("events")
            ->select([
                "events.eventID","events.admins_l2",
                "checkers_l2.sndCheck","checkers_l2.peer1Check",
                "checkers_l2.peer2Check",
                "abbr.chaptersNum"
            ])
            ->leftJoin("checkers_l2", "events.eventID", "=", "checkers_l2.eventID")
            ->leftJoin("abbr", "events.bookCode", "=", "abbr.code")
            ->where("events.eventID", $eventID)
            ->get();

    }

    public function getEventWithContributorsL3($eventID)
    {
        return $this->db->table("events")
            ->select([
                "events.eventID","events.admins_l3",
                "checkers_l3.peerCheck",
                "abbr.chaptersNum"
            ])
            ->leftJoin("checkers_l3", "events.eventID", "=", "checkers_l3.eventID")
            ->leftJoin("abbr", "events.bookCode", "=", "abbr.code")
            ->where("events.eventID", $eventID)
            ->get();

    }

    public function getEventContributors($eventID, $level, $mode, $withRoles = true)
    {
        $membersModel = new MembersModel();
        $admins = [];
        $adminsArr = [];
        $translators = [];
        $translatorsArr = [];
        $checkers = [];
        $checkersArr = [];
        $result = [];

        // L1 event for ulb, udb projects
        if($level == 1)
        {
            $event = $this->getEventWithContributors($eventID);
            if(!empty($event))
            {
                // Facilitators
                $adminsArr = (array)json_decode($event[0]->admins);

                // Checkers
                foreach ($event as $translator) {
                    $verbCheck = (array)json_decode($translator->verbCheck);
                    $peerCheck = (array)json_decode($translator->peerCheck);
                    $kwCheck = (array)json_decode($translator->kwCheck);
                    $crCheck = (array)json_decode($translator->crCheck);

                    if(in_array($mode, ["sun"]))
                    {
                        $checkersArr = Arrays::append($checkersArr, array_values(array_map(function($elm) {
                            return $elm->memberID;
                        }, $peerCheck)));
                        $checkersArr = Arrays::append($checkersArr, array_values(array_map(function($elm) {
                            return $elm->memberID;
                        }, $kwCheck)));
                        $checkersArr = Arrays::append($checkersArr, array_values(array_map(function($elm) {
                            return $elm->memberID;
                        }, $crCheck)));
                    }
                    else
                    {
                        $checkersArr = Arrays::append($checkersArr, array_values($verbCheck));
                        $checkersArr = Arrays::append($checkersArr, array_values($peerCheck));
                        $checkersArr = Arrays::append($checkersArr, array_values($kwCheck));
                        $checkersArr = Arrays::append($checkersArr, array_values($crCheck));
                    }
                }
                $checkersArr = array_unique($checkersArr);

                // Translators
                $data["chapters"] = [];
                for($i=1; $i <= $event[0]->chaptersNum; $i++)
                {
                    $data["chapters"][$i] = [];
                }

                $chapters = $this->getChapters($event[0]->eventID);

                foreach ($chapters as $chapter) {
                    $tmp["memberID"] = $chapter["memberID"];
                    $data["chapters"][$chapter["chapter"]] = $tmp;
                }

                foreach ($data["chapters"] as $chapter) {
                    if(!empty($chapter))
                        $translatorsArr[] = $chapter["memberID"];
                }
                $translatorsArr = array_unique($translatorsArr);
            }
        }
        elseif ($level == 2)
        {
            if (in_array($mode, ["udb","ulb"]))
            {
                $event = $this->getEventWithContributorsL2($eventID);
                if(!empty($event))
                {
                    // Facilitators
                    $adminsArr = (array)json_decode($event[0]->admins_l2);

                    // Checkers
                    foreach ($event as $translator) {
                        $sndCheck = (array)json_decode($translator->sndCheck);
                        $peer1Check = (array)json_decode($translator->peer1Check);
                        $peer2Check = (array)json_decode($translator->peer2Check);

                        $sndMems = [];
                        foreach ($sndCheck as $item) {
                            $sndMems[] = $item->memberID;
                        }

                        $p1Mems = [];
                        foreach ($peer1Check as $item) {
                            $p1Mems[] = $item->memberID;
                        }

                        $p2Mems = [];
                        foreach ($peer2Check as $item) {
                            $p2Mems[] = $item->memberID;
                        }

                        $checkersArr = Arrays::append($checkersArr, $sndMems);
                        $checkersArr = Arrays::append($checkersArr, $p1Mems);
                        $checkersArr = Arrays::append($checkersArr, $p2Mems);
                    }

                    $data["chapters"] = [];
                    for($i=1; $i <= $event[0]->chaptersNum; $i++)
                    {
                        $data["chapters"][$i] = [];
                    }

                    $chapters = $this->getChapters($event[0]->eventID, null, null, "l2");

                    foreach ($chapters as $chapter) {
                        $tmp["l2memberID"] = $chapter["l2memberID"];
                        $data["chapters"][$chapter["chapter"]] = $tmp;
                    }

                    foreach ($data["chapters"] as $chapter) {
                        if(!empty($chapter))
                            $checkersArr[] = $chapter["l2memberID"];
                    }
                    $checkersArr = array_unique($checkersArr);
                }
            }
            else
            {
                $event = $this->getEventWithContributors($eventID);
                if(!empty($event))
                {
                    // Facilitators
                    $adminsArr = (array)json_decode($event[0]->admins);

                    // Checkers
                    foreach ($event as $translator) {
                        $peerCheck = (array)json_decode($translator->peerCheck);
                        $kwCheck = (array)json_decode($translator->kwCheck);
                        $crCheck = (array)json_decode($translator->crCheck);

                        $checkersArr = Arrays::append($checkersArr, array_values(array_map(function($elm) {
                            return $elm->memberID;
                        }, $peerCheck)));
                        $checkersArr = Arrays::append($checkersArr, array_values(array_map(function($elm) {
                            return $elm->memberID;
                        }, $kwCheck)));
                        $checkersArr = Arrays::append($checkersArr, array_values(array_map(function($elm) {
                            return $elm->memberID;
                        }, $crCheck)));
                    }
                    $checkersArr = array_unique($checkersArr);

                    // Translators
                    $data["chapters"] = [];
                    for($i=1; $i <= $event[0]->chaptersNum; $i++)
                    {
                        $data["chapters"][$i] = [];
                    }

                    $chapters = $this->getChapters($event[0]->eventID);

                    foreach ($chapters as $chapter) {
                        $tmp["memberID"] = $chapter["memberID"];
                        $data["chapters"][$chapter["chapter"]] = $tmp;
                    }

                    foreach ($data["chapters"] as $chapter) {
                        if(!empty($chapter))
                            $translatorsArr[] = $chapter["memberID"];
                    }
                    $translatorsArr = array_unique($translatorsArr);
                }
            }
        }
        elseif ($level == 3)
        {
            if ($mode == "sun")
            {
                $event = $this->getEventWithContributors($eventID);
                if(!empty($event))
                {
                    // Facilitators
                    $adminsArr = (array)json_decode($event[0]->admins);

                    // Checkers
                    foreach ($event as $translator) {
                        $kwCheck = (array)json_decode($translator->kwCheck);
                        $crCheck = (array)json_decode($translator->crCheck);

                        $checkersArr = Arrays::append($checkersArr, array_values(array_map(function($elm) {
                            return $elm->memberID;
                        }, $kwCheck)));
                        $checkersArr = Arrays::append($checkersArr, array_values(array_map(function($elm) {
                            return $elm->memberID;
                        }, $crCheck)));
                    }
                    $checkersArr = array_unique($checkersArr);

                    // Translators
                    $data["chapters"] = [];
                    for($i=1; $i <= $event[0]->chaptersNum; $i++)
                    {
                        $data["chapters"][$i] = [];
                    }

                    $chapters = $this->getChapters($event[0]->eventID);

                    foreach ($chapters as $chapter) {
                        $tmp["memberID"] = $chapter["memberID"];
                        $data["chapters"][$chapter["chapter"]] = $tmp;
                    }

                    foreach ($data["chapters"] as $chapter) {
                        if(!empty($chapter))
                            $translatorsArr[] = $chapter["memberID"];
                    }
                    $translatorsArr = array_unique($translatorsArr);
                }
            }
            else
            {
                $event = $this->getEventWithContributorsL3($eventID);
                if(!empty($event))
                {
                    // Facilitators
                    $adminsArr = (array)json_decode($event[0]->admins_l3);

                    // Checkers
                    foreach ($event as $translator) {
                        $peerCheck = (array)json_decode($translator->peerCheck);
                        $peerMems = [];
                        foreach ($peerCheck as $item) {
                            $peerMems[] = $item->memberID;
                        }
                        $checkersArr = Arrays::append($checkersArr, $peerMems);
                    }

                    // Chapters
                    $data["chapters"] = [];
                    for($i=1; $i <= $event[0]->chaptersNum; $i++)
                    {
                        $data["chapters"][$i] = [];
                    }

                    $chapters = $this->getChapters($event[0]->eventID, null, null, "l3");

                    foreach ($chapters as $chapter) {
                        $tmp["l3memberID"] = $chapter["l3memberID"];
                        $data["chapters"][$chapter["chapter"]] = $tmp;
                    }

                    foreach ($data["chapters"] as $chapter) {
                        if(!empty($chapter))
                            $checkersArr[] = $chapter["l3memberID"];
                    }
                    $checkersArr = array_unique($checkersArr);
                }
            }
        }

        $allMembers = [];
        $allMembers = Arrays::append($allMembers, $adminsArr);
        $allMembers = Arrays::append($allMembers, $checkersArr);
        $allMembers = Arrays::append($allMembers, $translatorsArr);
        $allMembers = array_unique($allMembers);

        $membersArray = (array)$membersModel->getMembers($allMembers, true, true);

        foreach ($membersArray as $member) {
            if(in_array($member->memberID, $adminsArr))
            {
                $tmp = [
                    "fname" => trim(mb_convert_case ($member->firstName, MB_CASE_TITLE, 'UTF-8')),
                    "lname" => trim(mb_convert_case ($member->lastName, MB_CASE_TITLE, 'UTF-8')),
                    "uname" => trim($member->userName),
                    "role" => "",
                    "signup" => $member->created,
                    "email" => $member->email,
                    "tou" => "yes",
                    "sof" => "yes"
                ];
                $admins[$member->memberID] = $tmp;
            }
            if(in_array($member->memberID, $checkersArr))
            {
                $role = "";

                if($withRoles)
                {
                    $church_role = (array)json_decode($member->church_role);

                    if (in_array("Pastor", $church_role))
                        $role = __('pastor');
                    elseif (in_array("Seminary Professor", $church_role))
                        $role = __('seminary_professor');
                    elseif (in_array("Denominational Leader", $church_role))
                        $role = __('denominational_leader');
                    elseif (in_array("Bishop", $church_role))
                        $role = __('bishop');
                    elseif (in_array("Elder", $church_role))
                        $role = __('elder');
                    elseif (in_array("Teacher", $church_role))
                        $role = __('teacher');
                }

                $tmp = [
                    "fname" => trim(mb_convert_case ($member->firstName, MB_CASE_TITLE, 'UTF-8')),
                    "lname" => trim(mb_convert_case ($member->lastName, MB_CASE_TITLE, 'UTF-8')),
                    "uname" => trim($member->userName),
                    "role" => $role != "" ? $role : "",
                    "signup" => $member->created,
                    "email" => $member->email,
                    "tou" => "yes",
                    "sof" => "yes"
                ];
                $checkers[$member->memberID] = $tmp;
            }
            if(in_array($member->memberID, $translatorsArr))
            {
                $tmp = [
                    "fname" => trim(mb_convert_case ($member->firstName, MB_CASE_TITLE, 'UTF-8')),
                    "lname" => trim(mb_convert_case ($member->lastName, MB_CASE_TITLE, 'UTF-8')),
                    "uname" => trim($member->userName),
                    "role" => "",
                    "signup" => $member->created,
                    "email" => $member->email,
                    "tou" => "yes",
                    "sof" => "yes"
                ];
                $translators[$member->memberID] = $tmp;
            }
        }

        $result["admins"] = $admins;
        $result["checkers"] = $checkers;
        $result["translators"] = $translators;

        return $result;
    }


    public function getProjectWithContributors($projectID)
    {
        return $this->db->table("events")
            ->select([
                "events.eventID","events.admins", "events.admins_l2", "events.admins_l3",
                "translators.verbCheck","translators.peerCheck",
                "translators.kwCheck","translators.crCheck",
                "checkers_l2.sndCheck",
                "checkers_l2.peer1Check", "checkers_l2.peer2Check",
                "checkers_l3.peerCheck AS peer3Check", "projects.bookProject"
            ])
            ->leftJoin("translators", "events.eventID", "=", "translators.eventID")
            ->leftJoin("checkers_l2", "events.eventID", "=", "checkers_l2.eventID")
            ->leftJoin("checkers_l3", "events.eventID", "=", "checkers_l3.eventID")
            ->leftJoin("projects", "events.projectID", "=", "projects.projectID")
            ->where("events.projectID", $projectID)
            ->orderBy("events.eventID")
            ->get();

    }

    public function getProjectContributors($projectID, $withRoles = true, $withAdmins = true)
    {
        $project = $this->getProjectWithContributors($projectID);
        if (!empty($project)) {

            $contributors = [];
            $contributorsIDs = [];

            $membersModel = new MembersModel();

            $mode = $project[0]->bookProject;
            $lastEventID = null;

            // Checkers
            foreach ($project as $participant) {
                // Facilitators
                if ($withAdmins) {
                    $contributorsIDs += (array)json_decode($participant->admins);
                    $contributorsIDs += (array)json_decode($participant->admins_l2);
                    $contributorsIDs += (array)json_decode($participant->admins_l3);
                }

                $verbCheck = (array)json_decode($participant->verbCheck);
                $peerCheck = (array)json_decode($participant->peerCheck);
                $kwCheck = (array)json_decode($participant->kwCheck);
                $crCheck = (array)json_decode($participant->crCheck);
                $sndCheck = (array)json_decode($participant->sndCheck);
                $peer1Check = (array)json_decode($participant->peer1Check);
                $peer2Check = (array)json_decode($participant->peer2Check);
                $peer3Check = (array)json_decode($participant->peer3Check);

                // Resource Checkers
                if (in_array($mode, ["sun"])) {
                    $contributorsIDs = Arrays::append($contributorsIDs, array_values(array_map(function ($elm) {
                        return $elm->memberID;
                    }, $peerCheck)));
                    $contributorsIDs = Arrays::append($contributorsIDs, array_values(array_map(function ($elm) {
                        return $elm->memberID;
                    }, $kwCheck)));
                    $contributorsIDs = Arrays::append($contributorsIDs, array_values(array_map(function ($elm) {
                        return $elm->memberID;
                    }, $crCheck)));
                } else {
                    // Scripture Checkers
                    $contributorsIDs = Arrays::append($contributorsIDs, array_values($verbCheck));
                    $contributorsIDs = Arrays::append($contributorsIDs, array_values($peerCheck));
                    $contributorsIDs = Arrays::append($contributorsIDs, array_values($kwCheck));
                    $contributorsIDs = Arrays::append($contributorsIDs, array_values($crCheck));

                    $contributorsIDs = Arrays::append($contributorsIDs, array_values(array_map(function ($elm) {
                        return $elm->memberID;
                    }, $sndCheck)));
                    $contributorsIDs = Arrays::append($contributorsIDs, array_values(array_map(function ($elm) {
                        return $elm->memberID;
                    }, $peer1Check)));
                    $contributorsIDs = Arrays::append($contributorsIDs, array_values(array_map(function ($elm) {
                        return $elm->memberID;
                    }, $peer2Check)));
                }

                $contributorsIDs = Arrays::append($contributorsIDs, array_values(array_map(function ($elm) {
                    return $elm->memberID;
                }, $peer3Check)));

                // Translators/L2 checkers/L3 checkers
                if ($lastEventID != $participant->eventID) {
                    $chapters = $this->getChapters($participant->eventID, null, null, null);

                    foreach ($chapters as $chapter) {
                        if ($chapter["memberID"] != null) {
                            $contributorsIDs[] = $chapter["memberID"];
                        }
                        if ($chapter["l2memberID"] != null) {
                            $contributorsIDs[] = $chapter["l2memberID"];
                        }
                        if ($chapter["l3memberID"] != null) {
                            $contributorsIDs[] = $chapter["l3memberID"];
                        }
                    }
                    $lastEventID = $participant->eventID;
                }
            }

            $contributorsIDs = array_unique($contributorsIDs);

            $filteredNumeric = array_filter($contributorsIDs, function($elm) {
                return is_numeric($elm) && $elm > 0;
            });

            $contributors = Arrays::append($contributors, array_filter($contributorsIDs, function($elm) {
                return !is_numeric($elm);
            }));
            $contributors = array_map(function($elm) {
                $name = mb_split(" ", $elm);
                return [
                    "fname" => trim(mb_convert_case ($name[0], MB_CASE_TITLE, 'UTF-8')),
                    "lname" => trim(mb_convert_case (isset($name[1]) ? $name[1] : "", MB_CASE_TITLE, 'UTF-8')),
                    "uname" => "---",
                    "role" => "",
                    "signup" => "---",
                    "email" => "---",
                    "tou" => "yes",
                    "sof" => "yes"
                ];
            }, $contributors);

            $membersArray = (array)$membersModel->getMembers($filteredNumeric, true, true);

            foreach ($membersArray as $member) {
                if(in_array($member->memberID, $filteredNumeric))
                {
                    $role = "";

                    if($withRoles)
                    {
                        $church_role = (array)json_decode($member->church_role);

                        if (in_array("Pastor", $church_role))
                            $role = __('pastor');
                        elseif (in_array("Seminary Professor", $church_role))
                            $role = __('seminary_professor');
                        elseif (in_array("Denominational Leader", $church_role))
                            $role = __('denominational_leader');
                        elseif (in_array("Bishop", $church_role))
                            $role = __('bishop');
                        elseif (in_array("Elder", $church_role))
                            $role = __('elder');
                        elseif (in_array("Teacher", $church_role))
                            $role = __('teacher');
                    }

                    $tmp = [
                        "fname" => trim(mb_convert_case ($member->firstName, MB_CASE_TITLE, 'UTF-8')),
                        "lname" => trim(mb_convert_case ($member->lastName, MB_CASE_TITLE, 'UTF-8')),
                        "uname" => trim($member->userName),
                        "role" => $role != "" ? $role : "",
                        "signup" => $member->created,
                        "email" => $member->email,
                        "tou" => "yes",
                        "sof" => "yes"
                    ];

                    $contributors[] = $tmp;
                }
            }

            $contributors = array_unique($contributors, SORT_REGULAR);
            sort($contributors);

            return $contributors;
        }

        return [];
    }

    /**
     * Get notifications for assigned events
     * @return array
     */
    public function getNotifications()
    {
        $stepsIn = $this->db->quoteArray([
            EventSteps::PEER_REVIEW,
            EventSteps::KEYWORD_CHECK,
            EventSteps::CONTENT_REVIEW
        ]);

        $sql = "SELECT trs.*, ".PREFIX."members.userName, ".PREFIX."members.firstName, ".PREFIX."members.lastName, ".
                PREFIX."events.bookCode, ".PREFIX."projects.bookProject, ".PREFIX."projects.sourceBible, ".
                "t_lang.langName AS tLang, s_lang.langName AS sLang, ".PREFIX."abbr.name AS bookName ".
            "FROM ".PREFIX."translators AS trs ".
                "LEFT JOIN ".PREFIX."members ON trs.memberID = ".PREFIX."members.memberID ".
                "LEFT JOIN ".PREFIX."events ON ".PREFIX."events.eventID = trs.eventID ".
                "LEFT JOIN ".PREFIX."projects ON ".PREFIX."projects.projectID = ".PREFIX."events.projectID ".
                "LEFT JOIN ".PREFIX."languages AS t_lang ON ".PREFIX."projects.targetLang = t_lang.langID ".
                "LEFT JOIN ".PREFIX."languages AS s_lang ON ".PREFIX."projects.sourceLangID = s_lang.langID ".
                "LEFT JOIN ".PREFIX."abbr ON ".PREFIX."events.bookCode = ".PREFIX."abbr.code ".
            "WHERE (trs.eventID IN(SELECT eventID FROM ".PREFIX."translators WHERE memberID = :memberID) ".
                "OR ".PREFIX."events.admins LIKE :adminID) ".
            "AND trs.memberID != :memberID ".
            "AND trs.step IN ($stepsIn) ".
            "AND trs.checkerID = 0 AND trs.hideChkNotif = false";

        $prepare = [
            ":memberID" => Session::get("memberID"),
            ":adminID" => '%\"'.Session::get("memberID").'"%'
        ];

        return $this->db->select($sql, $prepare);
    }


    /**
     * Get notifications for Level 2 events
     * @return array
     */
    public function getNotificationsL2()
    {
        $sql = "SELECT chks.*, ".
            PREFIX."members.userName, ".PREFIX."members.firstName, ".PREFIX."members.lastName, ".
            PREFIX."events.bookCode, ".PREFIX."projects.bookProject, ".PREFIX."projects.sourceBible, mychks.step as myStep, ".
            "t_lang.langName AS tLang, s_lang.langName AS sLang, ".PREFIX."abbr.name AS bookName ".
            "FROM ".PREFIX."checkers_l2 AS chks ".
            "LEFT JOIN ".PREFIX."members ON chks.memberID = ".PREFIX."members.memberID ".
            "LEFT JOIN ".PREFIX."events ON ".PREFIX."events.eventID = chks.eventID ".
            "LEFT JOIN ".PREFIX."checkers_l2 as mychks ON mychks.memberID = :memberID AND mychks.eventID = chks.eventID ".
            "LEFT JOIN ".PREFIX."projects ON ".PREFIX."projects.projectID = ".PREFIX."events.projectID ".
            "LEFT JOIN ".PREFIX."languages AS t_lang ON ".PREFIX."projects.targetLang = t_lang.langID ".
            "LEFT JOIN ".PREFIX."languages AS s_lang ON ".PREFIX."projects.sourceLangID = s_lang.langID ".
            "LEFT JOIN ".PREFIX."abbr ON ".PREFIX."events.bookCode = ".PREFIX."abbr.code ".
            "WHERE (chks.eventID IN(SELECT eventID FROM ".PREFIX."checkers_l2 WHERE memberID = :memberID) ".
            "OR ".PREFIX."events.admins_l2 LIKE :adminID) ".
            "AND chks.sndCheck != '' ";

        $prepare = [
            ":memberID" => Session::get("memberID"),
            ":adminID" => '%\"'.Session::get("memberID").'"%'
        ];

        $notifications = $this->db->select($sql, $prepare);
        $notifs = [];

        foreach ($notifications as $notification)
        {
            // Second check notifications
            if($notification->memberID != Session::get("memberID"))
            {
                $sndCheck = (array)json_decode($notification->sndCheck, true);
                foreach ($sndCheck as $chapter => $data) {
                    // Exclude taken chapters
                    if($data["memberID"] > 0) continue;

                    $notif = clone $notification;
                    $notif->step = EventCheckSteps::SND_CHECK;
                    $notif->currentChapter = $chapter;
                    $notif->manageMode = "l2";
                    $notifs[] = $notif;
                }
            }

            // Peer check notifications
            $peer1Check = (array)json_decode($notification->peer1Check, true);
            $peer2Check = (array)json_decode($notification->peer2Check, true);
            foreach ($peer1Check as $chapter => $data) {
                // Exclude taken chapters
                if($data["memberID"] > 0)
                {
                    if(array_key_exists($chapter, $peer2Check))
                    {
                        $p2 = $peer2Check[$chapter];
                        if($p2["memberID"] > 0
                            || $data["memberID"] == Session::get("memberID"))
                            continue;
                    }
                }

                $notif = clone $notification;
                $notif->step = EventCheckSteps::PEER_REVIEW_L2;
                $notif->currentChapter = $chapter;
                $notif->manageMode = "l2";
                $notifs[] = $notif;
            }
        }

        return $notifs;
    }


    /**
     * Get notifications for Level 3 events
     * @return array
     */
    public function getNotificationsL3()
    {
        $sql = "SELECT chks.*, ".
            PREFIX."members.userName, ".PREFIX."members.firstName, ".PREFIX."members.lastName, ".
            PREFIX."events.bookCode, ".PREFIX."projects.bookProject, ".PREFIX."projects.sourceBible, mychks.step as myStep, ".
            "t_lang.langName AS tLang, s_lang.langName AS sLang, ".PREFIX."abbr.name AS bookName ".
            "FROM ".PREFIX."checkers_l3 AS chks ".
            "LEFT JOIN ".PREFIX."members ON chks.memberID = ".PREFIX."members.memberID ".
            "LEFT JOIN ".PREFIX."events ON ".PREFIX."events.eventID = chks.eventID ".
            "LEFT JOIN ".PREFIX."checkers_l3 as mychks ON mychks.memberID = :memberID AND mychks.eventID = chks.eventID ".
            "LEFT JOIN ".PREFIX."projects ON ".PREFIX."projects.projectID = ".PREFIX."events.projectID ".
            "LEFT JOIN ".PREFIX."languages AS t_lang ON ".PREFIX."projects.targetLang = t_lang.langID ".
            "LEFT JOIN ".PREFIX."languages AS s_lang ON ".PREFIX."projects.sourceLangID = s_lang.langID ".
            "LEFT JOIN ".PREFIX."abbr ON ".PREFIX."events.bookCode = ".PREFIX."abbr.code ".
            "WHERE (chks.eventID IN(SELECT eventID FROM ".PREFIX."checkers_l3 WHERE memberID = :memberID) ".
            "OR ".PREFIX."events.admins_l3 LIKE :adminID) ".
            "AND chks.peerCheck != '' ";

        $prepare = [
            ":memberID" => Session::get("memberID"),
            ":adminID" => '%\"'.Session::get("memberID").'"%'
        ];

        $notifications = $this->db->select($sql, $prepare);
        $notifs = [];

        foreach ($notifications as $notification)
        {
            if($notification->step != EventCheckSteps::PEER_REVIEW_L3)
                continue;

            if(Session::get("memberID") == $notification->memberID)
                continue;

            // Peer check notifications
            $peerCheck = (array)json_decode($notification->peerCheck, true);
            foreach ($peerCheck as $chapter => $data) {
                // Exclude taken chapters
                if($data["memberID"] > 0)
                    continue;

                $notif = clone $notification;
                $notif->step = EventCheckSteps::PEER_REVIEW_L3;
                $notif->currentChapter = $chapter;
                $notif->manageMode = "l3";
                $notifs[] = $notif;
            }
        }

        return $notifs;
    }


    /**
     * Get notifications for Level 2 events
     * @return array
     */
    public function getNotificationsSun()
    {
        $sql = "SELECT trs.*, ".
            PREFIX."members.userName, ".PREFIX."members.firstName, ".PREFIX."members.lastName, ".
            PREFIX."events.bookCode, ".PREFIX."projects.sourceBible, ".PREFIX."projects.bookProject, mytrs.step as myStep, ".
            "t_lang.langName AS tLang, s_lang.langName AS sLang, ".PREFIX."abbr.name AS bookName ".
            "FROM ".PREFIX."translators AS trs ".
            "LEFT JOIN ".PREFIX."members ON trs.memberID = ".PREFIX."members.memberID ".
            "LEFT JOIN ".PREFIX."events ON ".PREFIX."events.eventID = trs.eventID ".
            "LEFT JOIN ".PREFIX."translators as mytrs ON mytrs.memberID = :memberID AND mytrs.eventID = trs.eventID ".
            "LEFT JOIN ".PREFIX."projects ON ".PREFIX."projects.projectID = ".PREFIX."events.projectID ".
            "LEFT JOIN ".PREFIX."languages AS t_lang ON ".PREFIX."projects.targetLang = t_lang.langID ".
            "LEFT JOIN ".PREFIX."languages AS s_lang ON ".PREFIX."projects.sourceLangID = s_lang.langID ".
            "LEFT JOIN ".PREFIX."abbr ON ".PREFIX."events.bookCode = ".PREFIX."abbr.code ".
            "WHERE (trs.eventID IN(SELECT eventID FROM ".PREFIX."translators WHERE memberID = :memberID) ".
            "OR ".PREFIX."events.admins LIKE :adminID) ".
            "AND trs.kwCheck != '' AND ".PREFIX."projects.bookProject = 'sun' ";

        $prepare = [
            ":memberID" => Session::get("memberID"),
            ":adminID" => '%\"'.Session::get("memberID").'"%'
        ];

        $notifications = $this->db->select($sql, $prepare);
        $notifs = [];

        foreach ($notifications as $notification)
        {
            // Theological check notifications
            if($notification->memberID != Session::get("memberID"))
            {
                $kwCheck = (array)json_decode($notification->kwCheck, true);
                foreach ($kwCheck as $chapter => $data) {
                    // Exclude taken chapters
                    if($data["memberID"] > 0) continue;

                    $notif = clone $notification;
                    $notif->step = EventSteps::THEO_CHECK;
                    $notif->currentChapter = $chapter;
                    $notif->manageMode = "sun";
                    $notifs[] = $notif;
                }
            }

            // Verse-by-verse check notifications
            if($notification->memberID != Session::get("memberID"))
            {
                $crCheck = (array)json_decode($notification->crCheck, true);
                foreach ($crCheck as $chapter => $data) {
                    // Exclude taken chapters
                    if($data["memberID"] > 0) continue;

                    $notif = clone $notification;
                    $notif->step = EventSteps::CONTENT_REVIEW;
                    $notif->currentChapter = $chapter;
                    $notif->manageMode = "sun";
                    $notifs[] = $notif;
                }
            }
        }

        return $notifs;
    }

    public function getAllNotifications($langs = array("en")) {

        if(is_array($langs) && !empty($langs))
        {
            $langsIn = $this->db->quoteArray($langs);
            $stepsIn = $this->db->quoteArray([
                EventSteps::PEER_REVIEW,
                EventSteps::KEYWORD_CHECK,
                EventSteps::CONTENT_REVIEW,
            ]);

            $sql = "SELECT trs.*, ".PREFIX."members.userName, ".PREFIX."members.firstName, ".PREFIX."members.lastName, "
                .PREFIX."events.bookCode, ".PREFIX."projects.bookProject, ".PREFIX."projects.sourceBible, ".
                "t_lang.langName AS tLang, s_lang.langName AS sLang, ".PREFIX."abbr.name AS bookName ".
                "FROM ".PREFIX."translators AS trs ".
                "LEFT JOIN ".PREFIX."members ON trs.memberID = ".PREFIX."members.memberID ".
                "LEFT JOIN ".PREFIX."events ON ".PREFIX."events.eventID = trs.eventID ".
                "LEFT JOIN ".PREFIX."projects ON ".PREFIX."projects.projectID = ".PREFIX."events.projectID ".
                "LEFT JOIN ".PREFIX."languages AS t_lang ON ".PREFIX."projects.targetLang = t_lang.langID ".
                "LEFT JOIN ".PREFIX."languages AS s_lang ON ".PREFIX."projects.sourceLangID = s_lang.langID ".
                "LEFT JOIN ".PREFIX."abbr ON ".PREFIX."events.bookCode = ".PREFIX."abbr.code ".
                "WHERE (".PREFIX."projects.gwLang IN($langsIn) OR ".PREFIX."projects.targetLang IN($langsIn) OR ".PREFIX."events.admins LIKE :adminID) ".
                "AND trs.memberID != :memberID ".
                "AND trs.step IN ($stepsIn) ".
                "AND trs.checkerID = 0 AND trs.hideChkNotif = false";

            $prepare = [
                ":memberID" => Session::get("memberID"),
                ":adminID" => '%\"'.Session::get("memberID").'"%'
            ];

            return $this->db->select($sql, $prepare);
        }
    }

    /** Get list of all languages
     * @param null $isGW (true - gateway, false - other, null - all)
     * @param null $langs filter by list of lang ids
     * @return array
     */
    public function getAllLanguages($isGW = null, $langs = null)
    {
        $builder = $this->db->table("languages");

        if($isGW !== null)
        {
            $builder->where("languages.isGW", $isGW);
        }
        if(is_array($langs) && !empty($langs))
        {
            $builder->whereIn("languages.langID", $langs);
        }

        return $builder->select("languages.langID", "languages.langName", "languages.angName", "gateway_projects.gwProjectID")
            ->leftJoin("gateway_projects", "languages.langID", "=", "gateway_projects.gwLang")
            ->orderBy("languages.langID")->get();
    }


    /**
     * Get list of other languages
     * @param string $memberID
     * @param string $gwLang
     * @return array
     */
    public function getTargetLanguages($gwLang)
    {
        $builder = $this->db->table("languages, gateway_projects")
            ->where("languages.gwLang", function ($query ) use ($gwLang)
            {
                $query->select("langName")->from("languages")
                    ->where("langID", $gwLang);
            })
            ->where("gateway_projects.gwLang", $gwLang)
            ->select(array("languages.langID", "languages.langName", "languages.angName"));

        return $builder->get();
    }


    public function getAdminLanguages($memberID)
    {
        return $this->db->table("events")
            ->select("projects.gwLang", "projects.targetLang")
            ->leftJoin("projects", "events.projectID", "=", "projects.projectID")
            ->where("events.admins", "LIKE", "%\"$memberID\"%")
            ->orWhere("events.admins_l2", "LIKE", "%\"$memberID\"%")
            ->get();
    }

    public function getSuperadminLanguages($memberID)
    {
        return $this->db->table("gateway_projects")
            ->select("gwLang")
            ->where("admins", "LIKE", "%\"$memberID\"%")
            ->get();
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
                "FROM ".PREFIX."translations AS ts ".
                "LEFT JOIN ".PREFIX."translators AS trs1 ON ts.trID = trs1.trID ".
                "LEFT JOIN ".PREFIX."translators AS trs2 ON trs1.pairID = trs2.trID ".
                "LEFT JOIN ".PREFIX."checkers_l2 AS l2 ON ts.eventID = l2.eventID AND l2.memberID = :memberID ".
                "LEFT JOIN ".PREFIX."checkers_l3 AS l3 ON ts.eventID = l3.eventID AND l3.memberID = :memberID ".
                "WHERE tID = :tID";

        $prepare = array(":memberID" => $memberID, ":tID" => $tID);

        return $this->db->select($sql, $prepare);
    }

    public function getBooks()
    {
        return $this->db->table("abbr")
            ->orderBy("abbrID")
            ->get();
    }

    public function getBooksOfTranslators()
    {
            return $this->db->table("chapters")
                    ->select(["members.userName", "members.firstName", "members.lastName",
                        "chapters.chapter", "chapters.done", "abbr.name", "abbr.code",
                        "projects.bookProject", "projects.targetLang", "languages.angName", "languages.langName"])
                    ->leftJoin("members", "chapters.memberID", "=", "members.memberID")
                    ->leftJoin("events", "chapters.eventID", "=", "events.eventID")
                    ->leftJoin("projects", "events.projectID", "=", "projects.projectID")
                    ->leftJoin("abbr", "events.bookCode", "=", "abbr.code")
                    ->leftJoin("languages", "projects.targetLang", "=", "languages.langID")
                    ->orderBy("members.userName")
                    ->orderBy("abbr.abbrID")
                    ->orderBy("chapters.chapter")
                    ->get();
    }

    public function getEventMemberInfo($eventID, $memberID)
    {
        $sql = "SELECT trs.memberID AS translator, chk.currentChapter AS chkChapter, ".
            "chk.step AS checkerStep, chk.checkerID AS checker, ".
            "proj.bookProject, trs.isChecker, ".
            "l2.memberID AS l2checker, l3.memberID AS l3checker ".
            "FROM ".PREFIX."events AS evnt ".
            "LEFT JOIN ".PREFIX."translators AS trs ON evnt.eventID = trs.eventID AND trs.memberID = :memberID ".
            "LEFT JOIN ".PREFIX."translators AS chk ON evnt.eventID = chk.eventID AND chk.checkerID = :memberID ".
            "LEFT JOIN ".PREFIX."checkers_l2 AS l2 ON evnt.eventID = l2.eventID AND l2.memberID = :memberID ".
            "LEFT JOIN ".PREFIX."checkers_l3 AS l3 ON evnt.eventID = l3.eventID AND l3.memberID = :memberID ".
            "LEFT JOIN ".PREFIX."projects AS proj ON evnt.projectID = proj.projectID ".
            "WHERE evnt.eventID = :eventID";

        $prepare = array(
            ":memberID" => $memberID,
            ":eventID" => $eventID,
            ":outMemberID" => '%"memberID":"'.$memberID.'""%');

        return $this->db->select($sql, $prepare);
    }

    public function getEventTranslator($eventID, $trID)
    {
        return $this->db->table("translators")
            ->where(["eventID" => $eventID])
            ->where(["trID" => $trID])
            ->get();
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
     * Update gateway project
     * @param array $data
     * @param array $where
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
     * Update project
     * @param array $data
     * @param array $where
     * @return string
     */
    public function updateProject($data, $where)
    {
        return $this->db->table("projects")
            ->where($where)
            ->update($data);
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
     * @return string
     */
    public function addTranslator($data)
    {
        return $this->db->table("translators")
            ->insertGetId($data);
    }

    /**
     * Add member as new Level 2 checker for event
     * @param array $data
     * @return string
     */
    public function addL2Checker($data)
    {
        return $this->db->table("checkers_l2")
            ->insertGetId($data);
    }

    /**
     * Add member as new Level 3 checker for event
     * @param array $data
     * @return string
     */
    public function addL3Checker($data)
    {
        return $this->db->table("checkers_l3")
            ->insertGetId($data);
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
     * Delete event
     * @param array $where
     * @return int
     */
    public function deleteEvent($where)
    {
        return $this->db->table("events")
            ->where($where)
            ->delete();
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

    /**
     * Delete translators from event/s
     * @param array $where
     * @return int
     */
    public function deleteTranslators($where)
    {
        return $this->db->table("translators")
            ->where($where)
            ->delete();
    }

    /**
     * Update L2 Checker
     * @param array $data
     * @param array $where
     * @return int
     */
    public function updateL2Checker($data, $where)
    {
        return $this->db->table("checkers_l2")
            ->where($where)
            ->update($data);
    }

    /**
     * Delete L2 Checkers from event/s
     * @param array $where
     * @return int
     */
    public function deleteL2Checkers($where)
    {
        return $this->db->table("checkers_l2")
            ->where($where)
            ->delete();
    }

    /**
     * Update L3 Checker
     * @param array $data
     * @param array $where
     * @return int
     */
    public function updateL3Checker($data, $where)
    {
        return $this->db->table("checkers_l3")
            ->where($where)
            ->update($data);
    }

    /**
     * Delete L3 Checkers from event/s
     * @param array $where
     * @return int
     */
    public function deleteL3Checkers($where)
    {
        return $this->db->table("checkers_l3")
            ->where($where)
            ->delete();
    }

    /**
     * Assign chapter to translator's queue
     * @param $data
     * @return int
     */
    public function assignChapter($data)
    {
        return $this->db->table("chapters")
            ->insertGetId($data);
    }

    /**
     * Remove chapter from translator's queue
     * @param $where
     * @return int
     */
    public function removeChapter($where)
    {
        return $this->db->table("chapters")
            ->where($where)
            ->delete();
    }

    /**
     * Get all assigned chapters of event of a translator
     * @param $eventID
     * @param $memberID
     * @param $chapter
     * @param $manageMode
     * @return array|static[]
     */
    public function getChapters($eventID, $memberID = null, $chapter = null, $manageMode = "l1")
    {
        $this->db->setFetchMode(PDO::FETCH_ASSOC);

        $builder = $this->db->table("chapters");

        if($manageMode == "l2")
        {
            $builder->leftJoin("checkers_l2", function($join){
                $join->on("chapters.eventID", "=", "checkers_l2.eventID")
                    ->on("chapters.l2memberID", "=", "checkers_l2.memberID");
            });
            if($memberID !== null)
                $builder->where(["chapters.l2memberID" => $memberID]);
        }
        else if($manageMode == "l3")
        {
            $builder->leftJoin("checkers_l3", function($join){
                $join->on("chapters.eventID", "=", "checkers_l3.eventID")
                    ->on("chapters.l3memberID", "=", "checkers_l3.memberID");
            });
            if($memberID !== null)
                $builder->where(["chapters.l3memberID" => $memberID]);
        }
        else if($manageMode != null)
        {
            $builder->leftJoin("translators", function($join){
                $join->on("chapters.eventID", "=", "translators.eventID")
                    ->on("chapters.memberID", "=", "translators.memberID");
            });
            if($memberID !== null)
                $builder->where(["chapters.memberID" => $memberID]);
        }
        else
        {
            if($memberID !== null)
                $builder->where(["chapters.memberID" => $memberID]);
        }

        if($chapter !== null)
            $builder->where(["chapters.chapter" => $chapter]);

        $builder->where(["chapters.eventID" => $eventID])
            ->orderBy("chapters.chapter");

        $res = $builder->get();

        $this->db->setFetchMode(PDO::FETCH_CLASS);

        return $res;
    }


    /**
     * Get next chapter to translate/check
     * @param $eventID
     * @param $memberID
     * @param string $level
     * @return array|\Database\Query\Builder[]
     */
    public function getNextChapter($eventID, $memberID, $level = "l1")
    {
        $builder = $this->db->table("chapters")
            ->where(["eventID" => $eventID]);

        if($level == "l1")
        {
            $builder->where(["memberID" => $memberID])
                ->where("done", "!=", true);
        }
        else if($level == "l2")
        {
            $builder->where(["l2memberID" => $memberID])
                ->where("l2checked", "!=", true);
        }
        else if($level == "l3")
        {
            $builder->where(["l3memberID" => $memberID])
                ->where("l3checked", "!=", true);
        }

        return $builder->orderBy("chapter")->get();
    }

    /**
     * Update chapter
     * @param array $data
     * @param array $where
     * @return int
     */
    public function updateChapter($data, $where)
    {
        return $this->db->table("chapters")
            ->where($where)
            ->update($data);
    }

    public function calculateEventProgress($eventID) {

        $event = $this->getEvent($eventID);

        if(!empty($event)) {
            switch ($event[0]->state) {
                case EventStates::L2_RECRUIT:
                case EventStates::L2_CHECK:
                case EventStates::L2_CHECKED:
                    $level = "l2";
                    break;

                case EventStates::L3_RECRUIT:
                case EventStates::L3_CHECK:
                case EventStates::COMPLETE:
                    $level = "l3";
                    break;

                default:
                    $level = "l1";
                    break;
            }

            if(in_array($event[0]->bookProject, ["ulb","udb"]) && $level == "l1") // ULB, UDB of level 1
            {
                return $this->calculateUlbLevel1EventProgress($event, true);
            }
            elseif($level == "l2") // ULB, UDB of level 2
            {
                return $this->calculateUlbLevel2EventProgress($event, true);
            }
            elseif($level == "l3") // All projects of level 3
            {
                return $this->calculateAnyLevel3EventProgress($event, true);
            }
            elseif($event[0]->bookProject == "sun") // SUNs of level 1,2,3
            {
                return $this->calculateSunLevel1EventProgress($event, true);
            }
        }

        return 0;
    }

    public function calculateUlbLevel1EventProgress($event, $progressOnly = false) {
        $data = [];
        $data["overall_progress"] = 0;
        $data["chapters"] = [];
        for($i=1; $i <= $event[0]->chaptersNum; $i++)
        {
            $data["chapters"][$i] = [];
        }

        $chapters = $this->getChapters($event[0]->eventID, null, null, "l1");

        foreach ($chapters as $chapter) {
            $tmp["trID"] = $chapter["trID"];
            $tmp["memberID"] = $chapter["memberID"];
            $tmp["chunks"] = json_decode($chapter["chunks"], true);
            $tmp["done"] = $chapter["done"];

            $data["chapters"][$chapter["chapter"]] = $tmp;
        }

        $overallProgress = 0;
        $memberSteps = [];
        $members = [];

        $translationModel = new TranslationsModel();
        $chunks = $translationModel->getTranslationByEventID($event[0]->eventID);

        foreach ($chunks as $chunk) {
            if(!array_key_exists($chunk->memberID, $memberSteps))
            {
                $memberSteps[$chunk->memberID]["step"] = $chunk->step;
                $memberSteps[$chunk->memberID]["verbCheck"] = $chunk->verbCheck;
                $memberSteps[$chunk->memberID]["peerCheck"] = $chunk->peerCheck;
                $memberSteps[$chunk->memberID]["kwCheck"] = $chunk->kwCheck;
                $memberSteps[$chunk->memberID]["crCheck"] = $chunk->crCheck;
                $memberSteps[$chunk->memberID]["currentChapter"] = $chunk->currentChapter;
                $memberSteps[$chunk->memberID]["checkerID"] = $chunk->checkerID;
                $members[$chunk->memberID] = "";
            }

            if($chunk->chapter == null)
                continue;

            $data["chapters"][$chunk->chapter]["chunksData"][] = $chunk;

            if(!isset($data["chapters"][$chunk->chapter]["lastEdit"]))
            {
                $data["chapters"][$chunk->chapter]["lastEdit"] = $chunk->dateUpdate;
            }
            else
            {
                $prevDate = strtotime($data["chapters"][$chunk->chapter]["lastEdit"]);
                if($prevDate < strtotime($chunk->dateUpdate))
                    $data["chapters"][$chunk->chapter]["lastEdit"] = $chunk->dateUpdate;
            }
        }

        foreach ($data["chapters"] as $key => $chapter) {
            if(empty($chapter)) continue;

            $currentStep = EventSteps::PRAY;
            $multiDraftState = StepsStates::NOT_STARTED;
            $consumeState = StepsStates::NOT_STARTED;
            $verbCheckState = StepsStates::NOT_STARTED;
            $chunkingState = StepsStates::NOT_STARTED;
            $blindDraftState = StepsStates::NOT_STARTED;

            $members[$chapter["memberID"]] = "";
            $data["chapters"][$key]["progress"] = 0;

            $currentChapter = $memberSteps[$chapter["memberID"]]["currentChapter"];
            $verbCheck = (array)json_decode($memberSteps[$chapter["memberID"]]["verbCheck"], true);
            $peerCheck = (array)json_decode($memberSteps[$chapter["memberID"]]["peerCheck"], true);
            $kwCheck = (array)json_decode($memberSteps[$chapter["memberID"]]["kwCheck"], true);
            $crCheck = (array)json_decode($memberSteps[$chapter["memberID"]]["crCheck"], true);
            $currentChecker = $memberSteps[$chapter["memberID"]]["checkerID"];

            // Set default values
            $data["chapters"][$key]["multiDraft"]["state"] = StepsStates::NOT_STARTED;
            $data["chapters"][$key]["consume"]["state"] = StepsStates::NOT_STARTED;
            $data["chapters"][$key]["verb"]["state"] = StepsStates::NOT_STARTED;
            $data["chapters"][$key]["verb"]["checkerID"] = "na";
            $data["chapters"][$key]["chunking"]["state"] = StepsStates::NOT_STARTED;
            $data["chapters"][$key]["blindDraft"]["state"] = StepsStates::NOT_STARTED;
            $data["chapters"][$key]["selfEdit"]["state"] = StepsStates::NOT_STARTED;
            $data["chapters"][$key]["peer"]["state"] = StepsStates::NOT_STARTED;
            $data["chapters"][$key]["peer"]["checkerID"] = "na";
            $data["chapters"][$key]["kwc"]["state"] = StepsStates::NOT_STARTED;
            $data["chapters"][$key]["kwc"]["checkerID"] = "na";
            $data["chapters"][$key]["crc"]["state"] = StepsStates::NOT_STARTED;
            $data["chapters"][$key]["crc"]["checkerID"] = "na";
            $data["chapters"][$key]["finalReview"]["state"] = StepsStates::NOT_STARTED;

            // When no chunks created or translation not started
            if(empty($chapter["chunks"]) || !isset($chapter["chunksData"]))
            {
                if($currentChapter == $key)
                {
                    $currentStep = $memberSteps[$chapter["memberID"]]["step"];
                    if($currentChecker > 0)
                    {
                        $verbCheckState = StepsStates::IN_PROGRESS;
                        $consumeState = StepsStates::FINISHED;
                        $currentChecker = $memberSteps[$chapter["memberID"]]["checkerID"];
                        $members[$currentChecker] = "";
                    }
                    elseif(array_key_exists($key, $verbCheck))
                    {
                        $consumeState = StepsStates::FINISHED;
                        if(is_numeric($verbCheck[$key]))
                        {
                            $members[$verbCheck[$key]] = "";
                        }
                        else
                        {
                            $uniqID = uniqid("chk");
                            $members[$uniqID] = $verbCheck[$key];
                            $verbCheck[$key] = $uniqID;
                            $verbChecker = $uniqID;
                        }

                        if($currentStep == EventSteps::CHUNKING)
                        {
                            $verbCheckState = StepsStates::FINISHED;
                            $chunkingState = StepsStates::IN_PROGRESS;
                        }
                        elseif($currentStep == EventSteps::READ_CHUNK || $currentStep == EventSteps::BLIND_DRAFT)
                        {
                            $verbCheckState = StepsStates::FINISHED;
                            $chunkingState = StepsStates::FINISHED;
                            $blindDraftState = StepsStates::IN_PROGRESS;
                        }
                        else
                        {
                            $verbCheckState = StepsStates::CHECKED;
                        }
                    }
                    elseif($currentStep == EventSteps::VERBALIZE)
                    {
                        $verbCheckState = StepsStates::WAITING;
                        $consumeState = StepsStates::FINISHED;
                    }
                    elseif($currentStep == EventSteps::CONSUME)
                    {
                        $consumeState = StepsStates::IN_PROGRESS;
                    }
                    elseif($currentStep == EventSteps::MULTI_DRAFT)
                    {
                        $multiDraftState = StepsStates::IN_PROGRESS;
                    }
                }
                else
                {
                    $currentChecker = 0;
                }

                $data["chapters"][$key]["step"] = $currentStep;
                $data["chapters"][$key]["consume"]["state"] = $consumeState;
                $data["chapters"][$key]["multiDraft"]["state"] = $multiDraftState;
                $data["chapters"][$key]["verb"]["state"] = $verbCheckState;
                $data["chapters"][$key]["verb"]["checkerID"] = isset($verbChecker) ? $verbChecker : ($currentChecker > 0 ? $currentChecker : "na");
                $data["chapters"][$key]["chunking"]["state"] = $chunkingState;
                $data["chapters"][$key]["blindDraft"]["state"] = $blindDraftState;

                // Progress checks
                if(!$event[0]->langInput)
                {
                    if($data["chapters"][$key]["consume"]["state"] == StepsStates::FINISHED)
                        $data["chapters"][$key]["progress"] += 11;
                    if($data["chapters"][$key]["verb"]["state"] == StepsStates::CHECKED)
                        $data["chapters"][$key]["progress"] += 6;
                    if($data["chapters"][$key]["verb"]["state"] == StepsStates::FINISHED)
                        $data["chapters"][$key]["progress"] += 11;
                    if($data["chapters"][$key]["chunking"]["state"] == StepsStates::FINISHED)
                        $data["chapters"][$key]["progress"] += 11;
                }

                $overallProgress += $data["chapters"][$key]["progress"];

                $data["chapters"][$key]["chunksData"] = [];
                continue;
            }

            $currentStep = $memberSteps[$chapter["memberID"]]["step"];

            // Total translated chunks are 11% of all chapter progress
            if(!$event[0]->langInput)
                $data["chapters"][$key]["progress"] += sizeof($chapter["chunksData"]) * 11 / sizeof($chapter["chunks"]);
            $data["chapters"][$key]["step"] = $currentChapter == $key ? $currentStep : EventSteps::FINISHED;

            // These steps are finished here by default
            $data["chapters"][$key]["multiDraft"]["state"] = StepsStates::FINISHED;
            $data["chapters"][$key]["consume"]["state"] = StepsStates::FINISHED;
            $data["chapters"][$key]["chunking"]["state"] = StepsStates::FINISHED;

            // Verbalize Check
            if(array_key_exists($key, $verbCheck))
            {
                $data["chapters"][$key]["verb"]["state"] = StepsStates::FINISHED;

                if(!is_numeric($verbCheck[$key]))
                {
                    $uniqID = uniqid("chk");
                    $members[$uniqID] = $verbCheck[$key];
                    $verbCheck[$key] = $uniqID;
                    $data["chapters"][$key]["verb"]["checkerID"] = $verbCheck[$key];
                }
                else
                {
                    $data["chapters"][$key]["verb"]["checkerID"] = $verbCheck[$key];
                    $members[$verbCheck[$key]] = "";
                }
            }

            // Peer Check
            if(array_key_exists($key, $peerCheck))
            {
                // These steps are finished here by default
                $data["chapters"][$key]["blindDraft"]["state"] = StepsStates::FINISHED;
                $data["chapters"][$key]["selfEdit"]["state"] = StepsStates::FINISHED;

                if($key == $currentChapter && $currentStep == EventSteps::PEER_REVIEW)
                    $data["chapters"][$key]["peer"]["state"] = StepsStates::CHECKED;
                else
                    $data["chapters"][$key]["peer"]["state"] = StepsStates::FINISHED;

                $data["chapters"][$key]["peer"]["checkerID"] = $peerCheck[$key];
                $members[$peerCheck[$key]] = "";
            }
            else
            {
                if($key == $currentChapter)
                {
                    if($currentStep == EventSteps::PEER_REVIEW)
                    {
                        // These steps are finished here by default
                        $data["chapters"][$key]["blindDraft"]["state"] = StepsStates::FINISHED;
                        $data["chapters"][$key]["selfEdit"]["state"] = StepsStates::FINISHED;

                        if($currentChecker > 0)
                        {
                            $data["chapters"][$key]["peer"]["state"] = StepsStates::IN_PROGRESS;
                            $data["chapters"][$key]["peer"]["checkerID"] = $currentChecker;
                            $members[$currentChecker] = "";
                        }
                        else
                        {
                            $data["chapters"][$key]["peer"]["state"] = StepsStates::WAITING;
                            $data["chapters"][$key]["peer"]["checkerID"] = "na";
                        }
                    }
                    else
                    {
                        if($currentStep == EventSteps::SELF_CHECK)
                        {
                            $data["chapters"][$key]["blindDraft"]["state"] = StepsStates::FINISHED;
                            $data["chapters"][$key]["selfEdit"]["state"] = StepsStates::IN_PROGRESS;
                        }
                        else
                        {
                            $data["chapters"][$key]["blindDraft"]["state"] = StepsStates::IN_PROGRESS;
                        }
                    }
                }
                else
                {
                    if($event[0]->langInput)
                    {
                        $data["chapters"][$key]["selfEdit"]["state"] = StepsStates::FINISHED;
                    }
                }
            }


            // Keyword Check
            if(array_key_exists($key, $kwCheck))
            {
                if($key == $currentChapter && $currentStep == EventSteps::KEYWORD_CHECK)
                    $data["chapters"][$key]["kwc"]["state"] = StepsStates::CHECKED;
                else
                    $data["chapters"][$key]["kwc"]["state"] = StepsStates::FINISHED;

                $data["chapters"][$key]["kwc"]["checkerID"] = $kwCheck[$key];
                $members[$kwCheck[$key]] = "";
            }
            else
            {
                if($key == $currentChapter)
                {
                    if($currentStep == EventSteps::KEYWORD_CHECK)
                    {
                        if($currentChecker > 0)
                        {
                            $data["chapters"][$key]["kwc"]["state"] = StepsStates::IN_PROGRESS;
                            $data["chapters"][$key]["kwc"]["checkerID"] = $currentChecker;
                            $members[$currentChecker] = "";
                        }
                        else
                        {
                            $data["chapters"][$key]["kwc"]["state"] = StepsStates::WAITING;
                            $data["chapters"][$key]["kwc"]["checkerID"] = "na";
                        }
                    }
                }
            }


            // Content Review (Verse by Verse) Check
            if(array_key_exists($key, $crCheck))
            {
                if($key == $currentChapter)
                {
                    if($currentStep == EventSteps::CONTENT_REVIEW)
                        $data["chapters"][$key]["crc"]["state"] = StepsStates::CHECKED;
                    else
                    {
                        $data["chapters"][$key]["crc"]["state"] = StepsStates::FINISHED;
                        $data["chapters"][$key]["finalReview"]["state"] = StepsStates::IN_PROGRESS;
                    }
                }
                else
                {
                    $data["chapters"][$key]["crc"]["state"] = StepsStates::FINISHED;
                    $data["chapters"][$key]["finalReview"]["state"] = StepsStates::FINISHED;
                }

                $data["chapters"][$key]["crc"]["checkerID"] = $crCheck[$key];
                $data["chapters"][$key]["step"] = $key == $currentChapter
                    ? $currentStep : EventSteps::FINISHED;
                $members[$crCheck[$key]] = "";
            }
            else
            {
                if($key == $currentChapter)
                {
                    if($currentStep == EventSteps::CONTENT_REVIEW)
                    {
                        if($currentChecker > 0)
                        {
                            $data["chapters"][$key]["crc"]["state"] = StepsStates::IN_PROGRESS;
                            $data["chapters"][$key]["crc"]["checkerID"] = $currentChecker;
                            $members[$currentChecker] = "";
                        }
                        else
                        {
                            $data["chapters"][$key]["crc"]["state"] = StepsStates::WAITING;
                            $data["chapters"][$key]["crc"]["checkerID"] = "na";
                        }
                    }
                }
            }

            // Progress checks
            if(!$event[0]->langInput)
            {
                if($data["chapters"][$key]["consume"]["state"] == StepsStates::FINISHED)
                    $data["chapters"][$key]["progress"] += 11;
                if($data["chapters"][$key]["verb"]["state"] == StepsStates::FINISHED)
                    $data["chapters"][$key]["progress"] += 11;
                if($data["chapters"][$key]["chunking"]["state"] == StepsStates::FINISHED)
                    $data["chapters"][$key]["progress"] += 11;
                if($data["chapters"][$key]["selfEdit"]["state"] == StepsStates::FINISHED)
                    $data["chapters"][$key]["progress"] += 11;
                if($data["chapters"][$key]["peer"]["state"] == StepsStates::CHECKED)
                    $data["chapters"][$key]["progress"] += 6;
                if($data["chapters"][$key]["peer"]["state"] == StepsStates::FINISHED)
                    $data["chapters"][$key]["progress"] += 11;
                if($data["chapters"][$key]["kwc"]["state"] == StepsStates::CHECKED)
                    $data["chapters"][$key]["progress"] += 6;
                if($data["chapters"][$key]["kwc"]["state"] == StepsStates::FINISHED)
                    $data["chapters"][$key]["progress"] += 11;
                if($data["chapters"][$key]["crc"]["state"] == StepsStates::CHECKED)
                    $data["chapters"][$key]["progress"] += 6;
                if($data["chapters"][$key]["crc"]["state"] == StepsStates::FINISHED)
                    $data["chapters"][$key]["progress"] += 11;
                if($data["chapters"][$key]["finalReview"]["state"] == StepsStates::FINISHED)
                    $data["chapters"][$key]["progress"] += 12;
            }
            else
            {
                if($data["chapters"][$key]["multiDraft"]["state"] == StepsStates::FINISHED)
                    $data["chapters"][$key]["progress"] += 50;
                if($data["chapters"][$key]["selfEdit"]["state"] == StepsStates::FINISHED)
                    $data["chapters"][$key]["progress"] += 50;
            }

            $overallProgress += $data["chapters"][$key]["progress"];
        }

        $data["overall_progress"] = $overallProgress / sizeof($data["chapters"]);
        $data["members"] = $members;

        if($progressOnly)
        {
            return $data["overall_progress"];
        }
        else {
            return $data;
        }
    }

    public function calculateUlbLevel2EventProgress($event, $progressOnly = false) {
        $data = [];
        $data["overall_progress"] = 0;
        $data["chapters"] = [];
        for($i=1; $i <= $event[0]->chaptersNum; $i++)
        {
            $data["chapters"][$i] = [];
        }

        $chapters = $this->getChapters($event[0]->eventID, null, null, "l2");

        foreach ($chapters as $chapter) {
            $tmp["l2chID"] = $chapter["l2chID"];
            $tmp["l2memberID"] = $chapter["l2memberID"];
            $tmp["l2checked"] = $chapter["l2checked"];
            $tmp["currentChapter"] = $chapter["currentChapter"];
            $tmp["step"] = $chapter["step"];
            $tmp["sndCheck"] = (array)json_decode($chapter["sndCheck"], true);
            $tmp["peer1Check"] = (array)json_decode($chapter["peer1Check"], true);
            $tmp["peer2Check"] = (array)json_decode($chapter["peer2Check"], true);

            $data["chapters"][$chapter["chapter"]] = $tmp;
        }

        $overallProgress = 0;
        $members = [];

        foreach ($data["chapters"] as $key => $chapter) {
            if(empty($chapter) || $chapter["l2memberID"] == 0) continue;

            $snd = !empty($chapter["sndCheck"])
                && array_key_exists($key, $chapter["sndCheck"]);
            $p1 = !empty($chapter["peer1Check"])
                && array_key_exists($key, $chapter["peer1Check"])
                && $chapter["peer1Check"][$key]["memberID"] > 0;
            $p2 = !empty($chapter["peer2Check"])
                && array_key_exists($key, $chapter["peer2Check"])
                && $chapter["peer2Check"][$key]["memberID"] > 0;

            $members[$chapter["l2memberID"]] = "";
            $data["chapters"][$key]["progress"] = 0;

            // Set default values
            $data["chapters"][$key]["consume"]["state"] = StepsStates::NOT_STARTED;
            $data["chapters"][$key]["fstChk"]["state"] = StepsStates::NOT_STARTED;

            $data["chapters"][$key]["sndChk"]["state"] = StepsStates::NOT_STARTED;
            $data["chapters"][$key]["keywordsChk"]["state"] = StepsStates::NOT_STARTED;
            $data["chapters"][$key]["sndChk"]["checkerID"] = 'na';

            $data["chapters"][$key]["peerChk"]["state"] = StepsStates::NOT_STARTED;
            $data["chapters"][$key]["peerChk"]["checkerID1"] = 'na';
            $data["chapters"][$key]["peerChk"]["checkerID2"] = 'na';

            $currentStep = $chapter["step"];

            if($snd)
            {
                // First check
                $data["chapters"][$key]["consume"]["state"] = StepsStates::FINISHED;
                $data["chapters"][$key]["fstChk"]["state"] = StepsStates::FINISHED;

                if($chapter["sndCheck"][$key]["memberID"] > 0)
                {
                    $members[$chapter["sndCheck"][$key]["memberID"]] = "";
                    $data["chapters"][$key]["sndChk"]["checkerID"] = $chapter["sndCheck"][$key]["memberID"];

                    if($chapter["sndCheck"][$key]["done"] == 2)
                    {
                        // Second check
                        $data["chapters"][$key]["sndChk"]["state"] = StepsStates::FINISHED;
                        $data["chapters"][$key]["keywordsChk"]["state"] = StepsStates::FINISHED;

                        // Peer check
                        if($p1 && $p2)
                        {
                            $members[$chapter["peer1Check"][$key]["memberID"]] = "";
                            $members[$chapter["peer2Check"][$key]["memberID"]] = "";

                            $data["chapters"][$key]["peerChk"]["checkerID1"] = $chapter["peer1Check"][$key]["memberID"];
                            $data["chapters"][$key]["peerChk"]["checkerID2"] = $chapter["peer2Check"][$key]["memberID"];

                            if($chapter["peer2Check"][$key]["done"] == 1)
                            {
                                if($chapter["peer1Check"][$key]["done"] == 1)
                                {
                                    $data["chapters"][$key]["peerChk"]["state"] = StepsStates::FINISHED;
                                }
                                else
                                {
                                    $data["chapters"][$key]["peerChk"]["state"] = StepsStates::CHECKED;
                                }
                            }
                            else
                            {
                                $data["chapters"][$key]["peerChk"]["state"] = StepsStates::IN_PROGRESS;
                            }
                        }
                        else if($p1 && !$p2)
                        {
                            $members[$chapter["peer1Check"][$key]["memberID"]] = "";
                            $data["chapters"][$key]["peerChk"]["checkerID1"] = $chapter["peer1Check"][$key]["memberID"];
                            $data["chapters"][$key]["peerChk"]["state"] = StepsStates::WAITING;
                        }
                        else
                        {
                            $data["chapters"][$key]["peerChk"]["state"] = StepsStates::WAITING;
                        }
                    }
                    else if($chapter["sndCheck"][$key]["done"] == 1)
                    {
                        $data["chapters"][$key]["sndChk"]["state"] = StepsStates::FINISHED;
                        $data["chapters"][$key]["keywordsChk"]["state"] = StepsStates::IN_PROGRESS;
                    }
                    else
                    {
                        $data["chapters"][$key]["sndChk"]["state"] = StepsStates::IN_PROGRESS;
                    }
                }
                else
                {
                    $data["chapters"][$key]["sndChk"]["state"] = StepsStates::WAITING;
                }
            }
            else
            {
                if($currentStep == EventCheckSteps::CONSUME)
                {
                    $data["chapters"][$key]["consume"]["state"] = StepsStates::IN_PROGRESS;
                }
                else if($currentStep == EventCheckSteps::FST_CHECK)
                {
                    $data["chapters"][$key]["consume"]["state"] = StepsStates::FINISHED;
                    $data["chapters"][$key]["fstChk"]["state"] = StepsStates::IN_PROGRESS;
                }
            }


            // Progress checks
            if($data["chapters"][$key]["consume"]["state"] == StepsStates::FINISHED)
                $data["chapters"][$key]["progress"] += 20;
            if($data["chapters"][$key]["fstChk"]["state"] == StepsStates::FINISHED)
                $data["chapters"][$key]["progress"] += 20;
            if($data["chapters"][$key]["sndChk"]["state"] == StepsStates::FINISHED)
                $data["chapters"][$key]["progress"] += 20;
            if($data["chapters"][$key]["keywordsChk"]["state"] == StepsStates::FINISHED)
                $data["chapters"][$key]["progress"] += 20;
            if($data["chapters"][$key]["peerChk"]["state"] == StepsStates::CHECKED)
                $data["chapters"][$key]["progress"] += 10;
            if($data["chapters"][$key]["peerChk"]["state"] == StepsStates::FINISHED)
                $data["chapters"][$key]["progress"] += 20;

            $overallProgress += $data["chapters"][$key]["progress"];
        }

        $data["overall_progress"] = $overallProgress / sizeof($data["chapters"]);
        $data["members"] = $members;

        if($progressOnly)
        {
            return $data["overall_progress"];
        }
        else {
            return $data;
        }
    }


    public function calculateAnyLevel3EventProgress($event, $progressOnly = false) {
        $data = [];
        $data["overall_progress"] = 0;
        $data["chapters"] = [];
        for($i=1; $i <= $event[0]->chaptersNum; $i++)
        {
            $data["chapters"][$i] = [];
        }

        $chapters = $this->getChapters($event[0]->eventID, null, null, "l3");

        foreach ($chapters as $chapter) {
            $tmp["l3chID"] = $chapter["l3chID"];
            $tmp["l3memberID"] = $chapter["l3memberID"];
            $tmp["l3checked"] = $chapter["l3checked"];
            $tmp["currentChapter"] = $chapter["currentChapter"];
            $tmp["step"] = $chapter["step"];
            $tmp["peerCheck"] = (array)json_decode($chapter["peerCheck"], true);

            $data["chapters"][$chapter["chapter"]] = $tmp;
        }

        $overallProgress = 0;
        $members = [];

        foreach ($data["chapters"] as $key => $chapter) {
            if(empty($chapter) || $chapter["l3memberID"] == 0) continue;

            $p = !empty($chapter["peerCheck"])
                && array_key_exists($key, $chapter["peerCheck"])
                && $chapter["peerCheck"][$key]["memberID"] > 0;

            $members[$chapter["l3memberID"]] = "";
            $data["chapters"][$key]["progress"] = 0;

            // Set default values
            $data["chapters"][$key]["peerReview"]["state"] = StepsStates::NOT_STARTED;
            $data["chapters"][$key]["peerEdit"]["state"] = StepsStates::NOT_STARTED;
            $data["chapters"][$key]["peerChk"]["checkerID"] = 'na';

            $currentStep = $chapter["step"];

            if($p)
            {
                $data["chapters"][$key]["peerChk"]["checkerID"] = $chapter["peerCheck"][$key]["memberID"];
                $members[$chapter["peerCheck"][$key]["memberID"]] = "";

                if($chapter["peerCheck"][$key]["done"] == 2)
                {
                    $data["chapters"][$key]["peerReview"]["state"] = StepsStates::FINISHED;

                    if($chapter["currentChapter"] == $key)
                    {
                        $data["chapters"][$key]["peerEdit"]["state"] = StepsStates::CHECKED;
                    }
                    else
                    {
                        $data["chapters"][$key]["peerEdit"]["state"] = StepsStates::FINISHED;
                    }
                }
                elseif($chapter["peerCheck"][$key]["done"] == 1)
                {
                    if($currentStep == EventCheckSteps::PEER_REVIEW_L3)
                    {
                        $data["chapters"][$key]["peerReview"]["state"] = StepsStates::CHECKED;
                    }
                    else
                    {
                        $data["chapters"][$key]["peerReview"]["state"] = StepsStates::FINISHED;
                        $data["chapters"][$key]["peerEdit"]["state"] = StepsStates::IN_PROGRESS;
                    }
                }
                else
                {
                    if($currentStep == EventCheckSteps::PEER_REVIEW_L3)
                    {
                        $data["chapters"][$key]["peerReview"]["state"] = StepsStates::IN_PROGRESS;
                    }
                }
            }
            else
            {
                if($currentStep == EventCheckSteps::PEER_REVIEW_L3)
                    $data["chapters"][$key]["peerReview"]["state"] = StepsStates::WAITING;
            }

            // Progress checks
            if($data["chapters"][$key]["peerReview"]["state"] == StepsStates::CHECKED)
                $data["chapters"][$key]["progress"] += 25;
            if($data["chapters"][$key]["peerReview"]["state"] == StepsStates::FINISHED)
                $data["chapters"][$key]["progress"] += 50;
            if($data["chapters"][$key]["peerEdit"]["state"] == StepsStates::CHECKED)
                $data["chapters"][$key]["progress"] += 25;
            if($data["chapters"][$key]["peerEdit"]["state"] == StepsStates::FINISHED)
                $data["chapters"][$key]["progress"] += 50;

            $overallProgress += $data["chapters"][$key]["progress"];
        }

        $data["overall_progress"] = $overallProgress / sizeof($data["chapters"]);
        $data["members"] = $members;

        if($progressOnly)
        {
            return $data["overall_progress"];
        }
        else {
            return $data;
        }
    }

    public function calculateSunLevel1EventProgress($event, $progressOnly = false) {
        $data = [];
        $data["overall_progress"] = 0;
        $data["chapters"] = [];
        for($i=1; $i <= $event[0]->chaptersNum; $i++)
        {
            $data["chapters"][$i] = [];
        }

        $chapters = $this->getChapters($event[0]->eventID, null, null, "l1");

        foreach ($chapters as $chapter) {
            $tmp["trID"] = $chapter["trID"];
            $tmp["memberID"] = $chapter["memberID"];
            $tmp["chunks"] = json_decode($chapter["chunks"], true);
            $tmp["done"] = $chapter["done"];

            $data["chapters"][$chapter["chapter"]] = $tmp;
        }

        $overallProgress = 0;
        $memberSteps = [];
        $members = [];

        $translationModel = new TranslationsModel();
        $chunks = $translationModel->getTranslationByEventID($event[0]->eventID);

        foreach ($chunks as $chunk) {
            if(!array_key_exists($chunk->memberID, $memberSteps))
            {
                $memberSteps[$chunk->memberID]["step"] = $chunk->step;
                $memberSteps[$chunk->memberID]["kwCheck"] = $chunk->kwCheck;
                $memberSteps[$chunk->memberID]["crCheck"] = $chunk->crCheck;
                $memberSteps[$chunk->memberID]["currentChapter"] = $chunk->currentChapter;
                $members[$chunk->memberID] = "";
            }

            if($chunk->chapter == null)
                continue;

            $data["chapters"][$chunk->chapter]["chunksData"][] = $chunk;

            if(!isset($data["chapters"][$chunk->chapter]["lastEdit"]))
            {
                $data["chapters"][$chunk->chapter]["lastEdit"] = $chunk->dateUpdate;
            }
            else
            {
                $prevDate = strtotime($data["chapters"][$chunk->chapter]["lastEdit"]);
                if($prevDate < strtotime($chunk->dateUpdate))
                    $data["chapters"][$chunk->chapter]["lastEdit"] = $chunk->dateUpdate;
            }
        }

        foreach ($data["chapters"] as $key => $chapter) {
            if(empty($chapter)) continue;

            $currentStep = EventSteps::PRAY;
            $consumeState = StepsStates::NOT_STARTED;
            $chunkingState = StepsStates::NOT_STARTED;
            $rearrangeState = StepsStates::NOT_STARTED;
            $symbolDraftState = StepsStates::NOT_STARTED;

            $members[$chapter["memberID"]] = "";
            $data["chapters"][$key]["progress"] = 0;

            $currentChapter = $memberSteps[$chapter["memberID"]]["currentChapter"];
            $kwCheck = (array)json_decode($memberSteps[$chapter["memberID"]]["kwCheck"], true);
            $crCheck = (array)json_decode($memberSteps[$chapter["memberID"]]["crCheck"], true);

            // Set default values
            $data["chapters"][$key]["consume"]["state"] = StepsStates::NOT_STARTED;
            $data["chapters"][$key]["chunking"]["state"] = StepsStates::NOT_STARTED;
            $data["chapters"][$key]["rearrange"]["state"] = StepsStates::NOT_STARTED;
            $data["chapters"][$key]["symbolDraft"]["state"] = StepsStates::NOT_STARTED;
            $data["chapters"][$key]["selfEdit"]["state"] = StepsStates::NOT_STARTED;

            $data["chapters"][$key]["theoChk"]["state"] = StepsStates::NOT_STARTED;
            $data["chapters"][$key]["theoChk"]["checkerID"] = 'na';
            $data["chapters"][$key]["crc"]["state"] = StepsStates::NOT_STARTED;
            $data["chapters"][$key]["crc"]["checkerID"] = 'na';
            $data["chapters"][$key]["finalReview"]["state"] = StepsStates::NOT_STARTED;

            // When no chunks created or translation not started
            if(empty($chapter["chunks"]) || !isset($chapter["chunksData"]))
            {
                if($currentChapter == $key)
                {
                    $currentStep = $memberSteps[$chapter["memberID"]]["step"];

                    if($currentStep == EventSteps::CONSUME)
                    {
                        $consumeState = StepsStates::IN_PROGRESS;
                    }
                    elseif($currentStep == EventSteps::CHUNKING)
                    {
                        $consumeState = StepsStates::FINISHED;
                        $chunkingState = StepsStates::IN_PROGRESS;
                    }
                    elseif($currentStep == EventSteps::REARRANGE)
                    {
                        $consumeState = StepsStates::FINISHED;
                        $chunkingState = StepsStates::FINISHED;
                        $rearrangeState = StepsStates::IN_PROGRESS;
                    }
                    elseif($currentStep == EventSteps::SYMBOL_DRAFT)
                    {
                        $consumeState = StepsStates::FINISHED;
                        $chunkingState = StepsStates::FINISHED;
                        $rearrangeState = StepsStates::FINISHED;
                        $symbolDraftState = StepsStates::IN_PROGRESS;
                    }
                }

                $data["chapters"][$key]["step"] = $currentStep;
                $data["chapters"][$key]["consume"]["state"] = $consumeState;
                $data["chapters"][$key]["chunking"]["state"] = $chunkingState;
                $data["chapters"][$key]["rearrange"]["state"] = $rearrangeState;
                $data["chapters"][$key]["symbolDraft"]["state"] = $symbolDraftState;

                // Progress checks
                if($data["chapters"][$key]["consume"]["state"] == StepsStates::FINISHED)
                    $data["chapters"][$key]["progress"] += 12.5;
                if($data["chapters"][$key]["chunking"]["state"] == StepsStates::FINISHED)
                    $data["chapters"][$key]["progress"] += 12.5;
                if($data["chapters"][$key]["rearrange"]["state"] == StepsStates::FINISHED)
                    $data["chapters"][$key]["progress"] += 12.5;
                if($data["chapters"][$key]["symbolDraft"]["state"] == StepsStates::FINISHED)
                    $data["chapters"][$key]["progress"] += 12.5;

                $overallProgress += $data["chapters"][$key]["progress"];

                $data["chapters"][$key]["chunksData"] = [];
                continue;
            }

            $currentStep = $memberSteps[$chapter["memberID"]]["step"];

            $kw = !empty($kwCheck)
                && array_key_exists($key, $kwCheck);
            $cr = !empty($crCheck)
                && array_key_exists($key, $crCheck)
                && $crCheck[$key]["memberID"] > 0;

            if($kw)
            {
                // Theo check
                $data["chapters"][$key]["consume"]["state"] = StepsStates::FINISHED;
                $data["chapters"][$key]["chunking"]["state"] = StepsStates::FINISHED;
                $data["chapters"][$key]["rearrange"]["state"] = StepsStates::FINISHED;
                $data["chapters"][$key]["symbolDraft"]["state"] = StepsStates::FINISHED;
                $data["chapters"][$key]["selfEdit"]["state"] = StepsStates::FINISHED;

                if($kwCheck[$key]["memberID"] > 0)
                {
                    $members[$kwCheck[$key]["memberID"]] = "";
                    $data["chapters"][$key]["theoChk"]["checkerID"] = $kwCheck[$key]["memberID"];

                    if($kwCheck[$key]["done"] == 1)
                    {
                        // Verse-by-verse check
                        $data["chapters"][$key]["theoChk"]["state"] = StepsStates::FINISHED;

                        if($cr)
                        {
                            $members[$crCheck[$key]["memberID"]] = "";
                            $data["chapters"][$key]["crc"]["checkerID"] = $crCheck[$key]["memberID"];

                            if($crCheck[$key]["done"] == 2)
                            {
                                $data["chapters"][$key]["crc"]["state"] = StepsStates::FINISHED;
                                $data["chapters"][$key]["finalReview"]["state"] = StepsStates::FINISHED;
                            }
                            elseif($crCheck[$key]["done"] == 1)
                            {
                                $data["chapters"][$key]["crc"]["state"] = StepsStates::FINISHED;
                                $data["chapters"][$key]["finalReview"]["state"] = StepsStates::IN_PROGRESS;
                            }
                            else
                            {
                                $data["chapters"][$key]["crc"]["state"] = StepsStates::IN_PROGRESS;
                            }
                        }
                        else
                        {
                            $data["chapters"][$key]["crc"]["state"] = StepsStates::WAITING;
                        }
                    }
                    else
                    {
                        $data["chapters"][$key]["theoChk"]["state"] = StepsStates::IN_PROGRESS;
                    }
                }
                else
                {
                    $data["chapters"][$key]["theoChk"]["state"] = StepsStates::WAITING;
                }
            }
            else
            {
                if($currentStep == EventSteps::CONSUME)
                {
                    $data["chapters"][$key]["consume"]["state"] = StepsStates::IN_PROGRESS;
                }
                elseif($currentStep == EventSteps::CHUNKING)
                {
                    $data["chapters"][$key]["consume"]["state"] = StepsStates::FINISHED;
                    $data["chapters"][$key]["chunking"]["state"] = StepsStates::IN_PROGRESS;
                }
                elseif($currentStep == EventSteps::REARRANGE)
                {
                    $data["chapters"][$key]["consume"]["state"] = StepsStates::FINISHED;
                    $data["chapters"][$key]["chunking"]["state"] = StepsStates::FINISHED;
                    $data["chapters"][$key]["rearrange"]["state"] = StepsStates::IN_PROGRESS;
                }
                elseif($currentStep == EventSteps::SYMBOL_DRAFT)
                {
                    $data["chapters"][$key]["consume"]["state"] = StepsStates::FINISHED;
                    $data["chapters"][$key]["chunking"]["state"] = StepsStates::FINISHED;
                    $data["chapters"][$key]["rearrange"]["state"] = StepsStates::FINISHED;
                    $data["chapters"][$key]["symbolDraft"]["state"] = StepsStates::IN_PROGRESS;
                }
                elseif($currentStep == EventSteps::SELF_CHECK)
                {
                    $data["chapters"][$key]["consume"]["state"] = StepsStates::FINISHED;
                    $data["chapters"][$key]["chunking"]["state"] = StepsStates::FINISHED;
                    $data["chapters"][$key]["rearrange"]["state"] = StepsStates::FINISHED;
                    $data["chapters"][$key]["symbolDraft"]["state"] = StepsStates::FINISHED;
                    $data["chapters"][$key]["selfEdit"]["state"] = StepsStates::IN_PROGRESS;
                }
            }


            // Progress checks
            if($data["chapters"][$key]["consume"]["state"] == StepsStates::FINISHED)
                $data["chapters"][$key]["progress"] += 12.5;
            if($data["chapters"][$key]["chunking"]["state"] == StepsStates::FINISHED)
                $data["chapters"][$key]["progress"] += 12.5;
            if($data["chapters"][$key]["rearrange"]["state"] == StepsStates::FINISHED)
                $data["chapters"][$key]["progress"] += 12.5;
            if($data["chapters"][$key]["symbolDraft"]["state"] == StepsStates::FINISHED)
                $data["chapters"][$key]["progress"] += 12.5;
            if($data["chapters"][$key]["selfEdit"]["state"] == StepsStates::FINISHED)
                $data["chapters"][$key]["progress"] += 12.5;
            if($data["chapters"][$key]["theoChk"]["state"] == StepsStates::FINISHED)
                $data["chapters"][$key]["progress"] += 12.5;
            if($data["chapters"][$key]["crc"]["state"] == StepsStates::FINISHED)
                $data["chapters"][$key]["progress"] += 12.5;
            if($data["chapters"][$key]["finalReview"]["state"] == StepsStates::FINISHED)
                $data["chapters"][$key]["progress"] += 12.5;

            $overallProgress += $data["chapters"][$key]["progress"];
        }

        $data["overall_progress"] = $overallProgress / sizeof($data["chapters"]);
        $data["members"] = $members;

        if($progressOnly)
        {
            return $data["overall_progress"];
        }
        else {
            return $data;
        }
    }

    public function calculateOdbSunLevel1EventProgress($event, $progressOnly = false) {
        $data = [];
        $data["overall_progress"] = 0;
        $data["chapters"] = [];
        for($i=1; $i <= $event[0]->chaptersNum; $i++)
        {
            $data["chapters"][$i] = [];
        }

        $chapters = $this->getChapters($event[0]->eventID, null, null, "l1");

        foreach ($chapters as $chapter) {
            $tmp["trID"] = $chapter["trID"];
            $tmp["memberID"] = $chapter["memberID"];
            $tmp["chunks"] = json_decode($chapter["chunks"], true);
            $tmp["done"] = $chapter["done"];

            $data["chapters"][$chapter["chapter"]] = $tmp;
        }

        $overallProgress = 0;
        $memberSteps = [];
        $members = [];

        $translationModel = new TranslationsModel();
        $chunks = $translationModel->getTranslationByEventID($event[0]->eventID);

        foreach ($chunks as $chunk) {
            if(!array_key_exists($chunk->memberID, $memberSteps))
            {
                $memberSteps[$chunk->memberID]["step"] = $chunk->step;
                $memberSteps[$chunk->memberID]["kwCheck"] = $chunk->kwCheck;
                $memberSteps[$chunk->memberID]["crCheck"] = $chunk->crCheck;
                $memberSteps[$chunk->memberID]["currentChapter"] = $chunk->currentChapter;
                $members[$chunk->memberID] = "";
            }

            if($chunk->chapter == null)
                continue;

            $data["chapters"][$chunk->chapter]["chunksData"][] = $chunk;

            if(!isset($data["chapters"][$chunk->chapter]["lastEdit"]))
            {
                $data["chapters"][$chunk->chapter]["lastEdit"] = $chunk->dateUpdate;
            }
            else
            {
                $prevDate = strtotime($data["chapters"][$chunk->chapter]["lastEdit"]);
                if($prevDate < strtotime($chunk->dateUpdate))
                    $data["chapters"][$chunk->chapter]["lastEdit"] = $chunk->dateUpdate;
            }
        }

        foreach ($data["chapters"] as $key => $chapter) {
            if(empty($chapter)) continue;

            $currentStep = EventSteps::PRAY;
            $consumeState = StepsStates::NOT_STARTED;
            $rearrangeState = StepsStates::NOT_STARTED;
            $symbolDraftState = StepsStates::NOT_STARTED;

            $members[$chapter["memberID"]] = "";
            $data["chapters"][$key]["progress"] = 0;

            $currentChapter = $memberSteps[$chapter["memberID"]]["currentChapter"];
            $kwCheck = (array)json_decode($memberSteps[$chapter["memberID"]]["kwCheck"], true);
            $crCheck = (array)json_decode($memberSteps[$chapter["memberID"]]["crCheck"], true);

            // Set default values
            $data["chapters"][$key]["consume"]["state"] = StepsStates::NOT_STARTED;
            $data["chapters"][$key]["rearrange"]["state"] = StepsStates::NOT_STARTED;
            $data["chapters"][$key]["symbolDraft"]["state"] = StepsStates::NOT_STARTED;
            $data["chapters"][$key]["selfEdit"]["state"] = StepsStates::NOT_STARTED;

            $data["chapters"][$key]["theoChk"]["state"] = StepsStates::NOT_STARTED;
            $data["chapters"][$key]["theoChk"]["checkerID"] = 'na';
            $data["chapters"][$key]["crc"]["state"] = StepsStates::NOT_STARTED;
            $data["chapters"][$key]["crc"]["checkerID"] = 'na';

            // When no chunks created or translation not started
            if(empty($chapter["chunks"]) || !isset($chapter["chunksData"]))
            {
                if($currentChapter == $key)
                {
                    $currentStep = $memberSteps[$chapter["memberID"]]["step"];

                    if($currentStep == EventSteps::CONSUME)
                    {
                        $consumeState = StepsStates::IN_PROGRESS;
                    }
                    elseif($currentStep == EventSteps::REARRANGE)
                    {
                        $consumeState = StepsStates::FINISHED;
                        $rearrangeState = StepsStates::IN_PROGRESS;
                    }
                    elseif($currentStep == EventSteps::SYMBOL_DRAFT)
                    {
                        $consumeState = StepsStates::FINISHED;
                        $rearrangeState = StepsStates::FINISHED;
                        $symbolDraftState = StepsStates::IN_PROGRESS;
                    }
                }

                $data["chapters"][$key]["step"] = $currentStep;
                $data["chapters"][$key]["consume"]["state"] = $consumeState;
                $data["chapters"][$key]["rearrange"]["state"] = $rearrangeState;
                $data["chapters"][$key]["symbolDraft"]["state"] = $symbolDraftState;

                // Progress checks
                if($data["chapters"][$key]["consume"]["state"] == StepsStates::FINISHED)
                    $data["chapters"][$key]["progress"] += 16.6;
                if($data["chapters"][$key]["rearrange"]["state"] == StepsStates::FINISHED)
                    $data["chapters"][$key]["progress"] += 16.6;
                if($data["chapters"][$key]["symbolDraft"]["state"] == StepsStates::FINISHED)
                    $data["chapters"][$key]["progress"] += 16.6;

                $overallProgress += $data["chapters"][$key]["progress"];

                $data["chapters"][$key]["chunksData"] = [];
                continue;
            }

            $currentStep = $memberSteps[$chapter["memberID"]]["step"];

            $kw = !empty($kwCheck)
                && array_key_exists($key, $kwCheck);
            $cr = !empty($crCheck)
                && array_key_exists($key, $crCheck)
                && $crCheck[$key]["memberID"] > 0;

            if($kw)
            {
                // Theo check
                $data["chapters"][$key]["consume"]["state"] = StepsStates::FINISHED;
                $data["chapters"][$key]["rearrange"]["state"] = StepsStates::FINISHED;
                $data["chapters"][$key]["symbolDraft"]["state"] = StepsStates::FINISHED;
                $data["chapters"][$key]["selfEdit"]["state"] = StepsStates::FINISHED;

                if($kwCheck[$key]["memberID"] > 0)
                {
                    $members[$kwCheck[$key]["memberID"]] = "";
                    $data["chapters"][$key]["theoChk"]["checkerID"] = $kwCheck[$key]["memberID"];

                    if($kwCheck[$key]["done"] == 1)
                    {
                        // Verse-by-verse check
                        $data["chapters"][$key]["theoChk"]["state"] = StepsStates::FINISHED;

                        if($cr)
                        {
                            $members[$crCheck[$key]["memberID"]] = "";
                            $data["chapters"][$key]["crc"]["checkerID"] = $crCheck[$key]["memberID"];

                            if($crCheck[$key]["done"] == 1)
                            {
                                $data["chapters"][$key]["crc"]["state"] = StepsStates::FINISHED;
                            }
                            else
                            {
                                $data["chapters"][$key]["crc"]["state"] = StepsStates::IN_PROGRESS;
                            }
                        }
                        else
                        {
                            $data["chapters"][$key]["crc"]["state"] = StepsStates::WAITING;
                        }
                    }
                    else
                    {
                        $data["chapters"][$key]["theoChk"]["state"] = StepsStates::IN_PROGRESS;
                    }
                }
                else
                {
                    $data["chapters"][$key]["theoChk"]["state"] = StepsStates::WAITING;
                }
            }
            else
            {
                if($currentStep == EventSteps::CONSUME)
                {
                    $data["chapters"][$key]["consume"]["state"] = StepsStates::IN_PROGRESS;
                }
                elseif($currentStep == EventSteps::REARRANGE)
                {
                    $data["chapters"][$key]["consume"]["state"] = StepsStates::FINISHED;
                    $data["chapters"][$key]["rearrange"]["state"] = StepsStates::IN_PROGRESS;
                }
                elseif($currentStep == EventSteps::SYMBOL_DRAFT)
                {
                    $data["chapters"][$key]["consume"]["state"] = StepsStates::FINISHED;
                    $data["chapters"][$key]["rearrange"]["state"] = StepsStates::FINISHED;
                    $data["chapters"][$key]["symbolDraft"]["state"] = StepsStates::IN_PROGRESS;
                }
                elseif($currentStep == EventSteps::SELF_CHECK)
                {
                    $data["chapters"][$key]["consume"]["state"] = StepsStates::FINISHED;
                    $data["chapters"][$key]["rearrange"]["state"] = StepsStates::FINISHED;
                    $data["chapters"][$key]["symbolDraft"]["state"] = StepsStates::FINISHED;
                    $data["chapters"][$key]["selfEdit"]["state"] = StepsStates::IN_PROGRESS;
                }
            }


            // Progress checks
            if($data["chapters"][$key]["consume"]["state"] == StepsStates::FINISHED)
                $data["chapters"][$key]["progress"] += 16.6;
            if($data["chapters"][$key]["rearrange"]["state"] == StepsStates::FINISHED)
                $data["chapters"][$key]["progress"] += 16.6;
            if($data["chapters"][$key]["symbolDraft"]["state"] == StepsStates::FINISHED)
                $data["chapters"][$key]["progress"] += 16.6;
            if($data["chapters"][$key]["selfEdit"]["state"] == StepsStates::FINISHED)
                $data["chapters"][$key]["progress"] += 16.6;
            if($data["chapters"][$key]["theoChk"]["state"] == StepsStates::FINISHED)
                $data["chapters"][$key]["progress"] += 16.6;
            if($data["chapters"][$key]["crc"]["state"] == StepsStates::FINISHED)
                $data["chapters"][$key]["progress"] += 17;

            $overallProgress += $data["chapters"][$key]["progress"];
        }

        $data["overall_progress"] = $overallProgress / sizeof($data["chapters"]);
        $data["members"] = $members;

        if($progressOnly)
        {
            return $data["overall_progress"];
        }
        else {
            return $data;
        }
    }

}