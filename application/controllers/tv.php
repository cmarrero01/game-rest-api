<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Tournaments Controller
 *
 * @package		CodeIgniter
 * @subpackage	Rest Server
 * @category	Controller
 */

require APPPATH.'/libraries/REST_Controller.php';

/**
 * @property mixed Tournaments_model
 */
class tv extends REST_Controller
{

    public function __construct()
    {
        parent:: __construct();
        $this->load->model('Tournaments_model');
    }

    /*
     * Traemos el torneo por su id y por ende el file del demo
     */
    public function index_get(){
        $torneId = $this->get('torneId');
        if($torneId){
            $path = $_SERVER['DOCUMENT_ROOT'].'/application/libraries/csgo/'.$torneId.'/';
            if(file_exists($path.'battletv_'.$torneId.'.dem')){
                //$demo = file_get_contents($path.'battletv_'.$torneId.'.dem');
                $demo = base64_encode($path);
                $result = array('fileName'=>'battletv_'.$torneId.'.dem','fileDem'=>$demo);
                $this->response(array('response'=>true,'result'=>$result),200);
            }
            $this->response(array('response'=>false,'result'=>'The file doesnt exist. '),200);
        }
        $this->response(array('response'=>false,'result'=>'The tournament id is wrong'),200);
    }

}

