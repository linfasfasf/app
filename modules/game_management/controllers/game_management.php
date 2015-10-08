<?php
class Game_Management extends Admin_Controller
{
    public static $_redis_ccp = NULL;
    public static $icon_size = 51200; //50k
    public static $background_size = 204800; //200k
    public static $music_size = 512000; //500k
    public $game_type_arr = array();

    function __construct()
    {
        parent::__construct();
        $this->load->library('session');
        $this->upload_path =  str_replace('\\', '/', $this->concat_path( FCPATH , 'uploads'));
        $this->game_type_arr = array(
            '0'=>"未指定",
            '1'=>"角色扮演",
            '2'=>"经营策略",
            '3'=>"即时战斗",
            '4'=>"卡牌",
            '5'=>"模拟养成",
            '6'=>"动作射击",
            '7'=>"休闲时间",
            '8'=>"塔防",
            '9'=>"小游戏",
            '10'=>"棋牌",
            );
        $this->load->helper(array('form', 'url'));
        // library 用 array 载入多个有错误提示， 因为分开载
        $this->load->library('smarty');
        $this->load->library( 'form_validation');
        $this->load->model('acl/acl_model');
        $this->load->model('cp_game_info_model');
        $this->load->model('cp_game_revision_info_model');
        $this->load->model('cp_chn_game_info_model');
        $this->load->model('cp_channel_info_model');
        $this->target_path = 'uploads/generic';
        $this->access();
    }
    
    private function access() {
        $accesslevel = array(
            '/game_management/gamelist' => 'read',
            '/game_management/search_game' => 'read',
            '/game_management/onlinechannel' => 'read',
            '/game_management/viewgame' => 'read',
            '/game_management/editgame' => 'update',
            '/game_management/editgamehandler' => 'update',
            '/game_management/delete_game' => 'delete',
            '/game_management/view_revision' => 'read',
            '/game_management/delete_revision_handler' => 'delete',
            '/game_management/del_manage_compatibility' => 'delete',
        ); 
        foreach($accesslevel as $aco => $al) {
            $GLOBALS['ACCESSLEVEL'][$aco] = $al;
        }
    }
    /**
     * 检验 revision 是不是符合发布并上线的所有标准
     * @return array 没通过时返回 array(FALSE, $errmsg);
     */
    public function validate_revision_info($revision_id){
        if(empty($revision_id) || !is_numeric($revision_id)) return FALSE;
        $summary = FALSE; // 结果
        $result = $this->cp_game_revision_info_model->get_game_revision_detail($revision_id,FALSE);
        $game_info = $this->cp_game_info_model->get_game_detail($result['game_id']);
        $game_mode_names = array(
            '0' => '试玩包',
            '1' => '托管包', 
            '2' => '试玩包',
            '3' => '托管包', 
            '4' => '独立包', 
        );
        $game_mode_settings = array(
            '0' => array('file_dir'),
            // 20150211 托管包在的 apk 在渠道中设置
            //'1' => array('file_dir', 'apk_download_url', 'manifest_url'),
            '1' => array('file_dir', 'manifest_url'),
            '2' => array('file_dir', 'apk_download_url'),
            '3' => array('file_dir', 'apk_download_url', 'manifest_url'),
            //'4' => array('file_dir', 'apk_download_url', 'manifest_url'),
            // 20150121 独立包可以没有 apk 
            '4' => array('file_dir', 'manifest_url'),
        );
        $field_name_mapping = array(
            'manifest_url' => 'Manifest',
            'apk_download_url' => 'APK 地址',
            'file_dir' => '游戏资源目录',
        );
        foreach($game_mode_settings[$game_info['game_mode']] as $field){
            if(empty($result[$field])){
                $summary .= "{$game_mode_names[$game_info['game_mode']]} 要提供 " . $field_name_mapping[$field]  . " 信息";
            }
        }
        if($summary) {
            return array(FALSE, $summary);
        }else{
            return array(TRUE, '');
        }
    }

