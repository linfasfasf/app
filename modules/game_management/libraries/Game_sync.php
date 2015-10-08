<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
* Name:  Game_sync
*
* @package Game_sync 
* @description  同步游戏到远程的另一台机器
*
* Requirements: PHP5 or above
*/

class Game_sync
{
    protected $fields = array(
//        'game_data' => array(
//       ),
        'revision_data' => array(
            "game_id",
            "revision_id",
            "game_name",
            "package_ver",
            "package_ver_code",
            "game_desc",
            "orientation",
            "engine_version",
            "star",
            //"language", 没有
            "user_system",
            "payment",
            "game_type",
            "sdk_version",
            "cp_vendor",
            "is_maintain",
            "maintain_tip",
            "ver_last",
            "is_published",
            "create_time",
            "modify_time",
            "hot_versioncode",
            "genuine_versioncode",
            "manifest_json_md5",
            "tool_version",
            "engine",
            "compatible_arch",
            "opt_id"),
        'generic_resources' => array(
            "apk_download_url",
            "icon_url",
            "mdf_url",
            "manifest_url",
            "manifest_json_url",
            "bg_picture",
            "bg_music",
            "file_dir",
            "apk_file_dir",
            "cpk_file_dir",
            "package_name",
            "apk_name",
            "chafen_url",
            "manifest_version"),
        'chn_resources' => array(
            "channel_id",
            "res_file_dir",
            "res_icon_url",
            "res_bg_picture",
            "res_bg_music",
            "res_apk_download_url",
            "channel_config_type",
            "channel_config_version",
            "channel_config_url",
            ),
        'chafen_resources' => array(
            "chafen_url",
            "support_ver_code",
            "support_package_ver_code",
            )
        );
        
	/**
	 * @var string
	 **/
	protected $status;

    function __construct() {
        $this->local = NULL;
        $this->remote = NULL;
        $this->merged = NULL;
        $this->uploads = FCPATH .'uploads';
        $this->magic_suffix = 'STG';
        //$this->magic_suffix = '';
    }

    // 将文件传到 cdn 
    public function put_cdn($from, $to) {
    }

    // 从 cdn 取文件到本地
    public function get_cdn($from, $to) {
    }

    /**
     * 线上服务器导出信息 
     * 只导出已发布
     *
     * 需要提供备用的目录和备用的渠道资源目录
     */
    public function export_live_server($gamekey, $hotversioncode, $channel_id) {
        $sql = 'select game.game_key, revision.id 
            FROM cp_game_info  game INNER JOIN cp_game_revision_info revision 
            ON game.game_id = revision.game_id 
            WHERE game.game_key=? and revision.hot_versioncode=?'; //  and revision.is_published=1'; 
        $CI = &get_instance();
        $db = $CI->load->database('', TRUE);
        $query = $db -> query($sql, array($gamekey, $hotversioncode));
        $result =  $query->result_array(); 
        if(count($result)) {
            // 存在 revision
            $revision_id = $result[0]['id'];
            $result =  $this->export($revision_id, $channel_id);
            if($result === FALSE) {
                $result =  $this->export_generic($revision_id);
            }
            return $result;
        }else{
            // 不存在 revision
            $sql = 'select * from cp_game_info where game_key =?';
            $query = $db -> query($sql, array($gamekey));
            $result =  $query->result_array(); 
            if(count($result)) {
                $gameinfo =  $result[0];
                foreach($this->fields as $category) {
                    foreach($category as $field) { 
                        if(!array_key_exists($field, $gameinfo)) {
                            $gameinfo[$field] = '';
                        }
                    }
                }
                unset($gameinfo['modify_time']);
                unset($gameinfo['create_time']);
                $gameinfo['revision_id'] = 0;
                return $gameinfo;
            }else{
                return FALSE;
            }
        }
    }

