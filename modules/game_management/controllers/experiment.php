<?php 
/**
 * 实现新的 版本管理 功能
 *
 * @package experiment
 */

/**
 * Experiment class
 */

class Experiment extends Admin_Controller
{
    public function __construct() {
        parent::__construct();
        $this->upload_path = $this->get_full_path('');
        $this->load->helper('form');
        $this->load->library('smarty');
        $this->load->library('form_validation');
        $this->load->library('cocos_packingtool/uploadzip_library');
        $this->load->model('game_management/cp_channel_info_model');
        $this->load->model('game_management/cp_game_revision_info_model');
        $this->load->model('game_management/cp_game_revision_apk_model');
        $this->load->model('game_management/cp_game_revision_channel_resources_model');
        $this->load->model('game_management/cp_game_revision_chafen_model');
        $this->generic_path = $this->concat_path('uploads', 'generic');
        $this->access();
    }
    
    private function access() {
        $accesslevel = array(
            '/experiment/upload_resource' => 'create',
            '/experiment/game_revision_handler2' => 'create',
            '/experiment/chafen_list' => 'create',
            '/experiment/setup_channel' => 'create',
            '/experiment/ajax_remove_channel' => 'delete',
        ); 
        foreach($accesslevel as $aco => $al) {
            $GLOBALS['ACCESSLEVEL'][$aco] = $al ;
        }
    }

    protected function create_rand_folder($folder=''){
        $container_dir = $this->get_full_path($folder);
        for($i=0; $i<7; $i++){
            $rnd_name = $this->GenPasswd();
            $candidate_dir = $this->concat_path($container_dir, $rnd_name);
            if(!file_exists($candidate_dir)){
                if( mkdir($candidate_dir ,0770) ) {
                    chmod($candidate_dir,0770);
                    return  $this->concat_path($folder, $rnd_name);
                }
            }
        }
        return FALSE;
    }

    /**
     * get_upload_info
     *
     * 获取目录相关的信息
     *
     */
    protected function get_upload_info($server_file_path, $file_dir, $description='', $tags=''){
        $this->load->library('file_management/upload_info');
        $this->load->model('file_management/cp_upload_info_apk_model');
        $this->load->model('file_management/cp_upload_info_cpk_model');
        $this->load->model('file_management/cp_upload_info_manifest_model');
        $this->load->model('file_management/cp_upload_info_model');
        $opt_id = $this->session->userdata('user_id');

        $upload_info = Upload_info::probe_folder($server_file_path, $file_dir);
        Upload_info::update_info($upload_info, 'description', '用户填写');
        Upload_info::update_info($upload_info, 'manual_upload', '0');
        Upload_info::update_info($upload_info, 'opt_id',$opt_id);
        Upload_info::update_info($upload_info, 'description',$description);
        Upload_info::update_info($upload_info, 'tags',$tags);
        // 写表
        $UploadObj = new Upload_info($upload_info);
        if(!($UploadObj->has_apk || $UploadObj->has_manifest || $UploadObj->has_cpk)){
            // 没有信息不上传 batch
            return array();
        }
        $batch_id = $this->cp_upload_info_model->insert($UploadObj->cp_upload_info);
        if($batch_id) {
            Upload_info::update_info($upload_info, 'batch_id',$batch_id);
            $UploadObj->refresh_info($upload_info);
            //$UploadObj -> printthis();die();
            $this->cp_upload_info_manifest_model->insert($UploadObj->cp_upload_info_manifest);
            foreach ($UploadObj->cp_upload_info_apk as $record) {
                $this->cp_upload_info_apk_model->insert( $record);
            }
            foreach ($UploadObj->cp_upload_info_cpk as $record) {
                $this->cp_upload_info_cpk_model->insert( $record );
            }
            $msg = '成功. batchid: '. $batch_id; 
        }

        $apk_download_urls = array();
        if($UploadObj->has_apk){
            // bug here, if more than 1 apk in folder 
            foreach($UploadObj->cp_upload_info_apk as $apk_info){
                $apk_download_urls[basename($apk_info['file_name'], '.apk')] = $this->concat_path($apk_info['file_dir'], $apk_info['file_name']);
            }
        }
        if($UploadObj->has_manifest){
            $manifest_url = $this->concat_path($UploadObj->cp_upload_info_manifest['file_dir'], $UploadObj->cp_upload_info_manifest['file_name']);
        }else{
            $manifest_url = '';
        }
        if($UploadObj->has_cpk){
            $cpk_file_dir = $file_dir;
        }else{
            $cpk_file_dir = '';
        }
        if($UploadObj->has_assets){
            $mdf_url = $this->concat_path($UploadObj->cp_upload_info_manifest['file_dir'], 'assets.md5');
        }else{
            $mdf_url = '';
        }
        return array('batch_id' => $batch_id, 
            'file_dir'=> $file_dir,
            'cpk_file_dir'=>$file_dir,
            'apk_download_urls'=>$apk_download_urls,
            'mdf_url'=>$mdf_url,
            'manifest_url'=>$manifest_url); 
    }

    
    public function game_revision_handler2($game_id,$revision_id = FALSE){
        $this->load->library('session');
        if(!isset($game_id)){
            $this->session->set_flashdata('flash_message', '未指定游戏');
            redirect("game_management/gamelist");
        }
        $this->load->model('cp_game_info_model');
        $this->load->model('cp_game_revision_info_model');
        $game = $this->cp_game_info_model->get_game_detail($game_id);
        if(empty($game)){
            $this->session->set_flashdata ( 'flash_message', '没找到游戏' );
            redirect("game_management/gamelist");
        }
        $this->load->library('table');
        $other_ver = array();
        if($revision_id)
        {
            //修改版本
            $game_info = $this->cp_game_revision_info_model->get_game_revision_detail($revision_id,FALSE);
            $is_created = FALSE;
        }
        else
        {
            //新建版本
            $game_info = $this->cp_game_revision_info_model->get_revision_template();
            $is_created = TRUE;
            $other_ver_array = $this->cp_game_revision_info_model->get_game_revision_list($game_id);
            foreach ($other_ver_array as $key => $value) {
                array_push($other_ver, array('revision_id'=>$value['id'],'package_ver_code'=>$value['package_ver_code'],'hot_versioncode'=>$value['hot_versioncode']));
            }
            $other_ver = array_reverse($other_ver);
        }
        $lock = $game_info['is_published'] == 1;
        $game_info['game_id'] = $game['game_id'];
        $game_info['original_game_name'] = $game['game_name'];
        $game_info['game_mode'] = $game['game_mode'];
        $game_info['game_type'] = $game['game_type'];
        $game_info['game_key'] = $game['game_key'];
        $game_info['package_name'] = $game['package_name'];
        $game_info['cp_vendor'] = $game['cp_vendor'];
        $game_info['icon_url_face'] = $this->completeurl($game_info['icon_url']);
        $game_info['bg_picture_face'] = $this->completeurl($game_info['bg_picture']);
        $game_info['bg_music_face'] = $this->completeurl($game_info['bg_music']);
        $game_info['compatible_arch'] = explode(',',$game_info['compatible_arch']);
        if(empty($_POST))
        {
            $content = $this->load->view('add_revision2',array("other_ver"=>$other_ver,"new_game"=>$is_created,"lock"=>$lock,"game_info"=> $game_info, 'revision_id'=>  $revision_id?$revision_id:0),TRUE);
            $content .= $this->load->view('rtcore_patch_widget', array($revision_id => $revision_id), TRUE);
            return $this->smarty->view('general.tpl', array('content'=>$content));
        }
            //提交信息，修改或者新建
        $this->load->helper(array('form', 'url', 'game_install'));
        $this->load->library('form_validation');
        $validation_config = array(
            array(
                'field' => 'star',
                'label' => 'star',
                'rules' => 'required|numeric',
            ),
            array(
                'field' => 'game_name',
                'label' => '游戏版本名称',
                'rules' => 'trim|required|min_length[1]|max_length[32]',
            ),
            array(
                'field' => 'package_ver_code',
                'label' => '版本编号',
                'rules' => 'required|is_natural_no_zero',
            ),
            array(
                'field' => 'package_ver',
                'label' => '游戏版本',
                'rules' => 'trim|required|min_length[1]|max_length[32]',
            ),
            array(
                'field' => 'sdk_version',
                'label' => '游戏 SDK 版本',
                'rules' => 'trim|required|max_length[20]|greater_than[1]|xss_clean',
            ),
            array(
                'field' => 'engine_version',
                'label' => '引擎版本',
                'rules' => 'trim|required',
            ),
            array(
                'field' => 'orientation',
                'label' => '屏幕方向',
                'rules' => 'trim|required|numeric',
            ),
            array(
                'field' => 'game_desc',
                'label' => '游戏描述',
                'rules' => 'max_length[128]',
            ),
            array(
                'field' => 'hot_versioncode',
                'label' => '小版本编号',
                'rules' => 'trim|numeric',
            ),
            array(
                'field' => 'genuine_versioncode',
                'label' => '热更新号',
                'rules' => 'max_length[128]|numeric',
            ),
            array(
                'field' => 'genuine_versionname',
                'label' => '热更新名',
                'rules' => 'max_length[128]',
            ),
            array(
                'field' => 'tool_version',
                'label' => '工具版本',
                'rules' => 'trim|numeric',
            ),
        );
        $this->form_validation->set_rules($validation_config);
        if ($this->form_validation->run() === FALSE) {
            $content = $this->load->view('add_revision2', array("other_ver"=>$other_ver,"new_game"=>$is_created,"lock"=>$lock,"game_info" => $game_info, 'revision_id' => $revision_id ? $revision_id : 0,), TRUE);
            $content .= $this->load->view('rtcore_patch_widget', array('revision_id' => $revision_id?$revision_id:0), TRUE);
            return $this->smarty->view('general.tpl', array('content' => $content));
        }
        foreach ($game_info as $key => $value) {
            if (array_key_exists($key, $_POST) && ($this->input->post($key, TRUE) !== FALSE)) {
                $game_info[$key] = $this->input->post($key, TRUE);
            }
        }
        $game_info['compatible_arch'] = implode(',', $game_info['compatible_arch']);
        $select_rev_id = $this->input->post('select_rev_id',TRUE);
        $from_rev_id = $this->input->post('from_rev_id',TRUE);
//        $copy_chn = $this->input->post('copy_chn',TRUE);
        $copy_chn = FALSE;
        //关闭复制渠道选项
        //将默认为复制
        //1、该大版本首次建立，从复制源复制渠道资源
        //2、已存在其他小版本（复制的是小版本）,从其他版本同步
        $fromrevision = 0;
        if($select_rev_id)
        {
            $this->load->library('cocos_packingtool/uploadzip_library');
            //选定复制的版本
            if($select_rev_id != -1)
            {
                $fromrevision = $select_rev_id;
            }
            else
            {
                $tmp = $this->cp_game_revision_info_model->select('id')->where(array('id'=>$from_rev_id))->get();
                if(empty($tmp))
                {
                    $error_msg = '复制的版本不存在';
                    $content = $this->load->view('add_revision2', array("other_ver"=>$other_ver,"new_game"=>$is_created,"lock"=>$lock,"game_info" => $game_info, 'revision_id' => $revision_id ? $revision_id : 0, 'error_msg' => $error_msg), TRUE);
                    $content .= $this->load->view('rtcore_patch_widget', array('revision_id' => $revision_id?$revision_id:0), TRUE);
                    return $this->smarty->view('general.tpl', array('content' => $content));
                }
                $fromrevision = $from_rev_id;
            }
            $game_info_temp = $this->uploadzip_library->copy_revision($fromrevision, $game_id, TRUE);
            if ($game_info_temp === FALSE) {
                $error_msg = '复制的版本资源不存在';
                $content = $this->load->view('add_revision2', array("other_ver" => $other_ver, "new_game" => $is_created, "lock" => $lock, "game_info" => $game_info, 'revision_id' => $revision_id ? $revision_id : 0, 'error_msg' => $error_msg), TRUE);
                $content .= $this->load->view('rtcore_patch_widget', array('revision_id' => $revision_id?$revision_id:0), TRUE);
                return $this->smarty->view('general.tpl', array('content' => $content));
            }
            $game_info = $game_info_temp;
            $revision_id = $game_info['id'];
        }
        
        //文件上传
        $upload_filenames = array(
            'icon' => 'icon',
            'bgmusic' => 'bgmusic',
            'bgpic' => 'bgpic',
        );
        $upload_file_dbfield_mapping = array(
            'icon' => 'icon_url',
            'bgmusic' => 'bg_music',
            'bgpic' => 'bg_picture',
        );

        $files_input = array('icon' => TRUE, 'bgmusic' => TRUE, 'bgpic' => TRUE);
        foreach ($files_input as $key => $val) {
            if ($_FILES[$key]['error'])
                $files_input[$key] = FALSE;
        }
        if (empty($game_info['file_dir'])) {
            $tmp = $this->uploadzip_library->create_rand_folder($this->generic_path);
            $file_dir = str_replace('uploads/', '', $tmp);
            $file_dir = '/' . $file_dir; // 按约定目录前面要带斜杠
            $game_info['file_dir'] = $file_dir;
        } else {
            $file_dir = $game_info['file_dir'];
        }
        $this->load->library('upload');
        $full_upload_path = $this->get_full_path($file_dir);
        foreach ($files_input as $key => $val) {
            if ($val) {
                if (!file_exists($full_upload_path)) {
                    mkdir($full_upload_path, 0770, TRUE);
                    chmod($full_upload_path, 0770);
                }
                $upload_conf = array();
                $upload_conf['upload_path'] = $full_upload_path;
                $orig_ext = array_pop(explode('.', basename($_FILES[$key]['name'])));
                $upload_conf['file_name'] = $upload_filenames[$key] . '.' . $orig_ext;
                $upload_conf['allowed_types'] = '*';
                $upload_conf['overwrite'] = TRUE;
                $this->upload->initialize($upload_conf);
                $result = $this->upload->do_upload($key);
                if (!$result) {
                    $error_msg = $this->upload->display_errors();
                    $content = $this->load->view('add_revision2', array("other_ver"=>$other_ver,"new_game"=>$is_created,"lock"=>$lock,"game_info" => $game_info, 'revision_id' => $revision_id ? $revision_id : 0, 'error_msg' => $error_msg), TRUE);
                    $content .= $this->load->view('rtcore_patch_widget', array('revision_id' => $revision_id?$revision_id:0), TRUE);
                    return $this->smarty->view('general.tpl', array('content' => $content));
                } else {
                    $uploaddata = $this->upload->data();
                }
                $game_info[$upload_file_dbfield_mapping[$key]] = $this->concat_path($file_dir, $uploaddata['file_name']);

                $this->sync_with_cdn_file($game_info[$upload_file_dbfield_mapping[$key]],$revision_id);
            }
        }
        
        if($revision_id) {
            //修改版本
            $hot_ver = $game_info['hot_versioncode'];
            foreach ($game_info as $key => $value) {
                if(isset($_POST[$key]))
                {
                    $game_info[$key] = $this->input->post($key,TRUE);
                }
            }
            if(empty($game_info['hot_versioncode']) || !is_numeric($game_info['hot_versioncode']))
            {
                $game_info['hot_versioncode'] = $hot_ver;
            }
            $game_info['genuine_versioncode'] = empty($game_info['genuine_versioncode'])?NULL:$game_info['genuine_versioncode'];
            $game_info['compatible_arch'] = empty($game_info['compatible_arch'])?'':implode(',', $game_info['compatible_arch']);
            $this->cp_game_revision_info_model->update($revision_id,$game_info);

            if($select_rev_id)
            {
                $this->session->set_flashdata ( 'flash_message', '游戏创建成功' );
                return redirect('game_management/viewgame/'.$game_id);
            }
            else
            {
                $this->session->set_flashdata ( 'flash_message', '游戏修改成功' );
                return redirect('game_management/viewgame/'.$game_id);
            }
        } else {
            //新建版本但不复制版本
            $person_game = $this->input->post('person_game',TRUE);
            if(isset($person_game)){
                //创建单机游戏
                $resource = 'resources';
                $empty_manifest_path_xml = $this->get_full_path('resources/SceneManifest.xml');
                $empty_manifest_path_zip = $this->get_full_path('resources/SceneManifest.zip');
                copy($empty_manifest_path_xml, $this->get_full_path($this->concat_path($game_info['file_dir'],'SceneManifest.xml')));
                copy($empty_manifest_path_zip, $this->get_full_path($this->concat_path($game_info['file_dir'],'SceneManifest.zip')));
                $game_info['manifest_url'] = $this->concat_path($game_info['file_dir'],'SceneManifest.xml');
            }
            $game_info['hot_versioncode'] = time();
            $game_info['genuine_versioncode'] = empty($game_info['genuine_versioncode'])?NULL:$game_info['genuine_versioncode'];
            $new_id = $this->cp_game_revision_info_model->insert($game_info);
            $this->session->set_flashdata ( 'flash_message', '游戏创建成功' );
            return redirect('game_management/viewgame/'.$game_id);
        }
    }
    
