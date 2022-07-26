<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Site extends CI_Controller
{
	public $pm_count, $alerts;
	
	public function __construct()
	{
		parent::__construct();

		//hämta globala meddelanden
		$this->load->model('site/global_alerts');
		$this->global_alerts = $this->global_alerts->get_alerts();
		
		//första april
		if(APRIL_FOOLS)
			$this->load->library('holidays/april_fools');
	}

	public function index()
	{
		$this->news();
	}

	public function news($page = 0)
	{
		//moduler
		$this->load->model('site/news');
		$this->load->model('site/signup_box');
		$this->load->model('site/chat');
		$this->load->library('Attendance');

		//data
		$news = $this->news->get_news($page, 5);
		$attendance_types = $this->attendance->get_all(); //till signupbox

		if($this->member->valid) //data för inloggade användare
		{
			//data för antagna medlemmar
			if($this->permissions->has_permissions(array('rekryt', 'medlem', 'inaktiv')))
			{
				//chat
				$chat_messages = $this->chat->get_messages(null, 16);
				$earliest_message_id = $this->chat->get_last_message_id(); //hämta tidigaste meddelandet i db så att js vet när den inte ska ladda fler
			}
			else
				$chat_messages = $earliest_message_id = null;

			//data för inloggade användare
			$posts = $this->news->get_latest_posts();
		}
		else
			$chat_messages = $earliest_message_id = $posts = null;

		//vy
		$this->load->view(
			'site/news',
			array
			(
				'news' => $news,
				'page' => $page,
				'next_event' => $this->signup_box->get_upcomming_event($this->member->valid ? $this->member->permission_groups : null),
				'other_events' => $this->signup_box->get_other_events($this->member->valid ? $this->member : null),
				'attendance_types' => $attendance_types,
				'chat_messages' => $chat_messages,
				'earliest_message_id' => $earliest_message_id,
				'posts' => $posts,
				'global_alerts' => $this->global_alerts,
			)
		);
	}

	public function members()
	{
		//moduler
		$this->load->model('site/members');

		//data
		$groups_skytte = $this->members->get_groups(false);
		$groups_enablers = $this->members->get_groups(true);

		//vy
		$this->load->view('site/members', array(
			'global_alerts' => $this->global_alerts,
			'groups_skytte' => $groups_skytte,
			'groups_enablers' => $groups_enablers,
		));
	}

	public function streamers()
	{
		//moduler
		$this->load->model('api/streamers');

		//data
		$streamers = $this->streamers->get_streamers();

		//vy
		$this->load->view('site/streamers', array('streamers' => $streamers, 'global_alerts' => $this->global_alerts));
	}

	public function emblem()
	{
		//moduler
		// $this->load->model('');

		//data
		// $streamers = $this->streamers->get_streamers();

		//vy
		$this->load->view('site/emblem', array('global_alerts' => $this->global_alerts));
	}

	public function murkel()
	{
		$this->load->library('holidays/april_fools');
		
		echo '<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">';
		echo '<h1>Murkels hörna</h1>';
		echo '<h1>'. $this->april_fools->random_emojis(microtime(), 3) .'</h2>';
		echo '<a href="murkel" class="btn btn-success mt-4">Ladda om</a>';
	}

	public function logout()
	{
		$this->session->sess_destroy(); //förstör codeigniter-session
		redirect('forum/ucp.php?mode=logout&sid='. $this->member->phpbb_session_id);
	}
}
