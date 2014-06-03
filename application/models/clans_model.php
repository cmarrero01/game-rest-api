<?php
/**
 * <sumary>
 * Master, estas en el model de clans, no de clans, fijate bien please, aunque es igual ironicamente.
 * que esperar ver, cosas de clans.
 * </sumary>
 **/


class Clans_model extends CI_Model {

    function __construct()
    {
        parent::__construct();
    }

    /*
    * HOla, si como estas, este es el get de clans. Mamala
    */
    public function get_clans($options=array())
    {
        $default['idClan'] = '';
        $default['clanLead'] = '';
        $default['count'] = false;
        $default['offset'] = '';
        $default['limit'] = '';
        $set = $this->functions->merge($default,$options);

        $this->db->from('bp_clans');
        $this->db->join('bp_users','bp_users.userId = bp_clans.userClanLead','inner');

        if(!empty($set->clanLead))$this->db->where('userClanLead',$set->clanLead);
        if(!empty($set->idClan))$this->db->where('idClan',$set->idClan);

        if(!empty($set->idClan)){
            $clans = $this->db->get()->row();
        }else{
            if(!empty($set->limit))$this->db->limit($set->limit,$set->offset);

            if($set->count){
                $clans = $this->db->get()->num_rows();
            }else{
                $clans = $this->db->get()->result();
            }
        }

        return $clans;
    }

    /**
     * Edit clan profile
     * @param idClan
     * @param clanDescription
     */
    public function editClanProfile($idClan,$clanDescription){
        if(!empty($idClan)and !empty($clanDescription)){
            $this->db->where('idClan',$idClan);
            $this->db->update('bp_clans',array('clanDescription'=>$clanDescription));
            return true;
        }else{
            return false;
        }
    }
    /*
    * Obtener Clanes a los que pertenece un usuario
    */
    public function get_user_clans($options=array())
    {

    }

    /*
    * Hey, you.. mother fucker.. this is the members clans
    */
    public function get_members_clans($options=array())
    {
        $default['idClan'] = '';
        $default['idUser'] = '';
        $default['flag'] = '';
        $set = $this->functions->merge($default,$options);
        $this->db->select('idClansMember, idClan, idUser, joinDate, flag, userId, password, full_name, nicename, email, confirm_email_key, countryId, status, birthday, create_date, last_access, status_tos, favouriteGameId, tempPass, creditcard_id, paypal_address, level, facebook_id, (SELECT imagePath from bp_user_avatar where bp_user_avatar.idUser = bp_users.userId and bp_user_avatar.size = 53) as avatarPath');
        $this->db->from('bp_clans_members');
        $this->db->join('bp_users','bp_users.userId = bp_clans_members.idUser','inner');

        if(!empty($set->flag)){
            if($set->flag == 2)$set->flag = 0;
            $this->db->where('flag',$set->flag);
        }
        
        if(!empty($set->idClan))$this->db->where('idClan',$set->idClan);
        if(!empty($set->idUser))$this->db->where('idUser',$set->idUser);
        $this->db->where('flag != ', 3);

        $members = $this->db->get()->result();

        return $members;

    }

    /**
     * 
     */
    public function get_membersClans($options=array())
    {
		$default['idClan'] = '';
		$default['flag'] = '';
		$set = $this->functions->merge($default,$options);
                $this->db->select('idClansMember, idClan, idUser, flag, userId, full_name, nicename,(SELECT imagePath from bp_user_avatar where bp_user_avatar.idUser = bp_users.userId and bp_user_avatar.size = 53) as avatarPath');
		$this->db->from('bp_clans_members');
		$this->db->join('bp_users','bp_users.userId = bp_clans_members.idUser','inner');
                if(!empty($set->flag)){

			if($set->flag == 3)$set->flag = 0;

			$this->db->where('flag',$set->flag);
		}
		if(!empty($set->idClan))$this->db->where('idClan',$set->idClan);
                if(!empty($set->flag))$this->db->where('flag',$set->flag);
                $this->db->where('flag != ', 3);
        $members = $this->db->get()->result();

        return $members;

    }
    
    /*
    *
    * Request join to clan.
    * Endpoint Clans/requestJoinClan
    *
    */
    public function requestJoinClan($options=array()){
        $default['idClan'] = '';
        $default['idUser'] = '';
        $set = $this->functions->merge($default,$options);

        $getMembers = $this->get_members_clans(array('idUser'=>$set->idUser,'idClan'=>$set->idClan));

        if(empty($getMembers)){
            $add = $this->addMember($set);
            return $add;
        }else{
            return false;
        }
    }
    /*
    *
    * Delete Request join to Clan.
    * Endpoint clan/requestJoinClan
    *
    */
    public function deleteRequest($options=array()){
        $default['idClan'] = '';
        $default['idUser'] = '';
        $set = $this->functions->merge($default,$options);

        $this->db->where('idClan',$set->idClan);
        $this->db->where('idUser',$set->idUser);
        $this->db->where('flag',0);
        $this->db->or_where('flag',2);
        $delete = $this->db->delete('bp_clans_members');
        if($delete){
            return $delete;
        }else{
            return false;
        }
    }

