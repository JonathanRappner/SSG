<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Hanterar rättigheter
 */
class Permissions
{
	protected $CI;
	private $permission_groups, $permission_codes;

	public function __construct()
	{
		// Assign the CodeIgniter super-object
		$this->CI =& get_instance();

		//hämta rättighetsgrupper
		$this->permission_groups = $this->CI->db->query('SELECT group_id id, group_name title FROM phpbb_groups')->result();

		//sätt snabbkoder
		$this->get_by_id(2)->code = 'reg';
		$this->get_by_id(5)->code = 'super';
		$this->get_by_id(8)->code = 's0';
		$this->get_by_id(9)->code = 's1';
		$this->get_by_id(10)->code = 's2';
		$this->get_by_id(11)->code = 's3';
		$this->get_by_id(12)->code = 's4';
		$this->get_by_id(17)->code = 's5';
		$this->get_by_id(13)->code = 'medlem';
		$this->get_by_id(14)->code = 'rekryt';
		$this->get_by_id(15)->code = 'inaktiv';
		$this->get_by_id(16)->code = 'grpchef';
	}

	/**
	 * Kolla rättighetsgrupper med koder.
	 *
	 * @param mixed $permission_codes Sträng eller sträng-array med rättighetsgruppskoder.
	 * @param int $member_id Användare att kolla. Lämna blank för att kolla inloggade medlemmen.
	 * @return boolean
	 */
	public function has_permissions($permission_codes, $member_id = null)
	{
		//input-sanering
		assert(!empty($permission_codes));

		//variabler
		$administrator_permission_id = 5;

		//ska kolla rättigheter mot inloggade användaren men är inte inloggad
		if($member_id == null && !$this->CI->member->valid)
			return false;

		//Om $permissions inte är en array, gör om den till en.
		if(!is_array($permission_codes))
			$permission_codes = array($permission_codes);
		
		//gå igenom alla koder och översätt till id-nummer
		$permission_ids = array();
		foreach($permission_codes as $code)
			$permission_ids[] = $this->get_by_code($code)->id;

		//hämta medlems rättighetsgrupper
		$member_permissions = $member_id //är medlemen specifierad?
			? $this->CI->member->get_member_data($member_id)->permission_groups
			: $this->CI->member->permission_groups; //ladda inloggade medlemmens data

		//iterera genom alla medlemmens permission groups
		foreach($member_permissions as $member_permission)
		{
			if($member_permission->id == $administrator_permission_id) //om admin = alltid true
				return true;
			else if(in_array($member_permission->id, $permission_ids)) //kolla om behörighetsgrupp-id:t finns bland $permission_ids
				return true;
		}

		return false;
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
			if(isset($permission_group->code) && $permission_group->code == $code)
				return $permission_group;
	}

	/**
	 * Hämtar alla rättighetsgrupper.
	 *
	 * @return array
	 */
	public function get_permissions()
	{
		return $this->permission_groups;
	}
}
?>