    /**
     * 允许旧接口访问
     *
     * 通过设置标记位标识可以通过旧接口访问的游戏。
     *
     * 同一个包名只能有一个游戏可以访问
     */
    public function allow_old_api($game_id)
    {
        $this->load->model('game_management/cp_game_info_model');
        $game_info = $this->cp_game_info_model->get_game_detail($game_id);
        $result = $this->cp_game_info_model->where(array('package_name'=>$game_info['package_name'], 'channel_id'=>9999))->get_all();
        if(count($result)) {
            // error
            $this->session->set_flashdata('flash_message', '已经存在允许此接口的包');
        } else {
            $this->cp_game_info_model->update($game_id, array('channel_id'=>9999));
            $this->session->set_flashdata('flash_message', '成功');
        }
        redirect('game_management/viewgame/'.$game_id);
        // code...
    }

    public function disallow_old_api($game_id)
    {
        $this->load->model('game_management/cp_game_info_model');
        $this->cp_game_info_model->update($game_id, array('channel_id'=>0));
        $this->session->set_flashdata('flash_message', '成功');
        redirect('game_management/viewgame/'.$game_id);
    }

    /**
     * 设置版本的渠道信息
     *
     * 将 apk 映射到渠道
     */
    public function setup_channel($revision_id) {
        if(!isset($revision_id)) {
            die('no revision id specified');
        }
        $gameinfo = $this->cp_game_revision_info_model->get_game_detail($revision_id);
        if($gameinfo===FALSE) {
            show_404();
        }
        $mappings = $this->cp_game_revision_apk_model->get_mappings_extended($revision_id);
        $revision_resource = $this->cp_game_revision_info_model->select(array('icon_url','bg_picture','bg_music','game_desc','engine'))->where(array('id'=>$revision_id))->get();
        $this->load->model('common/third_sdk_model');
        foreach ($mappings as $key => $value) {
            $mappings[$key]['rev_icon_url'] = $revision_resource['icon_url'];
            $mappings[$key]['rev_bg_music'] = $revision_resource['bg_music'];
            $mappings[$key]['rev_bg_picture'] = $revision_resource['bg_picture'];
            $mappings[$key]['rev_game_desc'] = $revision_resource['game_desc'];
            $download_tran = array(
                'icon_url',
                'bg_music',
                'bg_picture',
                'rev_icon_url',
                'rev_bg_music',
                'rev_bg_picture',
                'apk_download_url',
                'genuine_apk_download_url',
                'so_apk_download_url',
            );
            foreach($download_tran as $field) {
                if(empty($mappings[$key][$field])){
                    if( in_array($field, array('rev_icon_url', 'rev_bg_picture')) ) {
                        $mappings[$key][$field] = '/asset/img/no_pic.gif';
                    }
                }else{
                    $mappings[$key][$field] = $this->completeurl($mappings[$key][$field]);
                    //$mappings[$key][$field] = $this->concat_path('/uploads', $mappings[$key][$field]);
                }
            }
            $mappings[$key]['third_sdk'] = 
            $this->db->select('version,sdk.id as int_version ,sdk.id as id')
                     ->from('cp_third_sdk as sdk')
                     ->join('cp_third_channel_sdk as chn_sdk','sdk.third_channel_sdk_id = chn_sdk.id')
                     ->join('cp_channel_info as chn','chn.third_channel_sdk_id = chn_sdk.id')
                     ->where(array('chn.channel_id' => $value['channel_id']))
                     ->order_by('id desc')->get()->result_array();
            $mappings[$key]['third_plug'] = 
            $this->db->select('version,plug.id as int_version ,plug.id as id')
                     ->from('cp_third_plugin as plug')
                     ->join('cp_third_channel_sdk as chn_sdk','plug.third_channel_sdk_id = chn_sdk.id')
                     ->join('cp_channel_info as chn','chn.third_channel_sdk_id = chn_sdk.id')
                     ->where(array('chn.channel_id' => $value['channel_id']))
                     ->order_by('id desc')->get()->result_array();
        }
        $data = array();
        $data['engine'] = $revision_resource['engine'];
        $data['game_id'] = $gameinfo['game_id']; 
        $data['mappings'] = $mappings; 
        $data['revision_id'] = $revision_id;
        $data['channel_list'] = $this->cp_channel_info_model->select(array('id', 'channel_id', 'channel_name'))->order_by('channel_id asc')->get_many();
        $apk_file_dir_arr = $this->cp_game_revision_info_model->select(array('is_published','cpk_file_dir', 'apk_download_url'))->get($revision_id);
        $apk_download_url_runtime = ''; // runtime 游戏
        if($apk_file_dir_arr) {
            $apk_file_dir = $apk_file_dir_arr['cpk_file_dir'];
            $is_published = $apk_file_dir_arr['is_published'];
            $apk_download_url = $apk_file_dir_arr['apk_download_url'];
            $apk_download_url_runtime = $apk_download_url;
            $data['apk_list'] = $this->get_apk_list($apk_file_dir);
            $data['is_published'] = $is_published;
        } else {
            $data['apk_list'] = array();
        }
        $filter_chn = function ($channel_info) use ($mappings) {
            foreach($mappings as $mapping) {
                if ($mapping['channel_id'] == $channel_info['channel_id']) return FALSE; 
            }
            return TRUE;
        };
        $filter_apk = function ($apk)  use($mappings) {
            foreach($mappings as $mapping){
                if ($mapping['apk_download_url'] == $apk['apk_download_url']) {
//                    return FALSE;
                }
            }
            return TRUE;
        };
        $data['channel_list'] = array_filter($data['channel_list'], $filter_chn);
        $gamemode = $gameinfo['game_mode'];
        if($gamemode == 4) {
            // 独立包不用设置 mapping
            $this->session->set_flashdata('flash_message', '独立包不需设置关联渠道');
            return redirect('game_management/viewgame/'. $gameinfo['game_id']);
        }elseif($gamemode == 1 || $gamemode == 3) { // 托管
            // 过滤掉已关联的
            $data['apk_list'] = array_filter($data['apk_list'], $filter_apk);
        }elseif($gamemode == 0 || $gamemode == 2 || $gamemode == 7) { //试玩
            // 不过滤
        }else {
            // 其他
            $this->session->set_flashdata('flash_message', '暂不支持此游戏类型');
            return redirect('game_management/viewgame/'. $gameinfo['game_id']);
        }
        //为旧的（apk放在版本文件夹的数据）建立渠道资源记录
        foreach ($data['apk_list'] as $key => $value) {
            $result = $this->cp_game_revision_channel_resources_model->select('id')->where(array('revision_id'=>$revision_id,'apk_download_url'=>$value['apk_download_url']))->get();
            if(!empty($result))  
                continue;
            $record = array(
                'apk_download_url' => $value['apk_download_url'],
                'bg_music' => '',
                'bg_picture' => '',
                'icon_url' => '',
                'revision_id' => $revision_id,
                'file_dir' => dirname($value['apk_download_url']),
            );
            $this->cp_game_revision_channel_resources_model->insert($record);
        }
        $apks = $this->db->select(array('apk_download_url', 'id'))->where(array('revision_id' => $revision_id))->get('cp_game_revision_channel_resources')->result_array();
        foreach ($apks as $key => $value) {
            $apk_download_url = $value['apk_download_url'];
            $apk_name = array_pop(explode('/', $apk_download_url));
            $apks[$key]['apk_name'] = $apk_name;
        }
        $data['apk_list'] = $apks;
        
        $orphans = $this->cp_game_revision_channel_resources_model->get_orphan_resources($revision_id);
        $data['chn_resources_orphans'] = $orphans;

        $system_config = CocosPlay_Config::get_instance();
        $remote_url = $system_config->get_value('system/sync/remote_url');
        if(!$remote_url) { 
            $remote_url = '';
            $data['enable_sync'] = FALSE;
        }else{
            $remote_url = trim($remote_url, '/');
            $data['enable_sync'] = TRUE;
        }
        $this->load->helper('custom_html_helper');
        $this->load->model('game_management/cp_game_revision_apk_so_model');
        $data['arches'] = $this->cp_game_revision_apk_so_model->where(array('revision_id'=>$revision_id))->get_all();
        $content = $this->load->view('setup_channel2', $data, TRUE);
        if($gamemode == 7) {
            $data['apk_download_url'] = $apk_download_url_runtime;
            $content = $this->load->view('setup_channel2', $data, TRUE);
        }
        $content .= $this->load->view('upload_widget', array('userdir'=>'tmp', 'revision_id'=>$revision_id), TRUE);
        if($data['enable_sync']) {
            $content .= $this->load->view('sync_widget', array('remote_url' => $remote_url), TRUE);
        }
        $content .= $this->load->view('channel_config_widget', array(), TRUE);
        return $this->smarty->view('general.tpl', array('content'=>$content));
    }

