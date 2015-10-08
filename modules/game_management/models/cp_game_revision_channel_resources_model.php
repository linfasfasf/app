<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Cp_game_revision_channel_resources_model extends MY_Model{
    public $table = 'cp_game_revision_channel_resources';
    public $apk_table = 'cp_game_revision_apk';
    public $primary_key = 'id';
    
    public function __construct() {
        parent::__construct();
    }

    /**
     * 没使用的 chn 资源
     */
    public function get_orphan_resources($revision_id) {
        if(!$revision_id)  {
            return;
        }
        $sql = 'select chn.* from ' . $this->table . ' as chn left join ' . $this->apk_table . ' as apk on chn.id=apk.revision_channel_resources_id where apk.channel_id is Null and chn.revision_id=?';
        $result = $this->db->query($sql, $revision_id)->result_array();
        return $result; 
    }

    /**
     * 删除没使用的 chn 资源
     */
    public function delete_orphan_resource($chnres_id){
        $sql = 'delete from ' . $this->table . ' where id = ? and id not in (select revision_channel_resources_id id from ' . $this->apk_table . ')';
        $result = $this->db->query($sql, array($chnres_id));
        return $result; 
    }
}
