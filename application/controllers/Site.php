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
}
