<?php
class Games_model extends CI_Model {

	function __construct()
	{
        // Call the Model constructor
		parent::__construct();
	}

	/**
	* <sumary>
	* 	//listado de games existentes
	* </sumary>
	**/
	public function games()
	{
        $query = $this->db->query('call getGames()');
        $result = $query->result();
		return $result;
	}

	/**
	* <sumary>
	* 	//buscar slider
	* </sumary>
	**/
	public function gamesSlider($sliderId)
	{
		$this->db->from('bp_sliders');
		$this->db->where('sliderId',$sliderId);
		$this->db->select('sliderId,name,gameId,width,height');

		//aca traeriramos el usuario y el pass para que corrensponde a ese $id
		$result = $this->db->get()->row();

		return $result;
	}

	/**
	* <sumary>
	* 	//Devuelve el listado de imagenes de un slider
	* </sumary>
	**/
	public function gamesSliderImages($sliderId)
	{
		$this->db->from('bp_slider_images as s');
		$this->db->where('s.sliderId',$sliderId);
		$this->db->select('s.imageId,s.sliderId,s.name,s.title,s.caption,s.url,s.gameId,g.gameStatus,g.description');
        $this->db->join('bp_games as g','s.gameId = g.gameId');

		//aca traeriramos el usuario y el pass para que corrensponde a ese $id
		$result = $this->db->get()->result();

		return $result;
	}

	/**
	* <sumary>
	* 	//buscar caracteristicas del juego
	* </sumary>
	**/
	public function game($gameId)
	{
		$this->db->from('bp_games');
		$this->db->where('gameId',$gameId);
		$this->db->select('gameId,name,bdStats,gameStatus,description,startDate,cashGame,featureImage');

		//Devolvemos el juego solicitado
		$result = $this->db->get()->row();

		return $result;
	}

	/**
	* <sumary>
	* 	//busca torneos segun juego y filtro
	* </sumary>
	**/
	function game_tournaments($gameId)
	{
		$this->db->from('bp_tournaments');
		$this->db->where('bp_tournaments.gameId',$gameId);
		$this->db->where('bp_tournaments.status','1');
		$this->db->or_where('bp_tournaments.status','2');
		$this->db->or_where('bp_tournaments.status','3');
		$this->db->join('bp_levels','bp_tournaments.level = bp_levels.idLevel','inner');
		$this->db->join('bp_game_modes','bp_tournaments.gameMode = bp_game_modes.idMode','inner');
		$this->db->join('bp_game_mode_server','bp_tournaments.gameModeServer = bp_game_mode_server.idGameModeServer','inner');
		$this->db->join('bp_game_maps','bp_tournaments.map = bp_game_maps.idMap','inner');
		$this->db->join('bp_tournament_status','bp_tournament_status.idStatus = bp_tournaments.status','inner');
        $this->db->join('bp_games','bp_games.gameId = bp_tournaments.gameId');
        $this->db->where('bp_games.gameStatus',1);
		$result = $this->db->get();
		return $result->result();
	}

	/**
	* <sumary>
	* 	//busca un solo torneo segun su ID
	* </sumary>
	**/
	public function game_tournament($tournamentId)
	{
		$this->db->from('bp_tournaments');
		$this->db->where('tournamentId',$tournamentId);
		$this->db->join('bp_levels','bp_tournaments.level = bp_levels.idLevel');
		$this->db->join('bp_game_modes','bp_tournaments.gameMode = bp_game_modes.idMode');
		$this->db->join('bp_game_maps','bp_tournaments.map = bp_game_maps.idMap');

		$result = $this->db->get()->result();
		return $result;
	}

	public function get_status($gameId)
	{
    	$this->db->from('bp_games');//seteo la tabla
    	$this->db->where('gameId', $gameId); //indico las sentencias WHERE necesarias
    	$this->db->select('gameId, gameStatus'); //elijo las columnas a seleccionar

    	$status = $this->db->get()->result();

    	return $status;
	}

}