<?php
/**
 * <sumary>
 * Modelo del usuario, represanta todas las acciones del usuario.
 * </sumary>
 **/

class Points_model extends CI_Model {

    function __construct()
    {
        // Call the Model constructor
        parent::__construct();
        $this->load->database();
    }

    /*
     * Obtengo la cantidad de points actuales que tiene el usuario
     */
    public function userPoints($userId,$tournamentId=0)
    {
        $this->db->select('id,points,date');
        $this->db->order_by("id", "desc");

        if($tournamentId!=0){
            $this->db->from('bp_historypoints');
            $this->db->where('userId',$userId);
            $this->db->where('tournamentId !=',$tournamentId);
            $this->db->limit(1);
            $query = $this->db->get();
        }else{
            $query = $this->db->get_where('bp_historypoints',array('userId' => $userId),1);
        }

        if ($query->num_rows() > 0){
            $userPoints = $query->row();
        }else{
            $userPoints = false;
        }

        return $userPoints;
    }

    /*
     * Historia completa de points
     */
    public function userHistoryPoints($options=array()){
        $userId = $options['userId'];
        $this->db->select('id, points, date');
        $this->db->from('bp_historypoints');
        $this->db->where('userId',$userId);
        $this->db->where('tournamentId',0);
        $this->db->order_by("id", "desc");
//        $this->db->join('bp_tournaments t','h.tournamentId = t.tournamentId');
        $points = $this->db->get();

        if($points->num_rows() > 0){
            return $points->result();
        }else{
            return false;
        }
    }

    /*
     * Update del history
     */
    public function plusPointsHistory($options=array()){
        $time = time();
        $now = date('Y-m-d h:i:s',$time);

        // A los puntos actuales le sumamos los que le demos que son los que le vamos a enttregar
        $points = $options['points']+$options['morePoints'];

        $args = array(
            'userId'=>$options['userId'],
            'points'=>$points,
            'date'=>$now
        );

        if(!empty($options['tournamentId'])){
            $this->db->from('bp_historypoints');
            $this->db->where('userId',$args['userId']);
            $this->db->where('tournamentId',$options['tournamentId']);
            $result = $this->db->get()->num_rows();
            if($result>0){
                return true;
            }else{
                $args['tournamentId'] = $options['tournamentId'];
            }
        }

        $newPoints = $this->db->insert('bp_historypoints',$args);

        if($newPoints){
            $userHistoryId = $this->db->insert_id();
            $prepare_data = array(
                'historyPointId'=>$userHistoryId,
                'userId'=>$options['userId'],
                'points'=>$points,
                'updateDate'=>date('Y-m-d h:i:s',time())
            );
            $this->updateActualPoints($prepare_data);
            return $points;
        }else{
            return false;
        }
    }

    public function discountUserPoints($userId, $bet, $tournamentId){

        if($userId){

            $user_points = $this->userPoints($userId,$tournamentId);

            //convierto los puntos a negativos asi los resto con la funcion de sumar puntos
            $new_bet = 0-$bet;

            if(($user_points->points - $bet)<0){
                return false;
            }else{
                $result = $this->plusPointsHistory(array('points'=>$user_points->points, 'morePoints'=>$new_bet, 'userId' => $userId,'tournamentId'=>$tournamentId));
                if($result===false){
                    return false;
                }
            }

            return $result;

        }else{

            return false;

        }

    }

    /////////// Metodos para actualizar o insertar puntos en la tabla de relaciones de puntos del usuario ///////////
    /*
     * Actualizar puntos en la tabla de relaciones
     */
    public function updateActualPoints($options=array()){
        if(isset($options['userId']) and !empty($options['userId'])){
            $actualPoints = $this->getActualPoints($options);
            //Si existen puntos actualizamos, si no, insertamos
            if($actualPoints){
                $this->db->update('bp_user_points',$options,array('userId'=>$options['userId']));
            }else{
                $this->db->insert('bp_user_points',$options);
            }
            return $this->getActualPoints($options);
        }
        return false;
    }

