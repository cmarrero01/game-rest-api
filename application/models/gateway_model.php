<?php
class Gateway_model extends CI_Model {

    function __construct()
    {
        // Call the Model constructor
        parent::__construct();
		$this->load->database();
    }

    /*
     * Insertamos o actualizamos el address de paypal del usuario
     */
    public function setPaypalAddress($options=array()){
        $updateAddress = $this->updatePaypalAddress($options);
        return $updateAddress;
    }

    /*
     * Actualizamos el paypal address del usuario
     */
    private function updatePaypalAddress($options=array()){

        $userId = $options['userId'];
        if(empty($userId)){
            return false;
        }

        $email = $options['email'];
        if(empty($email)){
            return false;
        }

        $update = $this->db->update('bp_users',array('paypal_address'=>$email),array('userId'=>$userId));

        if($update){
            return true;
        }else{
            return false;
        }
    }
    /*
     * Consultamos los datos del usuario para tomar el paypalAddress existente
     */
    private function getPaypalAddress($options=array()){
        $userId = $options['userId'];
        $this->db->from('bp_users');
        if(!empty($userId)){
            $this->db->where('userId',$userId);
        }
        $this->db->select('paypal_address');
        $paypal_address = $this->db->get();

        if($paypal_address->num_rows() > 0){
            return $paypal_address->row();
        }else{
            return false;
        }
    }
    
//
//	//obtengo el importe de la apuesta para el torneo
//    function get_bet($torneId)
//    {
//		$this->db->select('userBet');
//
//        $query = $this->db->get_where('bp_tournaments',array('tournamentId ' => $torneId));
//
//		if ($query->num_rows() > 0)
//		{
//		  		$userBet = $query->result();
//		}
//		else
//		{
//			 $userBet = false;  	// no existe el id del torneo
//		}
//		return $userBet[0]->userBet;
//	}
//
//	function get_paypalAccount()
//    {
//       	// obtengo el importe de la apuesta desde la BD
//		$paypalAccount =  'fad_1354579003_biz@gmail.com';
//
//		return $paypalAccount;
//	}
//
//	//inscribo el usuario en el torneo
//	function user_inscription($userId,$torneId)
//    {
//		// verifico primero que el ususuario no este inscripto
//        $query = $this->db->get_where('bp_tournamentsPlayers',array('userId ' => $userId, 'torneId'=> $torneId));
//
//		if ($query->num_rows() > 0)
//		{
//		  		$userInscripted = true; 	// el usuario ya esta iscripto en el torneo
//		}
//		else
//		{
//			// el usuario no esta inscipto, por lo tanto lo inscribo
//			$date=(date("Y-m-d H:i:s"));
//			$data = array(
//		    'userId' => $userId ,
//		    'torneId' => $torneId ,
//		    'inscriptionDate' => $date
//			);
//
//			$query = $this->db->insert('bp_tournamentsPlayers',$data);
//
//			if ($query)
//			{
//				$userInscripted = false;
//			}
//			else
//			{
//				$userInscripted = true;
//			}
//		}
//
//		return $userInscripted;
//	}
//
//	//obtengo la cantidad de dinero a enviar a la cuenta de paypal
//	function get_historyCashId($userId)
//	{
//		$this->db->select('id');
//
//		$this->db->limit(1);
//
//		$this->db->order_by("date", "desc");
//
//        $query = $this->db->get_where('bp_historycash',array('userId ' => $userId));
//
//		if ($query->num_rows() > 0)
//		{
//		  		$moneySend = $query->result();
//		}
//		else
//		{
//			 $moneySend = false;  	// no existe el id del torneo
//		}
//
//		return $moneySend[0]->id;
//	}
//
//	// guardo el la peticion del usuario
//	function requet_money($userId,$historyCashId)
//	{
//		$date=(date("Y-m-d H:i:s"));
//
//		$data = array(
//		   'userId' => $userId ,
//		   'historyCashId' => $historyCashId ,
//		   'date' => $date,
//		   'status' => 0
//			);
//
//		$query = $this->db->insert('bp_requestmoney',$data);
//
//		return $query;
//	}
//
//	// Cambio status a 1 el request de la peticion
//	function request_success($historyCashId)
//	{
//		$data = array(
//		   'status' => 1
//			);
//
//		$where = "historyCashId = '".  $historyCashId ."'";
//
//		$query = $this->db->update('bp_requestmoney',$data, $where);
//
//		return $query;
//	}
//
//	function get_user_data($userId)
//	{
//		$this->db->select('userId , full_name , nicename , email');
//
//		$query = $this->db->get_where('bp_users',array('userId ' => $userId));
//
//		if ($query->num_rows() > 0)
//		{
//		  		$userData = $query->result();
//		}
//		else
//		{
//			 $userData = false;  	// no existe el id del torneo
//		}
//		return $userData;
//	}
//
//	//Obtengo dinero a de la peticion
//	function get_money($userId)
//	{
//		$this->db->select('cash');
//
//		$this->db->limit(1);
//
//		$this->db->order_by("date", "desc");
//
//        $query = $this->db->get_where('bp_historycash',array('userId ' => $userId));
//
//		if ($query->num_rows() > 0)
//		{
//		  		$moneySend = $query->result();
//		}
//		else
//		{
//			 $moneySend = false;  	// no existe el id del torneo
//		}
//
//		return $moneySend[0]->cash;
//	}
//
//	//Busco segun filtro las peticiones realizadas
//	function money_requests($startDate,$endDate,$status)
//	{
//		$startDate = $startDate ." 00:00:00";
//
//		$endDate = $endDate ." 00:00:00";
//
//		if ($status != "")
//		{
//			$filter = " bp_requestmoney.date >=  '".$startDate."' AND bp_requestmoney.date <=  '".$endDate."' AND bp_requestmoney.status = '".$status."'";
//		}
//		else
//		{
//			$filter = " bp_requestmoney.date >=  '".$startDate."' AND bp_requestmoney.date <=  '".$endDate."'";
//		}
//
//		$select = "bp_requestmoney.userId as requestmoneyUserId ,  bp_requestmoney.date as requestMoneyDate , bp_requestmoney.status as requestMoneyStatus  , full_name , nicename , cash ,  bp_requestmoney.date as requestMoneyDate";
//
//		$join = "JOIN bp_historycash ON bp_historycash.id = bp_requestmoney.historyCashId JOIN  bp_users ON  bp_users.userId = bp_requestmoney.userId";
//
//		$query= "SELECT ".$select." FROM bp_requestmoney ".$join." WHERE ".$filter."";
//
//		$query = $this->db->query($query);
//
//		if ($query->num_rows() > 0)
//		{
//		  		$MoneyRequests = $query->result();
//		}
//		else
//		{
//			 $MoneyRequests = false;  	// no existe el id del torneo
//		}
//
//		return $MoneyRequests;
//	}
}
?>