<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 *  Teams Controller
 *
 * @package		CodeIgniter
 * @subpackage	Rest Server
 * @category	Controller
 */

require APPPATH.'/libraries/REST_Controller.php';

/**
 * @property mixed teams_model
 */
class Teams extends REST_Controller
{

    public function __construct()
    {
        parent:: __construct();
        $this->config->load('email');
        $this->load->model('teams_model');
        $this->load->model('inbox_model');
        $this->load->model('user_model');
        $this->load->model('sanitize_model');

    }

    /**
     * Lista de teams según el id del usuario.
     * Endpoint teams/getTeams
     * @param teamLead
     * @param userMemberId
     * @param flag
     */
    public function getTeams_get()
    {
        $options = array();

        $teamLead = $this->get('teamLead');
        $count = $this->get('count');
        $offset = $this->get('offset');
        $limit = $this->get('limit');
        $userMemberId = $this->get('userMemberId');
        $flag = $this->get('flag');

        if($teamLead)$options['teamLead']=$teamLead;

        if($userMemberId){
            $memebersTeam = $this->teams_model->get_members_teams(array('idUser'=>$userMemberId,'flag'=>$flag));
            if($memebersTeam){
                foreach($memebersTeam as $member){
                    $team[] = $this->teams_model->get_teams(array('idTeam'=>$member->idTeam));
                }
            }
        }else{
            $idTeam = $this->get('idTeam');
            if($idTeam)$options['idTeam']=$idTeam;
            if($limit)$options['limit']=$limit;
            $options['offset']=$offset;
            $options['count']=$count;
            $team = $this->teams_model->get_teams($options);
        }

        if($team)
        {
            $response = array('response' => true,'result' => $team);
            $this->response($response, 200);
        }
        else
        {
            $response = array('response' => false,'result' => 'The team not exist');
            $this->response($response, 404);
        }
    }

    /**
     * Devuelve los miembros de un Team.
     * Endpoint teams/requestJoinTeam
     * @param idTeam
     * @param flag
     */
    public function getMembers_get(){
        $idTeam = $this->get('idTeam');
        $flag = $this->get('flag');
        $members = $this->teams_model->get_members_teams(array('idTeam'=>$idTeam,'flag'=>$flag));
        if($members)
        {
            $response = array('response' => true,'result' => $members);
            $this->response($response, 200);
        }
        else
        {
            $response = array('response' => false,'result' => 'The Team has no members');
            $this->response($response, 404);
        }
    }

    /**
     * Edit Team Profile
     * @param idTeam
     * @param teamDescription
     */
    public function editProfile_get(){
        $idTeam = $this->get('idTeam');
        $teamDescription = $this->get('teamDescription');

        if(!empty($idTeam)and !empty($teamDescription)){
            $result = $this->teams_model->editTeamProfile($idTeam,$this->sanitize_model->sanitize($teamDescription));
            if($result){
                $response = array('response' => true);
                $this->response($response, 200);
            }else{
                $response = array('response' => false);
                $this->response($response, 404);
            }
        }else{
            $response = array('response' => false,'result'=>'You must send data');
            $this->response($response, 404);
        }
    }

    /**
     * Invita a nuevos miembros al Team, desde un Team ya existente.
     */
    public function inviteMembers_get(){
        $members = $this->get('members');
        $idTeam = $this->get('idTeam');
        $teamName = $this->get('teamName');
        $request = $this->teams_model->inviteMembers($idTeam,$members,$teamName);

        if($request){
            $response = array('response' => true,'result'=>$request);
            $this->response($response, 200);
        }else{
            $response = array('response' => false);
            $this->response($response, 404);
        }
    }

    public function getLeaderTeam_get(){
        $idTeam = $this->get('idTeam');
        $leadRequest = $this->teams_model->getTeamLeader($idTeam);
        $request = $leadRequest;

        if($request){
            $response = array('response' => true,'result'=>$request);
            $this->response($response, 200);
        }else{
            $response = array('response' => false);
            $this->response($response, 404);
        }
    }

    public function leaveTeam_get(){
        $idTeam = $this->get('idTeam');
        $idUser = $this->get('idUser');
        $request = $this->teams_model->leaveTeam(array('idUser' => $idUser, 'idTeam' => $idTeam));
        echo $request;
    }

