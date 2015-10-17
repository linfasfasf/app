<?php
class Test_Redis_Manager extends CodeIgniterUnitTestCase
{

    public function __construct()
    {
        //error_reporting(E_ERROR | E_WARNING | E_PARSE);
        parent::__construct('Redis Manager');
    }

    public function setUp()
    {
    }

    public function tearDown()
    {
    }

    public function test_redis_config()
    {
        // test_redis is up and running
        $config = CocosPlay_Config::get_instance();
        $redis_config =  $config->get_value('system/redis/ccp', FALSE);
        $this->assertTrue($redis_config);
    }
    public function test_redis_upandrunning()
    {
        $config = CocosPlay_Config::get_instance();
        $redis_config =  $config->get_value('system/redis/ccp', FALSE);
        //$this->assertTrue($redis_config);
        $Redis = new Redis();
        $redis_server = explode(":", $redis_config);
        $redis_server[1] = isset($redis_server[1]) ? $redis_server[1] : "6379";
        $Redis->connect($redis_server[0], $redis_server[1]);
        $key = 'SET_SMOKETEST';
        $value = 'TEST';
        $Redis->sAdd($key, $value);
        $getvalue = $Redis->sPop($key);
        $this->assertEqual($value, $getvalue);
        
    }
    public function test_Redis_manager()
    {
        $redis = Redis_Manager::get_instance();
        $file = rtrim(rtrim(FCPATH,'/'),'\\').'/uploads/syncdir/test.test';
        $handler = fopen($file, 'w');
        fwrite($handler, 'abc');
        fclose($handler);
        $redis->enqueue_file($file, '/syncdir/test.test');
        //sleep(6);
    }

}
