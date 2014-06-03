<?php

/**
 * <sumary>
 * Modelo del usuario, represanta todas las acciones del usuario.
 * </sumary>
 * */
class User_model extends CI_Model {

    function __construct() {
        // Call the Model constructor
        parent::__construct();
        $this->load->database();
    }

    /**
     * 	Consulta a la base de datos por la cantidad de usuarios
     * */
    public function user_count(){
        $this->db->from('bp_users');
        $this->db->select('count(userId) as count');
        return $this->db->get()->row();
    }

    /**
     * 	Consulta a la base de datos por el usuario con email y passowrd.
     * */
    public function login($email, $password) {
        $result = $this->db->query('call getUser(0,\'' . $email . '\',\'' . $password . '\');');
        return $result->row();
    }

    /**
     * 	Consulta a la base de datos por el usuario con email o nicename y confirm_email_key
     *  Devuelve 1 si el mail no esta confirmado
     * */
    public function emailIsConfirmed($email) {
        $query = $this->db->query('SELECT isConfirmEmail(\'' . $email . '\') as confirm;');
        $query = $query->row()->confirm;
        return $query;
    }

    /**
     * <sumary>
     * 	Confirma el mail del usuario
     * </sumary>
     * */
    public function confirmEmail($emailKey) {
        $query = $this->db->query('SELECT veryfiedEmail(\'' . $emailKey . '\') as isConfirm;');
        $query = $query->row()->isConfirm;
        return $query;
    }

    /*
     *
     * <sumary>
     * Modelo que trae usuarios mediante su nicename
     * </sumary>
     *
     */

    public function userByNicename($nicename) {
        $query = $this->db->query('call getUserByNiceName(\'' . $nicename . '\')');
        $result = $query->result();
        $query->next_result();
        $query->free_result();
        return $result;
    }

    /*
     * Get email by ID
     */
    public function getUserEmailById($userId){

        if(!empty($userId)){
            $this->db->select('email,nicename');
            $this->db->from('bp_users');
            $this->db->where('userId',$userId);
            $query = $this->db->get();
            if($query->num_rows() > 0){
                return $query->row();
            }
        }
        return false;
    }

    /**
     * <sumary>
     * //Vericar si el email de registro ya esta registro
     * </sumary>
     * */
    public function exist_email($email) {  // corroboramos que si exite el email esta registrado
        $query = $this->db->query('select checkUserByEmail(\'' . $email . '\',0) as uEmail');
        $uEmail = $query->row()->uEmail;

        if ($uEmail) {
            $existNoEmail = false;  // el email existe
        } else {
            $existNoEmail = true;  //el email no esta registrado por lo que podemos registrar al usuario
        }

        return $existNoEmail;
    }

    /**
     * <sumary>
     * //Vericar si el email de registro ya esta registrado en users que no son el mio
     * </sumary>
     * */
    public function exist_email_with_id($email, $userId) {  // corroboramos que si exite el email esta registrado
        $query = $this->db->query('select checkUserByEmail(\'' . $email . '\',' . $userId . ') as uEmail');
        $uEmail = $query->row()->uEmail;

        if ($uEmail) {
            return false;  // el email existe
        } else {
            return true;  //el email no esta registrado
        }
    }

    /**
     * <sumary>
     * //guardamos los datos de registro en la BD
     * </sumary>
     * */
    public function register_user($data) {
        $user = $this->db->query('SELECT ' . $data . ' as idUser');
        $idUser = $user->row()->idUser;

        if ($idUser) {
            return $idUser;
        } else {
            return false;
        }
    }

    public function update_user_meta($meta) {
        $this->db->where('idUser', $meta['idUser']);
        $this->db->where('meta_key', $meta['meta_key']);
        $this->db->delete('bp_usermeta');

        $this->db->insert('bp_usermeta', $meta);

    }

    public function get_user_meta($userId) {
        $this->db->select('meta_key, meta_value');
        $meta = $this->db->get_where('bp_usermeta', array('idUser' => $userId));

        if($meta->num_rows() > 0){
            return $meta->result();
        }else{
            return false;
        }
    }

