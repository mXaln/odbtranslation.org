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

    /**
     * For getting data of a translation
     * @param $fields Requested fields could be * for all or comma separated list
     * @param $where array Example: array('id' => array('=', 1), 'name' => array('!=', 'John'))
     * @return array
     */
    public function getTranslation($fields, $where)
    {
        $sql = "SELECT $fields FROM ".PREFIX."translations WHERE";
        $prepare = array();
        $i=0;

        foreach($where as $key=>$value)
        {
            $sql .= ($i>0 ? " AND " : " ")."$key ".$value[0]." :$key";
            $prepare[':'.$key] = $value[1];
            $i++;
        }

        return $this->db->select($sql, $prepare);
    }

    public function getTranslationWithBook($fields, $where, $group = false)
    {
        $sql = "SELECT $fields FROM ".PREFIX."translations".
            " LEFT JOIN ".PREFIX."books".
            " ON ".PREFIX."translations.bID=".PREFIX."books.bID".
            " WHERE";
        $prepare = array();
        $i=0;

        foreach($where as $key=>$value)
        {
            $sql .= ($i>0 ? " AND " : " ")."$key ".$value[0]." :".preg_replace("/.*\./", "", $key);
            $prepare[':'.preg_replace("/.*\./", "", $key)] = $value[1];
            $i++;
        }

        if($group)
            $sql .= " GROUP BY ".PREFIX."$group";
        $sql .= " ORDER BY ".PREFIX."books.chapter AND ".PREFIX."translations.translatedVerses";

        return $this->db->select($sql, $prepare);
    }

    /**
     * Create new translation
     * @param $data
     * @return string
     */
    public function createTranslation($data)
    {
        $this->db->insert(PREFIX."translations",$data);
        return $this->db->lastInsertId('tID');
    }

    /**
     * Update translation
     * @param $data
     * @param $where
     */
    public function updateTranslation($data, $where){
        $this->db->update(PREFIX."translations",$data,$where);
    }
}