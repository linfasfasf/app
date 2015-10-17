<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class White_list extends Admin_Controller{
    public function __construct() {
        parent::__construct();
        $this->load->library('smarty');
        $this->load->model('game_management/cp_whitelist_info_model');
        $this->access();
    }
    
    private function access() {
        $accesslevel = array(
            '/game_management/white_list/action_list' => 'create',
            '/game_management/white_list/white_list_view' => 'create',
        );
        foreach($accesslevel as $aco => $al) {
            $GLOBALS['ACCESSLEVEL'][$aco] = $al ;
        }
    }
    
    public function white_list_view() {
        $package_list = $this->cp_whitelist_info_model->get_packagename_list();
        $content = $this->load->view('white_list',array('package_list'=>$package_list),TRUE);
        return $this->smarty->view('general.tpl', array('content'=>$content));
    }
    
    public function action_list() {
        $action = $this->input->get_post('action',TRUE);
        $packagename = $this->input->get_post('packagename',TRUE);
        if(!$action)
        {
            redirect('game_management/white_list/white_list_view');
        }
        switch ($action) {
            case 'add':
                if (preg_match("/[\x7f-\xff]/", $packagename) || empty($packagename))
                {
                    $this->session->set_flashdata('flash_message','非法包名，无法添加');
                    break;
                }
                if($this->cp_whitelist_info_model->is_present_packagename($packagename))
                {
                    $this->session->set_flashdata('flash_message', $packagename.'已存在无需添加！');
                }
                else
                {
                    $this->cp_whitelist_info_model->insert(array('packagename'=>$packagename));
                    $this->session->set_flashdata('flash_message', $packagename.'添加到白名单！');
                }
                break;
            case 'delete':
                $package_id = $this->input->get_post('package_id',TRUE);
                $this->cp_whitelist_info_model->delete($package_id);
                $this->session->set_flashdata('flash_message', $packagename.'移出白名单！');
                break;
            default:
                break;
        }
        redirect('game_management/white_list/white_list_view');
    }
}
