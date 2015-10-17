<?php
/**
 * Cp_game_revision_info_model
 *
 * @package Cp_game_revision_info
 */

/**
 *
 */
class Cp_game_revision_info_model extends MY_Model {

    public $table = 'cp_game_revision_info';
    public $primary_key = 'id';

    public function __construct() {
        parent::__construct();
    }

    /**
     * 根据设置版本id，设置状态为发布
     * @param int $revision_id 版本id
     * @return boolean 成功设置返回TRUE,失败返回FALSE
     */
    public function set_published($revision_id){
        if (empty($revision_id)){
            return FALSE;
        }
        return $this->set_published_to($revision_id, 1);
    }

    /**
     * 设置cp_revision_info的版本信息的发布状态is_published
     * 0表示未发布，1表示发布并确保不同的同样版本号的不同版本只有一个是 published 的状态。
     * @param int $revision_id 版本id
     * @param int $status  要设置的状态
     * @return boolean 成功设置返回TRUE，失败返回FALSE
     */
    public function set_published_to($revision_id, $status){
        if (empty($revision_id)){
            return FALSE;
        }
        if($status!=1 && $status !=0){
            return FALSE;
        }
        if($status == 1){
            // 确保不同的同样版本号的不同版本只有一个是 published 的状态
            $game_info = $this->select(array('game_id','hot_versioncode'))->where(array('id'=>$revision_id))->get();
            if($game_info){
                $game_id = $game_info['game_id'];
                $sql = 'update cp_game_revision_info set is_published=0 where hot_versioncode=? and game_id=?';
                $this->db->query($sql, array($game_info['hot_versioncode'], $game_id));
            }
        }
        $sql = "update cp_game_revision_info set is_published=? where id=?"; // HACK WARNING: 将  del_flag 做为 is_published 的状态位, 这样做是为了减少对数据库的改动
        $result = $this->db->query($sql, array($status, $revision_id));
        if(!empty($result)){
            return TRUE;  // 操作成功
        }
        return FALSE;
    }

    /**
     * 获取某个游戏版本的发布状态，0为未发布，1为已发布
     * @param int $revision_id 版本id
     * @return int 0表示未发布，1表示已发布
     */
    public function is_published($revision_id){
        if (empty($revision_id)){
            return 0;
        }
        $sql = "select is_published from cp_game_revision_info where id=?"; // HACK WARNING: 将  del_flag 做为 is_published 的状态位, 这样做是为了减少对数据库的改动
        $query = $this->db->query($sql, array($revision_id));
        $result = $query -> result_array();
        if(!empty($result)){
            return $result[0]['is_published'];
        }
        return 0;
    }

    /**
     * 获取游戏的版本列表
     * 当前只在 viewgame 中使用
     * @param int $gameid 游戏id
     * @param boolean $only_published  可选项，是否只选取已发布的版本，默认为FALSE
     * @return type 成功返回游戏版本列表，失败返回NULL
     */
    public function get_game_revision_list($gameid, $only_published=FALSE)
    {
        if (empty($gameid)){
            return NULL;
        }
        $sql = "select id, package_ver_code,genuine_versioncode,hot_versioncode,apk_download_url, apk_name, appid, bg_music, bg_picture, chafen_url, coop_method, create_time, is_published as is_published, engine_version, file_dir, cpk_file_dir, game_desc, game_id, game_name, icon_url, is_maintain, language, maintain_tip, manifest_url, mdf_url, modify_time, opt_id, orientation, package_name, package_size, package_up_time, package_ver, package_ver_code, payment, sdk_version, star, user_system, ver_last,manifest_version from cp_game_revision_info where game_id = ?"; 
        if($only_published) {
            $sql .= ' and is_published=1';
        }
        $query = $this->db->query($sql, $gameid);
        $info = $query->result_array();
        return $info;
    }
    /**
     * 根据game_id获取该游戏下的所有版本的版本号
     * @param int $gameid 游戏id
     * @return array 版本号数组
     */
    public function get_package_version_codes($gameid){
        $result=array();
        $info = $this->get_package_version_name_code($gameid);
        foreach($info as $key=>$val){
            $result[] = $val;
        };
        return $result;
    }
    
