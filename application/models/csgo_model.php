<?php

class csgo_model extends CI_Model {

    function __construct()
    {
        parent::__construct();
    }
/*
    public function updatePlayerServer($options=array()){
        $players = $this->getPlayerServer($options);
        if($players){
            $this->db->update('bp_csgo_player_stats',$options,array('idPlayerStats'=>$players->idPlayerStats,'idTournament'=>$options['idTournament']));
        }else{
            $this->db->insert('bp_csgo_player_stats',$options);
        }
    }

    public function getPlayerServer($options=array()){
        $idTournament = $options['idTournament'];
        $this->db->from('bp_csgo_player_stats');

        $this->db->where('idTournament',$idTournament);
        $this->db->where('uniqueid',$options['uniqueid']);

        $result = $this->db->get();

        if($result->num_rows() > 0){

            return $result->row();
        }else{
            return false;
        }
    }
*/
    /*
     * save csgo rounds
     */
    public function saveCsgoRounds($options=array()){
        $round = $this->getRound($options);
        if(!empty($round)){
            return false;
        }
        $insert = $this->db->insert('bp_csgo_rounds',$options);
        return $this->db->insert_id();
    }

    /*
     * Guardar el score de la ronda
     */
    public function saveCsgoScore($options=array()){
        $insert = $this->db->insert('bp_csgo_round_team_score',$options);
        return $insert;
    }

    /*
     * Guardar el score de la ronda
     */
    public function saveCsgoHistory($options=array()){
        $insert = $this->db->insert('bp_csgo_round_history',$options);
        return $insert;
    }

    /*
     * Guardamos los jugadores de la partida
     */
    public function savePlayersRound($options=array()){
        $insert = $this->db->insert('bp_csgo_round_players',$options);
        return $insert;
    }

    /*
     * Traemos las rondas de un torneo
     */
    public function getRound($options=array()){
        $default[] = '';
        $set = $this->functions->merge($default,$options);

        $this->db->from('bp_csgo_rounds');

        if(isset($set->idTournament)){
            $this->db->where('idTournament',$set->idTournament);
        }

        if(isset($set->nameFile)){
            $this->db->where('nameFile',$set->nameFile);
        }

        $result = $this->db->get();

        if($result->num_rows() > 0){
            return $result->result();
        }else{
            return false;
        }
    }

    /*
     *
     */

    /*
     * Traemos el resultado de cada team de cada ronda de un torneo
     */
    public function getRoundTeams($options=array()){
        $default[] = '';
        $set = $this->functions->merge($default,$options);

        $rounds = $this->getRound(array('idTournament'=>$set->idTournament));

        if($rounds){
            foreach($rounds as $round){

                $this->db->from('bp_csgo_round_team_score');
                $this->db->where('idRound',$round->idRound);
                $result = $this->db->get();
                if($result->num_rows() > 0){
                    $teamScore[] = $result->result();
                }
            }
            return $teamScore;
        }
        return false;
    }

    /*
     * Tramoes los players de cada ronda de un torneo especifico
     */
    public function getRoundPlayers($options=array()){
        $default[] = '';
        $set = $this->functions->merge($default,$options);

        $rounds = $this->getRound(array('idTournament'=>$set->idTournament));

        if($rounds){
            foreach($rounds as $round){

                $this->db->from('bp_csgo_round_players');
                $this->db->where('idRound',$round->idRound);
                $result = $this->db->get();

                if($result->num_rows() > 0){
                    return $result->result();
                }
            }
        }else{
            return false;
        }
    }

    /*
     * Ganador por ronda
     */
    public function getPlayerWin($options=array()){
        $default[] = '';
        $set = $this->functions->merge($default,$options);

        $rounds = $this->getRound(array('idTournament'=>$set->idTournament));

        if($rounds){
            $resultRounds = array();
            foreach($rounds as $round){

                $result = $this->db->query('SELECT p.idPlayers,p.idRound,p.idPlayer,p.name,p.kills,p.deaths,ut.userId FROM bp_csgo_round_players p LEFT JOIN bp_users_steam ut ON ut.gameId = p.idPlayer
                WHERE p.idRound = '.$round->idRound.' and p.kills =
                (SELECT max(kills) FROM bp_csgo_round_players where idRound = '.$round->idRound.') ORDER BY deaths asc');

                if($result->num_rows() > 0){
                    $resultRounds = $result->row();
                }
            }

            return $resultRounds;
        }else{
            return false;
        }
    }

    /*
     * Traemos el ganador por consola
     */
    public function getPlayerWinnerFromConsole($opt=array()){
        $this->db->from('bp_csgo_console');
        $this->db->where('torneId',$opt['torneId']);
        $this->db->where('win','1');

        $query = $this->db->get();

        if($query->num_rows() > 0){
            return $query->result();
        }

        return false;
    }

    /*
     * Get winner by Steam ID
     */
    public function getWinnerBySteamId($opt=array()){
        $this->db->from('bp_csgo_console');
        $this->db->where('torneId',$opt['torneId']);
        $this->db->where('steamId',$opt['steamId']);
        $this->db->where('win','1');

        $query = $this->db->get();

        if($query->num_rows() > 0){
            return $query->row();
        }

        return false;
    }
    /*
     * Save console log into db
     */
    public function saveConsoleLog($result,$torneId,$teamWinner){
        if(!empty($result)){

            foreach($result as $key => $player){
                $prepare_data = array(
                    'torneId'=>$torneId,
                    'steamId'=>$key,
                    'kills'=>$player['kill'],
                    'deaths'=>$player['death'],
                    'team'=>$player['team'],
                    'win'=>(isset($player['win']))?$player['win']:0
                );
                $this->db->insert('bp_csgo_console',$prepare_data);
            }
        }

        if(!empty($teamWinner)){
            $prepare = array(
                'torneId'=>$torneId,
                'team'=>$teamWinner
            );
            $this->db->insert('bp_csgo_console_team',$prepare);
        }
    }

    /*
     * How is the player winner from console.log
     */
    public function howIsTheTeamWinnerFromConsoleLog($opt=array()){
        $this->db->from('bp_csgo_console_team');
        $this->db->where('torneId',$opt['torneId']);
        $query = $this->db->get();
        return $query->row();
    }
}