    public function get_apk_list($apk_file_dir) { 
        //$apk_file_dir = '/generic/z/o/zoqopg';
        $full_apk_file_dir = $this->concat_path($this->upload_path, $apk_file_dir);
        $apk_files = glob($full_apk_file_dir . '/*.apk');
        $fixname = function ($apk) use ($apk_file_dir) {
            $n = basename($apk);
            return array('apk_file_dir'=>$apk_file_dir , 'apk_download_url'=> $apk_file_dir . '/'. $n , 'apk_name' => $n); 
        };
        $apk_files = array_map($fixname, $apk_files);
        return $apk_files;
    }


    public function ajax_handler() {
        $action = $this->input->get_post('action', TRUE);
        switch ($action) {
            case 'remove':
                return $this->ajax_remove_channel();
            case 'setup':
                return $this->ajax_setup_channel();
            case 'edit':
                return $this->ajax_setup_channel(TRUE);
            case 'install_revision':
                return $this->ajax_install_revision();
            case 'replace_file':
                return $this->ajax_replace_file();
            case 'install_chafen':
                return $this->ajax_install_chafen();
            default:
                break;
        }
    }

    /**
     * ajax setup channel
     */
    public function ajax_setup_channel($update_on_duplicate=FALSE) {
        $revision_id = $this->input->get_post('revision_id',TRUE);
        $channel_id = $this->input->get_post('channel_id',TRUE);
        $apk_download_url = $this->input->get_post('apk_download_url',TRUE);
        $rev_chn_res_id = $this->input->get_post('rev_chn_res_id',TRUE);
        if($revision_id!==FALSE && $channel_id!==FALSE && $apk_download_url && $rev_chn_res_id){
            $result = $this->cp_game_revision_apk_model->add($revision_id, $channel_id, $apk_download_url, $rev_chn_res_id,$update_on_duplicate);
            if($result) {
                echo 'done';
                return;
            }
        }
        echo 'failed';
    }
    public function ajax_remove_channel() {
        $revision_id = $this->input->get_post('revision_id',TRUE);
        $channel_id = $this->input->get_post('channel_id',TRUE);
        if($revision_id!==FALSE && $channel_id!==FALSE) {
            $result = $this->cp_game_revision_apk_model->remove($revision_id, $channel_id);
            $this->cp_game_revision_apk_so_model->remove($revision_id,$channel_id);
            $orphans = $this->cp_game_revision_channel_resources_model->get_orphan_resources($revision_id);
            foreach ($orphans as $res) {
                $this->cp_game_revision_channel_resources_model->delete_orphan_resource($res['id']);
            }
        }
        if($result)
        {
            echo 'done';
            return ;
        }
        echo 'failed';
    }

    /**
     * 增加一个空的渠道资源映射
     *
     * 这个方法会在 cp_game_revision_apk 及 cp_game_revision_channel_resources 
     * 里建 一个关联的空的记录。这个记录只包含最基本的信息包括：revision_id, 
     * channel_id, revision_channel_resources_id 和 
     * cp_game_revision_channel_resources_model 的 file_dir
     *
     * @return type
     */
    public function add_chn_resource(){
        $this->load->library('upload');
        $revision_id = $this->input->post('revision_id',TRUE);
        $channel_id = $this->input->post('channel_id',TRUE);
        $apk_download_url = $this->input->post('apk_download_url',TRUE);
        $result = $this->cp_game_revision_apk_model->select('apk_download_url')->where(array('channel_id'=>$channel_id,'revision_id'=>$revision_id))->get();
        $this->load->library('cocos_packingtool/uploadzip_library');
        $vaild_chn = $this->uploadzip_library->validation_channel_id($channel_id);
        if(!isset($result['apk_download_url']) && $revision_id && $channel_id && $vaild_chn)
        {
            // setup placeholder
            $dir = $this->uploadzip_library->create_rand_folder($this->generic_path);
            $basename = basename($dir);
            $file_dir = $this->concat_path('/generic', $basename[0] . '/' . $basename[1] . '/' . $basename);
            $result = '';
            if($apk_download_url) {
                $result = $this->cp_game_revision_apk_model->create_empty_slot($revision_id, $channel_id, $file_dir,0, $apk_download_url);
            }else{
                $result = $this->cp_game_revision_apk_model->create_empty_slot($revision_id, $channel_id, $file_dir,0);
            }
            if($result){
                $this->session->set_flashdata('flash_message','添加成功');
            }else{
                $this->session->set_flashdata('flash_message','添加失败');
            }
        }else{
            $this->session->set_flashdata('flash_message','渠道已存在');
        }
        // 重新载入页面
        redirect('game_management/experiment/setup_channel/'.$revision_id);
    }
    /** 
     * 从 ion_auth copy 过来的方法, 加一层 smarty 的封闭
     */
    function _render_page($view, $data=null)
    {
        $this->viewdata = (empty($data)) ? $this->data: $data;
        $view_html = $this->load->view($view, $this->viewdata, TRUE);
        return $this->smarty->view('general.tpl', array('content'=>$view_html));
    }
    
    /**
     * 将上传的文件更新到 chn res 的相应位置 (field)
     */
    public function ajax_update_chn_res()
    {
        $revision_id = $this->input->get_post('revision_id',TRUE);
        $channel_id = $this->input->get_post('channel_id',TRUE);
        $file_name = $this->input->get_post('file_name',TRUE);
        $chnres_id = $this->input->get_post('chnres_id',TRUE);
        $res_msg = $this->cp_game_revision_channel_resources_model->where(array('id'=>$chnres_id))->get();
        $res_data = array();
        if(empty($res_msg)) {
            die('invalid chn resource id');
        }
        if(empty($res_msg['file_dir']))
        {
            die('file_dir missing! please delete and create new chn resource');
        }

        $this->load->helper(array('form', 'url', 'game_install'));
        //数据库目录
        $file_dir = $res_msg['file_dir'];
        //绝对路径
        $full_path = $this->get_full_path( $this->concat_path($file_dir, $file_name));
        if(!is_file($full_path)){
            die('File does not exist');
        }
        //上传文件的数据库路径 uploads/*
        $data_file_path = $this->concat_path($file_dir, $file_name);

        $apk_table_data = array();
        $chnres_table_data = array();
        if(ends_with($file_name, 'apk')) {
           if(starts_with($file_name,'_so_')){
                $apk_table_data['so_apk_download_url'] = $data_file_path;
                $apk_table_data['so_apk_md5'] = md5_file($full_path);
           }
           elseif(starts_with($file_name,'_'))
           {
                $apk_table_data['genuine_apk_download_url'] = $data_file_path;
                $apk_table_data['genuine_apk_md5'] = md5_file($full_path);
           }
           else{
                $apk_name = str_replace('.apk', '', $file_name);
                $apk_table_data['apk_download_url'] = $data_file_path;
                $apk_table_data['apk_md5'] = md5_file($full_path);
                $apk_table_data['apk_file_dir'] = $file_dir;
                $chnres_table_data['apk_download_url'] = $data_file_path;
                $chnres_table_data['apk_name'] = $apk_name;
                //额外处理解压真APK
                $genuine_apk_full_path = $this->get_full_path( $this->concat_path($file_dir, '_' . $file_name));
                if (!is_file($genuine_apk_full_path)) {
                    $ext_result = extract_genuine_apk($full_path);
                }
                $genuine_apk = $this->concat_path($file_dir, '_' . $file_name);
                $apk_table_data['genuine_apk_download_url'] = $genuine_apk;
                $apk_table_data['genuine_apk_md5'] = md5_file($this->get_full_path($genuine_apk));
                $this->sync_with_cdn_file($genuine_apk,$revision_id);
           }
        }elseif(ends_with($file_name, 'mp3') || ends_with($file_name, 'ogg')) {
           $chnres_table_data['bg_music'] = $data_file_path;
        }elseif(ends_with($file_name, 'png') || ends_with($file_name, 'jpg') || ends_with($file_name, 'jpeg')) { 
            $dimension = getimagesize($full_path);
            if ( max($dimension) > 512 ) { 
                $system_config = $this->db->select('value')->where(array('path'=>'system/toggle_watermark'))->get('cp_system_config')->result_array();
                $toggle_watermark = $system_config['0']['value'];
                if($toggle_watermark){
                    //开启水印
                    $this->load->library('common/watermark');
                    $water_png = $this->get_full_path('watermark/watermark.png');
                    $result = $this->watermark->add_watermark($full_path,$water_png);
                    if(!$result){
                        echo $this->watermark->last_error();
                        return ;
                    }
                    $origin_pic = $this->concat_path(dirname($full_path),'origin_'.basename($full_path));
                    rename($full_path,$origin_pic);
                    rename($result, $full_path);
                }
                $chnres_table_data['bg_picture'] = $data_file_path;
            }else{
               $chnres_table_data['icon_url'] = $data_file_path;
            }
        }elseif(ends_with($file_name, 'zip')) {
            $result = $this->uploadzip_library->glob_arch($revision_id,$channel_id,$file_dir);
            if($result){
                echo 'done';
                return ;
            }else{
                echo '无法识别的类型';
                return ;
            }
        }else{
            echo '无法识别的类型';
            return ;
        }
        if(!empty($chnres_table_data)){
            $this->cp_game_revision_channel_resources_model->update($chnres_id, $chnres_table_data);
        }
        if(!empty($apk_table_data)){
            $apk_table_data['modify_time'] = date('Y-m-d H-i-s');
            $this->db->where(array('channel_id'=>$channel_id,'revision_id'=>$revision_id))->update('cp_game_revision_apk',$apk_table_data);
        }
        $this->sync_with_cdn_file($data_file_path,$revision_id);
        echo 'done';
    }