    /**
     * 根据game_id获取该游戏下的所有版本的版本名和版本号
     * @param int $gameid 游戏id
     * @return array 版本名，版本号数组
     */
    public function get_package_version_name_code($gameid)
    {
        if (empty($gameid)){
            return NULL;
        }
        $sql = "select package_ver, package_ver_code from cp_game_revision_info where game_id = ?"; 
        $query = $this->db->query($sql, array($gameid));
        $info = $query->result_array();
        return $info;
    }

    /**
     * 返回详细的 游戏与相应版本的信息
     * @param int $game_id 游戏id
     * @param int $package_ver_code 游戏的版本编号
     * @param int $is_published  如果为真，则只返回已发布的 revision
     * @return type 返回游戏信息， 如果没有，则返回 NULL
     */
    public function get_published_revision_detail_vercode($game_id, $package_ver_code)
    {
        if (empty($game_id) || empty($package_ver_code)){
            return NULL;
        }
        $sql = 'select r.id from cp_game_info g, cp_game_revision_info r where g.game_id = r.game_id and r.package_ver_code = g.package_ver_code and r.game_id=? and r.package_ver_code=?  and r.is_published=1 order by r.id desc limit 1';
    	$query = $this->db->query($sql, array($game_id, $package_ver_code));
		$info = $query->result_array();
        if(count($info)>0){
            $revision_info =  $this->get_game_revision_detail($info[0]['id']);
            return $revision_info; 
        }
		return NULL;
    }

    /**
     * 获取一个游戏版本的详细信息
     * @param int $revision_id 版本id
     * @param int $is_published 可选项，是否只选取发布的版本,默认为TRUE
     * @return type 成功返回详细版本信息，失败返回NULL
     */
    public function get_game_revision_detail($revision_id,$is_published=TRUE)
    {
        if (empty($revision_id)){
            return NULL;
        }
        $sql = "select compatible_arch,engine,hot_versioncode,genuine_versioncode,genuine_versionname,id, apk_download_url, apk_name, appid, bg_music, bg_picture, chafen_url, coop_method, create_time, 
            is_published, engine_version, file_dir, cpk_file_dir, game_desc, game_id, game_name, icon_url, is_maintain, 
            language, maintain_tip, manifest_url, mdf_url, modify_time, opt_id, orientation, package_name, 
            package_size, package_up_time, package_ver, package_ver_code, payment, sdk_version, star, user_system, 
            ver_last, manifest_version, test_duration, manifest_json_url, manifest_json_md5, tool_version from cp_game_revision_info where  id=?";
        if($is_published){
            $sql .= ' and is_published=1';
        }
    	$query = $this->db->query($sql, $revision_id);
		$info = $query->result_array();
        if(count($info)>0){
            return $info[0];
        }
		return NULL;
    }
    /**
     * 根据版本id获取所属的游戏的游戏id
     * @param int $revision_id  版本id
     * @return type 成功返回游戏id，失败返回NULL
     */
    public function get_game_id($revision_id)
    {
        if (empty($revision_id)){
            return NULL;
        }
        $sql = "select id,game_id from cp_game_revision_info where  id=?";
    	$query = $this->db->query($sql, $revision_id);
		$info = $query->result_array();
		return $info[0]['game_id'];
    }
    
    /**
     * 获取版本信息模板
     * @return array 
     */
    public function get_revision_template()
    {
        $template = array( 'apk_download_url' => '',
            'apk_name' => '',
            'appid' => '',
            'bg_music' => '',
            'bg_picture' => '',
            'chafen_url' => '',
            'coop_method' => '',
            'is_published' => 0,
            'engine_version' => '',
            'file_dir' => '',
            'cpk_file_dir' => '',
            'game_desc' => NULL,
            'game_id' => '',
            'game_name' => '',
            'icon_url' => '',
            'is_maintain' => 0,
            'language' => '',
            'maintain_tip' => '',
            'manifest_url' => '',
            'mdf_url' => '',
            'create_time' => time(),
            'modify_time' => time(),
            'opt_id' => 0,
            'orientation' => '',
            'package_name' => '',
            'package_size' => '',
            'package_up_time' => 0,
            'package_ver' => '',
            'package_ver_code' => '',
            'payment' => '',
            'sdk_version' => '',
            'star' => 5,
            'user_system' => '',
            'manifest_version' => 0,
            'ver_last' => 0,
            'test_duration'=>'0',
            'hot_versioncode' => '',
            'genuine_versioncode' => NULL,
            'genuine_versionname' => '',
            'compatible_arch' => '',
            'engine' => '',
            'tool_version' => 1,
            );
        return $template; 
    }

