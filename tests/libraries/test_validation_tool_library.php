<?php
class test_api_validation extends CodeIgniterWebTestCase
{
	protected $rand = '';

	public function __construct()
	{
		parent::__construct('');
        $this->load->library('capi/api_validation');
	}

	public function setUp()
	{
    }

    public function tearDown()
	{
    }

    public function test_validation_required()
    {
        $Validation = new Api_Validation();
        $config = array ('abc'=> 'required');
        $testobject = array('efg'=> 33);
        $result = $Validation->run($config, $testobject);
        $this->assertEqual(TRUE, array_key_exists('abc', $result));

        $testobject = array('efg'=> 33, 'abc'=>'');
        $this->assertEqual(TRUE, array_key_exists('abc', $result));

        $testobject = array('efg'=> 33, 'abc'=>'ab');
        $result = $Validation->run($config, $testobject);
        $this->assertEqual(FALSE, array_key_exists('abc', $result));
    }
    public function test_validation_numeric_allow_empty()
    {
        $Validation = new Api_validation();
        $config = array ('abc'=> 'numeric');
        $testobject = array('efg'=> 33, 'abc'=>'');
        $result = $Validation->run($config, $testobject);
        $this->assertEqual(FALSE, array_key_exists('abc', $result));

    }
    public function test_validation_numeric()
    {
        $Validation = new Api_validation();
        $config = array ('abc'=> 'required|numeric');
        $testobject = array('efg'=> 33, 'abc'=>'0');
        $result = $Validation->run($config, $testobject);
        $this->assertEqual(FALSE, array_key_exists('abc', $result));

        $config = array ('abc'=> 'required|numeric');
        $testobject = array('efg'=> 33, 'abc'=>'1');
        $result = $Validation->run($config, $testobject);
        $this->assertEqual(FALSE, array_key_exists('abc', $result));

        $config = array ('abc'=> 'required|numeric');
        $testobject = array('efg'=> 33, 'abc'=>'123412.998');
        $result = $Validation->run($config, $testobject);
        $this->assertEqual(FALSE, array_key_exists('abc', $result));
    }
    public function test_validation_url()
    {
        $Validation = new Api_validation();
        $config = array ('abc'=> 'required|url');
        $testobject = array('efg'=> 33, 'abc'=>'0');
        $result = $Validation->run($config, $testobject);
        $this->assertEqual(TRUE, array_key_exists('abc', $result));

        // url 必须以 http 开头
        $config = array ('abc'=> 'required|url');
        $testobject = array('efg'=> 33, 'abc'=>'http://abcde.com/');
        $result = $Validation->run($config, $testobject);
        $testobject = array('efg'=> 33, 'abc'=>'https://abcde.com/');
        $result = $Validation->run($config, $testobject);
        $this->assertEqual(FALSE, array_key_exists('abc', $result));

        // url 可以为空
        $config = array ('abc'=> 'url');
        $testobject = array('efg'=> 33, 'abc'=>'');
        $result = $Validation->run($config, $testobject);
        $this->assertEqual(FALSE, array_key_exists('abc', $result));
    }

    public function test_validation_required_unless () {
        $config = array(
            'chn' => 'required_unless[mode=4]',
        );
        $Validation = new Api_validation();
        $testobj = array(
            'chn' => 111111,
            'mode' => 99,
        );
        // 有 chn 时不检测 mode
        $result = $Validation->validation_required_unless('chn', $testobj, 'mode=4');
        $this->assertTrue($result);
        $testobj = array(
            'mode' => 99,
        );
        // 没 chn 时, 要检测 mode
        $result = $Validation->validation_required_unless('chn', $testobj, 'mode=4');
        $this->assertFalse($result);
        $testobj = array(
            'mode' => 4,
        );
        // 要检测 mode 相等时 pass
        $result = $Validation->validation_required_unless('chn', $testobj, 'mode=4');
        $this->assertTrue($result);
        $result = $Validation->run($config, $testobj);
        $this->assertTrue(empty($result));
        $testobj = array(
            'chn'=> 1234,
        );
        $result = $Validation->run($config, $testobj);
        $this->assertTrue(empty($result));
        $testobj = array(
            'mode'=> 1234,
        );
        $result = $Validation->run($config, $testobj);
        $this->assertFalse(empty($result));
    }

    public function test_validation_integer()
    {
        $Validation = new Api_validation();
        $config = array ('abc'=> 'integer');
        $testobject = array('efg'=> 33, 'abc'=>'0');
        $result = $Validation->run($config, $testobject);
        $this->assertEqual(FALSE, array_key_exists('abc', $result));

        $config = array ('abc'=> 'integer');
        $testobject = array('efg'=> 33, 'abc'=>'1');
        $result = $Validation->run($config, $testobject);
        $this->assertEqual(FALSE, array_key_exists('abc', $result));

        /**
         * 正整数
        $config = array ('abc'=> 'integer');
        $testobject = array('efg'=> 33, 'abc'=>'-1');
        $result = $Validation->run($config, $testobject);
        $this->assertEqual(FALSE, array_key_exists('abc', $result));
         */

        $config = array ('abc'=> 'integer');
        $testobject = array('efg'=> 33, 'abc'=>'1.0');
        $result = $Validation->run($config, $testobject);
        $this->assertTrue(array_key_exists('abc', $result));

        $config = array ('abc'=> 'integer');
        $testobject = array('efg'=> 33, 'abc'=>'123412.998');
        $result = $Validation->run($config, $testobject);
        $this->assertTrue(array_key_exists('abc', $result));
    }
}