    public function gamelist(){
        // TODO：加可选参数，显示某个渠道的游戏
        //page  页码，默认为1,从第1页开始。
        //每页30行
        $operate = $this->uri->segment(4, '');
        $id = $this->uri->segment(5, '');
        if($operate == 'del' && !empty($id)){
            $this->cp_game_info_model->update($id, array('del_flag' => 1));
        }
        $game = $this->input->get_post('game',TRUE);
        $cp_vendor = $this->input->get_post('cp_vendor',TRUE);
        $cp_vendor = $cp_vendor == -1?FALSE:$cp_vendor;
        $range_time = $this->input->get_post('range_time',TRUE);
        $cocosplay = $this->input->get_post('cocosplay',TRUE);
        $runtime = $this->input->get_post('runtime',TRUE);
        $game_mode = FALSE;
        if(($cocosplay || $runtime) && (!$cocosplay || !$runtime)){
            $game_mode = $cocosplay?$cocosplay:$runtime;
        }
        $search = '';
        if($game || $cp_vendor || $range_time || $game_mode){
            $search .= ' where ';
            if($game){
                $search .= " (game_key like '%$game%' or game_name like '%$game%' or game_id like '%$game%') ";
            }
            if($game_mode){
                if($game){
                    $search .= ' and ';
                }
                $game_mode = $cocosplay?$cocosplay:$runtime;
                $search .= "game_mode = $game_mode";
            }
            if(!empty($range_time))
            {
                $start_time = substr($range_time,0,10);
                $start_time = strtotime($start_time);
                $end_time = substr($range_time,-10);
                $end_time = strtotime($end_time) + 24*60*60;
                if($game || $game_mode){
                    $search .= ' and ';
                }
                $search .= "modify_time > $start_time and modify_time < $end_time ";
            }
            if($cp_vendor){
                if($game || $game_mode || !empty($range_time)){
                    $search .= ' and ';
                }
                $search .= " cp_vendor like '%$cp_vendor%' ";
            }
        }
        $search_array = array(
            'game' => $game,
            'cp_vendor' => $cp_vendor,
            'range_time' => $range_time,
            'cocosplay' => $cocosplay,
            'runtime' => $runtime,
        );
        $this->load->library('pagination');
        $config['per_page'] = 15;
        $page = $this->uri->segment(3, 1);
        $config['base_url'] = site_url('ChannelManage/gamelist');
        $config['uri_segment'] = 1;
        $total = $this->db->query('select count(game_id) from cp_game_info '.$search)->result_array();
        $total = $total[0]['count(game_id)'];
        if($total == 0){
            $msg = '找不到指定游戏';
        }
        $config['total_rows'] = $total;
        $this->pagination->initialize($config);
        $s = ($page-1)*$config['per_page'];

        $data = $this->cp_game_info_model->getAllDetail($s,$config['per_page'],$search);
        //$data = $this->cp_game_info_model->where(array('del_flag' => 0))->order_by('create_time', 'desc')->limit($config['per_page'], $page)->get_all();
        //print_r($data);
        for($i=0; $i< count($data);$i++){
            $active_revision_info = $this->cp_game_info_model->get_game_detail_extended($data[$i]['game_id'],$data[$i]['package_ver_code'],TRUE);
            if(isset($active_revision_info['revision_id'])) {
                $data[$i]['revision_id'] = $active_revision_info['revision_id'];
                $data[$i]['package_ver'] = $active_revision_info['package_ver'];
                //$data[$i]['apk_name'] = $active_revision_info['apk_name'];
                $data[$i]['is_maintain'] = $active_revision_info['is_maintain'];
                $data[$i]['star'] = $active_revision_info['star'];
                $data[$i]['maintain_tip'] = $active_revision_info['maintain_tip'];
                $data[$i]['payment'] = $active_revision_info['payment'];
                $data[$i]['user_system'] = $active_revision_info['user_system'];
                $data[$i]['sdk_version'] = $active_revision_info['sdk_version'];
                $data[$i]['engine_version'] = $active_revision_info['engine_version'];
                $data[$i]['orientation'] = $active_revision_info['orientation']; // hack: 防止 gamelist 那里没游戏也显示横屏
            }else{
                $data[$i]['revision_id'] = '';
                $data[$i]['package_ver'] = 'NA';
                //$data[$i]['apk_name'] = '';
                $data[$i]['is_maintain'] = '';
                $data[$i]['star'] = 'NA';
                $data[$i]['maintain_tip'] = '';
                $data[$i]['payment'] = 'NA';
                $data[$i]['user_system'] = 'NA';
                $data[$i]['sdk_version'] = 'NA';
                $data[$i]['engine_version'] = '';
                $data[$i]['orientation'] = 'NA';
            }
        };
        foreach ($data as $key => &$value) {
            $ty = $value['game_type'];
            //echo "<pre>";
            //echo $ty."<br>";
            $value['game_type'] = $this->game_type_arr[$ty];
            //print_r($value)."<br>\n";
        }
        //echo print_r($this->game_type_arr,true)."<br>\n";
        //exit();
        
        $searchcp = $this->db->distinct()->select('cp_vendor')->from('cp_game_info')->get()->result_array();
        $content = $this->load->view('gamelist',array("arr_list"=>$data,"page" => $page,"total" => $total,
            "per_page" =>$config['per_page'] ,'new_game_id'=>$this->session->flashdata('new_game_id'),
            'search' => $search_array,'searchcp'=>$searchcp,'msg'=>$msg), TRUE);
        $this->smarty->view('general.tpl', array('content'=>$content));
    }

    public function addgame($msg='') {
		$identity = $this->session->userdata('identity');
        $content = $this->load->view('addgame',array('msg'=>$msg, 'identity'=>$identity), TRUE);
        $this->smarty->view('general.tpl', array('content'=>$content));
    }

    public function checkidentity($identity='') {
        $this->load->model('auth/ion_auth_model');
        if($this->ion_auth_model->identity_check($identity)) {
            return TRUE;
        }else{
            $this->form_validation->set_message('checkidentity', '没找到帐号');
            return FALSE;
        }
    }

