<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Statistics Controller
 *
 * @package		CodeIgniter
 * @subpackage	Rest Server
 * @category	Controller
*/

require APPPATH.'/libraries/REST_Controller.php';

class Statistics extends REST_Controller
{
	
	public function __construct()
    {
		parent:: __construct();
		$this->load->model('statistics_model');
		$this->load->helper(array('form', 'url'));
		$this->load->library('form_validation');
		$this->load->library('Cod4stats');			//libreria para parsear COD4
    }


    public function userStats_get(){
        $userType = $this->get('userType');
        $userId = $this->get('userId');

        if(!empty($userId)){

            $mostPlayedGames = $this->statistics_model->most_played_games($userId,$userType,3);

            if(!$mostPlayedGames){
                $mostPlayedGames = false;
            }

            $totalWon = $this->statistics_model->total_won($userId,$userType);
            $totalLost = $this->statistics_model->total_lost($userId,$userType);

            $lastTournamentsPlayed = $this->statistics_model->last_tournaments_played($userId,$userType,3);

//            if($userType===0){
//                $userViews = $this->statistics_model->get_profile_views($userId);
//            }else{
//                $userViews = false;
//            }

            if(!$lastTournamentsPlayed){
                $lastTournamentsPlayed = false;
            }

            $playerStats = array('mostPlayedGames'=>$mostPlayedGames,
//                                 'profileViews'=>$userViews,
                                 'totalWon' => $totalWon[0]->won,
                                 'totalLost' => $totalLost[0]->lost,
                                 'lastTournaments' => $lastTournamentsPlayed);

            $response = array('response' => true, 'result' => $playerStats);
            $this->response($response, 200);

        }else{
            $response = array('response' => false, 'result' => 'You must provide me an UserID');
            $this->response($response, 404);
        }

    }
    public function mostPlayedGames_get(){

        $userId = $this->get('idUser');

        if(!empty($userId)){
            $mpg = $this->statistics_model->most_played_games($userId);

            if($mpg){
                $response = array('response' => true, 'result' => $mpg);
                $this->response($response, 200);
            }
        }else{
            $response = array('response' => false, 'result' => 'You must provide me an UserID');
            $this->response($response, 404);
        }
    }

	/**
	* Torneos jugados.
	* @param gameId
	*/
	public function _torneos_jugados($gameId)		
	{
				
		$torneos = $this->statistics_model->torneos_jugados($this->post('gameId'));
		return $torneos;
	}
	
	/**
	* Cantidad de plata jugada en total.
	*/
	public function _cantidad_plata($gameId)		
	{
		$plata = $this->statistics_model->cantidad_plata($gameId); 		
		
		return $plata;
	}

	/**
	* Primeros 5 jugagodres destacados.
	*/
	public function _jugadores_destacados($gameId)		
	{
		$jugadores = $this->statistics_model->jugadores_destacados($gameId); 		
		
		return $jugadores;
	}
	
	/**
	* Promocion a proximos torneos improtantes.
	*/
	public function _promocion_torneos($gameId)		
	{
		$promocion = $this->statistics_model->promocion_torneos($gameId); 		
		
		return $promocion;
	}
	
	/**
	* Retorna las estadisticas generales del juego.
	* @param gameId
	*/
	public function gameStats_post()		
    {
		//Llamo a la funciones de arriba
		$data['torneos'] = $this->_torneos_jugados($this->post('gameId'));
		$data['plata'] = $this->_cantidad_plata($this->post('gameId'));
		$data['destacados'] = $this->_jugadores_destacados($this->post('gameId'));
		$data['promocion'] = $this->_promocion_torneos($this->post('gameId'));
		
		if($data)
		{
			$response = array('response' => true,'result' => $data);
			
			$this->response($response, 200);
		}
		else
		{
			$response = array('response' => false,'result' => 'Gameid no exist');
			
			$this->response($response, 404);
		}
	}
	
