<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * User Controller
 *
 * This is a mode of consume services for users
 * The data is mcoked.
 *
 * @package		CodeIgniter
 * @subpackage	Rest Server
 * @category	Controller
 * @author		Claudio Marrero
 * @link		http://marreroclaudio.com.ar/
 */
// This can be removed if you use __autoload() in config.php OR use Modular Extensions
require_once APPPATH . '/libraries/REST_Controller.php';

/**
 * @property mixed user_model
 * @property mixed points_model
 * @property mixed sanitize_model
 * @property mixed invite_model
 */
class User extends REST_Controller {

    public function __construct() {
        parent:: __construct();
        $this->load->model('user_model');
        $this->load->model('sanitize_model');
        $this->config->load('email', '', 'email');
        $this->load->model('invite_model');
    }

    /**
     * Encripta el id de un usuario.
     * @param userId
     */
    public function encode_id_get() { //creado para testing
        //encriptamos lo que recibimos
        $id = $this->encrypt->encode($this->get('userId'));
        //y lo devolvemos
        $this->response($id);
    }

    /**
     * Desencripta el id de usuario.
     * @param userId
     */
    public function decode_id_get() { //creado para testing
        //desencriptamos lo que recibimos
        $id = $this->encrypt->decode($this->get('userId'));
        //y lo devolvemos
        $this->response($id);
    }

    /**
     * <sumary>
     * Servicio que recibe un email y un password para loggear un usuario en un sistema dado
     * </sumary>
     * Endpoint: user/login
     * @param email
     * @param password
     * @param encripted
     */
    public function login_get() {
        if ($this->get('email') and $this->get('password')) {

            $userAccount = $this->get('email',TRUE);

            if ($this->get('encripted')) {
                $password = $this->get('password');
            } else {
                $password = md5($this->get('password'));
            }
            $valid_email = preg_match('/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/', $userAccount);

            //No es un mail, así que asumo que me mandó un nickname
            if ($valid_email == 0) {
                $userAccount = $this->user_model->userByNicename($userAccount);
                if($userAccount){
                    //Me ahorro una consulta para saber si esta confirmado el email
                    if($userAccount[0]->confirm_email_key == md5($userAccount[0]->email)){
                        $response = array('response' => false, 'result' => 'That email has not been confirmed');
                        $this->response($response, 404);
                    }else{
                        $userAccount = $userAccount[0]->email;
                        $loggin = $this->user_model->login($userAccount, $password);

                        if (!empty($loggin)) {
                            $response = array('response' => true, 'result' => $loggin);
                            $this->response($response, 200); // 200 being the HTTP response code
                        } else {
                            $response = array('response' => false, 'result' => 'The password is incorrect');

                            $this->response($response, 404);
                        }
                    }
                }else{
                    $response = array('response' => false, 'result' => 'The password is incorrect');

                    $this->response($response, 404);
                }
            }else{
                $isConfirmed = $this->user_model->emailIsConfirmed($userAccount);
            }

            if (!$isConfirmed) {
                $loggin = $this->user_model->login($userAccount, $password);
                if (!empty($loggin)) {
                    $response = array('response' => true, 'result' => $loggin);
                    $this->response($response, 200); // 200 being the HTTP response code
                } else {
                    $response = array('response' => false, 'result' => 'The password is incorrect');

                    $this->response($response, 404);
                }
            } else {
                $response = array('response' => false, 'result' => 'That email has not been confirmed');
                $this->response($response, 404);
            }
        } else {
            $response = array('response' => false, 'result' => 'Please send me some data');
            $this->response($response, 404);
        }
    }

    /**
     * <sumary>
     * Check if nicename exist into the db.
     * </sumary>
     * Endpoint: user/checkNicename
     * @param nicename
     */
    public function checkUserByNicename_get() {

        $nicename = $this->get('nicename');
        $user = $this->user_model->userByNicename($nicename);

        if (!empty($user)) {
            $response = array('response' => true, 'result' => true);
            $this->response($response, 200);
        } else {
            $response = array('response' => false, 'result' => false);
            $this->response($response, 404);
        }
    }

    /**
     * <sumary>
     * //enviar email de contacto
     * </sumary>
     * @param full_name
     * @param user_email
     * @param comment
     * */
    public function sendContact_get() {
        $full_name = $this->get('full_name');
        $user_email = $this->get('user_email');
        $comment = $this->get('comment');

        if (empty($user_email)) {
            $response = array('response' => false, 'result' => 'The email is empty');
            $this->response($response, 404);
        }
        if (empty($comment)) {
            $response = array('response' => false, 'result' => 'There is no comment');
            $this->response($response, 404);
        }

        $subject = "Contact Form by" . $full_name;
        $content = "The User: " . $full_name . ", has sent the following message:\n" . $comment;

        $this->email->from($user_email, $full_name);
        $this->email->to('contact@battlepro.com');
        $this->email->subject($subject);
        $this->email->message($content);
        $this->email->send();

        $this->email->from('contact@battlepro.com', 'BattlePro');
        $this->email->to($user_email);
        $this->email->subject('Thanks for send your comment');
        $this->email->message('Hello, We recive your email, we contact you soon. Thanks.');

        if (!$this->email->send()) {
            $response = array('response' => false, 'result' => 'There was an error');
            $this->response($response, 404);
        } else {
            $response = array('response' => true, 'result' => 'Contact Email Sent Succesfully');
            $this->response($response, 200);
        }
    }

