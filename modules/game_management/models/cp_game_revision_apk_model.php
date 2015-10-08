<?php
/**
 * Cp_game_revision_apk_model
 *
 * @package Cp_game_revision_apk
 */

/**
 *
 */
class Cp_game_revision_apk_model extends MY_Model {

    public $table = 'cp_game_revision_apk';
    public $primary_key = 'id';

    public function __construct() {
        parent::__construct();
        $this->load->model('cp_channel_info_model');
    }

    /**
     * 增加 apk 跟 渠道关联
     */
    function add( $revision_id, $chn_id, $apk_download_url , $rev_chn_res_id,$update_on_duplicate = FALSE) {
        $apk_file_dir = dirname($apk_download_url);
        $apk_name = basename($apk_download_url);
        if($update_on_duplicate) {
            $data = array(
                 $revision_id,
                 $chn_id,
                 $apk_file_dir,
                 $apk_name,
                 $apk_download_url,
                 $rev_chn_res_id,
                 $apk_file_dir, // on duplicate key update
                 $apk_name,
                 $apk_download_url,
                 $rev_chn_res_id
            );
            $sql = "INSERT INTO cp_game_revision_apk (revision_id, channel_id, apk_file_dir, apk_name, apk_download_url,revision_channel_resources_id, active)
                values (?, ?, ?, ?, ?, ?, 0) ON DUPLICATE KEY UPDATE apk_file_dir=?, apk_name=?, apk_download_url = ? ,revision_channel_resources_id = ?";
                $result = @$this->db->query($sql,  $data);
                if( $this->db->_error_number()) {
                    return FALSE;
                }else{
                    $apk = $this->select('id')->where(array('revision_id'=> $revision_id, 'channel_id' => $chn_id))->get();
                    if($apk) {
                        return $apk['id'];
                    }else{
                        return FALSE;
                    }
                } 
        } else {
            $data = array(
                 'revision_id'=>$revision_id,
                 'channel_id'=>$chn_id,
                 'apk_file_dir'=>$apk_file_dir,
                 'apk_name'=>$apk_name,
                 'apk_download_url'=>$apk_download_url,
                 'revision_channel_resources_id'=>$rev_chn_res_id,
             );
             $result = @$this->insert($data);
            if( $this->db->_error_number()) {
                return FALSE;
            }else{
                return $result;
            } 
        }
        return TRUE; 
    }

    /**
     * 移除 apk 跟渠道关联
     */
    function remove($revision_id, $chn_id) {
        if (!($revision_id && $chn_id)) {
            // 非法 revision
            return FALSE; 
        }
        $sql = "DELETE FROM cp_game_revision_apk WHERE revision_id=? and channel_id = ? ";
        $data = array(
            $revision_id, 
            $chn_id,
        );
        $query = @$this->db->query($sql, $data);
        if( $this->db->_error_number()) {
            return FALSE;
        } 
        return TRUE;
    }

