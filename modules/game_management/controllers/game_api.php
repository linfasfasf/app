<?php 
/**
 * 实现新的 版本管理 功能
 *
 * @package experiment
 */

/**
 * Game_api class
 */

// TODO: 加密访问

class Game_api extends Public_Controller
{
    public function __construct() {
        parent::__construct();
        $this->upload_path = $this->get_full_path('');
        $this->load->helper('form');
        $this->load->library('smarty');
        $this->load->library('form_validation');
        $this->load->library('cocos_packingtool/uploadzip_library');
        $this->load->model('game_management/cp_channel_info_model');
        $this->load->model('game_management/cp_game_revision_info_model');
        $this->load->model('game_management/cp_game_revision_apk_model');
        $this->load->model('game_management/cp_game_revision_channel_resources_model');
        $this->load->model('game_management/cp_game_revision_chafen_model');
        $this->load->library('game_sync');
        $this->generic_path = $this->concat_path('uploads', 'generic');
        header('Access-Control-Allow-Origin: *'); // TODO: 移除这个改成 jsonp

        /* doesn't work
        if(!$this->ip_limit($_SERVER['REMOTE_ADDR'])) {
            $result = array('res'=>'notok', 'data'=>'not authorized: ['.$_SERVER['REMOTE_ADDR'].']');
            $msg = json_encode($result);
            die($msg);
        }
         */
    }

    /**
     * 接收准线上的同步请求
     * 做出验证并给出回应
     *
     * @return 
     */
    public function ajax_sync_prepare() {
        // 需要加签名验证
        $data = $this->input->post('data');
        header('Content-Type: application/json');
        $default_result = array(
            'res' => 'notok', 
            'data' => 'nodata'
        );
        if($data) { 
            $gamekey = $data['game_key'];
            $channel_id = $data['channel_id'];
            $hotversioncode = $data['hot_versioncode'];
            //$result = $this->game_sync->verification_live_server($data);
            $result  = $this->game_sync
                ->export_live_server($gamekey, $hotversioncode, $channel_id);
            if($result) {
                echo json_encode(array('res'=>'ok', 
                    'data' =>
                    array(
                        'dest' => $result,
                        'source' => $data
                    )));
            }else{
                echo json_encode($default_result);
            }
        }else{
            echo json_encode($default_result);
        }
    }

    /**
     * 准线上导出游戏信息
     */
    public function ajax_sync_export() {
        $revision_id = $this->input->post('revision_id');
        $channel_id = $this->input->post('channel_id');
        $result = array();
        if($revision_id && $channel_id) {  
            $info = $this->game_sync->export($revision_id, $channel_id, TRUE);
            if($info) {
                $chafen = $this->game_sync->export_chafen($revision_id);
                if(!$chafen) {
                    $chafen = array();
                }
                $result = array('res'=>'ok', 'data'=>$info, 'chafen' => $chafen);
            }else{
                $result = array('res'=>'notok', 'data'=>'nosuchinfo', 'chafen' => 'nosuchinfo');
            }
        }else{
            $result = array('res'=>'notok', 'data'=>'invalid');
        }
        header('Content-Type: application/json');
        echo json_encode($result);
    }

    public function ajax_game_diff() {
        $local = $this->input->post('local');
        $remote = $this->input->post('remote');
        $option = $this->input->post('option');
        $result = array();
        if($local && $remote && $option) {  
            $info = $this->game_sync->diff($local, $remote, $option);
            if($info === FALSE){ 
                $result = array('res'=>'notok', 'data'=>'nosuchinfo');
            }else{
                if(empty($info)) { 
                    $result = array('res'=>'nodiff', 'data'=>'');
                }else{
                    $result = array('res'=>'ok', 'data'=>$info);
                }
            }
        }else{
            $result = array('res'=>'notok', 'data'=>'invalid');
        }
        header('Content-Type: application/json');
        echo json_encode($result);
    }

    /**
     * 线上服务器接收准线上服务器的数据更新请求
     *
     * @return result
     */
    public function ajax_apply() {
          $source = $this->input->post('source');
          $dest = $this->input->post('dest');
          $strategy = $this->input->post('strategy');
          $manifest_url = $this->input->post('manifest'); // 准线上的 manifest 的地址， 用来更新 cpk 表
          $chafen = $this->input->post('chafen'); // 差分资源
          $options = $this->input->post('option'); 
          $result = 'notok';
          if(!$manifest_url) { 
              $result = 'no manifest';
          }elseif(!$source) {
              $result = 'no source data';
          }elseif(!$dest) {
              $result = 'no target data';
          }elseif(!$strategy) {
              $result = 'no strategy';
          }else {
              // TODO: added strategy to control the merging process
              $merged = $this->game_sync->merge($source, $dest, $strategy);
              if($merged) {
                  if($chafen) {
                     list($ok, $options) =  $this->game_sync->apply($merged, $manifest_url, $strategy, $chafen, $options);
                  }else{
                     list($ok, $options) =  $this->game_sync->apply($merged, $manifest_url, $strategy);
                  }
                 if($ok) {
                      $result = 'ok';
                 }
              }
          }
          echo $result;
    }

    /**
     * 将同步资源放入队列
     */
    public function ajax_sync_res() {
          $source = $this->input->post('source');
          $dest = $this->input->post('dest');
          $strategy = $this->input->post('strategy');
          $option = $this->input->post('option');
          // NOTE: 不需要另外同步差分资源， 差分资源一定在 cpk 目录中。
          // 选了差分一定要选通用资源
          $chafens = $this->input->post('chafen');
          if($source && $option) {
              $merged = $this->game_sync->batch_put($source, $option);
              echo 'ok';
          }else{
              echo 'notok';
          }
    }

    protected function ip_limit($ip) {
        /*
        $this->config->load("ip_limit",true);
        $arr_conf = $this->config->item("ip_limit");
        $ip_list = $arr_conf["ip_list"];
        $ip_sec_list = $arr_conf["ip_sec_list"];
         */
        $ip_list = array(
            '192.168.52.72',
            '192.168.52.80',
            '10.10.12.164',
            '10.10.12.140',
            '211.151.20.112',
            '127.0.0.1',
        );

        if(in_array($ip,$ip_list)) {
            return TRUE;
        }

        return FALSE;
    }
}
// EOF
