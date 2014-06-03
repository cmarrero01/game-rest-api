<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * User Controller
 * Realiza transferencias entre cuentas de Paypal.
 * This is a mode of consume services for users
 * The data is mcoked.
 *
 * @package		CodeIgniter
 * @subpackage	Rest Server
 * @category	Controller
 * @author		Claudio Marrero
 * @link		http://marreroclaudio.com.ar/
*/


// This can be removed if you use __autoload() in config.php OR use Modular Extensions
require APPPATH.'/libraries/REST_Controller.php';

class Gateway extends REST_Controller
{
	
	public function __construct()
    {
		parent:: __construct();
		$this->load->model('gateway_model');
    }

    /*
     * Steamos el mail del usuario para que puodamos enviarle dinero a su cuenta de paypal
     */
    public function setPaypalAddress_get(){
        $userId = $this->get('userId');
        $email = $this->get('email');

        $updateAddress = $this->gateway_model->setPaypalAddress(array('userId'=>$userId,'email'=>$email));

        if($updateAddress){
            $response = array('response' => true,'result' =>  true);
			$this->response($response, 200);
        }else{
            $response = array('response' => false,'result' =>  false);
            $this->response($response, 404);
        }
    }

//    /**
//	* Recibe el id del torneo y ve que monto se aplica, luego toma los datos del usuario de paypal y  envia el pago a una cuenta de paypal de progamer.
//    * @param torneId, userId,
//    */
//	function gatewayPaypal_get()				//
//    {
//		//obtengo el importe a cobrar al usuario
//		$userBet =   $this->gateway_model->get_bet($this->get('torneId'));
//
//		$this->paypal_api_lib->add_nvp('RETURNURL', 'http://'.base_url().'/index.php/api/Gateway/finishPaypal/format/json?userBet='.$userBet.'&torneId='.$this->get('torneId').'&userId='.$this->get('userId').'');
//		$this->paypal_api_lib->add_nvp('CANCELURL', 'http://'.base_url().'cancel');
//		$this->paypal_api_lib->add_nvp('NOSHIPPING', '0');
//		$this->paypal_api_lib->add_nvp('ALLOWNOTE', '0');
//		$this->paypal_api_lib->add_nvp('SOLUTIONTYPE', 'Sole'); // esto es lo que no obliga a que se tenga que tener cuenta Paypal
//		$this->paypal_api_lib->add_nvp('LANDINGPAGE', 'Billing');
//		$this->paypal_api_lib->add_nvp('AMT', $userBet);
//		$this->paypal_api_lib->add_nvp('NOSHIPPING', '1');			// 1 para que no nos aparezca la direccion shipping
//		$this->paypal_api_lib->add_nvp('HDRIMG', 'http://www.battlepro.com/logo.gif');		// para que muestree el logo de battlepro en paypal
//		$this->paypal_api_lib->add_nvp('CURRENCYCODE', 'USD');
//		$this->paypal_api_lib->add_nvp('L_NAME0', 'Tournament COD4');
//		$this->paypal_api_lib->add_nvp('L_AMT0', $userBet);
//	    $this->paypal_api_lib->add_nvp('EMAIL', 'email@prueba.com');		//para nos autocomplete  esta parte en la cuenta de paypal
//
//
//		if($this->paypal_api_lib->send_api_call('SetExpressCheckout'))
//		{
//		  if (strtoupper($this->paypal_api_lib->nvp_data["ACK"]) =="SUCCESS")
//		  {
//			  $token = urldecode($this->paypal_api_lib->nvp_data["TOKEN"]);
//
//			  $payPalURL = PAYPAL_URL.$token;
//
//			  header("Location: ".$payPalURL);
//
//			  exit();
//		  }
//		}
//		else
//		{
//			$response = array('response' => false,'result' =>  paypal_errors());
//
//			$this->response($response, 404);
//		}
//
//	}
//
//	/**
//	*
//	* Finaliza el pago realizado por el usuario
//	* @param PayerID, token, userBet
//	*/
//	function finishPaypal_get()
//	{
//		$this->paypal_api_lib->add_nvp('PAYMENTREQUEST_0_PAYMENTACTION','Sale');
//		$this->paypal_api_lib->add_nvp('PAYERID',$this->get('PayerID'));
//		$this->paypal_api_lib->add_nvp('TOKEN',$this->get('token'));
//		$this->paypal_api_lib->add_nvp('PAYMENTREQUEST_0_AMT',$this->get('userBet'));
//
//		// verifico que el usuario no este ya inscripto en el torneo y luego lo inscribo
//		$userInscripted =   $this->gateway_model->user_inscription($this->get('userId'),$this->get('torneId'));
//
//		if($userInscripted)		// el usuario ya esta inscripto
//		{
//			$response = array('response' => false,'result' => 'The user is already iscripted');
//
//			$this->response($response, 404);
//		}
//		else		// el usuario todavia no inscripto por lo tanto debito el dinero
//		{
//			if($this->paypal_api_lib->send_api_call('DoExpressCheckoutPayment'))
//			{
//				$response = array('response' => true,'result' => $this->paypal_api_lib->nvp_data);
//
//				$this->response($response, 200);
//			}
//			else
//			{
//				$response = array('response' => false,'result' =>  paypal_errors());
//
//				$this->response($response, 404);
//			}
//
//		}
//
//	}
//
//	/**
//	* Envia una peticion del usuario para que le tranfieran su dinero a su cuenta de pyapal.
//	* @param userId
//	*/
//	public function requestMoney_get()
//	{
//		//obtengo el id del historyCash del usuario
//		$historyCashId =   $this->gateway_model->get_historyCashId($this->get('userId'));
//		print_r($historyCashId);
//		//guardo el la peticion del usuario
//		$requestMoney = $this->gateway_model->requet_money($this->get('userId'),$historyCashId );
//
//		// obtengo datos del usuario
//		$userData = $this->gateway_model->get_user_data($this->get('userId'));
//
//		//Obtengo dinero a de la peticion
//		$money =   $this->gateway_model->get_money($this->get('userId'));
//
//		// envio email al admin con aviso de la nueva petición
//		$this->_send_email_admin($money, $userData);
//
//		if ($requestMoney)
//		{
//			$response = array('response' => true,'result' => 'Resquest successful');
//
//			$this->response($response, 200);
//		}
//		else
//		{
//			$response = array('response' => false,'result' =>  'Error requesting money');
//
//			$this->response($response, 404);
//		}
//	}
//
//	//envia un email al administrador con la nueva peitcion de retiro de dinero
//	private function _send_email_admin($money, $userData)
//	{
//		print_r($userData);
//		$subject="New money Request";
//
//		$text = "The user Nro: ".$userData[0]->userId.". \n Name: ".$userData[0]->full_name."\n Request the money: ".$money." ";
//
//		$this->email->from('registration@progamer.com', 'Administrator');
//		$this->email->to('fadmoreno@gmail.com');
//		$this->email->subject('New money Request');
//		$this->email->message($text);
//		$this->email->send();
//	}
//	/**
//	* Indicar que se realizó la peticion de transferencia del usuario (Status = 1)
//	* @param historyCashId
//	*/
//
//	public function requesSuccess_get()		//parametro: historyCashId
//	{
//		//obtengo el id del historyCash del usuario
//		$requestSucces =   $this->gateway_model->request_success($this->get('historyCashId'));
//
//		if ($requestSucces)
//		{
//			$response = array('response' => true,'result' => 'Resquest complete');
//
//			$this->response($response, 200);
//		}
//		else
//		{
//			$response = array('response' => false,'result' =>  'Error completing the request');
//
//			$this->response($response, 404);
//		}
//	}
//
//	// Obtener un detalle de la peticiones realizadas filtrado por fecha y status
//	public function getMoneyRequest_get()	// parametros: startDate , endDate, status(opcional)
//	{
//		//busco las peticions segun el filtro
//		$MoneyRequests =   $this->gateway_model->money_requests($this->get('startDate'),$this->get('endDate'),$this->get('status'));
//
//		if($MoneyRequests)
//		{
//			$response = array('response' => true,'result' => $MoneyRequests);
//
//			$this->response($response, 200);
//		}
//		else
//		{
//			$response = array('response' => false,'result' =>  'No money request');
//
//			$this->response($response, 404);
//		}
//	}
}

