<?php
class test_capi_gamepackage_controller extends APIWebTestCase
{
	protected $rand = '';

	public function __construct()
	{
		parent::__construct('CAPI/GAMEPACKAGE');
        $this->load->helper(array('url','simple_test/simple_test'));
        $this->host = site_url();
	}

	public function setUp()
	{
    }

    public function tearDown()
	{
    }

    public function test_welcome()
    {
        $page = $this->get(site_url());
        $this->assertTrue($page);
    }

   // /capi/api/switchinfo 
    public function test_switchinfo()
    {
        $uri = '/capi/api/switchinfo';
        $url = site_url( $uri );
        $response = $this->get($url);
        $this->assertPattern('/\{"result":\{"res":"0","msg":"ok"\},"data":\{"debug":"."\}\}/');
    }

    public function get_channel_game_list(){
        $this->load->model('game_management/cp_chn_game_info_model');
        $chns = $this->db->get_where("cp_channel_info",array("del_flag"=>0))->result_array();
        $fields  = array_flip(array( "chn","game_id","game_key","pkg","ver","mode","visible","is_old_data"));
        $result = array();
        foreach($chns as $chn) {
            $chnid = $chn['channel_id'];
            $gamelist = $this->cp_chn_game_info_model->get_channel_game_list($chnid, 0, 0 , FALSE, TRUE);
            foreach($gamelist as $game){
                // chn_id, game_key, game_id, pkg, ver, mode, visibible
                $game_id = $game['game_id'];
                $game_key = $game['game_key'];
                $pkg = $game['package_name'];
                $ver = $game['package_ver_code'];
                $mode = $game['game_mode'];
                $visible = $game['is_visiable'];
                $is_old_data = $game['old_data_flag']; //值为9999 则为旧数据
                $result[]= array($chnid,$game_id,$game_key,$pkg,$ver,$mode,$visible,$is_old_data);
            }
        }

        return array($fields, $result);
    }

    function test_gamepackage(){
        $uri = '/capi/api/gamepackage';
        list($fields, $data) = $this->get_channel_game_list();
        foreach($data as $record) {
            // 2014/12/22 可见不可见兼容不兼容都要返回值
            //if(!$record[$fields['visible']]) continue; 
            $visible = $record[$fields['visible']];
            if($record[$fields['mode']]){
                $mode = '1';
            }else{
                $mode= '0';
                }

            if($record[$fields['mode']] == '7') continue; //runtime 游戏
            if($record[$fields['pkg']] == '' )  continue;  // 新的数据没有 pkg
            $requestparams = array(
                'pkg'=> $record[$fields['pkg']],
                'mode'=> $mode, 
                'ver'=> $record[$fields['ver']],
                'chn'=> $record[$fields['chn']],
                'gamekey'=> $record[$fields['game_key']],
            );
            $response = $this->send_request($uri, $requestparams, 'get');
            if($record[$fields['is_old_data']] == 9999)
            {
                echo "<br>旧数据<br>";
                $this->assertText('full_game_download_url');
            }
            else
            {
                echo "<br>新数据<br>";
                $this->assertText('"msg":"No such info"');
            }
            
            
            
        }
    }
}
