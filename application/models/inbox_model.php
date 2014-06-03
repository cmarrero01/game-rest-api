<?php
/**
* <sumary>
* Modelo del los achievements.
* </sumary>
**/


class Inbox_model extends CI_Model {

	function __construct()
	{
        // Call the Model constructor
		parent::__construct();
	}


	/*
	* List of messages
	*/
	function get_messages($data)
	{
		$idUserReceived = $data['idReceived'];

		if(!empty($idUserReceived)){

			$receivedType = $data['receivedType'];
			/*
			* receivedType == 0 if Message to User,
			* receivedType == 1 if Message to Team,
			* receivedType == 2 if Message to Clan,
			*/

			$this->db->from('bp_user_pm');
			$this->db->select('idPm,receivedType,idUserReceived,idUserSender,messageStatus,topic,message,senderDate,inResponseTo,sender.nicename as sender,received.nicename as received,(SELECT imagePath from bp_user_avatar where bp_user_avatar.idUser = iduserSender and bp_user_avatar.size = 53) as avatarPath,');
			$this->db->join('bp_users as sender','bp_user_pm.idUserSender = sender.userId','inner');
			$this->db->join('bp_users as received','bp_user_pm.idUserReceived = received.userId','left');
			$this->db->where('receivedType',$receivedType);
			$this->db->where('inResponseTo','0');
			$this->db->where("(idUserReceived=$idUserReceived OR idUserSender=$idUserReceived)");
			$this->db->order_by("senderDate", "desc");

			$result =  $this->db->get()->result();

			return $result;

		}else{
			return false;
		}
	}

    function get_notifications($data){
        $idUserReceived = $data['idReceived'];
        if(!empty($idUserReceived)){

            $receivedType = $data['receivedType'];

            $this->db->from('bp_user_pm');
            $this->db->select('idPm,receivedType,idUserReceived,idUserSender,messageStatus,topic,message,senderDate,inResponseTo');
            $this->db->where('receivedType',$receivedType);
            $this->db->where('inResponseTo','0');
            $this->db->where('idUserReceived',$idUserReceived);
            $this->db->where('idUserSender','0');
            $this->db->order_by("senderDate", "desc");

            $result =  $this->db->get()->result();
            return $result;

        }else{
            return false;
        }
    }

	/*
	* Add new messages
	*/
	public function addMessage($data){
		return $this->db->insert('bp_user_pm',$data);
	}


	/*
	* Get last message data from id
	*/
	public function getMessageData($idMessage){
		if(!empty($idMessage)){
			$this->db->from('bp_user_pm');
			$this->db->select('idUserReceived,idUserSender,receivedType, (SELECT imagePath from bp_user_avatar where bp_user_avatar.idUser = idUserSender and bp_user_avatar.size = 53) as avatarPath');
			$this->db->join('bp_users','bp_user_pm.idUserSender = bp_users.userId','left');
			$this->db->where('idPm',$idMessage);
			return $this->db->get()->row();
            echo $this->db->last_query();
            die;
		}else{
			return false;
		}
	}

	/*
	* Get conversation
	*/
	public function getConversation($data){
		$result = false;
		if(!empty($data)){
			$receivedType = $data['receivedType'];
            $idMessage = $data['idMessage'];
            $idUserReceived = $data['idUserReceived'];
            $idUserSender = $data['idUserSender'];
			$this->db->from('bp_user_pm');
			$this->db->join('bp_users','bp_user_pm.idUserSender = bp_users.userId');
			$this->db->select('idPm,receivedType,idUserReceived,idUserSender,messageStatus,topic,message,inResponseTo,senderDate,bp_users.nicename,(SELECT imagePath from bp_user_avatar where bp_user_avatar.idUser = iduserSender and bp_user_avatar.size = 53) as avatarPath,');
            $this->db->where('receivedType',$data['receivedType']);
            if($receivedType > 0){
                $this->db->where("((inResponseTo = ".$idMessage.") OR (idPm = ".$idMessage."))");
            }else{
                $this->db->where("((idUserReceived=$idUserReceived AND idUserSender=$idUserSender)OR(idUserReceived=$idUserSender AND idUserSender=$idUserReceived))");
            }
			$this->db->limit(10);
			$this->db->order_by("senderDate", "asc");
//			 echo $this->db->last_query();
			$result = $this->db->get()->result();
		}
		return $result;
	}