	/**
	* Estado del juego que hace referncia a si esta activo, o desactivado.
	* @param gameId
	*/  
	public function gameStatus_post()		
	{
		$gameStatus = $this->statistics_model->game_status($this->post('gameId'));		//devuelve true o false dependiendo si esta activo, o desactivado
		
		if(!empty($gameStatus))
		{
			$response = array('response' => false,'result' => 'Gameid no exist');
			
			$this->response($response, 404);
		}
		if($gameStatus == true)
		{
			$response = array('response' => true,'result' => array('status'=>true));		//devolvemos true si esta activo
			
			$this->response($response, 200);
		}
		else
		{
			$response = array('response' => true,'result' => array('status'=>false));
			
			$this->response($response, 200);
		}
		
	}

	/**
	* Recibe el server ID busca en la base de datos donde se encuentra el .log con las estadisticas del juego, lo parsea y lo  guarda en la BD
	* @param serverId
	*/ 
	public function gameParse_post()		
	{
		
		//obtengo los datos del server
		$dataServer = $this->statistics_model->data_server($this->post('serverId'));
		
		//devuelve true si parseo con exito
		$status = $this->cod4stats->parse_stats($dataServer);
		
		if($status)
		{
			
		    $response = array('response' => true,'result' => 'Parsing succesfully');
			
			$this->response($response, 200);
		}
		else
		{
			$response = array('response' => false,'result' => 'Error parsing');
			
			$this->response($response, 404);
		}
		
	}
	
	/**
	* Método que limpia el archivo log y las tablas de la BD de stats del juego cuando el torneo comienza.
	* @param tournamentId
	*/
	public function clearGameStats_get()			
	{
		//obtenfo los datos FTP del server donde se encuentra el torneo
		$dataServer = $this->statistics_model->get_ftp_info($this->get('tournamentId'));
		
		if (!$dataServer)
		{
			$response = array('response' => false,'result' => "The server doesn't exist");
			
			$this->response($response, 404);
		}
		
		//print_r($dataServer);
		//echo $dataServer[0]->ip;
		
		$config['hostname'] = $dataServer[0]->ip;
		$config['username'] = $dataServer[0]->ftpUser;
		$config['password'] = $dataServer[0]->ftpPass;
		$config['debug'] = TRUE;
		
		$this->ftp->connect($config);
		
		//Subo el log vacio
		$this->ftp->upload('C:/empresa/Pro Gamer/games_mp0.log', $dataServer[0]->pathFile, 'ascii', 0777);
		
		$this->ftp->close(); 
		
		//Vacio las tablas de estadisticas del juego
		
		$DB = $this->load->database("cod4",TRUE);
		$this->db->truncate('cod4_player_kills');
		$this->db->truncate('cod4_players');
		$this->db->truncate('cod4_rounds');
		$this->db->truncate('cod4_time');
		$this->db->truncate('cod4_weapons_perserver');
		
		$response = array('response' => true,'result' => 'Clear sever complete');
			
		$this->response($response, 200);		
	}
	
	/**
	* Verifico si finalizó el torneo para cambiar el estatus ha finalizado.
	* @param tournamentId
	*/
	public function checkStatusTournament_get()			//parametro: tournamentId	
	{
		//Busco el server id donde esta alojado el torneo
		$serverId = $this->statistics_model->get_serverId($this->get('tournamentId'));
		//print_r($serverId[0]->serverId);
		// chequeo que la cantidad de round sea igual no sea mayor a 1
		$rounds = $this->statistics_model->check_rounds($serverId[0]->serverId);		
		
		//print_r($rounds);
		
		if ($rounds)
		{
			if ($rounds >1)		// si hay mas de un round finalizo el torneo
			{
				//cambio el status del torneo a 2 (finalizado)
				$this->statistics_model->end_tournamennt($this->get('tournamentId'));
				
				$response = array('response' => true,'result' => "finish");
				
				$this->response($response, 200);
			}
			else
			{
				$response = array('response' => true,'result' => "Not finish");
				
				$this->response($response, 200);
			}
		}	
		else
		{
			$response = array('response' => false,'result' => 'No rounds');
			
			$this->response($response, 404);
		}
	}
}

