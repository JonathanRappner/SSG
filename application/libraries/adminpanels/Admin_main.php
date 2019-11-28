<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Huvud-admin-panelen
 */
class Admin_main implements Adminpanel
{
	protected $CI;

	public function __construct()
	{
		// Assign the CodeIgniter super-object
		$this->CI =& get_instance();
	}

	public function main($var1, $var2, $var3)
	{
		
	}

	public function view()
	{
		echo '<h5>Välj en admin-panel till vänster.</h5>';
		echo (count($this->CI->member->permission_groups) == 1)
			? '<p>Din rättighetsgrupp från forumet:</p>'
			: '<p>Dina rättighetsgrupper från forumet:</p>';
		echo '<ul>';
		
		foreach($this->CI->member->permission_groups as $perm)
			echo "<li>{$perm->name}</li>";

		echo '</ul>';
	}

	public function get_title()
	{
		return 'Hem';
	}

	public function get_code()
	{
		return 'main';
	}

	public function get_permissions_needed()
	{
		//åtkomst: alla admins
		return array('s0', 's1', 's2',  's3', 's4', 'grpchef');
	}
}
?>