    /**
     * 解压并关联渠道资源
     */
    public function ajax_unzip_chn_res()
    {
        $revision_id = $this->input->get_post('revision_id',TRUE);
        $channel_id = $this->input->get_post('channel_id',TRUE);
        //$channel_id = $this->input->get_post('channel',TRUE);
        $file_name = $this->input->get_post('file_name',TRUE);
        $file_dir = $this->input->get_post('upload_dir',TRUE);
        $field_name = 'channel_res';
        $file_path = $this->concat_path('uploads/tmp',$file_name);
        $chn_dir = $this->uploadzip_library->unzip_to_dir($file_path);
        list($rev_chn_result,$rev_chn_msg) = $this->uploadzip_library->revision_chn_related($revision_id,$chn_dir);
        if(!$rev_chn_result)
        {
            $mas_mapping = array(
                '1303' => '文件不完整',
                '1309' => '数据库错误'
            );
            //TODO:删除文件
            $msg = $mas_mapping[$rev_chn_msg];
            echo $msg; 
            return; 
        }
        $msg = 'done';
        //成功建立渠道资源记录,判断是否关联到渠道
        if($channel_id)
        {
            list($related_code,$related_msg) = $this->uploadzip_library->relate_to_chn($rev_chn_msg,$channel_id,$revision_id);
            if(!$related_code)
            {
                $msg = $mas_mapping[$rev_chn_msg];
                $this->session->set_flashdata ( 'flash_message',$msg);
                return redirect('game_management/experiment/setup_channel/'.$revision_id);
            }
            //$msg = '上传成功并关联到渠道'.$channel_id;
        }
        echo $msg;
    }
    
    /**
     * 删除没用的渠道资源
     */
    public function ajax_delete_chnres($chnres_id) {
        // 
        if(!$chnres_id) {
            echo 'no chnresid specified' ;
            return ;
        }
        $result = $this->cp_game_revision_channel_resources_model->delete_orphan_resource($chnres_id);
        echo 'done';
    }
    
    public function upload_resource() {
        $ziptype_mapping = array(
            1 => '通用资源和渠道资源',
            2 => '渠道资源',
            3 => '通用资源',
            4 => '差分资源',
            5 => '通用资源,真实apk',
            6 => '差分资源，真实apk',
            7 => '小版本资源',
            8 => 'runtime 游戏资源',
            99 => '未识别的类型',
        );
        $this->load->library('upload');
        $this->load->library('cocos_packingtool/uploadzip_library');
        $user_id = $this->session->userdata('user_id');
        $files_exist = glob($this->concat_path(FCPATH, "uploads/syncdir/$user_id/*.*"));
        $files_msg = array();
        foreach ($files_exist as $key => $value) {
                $ziptype = 99;
                $gamename = '未知';
                $package_ver_code = '未知';
                $files_status_code = 0;
                $remarks = '';
            list($checkcode,$resultmsg) = $this->uploadzip_library->check_zip_info($value);
            $allow_to_create = TRUE;
            if(!is_array($resultmsg) && !$checkcode)
            {
                //未识别的类型
                $ziptype = 99;
                $files_status_code = 0;
                $gamename = '未知';
                $package_ver_code = '未知';
                $remarks = '请检查信息文件info.json';
                $allow_to_create = FALSE;
            }
            else
            {
                //识别类型
                if(!$checkcode)
                {
                    //文件不完整
                    $files_status_code = 0;
                    if($resultmsg && $resultmsg['ziptype'] && $resultmsg['ziptype'] == 8) {
                        $remarks = $resultmsg['errormsg'];
                    }else{
                        $remarks = $resultmsg;
                    }
                }
                else
                {
                    //文件完整
                    $files_status_code = 1;
                    $remarks = '';
                }
                $package_ver_code = $resultmsg['gameinfo']['package_ver_code'];
                $gamekey = $resultmsg['gameinfo']['game_key'];
                $ziptype = $resultmsg['ziptype'];
                $game = $this->cp_game_info_model->select('*')->where(array('game_key'=>$gamekey))->get();
                if(!isset($game['game_name']))
                {
                    $files_status_code = 0;
                    $gamename = '不存在';
                    $package_ver_code = '不存在';
                    $remarks .= '所属游戏不存在';
                }
                else
                {
                    $gamename = $game['game_name'];
                }
                
                switch ($ziptype) {
                    case 1:
                        break;
                    case 3:
                        break;
                    case 2:
                        $is_ver_exist = $this->cp_game_revision_info_model->get_hotlist_by_ver($gamekey,$package_ver_code);
                        if(empty($is_ver_exist))
                        {
                            $package_ver_code = '不存在';
                            $allow_to_create = FALSE;
                            $remarks .= '所属版本不存在';
                            $files_status_code = 0;
                        }
                        break;
                    case 4:
                        $is_ver_exist = $this->uploadzip_library->validation_vercode($gamekey,$package_ver_code,FALSE);
                        if(!$is_ver_exist)
                        {
                            $package_ver_code = '不存在';
                            $allow_to_create = FALSE;
                            $files_status_code = 0;
                        }
                        $support_ver_code = $resultmsg['gameinfo']['support_ver_code'];
                        $is_support_ver_exist = $this->uploadzip_library->validation_vercode($gamekey,$support_ver_code,FALSE);
                        if(!$is_support_ver_exist)
                        {
                            $allow_to_create = FALSE;
                            $remarks .= "支持差分升级的版本不存在";
                            $files_status_code = 0;
                        }
                        break;
                    case 7:
                        $is_ver_exist = $this->uploadzip_library->validation_vercode($gamekey,$resultmsg['gameinfo']['from_hot_versioncode'],FALSE);
                        if(!$is_ver_exist)
                        {
                            $package_ver_code = '不存在';
                            $allow_to_create = FALSE;
                            $files_status_code = 0;
                        }
                    case 8:  // runtime 游戏
                    default:
                        break;
                }
            }
            
            if(is_array($remarks))
            {
                //文件不完整
                $fails = array(
                'required' => '缺失',
                'numeric' => '必需是数字',
                'gamemode' => '不合法',
                'version' => '不合法',
                'orientation' => '不合法',
                );
                $gameinfo = $remarks['gameinfo'];
                $fileinfo = $remarks['fileinfo'];
                $remarks = '';
                foreach ($gameinfo as $key2 => $value2) {
                    if(key_exists($value2,$fails))
                    {
                        $remarks .= $key2.$fails[$value2];
                        $remarks .= "<br>";
                    }
                }
                foreach ($fileinfo as $key2 => $value2) {
                    if(key_exists($value2,$fails))
                    {
                        $remarks .= $key2.$fails[$value2];
                        $remarks .= "<br>";
                    }
                }
            }
            $files_msg[basename($value)] = array(
                'ziptype' => $ziptype_mapping[$ziptype],
                'game_name' => $gamename,
                'package_ver_code' => $package_ver_code,
                'files_status_code' => $files_status_code,
                'remarks' => $remarks,
            );
        }
        return $this->_render_page('upload_resource',array('files_msg'=>$files_msg, 'userdir'=>''));
    }
    
    /**
     * 从用户暂存区处理文件
     */
    public function user_resource_handler() {
        $action = $this->input->get_post('action',TRUE);
        $filename = $this->input->get_post('filename',TRUE);
        $opt_id = $this->session->userdata('user_id');
        $filepath = $this->get_full_path('syncdir/'.$opt_id.'/'.$filename);
        switch ($action) {
            case 'delete':
                unlink($filepath);
                $flashmsg = "资源删除成功!";
                $this->session->set_flashdata('flash_message', $flashmsg);
                break;
            case 'create':
                list($code,$msg) = $this->uploadzip_library->install_game_handler($filepath);
                $flashmsg = "资源创建完毕!";
                $this->session->set_flashdata('flash_message', $flashmsg);
                break;
            default:
                break;
        }
        return redirect('game_management/experiment/upload_resource');
    }
    
    /**
     * 修改游戏信息
     * @return type
     */
    public function edit_game() {
        $game_id = $this->input->post('game_id',TRUE);
        if(!$game_id)
        {
            die('exception');
        }
        $validation_config = array(
            array(
                'field' =>'gamename',
                'label' =>'游戏名称',
                'rules' =>'trim|required|min_length[1]|max_length[32]',
            ),
            array(
                'field' =>'game_type',
                'label' =>'游戏类型',
                'rules' =>'required',
            ),
            array(
                'field' =>'game_key',
                'label' =>'Game Key',
                'rules' =>'trim|required|min_length[1]|max_length[20]|alpha_dash|xss_clean',
            ),
            array(
                'field' =>'supplier',
                'label' =>'提供商',
                'rules' =>'trim|required|min_length[1]|max_length[64]|xss_clean',
            ),
        );
        $this->form_validation->set_rules($validation_config);
        if($this->form_validation->run()===FALSE){
            $msg = $this->form_validation->error_string();
            $this->session->set_flashdata('flash_message',$msg);
            redirect('game_management/viewgame/'.$game_id);
        }
        $data = array();
        $data['game_name'] = $this->input->post('gamename',TRUE);
        $data['game_type'] = $this->input->post('game_type',TRUE);
        $data['game_key'] = $this->input->post('game_key',TRUE);
        $data['cp_vendor'] = $this->input->post('supplier',TRUE);
        if($data['game_type'] == 0)
        {
            $this->session->set_flashdata('flash_message','请指定游戏类型');
        redirect('game_management/viewgame/'.$game_id);
        }
        $this->cp_game_info_model->update($game_id,$data);
        $this->session->set_flashdata('flash_message','更新成功');
        redirect('game_management/viewgame/'.$game_id);
    }
    public function edit_revision() {
        $revision_id = trim($this->input->get_post('revision_id',TRUE));
        if(!$revision_id)
        {
            die('exception');
        }
        $validation_config = array(
            array(
                'field' =>'star',
                'label' =>'star',
                'rules' =>'required|numeric',
            ),
            array(
                'field' =>'game_name',
                'label' =>'游戏版本名称',
                'rules' =>'trim|required|min_length[1]|max_length[32]',
            ),
            array(
                'field' =>'package_ver_code',
                'label' =>'版本编号',
                'rules' =>'required|is_natural_no_zero',
            ),
            array(
                'field' =>'package_name',
                'label' =>'包名',
                'rules' =>'trim|required|min_length[6]',
            ),
            array(
                'field' =>'package_ver',
                'label' =>'游戏版本',
                'rules' =>'trim|required|min_length[1]|max_length[32]',
            ),
            array(
                'field' =>'sdk_version',
                'label' =>'游戏 SDK 版本',
                'rules' =>'trim|required|max_length[20]|greater_than[1]|xss_clean',
            ),
            array(
                'field' =>'engine_version',
                'label' =>'引擎版本',
                'rules' =>'trim',
            ),
            array(
                'field' =>'user_system',
                'label' =>'用户系统',
                'rules' =>'trim',
            ),
            array(
                'field' =>'payment',
                'label' =>'支付系统',
                'rules' =>'trim|required',
            ),
            array(
                'field' =>'orientation',
                'label' =>'屏幕方向',
                'rules' =>'trim|required|numeric',
            ),
            array(
                'field' =>'game_desc',
                'label' =>'游戏描述',
                'rules' =>'max_length[128]',
            )
        );
        $this->form_validation->set_rules($validation_config);
        if($this->form_validation->run()===FALSE){
            $msg = $this->form_validation->error_string();
            $this->session->set_flashdata('flash_message', $msg);
            redirect('game_management/experiment/get_revision_detail?revision_id='.$revision_id);
        }
        
        $user_system = trim($this->input->get_post('user_system',TRUE));
        $payment = trim($this->input->get_post('payment',TRUE));
        $game_id = trim($this->input->get_post('game_id',TRUE));
        $game_name = trim($this->input->get_post('game_name',TRUE));
        $game_desc = trim($this->input->get_post('game_desc',TRUE));
        $star = trim($this->input->get_post('star',TRUE));
        $sdk_version = trim($this->input->get_post('sdk_version',TRUE));
        $package_ver = trim($this->input->get_post('package_ver',TRUE));
        $package_ver_code = trim($this->input->get_post('package_ver_code',TRUE));
        $engine_version = trim($this->input->get_post('engine_version',TRUE));
        $orientation = trim($this->input->get_post('orientation',TRUE));
        $is_maintain = trim($this->input->get_post('is_maintain',TRUE));
        $maintain_tip = trim($this->input->get_post('maintain_tip',TRUE));
//        $icon_url_path = trim($this->input->get_post('icon_url_path',TRUE));
//        $bg_picture_path = trim($this->input->get_post('bg_picture_path',TRUE));
//        $bg_music_path = trim($this->input->get_post('bg_music_path',TRUE));
        $data = array(
            'game_name' => $game_name,
            'game_desc' => $game_desc,
            'star' => $star,
            'sdk_version' => $sdk_version,
            'package_ver' => $package_ver,
            'package_ver_code' => $package_ver_code,
            'engine_version' => $engine_version,
            'orientation' => $orientation,
            'is_maintain' => $is_maintain,
            'maintain_tip' => $maintain_tip,
            'user_system' => $user_system,
            'payment' => $payment
        );
        $this->cp_game_revision_info_model->update($revision_id,$data);
        $input_files = array(
            'icon_url' => 'icon',
            'bg_picture' => 'background',
            'bg_music' => 'music'
        );
        $allowed_types = array(
            'icon_url' => 'jpg|png',
            'bg_picture' => 'jpg|png',
            'bg_music' => 'ogg|mp3|wav'
        );
        //处理上传
        $this->load->library('upload');
        foreach ($input_files as $key => $value) {
            if(!$_FILES[$key]['error'])
            {
                //判断是否存在原来的资源
                //直接替换掉原来的资源，路径不变，数据库不变
                $file_dir_res = $this->cp_game_revision_info_model->select('file_dir')->where(array('id'=>$revision_id))->get();
                $file_dir = array_pop($file_dir_res);
                $orig_ext = array_pop(explode('.',basename($_FILES[$key]['name'])));
                $config = array();
                $config['file_name'] = $value.'.'.$orig_ext;
                $config['overwrite'] = TRUE;
                $config['upload_path'] = $this->get_full_path($file_dir);
                $config['allowed_types'] = $allowed_types[$key];
                $this->upload->initialize($config);
                $result = $this->upload->do_upload($key);
                if(!$result)
                {
                    $error_msg = $this->upload->display_errors();
                    $this->session->set_flashdata('flash_message', $error_msg);
                    redirect('game_management/viewgame/'.$game_id);
                }
                else
                {
                    $file_path = $this->concat_path($file_dir,$config['file_name']);
                    $this->cp_game_revision_info_model->update($revision_id,array($key=>$file_path));
                }
            }
        }
        $this->session->set_flashdata('flash_message', '更新成功');
        redirect('game_management/experiment/get_revision_detail?revision_id='.$revision_id);
    }
    
