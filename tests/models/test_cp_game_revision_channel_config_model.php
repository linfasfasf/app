<?php
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
class test_cp_game_revision_channel_config_model extends CodeIgniterUnitTestCase{
    public function __construct(){
    parent::__construct('cp_game_revision_channel_config_model');

        $this->load->model('game_management/cp_game_revision_channel_config_model');
        $this->load->helper('simple_test/simple_test');
        $this->test_data = get_test_data_template();
    }
    public function setUp(){
    }

    public function tearDown(){
    }
    public function test_init() {
    }
    
    public function test_add() {
        $apkid="1";
        $url="http://loacalhost/cocosplay/2";
        $ver="111";
        $this->cp_game_revision_channel_config_model->add($apkid,$url,$ver) ;
        $sql="select apk_id, ver, url ,active from cp_game_revision_channel_config where apk_id=? and ver=?" ;
        $res=$this->db->query($sql,$apkid,$ver);
        $result=$res->row();
        $this->assertEqual($result->apk_id, $apkid);
        $this->assertEqual($result->url, $url);
        $this->assertEqual($result->ver, $ver);
    }
    public function test_delete() {
        $sql1="select max(id) as id from cp_game_revision_channel_config";
        $res0=$this->db->query($sql1);
        $result=$res0->row();
        $id=$result->id;     //获取表中最大的id值
        echo "被删除的id=".$id;
        $this->cp_game_revision_channel_config_model->delete($id);
        
        $sql="select id from cp_game_revision_channel_config where id=?" ;
        $res2=$this->db->query($sql,$id);
        $result2=$res2->row();
        $this->assertFalse($result2, "无结果，delete()成功！");
        
    }

    public function test_max_ver() {
        $apk_id="1";
        $sql="select ver from cp_game_revision_channel_config where apk_id=? ";
        $res=$this->db->query($sql,$apk_id);
        $result = $res->result_array();
        array($arry);
        for($i=0;$i<count($result);$i++){
           $arry[$i]= $result[$i]['ver'];
        }
        usort($arry);
        $result2=$this->cp_game_revision_channel_config_model->max_ver($apk_id);
        $this->assertEqual($result2['ver'], end($arry),'成功取回最大ver:'.$result2['ver']);
    }
}