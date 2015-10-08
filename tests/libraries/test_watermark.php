<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
class test_watermark extends CodeIgniterUnitTestCase{
    
    public function __construct(){
	parent::__construct('testwatermark');

        $this->load->library('common/watermark');
        $this->load->helper('simple_test/simple_test');
        $this->test_data = get_test_data_template();
    }
    public function setUp(){
    }

    public function tearDown(){
    }
    public function test_init() {
    }
  
//    function test_resize(){
//        
//        $source_image= '/Users/chenqiwei/Documents/Test/1icon.png';
//        $width=72;
//        $height=72;
//        $res=$this->watermark->resize($source_image,$width,$height);
//        
//        $this->assertTrue($res);
//        
//    }
    
    function test_add_watermark(){  
   
      $source_image= '/Users/chenqiwei/Documents/Test/qsmybackground.jpg';//(必须)设置原图像的名字和路径. 路径必须是相对或绝对路径，但不能是URL.
      
      $wm_overlay_path ='/Users/chenqiwei/Documents/Test/qsmybackground.jpg';
//      $wm_overlay_path = FCPATH.'uploads/watermark/watermark.jpg';//水印图像的名字和路径
      $wm_opacity='90';
      $res=$this->watermark->add_watermark($source_image,$wm_overlay_path,$wm_opacity);
 
      $this->assertTrue($res);
 
    }
    
  
    
}
