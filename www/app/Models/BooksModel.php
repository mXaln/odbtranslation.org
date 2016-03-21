<?php
namespace Models;

use Core\Model;

class BooksModel extends Model
{
    public  function __construct()
    {
        parent::__construct();
    }

    /**
     * For getting data of a book
     * @param $fields Requested fields could be * for all or comma separated list
     * @param $where array Example: array('id' => array('=', 1), 'name' => array('!=', 'John'))
     * @return array
     */
    public function getBook($fields, $where)
    {
        $sql = "SELECT $fields FROM ".PREFIX."books WHERE";
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
}