<?php

class liuhe_info_model extends MY_Model{
    public function __construct() {
        parent::__construct();
    }
    
    public function save_info($content,$id,$title,$cid,$period){
        $sql = 'INSERT INTO cp_liuhe_info (content,article_id,title,cid,period)values(?,?,?,?,?)';
        $this->db->query($sql,array($content,$id,$title,$cid,$period));
        if($this->db->error()['code']!==0){
            var_dump($this->db->error());
            return FALSE;
        }
        
        return TRUE;
    }
    
    public function check_article_exist($id){
        $sql = 'SELECT article_id from cp_liuhe_info where article_id =?';
        $result = $this->db->query($sql,$id);
        $info = $result->result_array();
        if($this->db->error()['code']!==0){
            var_dump($this->db->error());
            return FALSE;
        }
        return $info;
    }
    
    public function get_title($cid,$start){
        $sql = 'SELECT cid, article_id, title from cp_liuhe_info where cid = ? order by article_id desc limit ?,10';
        $result = $this->db->query($sql,array($cid,$start));
        $info  = $result->result_array();
        if($this->db->error()['code']!==0){
            var_dump($this->db->error());
            return FALSE;
        }
        return $info;
    }
    
    public function get_article($article_id){
        $sql = 'SELECT * from cp_liuhe_info where article_id =?';
        $result = $this->db->query($sql,$article_id);
        $info = $result->result_array();
        return $info[0];
    }
    
    public function get_other_article($up_article){
        $sql = 'SELECT title, cid, article_id from cp_liuhe_info where article_id =?';
        $result = $this->db->query($sql,$up_article);
        $info = $result->result_array();
        if(!$info){
            return FALSE;
        }
        return $info[0];
    }

    public function get_total_num($period,$cid){
        $sql = 'SELECT count(period) from cp_liuhe_info where period = ? and cid =?';
        $result = $this->db->query($sql,array($period,$cid));
        $info   = $result->result_array();
        return $info[0]['count(period)'];
    }

    public function update_period($period){
        $sql = "SELECT max(period) from cp_liuhe_date ";
        $result = $this->db->query($sql);
        $info   = $result->result_array();
        $info_int = intval($info[0]['max(period)']);
        if($period >= $info_int){
            $sql = 'INSERT INTO cp_liuhe_date (period)values(?)';
            $this->db->query($sql,$period);
            return TRUE;
        }
        return FALSE;
    }
    
    public function get_period(){
        $sql = 'SELECT max(period) from cp_liuhe_date';
        $result = $this->db->query($sql);
        $info   = $result->result_array();
        return $info[0]['max(period)'];
    }
    
   
}
