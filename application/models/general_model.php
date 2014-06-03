<?php
/**
* <sumary>
* Modelo para cosas generales de todo el sitio
* </sumary>
**/


class General_model extends CI_Model {

    function __construct()
    {
        // Call the Model constructor
        parent::__construct();
    }
    
	//Trae los datos del achievement 
    function get_countries($countryId='')						
    {
		$this->db->from('bp_country');
		$this->db->select('idCountry,countryName');
		//Si viene el id del pais filtramos la lista a ese especifico
		if(!empty($countryId))$this->db->where('idCountry',$countryId);
		
		if(!empty($countryId)){
			$countries = $this->db->get()->row();
		}else{
			$countries = $this->db->get()->result();
		}

		return $countries;
		
    }
}