    /**
     * <sumary>
     * //Trae todos los datos del usuarios
     * </sumary>
     * */
    public function get_user_data($data) {
        if (!is_array($data)) {
            $query = $this->db->query('call getUser(\'' . $data . '\',\'\',\'\');');
        } else {
            $prepareQuery = 'call getUserBigWhere(';
            //Todos los where van contactenados a esta variable
            $whereClause = '';
            //Todos los join van concatenados a esta variable.
            $joinClause = '';

            if (isset($data['suggest']) and $data['suggest'] == 'false') {
                foreach ($data as $k => $v) {
                    if ($k != 'suggest' && $k != 'membersAdded')
                        $whereClause.= ' AND ' . $k . ' = "' . $v . '"';
                }
            }

            if (isset($data['suggest']) and $data['suggest'] == 'true') {
                if (isset($data['idClan'])) {
                    $whereClause.= ' AND m.idClan = "' . $data['idClan'] . '"';
                    $whereClause.= ' AND m.flag = "1"';
                    $joinClause.=' INNER JOIN  bp_clans_members m ON m.idUser = u.userId ';
                    $whereClause.= ' AND u.userId != "' . $data['myUser'] . '"';
                    $whereClause.= ' AND u.nicename LIKE "%' . $data['nicename'] . '%"';
                }elseif (isset($data['idTeam'])) {
                    $whereClause.= ' AND m.idTeam = "' . $data['idTeam'] . '"';
                    $whereClause.= ' AND m.flag = "1"';
                    $joinClause.=' INNER JOIN  bp_teams_members m ON m.idUser = u.userId ';
                    $whereClause.= ' AND u.userId != "' . $data['myUser'] . '"';
                    $whereClause.= ' AND u.nicename LIKE "%' . $data['nicename'] . '%"';
                } else {
                    $whereClause.= ' AND u.userId != "' . $data['myUser'] . '"';
                    $whereClause.= ' AND u.nicename LIKE "%' . $data['nicename'] . '%"';
                }
            }

            if (isset($data['membersAdded']) and !empty($data['membersAdded'])) {
                foreach ($data['membersAdded'] as $member) {
                    $whereClause.= ' AND u.userId != "' . $member . '"';
                }
            }

            if (isset($data['edit'])) {
                $whereClause.= ' AND u.userId != "' . $data['userId'] . '"';
            }

            $prepareQuery.= '\'' . $whereClause . '\',\'' . $joinClause . '\')';
            $query = $this->db->query($prepareQuery);
        }

        if ($query->num_rows() > 0) {
            if ($query->num_rows() > 1) {
                $userData = $query->result();
            } else {
                $userData = $query->row();
            }
        } else {
            $userData = false;  //el usuario no existe por lo que devuelvo false
        }
        $query->next_result();
        $query->free_result();
        return $userData;
    }


    /*
     *
     * <sumary>
     * Edita el profile del usuario
     * </sumary>
     *
     */

    public function edit_user($options, $idUser) {
        foreach ($options as $param) {
            $param = $this->db->escape($param);
        }
        $this->db->where('userId', $idUser);
        $this->db->where('confirm_email_key', '');
        return $this->db->update('bp_users', $options);
    }

    //Obtengo la cantidad actual de palta que posee un usuario
    //TODO: Revisar si esta esta usando, caso contrario eliminar.
    public function get_game_cash($userId) {
        $this->db->select('cash');
        $this->db->order_by("date", "desc");
        $limit = 1;
        $query = $this->db->get_where('bp_historyCash', array('userId' => $userId), $limit);
        if ($query->num_rows() > 0) {
            $gameCash = $query->result();  // guardo los resultados
        } else {
            $gameCash = false;  //el usuario no existe por lo que devuelvo false
        }

        return $gameCash;
    }

    /*
     * //devuelve la lista de amigos de un usuario
     */

