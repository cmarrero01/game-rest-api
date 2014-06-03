<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Tournaments Controller
 *
 * @package		CodeIgniter
 * @subpackage	Rest Server
 * @category	Controller
 */

require APPPATH.'/libraries/REST_Controller.php';

/**
 * @property mixed points_model
 * @property mixed Tournaments_model
 * @property mixed teams_model
 * @property mixed fct
 * @property mixed csgo
 * @property mixed user_model
 */
class Tournaments extends REST_Controller
{

    public function __construct()
    {
        parent:: __construct();
        $this->load->model('Tournaments_model');
        $this->load->library('csgo');
        $this->load->model('teams_model');
        //$this->load->library('tf2');
//        $this->tenMinutesBan();
    }

    /**
     * Agrega un torneo nuevo.
     *
     * @param name
     * @param gameId
     * @param typeId
     * @param serverId
     * @param userBeT
     * @param status
     * @param maxUsers
     */

    public function tournamentsAdd_get()
    {
        $creationDate = date("Y-m-d H:i:s");

        $tourneData = array(
            'name' => $this->get('name'),
            'gameId' => $this->get('gameId'),
            'typeId' => $this->get('typeId'),
            'creationDate' => $creationDate,
            'serverId' => $this->get('serverId'),
            'userBet' => $this->get('userBet'),
            'status' => 1,
            'maxUsers' => $this->get('maxUsers')
        );

        $sucessInsert = $this->Tournaments_model->add_tournament($tourneData);

        if($sucessInsert)
        {
            $response = array('response' => true,'result' => 'The tournament was create successfully');
            $this->response($response, 200);
        }
        else
        {
            $response = array('response' => false,'result' => 'The name already exist');
            $this->response($response, 404);
        }
    }


    /**
     * Devuelve 1 o una lista de tournamentes de acuerdo a las opciones que recibie en el array options.
     * @param name
     * @param gameId
     * @param typeId
     * @param creationDate
     * @param endDate
     * @param serverId
     * @param userBeT
     * @param status
     * @param maxUsers
     */

    public function tournamentGetWhithFilter_get()
    {
        $filter = array(
            't.name' => $this->get('name'),
            't.gameId' => $this->get('gameId'),
            'typeId' => $this->get('typeId'),
            't.creationDate' =>  $this->get('creationDate'),
            't.endDate' =>  $this->get('endDate'),
            'serverId' => $this->get('serverId'),
            'userBet' => $this->get('userBet'),
            't.status' => $this->get('status'),
            'maxUsers' => $this->get('maxUsers'),
            'limit' => $this->get('limit'),
            'pageOffset' => $this->get('pageOffset'),
            'orderBy' =>$this->get('orderBy')
        );

        if($filter['pageOffset']){
            $filter['pageOffset'] = ($filter['pageOffset']-1)*10;
        }else{
            $filter['pageOffset'] = 0;
        }

        $result = $this->Tournaments_model->filter_tournaments($filter);

        $response = array('response' => true,'result' => $result);

        $this->response($response, 200);

        if($result)
        {
            $response = array('response' => true,'result' => $result);

            $this->response($response, 200);
        }
        else
        {
            $response = array('response' => false,'result' => 'Tournaments not found');

            $this->response($response, 404);
        }
    }

    public function getLastPage_get(){
        $status = $this->get('status');
        $result = $this->Tournaments_model->getLastPage($status);
        $pages = ceil($result[0]->tournamentsCount / 10);

        $response = array('response'=>true,'result'=>$pages);
        $this->response($response, 200);
    }


    /**
     * Edita el torneo.
     * @param tournamentId
     * @param name
     * @param gameId
     * @param gameId
     * @param creationDate
     * @param endDate
     * @param serverId
     * @param userBet
     * @param status
     * @param maxUsers
     */
    public function editTournament_get()
    {
        $editData = array(
            'name' => $this->get('name'),
            'gameId' => $this->get('gameId'),
            'typeId' => $this->get('gameId'),
            'creationDate' =>  $this->get('creationDate'),
            'endDate' =>  $this->get('endDate'),
            'serverId' => $this->get('serverId'),
            'userBet' => $this->get('userBet'),
            'status' => $this->get('status'),
            'maxUsers' => $this->get('maxUsers')
        );

        $result = $this->Tournaments_model->edit_tournament($this->get('tournamentId'), $editData);

        if($result)
        {
            $response = array('response' => true,'result' => 'Tournament edited');

            $this->response($response, 200);
        }
        else
        {
            $response = array('response' => false,'result' => 'Tournament ID not found');

            $this->response($response, 404);
        }
    }

    /**
     * Devuelve el stake del torneo
     * @param tournamentId
     */
    public function tournamentStake_get(){
        $tournamentId = $this->get('tournamentId');

        if(!empty($tournamentId)){

            $bet = $this->tournaments_model->getTournamentStake($tournamentId);

            if($bet){
                $response = array('response' => true,'result' => $bet);
                $this->response($response, 200);
            }

        }else{
            $response = array('response' => false,'result' => 'Tournament ID not found');
            $this->response($response, 404);
        }
    }

