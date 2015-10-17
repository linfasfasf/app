<?php
class test_apiv3 extends APIWebTestCase
{
    //TODO:增加托管包以独立包的方式请求自己的公共资源的case
    protected $rand = '';

    public function __construct() {
        parent::__construct('apiv3');
        $this->load->helper(array('url', 'simple_test/simple_test'));
        $this->load->model('game_management/cp_game_revision_info_model');
        $this->load->model('game_management/cp_game_revision_channel_resources_model');
        $this->host = site_url();
    }

    public function setUp() {
        
    }

    public function tearDown()
	{
    }

        function test_gamepackage(){
        $uri = '/capi/apiv3/gamepackage';
        list($fields, $data) = $this->get_channel_game_list();
        foreach($data as $record) {
            //if(!$record[$fields['visible']]) continue; 
            $chn_test_duration = $record[$fields['chn_test_duration']];
            $visible = $record[$fields['visible']];
            $is_indep = FALSE;
            echo $mode = $record[$fields['mode']];
            if($mode == 4)
            {
                $is_indep = TRUE;
                $indep = 1;
            }
            else
            {
                $indep = 0;
            }
            $gamemode = $this->game_mode_trans($mode);
            if($gamemode == 5 && $chn_test_duration != -1)
            {
                //托管包试玩模式
                $gamemode = 6;
            }
            $requestparams = array(
                'gamekey' => $record[$fields['game_key']],
                'indep' => $indep,
                'ver'=> $record[$fields['ver']],
                'chn'=> $record[$fields['chn']],
                'gi'=> $record[$fields['game_id']],
            );
            $revision_id = $record[$fields['revision_id']];
            $sql = "SELECT icon_url FROM cp_game_revision_channel_resources res , cp_game_revision_apk apk WHERE apk.revision_id = ? AND apk.channel_id = ? AND apk.revision_channel_resources_id = res.id";
            $result = $this->db->query($sql,array($revision_id,$record[$fields['chn']]))->result_array();
            if(empty($result))
            {
                $res = '';
            }
            else
            {
                $res = $result[0];
            }
            if($record[$fields['chn']] != 111111)                continue;
            if($record[$fields['old_data_flag']] == 9999)
                echo "旧数据";
            else
                echo "新数据";
            echo "<br>";
            var_dump( site_url($uri) . '?'. $this->request_builder($requestparams));
            $response = $this->send_request($uri, $requestparams, 'get');
            // 不管独立包与否
            // 不管可见不可见，都返回结果
            // 不管维护与否，不管兼容与否都返回结果
            $this->assertText('download_url');
            switch ($mode) {
                case 0:
                    $this->assertText('"game_mode":0');
                    break;
                case 1:
                {
                    if($gamemode == 6)
                    {
                        $this->assertText ('"game_mode":6');
                        $this->assertText($chn_test_duration);
                    }
                    else {
                        $this->assertText ('"game_mode":5');
                    }
                    //托管包检查是否设置了渠道资源
                    if(!empty($res['icon_url']))
                    {
                        $a = explode('/', $res['icon_url']);
                        echo $file_dir = $a[4];
                        $this->assertText($file_dir);
                    }
                }
                default:
                    break;
            }
        }
        }
    
