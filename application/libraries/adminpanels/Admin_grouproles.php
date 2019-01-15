<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Hanterar events
 */
class Admin_grouproles implements Adminpanel
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
		
		echo 'Här kommer du kunna välja vilka befattningar som man kan anmäla sig till i specifika grupper.<br />T.ex. inga KSPs i November Lima';
	}

	public function get_code()
	{
		return 'grouproles';
	}

	public function get_title()
	{
		return 'Gruppbefattningar';
	}

	public function get_permissions_needed()
	{
		return array('super', 's0', 'grpchef');
	}
}
?>