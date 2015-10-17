<?php
/**
 *
 * @package Cp_game_info_model
 */ 
class Cp_game_info_model extends MY_Model {

    public $table = 'cp_game_info';
    public $primary_key = 'game_id';

    public function __construct() {
        parent::__construct();
        $this->load->model('cp_game_revision_info_model');
    }

    public function is_game_present( $game_id )
    {
        $sql = "select game_id from cp_game_info where game_id=? limit 1"; 
        $query = $this->db->query($sql,array( $game_id));
        $info = $query->result_array();
        if(!empty($info)){
            return TRUE;
        }
        return FALSE;
    }

    /**
     * is_package_name_present
     * 判断包名是否存在
     * 只在 game_management addgamehandler 使用，预计在将来版本移除
     */
    public function is_package_name_present( $package_name )
    {
        //$package_name = mysql_real_escape_string($package_name);
        $sql = "select game_id from cp_game_info where package_name=? limit 1";
        $query = $this->db->query($sql,array( $package_name));
        $info = $query->result_array();
        if(!empty($info)){
            return TRUE;
        }
        return FALSE;
    }
    
    public function is_game_key_present( $game_key)
    {
        $sql = "select game_id from cp_game_info where game_key=? limit 1";
        $query = $this->db->query($sql,array( $game_key));
        $info = $query->result_array();
        if(!empty($info)){
            return TRUE;
        }
        return FALSE;
    }
    
    /**
     * 获取游戏信息
     * 只在  cp_game_info 表中获取
     */
    public function get_game_detail($gameid)
    {
        if (empty($gameid)){
            return NULL;
        }
        $sql = "select game_key,game_id,game_name,package_name,package_ver,package_ver_code,game_mode,game_type,cp_vendor,channel_id from cp_game_info where del_flag = 0 and game_id=? limit 1";
        $query = $this->db->query($sql, $gameid);
        $info = $query->result_array();
        return $info[0];
    }

    /**
     * 获取游戏信息, 包括线上游戏版本中的信息
     *
     * TODO: 优化查询
     * 注意：不管理有没有查询到关联的子版本都会返回版本的信息，所以要另外判断是不是有合法的子版本信息
     * 
     * 2014/12/26 由于允许存在多个一样版本号的游戏版本， 所以 $is_publish 
     * 必须为真，不再使用. 兼容原因仍保留参数
     *
     * @param int gameid
     * @param int package_ver_code
     * @return array
     */
    public function get_game_detail_extended($gameid, $package_ver_code=NULL, $is_published=TRUE)
    {
        if (empty($gameid)){
            return NULL;
        }
        $sql = "select game_id,game_key,game_name, game_name as original_game_name,package_name,package_ver,package_ver_code,game_mode,game_type,cp_vendor,channel_id from cp_game_info where del_flag = 0 and game_id = ? limit 1";
        $query = $this->db->query($sql, $gameid);
        $info = $query->result_array();
        if(!count($info)) return NULL;
        $game_info = array();
        $game_info = $info[0];
        $rv_template = $this->cp_game_revision_info_model->get_revision_template();
        //unset($rv_template['apk_download_url']);
        //$rv_template['apk_download_url']=''; // dirty hack
        $rv_template['revision_id']='';
        if(!isset($package_ver_code)){
            $package_ver_code = $game_info['package_ver_code'];
        }
        if( isset($package_ver_code) && $package_ver_code!=''){
            $revision_info = $this->cp_game_revision_info_model->get_published_revision_detail_vercode($gameid,$package_ver_code);
            if($revision_info){
                $revision_info['revision_id']=$revision_info['id'];
                unset($revision_info['id']);
                foreach($revision_info as $key => $val ){
                    $rv_template[$key] = $val; 
                }
            }
        }
        unset($rv_template['package_name']);  // 以 game_info 的 package_name 为准
        unset($rv_template['game_id']); 
        foreach($rv_template as $key => $val ){
            $game_info[$key] = $val; // add extended info
        }
        return $game_info;
    }

    public function getAllDetail($s,$e,$search = ''){
        $sql = '
            select a.*,b.channelnum 
            from (select b.game_id,count(b.game_id) as channelnum 
            from cp_game_info a, cp_chn_game_info b, cp_channel_info c 
            where a.game_id=b.game_id and c.channel_id=b.channel_id  group by b.game_id) b 
            right join (select * from cp_game_info '.$search.' order by create_time desc) a on a.game_id=b.game_id limit ?,?
        ';
        $query = $this->db->query($sql, array($s,$e));
        $info = $query->result_array();
        return $info;
    }

