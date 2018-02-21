<?php
namespace App\Models;

use Database\Model;
use Helpers\Data;

class NewsModel extends Model
{
    public  function __construct()
    {
        parent::__construct();
    }

    public function getNews()
    {
        return $this->db->table("news")
            ->orderBy("id", "desc")
            ->get();
    }

    public function createNews($data)
    {
        return $this->db->table("news")
            ->insertGetId($data);
    }

    public function deleteNews($where)
    {
        return $this->db->table("news")
            ->where($where)
            ->delete();
    }

    public function updateNews($data, $where)
    {
        return $this->db->table("news")
            ->where($where)
            ->update($data);
    }
}