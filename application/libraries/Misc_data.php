<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Laddar och tillhandahåller smådata angående:
 * Grupper
 * Befattningar
 * Ranger
 */
class Misc_data
{
	protected $CI;
	private
		$groups = array(),
		$roles = array(),
		$ranks = array(),
		$event_types = array();

	public function __construct()
	{
		// Assign the CodeIgniter super-object
		$this->CI =& get_instance();

		//ladda data
		$this->get_db_data();

		// echo '<pre>';
		// print_r($this->groups);
		// print_r($this->roles);
		// print_r($this->ranks);
		// echo '</pre>';
	}

	/**
	 * Laddar data från db
	 *
	 * @return array
	 */
	private function get_db_data()
	{
		//variabler
		$groups = array();
		$roles = array();
		$ranks = array();
		$groups_roles = array();

		//grupper
		$sql =
			'SELECT id, name, code, dummy
			FROM ssg_groups
			WHERE
				active
			ORDER BY sorting ASC';
		$query = $this->CI->db->query($sql);
		foreach($query->result() as $row)
		{
			$row->dummy == $row->dummy > 0;
			$groups[] = $row;
		}
		$this->groups = $groups;

		//befattningar
		$sql =
			'SELECT id, name, name_long
			FROM ssg_roles
			ORDER BY sorting ASC';
		$query = $this->CI->db->query($sql);
		foreach($query->result() as $row)
			$roles[] = $row;
		$this->roles = $roles;

		//ranger
		$sql =
			'SELECT id, name, icon
			FROM ssg_ranks
			ORDER BY sorting ASC';
		$query = $this->CI->db->query($sql);
		foreach($query->result() as $row)
			$ranks[] = $row;
		$this->ranks = $ranks;

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
		$query = $this->CI->db->query($sql);
		foreach($query->result() as $row)
		{
			//hitta grupp med $row->id
			foreach($groups as &$group)
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

		//event_types
		$sql =
			'SELECT id, title
			FROM ssg_event_types
			ORDER BY id ASC';
		$query = $this->CI->db->query($sql);
		foreach($query->result() as $row)
			$event_types[] = $row;
		$this->event_types = $event_types;
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

	/**
	 * Hämta event-typer
	 *
	 * @return array
	 */
	public function get_event_types()
	{
		return $this->event_types;
	}
}
?>