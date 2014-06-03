<?php

require_once('Gameme.php');

class Tf2{
//
//    // This properties need to be change for dinamics information from database
//    var $rounds = 10;
//    var $prefix_file_names = 'backup_round';
//    var $extension_file = '.txt';
//    var $config = array('hostname'=>'208.167.232.112','username'=>'1004734_27035','password'=>'GAMES2011','debug'=>true);
//
	public function __construct()
    {
		$this->ci = & get_instance();
        /*$this->ci->load->library('ftp');
        $this->ci->load->model('csgo_model');*/
    }

    /*
    * Get stats in real time
    */
    public function getStats($server,$torne){

        $gameme = new gameMEAPI('http://battlepro.gameme.com/');
        $serverinfo = $gameme->client_api_serverinfo($server,GAMEME_DATA_PLAYERS);
        print_r($serverinfo);
        /*
        Array
        (
            [vendor] => Array
            (
                [label] => gameME
            [webpage] => http://www.gameME.com
            [license] => personal, non-commercial use only
            [copyright] => Copyright Â© gameME 2013 - TTS Oetzel & Goerz GmbH. All rights reserved.
        )

    [account] => Array
        (
            [webpage] => http://battlepro.gameme.com
        )

    [serverinfo] => Array
        (
            [0] => Array
            (
                [id] => 1
                    [addr] => 208.167.232.112
                    [port] => 27035
                    [game] => csgo
                    [steam] => Array
        (
            [version] => 1.23.1.2/12312 5336 secure
                            [officialversion] => 12312
                            [updaterequired] => 0
                        )

                    [uptime] => 251
                    [name] => Battle Pro - Counter Strike Pro Gamer
                    [map] => de_train
                    [mapimages] => Array
        (
            [0] => Array
            (
                [0] => http://s1.gameme.net/img/maps/csgo/0/de_train_1_180_14841_d402c91b4e4b29632855b4383a90cf1c7d209d60.jpg
                                    [1] => http://s1.gameme.net/img/maps/csgo/0/de_train_2_180_14841_283630bd168cba85f62d88a661e67400c9fa62fc.jpg
                                    [2] => http://s1.gameme.net/img/maps/csgo/0/de_train_3_180_14841_8fdb84809e8dfec63da07c5b292a41b0fd3d2f7d.jpg
                                )

                            [1] => Array
        (
            [0] => http://s1.gameme.net/img/maps/csgo/0/de_train_1_164_14841_dae9da708b9bd346d1329cfbe11f4224710a28f4.jpg
                                    [1] => http://s1.gameme.net/img/maps/csgo/0/de_train_2_164_14841_c1b10dc6b6d3844cf4f784a89bfcb2d05efbb407.jpg
                                    [2] => http://s1.gameme.net/img/maps/csgo/0/de_train_3_164_14841_c9d66f243a9c25493ac4f7bcdd456a1fa1f0a328.jpg
                                )

                            [2] => Array
        (
            [0] => http://s1.gameme.net/img/maps/csgo/0/de_train_1_40_14841_542e967d6757bb8f7baa59ff2b4542cc8ebd8ab0.jpg
                                    [1] => http://s1.gameme.net/img/maps/csgo/0/de_train_2_40_14841_455bdbad17c86376b35e3a7a4fbb5f4295894bba.jpg
                                    [2] => http://s1.gameme.net/img/maps/csgo/0/de_train_3_40_14841_0c59681051ee451a0f55e05fef2f60003833781f.jpg
                                )

                        )

                    [time] => 1372717725
                    [act] => 2
                    [bots] => 0
                    [max] => 12
                    [kills] => 59
                    [hs] => 21
                    [suicides] => 0
                    [shots] => 0
                    [hits] => 0
                    [cc] => us
                    [cn] => United States
                    [teams] => Array
        (
            [0] => Array
            (
                [name] => CT
                                    [count] => 1
                                )

                            [1] => Array
        (
            [name] => TERRORIST
                                    [count] => 1
                                )

                        )

                    [players] => Array
        (
            [0] => Array
            (
                [id] => 3
                                    [name] => emiliano.velazquez.m
                                    [uniqueid] => STEAM_0:1:65888900
                                    [steamavatar] => http://s2.gameme.net/img/community_default_avatar_184.jpg
                                    [team] => CT
                                    [squad] => None
                                    [kills] => 1
                                    [deaths] => 0
                                    [suicides] => 0
                                    [hs] => 1
                                    [shots] => 0
                                    [hits] => 0
                                    [isdead] => 0
                                    [hasbomb] => 0
                                    [ping] => 0
                                    [connected] => 1372717669
                                    [skillchange] => 2
                                    [skill] => 961
                                    [rank] => 3
                                    [assists] => 0
                                    [assisted] => 0
                                    [killstreak] => 1
                                    [deathstreak] => 0
                                    [rounds] => 1
                                    [survived] => 0.00
                                    [wins] => 0
                                    [losses] => 0
                                    [teamkills] => 0
                                    [teamkilled] => 0
                                    [healedpoints] => 0
                                    [flagscaptured] => 0
                                    [cc] => ar
                                    [cn] => Argentina
                                )

                            [1] => Array
        (
            [id] => 2
                                    [name] => mauritovela
                                    [uniqueid] => STEAM_0:1:34140120
                                    [steamavatar] => http://s2.gameme.net/img/community_default_avatar_184.jpg
                                    [team] => TERRORIST
                                    [squad] => None
                                    [kills] => 0
                                    [deaths] => 1
                                    [suicides] => 0
                                    [hs] => 0
                                    [shots] => 0
                                    [hits] => 0
                                    [isdead] => 1
                                    [hasbomb] => 0
                                    [ping] => 0
                                    [connected] => 1372717697
                                    [skillchange] => -2
                                    [skill] => 1075
                                    [rank] => 1
                                    [assists] => 0
                                    [assisted] => 0
                                    [killstreak] => 0
                                    [deathstreak] => 1
                                    [rounds] => 1
                                    [survived] => 0.00
                                    [wins] => 0
                                    [losses] => 0
                                    [teamkills] => 0
                                    [teamkilled] => 0
                                    [healedpoints] => 0
                                    [flagscaptured] => 0
                                    [cc] => ar
                                    [cn] => Argentina
                                )

                        )

                )

        )

)*/
    }
//
//    public function getServerFiles($torneId){
//
//        if(empty($torneId)){
//            return false;
//        }
//
//        $dir_destiny = $_SERVER['DOCUMENT_ROOT'].'application/libraries/csgo/'.$torneId;
//
//        if(!file_exists($dir_destiny)){
//            mkdir($dir_destiny,0644);
//        }
//
//        $this->ci->ftp->connect($this->config);
//
//        $remoteFiles = $this->ci->ftp->list_files('/1004734/csgo/csgo/');
//
//        foreach($remoteFiles as $files){
//            if(strstr($files,$this->prefix_file_names)){
//
//                $file = '/1004734/csgo/csgo/'.$files;
//                $destiny_file = $dir_destiny.'/'.$files;
//
//                $download = $this->ci->ftp->download($file,$destiny_file, 'ascii');
//
//                if(!$download){
//                    return false;
//                }else{
//                    $this->ci->ftp->delete_file($file);
//                }
//            }
//        }
//
//        $this->ci->ftp->close();
//
//        return true;
//    }
//
//    /*
//     * Parse statistics of tournament of csgo and save into database
//     */
//    public function parseCsgoStats($torneId){
//        $dir = $_SERVER['DOCUMENT_ROOT'].'application/libraries/csgo/'.$torneId.'/';
//        $dir_stats = opendir($dir);
//        while($files = readdir($dir_stats)){
//            if(!is_dir($files) and $files != '.' and $files != '..'){
//                $this->parseFileStats($torneId,$dir.$files);
//            }
//
//        }
//    }
//
//    /*
//     * Tomamos el team ganador de un torneo
//     */
//    public function howIsTheTeamWinner($torneId){
//
//        $teamsResults = $this->ci->csgo_model->getRoundTeams(array('idTournament'=>$torneId));
//
//        if($teamsResults){
//            $team1 = 0;
//            $team2 = 0;
//            $win = 0; //Empate: 0, Team1: 1, Team2: 2.
//            foreach($teamsResults as $teams){
//                if($team1 < $team2){
//                    $win = 2;
//                }elseif($team1 > $team2){
//                    $win = 1;
//                }else{
//                    $win = 0;
//                }
//            }
//            return $win;
//        }else{
//            return false;
//        }
//    }
//
//    /*
//     * Totamos el player ganador
//     */
//    public function howIsThePlayerWinner($torneId){
//        //TODO: Debemos chequear que pasa con mas de una ronda
//        $players = $this->ci->csgo_model->getPlayerWin(array('idTournament'=>$torneId));
//        return $players;
//    }
//    /*
//     * Parse file for save into database
//     */
//	private function parseFileStats($idTournament,$file){
//        $nameFile = $file;
//        $file = fopen($file, "r") or exit("Unable to open file!");
//
//        $csgo_round = array(
//            'idTournament'=>$idTournament,
//            'nameFile'=>$nameFile,
//            'timestamp'=>'',
//            'map'=>'',
//            'round'=>'',
//        );
//
//        $csgo_result_teams = array(
//            'team1'=>'',
//            'team2'=>''
//        );
//
//        $csgo_history = array(
//            'NumConsecutiveTerroristLoses'=>'',
//            'LoserBonus'=>''
//        );
//
//        $csgo_teams = array(
//            'PlayersOnTeam1'=>'',
//            'PlayersOnTeam2'=>''
//        );
//
//        $csgo_players_teams_1 = array(
//            'playerId'=>'',
//            'name'=>'',
//            'kills'=>'',
//            'assists'=>'',
//            'deaths'=>'',
//            'mvps'=>'',
//            'score'=>'',
//            'cash'=>''
//        );
//
//        $csgo_players_teams_2 = array(
//            'playerId'=>'',
//            'name'=>'',
//            'kills'=>'',
//            'assists'=>'',
//            'deaths'=>'',
//            'mvps'=>'',
//            'score'=>'',
//            'cash'=>''
//        );
//
//        $content = '';
//
//        $team1=false;
//        $team2=false;
//
//        while(!feof($file)){
//            //Linea del archivos
//            $line = fgets($file);
//            //Eliminamos cosas que no necesitamos y concatenamos en content.
//            $content.= trim(preg_replace('/\s\s+/', '',$line));
//
//            //Los datos generales de la ronda
//            foreach($csgo_round as $key => $value){
//                if(strstr($line,$key)){
//                    $csgo_round[$key] = trim(str_replace('"','',str_replace('"'.$key.'"','',$line)));
//                }
//            }
//
//            //Resultado por equipo hasta el momento de esta ronda.
//            foreach($csgo_result_teams as $key => $value){
//                if(strstr($line,'"'.$key.'"')){
//                    $csgo_result_teams[$key] = trim(str_replace('"','',str_replace('"'.$key.'"','',$line)));
//                }
//            }
//
//            //History de la ronda hasta el momento (History).
//            foreach($csgo_history as $key => $value){
//                if(strstr($line,$key)){
//                    $csgo_history[$key] = trim(str_replace('"','',str_replace('"'.$key.'"','',$line)));
//                }
//            }
//
//            //Steamos las variables necesarias para entrar al arreglo del team1
//            if(strstr($line,'PlayersOnTeam1')){
//                $team2=false;
//                $team1=true;
//                $setLineTeam1 = 1;
//                $team1_players = array();
//
//            }
//
//            //Steamos las variables necesarias para entrar al arreglo del team2
//            if(strstr($line,'PlayersOnTeam2')){
//                $team2=true;
//                $team1=false;
//                $setLineTeam2 = 1;
//                $team2_players = array();
//            }
//
//            // Parseamos todos los players del team 1
//            if($team1){
//                if($setLineTeam1==3 and !strstr($line,'}')){
//                    $playerId = trim(str_replace('"','',str_replace('"'.$key.'"','',$line)));
//                    if(!empty($playerId)){
//                        $team1_players[$playerId] = array();
//                    }
//                }else{
//                    if(isset($playerId)){
//                        foreach($csgo_players_teams_2 as $key => $value){
//                            if(strstr($line,$key)){
//                                $team1_players[$playerId][$key] = trim(str_replace('"','',str_replace('"'.$key.'"','',$line)));
//                            }
//                        }
//
//                        if(isset($lastLineTeam1)){
//                            if(strstr($line,'}') and strstr($lastLineTeam1,'}')){
//                                $setLineTeam1 = 2;
//                            }elseif(strstr($line,'}') and !strstr($lastLineTeam1,'Items')){
//                                $setLineTeam1 = 2;
//                            }
//
//                        }else{
//
//                            if(strstr($line,'}')){
//                                $setLineTeam1 = 2;
//                            }
//                        }
//                    }
//                }
//
//                $setLineTeam1++;
//                $lastLineTeam1 = $line;
//            }
//
//            // Parseamos todos los players del team 2
//            if($team2){
//
//                if($setLineTeam2==3 and !strstr($line,'}')){
//                    $playerId = trim(str_replace('"','',str_replace('"'.$key.'"','',$line)));
//                    if(!empty($playerId)){
//                        $team2_players[$playerId] = array();
//                    }
//                }else{
//
//                    foreach($csgo_players_teams_1 as $key => $value){
//                        if(strstr($line,$key)){
//                            $team2_players[$playerId][$key] = trim(str_replace('"','',str_replace('"'.$key.'"','',$line)));
//                        }
//                    }
//
//                    if(isset($lastLineTeam2)){
//                        if(strstr($line,'}') and strstr($lastLineTeam2,'}')){
//                            $setLineTeam2 = 2;
//                        }elseif(strstr($line,'}') and !strstr($lastLineTeam2,'Items')){
//                            $setLineTeam2 = 2;
//                        }
//
//                    }else{
//
//                        if(strstr($line,'}')){
//                            $setLineTeam2 = 2;
//                        }
//                    }
//                }
//
//                $setLineTeam2++;
//                $lastLineTeam2 = $line;
//            }
//        }
//        fclose($file);
//
//        $round = $this->csgo_rounds($csgo_round);
//
//        if(!empty($round)){
//            $this->csgo_teams_score($round,$csgo_result_teams);
//            $this->csgo_history($round,$csgo_history);
//            $this->csgo_players($round,$team1_players,$team2_players);
//            return true;
//        }else{
//            return false;
//        }
//    }
//
//    /*
//     * Save rounds into database
//     */
//    private function csgo_rounds($csgo_round){
//
//        $prepare_data = array(
//            'idTournament'=>$csgo_round['idTournament'],
//            'nameFile'=>$csgo_round['nameFile'],
//            'timeRound'=>$csgo_round['timestamp'],
//            'mapRound'=>$csgo_round['map'],
//            'numRound'=>$csgo_round['round']
//        );
//
//       $idRound = $this->ci->csgo_model->saveCsgoRounds($prepare_data);
//       return $idRound;
//    }
//
//    /*
//     * Save score of determine round
//     */
//    private function csgo_teams_score($idRound,$csgo_score){
//
//        $prepare_data = array(
//            'idRound'=>$idRound,
//            'team1'=>$csgo_score['team1'],
//            'team2'=>$csgo_score['team2']
//        );
//
//        $idScore = $this->ci->csgo_model->saveCsgoScore($prepare_data);
//        return $idScore;
//    }
//
//    /*
//     * Save history
//     */
//    private function csgo_history($idRound,$csgo_history){
//
//        $prepare_data = array(
//            'idRound'=>$idRound,
//            'NumConsecutiveTerroristLoses'=>$csgo_history['NumConsecutiveTerroristLoses'],
//            'LoserBonus'=>$csgo_history['LoserBonus']
//        );
//
//        $idHistory = $this->ci->csgo_model->saveCsgoHistory($prepare_data);
//        return $idHistory;
//    }
//
//    /*
//     * Save players into database
//     */
//    private function csgo_players($idRound,$team1_players,$team2_players){
//
//
//        foreach($team1_players as $key=>$value){
//            $prepare_data_team1['idRound'] = $idRound;
//            $prepare_data_team1['idPlayer'] = $key;
//            foreach($value as $pKey=>$pValue){
//                $prepare_data_team1[$pKey] = $pValue;
//            }
//            $prepare_data_team1['team'] = 1;
//
//            $this->ci->csgo_model->savePlayersRound($prepare_data_team1);
//        }
//
//        foreach($team2_players as $key=>$value){
//            $prepare_data_team2['idRound'] = $idRound;
//            $prepare_data_team2['idPlayer'] = $key;
//            foreach($value as $pKey=>$pValue){
//                $prepare_data_team2[$pKey] = $pValue;
//            }
//            $prepare_data_team2['team'] = 2;
//
//            $this->ci->csgo_model->savePlayersRound($prepare_data_team2);
//        }
//    }
//	/**
//	  * Functions for merge
//	  *
//	  * @access     public
//	  * @return     void
//	**/
//
//	private function merge($default, $options, $array=FALSE){
//		if (is_array($options)) {
//			  $settings = array_merge($default, $options);
//		  } else {
//			  parse_str($options, $output);
//			  $settings = array_merge($default, $output);
//		  }
//
//		  return ($array) ? $settings : (Object) $settings;
//    }
//
}