    /**
     * 检验从准线上服务器传过来的信息，验证线上服务器是否满足同步的条件。
     *
     * 1. 渠道必须已存在
     * 2. 游戏必须已存在
     * 3. 游戏版本没有已发布的渠道资源 (即已经在服役的渠道资源不可替换)
     * 4. 游戏版本必须已存在 (TODO: 改成可以创建新的版本)
     */
    public function verification_live_server($info) {
        $game_key = $info['game_key'];
        $channel_id = $info['channel_id'];
        $game_id = 0;
        $result = array(
            'game_id' => 'ok',
            'game_key' => 'ok',
            'channel_id' => 'ok',
            'channel_resource' => 'ok',
            'revision' => 'ok',
        );
        $CI = &get_instance();
        $db = $CI->load->database('', TRUE);
        $check_gk = $db->select('game_key, game_id')
            ->where(array('game_key'=>$info['game_key']))
            ->limit(1)->get('cp_game_info')
            ->result_array();
        if(!count($check_gk)) { 
            $result['game_key'] = 'fail';
        }else{
            $game_id = $check_gk[0]['game_id'];
        }

        $check = $db->select('channel_id')
            ->where(array('channel_id'=>$info['channel_id']))
            ->limit(1)
            ->get('cp_channel_info')
            ->result_array();
        if(!count($check)) { 
            $result['channel_id'] = 'fail';
        }

        // check revision
        if($game_id)  {
            $check = $db->select('id')
                ->where(array('game_id'=> $game_id,
                'package_ver_code'=>$info['package_ver_code'],
                'is_published'=> '1'))
                ->limit(1)
                ->get('cp_game_revision_info')
                ->result_array();
            if(!count($check)) { 
                $result['revision'] = 'fail';
            }else{
                $result['revision'] = $check[0]['id'];
            }
        }else{
                $result['revision'] = 'fail';
        }
        return $result;
    }

    // 导出游戏信息
    public function export($revision_id, $channel_id, $fixdir = FALSE) {
        // game_info
        // revision_info
        // channel_resource info
        $CI = &get_instance();
        $sql = 'select * from all_channel_game_info_view where revision_id=? and channel_id=?';
        $db = $CI->load->database('', TRUE);
        $query = $db -> query($sql, array($revision_id, $channel_id));
        $result =  $query->result_array(); 
        if(count($result)){
            $result = $result[0];
        }
        if($result && $this->verify_info($result)) {
            $this->rearrange_resource($result, $fixdir);
            return $result;
        }else{
            return FALSE;
        }
    }

    // 导出游戏通用资源信息
    public function export_generic($revision_id) {
        // game_info
        // revision_info
        // channel_resource info
        $CI = &get_instance();
        $sql = 'select * from all_indep_game_info_view where revision_id=?';
        $db = $CI->load->database('', TRUE);
        $query = $db -> query($sql, array($revision_id));
        $result =  $query->result_array(); 
        if(count($result)){
            $result = $result[0];
        }
        if($result && $this->verify_info($result)) {
            $this->rearrange_resource($result);
            return $result;
        }else{
            return FALSE;
        }
    }

    // 导出游戏信息， 并替换资源目录
    public function export_nextdir() {
    }

    // 导出准上线游戏的 chafen 信息
    public function export_chafen($revision_id) {
        $CI = &get_instance();
        $sql = 'select * from cp_game_revision_chafen where revision_id=?';
        $db = $CI->load->database('', TRUE);
        $query = $db->query($sql, array($revision_id));
        $result =  $query->result_array(); 
        if(count($result)){
            return $result;
        }else{
            return FALSE;
        }
    }

    /**
     * 检查 $gameinfo 的完整性
     *
     * @return array(status, info)
     */
    public function verify_info($gameinfo) {
        // 1. 所有必填项有值并且格式正确
        // 2. Manifest 存在并且格式正确
        // 3. 检查 CPK 文件列表 异步
        //return array(False, array('game_key'=>'doesnot exist'));
        return array(TRUE, NULL);
    }