    /**
     * 删除 revision_info
     * @param int game_id 要删除的 游戏 id
     * @return Bool 真表示成功, 注意没有  revision 也应该返回 TRUE
     */
    public function delete_revisions_of_game($game_id, $test=TRUE){
        //$this->db->trans_start(TRUE); // TRUE 表示测试
        $revision_list = $this->get_game_revision_list($game_id);
        if($revision_list === NULL){
            return TRUE;
        }
        if($test){
            $this->db->trans_start(TRUE);
        }else{
            $this->db->trans_start();
        }
        $sql = 'delete from cp_game_revision_resources where revision_id=?'; // game_id stands from revision_id in game_revision_resources
        $sql2 = 'delete from cp_game_revision_info where id=?'; // game_id stands from revision_id in game_revision_resources
        foreach($revision_list as $revision) {
            //echo $sql . " ".  $revision['id'];
            $this->db->query($sql, array($revision['id']));
            $this->db->query($sql2, array($revision['id']));
        }
        $this->db->trans_complete();
        if ($this->db->trans_status() === FALSE)
        {
            // generate an error... or use the log_message() function to log your error
            return FALSE;
        }
        return TRUE;
    }

    /**
     * 根据游戏id获取游戏版本信息详细信息
     * @param int $game_id 游戏id
     * @param int $package_ver_code 可选项，选取指定版本号的版本id，不填表示选取线上版本
     * @param boolean $only_published 可选项TRUE表示只选取发布版本，FLASE表示选取全部版本
     * @return type 返回相应的版本的详细信息
     */
    public function get_revisions_detail_gameid($game_id, $hot_versioncode='', $only_published=TRUE){
        if(!$game_id) return array();
        if($hot_versioncode){
            $sql = 'select rev.id from cp_game_revision_info rev, cp_game_info gm where gm.game_id=? and rev.hot_versioncode=? and gm.game_id = rev.game_id';
            $query = $this->db->query($sql, array($game_id, $hot_versioncode));
        }else{
            $sql = 'select rev.id from cp_game_revision_info rev, cp_game_info gm where gm.game_id=? and gm.game_id = rev.game_id';
            $query = $this->db->query($sql, array($game_id));
        }
        $revision_ids = $query->result_array();
        $result = array();
        foreach($revision_ids as  $revision_id){
            $tmpresult = $this->get_game_revision_detail($revision_id['id'], $only_published);
            if($tmpresult)
                $result[] = $tmpresult;
        }
        return $result; 
    }

    /**
     * 根据game_key获取游戏版本信息详细信息
     * @param int $game_id 游戏id
     * @param int $package_ver_code 可选项，选取指定版本号的版本id，不填表示选取线上版本
     * @param boolean $only_published 可选项TRUE表示只选取发布版本，FLASE表示选取全部版本
     * @return type 返回相应的版本的详细信息
     */
    public function get_revisions_detail_gamekey($game_key, $hot_versioncode='', $only_published=TRUE){
        if(!$game_key) return array();
        if($hot_versioncode){
            $sql = 'select rev.id from cp_game_revision_info rev, cp_game_info gm where gm.game_key=? and rev.hot_versioncode=? and gm.game_id = rev.game_id';
            $query = $this->db->query($sql, array($game_key, $hot_versioncode));
        }else{
            $sql = 'select rev.id from cp_game_revision_info rev, cp_game_info gm where gm.game_key=? and gm.game_id = rev.game_id';
            $query = $this->db->query($sql, array($game_key));
        }
        $revision_ids = $query->result_array();
        $result = array();
        foreach($revision_ids as  $revision_id){
            $tmpresult = $this->get_game_revision_detail($revision_id['id'], $only_published);
            if($tmpresult)
                $result[] = $tmpresult;
        }
        return $result; 
    }

