<?php
class test_cp_game_info_model extends CodeIgniterUnitTestCase
{
	protected $rand = '';

	public function __construct()
	{
		parent::__construct('cp_game_info_model');

        $this->load->model('game_management/cp_game_info_model');
        $this->load->model('game_management/cp_game_revision_info_model');
        $this->load->helper('simple_test/simple_test');
        $this->test_data = get_test_data_template();
	}

	public function setUp()
	{
        $result = setup_test_data($this->test_data);
        $this->game_id = $result['game_id'];
        $this->revision_id = $result['revision_id'];
    }

    public function tearDown()
	{
        $result = $this->cp_game_info_model->delete_game($this->game_id);
    }

	public function test_model_table_configuration()
	{
        $this->assertEqual('cp_game_info', $this->cp_game_info_model->table);
	}

    public function test_get_package_ver_code()
    {
        $result = $this->cp_game_info_model->get_active_package_version_code($this->game_id); 
        $this->assertEqual(999,$result);
    }

    public function test_get_resource_url_of_revision(){
        $std_package_name = $this->test_data['package_name'];
        $resource_pack_name = $this->test_data['resource_pack_name'];
        $package_ver = 999;
        $resource_url = $this->cp_game_info_model->get_resource_url_of_revision($std_package_name, $resource_pack_name, $package_ver);
        // 
        $this->assertEqual($this->test_data['resource_url'], $resource_url);
    }
}

/* End of file test_users_model.php */
/* Location: ./tests/models/test_users_model.php */
