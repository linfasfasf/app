<?php
class test_engineplug_chafenplug extends APIWebTestCase
{
    public function __construct() {
        parent::__construct('engineplug_chafenplug');
        $this->load->helper(array('url', 'simple_test/simple_test'));
    }

    public function setUp() {
    }

    public function tearDown() {
    }
        
    function test_engineplug(){
        $uri = '/capi/apiv4/engineplug';
        //$cpus = array( 'armeabi-v7a', 'arm64-v8a', '');
        $cpus = array( 'armeabi-v7a', ''); // arm64-v8a 暂缺
        $engines = array( 'lua', 'cpp', 'js', '');
        $vers = array('v2', 'v3', '', 'bad');
        foreach($cpus as $cpu) {
            foreach($engines as $engine) {
                foreach($vers as $ver) {
                    $requestparams = array(
                        'engine_version'    => $ver,
                        'engine' => $engine,
                        'engine_arch'    => $cpu,
                    );
                    $this->setMaximumRedirects(0);
                    $response = $this->send_request($uri, $requestparams, 'get');
                    if($ver == '') {
                        $this->assertText('302');
                    }elseif( $ver == 'bad') {
                        $this->assertText('305');
                    }else{
                        $this->assertResponse(302);
                    }
                }
            }
        }

        $ver = array(
            'v2' => 'http:\/\/downplayer.coco.cn\/engineplug\/libcocos2dlua_v2.so.zip',
            'v3' => 'http:\/\/downplayer.coco.cn\/engineplug\/libcocos2dlua_v3.so.zip',
        );
        foreach ($ver as $key => $value) {
            $requestparams = array(
                'ver' => $key,
                'engine_version' => 'v2',
                'engine' => 'js',
                'engine_arch' => 'armeabi',
            );
            $this->setMaximumRedirects(0);
            $response = $this->send_request($uri, $requestparams, 'get');
            $this->assertResponse(302);
            $b = $this->getBrowser();
            echo $headers = $b->getHeaders();
            echo $value;
            $this->assertTrue(preg_match('/'.$value.'/', $headers));
        }
    }

    function test_chafenplug(){
        $uri = '/capi/apiv4/chafenplug';
        $cpus = array( 'armeabi-v7a', 'arm64-v8a', 'armeabi', 'x86', 'x86_64');
        shuffle($cpus);
        $requestparams = array(
            'arch'    => implode(',',$cpus),
        );
        //随机排序传多个值
        $this->setMaximumRedirects(0);
        $response = $this->send_request($uri, $requestparams, 'get');
        $this->assertResponse(302);
        $b = $this->getBrowser();
        $headers = $b->getHeaders();
        $this->assertTrue(preg_match('/'.$cpus[0].'\/libbspatch\.so.zip/', $headers));

        //传一个值
        $requestparams = array(
            'arch'    => $cpus[0],
        );
        $response = $this->send_request($uri, $requestparams, 'get');
        $this->assertResponse(302);
        $b = $this->getBrowser();
        $headers = $b->getHeaders();
        $this->assertTrue(preg_match('/'.$cpus[0].'\/libbspatch\.so.zip/', $headers));               

        // 没有指定参数时
        $requestparams = array();
        $this->setMaximumRedirects(0);
        $response = $this->send_request($uri, $requestparams, 'get');
        $this->assertText('302');

        //错误参数
        $requestparams = array('arch' => 'bad');
        $this->setMaximumRedirects(0);
        $response = $this->send_request($uri, $requestparams, 'get');
        $this->assertText('305');
    }

    function test_engineplugv4(){
        $uri = '/capi/apiv4/engineplug';
        $cpus = array( 'armeabi-v7a', 'armeabi', 'x86','bad');
        $engines = array( 'lua', 'cpp', 'js','bad');
        $vers = array('v2', 'v3','');
        foreach($cpus as $cpu) {
            foreach($engines as $engine) {
                foreach($vers as $ver) {
                    $requestparams = array(
                        'engine_version'    => $ver,
                        'engine' => $engine,
                        'engine_arch'    => $cpu,
                    );
                    $this->setMaximumRedirects(0);
                    $response = $this->send_request($uri, $requestparams, 'get');
                    if($ver == ''){
                        $this->assertText('302');
                    }
                    elseif($cpu == 'bad' || $engine == 'bad') {
                        $this->assertText('305');
                    }else{
                        $this->assertResponse(302);
                    }
                }
            }
        }
    }
}