    /**
     * Devuelve los jugadores de un torneo dado.
     * @param tournamentId
     */
    public function usersInside_get(){
        //Obtengo los nicename de los usuario inscriptos en el torneo
        $usersInscripted = $this->Tournaments_model->get_users_inscripted($this->get('tournamentId'));

        if(!empty($usersInscripted)){
            $response = array('response' => true,'result' => $usersInscripted);
            $this->response($response, 200);
        }else{
            $response = array('response' => false,'result' => 'Not users in yet');
            $this->response($response, 200);
        }
    }

    /**
     * Join user to the tournament
     * @param idTournament
     * @param idUser
     * @param group_type_id
     */
    public function joinTournament_get(){

        $idTournament = $this->get('idTournament');
        $idUser = $this->get('idUser');
        $group_type_id = $this->get('group_type_id');
        $team = $this->get('teamId');

        if(empty($idTournament) or empty($idUser)){
            $response = array('response' => false,'result' => 'Send correct data please.');
            $this->response($response, 200);
        }



        $g = $this->Tournaments_model->thereAreSlots($idTournament);

        $g = $g[0];

        if(($g->group_1 + $g->group_2) < $g->maxUsers){

            if($g->group_1 < ($g->maxUsers / 2) && $group_type_id == 1){

                $group_type_id = 1;

            }elseif($g->group_2 < ($g->maxUsers / 2)){

                $group_type_id = 2;

            }else{

                $group_type_id = 1;

            }
        }else{
            $response = array('response' => false,'result' => 'This tournament is full');
            $this->response($response, 404);
        }

        if($group_type_id){

            $args = array(
                'tournamentId'=>$idTournament,
                'userId'=>$idUser,
                'group_type_id' => $group_type_id,
                'teamId' => $team,
                'active' => '1',
                'invited' => '0'
            );

            $join = $this->Tournaments_model->joinUserTournament($args);

            if($join){

                $full = $this->isTournamentFull($idTournament);

                if($full){

                    $playing = $this->Tournaments_model->usersAlreadyPlaying($idTournament);

                    $bandera = true;

                    foreach($playing as $player){

                        if(isset($player->torne) && !empty($player->torne)):

                            $bandera = false;

                            $this->Tournaments_model->outOfTournament(array('userId' => $player->player, 'tournamentId' => $idTournament));

                        endif;
                    }

                    if($bandera):
                        $this->Tournaments_model->changeStatusTournament($idTournament,'2');
                    endif;

                }
                $response = array('response' => true,'result' => 'joined');
                $this->response($response, 200);
            }

        }else{
            $response = array('response' => false,'result' => 'You are already join to this tournament.');
            $this->response($response, 404);
        }
    }

    /**
     * Quita a un usuario de un Torneo.
     * @param idTournament,
     * @param idUser
     */
    public function outOfTournament_get(){

        $idTournament = $this->get('idTournament');
        $idUser = $this->get('idUser');


        if(empty($idTournament) or empty($idUser)){
            $response = array('response' => false,'result' => 'Send correct data please.');
            $this->response($response, 404);
        }

        $args = array(
            'tournamentId'=>$idTournament,
            'userId'=>$idUser
        );

        $out = $this->Tournaments_model->outOfTournament($args);

        $full = $this->isTournamentFull($idTournament);

        if(!$full){
            $this->Tournaments_model->changeStatusTournament($idTournament,'1');
        }
        if($out){
            $response = array('response' => true,'result' => 'out');
            $this->response($response, 200);
        }else{
            $response = array('response' => false,'result' => 'You are already join to this tournament.');
            $this->response($response, 404);
        }
    }

    /**
     * is the tournament full?
     * @param idTournament
     */
    public function isTournamentFull_get(){

        $idTournament = $this->get('idTournament');

        if(!empty($idTournament)){

            if($this->isTournamentFull($idTournament)){
                $response = array('response' => true,'result' => true);
                $this->response($response, 200);
            }else{
                $response = array('response' => true,'result' => false);
                $this->response($response, 200);
            }

        }else{
            $response = array('response' => false,'result' => 'Send me some data please.');
            $this->response($response, 404);
        }
    }

    /**
     *  Public method for join a team to a tournament Team vs Team
     * @param: idTournament
     * @param: idUser
     * @param: teamId
     * @param: group_type_id
     */
    public function joinTeamTournament_get(){

        $tournament = $this->get('idTournament');
        $user = $this->get('idUser');
        $team = $this->get('teamId');
        $group = $this->get('group_type_id');
        $isInvited = $this->get('isInvited');

        $can_enter = $this->Tournaments_model->canRegisterTeam($tournament, $team, $group);

        if($can_enter){

            $result = $this->Tournaments_model->joinTeamTournament($tournament, $user, $team, $group,$isInvited);

            if($result){

                $tournament_full = $this->isTeamTournamentFull($tournament);

                $is_full = $this->Tournaments_model->isTeamFull($tournament, $team);

                if($tournament_full){

                    $this->Tournaments_model->changeStatusTournament($tournament, '2');

                }

                if($is_full){
                    $this->Tournaments_model->inactivate_otherTeams($tournament, $team, $group);
                }

                $response = array('response' => true,'result' => $result);
                $this->response($response, 200);

            }else{
                $response = array('response' => false,'result' => 'Send me valid data please.');
                $this->response($response, 404);
            }
        }else{
            $response = array('response' => false,'result' => 'You can\'t register because the limit has been reached');
            $this->response($response, 404);
        }
    }

