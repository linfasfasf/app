<?php
class test_cocos_packingtool_oauth_controller extends CodeIgniterWebTestCase
{
	public function __construct()
	{
		parent::__construct('CocosPackingtool/OAUTH');
        $this->load->helper(array('url','simple_test/simple_test'));
        $this->host = site_url();
        require_once( __DIR__ . '/../lib/OauthTest.php');
        ob_start();
        $this->OauthTest = new OauthTest();
        ob_clean();
	}

	public function setUp()
	{
    }

    public function tearDown()
	{
    }

    public function test_encode_decode_pass() {
        $pass = 'abc123';
        $encoded = $this->OauthTest->encode_pass($pass);
        $correct_encoded = 'XipzdyVlYWJjMTIzYWEmKl4mKg=='; 
        $this->assertEqual($correct_encoded, $encoded);
        $decoded = $this->OauthTest->decode_pass($correct_encoded);
        $this->assertEqual($decoded, $pass);
    }

    public function test_handle_response_good() {
        $res_good = '{"access_token":"50dc9ef36da9e38a50afbb8701926867","refresh_token":"da8f729854dfb156bca3899f6acf1d3e","expires_in":3600,"scope":"basic"}';
        $res_bad = '{"error":"invalid_grant","error_description":"\u6388\u6743\u5931\u8d25\uff0c\u7528\u6237\u540d\u6216\u5bc6\u7801\u9519\u8bef\u3002","error_code":20007,"error_uri":""}';
        $res_invalid = 'invalid blah balh';
        $res_invalid = '';
        $result = $this->OauthTest->handle_response( $res_good );
        $this->assertTrue(isset($result[1]));
        $this->assertEqual($result[0], '0');
        $this->assertEqual($result[1]->access_token, '50dc9ef36da9e38a50afbb8701926867');
        $result = $this->OauthTest->handle_response( $res_bad );
        $this->assertTrue(isset($result[0]));
        $this->assertEqual($result[0], '1');
        $this->assertEqual($result[1]->error, 'invalid_grant');
    }
    public function test_handle_response_bad() {
        $res_good = '{"access_token":"50dc9ef36da9e38a50afbb8701926867","refresh_token":"da8f729854dfb156bca3899f6acf1d3e","expires_in":3600,"scope":"basic"}';
        $res_bad = '{"error":"invalid_grant","error_description":"\u6388\u6743\u5931\u8d25\uff0c\u7528\u6237\u540d\u6216\u5bc6\u7801\u9519\u8bef\u3002","error_code":20007,"error_uri":""}';
        $res_invalid = 'invalid blah balh';
        $res_invalid = '';
        $result = $this->OauthTest->handle_response( $res_bad );
        $this->assertTrue(isset($result[0]));
        $this->assertEqual($result[0], '1');
        $this->assertEqual($result[1]->error, 'invalid_grant');
    }

    public function test_handle_response_invalid() {
        $res_invalid1 = 'invalid blah balh';
        $res_invalid2 = '';
        $result = $this->OauthTest->handle_response( $res_invalid1 );
        $this->assertTrue( $result[0] === 1);
        $result = $this->OauthTest->handle_response( $res_invalid2 );
        $this->assertTrue( $result[0] === 1);
    }
    public function test_is_login() {
        $_SESSION['packingtool_islogin'] = TRUE;
        $logged_in = $this->OauthTest->is_login();
        $this->assertTrue($logged_in);
        $_SESSION['packingtool_islogin'] = FALSE;
        $logged_in = $this->OauthTest->is_login();
        $this->assertFalse($logged_in);
    }

    public function test_logout() {
        $_SESSION['packingtool_islogin'] = TRUE;
        $this->OauthTest->logout();
        $this->assertFalse(isset($_SESSION['packingtool_islogin']));
    }

    public function test_oauth_login_handler() {
        ob_start();
        $res_good = '{"access_token":"50dc9ef36da9e38a50afbb8701926867","refresh_token":"da8f729854dfb156bca3899f6acf1d3e","expires_in":3600,"scope":"basic"}';
        $res_bad = '{"error":"invalid_grant","error_description":"\u6388\u6743\u5931\u8d25\uff0c\u7528\u6237\u540d\u6216\u5bc6\u7801\u9519\u8bef\u3002","error_code":20007,"error_uri":""}';
        $data = array(
            'packingtool_islogin' => '',
            'packingtool_accesstoken' => '',
            'packingtool_refreshtoken' => '',
        );
        //$this->session->sess_expiration = $json->expires_in;
        $this->session->unset_userdata($data);
        list($result, $json) = $this->OauthTest->handle_response($res_good);
        $this->OauthTest->handle_oauth_login_response($json);
        /*
        $this->assertTrue($this->session->userdata('packingtool_islogin'));
        $this->assertEqual($this->session->userdata('packingtool_accesstoken'), '50dc9ef36da9e38a50afbb8701926867');
         */
        $this->assertTrue($_SESSION[ 'packingtool_islogin' ]);
        $this->assertEqual($_SESSION[ 'packingtool_accesstoken' ], '50dc9ef36da9e38a50afbb8701926867');
        list($result, $json) = $this->OauthTest->handle_response($res_bad);
        $this->assertTrue($result);
        ob_end_flush();
    }
}
// EOF