    /**
     * <sumary>
     * //// Registro del usuario, envia un email para validar la cuenta
     * </sumary>
     * //params: string full_name, string nicename, string email, string password, int favoriteGame.
     * Endpoint: user/login
     * @param full_name
     * @param nicename
     * @param password
     * @param email
     * @param country
     * @param day_birth
     * @param month_birth
     * @param year_birth
     * @param favouriteGame
     * @param tos
     * */
    public function register_get() {
        $full_name = $this->get('full_name', TRUE);
        $country = 2;
        $nicename = $this->get('nicename', TRUE);
        $password = $this->get('password', TRUE);
        $email = $this->get('email', TRUE);
        $day_birth = $this->get('day_birth');
        $month_birth = $this->get('month_birth');
        $year_birth = $this->get('year_birth');
        $tos = $this->get('tos');
        $subscribe = $this->get('subscribe');
        $facebook_id = 0;
        $referalCode = $this->get('referalCode');

        if($this->get('facebook_id')){
            $facebook_id = $this->get('facebook_id');
        }

        if ($tos != 'on') {
            $response = array('response' => false, 'result' => 'You must accept the Terms & Conditions');
            $this->response($response, 200);
        }

        $this->_register_validate_post($email, $password);

        if (!empty($nicename)) {

            $nicename = $this->sanitize_model->sanitize($nicename);
            $usersByNicename = $this->user_model->userByNicename($nicename);
            if (!empty($usersByNicename)) {
                $response = array('response' => false, 'result' => 'Your nickname is already taken. :(');
                $this->response($response, 200);
            }
        }

        //pasamos el email al modelo para que compruebe si existe el email ya esta registrado.
        //noExistEmail devuelve true si no existe el usuario
        $noExistEmail = $this->user_model->exist_email($email);

        // si no existe el email, lo registramos
        if ($noExistEmail) {
            $date = (date("Y-m-d H:i:s"));

            //Sanitizamos antes de enviar a la BD
            $data = array(
                'full_name' => $full_name,
                'nicename' => $nicename,
                'password' => $password,
                'email' => $email
            );

            $data = $this->sanitize_model->sanitize($data);

            $birthday = $year_birth . '-' . $month_birth . '-' . $day_birth;

            //Prepare procedure tu add new user
            $procedure = 'insertNewUser(';
            $procedure.= "'" . $data['full_name'] . "',";
            $procedure.= "'" . $data['nicename'] . "',";
            $procedure.= "'" . $data['email'] . "',";
            $procedure.= "'" . $data['email'] . "',";
            $procedure.= "'" . $data['password'] . "',";
            $procedure.= "'" . $country . "',";
            $procedure.= "'" . $birthday . "',";
            $procedure.= "'" . $date . "',";
            $procedure.= "'" . $facebook_id . "')";

            $userId = $this->user_model->register_user($procedure);
            if ($userId) {
                if($subscribe == 'on'){
                    $this->newsletterSubscribe($userId);
                }
                $this->load->model('points_model');
                $options = array(
                    'userId'=>$userId,
                    'points'=>'0',
                    'morePoints'=>'1000'
                );

                $this->points_model->plusPointsHistory($options);

                if($facebook_id != 0){
                    $this->user_model->confirmEmail(md5($data['email']));
                }else{
                    $this->sendConfirmationEmail_get($email,$referalCode);
                }
            }

            //Realizamos el chequeo y el update si se envia el referal
            $this->invite_model->updateInvitation(array('email'=>$data['email'],'referalCode'=>$referalCode));

            $response = array('response' => true, 'result' => 'The user was successfully registered');
            $this->response($response, 200);             // exito
        } else {
            // si es erroneo (ya existe el email), devolvemos un mensaje de error
            $response = array('response' => false, 'result' => 'That Email already exist');
            $this->response($response, 404);
        }
    }

    /**
     * <sumary>
     * //// Ediciòn del perfil de usuario
     * </sumary>
     * //params: string full_name, string email, string country, int favoriteGame, int date_birth, int month_birth, int year_birth.
     * Endpoint: user/editProfile
     * @param full_name
     * @param email
     * @param country
     * @param day_birth
     * @param month_birth
     * @param year_birth
     * @param favouriteGame
     * */
    public function editProfile_get() {
        $email = $this->get('email', TRUE);
        $email = $this->sanitize_model->sanitize($email);
        $responseType = 1; // 1 = Edit Profile Info, 2 = Edit Email
        $userId = $this->get('userId');

        $valid_email = preg_match('/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/', $email);

        if ($valid_email == 0) {
            $response = array('response' => false, 'result' => 'That\'s not a valid Email');
            $this->response($response, 404);
        }

        $noExistEmail = $this->user_model->exist_email_with_id($email, $userId);    //pasamos el email al modelo para que compruebe si existe el email ya esta registrado. noExistEmail devuelve true si no existe el usuario

        if ($noExistEmail) {     // si no existe el email, lo editamos
            //Sanitizamos antes de enviar a la BD
            $full_name = $this->get('full_name', TRUE);
            $country = $this->get('country', TRUE);
            $day_birth = $this->get('day_birth');
            $month_birth = $this->get('month_birth');
            $year_birth = $this->get('year_birth');
            $favouriteGame = $this->get('favouriteGame');

            $birthday = $year_birth . '-' . $month_birth . '-' . $day_birth;

            //Verifico si el usuario edito el email o no
            $isEmailEdited = $this->user_model->get_user_data($userId);
            $isEmailEdited = $isEmailEdited->email;

            if ($isEmailEdited != $email) {
                //Lo editò, asi que tiene que validarlo desde el mail
                $responseType = 2;
                $confirmEmail = md5($email);
            } else {
                $confirmEmail = '';
            }

            $data = array(
                'full_name' => $this->sanitize_model->sanitize($full_name),
                'email' => $this->sanitize_model->sanitize($email),
                'confirm_email_key' => $confirmEmail,
                'countryId' => $country,
                'birthday' => $birthday,
                'favouriteGameId' => $favouriteGame,
            );

            $userId = $this->user_model->edit_user($data, $userId);

            if ($userId) {
                if ($isEmailEdited != $email) {
                    $this->sendConfirmationEmail_get($email);
                }
                $response = array('response' => $responseType, 'result' => 'The profile was edited');

                $this->response($response, 200);
            } else {
                $response = array('response' => false, 'result' => 'There was an error');

                $this->response($response, 404);
            }
        } else {  // si es erroneo (ya existe el email), devolvemos un mensaje de error
            $response = array('response' => false, 'result' => 'That Email already exist');
            $this->response($response, 404);
        }
    }

