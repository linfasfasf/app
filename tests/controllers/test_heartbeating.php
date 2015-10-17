<?php
error_reporting(-1);
class test_heartbeating extends APIWebTestCase {
    public function __construct() {
        parent::__construct('heartbeating');
        $this->load->helper(array('url', 'simple_test/simple_test'));
        $uris = array(
            '/capi/api/engineplug' => 200,
            '/capi/api/chafenplug' => 302,
            '/capi/api/switchinfo' => 200,
            '/capi/apiv3/androidsupportv4' => 302,
            '/capi/apiv3/whitelist' => 200,
            '/capi/apiv3/channel_sdk_info' => 200,
            '/capi/apiv2/channel_sdk_info' => 200,
            '/capi/apiv3/channelgamelist' => 200,
            '/capi/apiv3/gamepackage' => 200,
            '/capi/apiv3/gamepackagedir' => 200,
            '/capi/bgv3/picdir' => 200,
            '/capi/bgv3/musicdir' => 200,
            '/capi/apiv3/resmdfdir' => 200,
            '/capi/apiv3/manifestzipdir' => 200,
            '/capi/apiv3/cpkresourcedir' => 200,
            '/capi/apiv3/updatechafendir' => 200,
            '/capi/apiv2/gamepackage' => 200,
            '/capi/apiv2/gamepackagedir' => 200,
            '/capi/apiv2/manifestzipdir' => 200,
            '/capi/apiv2/cpkresourcedir' => 200,
            //新增apiv4接口
            '/capi/apiv4/hotversiondir' => 200,
            '/capi/apiv4/updatechafendir' => 200,
            '/capi/apiv4/gamepackagedir' => 200,
            '/capi/apiv4/gamepackage' => 200,
            '/capi/apiv4/musicdir' => 200,
            '/capi/apiv4/picdir' => 200,
            '/capi/apiv4/resmdfdir' => 200,
            '/capi/apiv4/manifestzipdir' => 200,
            '/capi/apiv4/manifestcpkdir' => 200,
            '/capi/apiv4/channel_sdk_info' => 200,
            '/capi/apiv4/cpkresourcedir' => 200,
            '/capi/apiv4/channelgamelist' => 200,
            '/capi/apiv4/chafenplug' => 200,
            '/capi/apiv4/engineplug' => 200,
            '/capi/apiv4/gamesodir' => 200,
            '/capi/apiv4/rtcomp' => 200,
            '/capi/apiv4/switchinfo' => 200,
            '/capi/apiv4/whitelist' => 200,
            '/capi/apiv4/androidsupportv4' => 302,
            '/capi/apiv4/reporterror' => 200,
            '/capi/apiv4/channelsdkplug' => 200,
        );

        $this->config = $cocosplay_config = array(
            'interception' => 0, //每条请求间隔时间
            'uris' => $uris, //访问的API
            'warning_time' => 3.0, //访问时间
            'host' => 'http://playerapi.coco.cn', //访问的主机
        );
    }

    public function setUp() {
        
    }

    public function tearDown() {
        
    }

    public function test_heartbeat() {
        $uris = $this->config['uris'];
        $warning_time = $this->config['warning_time'];
        $this->host = $this->config['host'];
        $this->setMaximumRedirects(0);
        $this->setConnectionTimeout($warning_time);
        foreach ($uris as $uri => $code) {
            $this->send_request($uri, array(), 'get');
            $this->assertResponse($code);
            //sleep(1);
        }
    }
}