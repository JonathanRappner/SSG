<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Hanterar rättigheter
 */
class Permissions
{
	protected $CI;
	private $permission_groups;

	public function __construct()
	{
		// Assign the CodeIgniter super-object
		$this->CI =& get_instance();

		$this->load_permissions();
	}

	/**
	 * Ladda och spara rättighetsgrupper från databasen.
	 *
	 * @return void
	 */
	private function load_permissions()
	{
		$sql =
			'SELECT *
			FROM ssg_permission_groups';
		$query = $this->CI->db->query($sql);
		foreach($query->result() as $row)
			$this->permission_groups[] = $row;
	}

	/**
	 * Kolla rättighetsgrupper med koder.
	 *
	 * @param mixed $permissions_code Sträng eller sträng-array med rättighetsgruppskoder.
	 * @param int $member_id Användare att kolla. Lämna blank för att kolla inloggade medlemmen.
	 * @return boolean
	 */
	public function has_permissions($permissions_code, $member_id = null)
	{
		// grupper att klipp-o-klistra
		// array('super', 's0', 's1', 's2',  's3', 's4', 'grpchef')
		// array('super', 's0')

		//input-sanering
		assert(!empty($permissions_code));

		//Om $permissions inte är en array, gör om den till en.
		if(!is_array($permissions_code))
			$permissions_code = array($permissions_code);
		
		//gå igenom alla koder och översätt till id-nummer
		foreach($permissions_code as $perm)
		{
			//försök att ladda permission-objekt med kod
			$loaded_perm = $this->get_by_code($perm);
			
			//assert:a att det lyckades
			assert(isset($loaded_perm->id), "Inkorrekt permission-group-kod: $perm");
			
			//stoppa in id i lista
			$permissions_id[] =  $loaded_perm->id;
		}

		//hämta medlems rättighetsgrupper
		$member_permissions = empty($member_id)
			? $this->CI->member->permission_groups //ladda inloggade medlemmens data
			: $this->CI->member->get_member_data($member_id)->permission_groups; //ladda specifierade medlemmens data

		return !empty(array_intersect($permissions_id, $member_permissions));
	}

	/**
	 * Hämtar rättighetsgrupp efter id.
	 *
	 * @param int $id
	 * @return object
	 */
	public function get_by_id($id)
	{
		foreach($this->permission_groups as $permission_group)
			if($permission_group->id == $id)
				return $permission_group;
	}

	/**
	 * Hämtar rättighetsgrupp efter kod.
	 *
	 * @param string $code
	 * @return object
	 */
	public function get_by_code($code)
	{
		foreach($this->permission_groups as $permission_group)
			if($permission_group->code == $code)
				return $permission_group;
	}
}
?>