    /**
     * 获取用于展示的关联信息
     *
     * 通过 revision_id 找所有与该 revison 关联的 channel 的信息
     *
     * 指定 chn_id 时，只返回该 revision 与 channel 的映射信息
     */
    function get_mappings_extended($revision_id, $chn_id=NULL) {
        if($chn_id) {
            // chn.id get shadowed by revid
            $sql = "select 
                apk.so_apk_download_url,apk.genuine_apk_download_url,apk.apk_md5,apk.genuine_apk_md5,apk.so_apk_md5,
                res.game_desc,res.download_third_plugin,res.download_third_sdk,res.third_plugin_int_version,res.third_sdk_int_version, rev.id, rev.game_name, chn.channel_name, chn.id, chn.channel_id, 
                apk.active,apk.apk_download_url,res.apk_download_url as res_apk_download_url,res.icon_url,res.bg_picture,res.bg_music
                ,res.file_dir as res_file_dir, res.id as chnres_id, apk.id as revision_apk_id
                ,apk.channel_config_type
                ,apk.revision_channel_config_id
                ,apk.channel_config_encoded
                FROM cp_game_revision_info rev, cp_game_revision_apk apk , cp_channel_info chn ,cp_game_revision_channel_resources res
                WHERE rev.id=apk.revision_id and chn.channel_id=apk.channel_id and rev.id=? and apk.channel_id=? 
                and res.id = apk.revision_channel_resources_id
                order by apk.channel_id asc";
        } else {
            $sql = "select
                apk.so_apk_download_url,apk.genuine_apk_download_url,apk.apk_md5,apk.genuine_apk_md5,apk.so_apk_md5,
                res.game_desc,res.download_third_plugin,res.download_third_sdk,res.third_plugin_int_version ,res.third_sdk_int_version,rev.id, rev.game_name, chn.channel_name, chn.id, chn.channel_id,
                apk.active,apk.apk_download_url,res.apk_download_url as res_apk_download_url,res.icon_url,res.bg_picture,res.bg_music
                ,res.file_dir as res_file_dir, res.id as chnres_id, apk.id as revision_apk_id
                ,apk.channel_config_type
                ,apk.revision_channel_config_id
                ,apk.channel_config_encoded
                FROM cp_game_revision_info rev, cp_game_revision_apk apk , cp_channel_info chn,cp_game_revision_channel_resources res
                WHERE rev.id=apk.revision_id and chn.channel_id=apk.channel_id and rev.id=? 
                and res.id = apk.revision_channel_resources_id
                order by apk.channel_id asc; --  and apk.channel_id=?";
        }
        $query = $this->db->query($sql, array($revision_id, $chn_id));
        $result = $query->result_array();
        foreach ($result as $key => $value) {
            if(empty($value['apk_download_url']) && !empty($value['res_apk_download_url']))
            {
                $result[$key]['apk_download_url'] = $value['res_apk_download_url'];
            }
        }
        return $result;
    }

    /**
     * 建立一个空的渠道资源关联
     */
    function create_empty_slot($revision_id, $channel_id, $file_dir,$active = 0, $apk_download_url = '') { 
        if($active !== 0)
        {
            $active = 1;
        }
        if(!$revision_id || !$channel_id || !$file_dir) {
            return ;
        }
        $sql1 = "INSERT INTO cp_game_revision_channel_resources (revision_id, file_dir,icon_url,bg_picture,bg_music,apk_download_url) VALUES (?, ?,'','','',?)";
        $sql2 = "INSERT INTO cp_game_revision_apk (revision_id, channel_id, revision_channel_resources_id,apk_name,apk_file_dir,apk_download_url,create_time, active, channel_config_type) VALUES (?, ?, LAST_INSERT_ID(),'','',?,?, ?, -1)";
        $this->db->trans_start();
        $this->db->query($sql1, array($revision_id, $file_dir, $apk_download_url));
        $this->db->query($sql2, array($revision_id, $channel_id,$apk_download_url,date('Y-m-d H-i-s'),$active));
        $this->db->trans_complete();
        
        if ($this->db->trans_status() === FALSE)
        {
            die('db insert failed');
            return false;
        }
        return true;
    }

    /**
     * 从关联的 chnres 中同步资源信息
     */
    function sync_chnres($chnres_id,$apk_name = '') { 
        if(!$chnres_id) {
            return ;
        }
        if(!empty($apk_name))
        {
            $sql1 = "Update cp_game_revision_apk apk, cp_game_revision_channel_resources res 
            SET apk.apk_download_url = res.apk_download_url, apk.apk_file_dir = res.file_dir,apk.modify_time=?,
            apk.apk_name = ?
            WHERE apk.revision_channel_resources_id = res.id and res.id=?";
            $result = $this->db->query($sql1, array(date('Y-m-d H-i-s'),$apk_name,$chnres_id));
        }
        else
        {
            $sql1 = "Update cp_game_revision_apk apk, cp_game_revision_channel_resources res 
            SET apk.apk_download_url = res.apk_download_url, apk.apk_file_dir = res.file_dir,apk.modify_time=?
            WHERE apk.revision_channel_resources_id = res.id and res.id=?";
            $result = $this->db->query($sql1, array(date('Y-m-d H-i-s'),$chnres_id));
        }
        return $result ;
    }


