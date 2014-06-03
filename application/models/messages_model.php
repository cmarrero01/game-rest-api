<?php
/**
* <sumary>
* Modelo del los achievements.
* </sumary>
**/


class Messages_model extends CI_Model {
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
		if(!empty($data)){
			$this->db->from('bp_user_message');

			$this->db->select('idMessage,idUserReceiver,idUserSender,messageStatus,message,senderDate,bp_users.nicename, (SELECT imagePath from bp_user_avatar where bp_user_avatar.idUser = idUserSender and bp_user_avatar.size = 53) as avatarPath');
			$this->db->join('bp_users','bp_user_message.idUserSender = bp_users.userId','left');
			$this->db->where('receivedType',$data['receivedType']);
			$this->db->where('idUserReceiver',$data['idUserReceiver']);
			$this->db->where('inResponseTo','0');
            $this->db->limit(5);
			$this->db->order_by("idMessage", "desc");

			$result =  $this->db->get()->result();

			return $result;

		}else{
			return false;
		}
	}
    /*
    * List of moar messages
    */
    function get_moar_messages($data){
        if(!empty($data)){
            $this->db->from('bp_user_message');

            $this->db->select('idMessage,idUserReceiver,idUserSender,messageStatus,message,senderDate,bp_users.nicename, (SELECT imagePath from bp_user_avatar where bp_user_avatar.idUser = idUserSender and bp_user_avatar.size = 53) as avatarPath');
            $this->db->join('bp_users','bp_user_message.idUserSender = bp_users.userId','left');
            $this->db->where('receivedType',$data['receivedType']);
            $this->db->where('idUserReceiver',$data['idUserReceiver']);
            $this->db->where('idMessage <',$data['idMessage']);
            $this->db->where('inResponseTo','0');
            $this->db->limit(5);
            $this->db->order_by("idMessage", "desc");

            $result =  $this->db->get()->result();

            return $result;

        }else{
            return false;
        }
    }
    /*
	* List of messages
	*/
	function get_replys($inResponseTo,$wasClicked)
	{
		if(!empty($inResponseTo)){

			$this->db->from('bp_user_message');
			$this->db->select('idMessage,idUserReceiver,idUserSender,messageStatus,message,senderDate,inResponseTo,bp_users.nicename, (SELECT imagePath from bp_user_avatar where bp_user_avatar.idUser = idUserSender and bp_user_avatar.size = 53) as avatarPath');
			$this->db->join('bp_users','bp_user_message.idUserSender = bp_users.userId','left');
			$this->db->where('inResponseTo',$inResponseTo);
			$this->db->order_by("idMessage", "asc");

			$result = $this->db->get()->result();

            if($wasClicked){
                $this->db->where('idMessage',$inResponseTo);
                $this->db->update('bp_user_message',array('hasReply'=>0));
            }

			return $result;
		}else{
			return false;
		}
	}
	/*
	* Add new messages
	*/
	public function add_message($data){
		return $this->db->insert('bp_user_message',$data);
	}

    /*
     * This messages has replys!
     */
    public function hasReply($inResponseTo){
        $this->db->where('idMessage',$inResponseTo);
        $this->db->update('bp_user_message',array('hasReply'=>'1'));
    }

	/*
	* Delete Message
	*/
	public function delete_message($idMessage){
		$this->db->from('bp_user_message');
		$this->db->where('idMessage',$idMessage);
		$this->db->or_where('inResponseTo',$idMessage);
		return $this->db->delete();
	}

	/*
	* Mark As Read Message
	*/
	public function mark_all_read($userId){
		$data=array(
			'messageStatus' => '1'
		);
		$this->db->where('idUserReceiver',$userId);
		return $this->db->update('bp_user_message',$data);
	}

}