    /**
     * Upload Avatar
     * @param file_data
     */
    public function editAvatar_post() {

        $file_data = $this->post('file_data');
        if (empty($file_data)) {
            $response = array('response' => false, 'result' => 'The file data is empty');
            $this->response($response, 200);
        }

        $avatar_img = $this->post('avatar_img');
        if (empty($avatar_img)) {
            $response = array('response' => false, 'result' => 'The avatar image is empty');
            $this->response($response, 200);
        }

        $idTeam = $this->post('idTeam');
        if (empty($idTeam)) {
            $response = array('response' => false, 'result' => 'The team id is empty');
            $this->response($response, 200);
        }

        $avatar_str = base64_decode($avatar_img);

        $path = $_SERVER['DOCUMENT_ROOT'].'/media/teamfiles/' . $idTeam . '/';

        if (!file_exists($path)) {
            mkdir($path);
        }

        $path = $_SERVER['DOCUMENT_ROOT'].'/media/teamfiles/' . $idTeam . '/' . $file_data['file_name'];
        $avatar_file = fopen($path, "w+");
        fwrite($avatar_file, $avatar_str);
        fclose($avatar_file);


        if (file_exists($path)) {

            //Eliminaos todos los avatars de este usuario que existan
            $this->teams_model->deleteAvatar(array('idTeam' => $idTeam));

            $args = array(
                'idTeam' => $idTeam,
                'imagePath' => 'media/teamfiles/' . $idTeam . '/' . $file_data['file_name'],
                'size' => '150'
            );

            //Insertamos en la base de datos el avatar
            $insertAvatar = $this->teams_model->addAvatar($args);
            //Si la imagen se inserta en la base de datos, devuelves todo joya men
            if ($insertAvatar) {

                $options['full_path'] = $path;
                $options['width'] = 53;
                $options['height'] = 53;
                $options['thumb'] = TRUE;

                if ($this->functions->resizeAvatars($options)) {

                    $args = array(
                        'idTeam' => $idTeam,
                        'imagePath' => 'media/teamfiles/' . $idTeam . '/' . $file_data['raw_name'] . '_thumb.' . $this->functions->getTypeFile($file_data['file_type']),
                        'size' => '53'
                    );
                    //Insertamos en la base de datos el avatar
                    $insertAvatar = $this->teams_model->addAvatar($args);
                }

                $avatars = $this->teams_model->getAvatars(array('idTeam' => $idTeam));
                if (!empty($avatars)) {
                    $response = array('response' => true, 'result' => $avatars);
                    $this->response($response, 200);
                } else {
                    $response = array('response' => false, 'result' => 'The avatar doesnt exist');
                    $this->response($response, 404);
                }
            } else {
                $response = array('response' => false, 'result' => 'Problem with insert avatar');
                $this->response($response, 404);
            }
        } else {
            $response = array('response' => false, 'result' => 'The folder for this user doesnt exist');
            $this->response($response, 404);
        }
    }

    /*
     * Get avatar of a team
     */
    public function getAvatar_get(){
        $idTeam = $this->get('idTeam');
        $avatars = $this->teams_model->getAvatars(array('idTeam'=>$idTeam));
        if($avatars){
            $this->response(array('response'=>true,'result'=>$avatars), 200);
        }
        $this->response(array('response'=>false,'result'=>'This team havent any avatar'), 200);
    }

    /**
     * Pedido de un usuario para ingresar a un Team.
     * Join member to team
     * Endpoint teams/requestJoinTeam
     * @param idUser
     * @param idTeam
     */
    public function requestJoinTeam_get(){
        $idUser = $this->get('idUser');
        $idTeam = $this->get('idTeam');

        $request = $this->teams_model->requestJoinTeam(array('idUser'=>$idUser,'idTeam'=>$idTeam ));

        if($request){
            $webUrl = $this->config->item('webUrl');
            $user = $this->user_model->get_user_data($idUser);

            $leadRequest = $this->teams_model->get_teams(array('idTeam'=>$idTeam));
            $teamLeadUser = $leadRequest->userTeamLead;

            $notLead['idUserReceived'] = $teamLeadUser;
            $notLead['topic'] = $user->nicename.' wants to join your Team';
            $notLead['email_subject'] = "New Team Member Request";
            $notLead['message'] = '<a target="_blank" href="'.$webUrl.'/profile/show/'.$user->nicename.'">'.$user->nicename.'</a> has sent a request to join your Team, and it\'s waiting for your approval. <a target="_blank" href="'.$webUrl.'/team_profile/show/'.$idTeam.'">Go there</a>';
            $notLead['sendEmail'] = true;
            $this->functions->addNotification($notLead);

            $response = array('response' => true);
            $this->response($response, 200);
        }else{
            $response = array('response' => false);
            $this->response($response, 404);
        }
    }

