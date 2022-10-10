<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Debrief extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();

		//hämta globala meddelanden
		$this->load->model('site/global_alerts');
		$this->global_alerts = $this->global_alerts->get_alerts();
	}

	/** Index hänvisar till events() */
	public function index()
	{
		$this->events();
	}

	/** Lista med tidigare events */
	public function events($page = 0)
	{
		if (!$this->check_login()) return;

		// Moduler
		$this->load->library('eventsignup');
		$this->load->library('attendance');
		$this->load->model('debrief/debrief_model');

		// Variabler
		$results_per_page = 20;
		$total_events = $this->db->query('SELECT COUNT(*) AS count FROM ssg_events WHERE ADDTIME(start_datetime, length_time) < NOW()')->row()->count;
		$event = $this->debrief_model->get_old_events($page, $results_per_page);

		$this->load->view(
			'debrief/events',
			array(
				'events' => $event,
				'page' => $page,
				'total_events' => $total_events,
				'results_per_page' => $results_per_page,
				'global_alerts' => $this->global_alerts,
			)
		);
	}


	/**
	 * Vy för enskilt event
	 *
	 * @param  mixed $event_id
	 * @return void
	 */
	public function event($event_id = null)
	{
		if (!$this->check_login()) return;

		// event-id saknas i url
		if (!$event_id) show_404();

		// Moduler
		$this->load->library('eventsignup');
		$this->load->library('attendance');
		$this->load->model('debrief/debrief_model');

		// Variabler
		$event = $this->eventsignup->get_event($event_id);
		$signup = $this->eventsignup->get_signup($event_id, $this->member->id);
		$debrief = $this->debrief_model->get_debrief($event_id, $this->member->id);
		$overview = $this->debrief_model->get_overview($event_id);

		// eventet finns inte
		if (!$event) {
			show_404();
			return;
		}

		$this->load->view(
			'debrief/event',
			array(
				'global_alerts' => $this->global_alerts,
				'event' => $event,
				'signup' => $signup,
				'debrief' => $debrief,
				'overview' => $overview,
			)
		);
	}

	/**	Vy för enskilld grupps debrief av specifikt event */
	public function group($event_id = null, $group_id = null)
	{
		if (!$this->check_login()) return;
		
		// Moduler
		$this->load->library('eventsignup');
		$this->load->model('debrief/debrief_model');

		// Variabler
		$event = $this->eventsignup->get_event($event_id);
		$signup = $this->eventsignup->get_signup($event_id, $this->member->id);
		$debrief = $this->debrief_model->get_debrief($event_id, $this->member->id);
		$group = $this->db->query('SELECT id, name, code FROM ssg_groups WHERE id = ?', $group_id)->row();

		$this->load->view(
			'debrief/group',
			array(
				'global_alerts' => $this->global_alerts,
				'event' => $event,
				'signup' => $signup,
				'debrief' => $debrief,
				'group' => $group,
			)
		);
	}

	/**	Formulär för att redigera eller skapa ny debrief */
	public function form($event_id = null, $member_id = null)
	{
		if (!$this->check_login()) return;


		$this->load->view(
			'debrief/form',
			array(
				'global_alerts' => $this->global_alerts,
				'event_id' => $event_id,
				'member_id' => $member_id,
			)
		);
	}

	/**
	 * Kolla om användaren är inloggad.
	 * Om inte, ladda inloggningssidan.
	 *
	 * @return bool Om false så har inloggningssidan laddats.
	 */
	private function check_login()
	{
		if (!$this->member->valid) // användaren är ej inloggad
		{
			$this->load->view('debrief/login_form');
			return false;
		} else if (!$this->permissions->has_permissions(array('rekryt', 'medlem', 'inaktiv'))) // användaren är inloggad men inte med i klanen
		{
			$this->load->view('debrief/only_members');
			return false;
		}

		return true;
	}
}