    public function get_userFriends($options = array()) {

        $default['userId'] = '';

        $set = $this->functions->merge($default, $options);

        $this->db->from('bp_friends');
        $this->db->join('bp_users', 'bp_friends.friendId = bp_users.userId');

        //Filtramos amigos por usuario
        if (isset($set->userId) && !empty($set->userId)) {
            $this->db->where('bp_friends.userId_User', $set->userId);
        }
        //Filtramos amigos por amigo para ver amigos de mi amigo
        if (isset($set->friendId) && !empty($set->friendId)) {
            $this->db->where('bp_friends.friendId', $set->friendId);
        }

        //Filtramos por amigos o por request frienshipis para tomar las peticiones o los que ya son amigos.

        if (isset($set->flag)) {
            $this->db->where('flag', $set->flag);
        }

        $this->db->order_by("nicename", "asc");
        $query = $this->db->get();

        if ($query->num_rows() > 0) {
            $userFriends = $query->result();  // guardo los resultados
        } else {
            $userFriends = false;  //el usuario no existe por lo que devuelvo false o no tiene amigos
        }

        return $userFriends;
//    return $this->db->last_query();
    }

    public function get_userFriendsWithAvatar($userId = 0, $avatarSize = 150,$flag='2',$limit='') {

        $query = '
SELECT bp_users.userId as idFriend, bp_users.nicename as friendNicename, (SELECT imagePath from bp_user_avatar where bp_user_avatar.idUser = idFriend and bp_user_avatar.size = '.$avatarSize.') as imagePath
FROM (`bp_friends`) JOIN `bp_users` ON `bp_friends`.`friendId` = `bp_users`.`userId`
WHERE `bp_friends`.`userId_User` = '.$userId.' AND `flag` = '.$flag.' ORDER BY friendNicename ASC
';

        if(!empty($limit)){
            $query .= ' LIMIT '.$limit;
        }
        $friends = $this->db->query($query);
        if($friends->num_rows() > 0){
            return $friends->result();
        }else{
            return false;
        }

    }

    public function get_chatUserFriends($userId = 0) {

        $this->db->select('bp_friends.friendId, bp_users.nicename');
        $this->db->join('bp_users', 'bp_friends.friendId = bp_users.userId');
        $this->db->where('flag', 2);
        $this->db->where('userId_encrypted', $userId);

        $resutlt = $this->db->get('bp_friends');
        return $resutlt->result();
    }

    /*
     *  Devuelve una lista de todos los usuarios que han solicitado amistad
     *
     */

    function get_friendship_requests($data) {
        if (!empty($data) && !empty($data['userId'])) {
            $this->db->from('bp_friends');
            $this->db->join('bp_users', 'bp_friends.userId_User = bp_users.userId');
            $this->db->where('friendId', $data['userId']);
            $this->db->where('flag', '1');
            $result = $this->db->get()->result();
            return $result;
        }
    }

    /*
     *  Devuelve una lista de todos los usuarios que no son amigos, incluidos los que no han aceptado la solicitud
     *
     */

    function get_suggestFriends($options = array()) {
        if (!empty($options)) {
            $userId = $options['userId'];
            $nicename = $options['nicename'];
            $userNotFriends = $this->db->query('SELECT userId, nicename FROM bp_users WHERE nicename LIKE "%' . $nicename . '%" AND userId NOT IN (SELECT friendId FROM bp_friends WHERE userId_User = ' . $userId . ') AND userId != ' . $userId . '');
        }
        return $userNotFriends->result();
    }

    /**
     * Devuelve una lista de todos los usuarios que no pertenecen al Clan, incluido el clan_leader.
     * @param type $options
     * @return type
     */
    function get_suggestMembersInviteClan($options = array()){
        if(!empty($options)){
            $userId = $options['userId'];
            $nicename = $options['nicename'];
            $idClan = $options['idClan'];
            $userSuggested = $this->db->query('SELECT userId, full_name, nicename FROM bp_users WHERE nicename LIKE "%'.$nicename.'%" AND userId NOT IN (SELECT idUser FROM bp_clans_members WHERE idClan = '.$idClan.' AND flag != 3) AND userId !='.$userId.'');
        }
        return $userSuggested->result();
    }