    public function addgamehandler() {
        //处理上传的游戏数据
        if(!$_POST) {
            $this->session->set_flashdata ( 'flash_message', '填写不完整' );
            return redirect("game_management/addgame");
        }
        
        $this->load->model('auth/ion_auth_model');
        $this->load->helper('post_data');
        $validation_config = array(
            array(
                'field' =>'gamename',
                'label' =>'游戏名称',
                'rules' =>'trim|required|min_length[1]|max_length[32]',
            ),
            array(
                'field' =>'gamemode',
                'label' =>'游戏模式',
                'rules' =>'required',
            ),
            array(
                'field' =>'gametype',
                'label' =>'游戏类型',
                'rules' =>'required',
            ),
            array(
                'field' =>'email',
                'label' =>'关联帐号',
                'rules' =>'required|trim|valid_email|callback_checkidentity',
            ),
            array(
                'field' =>'game_key',
                'label' =>'Game Key',
                'rules' =>'trim|required|min_length[1]|max_length[20]|is_unique[cp_game_info.game_key]|alpha_dash|xss_clean',
            ),
            array(
                'field' =>'supplier',
                'label' =>'提供商',
                'rules' =>'trim|min_length[1]|max_length[64]|xss_clean',
            ),
            array(
                'field' =>'offline_support',
                'label' =>'离线支持',
                'rules' =>'trim',
            ),
            array(
                'field' =>'purge_cache',
                'label' =>'是否清除缓存',
                'rules' =>'trim',
            ),
        );

        
        $gamemode = $this->input->post('gamemode',TRUE);
        $offline_support = $this->input->post('offline_support',TRUE);
        $purge_cache = $this->input->post('purge_cache',TRUE);
        
        if($gamemode && $gamemode== 7) {
            $validation_config[] = array(
                    'field' =>'packagename',
                    'label' =>'Runtime 游戏包名',
                    'rules' =>'required|trim|min_length[6]|max_length[256]'
                );
        }
        else if ($offline_support && $offline_support==1) {
            $validation_config[] = array(
                    'field' =>'allow_del_data',
                    'label' =>'是否允许删除data目录',
                    'rules' =>'trim'
                );
        }else if ($purge_cache && $purge_cache==0) {
            $validation_config[] = array(
                    'field' =>'purge_cache_time',
                    'label' =>'不清除缓存时间的限制',
                    'rules' =>'trim'
                );
        }
        else{
        }

        $this->form_validation->set_rules($validation_config);
        if($this->form_validation->run()===FALSE){
            $msg = $this->form_validation->error_string();
            return $this->addgame($msg);
        }
        $package_name = $this->input->post('packagename');
        if ($package_name && preg_match("/[\x7f-\xff]/", trim($package_name))) {
            $this->session->set_flashdata ( 'flash_message', '包名不能含有中文' );
            return redirect("game_management/addgame");
        }
        $allow_del_data = $this->input->post('allow_del_data');
        $purge_cache_time = $this->input->post('purge_cache_time');
        $gamename = trim( $this->input->post('gamename',TRUE));
        $package_name = trim($package_name);      //包名
        $package_version= trim( $this->input->post('game_version',TRUE));
        //$sdk_version = trim($_POST['sdk_version']);
        //$engine_version = trim($_POST['engine_version']);
        //$gamemode = 1;  //去除独立包，试玩包
        $game_key = $this->input->post('game_key',TRUE);
        $email = $this->input->post('email',TRUE);
        if($offline_support === FALSE) $offline_support = '0';
        $purge_cache = $this->input->post('purge_cache',TRUE);
        if($purge_cache === FALSE) $purge_cache = '1';
        $user = $this->ion_auth_model->user_info($email); // 关联用户
        //$cp_vendor = $this->input->post('supplier',TRUE);
        $cp_vendor = $user->company_name;
		$operator = $this->session->userdata('user_id'); // 操作用户

        //检查game_key是否已经存在
        $is_game_key_exists = $this->cp_game_info_model->is_game_key_present($game_key);        
        if($is_game_key_exists){
            $msg='Game Key已经存在，请重新设置';
            $_POST['game_key'] = '';
            return $this->addgame($msg);
        }

        $t = time();
        $ins["game_name"]        = $gamename;
        if($gamemode == 7) { // runtime 游戏
            $ins["package_name"]     = $package_name;
        }else{
            $ins["package_name"]     = '';
        }
        if($offline_support == 1){
            $ins["allow_del_data"]   = $allow_del_data;
        }  else {
            $ins["allow_del_data"]   = -99;
        }
        if($purge_cache == 0){
            $ins["purge_cache_time"]   = $purge_cache_time;
        }  else {
            $ins["purge_cache_time"]   = -99;
        }
        $ins["package_ver"]      = '';
        $ins["package_ver_code"] = NULL;
        $ins["game_mode"]        = $gamemode;
        $ins["game_type"]        = $_POST["gametype"];
        $ins["cp_vendor"]        = $cp_vendor;
        $ins["create_time"]      = $t;
        $ins["modify_time"]      = $t;
        $ins["del_flag"]         = 0;
        $ins["opt_id"]           = $operator;
        $ins["email"]            = $user->email;
        $ins["game_key"]         = $game_key;
        $ins["apk_name"]         = $apkname;
        $ins["offline_support"]  = $offline_support;
        $ins["purge_cache"]  = $purge_cache;

        $game_id = $this->cp_game_info_model->insert($ins);
        if(!$game_id){
            $this->session->set_flashdata ( 'flash_message', 'cp_game_info 写入表操作失败' );
            redirect("game_management/addgame");
        }else{
            // TODO: sync with cocos_packingtool
            $this->load->model('cocos_packingtool/cp_packingtool_game_model');
            $data = array(
                'game_name' => $gamename,
                'game_version_name' => '',
                'game_id' => $game_id,
            );
            $ok = $this->cp_packingtool_game_model->update_resource($user->id, $game_key, 0, $data);
            if(!$ok) {
                $this->session->set_flashdata ('flash_message', '无法推送至接入工具');
                return redirect('game_management/gamelist');
            }
        }

        $this->session->set_flashdata('flash_message', '添加完成' );
        $this->session->set_flashdata('new_game_id', $game_id);
        redirect('game_management/gamelist');
    }

