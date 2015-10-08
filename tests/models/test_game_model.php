<?php
class test_game_model extends CodeIgniterUnitTestCase
{
    public function __construct()
    {
        parent::__construct('game_model');
        $this->load->model('common/game_model');
    }

    public function setUp()
    {
    }

    public function tearDown()
    {
    }

    function test_add_game_slot() {
        $this->game_model->add_game_slot();
        echo $this->game_model->game_id;
        $this->game_model->delete();
    }

    function test_add_game() {
        $game_name = 'my_game_name';
        $data = array(
            'game_name' => $game_name,
        );
        $this->game_model->add_game($data);
        echo $this->game_model->game_id;
        $this->assertEqual( $this->game_model->game_key, $this->game_model->get_field('game_key'));
        $this->game_model->delete();
    }
}
//EOF
