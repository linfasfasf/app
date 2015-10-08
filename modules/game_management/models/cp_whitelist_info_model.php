<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Cp_whitelist_info_model extends MY_Model{
    public $table = 'cp_whitelist_info';
    public $primary_key = 'id';
    
    public function __construct() {
        parent::__construct();
    }
    
    public function get_packagename_list() {
        $sql = 'SELECT * FROM cp_whitelist_info';
        return $this->db->query($sql)->result_array();
    }
    
    public function is_present_packagename($packagename) {
        $result = $this->select('*')->where(array('packagename'=>$packagename))->get();
        if(empty($result))
        {
            return FALSE;
        }
        else
        {
            return TRUE;
        }
    }
}
