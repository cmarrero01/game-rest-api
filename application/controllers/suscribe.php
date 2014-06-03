<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Controller para suscribe desde comming soon
 *
 * @package		CodeIgniter
 * @subpackage	Rest Server
 * @category	Controller
*/

require APPPATH.'/libraries/REST_Controller.php';

class Suscribe extends REST_Controller
{
	public function __construct()
	{
		parent:: __construct();
        $this->load->model('suscribe_model');
        $this->load->model('sanitize_model');
	}

    /*
     * Metodo por defecto de suscribe, que suscribe un amil a la base de datos
     */
    public function index_get(){

        $email = $this->get('email',TRUE);
        $this->newSuscriptor($this->sanitize_model->sanitize($email));
    }

    public function newSuscriptor_get($email=''){

        if(empty($email)){
            $email = $this->get('email',TRUE);
        }

        $this->newSuscriptor($this->sanitize_model->sanitize($email));
    }

    private function newSuscriptor($email){
        $suscribe = $this->suscribe_model->suscribe($email);
        if($suscribe){
            $result = 'Great, We contact you when battlepro is already.';
        }else{
            $result = 'Ops!. We have some problems with your email.';
        }
        $response = array('response' => $result);
        $this->response($response, 200);
    }
}