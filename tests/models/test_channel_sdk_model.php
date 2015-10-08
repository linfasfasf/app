<?php
class test_channel_sdk_model extends CodeIgniterUnitTestCase
{
    public function __construct()
    {
        parent::__construct('channel_sdk_model');
        $this->load->model('common/channel_sdk_model');
    }

    public function setUp()
    {
    }

    public function tearDown()
    {
    }

    function test_add() {
        $version = '1.3.4.2';
        $downloadurl = 'channelsdk/1.2.5/efg.jar';
        $this->channel_sdk_model->delete_sdk($version);
        $id = $this->channel_sdk_model->add_sdk($version, $downloadurl);
        $sdks = $this->channel_sdk_model->get_sdks($version);
        $this->assertTrue($sdks);
        var_dump($sdks);
        $this->channel_sdk_model->delete_sdk($version);
        $sdks = $this->channel_sdk_model->get_sdks($version);
        $this->assertFalse($sdks);
    }

    function test_set_channels() {
        $channels = array( 
            '111111',
            '100114',
            '100113',
            '100113',
            '100113',
            '100111'
        );
        $version = '4.9.2.5';
        $downloadurl = 'channelsdk/1.2.5/efg.jar';
        $this->channel_sdk_model->delete_sdk($version);
        $id = $this->channel_sdk_model->add_sdk($version, $downloadurl);
        //$sdks = $this->channel_sdk_model->get_sdks($version);
        $newid = $this->channel_sdk_model->set_channels($id, $channels);
        $result = $this->channel_sdk_model->get_channel_status(111111);
        $this->assertTrue($result);
        if($result) {
            $this->assertEqual($result[0]['channel_id'], 111111);
            $this->assertEqual($result[0]['version'], $version);
        }
        $this->channel_sdk_model->delete_sdk($version);
        $result = $this->channel_sdk_model->get_channel_status(111111);
        $this->assertTrue($result);
        if($result) {
            $this->assertNotEqual($result[0]['version'], $version);
        }
    }
}
//EOF
