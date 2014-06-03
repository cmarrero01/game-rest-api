<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 *  Achievements Controller
 * Controlador que determina el dispositivo que está utilizando (desde donde se ha logeado) el usuario.
 * @package		CodeIgniter
 * @subpackage	Rest Server
 * @category	Controller
*/

require APPPATH.'/libraries/REST_Controller.php';

/**
 * @property mixed battleinvites_model
 */
class battleinvite extends REST_Controller
{
	
	public function __construct()
    {
		parent:: __construct();
        $this->load->model('battleinvites_model');
        $this->config->load('email');
	}

    public function index_get(){
        set_time_limit(0);

        $send = $this->get('send');
        $getFuturePlayers = $this->battleinvites_model->getFuturePlayers($send);

        if($getFuturePlayers){
            if(!empty($getFuturePlayers)){
                foreach($getFuturePlayers as $players){
                    if(isset($players->email) and !empty($players->email)){
                        $this->sendEmail($players->email);
                        $this->battleinvites_model->invitedSend($players->user_id);
                    }
                }
            }
            $this->response(array('response'=>true,'result'=>$getFuturePlayers),200);
        }
        $this->response(array('response'=>false),200);
    }

    public function suscriptors_get(){
        set_time_limit(0);

        $send = $this->get('send');
        $getFuturePlayers = $this->battleinvites_model->getSuscriptors($send);

        if($getFuturePlayers){
            if(!empty($getFuturePlayers)){
                foreach($getFuturePlayers as $players){
                    if($players->email != ''){
                        $this->sendEmail($players->email);
                        $this->battleinvites_model->suscripSend($players->idSuscriptor);
                    }
                }
            }
            $this->response(array('response'=>true,'result'=>$getFuturePlayers),200);
        }
        $this->response(array('response'=>false),200);
    }

    private function sendEmail($email){

        $this->email->from('no-reply@battlepro.com', 'Battle Pro');
        $this->email->to($email);

        $data = array(
            'web_url'=>$this->config->item('webUrl'),
            'image_url'=>$this->config->item('imagesUrl')
        );

        $message = $this->load->view('emails/invite/inviteSuscriptor', $data, true);

        $this->email->subject('Battlepro.com is Online.');
        $this->email->message($message);
        $this->email->send();
        return true;
    }
}

