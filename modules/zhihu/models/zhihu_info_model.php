<?php

class Zhihu_info_model extends MY_Model{
    public $table = 'cp_zh_list_info';
    public function __construct() {
        parent::__construct();
    }
    
    public function check_list_exist($date){
        $sql= 'select date from cp_zh_list_info where date =?';
        $result=$this->db->query($sql,$date);
        $info= $result->result_array();
        return $info;
    }
    
    
    public function add_list($imageurl,$type,$articleid,$title,$date){
        $sql = 'INSERT INTO cp_zh_list_info (imageurl, type, articleid, title, date) values (?,?,?,?,?)';
        $result=@$this->db->query($sql,array($imageurl,$type,$articleid,$title,$date));
        if($this->db->error()['code']!== 0)
        {
            return FALSE;
        }
        return TRUE;
    }
    
    public function show_list($start){
        $start = intval($start);
        $sql = 'SELECT * FROM cp_zh_list_info order by id desc limit ?,10 ';
        $result = $this->db->query($sql, array($start));
        var_dump($this->db->last_query());
        $info   = $result->result_array();
        if($this->db->error()['code']!==0){
            return FALSE;
        }
        return $info;
    }
    
    public function get_list_count(){
        $sql = 'SELECT COUNT(id) from cp_zh_list_info';
        $result = $this->db->query($sql);
        $info = $result->result_array();
        if($this->db->error()['code']!==0){
            return FALSE;
        }
        return $info[0];
    }
    
}