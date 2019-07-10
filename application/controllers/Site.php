<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Site extends CI_Controller
{
	public $pm_count, $alerts;
	
	public function __construct()
	{
		parent::__construct();

		$this->load->model('site/global_alerts');

		//hämta viktiga meddelanden
		$this->global_alerts = $this->global_alerts->get_alerts();
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
		$chat_messages = $this->chat->get_messages(null, 16);
		$earliest_message_id = $this->chat->get_last_message_id(); //hämta tidigaste meddelandet i db så att js vet när den inte ska ladda fler
		$posts = $this->news->get_latest_posts();

		//vy
		$this->load->view(
			'site/news',
			array
			(
				'news' => $news,
				'page' => $page,
				'next_event' => $this->signup_box->get_upcomming_event(),
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

	public function logout()
	{
		$this->session->sess_destroy(); //förstör codeigniter-session
		redirect('forum/ucp.php?mode=logout&sid='. $this->member->phpbb_session_id);
	}

	public function debug()
	{
		if($this->member->id != 1655) //Smorfty only
			show_404();
		
		//--importera chat--
		// $this->load->model('site/chat');
		// $latest_chat = $this->db->query('SELECT created FROM ssg_chat ORDER BY created DESC LIMIT 1')->row()->created;
		// $this->chat->import_shouts($latest_chat);


		//--använd /forum/import.php för forum-imports--


		//--kolla SMF-topics som även finns i phpBB och har nyare posts än phpBB-varianten, efter specifikt datum--
		// $date_limit = strtotime('2019-07-09 17:15');
		// $smf_topics = $this->db->query('SELECT t.id_topic, first.subject, first.poster_time AS first_poster_time, last.poster_time AS last_poster_time FROM smf_topics t INNER JOIN smf_messages last ON t.id_last_msg = last.id_msg INNER JOIN smf_messages first ON t.id_first_msg = first.id_msg WHERE last.poster_time > ? ORDER BY t.id_board', array($date_limit))->result();
		// foreach($smf_topics as $smf_topic)
		// {
		// 	$phpbb_topic = $this->db->query('SELECT t.topic_id, last.post_time, t.forum_id FROM phpbb_topics t INNER JOIN phpbb_posts last ON t.topic_last_post_id = last.post_id WHERE t.topic_time = ?', array($smf_topic->first_poster_time))->row();
		// 	if($phpbb_topic && $smf_topic->last_poster_time > $phpbb_topic->post_time)
		// 		echo "$smf_topic->subject (SMF-topic-id: $smf_topic->id_topic) (phpBB-topic-id: $phpbb_topic->topic_id) (forum-id: $phpbb_topic->forum_id)<br>"; // (smf: $smf_topic->last_poster_time phpBB: $phpbb_topic->post_time)
		// }

		//--kolla vilka topics som postades efter specifierat datum--
		// $date_limit = strtotime('2019-07-09 17:15');
		// $new_topics = $this->db->query('SELECT subject, poster_time, t.id_board AS board_id FROM smf_topics t INNER JOIN smf_messages m ON t.id_first_msg = m.id_msg LEFT OUTER JOIN phpbb_topics ON m.poster_time = phpbb_topics.topic_time WHERE poster_time > ? AND phpbb_topics.topic_id IS NULL ORDER BY t.id_board ASC, poster_time ASC', array($date_limit))->result();
		// foreach($new_topics as $topic)
		// 	echo "$topic->subject (Board: $topic->board_id)<br>";
	}
}