    public function onlinechannel(){
        //上线渠道
        $game_id = $this->uri->segment(3, 1);
        $data = $this->cp_chn_game_info_model->getAllDetail($game_id);
        $gm = $this->cp_game_info_model->select("game_name")->where(array('del_flag' => 0,"game_id"=>$game_id))->get();

        $content = $this->load->view('onlinechannel',array("arr_list" => $data,"gamename"=>$gm['game_name']), TRUE);
        $this->smarty->view('general.tpl', array('content'=>$content));
    }

    public function viewgame($gameid)
    {
        if(empty($gameid)){
            redirect('game_management/gamelist');
        }
        $active_version_name = ''; 
        $gameinfo_short = $this->cp_game_info_model->select('package_ver_code, email, offline_support, purge_cache ,allow_del_data,purge_cache_time')->where(array('game_id'=>$gameid))->get();
        $active_version_code = $gameinfo_short['package_ver_code']; // 可以是 NULL 值
        $email = $gameinfo_short['email']; // 可以是 NULL 值
        $offline_support = $gameinfo_short['offline_support']; // 可以是 NULL 值
        $purge_cache = $gameinfo_short['purge_cache']; // 可以是 NULL 值
        $allow_del_data = $gameinfo_short['allow_del_data'];
        $purge_cache_time = $gameinfo_short['purge_cache_time'];
        if($active_version_code===NULL) $active_version_code=0;
        $data = $this->cp_game_info_model->get_game_detail($gameid);
        
        $revision_data = $this->cp_game_revision_info_model->get_game_revision_list($gameid, FALSE);
        for($i=0; $i< count($revision_data); $i++){
            if($revision_data[$i]['package_ver_code']==$active_version_code && $revision_data[$i]['is_published']){
                $active_version_name = $revision_data[$i]['package_ver'];
            }
            //$revision_data[$i]['icon_url'] = site_url('game_management/image_no_cache') . '?file=' . urlencode($revision_data[$i]['icon_url']);
            $revision_data[$i]['icon_url'] = $this->completeurl($revision_data[$i]['icon_url'], TRUE);
            $revision_data[$i]['manifest_url'] = $this->completeurl($revision_data[$i]['manifest_url'], TRUE);
            $revision_data[$i]['apk_download_url'] = $this->completeurl($revision_data[$i]['apk_download_url'], TRUE);
            $revision_data[$i]['cdn_icon_url'] = $this->completeurl($revision_data[$i]['icon_url'], TRUE);
            $revision_data[$i]['cdn_manifest_url'] = $this->completeurl($revision_data[$i]['manifest_url'], TRUE);
            $revision_data[$i]['cdn_apk_download_url'] = $this->completeurl($revision_data[$i]['apk_download_url'], TRUE);
            $revision_data[$i]['bg_music'] = $this->completeurl($revision_data[$i]['bg_music'], TRUE);
            $revision_data[$i]['bg_picture'] = $this->completeurl($revision_data[$i]['bg_picture'], TRUE);
        }
        $data['star']='';
        $data['icon_url']='';
        $data['apk_name']='';
        $data['sdk_version']='';
        $data['engine_version']='';
        $data['orientation']='';
        $data['user_system']='';
        $data['payment']='';
        $data['is_maintain']='';
        $data['maintain_tip']='';
        $data['bg_music']='';
        $data['bg_picture']='';
        $data['email']=$email;
        $data['offline_support']=$offline_support;
        $data['purge_cache'] = $purge_cache;
        $data['allow_del_data'] = $allow_del_data;
        $data['purge_cache_time'] = $purge_cache_time;
        $this->load->model('auth/ion_auth_model');
        $user = $this->ion_auth_model->user_info($email); // 关联用户

        $data['cp_vendor']=$user->company_name;
        
        arsort($revision_data);
        if(count($data)){
            $content = $this->load->view('viewgame',array('game_id'=> $gameid,
                    'gameinfo'=>$data,
                    'active_version_name'=>$active_version_name,
                    'active_version_code'=>$active_version_code,
                    "game_revisions"=>$revision_data), TRUE);
            $userid = $this->session->userdata('user_id');
            $content .= $this->load->view('upload_widget', array('userdir'=>'syncdir/'.$userid), TRUE);
            $this->smarty->view('general.tpl', array('content'=>$content));
        }else{
            $this->load->library('session');
            $this->session->set_flashdata ( 'flash_message', '没找到符合条件的游戏' );
            redirect('game_management/gamelist');
        }
    }
    public function editgame($game_id,$msg='') {
        $this->load->library('session');
        if(empty($game_id)){
            redirect('game_management/gamelist');
        }
        $data = $this->cp_game_info_model->where(array('del_flag' => 0,'game_id'=>$game_id))->get();
        $version_list  = $this->cp_game_revision_info_model->get_package_version_codes($game_id);
        //var_dump($version_list);
        //echo print_r($data,true) ."<br>";

        $content = $this->load->view('editgame',array("gameinfo"=>$data,"game_id"=>$game_id, 'version_list'=>$version_list,'msg'=>$msg), TRUE);
        $this->smarty->view('general.tpl', array('content'=>$content));
    }


