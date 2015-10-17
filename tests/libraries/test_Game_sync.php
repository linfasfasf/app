<?php
class test_game_sync extends CodeIgniterWebTestCase
{
	public function __construct()
	{
		parent::__construct('');
        $this->load->library('game_management/game_sync');

        $this->local_testdata = 'a:58:{s:2:"id";s:3:"549";s:10:"channel_id";s:6:"100112";s:10:"game_order";s:1:"0";s:11:"is_visiable";s:1:"1";s:19:"ch_apk_download_url";s:0:"";s:13:"ch_bg_picture";s:0:"";s:22:"full_game_download_url";s:0:"";s:7:"game_id";s:4:"1111";s:8:"game_key";s:10:"MW3ZJBHGWH";s:9:"game_name";s:24:"熊出没之保卫家园";s:12:"package_name";s:3:"abc";s:9:"game_mode";s:1:"1";s:9:"game_type";s:1:"8";s:9:"cp_vendor";s:9:"熊出没";s:11:"package_ver";s:5:"2.5.5";s:16:"package_ver_code";s:3:"255";s:20:"rev_apk_download_url";s:28:"/remote/apk_file_dir/abc.apk";s:8:"apk_name";s:7:"abc.apk";s:5:"appid";s:0:"";s:8:"bg_music";s:28:"/neatgame/KFYGHU/bgmusic.ogg";s:10:"bg_picture";s:26:"/neatgame/KFYGHU/bgpic.jpg";s:10:"chafen_url";s:0:"";s:11:"coop_method";s:0:"";s:11:"create_time";s:10:"1429069516";s:12:"is_published";s:1:"1";s:14:"engine_version";s:2:"v2";s:8:"file_dir";s:16:"/neatgame/KFYGHU";s:9:"game_desc";s:51:"动画巨作改编，最新国民级塔防游戏。";s:8:"icon_url";s:25:"/neatgame/KFYGHU/icon.png";s:11:"is_maintain";s:1:"0";s:12:"maintain_tip";s:0:"";s:12:"manifest_url";s:33:"/local/file_dir/SceneManifest.xml";s:7:"mdf_url";s:0:"";s:6:"opt_id";s:1:"0";s:11:"orientation";s:1:"0";s:15:"package_up_time";s:1:"0";s:7:"payment";s:9:"运营商";s:11:"sdk_version";s:1:"2";s:4:"star";s:1:"5";s:11:"user_system";s:9:"运营商";s:11:"modify_time";s:10:"1429582681";s:13:"test_duration";s:1:"0";s:10:"is_visible";s:1:"1";s:8:"ver_last";s:1:"0";s:13:"rev_game_name";s:24:"熊出没之保卫家园";s:13:"old_data_flag";N;s:17:"chn_test_duration";s:2:"-1";s:16:"apk_download_url";s:27:"/local/apk_file_dir/abc.apk";s:5:"apkid";s:3:"423";s:11:"revision_id";s:4:"1234";s:12:"cpk_file_dir";s:16:"/neatgame/KFYGHU";s:16:"manifest_version";s:9:"181443811";s:12:"res_icon_url";s:23:"/local/chn/res_icon.png";s:12:"res_bg_music";s:0:"";s:14:"res_bg_picture";s:17:"/local/chn/bg.png";s:20:"res_apk_download_url";s:28:"/remote/apk_file_dir/abc.apk";s:12:"res_file_dir";s:20:"/remote/apk_file_dir";s:12:"apk_file_dir";s:20:"/remote/apk_file_dir";}';
        $this->remote_testdata = 'a:58:{s:2:"id";s:3:"549";s:10:"channel_id";s:6:"100112";s:10:"game_order";s:1:"0";s:11:"is_visiable";s:1:"1";s:19:"ch_apk_download_url";s:0:"";s:13:"ch_bg_picture";s:0:"";s:22:"full_game_download_url";s:0:"";s:7:"game_id";s:3:"507";s:8:"game_key";s:10:"MW3ZJBHGWH";s:9:"game_name";s:24:"熊出没之保卫家园";s:12:"package_name";s:3:"abc";s:9:"game_mode";s:1:"1";s:9:"game_type";s:1:"8";s:9:"cp_vendor";s:9:"熊出没";s:11:"package_ver";s:5:"2.5.5";s:16:"package_ver_code";s:3:"255";s:20:"rev_apk_download_url";s:28:"/remote/apk_file_dir/abc.apk";s:8:"apk_name";s:7:"abc.apk";s:5:"appid";s:0:"";s:8:"bg_music";s:28:"/neatgame/KFYGHU/bgmusic.ogg";s:10:"bg_picture";s:26:"/neatgame/KFYGHU/bgpic.jpg";s:10:"chafen_url";s:0:"";s:11:"coop_method";s:0:"";s:11:"create_time";s:10:"1429069516";s:12:"is_published";s:1:"1";s:14:"engine_version";s:2:"v2";s:8:"file_dir";s:16:"/neatgame/KFYGHU";s:9:"game_desc";s:51:"动画巨作改编，最新国民级塔防游戏。";s:8:"icon_url";s:25:"/neatgame/KFYGHU/icon.png";s:11:"is_maintain";s:1:"0";s:12:"maintain_tip";s:0:"";s:12:"manifest_url";s:34:"/remote/file_dir/SceneManifest.xml";s:7:"mdf_url";s:0:"";s:6:"opt_id";s:1:"0";s:11:"orientation";s:1:"0";s:15:"package_up_time";s:1:"0";s:7:"payment";s:9:"运营商";s:11:"sdk_version";s:1:"2";s:4:"star";s:1:"5";s:11:"user_system";s:9:"运营商";s:11:"modify_time";s:10:"1429582681";s:13:"test_duration";s:1:"0";s:10:"is_visible";s:1:"1";s:8:"ver_last";s:1:"0";s:13:"rev_game_name";s:24:"熊出没之保卫家园";s:13:"old_data_flag";N;s:17:"chn_test_duration";s:2:"-1";s:16:"apk_download_url";s:28:"/remote/apk_file_dir/abc.apk";s:5:"apkid";s:3:"423";s:11:"revision_id";s:3:"469";s:12:"cpk_file_dir";s:16:"/neatgame/KFYGHU";s:16:"manifest_version";s:9:"181443811";s:12:"res_icon_url";s:20:"/remote/res_icon.png";s:12:"res_bg_music";s:0:"";s:14:"res_bg_picture";s:14:"/remote/bg.png";s:20:"res_apk_download_url";s:28:"/remote/apk_file_dir/abc.apk";s:12:"res_file_dir";s:20:"/remote/apk_file_dir";s:12:"apk_file_dir";s:20:"/remote/apk_file_dir";}';
        $this->result_testdata = 'a:58:{s:2:"id";s:3:"549";s:10:"channel_id";s:6:"100112";s:10:"game_order";s:1:"0";s:11:"is_visiable";s:1:"1";s:19:"ch_apk_download_url";s:0:"";s:13:"ch_bg_picture";s:0:"";s:22:"full_game_download_url";s:0:"";s:7:"game_id";s:3:"507";s:8:"game_key";s:10:"MW3ZJBHGWH";s:9:"game_name";s:24:"熊出没之保卫家园";s:12:"package_name";s:3:"abc";s:9:"game_mode";s:1:"1";s:9:"game_type";s:1:"8";s:9:"cp_vendor";s:9:"熊出没";s:11:"package_ver";s:5:"2.5.5";s:16:"package_ver_code";s:3:"255";s:20:"rev_apk_download_url";s:28:"/remote/apk_file_dir/abc.apk";s:8:"apk_name";s:7:"abc.apk";s:5:"appid";s:0:"";s:8:"bg_music";s:28:"/neatgame/KFYGHU/bgmusic.ogg";s:10:"bg_picture";s:26:"/neatgame/KFYGHU/bgpic.jpg";s:10:"chafen_url";s:0:"";s:11:"coop_method";s:0:"";s:11:"create_time";s:10:"1429069516";s:12:"is_published";s:1:"1";s:14:"engine_version";s:2:"v2";s:8:"file_dir";s:16:"/neatgame/KFYGHU";s:9:"game_desc";s:51:"动画巨作改编，最新国民级塔防游戏。";s:8:"icon_url";s:25:"/neatgame/KFYGHU/icon.png";s:11:"is_maintain";s:1:"0";s:12:"maintain_tip";s:0:"";s:12:"manifest_url";s:33:"/local/file_dir/SceneManifest.xml";s:7:"mdf_url";s:0:"";s:6:"opt_id";s:1:"0";s:11:"orientation";s:1:"0";s:15:"package_up_time";s:1:"0";s:7:"payment";s:9:"运营商";s:11:"sdk_version";s:1:"2";s:4:"star";s:1:"5";s:11:"user_system";s:9:"运营商";s:11:"modify_time";s:10:"1429582681";s:13:"test_duration";s:1:"0";s:10:"is_visible";s:1:"1";s:8:"ver_last";s:1:"0";s:13:"rev_game_name";s:24:"熊出没之保卫家园";s:13:"old_data_flag";N;s:17:"chn_test_duration";s:2:"-1";s:16:"apk_download_url";s:27:"/local/apk_file_dir/abc.apk";s:5:"apkid";s:3:"423";s:11:"revision_id";s:3:"469";s:12:"cpk_file_dir";s:16:"/neatgame/KFYGHU";s:16:"manifest_version";s:9:"181443811";s:12:"res_icon_url";s:23:"/local/chn/res_icon.png";s:12:"res_bg_music";s:0:"";s:14:"res_bg_picture";s:17:"/local/chn/bg.png";s:20:"res_apk_download_url";s:28:"/remote/apk_file_dir/abc.apk";s:12:"res_file_dir";s:20:"/remote/apk_file_dir";s:12:"apk_file_dir";s:20:"/remote/apk_file_dir";}';
	}

