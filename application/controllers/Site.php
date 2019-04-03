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
		//moduler
		$this->load->model('api/streamers');

		//data
		$streamers = $this->streamers->get_streamers();

		//vy
		$this->load->view('site/streamers', array('streamers'=>$streamers));
	}

	public function test()
	{
		if($this->member->id != 1655) //Smorfty only
			show_404();
		
		
	}
}
