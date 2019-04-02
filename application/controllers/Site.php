<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Site extends CI_Controller
{
	public function index()
	{
		//data
		$this->load->model('site/signup_box');
		$this->load->library('Attendance');
		$attendance_types = $this->attendance->get_all();

		//vy
		$this->load->view('site/news', array_merge((array)$this->signup_box->event, array('attendance_types' => $attendance_types)));
	}

	public function members()
	{
		//vy
		$this->load->view('site/members');
	}

	public function streamers()
	{
		//data
		$sql =
			'SELECT
				s.*,
				m.name,
				g.name group_name,
				r.name role_name
			FROM ssg_streamers s
			INNER JOIN ssg_members m
				ON s.member_id = m.id
			LEFT OUTER JOIN ssg_groups g
				ON m.group_id = g.id
			LEFT OUTER JOIN ssg_roles r
				ON m.role_id = r.id
			ORDER BY m.name ASC';
		$streamers = $this->db->query($sql)->result();

		//vy
		$this->load->view('site/streamers', array('streamers'=>$streamers));
	}

	public function test()
	{
		if($this->member->id != 1655) //Smorfty only
			show_404();
		
		
	}
}