	public function setUp()
	{
    }

    public function tearDown()
	{
    }

    public function test_export()
    {
        $revision_id = 469;
        $channel_id = 100112;
        $sync = new Game_sync();
        $result = $sync->export($revision_id, $channel_id);
        $testdata1 = $result;
        $testdata1['game_id'] = '1111';
        $testdata1['revision_id'] = '1234';
        $testdata1['manifest_url'] = '/local/file_dir/SceneManifest.xml';
        $testdata1['apk_download_url'] = '/local/apk_file_dir/abc.apk';
        $testdata1['res_icon_url'] = '/local/chn/res_icon.png';
        $testdata1['res_bg_picture'] = '/local/chn/bg.png';
        $testdata2 = $result;
        $testdata2['manifest_url'] = '/remote/file_dir/SceneManifest.xml';
        $testdata2['apk_download_url'] = '/remote/apk_file_dir/abc.apk';
        $testdata2['res_icon_url'] = '/remote/res_icon.png';
        $testdata2['res_icon_url'] = '/remote/res_icon.png';
        $testdata2['res_bg_picture'] = '/remote/bg.png';
        $localtd1 = serialize($testdata1);
        $localtd2 = serialize($testdata2);
        $td3result = $testdata1;
        $td3result['game_id'] = $result['game_id'];
        $td3result['revision_id'] = $result['revision_id'];
        $localtd3 = serialize($td3result);
        $this->result_testdata = $localtd3;
        $this->assertTrue($result);
        $result = $sync->export(1234567, $channel_id);
        $this->assertFalse($result);
    }

