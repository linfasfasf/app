<?php
class test_cocos_packingtool_controller extends APIWebTestCase
{
	public function __construct()
	{
		parent::__construct('CocosPackingtool');
        $this->load->helper(array('url','simple_test/simple_test'));
        $this->host = site_url();
        $this->user = 'zhoumao.hong@chukong-inc.com';
        $this->password = 'XaLffy9H0l6';
	}

	public function setUp()
	{
    }

    public function tearDown()
	{
    }

    public function test_login_error() {
        $user = $this->user;
        $uri = '/cocos_packingtool/login';
        $requestparams = array(
            'identity'=> $user,
        );
        $response = $this->send_request($uri, $requestparams, 'post');
        $this->assertText('error');
    }

    public function test_login_fail() {
        $user = $this->user;
        $password = '_easef'; // fake password
        $uri = '/cocos_packingtool/login';
        $requestparams = array(
            'identity'=> $user,
            'password' => $password,
        );
        $response = $this->send_request($uri, $requestparams, 'post');
        $this->assertText('failed');
    }

    public function test_login_success() {
        $user = $this->user;
        $password = $this->password;
        $uri = '/cocos_packingtool/login';
        $requestparams = array(
            'identity'=> $user,
            'password' => $password,
        );
        $response = $this->send_request($uri, $requestparams, 'post');
        $this->assertText('user_id');
        $uri = '/cocos_packingtool/logged_in';
        $response = $this->send_request($uri, array(), 'get');
        $this->assertText('Logged in');
    }

    public function test_logged_in() {
        $uri = '/cocos_packingtool/logged_in';
        $response = $this->send_request($uri, array(), 'get');
        $this->assertText('0');
    }

    public function test_logout() {
        $user = $this->user;
        $password = $this->password;
        $uri = '/cocos_packingtool/login';
        $requestparams = array(
            'identity'=> $user,
            'password' => $password,
        );
        $response = $this->send_request($uri, $requestparams, 'post');
        $this->assertText('user_id');
        $uri = '/cocos_packingtool/logout';
        $response = $this->send_request($uri, array(), 'get');
        $uri = '/cocos_packingtool/logged_in';
        $response = $this->send_request($uri, array(), 'get');
        $this->assertText('Logged out');
    }


    public function test_ion_auth_register() {
        $this->load->model('auth/ion_auth_model');
        $uid = 'test@test.com';
        $id = $this->ion_auth_model->register($uid, $uid, $uid);
        echo $id; 
    }

    public function test_ologin_fail() {
        $user = 'zhoumao.hong@chukong-inc.com';
        $password = 'abc123'; // fake password
        $uri = '/cocos_packingtool/ologin';
        $requestparams = array (
            'identity'=> $user,
            'password' => $password,
        );
        $response = $this->send_request($uri, $requestparams, 'post');
        $this->assertText('1401');
    }
}
// EOF
