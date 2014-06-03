<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Controller del tablón de mensajes.
 *
 * @package		CodeIgniter
 * @subpackage	Rest Server
 * @category	Controller
 */

require APPPATH.'/libraries/REST_Controller.php';

class Messages extends REST_Controller
{

    public function __construct()
    {
        parent:: __construct();
        $this->load->model('messages_model');
        $this->load->model('sanitize_model');
    }

    /**
     * Devuelve los mensajes recibidos en el tablón. Aún aquellos que sean mensajes del propio usuario.
     * @param idUserReceiver, receivedType
     */
    public function myMessages_get()
    {
        $idUserReceiver=$this->get('idUserReceiver');
        $receivedType=$this->get('receivedType');

        $data = array(
            'idUserReceiver'=>$idUserReceiver,
            'receivedType'=>$receivedType
        );

        $myMessage = $this->messages_model->get_messages($data);

        if($myMessage)
        {
            $response = array('response' => true,'result' => $myMessage);
            $this->response($response, 200);
        }
        else
        {
            $response = array('response' => false,'result' => 'You don\'t have any message');
            $this->response($response, 404);
        }
    }

    /**
     * Devuleve mmás mensajes de un usuario a partir del id del último mensaje.
     * @param idUserReceiver, receivedType, idMessage
     */
    public function moarMessages_get()
    {
        $idUserReceiver=$this->get('idUserReceiver');
        $receivedType=$this->get('receivedType');
        $idMessage=$this->get('idMessage');

        $data = array(
            'idUserReceiver'=>$idUserReceiver,
            'receivedType'=>$receivedType,
            'idMessage'=>$idMessage
        );

        $myMessage = $this->messages_model->get_moar_messages($data);

        if($myMessage)
        {
            $response = array('response' => true,'result' => $myMessage);
            $this->response($response, 200);
        }
        else
        {
            $response = array('response' => false,'result' => 'You don\'t have any message');
            $this->response($response, 404);
        }
    }

    /**
     * Carga las respuestas del usuario a otros mensajes.
     * @param inResponseTo
     */
    public function myReplys_get()
    {
        $inResponseTo = $this->get('inResponseTo');
        $wasClicked = $this->get('wasClicked');

        $myReplys = $this->messages_model->get_replys($inResponseTo,$wasClicked);

        if($myReplys)
        {
            $response = array('response' => true,'result' => $myReplys);
            $this->response($response, 200);
        }
        else
        {
            $response = array('response' => false,'result' => 'You don\'t have any message');
            $this->response($response, 404);
        }
    }

    /**
     * Envia un nuevo mensaje.
     * @param idUserReceived, idUserSender, receivedType, messageStatus, inResponseTo, message
     */
    public function sendMessage_get()
    {

        $idUserReceiver = $this->get('idUserReceiver');
        $idUserSender = $this->get('idUserSender');
        $messageStatus = $this->get('messageStatus');
        $receivedType = $this->get('receivedType');
        $message = $this->get('message',TRUE);

        $inResponseTo = $this->get('inResponseTo');

        $array = array(
            'idUserReceiver'=>$idUserReceiver,
            'idUserSender'=>$idUserSender,
            'messageStatus'=>$messageStatus,
            'receivedType'=>$receivedType,
            'message'=>$this->sanitize_model->sanitize($message),
            'senderDate'=>date('Y-m-d h:s:i',time()),
            'inResponseTo'=>$inResponseTo,
        );

        $myMessage = $this->messages_model->add_message($array);

        if($inResponseTo){
            $this->messages_model->hasReply($inResponseTo);
        }

        if($myMessage)
        {
            $response = array('response' => true,'result' => $array);
            $this->response($response, 200);
        }
        else
        {
            $response = array('response' => false,'result' => 'We had some problems sending your message');
            $this->response($response, 404);
        }

    }

    /**
     * Borra un mensaje.
     * @param idMessage
     */
    public function eraseMessage_get()
    {
        $idMessage = $this->get('idMessage');

        $toDelete = $this->messages_model->delete_message($idMessage);

        if($toDelete)
        {
            $response = array('response' => true,'result' => 'Message Deleted');
            $this->response($response, 200);
        }
        else
        {
            $response = array('response' => false,'result' => 'We had some problems deleting your message');
            $this->response($response, 404);
        }
    }
    /*
    * Mark as read messages
    */
    public function markRead_get(){

        $userId = $this->get('userId');
        $markRead = $this->messages_model->mark_all_read($userId);

        if($markRead)
        {
            $response = array('response' => true,'result' => 'Operation Successful');
            $this->response($response, 200);
        }
        else
        {
            $response = array('response' => false,'result' => 'We had some problems deleting your message');
            $this->response($response, 404);
        }

    }
}

