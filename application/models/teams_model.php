<?php
/**
 * <sumary>
 * Master, estas en el model de teams
 * que esperar ver, cosas de teams.
 * </sumary>
 **/


class Teams_model extends CI_Model {

    function __construct()
    {
        parent::__construct();
    }

    /*
    * HOla, si como estas, este es el get de teams. Mamala
    */
    public function get_teams($options=array())
    {
        $default['idTeam'] = '';
        $default['teamLead'] = '';
        $default['count'] = false;
        $default['offset'] = '';
        $default['limit'] = '';
        $default['only_teams'] = false;
        $set = $this->functions->merge($default,$options);

        $this->db->from('bp_teams');
        if(!$set->only_teams){
            $this->db->join('bp_users','bp_users.userId = bp_teams.userTeamLead');
        }
        if(!empty($set->teamLead))$this->db->where('userTeamLead',$set->teamLead);
        if(!empty($set->idTeam))$this->db->where('idTeam',$set->idTeam);

        if(!empty($set->idTeam)){
            $teams = $this->db->get()->row();
        }else{
            if(!empty($set->limit))$this->db->limit($set->limit,$set->offset);

            if($set->count){
                $teams = $this->db->get()->num_rows();
            }else{
                $teams = $this->db->get()->result();
            }
        }
        return $teams;
    }

    /**
     * Edit team profile
     * @param idteam
     * @param teamDescription
     */
    public function editTeamProfile($idTeam,$teamDescription){
        if(!empty($idTeam)and !empty($teamDescription)){
            $this->db->where('idTeam',$idTeam);
            $this->db->update('bp_teams',array('teamDescription'=>$teamDescription));
            return true;
        }else{
            return false;
        }
    }

    /*
    * Hey, you.. mother fucker.. this is the members teams
    */
    public function get_members_teams($options=array())
    {
        $default['idTeam'] = '';
        $default['idUser'] = '';
        $default['flag'] = '';
        $set = $this->functions->merge($default,$options);
//
        $this->db->select('idTeamMember, idTeam, idUser, joinDate, flag, userId, password, full_name, nicename, email, confirm_email_key, countryId, status, birthday, create_date, last_access, status_tos, favouriteGameId, tempPass, creditcard_id, paypal_address, level, facebook_id,(SELECT imagePath from bp_user_avatar where bp_user_avatar.idUser = bp_users.userId and bp_user_avatar.size = 53) as avatarPath');
        $this->db->from('bp_teams_members');
        $this->db->join('bp_users','bp_users.userId = bp_teams_members.idUser','inner');

        if(!empty($set->flag)){

            if($set->flag == 3)$set->flag = 0;

            $this->db->where('flag',$set->flag);
        }

        if(!empty($set->idTeam))$this->db->where('idTeam',$set->idTeam);
        if(!empty($set->idUser))$this->db->where('idUser',$set->idUser);
        $this->db->where('flag != ', 3);
        $members = $this->db->get()->result();
//        echo $this->db->last_query();
//        die();
        return $members;

    }

    /*
    *
    * Request join to team.
    * Endpoint teams/requestJoinTeam
    *
    */
    public function requestJoinTeam($options=array()){
        $default['idTeam'] = '';
        $default['idUser'] = '';
        $set = $this->functions->merge($default,$options);

        $getMembers = $this->get_members_teams(array('idUser'=>$set->idUser,'idTeam'=>$set->idTeam));

        if(empty($getMembers)){
            $add = $this->addMember($set);
            return $add;
        }else{
            return false;
        }
    }
    /*
    *
    * Delete Request join to team.
    * Endpoint teams/requestJoinTeam
    *
    */
    public function deleteRequest($options=array()){
        $default['idTeam'] = '';
        $default['idUser'] = '';
        $set = $this->functions->merge($default,$options);

        $this->db->where('idTeam',$set->idTeam);
        $this->db->where('idUser',$set->idUser);
        $this->db->where('flag',0);
        $this->db->or_where('flag',2);
        $delete = $this->db->delete('bp_teams_members');
        if($delete){
            return $delete;
        }else{
            return false;
        }
    }

    /*
    *
    * Add new Member
    * Endpoint teams/requestJoinTeam
    *
    */
    private function addMember($set,$val){
        if (isset($val)) {
            if (isset($set->is_teamLead) and !empty($set->is_teamLead)) {
                $flag = 1;
            } else {
                $flag = 2;
            }
        } else {
            $flag = 0;
        }

        $array = array(
            'idTeam'=>$set->idTeam,
            'idUser'=>$set->idUser,
            'joinDate'=>date('Y-m-d h:i:s'),
            'flag'=>$flag
        );

        $insert = $this->db->insert('bp_teams_members',$array);
        return $insert;
    }

