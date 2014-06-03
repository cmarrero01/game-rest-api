<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 *  Clans Controller
 *
 * @package		CodeIgniter
 * @subpackage	Rest Server
 * @category	Controller
*/

require APPPATH.'/libraries/REST_Controller.php';

/**
 * @property mixed clans_model
 * @property mixed inbox_model
 */
class Clans extends REST_Controller
{

	public function __construct()
	{
		parent:: __construct();
        $this->config->load('email');
        $this->load->model('clans_model');
        $this->load->model('messages_model');
        $this->load->model('user_model');
        $this->load->model('sanitize_model');
	}

	/**
	* Lista de Clans
	* Endpoint Clans/getClans
	* Devuele la lista de Clanes.
	* @param clanLead
	* @param userMemberId
	* @param flag
	*/
	public function getClans_get()
	{
		$options = array();

		$clanLead = $this->get('clanLead');
        $count = $this->get('count');
        $offset = $this->get('offset');
        $limit = $this->get('limit');
		$userMemberId = $this->get('userMemberId');
		$flag = $this->get('flag');

		if($clanLead)$options['clanLead']=$clanLead;

		if($userMemberId){
			$memebersClan = $this->clans_model->get_members_clans(array('idUser'=>$userMemberId,'flag'=>$flag));

			if($memebersClan){
				foreach($memebersClan as $member){
					$clan[] = $this->clans_model->get_clans(array('idClan'=>$member->idClan));
				}
			}
		}else{
			$idClan = $this->get('idClan');
			if($idClan)$options['idClan']=$idClan;
            if($limit)$options['limit']=$limit;
            $options['offset']=$offset;
            $options['count']=$count;
			$clan = $this->clans_model->get_clans($options);
		}

		if($clan)
		{
			$response = array('response' => true,'result' => $clan);
			$this->response($response, 200);
		}
		else
		{
			$response = array('response' => false,'result' => 'The clan not exist');
			$this->response($response, 404);
		}
	}

	/**
	*
	* Get memebers of this clan
	* Devuelve los miembros de un Clan determinado <i>idClan</i>
	* Endpoint Clans/getMembers
	* @param idClan
	* @param flag
	*/
	public function getMembers_get(){
		$idClan = $this->get('idClan');
		$flag = $this->get('flag');

		$members = $this->clans_model->get_membersClans(array('idClan'=>$idClan,'flag'=>$flag));

        $response = array('response' => true,'result' => $members);
        $this->response($response, 200);

		if($members)
		{
			$response = array('response' => true,'result' => $members);
			$this->response($response, 200);
		}
		else
		{
			$response = array('response' => false, 'result' => 'The Clan has no members');
			$this->response($response, 404);
		}
	}
        
        public function getLeaderClan_get(){
            $idClan = $this->get('idClan');
            $leadRequest = $this->clans_model->getClanLeader($idClan);
            $request = $leadRequest;
            
            if($request){
			$response = array('response' => true,'result'=>$request);
			$this->response($response, 200);
		}else{
			$response = array('response' => false);
			$this->response($response, 404);
		}
        }