    /**
     *  Public method to list members from my team whom are available
     * @param: tournamentId
     * @param: teamId
     */
    public function availableMembersInvite_get(){
        //deberia recibir la cantidad de miembros que se permita en el tournament
        $teamId = $this->get('teamId');
        $tournamentId = $this->get('tournamentId');
        $teammembers = $this->Tournaments_model->get_teammembers_to_invite($teamId,$tournamentId);

        if ($teammembers){
            $response = array('response' => true,'result' => $teammembers);
            $this->response($response, 200);
        }else{
            $response = array('response' => false,'result' => 'There is no one available');
            $this->response($response, 404);
        }


    }

    /**
     * Chequea que torneo esta lleno y prepara a los usuarios para jugar.
     * @param userId
     */

    public function setTournamentForPlay_get(){
        $userId = $this->get('userId');
        $tournaments = $this->getTournamentsByUser($userId);

        if($tournaments){

            $tor = false;

            foreach($tournaments as $tournament){
                if($this->isTournamentFull($tournament->torneId)){
                    $tor = $tournament;
                    break;
                }
            }

            if($tor){
                $this->outUserOfTournament($userId,$tor->torneId);
                $response = array('response' => true,'result' => $tor);
                $this->response($response, 200);
            }else{
                $response = array('response' => false,'result' => false);
                $this->response($response, 200);
            }

        }else{
            $response = array('response' => false,'result' => 'Not exist any tournament yet');
            $this->response($response, 404);
        }
    }

    /*
    *
    *
    * Get tournaments where the player played today
    *
    *
    */
    public function tournamentsByUserFromToday_get(){

        $userId = $this->get('userId');
        if(!empty($userId)){
            $date = (date("Y-m-d",time()));
            $args = array(
                'userId'=>$userId,
                'date' =>$date
            );

            $tournaments = $this->Tournaments_model->getTournamentsByUserFromToday($args);

            if(!empty($tournaments)){
                foreach($tournaments as $tournament){
                    $tournament->data = $this->Tournaments_model->getTournaments(array('tournamentId'=>$tournament->torneId));
                }
                $response = array('response' => true,'result' => $tournaments);
                $this->response($response, 200);
            }else{
                $response = array('response' => false,'result' => 'The user has not played any tournaments today');
                $this->response($response, 404);
            }

        }else{
            $response = array('response' => false,'result' => "Nope, I don't work like that");
            $this->response($response, 404);
        }
    }


    /**
     * Cambiamos el estado de un torneo
     * @param tournamentId
     * @param status
     */
    public function changeStatusTournament_get(){
        $tournamentId = $this->get('tournamentId');
        $status = $this->get('status');
        $this->Tournaments_model->changeStatusTournament(array('tournamentId'=>$tournamentId,'status'=>$status));
        $response = array('response' => true,'result' => true);
        $this->response($response, 200);
    }

    /**
     * Revisamos si el usuario esta registrado al torneo
     *
     * @param userId
     * @param tournamentId
     * */
    public function isUserRegistered_get(){

        $tournamentId = $this->get('tournamentId');
        $userId = $this->get('userId');

        if(!empty($userId) && !empty($tournamentId)){

            $result = $this->Tournaments_model->isUserRegistered($tournamentId, $userId);

            if($result){

                $response = array('response' => true,'result' => 'User Registered','bannedStatus'=>$result->active);
                $this->response($response, 200);

            }else{
                $response = array('response' => false,'result' => 'User not Registered');
                $this->response($response, 404);
            }

        }else{
            $response = array('response' => false,'result' => 'Send valid data please');
            $this->response($response, 404);

        }

    }
    /**
     * Obtiene los teams registrados en un torneo de Team vs Team
     *
     * @param: tournamentId
     */
    public function registeredTeams_get(){

        $result = $this->Tournaments_model->registeredTeams($this->get('tournamentId'));

        if($result){
            $response = array('response' => true,'result' => $result);
            $this->response($response, 200);
        }else{
            $response = array('response' => false,'result' => 'There aren\'t teams in this tournament');
            $this->response($response, true);
        }
    }
    /**
     * Obtiene los torneos en los que está el usuario alctualmente registrado
     *
     * @param: userId
     */
    public function currentTournaments_get(){
        $userId = $this->get('userId');

        $tournaments = $this->Tournaments_model->getCurrent_Tournaments($userId);

        if($tournaments){

            foreach($tournaments as $tournament){

                $userWhoInvited = $tournament->invited;
                $isInvited = $this->Tournaments_model->isUserInvited($tournament->tournamentId,$userId);
                if ($isInvited){
                    $tournament->nicename = $this->Tournaments_model->whoInvitedMe($userWhoInvited)->nicename;
                }else{
                    $tournament->nicename = "";
                }
                if($tournament->gameMode == 3){

                    $group1 = $this->Tournaments_model->isTeamTournamentFull($tournament->tournamentId, '1');
                    $group2 = $this->Tournaments_model->isTeamTournamentFull($tournament->tournamentId, '2');

                    $tournament->cantidad = $group1->registered + $group2->registered;
                }
            }

            $response = array('response' => true,'result' => $tournaments);
            $this->response($response, 200);
        }else{
            $response = array('response' => false,'result' => 'You don\'t have tournaments right now');
            $this->response($response, true);
        }


    }

