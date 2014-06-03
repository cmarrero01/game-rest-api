<?php

class Feedback_model extends CI_Model {

    public function __construct() {
        parent::__construct();
    }

    
    //Guardamos los datos en la base de datos
    public function save($data = array()) {
        $data['User_Id'] = (isset($data['User_Id'])) ? $data['User_Id'] : 0;
        $this->db->insert('bp_feedback', $data);
    }

}

?>