    /**
     * 
     * @param type $set
     * @return type
     */
    private function suggestMember($set){

        $array = array(
            'idTeam'=>$set->idTeam,
            'idUser'=>$set->idUser,
            'joinDate'=>date('Y-m-d h:i:s'),
            'flag'=>'2'
        );

        $insert = $this->db->insert('bp_teams_members',$array);
        return $insert;
    }
    
    /*
    *
    * Accept new member
    * Endpoint teams/joinTeam
    *
    */
    public function joinMember($options=array()){
        $default['idTeam'] = '';
        $default['idUser'] = '';
        
        $set = $this->functions->merge($default,$options);

        $this->db->where('idTeam',$set->idTeam);
        $this->db->where('idUser',$set->idUser);
        $this->db->where('flag', 0);
        $this->db->or_where('flag', 2);
        $update = $this->db->update('bp_teams_members',array('flag'=>'1'));
        return $update;
    }
    
    public function getTeamLeader($idTeam){
        $this->db->from('bp_teams');
        $this->db->select('userTeamLead');
        $this->db->where('idTeam', $idTeam);
        $leader = $this->db->get();
        
         if($leader->num_rows() > 0){
            return $leader->row();
        }else{
            return false;
        }
        
        return false;
    }
    
    public function leaveTeam($options=array()){
        $default['idTeam'] = '';
        $default['idUser'] = '';

        $set = $this->functions->merge($default,$options);
        $this->db->where('idTeam',$set->idTeam);
        $this->db->where('idUser',$set->idUser);
        $update = $this->db->update('bp_teams_members',array('flag'=>3));
        return $update;
    }
    
    /*
    *
    * Delete a invitation to join.
    * Endpoint teams/joinTeam
    *
    */
    public function declineInvitation($options=array()){
        $default['idTeam'] = '';
        $default['idUser'] = '';
        
        $set = $this->functions->merge($default,$options);
        $this->db->from('bp_teams_members');
        $this->db->where('idTeam',$set->idTeam);
        $this->db->where('idUser',$set->idUser);
        $update = $this->db->delete();
        return $update;
    }

    /*
    *
    * Add new Team
    * Endpoint Teams/newTeam
    *
    */
    public function newTeam($options=array(),$members=array()){

        $insert =  $this->db->insert('bp_teams',$options);
        $idTeam = $this->db->insert_id();
        $webUrl = $this->config->item('webUrl');
        if($insert){
            if(!empty($members)){
                $default['idTeam'] = $idTeam;
                $set = $this->functions->merge($default,$members);
                foreach($members as $member){
                    $set->idUser = $member;
                    $args = array(
                        'idUserReceived'=>$set->idUser,
                        'topic'=>'You\'ve been invited to join a team '.$options['teamName'],
                        'message'=>'<div class="mensaje">Team <a target="_blank" href="'.$webUrl.'/team_profile/show/'.$idTeam.'"> '.$options['teamName'].'</a> 
                            has invited you to join their ranks and they are awaiting for your response. 
                            <a target="_blank" href="'.$webUrl.'/team_profile/show/'.$idTeam.'">Go there</a> </br>'

                    );
                    $this->functions->addNotification($args);
                    $this->suggestMember($set);
                }
            }
            $def['idTeam'] = $idTeam;
            $def['idUser'] = $options['userTeamLead'];
            $own['is_teamLead'] = true;
            $owner = $this->functions->merge($def,$own);
            $this->addMember($owner,1);
            $result = $idTeam;
        }else{
            $result = false;
        }

        return $result;
    }
    
    /**
     * Invite more more and moar members to a existing Team.
     * @param type $idTeam
     * @param type $members
     * @param type $teamName
     * @return type
     */
    public function inviteMembers($idTeam, $members, $teamName/* = array()*/) {
         if(!empty($members)){
             $webUrl = $this->config->item('webUrl');
             $default['idTeam'] = $idTeam;
                $options['teamName'] = $teamName;
                $set = $this->functions->merge($default,$members);
                foreach($members as $member){
                    $set->idUser = $member;
                    $set->idTeam = $idTeam;
                    $args = array(
                        'idUserReceived'=>$set->idUser,
                        'topic'=>'You\'ve been invited to a new team ' .$options['teamName'],
                        'message'=>'<div class="mensaje">Team <a target="_blank" href="'.$webUrl.'/team_profile/show/'.$idTeam.'"> '.$options['teamName'].'</a> has invited you to join their ranks and they are awaiting for your response.</br> 
                                    <a target="_blank" href="'.$webUrl.'/team_profile/show/'.$idTeam.'">Go there</a> </br>'
                    );
                    $this->functions->addNotification($args);
                    $result = $this->suggestMember($set);
                }
            }
        return $result;
    }
    
