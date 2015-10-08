<?php
class Test_cp_game_revision_info_model extends CodeIgniterUnitTestCase
{

	public function __construct()
	{
		parent::__construct('cp_game_revision_info_model');
        $this->load->model('game_management/cp_game_revision_info_model');
	}

	public function setUp()
	{
        /*
		$this->db->truncate('users');

		$insert_data = array(
			    'user_email' => 'demo'.$this->rand.'@demo.com',
			    'user_username' => 'test_'.$this->rand,
			    'user_password' => 'demo_'.$this->rand,
			    'user_join_date' => time(),
				'user_group'	=> 1
			);
		//$user_id = $this->users_model->add_user($insert_data);
		//$this->user = $this->users_model->get_user($user_id);
        //
        //*/
    }

    public function tearDown()
	{

    }

	public function test_model_table_configuration()
	{
        $this->assertEqual('cp_game_revision_info', $this->cp_game_revision_info_model->table);
	}

    public function test_delete_revisions_of_game(){
        // WARNING: 不要上传这个文件，有操作的危险
        $game_id = 30;
        $result = $this->cp_game_revision_info_model->delete_revisions_of_game($game_id);
        $this->assertEqual(TRUE, $result);
        /*
        $game_id = 10;
        $result = $this->cp_game_revision_info_model->delete_revisions_of_game($game_id);
         */
    }

    /**
     * 测试 manifest_version 可以 超过 2^31 - 1 = 2,147,483,647
     */
    public function test_update_manifest_version() {
        $manifest_version = 20150114165739;
        //$info = $this->cp_game_revision_info_model->get($revision_id);
        $info = $this->cp_game_revision_info_model->order_by('id desc')->get();
        $revision_id = $info['id'];
        echo $revision_id; 
        $ori_manifest_version = $info['manifest_version'];
        $result = $this->cp_game_revision_info_model->update($revision_id, array('manifest_version'=>0));
        $info = $this->cp_game_revision_info_model->get($revision_id);
        $this->assertEqual(0, $info['manifest_version']);
        $result = $this->cp_game_revision_info_model->update($revision_id, array('manifest_version'=>$manifest_version));
        $info = $this->cp_game_revision_info_model->get($revision_id);
        $this->assertEqual($manifest_version, $info['manifest_version']);
        $result = $this->cp_game_revision_info_model->update($revision_id, array('manifest_version'=>$ori_manifest_version));
        $info = $this->cp_game_revision_info_model->get($revision_id);
        $this->assertEqual($ori_manifest_version, $info['manifest_version']);
    }
}

/* End of file test_users_model.php */
/* Location: ./tests/models/test_users_model.php */