    /**
     * <sumary>
     * //Servicio para detectar si el mail esta confirmado
     * </sumary>
     * Endpoint: user/confirmEmail
     * @param email
     * */
    public function verifyEmail_get() {

        $emailKey = $this->get('a');
        $confirmEmail = $this->user_model->confirmEmail($emailKey);

        if ($confirmEmail) {
            $response = array('response' => true, 'result' => 'Your Email has been confirmed');
            $this->response($response, 200);
        } else {
            $response = array('response' => false, 'result' => "That Email doesn't exist");
            $this->response($response, 404);
        }
    }

    /**
     * <sumary>
     * //Servicio para enviar el mail de registro
     * </sumary>
     * Endpoint: user/confirmEmail
     * @param email
     * */
    public function sendConfirmationEmail_get($email,$referalCode=false) {

        if (empty($email)) {
            $responseType = 1;
            $email = $this->get('email');
            $email = $this->sanitize_model->sanitize($email);
        } else {
            $responseType = 2; //Viene del editProfile, que espera un 2 para desloguear por el cambio del email
        }

        $user = $this->user_model->getUserByEmail(array('email' => $email));

        if ($user) {

            $isNotConfirmed = $this->user_model->emailIsConfirmed($email);

            if ($isNotConfirmed) {
                $registerUrl = md5($email);
                $this->email->from('no-reply@battlepro.com', 'Battle Pro');
                $this->email->to($email);
                $this->email->subject('Registration BattlePro');
                $data = array(
                    'web_url'=>$this->config->item('webUrl'),
                    'image_url'=>$this->config->item('imagesUrl'),
                    'email'=>$email,
                    'user'=>$user
                );
                $this->email->message($this->load->view('emails/register/newuser',$data,true));

                if (!$this->email->send()) {
                    $response = array('response' => true, 'result' => "Problem sending an email with confirmation, please try again.");
                    $this->response($response, 200);
                } else {
                    //Actualizamos la invitacion (si es que existio)
                    $this->invite_model->updateInvitation(array('email'=>$email,'referalCode'=>$referalCode));
                    $response = array('response' => $responseType, 'result' => "Confirmation Email Sent Successfully");
                    $this->response($response, 200);
                }
            } else {
                $response = array('response' => false, 'result' => "That Email was already confirmed");
                $this->response($response, 404);
            }
        } else {
            $response = array('response' => false, 'result' => "That Email doesn't exist");
            $this->response($response, 404);
        }
    }

    /**
     * <sumary>
     * //Servicio para detectar si el mail esta registrado
     * </sumary>
     * Endpoint: user/existEmail
     * @param email
     * */
    public function existEmail_get($email) {

        if (empty($email)) {
            $email = $this->get('email');
        }

        $noExistEmail = $this->user_model->exist_email($email);

        if ($noExistEmail) {     // si no existe el email, devolvemos true
            $this->response(array('result' => true), 200);             // exito
        } else {      // si es erroneo (ya existe el email), devolvemos false
            $this->response(array('result' => false), 404);
        }
    }

    /**
     * <sumary>
     * //Servicio para detectar si el mail esta registrado en otro user
     * </sumary>
     * Endpoint: user/existEmailWithID
     * @param email
     * */
    public function existEmailWithID_get($email, $userId) {

        if (empty($email)) {
            $email = $this->get('email');
        }

        if (empty($userId)) {
            $userId = $this->get('userId');
        }

        $noExistEmail = $this->user_model->exist_email_with_id($email, $userId);

        if ($noExistEmail) {     // si no existe el email, devolvemos true
            $this->response(array('result' => true), 200);             // exito
        } else {      // si es erroneo (ya existe el email), devolvemos false
            $this->response(array('result' => false), 404);
        }
    }

    /**
     * <sumary>
     * // Debe controlar que el password sea alfanumerico y de mas de 8 caracteres y que email no este vacio
     * </sumary>
     * Endpoint: user/_register_validate
     * @param email
     * @param password
     * */
    public function _register_validate_post($email, $password) {  //params email ,password
        if (empty($email)) {
            $response = array('response' => false, 'result' => 'The email is empty');
            $this->response($response, 404);
        }

        if (empty($password)) {
            $response = array('response' => false, 'result' => 'The password is empty');
            $this->response($response, 404);
        }

        $valid_email = preg_match('/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/', $email);

        if ($valid_email == 0) {
            $response = array('response' => false, 'result' => 'The email is not a valid email address');
            $this->response($response, 404);
        }

        $valid_pass = preg_match('/^(?=^.{6,}$)((?=.*[0-9])(?=.*[a-z]))^.*$/', $password);

        if ($valid_pass == 0) {
            $response = array('response' => false, 'result' => 'The password must to be alfanumeric and have more than 6 caracters');
            $this->response($response, 404);
        }


        return true;
    }

    /**
     * <sumary>
     * //Trae los datos del usuario
     * </sumary>
     * Endpoint: user/user_data
     * @param userId
     * */
    public function user_data_get() {  //parameters userId
        $userId = $this->get('userId');
        $userMeta = $this->user_model->get_user_data($userId);

        if ($userMeta) {
            $response = array('response' => true, 'result' => $userMeta);
            $this->response($response, 200);
        } else {
            $response = array('response' => false, 'result' => 'User id no exist', 'userId' => $this->get('userId'));
            $this->response($response, 404);
        }
    }

    /**
     * <sumary>
     * //Trae los meta del usuario
     * </sumary>
     * Endpoint: user/user_meta
     * @param userId
     * */
    public function user_meta_get() {
        $userId = $this->get('userId');
        $userData = $this->user_model->get_user_meta($userId);
        if($userData){
            $response = array('response' => true, 'result' => $userData);
            $this->response($response, 200);
        }else{
            $response = array('response' => false, 'result' => "You haven`t metadata yet");
            $this->response($response, 200);
        }
    }

