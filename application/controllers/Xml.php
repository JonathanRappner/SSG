<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Xml extends CI_Controller
{
	
	public function index($group_string)
	{
		// variabler
		$group_code = str_replace('.xml', null, $group_string); // $group_string kan vara "vl" eller "vl.xml", gör om till "vl"
		if(!$this->db->query('SELECT COUNT(*) AS count FROM ssg_groups WHERE code = ?', $group_code)->row()->count)
			die("Invalid group code: {$group_code}");

		$members = $this->get_members($group_code);
		$group_name = $this->db->query('SELECT name FROM ssg_groups WHERE code = ?', $group_code)->row()->name;

		// vy
		$this->load->view('xml/arma_xml', array('group_code' => $group_code, 'group_name' => $group_name, 'members' => $members));
	}

	
	/**
	 * Hämta gruppens medlemmar
	 * @param string $group_code
	 * 
	 * @return array
	 */
	private function get_members($group_code)
	{
		$sql =
			'SELECT m.name, m.uid, g.code
			FROM ssg_members m
			INNER JOIN ssg_groups g
				ON m.group_id = g.id
			WHERE 
				g.code = ?
				AND m.uid # endast de som har uid
			ORDER BY m.id ASC';
		return $this->db->query($sql, $group_code)->result();
	}
}