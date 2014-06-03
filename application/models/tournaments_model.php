<?php
/**
 * <sumary>
 * Modelo del los torneos.
 * </sumary>
 **/


class Tournaments_model extends CI_Model {

    function __construct()
    {
        parent::__construct();
    }

    /*
    * Join user to the tournament
    *
    */
    public function joinUserTournament($options=array()){
        $usersJoined = $this->get_users_inscripted($options['tournamentId']);
        $insert = true;
        if($usersJoined){
            foreach($usersJoined as $users){
                if($users->userId == $options['userId']){
                    $insert = false;
                    break;
                }
            }
        }else{
            $insert = true;
        }

        if($insert){
            $args = array(
                'torneId'=>$options['tournamentId'],
                'userId'=>$options['userId'],
                'group_type_id' => $options['group_type_id'],
                'inscriptionDate'=>date('Y-m-d h:i:s',time()),
                'teamId' => $options['teamId'],
                'active' => $options['active'],
                'invited' => $options['invited']
            );

            $insert = $this->db->insert('bp_tournamentsplayers',$args);

            if($insert){
                return true;
            }else{
                return false;
            }
        }

        return $insert;
    }

    /*
    * Out of user of the tournament
    *
    */
    public function outOfTournament($options=array()){
        $default['tournamentId'] = '';
        $default['userId'] = '';
        $set = $this->functions->merge($default,$options);

        $this->db->where('userId',$set->userId);
        $this->db->where('torneId',$set->tournamentId);
        return $this->db->delete('bp_tournamentsplayers');
    }

    //Inserta el torneo en la BD
    public function add_tournament($tourneData=array())
    {
        $query = $this->db->insert('bp_tournaments',$tourneData);
        return $query;
    }

    /*
    * Filtro de torneos
    * usado para TV en particual
    */
    public function filter_tournaments($filter)
    {
        $filterData ="";
        foreach($filter as $key => $data)
        {
            if(!empty($data))
            {
                if($key == 't.creationDate')
                {
                    $filterData .= " AND $key like '%".$data."%'";
                }
                elseif($key == 'pageOffset' || $key == 'limit'){

                }
                elseif( $key == 'orderBy'){
                    $filterData .=' ORDER BY '.$data[0].' '.$data[1];
                }
                else
                {
                    $filterData .= " AND $key = '".$data."'";
                }
            }
        }

        $query = "SELECT *
		FROM bp_tournaments as t
		JOIN bp_servers as s ON s.serverId = t.serverId
		INNER JOIN bp_game_maps m ON m.idMap = t.map
		INNER JOIN bp_game_modes mo ON mo.idMode = t.gameMode
		WHERE 1=1 ".$filterData;
        /*if(isset($filter['orderBy']) && $filter['orderBy']){
            $query.="ORDER BY ".$filter['orderBy'][0]." ".$filter['orderBy'][1];
        }else{
            $query.="ORDER BY t.tournamentName asc";
        }*/
        if($filter['limit']){
            $query .= " LIMIT ".$filter['limit'];
        }
        if($filter['pageOffset']){
            $query .= " OFFSET ".$filter['pageOffset'];
        }

        $query = $this->db->query($query);

        if ($query->num_rows() > 0)
        {
            $tournaments = $query->result();
        }
        else
        {
            $tournaments = false;  	// no hay torneos para ese filtro
        }

        return $tournaments;
    }

    /**
     * @return mixed
     */
    public function getLastPage($status){

        return $this->db->query("select count(tournamentId) as tournamentsCount from bp_tournaments where status = ".$status."")->result();
    }


    //Finaliza un torneo
    public function finished_tournament($tournamentId, $date)
    {
        // verifico primero que exita el torneo
        $query = $this->db->get_where('bp_tournaments',array('tournamentId' => $tournamentId));

        if ($query->num_rows() > 0)
        {
            $updateData = array(
                'status' => '4',
                'endDate ' => $date
            );

            $where = "tournamentId = '".  $tournamentId ."'";

            $query = $this->db->update('bp_tournaments', $updateData, $where);

            return $query;
        }
        else		//el torneo no exite
        {
            return false;

        }

    }

