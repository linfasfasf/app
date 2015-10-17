<?php
class test_json extends CodeIgniterWebTestCase
{
	protected $rand = '';

	public function __construct()
	{
		parent::__construct('testmyjson');
        $path = FCPATH .  'tests/testfile/manifest.json';
        $content = file_get_contents($path);
        $this->load->library('common/myjson', array('jsonstring' => $content));
	}

	public function setUp()
	{
    }

    public function tearDown()
	{
    }

    public function test_init() {

    }

    public function test_readjson() {
        $root = $this->myjson->json(); 
        $result = $this->myjson->readjson($root, 'res_groups');
        $result = $this->myjson->readjson($result, 'files');
        $result = $this->myjson->readjson($result, 'md5');
        $this->assertTrue($result);
    }

    public function test_readjson_wildcard() {
        $root = $this->myjson->json(); 
        $result = $this->myjson->readjson($root, 'res_groups');
        $result = $this->myjson->readjson($result, 'files');
        $result = $this->myjson->readjson($result, '*', array('name'=>'res/flare.jpg'));
        var_dump($result);
        $this->assertTrue($result);
    }

    public function test_get() {
        $path = 'res_groups/files';
        $attr =  'md5';
        //$where = array('md5' => 'c6b018aa15d5bf80ed8836248ec22577');
        $where = array('name' => 'res_engine/dialog_cancel_normal.png');
        $root = $this->myjson->json(); 
        //$root_encode = json_encode($root);
        $result = $this->myjson->get($path,$attr, $where);
        $this->assertEqual(count($result), 1);
    }

    public function test_update() {
        $path = 'res_groups/files';
        $attr =  'md5';
        //$where = array('md5' => 'c6b018aa15d5bf80ed8836248ec22577');
        $val = '123412341234';
        $where = array('name' => 'res_engine/dialog_cancel_normal.png');
        $root = $this->myjson->json(); 
        $result = $this->myjson->update($path, $attr, $val, $where);
        $this->assertEqual(count($result), 1);
    }
}
