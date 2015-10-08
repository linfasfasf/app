<?php
/**
 * Please note this file shouldn't be exposed on a live server,
 * there is no filtering of $_POST!!!!
 */
error_reporting(0);

// Determines if running in cli mode
if (isset($argv))
{
	$cli_mode = setup_cli($argv);
}

/**
 * Configure your paths here:
 */
define('MAIN_PATH', realpath(dirname(__FILE__)).'/');
define('SIMPLETEST', MAIN_PATH.'tests/simpletest/'); // Directory of simpletest
define('ROOT', MAIN_PATH); // Directory of codeigniter index.php
define('TESTS_DIR', MAIN_PATH.'tests/'); // Directory of your tests.
define('APP_DIR', MAIN_PATH.'application/'); // CodeIgniter Application directory

//do not use autorun as it output ugly report upon no test run
require_once SIMPLETEST.'unit_tester.php';
require_once SIMPLETEST.'mock_objects.php';
require_once SIMPLETEST.'collector.php';
require_once SIMPLETEST.'web_tester.php';
require_once SIMPLETEST.'extensions/my_reporter.php';
require_once SIMPLETEST.'extensions/cli_reporter.php';

$test_suite = new TestSuite();
$test_suite->_label = 'CodeIgniter Test Suite';

class CodeIgniterUnitTestCase extends UnitTestCase {
	protected $_ci;

	public function __construct($name = '')
	{
		parent::__construct($name);
		$this->_ci =& get_instance();
	}

	public function __get($var)
	{
		return $this->_ci->$var;
	}
}

class CodeIgniterWebTestCase extends WebTestCase {
	protected $_ci;

	public function __construct($name = '')
	{
		parent::__construct($name);
		$this->_ci =& get_instance();
	}

	public function __get($var)
	{
		return $this->_ci->$var;
	}
}

class APIWebTestCase extends CodeIgniterWebTestCase{
    
    // 可在实例中替换成其他 host
    protected $host;

	public function __construct($name = '')
	{
		parent::__construct($name);
        $this->host = site_url();
	}

    /* request_builder 返回编码后的 get 请求
     * @param $params array('chn'=>'111111','ver'=>340)
     * @return string
         */
    public function request_builder( $params)
    {
        $newparams = array();
        foreach($params as $key=>$val)
        {
            $newparams[] = $key.'='.urlencode($val);
        }
        return implode('&', $newparams);
    }
    
    public function send_request($uri, $params, $method)
    {
        $url = rtrim($this->host, '/') . '/' . ltrim($uri, '/');
        if(!empty($params)){
            $requeststring = $url. '?'.$this->request_builder($params);
        }else{
            $requeststring = $url;
        }
        if($method=='post'){
            $response = $this->post($requeststring, $params);
        }else{
            echo $requeststring . "\n";
            $response = $this->get($requeststring);
        }
        return $response;
    }
    
    public function get_channel_game_list(){
        $this->load->model('game_management/cp_chn_game_info_model');
        $chns = $this->db->get_where("cp_channel_info",array("del_flag"=>0))->result_array();
        $fields  = array_flip(array( "chn","game_id","game_key","pkg","ver","mode","visible","old_data_flag","chn_test_duration", "revision_id"));
        $result = array();
        $i = 0;
        foreach($chns as $chn) {
            //var_dump($chn); echo "<br><br><br>";
            $chnid = $chn['channel_id'];
            $gamelist = $this->cp_chn_game_info_model->get_channel_game_list($chnid);
            foreach($gamelist as $game){
                $game_id = $game['game_id'];
                $game_key = $game['game_key'];
                $pkg = $game['package_name'];
                $ver = $game['hot_versioncode'];
                $mode = $game['game_mode'];
                $visible = $game['is_visiable'];
                $is_old_data = $game['old_data_flag'];
                $chn_test_duration = $game['chn_test_duration'];
                $revision_id = $game['revision_id'];
                $result[]= array($chnid,$game_id,$game_key,$pkg,$ver,$mode,$visible,$is_old_data,$chn_test_duration, $revision_id);
            }
            
        }
        $indep_games = $this->get_indep_games();
            foreach($indep_games as $game){
                $game_id = $game['game_id'];
                $game_key = $game['game_key'];
                $pkg = $game['package_name'];
                $ver = $game['package_ver_code'];
                $mode = 4;
                $visible = 1;
                $is_old_data = $game['old_data_flag'];;
                $chn_test_duration = -1;
                $revision_id = $game['id'];
                $chnid = 999997;
                $result[]= array($chnid,$game_id,$game_key,$pkg,$ver,$mode,$visible,$is_old_data,$chn_test_duration,$revision_id);
            }
        return array($fields, $result);
    }
           protected function game_mode_trans($game_mode)
    {
                /*              试玩包  独立包  托管包 
                 *  database      0      4      1
                 *  request       0      1      5
                 *                  */
        if(isset($game_mode) && $game_mode!==FALSE)
        {
            switch($game_mode){
                case 0:
                    return 0;
                    break;
                case 1:
                    return 5;
                    break;
                case 4:
                    return 1;
                    break; 
                default:
                    return FALSE;
            }
        }
        else
            return FALSE;
    }
    
