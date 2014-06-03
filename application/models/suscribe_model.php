<?php
/*
Modelo para suscribe desde comming soon
*/
class suscribe_model extends CI_Model {

	function __construct()
	{
		parent::__construct();
	}

    /*
     * Suscribe new email into database
     */
	public function suscribe($email)
	{
        if(!empty($email)){
            if($this->checkEmail($email)){
                return false;
            }else{
                $args = array('email'=>$email,'susDate'=>date('Y-m-d h:i:s',time()));
                $insert = $this->db->insert('bp_suscriptors',$args);

                if($insert){
                    return true;
                }else{
                    return false;
                }
            }
        }else{
            return false;
        }
	}
    /**
     * Subscribo a un usuario al newsletter
     * @param userId
     */
    public function newsletterSubscribe($userId){
        $data = array(
            'idUser'=>$userId,
            'meta_key'=>'subscribed',
            'meta_value'=>'1'
        );
        $this->db->insert('bp_usermeta',$data);
        return true;
    }

    public function isSubscribed($userId){
        $this->db->from('bp_usermeta');
        $this->db->where('idUser',$userId);
        $this->db->where('meta_key','subscribed');
        $this->db->where('meta_value','1');
        $subscribed = $this->db->get();
        if($subscribed->num_rows() == 0){
            return true;
        }else{
            return false;
        }
    }

    public function cancelSubscribe($userId,$email){
        if(!empty($userId)and empty($email)){
            $this->db->where('idUser',$userId);
            $this->db->where('meta_key','subscribed');
            $this->db->where('meta_value','1');
            $this->db->delete('bp_usermeta');
            return true;
        }elseif(!empty($email)and empty($userId)){
            $this->db->where('email',$email);
            $this->db->delete('bp_suscriptors');
            return true;
        }
        return false;
    }

    /*
     * Check if email exists in database
     */
    private function checkEmail($email){
        $this->db->from('bp_suscriptors');
        $this->db->where('email',$email);
        $result = $this->db->get();

        if($result->num_rows() > 0){
            return true;
        }else{
            return false;
        }
    }
}