<?php

namespace App\Models;

use Database\Model;
use Helpers\Data;
use DB;

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
	public function getMember(array $select, array $where)
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

        return $builder
            ->select($select)->get();
    }

	public function getMembers($memberIDs = array())
	{
		if(is_array($memberIDs) && !empty($memberIDs))
		{
            return $this->db->table("members")
                ->select("memberID", "userName")
                ->whereIn("memberID", $memberIDs)->get();
		}
	}


	/** Get member data
	 * @param $email
	 * @return array
	 */
	public function getMemberWithProfile($email)
	{
        return $this->db->table("members")
            ->leftJoin("profile", "members.memberID", "=", "profile.mID")
            ->where("members.userName", $email)
            ->orWhere("members.email", $email)->get();
	}

	public function getAdminMember($memberID)
	{
        return $this->db->table("gateway_projects")
            ->select("gwProjectID", "gwLang")
            ->where("admins", "LIKE", "%$memberID%")->get();
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

	public function getAdminsByGwProject($gwProjectID)
	{
        return $this->db->table("gateway_projects")
            ->select("admins")
            ->where("gwProjectID", $gwProjectID)->get();
	}

	public function getMembersByTerm($search)
	{
        return $this->db->table("members")
            ->where("isSuperAdmin", false)
            ->where("verified", true)
            ->where("userName", "LIKE", "%$search%")->get();
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