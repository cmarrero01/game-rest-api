<?php
/**
* <sumary>
* Modelo del los torneos.
* </sumary>
**/


class battleinvites_model extends CI_Model {

    function __construct(){
        parent::__construct();
    }

    public function getFuturePlayers($send=0){
        $this->db->from('bp_battleinvites');
        $this->db->where('sendInvite',0);
        if($send!=0){
            $this->db->limit($send);
        }
        $query = $this->db->get();
        if($query->num_rows() > 0){
            return $query->result();
        }
        return false;
    }

    public function invitedSend($user_id){
        $this->db->update('bp_battleinvites',array('sendInvite'=>1),array('user_id'=>$user_id));
    }

    public function getSuscriptors($send=0){
        $this->db->from('bp_suscriptors');
        $this->db->where('sendInvite',0);
        if($send!=0){
            $this->db->limit($send);
        }
        $query = $this->db->get();
        if($query->num_rows() > 0){
            return $query->result();
        }
        return false;
    }

    public function suscripSend($idSuscriptor){
        $this->db->update('bp_suscriptors',array('sendInvite'=>1),array('idSuscriptor'=>$idSuscriptor));
    }
}