    /**
     * Un usuario se une a un Team.
     * Join member to team
     * Endpoint teams/joinTeam
     * @param idUser
     * @param idTeam
     */
    public function joinTeam_get(){
        $idUser = $this->get('idUser');
        $idTeam = $this->get('idTeam');
        $idNoti = $this->get('idNotif');
        $hola = $this->inbox_model->update_notif($idNoti);

        $response = array('response' => $hola);
        $this->response($response, 200);

        $request = $this->teams_model->joinMember(array('idUser'=>$idUser,'idTeam'=>$idTeam ));

        if($request){
            $response = array('response' => true);
            $this->response($response, 200);
        }else{
            $response = array('response' => false);
            $this->response($response, 404);
        }
    }

    /**
     * Crea un Team nuevo.
     * Add new Team
     * Endpoint Teams/newTeam
     * @param teamName
     * @param teamDescription
     * @param members
     * @param userTeamLead
     */
    public function newTeam_get(){
        $teamName = $this->get('teamName',TRUE);
        $teamDescription = $this->get('teamDescription',TRUE);
        $members = $this->get('members');
        $userTeamLead = $this->get('userTeamLead');

        $args = array(
            'teamName'=>$this->sanitize_model->sanitize($teamName),
            'teamDescription'=>$this->sanitize_model->sanitize($teamDescription),
            'userTeamLead'=>$userTeamLead,
            'createDate'=>date('Y-m-d h:i:s',time()),
            'avatarPath'=>'/media/img/teamBig.png'
        );

        $request = $this->teams_model->newTeam($args,$members);

        if($request){
            $response = array('response' => true,'result'=>$request);
            $this->response($response, 200);
        }else{
            $response = array('response' => false);
            $this->response($response, 404);
        }
        return $request;
    }

    /**
     * Accept Member Request
     * @param idUser
     * @param idTeam
     */
    public function acceptRequest_get(){


        $idUser = $this->get('idUser');
        $idTeam = $this->get('idTeam');

        $request = $this->teams_model->joinMember(array('idUser'=>$idUser,'idTeam'=>$idTeam ));

        if($request){
            $webUrl = $this->config->item('webUrl');
            $team = $this->teams_model->get_teams(array('idTeam'=>$idTeam));
            $not['idUserReceived'] = $idUser;
            $not['topic'] = 'Your request to join '.$team->teamName.' has been Approved';
            $not['email_subject'] = 'Your request to join '.$team->teamName.' has been Approved';
            $not['message'] = 'Congratulations! Your request to join team <a target="_blank" href="'.$webUrl.'/team_profile/show/'.$team.'">'.$team->teamName.'</a> has been approved! You can now play team matches and chat with them at all times.';
            $not['sendEmail'] = true;
            $this->functions->addNotification($not);

            $response = array('response' => true);
            $this->response($response, 200);
        }else{
            $response = array('response' => false);
            $this->response($response, 404);
        }
    }

    /**
     *  Deny Member Request
     * @param idUser
     * @param idTeam
     *
     */
    public function denyRequest_get(){

        $idUser = $this->get('idUser');
        $idTeam = $this->get('idTeam');

        $request = $this->teams_model->deleteRequest(array('idUser'=>$idUser,'idTeam'=>$idTeam ));

        if($request){
            $webUrl = $this->config->item('webUrl');
            $team = $this->teams_model->get_teams(array('idTeam'=>$idTeam));

            $not['idUserReceived'] = $idUser;
            $not['topic'] = 'Your request to join '.$team->teamName.' has been Declined';
            $not['email_subject'] = 'Your request to join '.$team->teamName.' has been Declined';
            $not['message'] = 'We\'re sorry but your request to join team <a target="_blank" href="'.$webUrl.'/team_profile/show/'.$idTeam.'">'.$team->teamName.'</a> has been declined.';
            $not['sendEmail'] = true;
            $this->functions->addNotification($not);

            $response = array('response' => true);
            $this->response($response, 200);
        }else{
            $response = array('response' => false);
            $this->response($response, 404);
        }
    }

