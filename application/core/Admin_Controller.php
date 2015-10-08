<?php
error_reporting(0);
/*
 *检测用户是否登陆
 * 
 */

class Admin_Controller extends CI_Controller{
    
    public function __construct() {

        parent::__construct();
        $this->load->library('admin/ion_auth');
        $this->check_param();
        $this->check_login();
    }
    
    /**
     * 检测输入参数，防止sql注入
     */
    public function check_param(){
        foreach ($_POST as $key => $value){
            if(is_array($value)){
                $_POST[$key] =$value;
            }  else {
                $_POST[$key]= mysql_real_escape_string($value);
            }
        }
        
        foreach ($_GET as $key =>$value){
            $_GET[$key] = mysql_real_escape_string($value);
        }
    }
    
    public function check_login(){
        $is_login=  $this->session->userdata('user_id');
        if(!$is_login)
        {
            $url='index.php/admin/admin/login' ."?url=".urlencode(current_url());
            redirect($url,'refresh');
        }
    }
    
    
    public function completeurl($url1,$url2){
        $url1 = rtrim($url1,'\\');
        $url1 = rtrim($url1,'/');
        $url2 = rtrim($url2,'\\');
        $url2 = rtrim($url2,'/');
        return $url1.'/'.$url2.'/';
    }
    
    
}