    //Edita un torneo
    function edit_tournament($tournamentId,$editData)
    {
        // verifico primero que exita el torneo
        $query = $this->db->get_where('bp_tournaments',array('tournamentId' => $tournamentId));

        if ($query->num_rows() > 0)
        {
            $updateData = array();
            foreach($editData as $key => $data)
            {
                if(!empty($data))
                {
                    $updateData = array(
                        $key => $data
                    );
                }
            }
            $where = "tournamentId = '".  $tournamentId ."'";

            $query = $this->db->update('bp_tournaments', $updateData, $where);

            return $query;
        }
        else		//el torneo no exite
        {
            return false;

        }
    }


    /*
    * Chequea si esta terminado un torneo
    *
    */
    public function check_finished_tournament($tournamentId)
    {
        /*El status 0 es el terminado*/
        $query = $this->db->get_where('bp_tournaments',array('tournamentId' => $tournamentId,'status' => 4));

        if ($query->num_rows() > 0)
        {
            $tournamentDates = $query->result();
        }
        else
        {
            $tournamentDates = false;
        }

        return $tournamentDates;

    }

    public function my_registered_team($tournamentId = 0, $userId = 0){

        $this->db->select('teamId');
        $this->db->where('userId', $userId);
        $this->db->where('torneId', $tournamentId);
        $result = $this->db->get('bp_tournamentsplayers');

        if($result->num_rows()>0){
            $result = $result->result();
            return $result[0];
        }else{
            return false;
        }

    }

    /**
     * Cambiar el estado de un torneo
     *
     * @params: idTournament, status
     */
    public function changeStatusTournament($idTournament, $status){

        if(!$this->check_finished_tournament($idTournament)){
            $data = array(
                'status'=>$status
            );
            switch($status){
                case 2:
                    $data['startDate'] = (date("Y-m-d H:i:s"));
                    break;
                case 4:
                    $data['endDate'] = (date("Y-m-d H:i:s"));
                    break;
            }
            $this->db->where('tournamentId', $idTournament);
            $this->db->update('bp_tournaments', $data);
        }
    }

    /*
    * Obtengo todos los datos de los usuarios inscriptos en el torneo
    *
    *
    *
    */

    public function get_users_inscripted($tournamentId)
    {

        $this->db->from('bp_tournamentsplayers');
        $this->db->join('bp_users', 'bp_tournamentsplayers.userId = bp_users.userId');
        $this->db->where('torneId',$tournamentId);
        $this->db->where('bp_tournamentsplayers.invited',0);
        $this->db->where('bp_tournamentsplayers.active',1);
        $result = $this->db->get();

        if ($result->num_rows() > 0){
            $usersInscripted = $result->result();
        }else{
            $usersInscripted = false;
        }

        return $usersInscripted;
    }

    public function get_allusers_inscripted($tournamentId)
    {

        $this->db->from('bp_tournamentsplayers');
        $this->db->join('bp_users', 'bp_tournamentsplayers.userId = bp_users.userId');
        $this->db->where('torneId',$tournamentId);
        $this->db->where('bp_tournamentsplayers.invited',0);
        $this->db->where('bp_tournamentsplayers.readyToPlay',1);
        $this->db->where('bp_tournamentsplayers.active',1);
        $result = $this->db->get();

        if ($result->num_rows() > 0){
            $usersInscripted = $result->result();
        }else{
            $usersInscripted = false;
        }

        return $usersInscripted;
    }

    // Obtengo las cantidad de usuarios que ha madtado cada usuario
    function get_kills_by_users($users)
    {

        // Seleccionamos primero la base de datod del COD4
        $DB = $this->load->database("cod4",TRUE);

        $filter = "";
        $i=0;
        foreach ($users as $user)
        {
            if ($i==0)
            {
                $filter .= " Alias = '".$user['Alias']."'";
            }
            else
            {
                $filter .= " OR Alias = '".$user['Alias']."'";
            }
            $i++;

        }

        $query = $this->db->query("SELECT Alias , Kills , Deaths FROM  cod4_aliases as a JOIN cod4_players as p ON p.GUID = a.PLAYERID WHERE ".$filter." ORDER BY Kills DESC");

        if ($query->num_rows() > 0)
        {
            $tournamentRounds = $query->result();
        }
        else
        {
            $tournamentRounds = false;
        }

        return $tournamentRounds;
    }