    public function test_merge() {
        $td1 = unserialize($this->local_testdata);
        $td2 = unserialize($this->remote_testdata);
        $td3 = unserialize($this->result_testdata);
        $sync = new Game_sync();
        $result = $sync->merge($td1, $td2);
        $result2 = array_diff($td3, $result);
        $this->assertTrue(count($result2));
    }

    public function test_applychnres() {
        $td3 = unserialize($this->remote_testdata);
        $td3['res_apk_download_url'] = $td3['apk_download_url'] ;
        $sync = new Game_sync();
        //$result = $sync->apply_chnres($td3);
        //$this->assertTrue($result);
    }

    public function test_addorupdaterevision() {
        $td3 = unserialize($this->remote_testdata);
        $td3['res_apk_download_url'] = $td3['apk_download_url'] ;
        $td3['apk_name'] = basename($td3['apk_download_url']);
        $td3['package_name'] = preg_replace('/\.apk$/','', basename($td3['apk_download_url']));
        $sync = new Game_sync();
        //$result = $sync->add_or_update_revision($td3);
        //$this->assertTrue($result);
    }

    public function test_batch_put() {
        $td1 = unserialize($this->local_testdata);
        $td2 = unserialize($this->remote_testdata);
        $td3 = unserialize($this->result_testdata);
        $sync = new Game_sync();
        $result = $sync->merge($td1, $td2);
        //$result = $sync->batch_put($td1);
    }

    public function test_verification_live_server() {
        $td1 = unserialize($this->local_testdata);
        $td2 = unserialize($this->remote_testdata);
        $td3 = unserialize($this->result_testdata);
        $sync = new Game_sync();
        $result = $sync->verification_live_server($td2);
        $this->assertTrue($result);
    }

    public function test_exportliveserver() {
        $td1 = unserialize($this->local_testdata);
        $data = unserialize($this->remote_testdata);
        $td3 = unserialize($this->result_testdata);
        $gamekey = $data['game_key'];
        $channel_id = $data['channel_id'];
        $package_ver_code = $data['package_ver_code'];
        
        $sync = new Game_sync();
        $result = $sync->export_live_server($gamekey, 9999123, $channel_id);
        $this->assertFalse($result[0]);
        $result = $sync->export_live_server($gamekey, $package_ver_code, $channel_id);
        $this->assertTrue($result);
    }

    public function test_diff() {
        $td1 = unserialize($this->local_testdata);
        $data = unserialize($this->remote_testdata);
        $td3 = unserialize($this->result_testdata);
        $gamekey = $data['game_key'];
        $channel_id = $data['channel_id'];
        $package_ver_code = $data['package_ver_code'];
        
        $sync = new Game_sync();
        $result = $sync->diff($td1, $data);
        $this->assertTrue($result);
    }

    public function test_apply() {
        $td1 = unserialize($this->local_testdata);
        $td2 = unserialize($this->remote_testdata);
        $td3 = unserialize($this->result_testdata);
        $sync = new Game_sync();
        //$result = $sync->apply($td3, '');
        //$this->assertTrue($result[0]);
    }

    public function test_scanresource() {
        $manifest_url = "http://downplayer.coco.cn/neatgame/zguywo/SceneManifest.xml";
        $sync = new Game_sync();
        $result = $sync->scan_resources($manifest_url);
        $this->assertTrue(count($result['cpk_list']) == 30);
    }
    
    public function test_cpk_mapping() {
        $data = unserialize($this->remote_testdata);
        $manifest_url = "http://downplayer.coco.cn/neatgame/zguywo/SceneManifest.xml";
        $sync = new Game_sync();
        //$result = $sync->fixresourcemap($data, $manifest_url);
        //$this->assertTrue($result); 
    }
}
// EOF