    //addchannel page

    /**
     * published_game_list 用游戏管理2.0 的方式获取 game列表
     *
     * - 游戏是试玩或者托管，不能是独立包
     * - 游戏必须有已经发布的版本
     * - 游戏已发布的版本中必须有 apk 与当前的渠道关联
     * - 游戏之前没有添加过
     */
    public function published_game_list2($channel_id) {
        $sql = 'SELECT distinct game.game_id, game.game_name,game.game_mode,game.package_name,apk.apk_download_url
            FROM cp_channel_info channel
            INNER JOIN cp_game_revision_apk apk ON channel.channel_id=apk.channel_id 
            INNER JOIN cp_game_revision_info revision ON revision.id = apk.revision_id
            INNER JOIN cp_game_info game ON game.game_id = revision.game_id
            WHERE channel.channel_id=? 
            AND game.game_mode!=4
            AND revision.is_published=1
            AND game.game_id  NOT IN (SELECT chn.game_id FROM cp_chn_game_info chn WHERE chn.channel_id=? AND chn.del_flag=0)
            ORDER BY game.game_id DESC'; 
        $query = $this->db->query($sql, array($channel_id, $channel_id));
        $info = $query->result_array();
        return $info;
    }

    public function get_active_package_version_code($game_id){
        $sql = "select game_id,package_ver_code from cp_game_info where del_flag=0 and game_id=?";
        $query = $this->db->query($sql,$game_id);
        $info = $query->result_array();
        if(count($info)){
            return $info[0]['package_ver_code'];
        }
        return NULL;
    }

    /**
     * $package_name 无法定位唯一的游戏, 只在旧的 api 中使用
     * TODO: 计划将来版本删除
     */
    public function get_game_id_by_package_name($package_name)
    {
        //加入验证channel_id = 9999
        $sql = "select game_id from cp_game_info where del_flag=0 and package_name=? and channel_id = 9999";
        $query = $this->db->query($sql,$package_name);
        $info = $query->result_array();
        if(!count($info)){
            return NULL;
        }
        return $info[0]['game_id'];
    }

    /**
     * 通过 package_name 获取游戏的扩展信息
     * TODO: 计划将来版本删除
     */
    public function get_game_detail_extended_by_package_name($package_name, $package_ver_code=NULL, $is_published=TRUE)
    {
        //修改game_id获取方法能保证获取的是旧数据
        $game_id = $this->get_game_id_by_package_name($package_name);
        if( $game_id === NULL) return NULL; 
        if(!isset($package_ver_code) || $package_ver_code==''){
            return $this->get_game_detail_extended($game_id,NULL, $is_published);
        }else{
            return $this->get_game_detail_extended($game_id, $package_ver_code,$is_published);
        }
    }

    /**
     * 通过 package_name 和 package_ver_code 获取已发布版本的 revision_id 
     * 注意: package_name 和 package_ver_code 不再能唯一的定位一个版本。因为一个package_name 允许对应多个游戏
     * 因些加上了 channel_id=9999, 兼容旧数据，计划将来版本删除
     * 只在 fixresourcemap 中使用
     * TODO: 计划将来版本删除
     * */
    public function get_revision_id_by_package($package_name, $package_ver_code){
        // 兼容性原因加上  limit 1 , 原来的表设计没对这个做限制
        // 加上 channel_id = 9999 ，只在旧接口中使用
        $sql = "select r.id, g.game_id from cp_game_info g, cp_game_revision_info r where g.del_flag=0 and g.package_name=? and g.game_id=r.game_id and r.package_ver_code=? and r.is_published=1 and g.channel_id=9999 limit 1";
        $query = $this->db->query($sql,array($package_name, $package_ver_code));
        $info = $query->result_array();
        if(!count($info)){
            return NULL;
        }
        return $info[0]['id'];
    }

    public function reset_active_package_version($game_id){
        $sql = "update cp_game_info set package_ver_code=NULL, package_ver='' where game_id=?";
        $result = $this->db->query($sql, $game_id);
        return TRUE;
    }