    /**
     * Decline a invitation to join a Team.
     * @param idUser
     * @param idTeam
     */
    public function declineRequest_get(){


        $idUser = $this->get('idUser');
        $idTeam = $this->get('idTeam');

        $request = $this->teams_model->declineInvitation(array('idUser'=>$idUser,'idTeam'=>$idTeam ));

        if($request){
            $not['idUserReceived'] = $idUser;
            $not['topic'] = 'Thanks for your response!';
            $not['message'] = "You've declined the invitation.";
            $this->functions->addNotification($not);

            $response = array('response' => true);
            $this->response($response, 200);
        }else{
            $response = array('response' => false);
            $this->response($response, 404);
        }
    }

    /**
     *
     *  Get Team Inbox
     * @param idTeam
     */
    public function teamInbox_get(){

        $idReceived = $this->get('idTeam');
        $receivedType = 1;

        $array = array(
            'idReceived'=>$idReceived,
            'receivedType'=>$receivedType,
        );

        $inbox = $this->inbox_model->get_messages($array);

        if($inbox){
            $response = array('response' => true,'result' => $inbox);
            $this->response($response, 200);
        }else{
            $response = array('response' => false);
            $this->response($response, 404);
        }
    }

    /**
     *
     * Get Teams by User
     * @param idUser
     * */
    public function userTeams_get(){

        $userId = $this->get('idUser');
        if($userId != 0){
            $teams = $this->teams_model->teamsByUser($userId);
            if($teams){
                $response = array('response' => true,'result' => $teams);
                $this->response($response, 200);
            }else{
                $response = array('response' => false, 'result' => 'This user doesn\'t have teams');
                $this->response($response, 404);
            }
        }else{
            $response = array('response' => false, 'result' => 'Please, send a valid userId');
            $this->response($response, 404);
        }

    }

    /**
     *
     * Get Teams by Name
     * @param teamName
     * */
    public function getTeamByName_get(){

        $teamName = $this->get('teamName',TRUE);

        if($teamName){

            $teams = $this->teams_model->teamsByName($this->newSuscriptor($this->sanitize_model->sanitize($teamName)));

            if($teams){
                $response = array('response' => true,'result' => $teams);
                $this->response($response, 200);
            }else{
                $response = array('response' => false, 'result' => 'There is no team with that name');
                $this->response($response, 404);
            }
        }else{
            $response = array('response' => false, 'result' => 'Please, send a valid team name');
            $this->response($response, 404);
        }

    }

    /**
     *  Modifica al userMVP
     *  @param idTeam
     *  @param idUser
     *
     */
    public function changeMVP_get(){
        $idTeam = $this->get('idTeam');
        $idUser = $this->get('idUser');

        if(!empty($idUser)&&!empty($idTeam)){

            $array = array(
                'idTeam' => $idTeam,
                'idUser' => $idUser
            );

            $changed = $this->teams_model->change_mvp($array);

            if($changed){
                $response = array('response' => true,'result' => 'MVP Changed Successfully');
                $this->response($response, 200);
            }else{
                $response = array('response' => false,'result' => 'There was an error');
                $this->response($response, 404);
            }
        }else{
            $response = array('response' => false,'result' => 'Wrong data sent');
            $this->response($response, 404);
        }
    }

    /**
     *  Devuelve el Lider y MVP
     *  @param idTeam
     *
     */
    public function getImportantMembers_get(){

        $idTeam = $this->get('idTeam');

        if(!empty($idTeam)){

            $importantMembers = $this->teams_model->getImportantMembers($idTeam);

            if($importantMembers){
                $response = array('response' => true,'result' => $importantMembers);
                $this->response($response, 200);
            }else{
                $response = array('response' => false,'result' => "That Team Doesn't Exist");
                $this->response($response, 404);
            }
        }else{
            $response = array('response' => false,'result' => 'You have to give a Team!!');
            $this->response($response, 404);
        }
    }
}