    /*
     * Edit and add Avatar
     */

    public function addAvatar($options = array()) {
        /* @TODO: Aaca tenemos que hacerlo bien, esto es una mierda */
        $insert = $this->db->insert('bp_teams_avatar', $options);
        return $insert;
    }

    /*
     * Delete avatar/s
     */

    public function deleteAvatar($options = array()) {
        $default['idAvatar'] = '';
        $default['idTeam'] = '';
        $set = $this->functions->merge($default, $options);
        $this->db->from('bp_teams_avatar');
        if (!empty($set->idAvatar)) {
            $this->db->where('idAvatar', $set->idAvatar);
        }
        if (!empty($set->idTeam)) {
            $this->db->where('idTeam', $set->idTeam);
        }
        $delete = $this->db->delete();
        return $delete;
    }

    /*
     * Get Avatars by teamId
     */

    public function getAvatars($options = array()) {
        $default['idAvatar'] = '';
        $default['idTeam'] = '';
        $default['size'] = '';
        $set = $this->functions->merge($default, $options);
        $this->db->from('bp_teams_avatar');
        if (!empty($set->idAvatar)) {
            $this->db->where('idAvatar', $set->idAvatar);
        }
        if (!empty($set->idTeam)) {
            $this->db->where('idTeam', $set->idTeam);
        }
        if (!empty($set->size)) {
            $this->db->where('size', $set->size);
        }
        $result = $this->db->get()->result();
        return $result;
    }


    /*
    *
    * Get Team by User
    * Endpoint Teams/userTeams
    *
    */
    public function teamsByUser($userId = 0)
    {
        if($userId != 0){
            $this->db->from('bp_teams_members');
            $this->db->join('bp_teams','bp_teams_members.idTeam = bp_teams.idTeam');
            $this->db->select('bp_teams.idTeam, bp_teams.teamName');
            $this->db->where('bp_teams_members.idUser', $userId);
            $this->db->where('bp_teams_members.flag', 1);
            $result = $this->db->get();
            return $result->result();
        }else{
            return false;
        }
    }

    /*
    *
    * Get Team by Name
    * Endpoint Teams/getTeamByName
    *
    */
    public function teamsByName($teamName){
        if($teamName){
            $this->db->from('bp_teams');
            $this->db->select('idTeam');
            $this->db->like('teamName',$this->db->escape_like_str($teamName));
            return $this->db->get()->row();
        }else{
            return false;
        }
    }

    /*
    *
    * Set subleader
    *
    *
    */
    public function change_mvp($array){

        if(!empty($array)){

            $this->db->update('bp_teams',array('userMVP'=>$array['idUser']),array('idTeam'=>$array['idTeam']));

            $teams = $this->get_teams(array('idTeam'=>$array['idTeam']));

            if(!empty($teams)){
                if($teams[0]->userMVP == $array['idUser']){
                    return true;
                }
            }
            return false;

        }else{
            return false;
        }
    }
    /*
	*
	* Get Leader and SubLeader
	*
	*
	*/
    public function getImportantMembers($idTeam){
        if(!empty($idTeam)){
            $this->db->from('bp_teams t');
            $this->db->select('u.userId, u.nicename, u.email, u.full_name, u.level, (SELECT imagePath from bp_user_avatar where bp_user_avatar.idUser = u.userId and bp_user_avatar.size = 53) as imagePath');
            $this->db->join('bp_users u','u.userId = t.userTeamLead','inner');
            $this->db->where('t.idTeam',$idTeam);
            $result['lider'] = $this->db->get()->result();
            $this->db->from('bp_teams t');
            $this->db->select('u.userId, u.nicename, u.email, u.full_name, u.level, (SELECT imagePath from bp_user_avatar where bp_user_avatar.idUser = u.userId and bp_user_avatar.size = 53) as imagePath');
            $this->db->join('bp_users u','u.userId = t.userMVP','inner');
            $this->db->where('t.idTeam',$idTeam);
            $result['mvp'] = $this->db->get()->result();
            return $result;
        }else{
            return false;
        }
    }
}