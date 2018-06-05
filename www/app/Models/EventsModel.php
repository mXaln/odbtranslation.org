<?php
/**
 * Created by PhpStorm.
 * User: Maxim
 * Date: 05 Feb 2016
 * Time: 19:57
 */

namespace App\Models;

use DB;
use Helpers\Constants\EventCheckSteps;
use PDO;
use File;
use Cache;
use ZipArchive;
use \Helpers\Url;
use \Helpers\Data;
use \Database\Model;
use \Helpers\Session;
use \Helpers\Parsedown;
use \Helpers\UsfmParser;
use \Helpers\Constants\EventSteps;
use \Helpers\Constants\BookSources;
use \Helpers\Constants\EventStates;
use \Helpers\Constants\EventMembers;
use \Helpers\Spyc;

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
     * @return array
     */
    public function getEventsByProject($projectID)
    {
        $sql = "SELECT ".PREFIX."abbr.*, ".PREFIX."events.*, COUNT(DISTINCT ".PREFIX."translators.memberID) AS translators, ".
            "COUNT(DISTINCT ".PREFIX."checkers_l2.memberID) AS checkers_l2, COUNT(DISTINCT ".PREFIX."checkers_l3.memberID) AS checkers_l3 ".
            "FROM ".PREFIX."abbr ".
            "LEFT JOIN ".PREFIX."events ON ".PREFIX."abbr.code=".PREFIX."events.bookCode AND (".PREFIX."events.projectID=:projectID OR ".PREFIX."events.projectID is NULL) ".
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
        $sql = "SELECT ".PREFIX."translators.memberID AS translator, "
            ."checkers.checkerID AS checker, evnt.admins, evnt.admins_l2, ".PREFIX."translators.step, "
            .PREFIX."translators.checkerID, ".PREFIX."translators.peerCheck, ".PREFIX."translators.currentChapter, "
            .PREFIX."checkers_l2.memberID AS checker_l2, ".PREFIX."checkers_l3.memberID AS checker_l3, "
            .PREFIX."projects.projectID, ".PREFIX."projects.bookProject, ".PREFIX."projects.sourceLangID, "
            .PREFIX."projects.gwLang, ".PREFIX."projects.targetLang, ".PREFIX."projects.gwProjectID "
            .($getInfo ?
                ", evnt.eventID, evnt.state, evnt.bookCode, "
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
     * @param int $memberID
     * @param int $memberType
     * @param int null $eventID
     * @param bool true $includeFinished
     * @return array
     */
    public function getMemberEvents($memberID, $memberType, $eventID = null, $includeFinished = true, $includeNone = true, $tnChk = false)
    {
        $events = array();
        $sql = "SELECT ".($memberType == EventMembers::TRANSLATOR 
            ? PREFIX."translators.trID, "
                .PREFIX."translators.memberID AS myMemberID, ".PREFIX."translators.step, "
                .PREFIX."translators.checkerID, ".PREFIX."translators.checkDone, "
                .PREFIX."translators.currentChunk, ".PREFIX."translators.currentChapter, "
                .PREFIX."translators.translateDone, "
                .PREFIX."translators.verbCheck, ".PREFIX."translators.peerCheck, "
                .PREFIX."translators.kwCheck, ".PREFIX."translators.crCheck, "
                .PREFIX."translators.otherCheck, "
                .PREFIX."translators.isChecker, "
                ."mems.userName AS checkerName, mems.firstName AS checkerFName, "
                ."mems.lastName AS checkerLName, chapters.chunks, "
                ."(SELECT COUNT(*) FROM ".PREFIX."translators AS all_trs WHERE all_trs.eventID = ".PREFIX."translators.eventID ) AS currTrs, " 
            : "").($memberType == EventMembers::L2_CHECKER 
            ? PREFIX."checkers_l2.l2chID, "
                .PREFIX."checkers_l2.memberID, ".PREFIX."checkers_l2.step, "
                .PREFIX."checkers_l2.currentChapter, ".PREFIX."checkers_l2.sndCheck, "
                .PREFIX."checkers_l2.peer1Check, ".PREFIX."checkers_l2.peer2Check, "
                ."chapters.chunks, "
                ."(SELECT COUNT(*) FROM ".PREFIX."checkers_l2 AS all_chkrs WHERE all_chkrs.eventID = ".PREFIX."checkers_l2.eventID ) AS currChkrs, " 
            : "")
                ."evnt.eventID, evnt.state, evnt.bookCode, evnt.dateFrom, "
                ."evnt.dateTo, evnt.admins, "
                .PREFIX."projects.projectID, ".PREFIX."projects.bookProject, "
                .PREFIX."projects.sourceLangID, ".PREFIX."projects.gwLang, "
                .PREFIX."projects.targetLang, "
                .PREFIX."projects.sourceBible, t_lang.langName as tLang, "
                ."t_lang.direction as tLangDir, ".PREFIX."projects.notesLangID, notes_lang.direction as notesLangDir, "
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
            "LEFT JOIN ".PREFIX."events AS evnt ON ".$mainTable.".eventID = evnt.eventID ".
            "LEFT JOIN ".PREFIX."projects ON evnt.projectID = ".PREFIX."projects.projectID ".
            "LEFT JOIN ".PREFIX."languages AS t_lang ON ".PREFIX."projects.targetLang = t_lang.langID ".
            "LEFT JOIN ".PREFIX."languages AS s_lang ON ".PREFIX."projects.sourceLangID = s_lang.langID ".
            "LEFT JOIN ".PREFIX."languages AS notes_lang ON ".PREFIX."projects.notesLangID = notes_lang.langID ".
            "LEFT JOIN ".PREFIX."abbr ON evnt.bookCode = ".PREFIX."abbr.code ".
            "WHERE ".$mainTable.".memberID = :memberID ".
            (!is_null($eventID) ? " AND ".$mainTable.".eventID=:eventID " : " ").
            ($memberType == EventMembers::TRANSLATOR && !$includeNone ? "AND ".PREFIX."translators.step != 'none' " : "").
            ($memberType == EventMembers::TRANSLATOR && !$includeFinished ? " AND ".PREFIX."translators.step != 'finished' " : " ").
            "ORDER BY tLang, ".PREFIX."projects.sourceBible, ".PREFIX."abbr.abbrID";

        $prepare = array();
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
                .PREFIX."projects.sourceBible, ".PREFIX."projects.gwLang, "
                .PREFIX."projects.targetLang, ".PREFIX."projects.notesLangID, ".
                "t_lang.direction as tLangDir, s_lang.direction as sLangDir, "
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
     * Get Notes checker event/s
     * @param $memberID Notes Checker member ID
     * @param null $eventID event ID
     * @param null $chkMemberID Notes translator member ID
     * @return array
     */
    public function getMemberEventsForNotes($memberID, $eventID = null, $chkMemberID = null, $chapter = null)
    {
        $prepare = [];
        if($eventID)
            $prepare[":eventID"] = $eventID;
        if($chkMemberID)
            $prepare[":chkMemberID"] = $chkMemberID;

        $sql = "SELECT trs.*, ".PREFIX."members.userName, ".PREFIX."members.firstName, "
            .PREFIX."members.lastName, evnt.bookCode, evnt.admins, "
            ."evnt.dateFrom, evnt.dateTo, evnt.state, "
            ."t_lang.langName AS tLang, s_lang.langName AS sLang, "
            .PREFIX."abbr.name AS name, ".PREFIX."abbr.abbrID, "
            .PREFIX."projects.sourceLangID, ".PREFIX."projects.bookProject, "
            .PREFIX."projects.sourceBible, ".PREFIX."projects.gwLang, "
            .PREFIX."projects.targetLang, ".PREFIX."projects.notesLangID, notes_lang.direction as notesLangDir, ".
            "t_lang.direction as tLangDir, s_lang.direction as sLangDir, "
            .PREFIX."abbr.chaptersNum, ".PREFIX."projects.projectID ".
            "FROM ".PREFIX."translators AS trs ".
            "LEFT JOIN ".PREFIX."members ON trs.memberID = ".PREFIX."members.memberID ".
            "LEFT JOIN ".PREFIX."events AS evnt ON evnt.eventID = trs.eventID ".
            "LEFT JOIN ".PREFIX."projects ON ".PREFIX."projects.projectID = evnt.projectID ".
            "LEFT JOIN ".PREFIX."languages AS t_lang ON ".PREFIX."projects.targetLang = t_lang.langID ".
            "LEFT JOIN ".PREFIX."languages AS s_lang ON ".PREFIX."projects.sourceLangID = s_lang.langID ".
            "LEFT JOIN ".PREFIX."languages AS notes_lang ON ".PREFIX."projects.notesLangID = notes_lang.langID ".
            "LEFT JOIN ".PREFIX."abbr ON evnt.bookCode = ".PREFIX."abbr.code ".
            "WHERE ".PREFIX."projects.bookProject = 'tn' ".
            ($eventID ? "AND trs.eventID = :eventID " : " ").
            ($chkMemberID ? "AND trs.memberID = :chkMemberID " : " ").
            "ORDER BY tLang, ".PREFIX."abbr.abbrID";

        $events = $this->db->select($sql, $prepare);
        $filtered = [];

        foreach($events as $event)
        {
            // Checker event
            $otherCheck = (array)json_decode($event->otherCheck, true);
            $peerCheck = (array)json_decode($event->peerCheck, true);
            foreach ($otherCheck as $chap => $data) {
                if(!isset($chapter) || $chapter == $chap)
                {
                    if($data["memberID"] == $memberID && $data["done"] != 6)
                    {
                        $ev = clone $event;

                        $checkerFName = null;
                        $checkerLName = null;
                        $checkerID = 0;
                        $ev->step = EventSteps::PRAY;
                        $ev->checkDone = false;

                        if(isset($peerCheck[$chap]) && $peerCheck[$chap]["memberID"] != 0)
                        {
                            $memberModel = new MembersModel();
                            $member = $memberModel->getMember([
                                "firstName",
                                "lastName"
                            ], ["memberID", $peerCheck[$chap]["memberID"]]);
                            if(!empty($member))
                            {
                                $checkerFName = $member[0]->firstName;
                                $checkerLName = $member[0]->lastName;
                                $checkerID = $peerCheck[$chap]["memberID"];
                            }

                            $ev->checkDone = $peerCheck[$chap]["done"] > 0;
                        }

                        switch ($data["done"])
                        {
                            case 1:
                                $ev->step = EventSteps::CONSUME;
                                break;
                            case 2:
                                $ev->step = EventSteps::HIGHLIGHT;
                                break;
                            case 3:
                                $ev->step = EventSteps::SELF_CHECK;
                                break;
                            case 4:
                                $ev->step = EventSteps::KEYWORD_CHECK;
                                break;
                            case 5:
                                $ev->step = EventSteps::PEER_REVIEW;
                                break;
                        }

                        $ev->currentChapter = $chap;
                        $ev->peer = 1;
                        $ev->myMemberID = 0;
                        $ev->myChkMemberID = $memberID;
                        $ev->checkerFName = $checkerFName;
                        $ev->checkerLName = $checkerLName;
                        $ev->checkerID = $checkerID;
                        $ev->isContinue = true; // Means not owner of chapter
                        $ev->isCheckerPage = true;
                        $filtered[] = $ev;
                    }
                }
            }

            // Peer check event
            foreach ($peerCheck as $chap => $data) {
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
                        ], ["memberID", $otherCheck[$chap]["memberID"]]);
                        if(!empty($member))
                        {
                            $checkerFName = $member[0]->firstName;
                            $checkerLName = $member[0]->lastName;
                            $checkerID = $otherCheck[$chap]["memberID"];
                        }

                        $ev->step = EventSteps::PEER_REVIEW;
                        $ev->currentChapter = $chap;
                        $ev->peer = 2;
                        $ev->myMemberID = $memberID;
                        $ev->myChkMemberID = $memberID;
                        $ev->checkerFName = $checkerFName;
                        $ev->checkerLName = $checkerLName;
                        $ev->checkerID = $checkerID;
                        $ev->isContinue = true;
                        $ev->isCheckerPage = true;
                        $filtered[] = $ev;
                    }
                }
            }
        }

        return $filtered;
    }


    /**
     * Get L2 checker event/s
     * @param $memberID 2nd Checker member ID
     * @param null $eventID event ID
     * @param null $chkMemberID 1st Checker member ID
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
            .PREFIX."projects.sourceBible, ".PREFIX."projects.gwLang, "
            .PREFIX."projects.targetLang, ".PREFIX."projects.notesLangID, ".
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
            .PREFIX."projects.targetLang, ".PREFIX."projects.notesLangID, ".
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
                        $ev->memberID = $ev->memberID;
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
                    if($data["memberID"] == $checkerID && $data["done"] != 2)
                    {
                        $ev = clone $event;

                        $ev->step = $data["done"] == 0 ?
                            EventSteps::CONTENT_REVIEW :
                            EventSteps::FINAL_REVIEW;
                        $ev->currentChapter = $chap;
                        $ev->memberID = $ev->memberID;
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
            .PREFIX."projects.sourceBible, ".PREFIX."projects.gwLang, "
            .PREFIX."projects.targetLang, ".PREFIX."projects.notesLangID, ".
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
                        $ev->memberID = $ev->memberID;
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
            "gwproj.admins AS superadmins ".
            "FROM ".PREFIX."events AS evnt ".
            "LEFT JOIN ".PREFIX."projects AS proj ON proj.projectID = evnt.projectID ".
            "LEFT JOIN ".PREFIX."gateway_projects AS gwproj ON proj.gwProjectID = gwproj.gwProjectID ".
            "LEFT JOIN ".PREFIX."abbr AS abbr ON evnt.bookCode = abbr.code ".
            "LEFT JOIN ".PREFIX."languages AS tLang ON proj.targetLang = tLang.langID ".
            "LEFT JOIN ".PREFIX."languages AS sLang ON proj.sourceLangID = sLang.langID ".
            (!$isSuperAdmin ? "WHERE (evnt.admins LIKE :memberID OR evnt.admins_l2 LIKE :memberID) " : "").
            ($isSuperAdmin && $eventID ? "WHERE " : (!$isSuperAdmin && $eventID ? "AND " : "")).
            ($eventID ? "evnt.eventID = :eventID " : "").
            "ORDER BY evnt.state, tLang.langName, proj.sourceBible, abbr.abbrID";

        $prepare = [];
        if(!$isSuperAdmin) $prepare[":memberID"] = '%\"'.$memberID.'"%';
        if($eventID) $prepare[":eventID"] = $eventID;

        return $this->db->select($sql, $prepare);
    }

    public function getNewEvents($langs, $memberID = null)
    {
        $arr = array();

        if(is_array($langs) && !empty($langs)) {
            $in = $this->db->quoteArray($langs);

            $sql = "SELECT evnt.*, proj.bookProject, proj.sourceLangID, tLang.langName AS tLang, sLang.langName AS sLang, abbr.abbrID, abbr.name, ".
                "(SELECT COUNT(*) FROM ".PREFIX."translators AS all_trs WHERE all_trs.eventID = evnt.eventID) AS trsCnt, ".
                "(SELECT COUNT(*) FROM ".PREFIX."checkers_l2 AS all_chl2 WHERE all_chl2.eventID = evnt.eventID) AS chl2Cnt, ".
                "(SELECT COUNT(*) FROM ".PREFIX."checkers_l3 AS all_chl3 WHERE all_chl3.eventID = evnt.eventID) AS chl3Cnt ".
                "FROM ".PREFIX."events AS evnt ".
                "LEFT JOIN ".PREFIX."projects AS proj ON proj.projectID = evnt.projectID ".
                "LEFT JOIN ".PREFIX."abbr AS abbr ON evnt.bookCode = abbr.code ".
                "LEFT JOIN ".PREFIX."languages AS tLang ON proj.targetLang = tLang.langID ".
                "LEFT JOIN ".PREFIX."languages AS sLang ON proj.sourceLangID = sLang.langID ".
                ($memberID ?
                    "LEFT JOIN ".PREFIX."translators AS trs ON (trs.eventID = evnt.eventID AND trs.memberID = :memberID) ".
                    "LEFT JOIN ".PREFIX."checkers_l2 AS chl2 ON (chl2.eventID = evnt.eventID AND chl2.memberID = :memberID) ".
                    "LEFT JOIN ".PREFIX."checkers_l3 AS chl3 ON (chl3.eventID = evnt.eventID AND chl3.memberID = :memberID) " : "").
                "WHERE (evnt.state = :state OR evnt.state = :state1 OR evnt.state = :state2 OR evnt.state = :state3) ".
                    "AND (proj.gwLang IN ($in) OR proj.targetLang IN ($in)) ".
                    //"AND DATE(evnt.dateTo) > NOW() ".
                ($memberID ?
                    "AND (trs.memberID IS NULL AND chl2.memberID IS NULL AND chl3.memberID IS NULL) " : "").
            "ORDER BY evnt.state, abbr.abbrID";

            $prepare = array(
                ":state" => EventStates::STARTED,
                ":state1" => EventStates::TRANSLATING,
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


    public function getEventWithContributors($eventID)
    {
        return $this->db->table("events")
            ->select([
                "events.eventID","events.admins",
                "translators.verbCheck","translators.peerCheck",
                "translators.kwCheck","translators.crCheck",
                "translators.otherCheck", "abbr.chaptersNum",
                "projects.bookProject"
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
                PREFIX."events.bookCode, ".PREFIX."projects.bookProject, ".
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
     * Get notifications for assigned Notes events
     * @return array
     */
    public function getNotificationsNotes()
    {
        $sql = "SELECT trs.*, ".
            PREFIX."members.userName, ".PREFIX."members.firstName, ".PREFIX."members.lastName, ".
            PREFIX."events.bookCode, ".PREFIX."projects.bookProject, mytrs.step as myStep, ".
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
                "AND trs.otherCheck != '' AND trs.memberID != :memberID ".
                "AND mytrs.isChecker = 1 ".
                "AND ".PREFIX."projects.bookProject = 'tn'";

        $prepare = [
            ":memberID" => Session::get("memberID"),
            ":adminID" => '%\"'.Session::get("memberID").'"%'
        ];

        $notesNotifications = $this->db->select($sql, $prepare);
        $notifs = [];

        foreach ($notesNotifications as $notification)
        {
            $otherCheck = (array)json_decode($notification->otherCheck, true);
            $peerCheck = (array)json_decode($notification->peerCheck, true);

            foreach ($otherCheck as $chapter => $data) {
                // Exclude taken chapters
                if($data["memberID"] > 0) continue;

                $note = clone $notification;
                $note->currentChapter = $chapter;
                $note->step = "notes";
                $note->manageMode = "tn";
                $note->peer = 1;
                $notifs[] = $note;
            }

            foreach ($peerCheck as $chapter => $data) {
                // Exclude taken chapters
                if($data["memberID"] > 0) continue;

                // Exclude member that is already in otherCheck
                if($otherCheck[$chapter]["memberID"] == Session::get("memberID")) continue;

                $note = clone $notification;

                $memberModel = new MembersModel();
                $member = $memberModel->getMember([
                    "firstName",
                    "lastName"
                ], ["memberID", $otherCheck[$chapter]["memberID"]]);
                if(!empty($member))
                {
                    $note->firstName = $member[0]->firstName;
                    $note->lastName = $member[0]->lastName;
                }

                $note->currentChapter = $chapter;
                $note->step = EventSteps::PEER_REVIEW;
                $note->manageMode = "tn";
                $note->peer = 2;
                $notifs[] = $note;
            }
        }

        return $notifs;
    }


    /**
     * Get notifications for Level 2 events
     * @return array
     */
    public function getNotificationsL2()
    {
        $sql = "SELECT chks.*, ".
            PREFIX."members.userName, ".PREFIX."members.firstName, ".PREFIX."members.lastName, ".
            PREFIX."events.bookCode, ".PREFIX."projects.bookProject, mychks.step as myStep, ".
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
            "OR ".PREFIX."events.admins LIKE :adminID) ".
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
                    $p2 = $peer2Check[$chapter];
                    if($p2["memberID"] > 0
                        || $data["memberID"] == Session::get("memberID"))
                        continue;
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
     * Get notifications for Level 2 events
     * @return array
     */
    public function getNotificationsSun()
    {
        $sql = "SELECT trs.*, ".
            PREFIX."members.userName, ".PREFIX."members.firstName, ".PREFIX."members.lastName, ".
            PREFIX."events.bookCode, ".PREFIX."projects.bookProject, mytrs.step as myStep, ".
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
                .PREFIX."events.bookCode, ".PREFIX."projects.bookProject, ".
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
            ->where("events.admins", "LIKE", "%$memberID%")
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
                        "projects.bookProject", "projects.targetLang"])
                    ->leftJoin("members", "chapters.memberID", "=", "members.memberID")
                    ->leftJoin("events", "chapters.eventID", "=", "events.eventID")
                    ->leftJoin("projects", "events.projectID", "=", "projects.projectID")
                    ->leftJoin("abbr", "events.bookCode", "=", "abbr.code")
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

    // Used just for development purposes
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

    public function getTWords($lang = "en")
    {
        $ch = curl_init();

        switch ($lang)
        {
            case "ceb":
                curl_setopt($ch, CURLOPT_URL, template_url("source/ceb_ulb/terms.json"));
                break;

            default:
                curl_setopt($ch, CURLOPT_URL, "https://api.unfoldingword.org/ts/txt/2/bible/".$lang."/terms.json");
                break;
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $cat = curl_exec($ch);
        curl_close($ch);
        return $cat;
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


    /**
     * Download notes from DCS and extract them
     * @param string $lang
     * @param bool $update
     * @return bool|string
     */
    public function downloadAndExtractNotes($lang = "en", $update = false)
    {
        $filepath = "../app/Templates/Default/Assets/source/".$lang."_notes.zip";
        $folderpath = "../app/Templates/Default/Assets/source/".$lang."_tn";

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
            preg_match("/([0-9]{2,3}|front)\/([0-9]{2,3}|intro).md$/", $file, $matches);
            
            if(!isset($matches[1]) || !isset($matches[2])) return false;
            
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
    public function GetTranslationWords($book, $chapter, $lang = "en")
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

                preg_match("/([a-z]{3})\/(\d+)\/(\d+)$/", $occurrence, $matches);

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
        if($manageMode != null)
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
     * Get next chapter to translate
     * @param $eventID
     * @return array|static[]
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
}