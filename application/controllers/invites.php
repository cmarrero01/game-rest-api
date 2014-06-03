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
 * @property mixed user_model
 * @property mixed invite_model
 */
class invites extends REST_Controller
{
	
	public function __construct()
    {
		parent:: __construct();
        $this->load->model('battleinvites_model');
        $this->config->load('email');
        $this->load->model('user_model');
        $this->load->model('invite_model');
	}

    /*
     * Envia las invitaciones a mails que un usuario proporciona
     */
    public function index_get(){
        $userId =  $this->get('userId');
        $emails = $this->get('emails');
        //$message = $this->get('message');

        $referalCode = md5($userId);

        $userFrom = $this->user_model->getUserEmailById($userId);

        if($emails and !empty($emails)){
            $prepare_data = array(
                'userFrom'=>$userFrom->email,
                'nicenameFrom'=>$userFrom->nicename,
                'emails'=>$emails,
                'subject'=>'Your Friend '.$userFrom->nicename.' invite you',
                //'message'=>$message,
                'userIdTo'=>$userId,
                'referalCode'=>$referalCode
            );
            $this->sendEmail($prepare_data);
            $this->response(array('response'=>true,'result'=>$emails),200);
        }

        $this->response(array('response'=>false),200);
    }

    /*
     * How many invites have this user
     */
    public function getInvitations_get(){
        $userId = $this->get('userId');
        $accepted = $this->get('accepted');
        $referalCode = md5($userId);
        $invites = $this->getInvitations($referalCode,$accepted);
        if(!empty($invites)){
            $this->response(array('response'=>true,'result'=>$invites),200);
        }
        $this->response(array('response'=>false),200);
    }

    /*
     * Envia la invitacion a participar de battlepro.com a los usuarios existentes
     * en la tabla bp_battleinvite
     */
    public function baseinvite_get(){
        set_time_limit(0);

        $send = $this->get('send');
        $getFuturePlayers = $this->battleinvites_model->getFuturePlayers($send);

        if($getFuturePlayers){
            if(!empty($getFuturePlayers)){
                foreach($getFuturePlayers as $players){
                    if(isset($players->email) and !empty($players->email)){
                        $this->sendBattleEmail($players->email);
                        $this->battleinvites_model->invitedSend($players->user_id);
                    }
                }
            }
            $this->response(array('response'=>true,'result'=>$getFuturePlayers),200);
        }
        $this->response(array('response'=>false),200);
    }

    /*
     * Envia una invitacion a participar a los usuarios suscriptos en la tabla bp_suscriptors
     */
    public function suscriptors_get(){
        set_time_limit(0);

        $send = $this->get('send');
        $getFuturePlayers = $this->battleinvites_model->getSuscriptors($send);

        if($getFuturePlayers){
            if(!empty($getFuturePlayers)){
                foreach($getFuturePlayers as $players){
                    if($players->email != ''){
                        $this->sendBattleEmail($players->email);
                        $this->battleinvites_model->suscripSend($players->idSuscriptor);
                    }
                }
            }
            $this->response(array('response'=>true,'result'=>$getFuturePlayers),200);
        }
        $this->response(array('response'=>false),200);
    }

    /////////////////////// METHODOS PRIVADOS //////////////////////////////
    /*
     * Envia mails de invitacion con la uenta de battle pro.
     */
    private function sendBattleEmail($email){

        $this->email->from('no-reply@battlepro.com', 'Battle Pro');
        $this->email->to($email);

        $data = array(
            'web_url'=>$this->config->item('webUrl'),
            'image_url'=>$this->config->item('imagesUrl')
        );

        $message = $this->load->view('emails/invite/inviteSuscriptor', $data, true);

        $this->email->subject('Battle Pro is Online');
        $this->email->message($message);
        $this->email->send();
    }

    /*
    * Envia mails de invitacion con la cuenta del usuario que lo envia
    */
    private function sendEmail($options=array()){

        $options['message'] = '';

        if(is_array($options['emails'])){

            foreach($options['emails'] as $emails){
                $is_not_register = $this->user_model->exist_email($emails);
                if($is_not_register){
                    $this->sendOneMail($options['userFrom'],$options['nicenameFrom'],$emails,$options['subject'],$options['message'],$options['userIdTo']);
                }
            }
        }else{
            $is_not_register = $this->user_model->exist_email($options['emails']);
            if($is_not_register){
                $this->sendOneMail($options['userFrom'],$options['nicenameFrom'],$options['emails'],$options['subject'],$options['message'],$options['userIdTo']);
            }
        }
    }

    /*
     * Envia mails a qien y de quien se lo especifique
     */
    private function sendOneMail($from,$nicename,$to,$subject,$message,$userId){
        $this->email->from($from, $nicename);
        $this->email->to($to);

        $data = array(
            'web_url'=>$this->config->item('webUrl'),
            'image_url'=>$this->config->item('imagesUrl'),
            'message'=>$message,
            'nicename'=>$nicename,
            'referalCode'=>md5($userId)
        );

        $mail = $this->load->view('emails/invite/inviteFriends', $data, true);

        $this->email->subject($subject);
        $this->email->message($mail);
        $this->email->send();

        $this->saveInviteToUser($userId,$to);
    }

    /*
     * Guardamos la informacion de que un usuario invito a un dterminado emails
     */
    private function saveInviteToUser($userId,$emailTo){
        $prepare_data = array(
            'userFromId'=>$userId,
            'emailTo'=>$emailTo,
            'sendDate'=>date('Y-m-d h:i:s',time()),
            'referalCode'=>md5($userId),
            'flag'=>1
        );
        $invite = $this->invite_model->saveInvitationToFriend($prepare_data);
        if($invite){
            return true;
        }
        return false;
    }

    /*
     * Cuantas invitaciones ha enviado el usuario
     */
    private function getInvitations($referalCode,$flag){
        $invites = $this->invite_model->getInvitation(array('referalCode'=>$referalCode,'flag'=>$flag));
        return $invites;
    }
}