    /**
     * <sumary>
     * //Actualiza los meta del usuario
     * </sumary>
     * Endpoint: user/update_user_meta
     * @param userId
     * */
    public function update_user_meta_get() {
        $meta["idUser"] = $this->get('idUser');
        $meta["meta_key"] = $this->sanitize_model->sanitize($this->get('meta_key'));
        $meta["meta_value"] = $this->sanitize_model->sanitize($this->get('meta_value'));

        $this->user_model->update_user_meta($meta);
//          $response = array('response' => true, 'result' => $meta);
//        $this->response($response, 200);
    }

    /**
     * <sumary>
     * //Trae los datos del usuario
     * </sumary>
     * Endpoint: user/userData
     * @param myUser
     * @param userId
     * @param nicename
     * @param suggest
     * @param membersAdded
     * @param idClan
     * @param exact_match
     * @param edit
     * */
    public function userData_get() {
        $myUser = $this->get('myUser');
        $userId = $this->get('userId');
        $nicename = $this->get('nicename');
        $suggest = $this->get('suggest');
        $membersAdded = $this->get('membersAdded');
        $is_edit = $this->get('edit');
        $idClan = $this->get('idClan');
        $idTeam = $this->get('idTeam');
        $exact_match = $this->get('exact_match');

        $data = array();

        if (!empty($myUser)) {
            $data['myUser'] = $myUser;
        }
        if (!empty($userId)) {
            $data['userId'] = $userId;
        }

        if (!empty($nicename)) {
            $data['nicename'] = $nicename;
        }

        if (!empty($suggest)) {
            $data['suggest'] = $suggest;
        }

        if (!empty($membersAdded)) {
            $data['membersAdded'] = $membersAdded;
        }

        if (!empty($is_edit)) {
            $data['edit'] = 1;
        }
        if (!empty($idClan)) {
            $data['idClan'] = $idClan;
        }
        if (!empty($idTeam)) {
            $data['idTeam'] = $idTeam;
        }

        if (!empty($exact_match) and !empty($nicename)) {
            if ($exact_match) {
                $userData = $this->user_model->userByNicename($data['nicename']);
                $response = array('response' => true, 'result' => $userData);
                $this->response($response, 200);
            }
        }

        $userData = $this->user_model->get_user_data($data);

        if ($userData) {
            $response = array('response' => true, 'result' => $userData);
            $this->response($response, 200);
        } else {
            $response = array('response' => false, 'result' => 'User id no exist', 'userId' => $this->get('userId'));
            $this->response($response, 200);
        }
    }

    /**
     *
     * <sumary>
     * Edita el profile del usuario
     * </sumary>
     * Endpoint: user/edit
     * @param idUser
     * @param nicename
     * @param status
     *
     */
    public function edit_get() {

        $idUser = $this->get('idUser');
        $nicename = $this->get('nicename');
        $status = $this->get('status');

        $array = array(
            'nicename' => $nicename,
            'status' => $status
        );

        $editUser = $this->user_model->edit_user($array, $idUser);

        if (!empty($editUser)) {
            $response = array('response' => true, 'result' => true);
            $this->response($response, 200);
        } else {
            $response = array('response' => false, 'result' => false);
            $this->response($response, 404);
        }
    }

    /**
     * Retorna detalles generales del usuario.
     * @param userID
     *
     */
    public function user_details_post() {
        $userDetails = $this->user_model->get_user_general_details($this->post('userId'));
        if ($userDetails) {
            $response = array('response' => true, 'result' => $userDetails);
            $this->response($response, 200);
        } else {
            $response = array('response' => false, 'result' => 'User id no exist');
            $this->response($response, 200);
        }
    }

    /**
     * Devuelve la cantidad de plata que tiene sin retirar.
     * @param userId
     */
    public function game_cash_post() {
        //Desencripto el usuario ID
        $userId = $this->encrypt->decode($this->post('userId'));

        $gameCash = $this->user_model->get_game_cash($userId);

        if ($gameCash) {
            $response = array('response' => true, 'result' => $gameCash);

            $this->response($response, 200);
        } else {
            $response = array('response' => false, 'result' => 'User id no exist');

            $this->response($response, 404);
        }
    }

    /**
     * // lista de amigos del usuario
     * //parametro: userId
     * @TODO: Este metodo, debe remplazar al metodo post de arriba, ya que es solo una peticion de datos no un update de nada.
     */
    public function userFriends_get() {
        //Desencripto el usuario ID
        $userId = $this->get('userId');
        $flag = $this->get('flag');

        $userFriends = $this->user_model->get_userFriends(array('userId' => $userId, 'flag' => $flag));


        if ($userFriends) {
            $response = array('response' => true, 'result' => $userFriends);
            $this->response($response, 200);
        } else {
            $response = array('response' => false, 'result' => 'User id no exist or not have friends. For ever alone.');
            $this->response($response, 404);
        }
    }
    public function userFriendsWithAvatar_get(){
        $userId = $this->get('userId');
        $avatarSize = $this->get('avatarSize');
        $flag = $this->get('flag');
        $limit = $this->get('limit');

        if(!$avatarSize){
            $avatarSize = 150;
        }

        if(!$flag){
            $flag = 2;
        }

        if(!$limit){
            $limit = '';
        }

        $friends = $this->user_model->get_userFriendsWithAvatar($userId, $avatarSize,$flag,$limit);
        if($friends){
            $response = array('response' => true, 'result' => $friends);
            $this->response($response, 200);
        }else{
            $response = array('response' => false, 'result' => 'User id no exist or not have friends. For ever alone.');
            $this->response($response, 404);
        }
    }

    public function chat_userFriends_get() {
        //Desencripto el usuario ID
        $userId = $this->get('userId');
        $userFriends = $this->user_model->get_chatUserFriends($userId);

        if ($userFriends) {
            $response = array('response' => true, 'result' => $userFriends);
            echo json_encode($response);
            //$this->response($response, 200);
        } else {
            $response = array('response' => false, 'result' => 'User id no exist or not have friends. For ever alone.');
            $this->response($response, 404);
        }
    }

