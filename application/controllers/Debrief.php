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
		$groups = $this->db->query('SELECT id, code, name FROM ssg_groups WHERE active AND NOT dummy AND selectable ORDER BY enabler ASC, sorting ASC')->result();
		$init_state = $this->debrief_model->get_event_state($event_id);

		// eventet finns inte
		if (!$event) {
			show_404();
			return;
		}

		$this->load->view(
			'debrief/event',
			array(
				'global_alerts' => $this->global_alerts,
				'member_id' => $this->member->id,
				'event' => $event,
				'groups' => $groups,
				'init_state' => $init_state,
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
		$init_state = $this->debrief_model->get_group_state($event_id, $group_id);

		$this->load->view(
			'debrief/group',
			array(
				'global_alerts' => $this->global_alerts,
				'event' => $event,
				'signup' => $signup,
				'debrief' => $debrief,
				'group' => $group,
				'init_state' => $init_state,
			)
		);
	}

	/**	Formulär för att redigera eller skapa ny debrief */
	public function form($event_id = null, $member_id = null)
	{
		if (!$this->check_login()) return;

		// Moduler
		$this->load->library('eventsignup');
		$this->load->model('debrief/debrief_model');

		// Variabler
		if(!$member_id){ // inget member_id i URL: sätt till inloggade användarens id
			$member_id = $this->member->id;
		} else if($member_id != $this->member->id && !$this->permissions->has_permissions(array('grpchef', 's0', 's1'))) // ett id är satt, id:t är inte ditt eget och du har inte rätt rättigheter
			die('Du har inte rättigheterna för att ändra på andra användares debriefs.');
		$member = $this->db->query('SELECT id, name FROM ssg_members WHERE id = ?', $member_id)->row();
		$event = $this->eventsignup->get_event($event_id);
		$signup = $this->eventsignup->get_signup($event_id, $member_id);
		$debrief = $this->debrief_model->get_debrief($event_id, $member_id);
		$group = $this->db->query('SELECT id, name, code, dummy FROM ssg_groups WHERE id = ?', $signup->group_id)->row();
		$groups = $this->db->query('SELECT id, name FROM ssg_groups WHERE NOT dummy AND active AND selectable ORDER BY enabler ASC, sorting ASC')->result();
		$role = $this->db->query('SELECT id, name, name_long, dummy FROM ssg_roles WHERE id = ?', $signup->role_id)->row();
		$group_roles = $this->db->query( // grupp-id med alla dess roller
			'SELECT rg.group_id, role_id, r.name, r.name_long
			FROM ssg_roles_groups rg
			INNER JOIN ssg_roles r ON rg.role_id = r.id
			WHERE NOT r.dummy
			ORDER BY rg.group_id, r.sorting ASC'
		)->result();

		if(!$signup)
			die("Medlem {$member_id} har inte anmält sig till event {$event_id}.");


		$this->load->view(
			'debrief/form',
			array(
				'global_alerts' => $this->global_alerts,
				'event' => $event,
				'signup' => $signup,
				'debrief' => $debrief,
				'member' => $member,
				'group' => $group,
				'groups' => $groups,
				'role' => $role,
				'group_roles' => $group_roles,
				'debriefing_myself' => $member_id == $this->member->id,
			)
		);
	}

	/** Skapa eller uppdatera debrief och signup-data. */
	public function submit() {
		if (!$this->check_login()) return;

		// Moduler
		$this->load->model('debrief/debrief_model');

		$this->debrief_model->submit($this->input->post());
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
