<?php

/**
 * <sumary>
 * Modelo del usuario, represanta todas las acciones del usuario.
 * </sumary>
 * */
class test_model extends CI_Model {

    function __construct() {
        parent::__construct();
        $this->load->database();
    }

    public function deleteUser(){
        $this->db->from('bp_users');
        $query = $this->db->get();
        $result = $query->result();
        $last = end(array_values($result));
        //Delete
        $this->db->delete('bp_users',array('userId'=>$last->userId));
        $this->db->delete('bp_historypoints',array('userId'=>$last->userId));
    }
}

?>
