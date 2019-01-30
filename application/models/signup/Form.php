<?php
/**
 * Modell för Anmälningsformuläret.
 */
class Form extends CI_Model
{
	private 
		$groups,
		$roles,
		$groups_roles;


	public function __construct()
	{
		parent::__construct();

		//grupper
		$sql =
			'SELECT id, name, code, dummy
			FROM ssg_groups
			WHERE
				active
			ORDER BY sorting ASC';
		$query = $this->db->query($sql);
		foreach($query->result() as $row)
		{
			$row->dummy == $row->dummy > 0;
			$this->groups[] = $row;
		}

		//befattningar
		$sql =
			'SELECT id, name, name_long
			FROM ssg_roles
			ORDER BY sorting ASC';
		$query = $this->db->query($sql);
		$this->roles = $query->result();

		//groups_roles
		$sql =
			'SELECT
				ssg_roles_groups.group_id AS group_id,
				ssg_roles.id AS role_id,
				ssg_roles.name AS role_name,
				ssg_roles.name_long AS role_name_long
			FROM ssg_roles_groups
			INNER JOIN ssg_roles
				ON ssg_roles_groups.role_id = ssg_roles.id
			ORDER BY
				ssg_roles.sorting ASC';
		$query = $this->db->query($sql);
		foreach($query->result() as $row)
		{
			//hitta grupp med $row->id
			foreach($this->groups as &$group)
				if($group->id == $row->group_id)
				{
					if(!isset($group->roles)) //skapa ny array om det behövs
						$group->roles = array();

					//använd långa namn om de finns (FAC => Forward Air Controller)
					$group->roles[$row->role_id] = isset($row->role_name_long)
						? "$row->role_name ($row->role_name_long)"
						: $row->role_name;
				}
		}
	}

	
	/**
	 * Hämta grupper (EA, FA, TL, osv.)
	 *
	 * @return array
	 */
	public function get_groups()
	{
		return $this->groups;
	}

	/**
	 * 
	 *
	 * @return array
	 */
	public function get_groups_roles()
	{
		return $this->groups;
	}

	/**
	 * Hämta befattningar (Plutonschef, KSP, osv.)
	 *
	 * @return array
	 */
	public function get_roles()
	{
		return $this->roles;
	}

	/**
	 * Hämta ranger (Menig kl. 1, Furir, osv.)
	 *
	 * @return array
	 */
	public function get_ranks()
	{
		return $this->ranks;
	}

}