    public function editgamehandler()
    {
        //处理上传的游戏数据
        $this->load->library('session');
        $this->load->helper('post_data');
        $test_result  = all_present_in_post(array('game_id','gamename'));//只检查这两项？
        if(!$test_result) {
                $this->session->set_flashdata ( 'flash_message', '填写不完整' );
                redirect("game_management/gamelist");
                return;
        }
        $this->load->model('auth/ion_auth_model');

        $game_key = $this->input->post('game_key');
        $t = time();
        $game_id =  $this->input->post('game_id');
        $gamename =  $this->input->post('gamename');
        //检查game_key是否更改   
        $data = $this->cp_game_info_model->where(array('del_flag' => 0,'game_id'=>$game_id))->get();        
        $old_game_key = $data['game_key'];
        if($game_key != $old_game_key)
        {
            //game_key发生更改
            $is_game_key_exists = $this->cp_game_info_model->is_game_key_present($game_key);   
            //检查新的game_key是否已经存在
            if($is_game_key_exists){
            $this->session->set_flashdata( 'flash_message', 'Game Key已经存在，请重新设置'.$game_key);
            redirect('game_management/gamelist');
            }
        }
        
        $validation_config = array(
            array(
                'field' =>'gamename',
                'label' =>'游戏名称',
                'rules' =>'trim|required|min_length[1]|max_length[32]',
            ),
            array(
                'field' =>'gametype',
                'label' =>'游戏类型',
                'rules' =>'required',
            ),
            array(
                'field' =>'gamemode',
                'label' =>'游戏模式',
                'rules' =>'',
            ),
            array(
                'field' =>'email',
                'label' =>'关联帐号',
                'rules' =>'required|trim|valid_email|callback_checkidentity',
            ),
            array(
                'field' =>'game_key',
                'label' =>'Game Key',
                'rules' =>'trim|required|min_length[1]|max_length[20]|alpha_dash|xss_clean',
            ),
            array(
                'field' =>'supplier',
                'label' =>'提供商',
                'rules' =>'trim|min_length[1]|max_length[64]|xss_clean',
            ),
            array(
                'field' =>'offline_support',
                'label' =>'离线支持',
                'rules' =>'trim',
            ),
            array(
                'field' =>'purge_cache',
                'label' =>'是否清除缓存',
                'rules' =>'trim',
            ),
        );
        $this->form_validation->set_rules($validation_config);
        if($this->form_validation->run()===FALSE){
            $msg = $this->form_validation->error_string();
            return $this->editgame($game_id,$msg);
        }
        
        $email = $this->input->post('email',TRUE);
        $offline_support = $this->input->post('offline_support',TRUE);
        $allow_del_data = $this->input->post('allow_del_data',TRUE);
        $purge_cache_time = $this->input->post('purge_cache_time',TRUE);
        if($offline_support === FALSE) $offline_support = '0';
        $purge_cache = $this->input->post('purge_cache',TRUE);
        if($purge_cache === FALSE) $purge_cache = '1';
        $user = $this->ion_auth_model->user_info($email); // 关联用户
        //$cp_vendor = $this->input->post('supplier',TRUE);
        $cp_vendor = $user->company_name;
		$operator = $this->session->userdata('user_id'); // 操作用户

        $ins["game_id"]            = $game_id;
        $ins["game_name"]          = $gamename;
        $package_name     = $this->input->post('package_name');
        if($package_name === FALSE) {
            $ins["package_name"]     = '';
        }else{
            $ins["package_name"]     = trim($package_name);
        }
        if($offline_support == 1){
            $ins["allow_del_data"]   = $allow_del_data;
        }  else {
            $ins["allow_del_data"]   = -99;
        }
        if($purge_cache == 0){
            $ins["purge_cache_time"]   = $purge_cache_time;
        }  else {
            $ins["purge_cache_time"]   = -99;
        }

        // $ins["package_name"]    = $bagname; 不修改这个
        //$ins["package_ver_code"] = $_POST["version_number"];
        //$ins["game_mode"]          = $this->input->post('gamemode');
        $ins["game_type"]          = $_POST["gametype"];
        $ins["email"]              = $user->email;
        $ins["cp_vendor"]          = $cp_vendor;
        //$ins["apk_name"]         = $apkname;
        $ins["modify_time"]        = $t;
        $ins["game_key"]           = $game_key;
        $ins["opt_id"]             = $operator;
        $ins["offline_support"]    = $offline_support;
        $ins["purge_cache"]    = $purge_cache;

        $res = $this->cp_game_info_model->update($game_id,$ins);

        if(!$res){
            $this->session->set_flashdata ( 'flash_message', '数据库更新失败' );
        }else{
            // TODO: sync with cocos_packingtool
            $this->load->model('cocos_packingtool/cp_packingtool_game_model');
            $ok = $this->cp_packingtool_game_model->update_association($user->id, $game_key, $game_id);
            $revision_data = $this->db->select('package_ver_code, icon_url, bg_music, bg_picture, package_ver')
                ->distinct()
                ->where(array('game_id'=>$game_id))
                ->get('cp_game_revision_info');
            $this->db->trans_start();
            foreach($revision_data->result() as $rev) {
                $ok = $this->cp_packingtool_game_model->update_resource($user->id, $game_key, $rev->package_ver_code, 
                        array('game_id'=> $game_id,
                        'game_name'=>$gamename, 
                        'game_version_name'=> $rev->package_ver,
                        //'game_icon_url'=> $this->completeurl($rev->icon_url),
                        //'game_music_url'=> $this->completeurl($rev->bg_music),
                        //'game_background_url'=> $this->completeurl($rev->bg_picture),
                        )
                    );
                if($ok) break; // 只需更新一次
            }
            $this->db->trans_complete();
            if ($this->db->trans_status() === FALSE) {
                $this->session->set_flashdata ('flash_message', '更新无法推送至接入工具');
            }else{
                $this->session->set_flashdata ('flash_message', '已更新');
                $this->cp_packingtool_game_model->update_gamename($game_key,$gamename);
            }
        }
        redirect('game_management/gamelist');

    }