    // Determino el ganador del torneo desde la BD con las estadisticas del juego
    function get_winner($users)
    {
        // Seleccionamos primero la base de datos del COD4
        $DB = $this->load->database("cod4",TRUE);

        $filter = "";
        $i=0;
        foreach ($users as $user)
        {
            if ($i==0)
            {
                $filter .= " Alias = '".$user['Alias']."'";
            }
            else
            {
                $filter .= " OR Alias = '".$user['Alias']."'";
            }
            $i++;

        }

        $query =  $this->db->query("SELECT Alias , Kills , Deaths FROM  cod4_aliases as a JOIN cod4_players as p ON p.GUID = a.PLAYERID WHERE Kills=(SELECT max(Kills) FROM  cod4_aliases as a JOIN cod4_players as p ON p.GUID = a.PLAYERID WHERE  ".$filter.") AND ( ".$filter.") ORDER BY Kills DESC") ;
        //$query = $this->db->query("SELECT Alias , Kills , Deaths FROM  cod4_aliases as a JOIN cod4_players as p ON p.GUID = a.PLAYERID WHERE Kills=(SELECT MAX(Kills) FROM cod4_aliases) AND ".$filter." ORDER BY Kills DESC");
        if ($query->num_rows() > 0)
        {
            if ($query->num_rows() == 1)		// si hay solo resultado hay un solo usuario con la mayor cantdad de muertos
            {
                $userWinner = $query->result();

            }
            else		// hay mas de un usuario con la misma cantidad de muerte, entonces me fijo en la que tenga menor cantidad de muertes propias
            {
                $userWinner = $query->result();

                $filter = "";
                $i=0;
                foreach ($userWinner as $user)
                {
                    if ($i==0)
                    {
                        $filter .= " Alias = '".$user->Alias."'";
                    }
                    else
                    {
                        $filter .= " OR Alias = '".$user->Alias."'";
                    }
                    $i++;

                }
                echo $filter;

                $query = $this->db->query("SELECT Alias , Kills , Deaths FROM  cod4_aliases as a JOIN cod4_players as p ON p.GUID = a.PLAYERID WHERE Deaths=(SELECT min(Deaths) FROM  cod4_aliases as a JOIN cod4_players as p ON p.GUID = a.PLAYERID WHERE  ".$filter.") AND ( ".$filter.") ORDER BY Deaths ASC");

                $userWinner = $query->result();
            }
        }
        else
        {
            $userWinner = false;
        }

        return $userWinner;

    }

    //Guardo al usuario ganador
    function save_winner($userWinner,$tournamentId)
    {
        $DB = $this->load->database("default",TRUE);

        // obtengo el userId del nicename
        $this->db->select('userId');

        $query = $this->db->get_where('bp_users',array('nicename' => $userWinner[0]->Alias));

        $userId = $query->result();

        $data = array(
            'torneId' => $tournamentId ,
            'userId' => $userId[0]->userId,
            'position' => "1"
        );

        $query = $this->db->insert('bp_winners',$data);
    }

    //Selecciono el usuario ganador
    function select_winner($torneId)
    {
        $DB = $this->load->database("default",TRUE);

        // obtengo el userId del nicename
        $this->db->select('userId');

        $query = $this->db->get_where('bp_winners',array('torneId' => $torneId));

        if ($query->num_rows() > 0)
        {
            $winner = $query->result();
        }
        else
        {
            $winner = false;
        }

        return $winner;
    }
    function get_BpWinner($tournamentId){

        $query = $this->db->query("SELECT u.userId, nicename, w.position FROM bp_users AS u JOIN bp_winners AS w ON u.userId = w.userId WHERE torneId ='".$tournamentId."' ORDER BY w.position");

        if ($query->num_rows() >= 1) {

            return $query->result();
        }else{
            //tratar el error si vienen cero filas
        }


    }