    /**
     * 将 $local 与 $remote 的信息进行合并
     * $startegy 可以指定合并使用的函数
     *
     * @return 返回结果
     */
    public function merge($local, $remote, $strategy = NULL) {
        if(empty($remote)) { 
            unset($local['game_id']);
            unset($local['revision_id']);
            return $local;
        }
        elseif(empty($local)) {
            return FALSE;
        }
        else {
            $local['game_id'] = $remote['game_id'];
            $local['revision_id'] = $remote['revision_id'];
            $local['is_published'] = $remote['is_published'];
            foreach($local as $key => $val) {
                if(isset($strategy)) {
                    if(in_array($key, $strategy)) {
                        $remote[$key] = $local[$key];
                    }
                }else{
                    $remote[$key] = $local[$key];
                }
            }
            return $remote;
        }
    }

    /**
     * 根据合并后的文件信息
     * 将本地的文件目录传到远程的目录
     *
     * $merged_remote 是远程合并 apply 后返回的数据
     */
    //public function batch_put($local, $merged_remote, $options = NULL) 
    public function batch_put($local, $options = NULL) {
        /*
        $generic_resources = $this->fields['generic_resources'];
        $chn_resources = $this->fields['chn_resources'];
         */

        $dir_queue_key = 'SET_SYNC_LOCAL_CDN_DIR_GOLIVE';
        $file_queue_key = 'SET_SYNC_LOCAL_CDN_FILE_GOLIVE';
        $dirs = array(
            'file_dir', 
            'apk_file_dir', 
            'cpk_file_dir', 
            //'res_file_dir', 
        );
        $chndir = 'res_file_dir';
        $result = array();

        $redis_manager = Redis_Manager::get_instance();
        $added = array(); // 避免重复加目录
        foreach($dirs as $dir) {
            if(strlen(basename($local[$dir])) == (6 + strlen($this->magic_suffix)) && strpos($local[$dir], $this->magic_suffix) !== FALSE) {
                $fixed_dir = dirname($local[$dir]) . '/' . substr(basename($local[$dir]), 0, 6);
            }else{
                $fixed_dir = $local[$dir];
            }
            $addstring = $fixed_dir. '@' . $local[$dir];
            if(!in_array($addstring, $added)) {
                if($options) {
                    if($options['generic_resources'] && $local[$dir]) {
                        $result[] = $redis_manager->enqueue_dir($this->uploads . $fixed_dir, $local[$dir], $dir_queue_key);
                    }else{
                        // do nothing
                    }
                }else{
                    if($local[$dir]) {
                        $result[] = $redis_manager->enqueue_dir($this->uploads . $fixed_dir, $local[$dir], $dir_queue_key);
                    }
                }
                $added[] = $addstring;
            }
        }
        unset($added);
        if($options && $options['chn_resources'] && $local[$chndir]) {
            if(strlen(basename($local[$chndir])) == (6 + strlen($this->magic_suffix)) && strpos($local[$chndir], $this->magic_suffix) !== FALSE) {
                $fixed_dir = dirname($local[$chndir]) . '/' . substr(basename($local[$chndir]), 0, 6);
            }else{
                $fixed_dir = $local[$chndir];
            }
            $result[] = $redis_manager->enqueue_dir($this->uploads . $fixed_dir, $local[$chndir], $dir_queue_key);
        }
        if ($options && $options['chn_resources'] && $local['channel_config_url']) {
            $config_file_url = dirname($local['channel_config_url']);
            $result[] = $redis_manager->enqueue_file($this->uploads . $config_file_url, $config_file_url, $file_queue_key);
        }
        return $result;
    }

