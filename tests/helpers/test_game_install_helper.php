<?php
class Test_game_install_helper extends CodeIgniterUnitTestCase
{

    public function __construct()
    {
		parent::__construct('game_install_helper');
        $this->load->helper('game_install');
    }

    public function setUp()
    {
    }

    public function tearDown()
    {
    }

    public function test_fixresourcemap(){
        $db = $this->load->database('', TRUE);
        // TODO: 自动选择合适的 revision_id 
        //$this->assertTrue(fixresourcemap(64));
    }

    // 有效的 version
    public function test_parse_manifest_ver_good() {
        $xml_string = '<?xml version="1.0" encoding="utf-8"?><scene_list ver="20150114165729"></scene_list>';
        $ver = '20150114165729';
        $tmp = FCPATH . 'uploads/syncdir';
        $file = $tmp . '/SceneManifest.xml';
        if(file_exists($file)) {
            unlink ($file);
        }
        $handler = fopen($file, 'w');
        fwrite($handler, $xml_string);
        fclose($handler);
        $manifest_ver = parse_manifest_ver($file);
        $this->assertEqual($manifest_ver, $ver);
        unlink($file);
    }

    // 非法的 xml
    public function test_parse_manifest_ver_invalid() {
        $xml_string = 'asdljkklasdjf';
        $ver = '0';
        $tmp = FCPATH . 'uploads/syncdir';
        $file = $tmp . '/SceneManifest.xml';
        if(file_exists($file)) {
            unlink ($file);
        }
        $handler = fopen($file, 'w');
        fwrite($handler, $xml_string);
        fclose($handler);
        $manifest_ver = parse_manifest_ver($file);
        $this->assertTrue(isset($manifest_ver));
        unlink($file);
    }

    // 没有 version
    public function test_parse_manifest_ver_bad() {
        $xml_string = '<?xml version="1.0" encoding="utf-8"?><scene_list></scene_list>';
        $ver = 3;
        $tmp = FCPATH . 'uploads/syncdir';
        $file = $tmp . '/SceneManifest.xml';
        if(file_exists($file)) {
            unlink ($file);
        }
        $handler = fopen($file, 'w');
        fwrite($handler, $xml_string);
        fclose($handler);
        $manifest_ver = parse_manifest_ver($file);
        $this->assertEqual($manifest_ver, 0);
        $manifest_ver = parse_manifest_ver($file, $ver);
        $this->assertEqual($manifest_ver, $ver + 1);
        unlink($file);
    }

    public function test_extract_fakeapk(){
        $this->test_file_path = FCPATH.'tests/testfile';
        $fake_apk_path = $this->test_file_path . '/test_extract_genuine.apk';
        $genuine_apk_path = $this->test_file_path . '/_test_extract_genuine.apk';
        if(is_file($genuine_apk_path)) {
            unlink($genuine_apk_path);
        }
        $result = extract_genuine_apk($fake_apk_path);
        $this->assertTrue($result);
        $this->assertTrue(is_file($genuine_apk_path));
        if(is_file($genuine_apk_path)) {
            unlink($genuine_apk_path);
        }
    }
}

/* Location: ./tests/libraries/test_CocosPlay_Config */
