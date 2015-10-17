<?php
class Cp_game_revision_apk_so_model extends MY_Model {
    public $table = 'cp_game_revision_apk_so';
    public $primary_key = 'id';
    public function __construct() {
        parent::__construct();
    }

    public function update($revision_id,$channel_id,$arch_type,$download_url){
    	$sql = '
			INSERT INTO cp_game_revision_apk_so (arch_type,download_url,revision_id,channel_id) 
			VALUES (?,?,?,?) ON DUPLICATE KEY UPDATE download_url = ?
    	';
    	return $this->query($sql,array($arch_type,$download_url,$revision_id,$channel_id,$download_url))->result_array();
    }

    public function remove($revision_id,$channel_id){
        $sql = 'DELETE FROM cp_game_revision_apk_so WHERE revision_id = ? AND channel_id = ?';
        return $this->db->query($sql,array($revision_id,$channel_id));
    }
    public function get_so($revision_id,$channel_id){
        $sql = 'SELECT * FROM cp_game_revision_apk_so WHERE revision_id = ? AND channel_id = ?';
        return $this->db->query($sql,array($revision_id,$channel_id))->result_array();
    }
}
