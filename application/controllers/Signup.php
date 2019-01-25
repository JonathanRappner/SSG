<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Signup extends CI_Controller
{
	/**
	 * Huvudsida
	 */
	public function index()
	{
		$this->events();
	}

	/**
	 * Lista events
	 */
	public function events()
	{
		if(!$this->check_login()) return;

		//moduler
		$this->load->model('signup/Events');

		$next_event = $this->Events->get_next_event();
		$next_event->member_attendance = $this->Events->get_member_attendance($next_event->id, $this->member->id); //nuvarande inloggadde medlem närvaro
		$upcoming_events = $this->Events->get_upcoming_events(false);

		//ladda vy
		$this->load->view('signup/events', array(
			'next_event' => $next_event,
			'upcoming_events' => $upcoming_events
		));
	}

	/**
	 * Se anmälningar till event
	 *
	 * @param int $event_id Event-id
	 */
	public function event($event_id = null)
	{
		//moduler
		$this->load->model('signup/Events');
		$this->load->model('signup/Signups');

		//hämta event-data före inloggning till ogp/twitter-preview
		$event = $this->Events->get_event($event_id);

		//twitter/ogp-preview
		$this->preview->set_data(
			"SSG Anmälan: $event->title",
			"Datum: $event->start_date ($event->start_time - $event->end_time)",
			$event->preview_image
		);
		
		//kolla login, fortsätt om medlem är inloggad
		if(!$this->check_login()) return;

		//hämta data som inte behövs för ogp/twitter-preview
		$signup = $this->Signups->get_signup($event_id, $this->member->id);
		$signups = $this->Signups->get_signups($event_id);
		$non_signups = $this->Signups->get_non_signups($event_id);
		$events = $this->Events->get_upcoming_events(true);
		$advanced_stats = $this->Signups->get_advanced_stats($event_id);

		$this->load->view('signup/event',
			array(
				'event' => $event,
				'events' => $events,
				'signups' => $signups,
				'signup' => $signup,
				'non_signups' => $non_signups,
				'advanced_stats' => $advanced_stats,
			)
		);
	}

	/**
	 * Ny anmälan
	 *
	 * @param int $event_id Event-id
	 */
	public function form($event_id = null)
	{
		//modeller
		$this->load->model('signup/Events');
		$this->load->model('signup/Signups');

		//hämta data
		$event = $event_id != null ? $this->Events->get_event($event_id) : null;

		//twitter/ogp-preview
		$this->preview->set_data(
			"SSG Anmälan: $event->title",
			"Datum: $event->start_date ($event->start_time - $event->end_time)",
			$event->preview_image
		);

		//kolla login
		if(!$this->check_login()) return;

		//hämta data
		$events = $this->Events->get_upcoming_events(true);
		$signup = $event_id != null ? $this->Signups->get_signup($event_id, $this->member->id) : null;

		//visa inte form för gamla events
		if(isset($event) && $event->is_old)
		{
			$this->alerts->add_alert('danger', 'Du kan inte anmäla dig till gamla events.');
			redirect('signup');
			return;
		}

		//ladda vy
		$this->load->view('signup/form', array(
			'event' => $event,
			'events' => $events,
			'signup' => $signup,
		));
	}

	/**
	 * Strölir-sida
	 */
	public function strolir()
	{
		if(!$this->check_login()) return;
		
		//ladda vy
		$this->load->view('signup/strolir');
	}

	/**
	 * Historik-sida
	 */
	public function history($page = 0)
	{
		if(!$this->check_login()) return;
		
		//moduler
		$this->load->model('signup/Events');
		$this->load->library('Doodads');

		//variabler
		$results_per_page = 20;
		$total_events = $this->db->query('SELECT COUNT(*) AS count FROM ssg_events WHERE ADDTIME(start_datetime, length_time) < NOW()')->row()->count;
		$total_pages = ceil($total_events / $results_per_page);

		//ladda data
		$events = $this->Events->get_old_events($page, $results_per_page);
		
		//ladda vy
		$this->load->view('signup/history', array(
			'events' => $events,
			'page' => $page,
			'total_pages' => $total_pages,
		));
	}

	/**
	 * Personlig statistiksida
	 *
	 * @param int $other_member_id Ladda annan medlems sida. Null laddar egna sidan.
	 * @param int $page Till pagination för anmälningar-listan.
	 * @return void
	 */
	public function mypage($other_member_id = null, $page = 0)
	{
		if(!$this->check_login()) return;
		
		//modeller
		$this->load->model('signup/Signups');
		$this->load->model('signup/Mypage');

		//init
		$this->Mypage->init($other_member_id, $page);
		
		//ladda vy
		$this->load->view('signup/mypage', array(
			'loaded_member' => $this->Mypage->get_loaded_member(), //medlemen vars statistik visas på sidan
			'stats' => $this->Mypage->get_stats(),
			'page' => $page,
		));
	}

	/**
	 * Admin-sida
	 */
	public function admin($load_adminpanel = 'main', $var1 = null, $var2 = null)
	{
		if(!$this->check_login()) return;

		//permissions
		if(!$this->permissions->has_permissions(array('super', 's0', 's1', 's2',  's3', 's4', 'grpchef')))
		{
			$this->alerts->add_alert('danger', 'Du har inte tillgång till denna sida.');
			redirect('signup');
		}

		//ladda adminpanels
		require_once('application/libraries/adminpanels/interface_adminpanel.php');
		$adminpanels = array();

		$this->load->library('adminpanels/Admin_main');
		$this->load->library('adminpanels/Admin_events');
		$this->load->library('adminpanels/Admin_groups');
		$this->load->library('adminpanels/Admin_members');
		$this->load->library('adminpanels/Admin_autoevents');
		$this->load->library('adminpanels/Admin_recesses');
		$adminpanels[] = new Admin_main();
		$adminpanels[] = new Admin_events();
		$adminpanels[] = new Admin_groups();
		$adminpanels[] = new Admin_members();
		$adminpanels[] = new Admin_recesses();
		$adminpanels[] = new Admin_autoevents();

		//hitta current adminpanel
		$adminpanel = null;
		foreach($adminpanels as $panel)
			if($panel->get_code() == $load_adminpanel)
				$adminpanel = $panel;

		//om nonsense, rederict:a till main
		if($adminpanel == null)
			redirect('signup/admin');

		//kör adminpanelens main-metod
		if($this->permissions->has_permissions($adminpanel->get_permissions_needed()))
			$adminpanel->main($var1, $var2);
		else //access denied
		{
			$this->alerts->add_alert('danger', 'Du har inte tillgång till admin-panelen <strong>'. $adminpanel->get_title() .'</strong>');
			redirect('signup/admin');
		}


		//ladda vy
		$this->load->view('signup/admin', array(
			'member' => $this->member,
			'adminpanels' => $adminpanels,
			'adminpanel' => $adminpanel,
		));
	}

	/**
	 * Visar dialogruta ang. utloggning
	 *
	 * @param string $redirect Redirect-url om svaret var nej.
	 */
	public function logout_confirm($redirect = null)
	{
		if(!$this->check_login()) return;
		
		//ladda vy
		$this->load->view('signup/logout', array('redirect'=>$redirect));
	}

	/**
	 * Loggar ut användaren.
	 *
	 * @return void
	 */
	public function logout()
	{
		$this->session->sess_destroy();
		redirect('signup');
	}

	/**
	 * Ny eller redigerad anmälan ska sparas.
	 *
	 * @return void
	 */
	public function submit_signup()
	{
		$this->load->model('signup/Signups');

		$this->Signups->submit($this->input->post(), null);
	}

	/**
	 * Inloggningsuppgifter har skickats
	 */
	public function login()
	{
		//variabler
		$username = $this->input->post('username');
		$password = $this->input->post('password');
		$redirect = $this->input->post('redirect');

		if(!empty($username) && !empty($password)) //variabler finns
		{
			//försök logga in användare, om det inte går: visa login_form
			if($this->member->validate_login($username, $password))
				header("location: $redirect"); //använd inte redirect()
			else
				$this->load->view('signup/login_form', array('fail' => true));
		}
		else //variabler finns inte
			show_error('Variabler saknas vid inloggningsförsök.');
	}

	/**
	 * Kolla om användaren är inloggad.
	 * Om inte, ladda inloggningssidan.
	 *
	 * @return bool Om false så har inloggningssidan laddats.
	 */
	private function check_login()
	{
		if(!$this->member->valid)
		{
			$this->load->view('signup/login_form');
			return false;
		}

		return true;
	}

	public function test()
	{
		if($this->member->id != 1655) //Smorfty only
			show_404();
	}
}