	/**
	* El usuario <i>idUser</i> requiere unirse a un clan <i>idClan</i>
	* Join member to clans
	* Endpoint Clans/requestJoinClan
	* @param idUser
	* @param idClan
	*/
	public function requestJoinClan_get(){
		$idUser = $this->get('idUser');
		$idClan = $this->get('idClan');
        $webUrl = $this->config->item('webUrl');
        $request = $this->clans_model->requestJoinClan(array('idUser'=>$idUser,'idClan'=>$idClan ));

		if($request){

			$leadRequest = $this->clans_model->clans_model(array('idClan'=>$idClan));
            $user = $this->user_model->get_user_data($idUser);

            $clanLeadUser = $leadRequest[0]->clanLeadUser;

			$notLead['idUserReceived'] = $clanLeadUser;
			$notLead['topic'] = $user->nicename.' wants to join your Clan';
            $notLead['email_subject'] = "New Team Member Request";
            $notLead['message'] = '<a target="_blank" href="'.$webUrl.'/profile/show/'.$user->nicename.'">'.$user->nicename.'</a> has sent a request to join your Clan, and it\'s waiting for your approval. <a target="_blank" href="'.$webUrl.'/clan_profile/show/'.$idClan.'">Go there</a>';
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
	* Devuelve el pedido del usuario por unirse a un clan.
	* Join member to clan
	* Endpoint Clans/joinClan
	* @param idUser
	* @param idClan
	*/
	public function joinClan_get(){
		$idUser = $this->get('idUser');
		$idClan = $this->get('idClan');
                //id del mensaje de notificación para modificar
                $idNoti = $this->get('idNotif');
                $hola = $this->inbox_model->update_notif($idNoti);

                $args = array('idUser'=>$this->get('idUser'),'idClan'=>$this->get('idClan'));

		$request = $this->clans_model->joinMember($args);

		if($request){
			$response = array('response' => true);
			$this->response($response, 200);
		}else{
			$response = array('response' => false);
			$this->response($response, 404);
		}
	}

	/**
	* Devuelve la creación de un Clan nuevo.
	* Add new Clan
	* Endpoint Clans/newClan
	* @param clanName
	* @param clanDescription
	* @param members
	* @param userClanLead
	*/
	public function newClan_get(){

		$clanName = $this->get('clanName',TRUE);
		$clanDescription = $this->get('clanDescription',TRUE);
		$members = $this->get('members');
		$userClanLead = $this->get('userClanLead');

		$args = array(
			'clanName'=>$this->sanitize_model->sanitize($clanName),
			'clanDescription'=>$this->sanitize_model->sanitize($clanDescription),
			'userClanLead'=>$userClanLead,
			'createDate'=>date('Y-m-d h:i:s',time()),
			'avatarPath'=>'/media/img/clanBig.png'
//                        'flag' => 2
			);

		$request = $this->clans_model->newClan($args,$members);

		if($request){
			$response = array('response' => true,'result'=>$request);
			$this->response($response, 200);
		}else{
			$response = array('response' => false);
			$this->response($response, 404);
		}
	}

    /**
     * Edit Clan Profile
     * @param idClan
     * @param clanDescription
     */
    public function editProfile_get(){
        $idClan = $this->get('idClan');
        $clanDescription = $this->get('clanDescription');

        if(!empty($idClan)and !empty($clanDescription)){
            $result = $this->clans_model->editClanProfile($idClan,$this->sanitize_model->sanitize($clanDescription));
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
     * Invita a nuevos miembros al Clan, desde un Clan ya existente.
     */
    public function inviteMembers_get(){
        $members = $this->get('members');
        $idClan = $this->get('idClan');
        $clanName = $this->get('clanName');
        $request = $this->clans_model->inviteMembers($idClan, $members, $clanName);
        if($request){
            $response = array('response' => true,'result'=>$request);
            $this->response($response, 200);
        }else{
            $response = array('response' => false);
            $this->response($response, 404);
        }
    }

	/**
	* Devuelve un mensaje al usuario que ha pedido unirse a un Clan.
	*  Accept Member Request
	* @param idUser
	* @param idClan
	*
	*/
	public function acceptRequest_get(){


		$idUser = $this->get('idUser');
		$idClan = $this->get('idClan');
        $webUrl = $this->config->item('webUrl');

        $request = $this->clans_model->joinMember(array('idUser'=>$idUser,'idClan'=>$idClan ));

		if($request){
            $clan = $this->clans_model->get_clans(array('idClan'=>$idClan));
			$not['idUserReceived'] = $idUser;
			$not['topic'] = 'Your request to join '.$clan->clanName.' has been Approved';
			$not['email_subject'] = 'Your request to join '.$clan->clanName.' has been Approved';
			$not['message'] = 'Congratulations! The Clan <a target="_blank" href="'.$webUrl.'/clan_profile/show/'.$idClan.'">'.$clan->clanName.'</a> has added you as a member. Now you can participate in it\'s discussions and events. You can now talk to other clan members using our chat.';
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
	* Devuelve un mensaje al usuario que se le ha denegado el pedido unirse a un Clan.
	*  Deny Member Request
	*  @param idUser
	*  @param idClan
	*  @param flag 1 means no mails to send, when it's 2 should send the nottification and email.
	*/
	public function denyRequest_get(){

		$idUser = $this->get('idUser');
		$idClan = $this->get('idClan');
		$flag = $this->get('flag');
        $webUrl = $this->config->item('webUrl');

        $request = $this->clans_model->deleteRequest(array('idUser'=>$idUser,'idClan'=>$idClan ));

            if($request && $flag == 2){
            $clan = $this->clans_model->get_clans(array('idClan'=>$idClan));

            $not['idUserReceived'] = $idUser;
            $not['topic'] = 'Your request to join '.$clan->clanName.' has been Declined';
            $not['email_subject'] = 'Your request to join '.$clan->clanName.' has been Declined';
            $not['message'] = 'We\'re sorry but your request to join clan <a target="_blank" href="'.$webUrl.'/clan_profile/show/'.$idClan.'">'.$clan->clanName.'</a> has been declined.';
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
         * Un usuario deja el Clan.
         */
        public function leaveClan_get(){
            $idClan = $this->get('idClan');
            $idUser = $this->get('idUser');
            $request = $this->clans_model->leaveClan(array('idUser' => $idUser, 'idClan' => $idClan));
            echo $request;
        }

        /**
     * Decline a invitation to join a Team.
     * @param idUser
     * @param idTeam
     */
    public function declineRequest_get() {

        $idUser = $this->get('idUser');
        $idClan = $this->get('idClan');

        $request = $this->clans_model->declineInvitation(array('idUser' => $idUser, 'idClan' => $idClan));

        if ($request) {
            $not['idUserReceived'] = $idUser;
            $not['topic'] = 'Thanks for your response!';
            $not['message'] = "You've been declined the invitation.";
            $this->functions->addNotification($not);

            $response = array('response' => true);
            $this->response($response, 200);
        } else {
            $response = array('response' => false);
            $this->response($response, 404);
        }
    }
	/**
	*  Devuelve el inbox del Clan.
	*  Get Clan Inbox
	*  @param idClan
	*
	*/
	public function clanInbox_get(){

		$idReceived = $this->get('idClan');
		$receivedType = 2;

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

        $idClan = $this->post('idClan');
        if (empty($idClan)) {
            $response = array('response' => false, 'result' => 'The clan id is empty');
            $this->response($response, 200);
        }

        $avatar_str = base64_decode($avatar_img);

        $path = $_SERVER['DOCUMENT_ROOT'].'/media/clanfiles/' . $idClan . '/';

        if (!file_exists($path)) {
            mkdir($path);
        }

        $path = $_SERVER['DOCUMENT_ROOT'].'/media/clanfiles/' . $idClan . '/' . $file_data['file_name'];
        $avatar_file = fopen($path, "w+");
        fwrite($avatar_file, $avatar_str);
        fclose($avatar_file);


        if (file_exists($path)) {

            //Eliminaos todos los avatars de este usuario que existan
            $this->clans_model->deleteAvatar(array('idClan' => $idClan));

            $args = array(
                'idClan' => $idClan,
                'imagePath' => 'media/clanfiles/' . $idClan . '/' . $file_data['file_name'],
                'size' => '150'
            );

            //Insertamos en la base de datos el avatar
            $insertAvatar = $this->clans_model->addAvatar($args);
            //Si la imagen se inserta en la base de datos, devuelves todo joya men
            if ($insertAvatar) {

                $options['full_path'] = $path;
                $options['width'] = 53;
                $options['height'] = 53;
                $options['thumb'] = TRUE;

                if ($this->functions->resizeAvatars($options)) {

                    $args = array(
                        'idClan' => $idClan,
                        'imagePath' => 'media/clanfiles/' . $idClan . '/' . $file_data['raw_name'] . '_thumb.' . $this->functions->getTypeFile($file_data['file_type']),
                        'size' => '53'
                    );
                    //Insertamos en la base de datos el avatar
                    $insertAvatar = $this->clans_model->addAvatar($args);
                }

                $avatars = $this->clans_model->getAvatars(array('idClan' => $idClan));
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
     * Get avatar of a clan
     */
    public function getAvatar_get(){
        $idClan = $this->get('idClan');
        $avatars = $this->clans_model->getAvatars(array('idClan'=>$idClan));
        if($avatars){
            $this->response(array('response'=>true,'result'=>$avatars), 200);
        }
        $this->response(array('response'=>false,'result'=>'This clan havent any avatar'), 200);
    }

    /**
     *  Modifica al userLead
     *  @param idClan
     *  @param idUser
     *
     */
    public function changeSubLead_get(){
        $idClan = $this->get('idClan');
        $idUser = $this->get('idUser');

        if(!empty($idClan)&&!empty($idUser)){
            $array = array(
                'idClan' => $idClan,
                'idUser' => $idUser
            );

            $changed = $this->clans_model->change_subLead($array);

            if($changed){
                $response = array('response' => true,'result' => 'SubLeader Changed Successfully');
                $this->response($response, 200);
            }else{
                $response = array('response' => false,'result' => 'There was an error');
                $this->response($response, 404);
            }
        }

    }

    /**
     *  Devuelve el Lider y SubLider
     *  @param idClan
     *
     */
    public function getImportantMembers_get(){

        $idClan = $this->get('idClan');

        if(!empty($idClan)){

            $importantMembers = $this->clans_model->getImportantMembers($idClan);

            if($importantMembers){
                $response = array('response' => true,'result' => $importantMembers);
                $this->response($response, 200);
            }else{
                $response = array('response' => false,'result' => "That Clan Doesn't Exist");
                $this->response($response, 404);
            }
        }else{
            $response = array('response' => false,'result' => 'You have to give a Clan!!');
            $this->response($response, 404);
        }
    }

    /**
     *
     * Get Clans by Name
     * @param teamName
     * */
    public function getClanByName_get(){

        $clanName = $this->get('clanName',TRUE);

        if($clanName){

            $clans = $this->clans_model->clansByName($this->sanitize_model->sanitize($clanName));

            if($clans){
                $response = array('response' => true,'result' => $clans);
                $this->response($response, 200);
            }else{
                $response = array('response' => false, 'result' => 'There is no clan with that name');
                $this->response($response, 404);
            }
        }else{
            $response = array('response' => false, 'result' => 'Please, send a valid clan name');
            $this->response($response, 404);
        }

    }

    /**
     *
     */
    public function postAnnouncement_get(){
        $idClan = $this->get('idClan');
        $announcement = $this->get('announcement',TRUE);
        $title = $this->get('title',TRUE);

        $announcement = $this->sanitize_model->sanitize($announcement);
        $title = $this->sanitize_model->sanitize($title);

        $result = $this->clans_model->postAnnouncement($idClan, $announcement, $title);

        if($result){
            $response = array('response' => true, 'result' => $result);
            $this->response($response, 200);
        }else{
            $response = array('response' => false, 'result' => 'Error!');
            $this->response($response, 404);
        }
    }

    /**
     *
     */
    public function getAnnouncements_get(){

        $idClan = $this->input->get('idClan');

        if($idClan && $idClan != 0 && $idClan != null && !empty($idClan)){

            $result = $this->clans_model->getAllAnnouncements($idClan);

            if($result && is_array($result) && !empty($result)){
                $response = array('response' => true, 'result' => $result);
                $this->response($response, 404);
            }else{
                $response = array('response' => false, 'result' => 'Something went wrong!!');
                $this->response($response, 404);
            }
        }else{
            $response = array('response' => false, 'result' => 'Something went wrong!!');
            $this->response($response, 404);
        }

    }

    /**
     *
     */
    public function getAnnounceById_get(){

        $idAnnounce = $this->get('idAnnounce');

        $announce = $this->clans_model->getAnnouncementById($idAnnounce);

        if($announce && !empty($announce)){
            $response = array('response' => true, 'result' => $announce);
            $this->response($response, 200);
        }else{
            $response = array('response' => false, 'result' => 'Something went wrong!!');
            $this->response($response, 404);
        }

    }

    /**
     *
     */
    public function saveAnnouncement_get(){

        $id = $this->get('id');
        $text = $this->get('text');
        $title = $this->get('title');

        $text = $this->sanitize_model->sanitize($text);
        $title = $this->sanitize_model->sanitize($title);

        $update = $this->clans_model->updateAnnouncement($id,$title,$text);

        if($update && !empty($update)){
            $response = array('response' => true, 'result' => 'The announce has been update!');
            $this->response($response, 200);
        }else{
            $response = array('response' => false, 'result' => 'Something went wrong!!');
            $this->response($response, 404);
        }
    }

    /**
     *
     */
    public function removeAnnouncement_get(){

        $id = $this->get('id');

        $remove = $this->clans_model->removeAnnounce($id);

        if($remove){
            $response = array('response' => true, 'result' => 'The announce has been removed!');
            $this->response($response, 200);
        }else{
            $response = array('response' => false, 'result' => 'Something went wrong!!');
            $this->response($response, 404);
        }
    }
}