    //Obtengo la cantidad de inscriptos
    function users_quantity($torneId)
    {
        $DB = $this->load->database("default",TRUE);

        // obtengo el userId del nicename
        $query= "SELECT count(userId) as usersQuantity FROM bp_tournamentsplayers WHERE torneId = '".$torneId."'";

        $query = $this->db->query($query);

        if ($query->num_rows() > 0)
        {
            $usersQuantity = $query->result();
        }
        else
        {
            $usersQuantity = false;
        }

        $usersQuantity = $usersQuantity[0]->usersQuantity;

        return $usersQuantity;
    }

    //Obtengo la apuesta del torneo
    function tournament_bet($torneId)
    {
        $DB = $this->load->database("default",TRUE);

        // obtengo el userId del nicename
        $this->db->select('userBet');

        $query = $this->db->get_where('bp_tournaments',array('tournamentId' => $torneId));

        if ($query->num_rows() > 0)
        {
            $userBet = $query->result();
        }
        else
        {
            $userBet = false;
        }

        $userBet = $userBet[0]->userBet;

        return $userBet;
    }

    //obtengo el dinero actual que posee el usuario
    function get_actual_cash($winner)
    {
        $DB = $this->load->database("default",TRUE);

        // obtengo el userId del nicename
        $this->db->select('cash');

        $query = $this->db->get_where('bp_historycash',array('userId' => $winner));


        if ($query->num_rows() > 0)
        {
            $actualCash = $query->result();

            $actualCash = $actualCash[0]->cash;

        }
        else
        {
            $actualCash = 0;
        }

        return $actualCash;
    }

    //obtengo el dinero actual que posee el usuario
    function save_new_cash($userId,$newCash)
    {
        $date=(date("Y-m-d H:i:s"));

        $updateData = array(
            'userId' => $userId,
            'cash' => $newCash,
            'date' => $date
        );

        $query = $this->db->insert('bp_historycash', $updateData);

        return $query;
    }

    /*
    *
    *
    * 	Trae una lista de torneos
    *
    *
    */
    public function getTournaments($options=array())
    {
        $default['tournamentId'] = '';
        $default['status'] = '';
        $default['status_or'] = '';
        $default['reply'] = false;
        $default['players'] = false;
        $default['for_parse'] = false;
        $set = $this->functions->merge($default,$options);

        $this->db->from('bp_tournaments');

        if(!empty($set->tournamentId)){
            $this->db->where('tournamentId',$set->tournamentId);
        }

        if(isset($set->gameId) and !empty($set->gameId)){
            $this->db->where('gameId',$set->gameId);
        }

        if(!empty($set->status)){
            $this->db->where('bp_tournaments.status',$set->status);
        }

        if(!empty($set->status_or)){
            $this->db->or_where('bp_tournaments.status',$set->status_or);
        }

        if(!$set->reply){
            $this->db->join('bp_levels','bp_tournaments.level = bp_levels.idLevel');
            //$this->db->join('bp_game_modes','bp_tournaments.gameMode = bp_game_modes.idMode');
            $this->db->join('bp_game_maps','bp_tournaments.map = bp_game_maps.idMap');
            $this->db->join('bp_games','bp_tournaments.gameId = bp_games.gameId');
            $this->db->join('bp_servers','bp_tournaments.serverId = bp_servers.serverId');
        }



        $query = $this->db->get();
        $result = $query->result();

        if($set->players){
            if($query->num_rows() > 0){
                foreach($result as $item){
                    $this->db->from('bp_tournamentsplayers');
                    $this->db->where('torneId',$item->tournamentId);

                    if(!empty($set->status) and isset($set->steamJoin)){
                        $this->db->join('bp_users_steam','bp_users_steam.userId = bp_tournamentsplayers.userId');
                    }

                    if($set->for_parse){
                        $this->db->join('bp_users','bp_tournamentsplayers.userId = bp_users.userId');
                    }

                    $players = $this->db->get();

                    if($players->num_rows() > 0){
                        $item->players = $players->result();
                        $item->totalPlayers = 0;
                        $item->invitedPlayers = 0;
                        foreach ($item->players as $player) {
                            if($player->invited == 0){
                                $item->totalPlayers++;
                            }else{
                                $item->invitedPlayers++;
                            }

                        }


                    }else{
                        $item->players = '';
                        $item->totalPlayers = 0;
                    }

                }
            }
        }

        return $result;
    }

