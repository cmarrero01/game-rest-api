<?php
class Statistics_model extends CI_Model {

    function __construct()
    {
        // Call the Model constructor
        parent::__construct();
        $this->load->database();
    }

    function get_profile_views($userId){
        $this->db->from('bp_usermeta');
        $this->db->select('meta_value');
        $this->db->where('idUser',$userId);
        $this->db->where('meta_key','profile_views');
        return $this->db->get()-result();
    }

    function most_played_games($userId,$userType,$limit){
        if(!empty($userId)){
            $this->db->from('bp_user_stats us');
            $this->db->select('us.gameId, g.name, count(*) as cantidad');
            $this->db->join('bp_games g','g.gameId = us.gameId');
            $this->db->where('userType',$userType);
            $this->db->where('userId',$userId);
            $this->db->group_by('us.gameId');
            $this->db->order_by('cantidad','DESC');
            if($limit){
                $this->db->limit($limit);
            }
            return $this->db->get()->result();
        }
    }

    function last_tournaments_played($userId,$userType,$limit){
        if(!empty($userId)){
            $this->db->from('bp_user_stats us');
            $this->db->select('us.result, us.tournamentId, g.name, t.stake');
            $this->db->join('bp_tournaments t','t.tournamentId = us.tournamentId');
            $this->db->join('bp_games g','g.gameId = us.gameId');
            $this->db->where('userId',$userId);
            $this->db->where('userType',$userType);
            if($limit){
                $this->db->limit($limit);
            }
            $this->db->order_by('us.date','DESC');

            return $this->db->get()->result();

        }
    }

    function total_won($userId,$userType){
        if(!empty($userId)){
            $this->db->from('bp_user_stats us');
            $this->db->select('count(result) as won');
            $this->db->where('userType',$userType);
            $this->db->where('userId',$userId);
            $this->db->where('result','1');
            return $this->db->get()->result();
        }
    }

    function total_lost($userId,$userType){
        if(!empty($userId)){
            $this->db->from('bp_user_stats us');
            $this->db->select('count(result) as lost');
            $this->db->where('userType',$userType);
            $this->db->where('userId',$userId);
            $this->db->where('result','0');
            return $this->db->get()->result();
        }
    }

    function torneos_jugados($gameId)
    {
        //
        $torneos = array(1,3,7,3,5,8);
        return $torneos;
    }

    function cantidad_plata($gameId)
    {
        //
        $torneos = 1000;
        return $torneos;
    }

    function jugadores_destacados($gameId)
    {
        //
        $jugadores = array(1,2,3,56,6);
        return $jugadores;
    }

    function promocion_torneos($gameId)
    {
        //
        $jugadores = array(1,2,3,56,6);
        return $jugadores;
    }

    function game_cash($gameId)
    {
        //
        $dinero = 1000;
        return $dinero;
    }

    function game_status($gameId)
    {
        //
        $status = true;		// 1 activo 0 no activo
        return $status;
    }

    //Obtencion de los datos del servidor de juegos
    function data_server($serverId)
    {
        $myserver =array(
            'ID'=> 2,
            'Name'=> 'server cod4',
            'IP'=>'127.0.0.1',
            'Port'=>'8080',
            'Description'=>'',
            'ModName'=>"",
            'AdminName'=>"",
            'ClanName'=>"",
            'AdminEmail'=>"",
            'GameLogLocation'=>"C:/empresa/Pro Gamer/cod4/games_mp.log",
            'LastLogLine'=>0,
            'LastLogLineChecksum'=>0,
            'PlayedSeconds'=>0,
            'ServerEnabled'=>1,
            'ParsingEnabled'=>1,
            'ftppath'=>"",
            'LastUpdate'=>"1353271982",
            'ServerLogo'=>"",
            'FTPPassiveMode'=>0,
        );

        return $myserver;
    }

    //obtengo los datos FTP del server
    function get_ftp_info($tournamentId)
    {
        $this->db->select('ip , ftpUser , ftpPass , typeFtpId , pathFile , bp_servers.status as serverStatus');
        $this->db->join('bp_servers ', 'bp_servers.serverId = bp_tournaments.tournamentId');
        $query = $this->db->get_where('bp_tournaments',array('tournamentId' => $tournamentId));

        if ($query->num_rows() > 0)
        {
            $serverInfo = $query->result();		// guardo los resultados
        }
        else
        {
            $serverInfo = false;		//el server no existe
        }

        return $serverInfo;
    }

    //Busco el server id donde esta alojado el torneo
    function get_serverId($tournamentId)
    {
        // Seleccionamos primero la base de datod del COD4
        $DB = $this->load->database("default",TRUE);

        $this->db->select('serverId');

        $query = $this->db->get_where('bp_tournaments',array('tournamentId' => $tournamentId));
        if ($query->num_rows() > 0)
        {
            $serverId = $query->result();		// guardo los resultados
        }
        else
        {
            $serverId = false;		//el server no existe
        }

        return $serverId;

    }

    // Chequeo que la cantidad de round no sea mayo a uno
    function check_rounds($serverId)
    {
        // Seleccionamos primero la base de datod del COD4
        $DB = $this->load->database("cod4",TRUE);

        $query= "SELECT DISTINCT ID FROM cod4_rounds WHERE SERVERID = '".$serverId."'";

        $query = $this->db->query($query);
        print_r($query->queries);
        if ($query->num_rows() > 0)
        {
            $rounds = $query->num_rows();
        }
        else
        {
            $rounds = false;		//el server no existe
        }

        return $rounds;
    }

    // cambio el el status del torneo a 2 (finalizado)
    function end_tournamennt($tournamentId)
    {
        //selecciono la BD default
        $DB = $this->load->database("default",TRUE);

        $data = array(
            'status' => 2 ,
        );

        $where = "tournamentId = '".  $tournamentId ."'";

        $query = $this->db->update('bp_tournaments', $data, $where);
        //print_r($query);
        if ($query)
        {
            $endTournament = true;
        }
        else
        {
            $endTournament = false;
        }

        return $endTournament;
    }
}