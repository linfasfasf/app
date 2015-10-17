<?php
class test_acl_model extends CodeIgniterUnitTestCase
{
    protected $rand = '';

    public function __construct()
    {
        parent::__construct('acl_model');
        $this->load->model('acl/acl_model');
        $this->aco_list = $this->acl_model->list_nodes('aco');
        $this->aro_list = $this->acl_model->list_nodes('aro');
        $this->perm_keys = array('create', 'read', 'update', 'delete');
    }

    public function setUp()
    {
    }

    public function tearDown()
    {
    }

    public function test_locate_node() {
        $aco  = '/';
        $aro  = 'group.all';
        $aco_node = $this->acl_model->locate_node($aco, 'aco');
        $aro_node = $this->acl_model->locate_node($aro, 'aro');
        $this->assertTrue(!empty($aco_node));
        $this->assertTrue(!empty($aro_node));
    }

    public function test_add_aco_node() {
        $table = 'aco';
        $alias_group = '/testcontroller';
        $alias_member = '/testcontroller/edit';
        $cnt = $this->get_tree($table);
        $this->acl_model->remove_aco_node($alias_group);
        $root = $this->acl_model->locate_root($table);
        var_dump($root);
        $this->acl_model->add_aco_node($root['alias'],$alias_group);
        $this->acl_model->add_aco_node($alias_group, $alias_member);
        $aco_node = $this->acl_model->locate_node($alias_member, 'aco');
        $cnt2 = $this->get_tree($table);
        $this->assertTrue(!empty($aco_node));
        $this->acl_model->remove_aco_node($alias_group);
        $aco_node = $this->acl_model->locate_node($alias_member, 'aco');
        $this->assertTrue(empty($aco_node));
        $cnt3 = $this->get_tree($table);

        // 结构变化
        $this->assertTrue($cnt != $cnt2);
        $this->assertEqual($cnt, $cnt3);
    }

    public function test_add_aro_node() {
        $table = 'aro';
        $alias_group = 'group.test';
        $alias_member = 'test_group_member';
        $cnt = $this->get_tree($table);
        $this->acl_model->remove_aro_node($alias_group);
        $root = $this->acl_model->locate_root( 'aro');
        $this->acl_model->add_aro_node($root['alias'],$alias_group);
        $this->acl_model->add_aro_node($alias_group, $alias_member);
        $aro_node = $this->acl_model->locate_node($alias_member, 'aro');
        $cnt2 = $this->get_tree($table);
        $this->assertTrue(!empty($aro_node));
        $this->acl_model->remove_aro_node($alias_group);
        $aro_node = $this->acl_model->locate_node($alias_member, 'aro');
        $this->assertTrue(empty($aro_node));
        $cnt3 = $this->get_tree($table);
        $this->assertTrue($cnt != $cnt2);
        $this->assertEqual($cnt, $cnt3);
    }


    public function test_get_aco() {
    }

    public function test_get_aro() {
    }

    public function test_get_aros_foreignkey() {
        $group_id = 1; // group.admin
        $info = $this->acl_model->get_aros_foreignkey($group_id);
        $this->assertTrue(!empty($info));
        if(!empty($info)) {
            $this->assertEqual($info[0]['alias'], 'group.admin');
        }
        $group_id = 4; // group.maintenance
        $info = $this->acl_model->get_aros_foreignkey($group_id);
        $this->assertTrue(!empty($info));
        if(!empty($info)) {
            $this->assertEqual($info[0]['alias'], 'group.maintenance');
        }
    }

    public function test_get_aros_foreignkey_array() {
        $group_id = '1'; // group.admin
        $group_id2 = '4'; // group.maintenance
        $keys = array($group_id, $group_id2);

        $result = $this->acl_model->get_aros_foreignkey($keys);
        $ids = $this->acl_model->extract($result, 'foreign_key');
        $this->assertTrue(in_array($group_id, $ids, true)); 
        $this->assertTrue(in_array($group_id2, $ids, true));
    }

    /**
     * if aco undefined if will use '/' as the default setting
     */
    function test_check_nodefined_aco() {
        $default_aco = '/';
        $fake_aco = '/youdontfindthiscontroller';
        foreach($this->aro_list as $aro) {
            foreach($this->perm_keys as $perm) {
                $acc = $this->acl_model->check($aro['alias'], $fake_aco,  $perm);
                $acc2 = $this->acl_model->check($aro['alias'], $default_aco,  $perm);
                $this->assertEqual($acc, $acc2);
            }
        }
    }

    /**
     * if aro undefined if will use 'group.anonymous' as the default setting
     */
    function test_check_nodefined_aro() {
        $default_aro = 'group.anonymous';
        $fake_aro = 'group.youdontfindthis';
        foreach($this->aco_list as $aco) {
            foreach($this->perm_keys as $perm) {
                $acc = $this->acl_model->check($default_aro, $aco['alias'],  $perm);
                $acc2 = $this->acl_model->check($fake_aro, $aco['alias'],  $perm);
                $this->assertEqual($acc, $acc2);
            }
        }
    }

    function test_check_admin() {
        $admin_aro = 'group.admin';
        foreach($this->aco_list as $aco) {
            foreach($this->perm_keys as $perm) {
                $acc = $this->acl_model->check($admin_aro, $aco['alias'],  $perm);
                $this->assertEqual($acc, 1);
            }
        }
    }

    function test_check_anony() {
        $anony = 'group.anonymous';
        $disallow_list = array(
            '/acl',
            '/auth',
            '/file_management',
            '/game_management',
            '/system_management',
            '/channel_management',
        );
        foreach($this->aco_list as $aco) {
            $acc = $this->acl_model->check($anony, $aco['alias'],  '*');
            echo $aco['alias'] . ' ' . $acc; 
            if(in_array($aco['alias'], $disallow_list)) {
                $this->assertEqual($acc, 0);
            }else {
                // not sure
            }
        }
    }

    function test_allow() {
    }

    /**
     * type = 'aro'|'aco'
     */
    protected function get_tree($type) {
        ob_start();
        if($type=='aro') {
            $this->acl_model->print_aro();
        } elseif($type=='aco') {
            $this->acl_model->print_aco();
        }
        $cnt = ob_get_contents();
        ob_end_clean();
        return $cnt; 
    }
}

/* End of file test_users_model.php */
/* Location: ./tests/models/test_users_model.php */
