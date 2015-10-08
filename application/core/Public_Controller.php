<?php

class Public_Controller extends CI_Controller{
    public function __construct() {
        parent::__construct();
        
    }
    
    public function completeurl($url1,$url2){
        $url1 = rtrim($url1,'\\');
        $url1 = rtrim($url1,'/');
        $url2 = rtrim($url2,'\\');
        $url2 = rtrim($url2,'/');
        return $url1.'/'.$url2.'/';
    }
    
}