    /**
     * 获取通用文件列表
     * @param type $revision_id
     */
    protected function get_files_list($revision_id){
        $revision = $this->cp_game_revision_info_model->get_game_revision_detail($revision_id ,FALSE);
        if(empty($revision))
        {
            return array();
        }
        $file_dir = $revision['file_dir'];
        $full_dir_path = $this->get_full_path($file_dir);
        $files = glob($this->concat_path($full_dir_path,'*'));
        foreach ($files as $key => $value) {
            $file = basename($value);
            $file_path = $this->get_full_path($this->concat_path($file_dir,$file));
            $download_url = $this->completeurl($this->concat_path($file_dir,$file));
            $filesize = filesize($file_path);
            
            if($filesize>1024*1024)
            {
                $filesize = number_format($filesize/(1024*1024),2).' MB';
            }
            else
            {
                $filesize = number_format($filesize/(1024),2).' KB';
            }
            $files[$file] = array(
                'download_url' => $download_url,
                'md5' => md5_file($file_path),
                'size' => $filesize,
                'file_path' => $value
            );
            unset($files[$key]);
        }
        clearstatcache();
        return $files;
    }
    
    public function game_revision_form($revision_id) {
        $revision_info = $this->get_revision_info($revision_id);
        $this->load->view('game_revision_form',array('game_revision'=>$revision_info));
    }
    /**
     * 加载版本详细信息的页面
     */
    /*
    public function get_revision_detail() {
        $revision_id = $this->input->get_post('revision_id',TRUE);
        if(!$revision_id)
        {
            die('exception');
        }
        $files_list = $this->get_files_list($revision_id);
        $revision_info = $this->get_revision_info($revision_id);
        $game_id = $revision_info['game_id'];
        $result = $this->cp_game_info_model->select('game_mode')->where(array('game_id'=>$game_id))->get();
        $revision_info['game_mode'] = $result['game_mode'];
        $chafen_list = $this->get_support_ver($revision_id,$game_id);
        $userid = $this->session->userdata('user_id');
        if($revision_info['game_mode'] ==4)
        {
            $data = array('game_id'=>$game_id);
        }
        else
        {
            $data = $this->setup_channel($revision_id);
        }
        $data['files_list'] = $files_list;
        $data['chafen_list'] = $chafen_list;
        $data['game_revision'] = $revision_info;
        $revision_tmp = $this->concat_path($this->concat_path('syncdir', $userid),'revision_tmp');
        if(!is_dir($this->get_full_path($revision_tmp)))
        {
            mkdir($this->get_full_path($revision_tmp), 0770);
        }
        $data['revision_tmp'] = $revision_tmp;
        $content = $this->load->view('game_revision_detail',$data,TRUE);
        $content .= $this->load->view('upload_widget', array('userdir'=>'syncdir/'.$userid,'revision_id'=>$revision_id), TRUE);
        return $this->smarty->view('general.tpl' , array('content'=>$content));
    }
     * */
    
    public function get_revision_info($revision_id){
        $result = $this->cp_game_revision_info_model->get_game_revision_detail($revision_id,FALSE);
        if(empty($result))
        {
            return array();
        }
        $resources = array(
            'icon_url',
            'bg_picture',
            'bg_music',
        );
        foreach ($resources as $key => $value) {
            if(!empty($result[$value]))
            {
                $result[$value] = $this->completeurl($result[$value]);
            }
        }
        return $result;
    }
    
    /**
     * 获取支持升级的版本号
     * @param type $revision
     * @param type $game_id
     */
    protected function get_support_ver($revision_id,$game_id) {
        $revision_info = $this->get_revision_info($revision_id);
        $result = $this->cp_game_revision_info_model->get_game_revision_list($game_id,TRUE);
        $chafen_list = array();
        foreach ($result as $key => $value) {
            if($revision_info['hot_versioncode'] <= $value['hot_versioncode'])
            {
                unset($result[$key]);
                continue;
            }
            $chafen = $this->cp_game_revision_chafen_model->select('*')->where(array('revision_id'=>$revision_id,'support_ver_code'=>$value['hot_versioncode']))->get();
            if(empty($chafen))
            {
                $chafen_list[$value['id']] = array(
                    'download_url' => '',
                    'md5' => '',
                    'size' => '',
                    'file_path' => '',
                    'support_ver_code' => $value['hot_versioncode'],
                    'support_package_ver_code' => $value['package_ver_code'],
                    'file_name' => '',
                );
                continue;
            }
            $chafen_url = $chafen['chafen_url'];
            $file_path = $this->get_full_path($chafen_url);
            $file_name = basename($chafen_url);
            $download_url = $this->completeurl($chafen_url);
            $filesize = filesize($file_path);
            if($filesize>1000*1000)
            {
                $filesize = number_format($filesize/(1000*1000),2).' MB';
            }
            else
            {
                $filesize = number_format($filesize/(1000),2).' KB';
            }
            $chafen_list[$value['id']] = array(
                'download_url' => $download_url,
                'md5' => md5_file($file_path),
                'size' => $filesize,
                'file_path' => $file_path,
                'support_ver_code' => $chafen['support_ver_code'],
                'support_package_ver_code' => $chafen['support_package_ver_code'],
                'file_name' => $file_name,
            );
        }
        clearstatcache();
        return array_reverse($chafen_list);
    }
    /**
     * 获取差分文件列表
     * @param type $revision_id
     */
    protected function get_chafen_list($revision_id) {
        $chafens = $this->cp_game_revision_chafen_model->get_chafen_revision($revision_id);
        $chafen_list = array();
        if(empty($chafens))
        {
            return array();
        }
        foreach ($chafens as $key => $value) {
            $chafen_url = $value['chafen_url'];
            $file_path = $this->get_full_path($chafen_url);
            $file_name = basename($chafen_url);
            $download_url = $this->completeurl($chafen_url);
            $filesize = filesize($file_path);
            if($filesize>1024*1024)
            {
                $filesize = number_format($filesize/(1024*1024),2).' MB';
            }
            else
            {
                $filesize = number_format($filesize/(1024),2).' KB';
            }
            $chafen_list[$file_name] = array(
                'download_url' => $download_url,
                'md5' => md5_file($file_path),
                'size' => $filesize,
                'file_path' => $file_path,
                'support_ver_code' => $value['support_ver_code'],
                'support_package_ver_code' => $value['support_package_ver_code'],
            );
        }
        clearstatcache();
        return $chafen_list;
    }
    
    /**
     * 游戏版本列表/上传游戏版本资源 （已废弃）
     */
    protected function ajax_install_revision() {
        $file_name = $this->input->get_post('file_name',TRUE);
        if(!ends_with($file_name,'zip'))
        {
            echo '格式不正确';
            return ;
        }
        $upload_dir = $this->input->get_post('upload_dir',TRUE);
        $zip_path = $this->get_full_path($this->concat_path($upload_dir,$file_name));
        $game_id = $this->input->get_post('game_id',TRUE);
        $game_info = $this->cp_game_info_model->get_game_detail($game_id);
        list($code,$msg) = $this->uploadzip_library->install_game_handler($zip_path,$game_info['game_key'],1);
        if($code == 0)
        {
            echo 'done';
        }
        else
        {
            //失败删除包
            echo '创建失败';
        }
    }
    
    /**
     * 将暂存区文件上传到版本资源文件夹
     * 若存在原文件则替换文件
     * 用于文件列表
     */
    protected function ajax_replace_file() {
        $file_name = $this->input->get_post('file_name',TRUE);
        $upload_dir = $this->input->get_post('upload_dir',TRUE);
        $revision_id = $this->input->get_post('revision_id',TRUE);
        $revision_info = $this->cp_game_revision_info_model->get_game_revision_detail($revision_id,FALSE);
        $file_dir = $revision_info['file_dir'];
        $old_file_path = $this->get_full_path($this->concat_path($file_dir,$file_name));
        if(is_file($old_file_path))
        {
            unlink($old_file_path);
        }
        $new_file_path = $this->get_full_path($this->concat_path($upload_dir,$file_name));
        rename($new_file_path, $old_file_path);
        $this->sync_with_cdn_file($this->concat_path($file_dir,$file_name),$revision_id);
        echo 'done';
    }
    
