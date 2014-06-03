<?php
/**
* <sumary>
* Modelo del los achievements.
* </sumary>
**/


class Achievements_model extends CI_Model {

    function __construct()
    {
        // Call the Model constructor
        parent::__construct();
    }

	//Trae los datos del achievement
    function get_achievement($achievementId)
    {
		$this->db->select('achivUserWinId, bp_userachievements.userId, winDate, nicename,full_name,achivId');

		$this->db->join('bp_users', 'bp_userachievements.userId = bp_users.userId');

		$query2 = $this->db->get_where('bp_userachievements',array('achievId' => $achievementId));

		$userachievements = $query2->result();


		$query = $this->db->get_where('bp_achievements',array('achivId' => $achievementId));

		$achievementData = $query->result();

		$achievementData[0]->userAchievements =$userachievements;

		if ($query->num_rows() > 0)
		{
		   $achievementData = $achievementData;
		}
		else
		{
			$achievementData = false;
		}

		return $achievementData;

    }

	//Trae los achievement de ganados por el usuario
    function get_user_achievement($userId, $limit)
    {
		$this->db->select('achivId, winDate, bpPoints ,imagePath,name,description,status');

		$this->db->join('bp_achievements', 'bp_achievements.achivId = bp_userachievements.achievId');

        if($limit != 0)
        {
            $this->db->limit($limit);
        }

		$query = $this->db->get_where('bp_userachievements',array('userId' => $userId));

		if ($query->num_rows() > 0)
		{
		   $achievementData = $query->result();
		}
		else
		{
			$achievementData = false;
		}

		return $achievementData;
	}

	/*
	* Lista de todos los achievements existentes
	*/
	public function get_list_achievements(){

		$this->db->from('bp_achievements');
		$this->db->select('achivId,imagePath,bpPoints,name,description,status');
		$query = $this->db->get();

		if ($query->num_rows() > 0)
		{
		   $list = $query->result();
		}
		else
		{
			$list = false;
		}

		return $list;
	}

}