    /**
     * 更新apk_download_url必须更新两个表的这个字段
     * @param  [type] $apk_id           [description]
     * @param  [type] $apk_download_url [description]
     * @return [type]                   [description]
     */
    function update_apk($apk_id,$apk_download_url){
        $sql1 = 'UPDATE cp_game_revision_apk SET apk_download_url = ? WHERE id = ?';
        $sql2 = 'UPDATE cp_game_revision_channel_resources 
                SET apk_download_url = ?
                WHERE id = (SELECT revision_channel_resources_id FROM cp_game_revision_apk WHERE id = ?)';
        $this->db->trans_start();
        $this->db->query($sql1, array($apk_download_url,$apk_id));
        $this->db->query($sql2, array($apk_download_url,$apk_id));
        $this->db->trans_complete();
        return $this->trans_status();
    }

    /**
     * 获取与 revision_apk 相关联的游戏名称及渠道名称
     */
    function get_apk_info_extended($apk_id) {
        $sql = "SELECT revision.game_name, channel.channel_name 
            ,apk.revision_id, apk.channel_id
            ,apk.id as apk_id
            ,revision.game_id
            FROM cp_game_revision_info revision 
            INNER JOIN cp_game_revision_apk apk 
            ON apk.revision_id = revision.id 
            INNER JOIN cp_channel_info channel 
            ON channel.channel_id = apk.channel_id
            WHERE apk.id = ?";
        $result = $this->db->query($sql, array($apk_id));
        $records =  $result->result_array(); 
        if($records) {
            return $records[0];
        }else{
            return FALSE; 
        }
    }

    /**
     * config_type 默认为 -1
     * 编辑后值为 0 或 1
     * 这个方法将 config_type 的值在 0, 1之间切换
     */
    function toggle_config_type($apk_id,$config_type = NULL) {
        $config_type = $config_type+0;
        if(!($config_type ===1 || $config_type ===0 || $config_type === NULL)){
            echo 's';
            return FALSE;
        }
        $new_channel_config_type = $config_type;
        if(is_null($new_channel_config_type)){
            $sql = "SELECT channel_config_type from cp_game_revision_apk WHERE id = ?";
            $result = $this->db->query($sql, array($apk_id));
            $records = $result->result_array();
            if($records){
                $record =  $records[0];
                $channel_config_type = $record['channel_config_type'] + 0;
                $new_channel_config_type = ($channel_config_type + 1) % 2;     
            }else{
                return FALSE;
            }
        }
        $update_sql = "update cp_game_revision_apk set channel_config_type = ? where id =? ";
        $result = $this->db->query($update_sql, array($new_channel_config_type, $apk_id));
        return $result;
    }

    /**
     * 渠道资源上线
     */
    function activate_apk($apk_id) {
        $ok = $this->update($apk_id, array('active'=> 1));

        /**
         * 在渠道中自动添加游戏
         */
        // TODO
        $sql = "SELECT game.game_id, apk.revision_id, apk.channel_id, apk.active FROM cp_game_revision_apk apk INNER JOIN cp_game_revision_info revision 
            ON revision.id = apk.revision_id INNER JOIN cp_game_info game ON game.game_id = revision.game_id WHERE apk.id = ?";
        $query = $this->db->query($sql, $apk_id);
        $result = $query -> result_array();
        if($result) {
            $info = $result[0];
            if($info['active'] != 1) {
                return FALSE;
            }
            $time = time();
            $sql = "INSERT INTO cp_chn_game_info (channel_id, game_id, is_visiable, coop_method, test_duration, create_time, modify_time) VALUES
                (?, ?, 0, 'cpa', -1, ?, ?) ON DUPLICATE KEY UPDATE coop_method = coop_method ";
            $query = $this->db->query($sql , array($info['channel_id'], $info['game_id'], $time, $time));
            if($query) {
                return TRUE;
            }else{
                return FALSE;
            }
        }else{
            return FALSE;
        }
    }

    /**
     * 渠道资源下线
     */
    function deactivate_apk($apk_id) {
        $this->update($apk_id, array('active'=> 0));
        return TRUE;
    }
}
// EOF
