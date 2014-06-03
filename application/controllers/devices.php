<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 *  Achievements Controller
 * Controlador que determina el dispositivo que está utilizando (desde donde se ha logeado) el usuario.
 * @package		CodeIgniter
 * @subpackage	Rest Server
 * @category	Controller
*/

require APPPATH.'/libraries/REST_Controller.php';

/**
 * @property mixed devices_model
 */
class Devices extends REST_Controller
{

	public function __construct()
    {
		parent:: __construct();
		$this->load->model('devices_model');
	}

    /*
     * Tomamos el dispositivo del usuario
     */
    public function index_post(){
        $deviceJson = $this->post('device');
        $idUser = $this->post('idUser');

        $device = json_decode(json_decode($deviceJson));
        $macAddress = $device->ethernet;
        $macs = '';

        if(!empty($macAddress)){
            if(is_array($macAddress)){
                foreach($macAddress as $mac){
                    $macs.= $mac->MACAddress;
                }
            }else{
                $macs = $macAddress[0]->MACAddress;
            }
        }

        $prepare = array(
            'idUser'=>$idUser,
            'device'=>serialize($deviceJson),
            'macAddress'=>$macs,
            'processor'=>$device->processor[0]->ProcessorId
        );

        $devices = $this->devices_model->getDevices($prepare);
        if($devices){
            $this->response($devices,200);
        }else{
            $newDevice = $this->devices_model->addNewDevice($prepare);
            if($newDevice){
                $this->response($newDevice,200);
            }
        }
        $this->response(array('error'=>'Problem adding device'),200);

    }
//
//    /*
//	/**
//	* Devuelve la destrucción (ó la terminación) de una sesión.
//	*/
//	public function logOut_get()
//    {
//
//		$status = $this->devices_model->logout_device('2616490720');
//		$status = $this->devices_model->checkDeviceStatus('2616490720');
//
//		if($status)
//		{
//		    $this->response('true', 200);
//		}
//		else
//		{
//			$this->response('false', 404);
//		}
//	}
//	/**
//	* Devuelve la creación de una sesión.
//	*/
//	function logIn_get()
//    {
//
//		$status = $this->devices_model->login_device('2616490720');
//		$status = $this->devices_model->checkDeviceStatus('2616490720');
//
//		if($status)
//		{
//		    $this->response('true', 200);
//		}
//		else
//		{
//			$this->response('false', 404);
//		}
//	}
//	/**
//	* Devuelve el estado de los dispositivos.
//	*/
//	public function checkDevice_get()
//    {
//
//		$status = $this->devices_model->checkDeviceStatus('2616490720');
//
//		if($status)
//		{
//		    $this->response('true', 200);
//		}
//		else
//		{
//			$this->response('false', 404);
//		}
//
//	}
//
//	/**
//	* Devuelve el dispositivo que está usando actualmente el usuario.
//	*/
//	public function checkDeviceGame_get()
//    {
//
//		$status = $this->devices_model->checkDeviceGame('2616490720');
//
//		if($status)
//		{
//		    $this->response('true', 200);
//		}
//		else
//		{
//			$this->response('false', 404);
//		}
//
//	}
//
//	/**
//	* Devuelve el dispositivo desde el cual se ha ejecutado el juego.
//	*/
//	public function lounchGame_get()
//    {
//
//		$this->devices_model->launch_game('2616490720');
//		$status = $this->devices_model->checkDeviceGame('2616490720');
//
//
//		if($status)
//		{
//		    $this->response('true', 200);
//		}
//		else
//		{
//			$this->response('false', 404);
//		}
//
//	}
//
//	/**
//	* Devuelve el dispositivo desde el cual se ha cerrado el juego.
//	*/
//	public function shotDownGame_get()
//    {
//
//		$this->devices_model->block_game('2616490720');
//		$status = $this->devices_model->checkDeviceGame('2616490720');
//		if($status)
//		{
//		    $this->response('true', 200);
//		}
//		else
//		{
//			$this->response('false', 404);
//		}
//	}
//

}