    /**
     * 处理差分资源
     */
    protected function ajax_install_chafen() {
        $file_name = $this->input->get_post('file_name',TRUE);
        if(!ends_with($file_name,'cpk'))
        {
            echo '格式不正确';
            return ;
        }
        $upload_dir = $this->input->get_post('upload_dir',TRUE);
        $revision_id = $this->input->get_post('revision_id',TRUE);
        $support_ver = $this->input->get_post('support_ver',TRUE);
        $tmp_file_path = $this->get_full_path($this->concat_path($upload_dir,$file_name));
        $tmp_dir = dirname($tmp_file_path);
        $revision_info = $this->cp_game_revision_info_model->get_game_revision_detail($revision_id,FALSE);
        $file_dir = $revision_info['file_dir'];
        $new_file_name = 'chafen_'.$support_ver.'.cpk';
        $new_file_path = $this->concat_path($this->get_full_path($file_dir),$new_file_name);
        $new_file_base = $this->get_full_path($file_dir);
        $game_id = $this->cp_game_revision_info_model->get_game_id($revision_id);
        if(is_file($new_file_path))
        {
            //执行替换
            $result1 = unlink($new_file_path);
            $result2 = rename($tmp_file_path, $new_file_path);
        }else{
            if(!is_dir($new_file_base)) {
                mkdir($new_file_base, 0770, TRUE);
            }
            $result2 = rename($tmp_file_path, $new_file_path);
        }
        $chafen_existed = $this->cp_game_revision_chafen_model->select('id')
            ->where(array('revision_id'=> $revision_id, 'support_ver_code'=>$support_ver))
            ->limit(1)->get();
        if($chafen_existed) {
            $this->sync_with_cdn_file($this->concat_path($file_dir,$new_file_name),$revision_id);
        } else {
            $versioncodearray = $this->cp_game_revision_info_model->select('package_ver_code')->where(array('hot_versioncode'=>$support_ver,'game_id'=>$game_id))->get();
            $support_package_ver_code = array_pop($versioncodearray);

            $data = array(
                'revision_id' => $revision_id,
                'support_ver_code' => $support_ver,
                'chafen_url' => $this->concat_path($file_dir,$new_file_name),
                'support_package_ver_code' => $support_package_ver_code
            );
            $this->cp_game_revision_chafen_model->insert($data);
            $this->sync_with_cdn_file($this->concat_path($file_dir,$new_file_name),$revision_id);
        }
        echo 'done';
    }

    public function set_unpublished() {
        $revision_id = $this->input->get('revision_id',TRUE);
        $game_id = $this->input->get('game_id',TRUE);
        $this->cp_game_revision_info_model->set_published_to($revision_id,0);
        $this->session->set_flashdata('flash_message', '成功');
        redirect('game_management/viewgame/'.$game_id);
    }
    
    public function chafen_list() {
        $revision_id = $this->input->get('revision_id');
        $game_id = $this->input->get('game_id',TRUE);
        $chafen_list = $this->get_support_ver($revision_id,$game_id);
        $userid = $this->session->userdata('user_id');
        $revision_tmp = $this->concat_path($this->concat_path('syncdir', $userid),'revision_tmp');
        if(!is_dir($this->get_full_path($revision_tmp)))
        {
            mkdir($this->get_full_path($revision_tmp),0770,TRUE);
        }
        $revision_info = $this->get_revision_info($revision_id);
        $result = $this->cp_game_info_model->select('game_mode')->where(array('game_id'=>$game_id))->get();
        $revision_info['game_mode'] = $result['game_mode'];
        $content = $this->load->view('chafen_list',array('game_id'=>$game_id,'chafen_list'=>$chafen_list,'revision_tmp'=>$revision_tmp,'game_revision'=>$revision_info),TRUE);
        $content .= $this->load->view('upload_widget', array('userdir'=>'syncdir/'.$userid,'revision_id'=>$revision_id), TRUE);
        return $this->smarty->view('general.tpl' , array('content'=>$content));
    }

    /**
     * active_apk
     *
     * 将渠道资源设为上线状态
     */
    public function active_apk() {
        $revision_id = $this->input->get('revision_id',TRUE);
        $apk_id = $this->input->get('apk_id',TRUE);
        $active = $this->input->get('active',TRUE);
        if($active === FALSE || !$apk_id) {
            $this->session->set_flashdata('flash_message', '参数不正确');
            redirect('game_management/experiment/setup_channel/'.$revision_id);
        }else{
            if($active == 1) {
                $result = $this->cp_game_revision_apk_model->activate_apk($apk_id);
                if(!$result) {
                    $this->session->set_flashdata('flash_message', '未更新上线状态，或者游戏渠道关联失败');
                }
            }else{
                $result = $this->cp_game_revision_apk_model->deactivate_apk($apk_id);
            }
            //$this->cp_game_revision_apk_model->update($apk_id,array('active'=>$active));
            redirect('game_management/experiment/setup_channel/'.$revision_id);
        }
    }
    
    /**
     * copy_chnres
     * 复制渠道资源
     */
    public function copy_chnres(){
        $downsource = $this->input->get('from',TRUE);
        $from_apk = $this->concat_path('uploads', str_replace($this->completeurl('/'),'', $downsource));
        $chnres = $this->input->get('chnres',TRUE);
        $rev_id = $this->input->get('revid',TRUE);
        $to_channel_id = $this->input->get('to',TRUE);
        $delete = $this->input->get('delete',TRUE);
        if(ends_with($delete,'.apk'))
        {
            $delete = str_replace(site_url(),'', $delete);
            $genuine = $this->get_genuine_apk($delete);
            !is_file($delete) or unlink($delete);
            !is_file($genuine) or unlink($genuine);
            $dst_dir = dirname($delete);
        }
        else
        {
            $dst_dir = $this->concat_path('uploads',$delete);
        }
        $newfilename = $this->concat_path($dst_dir, basename($from_apk));
        $apk_table = array();
        if(is_file($from_apk))
        {
            copy($from_apk, $newfilename);
            $apk_table['apk_md5'] = md5_file($newfilename);
            $apk_table['apk_download_url'] = $this->get_path_database($newfilename);
            $apk_table['apk_name'] = pathinfo($newfilename,PATHINFO_FILENAME);
            $apk_table['apk_file_dir'] = pathinfo($apk_table['apk_download_url'],PATHINFO_DIRNAME);
        }
        else
        {
            $this->session->set_flashdata('flash_message','文件不存在，复制失败');
            redirect('game_management/experiment/setup_channel/'.$rev_id);
        }
        $from_gen_apk = $this->get_genuine_apk($from_apk);
        $genuine_apk = $this->get_genuine_apk($newfilename);
        if(is_file($from_gen_apk))
        {
            copy($from_gen_apk, $genuine_apk);
        }
        else
        {
            extract_genuine_apk($newfilename);
        }
        $apk_table['genuine_apk_download_url'] = $this->get_path_database($genuine_apk);
        $apk_table['genuine_apk_md5'] = md5_file($genuine_apk);

        $from_so_apk = $this->get_so_apk($from_apk);
        $so_apk = $this->get_so_apk($newfilename);
        if(is_file($from_so_apk))
        {
            copy($from_so_apk,$so_apk);
            $apk_table['so_apk_download_url'] = $this->get_path_database($so_apk);
            $apk_table['so_apk_md5'] = md5_file($so_apk);
        }
        
        //更新数据库
        $in_filename = $this->get_path_database($newfilename);
        $pathinfo = pathinfo($in_filename);
        $this->cp_game_revision_channel_resources_model->update($chnres, array('apk_download_url' => $in_filename));
        $this->db->where(array('revision_channel_resources_id'=>$chnres))->update('cp_game_revision_apk',$apk_table);
        $this->session->set_flashdata('flash_message','复制成功');
        $glob_file = glob(dirname($from_apk).'/*');
        foreach ($glob_file as $key => $value) {
            if($this->uploadzip_library->is_arch_file($value))
            {
                copy($value, $this->get_full_path($pathinfo['dirname']).'/'.basename($value));
            }
        }
        $this->uploadzip_library->glob_arch($rev_id,$to_channel_id,dirname($in_filename));
        redirect('game_management/experiment/setup_channel/'.$rev_id);
    }
    
    /**
     * get_genuine_apk
     *
     * 根据 apk 名拼接真实 apk 名
     */
    private function get_genuine_apk($apk){
        return $this->concat_path(dirname($apk), '_'.basename($apk));
    }

    private function get_so_apk($apk){
        return $this->concat_path(dirname($apk), '_so_'.basename($apk));
    }

    /**
     * 上传 runtime resource
     */
    public function upload_runtime_resource() 
    {
        $ziptype_mapping = array(
            1 => '通用资源和渠道资源',
            2 => '渠道资源',
            3 => '通用资源',
            4 => '差分资源',
            5 => '通用资源,真实apk',
            6 => '差分资源，真实apk',
            7 => '小版本资源',
            99 => '未识别的类型',
        );
        $this->load->library('upload');
        $this->load->library('cocos_packingtool/uploadzip_library');
        $user_id = $this->session->userdata('user_id');
        $files_exist = glob($this->concat_path(FCPATH, "uploads/syncdir/$user_id/*.*"));
        $files_msg = array();
        foreach ($files_exist as $key => $value) {
                $ziptype = 99;
                $gamename = '未知';
                $package_ver_code = '未知';
                $files_status_code = 0;
                $remarks = '';
            list($checkcode,$resultmsg) = $this->uploadzip_library->check_zip_info($value);
            $allow_to_create = TRUE;
            if(!is_array($resultmsg) && !$checkcode)
            {
                //未识别的类型
                $ziptype = 99;
                $files_status_code = 0;
                $gamename = '未知';
                $package_ver_code = '未知';
                $remarks = '请检查信息文件info.json';
                $allow_to_create = FALSE;
            }
            else
            {
                //识别类型
                if(!$checkcode)
                {
                    //文件不完整
                    $files_status_code = 0;
                    $remarks = $resultmsg;
                }
                else
                {
                    //文件完整
                    $files_status_code = 1;
                    $remarks = '';
                }
                $package_ver_code = $resultmsg['gameinfo']['package_ver_code'];
                $gamekey = $resultmsg['gameinfo']['game_key'];
                $ziptype = $resultmsg['ziptype'];
                $game = $this->cp_game_info_model->select('*')->where(array('game_key'=>$gamekey))->get();
                if(!isset($game['game_name']))
                {
                    $files_status_code = 0;
                    $gamename = '不存在';
                    $package_ver_code = '不存在';
                    $remarks .= '所属游戏不存在';
                }
                else
                {
                    $gamename = $game['game_name'];
                }
                
                switch ($ziptype) {
                    case 1:
                        break;
                    case 3:
                        break;
                    case 2:
                        $is_ver_exist = $this->cp_game_revision_info_model->get_hotlist_by_ver($gamekey,$package_ver_code);
                        if(empty($is_ver_exist))
                        {
                            $package_ver_code = '不存在';
                            $allow_to_create = FALSE;
                            $remarks .= '所属版本不存在';
                            $files_status_code = 0;
                        }
                        break;
                    case 4:
                        $is_ver_exist = $this->uploadzip_library->validation_vercode($gamekey,$package_ver_code,FALSE);
                        if(!$is_ver_exist)
                        {
                            $package_ver_code = '不存在';
                            $allow_to_create = FALSE;
                            $files_status_code = 0;
                        }
                        $support_ver_code = $resultmsg['gameinfo']['support_ver_code'];
                        $is_support_ver_exist = $this->uploadzip_library->validation_vercode($gamekey,$support_ver_code,FALSE);
                        if(!$is_support_ver_exist)
                        {
                            $allow_to_create = FALSE;
                            $remarks .= "支持差分升级的版本不存在";
                            $files_status_code = 0;
                        }
                        break;
                    case 7:
                        $is_ver_exist = $this->uploadzip_library->validation_vercode($gamekey,$resultmsg['gameinfo']['from_hot_versioncode'],FALSE);
                        if(!$is_ver_exist)
                        {
                            $package_ver_code = '不存在';
                            $allow_to_create = FALSE;
                            $files_status_code = 0;
                        }
                    default:
                        break;
                }
            }
            
            if(is_array($remarks))
            {
                //文件不完整
                $fails = array(
                'required' => '缺失',
                'numeric' => '必需是数字',
                'gamemode' => '不合法',
                'version' => '不合法',
                'orientation' => '不合法',
                );
                $gameinfo = $remarks['gameinfo'];
                $fileinfo = $remarks['fileinfo'];
                $remarks = '';
                foreach ($gameinfo as $key2 => $value2) {
                    if(key_exists($value2,$fails))
                    {
                        $remarks .= $key2.$fails[$value2];
                        $remarks .= "<br>";
                    }
                }
                foreach ($fileinfo as $key2 => $value2) {
                    if(key_exists($value2,$fails))
                    {
                        $remarks .= $key2.$fails[$value2];
                        $remarks .= "<br>";
                    }
                }
            }
            $files_msg[basename($value)] = array(
                'ziptype' => $ziptype_mapping[$ziptype],
                'game_name' => $gamename,
                'package_ver_code' => $package_ver_code,
                'files_status_code' => $files_status_code,
                'remarks' => $remarks,
            );
        }
        return $this->_render_page('upload_resource',array('files_msg'=>$files_msg, 'userdir'=>''));
    }
    
