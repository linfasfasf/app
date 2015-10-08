<?php

class Response extends CI_Controller{
    
    private static $instance;
            
    function __construct() {
        parent::__construct();
        self::$instance =& $this;
    }
    
    public static function get_instance(){
        return self::$instance;
    }
    /*
     * 接口发送，使用json或XML格式
     * $fun 格式选择 ，默认 json
     */
    
    public static function post($code,$message,$data=  array(),$fun='json'){
        if(!is_numeric($code)){
            $arr=array(
                'code'=>'201',
                'msg'=>'数据错误',
            );
            echo json_encode($arr);
            exit();
        }
        if($fun=='xml'){
            self::xml($code, $message,$data);
        }elseif ($fun=='json') {
            self::json($code, $message, $data);
        }  else {
            $arr=array(
                'code'=>'402',
                'msg'=>'数据传送方式错误',
            );
        }
        
    }

    public static function json($code,$message,$data=  array()){
        if(!is_numeric($code)){
            $arr=array(
                'code'=>'201',
                'msg'=>'数据错误',
            );
            echo json_encode($arr);
            exit();
        }
        
	  $arr = array(
	   'code'=>$code,
	   'msg'=>$message,
           'data'=>$data   
	  );
	  $json=json_encode($arr);
	  echo   $json;
        }
        
        
        public static function xml($code,$message,$data=  array()){
            if (!is_numeric($code)){
                return ;
            }
            $arr=array(
                'code'=>$code,
                'msg'=>$message,
                'data'=>$data
            );
            header("Content-Type:text/xml");
            $xml="<?xml version='1.0' encoding='UTF-8' ?>\n";
            $xml .="<root>\n";
            $xml .=self::xmlencode($arr);
            
            $xml .="</root>\n";
            echo $xml;
        }
        
        
        public static function xmlencode($data){
            $xml="";
            foreach ($data as $key =>$val){
            $xml .="<{$key}>\n";
            $xml .=is_array($val)?self::xmlencode($val):$val;
            $xml .="</{$key}>\n";
            }
            return $xml;
        }
}