    /*
    *
    * Add new Member
    * Endpoint clans/requestJoinClan
    *
    */
    private function addMember($set, $val){
        if(isset($val)){
            if(isset($set->is_clanLead) and !empty($set->is_clanLead)){
                $flag = 1;
            }else{
                $flag = 2;
            }
        }else{
            $flag = 0;
        }

        $array = array(
            'idClan'=>$set->idClan,
            'idUser'=>$set->idUser,
            'joinDate'=>date('Y-m-d h:i:s'),
            'flag'=>$flag
        );

        $insert = $this->db->insert('bp_clans_members',$array);
        return $insert;
    }
        /**
         * 
         * @param type $set
         * @return type
         */
        private function suggestMember($set){
		$array = array(
			'idClan'=>$set->idClan,
			'idUser'=>$set->idUser,
			'joinDate'=>date('Y-m-d h:i:s'),
			'flag'=>'2'
		);
		
		$insert = $this->db->insert('bp_clans_members',$array);
                return $insert;
	}
        
    /**
     * Se cambia el flag de un clan_member para indicar que ha dejado el Clan.
     * @param type $set
     * @return type
     */
    public function leaveClan($options=array()){
        $default['idClan'] = '';
        $default['idUser'] = '';

        $set = $this->functions->merge($default,$options);
        $this->db->where('idClan',$set->idClan);
        $this->db->where('idUser',$set->idUser);
        $update = $this->db->update('bp_clans_members',array('flag'=>3));
        return $update;
    }
    /*
    *
    * Accept new member
    * Endpoint clans/joinClan
    *
    */
    public function joinMember($options=array()){
        $default['idClan'] = '';
        $default['idUser'] = '';

        $set = $this->functions->merge($default,$options);

        $this->db->where('idClan',$set->idClan);
        $this->db->where('idUser',$set->idUser);
        $this->db->where('flag', 0);
        $this->db->or_where('flag', 2);
        $update = $this->db->update('bp_clans_members',array('flag'=>1));
        return $update;
    }

    /*
    *
    * Accept new member
    * Endpoint clans/joinClan
    *
    */
    public function declineInvitation($options=array()){
        $default['idClan'] = '';
        $default['idUser'] = '';

        $set = $this->functions->merge($default,$options);

        $this->db->from('bp_clans_members');
        $this->db->where('idClan',$set->idClan);
        $this->db->where('idUser',$set->idUser);
        $update = $this->db->delete();
        return $update;
    }
    
    /*
    *
    * Add new Clan
    * Endpoint Clans/newClan
    *
    */
    public function newClan($options=array(),$members=array()){

        $insert =  $this->db->insert('bp_clans',$options);
        $webUrl = $this->config->item('webUrl');

        if($insert){
            $idClan = $this->db->insert_id();
            if(!empty($members)){
                $default['idClan'] = $idClan;
                $set = $this->functions->merge($default,$members);
                foreach($members as $member){
                    $set->idUser = $member;
                    $args = array(
                        'idUserReceived'=>$set->idUser,
                        'topic'=>'You\'ve been invited to join a clan ' .$options['clanName'],
                        'message'=>'<div class="mensaje">Clan <a target="_blank" href="'.$webUrl.'/clan_profile/show/'.$idClan.'"> '.$options['clanName'].'</a> 
                            has invited you to join their ranks and they are awaiting for your response</br>'
                    );
                    $this->functions->addNotification($args);
                    $this->addMember($set, 1);
                }
            }
            $def['idClan'] = $idClan;
            $def['idUser'] = $options['userClanLead'];
            $own['is_clanLead'] = true;
            $owner = $this->functions->merge($def,$own);
            $this->addMember($owner,1);
            $result = $idClan;
        }else{
            $result = false;
        }

        return $result;
    }

