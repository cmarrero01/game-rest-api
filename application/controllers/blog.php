<?php

require APPPATH . '/libraries/REST_Controller.php';

class Blog extends REST_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('blog_model');
    }

    public function getRecentPosts_get() {
        $userId = $this->get('userId');
        $page = $this->get('page');
        $data = array();
        $data['userId'] = $userId;
        $data['page'] = $page;
        $posts = $this->blog_model->getRecentPosts($data);
        if($posts){
            $this->response(array('response' => true, 'result' => $posts), 200);
        }
        $this->response(array('response' => false), 200);
    }
    public function getLatestPost_get() {
        $userId = $this->get('userId');
        $post = $this->blog_model->getLatestPost(array('userId'=>$userId));
        if($post){
            $this->response(array('response' => true, 'result' => $post), 200);
        }
        $this->response(array('response' => false), 200);
    }

    public function getAmountRows_get() {
        $userId = $this->get('userId');
        $amountRows = $this->blog_model->getAmountRows(array('userId' => $userId));
        $response = array('response' => true, 'result' => $amountRows);
        $this->response($response, 200);
    }

    public function savePost_get() {
        $userId = $this->get('userId');
        $content = $this->get('content');
        $title = $this->get('title');
        $this->blog_model->savePost(array('userId'=>$userId, 'content'=>$content, 'title' => $title));
    }
    
    public function deletePost_get(){
        $idPost = $this->get('idPost');
        $userId = $this->get('userId');
        $this->blog_model->deletePost(array('idPost'=>$idPost,'userId'=>$userId));
    }

}

?>