    /**
     * 将信息写入表
     * 
     * $info revision 信息
     * $manifest_url 可以取到 manifest 的地址
     * @return 返回结果及重定向的目录
     */
    public function apply($info, $manifest_url, $strategy=NULL, $chafen_resources=NULL, $options = NULL) {
        // 游戏信息应该未发布状态
        // 如果要设为发布应该加队列检查 cdn 的文否完整，待完整时再更新为发布
        // 并发布通知
        // info 的格式为 published_indep_game_info_view 中的字段
        // 1. 验证
        if(!$options) {
            if(isset($strategy)) {
                    $options = $this->build_options($strategy);
            }else{
                $options = array(
                    'revision_data' => 0,
                    'generic_resources' => 0,
                    'chn_resources' => 0,
                    'chafen_resources' => 0,
                );
            }
        }
        $summary = array();
        list($pass, $verification_info) = $this->verify_info($info);
        if ($pass) {
            if($options['revision_data'] || $options['generic_resources']) {
                $update_result = $this->add_or_update_revision($info);
                $summary['update_revision'] = $update_result; 
                if($update_result) {
                    $cpk_update_result = $this->update_cpk_mapping($info, $manifest_url);
                }
            }
            if($options['chn_resources'] ) {
                if($info['revision_id']) {
                    $result = $this->apply_chnres($info); 
                    $summary['update_chnres'] = $result;
                }else{
                    $summary['update_chnres'] = 'no revision_id';
                    return array(FALSE, $summary); 
                }
            }
            if($options['chafen_resources'] && $chafen_resources) {
                // TODO: 更新同步 chafen_resources
                if($info['revision_id']) {
                    $result = $this->apply_chafen($info['revision_id'], $chafen_resources); 
                    $summary['chafen_resources'] = $result;
                }else{
                    $summary['chafen_resources'] = 'no revision_id';
                    return array(FALSE, $summary); 
                }
            }
            // TODO: 处理失败情况
            return array(TRUE, $options);
        }else{
            $summary['verify_info'] = $verification_info;
            return array(FALSE,$summary); 
        }
    }

    /**
     * 更新 cp_game_revision_resources 
     *
     * @param array $info revision 的信息包括 cpk_file_dir 和 revision_id
     * @param string $manifest_url 在准线上的 manifest 的地址
     * @return bool
     */
    public function update_cpk_mapping($info, $manifest_url) {
        return $this->fixresourcemap($info, $manifest_url);
    }

    /**
     * 从 strategy 找出更新的 options (渠道资源，通用资源，游戏信息）等。
     */
    public function build_options($strategy) {
        $options  = array('revision_data'=>0, 'generic_resources'=>0, 'chn_resources'=>0);
        foreach($strategy as $key) {
            if(in_array($key, $this->fields['revision_data'])) {
               $options['revision_data'] = 1;
            }
            elseif(in_array($key, $this->fields['generic_resources'])) {
               $options['generic_resources'] = 1;
            }
            elseif(in_array($key, $this->fields['chn_resources'])) {
               $options['chn_resources'] = 1;
            }
            elseif(in_array($key, $this->fields['chafen_resources'])) {
               $options['chafen_resources'] = 1;
            }
        }
        return $options;
    }

    public function add_or_update_revision(&$info) {
        // do nothing for now
        $CI = &get_instance();
        $db = $CI->load->database('', TRUE);
        $CI->load->model('common/game_model');
        $revision_id = $info['revision_id'];
        if($revision_id) { 
            $result = $CI->game_model->update_revision($revision_id, $info);
        }else{
            unset($info['revision_id']);
            $info['is_published'] = 0; // 默认为未发布
            $result = $CI->game_model->add_revision($info);
            if($result) {
                 // 更新成功 要刷新 revision_id 不然后续的操作无法进行
                 $info['revision_id'] = $result;
            }else{
                return FALSE;
            }
        }
        return $result;
    }

    /**
     * 应用渠道信息
     */
    public function apply_chnres($info) {
        $CI = &get_instance();
        $db = $CI->load->database('', TRUE);
        $CI->load->model('common/game_model');
        $revision_id = $info['revision_id'];
        $channel_id = $info['channel_id'];
        if($revision_id) {
            return $CI->game_model->update_channel_resources($revision_id,$channel_id, $info);
        }else{
            return FALSE;
        }
    }

