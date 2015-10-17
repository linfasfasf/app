<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once FCPATH.'modules/cocos_packingtool/libraries/uploadzip_library.php';
class Uploadzip_libraryTest extends uploadzip_library{
    public function __construct() {
        parent::__construct();
    }
    
    public function get_manifest_ver($manifest_url,$default = 0){
        return parent::get_manifest_ver($manifest_url,$default = 0);
    }
    
    public function gen_gamekey()
    {
        return parent::gen_gamekey();
    }
    
    public function check_all_info($rule, $info) {
        return parent::check_all_info($rule, $info);
    }
    
    public function get_info($path) {
        return parent::get_info($path);
    }
    
    public function get_fileinfo_by_zip($path) {
        return parent::get_fileinfo_by_zip($path);
    }
    
    public function find_chn_resources($dir) {
        return parent::find_chn_resources($dir);
    }
    
    public function validation_channel_id($channel_id) {
        return parent::validation_channel_id($channel_id);
    }
    
    public function get_file_info_by_dir($target_file, $dir) {
        return parent::get_file_info_by_dir($target_file, $dir);
    }
}