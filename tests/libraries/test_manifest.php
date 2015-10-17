<?php
class test_manifest extends CodeIgniterWebTestCase
{
	public function __construct()
	{
		parent::__construct('testmanifest');
        $path = FCPATH .  'tests/testfile/manifest.json';
        $content = file_get_contents($path);
        $this->load->library('common/manifest', array('jsonstring' => $content));
        $this->json_copy = unserialize(serialize($this->manifest));
	}

	public function setUp()
	{
    }

    public function tearDown()
	{
    }

    public function test_init() {

    }

    public function test_get_cpk_list() {
        $cpks = $this->manifest->get_cpk_list();
        $this->assertTrue(count($cpks));
    }

    public function test_get() {
        $path = 'testonly/n1/n12';
        $attr = 'n13';
        $result = $this->manifest->get($path,$attr);
        $this->assertTrue($result);
        $this->assertEqual($result[0] , 13);
    }

    public function test_update_md5() {
        $cpk_url = 'group/boot.cpk';
        $this->manifest->update_cpk_md5( $cpk_url, '1234'); 
        $where = array('url'=> $cpk_url);
        $result = $this->manifest->get('res_groups', 'md5', $where);
        $this->assertEqual(count($result), 1);
        $result = $this->manifest->get('res_groups', 'md5');
        $this->assertEqual(count($result), 4);
    }

    public function test_update_md5_batch() {
        $updates = array(
            'patch/boot/patch_3.cpk' => '1111',
            'group/boot.cpk' => '2222',
            'group/common.cpk' => '3333',
            'group/gamelayer.cpk' => '4444',
            'group/doesnexists.cpk' => '5555',
        );
        $this->manifest->update_cpk_md5_batch($updates);
        $json = $this->manifest->json();
        $result = $this->manifest->get_flat('res_groups/patch/*', 'md5', array('md5'=> '1111'));
        $this->assertTrue($result);
        $result = $this->manifest->get_flat('res_groups/patch/*', 'md5');
        $this->assertEqual(count($result), 2);
    }

    public function test_get_res_md5() {
        $result = $this->manifest->get_res_md5('res_engine/dialog_confirm_normal.png');
        $this->assertEqual($result, "584e1398dbf22de9c8a05d75f42a7a45");
    }
}
