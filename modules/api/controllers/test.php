<?php
error_reporting(0);
require_once  __DIR__.'/response.php';
class Test extends Response
{
    
    public function __construct() {
        parent::__construct();
        $this->response = parent::get_instance();
    }
    
    public function test(){
        $data = array(
            'name'=>'lero_lin',
            'scroe'=> '88',
        );
        $info = array(
            'code' => '200',
            'msg' => 'success',
            'data'=>$data
        );
        
        $this->response->post(200,'success',$info,xml);
    }
    
}