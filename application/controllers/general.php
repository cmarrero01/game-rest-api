<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 *  EndPoints de uso general para todo el sistema.
 *
 * @package		CodeIgniter
 * @subpackage	Rest Server
 * @category	Controller
*/

require APPPATH.'/libraries/REST_Controller.php';

class General extends REST_Controller
{
	
	public function __construct()
    {
		parent:: __construct();
		$this->load->model('general_model');
	}
	
	/**
	* Devuelve la lista de países.
	* @param countryId
	*/
	public function countryList_get()
    {
		
		$countries = $this->general_model->get_countries($this->get('countryId'));

		if($countries)
		{
			$response = array('response' => true,'result' => $countries);
		    $this->response($response, 200);
		}
		else
		{
			$response = array('response' => false,'result' => 'The achievement Id not exist');
			$this->response($response, 404);
		}
	}
	
}

