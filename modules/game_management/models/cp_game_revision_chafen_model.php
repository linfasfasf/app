<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Cp_game_revision_chafen_model extends MY_Model{
    public $table = 'cp_game_revision_chafen';
    public $primary_key = 'id';
    
    public function __construct() {
        parent::__construct();
    }
    
    public function get_chafen_revision($revision_id) {
        $sql = "SELECT * FROM cp_game_revision_chafen WHERE revision_id = ?";
        $chafen_resources = $this->db->query($sql,$revision_id)->result_array();
        return $chafen_resources;
    }
}