    /**
     * 获取独立包进行测试
     */
    protected function get_indep_games()
    {
        $sql = 'select game.channel_id as old_data_flag,game.game_mode,rev.package_name,rev.package_ver_code, game.game_key,game.game_id,rev.id from cp_game_info game,cp_game_revision_info rev where game.game_id = rev.game_id and game.game_mode = 4 and is_published = 1';
        return $result = $this->db->query($sql)->result_array();
    }
}


// Because get is removed in ci we pull it out here.
$run_all = FALSE;
if (isset($_GET['all']) || isset($_POST['all']))
{
	$run_all = TRUE;
}

//Capture CodeIgniter output, discard and load system into $CI variable
ob_start();
	include(ROOT.'index.php');
	$CI =& get_instance();
ob_end_clean();

$CI->load->library('session');
$CI->session->sess_destroy();

$CI->load->helper('directory');
$CI->load->helper('form');

// Get all main tests
if ($run_all OR ( ! empty($_POST) && ! isset($_POST['test'])))
{
	$test_objs = array('controllers','controllers2','apiv3','apiv4','models','views','libraries','bugs','helpers','phpscript');

	foreach ($test_objs as $obj)
	{
		if (isset($_POST[$obj]) OR $run_all)
		{
			$dir = TESTS_DIR.$obj;
			$dir_files = directory_map($dir);
                        foreach ($dir_files as $key => $value) {
                            if(is_array($value))
                            {
                                unset($dir_files[$key]);
                            }
                        }
			foreach ($dir_files as $file)
			{
				if ($file != 'index.html')
				{
					$test_suite->addFile($dir.'/'.$file);
				}
			}
		}
	}
}
elseif (isset($_POST['test'])) //single test
{
	$file = $_POST['test'];

	if (file_exists(TESTS_DIR.$file))
	{
		$test_suite->addFile(TESTS_DIR.$file);
	}
}

// ------------------------------------------------------------------------

/**
 * Function to determine if in cli mode and if so set up variables to make it work
 *
 * @param Array of commandline args
 * @return Boolean true or false if commandline mode setup
 *
 */
function setup_cli($argv)
{
	if (php_sapi_name() == 'cli')
	{
		if (isset($argv[1]))
		{
			if (stripos($argv[1],'.php') !== false)
			{
				$_POST['test'] = $argv[1];
			}
			else
			{
				$_POST[$argv[1]] = $argv[1];
			}
		}
		else
		{
			$_POST['all'] = 'all';
		}
		$_SERVER['HTTP_HOST'] = '';
		$_SERVER['REQUEST_URI'] = '';
		return true;
	}
	return false;
}

/**
 * Function to map tests and strip .html files.
 *
 *
 * @param	string
 * @return 	array
 */
function map_tests($location = '')
{
	if (empty($location))
	{
		return FALSE;
	}

	$files = directory_map($location);
	$return = array();

	foreach ($files as $file)
	{
		if ($file != 'index.html')
		{
			$return[] = $file;
		}
	}
	return $return;
}

//variables for report
$controllers = map_tests(TESTS_DIR.'controllers');
$controllers2 = map_tests(TESTS_DIR.'controllers2');
$apiv3 = map_tests(TESTS_DIR.'apiv3');
$apiv4 = map_tests(TESTS_DIR.'apiv4');
$models = map_tests(TESTS_DIR.'models');
$views = map_tests(TESTS_DIR.'views');
$libraries = map_tests(TESTS_DIR.'libraries');
$bugs = map_tests(TESTS_DIR.'bugs');
$helpers = map_tests(TESTS_DIR.'helpers');
$tmp = map_tests(TESTS_DIR.'phpscript');
foreach ($tmp as $key => $value) {
    if(is_array($value))
    {
        unset($tmp[$key]);
    }
}
$phpscript = $tmp;
$form_url =  'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];

//display the form
if (isset($cli_mode))
{
	exit ($test_suite->run(new CliReporter()) ? 0 : 1);
}
else
{
    $CI->benchmark->mark('testset');
	include(TESTS_DIR.'test_gui.php');
    $CI->benchmark->mark('testsetend');
    echo $CI->benchmark->elapsed_time('testset', 'testsetend');
}