    public function get_active_revision_id($game_id){
        $sql = "select r.id as revision_id,g.game_id as game_id from cp_game_info g,cp_game_revision_info r where g.del_flag=0 and g.game_id=? and g.game_id=r.game_id and g.package_ver_code=r.package_ver_code and r.is_published=1 order by r.id desc limit 1";
        $query = $this->db->query($sql, $game_id);
        $info = $query->result_array();
        if(!count($info)){
            return NULL;
        }
        return $info[0]['revision_id'];
    }

    /**
     * get_active_revision_id_by_package
     *
     * 取得当前线上版本的  revision_id
     * package_name 无法定位唯 一的记录
     * TODO: 计划删除
    public function get_active_revision_id_by_package($package_name){
        $sql = "select r.id as revision_id,g.game_id as game_id from cp_game_info g,cp_game_revision_info r where g.del_flag=0 and g.package_name=? and g.game_id=r.game_id and g.package_ver_code=r.package_ver_code and r.is_published=1 order by r.id desc limit 1";
        $query = $this->db->query($sql,$package_name);
        $info = $query->result_array();
        if(!count($info)){
            return NULL;
        }
        return $info[0]['revision_id'];
    }
     */
    
    /**
     * 返回上前线上版本的资源包的 url 
     *
     * @param string package_name 包名
     * @param string resource_name cpk 文件名
     * @return string
     * TODO: 无法唯一定位记录 计划删除
    public function get_resource_url_of_active_revision($package_name, $resource_name){
        // res.game_id 已映射为 cp_game_revision_info 的 id
        $sql = 'select r.id as revision_id,g.game_id as game_id, res.resource_url as resource_url ' . 
            ' from cp_game_info g,cp_game_revision_info r, cp_game_revision_resources res ' .
            ' where g.del_flag=0 and g.package_name=? and g.game_id=r.game_id and g.package_ver_code=r.package_ver_code and res.revision_id=r.id and res.resource_pack_name=? and r.is_published=1 limit 1';
        $query = $this->db->query($sql,array($package_name, $resource_name));
        $info = $query->result_array();
        if(!count($info)){
            return NULL;
        }
        return $info[0]['resource_url'];
    }
     */

    /**
     * get_resource_url_of_revision
     *
     * 返回指定版本的资源包的 url。
     * 注意：只在旧接口中使用
     *
     * @param string resource_name  cpk文件名
     * @param int package_ver_code 版本号
     * @return string resource_url 
     */
    public function get_resource_url_of_revision($package_name, $resource_name, $hot_versioncode){
        //加入channel_id = 9999限制
        $sql = 'select r.id as revision_id,g.game_id as game_id, res.resource_url as resource_url from cp_game_info g,cp_game_revision_info r, cp_game_revision_resources res where g.del_flag=0 and g.package_name=? and g.game_id=r.game_id and r.hot_versioncode=? and res.revision_id=r.id and res.resource_pack_name=? and r.is_published=1 and g.channel_id = 9999';
        //$sql = 'select r.id as revision_id,g.game_id as game_id, res.resource_url as resource_url from cp_game_info g,cp_game_revision_info r, cp_game_revision_resources res where g.del_flag=0 and g.package_name=? and g.game_id=r.game_id and r.package_ver_code=? and res.revision_id=r.id limit 5';
        $query = $this->db->query($sql,array($package_name, $hot_versioncode, $resource_name));
        $info = $query->result_array();
        if(!count($info)){
            return NULL;
        }
        return $info[0]['resource_url'];
    }


    /**
     * get_game_list
     *
     * 返回游戏的列表，只返回线上版本
     * 当前只在 capi/api?gamelist 中用到
     * TODO: 计划将来版本删除
     */
    public function get_game_list($limit_from,$len=NULL){
        /*
        download_url":false,
        "package_name":"asbc.defg.hkj",
        "game_name":"new 2014 11 14",
        "game_mode":"1",
        "game_version":"",
        "versioncode":"2",
        "icon_link":false,
        "description":null,
        "orientation":"0",
        "engine_version":"",
        "star":"0",
        "duration":"
         */
        if(!isset($limit_from)) $limit_from = 0;
        if(!isset($len)) $len = 10;
        $sql = "select g.game_id,r.game_name,g.package_name,r.apk_download_url as apk_download_url,g.game_mode,g.package_ver,g.package_ver_code,r.icon_url,r.game_desc,r.orientation,r.engine_version,r.star,r.test_duration from cp_game_info g, cp_game_revision_info r where g.game_mode in(0,1,2,3) and g.del_flag = 0 and g.package_ver_code=r.package_ver_code and r.is_published=1 order by game_id desc limit ?,?";
        $query = $this->db->query($sql,array($limit_from, $len));
        $info = $query->result_array();
        if(!count($info)){
            return NULL;
        }
        return $info;
    }


