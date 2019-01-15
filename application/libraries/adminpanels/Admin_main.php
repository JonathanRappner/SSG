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

	public function main($var1, $var2)
	{
		
	}

	public function view()
	{
		echo '<h5>Välj en admin-panel till vänster.</h5>';
		echo '<p>Din(a) rättighetsgrupp(er):</p>';
		echo '<ul>';

		foreach($this->CI->member->permission_groups as $perm_id)
			echo '<li>'. $this->CI->permissions->get_by_id($perm_id)->title .'</li>';

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
		return array('super', 's0', 's1', 's2',  's3', 's4', 'grpchef');
	}
}
?>