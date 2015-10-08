<?php
class test_apiv3_updatechafendir extends APIWebTestCase
{
    //TODO:增加托管包以独立包的方式请求自己的公共资源的case
    protected $rand = '';

    public function __construct() {
        parent::__construct('apiv3_updatechafen');
        $this->load->helper(array('url', 'simple_test/simple_test'));
        $this->load->model('game_management/cp_game_revision_info_model');
        $this->load->model('game_management/cp_game_revision_channel_resources_model');
        $this->host = site_url();
    }

    public function setUp() {
    }

    public function tearDown() {
    }
        
    protected function get_oldv_and_newv($revision_id) 
    {
        $sql = "select support_ver_code 
            from cp_game_revision_chafen 
            where chafen_url is not null and chafen_url != '' ";
        $query = $this->db->query($sql,array($revision_id));
        $info = $query->result_array();
        $result = array();
        foreach($info as $oldv) {
            $result[] = $oldv['support_ver_code']; 
        }
        return $result;
    }

    function test_updatechafendir() {
        $uri = '/capi/apiv3/updatechafendir';
        list($fields, $data) = $this->get_channel_game_list();
        foreach($data as $record) {
            $chn_test_duration = $record[$fields['chn_test_duration']];
            $visible = $record[$fields['visible']];
            $is_indep = FALSE;
            $mode = $record[$fields['mode']];
            if($mode == 4)
            {
                $is_indep = TRUE;
                $indep = 1;
            }
            else
            {
                $indep = 0;
            }
            if(!$is_indep)
                $game_info = $this->cp_chn_game_info_model->get_channel_game_detail_gamekey($record[$fields['chn']],$record[$fields['game_key']],$record[$fields['ver']]);
            else
                $game_info = $this->cp_chn_game_info_model->get_channel_game_detail_gamekey('999997',$record[$fields['game_key']],$record[$fields['ver']]);
            $gamemode = $this->game_mode_trans($mode);
            if($gamemode == 5 && $chn_test_duration != -1)
            {
                //托管包试玩模式
                $gamemode = 6;
            }
            $this->load->model('game_management/cp_game_revision_info_model');
            $newv = $game_info['hot_versioncode'];
            $oldvs = $this->get_oldv_and_newv($record[$fields['game_key']]); 
            foreach($oldvs as $oldv) {
                $requestparams = array(
                    'gamekey' => $record[$fields['game_key']],
                    'chn'=> $record[$fields['chn']],
                    'oldv' => $oldv,
                    'newv' => $newv,
                    'indep' => $indep,
                );
                $this->load->model('game_management/cp_game_revision_chafen_model');
                $chafen = $this->cp_game_revision_chafen_model->select('chafen_url')->where(array('revision_id'=>$game_info['revision_id'],'support_ver_code'=>$oldv))->get();
                $version_error = FALSE;
                if(empty($chafen))
                {
                    $version_error = TRUE;
                } 
                $this->setMaximumRedirects(0);
                $response = $this->send_request($uri, $requestparams, 'get');
                if($is_indep){
                    if($version_error)
                        $this->assertText('305');
                    else
                        $this->assertResponse(302);
                }else{
                    if($visible == 0){
                        $this->assertText ('"msg":"invisible"');
                    }elseif ($game_info['is_maintain'] == 1) {
                        $this->assertText('310');
                    } else {
                        if ($version_error)
                            $this->assertText('305');
                        else
                            $this->assertResponse(302);
                    }
                }
            } // oldv iteration
        }
    }
    
}
