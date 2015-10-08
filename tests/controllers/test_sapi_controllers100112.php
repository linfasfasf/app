<?php
class test_sapi_controllers100112 extends APIWebTestCase
{
    protected $rand = '';

    public function __construct()
    {
        parent::__construct('SAPI/100112');
        $this->load->helper('url');
        $this->debug = FALSE;
    }

    public function setUp()
    {
        $chn = '100112';
        $secret = 'Tyko48gMebd38Tc9tmoe';
        $db = $this->load->database('',TRUE);
        //$sql = "select channel_key from cp_channel_info where channel_id='100112'";
        $sql = "update cp_channel_info set channel_key=? where channel_id=?";
        $query = $db->query($sql, array($secret, $chn));
    }

    public function tearDown()
    {

    }

    protected function hash3(&$params, $channel_key)
    {
        // normalized params
        ksort($params);
        $arr_signstring = array();
        foreach($params as $k => $v) {
            $arr_signstring[] = "$k=$v";
        };
        $signstring = implode('&', $arr_signstring);
        $signstring .= $channel_key;
        $sign = md5($signstring);
        $params['sign'] = $sign; 
    }

    // /sapi/gamelist
    function test_gamelist_page(){
        $uri = '/sapi/gamelist';
        $mid = '20';
        $chn = '100112';
        $secret = 'Tyko48gMebd38Tc9tmoe';
        $start = '2';
        $len = '1';
        $requestparams = array(
            'mid'=> $mid,
            'chn'=> $chn,
            'start'=> $start,
            'len'=>$len,
        );
        $this->hash3($requestparams, $secret);

        $this->setMaximumRedirects(0);
        echo site_url($uri). '?'. $this->request_builder($requestparams) . '<br />';
        $response = $this->send_request($uri, $requestparams, 'get');
        if($this->debug) {
            echo $response;
        }
        //$this->assertResponse(302);
        $this->assertText('download_url');
    }

    function test_gamelist(){
        $uri = '/sapi/gamelist';
        $mid = '20';
        $chn = '100112';
        $secret = 'Tyko48gMebd38Tc9tmoe';
        $requestparams = array(
            'mid'=> $mid,
            'chn'=> $chn,
            'start'=>'0',
            'len'=>10,
        );
        $this->hash3($requestparams, $secret);

        $this->setMaximumRedirects(0);
        echo site_url($uri). '?'. $this->request_builder($requestparams) . '<br />';
        $response = $this->send_request($uri, $requestparams, 'get');
        if($this->debug) {
            echo $response;
        }
        //$this->assertResponse(302);
        $this->assertText('download_url');

        $chn = '111112'; // invalid chn
        $requestparams = array(
            'mid'=> $mid,
            'chn'=> $chn,
            'start'=>'0',
            'len'=>20,
        );
        $this->hash3($requestparams, $secret);
        $response = $this->send_request($uri, $requestparams, 'get');
        $this->assertText('Parameter error2');
    }

    function test_gamelist_101() {
        $uri = '/sapi/gamelist';
        $mid = '20';
        $chn = '100112';
        $secret = 'Tyko48gMebd38Tc9tmoe';
        $requestparams = array(
            'mid'=> $mid,
            'chn'=> $chn,
            'start'=>'0',
            'len'=>101,
        );
        $this->hash3($requestparams, $secret);

        $this->setMaximumRedirects(0);
        echo site_url($uri). '?'. $this->request_builder($requestparams) . '<br />';
        $response = $this->send_request($uri, $requestparams, 'get');
        if($this->debug) {
            echo $response;
        }
        //$this->assertResponse(302);
        $this->assertText('download_url');
    }

    // /sapi/chngmstate
    function test_chngmstate(){
        $uri = '/sapi/chngmstate';
        $secret = 'Tyko48gMebd38Tc9tmoe';
        $requestparams = array(
            'mid'=> '20',
            'chn'=> '100112',
        );
        $this->hash3($requestparams, $secret);

        $this->setMaximumRedirects(0);
        echo site_url($uri). '?'. $this->request_builder($requestparams) . '<br />';
        $response = $this->send_request($uri, $requestparams, 'get');
        if($this->debug) {
            echo $response;
        }
        //$this->assertResponse(302);
        $this->assertText('package_num');
    }
    function test_sign_chngmstate(){
        $uri = '/sapi/chngmstate';
        $secret = 'Tyko48gMebd38Tc9tmoe';

        $requestparams = array(
            'mid'=> 123,
            'chn'=> 123456,
        );
        $this->hash3($requestparams, $secret);
        $requestparams['mid'] = '999'; // 错误的 mid

        $this->setMaximumRedirects(0);
        echo site_url($uri). '?'. $this->request_builder($requestparams) . '<br />';
        $response = $this->send_request($uri, $requestparams, 'get');
        if($this->debug) {
            echo $response;
        }
        $this->assertText('Parameter error2');
    }
    function test_sign_gamelist() {
        //==================== 
        $uri = '/sapi/gamelist';
        $secret = 'Tyko48gMebd38Tc9tmoe';
        $requestparams = array(
            'mid'   => '123',
            'chn'   => '100112',
            'start' => '2',
            'len'   => '2',
        );
        $this->hash3($requestparams, $secret);
        $requestparams['len'] = '3';  // 错误的 len

        $this->setMaximumRedirects(0);
        $response = $this->send_request($uri, $requestparams, 'get');
        if($this->debug) {
            echo $response;
        }
        //$this->assertResponse(302);
        $this->assertText('Parameter error2');
    }

    function test_sign_gamelist_nomid() {
        //==================== 
        $uri = '/sapi/gamelist';
        $secret = 'Tyko48gMebd38Tc9tmoe';
        $requestparams = array(
            'mid'   => '',
            'chn'   => '100112',
            'start' => '2',
            'len'   => '2',
        );
        $this->hash3($requestparams, $secret);
        $this->setMaximumRedirects(0);
        $response = $this->send_request($uri, $requestparams, 'get');
        if($this->debug) {
            echo $response;
        }
        $this->assertText('error');
    }

    function test_sign_gamelist_additionalparam() {
        //==================== 
        $uri = '/sapi/gamelist';
        $secret = 'Tyko48gMebd38Tc9tmoe';
        $requestparams = array(
            'mid'   => '123',
            'chn'   => '100112',
            'start' => '0',
            'len'   => '340',
            'extraparam' => '',
            'extraparam2' => 'asdfkalsdkfj123491723468',
        );
        $this->hash3($requestparams, $secret);
        $this->setMaximumRedirects(0);
        echo site_url($uri). '?'. $this->request_builder($requestparams) . '<br />';
        $response = $this->send_request($uri, $requestparams, 'get');
        if($this->debug) {
            echo $response;
        }
        $this->assertText('ok');
    }

    function test_gamelist_invalidlen(){
        $uri = '/sapi/gamelist';
        $mid = '20';
        $chn = '100112';
        $secret = 'Tyko48gMebd38Tc9tmoe';
        $start = '2';
        $len = '1.0';
        $requestparams = array(
            'mid'=> $mid,
            'chn'=> $chn,
            'start'=> $start,
            'len'=>$len,
        );
        $this->hash3($requestparams, $secret);

        $this->setMaximumRedirects(0);
        echo site_url($uri). '?'. $this->request_builder($requestparams) . '<br />';
        $response = $this->send_request($uri, $requestparams, 'get');
        if($this->debug) {
            echo $response;
        }
        //$this->assertResponse(302);
        $this->assertText('Parameter error');
    }

}

/* End of file test_users_model.php */
/* Location: ./tests/models/test_users_model.php */
