<?php

require_once(FCPATH . 'modules/capi/controllers/apiv2.php');

class Apiv2test extends Apiv2 {
    public function __construct()
    {
        parent::__construct();
    }
    public function test_()
    {
        return $this->test();
        // code...
    }
}