    /**
     * Lista de no amigos/solicitudes pendiendes.
     * @param userId
     */
    public function suggestFriends_get() {
        $userId = $this->get('userId');
        $nicename = $this->get('nicename');

        $userList = $this->user_model->get_suggestFriends(array('userId' => $userId, 'nicename' => $nicename));

        if ($userList) {
            $response = array('response' => true, 'result' => $userList);
            $this->response($response, 200);
        } else {
            $response = array('response' => false, 'result' => 'There are no suggestions');
            $this->response($response, 404);
        }
    }

    /**
     * Lista de usuarios que no pertecen al clan.
     */
    public function suggestMemberInviteClan_get(){
        $userId = $this->get('userId');
        $nicename = $this->get('nicename');
        $idClan = $this->get('idClan');

        $userList = $this->user_model->get_suggestMembersInviteClan(array('userId' => $userId, 'nicename' => $nicename, 'idClan' => $idClan));

        if ($userList) {
            $response = array('response' => true, 'result' => $userList);
            $this->response($response, 200);
        } else {
            $response = array('response' => false, 'result' => 'There are no suggestions');
            $this->response($response, 404);
        }
    }

    /**
     * Lista de usuarios que no pertecen al clan.
     */
    public function suggestMemberInviteTeam_get(){
        $userId = $this->get('userId');
        $nicename = $this->get('nicename');
        $idTeam = $this->get('idTeam');

        $userList = $this->user_model->get_suggestMembersInviteTeam(array('userId' => $userId, 'nicename' => $nicename, 'idTeam' => $idTeam));

        if ($userList) {
            $response = array('response' => true, 'result' => $userList);
            $this->response($response, 200);
        } else {
            $response = array('response' => false, 'result' => 'There are no suggestions');
            $this->response($response, 404);
        }
    }

    /**
     * Relacion entre dos usuarios que los convierten en amigos.
     * Devuelve TRUE si se hicieron amigos, False si hubo un problema.
     * @param userId
     * @param friendId
     */
    public function frienshipSendRequest_get() {//Parametro: userId, friendId

        $webUrl = $this->config->item('webUrl');
        //Desencripto el usuario ID
        $userId = $this->get('userId');

        //Desencripto el friend ID
        $friendId = $this->get('friendId');

        $date = (date("Y-m-d H:i:s", time()));

        $userAddFriend = $this->user_model->frienshipSendRequest($userId, $friendId, $date);   //true si se hicieron amigos

        if ($userAddFriend) {
            $userData = $this->user_model->get_user_data($userId);
            $args = array(
                'idUserReceived' => $friendId,
                'topic' => 'New Friend Request',
                'message' => 'Hi there! <br/> The user <a href="'.$webUrl.'/profile/user/' . $userData->nicename . '">' . $userData->nicename . '</a> has sent you a friend request.<br />',
                'sendEmail'=>1
            );
            $this->functions->addNotification($args);
            $response = array('response' => true, 'result' => $userAddFriend);
            $this->response($response, 200);
        } else {
            $response = array('response' => false, 'result' => 'User id no exist or the friendship already exist');
            $this->response($response, 404);
        }
    }

    /**
     * Listar peticiones de Amistad
     * @param userId
     *
     */
    public function userFriendshipRequests_get() {

        $userId = $this->get('userId');

        $result = $this->user_model->get_friendship_requests(array('userId' => $userId));

        if ($result) {
            $response = array('response' => true, 'result' => $result);
            $this->response($response, 200);
        } else {
            $response = array('response' => false, 'result' => 'User id no exist or the friendship not exist');
            $this->response($response, 404);
        }
    }

    /**
     * Aceptar peticion de amistad
     * @param userId
     * @param friendId
     */
    public function frienshipAccepted_get() {

        //Desencripto el usuario ID
        $userId = $this->get('userId');

        //Desencripto el friend ID
        $friendId = $this->get('friendId');

        $accept = $this->user_model->frienship_accepted(array('userId' => $friendId, 'friendId' => $userId));

        if ($accept) {
            $response = array('response' => true, 'result' => $accept);
            $this->response($response, 200);
        } else {
            $response = array('response' => false, 'result' => 'User id no exist or the friendship not exist');
            $this->response($response, 404);
        }
    }

    /**
     * Denegar peticion de amistad
     * @param userId
     * @param friendId
     */
    public function friendshipDenied_get() {

        //Desencripto el usuario ID
        $userId = $this->get('userId');

        //Desencripto el friend ID
        $friendId = $this->get('friendId');

        $accept = $this->user_model->user_remove_friend($friendId, $userId);

        if ($accept) {
            $response = array('response' => true, 'result' => $accept);
            $this->response($response, 200);
        } else {
            $response = array('response' => false, 'result' => 'User id no exist or the friendship not exist');
            $this->response($response, 404);
        }
    }

    public function inviteDenied_get() {

        //Desencripto el usuario ID
        $userId = $this->get('userId');

        //Desencripto el friend ID
        $friendId = $this->get('friendId');

        $accept = $this->user_model->user_remove_friend($friendId, $userId);

        if ($accept) {
            $response = array('response' => true, 'result' => $accept);
            $this->response($response, 200);
        } else {
            $response = array('response' => false, 'result' => 'User id no exist or the friendship not exist');
            $this->response($response, 404);
        }
    }

    /**
     * Elimina un amigo de la lista de amigos.
     * @param userId
     * @param friendId
     */
    public function userRemoveFriend_get() {
        //Desencripto el usuario ID
//        $userId = $this->encrypt->decode($this->post('userId'));
        $userId = $this->get('userId');
        $friendId = $this->get('friendId');

        //Desencripto el friend ID
//        $friendId = $this->encrypt->decode($this->post('friendId'));

        $userRemoveFriend = $this->user_model->user_remove_friend($userId, $friendId);   //true si se eliminó la amistad amigos

        if ($userRemoveFriend) {
            $response = array('response' => true, 'result' => $userRemoveFriend);
            $this->response($response, 200);
        } else {
            $response = array('response' => false, 'result' => 'User id no exist or the friendship not exist');
            $this->response($response, 404);
        }
    }