    public function delete_game($game_id, $test=FALSE){
        if(!$game_id) return FALSE; 
        $sql = "DELETE cp_game_info , cp_game_revision_info, cp_game_revision_apk, cp_game_revision_resources , cp_chn_game_info , cp_contentprovider_games
            FROM cp_game_info 
            LEFT JOIN cp_game_revision_info ON cp_game_info.game_id=cp_game_revision_info.game_id 
            LEFT JOIN cp_game_revision_apk ON cp_game_revision_info.id  = cp_game_revision_apk.revision_id
            LEFT JOIN cp_game_revision_resources ON cp_game_revision_info.id  = cp_game_revision_resources.revision_id
            LEFT JOIN cp_chn_game_info ON cp_game_info.game_id=cp_chn_game_info.game_id 
            LEFT JOIN cp_contentprovider_games ON cp_contentprovider_games.game_key=cp_game_info.game_key
            WHERE cp_game_info.game_id =?";
        $game_result = $this->query($sql, $game_id);
        if(empty($game_result)) {
            return FALSE; 
        }else{
            return TRUE;
        }
    }
    public function delete_game2($game_id, $test=FALSE){
        $result = $this->cp_game_revision_info_model->delete_revisions_of_game($game_id, $test);
        if($result) {
            $sql = "delete from cp_game_info where game_id=?";
            $game_result = $this->query($sql, $game_id);
            if($game_result) return TRUE;
        }
        return FALSE;
    }
    
    /**
     * 修改 get_resource_url_of_revision() $package_name改为$game_key
     * @param string $game_key
     * @param int $package_ver_code
     * @param string $resource_name
     * @return string $resource_url
     */
    public function get_resource_url($game_key,$hot_versioncode,$resource_name){
        $sql = 'select r.id as revision_id,g.game_id as game_id, res.resource_url as resource_url '
                . 'from cp_game_info g,cp_game_revision_info r, cp_game_revision_resources res '
                . 'where g.del_flag=0 and g.game_key=? and g.game_id=r.game_id '
                . 'and r.hot_versioncode=? and res.revision_id=r.id and res.resource_pack_name=? '
                . 'and r.is_published=1';
        $query = $this->db->query($sql,array($game_key, $hot_versioncode, $resource_name));
        $info = $query->result_array();
        if(!count($info)){
            return NULL;
        }
        return $info[0]['resource_url'];
    }
    
    public function get_package_name_by_game_key($gamekey)
    {
        $sql = 'select package_name from cp_game_info where game_key = ? and del_flag = 0';
        $query = $this->db->query($sql,array($gamekey));
        $info = $query->result_array();
        if(!count($info))
            return NULL;
        return $info[0]['package_name'];
    }
    
    /**
     * 根据gamekey获取最新已发布的版本号
     * @param type $gamekey
     * @return type
     */
    public function get_latest_ver_by_gamekey($gamekey, $chn=NULL) {
        if(isset($chn) && $chn != '999997') {
            $sql = 'SELECT max(rev2.hot_versioncode) hot_versioncode FROM cp_game_revision_info rev2, cp_game_revision_apk apk2 , cp_game_info game
                    WHERE rev2.is_published=1 and rev2.id=apk2.revision_id and game.game_key=? and apk2.channel_id=? and game.game_id = rev2.game_id 
                    and apk2.apk_download_url != \'\' and apk2.apk_download_url is not NULL and apk2.active = 1';
            $query = $this->db->query($sql,array($gamekey, $chn));
        }else{
            $sql = "select max(rev.hot_versioncode) as hot_versioncode from cp_game_revision_info rev,cp_game_info game "
                . "where game.game_id=rev.game_id and game.game_key=? and is_published = 1";
            $query = $this->db->query($sql,array($gamekey));
        }
        $info = $query->result_array();
        if(!count($info))
            return NULL;
        return $info[0]['hot_versioncode'];
    }