    /**
     * Devuelve una lista de todos los usuarios que no pertenecen al Clan, incluido el clan_leader.
     * @param type $options
     * @return type
     */
    function get_suggestMembersInviteTeam($options = array()){
        if(!empty($options)){
            $userId = $options['userId'];
            $nicename = $options['nicename'];
            $idTeam = $options['idTeam'];
            $userSuggested = $this->db->query('SELECT userId, full_name, nicename FROM bp_users WHERE nicename LIKE "%'.$nicename.'%" AND userId NOT IN (SELECT idUser FROM bp_teams_members WHERE idTeam = '.$idTeam.' AND flag != 3) AND userId !='.$userId.'');
        }
        return $userSuggested->result();
    }
    /*
     * //agregar amistad entre usuarios
     *
     *
     *
     */

    function frienshipSendRequest($userId, $friendId, $date) {
        //verifico primero que ambos usuario existan
        $query = $this->db->get_where('bp_users', array('userId' => $userId));

        if ($query->num_rows() == 0) {
            //no existe el usuario
            return false;
        }

        // Request friend exist?
        $query = $this->db->get_where('bp_users', array('userId' => $friendId));
        if ($query->num_rows() == 0) {
            //no existe el futuro usuario amigo
            return false;
        }

        //verifico primero que no exista ya la amistad o que no exista el request
        $query = $this->db->get_where('bp_friends', array('userId_User' => $userId, 'friendId' => $friendId));

        if ($query->num_rows() == 0) {
            $query = $this->db->get_where('bp_friends', array('userId_User' => $friendId, 'friendId' => $userId));
            if ($query->num_rows() == 0) {
                //no existe amistad por lo tanto la creo
                $data = array(
                    'userId_User' => $userId,
                    'friendId' => $friendId,
                    'date' => $date,
                    'flag' => 1,
                    'userId_encrypted' => md5($userId)
                );

                $userInsert = $this->db->insert('bp_friends', $data);

                return $userInsert;
            } else {
                return false;
            }
        } else {
            //la amistad ya existe
            return false;
        }
    }

    /*
     *
     * Acept Frienships
     *
     */

    public function frienship_accepted($options = array()) {
        $default['userId'] = '';
        $default['friendId'] = '';
        $set = $this->functions->merge($default, $options);

        $friendsOrRquest = $this->get_userFriends(array('userId' => $set->userId, 'friendId' => $set->friendId, 'flag' => 1, 'userId_encrypted' => md5($set->userId)));

        // Si existe el request devuelvo false
        if (!empty($friendsOrRquest)) {
            $this->db->where('idFrienship', $friendsOrRquest[0]->idFrienship);
            $update = $this->db->update('bp_friends', array('flag' => 2));
            $this->addAcceptFrienship($set->userId, $set->friendId);
            return $update;
        } else {
            //Si no existe la amistad o no existe el request, acepto la amistad
            return false;
        }
    }

    /**
     * Elimina de la base de datos la relación entre dos usuarios.
     * @param type $userId
     * @param type $frienId
     */
    public function removeFrienship($userId, $frienId){
        $options = array($userId, $frienId);
        $default['userId'] = '';
        $default['friendId'] = '';
        $set = $this->function->merge($default, $options);

        $removed = $this->db->delete('bp_friends', array('userId_User' => $set->userId, 'friendId' => $set->friendId));

        if(!empty($removed)){
            return $removed;
        } else {
            return false;
        }
    }

    /*
     *
     * Insert aceptance frienship
     *
     */

    private function addAcceptFrienship($userId, $friendId) {
        $insert = $this->db->insert('bp_friends', array('userId_User' => $friendId, 'friendId' => $userId, 'date' => date('Y-m-d h:i:s', time()), 'flag' => 2, 'userId_encrypted' => md5($friendId)));
        return $insert;
    }

