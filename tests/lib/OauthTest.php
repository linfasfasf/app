<?php

require_once(FCPATH . 'modules/cocos_packingtool/controllers/oauth.php');

class OauthTest extends Oauth {
    public function __construct()
    {
        parent::__construct();
    }

    public function encode_pass($password) {
        return parent::encode_pass($password);
    }

    public function decode_pass($password) {
        return parent::decode_pass($password);
    }

    public function do_hash(&$params) {
        return parent::do_hash($params);
    }

    public function add_user($corpid, $username, $password, $email) {
        return parent::add_user($corpid, $username, $password, $email);
    }

    public function add_corp($id, $name) {
        return parent::add_corp($id, $name);
    }

    public function oauth_login($username, $password) {
        return parent::oauth_login($username, $password);
    }

    public function oauth_userinfo($access_token) {
        return parent::oauth_userinfo($access_token);
    }

    public function handle_response($response) {
        return parent::handle_response($response);
    }

    public function is_login() {
        return parent::is_login();
    }

    public function handle_oauth_login_response($response) {
        return parent::handle_oauth_login_response($response);
    }

}
