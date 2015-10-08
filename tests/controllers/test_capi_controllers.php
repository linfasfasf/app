<?php
class test_capi_controllers extends APIWebTestCase
{
	protected $rand = '';

	public function __construct()
	{
		parent::__construct('CAPI');
        $this->load->helper(array('url','simple_test/simple_test'));
	}

	public function setUp()
	{
        $this->test_data = get_test_data_template();
        // 填充测试测据
        $result = setup_test_data($this->test_data);
        $this->game_id = $result['game_id'];
        $this->revision_id = $result['revision_id'];
        $this->chn_game_id = $result['chn_game_id'];
        $this->pkg=$this->test_data['package_name'];
        $this->mode= $this->test_data['game_mode']=='0' || $this->test_data['game_mode']=='2'?0:1;
        $this->ver= $this->test_data['package_ver_code'];
        $this->ver_last = $this->test_data['ver_last'];
    }

    public function tearDown()
	{
        $this->load->model('game_management/cp_game_info_model');
        $this->load->model('game_management/cp_chn_game_info_model');
        // 删除测试测据
        //$result = $this->cp_game_info_model->delete_game($this->game_id);
        $result = $this->cp_game_info_model->where(array('package_name'=>'test.package.name.joe'))->delete();
        $this->cp_chn_game_info_model->delete($this->chn_game_id);
        $this->cp_game_revision_info_model->delete($this->revision_id);
    }

    public function test_welcome()
    {
        $page = $this->get(site_url());
        $this->assertTrue($page);
    }

   // /capi/api/switchinfo 
    public function test_switchinfo()
    {
        $uri = '/capi/api/switchinfo';
        $url = site_url( $uri );
        $response = $this->get($url);
        $this->assertPattern('/\{"result":\{"res":"0","msg":"ok"\},"data":\{"debug":"."\}\}/');
    }
    // 3.1.	/capi/api/channelgamelist
    public function test_channelgamelist()
    {
        $uri = '/capi/api/channelgamelist';
        $url = site_url( $uri );
        $response = $this->get($url);
        $this->assertText('Parameter error');
        $this->assertText('302');

        $requestparams = array(
            'chn'=>'111111'
        );
        $requeststring = $this->request_builder($requestparams);
        $response = $this->get($url.'?'.$requeststring);
        $this->assertText('download_url');
    }

    //* capi/api/gamepackage
    function test_gamepackage_nochannel(){
        $uri = '/capi/api/gamepackage';
        $response = $this->send_request($uri, array(), 'get');
        $this->assertText('303');
        $this->assertText('Method error');

        $requestparams = array(
            'pkg'=>$this->pkg,
            'mode'=> $this->mode,
            'ver'=> $this->ver,
        );
        $response = $this->send_request($uri, $requestparams, 'get');
        $this->assertText('download_url');
    }
    //* capi/api/gamepackage
    function test_gamepackage(){
        $uri = '/capi/api/gamepackage';
        $response = $this->send_request($uri, array(), 'get');
        $this->assertText('303');
        $this->assertText('Method error');

        $requestparams = array(
            'pkg'=>$this->pkg,
            'mode'=> $this->mode,
            'ver'=> $this->ver,
            'chn' =>  $this->test_data['channel_id'], 
        );
        $response = $this->send_request($uri, $requestparams, 'get');
        $this->assertText('download_url');
        $this->assertText('full_game_download_url');
    }

    //* capi/api/gamepackage
    // 测试试用包能否获取
    function test_gamepackage_trial(){
        $uri = '/capi/api/gamepackage';
        $test_data = get_test_data_template();
        $test_data['game_mode'] = '0';
        $test_data['package_name'] = $test_data['package_name'].'.abc';
        // 填充测试测据
        $result = setup_test_data($test_data);
        $game_id = $result['game_id'];
        $revision_id = $result['revision_id'];
        $chn_game_id = $result['chn_game_id'];
        $requestparams = array(
            'pkg'=>$test_data['package_name'], 
            'mode'=> '0',
            'ver'=> $test_data['package_ver_code'],
        );
        $response = $this->send_request($uri, $requestparams, 'get');
        $this->assertText('download_url');
        //clean up
        $this->load->model('game_management/cp_game_info_model');
        $this->load->model('game_management/cp_chn_game_info_model');
        $result = $this->cp_game_info_model->delete_game($game_id);
        $this->cp_chn_game_info_model->delete($chn_game_id);
    }