    //eliminar amistad entre usuario
    public function user_remove_friend($userId, $friendId) {
        //primero verifico que exista la amistad
        $query = $this->db->get_where('bp_friends', array('userId_User' => $userId, 'friendId' => $friendId));
        if ($query->num_rows() == 0) {
            //no existe amistad
            return false;
        }

        $this->db->delete('bp_friends', array('userId_User' => $userId, 'friendId' => $friendId));
        $this->db->delete('bp_friends', array('userId_User' => $friendId, 'friendId' => $userId));

        $exito = 'The friendship was remove successfully';
        return $exito;
    }

    /*
     *
     * Cambio pass
     *
     *
     */

    public function changeUserPass($data) {
        if ($data) {
            $currentPass = md5($data['currentPass']);
            $newPass = md5($data['newPass']);
            $this->db->from('bp_users');
            $this->db->where('userId', $data['userId']);
            $this->db->where('password', $currentPass);
            $exist = $this->db->get()->result();
            if (sizeof($exist) == 1) {
                $auxData = array('password' => $newPass);
                $this->db->where('userId', $data['userId']);
                $this->db->update('bp_users', $auxData);
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    //guarda el password temoporario
    public function save_temp_pass($email, $password) {
        $password = md5($password);
        $data = array(
            'tempPass' => $password,
        );

        $where = "email = '" . $email . "'";

        $query = $this->db->update('bp_users', $data, $where);

        if ($query) {
            return true;
        } else {
            return false;
        }
    }

    //traigo el tempPass
    public function compare_temp_pass($email) {
        $this->db->select('tempPass');

        $query = $this->db->get_where('bp_users', array('email' => $email));

        if ($query) {
            return $query->result();
        } else {
            return false;
        }
    }

    //guardo el nuevo password
    public function save_new_pass($email, $password) {
        $password = md5($password);
        $data = array(
            'password' => $password,
        );

        $where = "email = '" . $email . "'";

        $query = $this->db->update('bp_users', $data, $where);

        if ($query) {
            return true;
        } else {
            return false;
        }
    }

    //vacio celda tempPass
    public function empty_tempPass($email) {
        $data = array(
            'tempPass' => ""
        );

        $where = "email = '" . $email . "'";

        $query = $this->db->update('bp_users', $data, $where);

        if ($query) {
            return true;
        } else {
            return false;
        }
    }

    /*
     * Get users by email
     */

    public function getUserByEmail($options = array()) {
        $default['email'] = '';
        $set = $this->functions->merge($default, $options);
        if (!empty($set->email)) {
            $this->db->from('bp_users');
            $this->db->where('email', $set->email);
            $result = $this->db->get()->row();
            return $result;
        }

        return false;
    }

    /*
     * Get Avatars by userId
     */

    public function getAvatars($options = array()) {
        $default['idAvatar'] = '';
        $default['idUser'] = '';
        $default['size'] = '';
        $set = $this->functions->merge($default, $options);
        $this->db->from('bp_user_avatar');
        if (!empty($set->idAvatar)) {
            $this->db->where('idAvatar', $set->idAvatar);
        }
        if (!empty($set->idUser)) {
            $this->db->where('idUser', $set->idUser);
        }
        if (!empty($set->size)) {
            $this->db->where('size', $set->size);
        }
        $result = $this->db->get()->result();
        return $result;
    }

    /*
     * Edit and add Avatar
     */

    public function addAvatar($options = array()) {
        /* @TODO: Aaca tenemos que hacerlo bien, esto es una mierda */
        $insert = $this->db->insert('bp_user_avatar', $options);
        return $insert;
    }

    /*
     * Delete avatar/s
     */

    public function deleteAvatar($options = array()) {
        $default['idAvatar'] = '';
        $default['idUser'] = '';
        $set = $this->functions->merge($default, $options);
        $this->db->from('bp_user_avatar');
        if (!empty($set->idAvatar)) {
            $this->db->where('idAvatar', $set->idAvatar);
        }
        if (!empty($set->idUser)) {
            $this->db->where('idUser', $set->idUser);
        }
        $delete = $this->db->delete();
        return $delete;
    }

    /*
     * Report User
     */

    public function reportUser($data) {
        if (!empty($data)) {
            $insert = $this->db->insert('bp_report', $data);
            return $insert;
        } else {
            return false;
        }
    }

    public function getReportTypes(){

        $types = $this->db->get('bp_report_type');

        if($types->num_rows() > 0 ){
            return $types->result();
        }else{
            return false;
        }
    }

    public function sanitize($data) {
        if (is_array($data)) {
            foreach ($data as $param) {
                $param = $this->db->escape_str($param);
            }
        } else {
            $data = $this->db->escape_str($data);
        }
        return $data;
    }

    /**
     *
     * Get user from database by his twitter_id
     *
     * @param int $twitter_id
     */
    public function userByTwitterId($twitter_id = 0){
        if($twitter_id){
            $this->db->select('userId, email, password, full_name, nicename, status, birthday, countryName, create_date');
            $this->db->from('bp_users');
            $this->db->join('bp_country', 'countryId = idCountry');
            $this->db->where('twitter_id', $twitter_id);
            $result = $this->db->get();

            if($result->num_rows() > 0){
                return $result->row();
            }else{
                return false;
            }
        }else{
            return false;
        }
    }

    public function userByFbId($id=0){

        $this->db->from('bp_users');
        $this->db->where('facebook_id',$id);
        $user = $this->db->get();

        if($user->num_rows() > 0){
            return $user->result();
        }else{
            return false;
        }

    }

    public function getNowPlaying($userId = 0){

        if($userId){
            $this->db->select('g.name as game, t.tournamentName as tournament, m.nameMap as map');
            $this->db->from('bp_tournamentsplayers tp');
            $this->db->join('bp_tournaments t', 'tp.torneId = t.tournamentId');
            $this->db->join('bp_games g', 't.gameId = g.gameId');
            $this->db->join('bp_game_maps m', 't.map = m.idMap');
            $this->db->where('tp.userId',$userId);
            $this->db->where('t.status',3);
            $this->db->where('tp.invited',0);
            $this->db->where('tp.active',1);

            $result = $this->db->get();

            if($result->num_rows() > 0){

                return $result->result();

            }else{

                return false;

            }
        }else{
            return false;
        }
    }

    /**
     * Get all users with some stats
     */
    public function getUsersList($offset){
        $query = "call rankingUsers($offset)";
        $users = $this->db->query($query);

        if($users->num_rows() > 0 ){
            return $users->result();
        }else{
            return false;
        }
    }

    /**
     * @param $userId
     * @param $myId
     * @return bool
     */
    public function getUserPreview($userId, $myId){
        /*
        SELECT userId as iduserPreview, nicename, countryName,
        (SELECT imagePath from bp_user_avatar where bp_user_avatar.idUser = iduserPreview and bp_user_avatar.size = 150) as imagePath, birthday
        FROM bp_users u join bp_country c on u.countryId = c.idCountry where userId = 62
         * */
        $this->db->select('userId as iduserPreview, nicename, countryName as country, (SELECT imagePath from bp_user_avatar where bp_user_avatar.idUser = iduserPreview and bp_user_avatar.size = 150) as avatarPath, birthday, (SELECT flag from bp_friends where bp_friends.userId_User = '.$myId.' and bp_friends.friendId = '.$userId.') as isFriend');
        $this->db->from('bp_users u');
        $this->db->join('bp_country c','u.countryId = c.idCountry');
        $this->db->where('userId', $userId);

        $preview = $this->db->get();

        if($preview->num_rows() > 0){
            return $preview->result();
        }else{
            return false;
        }

    }

    /**
     * @return mixed
     */
    public function getLastPage(){
        return $this->db->query("select count(userId) as userCount from bp_users where confirm_email_key = ''")->result();
    }


    /**
     *
     * Agregame visitas
     * End Point: user/addUserProfileView
     * @params: userId
     *
     */
    public function add_user_profile_view($myProfile,$idSession,$profileViews){
        //Si es mi perfil, solo devuelvo la cantidad de visitas
        if($myProfile){
            return true;
        }else{
            if(!empty($profileViews)&&!empty($idSession)){
                //No es mi perfil, pregunto si esta persona ya lo visito
                $this->db->from('bp_profile_views');
                $this->db->where('idSession',$idSession);
                $alreadyView = $this->db->get()->row();
                if(!$alreadyView){
                    //Si no lo visito, sumo la visita
                    $data = array('idSession'=>$idSession,'profile_views'=>$profileViews);
                    $insert = $this->db->insert('bp_profile_views',$data);
                    if($insert){
                        return true;
                    }else{
                        return false;
                    }
                }
                return true;
            }else{
                return false;
            }
        }
    }

    public function get_profile_views($profileViews){
        $this->db->from('bp_profile_views');
        $this->db->select('count(profile_views) as Views');
        $this->db->where('profile_views',$profileViews);
        return $this->db->get()->row();
    }

    /**
     * @param null
     * @return object
     */
    public function getNewGlobalAnnounce(){
        $this->db->from('bp_global_announce');
        $this->db->where('isActive != ("0000-00-00 00:00:00")');
        $this->db->order_by('idAnnounce','DESC');
        return $this->db->get()->row();
    }

    /*
     * Get Steam id of user
     */
    public function getSteamId($options=array()){
        $this->db->from('bp_users_steam');
        if(isset($options['userId']) and !empty($options['userId'])){
            $this->db->where('userId',$options['userId']);
        }else{
            return false;
        }
        $query = $this->db->get();
        if($query->num_rows() > 0){
            return $query->row();
        }
        return false;
    }
    /**
     * @param int $userId
     * @param string $device
     * End Point: user/getAutoLogin
     */
    public function autologinData($userId = 0,$device = ''){

        if($userId != 0 and $device != ''){

//SELECT userId, email, full_name, nicename, status, birthday FROM battlepro_api.bp_user_device b join bp_users u on b.idUser = u.userId where userId = 28 and macAddress = 'BC:5F:F4:7C:30:BF';
//            $this->db->select('userId, email, full_name, nicename, status, birthday');
            $this->db->from('bp_users u');
            $this->db->join('bp_user_device d','d.idUser = u.userId');
            $this->db->where('u.userId',$userId);
            $this->db->where('d.macAddress',$device);


            $devices = $this->db->get();

            if($devices->num_rows > 0){
                return $devices->row();
            }else{
                return false;
            }
        }else{
            return false;
        }

    }

    /**
     * @param $userId
     * @param $token
     */
    public function saveToken($userId,$token){

        $update = $this->getToken($userId);
        //preguntamos si hay que actualizar o insertar
        if($update){
            //rutina de actualizacion de token
            $data = array(
                'meta_value' => $token
            );
            $this->db->where('meta_key','auth_token');
            $this->db->where('idUser',$userId);
            $this->db->update('bp_usermeta',$data);

            return true;

        }else{
            //rutina de inserción de token
            $data= array(
                'idUser' => $userId,
                'meta_key'=>'auth_token',
                'meta_value' => $token
            );
            $this->db->insert('bp_usermeta',$data);
            return $this->db->insert_id();
        }

    }

    public function loginByToken($token = ''){

        if(!empty($token) && $token){

            $this->db->from('bp_users u');
            $this->db->join('bp_usermeta m','u.userId = m.idUser');
            $this->db->where('m.meta_key','auth_token');
            $this->db->where('m.meta_value',$token);

            $user = $this->db->get();

            if($user->num_rows > 0){
                return $user->row();
            }else{
                return false;
            }
        }else{
            return false;
        }
    }

    /**
     * Obtenemos el token
     * @param $userId, $key
     * @return: object / boolean
     */
    private function getToken($userId,$key="auth_token"){

        $this->db->from('bp_usermeta');
        $this->db->where('meta_key',$key);
        $this->db->where('idUser',$userId);
        $token = $this->db->get();

        if($token->num_rows > 0){
            return $token->result();
        }else{
            return false;
        }
    }

}

?>
