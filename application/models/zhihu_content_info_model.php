<?php


class zhihu_content_info_model extends MY_Model{
    
    public $table = 'cp_zh_content_info';
    
    public function __construct() {
        parent::__construct();
        
    }
    
    public function check_content_exist($articleid){
        $sql = 'select id from cp_zh_content_info where articleid =?';
        $result=$this->db->query($sql,array($articleid));
        $info  = $result->result_array();
        if($info==NULL){
            return FALSE; 
        }
        return TRUE;
    }

    public function add_content($body,$image_source,$imageurl,$cssurl,$articleid){
        $sql = 'INSERT INTO cp_zh_content_info ( body, image_source,imageurl,articleid,css)'
                . 'values (?,?,?,?,?)';
        $this->db->query($sql,array($body,$image_source,$imageurl,$articleid,$cssurl));
        if($this->db->error()['code']!== 0){
            return FALSE;
        }
        return TRUE;
    }
    
    public function check_update(){
        $sql = 'SELECT distinct a.articleid from cp_zh_list_info as a LEFT JOIN cp_zh_content_info '
                . 'on a.articleid = cp_zh_content_info.articleid where cp_zh_content_info.articleid is null ';
        $result=$this->db->query($sql);
        $info  =$result->result_array();
        if ($debug=$this->db->error()['code']!=0) {
            return $debug;
        }
        return $info;
    }
    
    public function get_content($articleid){
        $sql = 'SELECT a.body, a.imageurl, a.articleid, a.css, b.title 
            from cp_zh_content_info a,cp_zh_list_info b where a.articleid = b.articleid and a.articleid =?';
        $result = $this->db->query($sql,array($articleid));
        $info   = $result->result_array();
        if($this->db->error()['code']!= 0){
            return FALSE;
        }
        return $info;
    }
}