    /**
     * 根据游戏名获取线上版本的详细信息
     * @param string $game_name 游戏名
     * @param boolean $only_published 可选项TRUE表示只选取发布版本，FLASE表示选取全部版本
     * @return type 版本详细信息
     */
    public function get_revisions_detail_gamename($game_name, $only_published=TRUE){
        if($game_name=='') return array();
        $sql = 'select rev.id from cp_game_revision_info rev, cp_game_info gm where (rev.game_name like ? or gm.game_name like ?)  and gm.game_id = rev.game_id';
        $query = $this->db->query($sql, array($game_name, $game_name));
        $revision_ids = $query->result_array();
        $result = array();
        foreach($revision_ids as  $revision_id){
            $tmpresult = $this->get_game_revision_detail($revision_id['id'], $only_published);
            if($tmpresult)
                $result[] = $tmpresult;
        }
        return $result; 
    }
    
    /**
     * 根据filedir获取版本详细信息
     * @param string $filedir 文件名 
     * @param boolean $only_published 可选项TRUE表示只选取发布版本，FLASE表示选取全部版本
     * @return type 版本详细信息
     */
    public function get_revisions_detail_filedir($filedir, $only_published){
        if($filedir=='') return array();
        $sql = 'select id from cp_game_revision_info where file_dir=? or cpk_file_dir=?';
        $query = $this->db->query($sql, array($filedir, $filedir));
        $revision_ids = $query->result_array();
        $result = array();
        foreach($revision_ids as  $revision_id){
            $tmpresult = $this->get_game_revision_detail($revision_id['id'], $only_published);
            if($tmpresult)
                $result[] = $tmpresult;
        }
        return $result; 
    }
    
    /**
     * 根据文件夹名获取该文件夹下的游戏id列表
     * @param string $filedir 文件夹名
     * @return array game_id数组
     */
    public function get_gamelist_filedir($filedir){
        if($filedir=='') return array();
        $sql = 'select game_id,game_name from cp_game_revision_info where file_dir=? or cpk_file_dir=? group by game_id';
        $query = $this->db->query($sql, array($filedir, $filedir));
        $game_list = $query->result_array();
        return $game_list;
    }
    
    /**
     * 根据游戏名获取相同文件夹下的游戏id列表
     * @param string $game_name 游戏名
     * @return array game_id列表
     */
    public function get_gamelist_gamename($game_name){
        if($game_name=='') return array();
        $helper = function ($arr){ return $arr['file_dir'] ;};
        $sql = 'select distinct rev.file_dir from cp_game_revision_info rev, cp_game_info gm where (rev.game_name like ? or gm.game_name like ?)  and gm.game_id = rev.game_id and not rev.file_dir=\'\'';
        $game_name = '%'. $game_name . '%';
        $query = $this->db->query($sql, array($game_name, $game_name));
        $game_dirs = array_map($helper, $query->result_array());
        $sql2 = 'select distinct rev.cpk_file_dir as file_dir from cp_game_revision_info rev, cp_game_info gm where (rev.game_name like ? or gm.game_name like ?)  and gm.game_id = rev.game_id and not rev.file_dir=\'\'';
        $query = $this->db->query($sql, array($game_name, $game_name));
        $game_dirs2 = array_map( $helper, $query->result_array());
        $final_dirs = array_unique($game_dirs + $game_dirs2);
        $game_list = array();
        foreach($final_dirs as $folder) {
            $game_list[$folder] = array();
            $games = $this->get_gamelist_filedir($folder);
            if(!empty($games)){
                foreach($games as $game){
                    array_push($game_list[$folder], $game);
                }
            }
        }
        return $game_list; 
    }

    /**
     * 取 revision 的相应的 game_info 
     */
    public function get_game_detail($revision_id)
    {
        if (empty($revision_id)){
            return FALSE;
        }
        $sql = "SELECT game.game_key, game.game_id, game.game_name, game.package_name, game.package_ver,
                game.package_ver_code, game.game_mode, game.game_type, game.cp_vendor, game.channel_id 
                from cp_game_info game 
                INNER JOIN cp_game_revision_info revision ON game.game_id = revision.game_id
                WHERE revision.id = ? LIMIT 1";
        $query = $this->db->query($sql, $revision_id);
        $info = $query->result_array();
        if (empty($info)) {
            return FALSE;
        } else {
            return $info[0];
        }
    }

