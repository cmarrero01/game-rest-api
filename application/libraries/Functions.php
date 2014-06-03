<?php

class Functions {

    public function __construct() {
        $this->ci = & get_instance();
        $this->ci->load->model('inbox_model');
        $this->ci->load->model('user_model');
        $this->ci->config->load('email');
    }

    /**
     * Functions for merge
     *
     * @access     public
     * @return     void
     * */
    public function merge($default, $options, $array = FALSE) {
        if (is_array($options)) {
            $settings = array_merge($default, $options);
        } else {
            parse_str($options, $output);
            $settings = array_merge($default, $output);
        }

        return ($array) ? $settings : (Object) $settings;
    }

    /**
     * Functions for save notifications
     *
     * @access     public
     * @return     void
     * */
    public function addNotification($options = array()) {
        $default['sendEmail'] = false;
        $default['idUserReceived'] = '';
        $default['idUserSender'] = 0;
        $default['messageStatus'] = 0;
        $default['inResponseTo'] = 0;
        $default['email_subject'] = '';
        $default['view_message'] = '';
        $set = $this->merge($default, $options);


        //@TODO: Validar lo que entra para asegurarnos que la notificacion este correcta.
        $args = array(
            'idUserReceived' => $set->idUserReceived,
            'idUserSender' => $set->idUserSender,
            'messageStatus' => $set->messageStatus,
            'topic' => $set->topic,
            'message' => $set->message,
            'inResponseTo' => $set->inResponseTo,
            'senderDate' => date('Y-m-d h:i:s', time())
        );

        $this->ci->inbox_model->addMessage($args);

        if($set->sendEmail){
            
            $userReceivedData = $this->ci->user_model->get_user_data($set->idUserReceived);
            $this->ci->email->from('notifications@battlepro.com', 'Battle Pro');
            $this->ci->email->to($userReceivedData->email);

            if(isset($set->email_subject) and empty($set->email_subject)) {
                $subject = 'You have a new notification in your inbox';
            } else {
                $subject = $set->email_subject;
            }

            $data = array(
                'web_url'=>$this->ci->config->item('webUrl'),
                'image_url'=>$this->ci->config->item('imagesUrl'),
                'message' => $set->message
            );
            if(isset($set->view_message)and empty($set->view_message)) {
                $message = $this->ci->load->view('emails/notifications/notification', $data, true);
            } else {
                $message = $this->ci->load->view($set->view_message, $data, true);
            }

            $this->ci->email->subject($subject);
            $this->ci->email->message($message);
            $this->ci->email->send();
        }

        return true;
    }
    
    public function addInvitationClanTeam($options = array()) {
        $default['sendEmail'] = '';
        $default['idUserReceived'] = '';
        $default['idUserSender'] = 0;
        $default['messageStatus'] = 0;
        $default['inResponseTo'] = 0;
        $default['email_subject'] = '';
        $default['view_message'] = '';
        $set = $this->merge($default, $options);


        //@TODO: Validar lo que entra para asegurarnos que la notificacion este correcta.
        $args = array(
            'idUserReceived' => $set->idUserReceived,
            'idUserSender' => $set->idUserSender,
            'messageStatus' => $set->messageStatus,
            'topic' => $set->topic,
            'message' => $set->message,
            'inResponseTo' => $set->inResponseTo,
            'senderDate' => date('Y-m-d h:i:s', time())
        );

        $this->ci->inbox_model->addMessage($args);
    
        return true;
    }

    /*
     *
     *
     * Resize images
     *
     *
     */

    public function resizeAvatars($options = array()) {
        $default['full_path'] = '';
        $default['width'] = '';
        $default['height'] = '';
        $default['thumb'] = '';
        $default['thumb'] = '';
        $set = $this->merge($default, $options);

        $config['image_library'] = 'gd2';
        $config['source_image'] = $set->full_path;
        $config['create_thumb'] = $set->thumb;
        $config['maintain_ratio'] = TRUE;
        $config['width'] = $set->width;
        $config['height'] = $set->height;

        $this->ci->load->library('image_lib', $config);

        if (!$this->ci->image_lib->resize()) {
            return $this->ci->image_lib->display_errors();
        } else {
            return true;
        }
    }

    /*
     *
     * Get type of image
     *
     *
     */

    public function getTypeFile($file_type) {
        $type = explode('/', $file_type);
        if ($type[1] == 'jpeg') {
            $type[1] = 'jpg';
        }
        return $type[1];
    }

    /**
     * <sumary>
     * // Sanitizing Strings 
     * </sumary>
     * */
    public function cleanInput($input) {

        $search = array(
            '@<script[^>]*?>.*?</script>@si', // Strip out javascript
            '@<[\/\!]*?[^<>]*?>@si', // Strip out HTML tags
            '@<style[^>]*?>.*?</style>@siU', // Strip style tags properly
            '@<![\s\S]*?--[ \t\n\r]*>@'         // Strip multi-line comments
        );

        $output = preg_replace($search, '', $input);
        return $output;
    }

    /**
     * <sumary>
     * // Sanitizing for DB
     * // Uses cleanInput() and adds slashes as to not mess with the DB functions
     * </sumary>
     * */
    public function sanitize($input) {
        if (is_array($input)) {
            foreach ($input as $var => $val) {
                $output[$var] = sanitize($val);
            }
        } else {
            if (get_magic_quotes_gpc()) {
                $input = stripslashes($input);
            }
            $input = $this->cleanInput($input);
            $output = mysql_real_escape_string($input);
        }
        return $output;
    }

    public function echoArray($array, $title = "") {
        echo "<pre>";
        if ($title != "")
            echo "<h3>$title</h4>";
        print_r($array);
        echo "</pre>";
    }

}