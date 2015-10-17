<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class test_cp_game_revision_apk_model extends CodeIgniterUnitTestCase{
    public function __construct(){
	parent::__construct('cp_game_revision_apk_model');

        $this->load->model('game_management/cp_game_revision_apk_model');
        $this->load->helper('simple_test/simple_test');
        $this->test_data = get_test_data_template();
    }
    public function setUp(){
    }

    public function tearDown(){
    }
    public function test_init() {
    }
    
    public function test_add(){
        $revision_id = '88';
        $chn_id='100114';
        $apk_file_dir='/gametest/chenqiwei/va408p';
        $apk_name='cqwtest.apk';
        $apk_download_url='/gametest/va408p/chenqiwei.apk';
        $rev_chn_res_id='88'; 
        $data=array(
            $revision_id,
            $chn_id,
            $rev_chn_res_id
                );
        
        $sql="select * from cp_game_revision_apk where revision_id=? and channel_id=?";
        $result=$this->db->query($sql,$data);
        $res=$result->row();
        if($res){
            $this->assertTrue($res,'已存在记录');
            $restrue=$this->cp_game_revision_apk_model->add($revision_id,$chn_id,$apk_download_url,$rev_chn_res_id,TRUE);
            $result2=$this->db->query($sql,$data);     
        $this->assertTrue($result2,'此记录更新成功。');
        }  else {
            $this->assertFalse($res,'无记录');
            $restrue=$this->cp_game_revision_apk_model->add($revision_id,$chn_id,$apk_download_url,$rev_chn_res_id,FALSE);
            $result2=$this->db->query($sql,$data);     
        $this->assertTrue($result2,'此记录添加成功。');
        }      
    }
    
    
    public function test_get_mappings_extended(){
        $data=array(
            $revision_id = '88',
            $chn_id='100114'
        );
        $result=$this->cp_game_revision_apk_model->get_mappings_extended($revision_id,$chn_id);
        
        $this->assertTrue($result,'成功获得映射信息');
        
    }
    
    
        public function test_remove(){
        $data=array(
            $revision_id = '88',
            $chn_id='100114'
        );
        $sql="select * from cp_game_revision_apk where revision_id=? and channel_id=?";
        $result=$this->db->query($sql,$data);
        $res=$result->row();
        $this->assertTrue($res,'此时存在该数据');
        
        $this->cp_game_revision_apk_model->remove($revision_id,$chn_id);
        
        $result=$this->db->query($sql,$data);
        $res=$result->row();
        $this->assertFalse($res,'此时不存在该数据');     
    }
    /**
     * 建立一个空的渠道资源关联
     */
    public function test_create_empty_slot(){
        $data=array(
            $revision_id = '88',
            $channel_id='100114',
            $file_dir='/chenqiwei/test/',
            $active=0,
            $apk_download_url='/chenqiwei/test/test.apk'
        );
        $data1=array(
            $revision_id = '88',
            $file_dir='/chenqiwei/test/',
            $apk_download_url='/chenqiwei/test/test.apk'
        );
        $data2=array(
            $revision_id = '88',
            $channel_id='100114',
            $active=0
        );
        
        $result=$this->cp_game_revision_apk_model->create_empty_slot($revision_id,$channel_id,$file_dir,$active,$apk_download_url);
        $this->assertTrue($result,'成功执行create_empty_slot（）');
        
        $sql1="select revision_id from cp_game_revision_channel_resources where revision_id=? and  file_dir=? and apk_download_url=?";
        $res1=$this->db->query($sql1,$data1);
        $result1=$res1->row();
        $this->assertTrue($result1,'成功创建cp_game_revision_channel_resources记录');
        
        $sql2="select revision_id from cp_game_revision_apk where revision_id=? and channel_id=? and active=?";
        $res2=$this->db->query($sql2,$data2);
        $result2=$res2->row();
        $this->assertTrue($result2,'成功创建cp_game_revision_channel_resources记录');
    }
    
    public function test_sync_chnres(){
        $data=array(
            $chnres_id='88',
            $apk_name = ''
        );
        $sql1="select apk_download_url,file_dir from cp_game_revision_channel_resources where id=? ";
        $res1=$this->db->query($sql1,$chnres_id);
        $result1=$res1->row();
        
        $result=$this->cp_game_revision_apk_model->sync_chnres($chnres_id,$apk_name);
        $this->assertTrue($result,'成功执行sync_chnres(）');
        
        $sql2="select apk_download_url,apk_file_dir,modify_time from cp_game_revision_apk where revision_channel_resources_id=? ";
        $res2=$this->db->query($sql2,$chnres_id);
        $result2=$res2->row();
        
        $this->assertEqual($result1->apk_download_url, $result2->apk_download_url);
        $this->assertEqual($result1->file_dir, $result2->file_dir);
 
       
        
    }
    
    public function test_update_apk(){
        $apk_id="88";
        $apk_download_url="/chenqiwei/test/test.apk";
        
        $this->cp_game_revision_apk_model->update_apk($apk_id,$apk_download_url);
            
        $sql1="select id,apk_download_url from cp_game_revision_apk where id=? ";
        $res1=$this->db->query($sql1,$apk_id);
        $result1=$res1->row();
        $this->assertEqual($result1->apk_download_url, $apk_download_url,"表1更新成功");
            
        $sql2="select apk_download_url from cp_game_revision_channel_resources WHERE id = (SELECT revision_channel_resources_id FROM cp_game_revision_apk WHERE id = ?)";
        $res2=$this->db->query($sql2,$apk_id);
        $result2=$res2->row();
        $this->assertEqual($result2->apk_download_url, $apk_download_url,"表2更新成功");
        
    }
    
    public function test_get_apk_info_extended() {
        $apk_id="88";
        $res=$this->cp_game_revision_apk_model->get_apk_info_extended($apk_id);
        var_dump($res);
        $this->assertTrue($res,'获取成功');
        
    }
    
    public function test_toggle_config_type(){
        
        $apk_id="88";
        $sql = "SELECT channel_config_type from cp_game_revision_apk WHERE id = ?";
        $res1 = $this->db->query($sql, $apk_id);
        $result1=$res1->row();   
        
        $res=$this->cp_game_revision_apk_model->toggle_config_type($apk_id);
        $this->assertTrue($res.'成功执行toggle_config_type（）');
        
        $sql = "SELECT channel_config_type from cp_game_revision_apk WHERE id = ?";
        $res2 = $this->db->query($sql, $apk_id);
        $result2=$res2->row();
        
        $this->assertNotEqual($result1->channel_config_type,$result2->channel_config_type,'config_type值已改变');
        
    }
    
}