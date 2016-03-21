<?php
/**
 * Created by PhpStorm.
 * User: Maxim
 * Date: 05 Feb 2016
 * Time: 19:57
 */

namespace Models;

use Core\Model;
use Helpers\Data;
use Helpers\Session;

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

    public function getProjects($userName, $isSuperAdmin, $projectID = null)
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
                "(SELECT gwProjectID FROM ".PREFIX."gateway_projects WHERE admins LIKE :userName) ";
            $prepare[":userName"] = '%"'.$userName.'"%';
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
            "LEFT JOIN vm_translators ON vm_translators.eventID=vm_events.eventID ".
            "LEFT JOIN vm_checkers_l2 ON vm_checkers_l2.eventID=vm_events.eventID ".
            "LEFT JOIN vm_checkers_l3 ON vm_checkers_l3.eventID=vm_events.eventID "
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
            "GROUP BY vm_abbr.id ORDER BY vm_abbr.id";

        $prepare = array(":projectID" => $projectID);

        return $this->db->select($sql, $prepare);
    }

    public function getEventMember($eventID, $memberID)
    {
        $sql = "SELECT vm_translators.memberID AS translators, ".
            "vm_checkers_l2.memberID AS checkers_l2, vm_checkers_l3.memberID AS checkers_l3 ".
            "FROM vm_events ".
            "LEFT JOIN vm_translators ON vm_translators.eventID = vm_events.eventID AND vm_translators.memberID = :memberID ".
            "LEFT JOIN vm_checkers_l2 ON vm_checkers_l2.eventID = vm_events.eventID AND vm_checkers_l2.memberID = :memberID ".
            "LEFT JOIN vm_checkers_l3 ON vm_checkers_l3.eventID = vm_events.eventID AND vm_checkers_l3.memberID = :memberID ".
            "WHERE vm_events.eventID = :eventID";

        $prepare = array(":eventID" => $eventID, ":memberID" => $memberID);

        return $this->db->select($sql, $prepare);
    }

    public function getAllGwLanguages()
    {
        return $this->db->select("SELECT langID, langName FROM ".PREFIX."languages WHERE isGW = 1 ORDER BY `langID` ASC");
    }

    public function getMemberGwLanguages($userName)
    {
        $sql = "SELECT * FROM ".PREFIX."gateway_projects LEFT JOIN ".PREFIX."languages ".
            "ON ".PREFIX."gateway_projects.gwLang=".PREFIX."languages.langID ".
            "WHERE ".PREFIX."gateway_projects.admins LIKE :userName ".
            "GROUP BY ".PREFIX."gateway_projects.gwLang ORDER BY ".PREFIX."languages.langID";

        $prepare = array(":userName" => '%"'.$userName.'"%');

        return $this->db->select($sql, $prepare);
    }

    public function getTargetLanguages($userName, $gwLang)
    {
        $adminPart = "WHERE ".PREFIX."languages.gwLang IN ".
            "(SELECT langName FROM ".PREFIX."languages WHERE langID IN ".
            "(SELECT gwLang FROM ".PREFIX."gateway_projects WHERE admins LIKE :userName AND gwLang=:gwLang))";

        $sql = "SELECT * FROM vm_languages ".
            (Session::get("isSuperAdmin") ? "WHERE ".PREFIX."languages.gwLang IN (SELECT langName FROM ".PREFIX."languages WHERE langID=:gwLang)" : $adminPart).
            " ORDER BY langID";

        $prepare = array();
        $prepare[":gwLang"] = $gwLang;
        if(!Session::get("isSuperAdmin"))
        {
            $prepare[":userName"] = '%"'.$userName.'"%';
        }
        return $this->db->select($sql, $prepare);
    }

    public function getSourceTranslations()
    {
        return $this->db->select("SELECT ".PREFIX."books.bookProject, ".PREFIX."languages.langName, ".PREFIX."languages.langID ".
            "FROM ".PREFIX."books
            LEFT JOIN ".PREFIX."languages ON ".PREFIX."books.gwLang=".PREFIX."languages.langID
            GROUP BY ".PREFIX."books.gwLang, ".PREFIX."books.bookProject ORDER BY ".PREFIX."languages.langName");
    }

    public function getBooks($gwLang, $bookProject)
    {
        $sql = "SELECT * FROM ".PREFIX."books LEFT JOIN ".PREFIX."abbr ON ".PREFIX."books.abbrID=".PREFIX."abbr.id ".
            "WHERE ".PREFIX."books.gwLang=:gwLang AND ".PREFIX."books.bookProject=:bookProject ORDER BY ".PREFIX."books.abbrID";

        $prepare = array(":gwLang" => $gwLang, ":bookProject" => $bookProject);

        return $this->db->select($sql, $prepare);
    }

    public function getAdmins($search)
    {
        $sql = "SELECT userName FROM ".PREFIX."members ".
                    "WHERE isAdmin=1 ".
                        "AND isSuperAdmin=0 " .
                        "AND userName LIKE :userName";

        $prepare = array(":userName" => "%$search%");

        return $this->db->select($sql, $prepare);

    }

    public function createGatewayProject($data)
    {
        $this->db->insert(PREFIX."gateway_projects",$data);
        return $this->db->lastInsertId('gwProjectID');
    }

    public function createProject($data)
    {
        $this->db->insert(PREFIX."projects",$data);
        return $this->db->lastInsertId('projectID');
    }

    public function createEvent($data)
    {
        $this->db->insert(PREFIX."events",$data);
        return $this->db->lastInsertId('eventID');
    }

    public function addTranslator($data)
    {
        try
        {
            $this->db->insert(PREFIX."translators",$data);
        } catch(\PDOException $e)
        {
            return $e->getMessage();
        }
        return $this->db->lastInsertId('trID');
    }

    public function addDraftChecker($data, $checkerData, $shouldUpdateChecker)
    {
        $updateSuccess = true;
        if($shouldUpdateChecker)
        {
            if($this->db->update(PREFIX."members", $checkerData, array("memberID" => Session::get("memberID"))))
            {
                foreach ($checkerData as $key => $value)
                    Session::set($key, $value);
            }
            else
            {
                $updateSuccess = false;
            }
        }

        if($updateSuccess)
        {
            try
            {
                $this->db->insert(PREFIX."checkers_draft",$data);
            } catch(\PDOException $e)
            {
                return $e->getMessage();
            }
            return $this->db->lastInsertId('drfchID');
        }
        else
        {
            return "[Member update error]";
        }
    }

    public function addL2Checker($data, $checkerData, $shouldUpdateChecker)
    {
        $updateSuccess = true;
        if($shouldUpdateChecker)
        {
            if($this->db->update(PREFIX."members", $checkerData, array("memberID" => Session::get("memberID"))))
            {
                foreach ($checkerData as $key => $value)
                    Session::set($key, $value);
            }
            else
            {
                $updateSuccess = false;
            }
        }

        if($updateSuccess)
        {
            foreach ($checkerData as $key => $value)
                Session::set($key, $value);

            try
            {
                $this->db->insert(PREFIX."checkers_l2",$data);
            } catch(\PDOException $e)
            {
                return $e->getMessage();
            }
            return $this->db->lastInsertId('l2chID');
        }
        else
        {
            return "[Member update error]";
        }
    }

    public function addL3Checker($data, $checkerData, $shouldUpdateChecker)
    {
        $updateSuccess = true;
        if($shouldUpdateChecker)
        {
            if($this->db->update(PREFIX."members", $checkerData, array("memberID" => Session::get("memberID"))))
            {
                foreach ($checkerData as $key => $value)
                    Session::set($key, $value);
            }
            else
            {
                $updateSuccess = false;
            }
        }

        if($updateSuccess)
        {
            foreach ($checkerData as $key => $value)
                Session::set($key, $value);

            try
            {
                $this->db->insert(PREFIX."checkers_l3",$data);
            } catch(\PDOException $e)
            {
                return $e->getMessage();
            }
            return $this->db->lastInsertId('l3chID');
        }
        else
        {
            return "[Member update error]";
        }
    }

    public  function updateEvent($data, $where)
    {
        return $this->db->update(PREFIX."events", $data, $where);
    }
}