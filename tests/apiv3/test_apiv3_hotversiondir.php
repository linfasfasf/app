<?php
class test_apiv3_hotversiondir extends APIWebTestCase
{
    public function __construct() {
        parent::__construct('apiv3');
        $this->load->helper(array('url', 'simple_test/simple_test'));
        $this->load->model('game_management/cp_game_revision_chafen_model');
    }

    public function setUp() {
    }

    public function tearDown() {
    }
        
    function test_hotversiondir() {
        $uri = '/capi/apiv3/hotversiondir';
    }
}
