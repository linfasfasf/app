<?php
class Cp_runtime_core_patch_model extends MY_Model {

    public $table = 'cp_runtime_core_patch';
    public $primary_key = 'id';

    public function __construct() {
        parent::__construct();
    }

    public function get_extended($revision_id) {
        $sql = "SELECT `patch`.*, rtcore.id as rtc_id, `rtcore`.`version` as runtime_core_version
            FROM `cp_runtime_core_patch` as patch 
            RIGHT JOIN `cp_runtime_core` as rtcore 
            ON `rtcore`.`id` = `patch`.`runtime_core_id` 
            WHERE `patch`.`revision_id` = ? LIMIT 20";
        $query = $this->db->query($sql, $revision_id);
        $records = $query->result_array();
        $sql2 = "SELECT `patch`.*, rtcore.id as rtc_id, `rtcore`.`version` as runtime_core_version
            FROM `cp_runtime_core` as rtcore 
            LEFT JOIN `cp_runtime_core_patch` as patch 
            ON `rtcore`.`id` = `patch`.`runtime_core_id` 
            WHERE rtcore.id not in (
                    SELECT rtcore.id
                    FROM `cp_runtime_core_patch` as patch 
                    RIGHT JOIN `cp_runtime_core` as rtcore 
                    ON `rtcore`.`id` = `patch`.`runtime_core_id` 
                    WHERE `patch`.`revision_id` = ?)";
        $query = $this->db->query($sql2, $revision_id);
        $records2 = $query->result_array();
        $records = array_merge($records, $records2);
        return $records;
    }

    public function add_or_update($revision_id, $runtime_core_id, $data=array()) {
        if(!empty($data) && is_array($data)) {
            $fields = implode(',' , array_keys($data)) ;
            $values = implode(',' , array_map(function ($v) { return '?';}, array_values($data))); // '?'

            $updates = implode(', ', array_map(function ($v, $k) { return $k . '=?'; }, $data, array_keys($data)));
            // patch_ver 为自增 字段， 每次更新加一
            $updates .=',patch_version=patch_version+1';
            $sql = " INSERT INTO cp_runtime_core_patch (revision_id, runtime_core_id,$fields, patch_version) VALUES (?,?, $values, 1) ON DUPLICATE KEY UPDATE $updates";
            $db = $this->load->database("",true);
            $data_values = array_values($data);
            $qparams = array_merge(array($revision_id, $runtime_core_id), $data_values);
            $qparams = array_merge($qparams, $data_values);
            $query = $db->query($sql, $qparams);
            //echo $db->last_query();
            return $query;
        }
        return FALSE;
    }

    public function get_rtcore_patch_info($gamekey, $ver, $rtcore_ver) {
       if($gamekey && $ver && $rtcore_ver) {
           $sql = " SELECT 
                    rtcore.version, patch.*
                    FROM cp_game_info  game
                    INNER JOIN cp_game_revision_info revision
                    ON game.game_id = revision.game_id
                    INNER JOIN cp_runtime_core_patch patch 
                    ON patch.revision_id = revision.id
                    INNER JOIN cp_runtime_core rtcore
                    ON rtcore.id = patch.runtime_core_id
                    WHERE 
                    game.game_key = ?
                    AND  game.game_mode=7 
                    AND revision.is_published = 1
                    AND revision.hot_versioncode = ?
                    AND rtcore.version =?";

            $db = $this->load->database("",true);
            $query = $db->query($sql, array($gamekey, $ver, $rtcore_ver));
            $result = $query -> result_array();
            return $result; 
       }else{
           return FALSE; 
       }
    } 
}
//EOF