    function test_manifestzipdir(){
        $uri = '/capi/apiv3/manifestzipdir';
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
            $requestparams = array(
                'gamekey' => $record[$fields['game_key']],
                'ver'=> $record[$fields['ver']],
                'chn'=> $record[$fields['chn']],
                'indep' => $indep,
            );
            var_dump(site_url($uri). '?'. $this->request_builder($requestparams));
            $this->setMaximumRedirects(0);
            $response = $this->send_request($uri, $requestparams, 'get');
            switch($mode){
            case 0:
                $this->assertText('305');
                break;
            case 4:
                $this->assertResponse(302);
                break;
            case 1:
                if(!$visible){
                    $this->assertText ('"msg":"invisible"');
                }elseif($game_info['is_maintain'] == 1){
                    $this->assertText('310');
                }else{
                    $this->assertResponse(302);
                }
            default:
            }
        }
    }
    
    function test_resmdfdir(){
        $uri = '/capi/apiv3/resmdfdir';
        list($fields, $data) = $this->get_channel_game_list();
        foreach($data as $record) {
            $chn_test_duration = $record[$fields['chn_test_duration']];
            $visible = $record[$fields['visible']];
            $is_indep = FALSE;
            $mode = $record[$fields['mode']];
            $game_id = $record[$fields['game_id']];
            $ver = $record[$fields['ver']];
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
            $requestparams = array(
                'gamekey' => $record[$fields['game_key']],
                'ver'=> $ver,
                'chn'=> $record[$fields['chn']],
                'indep' => $indep,
            );
            var_dump(site_url($uri) . '?' . $this->request_builder($requestparams));
            $this->setMaximumRedirects(0);
            $response = $this->send_request($uri, $requestparams, 'get');
            $record = $this->cp_game_revision_info_model->select(array('mdf_url'))->where(array('game_id'=>$game_id,'package_ver_code'=>$ver,'is_published'=>1))->get();
            if($is_indep){
                if(!$record['mdf_url']){
                    $this->assertText('305');
                }else{
                    $this->assertResponse(302);
                }
            }
            // 非独立包
            else{
                if($visible==0){
                    // 不可见则显示不可见， 独立包不考虑可不可见
                    $this->assertText ('"msg":"invisible"');
                }elseif($game_info['is_maintain'] == 1 ){
                    $this->assertText('310');
                }elseif(!$record['mdf_url']){
                    // 没有值
                    $this->assertText('305');
                }else{
                    $this->assertResponse(302);
                }
            }
        }
    }
    
    protected function get_oldv_and_newv($gamekey) 
    {
        $sql = "select rev.package_ver_code , rev.ver_last ,rev.chafen_url "
                . "from cp_game_info gm , cp_game_revision_info rev "
                . "where gm.game_id = rev.game_id "
                . "and gm.game_key = ? and rev.is_published = 1 "
                . "order by rev.package_ver_code asc";
        $query = $this->db->query($sql,array($gamekey));
        $info = $query->result_array();
        $oldv = $info[0];
        $newv = end($info);
        return array($oldv,$newv);
    }
    function test_updatechafendir(){
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
            list($oldv,$newv) = $this->get_oldv_and_newv($record[$fields['game_key']]); 
            $requestparams = array(
                'gamekey' => $record[$fields['game_key']],
                'chn'=> $record[$fields['chn']],
                'oldv' => $oldv['package_ver_code'],
                'newv' => $newv['package_ver_code'],
                'indep' => $indep,
            );
            var_dump($this->request_builder($requestparams));
            $this->load->model('game_management/cp_game_revision_chafen_model');
            $chafen = $this->cp_game_revision_chafen_model->select('chafen_url')->where(array('revision_id'=>$game_info['revision_id'],'support_ver_code'=>$oldv['package_ver_code']))->get();
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
            }
            elseif($visible == 0)
                $this->assertText ('"msg":"invisible"');
            elseif ($game_info['is_maintain'] == 1) {
                $this->assertText('310');
            } else {
                if ($version_error)
                    $this->assertText('305');
                else
                    $this->assertResponse(302);
            }
        }
    }
    
    function test_cpkresourcedir(){
        $uri = '/capi/apiv3/cpkresourcedir';
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
            $this->load->model('game_management/cp_game_info_model');
            $this->load->model('game_management/cp_game_revision_resources_model');
            $no_cpk = FALSE;
            $cpkname = 'scene_a.cpk';
            $resources = $this->cp_game_revision_resources_model->select('resource_pack_name')->where(array('revision_id'=>$game_info['revision_id']))->get();
            if(empty($resources))
            {
                $no_cpk = TRUE;
            }
            else
            {
                $cpkname = $resources['resource_pack_name'];
            }
            $requestparams = array(
                'gamekey' => $record[$fields['game_key']],
                'indep' => $indep, 
                'ver'=> $record[$fields['ver']],
                'chn'=> $record[$fields['chn']],
                'cpk'=>$cpkname,
                'indep' => $indep,
            );
            var_dump(site_url($uri) . '?'.  $this->request_builder($requestparams));
            echo $game_info['revision_id'];
            //存在不上传cpk文件的托管包(没有分场景的情况).
            //若有上传cpk，则至少有scene_a.cpk(第一个场景)
            //scene_b.cpk手工分场景
            //scene_c.cpk剩下的未分场景，均分剩下的场景
            var_dump($resources);  
            $this->setMaximumRedirects(0);
            $response = $this->send_request($uri, $requestparams, 'get');
            if($is_indep)
            {
                if($no_cpk)
                    $this->assertText('305');
                else
                    $this->assertResponse(302);
            }
            elseif($visible == 0)
                $this->assertText ('"msg":"invisible"');
            elseif($game_info['is_maintain'] == 1)
                $this->assertText ('310');
            elseif($mode == '0'){
                $this->assertText('305');
            }
            elseif($no_cpk)
                $this->assertText ('305');
            else
                $this->assertResponse(302);
        }
    }
    
    function test_picdir(){
        $uri = '/capi/bgv3/picdir';
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
            $gamemode = $this->game_mode_trans($mode);
            if($gamemode == 5 && $chn_test_duration != -1)
            {
                //托管包试玩模式
                $gamemode = 6;
            }
            if(!$is_indep)
                $game_info = $this->cp_chn_game_info_model->get_channel_game_detail_gamekey($record[$fields['chn']],$record[$fields['game_key']],$record[$fields['ver']]);
            else
                $game_info = $this->cp_chn_game_info_model->get_channel_game_detail_gamekey('999997',$record[$fields['game_key']],$record[$fields['ver']]);
            $requestparams = array(
                'gamekey' => $record[$fields['game_key']],
                'ver'=> $record[$fields['ver']],
                'chn'=> $record[$fields['chn']],
                'indep' => $indep,
            );
            var_dump(site_url($uri) . '?'.  $this->request_builder($requestparams));
            $this->setMaximumRedirects(0);
            $response = $this->send_request($uri, $requestparams, 'get');
            

            if ($is_indep) {
                //独立包不判断状态
                if(empty($game_info['bg_picture']))
                {
                    $this->assertText('305');
                }
                else
                    $this->assertResponse(302);
            }
            else{
                //非独立包，判断三个状态
                if ($visible == 0)
                {
                    //不可见
                    $this->assertText('"msg":"invisible"');
                }
                //维护
                elseif($game_info['is_maintain'] == 1)
                {
                    //维护中
                    $this->assertText('310');
                }
                //没传appv，不会出现不兼容的情况
                else
                {
                    if(empty($game_info['bg_picture']))
                    {
                        $this->assertText('305');
                    }
                    else
                        $this->assertResponse(302);
                }
            }
        }
    }
    function test_musicdir(){
        $uri = '/capi/bgv3/musicdir';
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
            $gamemode = $this->game_mode_trans($mode);
            if($gamemode == 5 && $chn_test_duration != -1)
            {
                //托管包试玩模式
                $gamemode = 6;
            }
            if(!$is_indep)
                $game_info = $this->cp_chn_game_info_model->get_channel_game_detail_gamekey($record[$fields['chn']],$record[$fields['game_key']],$record[$fields['ver']]);
            else
                $game_info = $this->cp_chn_game_info_model->get_channel_game_detail_gamekey('999997',$record[$fields['game_key']],$record[$fields['ver']]);
            $requestparams = array(
                'gamekey' => $record[$fields['game_key']],
                'ver'=> $record[$fields['ver']],
                'chn'=> $record[$fields['chn']],
                'indep' => $indep,
            );
            var_dump(site_url($uri) . '?'.  $this->request_builder($requestparams));
            $this->setMaximumRedirects(0);
            $response = $this->send_request($uri, $requestparams, 'get');
            if ($is_indep) {
                //独立包不判断状态
                if(empty($game_info['bg_music']))
                {
                    $this->assertText('305');
                }
                else
                    $this->assertResponse(302);
            }
            else{
                //非独立包，判断三个状态
                if ($visible == 0)
                {
                    //不可见
                    $this->assertText('"msg":"invisible"');
                }
                //维护
                elseif($game_info['is_maintain'] == 1)
                {
                    //维护中
                    $this->assertText('310');
                }
                //没传appv，不会出现不兼容的情况
                else
                {
                    if(empty($game_info['bg_music']))
                    {
                        $this->assertText('305');
                    }
                    else
                        $this->assertResponse(302);
                }
            }
        }
    }
    
    public function test_chngmlist() {
        $uri = '/capi/apiv3/channelgamelist';
        $requestparams = array(
            'chn' => 111111,
            'start' => 'abc',
            'len' => '0',
        );
        $response = $this->send_request($uri, $requestparams, 'get');
        $this->assertText('"res":"302"');
        $requestparams = array(
            'chn' => 111111,
            'start' => '0',
            'len' => '1.5',
        );
        $response = $this->send_request($uri, $requestparams, 'get');
        $this->assertText('"res":"302"');
        $requestparams = array(
            'chn' => 'abc',
            'start' => '0',
            'len' => '0',
        );
        $response = $this->send_request($uri, $requestparams, 'get');
        $this->assertText('"res":"302"');
    }
    
    /**
     * 测试托管包以独立包的方式获取资源
     * @param type $param
     */
    public function test_indepgame_get_depgame_res() {
        $uri = '/capi/apiv3/resmdfdir';
        list($fields, $data) = $this->get_channel_game_list();
        foreach($data as $record) {
            $chn_test_duration = $record[$fields['chn_test_duration']];
            $visible = $record[$fields['visible']];
            $is_indep = FALSE;
            $mode = $record[$fields['mode']];
            $game_id = $record[$fields['game_id']];
            $ver = $record[$fields['ver']];
            if($mode == 4)
            {
                //独立包不检查
                continue;
            }
            else
            {
                //托管包以独立包的方式
                $indep = 1;
                $is_indep = TRUE;
            }
            if(!$is_indep)
                $game_info = $this->cp_chn_game_info_model->get_channel_game_detail_gamekey($record[$fields['chn']],$record[$fields['game_key']],$record[$fields['ver']]);
            else
                $game_info = $this->cp_chn_game_info_model->get_channel_game_detail_gamekey('999997',$record[$fields['game_key']],$record[$fields['ver']]);
            $requestparams = array(
                'gamekey' => $record[$fields['game_key']],
                'ver'=> $ver,
                'chn'=> $record[$fields['chn']],
                'indep' => $indep,
            );
            var_dump(site_url($uri) . '?' . $this->request_builder($requestparams));
            $this->setMaximumRedirects(0);
            $response = $this->send_request($uri, $requestparams, 'get');
            $record = $this->cp_game_revision_info_model->select(array('mdf_url'))->where(array('game_id'=>$game_id,'package_ver_code'=>$ver,'is_published'=>1))->get();
            if($is_indep){
                if(!$record['mdf_url']){
                    $this->assertText('305');
                }else{
                    $this->assertResponse(302);
                }
            }
            // 非独立包
            else{
                if($visible==0){
                    // 不可见则显示不可见， 独立包不考虑可不可见
                    $this->assertText ('"msg":"invisible"');
                }elseif($game_info['is_maintain'] == 1 ){
                    $this->assertText('310');
                }elseif(!$record['mdf_url']){
                    // 没有值
                    $this->assertText('305');
                }else{
                    $this->assertResponse(302);
                }
            }
        }
    }
}