    /*
     * Traemos los puntos de un usuario
     */
    public function getActualPoints($options=array()){
        if(isset($options['userId']) and !empty($options['userId'])){
            $this->db->from('bp_user_points b');
            $this->db->select('b.userId,b.points,u.nicename,(SELECT count(userId)+1 FROM bp_user_points h WHERE points >= b.points and h.userId != b.userId) as rank');
            $this->db->where('b.userId',$options['userId']);
            $this->db->join('bp_users u','b.userId = u.userId');
            $query = $this->db->get();
            if($query->num_rows() >= 1){
                return $query->row();
            }
        }
        return false;
    }

    /*
     * Traemos el ranking de 6 usuarios basandonos en un aproximado mediante el puntaje del usuario
     */
    public function getRankingUsers($options=array()){
        $points = $this->getActualPoints(array('userId'=>$options['userId']));
        if($points){
            $limitBottom = 2;
            $limitTop = 3;
            //Consultamos los usuarios que tenemos encima
            $this->db->from('bp_user_points b');
            $this->db->select('b.userId,b.points,u.nicename,(SELECT count(userId)+1 FROM bp_user_points h WHERE points >= b.points and h.userId != b.userId) as rank');
            $this->db->where('b.points >=',$points->points);
            $this->db->where('b.userId !=',$options['userId']);
            $this->db->join('bp_users u','b.userId = u.userId');
            $this->db->order_by('b.points','asc');
            $this->db->limit($limitTop);
            $top = $this->db->get();
            $userTop = false;

            if($top->num_rows() >= 1){
                $userTop = array_reverse($top->result());
            }

            if($top->num_rows() < 3){
                $fix = ($top->num_rows()==0)?1:0;
                $limitBottom = $fix + 2 - $top->num_rows() + 2;
            }
            //Consultamos todos los usuario que tenemos debajo
            $this->db->from('bp_user_points b');
            $this->db->select('b.userId,b.points,u.nicename,(SELECT count(userId)+1 FROM bp_user_points h WHERE points >= b.points and h.userId != b.userId) as rank');
            $this->db->where('b.points <=',$points->points);
            $this->db->where('b.userId !=',$options['userId']);
            $this->db->join('bp_users u','b.userId = u.userId');
            $this->db->order_by('b.points','desc');
            $this->db->limit($limitBottom);
            $bottom = $this->db->get();
            $userBottom = false;

            if($bottom->num_rows() >= 1){
                $userBottom = $bottom->result();
            }

            //En caso que seamos el ultimo, volvemos a traer los usuarios por encima, ocon otro limit
            if($bottom->num_rows() < 2){
                $fix = ($bottom->num_rows()==0)?1:0;
                $limitTop = 3 - $bottom->num_rows() + 3 - $fix;

                $this->db->from('bp_user_points b');
                $this->db->select('b.userId,b.points,u.nicename,(SELECT count(userId)+1 FROM bp_user_points h WHERE points >= b.points and h.userId != b.userId) as rank');
                $this->db->where('b.points >=',$points->points);
                $this->db->where('b.userId !=',$options['userId']);
                $this->db->join('bp_users u','b.userId = u.userId');
                $this->db->order_by('b.points','asc');
                $this->db->limit($limitTop);
                $top = $this->db->get();
                if($top->num_rows() >= 1){
                    $userTop = array_reverse($top->result());
                }
            }

            $userTop[] = $points;
            $userRanking = array_merge($userTop,$userBottom);

            return $userRanking;
        }
        return false;
    }
    /*public function getRankingUsers($options=array()){
        if(isset($options['userId']) and !empty($options['userId'])){
            $query = $this->db->query('call rankingUserProfile('.$options['userId'].')');
            if($query->num_rows() >= 1){
                return $query->result();
            }
        }
        return false;
    }*/

    /////////////////////////// PRIVATE METHODOS //////////////////////////////
}
?>