    /**
     * Envía por email una URL para recuperar el password.
     */
    public function _sendPassByEmail($email, $password) {
        $subject = "Password recover Battle Pro";
        $webUrl = $this->config->item('webUrl');
        $text = "Battle Pro. \n Your new password is: " . $password . "\n Please enter to " . $webUrl . "/recover to complete the password recovery process.";

        $this->email->from('recover@progamer.com', 'Administrator');
        $this->email->to($email);
        $this->email->subject('Recover Password Battle Pro');
        $this->email->message($text);
        $this->email->send();
    }

    /**
     * Resetea la contraseña y la envia por email
     * @param email
     */
    public function forgotPassword_get() {     //Parametro: email
        $noExistEmail = $this->user_model->exist_email($this->get('email'));    //pasamos el email al modelo para que compruebe si existe el email ya esta registrado. noExistEmail devuelve true si no existe el usuario

        if ($noExistEmail) {     // error no existe el email,
            $response = array('response' => false, 'result' => 'The email not exist');

            $this->response($response, 404);
        } else {
            $caracteres = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
            $longpalabra = 8;
            for ($pass = '', $n = strlen($caracteres) - 1; strlen($pass) < $longpalabra;) {
                $x = rand(0, $n);
                $pass.= $caracteres[$x];
            }

            $this->_sendPassByEmail($this->get('email'), $pass);

            $savedPass = $this->user_model->save_temp_pass($this->get('email'), $pass);

            if ($savedPass) {

                $response = array('response' => true, 'result' => 'The password was send to ' . $this->get('email'));

                $this->response($response, 200);
            } else {
                $response = array('response' => false, 'result' => "The temp pass couln't be saved");

                $this->response($response, 404);
            }
        }
    }

    /**
     * Cambia la contraseña
     * @param userId
     * @param pass
     * @param newPass
     */
    public function changeUserPass_get() {
        $userId = $this->get('userId');
        $currentPass = $this->get('currentPass');
        $newPass = $this->get('newPass');

        if (empty($userId) || empty($currentPass) || empty($newPass)) {
            $response = array('response' => false, 'result' => 'All fields are required');
            $this->response($response, 404);
        } else {
            $data = array(
                'userId' => $userId,
                'currentPass' => $currentPass,
                'newPass' => $newPass
            );
            $wasChanged = $this->user_model->changeUserPass($data);

            if ($wasChanged) {
                $response = array('response' => true, 'result' => 'Password changed!');
                $this->response($response, 200);
            } else {
                $response = array('response' => false, 'result' => 'Incorrect password');
                $this->response($response, 404);
            }
        }
    }

    /**
     * Resetea la contraseña y la envia por email
     * @param email
     * @param tempPass
     * @param newPass
     * @param confirmPass
     */
    public function confirmForgottenPass_get() {     //Parametro: email, tempPass , newPass, confirmPass
        $noExistEmail = $this->user_model->exist_email($this->get('email'));    //pasamos el email al modelo para que compruebe si existe el email ya esta registrado. noExistEmail devuelve true si no existe el usuario

        if ($noExistEmail) {     // error no existe el email,
            $response = array('response' => false, 'result' => 'The email not exist');

            $this->response($response, 404);
        } else {
            $comparedTempPass = $this->user_model->compare_temp_pass($this->get('email'));

            $temppass = md5($this->get('tempPass'));

            if ($comparedTempPass[0]->tempPass != $temppass) {
                $response = array('response' => false, 'result' => 'The temp pass is incorrect');

                $this->response($response, 404);
            } else {
                if ($this->get('newPass') != $this->get('confirmPass')) {
                    $response = array('response' => false, 'result' => 'The passwords are different');

                    $this->response($response, 404);
                } else {
                    //borro el passord temporario

                    $this->user_model->empty_tempPass($this->get('email'));

                    //guardo el passoword nuevo
                    $savedPass = $this->user_model->save_new_pass($this->get('email'), $this->get('newPass'));

                    $response = array('response' => true, 'result' => 'The password was changed');

                    $this->response($response, 200);
                }
            }
        }
    }

    /**
     * Consulta de usuarios por email.
     * @param email
     */
    public function getUserByEmail_get() {
        $email = $this->get('email');
        $user = $this->user_model->getUserByEmail(array('email' => $email));
        if ($user) {
            $response = array('response' => true, 'result' => $user);
            $this->response($response, 200);
        } else {
            $response = array('response' => false, 'result' => $user);
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
            $response = array('response' => false, 'result' => false);
            $this->response($response, 404);
        }

        $avatar_img = $this->post('avatar_img');
        if (empty($avatar_img)) {
            $response = array('response' => false, 'result' => false);
            $this->response($response, 404);
        }

        $userId = $this->post('userId');
        if (empty($userId)) {
            $response = array('response' => false, 'result' => false);
            $this->response($response, 404);
        }

        $avatar_str = base64_decode($avatar_img);

        $path = $_SERVER['DOCUMENT_ROOT'].'/media/userfiles/' . $userId . '/';

        if (!file_exists($path)) {
            mkdir($path,0777);
        }

        $path = $_SERVER['DOCUMENT_ROOT'].'/media/userfiles/' . $userId . '/' . $file_data['file_name'];
        $avatar_file = fopen($path, "w+");
        fwrite($avatar_file, $avatar_str);
        fclose($avatar_file);

        if (file_exists($path)) {

            //Eliminaos todos los avatars de este usuario que existan
            $deleteAvatars = $this->user_model->deleteAvatar(array('idUser' => $userId));

            $args = array(
                'idUser' => $userId,
                'imagePath' => 'media/userfiles/' . $userId . '/' . $file_data['file_name'],
                'size' => '150'
            );

            //Insertamos en la base de datos el avatar
            $insertAvatar = $this->user_model->addAvatar($args);
            //Si la imagen se inserta en la base de datos, devuelves todo joya men
            if ($insertAvatar) {

                $options['full_path'] = $path;
                $options['width'] = 53;
                $options['height'] = 53;
                $options['thumb'] = TRUE;

                if ($this->functions->resizeAvatars($options)) {

                    $args = array(
                        'idUser' => $userId,
                        'imagePath' => 'media/userfiles/' . $userId . '/' . $file_data['raw_name'] . '_thumb.' . $this->functions->getTypeFile($file_data['file_type']),
                        'size' => '53'
                    );
                    //Insertamos en la base de datos el avatar
                    $insertAvatar = $this->user_model->addAvatar($args);
                }

                $avatars = $this->user_model->getAvatars(array('idUser' => $userId));
                if (!empty($avatars)) {

                    $response = array('response' => true, 'result' => $avatars);
                    $this->response($response, 200);
                } else {

                    $response = array('response' => false, 'result' => false);
                    $this->response($response, 404);
                }
            } else {

                $response = array('response' => false, 'result' => false);
                $this->response($response, 404);
            }
        } else {
            $response = array('response' => false, 'result' => false);
            $this->response($response, 404);
        }
    }

