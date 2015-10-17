<?php
class Test_cdn_manager extends CodeIgniterUnitTestCase
{

    public function __construct()
    {
        parent::__construct('CDN Manager');
    }

    public function setUp()
    {
    }

    public function tearDown()
    {
    }


    public function test_this(){
        $Manager = new CDN_Manager();
        echo $Manager->rsync_local_cdn();
    }
}

/* Location: ./tests/libraries/test_CocosPlay_Config */