    public function checkfile_uploaded($ext){
        if($this->session->userdata('upload_icon_status') && $this->session->userdata('upload_icon_status')=='GOOD'){
            return TRUE; 
        }elseif($this->session->userdata('upload_icon_status')){
            $this->form_validation->set_message('checkfile_uploaded', $this->session->userdata('upload_icon_status'));
            return FALSE;
        }
        if($ext==''){
            $this->form_validation->set_message('checkfile_uploaded', '请指定渠道LOGO');
            return FALSE;
        }elseif(strtolower($ext)!='.jpg' && strtolower($ext)!='.png' && strtolower($ext)!='.jpeg'){
            $this->form_validation->set_message('checkfile_uploaded', '只允许 png, jpg');
            return FALSE;
        }else{
            return TRUE;
        }
    }

    /* 使游戏版本成为 线上的版本 
     * $revision_id cp_game_revision_info 的表 id 字段
     */
    public function activate_revision($revision_id)
    {
        $this->load->library('session');
        if(empty($revision_id)){
            $this->session->set_flashdata ( 'flash_message', '未指定版本' );
            //  redirect("game_management/gamelist");
        }
        $result = $this->cp_game_revision_info_model->select(array('id', 'game_id','package_ver_code', 'file_dir', 'manifest_url', 'cpk_file_dir', 'apk_download_url'))->where(array('id'=>$revision_id))->get();
        if(empty($result)){
            //  redirect("game_management/gamelist"); //TODO: 改为跳转到来源页面
        }else{
            list($valid, $summary) = $this->validate_revision_info($revision_id);
            if(!$valid){
                $this->session->set_flashdata('flash_message', $summary);
                redirect('game_management/viewgame/'. $result['game_id']);
            }
        }
        $this->cp_game_info_model->update($result['game_id'], array('package_ver_code'=>$result['package_ver_code']));
        redirect('game_management/viewgame/'. $result['game_id']);
    }

    public function delete_revision_handler($revision_id)
    {
        $this->load->library('session');
        if(empty($revision_id)){
            $this->session->set_flashdata ( 'flash_message', '没找到游戏版本' );
            redirect('game_management/viewgame/'.$game_id);
        }
        $game_id = $this->cp_game_revision_info_model->get_game_id($revision_id);
        if(empty($game_id)){
            $this->session->set_flashdata ( 'flash_message', '没找到游戏' );
            redirect('game_management/gamelist');
        }
        $published = $this->cp_game_revision_info_model->is_published($revision_id);
        //$active_package_ver_code = $this->cp_game_revision_info_model->get_active_package_ver_code($game_id);
        /*
        if($published ){
            $this->session->set_flashdata ( 'flash_message', '不能删除已发布的版本' );
            redirect('game_management/viewgame/'.$game_id);
        }
         */

        $active_revision_id = $this->cp_game_info_model->get_active_revision_id($game_id);
        if($active_revision_id == $revision_id) {
            $this->cp_game_info_model->reset_active_package_version($game_id);
        }
        $result = $this->cp_game_revision_info_model->delete_revision($revision_id);
        if($result ){
            $this->session->set_flashdata ( 'flash_message', '删除完成' );
        }
        else{
            $this->session->set_flashdata ( 'flash_message', '删除失败' );
        }
        redirect('game_management/viewgame/'.$game_id);
    }

