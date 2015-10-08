<?php
class test_uploadzip_library extends CodeIgniterWebTestCase
{
    public function __construct() {
        parent::__construct('CocosPackingtool/UPLOADZIP_LRBRARY');
        require_once(__DIR__.'/../lib/Uploadzip_libraryTest.php');
        ob_start();
        $this->uploadtest= new Uploadzip_libraryTest();
        $this->test_file_path = FCPATH.'tests/testfile';
        ob_clean();
        $this->target_info = array(
            'gameinfo' => array(
                "game_name" => "required",
                "game_mode" => "required|gamemode",
                "package_name" => "required",
                "game_key" => "required",
                "cp_vendor" => "required",
                "engine_version" => "required|numeric",
                "orientation" => "required|orientation",
                "package_ver_code" => "required|numeric",
                "package_ver" => "required",
                "channel_id" => "",
            ),
            'fileinfo' => array(
                'icon' => "required",
                'background' => "required",
                'SceneManifest' => "required",
                'assets' => "required",
                'apk' => "required",
                'cpk' => "required",
                'music' => "required",
            ),
        );
        $this->trueinfo = array(
                    'gameinfo'=>array(
                        'game_name' => '武尊',
                        'game_mode' => '1',
                        'package_name' => 'test',
                        'game_key' => 'asaaahtyug',
                        'cp_vendor' => '上海军梦',
                        'sdk_version' => '2',
                        'engine_version' => '2',
                        'orientation' => 1,
                        'package_ver_code' => '1',
                        'package_ver' => '2'
                    ),
                    'fileinfo'=>array(
                        'apk' => array('a.apk'),
                        'cpk' => array('a.cpk','a.cpk','a.cpk'),
                        'assets' => 'assets.md5',
                        'icon' => 'icon.png',
                        'background' => 'background.jpg',
                        'SceneManifest' => 'SceneManifest.zip',
                        'info' => 'info.json',
                        'music' => 'music.ogg',
                    ),
        );
    }
    
    public function setUp() {
        //for unzip_to_dir
        
    }
    
    public function tearDown() {
        
    }
    
    public function test_get_manifest_ver()
    {
        $path = 'tests/testfile/SceneManifest.xml';
        $this->uploadtest->get_manifest_ver($path);
        $this->assertEqual($this->uploadtest->get_manifest_ver($path), 9999);
    }
    
    public function test_check_all_info()
    {
        //正确的情况
        $target_info = $this->target_info;
        $trueinfo = $this->trueinfo;
        list($code,$msg) = $this->uploadtest->check_all_info($target_info,$trueinfo);
        $this->assertTrue($code);
        
        //fileinfo项目不全
        $tempinfo = $trueinfo;
        unset($tempinfo['fileinfo']['apk']);
        list($code,$msg) = $this->uploadtest->check_all_info($target_info,$tempinfo);
        $this->assertFalse($code);
        unset($tempinfo['fileinfo']);
        list($code,$msg) = $this->uploadtest->check_all_info($target_info,$tempinfo);
        $this->assertFalse($code);
        
        //gameinfo项目不全
        $tempinfo = $trueinfo;
        unset($tempinfo['gameinfo']['game_mode']);
        list($code,$msg) = $this->uploadtest->check_all_info($target_info,$tempinfo);
        $this->assertFalse($code);
        unset($tempinfo['gameinfo']);
        list($code,$msg) = $this->uploadtest->check_all_info($target_info,$tempinfo);
        $this->assertFalse($code);
        
        //非独立包不传gamekey的错误情况
        $tempinfo = $trueinfo;
        unset($tempinfo['gameinfo']['game_key']);
        $tempinfo['gameinfo']['game_mode'] = 1;
        list($code,$msg) = $this->uploadtest->check_all_info($target_info,$tempinfo);
        $this->assertFalse($code);
        
        //gamemode不合法的情况
        $tempinfo = $trueinfo;
        $tempinfo['gameinfo']['game_mode'] = 999;
        list($code,$msg) = $this->uploadtest->check_all_info($target_info,$tempinfo);
        $this->assertFalse($code);
        
        //数字检查
        $tempinfo = $trueinfo;
        $tempinfo['gameinfo']['package_ver_code'] = 'ss';
        list($code,$msg) = $this->uploadtest->check_all_info($target_info,$tempinfo);
        $this->assertFalse($code);
        
        
        //版本错误检查,required检查
        $tempinfo = $trueinfo;
        $tempinfo['gameinfo']['package_ver_code'] = 'ss';
        list($code,$msg) = $this->uploadtest->check_all_info($target_info,$tempinfo);
        $this->assertFalse($code);
        $tempinfo['gameinfo']['package_ver_code'] = '';
        list($code,$msg) = $this->uploadtest->check_all_info($target_info,$tempinfo);
        $this->assertFalse($code);
    }
    
    
    
    public function test_unzip_to_dir_and_del_dir()
    {
        $path = $this->test_file_path;
        copy($path.'/uploadzip.zip',$path.'/temp.zip');
        $path = FCPATH.'/tests/testfile/temp.zip';
        $dir = FCPATH.$this->uploadtest->unzip_to_dir($path);
        $this->assertTrue(is_file($dir.'/info.json'));
        $this->uploadtest->del_dir($dir);
        $this->assertFalse(is_dir($dir));
        //unzip 里的 rename 现改为 copy，这个 assert 不会成功
        //$this->assertFalse(is_file($path));
    }
    
    public function test_get_info()
    {
        $path = $this->test_file_path.'/uploadzip.zip';
        list($code,$info) = $this->uploadtest->get_info($path);
        $this->assertTrue($code);
    }
    
    public function test_get_fileinfo_by_zip(){
        $path = $this->test_file_path.'/uploadzip.zip';
        list($code,$msg) = $this->uploadtest->get_fileinfo_by_zip($path);
        $this->assertTrue($code);
    }
    
    public function test_find_chn_resources(){
        echo $path = $this->test_file_path;
        $result = $this->uploadtest->find_chn_resources($path);
        var_dump($result);
        $dir = array(
            'chn1','chn2'
        );
        $this->assertTrue(count($result) === 2);
        $this->assertTrue(in_array(basename($result[0]),$dir)&&in_array(basename($result[1]),$dir));
    }
    
    public function test_validation_channel_id(){
        $this->assertFalse($this->uploadtest->validation_channel_id('89898'));
        $this->assertTrue($this->uploadtest->validation_channel_id('111111'));
    }
    
    public function test_get_file_info_by_dir(){
        $path = $this->test_file_path;
        $target_files = array(
            'zip'=>'*.zip'
        );
        $result = $this->uploadtest->get_file_info_by_dir($target_files,$path);
        $file_path = array_pop($result);
        $file = basename($file_path);
        $this->assertTrue($file === "uploadzip.zip");
    }
}
