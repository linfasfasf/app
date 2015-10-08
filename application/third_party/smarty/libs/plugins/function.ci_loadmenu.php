<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */

/**
 * <pre>
 * array(
 *   'name' => required name of the config properties
 *   'assign' => optional smarty variable name to assign to - defaults to name
 * )
 * </pre>
 * @param Smarty
 */
function smarty_function_ci_loadmenu($params, &$smarty)
{
        if ($smarty->debugging) {
            $_params = array();
            require_once(SMARTY_CORE_DIR . 'core.get_microtime.php');
            $_debug_start_time = smarty_core_get_microtime($_params, $smarty);
        }

        $_name = isset($params['name']) ? $params['name'] : '';
        $_assign = isset($params['assign']) ? $params['assign'] : $_name;

        if ($_name != '')
        {
            // get a Code Igniter instance
            $CI = &get_instance();
            $CI->config->load($_name, TRUE);
            $modules = $CI->config->item('modules');
            $CI->load->model('acl/acl_model');
            $CI->load->library('acl/Hash');
            $model = $CI->acl_model;
            $hash = $CI->hash;
            $hash_path = "data.{s}";
            $display_pages_with_accesslevel = 'read';
            foreach($modules as $i => &$module) {
                foreach($module['data'] as $item => $url) {
                    $urlfixed = '/'. $url;
                    if(!$model->accessible($urlfixed, $display_pages_with_accesslevel)) {
                        unset($module['data'][$item]);
                    }
                }
                unset($url);

                if(empty($module['data'])) {
                    unset($modules[$i]);
                }
            }
            unset($module);
            $smarty->assign( $_assign, $modules );
        }

        if ($smarty->debugging) {
            $_params = array();
            require_once(SMARTY_CORE_DIR . 'core.get_microtime.php');
            $smarty->_smarty_debug_info[] = array('type'      => 'config',
                                                'filename'  => $_file.' ['.$_section.'] '.$_scope,
                                                'depth'     => $smarty->_inclusion_depth,
                                                'exec_time' => smarty_core_get_microtime($_params, $smarty) - $_debug_start_time);
        }

}

/* vim: set expandtab: */

?>
