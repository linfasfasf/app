<?php

class liuhecai extends My_Controller{
    public function __construct() {
        parent::__construct();
    }
    
    public function px(){
        $num = $_GET['num'];
        if($num==NULL){
            $num_arr = range(1, 49);
            $this->load->view('six/liuhe',array('arr'=>$num_arr));
        }
        $num .= $num.',';
        var_dump($num);
        $this->load->view('six/liuhe',array('arr_chose'=>$num));
    }
}