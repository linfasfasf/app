<?php
class Cp_real_core_map_model extends MY_Model {

    public $table = 'cp_real_core_map';
    public $primary_key = 'id';

    public function __construct() {
        parent::__construct();
    }
    
    public function get_all_comp(){
        $sql = 'SELECT runtime.version , runtime.engine, runtime.engine_version , map.real_sdk_ver,map.id as id
            FROM cp_real_core_map map
            INNER JOIN cp_runtime_core runtime on map.runtime_id = runtime.id
            ORDER BY runtime.engine_version ASC,runtime.version DESC,map.real_sdk_ver DESC';
        return $this->db->query($sql,array())->result_array();
    }
}