    /*
    *
    *
    * 	Get Tournaments by userId
    *
    *
    */
    public function getTournamentsByUser($options=array()){

        $default['userId'] = '';
        $set = $this->functions->merge($default,$options);

        $this->db->from('bp_tournamentsplayers');
        $this->db->join('bp_users', 'bp_tournamentsplayers.userId = bp_users.userId');

        if(!empty($set->userId)){
            $this->db->where('bp_tournamentsplayers.userId',$set->userId);
            $this->db->where('bp_tournamentsplayers.invited',0);
        }

        return $this->db->get()->result();
    }

    /*
    *
    *
    * 	Get Tournaments from today by userId
    *
    *
    */
    public function getTournamentsByUserFromToday($options=array()){

        $default['userId'] = '';
        $default['date'] = '';
        $set = $this->functions->merge($default,$options);

        $this->db->from('bp_tournamentsplayers');
        $this->db->join('bp_tournaments', 'bp_tournamentsplayers.torneId = bp_tournaments.tournamentId');

        if(!empty($set->userId)&&!empty($set->date)){
            $this->db->where('bp_tournamentsplayers.userId',$set->userId);
            $this->db->where('bp_tournamentsplayers.invited',0);
            $this->db->like('bp_tournamentsplayers.inscriptionDate',$set->date);
            $this->db->where('bp_tournaments.status','4');
        }
        return $this->db->get()->result();
    }

    /*
    *
    * Elimina al usuario de los demas torneos
    *
    *
    */

    public function outUsersOfTournament($options=array()){

        $default['torneId'] = '';
        $default['userId'] = '';
        $set = $this->functions->merge($default,$options);

        $this->db->where('userId', $set->userId);

        if(!empty($set->torneId)){
            $this->db->where('torneId !=', $set->torneId);
        }

        if($this->db->delete('bp_tournamentsplayers')){
            return true;
        }else{
            return false;
        }
    }


    /**
     * Consulta si un usuario esta registrado en un torneo en estado 1
     *
     * @param tournamentId
     * @param userId
     */
    public function isUserRegistered($tournamentId, $userId){

        $this->db->select('tournamentId,active');
        $this->db->from('bp_tournaments');
        $this->db->join('bp_tournamentsplayers', 'bp_tournaments.tournamentId = bp_tournamentsplayers.torneId');
        $this->db->where('bp_tournaments.tournamentId', $tournamentId);
        $this->db->where('bp_tournamentsplayers.userId', $userId);
        $this->db->where('bp_tournaments.status', 1);
        $this->db->where('bp_tournamentsplayers.invited', 0);
        $result = $this->db->get()->result();

        if(!empty($result)){
            return $result[0];
        }

    }

    public function registeredTeams($tournamentId = 0){
        if($tournamentId != 0){
            return $this->db->query('select distinct(teamId) as TEAM,(select count(userId) from bp_tournamentsplayers where teamId = TEAM and torneId = '.$tournamentId.' and active=1 and invited=0) as totalUsers, teamName, avatarPath, group_type_id from bp_tournamentsplayers join bp_teams on bp_tournamentsplayers.teamId = bp_teams.idTeam WHERE torneId = '.$tournamentId)->result();
        }else{
            return false;
        }

    }

    public  function joinTeamTournament($tournament, $user, $team, $group,$isInvited){

        $proceed = $this->isTeamFull($tournament, $team);

        if($proceed){
            return false;
        }else{
            if(isset($isInvited)&&$isInvited){
                $insert = $this->joinUserTournament(array('tournamentId'=>$tournament, 'userId'=>$user, 'group_type_id'=>$group,'teamId'=>$team,'active'=>1,'invited'=>$isInvited));
            }else{
                $invited = $this->isUserInvited($tournament, $user);
                if($invited){
                    $data = array(
                        'invited' => $isInvited
                    );
                    $this->db->where('userId',$user);
                    $this->db->where('torneId',$tournament);
                    $this->db->update('bp_tournamentsplayers',$data);
                }else{
                    $insert = $this->joinUserTournament(array('tournamentId'=>$tournament, 'userId'=>$user, 'group_type_id'=>$group,'teamId'=>$team,'active'=>1,'invited'=>0));
                }
            }
            if($insert){
                return true;
            }else{
                return false;
            }
        }


    }

