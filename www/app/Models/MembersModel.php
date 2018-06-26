<?php

namespace App\Models;

use Database\Model;
use Helpers\Data;
use DB;
use Support\Facades\Language;

class MembersModel extends Model {

    protected $table = 'members';
    protected $primaryKey = 'memberID';

    public  function __construct()
    {
            parent::__construct();
    }

    /**
     * Get member
     * @param array $select An array of fields
     * @param array $where Single/Multidimentional array with where params (field, operator, value, logical)
     * @return array|static[]
     */
    public function getMember(array $select, array $where, $getProfile = false)
    {
        $builder = $this->db->table("members");

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

        if($getProfile)
        {
            $select[] = "profile.*";
            $builder->leftJoin("profile", "members.memberID", "=", "profile.mID");
        }

        return $builder
            ->select($select)->get();
    }


    public function getLastTempMember()
    {
        return $this->db->table("members")
            ->select("userName")
            ->where("userName", "like", "%user%")
            ->orderBy("memberID", "desc")
            ->limit(1)->get();
    }

    public function getMembers($memberIDs = array(), $more = false)
    {
        if(is_array($memberIDs) && !empty($memberIDs))
        {
            $select = ["members.memberID", "members.userName", "members.firstName", "members.lastName", "profile.avatar"];

            if($more)
                $select = array_merge($select, ["members.email"]);

            return $this->db->table("members")
                ->select($select)
                ->leftJoin("profile", "members.memberID", "=", "profile.mID")
                ->whereIn("memberID", $memberIDs)
                ->orderBy("firstName")->get();
        }
    }


    /** Get member data
     * @param $emailOrUnameOrId
     * @param  $login boolean Do not get user by ID during login
     * @return array User info with profile
     */
    public function getMemberWithProfile($emailOrUnameOrId, $login = false)
    {
        $builder = $this->db->table("members");

        $builder->leftJoin("profile", "members.memberID", "=", "profile.mID")
            ->where("members.userName", $emailOrUnameOrId)
            ->orWhere("members.email", $emailOrUnameOrId);

        if(!$login)
            $builder->orWhere("members.memberID", $emailOrUnameOrId);

        return $builder->get();
    }

    /**
     * Get admins (facilitators) by name
     * @param string $search
     * @return array
     */
    public function getAdminsByTerm($search)
    {
        return $this->db->table("members")
            ->select("memberID", "userName")
            ->where("isAdmin", true)
            ->where("isSuperAdmin", false)
            ->where("userName", "LIKE", "%$search%")->get();
    }

    public function getMembersByTerm($search)
    {
        return $this->db->table("members")
            //->where("isSuperAdmin", false)
            ->where("verified", true)
            ->where("userName", "LIKE", "%$search%")
            ->orWhere("firstName", "LIKE", "%$search%")
            ->orWhere("lastName", "LIKE", "%$search%")->get();
    }

