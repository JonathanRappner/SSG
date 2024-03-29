<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Signup extends CI_Controller
{
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

		// Inloggade medlemmens permission groups
		$permission_groups = $this->member->valid ? $this->member->permission_groups : null;

		//moduler
		$this->load->library('attendance');
		$this->load->library('eventsignup');
		$this->load->model('signup/Events');

		//nästa event
		$next_event_id = $this->Events->get_next_event_id($permission_groups);

		//om det inte finns ett nästa event
		if($next_event_id)
		{
			$next_event = $this->eventsignup->get_event($next_event_id);
			$next_event->member_attendance = $this->eventsignup->get_member_attendance($next_event_id, $this->member->id); //nuvarande inloggadde medlem närvaro
			$upcoming_events = $this->Events->get_upcoming_events($next_event_id);

			//ladda vy
			$this->load->view('signup/events', array(
				'next_event' => $next_event,
				'upcoming_events' => $upcoming_events,
				'global_alerts' => $this->global_alerts,
			));
		}
		else
		{
			//ladda vy
			$this->load->view('signup/events_empty', array('global_alerts' => $this->global_alerts));
		}
	}

	/**
	 * Se anmälningar till event
	 *
	 * @param int $event_id Event-id
	 */
	public function event($event_id = null, $show_form = null)
	{
		//moduler
		$this->load->library('attendance');
		$this->load->library('eventsignup');
		$this->load->model('signup/Form');

		//hämta event-data före inloggning till ogp/twitter-preview
		$event = $this->eventsignup->get_event($event_id);

		//twitter/ogp-preview
		$this->preview->set_data(
			"SSG Anmälan: $event->title",
			"Datum: $event->start_date ($event->start_time - $event->end_time)",
			$event->preview_image
		);
		
		//kolla login, fortsätt om medlem är inloggad
		if(!$this->check_login()) return;

		//hämta data
		$show_form = isset($show_form) && !$event->is_old;
		$signup = $this->eventsignup->get_signup($event_id, $this->member->id);
		$signups = $this->eventsignup->get_signups($event_id);
		$groups = $this->Form->get_groups();
		$non_signups = $this->eventsignup->get_non_signups($event_id);
		$advanced_stats = $this->eventsignup->get_advanced_stats($event_id);

		$this->load->view('signup/event', array(
				'event' => $event,
				'signups' => $signups,
				'signup' => $signup,
				'show_form' => $show_form,
				'groups' => $groups,
				'non_signups' => $non_signups,
				'advanced_stats' => $advanced_stats,
				'global_alerts' => $this->global_alerts,
			)
		);
	}

	/**
	 * Redirect:a gamla länkar
	 *
	 * @param int $event_id
	 * @return void
	 */
	public function form($event_id)
	{
		redirect("signup/event/$event_id/showform");
	}

	/**
	 * Historik-sida
	 */
	public function history($page = 0)
	{
		if(!$this->check_login()) return;
		
		//moduler
		$this->load->library('attendance');
		$this->load->library('eventsignup');
		$this->load->model('signup/History');

		//variabler
		$results_per_page = 20;
		$total_events = $this->db->query('SELECT COUNT(*) AS count FROM ssg_events WHERE ADDTIME(start_datetime, length_time) < NOW()')->row()->count;

		//ladda data
		$events = $this->History->get_old_events($page, $results_per_page);
		
		//ladda vy
		$this->load->view('signup/history', array(
			'events' => $events,
			'page' => $page,
			'total_events' => $total_events,
			'results_per_page' => $results_per_page,
			'global_alerts' => $this->global_alerts,
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
		$this->load->library('attendance');
		$this->load->library('eventsignup');
		$this->load->model('signup/Mypage');

		//init
		$this->Mypage->init($other_member_id, $page);
		
		//ladda vy
		$this->load->view('signup/mypage', array(
			'loaded_member' => $this->Mypage->get_loaded_member(), //medlemen vars statistik visas på sidan
			'stats' => $this->Mypage->get_stats(),
			'page' => $page,
			'since_date' => $this->Mypage->since_date,
			'global_alerts' => $this->global_alerts,
		));
	}

	/**
	 * Admin-sida
	 */
	public function admin($load_adminpanel = 'main', $var1 = null, $var2 = null, $var3 = null)
	{
		if(!$this->check_login()) return;

		//permissions
		if(!$this->permissions->has_permissions(array('s0', 's1', 's2',  's3', 's4', 'grpchef')))
		{
			$this->alerts->add_alert('danger', 'Du har inte tillgång till denna sida.');
			redirect('signup');
		}

		//ladda adminpanels
		require_once('application/libraries/adminpanels/interface_adminpanel.php');
		$adminpanels = array();

		$this->load->library('adminpanels/Admin_main');
		$this->load->library('adminpanels/Admin_events');
		$this->load->library('adminpanels/Admin_signups');
		$this->load->library('adminpanels/Admin_global_alerts');
		$this->load->library('adminpanels/Admin_groups');
		$this->load->library('adminpanels/Admin_grouproles');
		$this->load->library('adminpanels/Admin_members');
		$this->load->library('adminpanels/Admin_roles');
		$this->load->library('adminpanels/Admin_ranks');
		$this->load->library('adminpanels/Admin_recesses');
		$this->load->library('adminpanels/Admin_autoevents');
		$this->load->library('adminpanels/Admin_event_types');
		$adminpanels[] = new Admin_main();
		$adminpanels[] = new Admin_events();
		$adminpanels[] = new Admin_signups();
		$adminpanels[] = new Admin_global_alerts();
		$adminpanels[] = new Admin_groups();
		$adminpanels[] = new Admin_grouproles();
		$adminpanels[] = new Admin_members();
		$adminpanels[] = new Admin_roles();
		$adminpanels[] = new Admin_ranks();
		$adminpanels[] = new Admin_recesses();
		$adminpanels[] = new Admin_autoevents();
		$adminpanels[] = new Admin_event_types();

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
			$adminpanel->main($var1, $var2, $var3);
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
			'global_alerts' => $this->global_alerts,
		));
	}

	/**
	 * Ny eller redigerad anmälan ska sparas.
	 *
	 * @return void
	 */
	public function submit_signup()
	{
		//moduler
		$this->load->library('eventsignup');

		$this->eventsignup->submit_signup($this->input->post(), null);
	}

	/**
	 * Kolla om användaren är inloggad.
	 * Om inte, ladda inloggningssidan.
	 *
	 * @return bool Om false så har inloggningssidan laddats.
	 */
	private function check_login()
	{
		if(!$this->member->valid) //användaren är ej inloggad
		{
			$this->load->view('signup/login_form');
			return false;
		}
		else if(!$this->permissions->has_permissions(array('rekryt', 'medlem', 'inaktiv'))) //användaren är inloggad men inte med i klanen
		{
			$this->load->view('signup/only_members');
			return false;
		}

		return true;
	}
}
