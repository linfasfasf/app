<?php

class zhihu extends Admin_Controller{
    
    public function __construct() {
        parent::__construct();
        $this->load->model('zhihu_info_model');
        $this->load->model('zhihu_content_info_model');
    }
    
    public function getjson($url){
        if(! $url){
            return FALSE;
        }
        $info = file_get_contents($url);
        if(!$info){
            return FALSE;
        }
        $info_decode = json_decode($info, TRUE);
        return $info_decode;
    }
    
    public function get_list(){
        $latesturl = 'http://news-at.zhihu.com/api/4/news/latest';
        $getinfo   = $this->getjson($latesturl);
        if(!$getinfo){
            log('404 not info '.$latesturl);
        }
        $stories    = array();
        $date       = $getinfo['date'];
        $stories    = $getinfo['stories'];
        $exist      = $this->zhihu_info_model->check_list_exist($date);
        if( !$exist){
            foreach ($stories as $story){
                $imgurl     = $story['images'];
                $type       = $story['type'];
                $articleid  = $story['id'];
                $artitle    = $story['title'];
                $result=$this->zhihu_info_model->add_list($imgurl,$type,$articleid,$artitle,$date);
                if(!$result){
                    $debug = $this->db->error();
                    $this->log($debug);
                    return FALSE;
                }
            }
            return TRUE;
        }
        return  FALSE;
    }
    /**
     * 每个更新，更新list以及content
     */
    public function daily_update(){
        $get_list = $this->get_list();
        if(!$get_list){
            die('do not need update');
        }
        $update_result = $this->update_content();
        if(!$update_result){
            echo 'update fail';
        }
        echo 'update success ';
        
    }
    /**
     *   添加content 到数据库
     * @param type $articleid
     * @return boolean
     */
    public function add_content($articleid){
        if(!$articleid ){
            return FALSE;
        }
        if(is_array($articleid)){
            $articleid = implode($articleid);
            $url = 'http://news-at.zhihu.com/api/4/news/'.$articleid;
        }  else {
            $url = 'http://news-at.zhihu.com/api/4/news/'.$articleid;
        }
        $get_info = $this->getjson($url);
        if(!$get_info){
            log('get content fail '.$url);
            return FALSE;
        }
        
        $body           = $get_info['body'];
        $image_source   = $get_info['image_source'];
        $imageurl       = $get_info['image'];
        $cssurl         = $get_info['css'];
        $articleid      = $get_info['id'];
        $check = $this->zhihu_content_info_model->check_content_exist($articleid);
        if(!$check){
            $result=$this->zhihu_content_info_model->add_content($body,$image_source,$imageurl,$cssurl,$articleid);
            if(!$result){
                $debug=  $this->db->error();
                log($debug);
                return FALSE;
            }
            return TRUE;
        }
        return FALSE;//content已存在，添加失败
    }
    
    /*
     * 更新文本内容
     */
    public function update_content(){
        $check_update=$this->zhihu_content_info_model->check_update();
        if($this->db->error()['code']!=0){
            log($check_update);
            return FALSE;
        }
        if($check_update==NULL){
            echo 'do not need update';
        }
        foreach ($check_update as $articleid){
            $result=$this->add_content($articleid);
            if(!$result){
                return FALSE;
            }
        }
        return TRUE;
    }

    public function get_article_info($articleid){
        
    }
    
    public function test_get(){
        $articleid = '7106194';
        $result = $this->zhihu_content_info_model->get_content($articleid);
//        header('Content-type: image/jpeg');
        $pic=file_get_contents($result[0]['imageurl']);
        echo $pic;
        
//                    $uinfo = parse_url($pic);//解析URL地址，比如http://www.metsky.com/archives/1.html
//            if($uinfo['path']) //
//                $data = $uinfo['path'];//这里得到/archives/1.html
//            else
//                $data = '/';//默认根
//            if(!$fsp = @fsockopen($uinfo['host'], (($uinfo['port']) ? $uinfo['port'] : "80"), $errno, $errstr, 12)){
//                echo "对不起对方网站暂时无法打开，请您稍后访问：".$uinfo['host'];    exit;
//            }else{
//                fputs($fsp, "GET “.$data .” HTTP/1.0\r\n");//如果是跨站POST提交，可使用POST方法
//                fputs($fsp, "Host: ".$uinfo['host']."\r\n");
//                fputs($fsp, "Referer: http://www.zhihu.com\r\n");//伪造REFERER地址
//                fputs($fsp, "User-Agent: Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)\r\n\r\n");
//                $res='';
//                while(!feof($fsp))  {
//                    $res.=fgets($fsp, 128);
//                    if(strstr($res,"200 OK")) {
//                        header("Location:$url"); exit;
//                    }
//                }
//            }
    }

