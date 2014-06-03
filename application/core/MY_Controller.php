<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class MY_Controller extends CI_Controller {

    function __construct(){
        parent::__construct();
        $this->load->library('rest', array(
            'server' => $this->config->item('server'),
            'http_user' => $this->config->item('http_user'),
            'http_pass' => $this->config->item('http_pass'),
            'http_auth' => $this->config->item('http_auth')
        ));
    }
}