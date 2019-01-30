<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Hanterar events
 */
class Admin_groups implements Adminpanel
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
		echo 
			'<div class="alert alert-info alert-dismissible fade show" role="alert">
				Denna admin-panel är inte implementerad ännu.
			</div>';
		
		echo 'Här kommer du kunna administrera SSG:s grupper/enheter.';
	}

	public function get_code()
	{
		return 'grouproles';
	}

	public function get_title()
	{
		return 'Grupper';
	}

	public function get_permissions_needed()
	{
		return array('s0', 'grpchef');
	}
}
?>