    public function ajax_update_runtime_gameurl()
    {
        $revision_id = $this->input->get_post('revision_id',TRUE);
        $channel_id = $this->input->get_post('channel_id',TRUE);
        $gameurl = $this->input->get_post('gameurl',TRUE);
        $revision_apk_id = $this->input->get_post('revisionapkid',TRUE);

        if($revision_id && $channel_id && $revision_apk_id) {
            $result = $this->cp_game_revision_apk_model->update_apk($revision_apk_id,$gameurl);
            if($result === 0 || $result) {
                $manifest_url = $this->concat_path($gameurl, 'manifest.cpk');
                $manifest_url = str_replace($this->get_full_path(''), '', $manifest_url);
                $manifest_url = '/' . ltrim($manifest_url, '/');
                if(starts_with($manifest_url, 'http')) {
                    // TODO : #21383 远程取 manifest_url 并计算 manifest_md5
                    // BUG : 空白页面也会返回 content 要判断 http code
                    /*
                     * 有网络延迟，应想其他途径
                    $content = file_get_contents($manifest_url);
                    $status_code = $http_response_header[0];
                    if (!preg_match('/200/' , $status_code) || $content === FALSE ) {
                        // cannot download manifest.cpk
                        echo 'manifestnotok';
                        return ;
                    }else{
                        $md5 = md5($content);
                        // update game_revision_info;
                        $ok = $this->cp_game_revision_info_model->update($revision_id,array('manifest_json_url' => $manifest_url, 'manifest_json_md5' => $md5));
                        if($ok) {
                            echo 'done';
                            return ;
                        }else{
                            echo 'manifestnotok';
                            return ;
                        }
                    }
                    */
                    
                    $ok = $this->cp_game_revision_info_model->update($revision_id,array('manifest_json_url' => $manifest_url, 'manifest_json_md5' => ''));
                    if($ok) {
                        echo 'done';
                        return ;
                    }else{
                        echo 'manifestnotok';
                        return ;
                    }
                }else{
                    $full_manifest_path = $this->get_full_path($manifest_url);
                    $this->load->helper('game_install');
                    unzip_file_path($full_manifest_path);
                    $json_file = $this->concat_path(dirname($full_manifest_path), 'manifest.json');
                    if(file_exists($json_file)) {
                        $md5 = md5_file($json_file);
                        if($md5) {
                            // update game_revision_info;
                            $ok = $this->cp_game_revision_info_model->update($revision_id,array('manifest_json_url' => $manifest_url, 'manifest_json_md5' => $md5));
                            if($ok) {
                                echo 'done';
                                return ;
                            }else{
                                echo 'manifestnotok';
                                return ;
                            }
                        }else{
                            echo 'md5notok';
                            return ;
                        }
                    }else{
                        echo 'manifestnotok';
                        return ;
                    }
                }
            };
        }
        echo 'notok';
    }

    /**
     * 重载同步CDN方法，如果填写了$revision_id则检查游戏是否为发布状态
     * @param  [type] $path        [description]
     * @param  [type] $revision_id [description]
     * @return [type]              [description]
     */
    protected function sync_with_cdn_dir($path,$revision_id = NULL){
        if(is_null($revision_id)){
            return parent::sync_with_cdn_dir($path);
        }else{
            $this->load->model('game_management/cp_game_revision_info_model');
            $revision = $this->cp_game_revision_info_model->get_game_revision_detail($revision_id);
            if($revision['is_published'] == 1){
                return parent::sync_with_cdn_dir($path);
            }else{
                return FALSE;
            }
        }
    }

    /**
     * 重载同步CDN方法，如果填写了$revision_id则检查游戏是否为发布状态
     * @param  [type] $path        [description]
     * @param  [type] $revision_id [description]
     * @return [type]              [description]
     */
    protected function sync_with_cdn_file($path,$revision_id = NULL){
        if(is_null($revision_id)){
            return parent::sync_with_cdn_file($path);
        }else{
            $this->load->model('game_management/cp_game_revision_info_model');
            $revision = $this->cp_game_revision_info_model->get_game_revision_detail($revision_id);
            if($revision['is_published'] == 1){
                return parent::sync_with_cdn_file($path);
            }else{
                return FALSE;
            }
        }
    }

    public function set_thirdsdk(){
        $type = $this->input->get('type',TRUE);
        $value = $this->input->get('value',TRUE);
        $download_mapping = array(
            'plugin' => 'download_third_plugin',
            'sdk' => 'download_third_sdk'
        );
        $type_mapping = array(
            'plugin' => 'third_plugin_int_version',
            'sdk' => 'third_sdk_int_version'
        );
        $field = $type_mapping[$type];
        $is_download = $download_mapping[$type];
        if(!$field){
            $this->session->set_flashdata('flash_message','更新失败');
            redirect('game_management/experiment/setup_channel/'.$revision_id);
        }
        $revision_id = $this->input->get('revision_id',TRUE);
        $chnres_id = $this->input->get('chnres_id',TRUE);
        if(!$field || !$revision_id || !$chnres_id || !$value){
            $this->session->set_flashdata('flash_message','更新失败');
            redirect('game_management/experiment/setup_channel/'.$revision_id);
            return ;
        }
        if($value == 'null' || $value == 'NULL'){
            $value = NULL;
        }
        if(is_null($value)){
            $is_download_value = 0;
        }else{
            $is_download_value = 1;
        }
        $data = array(
            $field => $value,
            $is_download => $is_download_value,
        );
        $this->load->model('game_management/cp_game_revision_channel_resources_model');
        $result = $this->cp_game_revision_channel_resources_model->update($chnres_id,$data);
        if($result){
            $this->session->set_flashdata('flash_message','更新成功');
            redirect('game_management/experiment/setup_channel/'.$revision_id);
        }else{
            $this->session->set_flashdata('flash_message','更新失败');
            redirect('game_management/experiment/setup_channel/'.$revision_id);
        }
    }
    /**
     * 渠道配置文件管理页面
     */
    public function edit_channel_config($apkid) {
         if(!$apkid) {
             return redirect('/main');
         }
         
        if($_FILES['chnconfigfile']) { 
            $channel_config_version = $this->input->post('channel_config_version');
            if(!$channel_config_version) {
                $this->session->set_flashdata('flash_message', '请输入版本');
            }else{
                $this->load->model('game_management/cp_game_revision_apk_model');
                $info = $this->cp_game_revision_apk_model->get_apk_info_extended($apkid);
                if(!$info) {
                    $this->session->set_flashdata('flash_message', '找不到渠道资源信息');
                    return redirect('game_management/experiment/edit_channel_config/' . $apkid);
                }
                $revision_id = $info['revision_id'];
                $channel_id = $info['channel_id'];
                $file_dir = "channelconfig";
                $key = 'chnconfigfile';
                $this->load->library('upload');
                $full_upload_path = $this->get_full_path($file_dir);
                $upload_conf = array();
                $upload_conf['upload_path'] = $full_upload_path;
                $orig_ext = array_pop(explode('.', basename($_FILES[$key]['name'])));
                $upload_conf['file_name'] = $revision_id . '_' . $channel_id . '_' . $channel_config_version . '.' . basename($_FILES[$key]['name']);
                $upload_conf['allowed_types'] = '*';
                $upload_conf['overwrite'] = TRUE;
                $this->upload->initialize($upload_conf);
                $result = $this->upload->do_upload($key);
                if (!$result) {
                    $error_msg = $this->upload->display_errors();
                    $this->session->set_flashdata('flash_message', $error_msg);
                } else {
                    $uploaddata = $this->upload->data();
                    $root = $this->get_full_path("");
                    $url = str_replace( $root, '', $uploaddata['full_path']);
                    $this->load->model('game_management/cp_game_revision_channel_config_model');
                    $result = $this->cp_game_revision_channel_config_model->add($apkid, $url, $channel_config_version);
                    if(!$result && $result!==0) {
                        $this->session->set_flashdata('flash_message', '更新数据库失败');
                    }else{
                        $this->sync_with_cdn_file($url,$revision_id);
                        $this->session->set_flashdata('flash_message', 'ok');
                        $max = $this->cp_game_revision_channel_config_model->max_ver($apkid);
                        if($max && $max['ver'] == $channel_config_version) {
                            $ok = $this->cp_game_revision_apk_model->update($apkid, array('revision_channel_config_id' => $max['id']));
                            if($ok===FALSE) {
                                $this->session->set_flashdata('flash_message', 'apk 表 config_id 更新失败');
                            }else{
                                // TODO : 同步文件到 cdn
                            }
                        }
                    }
                }
                return redirect('game_management/experiment/edit_channel_config/' . $apkid);
            }
        }

        $this->load->model('game_management/cp_game_revision_apk_model');
        $info = $this->cp_game_revision_apk_model->get_apk_info_extended($apkid);
        if(!$info) {
            //return redirect();// TODO : 
        }

        $this->load->model('game_management/cp_game_revision_channel_config_model');
        $chn_configs = $this->cp_game_revision_channel_config_model->select(array('id', 'url', 'ver', 'modify_time'))->where(array('apk_id'=>$apkid))->get_all();
        foreach($chn_configs as $indx => $config) {
            if($config['url']) {
                $chn_configs[$indx]['url'] = $this->completeurl($config['url']);
            }
        }

        $info['configs'] = $chn_configs; 
        $content = $this->load->view('edit_channel_config', $info, True);
        return $this->smarty->view('general.tpl', array('content'=>$content));
    }

    /**
     *
     */
    public function toggle_config_type($apkid,$config_type) {
        $this->load->model('game_management/cp_game_revision_apk_model');
        $info = $this->cp_game_revision_apk_model->get_apk_info_extended($apkid);
        if(!$info) {
            //return redirect();// TODO : 
        }
        $revision_id = $info['revision_id'];
        $result = $this->cp_game_revision_apk_model->toggle_config_type($apkid,$config_type);
        if($result) {
            $this->session->set_flashdata('flash_message','配置类型设置成功');
        }else{
            $this->session->set_flashdata('flash_message','配置类型设置不成功');
        }
        redirect("game_management/experiment/setup_channel/" . $revision_id);
    }

    /** 
     * 删除 cp_game_revision_channel_config 记录
     */
    public function delete_channel_config($config_id) {
        if (!$config_id) {
            return redirect('game_management/gamelist');
        }
        $this->load->model('game_management/cp_game_revision_channel_config_model');
        $chn_configs = $this->cp_game_revision_channel_config_model->select(array('id', 'url', 'ver', 'apk_id'))->where(array('id'=>$config_id))->get();
        if($chn_configs) {
            $apk_id = $chn_configs['apk_id'];
            $ok = $this->cp_game_revision_channel_config_model->delete($config_id);
            
            $max = $this->cp_game_revision_channel_config_model->max_ver($apk_id);
            $this->session->set_flashdata('flash_message', $ok);
            if($max) {
                $ok = $this->cp_game_revision_apk_model->update($apk_id, array('revision_channel_config_id' => $max['id']));
                if(!$ok) {
                    $this->session->set_flashdata('flash_message', 'apk 表 config_id 更新失败');
                }
            }else{
                $ok = $this->cp_game_revision_apk_model->update($apk_id, array('revision_channel_config_id' => NULL));
            }
            redirect('game_management/experiment/edit_channel_config/' . $apk_id);
        }else{
            $this->session->set_flashdata('flash_message', '找不到指定config资源');
            return redirect('game_management/gamelist');
        }
    }