        /**
         * Add more members to a Clan.
         * @param type $idClan
         * @param type $members
         * @return type
         */
        public function inviteMembers($idClan, $members, $clanName/* = array() */) {
        if (!empty($members)) {
            $webUrl = $this->config->item('webUrl');
            $default['idClan'] = $idClan;
            $options['clanName'] = $clanName;
            $set = $this->functions->merge($default, $members);
            foreach ($members as $member) {
                $set->idUser = $member;
                $set->idClan = $idClan;
                $args = array(
                    'idUserReceived' => $set->idUser,
                    'topic' => 'You\'ve been invited to a new clan ' . $options['clanName'],
                    'message' => '<div class="mensaje">Clan <a target="_blank" href="' . $webUrl . '/clan_profile/show/' . $idClan . '"> ' . $options['clanName'] . '</a> has invited you to join their ranks and they are awaiting for your response.</br> 
                                            <a target="_blank" href="' . $webUrl . '/clan_profile/show/' . $idClan . '">Go there</a> </br>'
                );
                $this->functions->addNotification($args);
                $result = $this->suggestMember($set);
            }
        }
        return $result;
    }
    /*
	*
	* Set subleader
	*
	*
	*/
    public function change_subLead($array){

        if(!empty($array)){

            $this->db->update('bp_clans',array('userClanSubLead'=>$array['idUser']),array('idClan'=>$array['idClan']));

            $clans = $this->get_clans(array('idClan'=>$array['idClan']));

            if(!empty($clans)){
                if($clans->userClanSubLead == $array['idUser']){
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
    public function getImportantMembers($idClan){
        if(!empty($idClan)){
            $this->db->from('bp_clans c');
            $this->db->select('u.userId, u.nicename, u.email, u.full_name, u.level, (SELECT imagePath from bp_user_avatar where bp_user_avatar.idUser = u.userId and bp_user_avatar.size = 53) as imagePath');
            $this->db->join('bp_users u','u.userId = c.userClanLead','inner');
            $this->db->where('c.idClan',$idClan);
            $result['lider'] = $this->db->get()->result();
            $this->db->from('bp_clans c');
            $this->db->select('u.userId, u.nicename, u.email, u.full_name, u.level, (SELECT imagePath from bp_user_avatar where bp_user_avatar.idUser = u.userId and bp_user_avatar.size = 53) as imagePath');
            $this->db->join('bp_users u','u.userId = c.userClanSubLead','inner');
            $this->db->where('c.idClan',$idClan);
            $result['subleader'] = $this->db->get()->result();
            return $result;
        }else{
            return false;
        }
    }

    /*
    *
    * Get clan by Name
    * Endpoint Clans/getClanByName
    *
    */
    public function clansByName($clanName){
        if($clanName){
            $this->db->from('bp_clans');
            $this->db->select('idClan');
            $this->db->like('clanName',$this->db->escape_like_str($clanName));
            return $this->db->get()->row();
        }else{
            return false;
        }
    }

    /**
     * @param $idClan
     * @param $announcement
     * @param $title
     * @return bool
     */
    public function postAnnouncement($idClan, $announcement, $title){

        $data = array(
            'idClan'=> $idClan,
            'announcement' =>$announcement,
            'title' =>$title,
            'postDate' => date('Y-m-d h:i:s',time())
        );

        $this->db->insert('bp_clans_announcements',$data);
        $id = $this->db->insert_id();

        if($id != 0 && $id != null){
            return $this->getAnnouncementById($id);
        }else{
            return false;
        }

    }

    /**
     * @param $idClan
     * @return bool
     */
    public function getAllAnnouncements($idClan){

        $this->db->from('bp_clans_announcements');
        $this->db->where('idClan',$idClan);
        $this->db->order_by('postDate','DESC');


        $announcements = $this->db->get();

        if($announcements->num_rows() > 0){
            return $announcements->result();
        }else{
            return false;
        }
    }
    
    public function getClanLeader($idClan){
        $this->db->from('bp_clans');
        $this->db->select('userClanLead');
        $this->db->where('idClan', $idClan);
        $leader = $this->db->get();
        
         if($leader->num_rows() > 0){
            return $leader->row();
        }else{
            return false;
        }
        
        return false;
    }

    /**
     * @param $id
     * @return bool
     */
    public function getAnnouncementById($id){

        $this->db->from('bp_clans_announcements');
        $this->db->where('idAnnouncement',$id);
        $result = $this->db->get();

        if($result->num_rows() > 0){
            return $result->row();
        }else{
            return false;
        }
    }

    /**
     *
     * @param $id
     * @param $title
     * @param $text
     *
     * @return bool
     */
    public function updateAnnouncement($id,$title,$text){

        $this->db->where('idAnnouncement',$id);
        $data = array(
            'title'=>$title,
            'announcement'=>$text
        );

        $this->db->update('bp_clans_announcements',$data);

        return true;
    }

    /**
     * @param id
     * @return bool
     */
    public function removeAnnounce($id){

        $this->db->delete('bp_clans_announcements', array('idAnnouncement' => $id));
        return true;

    }


    /*
     * Edit and add Avatar
     */

    public function addAvatar($options = array()) {
        $insert = $this->db->insert('bp_clans_avatar', $options);
        return $insert;
    }

    /*
     * Delete avatar/s
     */

    public function deleteAvatar($options = array()) {
        $default['idAvatar'] = '';
        $default['idClan'] = '';
        $set = $this->functions->merge($default, $options);
        $this->db->from('bp_clans_avatar');
        if (!empty($set->idAvatar)) {
            $this->db->where('idAvatar', $set->idAvatar);
        }
        if (!empty($set->idClan)) {
            $this->db->where('idClan', $set->idClan);
        }
        $delete = $this->db->delete();
        return $delete;
    }

    /*
     * Get Avatars by teamId
     */

    public function getAvatars($options = array()) {
        $default['idAvatar'] = '';
        $default['idClan'] = '';
        $default['size'] = '';
        $set = $this->functions->merge($default, $options);
        $this->db->from('bp_clans_avatar');
        if (!empty($set->idAvatar)) {
            $this->db->where('idAvatar', $set->idAvatar);
        }
        if (!empty($set->idClan)) {
            $this->db->where('idClan', $set->idClan);
        }
        if (!empty($set->size)) {
            $this->db->where('size', $set->size);
        }
        $result = $this->db->get()->result();
        return $result;
    }
}