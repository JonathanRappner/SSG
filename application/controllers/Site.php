<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Site extends CI_Controller
{
	public $pm_count;
	
	public function __construct()
	{
		parent::__construct();

		$this->load->model('site/pm_alert');
		$this->pm_count = $this->pm_alert->get_pm_count($this->member->id); //antal olästa pm
	}

	public function index()
	{
		//moduler
		$this->load->model('site/signup_box');
		$this->load->library('Attendance');

		//data
		$attendance_types = $this->attendance->get_all(); //till signupbox
		

		//vy
		$this->load->view('site/news', array_merge((array)$this->signup_box->event, array('attendance_types' => $attendance_types)));
	}

	public function members()
	{
		//moduler
		$this->load->model('site/pm_alert');

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
