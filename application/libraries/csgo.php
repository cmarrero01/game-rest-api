<?php

require_once('Gameme.php');
require __DIR__ . '/source/SourceQuery/SourceQuery.class.php';

class Csgo{

    // This properties need to be change for dinamics information from database
    var $rounds = 10;
    var $prefix_file_names = 'backup_round';
    var $extension_file = '.txt';
    var $config = array('hostname'=>'208.167.232.112','username'=>'1004734_27035','password'=>'GAMES2011','debug'=>false);

	public function __construct()
    {
		$this->ci = & get_instance();
        $this->ci->load->library('ftp');
        $this->ci->load->model('csgo_model');
        $this->ci->load->model('tournaments_model');
    }

    /*
    * Get stats in real time from Gameme.com
    */
    public function getStatsGameMe($server,$torne){

        $gameme = new gameMEAPI('http://battlepro.gameme.com/');
        $serverinfo = $gameme->client_api_serverinfo($server,GAMEME_DATA_PLAYERS);
        //Chequeuamos que el server nos devuelva algo
        if(!empty($serverinfo)){
            //Si viene algo, pedimos el server info
            if(!empty($serverinfo['serverinfo'])){
                //Si el server info esta setado, pedimos la informacion del primero encontrado
                if(isset($serverinfo['serverinfo'][0]) and !empty($serverinfo['serverinfo'][0])){
                    $server = $serverinfo['serverinfo'][0];
                    $playerStats = $server['players'];
                    if(!empty($playerStats)){
                        foreach($playerStats as $player){
                            $player['idStats'] = 0;
                            $player['idTeamStats'] = 0;
                            $player['idTournament'] = $torne;
                            $this->ci->csgo_model->updatePlayerServer($player);
                        }
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

    /*
     * Traemos los archivos de cada ronda del server, y los parseamos
     */
    public function getServerFiles($torneId){

        if(empty($torneId)){
            return false;
        }

        $path = $_SERVER['DOCUMENT_ROOT'].'/application/libraries/csgo/';
        $dir_destiny = $path.$torneId;

        if(!file_exists($dir_destiny)){
            mkdir($dir_destiny,0755);
        }

        $serverInfo = $this->getServerByTournament($torneId);
        if($serverInfo){
            $serverInfo = (is_array($serverInfo))?$serverInfo[0]:$serverInfo;
        }else{
            return false;
        }

        $this->ci->ftp->connect(array('hostname'=>$serverInfo->ip,'username'=>$serverInfo->ftpUser,'password'=>$serverInfo->ftpPass,'debug'=>false));

        $remoteFiles = $this->ci->ftp->list_files('/1004734/csgo/csgo/');
        if($remoteFiles){

            $download = $this->ci->ftp->download('/1004734/csgo/csgo/console.log',$dir_destiny.'/console.log', 'ascii');

            if($download){
                $this->ci->ftp->upload($path.'console.log', '/1004734/csgo/csgo/console.log', 'ascii', 0775);
            }

            foreach($remoteFiles as $files){
                if(strstr($files,$this->prefix_file_names)){

                    $file = '/1004734/csgo/csgo/'.$files;
                    $destiny_file = $dir_destiny.'/'.$files;

                    $download = $this->ci->ftp->download($file,$destiny_file, 'ascii');

                    if(!$download){
                        return false;
                    }else{
                        $this->ci->ftp->delete_file($file);
                    }
                }
            }
        }else{
            return false;
        }

        $this->ci->ftp->close();
        $this->parseCsgoStats($torneId);
        $this->ci->tournaments_model->changeStatusTournament($torneId,4);
        return true;
    }

    /*
     * Parse statistics of tournament of csgo and save into database
     */
    public function parseCsgoStats($torneId){
        $dir = $_SERVER['DOCUMENT_ROOT'].'/application/libraries/csgo/'.$torneId.'/';
        $dir_stats = opendir($dir);
        while($files = readdir($dir_stats)){
            if(!is_dir($files) and $files != '.' and $files != '..' and $files != 'console.log'){
                $this->parseFileStats($torneId,$dir.$files);
            }
        }
        return true;
    }

    /*
     * Tomamos el team ganador de un torneo
     */
    public function howIsTheTeamWinner($torneId){

        $teamsResults = $this->ci->csgo_model->getRoundTeams(array('idTournament'=>$torneId));

        if($teamsResults){
            $team1 = 0;
            $team2 = 0;
            $win['team'] = ''; //Empate: 0, Team1: 1, Team2: 2.
            foreach($teamsResults as $teams){

                if($team1 < $team2){
                    $win['team'] = '2';
                }elseif($team1 > $team2){
                    $win['team']  = '1';
                }else{
                    $win['team']  = '';
                }

                $team1+= $teams[0]->team1;
                $team1+= $teams[0]->team2;
            }
            return $win;
        }else{
            return false;
        }
    }

    /*
     * Totamos el player ganador
     */
    public function howIsThePlayerWinner($torneId){
        $tournament = $this->ci->tournaments_model->getTournaments(array('tournamentId'=>$torneId,'players'=>true,'status'=>4,'for_parse'=>true,'steamJoin'=>true));
        if(!empty($tournament) and isset($tournament[0])){
            $tournament = $tournament[0];
            $gameModeServer = $tournament->gameModeServer;
            if($gameModeServer == 2){
                $winners = $this->ci->csgo_model->getPlayerWinnerFromConsole(array('torneId'=>$torneId));

                if(!empty($winners)){
                    $kills = 0;
                    $deaths = 0;
                    $steamId = '';
                    foreach($winners as $winner){
                        if($kills < $winner->kills){
                            $steamId = $winner->steamId;
                            $deaths = $winner->deaths;
                            $kills = $winner->kills;
                        }
                    }

                    if(!empty($steamId)){

                        $steamId = explode(':',$steamId);
                        if(isset($steamId[2])){
                            if(!empty($tournament->players)){

                                $userWin = array();
                                foreach($tournament->players as $player){
                                    if($player->steamId == $steamId[2]){
                                        $userWin['idPlayer'] = $player->gameId;
                                        $userWin['name'] = $player->nicename;
                                        $userWin['kills'] = $kills;
                                        $userWin['deaths'] = $deaths;
                                        $userWin['userId'] = $player->userId;
                                    }
                                }
                                return $userWin;
                            }
                        }else{
                            $players = false;
                        }
                    }else{
                        $players = false;
                    }
                }else{
                    $players = false;
                }
            }else{
                $players = $this->ci->csgo_model->getPlayerWin(array('idTournament'=>$torneId));
            }
        }

        return $players;
    }

    /*
     * Verificamos si el torneo finalizo, para tomar las estadisticas y resetear todo
     */
    public function isTournamentFinish($server,$torneId,$tournamentType){

        switch($tournamentType){
            case 1://Demolition
                $nameFIle = 'backup_round20';
                break;
            case 2://Arms Race
                $nameFIle = 'backup_round00';
                break;
            case 3;//Deathmach
                $nameFIle = 'backup_round00';
                break;
            case 4://Classic competitive
                $nameFIle = 'backup_round20';
                break;
            default://If is deadmach
                $nameFIle = 'backup_round00';
                break;
        }

        $serverInfo = $this->getServerByTournament($torneId);
        if($serverInfo){
            $serverInfo = (is_array($serverInfo))?$serverInfo[0]:$serverInfo;
        }else{
            return false;
        }

        $config = array('hostname'=>$serverInfo->ip,'username'=>$serverInfo->ftpUser,'password'=>$serverInfo->ftpPass,'debug'=>false);
        $this->ci->ftp->connect($config);

        $remoteFiles = $this->ci->ftp->list_files('/1004734/csgo/csgo/');
        if($remoteFiles){

            foreach($remoteFiles as $files){

                if(strstr($files,$nameFIle)){
                    return true;//The tournament is finished
                }
            }
            return false;//This tournament not finish yet.
        }
        $this->ci->ftp->close();
        return false;
    }

    public function replyTournament($torneId){
        $tournament = $this->ci->tournaments_model->getTournaments(array('tournamentId'=>$torneId,'reply'=>true));
        $tournament = (is_array($tournament))?$tournament[0]:$tournament;
        $reply = array();
        foreach($tournament as $key=>$value){
            if($key!='tournamentId'){
                $reply[$key] = $value;
            }
        }
        $reply['status'] = 1;
        $newTournament = $this->ci->tournaments_model->add_tournament($reply);
        return $newTournament;
    }

    /*
     * Init record
     */
    public function record($server='',$port='',$password='',$torneId=''){
        set_time_limit(0);
        // Edit this ->
        define( 'SQ_SERVER_ADDR', $server );
        define( 'SQ_SERVER_PORT', $port );
        define( 'SQ_TIMEOUT',     1 );
        define( 'SQ_ENGINE',      SourceQuery :: SOURCE );
        // Edit this <-

        $Query = new SourceQuery( );

        try
        {
            $recordName = 'battleTv_'.$torneId;
            $Query->Connect( $server, $port, SQ_TIMEOUT, SQ_ENGINE );
            $Query->SetRconPassword($password);
            $Query->Rcon('tv_enable 1');
            $rcon = $Query->Rcon('tv_record '.$recordName);
            $Query->Disconnect();

            return $rcon;
        }
        catch( Exception $e )
        {
            $Query->Disconnect();
            $this->record($server,$port,$password,$torneId);
        }
    }

    /*
     * Stop record
     */
    public function stopRecord($server='',$port='',$password=''){
        set_time_limit(0);
        // Edit this ->
        define( 'SQ_SERVER_ADDR', $server );
        define( 'SQ_SERVER_PORT', $port );
        define( 'SQ_TIMEOUT',     1 );
        define( 'SQ_ENGINE',      SourceQuery :: SOURCE );
        // Edit this <-

        $Query = new SourceQuery( );

        try
        {
            $Query->Connect( $server, $port, SQ_TIMEOUT, SQ_ENGINE );
            $Query->SetRconPassword($password);
            $Query->Rcon('tv_stoprecord');
            $Query->Disconnect();
            return true;
        }
        catch( Exception $e )
        {
            $Query->Disconnect();
            $this->stopRecord($server,$port,$password);
        }
    }

    /*
     * Descargar partida grabada
     */
    public function downloadMatch($server,$ftpUser,$ftpPassword,$torneId){

        $config = array('hostname'=>$server,'username'=>$ftpUser,'password'=>$ftpPassword,'debug'=>false);
        $this->ci->ftp->connect($config);

        $path = $_SERVER['DOCUMENT_ROOT'].'/application/libraries/csgo/';
        $dir_destiny = $path.$torneId;
        $nameFile = 'battletv_'.$torneId.'.dem';

	 if(!file_exists($dir_destiny)){
            mkdir($dir_destiny,0755);
        }

        $download = $this->ci->ftp->download('/1004734/csgo/csgo/'.$nameFile,$dir_destiny.'/'.$nameFile, 'ascii');
        $this->ci->ftp->delete_file('/1004734/csgo/csgo/'.$nameFile);
        if($download){

            $this->ci->ftp->close();
            return true;
        }

        $this->ci->ftp->close();
        return false;

    }
    ///////////////////////////////////////////////////////////////////////////////////
    /*
     * Get server by Torneo
     */
    private function getServerByTournament($torneId){
        $server = $this->ci->tournaments_model->filter_tournaments(array('tournamentId'=>$torneId));
        if($server){
            return $server;
        }
        return false;
    }
    /*
     * Parse file for save into database
     */
	private function parseFileStats($idTournament,$file){
        $nameFile = $file;
        $file = fopen($file, "r") or exit("Unable to open file!");

        $csgo_round = array(
            'idTournament'=>$idTournament,
            'nameFile'=>$nameFile,
            'timestamp'=>'',
            'map'=>'',
            'round'=>'',
        );

        $csgo_result_teams = array(
            'team1'=>'',
            'team2'=>''
        );

        $csgo_history = array(
            'NumConsecutiveTerroristLoses'=>'',
            'LoserBonus'=>''
        );

        $csgo_teams = array(
            'PlayersOnTeam1'=>'',
            'PlayersOnTeam2'=>''
        );

        $csgo_players_teams_1 = array(
            'playerId'=>'',
            'name'=>'',
            'kills'=>'',
            'assists'=>'',
            'deaths'=>'',
            'mvps'=>'',
            'score'=>'',
            'cash'=>''
        );

        $csgo_players_teams_2 = array(
            'playerId'=>'',
            'name'=>'',
            'kills'=>'',
            'assists'=>'',
            'deaths'=>'',
            'mvps'=>'',
            'score'=>'',
            'cash'=>''
        );

        $content = '';

        $team1=false;
        $team2=false;

        while(!feof($file)){
            //Linea del archivos
            $line = fgets($file);
            //Eliminamos cosas que no necesitamos y concatenamos en content.
            $content.= trim(preg_replace('/\s\s+/', '',$line));

            //Los datos generales de la ronda
            foreach($csgo_round as $key => $value){
                if(strstr($line,$key)){
                    $csgo_round[$key] = trim(str_replace('"','',str_replace('"'.$key.'"','',$line)));
                }
            }

            //Resultado por equipo hasta el momento de esta ronda.
            foreach($csgo_result_teams as $key => $value){
                if(strstr($line,'"'.$key.'"')){
                    $csgo_result_teams[$key] = trim(str_replace('"','',str_replace('"'.$key.'"','',$line)));
                }
            }

            //History de la ronda hasta el momento (History).
            foreach($csgo_history as $key => $value){
                if(strstr($line,$key)){
                    $csgo_history[$key] = trim(str_replace('"','',str_replace('"'.$key.'"','',$line)));
                }
            }

            //Steamos las variables necesarias para entrar al arreglo del team1
            if(strstr($line,'PlayersOnTeam1')){
                $team2=false;
                $team1=true;
                $setLineTeam1 = 1;
                $team1_players = array();

            }

            //Steamos las variables necesarias para entrar al arreglo del team2
            if(strstr($line,'PlayersOnTeam2')){
                $team2=true;
                $team1=false;
                $setLineTeam2 = 1;
                $team2_players = array();
            }

            // Parseamos todos los players del team 1
            if($team1){
                if(isset($setLineTeam1) and $setLineTeam1==3 and !strstr($line,'}')){
                    $playerId = trim(str_replace('"','',str_replace('"'.$key.'"','',$line)));
                    if(!empty($playerId)){
                        $team1_players[$playerId] = array();
                    }
                }else{
                    if(isset($playerId)){
                        foreach($csgo_players_teams_2 as $key => $value){
                            if(strstr($line,$key)){
                                $team1_players[$playerId][$key] = trim(str_replace('"','',str_replace('"'.$key.'"','',$line)));
                            }
                        }

                        if(isset($lastLineTeam1)){
                            if(strstr($line,'}') and strstr($lastLineTeam1,'}')){
                                $setLineTeam1 = 2;
                            }elseif(strstr($line,'}') and !strstr($lastLineTeam1,'Items')){
                                $setLineTeam1 = 2;
                            }

                        }else{

                            if(strstr($line,'}')){
                                $setLineTeam1 = 2;
                            }
                        }
                    }
                }

                $setLineTeam1++;
                $lastLineTeam1 = $line;
            }

            // Parseamos todos los players del team 2
            if($team2){

                if($setLineTeam2==3 and !strstr($line,'}')){
                    $playerId = trim(str_replace('"','',str_replace('"'.$key.'"','',$line)));
                    if(!empty($playerId)){
                        $team2_players[$playerId] = array();
                    }
                }else{

                    foreach($csgo_players_teams_1 as $key => $value){
                        if(strstr($line,$key)){
                            $team2_players[$playerId][$key] = trim(str_replace('"','',str_replace('"'.$key.'"','',$line)));
                        }
                    }

                    if(isset($lastLineTeam2)){
                        if(strstr($line,'}') and strstr($lastLineTeam2,'}')){
                            $setLineTeam2 = 2;
                        }elseif(strstr($line,'}') and !strstr($lastLineTeam2,'Items')){
                            $setLineTeam2 = 2;
                        }

                    }else{

                        if(strstr($line,'}')){
                            $setLineTeam2 = 2;
                        }
                    }
                }

                $setLineTeam2++;
                $lastLineTeam2 = $line;
            }
        }
        fclose($file);

        $round = $this->csgo_rounds($csgo_round);

        if(!empty($round) and $round){
            $this->csgo_teams_score($round,$csgo_result_teams);
            $this->csgo_history($round,$csgo_history);
            $this->csgo_players($round,$team1_players,$team2_players);
            return true;
        }else{
            return false;
        }
    }

    /*
     * Save rounds into database
     */
    private function csgo_rounds($csgo_round){

        $prepare_data = array(
            'idTournament'=>$csgo_round['idTournament'],
            'nameFile'=>$csgo_round['nameFile'],
            'timeRound'=>$csgo_round['timestamp'],
            'mapRound'=>$csgo_round['map'],
            'numRound'=>$csgo_round['round']
        );

       $idRound = $this->ci->csgo_model->saveCsgoRounds($prepare_data);

       if($idRound){
           return $idRound;
       }else{
           return false;
       }
    }

    /*
     * Save score of determine round
     */
    private function csgo_teams_score($idRound,$csgo_score){

        $prepare_data = array(
            'idRound'=>$idRound,
            'team1'=>$csgo_score['team1'],
            'team2'=>$csgo_score['team2']
        );

        $idScore = $this->ci->csgo_model->saveCsgoScore($prepare_data);
        return $idScore;
    }

    /*
     * Save history
     */
    private function csgo_history($idRound,$csgo_history){

        $prepare_data = array(
            'idRound'=>$idRound,
            'NumConsecutiveTerroristLoses'=>$csgo_history['NumConsecutiveTerroristLoses'],
            'LoserBonus'=>$csgo_history['LoserBonus']
        );

        $idHistory = $this->ci->csgo_model->saveCsgoHistory($prepare_data);
        return $idHistory;
    }

    /*
     * Save players into database
     */
    private function csgo_players($idRound,$team1_players,$team2_players){


        foreach($team1_players as $key=>$value){
            $prepare_data_team1['idRound'] = $idRound;
            $prepare_data_team1['idPlayer'] = $key;
            foreach($value as $pKey=>$pValue){
                $prepare_data_team1[$pKey] = $pValue;
            }
            $prepare_data_team1['team'] = 1;

            $this->ci->csgo_model->savePlayersRound($prepare_data_team1);
        }

        foreach($team2_players as $key=>$value){
            $prepare_data_team2['idRound'] = $idRound;
            $prepare_data_team2['idPlayer'] = $key;
            foreach($value as $pKey=>$pValue){
                $prepare_data_team2[$pKey] = $pValue;
            }
            $prepare_data_team2['team'] = 2;

            $this->ci->csgo_model->savePlayersRound($prepare_data_team2);
        }
    }

    /*
     * Parse de archivo de consola del server
     */
    public function parseConsole($torneId){

        $file = $_SERVER['DOCUMENT_ROOT'].'/application/libraries/csgo/'.$torneId.'/console.log';

        $file = fopen($file, "r") or exit("Unable to open file!");

        $round = false;
        $i=0;
        $content = '';
        $players = array();
        $killers = array();
        //Por ahora solo parsea una sola ronda
        while(!feof($file)){

            $line = fgets($file);

            if(strstr($line,'World triggered "Round_Start"') and $i==0){
                $round = true;
                $i=1;
            }

            if($round and $i==1){

                if(strstr($line,'World triggered "Round_End"')){
                    $round = false;
                    $i=0;
                }else{
                    $content.=$line;
                    $playerId = $this->parsePlayerFromConsole($line);

                    if(!empty($playerId)){
                        $players[] = $playerId;
                    }

                    if(strstr($line,'killed')){
                        $killers[] = $line;
                    }
                }
            }
        }
        $players = $this->cleanArrayPlayersFromConsole($players);
        $result = $this->totalKillsForPlayerFromConsole($players,$killers);

        $teamWinner = $this->howIsTheTeamWinnerFromConsole($content);

        /*
         * save results into DB
         */
        $this->ci->csgo_model->saveConsoleLog($result,$torneId,$teamWinner);

        return $result;
    }

    /*
     * Check the team winner of a round form console.log
     */
    private function howIsTheTeamWinnerFromConsole($completeRound){
        if(strstr($completeRound,'SFUI_Notice_CTs_Win')){
            $team =  'CT WIN';
        }
        if(strstr($completeRound,'SFUI_Notice_Terrorists_Win')){
            $team =  'Terrorist WIN';
        }
        return (isset($team))?$team:'';
    }

    /*
     * Form modal how is the team winner
     */
    public function howIsTheTeamWinnerFromConsoleModal($torneId){
        $team = $this->ci->csgo_model->howIsTheTeamWinnerFromConsoleLog(array('torneId'=>$torneId));
        return $team;
    }
    /*
     * Parse player for get the steam id;
     */
    private function parsePlayerFromConsole($line){
        if(strstr($line,'<STEAM_')){
            $steamId = explode('<',$line);
            if(isset($steamId[2]) and !empty($steamId[2])){
                $player = substr($steamId[2], 0, strpos($steamId[2], '>'));
                if($player !='BOT'){
                    $result = $player;
                }
            }else{
                $result = '';
            }
        }else{
            $result = '';
        }
        return (isset($result))?$result:'';
    }

    /*
     * Clean array of players checking disconecting and duplicates
     */
    private function cleanArrayPlayersFromConsole($players=array()){
        $players =  array_values(array_unique($players));
        return $players;
    }

    /*
     * Count total kills and deaths of each steam id
     */
    private function totalKillsForPlayerFromConsole($players=array(),$killers=array()){
        $bigPlayer = array();
        if(!empty($players) and !empty($killers)){
            foreach($players as $player){
                $bigPlayer[$player] = array();
                $bigPlayer[$player]['kill'] = 0;
                $bigPlayer[$player]['death'] = 0;
                $bigPlayer[$player]['team'] = '';
                foreach($killers as $kill){
                    $parse = explode('killed',$kill);
                    if(!empty($parse[0])){
                        if(strstr($parse[0],$player)){
                            $bigPlayer[$player]['kill']++;
                            if(strstr($parse[0],'<CT>')){
                                $bigPlayer[$player]['team'] = 'CT';
                            }
                            if(strstr($parse[0],'<TERRORIST>')){
                                $bigPlayer[$player]['team'] = 'TERRORIST';
                            }
                        }
                    }
                    if(!empty($parse[1])){
                        if(strstr($parse[1],$player)){
                            $bigPlayer[$player]['death']++;

                            if(strstr($parse[1],'<CT>')){
                                $bigPlayer[$player]['team'] = 'CT';
                            }
                            if(strstr($parse[1],'<TERRORIST>')){
                                $bigPlayer[$player]['team'] = 'TERRORIST';
                            }
                        }
                    }
                }
            }
            //Hardcore
            $kills = 0;
            $deaths = 0;
            $steamId = 0;
            $result = array();

            //Resolvemos quien es el ganador
            foreach($bigPlayer as $key=>$player){

                if($player['kill'] >= $kills){
                    if($player['kill'] > $kills){
                        $steamId = $key;
                        $kills = $player['kill'];
                        $deaths = $player['death'];
                    }else{
                        if($player['deaths'] <= $deaths){
                            if($player['death'] < $deaths){
                                $steamId = $key;
                                $kills = $player['kill'];
                                $deaths = $player['death'];
                            }else{
                                if(!empty($result)){
                                    $result[] = $key;
                                }else{
                                    $result[] = $steamId;
                                    $result[] = $key;
                                }
                            }
                        }
                    }

                }

            }
            if(!empty($result)){
                foreach($result as $playerId){
                    $bigPlayer[$playerId]['win'] = 1;
                }
            }else{
                $bigPlayer[$steamId]['win'] = 1;
            }
            return $bigPlayer;
        }else{
            return false;
        }
    }

    /*
     * Metodo para chequear si el torneo sigue activo, identifica los jugadores que estan
     * en el server
     */
    public function playersInServerByTournament($torneId=false){
        set_time_limit(0);

        $prepare = array(
            'players'=>1,
            'status'=>3,
            'steamJoin'=>true
        );

        if($torneId){
            $prepare['tournamentId'] = $torneId;
        }
        //Tournament and players of the tournament
        $tournaments = $this->ci->tournaments_model->getTournaments($prepare);
        if(!empty($tournaments)){
            //Variable que delvolvemos
            $result = array();
            //recorremos todos los torneos
            foreach($tournaments as $tournament){
                //Tournament indice 0 is the tournament data
                $result['tournaments'][$tournament->tournamentId] =
                    array(
                        'tournamentId'=>$tournament->tournamentId,
                        'players'=>$this->connectToServer($tournament)
                    );
            }
            return $result;
        }
        return false;
    }

    /*
     * Connect with server by rcon
     */
    private function connectToServer($tournament){
        $result = array();
        //Set data for connecto the server
        $SQ_SERVER_ADDR = $tournament->ip;
        $SQ_SERVER_PORT = $tournament->port;
        $RCON = $tournament->rconPass;
        $SQ_TIMEOUT = 1;
        $SQ_ENGINE = SourceQuery :: SOURCE;
        //Abrimos el buffer para traer los players el server
        $Query = new SourceQuery( );
        //Conectamos al servers
        $Query->Connect( $SQ_SERVER_ADDR, $SQ_SERVER_PORT, $SQ_TIMEOUT, $SQ_ENGINE );
        try
        {
            //Seteamos el rcon para accedemos
            $Query->SetRconPassword($RCON);
            $status = $Query->Rcon('status');
            //Devolvemos la lista de players.
            $server = $this->parseStatus($status);
            //Identificamos los jugadores que estan jugando en este momento.
            if(isset($server['players']) and isset($server['humans']) and $server['humans'] > 0){
                //Recorremos todos los players que estan en el torneo
                foreach($tournament->players as $tPlayers){
                    $result[$tPlayers->steamId] = array(
                        'steamId'=>$tPlayers->steamId,
                        'userId'=>$tPlayers->userId,
                        'team'=>$tPlayers->group_type_id,
                        'playing'=>false
                    );
                    //Recorro todos los players que estan en el server
                    foreach($server['players'] as $sPlayers){
                        //Si el player cohincide con el registrado, identifico que esta jugando
                        if($sPlayers == $tPlayers->steamId){
                            $result[$sPlayers]['playing'] = true;
                        }
                    }
                }
                //Pregunto si estan los dos bandos jugando.
                $result['both'] = $this->bandSideConnected($result);
            }
            return $result;
        }
        catch( Exception $e )
        {
            //Si hubo problemas de conexion, volvemos a inentar
            return $this->connectToServer($tournament);
        }
    }

    /*
     * Parse status from the server
     */
    private function parseStatus($status){
        $result = array();
        $players = explode('players',$status);
        if(isset($players[1])){
            //Take if have players into the server
            $humans = explode('humans',$players[1]);
            if(isset($humans[0])){
                $result['humans'] = preg_replace("/[^0-9]/", "", $humans[0]);
            }
            //Players into the server
            $steamIds = explode('STEAM_',$players[1]);
            if(isset($steamIds[1])){
                unset($steamIds[0]);
                foreach($steamIds as $steamId){
                    $id = explode(':',$steamId);
                    if(isset($id[2])){
                        $id = explode(' ',$id[2]);
                        $result['players'][] = $id[0];
                    }
                }
            }
        }

        return $result;
    }

    /*
     * Identificamos si es un solo bando el que esta conectado
     */
    private function bandSideConnected($result){
        $team1 = false;
        $team2 = false;
        foreach($result as $players){
            if($players['team'] == 1 and $players['playing']){
               $team1 = true;
            }
            if($players['team'] == 2 and $players['playing']){
                $team2 = true;
            }
        }
        //Si los dos teams dan true, estan los dos bandos jugando.
        if($team1 and $team2){
            return true;
        }
        return false;
    }
}