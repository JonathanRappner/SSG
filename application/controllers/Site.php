<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Site extends CI_Controller
{
	public $pm_count;
	
	public function __construct()
	{
		parent::__construct();

		$this->load->model('site/pm_alert');

		//antal olästa pm
		if($this->member->valid)
			$this->pm_count = $this->pm_alert->get_pm_count($this->member->id);
	}

	public function index()
	{
		$this->news();
	}

	public function news()
	{
		//moduler
		$this->load->model('site/signup_box');
		$this->load->model('site/chat');
		$this->load->library('Attendance');

		//data
		$attendance_types = $this->attendance->get_all(); //till signupbox
		$chat_messages = $this->chat->get_messages(null, 16);
		$earliest_message_id = $this->chat->get_last_message_id(); //hämta tidigaste meddelandet i db så att js vet när den inte ska ladda fler

		//vy
		$this->load->view('site/news',
			array_merge((array)$this->signup_box->event,
				array('attendance_types' => $attendance_types, 'chat_messages' => $chat_messages, 'earliest_message_id' => $earliest_message_id)
			)
		);
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

	public function emblem()
	{
		//moduler
		// $this->load->model('');

		//data
		// $streamers = $this->streamers->get_streamers();

		//vy
		$this->load->view('site/emblem'/*, array('streamers'=>$streamers)*/);
	}

	public function logout()
	{
		$this->session->sess_destroy(); //förstör codeigniter-session
		redirect('forum/ucp.php?mode=logout&sid='. $this->member->phpbb_session_id);
	}

	public function test()
	{
		if($this->member->id != 1655) //Smorfty only
			show_404();
		
		// importera chat
		// $this->load->model('site/chat');
		// $this->chat->import_shouts();
	}
}
