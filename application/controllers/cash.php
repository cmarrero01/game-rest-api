<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * //
 *
 * @package		CodeIgniter
 * @subpackage	Rest Server
 * @category	Controller
*/

require APPPATH.'/libraries/REST_Controller.php';

class Cash extends REST_Controller
{
	public function __construct()
	{
		parent:: __construct();
	}
}