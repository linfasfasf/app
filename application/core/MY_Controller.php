<?php
class My_Controller extends CI_Controller
{

    /**
     * The name of the module that this controller instance actually belongs to.
     *
     * @var string
     */
    public $module;

    /**
     * The name of the controller class for the current class instance.
     *
     * @var string
     */
    public $controller;

    /**
     * The name of the method for the current request.
     *
     * @var string
     */
    public $method;

    /**
     * Load and set data for some common used libraries.
     */
    public function __construct() {
        parent::__construct();
        //语言包设置
        $web_lang = isset($_GET['lang']) ? trim($_GET['lang']) : '';
    }

    function loadLanguageFile($filename, $language='zh') {
        $langfolder = 'zh';//初始化
        switch ($language){
        case 'ko'://加载英文文件
            $langfolder = 'ko';
            break;
        case 'en'://加载中文文件
            $langfolder = 'en';
            break;
        default:
            $langfolder = 'zh';
        }
        $this->lang->load($filename, $langfolder);
    }
}

/**
 * Returns the CodeIgniter object.
 *
 * Example: ci()->db->get('table');
 *
 * @return \CI_Controller
 */
function ci()
{

    static $instance;

    if(is_object($instance)) return $instance;

    if($instance = & get_instance())
        return $instance;

    $instance = new CI_Simple();

    return $instance;
}

/**
 * CI简化类，只加载需要的类
 */
class CI_Simple{

    public $load;

    public function __construct(){
        $this->load = & load_class('Loader', 'core');
    }

    public function __get($property){

        if(!isset($this->$property))
            $this->$property = & load_class(ucfirst($property));

        return  $this->$property;
    }


}
