<?php
/**
* <sumary>
* Modelo del los torneos.
* </sumary>
**/


class Devices_model extends CI_Model {

    function __construct()
    {
        // Call the Model constructor
        parent::__construct();
    }

    /*
     * Traemos los devices del usuerio
     */
    public function getDevices($opt=array()){

        $this->db->from('bp_user_device');

        if(isset($opt['idDevice']) and !empty($opt['idDevice'])){
            $this->db->where('idDevice',$opt['idDevice']);
        }

        if(isset($opt['idUser']) and !empty($opt['idUser'])){
            $this->db->where('idUser',$opt['idUser']);
        }

        if(isset($opt['macAddress']) and !empty($opt['macAddress'])){
            $this->db->where('macAddress',$opt['macAddress']);
        }

        if(isset($opt['processor']) and !empty($opt['processor'])){
            $this->db->where('processor',$opt['processor']);
        }

        $query = $this->db->get();

        if($query->num_rows() > 0){
            return $query->result();
        }
        return false;
    }

    /*
     * Guardamos los devices del usuario
     */
    public function addNewDevice($opt=array()){
        if(isset($opt['idUser']) and !empty($opt['idUser'])){
            $new = $this->db->insert('bp_user_device',$opt);
            if($new){
                $idDevice = $this->db->insert_id();
                $devices = $this->getDevices(array('idDevice'=>$idDevice));
                return $devices;
            }
        }
    }

    /*
     * Update device
     */
    public function updateDevice($opt=array(),$idDevice){

        if(isset($opt['idUser']) and !empty($opt['idUser'])){
            $devices = $this->getDevices(array('idUser'=>$opt['idUser']));
            if($devices){
                $this->db->update('bp_user_device',$opt,$idDevice);
                $devices = $this->getDevices(array('idDevice'=>$idDevice));
                return $devices;
            }
        }
    }
    
    public function checkDeviceStatus($device)			
    {
		$query = $this->db->get_where('bp_devices',array('name' => $device));
		return $query->row()->status;		
    }
	
	public function logout_device($device){
		$data = array('status'=>0);
		$this->db->update('bp_devices',$data, "idDevice = 1");
	}
	
	public function login_device($device){
		$data = array('status'=>1);
		$this->db->update('bp_devices',$data, "idDevice = 1");
	}
	
	public function launch_game($device){
		$data = array('cod4'=>1);
		$this->db->update('bp_devices',$data, "idDevice = 1");
	}
	
	public function block_game($device){
		$data = array('cod4'=>0);
		$this->db->update('bp_devices',$data, "idDevice = 1");
	}
	
	public function checkDeviceGame($device)			
    {
		$query = $this->db->get_where('bp_devices',array('name' => $device));
		return $query->row()->cod4;		
    }
}