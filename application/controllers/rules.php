<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Controller de las reglas de un torneo.
 *
 * @package		CodeIgniter
 * @subpackage	Rest Server
 * @category	Controller
*/

require APPPATH.'/libraries/REST_Controller.php';

class Rules extends REST_Controller
{
	public function __construct()
	{
		parent:: __construct();
		$this->load->model('rules_model');
	}

	/**
	* Devuelve las reglas de un toreno.
	* @param tournamentId
	*/
	public function getTournamentRules_get()
	{

		$rules = $this->rules_model->get_rules($this->input->get('tournamentId'));

		if($rules)
		{
			$response = array('response' => true,'result' => $rules);
			$this->response($response, 200);
		}
		else
		{
			$response = array('response' => false,'result' => 'This tournament doesn\'t have any rule');
			$this->response($response, 404);
		}

	}

	/**
	* Devuelve la descripción de una regla de toreno.
	* @param ruleId
	*/
	public function ruleDescriptionById_get()
	{
		$ruleDesc = $this->rules_model->get_description($this->input->get('ruleId'));

		if($ruleDesc)
		{
			$response = array('response' => true,'result' => $ruleDesc);
			$this->response($response, 200);
		}
		else
		{
			$response = array('response' => false,'result' => 'The rule id doesn\'t match any rule');
			$this->response($response, 404);
		}

	}
}