    /**
     * Search members by filters
     * @param $name
     * @param $role
     * @param $languages
     * @param bool $count return total rows
     * @param bool $admin get sensitive info
     * @param bool $verified get verified members
     * @param int $page page number
     * @param int $take items per page
     * @return array|int|static[]
     */
    public function searchMembers($name, $role, $languages, $count = false, $admin = false, $verified = false, $page = 1, $take = 50)
    {
        $skip = ($page-1) * $take; // Skip 50 (default) rows every page

        $builder = $this->db->table("members");

        $builder->leftJoin("profile", "members.memberID", "=", "profile.mID")
            ->where("members.isDemo", "=", false); // exclude demo accounts

        if($verified) // Exclude non-verified accounts
            $builder->where("members.verified", true);

        $builder->distinct();

        if(!$count)
        {
            $select = [
                "members.memberID",
                "members.userName",
                "members.firstName",
                "members.lastName",
                "members.isAdmin",
                "profile.prefered_roles",
                "blocked"
            ];

            if($admin)
                $select[] = "members.email";

            $builder->select($select)
                ->skip($skip)->take($take) // limit to 50 (default) rows per page
                ->orderBy("members.userName");
        }

        if($name)
            $builder->where(function($query) use ($name) {
                $query->where("members.userName", "LIKE", "%$name%") // search in usernames
                    ->orWhere("members.firstName", "LIKE", "%$name%") // search in first names
                    ->orWhere("members.lastName", "LIKE", "%$name%"); // search in last names
            });

        if($role == "translators")
            $builder->where("members.isAdmin", false); // exclude facilitators (admins) when searching just translators
        elseif ($role == "facilitators")
            $builder->where("members.isAdmin", true); // facilitators (admins)

        // search facilitators in events they are assigned to
        if(($role == "facilitators" || $role == "all") && $languages)
        {
            $builder->crossJoin("projects")
                ->leftJoin("events", "events.projectID", "=", "projects.projectID")
                ->where(function($query) use ($languages) {
                    $query->where(function($query) use ($languages) {
                        $query->whereIn("projects.gwLang", $languages)
                            ->orWhereIn("projects.targetLang", $languages);
                    })
                        ->whereRaw("`".PREFIX."events`.`admins` LIKE CONCAT('%\"', `".PREFIX."members`.`memberID`, '\"%')");
                });
        }

        // search by language
        if($languages)
        {
            // search translators by language in their profiles
            if($role == "translators")
                $builder->where(function($query) use ($languages) {
                    foreach ($languages as $language)
                        $query->orWhere("profile.languages", "LIKE", "%\"$language\"%");
                });
            elseif($role == "all" || $role == "facilitators")
                $builder->orWhere(function ($query) use ($languages) {
                    $query->where(function($query) use ($languages) {
                        foreach ($languages as $language)
                            $query->orWhere("profile.languages", "LIKE", "%\"$language\"%");
                    });
                });
        }

        if(!$count)
            return $builder->get();
        else
            return $builder->count("memberID");
    }

    /**
     * Create new member
     * @param $data
     * @return string
     */
    public function createMember($data){
        return $this->db->table("members")
            ->insertGetId($data);
    }


    /**
     * Update member
     * @param $data
     * @param $where
     * @return int Number of affected rows
     */
    public function updateMember($data, $where){
        return $this->db->table("members")
            ->where($where)
            ->update($data);
    }

    public function createProfile($data)
    {
        return $this->db->table("profile")
            ->insertGetId($data);
    }

    public function updateProfile($data, $where)
    {
        return $this->db->table("profile")
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

    public function translate($value, $code = "en", $params = [])
    {
        Language::instance('app')->load("messages", $code);
        return Language::instance('app')->get($value, $code, $params);
    }
    
    public function createMultipleMembers($members = 50, $profileLangs = ["en" => [3,3]], $password = null)
    {
        $result = "";
        $lastUser = $this->getLastTempMember();
        $lastMember = 0;

        if(!$password) return $result;

        if(!empty($lastUser))
        {
            $lastUserName = $lastUser[0]->userName;
            preg_match("/\d+/", $lastUserName, $matches);

            if(!empty($matches))
                $lastMember = $matches[0];
        }

        $result .= "The members (user".($lastMember+1)." to ";

        for($i = $lastMember+1; $i <= $members+$lastMember; $i++)
        {
            $mData = [
                "userName" => "user".$i,
                "firstName" => "User".$i,
                "lastName" => "N",
                "password" => $password,
                "email" => "user".$i."@v-mast.com",
                "active" => true,
                "verified" => true
            ];

            $memberID = $this->createMember($mData);

            $pData = [
                "mID" => $memberID,
                "prefered_roles" => json_encode(["translator"]),
                "languages" => json_encode($profileLangs)
            ];

            $this->createProfile($pData);
        }

        $result .= "user".($i-1).") have been created!";

        return $result;
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