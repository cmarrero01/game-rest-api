<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 *  Achievements Controller
 *
 * @package		CodeIgniter
 * @subpackage	Rest Server
 * @category	Controller
 */

require APPPATH.'/libraries/REST_Controller.php';

class Achievements extends REST_Controller
{

    public function __construct()
    {
        parent:: __construct();
        $this->load->model('achievements_model');
    }

    // Devuelve la informacion completa del achievement.
    function achiev_get()				//parametro: achievementId
    {

        $achievementData = $this->achievements_model->get_achievement($this->get('achievementId'));

        if($achievementData)
        {
            $response = array('response' => true,'result' => $achievementData);

            $this->response($response, 200);
        }
        else
        {
            $response = array('response' => false,'result' => 'The achievement Id not exist');

            $this->response($response, 404);
        }
    }

    // Devuevle los achievements del usuario.
    public function achievs_get()				//parametro: userId, limit = ''
    {
        $quantity = ($this->get('limit'))? $this->get('limit') : 0;
        $userAchievements = $this->achievements_model->get_user_achievement($this->get('userId'), $quantity);


        if($userAchievements)
        {
            $response = array('response' => true,'result' => $userAchievements);

            $this->response($response, 200);
        }
        else
        {
            $response = array('response' => false,'result' => "The user Id don't have achievements");

            $this->response($response, 404);
        }
    }

    /*
	/ Devuelve una lista de todos los achievements existentes del usuario.
	*/
    public function completeList_get()				//parametro: userId
    {

        $list = $this->achievements_model->get_list_achievements();
        if($list)
        {
            $response = array('response' => true,'result' => $list);
            $this->response($response, 200);
        }
        else
        {
            $response = array('response' => false,'result' => "The database don't have achievements");
            $this->response($response, 404);
        }
    }
    public function showAchievementsBoard_get()
    {
        $user_achievements = $this->achievements_model->get_user_achievement($this->input->get('userId'));
        $all_achievs = $this->achievements_model->get_list_achievements();

        if($user_achievements)
        {
            $response = array('response' => true, 'result' => array($user_achievements,$all_achievs));
            $this->response($response, 200);
        }else{
            $response = array('response' => false,'result' => $all_achievs);
            $this->response($response, 404);
        }
    }
}

