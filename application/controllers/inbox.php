<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 *  Controlador del Inbox.
 *
 * @package		CodeIgniter
 * @subpackage	Rest Server
 * @category	Controller
*/

require APPPATH.'/libraries/REST_Controller.php';

class Inbox extends REST_Controller
{
	
	public function __construct()
	{
		parent:: __construct();
		$this->load->model('inbox_model');
		$this->load->model('sanitize_model');
        $this->config->load('email');
    }
	
	/**
	* Devuelve los mensajes de la bandeja del usuario.
	* @param userId
	*/
	public function myInbox_get()
	{
		$idReceived = $this->get('userId');
		$receivedType = 0;

		$array = array(
			'idReceived'=>$idReceived,
			'receivedType'=>$receivedType,
			);

		$messages = array();

		$inbox = $this->inbox_model->get_messages($array);

		foreach ($inbox as $message) {
			$thisLast = $this->inbox_model->last_message($message->idPm);
			if($thisLast){
				array_push($messages, $thisLast);
			}
			else{
				array_push($messages, $message);
			}
		}

		if($messages)
		{
			$response = array('response' => true,'result' => $messages);
			$this->response($response, 200);
		}
		else
		{
			$response = array('response' => false,'result' => 'You don\'t have any message');
			$this->response($response, 404);
		}
	}

    /**
     * Devuelve las notificaciones del usuario.
     * @param userId
     */
    public function myNotifications_get(){

        $idReceived = $this->get('userId');
        $receivedType = 0;

        $array = array(
            'idReceived'=>$idReceived,
            'receivedType'=>$receivedType,
        );

        $inbox = $this->inbox_model->get_notifications($array);

        if($inbox)
        {
            $response = array('response' => true,'result' => $inbox);
            $this->response($response, 200);
        }
        else
        {
            $response = array('response' => false,'result' => 'You don\'t have any message');
            $this->response($response, 404);
        }
    }
	
	/**
	* Crea un nuevo mensaje PRIVADO.
	* @param idUserReceived, idUserSender, receivedType, messageStatus, topic, message
	*/
	public function sendMessage_get()
	{
		$idUserReceived = $this->get('idUserReceived');
		$idUserSender = $this->get('idUserSender');
		$receivedType = $this->get('receivedType');
		$messageStatus = $this->get('messageStatus');
		$topic = $this->get('topic',TRUE);
		$message = $this->get('message',TRUE);

		//Data to check first message between Receiver and Sender
		$data = array(
			'receivedType' => $receivedType,
			'idUserReceived'=>$idUserReceived,
			'idUserSender'=>$idUserSender,
			);

		$first_message = $this->inbox_model->first_message($data);

        $message = $this->sanitize_model->sanitize($message);
        $topic = $this->sanitize_model->sanitize($topic);

		if(!empty($first_message)){
			$array = array(
				'receivedType' => $receivedType,
				'idUserReceived'=>$idUserReceived,
				'idUserSender'=>$idUserSender,
				'messageStatus'=>$messageStatus,
				'message'=>$message,
				'topic'=>$topic,
				'senderDate'=>date('Y-m-d h:s:i',time()),
				'inResponseTo' => $first_message[0]->idPm,
				);
		}else{
			$array = array(
				'receivedType' => $receivedType,
				'idUserReceived'=>$idUserReceived,
				'idUserSender'=>$idUserSender,
				'messageStatus'=>$messageStatus,
				'message'=>$message,
				'topic'=>$topic,
				'senderDate'=>date('Y-m-d h:s:i',time()),
				);
		}
		
		$myMessage = $this->inbox_model->addMessage($array);
		
		if($myMessage)
		{
            //Mando un email con el contenido del PM
            $this->load->model('user_model');
            $webUrl = $this->config->item('webUrl');

            $userReceivedData = $this->user_model->get_user_data($array['idUserReceived']);
            $userSenderData = $this->user_model->get_user_data($array['idUserSender']);
            $this->email->from('notifications@battlepro.com', 'Battle Pro');
            $this->email->to($userReceivedData->email);

            $subject = 'You have a new private message';

            $data = array(
                'web_url'=>$this->config->item('webUrl'),
                'image_url'=>$this->config->item('imagesUrl'),
                'message' => '<h3 style="color:#ccc; font-size:17px; font-family:arial; line-height: 1.5;" ><a href="'.$webUrl.'/profile/show/'.$userSenderData->nicename.'" target="_blank">'.$userSenderData->nicename.'</a> has sent you a message</h3></br>'.$message
            );

            $content = $this->load->view('emails/notifications/notification', $data, true);

            $this->email->subject($subject);
            $this->email->message($content);
            $this->email->send();
			$response = array('response' => true,'result' => 'Message added');
			$this->response($response, 200);
		}
		else
		{
			$response = array('response' => false,'result' => 'We have some problems sender you message');
			$this->response($response, 404);
		}
	}

