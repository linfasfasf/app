<?php
class Test_cp_chn_game_info_model extends CodeIgniterUnitTestCase
{

	public function __construct()
	{
		parent::__construct('cp_chn_game_info_model');
        $this->load->model('game_management/cp_game_info_model');
        $this->load->model('game_management/cp_chn_game_info_model');
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
        $this->assertEqual('cp_chn_game_info', $this->cp_chn_game_info_model->table);
	}

    public function test_channel_game_list(){
        //$this->load->database("test");
        $result = $this->cp_chn_game_info_model->get_channel_game_list('111111',0,1,TRUE,TRUE);
        $result_count = count($result);
        $this->assertEqual($result_count,1);
        $result = $this->cp_chn_game_info_model->get_channel_game_list('111111',0,0,FALSE,TRUE);
        $all_result_count = count($result);
        $result = $this->cp_chn_game_info_model->get_channel_game_list('111111',0,0,TRUE,TRUE);
        $only_visible_count = count($result);
        $this->assertTrue($all_result_count > $only_visible_count);
    }

    /**
     * 独立包游戏从apiv2 中请求时可以不传 chn
     *
     * 内部实现是：不传 chn 时，默认请求 chn=999997 
     * 999997 是一个虚拟的渠道号. 
     * 通过这种方式将独立包与其他的游戏资源请求统一起来
     */
    public function test_channel_game_detail_indep() {
        
        $db = $this->load->database("", TRUE);
        $sql = "select rev.game_id from cp_game_info game , cp_game_revision_info rev where rev.game_id=game.game_id and rev.is_published=1 and game.game_mode=4 order by rev.id desc";
        $query = $db->query($sql);
        $games = $query -> result_array();
        if(empty($games)) return; 
        foreach($games as $game) {
            $result = $this->cp_chn_game_info_model->get_channel_game_detail('999997',$game['game_id']);
            $this->assertTrue(array_key_exists('game_order',$result[0]));
            $result = $this->cp_chn_game_info_model->get_channel_game_detail('999997',$game['game_id'], $result[0]['hot_versioncode']);
            $this->assertTrue(array_key_exists('game_order',$result[0]));
        }
    }

    /**
     * 非独立包游戏
     */
    public function test_channel_game_detail_nonindep() {
        $db = $this->load->database("", TRUE);
        $sql = "select game_id, channel_id, game_mode from published_channel_game_info_view";
        $query = $db->query($sql);
        $games = $query -> result_array();
        if(empty($games)) return; 
        foreach($games as $game) {
            if($game['game_mode'] == 7 ) continue; // 不处理 runtime 游戏
            $result = $this->cp_chn_game_info_model->get_channel_game_detail($game['channel_id'],$game['game_id']);
            if(!empty($result))
            {
                $this->assertTrue(array_key_exists('game_order',$result[0]));
                $result = $this->cp_chn_game_info_model->get_channel_game_detail($game['channel_id'],$game['game_id'], $result[0]['hot_versioncode']);
                $this->assertTrue(array_key_exists('game_order',$result[0]));
            }
            else {
                var_dump($game);
                $this->assertTrue(FALSE); // fail
            }
        }
    }

    function test_113() {
        $result = $this->cp_chn_game_info_model->get_channel_game_detail(100113, 197);
        //var_dump($result);
    }
}

/* End of file test_users_model.php */
/* Location: ./tests/models/test_users_model.php */
