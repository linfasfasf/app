<?php
class test_capi_manifestdir_controller extends APIWebTestCase
{
	public function __construct()
	{
		parent::__construct('CAPI/MANIFESTDIR');
        $this->load->helper(array('url','simple_test/simple_test'));
        $this->load->model('game_management/cp_game_info_model');
	}

	public function setUp()
	{
    }

    public function tearDown()
	{
    }

    public function get_channel_game_list(){
        $this->load->model('game_management/cp_channel_info_model');
        $this->load->model('game_management/cp_chn_game_info_model');
        $chns = $this->cp_channel_info_model->get_all();
        // accessible 表示是否加了 channel_id = 9999
        $fields  = array_flip(array( "chn","game_id","game_key","pkg","ver","mode","visible", "old_data_flag"));
        $result = array();
        foreach($chns as $chn) {
            $chnid = $chn['channel_id'];
            $gamelist = $this->cp_chn_game_info_model->get_channel_game_list($chnid);
            foreach($gamelist as $game){
                // chn_id, game_key, game_id, pkg, ver, mode, visibible
                $game_id = $game['game_id'];
                $game_key = $game['game_key'];
                $pkg = $game['package_name'];
                $ver = $game['package_ver_code'];
                $mode = $game['game_mode'];
                $visible = $game['is_visiable'];
                $accesible = $game['old_data_flag'];
                $result[]= array($chnid,$game_id,$game_key,$pkg,$ver,$mode,$visible, $accesible);
            }
        }

        return array($fields, $result);
    }

    function test_manifest(){
        $uri = '/capi/api/manifest';
        list($fields, $data) = $this->get_channel_game_list();
        foreach($data as $record) {
            //if(!$record[$fields['visible']]) continue; 
            $visible = $record[$fields['visible']];
            if($record[$fields['mode']]){
                $mode = '1';
            }else{
                $mode= '0';
                }
            if($mode=='0') continue; // 试玩版本没有 manifest
            if($record[$fields['pkg']] == '' )  continue;  // 新的数据没有 pkg
            $requestparams = array(
                'pkg'=> $record[$fields['pkg']],
                'mode'=> $mode, 
                'ver'=> $record[$fields['ver']],
                'chn'=> $record[$fields['chn']],
                'gamekey'=> $record[$fields['game_key']],
            );
            $game = $this->cp_game_info_model->where(array('game_id'=>$record[$fields['game_id']]))->get();
            var_dump($record);
            $response = $this->send_request($uri, $requestparams, 'get');
            if($record[$fields['old_data_flag']]!='9999'){
                return $this->assertText('305');  // 屏敝旧接口
            }
            if($mode=='0'){
                // 历史原因，有些 试玩的仍有 manifest_url 信息。 
                //$this->assertText('304');
            }else{
                $this->assertText('manifest_url');
            }
        }
    }
    function test_manifestdir(){
        $uri = '/capi/api/manifestdir';
        list($fields, $data) = $this->get_channel_game_list();
        foreach($data as $record) {
            $game_info = $this->cp_chn_game_info_model->get_channel_game_detail_gamekey($record[$fields['chn']],$record[$fields['game_key']],NULL);
            //if(!$record[$fields['visible']]) continue; 
            $visible = $record[$fields['visible']];
            if($record[$fields['mode']]){
                $mode = '1';
            }else{
                $mode= '0';
                }
            if($mode=='0') continue; // 试玩版本没有 manifest
            if($record[$fields['pkg']] == '' )  continue;  // 新的数据没有 pkg
            $requestparams = array(
                'pkg'=> $record[$fields['pkg']],
                'mode'=> $mode, 
                'ver'=> $record[$fields['ver']],
                'chn'=> $record[$fields['chn']],
                'gamekey'=> $record[$fields['game_key']],
            );
            var_dump(site_url($uri) .'?'. $this->request_builder($requestparams));
            $this->setMaximumRedirects(0);
            $response = $this->send_request($uri, $requestparams, 'get');
            if($record[$fields['old_data_flag']]!='9999'){
                return $this->assertText('305');  // 屏敝旧接口
            }
            if($record[$fields['mode']]==4){ // 只有非独立包才判断在渠道中的可见性设置
                $this->assertResponse(302);
            }else{
                if(!$visible){
                    $this->assertText('invisible');
                }elseif($game_info['is_maintain'] == 1){
                    $this->assertText('310');
                }else{
                    $this->assertResponse(302);
                }
            }
        }
    }
    function test_manifestzipdir(){
        $uri = '/capi/api/manifestzipdir';
        list($fields, $data) = $this->get_channel_game_list();
        foreach($data as $record) {
            //if(!$record[$fields['visible']]) continue; 
            $game_info = $this->cp_chn_game_info_model->get_channel_game_detail_gamekey($record[$fields['chn']],$record[$fields['game_key']],NULL);
            $visible = $record[$fields['visible']];
            if($record[$fields['mode']]){
                $mode = '1';
            }else{
                $mode= '0';
                }
            if($mode=='0') continue; // 试玩版本没有 manifest
            if($record[$fields['pkg']] == '' )  continue;  // 新的数据没有 pkg
            $requestparams = array(
                'pkg'=> $record[$fields['pkg']],
                'mode'=> $mode, 
                'ver'=> $record[$fields['ver']],
                'chn'=> $record[$fields['chn']],
                'gamekey'=> $record[$fields['game_key']],
            );
            var_dump(site_url($uri) .'?'. $this->request_builder($requestparams));
            $this->setMaximumRedirects(0);
            $response = $this->send_request($uri, $requestparams, 'get');
            if(!$record[$fields['old_data_flag']] == '9999') {
                $this->assertText('305');
                continue; 
            }else{
                if($record[$fields['mode']]==4){ // 只有非独立包才判断在渠道中的可见性设置
                    $this->assertResponse(302);
                }else{
                    if(!$visible){
                    $this->assertText('invisible');
                    }elseif($game_info['is_maintain'] == 1){
                        $this->assertText('310');
                    }else{
                        $this->assertResponse(302);
                    }
                }
            }
        }
    }
}