    public function acceptTournament_get(){
        $userId = $this->get('userId');
        $tournamentId = $this->get('tournamentId');

        $acceptInvite = $this->Tournaments_model->acceptInvite($userId,$tournamentId);

        $tournament_full = $this->isTeamTournamentFull($tournamentId);

        if($tournament_full){

            $this->Tournaments_model->changeStatusTournament($tournamentId, '2');

        }


        if($acceptInvite){
            $response = array('response' => true);
            $this->response($response, 200);
        }else{
            $response = array('response' => false);
            $this->response($response, 404);
        }
    }

    /**
     *
     *Funcion para averiguar cuanto falta para que inicie el torneo.
     *
     * @param: tournamentId
     *
     */
    public function imReady_get(){

        $user = $this->get('userId');
        $tournament = $this->get('tournamentId');

        $success = $this->Tournaments_model->set_ready($user, $tournament);

        if($success){
            $response = array('response' => true,'result' => 'Wait for the launching');
            $this->response($response, 200);
        }else{
            $response = array('response' => false,'result' => 'Send me valid data please');
            $this->response($response, 404);
        }


    }
    /**
     * Pregunta si todos están en condiciones de largar el juego, y avisa si se puede lanzar o no
     *
     *@param: tournamentId
     */
    public function isTournamentLaunching_get(){

        $tournament = $this->get('tournamentId');

        $result = $this->Tournaments_model->isTournamentReady($tournament);

        if($result->ready == $result->maxUsers){

            if(!$this->Tournaments_model->check_finished_tournament($tournament)){

                $proceed = $this->discountPointsToAllUsers($tournament);

                if($proceed){

                    $this->Tournaments_model->changeStatusTournament($tournament,'3');
                    //Lanzamos el demonio java
                    $tournamentData = $this->Tournaments_model->getTournaments(array('tournamentId'=>$tournament));
                    $gameModeServer = $tournamentData[0]->gameModeServer;
                    $this->tournamentDeamon($tournamentData[0]->ip,$tournament,$gameModeServer,'csgo');
                    //Comenzamos a grabar la partida
                    //$this->recordMatch('csgo',$tournament);
                    //Response
                    $response = array('response' => true, 'result' => $result);
                    $this->response($response, 200);
                }else{
                    $response = array('response' => false, 'result' => "Can't discount all user's points");
                    $this->response($response, 404);
                }

            }

        }else{
            $response = array('response' => false, 'result' => "Wait a minute");
            $this->response($response, 404);
        }
    }
    /*
     * Registro del team
     */
    public function myRegTeam_get(){

        $tournamentId = $this->get('tournamentId');
        $userId = $this->get('userId');

        $team = $this->Tournaments_model->my_registered_team($tournamentId, $userId);

        if($team){

            $response = array('response' => true, 'result' => $team);
            $this->response($response, 200);

        }else{

            $response = array('response' => false, 'result' => 'You\'re not registered');
            $this->response($response, 404);

        }

    }

    /*
     * Tomamos los datos del usuario para setear el gameId y el steamId
     * a este metodo lo llama la APP de escritorio
     */
    public function relateUserIds_get(){

        $userId = $this->get('userId');
        $gameId = $this->get('gameId');

        if(!empty($userId)&&!empty($gameId)){

            $steamId = $this->steamIdFromUser($gameId);

            $shouldUpdate = $this->tournaments_model->checkUserIdRelation(array('userId'=>$userId));

            if(empty($steamId)){
                $steamId = '';
            }

            $data = array(
                'userId'=>$userId,
                'gameId'=>$gameId,
                'steamId'=>$steamId
            );

            if($shouldUpdate){
                $this->tournaments_model->updateUserIdRelation($data);
                $response = array('response' => true,'result' => 'Data updated succesfully');
                $this->response($response, 200);
            }else{
                $letsCreateRelation = $this->tournaments_model->createUserIdRelation($data);
                if ($letsCreateRelation){
                    $response = array('response' => true,'result' => 'Relation Created Succesfully');
                    $this->response($response, 200);
                }else{
                    $response = array('response' => false,'result' => 'Something went wrong with data insert');
                    $this->response($response, 404);
                }
            }
        }else{
            $response = array('response' => false,'result' => 'You must provide at least a userId and gameId');
            $this->response($response, 404);
        }
    }