    public function ajax_batch_online(){
        $chns = $this->input->post('chns');
        $rev_id = $this->input->post('rev_id');
        if(!$rev_id || empty($chns)){
            echo 'failed';
        }else{
            $chns_str = implode(',', $chns);
            $result = $this->db->where("revision_id = $rev_id and active = 0 and channel_id in ($chns_str)")->update('cp_game_revision_apk',array('active'=>1));
            if($result){
                echo 'done';
            }else{
              echo 'failed';  
            }
        }
    }

    public function ajax_delete_chn_res(){
        $type = $this->input->post('type');
        $resid = $this->input->post('resid');
        $apk_id = $this->input->post('apkid');
        if(!$resid || !$type ||!$apk_id){
            die('failed');
        }

        switch ($type) {
            case 'icon':
            case 'background':
            case 'music':
                $mapping = array(
                    'icon' => 'icon_url',
                    'background' => 'bg_picture',
                    'music' => 'bg_music',
                );
                $data_type = $mapping[$type];
                $file = $this->db->select($data_type)->where(array('id'=>$resid))->get('cp_game_revision_channel_resources')->result_array();
                $file_path = $file[0][$data_type];
                unlink($this->get_full_path($file_path));
                $result = $this->db->where(array('id'=>$resid))->update('cp_game_revision_channel_resources',array($data_type=>''));
                die('done');
            break;
            case 'apk':
                $data['apk_file_dir'] = '';
                $data['apk_download_url'] = '';
                $data['apk_md5'] = '';
                $result = $this->db->select('apk_download_url')->where(array('id'=>$apk_id))->get('cp_game_revision_apk')->result_array();
                unlink($this->get_full_path($result[0]['apk_download_url']));
                $this->db->where(array('id'=>$resid))->update('cp_game_revision_channel_resources',array('apk_download_url'=>''));
                $this->db->where(array('id'=>$apk_id))->update('cp_game_revision_apk',$data);
                die('done');
            break;
            case 'genuine_apk':
                $data['genuine_apk_download_url'] = '';
                $data['genuine_apk_md5'] = '';
                $result = $this->db->select('genuine_apk_download_url')->where(array('id'=>$apk_id))->get('cp_game_revision_apk')->result_array();
                unlink($this->get_full_path($result[0]['genuine_apk_download_url']));
                $this->db->where(array('id'=>$apk_id))->update('cp_game_revision_apk',$data);
                die('done');
            break;
            case 'so_apk':
                $data['so_apk_download_url'] = '';
                $data['so_apk_md5'] = '';
                $result = $this->db->select('so_apk_download_url')->where(array('id'=>$apk_id))->get('cp_game_revision_apk')->result_array();
                unlink($this->get_full_path($result[0]['so_apk_download_url']));
                $this->db->where(array('id'=>$apk_id))->update('cp_game_revision_apk',$data);
                die('done');
            break;
            case 'arch':
                $gamesoid = $this->input->post('gamesoid',TRUE);
                $result = $this->db->select('download_url')->where('id',$gamesoid)->get('cp_game_revision_apk_so')->result_array();
                if(empty($result)){
                    die('failed');
                }
                unlink($this->get_full_path($result[0]['download_url']));
                $this->db->where('id',$gamesoid)->delete('cp_game_revision_apk_so');
            die('done');
            break;
            default:
            echo 'done';
                break;
            echo 'failed';
        }
    }

    /**
     * 删除假apk,真apk,去除架构文件的APK以及架构文件
     * 因为用户上传的只是假APK一个文件，在替换APK的时候，通过这个方法能够干净地删除相关文件
     * @return [type] [description]
     */
    public function ajax_del_apk_related_file(){
        $apk_id = $this->input->post('apk_id',TRUE);
        $apk_download_url = $this->input->post('apk_download_url',TRUE);
        if($apk_id){
            $this->load->model('cp_game_revision_apk_model');
            $this->load->model('cp_game_revision_apk_so_model');
            $apk = $this->db->where(array('id'=>$apk_id))->get('cp_game_revision_apk')->result_array();
            $apk = $apk[0];
            $apk_download_url = $apk['apk_download_url'];
            $channel_id = $apk['channel_id'];
            $revision_id = $apk['revision_id'];
            $so_files = $this->cp_game_revision_apk_so_model->get_so($revision_id,$channel_id);
            $del_apk = $this->cp_game_revision_apk_model->update_apk($apk_id,'');
            $apks = array(
                'so_apk_download_url' => '',
                'genuine_apk_download_url' => '',
                'apk_md5' => '',
                'so_apk_md5' => '',
                'genuine_apk_md5' => '',
            );
            $del_apks = $this->db->where(array('id'=>$apk_id))->update('cp_game_revision_apk',$apks);
            $del_so = $this->cp_game_revision_apk_so_model->remove($revision_id,$channel_id);
            if(!empty($apk_download_url) && $del_so && $del_apk && $del_apks){
                unlink($this->get_full_path($apk_download_url));
                unlink($this->get_full_path($this->get_genuine_apk($apk_download_url)));
                unlink($this->get_full_path($this->get_so_apk($apk_download_url)));
                foreach ($so_files as $value) {
                    unlink($this->get_full_path($value['download_url']));
                }
                exit('done');
            }
        }
        exit('failed');
    }
    public function modify_chn_gamedesc(){
        $res_id = $this->input->post('resid',TRUE);
        $text = $this->input->post('text',TRUE);
        $result = $this->db->where(array('id'=>$res_id))->update('cp_game_revision_channel_resources',array('game_desc'=>$text));
        if($result){
            exit('done');
        }else{
            exit('failed');
        }
    }
    /**
     * getchanneconfig
     */
    public function ajax_get_channel_config() {
        $apkid = $this->input->get_post('apkid', TRUE);
        header('Content-Type: application/json');
        $data = array(
            'msg' => 'notok',
            'data' => array(),
        );
        if ($apkid) {
            $cnt = $this->cp_game_revision_apk_model->select('channel_config_text, channel_config_encoded')->where(array('id'=>$apkid))->get();
            if($cnt) {
                $data['msg'] = 'ok';
                $cnt['channel_config_text'] = $cnt['channel_config_text']; 
                $cnt['channel_config_encoded'] = $cnt['channel_config_encoded']; 
                $data['data'] = $cnt;
            }
            echo json_encode($data);
        }else{
            echo json_encode($data);
        }
    }
    /**
     * updatechannelconfig
     */
    public function ajax_update_channel_config() {
        $apkid = $this->input->get_post('apkid', TRUE);
        $dtype = $this->input->get_post('dtype', TRUE);
        $cfgtext = $this->input->get_post('cfgtext', TRUE);
        $cfgtextencoded = $this->input->get_post('cfgtextencoded', TRUE);

        if($cfgtext === FALSE) {
            $cfgtext = '';
        }

        if($cfgtextencoded === FALSE) {
            $cfgtextencoded = '';
        }

        header('Content-Type: application/json');
        $data = array(
            'msg' => 'notok',
            'data' => array(),
        );
        if($cfgtext === FALSE || $cfgtextencoded === FALSE || !$apkid) {
            echo json_encode($data);
        }
        else {
            $ok = $this->cp_game_revision_apk_model->update($apkid, array('channel_config_type'=>$dtype, 'channel_config_text' => $cfgtext, 'channel_config_encoded' => $cfgtextencoded));
            if($ok || $ok === 0) {
                $data['msg'] = 'ok';
            }
            echo json_encode($data);
        }
    }

    /**
     * 返回 rtcorepatch 配置信息 
     *
     * 在 runtime 游戏的版本编辑中使用
     */
    public function ajax_rtcorepatch() {
        $revision_id = $this->input->get_post('revision_id', TRUE);
        header('Content-Type: application/json');
        $data = array(
            'msg' => 'notok',
            'data' => array(),
        );
        if($revision_id) {
            $this->load->model('game_management/cp_runtime_core_patch_model');
            $records = $this->cp_runtime_core_patch_model->get_extended($revision_id);
            $data ['msg'] = 'ok';
            $data ['data'] = $records;
            foreach($records as $index => $record) {
                foreach($record as $key => $value) {
                    if(ends_with($key, '_url') && $value) {
                        $records[$index][$key] = $this->completeurl($value);
                    }
                }
            }
            $data ['data'] = $records;
            //$data ['rtcores'] = $records;
            echo json_encode($data);
        }else{
            echo json_encode($data);
        }
    }

    /**
     * ajax 方式上传 cpk 文件
     */
    public function ajax_upload_rtcorepatch() {
        $revision_id = $this->input->get_post('revision_id', TRUE);
        $runtime_core_id = $this->input->get_post('runtime_core_id', TRUE);
        $file_b64 = $this->input->get_post('file_b64', TRUE);
        $filename = $this->input->get_post('filename', TRUE);
        header('Content-Type: application/json');
        $data = array(
            'msg' => 'notok',
            'data' => array(),
        );
        if($file_b64 === FALSE) {
            echo json_encode($data);
        }else{
            $file_content = base64_decode($file_b64);
            unset($file_b64);
            if($file_content && $revision_id && $runtime_core_id && $filename)
            {
                $mapping = array(
                    'armeabi-v7a' => 'armeabi_v7a_patch_url',
                    'armeabi' => 'armeabi_patch_url',
                    'arm64-v8a' => 'arm64_v8a_patch_url',
                    'x86' => 'x86_patch_url'
                );
                $mapping_md5 = array(
                    'armeabi-v7a' => 'armeabi_v7a_patch_md5',
                    'armeabi' => 'armeabi_patch_md5',
                    'arm64-v8a' => 'arm64_v8a_patch_md5',
                    'x86' => 'x86_patch_md5'
                );
                $keys = array_keys($mapping);
                $arch = '';
                foreach ($keys as $value) {
                    $a = strchr($filename,$value);
                    if($a)
                    {
                        $arch = $value;
                        break;
                    }
                }
                if(!$arch)
                {
                    //echo '文件类型无法识别';
                    echo json_encode($data);
                }
                else
                {
                    $path_segs = '/patch/' . $runtime_core_id;
                    $newpath = $this->mkrevdir($revision_id, $path_segs);
                    if($newpath === FALSE)  {
                        echo json_encode($data);
                    }else{
                        $filepath = $this->concat_path($newpath, $filename);
                        $ok = file_put_contents($this->get_full_path($filepath), $file_content);
                        $md5 = md5($file_content);
                        $updatedata = array(
                            $mapping[$arch] => $filepath, 
                            $mapping_md5[$arch] => $md5,
                        );
                        unset($file_content);
                        $this->load->model('game_management/cp_runtime_core_patch_model');
                        $result = $this->cp_runtime_core_patch_model->add_or_update($revision_id, $runtime_core_id, $updatedata);
                        $data['msg'] = 'ok';
                        $data['data'] = $updatedata;
                        echo json_encode($data);
                    }
                }                
            }else{
                echo json_encode($data);
            }
        }
    }

    /**
     * 在游戏版本的目录中建立一个 path_segs 指定的目录
     */
    public function mkrevdir($revision_id, $path_segs) {
        $result = $this->cp_game_revision_info_model->select('file_dir, manifest_json_url')->where(array('id'=> $revision_id))->get();
        if($result && count($result['file_dir']>16)) {
            $newpath = $this->concat_path($result['file_dir'], $path_segs);
            $newdir = $this->get_full_path($newpath);
            if(!file_exists($newdir)) {
                mkdir($newdir, 0770, TRUE);
            }
            if(!file_exists($newdir)) {
                return FALSE;
            }else{
                return $newpath;
            }
        }else{
            return FALSE;
        }
    }
}
// EOF
