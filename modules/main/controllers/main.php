<?php

class main extends Admin_Controller{
    public function __construct() {
        parent::__construct();
        $this->load->library('smarty');
//        redirect('zhihu/show_list?limit=5');
    }
    
    public function index(){
        $page=$this->input->get('page');
        $this->load->view('index');
//        $content=  $this->load->view('welcome',array('page'=>$page),TRUE);
//        $this->smarty->view('general.tpl',$content);
    }
    
    public function top(){
        $this->load->view('include/top');
    }
    
    public function center(){
//        $this->load->view('include/center');
        redirect('zhihu/show_list?limit=10');
    }
}