    /**
     * Get avatar
     * @param userId
     */
    public function getAvatars_get() {

        $userId = $this->get('userId');
        $avatars = $this->user_model->getAvatars(array('idUser' => $userId));

        if (!empty($avatars)) {
            $response = array('response' => true, 'result' => $avatars);
            $this->response($response, 200);
        } else {
            $response = array('response' => false, 'result' => false);
            $this->response($response, 404);
        }
    }

    /**
     * report_get
     * reporte de usuarios
     * @param userSender
     * @param userReported
     * @param offense
     * @param comment
     * @return [type] [description]
     */
    public function user_report_get() {
        $userSender = $this->get('userSender');
        $userReported = $this->get('userReported');
        $offense = $this->get('offense');
        $comment = $this->get('comment');
        $tournamentId = $this->get('tournamentId');
        $date = (date("Y-m-d H:i:s"));

        if(!empty($tournamentId) && $tournamentId){
            $tour = $tournamentId;
        }else{
            $tour = 0;
        }

        $data = array(
            'userSender' => $userSender,
            'userReported' => $userReported,
            'report_type' => $offense,
            'tournamentId' => $tour,
            'comment' => $this->sanitize_model->sanitize($comment),
            'date' => $date
        );

        $report = $this->user_model->reportUser($data);

        if ($report) {
            $response = array('response' => true, 'result' => true);
            $this->response($response, 200);
        } else {
            $response = array('response' => false, 'result' => false);
            $this->response($response, 404);
        }
    }

    /**
     * Get all report types
     */
    public function getReportTypes_get(){

        $types = $this->user_model->getReportTypes();

        if($types && !empty($types)){
            $response = array('response' => true, 'result' => $types);
            $this->response($response, 200);
        }else{
            $response = array('response' => false, 'result' => 'There are not report types');
            $this->response($response, 404);
        }
    }

    /***
     *
     * Get BattleproUser for a twitterID
     *
     * @param: twitterId
     */
    public function userByTwitterId_get(){

        $twitter_id = $this->get('twitterId');

        $user = $this->user_model->userByTwitterId($twitter_id);

        if($user){
            $response = array('response'=>true,'result'=>$user);
            $this->response($response, 200);
        }else{
            $response = array('response'=>false,'result'=>false);
            $this->response($response, 404);
        }

    }

    public function getUserByFacebookId_get(){
        $id = $this->get('id');

        $user = $this->user_model->userByFbId($id);

        if($user){
            $response = array('response'=>true,'result'=>$user);
            $this->response($response, 200);
        }else{
            $response = array('response'=>false,'result'=>false);
            $this->response($response, 404);
        }

    }

    /**
     * converts SteamID64 to SteamID
     * @param: steamId
     */
    public function steamId64toSteamId_get() {

        $steamId64 = $this->get('gameId');
        $offset = bcsub('', $steamId64);
        $id = bcdiv($offset, '2');
        if (bcmod($offset, '2')) {
            $steamid = bcadd($id, '2')*(-1);
            $steamid+=2;
            //$steamid = 'STEAM_0:1:' . $steamid;
        } else {
            $steamid = bcadd($id, '1')*(-1);
            $steamid+=1;
            //$steamid = "STEAM_0:0:" . $steamid;
        }
        $this->response($steamid);
    }

    /*
     * Get steam id of user
     */
    public function steamId_get(){
        $userId = $this->get('userId');
        $to64 = $this->get('to64');
        $steamId = $this->user_model->getSteamId(array('userId'=>$userId));
        if($steamId){
            if($to64){
                $steamId = $steamId->steamId;
                $split = explode(":", 'STEAM_0:1:'.$steamId); // STEAM_?:?:??????? format

                $x = substr($split[0], 6, 1);
                $y = $split[1];
                $z = $split[2];

                $steamId = ($z * 2) + 0x0110000100000000 + $y;
            }
            $this->response(array('response'=>true,'result'=>$steamId),'200');
        }
        $this->response(array('response'=>false),200);
    }

    /*
    * Desconectamos al usuario de todos los torneos en los que este registrado
    */
    public function disconnect_get(){
        $userId = $this->get('userId');
        if(!$userId){
            $this->response(array('response'=>false),200);
        }
        //Le decimos a los torneos que este usuario se fue
        $disconnect = $this->makeDisconnection($userId);

        if($disconnect){
            $this->response(array('response'=>$disconnect),200);
        }
        $this->response(array('response'=>false),200);

    }

    public function getNowPlaying_get(){
        $userId = $this->get('userId');

        $result = $this->user_model->getNowPlaying($userId);

        if($result){
            $response = array('response'=>true,'result'=>$result);
            $this->response($response, 200);
        }else{
            $response = array('response'=>false,'result'=>'This user is inactive right now...');
            $this->response($response, 200);
        }
    }

