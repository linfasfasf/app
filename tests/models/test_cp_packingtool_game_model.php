<?php
class Test_packingtool_game_model extends CodeIgniterUnitTestCase
{

	public function __construct()
	{
		parent::__construct('cp_packingtool_game_model');
        $this->load->model('cocos_packingtool/cp_packingtool_game_model');
	}

	public function setUp()
	{
    }

    public function tearDown()
	{

    }

	public function test_model_table_configuration()
	{
        $this->assertEqual('cp_contentprovider_games', $this->cp_packingtool_game_model->table);
	}

    public function test_update_resource() {
        $user_id = '1';
        $game_key = '26';
        $game_version_code = '1';
        $game_ver_name = '1.0.0';
        $result = $this->cp_packingtool_game_model->update_resource($user_id, $game_key, $game_version_code, array('game_version_name'=>$game_ver_name));
        $this->assertTrue($result);
    }

    public function test_update_extension() {
        $user_id = '1';
        $game_key = '26';
        $game_version_code = '1';
        $game_ver_name = '1.0.0';
        $extension = 'asdfaklsdjf';
        $result = $this->cp_packingtool_game_model->update_resource($user_id, $game_key, $game_version_code, array('extension'=>$extension));
        $this->assertTrue($result);
    }
}

/* End of file test_users_model.php */
/* Location: ./tests/models/test_users_model.php */
