<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Endpoints for all rules for BP points
 *
 * @package		CodeIgniter
 * @subpackage	Rest Server
 * @category	Controller
*/

require APPPATH.'/libraries/REST_Controller.php';

/**
 * @property mixed user_model
 * @property mixed points_model
 */
class Points extends REST_Controller
{
	public function __construct()
	{
		parent:: __construct();
        $this->load->model('points_model');
	}

    /*
     * Sumar points
     */
    public function scorePointsToUser_get(){
        $userId = $this->get('userId');

        $userPoints = $this->points_model->userPoints($userId);

        //Si nos devuelve false, es por que hubo problemas con el usuario
        if(!$userPoints){
            $this->response(array('response'=>false,'result'=>'You don\'t have points'), 200);
        }
        //Si tenemos mas de 100, retornamos false.
        if(!$this->checkAmountPoint($userPoints->points)){
            $this->response(array('response'=>false,'result'=>'You have more than 100 points'), 200);
        }

        //Chequeamos el ultimo movimiento de points y vemos si ya paso 1:30.
        if(!$this->checkTimeLastPoint($userPoints)){
            $this->response(array('response'=>false,'result'=>'Not yet been an hour and a half.'), 200);
        }
        //Aca le doy 500 puntos
        $plusPoint = $this->plusPoints($userPoints->points,$userId,500);

        if($plusPoint){
            $this->response(array('response'=>true,'result'=>$plusPoint), 200);
        }else{
            $this->response(array('response'=>false), 200);
        }

    }

    /**
     * Devuelve los puntos que tiene acumulado el usuario
     * @param userId
     */
    public function userPoints_get()
    {
        $userId = $this->get('userId');
        $history = $this->get('is_history');

        $args = array('userId'=>$userId);

        if($history){
            //busco la cantidad actual de puntos del usuario
            $userPoints = $this->points_model->userHistoryPoints($args);
        }else{
            //busco la cantidad actual de puntos del usuario
            $userPoints = $this->points_model->userPoints($userId);
        }

        if($userPoints){
            $response = array('response' => true,'result' => $userPoints);
            $this->response($response, 200);
        }else{
            $response = array('response' => false,'result' => 'User id no exist');
            $this->response($response, 404);
        }
    }

    /*
     * Get ranking user
     */
    public function getRanking_get(){
        $userId = $this->get('userId');
        $prepare_data = array(
            'userId'=>$userId
        );
        $ranking = $this->points_model->getRankingUsers($prepare_data);
        if($ranking){
            $this->response(array('response'=>true,'result'=>$ranking),200);
        }
        $this->response(array('response'=>false),200);
    }

    /*
     * Metodo que utilizamos para darle al usuario
     */
    public function userWinPoints_get(){

        $userId = $this->get('userId');
        $amountPoints = $this->get('amountPoints');
        //busco la cantidad actual de puntos del usuario
        $userPoints = $this->points_model->userPoints($userId);

        //Si nos devuelve false, es por que hubo problemas con el usuario
        if(!$userPoints){
            $this->response(array('response'=>false,'result'=>'You don\'t have points'), 200);
        }

        $plusPoint = $this->plusPoints($userPoints->points,$userId,$amountPoints);

        if($plusPoint){
            $this->response(array('response'=>true,'result'=>$plusPoint), 200);
        }else{
            $this->response(array('response'=>false), 200);
        }
    }

    /////////////////////// PRIVATE METHODS ////////////////////////////////
    /*
     * Verificamos el monto, si es menor que 100 permitimos sumar
     */
    private function checkAmountPoint($point){
        if($point < 100){
            return true;
        }

        return false;
    }

    /*
     * Chequeamos que hayan pasado mas de 1h y 30m de la ultima vez que le dimos points
     */
    private function checkTimeLastPoint($points){
        //Tomamos la fecha y hora actual
        $time = time();
        $dateTime = date('Y-m-d h:i:s',$time);

        //Fecha de ultima entrega de points
        $lasTime = $points->date;

        $now = new DateTime($dateTime);
        $lasTime = new DateTime($lasTime);

        $interval = date_diff($now,$lasTime);

        if($interval->y > 0 or $interval->m > 0 or $interval->d > 0 or $interval->days > 0){
            return true;
        }

        if($interval->h >= 1 and $interval->i >= 30){
            return true;
        }

        return false;
    }

    /*
     * Le sumamos puntos al usuario (este metodo puede ser usado para torunaments).
     */
    private function plusPoints($points,$userId,$morePoints){
        $options = array(
            'morePoints'=>$morePoints,
            'userId'=>$userId,
            'points'=>$points
        );
        $morePoints = $this->points_model->plusPointsHistory($options);
        if($morePoints){
            return $morePoints;
        }else{
            return false;
        }
    }
}