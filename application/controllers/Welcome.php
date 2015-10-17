<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Welcome extends CI_Controller {

	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see http://codeigniter.com/user_guide/general/urls.html
	 */
         
        public function __construct() {
            parent::__construct();
            $this->load->model('liuhe_info_model');
        }




        public function index()
	{
            $this->load->view('six/index');
	}
        
        public function get_live(){
            $this->load->view('live');
        }
        
        public function get_msg($cid =136){
            $cid=array(136,139,124,132,135,134,130,122,112,115,50,36,4,28,120,131,117,118,114,113,
                137,133,9,127,126,31,123,116,109,25,22,129,128,100,106,101,102,103,104,47,48,66,61,53,62,57,60);
            if(isset($_GET['cid'])){
                $val = $_GET['cid'];
            }
            $p = 1;
            
            do{
                $result=$this->get_articleid($p,$val);
                $p++;
            
            }
            while ($result);
            
        }
       

        public function get_articleid($p=1,$cid=136){
            $period = $this->liuhe_info_model->get_period();
            $ch = curl_init('http://111.tequ.me/zl1.aspx?cid='.$cid.'&p='.$p);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $output = curl_exec($ch);
            var_dump($output);
            if(preg_match_all("/\?id=\d*/", $output,$match)){//匹配文章id
                var_dump($match);
                foreach ($match[0] as $val){
                    $id = substr($val, 4);
                    $article_id = intval($id);
                    var_dump($article_id) ;
                    $preg = '/id='.$article_id.'&amp.*?<\/a>/';
                    if(preg_match($preg, $output, $matche_title)){ //匹配文章标题
                        $title = substr($matche_title[0], 22);
                        $title = substr($title, 0,-4);
                        var_dump($title);
                    }
                    
                    if(!$this->liuhe_info_model->check_article_exist($article_id)){
                        $this->get_article($article_id,$cid,$title,$period);
                    }
                }
                return TRUE;
            }
            return FALSE;
        }
        
        
        public function get_article($id,$cid=139,$title,$period){
            $ch = curl_init('http://111.tequ.me/view1.aspx?id='.$id.'&cid='.$cid);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $output = curl_exec($ch);
            var_dump($output);
            if(preg_match("/<p>.*<\/p>*/", $output,$match)){
                var_dump($match);
                $info = $this->liuhe_info_model->save_info($match[0],$id,$title,$cid,$period);
                if(!$info){
                    echo 'fail'.$info;
                }
                echo 'success';
            }
        }
        
        
        public function show_title(){
            $cid = $_GET['cid'];
            $p = 1;
            if(isset($_GET['p'])){
                $p   = $_GET['p'];
            }
            $p   = intval($p);
            $start = ($p-1)*10;
            $cid = intval($cid);
            $period = $this->liuhe_info_model->get_period();
            $period = intval($period);
            $total = $this->liuhe_info_model->get_total_num($period,$cid);
            $info = $this->liuhe_info_model->get_title($cid,$start);
            $this->load->view('six/show_msg',array('msg'=>$info,'current_page'=>$p,'total'=>$total));
        }
        
        public function show_article(){
            $cid = $_GET['cid'];
            $article_id = $_GET['id'];
            $cid = intval($cid);
            $article_id = intval($article_id);
            $up_page = $article_id-1;
            $get_up = $this->liuhe_info_model->get_other_article($up_page);
            $down_page = $article_id+1;
            $get_down = $this->liuhe_info_model->get_other_article($down_page);
            $result = $this->liuhe_info_model->get_article($article_id);
            $this->load->view('six/show_article',array('result'=>$result,'up'=>$get_up,'down'=>$get_down));
        }
        
        
        

        public function get_date(){
            $ch = curl_init('http://111.tequ.me/zl.aspx?cid=74');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $result = curl_exec($ch);
            if(preg_match("/'>.*?<\/a>/", $result,$match)){
                $date = substr($match[0], 2,3);
                $period = intval($date);
                if($this->liuhe_info_model->update_period($period)){
                    echo 'update success';
                }
                echo 'fail';
            }
        }
}
