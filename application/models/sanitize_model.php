<?php

class Sanitize_model extends CI_Model {

    public function __construct() {
        parent::__construct();
    }


    public function sanitize($data) {
        if (is_array($data)) {
            foreach ($data as $param) {
                $param = $this->db->escape_str($param);
                $param = htmlspecialchars($param);
//                $param = mysql_real_escape_string($param);
            }
        } else {
            $data = $this->db->escape_str($data);
            $data = htmlspecialchars($data);
//            $data = mysql_real_escape_string($data);
        }
        return $data;
    }

}

?>