    public function get_teammembers_to_invite($teamId,$tournamentId){

        $query = 'select u.userId, u.nicename from bp_teams_members as tm join bp_users as u on tm.idUser = u.userId where tm.idTeam = '.$teamId.' and tm.idUser not in (SELECT p.userId from bp_tournamentsplayers as p where p.torneId = '.$tournamentId.' AND p.teamId = '.$teamId.')';

        return $this->db->query($query)->result();
    }

    public function isTeamFull($tournament, $team){
        $this->db->select('count(distinct(userId)) as countUser, maxusers');
        $this->db->from('bp_tournamentsplayers p');
        $this->db->join('bp_tournaments t ', 'p.torneId = t.tournamentId ');
        $this->db->where('p.teamId ',$team);
        $this->db->where('t.tournamentId ',$tournament);
        $this->db->where('p.active ','1');
        $this->db->where('p.invited','0');
        $this->db->limit(1);
        $result = $this->db->get()->result();
        $result = $result[0];
        if($result->countUser == ($result->maxusers / 2)){
            return true;
        }else{
            return false;
        }
    }

    public function inactivate_otherTeams($tournament, $team, $group){
        $this->db->query('update bp_tournamentsplayers set active = 0 where teamId != '.$team.' and group_type_id ='.$group.' and torneId = '.$tournament);
    }