    /*
     * Tomamos los datos del usuario para setear el gameId y el steamId
     */
    public function relateUserIds_post(){

        $userId = $this->post('userId');
        $gameId = $this->post('gameId');

        if(!empty($userId)&&!empty($gameId)){

            $steamId = $this->steamIdFromUser($gameId);

            $shouldUpdate = $this->tournaments_model->checkUserIdRelation(array('userId'=>$userId));

            if(empty($steamId)){
                $steamId = '';
            }

            $data = array(
                'userId'=>$userId,
                'gameId'=>$gameId,
                'steamId'=>$steamId
            );

            if($shouldUpdate){
                $this->tournaments_model->updateUserIdRelation($data);
                $response = array('response' => true,'result' => 'Data updated succesfully');
                $this->response($response, 200);
            }else{
                $letsCreateRelation = $this->tournaments_model->createUserIdRelation($data);
                if ($letsCreateRelation){
                    $response = array('response' => true,'result' => 'Relation Created Succesfully');
                    $this->response($response, 200);
                }else{
                    $response = array('response' => false,'result' => 'Something went wrong with data insert');
                    $this->response($response, 404);
                }
            }
        }else{
            $response = array('response' => false,'result' => 'You must provide at least a userId and gameId');
            $this->response($response, 404);
        }
    }


    ////////////////////////////////////GAMES TOURNAMENTS///////////////////////////////////
    /*
    * Traemos las estadisticas de counter strike global offensive
    */
    public function getGameStats_get(){
        $torneId = $this->get('torneId');
        $game = $this->get('game');
        $stats = $this->{$game}->getServerFiles($torneId);
        $console = $this->parseLogServer($torneId,$game);
        $this->stopRecordMatch($game,$torneId);
        $wasRewarded = $this->transferRewardsToWinner($torneId,$game);

        if($stats and $wasRewarded){
            $tournament = $this->tournaments_model->getTournaments(array('tournamentId'=>$torneId));
            if($tournament){
                $this->{$game}->downloadMatch($tournament[0]->ip,$tournament[0]->ftpUser,$tournament[0]->ftpPass,$torneId);
            }
            $this->response(array('response'=>true,'console'=>$console), 200);
        }else{
            $this->response(array('response'=>false),200);
        }
    }


    /*
     * Is tournaent finish?
     */
    public function isTournamentFinish_get(){
        $torneId = $this->get('torneId');
        $server = $this->get('server');
        $tournamentType = $this->get('tournamentType');
        $game = $this->get('game');

        $finish = $this->{$game}->isTournamentFinish($server,$torneId,$tournamentType);

        if($finish){
            $this->stopRecordMatch($game,$torneId);
            $this->{$game}->replyTournament($torneId);
            $this->response(array('response'=>true),200);
        }

        $this->response(array('response'=>false),200);
    }

    /*
    * Verificamos si el servidor sigue activo, para tomar desiciones al respecto.
    */
    public function getServerPlayers_get(){
        $torneId = $this->get('torneId');
        $game = $this->get('game');
        $result = $this->getServerPlayers($torneId,$game);
        if($result){
            $this->response(array('response'=>$result),200);
        }
        $this->response(array('response'=>false),200);
    }


    /*
     * Metodo que se utiliza para
     */
    public function isTournamentFinishForUsers_get(){
        $torneId = $this->get('torneId');
        //$game = $this->get('game');
        $isTournamentFinish = $this->tournaments_model->check_finished_tournament($torneId);
        if($isTournamentFinish){
            $this->tournamentDeamonStop($torneId);
            $result = true;//$this->howIsThePlayerWinner($torneId,$game);
        }else{
            $result = false;
        }
        $this->response(array('response'=>$result), 200);
    }

    /*
     * Tomar ganador de un torneo de CSGO (Team ganador)
     */
    public function howIsTheTeamWinner_get(){

        $torneId = $this->get('torneId');
        $game = $this->get('game');
        $getWinnings = $this->get('getWinnings');

        $win = $this->{$game}->howIsTheTeamWinner($torneId);


        if($getWinnings){
            $stake = $this->tournamentStake($torneId);
            $args = array(
                'tournamentId'=>$torneId,
                'status'=>4,
                'players'=>1
            );
            //Get the ammount of players in this tournament
            $tournament = $this->Tournaments_model->getTournaments($args);
            $teamsPlayers = array();
            if(!empty($tournament)){
                //Tomamos los bandos en los que se registraron, y determinamos cual fue el bando ganador
                if($tournament[0]->gameModeServer == 1 or $tournament[0]->gameModeServer == 4){
                    foreach($tournament as $tor){
                        if(isset($tor->players)){
                            foreach($tor->players as $players){
                                $team = $this->teams_model->get_teams(array('idTeam'=>$players->teamId,'only_teams'=>true));
                                $teamsPlayers[$players->group_type_id][] = array('userId'=>$players->userId,'group_type_id'=>$players->group_type_id,'team'=>$team);

                            }
                        }
                    }
                }

                $torneUsers = $tournament[0]->maxUsers;

                //Calculate the prize
                if($stake->stake>0){
                    $winningAmount = ($stake->stake * $torneUsers)/($torneUsers/2);
                }else{
                    $winningAmount = 0;
                }

                $win['amount'] = $winningAmount;

                if(isset($win['team'])){
                    switch($win['team']){
                        case '2':
                            $win['team'] = 'CT Win (First you were Terrorist)';
                            $win['playersWin'] = $teamsPlayers[2];
                            break;
                        case '1':
                            $win['team'] = 'Terrorist Win (First you were CT)';
                            $win['playersWin'] = $teamsPlayers[1];
                            break;
                        default:
                            $win['team'] = 'Draw';
                            $win['playersWin'] = array();
                            break;
                    }
                }
            }
        }

        $this->response(array('response'=>true,'result'=>$win), 200);
    }

