<?php
class Cp_game_revision_channel_config_model extends MY_Model {
    public $table = 'cp_game_revision_channel_config';
    public $primary_key = 'id';
    
    public function __construct() {
        parent::__construct();
    }

    public function add($apk_id, $url, $ver) {
        $sql = "insert into `" . $this->table . "` (ver, url, apk_id, active) values (?,?,?,0) ON DUPLICATE KEY UPDATE url = ?";
        $result = $this->db->query($sql, array($ver,$url, $apk_id, $url));
        return $result;
    }

    public function delete($id) {
        $sql = "delete from " . $this->table . " where id = ? ";
        $result = $this->db->query($sql, array($id));
        return $result;
    }

    public function max_ver($apk_id) {
        $sql = "select c.id,c.url,c.apk_id,c.ver from (select max(ver) as ver from cp_game_revision_channel_config where apk_id = ? ) as m , cp_game_revision_channel_config c where c.ver=m.ver and c.apk_id=? limit 1";
        $query = $this->db->query($sql, array($apk_id, $apk_id));
        $result = $query->result_array();
        if($result) {
            return $result[0];
        }else{
            return FALSE; 
        }
    }
}