    public function get_content(){
        $articleid =  $this->input->get('id');
        $articleid = intval($articleid);
        if(empty($articleid)){
            return FALSE;
        }
        $result=$this->zhihu_content_info_model->get_content($articleid);
        if(preg_match_all("/[a-zA-z]+\:\/\/[^\s]*\.jpg/", $result[0]['body'], $matches)){
            $picurl=$matches[0];
            for($i=0;$i<count($picurl);$i++){
                
                if(preg_match('/pic.*\.com/', $picurl["$i"], $servername)){
                    $file_name = str_replace("http://".$servername[0], '', $picurl["$i"]);
                   if(preg_match('/http/', $file_name)){
                        $file_name = str_replace("https://".$servername[0], '', $picurl["$i"]);
                    }
                    $file_name =  $this->format_url($file_name);
                    $fcpath = FCPATH;
                    $fcpath = $this->format_url($fcpath);
                    $filearr[] = 'http://app.com/pic/'.$file_name;
                    $file   = $fcpath.'/pic/'.$file_name;
                    if(!file_exists($file)){
                        $this->get_pic($picurl["$i"], $file,TRUE);
                    }
                }
            }
            for($i=0;$i<count($matches[0]);$i++){
                $preg_url = $this->preg_format($matches[0]["$i"]);
                $result[0]['body']= ereg_replace($preg_url, $filearr["$i"], $result[0]['body']);
            }
        }
        
        $this->load->view('getcontent',array('zhihu'=>$result[0]));
    }
    
    
    /*
     * 格式化url地址
     */
    public function format_url($url){
//        echo $url;
        $url = str_replace('\\', "/", $url);
        $url = trim($url, '/');
        return $url;
    }
    
    
    /**
     * 
     * @param type $url 图片url
     * @param type $file 图片存储地址
     */
    public function get_pic($url,$file,$is_https=FALSE){
        
        if($is_https){
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: image/jpeg'));
            curl_setopt($ch, CURLOPT_REFERER, 'http://www.zhihu.com/');
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            $pic = curl_exec($ch);
            $file = fopen($file,"w+");
            fwrite($file, $pic);
            fclose($file);
        }  else {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: image/jpeg'));
            curl_setopt($ch, CURLOPT_REFERER, 'http://www.zhihu.com/');
            $pic = curl_exec($ch);
            $file = fopen($file,"w+");
            fwrite($file, $pic);
            fclose($file);
        }
    }

    public function show_list(){
        $page = $this->input->get(page);
        if(is_null($page)){
            $page = 1;
        }
        $page_search=$page-1;
        $start = intval($page_search*10);
        if(!is_numeric($start)){
            log(' show_list $limit error');
            return FALSE;
        }
        $info = $this->zhihu_info_model->show_list($start);
        for($i=0;$i<count($info);$i++){
             if(preg_match('/pic.*\.com/', $info["$i"]['imageurl'], $servername)){
                 
                    $file_name = str_replace("http://".$servername[0], '', $info["$i"]['imageurl']) ;
//                    $file_name = str_replace("https://".$servername[0], '', $info["$i"]['imageurl']) ;
                    $file_name =  $this->format_url($file_name);
                    $fcpath = FCPATH;
                    $fcpath = $this->format_url($fcpath);
                    $filearr[] = 'http://app.com/pic/'.$file_name;
                    $file   = $fcpath.'/pic/'.$file_name;
                    if(!file_exists($file)){
                        $this->get_pic($info["$i"]['imageurl'], $file);
                    }
                }
        }
        for($i=0;$i<count($info);$i++){
            $info["$i"]['imageurl'] = $filearr["$i"];
        }
        
        $total = $this->zhihu_info_model->get_list_count();
    $this->load->view('showlist',array('list'=>$info,'total'=>  intval($total['COUNT(id)']),'current_page'=>$page));
    }

    /**
     * 
     * @param type $preg 输入正则表达式url
     * @return 返回图片url 的正则表达匹配
     */
    public function preg_format($preg){
        if(!$preg){
            return FALSE;
        }
        $search = array('\;','\/\/','\.jpg');
        $replace = array('\:','\/\/','\.jpg');
        $preg = ereg_replace("\:", "\:", $preg);
        $preg = ereg_replace("\/", "\/", $preg);
        $preg = ereg_replace("\.jpg", "\.jpg", $preg);
        return $preg;
    }

    protected function log($data,$post=FALSE){
        if(!$data){
            return;
        }
        $config  = $this-> config ->item('log');
        $dir     = $config['log_dir'];
        $filename= 'zhihu.log';
        $date_day  = date('Y:m:d');
        $date_now  = date('H:i:s');
        $file      = $dir.'/'.$filename.$date;
        error_log($date_now.$data . '/n', 3, $file);
        
        if($post){
            foreach ($post as $value){
                error_log($value .'/n', 3, $file);
            }
        }
    }
    
    
    public function maopao(){
        $arr = array(1,3,43,34,756,24,3,344,33,544);
        for($i=10;$i>=0;$i--){
            $k = 0;
            for($j=$k+1;$j<$i;$j++){
                echo $k.' '.$j."  ";
                if($arr[$k]>=$arr[$j]){
                    $tmp = $arr[$j];
                    $arr[$j] = $arr[$k];
                    $arr[$k] = $tmp;
                }
                echo $arr[$k]. ' '.$arr[$j].'-----------';
                $k++;
            }echo '@@@@@@@@@@@@@@';
        }
        var_dump($arr);
    }
}