    /**
     * 应用差分信息
     */
    public function apply_chafen($revision_id, $chafen_resources) {
        $CI = &get_instance();
        $db = $CI->load->database('', TRUE);
        $data = array();
        foreach($chafen_resources as  $chafen) {
            $data[] = array(
                'revision_id' => $revision_id,
                'chafen_url' => dirname($chafen['chafen_url']) . 'STG/' . basename($chafen['chafen_url']),
                'support_ver_code' => $chafen['support_ver_code'],
                'support_package_ver_code' => $chafen['support_package_ver_code'],
            );
        }
        $result = $db->insert_batch('cp_game_revision_chafen', $data);
        if($result) {
            return $result;
        }else{
            return FALSE;
        }
    }

    /**
     * 根据 info 及本机的目录占用情况重新调整资源
     *
     * 重新调整 file_dir, res_file_dir 等资源的分布
     *
     * @return 返回调整后的 info
     */
    public function rearrange_resource(&$info, $fixdir = FALSE) {
        $resource_keys = array(
            "apk_download_url",
            "icon_url",
            "mdf_url",
            "manifest_url",
            "bg_picture",
            "bg_music",
            "chafen_url",
            "res_icon_url",
            "res_bg_picture",
            "res_bg_music",
            "res_apk_download_url",
            "manifest_json_url",
            //"channel_config_url", // CDN 的目录不用变
        );
        $dir_keys = array(
            "file_dir",
            "apk_file_dir",
            "cpk_file_dir",
            "res_file_dir",
        );

        # 19836 游戏同步到了游戏名而不是版本的游戏名
        $info['game_name'] = $info['rev_game_name'];
        // 暂时没有增加逻辑
        $info['res_apk_download_url'] = $info['apk_download_url'];
        $info['apk_download_url'] = $info['rev_apk_download_url'];
        if(empty($info['apk_download_url'])) {
            $info['apk_download_url'] = $info['res_apk_download_url'];
        }
        $info['res_file_dir'] = dirname($info['res_apk_download_url']);
        $info['apk_file_dir'] = dirname($info['apk_download_url']);
        $info['apk_name'] = basename($info['apk_download_url']);
        $info['package_name'] = preg_replace('/\.apk$/','', basename($info['apk_download_url']));

        if($fixdir ) {
            foreach($dir_keys as $key) {
                if($info[$key]) {
                    $info[$key] = rtrim($info[$key], '/') . $this->magic_suffix;
                }
            }
            foreach($resource_keys as $key) {
                if($info[$key]) {
                    $basename = basename($info[$key]);
                    $dir      = dirname($info[$key]);
                    $info[$key] = $dir . $this->magic_suffix . '/' . $basename;
                }
            }
        }
    }

    /**
     */
    public function diff($local, $remote, $option=array()) {
        $diff = array();
        if(!isset($option) || empty($option)) { 
            $option = array(
            'chn_resources' => 1,
            'generic_resources' => 1,
            'revision_data' => 0,
            );
        }
        if ($local && $remote) {
            // 游戏资源就算值一样，文件也可能不一样，要提示用户
            if($option['generic_resources']) {
                foreach($this->fields['generic_resources'] as $key) {
                    if($local[$key] == '' && $remote[$key] == '') {
                        // continue
                    }else{
                        $diff[$key] = array(
                            $local[$key], 
                            $remote[$key], 
                        );
                    }
                }
            }
            if($option['chn_resources']) {
                foreach($this->fields['chn_resources'] as $key) {
                    if($local[$key] == '' && $remote[$key] == '') {
                        // continue
                    }else{
                        $diff[$key] = array(
                            $local[$key], 
                            $remote[$key], 
                        );
                    }
                }
            }
            if($option['revision_data']) {
                foreach($local as $key => $val) {
                    if(!in_array($key, array('game_id', 'revision_id'))) {
                        if($local[$key] != $remote[$key]) {
                            $diff[$key] = array(
                                $local[$key], 
                                $remote[$key], 
                            );
                        }
                    }
                }
            }

            return $diff;
        }else{
            return FALSE;
        }
    }

