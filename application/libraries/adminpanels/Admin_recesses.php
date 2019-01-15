<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Hanterar uppehållsperioder
 */
class Admin_recesses implements Adminpanel
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
		
		echo 'Här kommer du kunna administrera perioder där hemsidan inte skapar automatiska events.';
	}

	public function get_code()
	{
		return 'recesses';
	}

	public function get_title()
	{
		return 'Uppehållsperioder';
	}

	public function get_permissions_needed()
	{
		return array('super', 's0');
	}
}
?>