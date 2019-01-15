<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Hanterar medlemmar
 */
class Admin_members implements Adminpanel
{
	protected $CI;
	// private $foo;

	public function __construct()
	{
		// Assign the CodeIgniter super-object
		$this->CI =& get_instance();
	}

	public function main($var1, $var2)
	{
		// $this->foo = $var1;
	}

	public function view()
	{
		echo 
			'<div class="alert alert-warning alert-dismissible fade show" role="alert">
				Denna admin-panel är inte färdiutvecklad ännu.
			</div>';
		
		$query = $this->CI->db->query('SELECT id, name FROM ssg_members ORDER BY name ASC');
		echo '<ul>';
		foreach($query->result() as $row)
			echo "<li><a href='". base_url("/signup/mypage/$row->id") ."'>$row->name</a></li>";
		echo '</ul>';
	}

	public function get_code()
	{
		return 'members';
	}

	public function get_title()
	{
		return 'Medlemmar';
	}

	public function get_permissions_needed()
	{
		//åtkomst: admins
		return array('super', 's0', 's1', 's2',  's3', 's4', 'grpchef');
	}
}
?>