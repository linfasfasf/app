<?php

defined('BASEPATH') OR exit('No direct script access allowed');
error_reporting(-1);
/**
 * The admin class is basically the main controller for the backend.
 *
 * @author      PyroCMS Dev Team
 * @copyright   Copyright (c) 2012, PyroCMS LLC
 * @package	 	PyroCMS\Core\Controllers
 */
class Admin extends Public_Controller {

    /**
     * Constructor method
     */
    public function __construct() {
        parent::__construct();
		$this->load->library(array('ion_auth','form_validation'));
		$this->load->helper(array('url','language'));
    }

    /**
     * Show the control panel
     */
    public function index() {
        $is_login = $this->ion_auth->logged_in();
        if($is_login)
        {
            redirect('main');
        }
        else
        {

            $this->session->unset_userdata(array('captcha'=>''));
            // captcha  
            // sys_get_temp_dir()
            $cap = $this->new_captcha();
            /*
            * 使用开发者帐号体系
            * Client接入SSO 需要向SSO Server提交资料申请接入，SSO Server为Client分配client_id。 
            */
            $this->load->helper('cocos_passport');
            $this->config->load("sso_client", true);
            $sso_client_settings = $this->config->item('sso_client');
            $environment = $sso_client_settings['environment'];
            $is_sso_enabled = $sso_client_settings['sso_enabled'];

            // return to the referrer url 
            $url = $this->input->get_post('url', TRUE);
            if(!$url) $url = '';
            $this->load->view('admin/login', array('sso_signin_url'=>'', 'url'=>$url, 'captcha' => $cap));
        }
    }

    function ajax_refresh_captcha() { 
        $image = $this->new_captcha();
        if($image) {
            echo $image; 
        }else {
            echo '不能创建 captcha'; 
        }
    }

    function new_captcha() {
            $this->load->helper('captcha');
            $vals = array(
                'word'	=> '',
                'img_path'	=> FCPATH . '/uploads/captcha/',
                'img_url'	=> site_url('/uploads/captcha') . '/',
                //'font_path'	=> './path/to/fonts/texb.ttf',
                'img_width'	=> 120,
                'img_height' => 30,
                'expiration' => 7200
                );

            $cap = create_captcha($vals);
            $this->session->set_userdata('captcha', $cap['word']);
            //echo $cap['image'];
            return $cap['image'];
    }

    function check_captcha($captcha) {
        return TRUE;
                if (strtolower($this->session->userdata('captcha')) == strtolower($captcha)) {
                    $result = TRUE;
                }else{
                    $this->form_validation->set_message('check_captcha', 'captcha 错误');
                    $result = False;
                }
                $this->session->unset_userdata(array('captcha'=>''));
                return $result; 
    }

    /**
     * Log in
     */
    public function login() {
        $is_login = $this->ion_auth->logged_in();
        $login=$this->session->userdata();
        if($is_login)
        {
            /*
            $data = array('is_login'=>'','admin'=>'','is_sso'=>'','sso_st'=>'', 'aros'=>'', 'id'=>'', 'user_id'=>'', 'username'=>'', 'captcha'=>'');
            $this->session->unset_userdata($data);
            $logout = $this->ion_auth->logout();
            */
            return redirect('main');
        }
		//validate form input
		$this->form_validation->set_rules('identity', '用户名', 'required');
		$this->form_validation->set_rules('password', '密码', 'required');
		//$this->form_validation->set_rules('captcha', '验证码', 'required|callback_check_captcha');

        $url = $this->input->get_post('url');
        $captcha = $this->input->get_post('captcha');


		if ($this->form_validation->run() == true)
		{
			//check to see if the user is logging in
			//check for "remember me"
			//$remember = (bool) $this->input->post('remember');
            $remember = FALSE; 

            if ($this->ion_auth->login($this->input->post('identity'), $this->input->post('password'), $remember))
            {
                // clean aros first to refresh
                $this->session->unset_userdata(array('aros'=>''));
				//if the login is successful
				//redirect them back to the home page
				//$this->session->set_flashdata('flash_message', $this->ion_auth->messages());
                if($url) { 
                    redirect($url, 'refresh');
                }else {
                    redirect('main', 'refresh');
                }
            }
            else{
              
                    //if the login was un-successful
                    //redirect them back to the login page
                    $this->session->set_flashdata('flash_message', $this->ion_auth->errors());
                    if($url){
                        $url = 'admin/index?url='. urlencode($url);
                        redirect($url, 'refresh'); //use redirects instead of loading views for compatibility with MY_Controller libraries
                    }else{
                        redirect('admin/index', 'refresh'); //use redirects instead of loading views for compatibility with MY_Controller libraries
                     }
            }
		}
		else
		{
			//the user is not logging in so display the login page
			//set the flash data error message if there is one
            $this->data['flash_message'] =  $this->session->flashdata('flash_message');
			$this->data['flash_message'] .= (validation_errors()) ? '<br />' .  validation_errors() : '';

			$this->data['identity'] = array('name' => 'identity',
				'id' => 'identity',
				'type' => 'text',
				'value' => $this->form_validation->set_value('identity'),
			);
			$this->data['password'] = array('name' => 'password',
				'id' => 'password',
				'type' => 'password',
			);
			$this->data['url'] = $url;
        }
        $cap = $this->new_captcha();
        $this->data['captcha'] = $cap;
        $this->load->view('admin/login', $this->data);
    }
    /**
     * Logout
     */
    public function logout() {
        $this->load->library('session');
        $this->session->set_flashdata('success', '退出成功');
        if($this->session->userdata('is_sso')){
            // sso signout
            $this->load->helper('cocos_passport');
            $this->config->load("sso_client",true);
            $sso_client_settings = $this->config->item('sso_client');
            $environment = $sso_client_settings['environment'];
            $settings = $sso_client_settings[$environment];
            $client_id = $settings['sso_client_id'];
            $api = $settings['signout'];
            //$api = 'http://passport.cocos.com/sso/signout';
            $res = my_send_request( $api,array('client_id'=>$client_id,'url'=>'http://manage.cocosplay.coco.cn'));
        }
        $data = array('is_login'=>'','admin'=>'','is_sso'=>'','sso_st'=>'', 'aros'=>'');
        $this->session->unset_userdata($data);

		//log the user out
		$logout = $this->ion_auth->logout();

		//redirect them to the login page
		$this->session->set_flashdata('flash_message', $this->ion_auth->messages());
        redirect('admin/admin/login');
    }

