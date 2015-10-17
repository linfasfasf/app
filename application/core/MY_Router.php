<?php
(defined('BASEPATH')) or exit('No direct script access allowed');

/* load the HMVC_Router class */
require APPPATH . 'third_party/HMVC/Router.php';

class MY_Router extends HMVC_Router {
}

function autoload($class) {
    
    /* don't autoload CI_ prefixed classes or those using the config subclass_prefix */
    if (strstr($class, 'CI_') or strstr($class, config_item('subclass_prefix'))) return;
    
    /* autoload core classes */
    if(is_file($location = APPPATH.'core/'.$class.EXT)) {
        include_once $location;
        return;
    }		
    
    /* autoload library classes */
    if(is_file($location = APPPATH.'libraries/'.$class.EXT)) {
        include_once $location;
        return;
    }		
}

spl_autoload_register('autoload');
