<?php
class Test_cp_channel_info_model extends CodeIgniterUnitTestCase
{

	public function __construct()
	{
		parent::__construct('cp_channel_info_model');
        $this->load->model('game_management/cp_channel_info_model');
        $this->load->helper('simple_test/simple_test');
	}

	public function setUp()
	{
    }

    public function tearDown()
	{
    }

	public function test_model_table_configuration()
	{
        $this->assertEqual('cp_channel_info', $this->cp_channel_info_model->table);
	}

    public function test_delete(){
//        $this->cp_channel_info_model->delete(24);
    }
}

/* End of file test_users_model.php */
/* Location: ./tests/models/test_users_model.php */