    /*
     * 获取加密后的字符串
     */
    private function _getPartLoginCookie($email) {
        $this->load->helper('partlogin');
        $adminInfo = $this->admin_auth->get_admin();
        $admin_id = $adminInfo->admin_id; //  管理员id
        $group_id = $adminInfo->group_id;// 组id
        $loginDate = time();    //登陆的时间
        $loginIp = getOnLineIp();
        //$loginIp = "192.168.13.82"; //@todo线上去掉
        $str = json_encode(array('adminid' => $admin_id , 'groupid' => $group_id , 'email' => $email , 'logindate' => $loginDate , 'loginip' => $loginIp));
        //$str = $admin_id . "\t" .$group_id. "\t" . $email . "\t" . $loginDate . "\t" . $loginIp;
        $str = authcode($str, "ENCODE", $loginIp);
        $str = encode($str);
        $str = base64_encode($str);
        return $str;
    }

    function forgot_password() 
	{
		//setting validation rules by checking wheather identity is username or email
		if($this->config->item('identity', 'ion_auth') == 'username' )
		{
		   $this->form_validation->set_rules('email', '用户名', 'required');
		}
		else
		{
		   $this->form_validation->set_rules('email', '邮箱地址', 'required|valid_email');
		}


		if ($this->form_validation->run() == false)
		{
			//setup the input
			$this->data['email'] = array('name' => 'email',
				'id' => 'email',
			);

			if ( $this->config->item('identity', 'ion_auth') == 'username' ){
				$this->data['identity_label'] = '用户名';
			}
			else
			{
				$this->data['identity_label'] = '邮箱地址';
			}

			//set any errors and display the form
			$this->data['message'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('message');
			$this->_render_page('admin/forgot_password', $this->data);
		}
		else
		{
			// get identity from username or email
			if ( $this->config->item('identity', 'ion_auth') == 'username' ){
				$identity = $this->ion_auth->where('username', strtolower($this->input->post('email')))->users()->row();
			}
			else
			{
				$identity = $this->ion_auth->where('email', strtolower($this->input->post('email')))->users()->row();
			}
	            	if(empty($identity)) {

	            		if($this->config->item('identity', 'ion_auth') == 'username')
		            	{
                                   $this->ion_auth->set_message('forgot_password_username_not_found');
		            	}
		            	else
		            	{
		            	   $this->ion_auth->set_message('forgot_password_email_not_found');
		            	}

		                $this->session->set_flashdata('message', $this->ion_auth->messages());
                		redirect("admin/forgot_password", 'refresh');
            		}

			//run the forgotten password method to email an activation code to the user
			$forgotten = $this->ion_auth->forgotten_password($identity->{$this->config->item('identity', 'ion_auth')});

			if ($forgotten)
			{
				//if there were no errors
				$this->session->set_flashdata('message', $this->ion_auth->messages());
				//redirect("admin/login", 'refresh'); //we should display a confirmation page here instead of the login page
				redirect("admin/login"); //we should display a confirmation page here instead of the login page
			}
			else
			{
				$this->session->set_flashdata('message', $this->ion_auth->errors());
				redirect("admin/forgot_password", 'refresh');
			}
		}
	}

	//function _render_page($view, $data=null, $render=false)
	function _render_page($view, $data=null)
	{

		$this->viewdata = (empty($data)) ? $this->data: $data;

		$this->load->view($view, $this->viewdata);
        
	}
    
        public function register()
        {
            $username = strtolower('yaoshan.lin@chukong-inc.com');
            $email    = strtolower('yaoshan.lin@chukong-inc.com');
            $password = '12345678';

            $additional_data = array(
                'first_name' => '1',
                'last_name'  => '1',
                'company'    => '1',
                'phone'      => '1',
            );
            if($this->ion_auth->register($username, $password, $email, $additional_data))
            {
                echo 'register success';
            }  else {
                echo 'register fail!';
            }
        }
        
}