    /*
     * Who is the team winner form console logo modal
     */
    public function howIsTheTeamWinnerConsole_get(){
        $torneId = $this->get('torneId');
        $game = $this->get('game');
        $team = $this->{$game}->howIsTheTeamWinnerFromConsoleModal($torneId);
        $this->response(array('response'=>true,'result'=>$team), 200);
    }

    /*
     * Tomamos el player ganador de CSGO
     */
    public function howIsThePlayerWinner_get(){
        $torneId = $this->get('torneId');
        $game = $this->get('game');
        $getWinnings = $this->get('getWinnings');

        if($getWinnings){
            $winner = $this->howIsThePlayerWinner($torneId,$game,$getWinnings);
        }else{
            $winner = $this->howIsThePlayerWinner($torneId,$game);
        }

        $this->response(array('response'=>true,'result'=>$winner), 200);
    }

    /*
     * todo: borrar este metodo (soloc aludio)
     */
    public function testDeamonJava_get(){

        $server = $this->get('server');
        $tournament = $this->get('tournamentId');
        $tournamentType = $this->get('tournamentType');
        $game = $this->get('gameId');

        $shoot = $this->tournamentDeamon($server,$tournament,$tournamentType,$game);

        $response = array('response' => true,'result' => $shoot);
        $this->response($response, 200);
    }

    /*
     * Grabar partida
     */
    public function record_get(){
        $game = $this->get('game');
        $torneId = $this->get('torneId');

        $this->recordMatch($game,$torneId);
        $this->response(array('response'=>true), 200);
    }

    /*
     * Parar partida
     */
    public function stopRecord_get(){
        $game = $this->get('game');
        $torneId = $this->get('torneId');

        $this->stopRecordMatch($game,$torneId);
        $this->response(array('response'=>true), 200);
    }

    /*
     * Get all tournaments without filters
     */
    public function getTournaments_get(){
        $players = $this->get('players');
        if($players){
            $data = array('players'=>$players);
        }else{
            $data = array();
        }
        if($this->get('torneId')){
            $data['tournamentId'] = $this->get('torneId');
        }
        $tournaments = $this->tournaments_model->getTournaments($data);
        if($tournaments){
            $this->response($tournaments, 200);
        }
        $this->response(array('response'=>false), 200);
    }


    /*
     * Get console log
     */
    public function parseLogServer_get(){
        $torneId = $this->get('torneId');
        $game = $this->get('game');
        $console = $this->{$game}->parseConsole($torneId);
        $this->response($console,200);
    }

    public function parseCsgoStats_get(){
        $torneId = $this->get('torneId');
        $game = $this->get('game');
        $stats = $this->{$game}->parseCsgoStats($torneId);
        $this->response($stats,200);
    }

    /*
     * Abandon tournament
     */
    public function abandon_get(){
        $userId = $this->get('userId');
        $tournamentId = $this->get('tournamentId');

        $prepare = array(
            'userId'=>$userId,
            'tournamentId'=>$tournamentId
        );

        $abandon = $this->tournaments_model->abandon_game($prepare);
        if($abandon){

            $reason = $this->get('reason');
            $comment = $this->get('comment');

            $prepare['reasonId'] = $reason;
            $prepare['comment'] = $comment;

            $save = $this->tournaments_model->abandon_game_comments($prepare);
            if($save){
                $this->response(array('response'=>true,'result'=>true),200);
            }
            $this->response(array('response'=>true,'result'=>false),200);
        }
        $this->response(array('response'=>false),200);
    }

/////////////////////////////////////// PRIVATE METHODS /////////////////////////////////////////////

    /*
     * Metodo que chequea si un torneo esta activo en un server de gameserver.com
     */
    private function getServerPlayers($torneId,$game){
        $result = $this->{$game}->playersInServerByTournament($torneId);
        if($result){
            foreach($result['tournaments'] as $tournament){
                foreach($tournament as $key=>$value){

                    if($key == 'players'){
                        foreach($value as $k=>$val){

                            if($k == 'both'){
                                if($val == 0){
                                    return false;
                                    break;
                                }
                            }else{
                                $player[] = $val;
                            }
                        }
                        if(empty($player)){
                            return false; break;
                        }
                    }
                }

            }
            return true;
        }
        return false;
    }
    /*
     * Get SteamID From GameID
     */
    private function steamIdFromUser($gameId){

        $offset = bcsub('', $gameId);
        $id = bcdiv($offset, '2');
        if (bcmod($offset, '2')) {
            $steamid = bcadd($id, '2')*(-1);
            $steamid+=2;
            //$steamid = 'STEAM_0:1:' . $steamid;
        } else {
            $steamid = bcadd($id, '1')*(-1);
            $steamid+=1;
            //$steamid = "STEAM_0:0:" . $steamid;
        }
        return $steamid;
    }

    /*
     * Grabando la partida
     */
    private function recordMatch($game,$torneId){
        $tournament = $this->tournaments_model->filter_tournaments(array('tournamentId'=>$torneId));
        if(!empty($tournament)){
            $tournament = (is_array($tournament))?$tournament[0]:$tournament;
            $this->{$game}->stopRecord($tournament->ip,$tournament->port,$tournament->rconPass);
            $record = $this->{$game}->record($tournament->ip,$tournament->port,$tournament->rconPass,$torneId);
            if($record){
                return true;
            }
        }
        return false;
    }

