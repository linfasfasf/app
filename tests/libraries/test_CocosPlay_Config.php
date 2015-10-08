<?php
class Test_cocosplay_config extends CodeIgniterUnitTestCase
{

    public function __construct()
    {
        //error_reporting(E_ERROR | E_WARNING | E_PARSE);
        parent::__construct('CocosPlay Config');
        $this->config = CocosPlay_Config::get_instance();
        $this->required_fields_list = array(
            'system/cocosplay/download_base_url' => 'http://downplayer.coco.cn/',
            'system/cocosplay/log_dir' => '/home/www/logs',
            'system/ip_limit/ip_list' => '121.207.240.77,218.5.2.219,110.87.60.97,220.250.21.82,120.35.5.217,110.85.58.1,220.181.158.76,123.125.74.254,123.125.74.246,123.125.74.247,59.56.21.24,120.42.46.251,120.42.88.117',
            'system/ip_limit/ip_sec_list' => '192.168.1|10.10.2|::1|192.168.52',
            'system/redis/ccp' => 'redis.ccpma.punchbox.usr:63790',
            'system/switchinfo/debug' =>'1',
            'system/cdn/ftp_hostname' => '119.90.1.204',
            'system/cdn/rsync_hostname' => '119.90.1.204',
            'system/cdn/ftp_username' => 'cocoachina4',
            'system/cdn/rsync_username' => 'cocoachina',
//            'system/cdn/password' => '',
        );
    }

    public function setUp()
    {
        $this->orivalues = array();
        foreach($this->required_fields_list as $path => $value){
            $this->orivalues[$path] = $this->config->get_value($path);
            $this->config->set_value($path, $value);
        }
    }

    public function tearDown()
    {
        foreach($this->orivalues as $path => $value) {
            $this->config->set_value($path, $value);
        }
    }

    public function test_required_fields(){
        foreach(array_keys($this->required_fields_list) as $path){
            $this->assertTrue($this->config->get_value($path));
        }
    }

    public function test_required_values(){
        foreach ($this->required_fields_list as $path => $value){
            $this->assertEqual($this->config->get_value($path) , $value);
        }
    }

    public function test_set_value(){
        $key = 'system/cdn/ftp_username';
        $ori = $this->required_fields_list[$key];
        $new = 'test abc';
        $this->config->set_value($key, $new);
        $this->assertEqual($this->config->get_value($key), $new);
        $this->config->set_value($key, $ori);
        $this->assertEqual($this->config->get_value($key), $ori);
    }

    public function test_delete(){
        $key = 'test/test_key';
        $value = 'test abc';
        $ensure = TRUE; 
        $this->config->set_value($key, $value, $ensure);
        $this->assertEqual($this->config->get_value($key), $value);
        $this->config->delete($key);
        $this->assertEqual($this->config->get_value($key), FALSE);
    }
}

/* Location: ./tests/libraries/test_CocosPlay_Config */