    function test_gamepackagedir(){
        $uri = '/capi/api/gamepackagedir';
        $response = $this->send_request($uri, array(), 'get');
        $this->assertText('303');

        $requestparams = array(
            'pkg'=>$this->pkg,
            'mode'=> $this->mode,
            'ver'=> $this->ver,
        );

        $this->setMaximumRedirects(0);
        $response = $this->send_request($uri, $requestparams, 'get');
        $this->assertResponse(302);
    }
    function test_manifestdir(){
        $uri = '/capi/api/manifestdir';
        $response = $this->send_request($uri, array(), 'get');
        $this->assertText('303');

        $requestparams = array(
            'pkg'=>$this->pkg,
            'mode'=> $this->mode,
            'ver'=> $this->ver,
        );

        $this->setMaximumRedirects(0);
        //echo site_url($uri) . '?' . $this->request_builder($requestparams);
        $response = $this->send_request($uri, $requestparams, 'get');
        $this->assertResponse(302);
        $uri = '/capi/api/manifest';
        $response = $this->send_request($uri, $requestparams, 'get');
        $this->assertText('manifest_url');
    }
    /* 
    function test_resourcedir(){
        $uri = '/capi/api/resourcedir';
        $response = $this->send_request($uri, array(), 'get');
        $this->assertText('303');

        $requestparams = array(
            'pkg'=>'org.yileweb.legend.UC',
            'mode'=>1,
            'ver'=>'340',
            'rsn' => 'assets/res/sound/6000.mp3', 
        );

        $this->setMaximumRedirects(0);
        $response = $this->send_request($uri, $requestparams, 'get');
        $this->assertResponse(302);
    }
     */
    function test_musicdir(){
        $uri = '/capi/bg/musicdir';
        $response = $this->send_request($uri, array(), 'get');
        $this->assertText('303');

        $requestparams = array(
            'pkg'=>$this->pkg, 
            'mode'=>1,
            'ver'=> $this->ver,
        );

        $this->setMaximumRedirects(0);
        $response = $this->send_request($uri, $requestparams, 'get');
        $this->assertResponse(302);
    }
    function test_cpkresourcedir(){
        $uri = '/capi/api/cpkresourcedir';
        $requestparams = array(
            'pkg'  => $this->test_data['package_name'],
            'mode' => $this->test_data['game_mode'],
            'ver'  => $this->test_data['package_ver_code'],
            'cpk'  => $this->test_data['resource_pack_name'],
        );
        $this->setMaximumRedirects(0);
        $response = $this->send_request($uri, $requestparams, 'get');
        $this->assertResponse(302);

        //$requestparams['ver']= '1';
        $requestparams['appv']= '2.0';

        $uri = '/capi/api/cpkresourcedir';
        $response = $this->send_request($uri, $requestparams, 'get');
        $this->assertText('312'); // 不兼容
    }
    function test_updatechafendir(){
        $uri = '/capi/api/updatechafendir';
        $response = $this->send_request($uri, array(), 'get');
        $this->assertText('303');

        $requestparams = array(
            'pkg'=>$this->pkg, 
            'mode'=>$this->mode,
            'oldv'=> $this->ver_last,
        );

        $this->setMaximumRedirects(0);
        $response = $this->send_request($uri, $requestparams, 'get');
        $this->assertResponse(302);
    }
    function test_manifestver(){
        $uri = '/capi/api/manifestver';
        $response = $this->send_request($uri, array(), 'get');
        $this->assertText('303');
        $this->assertText('Method error');

        $requestparams = array(
            'pkg'=>$this->pkg,
            'mode'=> $this->mode,
            'ver'=> $this->ver,
            'chn' =>  $this->test_data['channel_id'], 
        );
        $response = $this->send_request($uri, $requestparams, 'get');
        $this->assertText('"manifest_version":"2"');
    }
}

/* End of file test_users_model.php */
/* Location: ./tests/models/test_users_model.php */