    /**
     * 删除 revision 及其所有关联的资源
     */
    public function delete_revision($revision_id) {
        if(!$revision_id) return FALSE; 
        $sql = "DELETE cp_game_revision_info, cp_game_revision_apk, cp_game_revision_resources
            FROM cp_game_revision_info
            LEFT JOIN cp_game_revision_apk ON cp_game_revision_info.id  = cp_game_revision_apk.revision_id
            LEFT JOIN cp_game_revision_resources ON cp_game_revision_info.id  = cp_game_revision_resources.revision_id
            WHERE cp_game_revision_info.id = ? ";
        $query = $this->db->query($sql, $revision_id);
        if(empty($query)) return FALSE; 
        return TRUE; 
    }
    
    /**
     * 返回相同$revision_id相同大版本号的各个版本的id
     * @param type $revision_id
     * @return array
     */
    public function get_revision_id_list_same_ver($revision_id) {
        $sql = 'SELECT rev.id FROM cp_game_revision_info rev 
                INNER JOIN cp_game_revision_info rev2 
                ON rev.package_ver_code = rev2.package_ver_code
                AND rev.game_id = rev2.game_id
                WHERE rev2.id = ?';
        $result = $this->db->query($sql,array($revision_id))->result_array();
        if(empty($result))
        {
            return array();
        }
        else{
            $rev_id_list = array();
            foreach ($result as $value) {
                array_push($rev_id_list, $value['id']);
            }
            return $rev_id_list;
        }
    }
    
    public function get_chnresid_list_same_ver($chnresid,$chn_id){
        $sql = 'SELECT res.id FROM cp_game_revision_channel_resources res
                INNER JOIN cp_game_revision_apk apk
                ON apk.revision_channel_resources_id = res.id
                WHERE apk.channel_id = ?
                AND res.revision_id IN 
                (
                    SELECT rev.id FROM cp_game_revision_info rev 
                    INNER JOIN cp_game_revision_info rev2 
                    ON rev.package_ver_code = rev2.package_ver_code
                    INNER JOIN cp_game_revision_channel_resources res2
                    ON res2.revision_id = rev2.id
                    WHERE rev2.id = res2.revision_id
                    AND rev.game_id = rev2.game_id
                    AND res2.id = ?
                )';
        $result = $this->db->query($sql,array($chn_id,$chnresid))->result_array();
        if(empty($result))
        {
            return array();
        }
        else{
            $id_list = array();
            foreach ($result as $value) {
                array_push($id_list, $value['id']);
            }
            return $id_list;
        }
    }
    
    public function get_revision_id_gamekey($gamekey,$hot_versioncode = FALSE,$only_published = FALSE) {
        $sql = 'SELECT id FROM cp_game_revision_info rev
                INNER JOIN cp_game_info game ON (rev.game_id = game.game_id)
                WHERE game.game_key=?';
        if($only_published)
        {
            $sql .= ' AND rev.published = 1';
        }
        if($hot_versioncode)
        {
            $sql .= ' AND rev.hot_versioncode = ?';
        }
        else
        {
            $sql .= ' -- AND rev.hot_versioncode = ?';
        }
        $result = $this->db->query($sql,array($gamekey,$hot_versioncode))->result_array();
        if(empty($result))
        {
            return array();
        }
        else{
            $rev_id_list = array();
            foreach ($result as $value) {
                array_push($rev_id_list, $value['id']);
            }
            return $rev_id_list;
        }
    }
    
    /**
     * 查找某个大版本下的所有版本
     * @param type $gamekey
     * @param type $packagevercode
     * @param type $search  TRUE表示返回版本ID，TRUE表示返回版本号
     * @return array
     */
    public function get_hotlist_by_ver($gamekey,$packagevercode,$search = FALSE){
        if($search)
        {
            $search = 'id';
        }
        else
        {
            $search = 'hot_versioncode';
        }
        $sql = 'SELECT rev.'.$search.' 
                FROM cp_game_revision_info rev
                INNER JOIN cp_game_info game 
                ON game.game_id = rev.game_id
                WHERE game.game_key = ?
                AND rev.package_ver_code = ?
               ';
        $result = $this->db->query($sql,array($gamekey,$packagevercode))->result_array();
        if(empty($result))
        {
            return array();
        }
        else{
            $list = array();
            foreach ($result as $value) {
                array_push($list, $value[$search]);
            }
            return $list;
        }
    }
}
// EOF