    /**
     * 发布一个游戏
     * 1、检查游戏的完整性
     * 2、同步游戏的资源到CDN
     * @param  [type] $revision_id [description]
     * @return [type]              [description]
     */
    public function publish_revision_handler($revision_id)
    {
        if(empty($revision_id)){
            $this->session->set_flashdata('flash_message', '未指定版本');
            return redirect('game_management/gamelist'); 
        }
        list($valid, $summary) = $this->validate_revision_info($revision_id);
        if($valid)  {
            $result = $this->cp_game_revision_info_model->set_published($revision_id);
            if($result ){
                $this->load->helper('game_install');
                $revision = $this->cp_game_revision_info_model->get_game_revision_detail($revision_id);
                $this->sync_with_cdn_dir($revision['file_dir']);
                $res = $this->db->select('res.file_dir')
                    ->from('cp_game_revision_channel_resources as res')
                    ->join('cp_game_revision_apk as apk','res.revision_id=apk.revision_id')
                    ->where('apk.apk_download_url != "" AND apk.apk_download_url IS NOT NULL AND res.revision_id = '.$revision_id)
                    ->group_by('res.file_dir')
                    ->get()->result_array();
                foreach ($res as $value) {
                    $this->sync_with_cdn_dir($value['file_dir']);
                }
                $chn_config = $this->db->select('config.url')
                    ->from('cp_game_revision_channel_config as config')
                    ->join('cp_game_revision_apk as apk','config.id=apk.revision_channel_config_id')
                    ->where('apk.apk_download_url != "" AND apk.apk_download_url IS NOT NULL AND apk.revision_id = '.$revision_id)
                    ->group_by('config.url')
                    ->get()->result_array();
                foreach ($chn_config as $value) {
                    $this->sync_with_cdn_file($value['url']);
                }
                fixresourcemap($revision_id);
                $summary .= '成功';
                
            } else{
                $summary .= '失败';
            }
        }
        $this->session->set_flashdata('flash_message', $summary);
        $game_id = $this->cp_game_revision_info_model->get_game_id($revision_id);
        redirect('game_management/viewgame/'.$game_id);
    }
    public function view_revision($revision_id)
    {
        if(empty($revision_id)){
            redirect('game_management/gamelist');
        }
        $game_id = $this->cp_game_revision_info_model->get_game_id($revision_id);
        $gameinfo_data = $this->cp_game_info_model->get_game_detail($game_id);
        $active_version_code = $gameinfo_data['package_ver_code']; // 可以是 NULL 值
        $active_version_name = $gameinfo_data['package_ver']; // 可以是 NULL 值
        if($active_version_code===NULL) {
            $active_version_code=0;
            $active_version_name='';
        }
        
        $revision_data = $this->cp_game_revision_info_model->get_game_revision_detail($revision_id, FALSE);
        $revision_data['icon_url'] = $this->completeurl($revision_data['icon_url']);
        $revision_data['bg_picture'] = $this->completeurl($revision_data['bg_picture']);
        $revision_data['bg_music'] = $this->completeurl($revision_data['bg_music']);
        $revision_data['manifest_url'] = $this->completeurl($revision_data['manifest_url']);
        $revision_data['compatible_arch'] = explode(',', $revision_data['compatible_arch']);
        if($revision_data['chafen_url']){
            $revision_data['chafen_url'] = $this->completeurl($revision_data['chafen_url']);
        }
        $game_type_list = array( '未指定', '角色扮演', '经营策略', '即时战斗', '卡牌', '模拟养成', '动作射击', '休闲时间', '塔防', '小游戏', '棋牌');
        if( $gameinfo_data['game_type'] ){
            $gameinfo_data['game_type'] = $game_type_list[$gameinfo_data['game_type']];
        }else { $gameinfo_data['game_type'] = '';}
        

        if(!empty($revision_data)){
            $content = $this->load->view('view_revision',array(
                    'game_id'=> $game_id,
                    'revision_id' => $revision_id,
                    'gameinfo'=>$gameinfo_data,
                    'game_revision_info'=>$revision_data,
                    'active_version_code'=>$active_version_code,
                    'active_version_name'=>$active_version_name)
                    , TRUE);
            $this->smarty->view('general.tpl', array('content'=>$content));
        }else{
            $this->load->library('session');
            $this->session->set_flashdata ( 'flash_message', '没找到符合条件的游戏版本' );
            redirect('game_management/viewgame/'. $game_id);
        }
    }
    public function delete_game($game_id){
        // 1. validation: input format valid ? check acl
        // 2. 找到所有的 cp_game_revision_info 据此找到所有的  cp_game_revision_resources
        // 3. 删除 cp_chn_game_info 里的相关 game_id
        // 3. 所有的  redis hash
        // 4. 删除  uploads 上的相应文件， 加日志
        // scope:
        // cp_game_revision_resources, cp_game_revision_info, cp_game_info, cp_chn_game_info
        // redis
        // uploads/文件， cdn 文件
        // 增加  log 文件
        $result =  $this->cp_game_info_model->delete_game($game_id);
        if(!$result){
            $this->load->library('session');
            $this->session->set_flashdata('flash_message', '删除失败');
            redirect('game_management/viewgame/'.$game_id);
        }else{
            // 删除 cocos_packingtool 里的相关游戏
            $this->db->delete('cp_contentprovider_games', array('game_id'=>$game_id));
            $this->load->library('session');
            $this->session->set_flashdata('flash_message', '删除成功');
            redirect('game_management/gamelist');
        }
    }

    public function edit_revision_package($revision_id)
    {
        $content = $this->load->view('edit_revision_package', array(), TRUE);
        $this->smarty->view('general.tpl', array('content'=>$content));
    }

