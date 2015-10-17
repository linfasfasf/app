<?php
class test_cocosplay_notification extends CodeIgniterUnitTestCase{
    function __construct() {
        parent::__construct('notification');
        $this->load->library('common/cocosplay_notification');
    }

    function test_setmsg() {
        $testdata = array(
            'test msg ' . time(),
            'INFO', 
            '1', 
            time(),
        );
        $this->cocosplay_notification->deleteOldMsgs(4,0);
        $msgid = $this->cocosplay_notification->setMsg(4, $testdata, 'user');
        $this->assertTrue($msgid);
        $msgs = $this->cocosplay_notification->getMsgsUser(4);
        $this->assertEqual(count($msgs), 1);
        foreach($msgs as $key => $val) {
            $this->cocosplay_notification->deleteMsgUser(4, $key, TRUE);
        }
        $msgs = $this->cocosplay_notification->getMsgsUser(4);
        $this->assertFalse($msgs);
        $msgid = $this->cocosplay_notification->setMsg(4, $testdata, 'user');
        $msgid = $this->cocosplay_notification->setMsg(4, $testdata, 'user');
        $this->cocosplay_notification->deleteOldMsgs(4,0);
        $msgs = $this->cocosplay_notification->getMsgsUser(4);
        $this->assertFalse($msgs);
    }
}
