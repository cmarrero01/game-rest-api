<?php

/**
 * <sumary>
 * Modelo del usuario, represanta todas las acciones del usuario.
 * </sumary>
 * */
class Invite_model extends CI_Model {

    function __construct() {
        // Call the Model constructor
        parent::__construct();
        $this->load->database();
    }

    /*
     * Guarda cada invitacionq ue se envia
     */
    public function saveInvitationToFriend($options=array()){
        $getInvitation = $this->getInvitation(array('email'=>$options['emailTo']));
        if(!$getInvitation){
            $insert = $this->db->insert('bp_invites',$options);
            if($insert){
                return true;
            }
        }
        return false;
    }

    /*
     * Get invitaion
     */
    public function getInvitation($options=array()){
        $this->db->from('bp_invites');

        if(isset($options['referalCode'])){
            $this->db->where('referalCode',$options['referalCode']);
        }

        if(isset($options['email'])){
            $this->db->where('emailTo',$options['email']);
        }

        if(isset($options['flag']) and $options['flag']){
            $this->db->where('flag',$options['flag']);
        }

        $query = $this->db->get();

        if($query->num_rows() == 1){
            return $query->row();
        }elseif($query->num_rows() > 1){
            return $query->result();
        }

        return false;
    }

    /*
     * Actualizamos la invitacion
     */
    public function updateInvitation($options=array()){
        if(isset($options['referalCode']) and $options['referalCode']){
            $getInvitation = $this->getInvitation(array('email'=>$options['email'],'referalCode'=>$options['referalCode']));
            if($getInvitation){
                $update = $this->db->update('bp_invites',array('flag'=>2),array('inviteId'=>$getInvitation->inviteId));
                return true;
            }
        }
        return false;
    }

    /*
     * Eliminamos la invitacion
     */
    public function deleteInvite($options=array()){
        $this->db->delete('bp_invites',$options);
    }
}

?>