	/**
	* Replys messages to current conversation
	*/
	public function replyMessage_get()
	{
		$idUserSender = $this->get('idUserSender');
		$messageStatus = $this->get('messageStatus');
		$topic = $this->get('topic');
		$message = $this->get('message');
		$inResponseTo = $this->get('inResponseTo');

		$lastMessage = $this->inbox_model->getMessageData($inResponseTo);

		$array = array(
			'receivedType' => $lastMessage->receivedType,
			'idUserReceived' => $lastMessage->idUserReceived,
			'idUserSender' => $lastMessage->idUserSender,
			);

		$first_message = $this->inbox_model->first_message($array);

		$data = array(
			'idUserSender' => $idUserSender,
			'messageStatus'=>$messageStatus,
			'message'=>$this->sanitize_model->sanitize($message),
			'topic'=>$this->sanitize_model->sanitize($topic),
			'senderDate'=>date('Y-m-d h:s:i',time()),
			'inResponseTo' => $first_message[0]->idPm,
			'receivedType' => $lastMessage->receivedType
			);
        if($lastMessage->receivedType > 0){
            $data['idUserReceived'] = $first_message[0]->idUserReceived;
        }else{
            if($idUserSender==$lastMessage->idUserSender){
                $data['idUserReceived']=$lastMessage->idUserReceived;
            }else{
                $data['idUserReceived']=$lastMessage->idUserSender;
            };
        }

		$myMessage = $this->inbox_model->addMessage($data);
		
		if($myMessage)
		{			
			$response = array('response' => true,'result' => 'Message added');
			$this->response($response, 200);
		}
		else
		{
			$response = array('response' => false,'result' => 'We have some problems sender you message');
			$this->response($response, 404);
		}
	}

	/*
	* Get Conversation from last message
	* @param idPm
	*/
	public function conversation_get()
	{
		$idMessage = $this->get('idPm');
		$lastMessage = $this->inbox_model->getMessageData($idMessage);

		$data = array(
			'idMessage' => $idMessage,
			'idUserReceived' => $lastMessage->idUserReceived,
			'idUserSender' => $lastMessage->idUserSender,
			'receivedType' => $lastMessage->receivedType
			);


        $conversation = $this->inbox_model->getConversation($data);

        foreach ($conversation as $message) {
			if ($message->messageStatus == 0){
				$this->inbox_model->mark_read($message->idPm);
			}
		}

		if($conversation)
		{
			$response = array('response' => true,'result' => $conversation);
			$this->response($response, 200);
		}
		else
		{
			$response = array('response' => false,'result' => 'The message does not exist');
			$this->response($response, 404);
		}
	}
	/**
	* Marca un mensaje como leído. Mark Read Messages
	* 
	*/
	public function markAsRead_get(){

		$idMessage = $this->get();

		foreach ($idMessage as $id) {
			$lastMessage = $this->inbox_model->getMessageData($id);

			$data = array(
				'idMessage' => $id,
				'idUserReceived' => $lastMessage->idUserReceived,
				'idUserSender' => $lastMessage->idUserSender,
				'receivedType' => $lastMessage->receivedType
				);

			$conversation = $this->inbox_model->getConversation($data);

			foreach ($conversation as $message) {
				if ($message->messageStatus == 0){
					$this->inbox_model->mark_read($message->idPm);
				}
			}
		}

		if($conversation)
		{
			$response = array('response' => true,'result' => 'Mark As Read Succesfull');
			$this->response($response, 200);
		}
		else
		{
			$response = array('response' => false,'result' => 'We had some problems marking your messages');
			$this->response($response, 404);
		}
	}
    /**
     * Marca una notificacion como leida
     *
     */
    public function markAsReadNoti_get(){

        $idMessage = $this->get();
        $isRead = $this->inbox_model->mark_read($idMessage);

        if($isRead)
        {
            $response = array('response' => true,'result' => 'Mark As Read Succesfull');
            $this->response($response, 200);
        }
        else
        {
            $response = array('response' => false,'result' => 'We had some problems marking your messages');
            $this->response($response, 404);
        }
    }

	/** 
	* Borrar un mensaje. Delete messages
	* 
	*/
	public function eraseMessage_get()
	{
		$idMessage = $this->get();

		foreach ($idMessage as $id) {
			$lastMessage = $this->inbox_model->getMessageData($id);

			$data = array(
				'idMessage' => $id,
				'idUserReceived' => $lastMessage->idUserReceived,
				'idUserSender' => $lastMessage->idUserSender,
				'receivedType' => $lastMessage->receivedType
				);

			$conversation = $this->inbox_model->getConversation($data);

			foreach ($conversation as $message) {
				$this->inbox_model->delete_message($message->idPm);
			}
		}

		if($conversation)
		{
			$response = array('response' => true,'result' => 'Messages Deleted');
			$this->response($response, 200);
		}
		else
		{
			$response = array('response' => false,'result' => 'We had some problems deleting your messages');
			$this->response($response, 404);
		}
	}
    /**
     * Borra una notificación.
     * @param idMessage
     */
    public function deleteNotification_get()
    {
        $idMessage = $this->get();

        foreach ($idMessage as $id) {
            $toDelete = $this->inbox_model->delete_message($id);
        }
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

    public function checkForPrivateMessage_get(){

        $userId = $this->get('userId');
        $receivedType = $this->get('receivedType');

        if($userId){
            $newMessage = $this->inbox_model->checkForPrivateMessage($userId,$receivedType);

            if($newMessage){
                $response = array('response' => true,'result' => $newMessage);
                $this->response($response, 200);
            }else{
                $response = array('response' => false,'result' => 'You have no new messages');
                $this->response($response, 404);
            }
        }else{
            $response = array('response' => false,'result' => 'Wrong data');
            $this->response($response, 404);
        }
    }
        }

