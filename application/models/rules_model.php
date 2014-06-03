<?php
/*
Modelaje de Rules, BattlePro Rocks, BattlePro Rules!
*/
class rules_model extends CI_Model {

    function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    public function get_rules($idTournament = '')
    {
        if ($idTournament != '') {
            //defino el select que es un hijueputa
            $select = <<< SELECT
gm.nameMode as GMName,
gm.description as GMDescription,
gm.imageClass as GMClass,
gs.structureName as GSName,
gs.description as GSDescription,
gs.imageClass as GSClass,
gms.nameModeServer as GMSName,
gms.description as GMSDescription,
gms.imageClass as GMSClass,
SELECT;

            $this->db->from('bp_tournaments as t');
            $this->db->select($select);
            $this->db->join('bp_game_modes as gm','t.gameMode = gm.idMode','left');
            $this->db->join('bp_game_mode_server as gms','t.gameModeServer = gms.idGameModeServer','rigth');
            $this->db->join('bp_game_structure as gs','t.gameStructure = gs.idGameStructure','left');
            $this->db->where('t.tournamentId',$idTournament);
            $rules = $this->db->get();
//            echo $this->db->last_query();
            if($rules->num_rows() > 0){
                return $rules->result();
            }else{
                return false;
            }
        }else{
            $rules = false;
        }
        return $rules;
    }

}