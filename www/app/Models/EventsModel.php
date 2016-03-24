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
            "GROUP BY vm_abbr.abbrID ORDER BY vm_abbr.abbrID";

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

    /**
     * Get all events of a member
     * @param $memberID
     * @param $memberType
     * @param null $eventID
     * @return array
     */
    public function getMemberEvents($memberID, $memberType, $eventID = null)
    {
        $events = array();
        $sql = "SELECT ".($memberType == EventMembers::TRANSLATOR ? PREFIX."translators.step, ".PREFIX."translators.trID, ".PREFIX."translators.currentChunk, " : "")
            .PREFIX."events.eventID, ".PREFIX."events.bookCode, ".PREFIX."events.chapters, "
            .PREFIX."projects.bookProject, ".PREFIX."projects.sourceLangID, "
            .PREFIX."languages.langName, ".PREFIX."abbr.name FROM ";
        $mainTable = "";

        switch($memberType)
        {
            case EventMembers::TRANSLATOR:
                $mainTable = PREFIX."translators";
                break;

            case EventMembers::L2_CHECKER:
                $mainTable = PREFIX."checkers_l2";
                break;

            case EventMembers::L3_CHECKER:
                $mainTable = PREFIX."checkers_l3";
                break;
        }

        $sql .= $mainTable." LEFT JOIN ".PREFIX."events ON ".$mainTable.".eventID = ".PREFIX."events.eventID ".
            "LEFT JOIN ".PREFIX."projects ON ".PREFIX."events.projectID = ".PREFIX."projects.projectID ".
            "LEFT JOIN ".PREFIX."languages ON ".PREFIX."projects.targetLang = ".PREFIX."languages.langID ".
            "LEFT JOIN ".PREFIX."abbr ON ".PREFIX."events.bookCode = ".PREFIX."abbr.code ".
            "WHERE ".$mainTable.".memberID = :memberID ".
            (!is_null($eventID) ? " AND ".$mainTable.".eventID=:eventID" : "");

        $prepare = array();
        $prepare[":memberID"] = $memberID;

        if(!is_null($eventID))
            $prepare[":eventID"] = $eventID;

        return $this->db->select($sql, $prepare);
    }

    /**
     * Get list of all gateway languages
     * @return array
     */
    public function getAllGwLanguages()
    {
        return $this->db->select("SELECT langID, langName FROM ".PREFIX."languages WHERE isGW = 1 ORDER BY `langID` ASC");
    }

    /**
     * Get Gateway languages assigned to admin
     * @param string $userName
     * @return array
     */
    public function getMemberGwLanguages($userName)
    {
        $sql = "SELECT * FROM ".PREFIX."gateway_projects LEFT JOIN ".PREFIX."languages ".
            "ON ".PREFIX."gateway_projects.gwLang=".PREFIX."languages.langID ".
            "WHERE ".PREFIX."gateway_projects.admins LIKE :userName ".
            "GROUP BY ".PREFIX."gateway_projects.gwLang ORDER BY ".PREFIX."languages.langID";

        $prepare = array(":userName" => '%"'.$userName.'"%');

        return $this->db->select($sql, $prepare);
    }

    /**
     * Get list of other languages
     * @param string $userName
     * @param string $gwLang
     * @return array
     */
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

    public function getBooks($gwLang, $bookProject)
    {
        $sql = "SELECT * FROM ".PREFIX."books LEFT JOIN ".PREFIX."abbr ON ".PREFIX."books.abbrID=".PREFIX."abbr.id ".
            "WHERE ".PREFIX."books.gwLang=:gwLang AND ".PREFIX."books.bookProject=:bookProject ORDER BY ".PREFIX."books.abbrID";

        $prepare = array(":gwLang" => $gwLang, ":bookProject" => $bookProject);

        return $this->db->select($sql, $prepare);
    }

    /**
     * Get admins by name
     * @param string $search
     * @return array
     */
    public function getAdmins($search)
    {
        $sql = "SELECT userName FROM ".PREFIX."members ".
                    "WHERE isAdmin=1 ".
                        "AND isSuperAdmin=0 " .
                        "AND userName LIKE :userName";

        $prepare = array(":userName" => "%$search%");

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

    /**
     * Add member as new Level 3 checker for event
     * @param array $data
     * @param array $checkerData
     * @param bool $shouldUpdateChecker
     * @return string
     */
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