    /**
     * 根据 options 保存 chnres 和 generic resources
     *
     * batch_put 根据目录同步， 这里同步单个资源
     */
    public function queue_resources($info, $options) {
        if(!$option['generic_resources']) {
        }
        if(!$option['chn_resources']) {
        }
    }

    public function ping() {
        return 'pong';
    }

    public function scan_resources($manifest_url)
    {
        //echo "开始处理cpk文件<br>\n";
        $result_array = array();
        $xmlstr = file_get_contents($manifest_url);
        if(!$xmlstr) {
            return FALSE;
        }
        $resource_map = array();
        $cpk_list = array();
        // TODO : handle exception
        $xml = simplexml_load_string($xmlstr);

        foreach($xml->scene as $obj) {
            $key = $obj->attributes();
            array_push($cpk_list, (string) $key);
            foreach($obj->res as $val) {
                // $key = scene_b001.cpk $val = bg_123.jpg 
                $resource_map[(string)$val] = (string) $key; 
            }   
        }
        $result_array['resource_map'] = $resource_map; 
        $result_array['cpk_list'] = $cpk_list; 
        return $result_array;
    }

    public function fixresourcemap($revision_info, $manifest_url) {
            $CI = & get_instance();
            $CI->load->model('game_management/cp_game_info_model');
            $db = $CI->load->database('',True);

            $redis =  Redis_Manager::get_instance();
            $ccp_redis = $redis->Redis;
            
            $is_published = TRUE;  // 只能是已发布的
            if(empty($revision_info) ||
                empty($revision_info['cpk_file_dir']) ||
                empty($revision_info['manifest_url']) || 
                empty($revision_info['revision_id'])) {
                    return FALSE;
                }
            $revision_id = $revision_info['revision_id'];

            $game_info = $CI->cp_game_info_model->get_game_detail($revision_info['game_id']);

            if($game_info['game_mode'] == '0') {
                return FALSE; 
            }

            $result_array = $this->scan_resources($manifest_url);
            $t = time();
            if($revision_id && !empty($result_array)) { // Fixed bug when updating revisions
                $sql="delete from cp_game_revision_resources where revision_id=?";
                $result = $db->query( $sql, array( $revision_id,));
                foreach($result_array['cpk_list'] as $cpk) {
                    $resource_pack_id = basename($cpk,'.cpk');
                    $resource_pack_id = str_replace($resource_pack_id, 'scene_a','1');
                    $resource_pack_id = str_replace($resource_pack_id, 'scene_b','2');
                    $resource_pack_id = str_replace($resource_pack_id, 'scene_c','3');
                    // 注意：game_id 映射为 revision_id
                    // TODO: 改为 insert batch
                    $sql="insert into cp_game_revision_resources (revision_id,package_ver_code,resource_pack_id, resource_pack_name,resource_pack_map,scene_desc,resource_url,create_time,modify_time,opt_id) VALUES (?,?,?,?,?,?,?,?,?,?)"; // TODO: 游戏版本支持
                    $result = $db->query( $sql, array( $revision_id,
                                                $revision_info['package_ver_code'],
                                                $resource_pack_id,
                                                $cpk,
                                                $revision_info['manifest_url'],
                                                $revision_info['game_name'],
                                                $revision_info['cpk_file_dir'] .'/'.  $cpk,
                                                $t, $t, 0 ));
                    if(!$result){
                        return FALSE;
                    }
                }
                $hash_key = "HASH_GM_".$game_info['package_name']."_".$revision_info['hot_versioncode'];
                if(array_key_exists('resource_map', $result_array)) {
                    foreach($result_array['resource_map'] as $key => $val){
                        try{
                            $ccp_redis->hSet ( $hash_key, $key, $val);
                        }catch(Exception $e){
                            echo "Redis Access Error, Please contact Admin"; die();
                        }
                    }
                }
            }
            return $hash_key;
    }
}
// EOF
