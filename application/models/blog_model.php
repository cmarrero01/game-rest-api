<?php

class Blog_model extends CI_Model {

    function __construct() {
        parent::__construct();
    }

    function getRecentPosts($data = array()) {

        $offset = 1 + (($data['page'] - 1) * 3);
        $userId = $data['userId'];
        $this->db->from('bp_user_posts');
        $this->db->where('idUser', $userId);
        $this->db->order_by('idPosts', 'DESC');
        $this->db->limit(3);
        $this->db->offset($offset);

        $query = $this->db->get();
        if($query->num_rows() > 0){
            return $query->result();
        }
        return false;
    }

    function getLatestPost($data = array()) {

        $userId = $data['userId'];
        $this->db->from('bp_user_posts');
        $this->db->where('idUser', $userId);
        $this->db->order_by('idPosts', 'DESC');
        $query = $this->db->get();
        if($query->num_rows() > 0){
            return $query->row();
        }
        return false;
    }

    function getAmountRows($data = array()) {
        $userId = $data['userId'];
        $this->db->from('bp_user_posts');
        $this->db->where('idUser', $userId);
        return $this->db->count_all_results();
    }

    function savePost($data = array()) {
        $userId = $data['userId'];
        $content = $data['content'];
        $title = $data['title'];
        $this->db->insert('bp_user_posts', array(
            'idUser' => $userId,
            'title' => $title,
            'content' => $content,
            'createDate' => date('Y-m-d H:i:s')
        ));
    }

    function deletePost($data = array()) {
        $idPost = $data['idPost'];
        $userId = $data['userId'];

        $isMyPost = $this->db->from('bp_user_posts')->where(array('idUser' => $userId, 'idPosts' => $idPost))->count_all_results();
        if ($isMyPost > 0) {
            $this->db->delete('bp_user_posts', array('idPosts' => $idPost));
        }
    }

}

?>