    /*
     * Stopeando la partida
     */
    private function stopRecordMatch($game,$torneId){
        $tournament = $this->tournaments_model->filter_tournaments(array('tournamentId'=>$torneId));
        if(!empty($tournament)){
            $tournament = (is_array($tournament))?$tournament[0]:$tournament;
            $this->{$game}->stopRecord($tournament->ip,$tournament->port,$tournament->rconPass);
            return true;
        }
        return false;
    }

    /*
     * Get console log
     */
    private function parseLogServer($torneId,$game){
        $console = $this->{$game}->parseConsole($torneId);
        return $console;
    }

    /*
     * Metodo privado para detectar el ganador del torneo
     */
    private function howIsThePlayerWinner($torneId,$game,$getWinnings=''){

        $winner = $this->{$game}->howIsThePlayerWinner($torneId);

        if(!empty($getWinnings)){

            $stake = $this->tournamentStake($torneId);

            $args = array(
                'tournamentId'=>$torneId,
                'status'=>4
            );
            //Get the ammount of players in this tournament
            $torneUsers = $this->Tournaments_model->getTournaments($args);
            $torneUsers = $torneUsers[0]->maxUsers;

            //Calculate the prize
            if($stake->stake > 0){

                $winningAmount = $stake->stake * $torneUsers;

            }else{
                //Si el torneo no tiene apuestas o no esta en estado '4' no hago insert en la tabla
                return false;
            }
            $this->load->model('user_model');
            if(is_array($winner)):
                $winner['amount'] = $winningAmount;

                $winner['avatar'] = $this->user_model->getAvatars(array('idUser' => $winner['userId'],'size'=>150));
            else:
                $winner->amount = $winningAmount;

                $winner->avatar = $this->user_model->getAvatars(array('idUser' => $winner->userId,'size'=>150));
            endif;
        }

        return $winner;
    }

    /**
     * Private method for check if tournament is full
     */
    private function isTournamentFull($idTournament){

        if(!empty($idTournament)){

            $args = array(
                'tournamentId'=>$idTournament,
                'status'=>1,
                'status_or'=>2
            );

            $tournament = $this->Tournaments_model->getTournaments($args);

            $usersInscripted = $this->Tournaments_model->get_users_inscripted($idTournament);

            if(!empty($tournament)){

                $capacity = $tournament[0]->maxUsers;

                if(!empty($usersInscripted)){

                    $totalUsers = count($usersInscripted);

                    if($capacity == $totalUsers){
                        return true;
                    }else{
                        return false;
                    }

                }else{
                    return false;
                }

            }else{
                return false;
            }

        }else{
            return false;
        }
    }



    private function isTeamTournamentFull($tournamentId){

        $args = array(
            'tournamentId'=>$tournamentId,
            'status'=>1,
            'status_or'=>2
        );

        $tournament = $this->Tournaments_model->getTournaments($args);

        $group1 = $this->Tournaments_model->isTeamTournamentFull($tournamentId, '1');
        $group2 = $this->Tournaments_model->isTeamTournamentFull($tournamentId, '2');

        if(!empty($group1) && !empty($group2) && (($group1->registered + $group2->registered) == $tournament[0]->maxUsers)){
            return true;
        }else{
            return false;
        }


    }


    /*
    * Delete players when the other tournament is full
    */
    private function outUserOfTournament($userId,$torneId){
        $args = array(
            'userId'=>$userId,
            'torneId'=>$torneId
        );
        return $this->Tournaments_model->outUsersOfTournament($args);
    }

    /*
   *
   *
   * Obtiene los torneos de un usuario
   *
   *
   */
    private function getTournamentsByUser($userId){

        if(!empty($userId)){

            $args = array(
                'userId'=>$userId
            );

            $tournaments = $this->Tournaments_model->getTournamentsByUser($args);

            if(!empty($tournaments)){
                foreach($tournaments as $tournament){
                    $tournament->data = $this->Tournaments_model->getTournaments(array('tournamentId'=>$tournament->torneId));
                }
                return $tournaments;
            }else{
                return false;
            }

        }else{
            return false;
        }
    }

    /*
     * Iniciamos el demonio java para que cheque el torneo permanentemente
     * Este metodo debe ser llamado desde el cambio de estado del torneo
     */
    private function tournamentDeamon($server,$tournamentId,$tournamentType,$gameName){

        $deamon = 'http://127.0.0.1:9999/battlepro/tournament/launch';

        $server = '/server/'.$server;
        $tournament = '/tournamentId/'.$tournamentId;
        $tournamentType = '/tournamentType/'.$tournamentType;
        $game = '/gameId/'.$gameName;

        $deamon.= $server.$tournament.$tournamentType.$game;

        $shoot['return'] = file_get_contents($deamon);
        $shoot['url'] = $deamon;

        return $shoot;
    }