    /**
     * 根据gamekey获取最新已发布的大版本的最旧小版本
     * @param type $gamekey
     * @return type
     */
    public function get_latest_ver_by_gamekey_indep($gamekey) {
        $sql = "select min(rev.hot_versioncode) as hot_versioncode from cp_game_revision_info rev,cp_game_info game 
            WHERE game.game_id=rev.game_id and game.game_key=? and is_published = 1 
            GROUP BY rev.package_ver_code ORDER BY rev.package_ver_code DESC limit 1";
        $query = $this->db->query($sql,array($gamekey));
        $info = $query->result_array();
        if(!count($info))
            return NULL;
        return $info[0]['hot_versioncode'];
    }

    /**
     * 获取 game_key 对应的大版本下的最旧的小版本号
     */
    public function get_oldest_ver_by_gamekey_currentver($gamekey, $package_ver = NULL) {
        if(isset($package_ver)) {
        $sql = "select min(rev.hot_versioncode) as hot_versioncode 
                from cp_game_revision_info rev, cp_game_info game 
                where game.game_id=rev.game_id and game.game_key=? and rev.is_published = 1 and rev.package_ver_code=?";
        $query = $this->db->query($sql,array($gamekey, $package_ver));
        $info = $query->result_array();
        if(!count($info))
            return FALSE;
        return $info[0]['hot_versioncode'];
        }else{
            $sql = "SELECT min(rev.hot_versioncode) as hot_versioncode, rev.package_ver_code from cp_game_revision_info rev,cp_game_info game 
                WHERE game.game_id=rev.game_id and game.game_key=? and is_published = 1 
                GROUP BY rev.package_ver_code ORDER BY rev.package_ver_code DESC LIMIT 1";
            $query = $this->db->query($sql,array($gamekey));
            $info = $query->result_array();
            if(!count($info))
                return FALSE;
            return $info[0]['hot_versioncode'];
        }
    }
    
    /**
     * 获取指定gamekey，大版本号下的小版本列表
     * @param type $gamekey
     * @param type $package_ver_code
     * @return type
     */
    public function get_hotver_list_gamekey_currentver($gamekey,$package_ver_code){
        $sql = "SELECT rev.hot_versioncode 
            FROM cp_game_revision_info rev, cp_game_info game 
            WHERE game.game_id=rev.game_id AND game.game_key=? AND rev.is_published = 1 AND rev.package_ver_code=?";
        $result = $this->db->query($sql,array($gamekey,$package_ver_code))->result_array();
        if(!count($result))
        {
            return NULL;
        }
        $list = array();
        foreach ($result as $value) {
            array_push($list, $value['hot_versioncode']);
        }
        return $list;
    }
    
    /**
     * 根据gamekey获取当前版本号
     * @param type $gamekey
     * @return type
     */
    public function get_current_ver_by_game_key($gamekey)
    {
        $sql = "SELECT `package_ver_code` FROM `cp_game_info` WHERE game_key = ? and del_flag = 0";
        $query = $this->db->query($sql,array($gamekey));
        $info = $query->result_array();
        if(!count($info))
            return NULL;
        return $info[0]['package_ver_code'];
    }
    
    public function get_random_cpk_name($gamekey,$ver = NULL)
    {
        $sql = "select resource_pack_name "
                . "from cp_game_info gm, cp_game_revision_info rev, cp_game_revision_resources res "
                . "where gm.game_id = rev.game_id and rev.id = res.revision_id "
                . "and gm.game_key = ? ";
        if(empty($ver))
            $sql.="and gm.package_ver_code = rev.package_ver_code";
        else
            $sql.="and rev.package_ver_code = {$ver}";
        $query = $this->db->query($sql,array($gamekey));
        $info = $query->result_array();
        $num = count($info);
        if($num == 0)
            return NULL;
        else
        {
            $ran = rand(0, $num-1);
            return $info[$ran]['resource_pack_name'];
        }
    }
    
    /**
     * 返回创建一个游戏的模板
     * @return type
     */
    public function get_game_template()
    {
        return array(
            'game_key' => '',
            'game_name' => '',
            'package_name' => '',
//            'package_ver_code' => '',
            'game_mode' => '',
            'game_type' => 0,
            'cp_vendor'=>'',
            'apk_name' => '',
            'create_time' =>time(),
            'modify_time' => time(),
            'opt_id' => 0,
        );
    }
}
