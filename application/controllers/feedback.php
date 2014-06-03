<?php
require APPPATH.'/libraries/REST_Controller.php';

class Feedback extends REST_Controller{
    public function __construct() {
        parent::__construct();
        //Cargamos el modelo que interactúa con la base de datos
        $this->load->model('feedback_model');
        $this->load->model('sanitize_model');
    }
    
    
    //Método que envía los datos al método del modelo que guarda los datos del feedback
    public function save_get(){
        $data = $this->get();
        $data = $this->sanitize_model->sanitize($data);
        $this->feedback_model->save($data);
    }
    
    
}
?>
