<?php
class test_game_management_revisionhandler_controllers extends CodeIgniterWebTestCase
{
	protected $rand = '';

	public function __construct()
	{
        parent::__construct('game_management/manifestversion');
        $this->load->helper('url');
        $this->debug = TRUE;
        $this->host = site_url();
	}

	public function setUp()
	{
    }
}
// EOF