	/*
	* First messages
	*/
	public function first_message($data){
		if(!empty($data)){
			$this->db->from('bp_user_pm');

			$receivedType = $data['receivedType'];
			$idUserReceived = $data['idUserReceived'];
			$idUserSender = $data['idUserSender'];

			$this->db->where('inResponseTo', '0');
			$this->db->where('receivedType', $receivedType);
			$this->db->where("((idUserReceived=$idUserReceived AND idUserSender=$idUserSender)OR(idUserReceived=$idUserSender AND idUserSender=$idUserReceived))");
			$this->db->select('idPm,idUserReceived');
			$this->db->order_by("senderDate", "asc");
			$this->db->limit(1);

			$result = $this->db->get()->result();
			return $result;
		}else{
			return false;
		}
	}

	/*
	* Get last message
	*/
	public function last_message($idMessage){
		if(!empty($idMessage)){
			$this->db->from('bp_user_pm');
			$this->db->select('idPm,receivedType,idUserReceived,idUserSender,messageStatus,topic,message,senderDate,inResponseTo,sender.nicename as sender,received.nicename as received');
			$this->db->join('bp_users as sender','bp_user_pm.idUserSender = sender.userId','inner');
			$this->db->join('bp_users as received','bp_user_pm.idUserReceived = received.userId','left');
			$this->db->where('inResponseTo',$idMessage);
			$this->db->order_by("senderDate", "desc");
			$this->db->limit(1);
			return $this->db->get()->row();
		}else{
			return false;
		}
	}

	/*
	* Get responses
	*/
	public function getResponses($idPm){
		if(!empty($idPm)){
			$this->db->from('bp_user_pm');
			$this->db->select('idPm,receivedType,idUserReceived,idUserSender,messageStatus,topic,message,inResponseTo,senderDate,bp_users.nicename');
			$this->db->join('bp_users','bp_user_pm.idUserSender = bp_users.userId','left');
			$this->db->where('inResponseTo',$idPm);
			return $this->db->get()->result();
		}else{
			return false;
		}
	}

	/*
	* Mark as Read
	*/
	public function mark_read($idMessage){
		$ok = false;
		if(is_array($idMessage)){
			foreach ($idMessage as $a) {
				$data = array('messageStatus' => '1');
				$this->db->where('idPm', $a);
				$ok = $this->db->update('bp_user_pm', $data);
				if(!$ok){
					return $ok;
				}
			}
		}else{
			$data = array('messageStatus' => '1');
			$this->db->where('idPm', $idMessage);
			$ok = $this->db->update('bp_user_pm', $data);
			if(!$ok){
				return $ok;
			}
		}
		return $ok;
	}
	/*
	* Delete Message
	*/
	public function delete_message($idMessage){
		$ok = false;
		if(is_array($idMessage)){
			foreach ($idMessage as $a) {
				$this->db->from('bp_user_pm');
				$this->db->where('idPm', $a);
				$ok = $this->db->delete();
				if(!$ok){
					return $ok;
				}
			}
		}else{
			$this->db->from('bp_user_pm');
			$this->db->where('idPm', $idMessage);
			$ok = $this->db->delete();
			if(!$ok){
				return $ok;
			}
		}
		return $ok;
	}

        /**
         * Actualiza el contenido de una notificación, actualmente utilizada
         * para team y clans.
         * @param type $idMessage
         * @return type
         */
        public function update_notif($idMessage){
            $ok = false;

            $data = array('message' => 'Thanks for your response!');
            $this->db->where('idPm', $idMessage);
            $ok = $this->db->update('bp_user_pm', $data);
            if(!$ok){
                return $ok;
            }
            return $ok;
        }

    public function checkForPrivateMessage($userId,$receivedType){
        if($userId){
            $query = ('SELECT s.nicename,u.topic,u.senderDate,u.idPm FROM battlepro_api.bp_user_pm u JOIN battlepro_api.bp_users s ON s.userId = u.idUserSender WHERE u.idUserReceived = '.$userId.' AND u.messageStatus = 0 AND receivedType = '.$receivedType.' ORDER BY u.senderDate DESC LIMIT 1');
            $msg = $this->db->query($query);
            return $msg->result();
        }else{
            return false;
        }
    }

}