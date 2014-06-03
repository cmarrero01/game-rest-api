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
 * @property mixed invite_model
 */
class testUser extends MY_Controller {

    public function __construct() {
        parent:: __construct();
        $this->load->library('unit_test');
        $this->load->model('testmodels/test_model');
        $this->load->model('invite_model');
    }

    public function index(){
        $this->testRegister();
        $this->smallMethods();
    }

    /*
     * Test register user
     * Registro con datos correctos
     * Registro sin terminos de condiciones
     * Registro con mail existente
     * Registro con nicename existente
     */
    public function testRegister(){

        /////////////////// START TEST FOR REGISTER AN USER ///////////////////////////////
        $this->benchmark->mark('test_start');
        //Expected data for register user
        $prepareData = array(
            'full_name'=>'Prepare TEst',
            'nicename'=>'prepareTEst',
            'password'=>'asd.qwe123',
            'email'=>'unit-test@test.com',
            'day_birth'=>'08',
            'month_birth'=>'02',
            'year_birth'=>'1985',
            'tos'=>'on'
        );

        $register = $this->rest->get('user/register',$prepareData,'json');
        $response = (object) array('response'=>2,'result'=>'Confirmation Email Sent Successfully');
        //Clean information of this user
        $this->deleteUser();
        $this->benchmark->mark('test_end');
        echo $this->unit->run($register, $response, 'Register User','Time: '.$this->benchmark->elapsed_time('test_start', 'test_end'));
        /////////////////// END TEST FOR REGISTER AN USER ///////////////////////////////

        /////////////////// START TEST FOR REGISTER AN USER ///////////////////////////////
        $this->benchmark->mark('test_start');
        //When user is menor de 13 años
        $prepareData = array(
            'full_name'=>'Prepare TEst',
            'nicename'=>'prepareTEst',
            'password'=>'asd.qwe123',
            'email'=>'cmarrero01@gmail.com',
            'day_birth'=>'08',
            'month_birth'=>'02',
            'year_birth'=>'1985',
            'tos'=>'on'
        );

        $register = $this->rest->get('user/register',$prepareData,'json');
        $response = (object) array('response' => false,'result'=> 'That Email already exist' );
        $this->benchmark->mark('test_end');
        echo $this->unit->run($register, $response, 'The Email already exist','Time: '.$this->benchmark->elapsed_time('test_start', 'test_end'));
        /////////////////// END TEST FOR REGISTER AN USER ///////////////////////////////


        /////////////////// START TEST FOR REGISTER AN USER ///////////////////////////////
        $this->benchmark->mark('test_start');
        //When user is menor de 13 años
        $prepareData = array(
            'full_name'=>'Prepare TEst',
            'nicename'=>'testing',
            'password'=>'asd.qwe123',
            'email'=>'unit-test2@test.com',
            'day_birth'=>'08',
            'month_birth'=>'02',
            'year_birth'=>'1985',
            'tos'=>'on'
        );

        $register = $this->rest->get('user/register',$prepareData,'json');
        $response = (object) array('response' => false, 'result' => 'Your nickname is already taken. :(');
        $this->benchmark->mark('test_end');
        echo $this->unit->run($register, $response, 'The Nickname already exist','Time: '.$this->benchmark->elapsed_time('test_start', 'test_end'));
        /////////////////// END TEST FOR REGISTER AN USER ///////////////////////////////

        /////////////////// START TEST FOR REGISTER AN USER ///////////////////////////////
        $this->benchmark->mark('test_start');
        //Creamos la invitacion
        $referalCode = md5(21);

        $prepare_invite = array(
            'userFromId'=>21,
            'emailTo'=>'unit-test2@test.com',
            'sendDate'=>date('Y-m-d h:i:s',time()),
            'referalCode'=>$referalCode,
            'flag'=>0
        );
        $this->invite_model->saveInvitationToFriend($prepare_invite);


        //When user is menor de 13 años
        $prepareData = array(
            'full_name'=>'Referal user',
            'nicename'=>'referal_user',
            'password'=>'asd.qwe123',
            'email'=>'unit-test2@test.com',
            'day_birth'=>'08',
            'month_birth'=>'02',
            'year_birth'=>'1985',
            'tos'=>'on',
            'referalCode'=>$referalCode
        );

        $register = $this->rest->get('user/register',$prepareData,'json');
        $response = (object) array('response'=>2,'result'=>'Confirmation Email Sent Successfully');

        $this->deleteUser();
        $this->deleteInvite('unit-test2@test.com',$referalCode);

        $this->benchmark->mark('test_end');
        echo $this->unit->run($register, $response, 'Register with referal','Time: '.$this->benchmark->elapsed_time('test_start', 'test_end'));
        /////////////////// END TEST FOR REGISTER AN USER ///////////////////////////////
    }

    /*
     * Revisamos methodos chicos
     */
    public function smallMethods(){
        /////////////////// TEST FOR GET THE STEAM ID OF USER ///////////////////////////////
        $this->benchmark->mark('test_start');
        $prepare_data = array(
            'userId'=>21
        );
        $steam = $this->rest->get('user/steamId',$prepare_data,'json');
        $expected = (object)array('response' => 1, 'result' => (object)array ( 'user_steam' => 48, 'gameId' => 112745671, 'userId' => 21, 'steamId' => 56372835 ) );
        $this->benchmark->mark('test_end');
        echo $this->unit->run($steam, $expected, 'Steam Id','Time: '.$this->benchmark->elapsed_time('test_start', 'test_end'));
    }



    ///////////////////////// METHODOS PRIVADOS DE INTEGRACION ////////////////////////

    private function deleteUser(){
        $this->test_model->deleteUser();
    }

    /*
     * Eliminar los invite creados en los test
     */
    private function deleteInvite($email,$referalCode){
        $prepare_delete = array(
            'emailTo'=>$email,
            'referalCode'=>$referalCode
        );
        $this->invite_model->deleteInvite($prepare_delete);
    }
}