    /**
     * @param page
     * @return Object
     */
    public function getUsersList_get(){

        $page = $this->get('page');

        $users = null;

        if($page){

            $offset = ($page-1)*15;

            $users = $this->user_model->getUsersList($offset);
        }else{
            $users = $this->user_model->getUsersList(0);
        }

        if($users){
            $response = array('response'=>true,'result'=>$users);
            $this->response($response, 200);
        }else{
            $response = array('response'=>false,'result'=>'We have no users yet :( poor battlepro');
            $this->response($response, 200);
        }
    }

    /**
     * @param userId
     */
    public function getUserPreview_get(){

        $userId = $this->get('userId');
        $myId = $this->get('myId');

        $preview = $this->user_model->getUserPreview($userId,$myId);

        if($preview){
            $response = array('response'=>true,'result'=>$preview);
            $this->response($response, 200);
        }else{
            $response = array('response'=>false,'result'=>false);
            $this->response($response, 200);
        }
    }

    public function getLastPage_get(){
        $result = $this->user_model->getLastPage();
        $pages = ceil($result[0]->userCount / 15);

        $response = array('response'=>true,'result'=>$pages);
        $this->response($response, 200);
    }

    /**
     *
     * Adds view counter
     * @param userId
     *
     */
    public function addUserProfileView_get(){
        $myProfile = $this->get('my_profile');
        $idSession = $this->get('idSession');
        $profileViews = $this->get('profileViews');
        if(!empty($profileViews)){
            $added = $this->user_model->add_user_profile_view($myProfile,$idSession,$profileViews);
            if($added){
                $totalViews = $this->user_model->get_profile_views($profileViews);
                if($totalViews){
                    $response = array('response' => true, 'result' => $totalViews);
                    $this->response($response, 200);
                }
            }
            $response = array('response' => false, 'result' => 'An error ocurred');
            $this->response($response, 404);
        }else{
            $response = array('response' => false, 'result' => 'You must provide me an UserID');
            $this->response($response, 404);
        }
    }

    /**
     *
     * User Count
     *
     *
     */
    public function userCount_get(){
        $userCount = $this->user_model->user_count();
        if($userCount){
            $response = array('response' => true, 'result' => $userCount);
            $this->response($response, 200);
        }else{
            $response = array('response' => false, 'result' => 'An error ocurred');
            $this->response($response, 404);
        }
    }

    /**
     * Trae los datos para que el desktop haga el autologin
     * @param:device
     * @param:userId
     */

    public function getAutoLogin_get(){

        $this->load->library('encrypt');

        $device = $this->encrypt->decode($this->get('device'));
        $userId = $this->encrypt->decode($this->get('userId'));

        $result = $this->user_model->autologinData($userId,$device);

        if($result){
            $response = array('response' => true, 'result' => $result);
            $this->response($response, 200);
        }else{
            $response = array('response' => false, 'result' => 'Bad Credentials');
            $this->response($response, 404);
        }
    }

    public function getNewGlobalAnnounce_get(){

        $alert = $this->user_model->getNewGlobalAnnounce();

        if($alert){
            $response = array('response' => true, 'result' => $alert);
            $this->response($response, 200);
        }else{
            $response = array('response' => false, 'result' => 'There are no active announcements');
            $this->response($response, 404);
        }
    }
    public function newsletterSubscribe_get(){
        $userId = $this->get('userId');
        $subscribed = $this->newsletterSubscribe($userId);
        if($subscribed){
            $response = array('response' => true, 'result' => $subscribed);
            $this->response($response, 200);
        }else{
            $response = array('response' => false, 'result' => 'There was a problem');
            $this->response($response, 404);
        }
    }

    //Recibe userid O email, no ambos
    public function cancelSubscribe_get(){
        $userId = $this->get('userId');
        $email = $this->get('email');
        $subscribed = $this->cancelSubscribe($userId,$email);
        if($subscribed){
            $response = array('response' => true, 'result' => $subscribed);
            $this->response($response, 200);
        }else{
            $response = array('response' => false, 'result' => 'There was a problem');
            $this->response($response, 404);
        }
    }

    /**
     *
     */
    public function saveAutoAccessToken_get(){
        $this->load->library('encrypt');

        $userId = $this->get('userId');
        $token = $this->get('token');

        $save = $this->user_model->saveToken($this->encrypt->decode($userId),$this->encrypt->decode($token));

        if(!empty($save) && $save){
            $response = array('response' => true);
            $this->response($response,200);
        }else{
            $response = array('response' => false);
            $this->response($response,404);
        }
    }

    public function matchLoginToken_get(){

        $this->load->library('encrypt');

        $token = $this->get('token');

        $login = $this->user_model->loginByToken($this->encrypt->decode($token));

        if(!empty($login) && $login){
            $response = array('response' => true,'result'=>$login);
            $this->response($response,200);
        }else{
            $response = array('response' => false);
            $this->response($response,404);
        }
    }

    ////////////////////////// PRIVATE METHODOS ///////////////////
    private function makeDisconnection($userId){
        $this->load->model('tournaments_model');
        $disconnect = $this->tournaments_model->disconnect($userId);
        if($disconnect){
            return true;
        }
        return false;
    }

    private function newsletterSubscribe($userId){
        $this->load->model('suscribe_model');
        if(!empty($userId)){
            $isSubscribed = $this->suscribe_model->isSubscribed($userId);
            if($isSubscribed){
                $subscribe = $this->suscribe_model->newsletterSubscribe($userId);
                if($subscribe){
                    return true;
                }
            }
        }
        return false;
    }

    private function cancelSubscribe($userId,$email){
        $this->load->model('suscribe_model');
        $cancel = $this->suscribe_model->cancelSubscribe($userId,$email);
        if($cancel){
            return true;
        }
        return false;
    }
}