    /*
     * Iniciamos el demonio java para que cheque el torneo permanentemente
     * Este metodo debe ser llamado desde el cambio de estado del torneo
     */
    private function tournamentDeamonStop($tournamentId){
        $deamon = 'http://127.0.0.1:9999/battlepro/tournament/stop/tournamentId/'.$tournamentId;
        $shoot['return'] = file_get_contents($deamon);
        $shoot['url'] = $deamon;
        return $shoot;
    }

    private function tournamentStake($tournamentId){
        if(!empty($tournamentId)){
            $bet = $this->tournaments_model->getTournamentStake($tournamentId);
            if($bet){
                return $bet;
            }
        }
        return false;
    }

    /*
     * Transferencia de puntos
     */
    private function transferRewardsToWinner($tournamentId,$game){

        $this->load->model('points_model');
        $args = array(
            'tournamentId'=>$tournamentId,
            'status'=>4,
            'players'=>true
        );
        //Get the ammount of players in this tournament
        $tournamentInfo = $this->Tournaments_model->getTournaments($args);
        $gameId = $tournamentInfo[0]->gameId;
        $gameMode = $tournamentInfo[0]->gameMode;
        $torneUsers = $tournamentInfo[0]->maxUsers;
        $stake = $tournamentInfo[0]->stake;
        // Get all users inscripted in this tournament
        $winners = $tournamentInfo[0]->players;
        // Si es usuario, va a quedar en 0, sino, cambi a 1 en el switch de abajo
        $userType = 0;
        switch($gameMode){
            case 3:
                $userType = 1;
                $teamWinner = $this->{$game}->howIsTheTeamWinnerFromConsoleModal($tournamentId);

                switch($teamWinner->team){
                    case 'CT WIN':
                        $teamWinner = 2;
                        break;
                    case 'Terrorist WIN':
                        $teamWinner = 1;
                        break;
                }
                //Calculate the prize
                if($stake->stake > 0){
                    $winningAmount = ($stake->stake * $torneUsers)/($torneUsers/2);
                }else{
                    return false;
                }
                foreach($winners as $winner){
                    if($winner->group_type_id == $teamWinner){
                        if(!isset($winnerTeamId)){
                            $teamWinnerId = $winner->teamId;
                        }
                        //Get users current points
                        $winnerCurrentPoints = $this->points_model->userPoints($winner->userId);
                        //Gather all the info to transfer the points
                        $options = array(
                            'userId'=>$winner->userId,
                            'points'=>$winnerCurrentPoints->points,
                            'morePoints'=>$winningAmount
                        );
                        $transfer = $this->points_model->plusPointsHistory($options);
                    }else{
                        if(!isset($teamLoserId)){
                            $teamLoserId = $winner->teamId;
                        }
                    }
                }
                break;
            default:

                $winner = $this->howIsThePlayerWinner($tournamentId,$game);
                print_r($winner);
                if(is_array($winner)):
                    $winnerId = $winner['userId'];
                else:
                    $winnerId = $winner->userId;
                endif;

                $winnerCurrentPoints = $this->points_model->userPoints($winnerId);
                //Get users current points

                //Calculate the prize
                if($stake > 0){
                    $winningAmount = $stake * $torneUsers;
                }else{
                    //Si el torneo no tiene apuestas o no esta en estado '4' no hago insert en la tabla
                    return false;
                }

                //Gather all the info to transfer the points
                $options = array(
                    'userId'=>$winnerId,
                    'points'=>$winnerCurrentPoints->points,
                    'morePoints'=>$winningAmount
                );
                print_r($options);
                $transfer = $this->points_model->plusPointsHistory($options);
                break;
        }

        if(isset($teamWinnerId)&&isset($teamLoserId)){
            $data = array(
                'teamWinner' => $teamWinnerId,
                'teamLoser' => $teamLoserId
            );
            $result = $this->setUserStatsAfterTournament($data,$userType,$tournamentId,$gameId);
        }else{
            $data = array(
                'winnerId' => $winnerId,
                'losersIds' => $winners
            );
            $result = $this->setUserStatsAfterTournament($data,$userType,$tournamentId,$gameId);
        }

        return $result;
    }

    /**
     * Descontar puntos/dinero al iniciar un torneo
     * @param $tournamentId
     */
    private function discountPointsToAllUsers($tournamentId){
//        public function discountPointsToAllUsers_get(){
//        $tournamentId = $this->get('tournamentId');
        $this->load->model('points_model');
        //obtengo el costo del torneo
        $stake = $this->tournamentStake($tournamentId);

        //obtengo los usuarios del torneo
        $users = $this->Tournaments_model->get_users_inscripted($tournamentId);

        //le descuento a cada usuario los puntos correspondients
        foreach ($users as $user) {

            $result = $this->points_model->discountUserPoints($user->userId,$stake->stake,$tournamentId);

            if($result===false){
                return false;
            }
        }
        return $result;
    }

    /**
     * Insertar estadísticas para usuarios al finalizar un torneo
     * @param $tournamentId
     */
    private function setUserStatsAfterTournament($data,$userType,$tournamentId,$gameId){
        if(!empty($data)&&!empty($tournamentId)&&!empty($gameId)){
            $result = $this->Tournaments_model->setUserStatsAfterTournament($data,$userType,$tournamentId,$gameId);
            return $result;
        }else{
            return false;
        }
    }

}
