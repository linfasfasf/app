<?php
/*
 * 为 simple_test 提供工具支持
 */

/* 供测试用， 加入了 测试的数据 
 */
function get_test_data_template(){
    $game_name = '测试游戏 abc !!';
    $template = array(
        'game_name'          => $game_name,
        'channel_id'          => '111111',
        'api_flag'          => '9999', // 过滤掉新的数据
        'game_mode'          => '1',
        'package_name'       => 'test.package.name.joe',
        'cp_vendor'          => 'UC',
        'package_ver_code'   => '999',
        'hot_versioncode'   => '999',
        'ver_last'   => '997',
        'package_ver'        => '999.0',
        'sdk_version'        => '2',
        'star'               => '2',
        'user_system'        => 'android',
        'orientation'        => '1',
        'mdf_url'            => '/neat/asdf12/mdf.xml',
        'game_desc'          => $game_name . '_rev_desc',
        'icon_url'           => '/neat/asdf12/mdf.icon.png',
        'file_dir'           => '/neat/asdf12',
        'is_maintain'        => '0',
        'bg_music'           => '/neat/asdf12/bg.mp3',
        'apk_name'           => 'mygame.apk',
        'manifest_url'       => '/neat/asdf12/scenemanifest.xml',
        'resource_url'       => '/neat/asdf12/scene_boo1.cpk',
        'chafen_url'       => '/neat/asdf12/chafen.cpk',
        'channel_is_visible'       => '1',
        'resource_pack_name' => 'scene_b001.cpk',
        'manifest_version' => '2',
        'full_game_download_url' => 'http://fake.com/game_download_url.apk',
    );
    return $template;
}
function setup_test_data( $data ){
        $ci = &get_instance();
        $ci->load->model('game_management/cp_game_info_model');
        $ci->load->model('game_management/cp_game_revision_info_model');
        $ci->load->model('game_management/cp_chn_game_info_model');
        $game_id = FALSE;
        $revision_id = FALSE;
    
        $game_info = $ci->cp_game_info_model->select(array('game_id'))->order_by('game_id desc')->limit(1)->get();
        $test_game_data = array(
            'package_ver_code' => $data['package_ver_code'],
            'game_key' => $game_info['game_id'].'_' . time(),
            'game_mode' => $data['game_mode'],
            'del_flag' => 0,
            'game_name' => $data['game_name'],
            'package_name' => $data['package_name'],
            'apk_name' => $data['package_name'].'.apk',
            'cp_vendor' =>  $data['cp_vendor'],
            'channel_id' =>  $data['api_flag'],
            'modify_time' => time(),
            'create_time' => time(),
        );
        if(array_key_exists('game_key', $data)) {
            $test_game_data['game_key'] = $data['game_key'];
        }
        $game_id = $ci->cp_game_info_model->insert($test_game_data);
        $test_revision_data =
            array(
            'game_id'          => $game_id,
            'package_ver_code' => $data['package_ver_code'],
            'hot_versioncode' => $data['hot_versioncode'],
            'ver_last' => $data['ver_last'],
            'chafen_url' => $data['chafen_url'],
            'package_name'      => $data['package_name'],
            'package_ver'      => $data['package_ver'],
            'sdk_version'      => $data['sdk_version'],
            'star'             => $data['star'],
            'user_system'      => $data['user_system'],
            'orientation'      => $data['orientation'],
            'mdf_url'          => $data['mdf_url'],
            'game_name'        => $data['game_name']. '_rev',
            'game_desc'        => $data['game_name'] . '_rev_desc',
            'icon_url'         => $data['icon_url'],
            'file_dir'         => $data['file_dir'],
            'is_maintain'      => $data['is_maintain'],
            'is_published'         => '1', // 已发布
            'manifest_url'     => $data['manifest_url'],
            'manifest_version'     => $data['manifest_version'],
            'bg_music'         => $data['bg_music'],
            'apk_name'         => $data['apk_name'],
        );
        $revision_id = $ci->cp_game_revision_info_model->insert($test_revision_data);
        $resource_url =  $data['resource_url'];
        $resource_pack_name =$data['resource_pack_name'];
        $db = $ci->load->database('', TRUE);
        $sql = 'insert into cp_game_revision_resources (revision_id, resource_url, resource_pack_name, package_ver_code, resource_pack_id, resource_pack_map) values (?, ?, ?, ?, ?, ?)';
        $result = $db->query($sql, array($revision_id, $resource_url, $resource_pack_name, '999', '11234234', 'xxasdf'));
        $test_channel_game_data = array(
            'game_id' =>  $game_id,
            'channel_id' => $data['channel_id'],
            'full_game_download_url' => $data['full_game_download_url'], 
            'is_visiable' => $data['channel_is_visible'],
            'create_time' => time(),
            'modify_time' => time(),
        );
        $chn_game_id = $ci->cp_chn_game_info_model->insert($test_channel_game_data);
        return array('game_id' => $game_id, 'revision_id'=> $revision_id, 'resource_result'=> $result, 'chn_game_id'=>$chn_game_id);
}