    /**
     * 兼容列表的 CRUD
     */
    public function manage_compatibility($type)
    {
        if(is_null($type)){
        $this->load->model('game_management/cp_game_version_map_model');
        $msg='';
        $channel_sdk = $this->input->post('channel_sdk_version',true);
        $game_sdk = $this->input->post('game_sdk_version',true);
        $active = $this->input->post('is_active',true);
        if($active===FALSE){
            $active=0; // 默认为 0
        }
        if(!empty($channel_sdk) && !empty($game_sdk))
        {
            $is_exist = $this->cp_game_version_map_model->where(array('app_version'=>$channel_sdk,'sdk_version'=>$game_sdk))->count_all_results();
            if(!$is_exist)
            {
                $t = time();
                $ins['app_version'] = $channel_sdk;
                $ins['sdk_version'] = $game_sdk;
                $ins['creat_time'] = $t;
                $ins['modify_time'] = $t;
                $ins['del_flag'] = $active;
                $id = $this->cp_game_version_map_model->insert($ins);
                if(!$id){
                    $msg = "添加失败!";
                }
                else
                {
                    $msg = "添加成功!";
                }
            }
            else
            {
                $msg = "渠道SDK: {$channel_sdk} 与游戏SDK: {$game_sdk} 兼容关系已存在";
            }
        }
        $data = $this->cp_game_version_map_model->order_by('app_version desc')->get_all();
        $content = $this->load->view('manage_compatibility', array('data'=>$data,'msg'=>$msg), TRUE);
        $this->smarty->view('general.tpl', array('content'=>$content));
        }
        else
        {
            $this->load->model('game_management/cp_real_core_map_model');
            if(!empty($_POST))
            {
                $validation_config = array(
                    array(
                        'field' =>'runtime_ver',
                        'label' =>'Runtime Version',
                        'rules' =>'trim|required|min_length[1]|max_length[32]',
                    ),
                    array(
                        'field' =>'real_sdk_ver',
                        'label' =>'Real SDK 版本',
                        'rules' =>'trim|required|min_length[1]|max_length[32]',
                    ),
                );
                $this->form_validation->set_rules($validation_config);
                if($this->form_validation->run()===FALSE){
                    $msg = $this->form_validation->error_string();
                    $this->session->set_flashdata('flash_message',$msg);
                    redirect('game_management/manage_compatibility/runtime');
                }
                $runtime_id = $this->input->post('runtime_ver',TRUE);
                $real_sdk_ver = $this->input->post('real_sdk_ver',TRUE);
                $active = $this->input->post('is_active',TRUE);
                $insert = array(
                    'runtime_id' => $runtime_id,
                    'real_sdk_ver' => $real_sdk_ver,
                );
                $result = $this->cp_real_core_map_model->select('id')->where($insert)->get();
                if(!empty($result))
                {
                    $msg = "兼容关系已存在";
                    $this->session->set_flashdata('flash_message',$msg);
                    redirect('game_management/manage_compatibility/runtime');
                }
                $insert['create_time'] = date('Y-m-d H-i-s');
                $id = $this->cp_real_core_map_model->insert($insert);
                if(!$id)
                {
                    $this->session->set_flashdata('flash_message','添加失败');
                    redirect('game_management/manage_compatibility/runtime');
                }
                else
                {
                    $this->session->set_flashdata('flash_message','添加成功');
                    redirect('game_management/manage_compatibility/runtime');
                }
            }
            $data = $this->cp_real_core_map_model->get_all_comp();
            $this->load->model('system_management/cp_runtime_core_model');
            $runtimes = $this->cp_runtime_core_model->select(array('id','version','engine', 'engine_version'))->order_by('version desc, engine')->get_all();
            $version = array();
            $version[0] = 'Engine | Runtime';
            foreach ($runtimes as $runtime) {
                $version[$runtime['id']] = $runtime['engine'].'   '.$runtime['version'];
            }
            $content = $this->load->view('rt_compatibility', array('data' => $data,'version' => $version), TRUE);
            return $this->smarty->view('general.tpl', array('content' => $content));
        }
    }
    
    /**
     * 删除一条渠道sdk和游戏sdk的兼容关系
     * @param type $id
     */
    public function del_manage_compatibility($id,$type)
    {
        if(is_null($type))
        {
            if(!empty($id))
            {
                $this->load->model('game_management/cp_game_version_map_model');
                $is_del = $this->cp_game_version_map_model->delete($id);
                if($is_del)
                    $this->session->set_flashdata ( 'flash_message', '删除成功' );
                else
                    $this->session->set_flashdata ( 'flash_message', '删除失败' );
            }
                redirect("game_management/manage_compatibility");   
        }
        else {
            if(!empty($id))
            {
                $this->load->model('game_management/cp_real_core_map_model');
                $is_del = $this->cp_real_core_map_model->delete($id);
                if($is_del)
                    $this->session->set_flashdata ( 'flash_message', '删除成功' );
                else
                    $this->session->set_flashdata ( 'flash_message', '删除失败' );
            }
                redirect("game_management/manage_compatibility/runtime");   
        }
    }
    
    /**
     * 返回不带缓存的图片
     *
     * 返回不带缓存的图片
     *
     * $file = '/neatgame/9f7e1m/icon.PNG';
     * @param string
     */
    public function image_no_cache()
    {
        $file = $this->input->get_post('file');
        //$file = '/neatgame/9f7e1m/icon.PNG';
        $this->load->helper('game_install');
        $p = $this->completeurl($file);
        $mime = file_mime_type($p);

        $fc =  file_get_contents($p);

        #header('Content-Disposition:inline; filename="' . basename($file) . '"');
        if($mime){
            $type = 'Content-Type: '.$mime;
        }else{
            $type = 'Content-Type: image/png';
        }
        $this->output->set_header("Cache-Control: no-store, no-cache, must-revalidate");
        $this->output->set_header("Cache-Control: post-check=0, pre-check=0");
        $this->output->set_header("Pragma: no-cache");
        $this->output->set_output($fc);
        $this->output->set_header($type);
        // hack there is an anonying text/html content_type
        $this->output->set_status_header('300');
        return ;
    }
    
    /**
     * 创建一个随机文件夹
     * @param type $root_folder_path
     * @return boolean
     */
    protected function create_rand_folder($root_folder_path)
    {
        $try = 6;
        $full_path = '';
        while($try>0){
              $try--;
              $rnd_file_name = $this->GenPasswd();
              $rnd_file_name = $rnd_file_name[0] . '/' . $rnd_file_name[1] . '/' . $rnd_file_name;
              $full_path = $this->concat_path($root_folder_path, $rnd_file_name);
              if(!file_exists($full_path)){
                  $result = mkdir($full_path, 0777,TRUE);
                  if($result){
                     chmod($full_path, 0777);
                     return $full_path; 
                  }
              }
        }
        return FALSE; 
    }
}