    public function canRegisterTeam($tournament = 0, $team = 0, $group = 0){

        //verifica que hayan menos de 3 teams registrados sin contar el suyo.
        $this->db->select('count(distinct teamId) as cantidadTeams');
        $this->db->where('torneId', $tournament);
        $this->db->where('group_type_id', $group);
        $this->db->where('teamId !=', $team);
        $result = $this->db->get('bp_tournamentsplayers');
        if($result->num_rows > 0){
            $result = $result->result();
            if($result[0]->cantidadTeams < 3){
                return true;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }

    public function getCurrent_Tournaments($userId){
        //select count(userId) as registered, torne.teamId from bp_tournamentsplayers as torne  join (select distinct(teamId)from bp_tournamentsplayers as tt where tt.group_type_id = 2 and tt.torneId = 4 and tt.invited = 0 and tt.active = 1) as teams on teams.teamId = torne.teamId group by torne.teamId order by registered desc limit 1

        $this->db->select('t.tournamentId, t.gameMode , p.invited ,t.startDate,maxUsers,t.gameId, t.status, t.tournamentName,s.ip,s.port, (select count(distinct userId) from bp_tournamentsplayers where torneId = tournamentId and active = 1 and invited=0) as cantidad');
        $this->db->from('bp_tournamentsplayers p');
        $this->db->join('bp_tournaments t','p.torneId = t.tournamentId');
        $this->db->join('bp_servers s','t.serverId = s.serverId');
        $this->db->where('p.userId',$userId);
        $this->db->where('p.active',1);
        $this->db->where('t.status in (1,2)');

        $result = $this->db->get();

        if($result->num_rows()>0){
            return $result->result();
        }else{
            return false;
        }

//        $result = $this->db->query('select distinct t.tournamentId, p.invited ,t.startDate, u.nicename,(select count(distinct userId) from bp_tournamentsplayers where torneId = tournamentId and active = 1 and invited = 0) as cantidad, maxUsers, t.status, name from bp_tournamentsplayers p join bp_tournaments t on p.torneId = t.tournamentId join bp_users u on p.invited = u.userId where p.userId = '.$userId.' and t.status in (1,2) and p.active = 1');

    }

    public function set_ready($user, $tournament){

        $this->db->where('torneId',$tournament);
        $this->db->where('userId',$user);
        $data = array('readyToPlay' => 1);
        $this->db->update('bp_tournamentsplayers',$data);

        $success = $this->i_am_ready($tournament, $user);

        if($success){
            return true;
        }else{
            return false;
        }
    }

    public function acceptInvite($userId,$tournamentId){
        if($userId && $tournamentId){
            $this->db->where('userId',$userId);
            $this->db->where('torneId',$tournamentId);
            $data = array('invited' => 0);
            $this->db->update('bp_tournamentsplayers',$data);

            return true;
        }else{
            return false;
        }
    }

    public function isTournamentReady($tournament){

        $this->db->from('bp_tournamentsplayers p');
        $this->db->select('count(distinct p.userId) as ready, t.maxUsers, s.ip, s.ftpUser');
        $this->db->join('bp_tournaments t ','torneId = tournamentId');
        $this->db->join('bp_servers s', 's.serverId = t.serverId');
        $this->db->where('torneId',$tournament);
        $this->db->where('readyToPlay',1);
        $result = $this->db->get()->result();

        return $result[0];


    }

    public function setUserStatsAfterTournament($data,$userType,$tournamentId,$gameId){

        $date = (date("Y-m-d H:i:s"));
        //es un team
        if($userType==1){
            if(isset($data['teamWinner'])&&isset($data['teamLoser'])&&!empty($data['teamWinner'])&&!empty($data['teamLoser'])){
                $insert = array(
                    array(
                        'userType' => $userType,
                        'userId' => $data['teamWinner'],
                        'gameId' => $gameId,
                        'tournamentId' => $tournamentId,
                        'result' => 1,
                        'date' => $date
                    ),
                    array(
                        'userType' => $userType,
                        'userId' => $data['teamLoser'],
                        'gameId' => $gameId,
                        'tournamentId' => $tournamentId,
                        'result' => 0,
                        'date' => $date
                    )
                );
            }else{
                return false;
            }
        }else{
            $insert = array();
            if(isset($data['losersIds'])&&!empty($data['losersIds'])){
                foreach($data['losersIds'] as $player){
                    if($player->userId == $data['winnerId']){
                        $result = 1;
                    }else{
                        $result = 0;
                    }
                    $user = array(
                        'userType' => $userType,
                        'userId' => $player->userId,
                        'gameId' => $gameId,
                        'tournamentId' => $tournamentId,
                        'result' => $result,
                        'date' => $date
                    );
                    array_push($insert,$user);
                }
            }else{
                return false;
            }
        }
        $result = $this->db->insert_batch('bp_user_stats',$insert);
        if($result){
            return true;
        }else{
            return false;
        }
    }


    public function usersAlreadyPlaying($idTournament = 0){
        if($idTournament){

            // select userId from bp_tournaments t join bp_tournamentsplayers p on p.torneId = t.tournamentId where status = 3
            $result = $this->db->query('select userId as player,(select tournamentId from bp_tournaments t join bp_tournamentsplayers tp on t.tournamentId = tp.torneId where t.status = 3 and tp.userId = player ) as torne from bp_tournamentsplayers where torneId = '.$idTournament.'');

            if($result->num_rows() > 0){
                return $result->result();
            }else{
                return false;
            }

        }
    }
    /**
     * Verifica en que bando hay lugar para registrar
     * @param: idTournament
     *
     */
    public function thereAreSlots($idTournament = 0){

        if($idTournament){

            //Mansa query claudio, te la debo para los stored procedures

            $result = $this->db->query('select maxUsers, (select count(userId) from bp_tournamentsplayers where torneId = '.$idTournament.' and group_type_id = 1 and invited = 0 and active = 1) as group_1, (select count(userId) from bp_tournamentsplayers where torneId = '.$idTournament.' and group_type_id = 2 and invited = 0 and active = 1) as group_2 from bp_tournaments where tournamentId = '.$idTournament);

            return $result->result();

        }else{
            return false;
        }

    }

    public function whoInvitedMe($userId){
        if(!empty($userId)){
            $this->db->from('bp_users');
            $this->db->select('nicename');
            $this->db->where('userId',$userId);
            return $this->db->get()->row();
        }
    }

    public function isUserInvited($tournament, $user){

        $this->db->where('invited !=',0);
        $this->db->where('torneId',$tournament);
        $this->db->where('userId',$user);
        $result = $this->db->get('bp_tournamentsplayers');

        if($result->num_rows() > 0){
            return true;
        }else{
            return false;
        }
    }

    public function isTeamTournamentFull($tournamentId, $group){

        $query = $this->db->query('select count(userId) as registered, torne.teamId from bp_tournamentsplayers as torne join (select distinct(teamId)from bp_tournamentsplayers as tt where tt.group_type_id = '.$group.' and tt.torneId = '.$tournamentId.' and tt.invited = 0 and tt.active = 1) as teams on teams.teamId = torne.teamId where torne.invited = 0 and torne.active = 1 and torne.torneid = '.$tournamentId.' group by torne.teamId order by registered desc limit 1');

        return $query->row();
    }

    public function tenMinutesBan(){

        //traigo todos los que se hayan pasado del limite de 10 min de registro y que no estén invitados

        $this->db->select('tourPlayerId');
        $this->db->from('bp_tournamentsplayers p');
        $this->db->join('bp_tournaments t','torneId = tournamentId');
        $this->db->where('status',1);
        $this->db->where('active',1);
        $this->db->where('invited',0);
        $this->db->where("ADDTIME(inscriptionDate, '0:10:00') <",date('Y-m-d h:i:s',time()));
        $result = $this->db->get();
//        echo $this->db->last_query();
//        print_r($result->result());
        $players_to_ban = $result->result();
        if($result->num_rows()>0){
            //baneo uno y cada uno de ellos
            foreach ($players_to_ban as $player_to_ban) {
                $this->do_ban($player_to_ban->tourPlayerId);
            }
        }
    }

    public function getTournamentStake($tournamentId){
        if(!empty($tournamentId)){
            $this->db->select('stake');
            $this->db->from('bp_tournaments');
            $this->db->where('tournamentId',$tournamentId);
            return $this->db->get()->row();
        }else{
            return false;
        }
    }

    public function checkUserIdRelation($data){
        $this->db->from('bp_users_steam');
        if(!empty($data['userId'])){
            $this->db->where('userId',$data['userId']);
        }
        if(!empty($data['gameId'])){
            $this->db->where('gameId',$data['gameId']);
        }
        if(!empty($data['steamId'])){
            $this->db->where('steamId',$data['steamId']);
        }
        $result = $this->db->get()->row();

        return $result;
    }

    public function updateUserIdRelation($data){
        $this->db->where('userId',$data['userId']);
        return $this->db->update('bp_users_steam',$data);
    }

    public function createUserIdRelation($data){
        return $this->db->insert('bp_users_steam',$data);
    }

    /*
     * Desconectamos a un usuario de los torneos a cusa de una desconecion de internet o deslogueo
     */
    public function disconnect($userId=''){
        $tournaments = $this->getTournaments(array('status'=>1,'status_or'=>2));
        if(!empty($tournaments)){
            foreach($tournaments as $torne){
                $this->outOfTournament(array('userId'=>$userId,'tournamentId'=>$torne->tournamentId));
            }
            return true;
        }
        return false;
    }

    /*
     * Make abandon some user
     */
    public function abandon_game($options=array()){
        $default['tournamentId'] = '';
        $default['userId'] = '';
        $set = $this->functions->merge($default,$options);

        //seteo la columna active en 0 para que aparezca como baneado
        if(!empty($set->tournamentId) and !empty($set->userId)){
            $this->db->set('abandon',1);
            $this->db->where('torneId',$set->tournamentId);
            $this->db->where('userId',$set->userId);
            $this->db->update('bp_tournamentsplayers');
            return true;
        }
        return false;
    }

    /*
     * Make abandon some user
     */
    public function abandon_game_comments($options=array()){
        $insert = $this->db->insert('bp_tournaments_abandon',$options);
        if($insert){
            return true;
        }
        return false;
    }

    private function do_ban($id){

        //seteo la columna active en 0 para que aparezca como baneado
        $this->db->set('active',0);
        $this->db->where('tourPlayerId',$id);
        $this->db->update('bp_tournamentsplayers');

    }

    private function i_am_ready($tournament, $user){

        $this->db->select('tourPlayerId');
        $this->db->where('torneId',$tournament);
        $this->db->where('userId',$user);
        $result = $this->db->get('bp_tournamentsplayers');

        if($result->num_rows() == 1){
            return true;
        }else{
            return false;
        }
    }

}