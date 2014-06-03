<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * TESTING Controller
 *
 * @package		CodeIgniter
 * @author		Claudio Marrero
 * @link		http://marreroclaudio.com.ar/
 */
/**
 * @property mixed unit
 * @property mixed test_model
 */
class testInvites extends MY_Controller {

    public function __construct() {
        parent:: __construct();
        $this->load->library('unit_test');
    }

    public function index(){
        $this->testInviteFriend();
    }

    /*
     * Test de invitacion a amigos
     */
    public function testInviteFriend(){

        /////////////////// START TEST FOR REGISTER AN USER ///////////////////////////////
        $this->benchmark->mark('test_start');
        //Expected data for register user
        $prepareData = array(
            'userId'=>21,
            'message'=>'Testing Invite',
            'emails'=>array('cmarrero01@gmail.com','juanciullini@gmail.com')
        );

        $sendInvite = $this->rest->get('invites',$prepareData,'json');
        $response = (object) array('response'=>true);
        $this->benchmark->mark('test_end');
        echo $this->unit->run($sendInvite, $response, 'Send Invite to array friends','Time: '.$this->benchmark->elapsed_time('test_start', 'test_end'));
        /////////////////// END TEST FOR REGISTER AN USER ///////////////////////////////

        /////////////////// START TEST FOR REGISTER AN USER ///////////////////////////////
        $this->benchmark->mark('test_start');
        //Expected data for register user
        $prepareData = array(
            'userId'=>21,
            'message'=>'Testing Invite',
            'emails'=>'cmarrero01@gmail.com'
        );

        $sendInvite = $this->rest->get('invites',$prepareData,'json');
        $response = (object) array('response'=>true);
        $this->benchmark->mark('test_end');
        echo $this->unit->run($sendInvite, $response, 'Send Invite to one friend','Time: '.$this->benchmark->elapsed_time('test_start', 'test_end'));
        /////////////////// END TEST FOR REGISTER AN USER ///////////////////////////////
    }

}
