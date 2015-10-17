<?php
/**
 * 保存 游戏与渠道的对应规则，及渠道的一些  config 设置
 *
 * @package Cp_chn_game_info_model 
 */

class Cp_chn_game_info_model extends MY_Model {

    public $table = 'cp_chn_game_info';
    public $primary_key = 'id';

    public function __construct() {
        parent::__construct();
    }

    /**
     * 获取关联了指定渠道的 gamelist 列表
     *
     * 获取关联了指定渠道的 gamelist 列表。游戏必须是已经关联到指定的渠道， 
     * 关且游戏中有已经发布的版本。在获取游戏版本信息时，
     * 
     * - 不包括独立包
     * - 必须有关联
     * - 只显示已发布的
     *
     * @param int $chn_id 渠道id
     * @param int $start  起始条数
     * @param int $len    查询条数  
     * @param boolean $only_visible TRUE表示只显示渠道中设为可见的游戏，FALSE表示显示全部，默认为FALSE
     * @param boolean $include_indepgame 只显示独立包
     * @return array 返回满足条件的游戏
     */
    public function get_channel_game_list($chn_id, $start=0, $len=0, $only_visible=FALSE, $include_indepgame=FALSE, $hot_games_only = 0, $no_runtimegame = TRUE) {
        $sql = 'SELECT
            mapping.id, mapping.channel_id, mapping.game_order, mapping.is_visiable,
            mapping.apk_download_url as ch_apk_download_url, mapping.bg_picture as ch_bg_picture, 
            mapping.full_game_download_url, game.game_id, game.game_key, game.game_name, 
            game.game_mode, game.game_type, game.cp_vendor,
            revision.hot_versioncode,revision.genuine_versioncode,
            revision.package_ver, revision.package_ver_code, revision.apk_download_url as rev_apk_download_url, 
            revision.apk_name, revision.appid, revision.bg_music, revision.bg_picture, 
            revision.chafen_url, revision.coop_method, revision.create_time, revision.is_published,
            revision.engine_version, revision.file_dir, revision.game_desc, revision.icon_url, 
            revision.is_maintain, revision.maintain_tip, revision.manifest_url, revision.mdf_url,
            revision.opt_id, revision.orientation, revision.package_up_time, revision.payment,
            revision.sdk_version, revision.star, revision.user_system,
            revision.test_duration, mapping.is_visiable as is_visible, revision.ver_last,revision.game_name as rev_game_name,
            game.channel_id as old_data_flag
            ,mapping.test_duration as chn_test_duration
            ,apk.apk_download_url, apk.id as apkid
            ,revision.id as revision_id
            ,revision.cpk_file_dir
            ,revision.manifest_version
            ,revision.manifest_json_url
            ,revision.manifest_json_md5
            ,res.icon_url as res_icon_url
            ,res.bg_music as res_bg_music
            ,res.bg_picture as res_bg_picture
            ,GREATEST (game.modify_time, revision.modify_time, COALESCE(UNIX_TIMESTAMP(apk.modify_time), 0)) as modify_time
            ,mapping.modify_time as mapping_modify_time
            ,game.offline_support
            ,mapping.hot
            ,mapping.prefetch_type
            ,revision.package_name as rev_package_name
            ,COALESCE(game.package_name, REPLACE(SUBSTRING_INDEX(apk.apk_download_url, \'/\', -1), \'.apk\', \'\'), revision.package_name, \'\') as package_name
            FROM cp_chn_game_info mapping INNER JOIN cp_game_info game ON game.game_id=mapping.game_id 
            INNER JOIN cp_game_revision_info revision ON revision.game_id=game.game_id
            INNER JOIN 
            (SELECT rev2.game_id, max(rev2.hot_versioncode) hot_versioncode FROM cp_game_revision_info rev2, cp_game_revision_apk apk2 
            WHERE apk2.active=1 and rev2.is_published=1 and rev2.id=apk2.revision_id and apk2.channel_id=? and apk2.apk_download_url != \'\' 
            and apk2.apk_download_url is not NULL GROUP BY rev2.game_id) revision_filter 
            ON revision_filter.hot_versioncode = revision.hot_versioncode AND revision_filter.game_id = revision.game_id 
            INNER JOIN cp_game_revision_apk apk ON (apk.revision_id=revision.id AND apk.channel_id=mapping.channel_id AND apk.apk_download_url IS NOT NULL AND apk.apk_download_url != "")
            LEFT JOIN cp_game_revision_channel_resources res ON (res.id=apk.revision_channel_resources_id)
            WHERE mapping.channel_id=? 
            AND revision.is_published=1 ';
        if( $only_visible ) {
            $sql = $sql . " AND mapping.is_visiable = 1";
        }

        if(!$include_indepgame) {
            $sql = $sql . " AND NOT game.game_mode = 4";
        }

        if( $hot_games_only ) {
            $sql = $sql . " AND mapping.hot = 1";
        }

        if( $no_runtimegame ) {
            $sql = $sql . " AND NOT game.game_mode = 7";
        }

        $sql .= ' AND mapping.del_flag=0 
                ORDER BY mapping.game_order DESC,
                modify_time DESC';
        if($len>0 && $start>=0) {
            $sql .= " LIMIT $start, $len";
            $query = $this->db->query($sql, array($chn_id, $chn_id, $start, $len));
        }else{
            $query = $this->db->query($sql, array($chn_id, $chn_id));
        }
        $info = $query->result_array();
        if(count($info)){
            foreach($info as &$channel_game){
                // TODO: 
                if($channel_game['game_mode'] == 4) {
                    $channel_game['apk_download_url'] = $channel_game['rev_apk_download_url'];
                }elseif(!$channel_game['apk_download_url'] && !$channel_game['apkid']) {
                    // 向下兼容， 如果已经加入到渠道的， 
                    // 并且没有设置映射的，直接从 revision 中读 
                    // apk_download_url
                    $channel_game['apk_download_url'] = $channel_game['rev_apk_download_url'];
                }
                //music,bgpic,icon，读取顺序，若设置了渠道资源，则优先读取，否则从版本信息取值
                if(!empty($channel_game['res_icon_url']))
                {
                    $channel_game['icon_url'] = $channel_game['res_icon_url'];
                }
                if(!empty($channel_game['res_bg_picture']))
                {
                    $channel_game['bg_picture'] = $channel_game['res_bg_picture'];
                }
                if(!empty($channel_game['res_bg_music']))
                {
                    $channel_game['bg_music'] = $channel_game['res_bg_music'];
                }
            }
            unset($channel_game);
        }
        return $info;
    }
    
    /**
     * 根据渠道id，游戏id查找渠道、游戏、版本信息
     *
     * 根据渠道id，游戏id查找渠道、游戏、版本信息
     *
     * - 没设置版本号则使用线上版本号
     *
     * 独立包游戏从apiv2 中请求时可以不传 chn
     *
     * 内部实现是：不传 chn 时，默认请求 chn=999997 
     * 999997 是一个虚拟的渠道号. 
     * 通过这种方式将独立包与其他的游戏资源请求统一起来
     *
     * 本方法接收到 chn=999997 的请求时，从虚拟渠道返回结果
     *
     * 注意：这个方法只有 apiv2,v3,v4 的 php 版本中使用
     *       在使用时不返回 game_mode = 7 的游戏
     *
     * @param int $chn_id 渠道id
     * @param int $game_id 游戏id
     * @param int 版本编号
     * @return array 渠道、游戏、版本信息
     */
    public function get_channel_game_detail($chn_id, $game_id, $hot_versioncode=NULL, $no_runtimegame = TRUE){
        // hack warning 
        $sql = '';
        if($chn_id== '999997') {
            // 这是个 魔术 channel 用来关联 独立包游戏
            // 2015/01/21 需求， 独立包不需要用传 channel_id 也可以获取, 
            // 解决的办法是加一个虚拟的渠道 999997 , 用来关联所有的独立包游戏
            $sql = "SELECT
                0 id, 999997 channel_id, 0 game_order, 1 is_visiable, 
                '' ch_apk_download_url, '' ch_bg_picture, 
                '' full_game_download_url, game.game_id, game.game_key, game.game_name, 
                game.game_mode, game.game_type, game.cp_vendor,
                revision.hot_versioncode,revision.genuine_versioncode,
                revision.package_ver, revision.package_ver_code, revision.apk_download_url as rev_apk_download_url, 
                revision.apk_name, revision.appid, revision.bg_music, revision.bg_picture, 
                revision.chafen_url, revision.coop_method, revision.create_time, revision.is_published,
                revision.engine_version, revision.file_dir, revision.game_desc, revision.icon_url, 
                revision.is_maintain, revision.maintain_tip, revision.manifest_url, revision.mdf_url,
                revision.opt_id, revision.orientation, revision.package_up_time, revision.payment,
                revision.sdk_version, revision.star, revision.user_system,
                revision.test_duration, 1 is_visible, revision.ver_last,
                revision.game_name as rev_game_name,
                game.channel_id as old_data_flag
                ,revision.test_duration as chn_test_duration
                ,revision.apk_download_url as apk_download_url, '' apkid
                ,revision.id as revision_id
                ,revision.cpk_file_dir
                ,revision.manifest_version
                ,revision.manifest_json_url
                ,revision.manifest_json_md5
                ,GREATEST (game.modify_time, revision.modify_time) as modify_time
                ,game.offline_support
                ,revision.package_name as rev_package_name
                ,game.package_name
                ,0 hot
                ,0 prefetch_type
                FROM cp_game_info game 
                INNER JOIN cp_game_revision_info revision ON revision.game_id=game.game_id";

            // 示指定版本号
            if(empty($hot_versioncode)) {
                $sql .= " INNER JOIN 
                (SELECT rev2.game_id, max(rev2.hot_versioncode) hot_versioncode FROM cp_game_revision_info rev2 WHERE rev2.is_published=1 GROUP BY rev2.game_id) revision_filter 
                ON revision_filter.hot_versioncode = revision.hot_versioncode AND revision_filter.game_id = revision.game_id 
                WHERE game.game_id = ?
                AND revision.is_published=1 
                ORDER BY modify_time DESC 
                LIMIT 1";
                $query = $this->db->query($sql, array($game_id));
            } else {
                // 指定版本号
                $sql .= " WHERE game.game_id = ?
                AND revision.hot_versioncode=?
                AND revision.is_published=1 
                ORDER BY modify_time DESC 
                LIMIT 1";
                $query = $this->db->query($sql, array($game_id, $hot_versioncode));
            }
        } 
        else 
        {
            // 非独立包

        $sql = 'SELECT
            mapping.id, mapping.channel_id, mapping.game_order, mapping.is_visiable, 
            mapping.apk_download_url as ch_apk_download_url, mapping.bg_picture as ch_bg_picture, 
            mapping.full_game_download_url, game.game_id, game.game_key, game.game_name, 
            game.game_mode, game.game_type, game.cp_vendor,
            revision.hot_versioncode,revision.genuine_versioncode,
            revision.package_ver, revision.package_ver_code, revision.apk_download_url as rev_apk_download_url, 
            revision.apk_name, revision.appid, revision.bg_music, revision.bg_picture, 
            revision.chafen_url, revision.coop_method, revision.create_time, revision.is_published,
            revision.engine_version, revision.file_dir, revision.game_desc, revision.icon_url, 
            revision.is_maintain, revision.maintain_tip, revision.manifest_url, revision.mdf_url,
            revision.opt_id, revision.orientation, revision.package_up_time, revision.payment,
            revision.sdk_version, revision.star, revision.user_system,
            revision.test_duration, mapping.is_visiable as is_visible, revision.ver_last,
            revision.game_name as rev_game_name,
            game.channel_id as old_data_flag
            ,mapping.test_duration as chn_test_duration
            ,apk.apk_download_url, apk.id as apkid
            ,res.icon_url as res_icon_url,res.bg_picture as res_bg_picture,res.bg_music as res_bg_music
            ,revision.id as revision_id
            ,revision.cpk_file_dir
            ,revision.manifest_version
            ,revision.manifest_json_url
            ,revision.manifest_json_md5
            ,GREATEST (game.modify_time, revision.modify_time, COALESCE(UNIX_TIMESTAMP(apk.modify_time), 0)) as modify_time
            ,game.offline_support
            ,revision.package_name as rev_package_name
            ,COALESCE(game.package_name, REPLACE(SUBSTRING_INDEX(apk.apk_download_url, \'/\', -1), \'.apk\', \'\'), \'\') as package_name
            ,mapping.hot
            ,mapping.prefetch_type
            FROM cp_chn_game_info mapping INNER JOIN cp_game_info game ON game.game_id=mapping.game_id 
            INNER JOIN cp_game_revision_info revision ON revision.game_id=game.game_id';

            if(empty($hot_versioncode)) {
                $sql .= ' INNER JOIN 
                (SELECT rev2.game_id, max(rev2.hot_versioncode) hot_versioncode FROM cp_game_revision_info rev2, cp_game_revision_apk apk2 
                WHERE apk2.active=1 and rev2.is_published=1 and rev2.id=apk2.revision_id and apk2.channel_id=? and apk2.apk_download_url != \'\' 
                and apk2.apk_download_url is not NULL GROUP BY rev2.game_id) revision_filter 
                ON revision_filter.hot_versioncode = revision.hot_versioncode AND revision_filter.game_id = revision.game_id 
                LEFT JOIN cp_game_revision_apk apk ON (apk.revision_id=revision.id AND apk.channel_id=mapping.channel_id)
                LEFT JOIN cp_game_revision_channel_resources res ON (res.id=apk.revision_channel_resources_id)
                WHERE mapping.channel_id=? 
                AND mapping.game_id = ?
                AND mapping.del_flag=0 
                AND revision.is_published=1 ';

                if( $no_runtimegame ) {
                    $sql .= ' AND NOT game.game_mode = 7 ';
                }

                $sql .= ' ORDER BY mapping.game_order DESC,
                modify_time DESC 
                LIMIT 1';

                $query = $this->db->query($sql, array($chn_id, $chn_id, $game_id));

            }else {
                $sql .= ' LEFT JOIN cp_game_revision_apk apk ON (apk.active=1 and apk.revision_id=revision.id AND apk.channel_id=mapping.channel_id)
                LEFT JOIN cp_game_revision_channel_resources res ON (res.id=apk.revision_channel_resources_id)    
                WHERE mapping.channel_id=? 
                AND mapping.game_id = ?
                AND revision.hot_versioncode=?
                AND revision.is_published=1 
                AND mapping.del_flag=0 ';

                if( $no_runtimegame ) {
                    $sql .= ' AND NOT game.game_mode = 7 ';
                }

                $sql .= ' ORDER BY mapping.game_order DESC,
                modify_time DESC 
                LIMIT 1';

                $query = $this->db->query($sql, array($chn_id, $game_id, $hot_versioncode));
            }
        }
        $info = $query->result_array();
        if(count($info)){
            foreach($info as &$channel_game){
                // TODO: 
                if($channel_game['game_mode'] == 4) {
                    $channel_game['apk_download_url'] = $channel_game['rev_apk_download_url'];
                }elseif(!isset($channel_game['apkid'])) {
                    // 向下兼容， 如果已经加入到渠道的， 
                    // 并且没有设置映射的，直接从 revision 中读 
                    // apk_download_url
                    $channel_game['apk_download_url'] = $channel_game['rev_apk_download_url'];
                }
                //music,bgpic,icon，读取顺序，若设置了渠道资源，则优先读取，否则从版本信息取值
                if(!empty($channel_game['res_icon_url']))
                {
                    $channel_game['icon_url'] = $channel_game['res_icon_url'];
                }
                if(!empty($channel_game['res_bg_picture']))
                {
                    $channel_game['bg_picture'] = $channel_game['res_bg_picture'];
                }
                if(!empty($channel_game['res_bg_music']))
                {
                    $channel_game['bg_music'] = $channel_game['res_bg_music'];
                }
            }
            unset($channel_game);
        }
        return $info;
    }

    /**
     * 根据game_id查找与他关联的渠道id，修改时间，渠道名
     * @param int $game_id 游戏id
     * @return array 与指定游戏关联的渠道信息
     */
    public function getAllDetail($game_id){
        $sql = "select a.channel_id,a.modify_time,b.channel_name from cp_chn_game_info a, cp_channel_info b where a.game_id =? and a.channel_id = b.channel_id and a.del_flag =0 and b.del_flag = 0";
        $query = $this->db->query($sql, $game_id);
        $info = $query->result_array();
        return $info;
    }

    /**
     * 根据game_key chn_id查找详细的游戏信息，可以指定具体的游戏版本，游戏模式
     *
     * 根据game_key chn_id查找详细的游戏信息，可以指定具体的游戏版本，游戏模式
     *
     * @param int $chn_id  渠道id
     * @param string $gamekey 游戏唯一标识
     * @param int $package_ver_code 游戏版本号
     * @param int $game_mode  游戏模式
     * @return array 详细的游戏信息
     */
    public function get_channel_game_detail_gamekey($chn_id,$gamekey, $hot_versioncode, $game_mode=FALSE){
        $sql = 'SELECT package_ver_code,game_id FROM cp_game_info WHERE game_key=?';
        $query  = $this->db->query($sql, $gamekey);
        $info = $query->result_array();
        if(empty($info)) {
            return array();
        }
        $active_package_ver_code = $info[0]['package_ver_code'];
        $game_id = $info[0]['game_id'];
        /*
         20150213 不再使用线上版本 
        if(!isset($package_ver_code) || !is_numeric($package_ver_code)) {
            $package_ver_code = $active_package_ver_code; 
        }
         */

        // 如果没有 package_ver_code/hot_versioncode 直接以最新的 发布版本
        $game_detail = $this->get_channel_game_detail($chn_id, $game_id, $hot_versioncode);
        if(empty($game_detail)) {
            return array();
        }else{
            if($game_mode !== FALSE) {
                if($game_detail[0]['game_mode']!=$game_mode) {
                    return array();
                }
            }
            return $game_detail[0];
        }
    }
}
