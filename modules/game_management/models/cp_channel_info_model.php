<?php
class Cp_channel_info_model extends MY_Model {

    public $table = 'cp_channel_info';
    public $primary_key = 'id';

    public function __construct() {
        parent::__construct();
        // 用来维护集成性
        $this->before_delete[] = 'delete_channel_games';
    }

    /**
     * 根据分页条件查找渠道信息，渠道游戏对应关系
     * @param int $s 起始条数
     * @param int $e 查询条数
     * @return 相应分页条件的渠道游戏信息
     */
    public function getlistdata($s, $e){
        //$sql = "select a.*,b.gamenum from (select g.channel_id,count(g.channel_id) as gamenum from cp_channel_info c,cp_chn_game_info g where c.channel_id=g.channel_id group by g.channel_id) b right join (select * from cp_channel_info) a on a.channel_id=b.channel_id where a.del_flag=0 order by a.create_time desc limit {$s},{$e}";
        //$sql = "select a.*,b.gamenum from (select g.channel_id,count(g.channel_id) as gamenum from cp_chn_game_info g where g.del_flag = 0 group by g.channel_id) b right join (select * from cp_channel_info) a on a.channel_id=b.channel_id where a.del_flag=0 order by a.create_time desc limit {$s},{$e}";
//        $sql = "select a.*,b.gamenum from (select g.channel_id,count(g.channel_id) as gamenum from cp_chn_game_info g, cp_game_info p where g.game_id=p.game_id and g.del_flag = 0 and p.del_flag = 0  and p.package_ver_code IS NOT NULL group by g.channel_id) b right join (select * from cp_channel_info) a on a.channel_id=b.channel_id where a.del_flag=0 order by a.create_time desc limit ?,?";
        $sql = "select a.*,b.gamenum from (select g.channel_id,count(g.channel_id) as gamenum from cp_chn_game_info g, cp_game_info p where g.game_id=p.game_id and g.del_flag = 0 and p.del_flag = 0 and not p.game_mode=4 group by g.channel_id) b right join (select * from cp_channel_info) a on a.channel_id=b.channel_id where a.del_flag=0 order by a.create_time desc limit ?,?";
        $query = $this->db->query($sql, array($s,$e));
        $info = $query->result_array();
        return $info;
    }
    public function get_visible_channels(){
        return NULL; 
    }

    public function delete_channel_games() {
        if ($this->result_mode == 'object') {
            $result = $this->db->get($this->table)->result();
        } else {
            $result = $this->db->get($this->table)->result_array();
        }
        $rowchanged = FALSE;
        foreach ($result as &$row) {
            $db = $this->load->database('', TRUE);
            $sql = "delete from cp_chn_game_info where channel_id=?";
            $rowchanged = $db->query($sql, array($row['channel_id']));
        }
        return $rowchanged; 
    }

}
