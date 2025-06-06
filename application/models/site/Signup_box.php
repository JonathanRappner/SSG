<?php
/**
 * Modell för Anmälningsrutan.
 */
class Signup_box extends CI_Model
{
	public $event;

	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Hämta nästkommande obligatoriska event
	 *
	 * @param array $permission_groups Den inloggade medlemmens permission groups $member->permission_groups
	 * @return object
	 */
	public function get_upcomming_event($permission_groups = null)
	{
		// Variabler
		$deadline_time = '00:00:00';
		$event = new stdClass;
		
		// Är rekryt eller S4? (Kan inte använda has_permissions() eftersom den alltid ger true för admins)
		$see_gsu = false;
		if($permission_groups)
			foreach($permission_groups as $group)
				if($group->id == 12 || $group->id == 14) // se db-tabell phpbb_groups
					$see_gsu = true;
		$where_clause = $see_gsu
			? 'AND (ssg_events.highlight OR ssg_events.type_id = 5)' // visa events med display = 1 eller av typen GSU/ASU
			: 'AND ssg_events.highlight'; // visa endast events med display = 1

		//event
		$sql =
			'SELECT
				ssg_events.id AS event_id, ssg_events.title, forum_link,
				ssg_event_types.title AS type_name,
				DATE_FORMAT(start_datetime, "%Y-%m-%d") AS start_date,
				DAYOFWEEK(start_datetime) AS day_of_week,
				TIME_FORMAT(start_datetime, "%H:%i") AS start_time,
				TIME_FORMAT(ADDTIME(start_datetime, length_time), "%H:%i") AS end_time,
				UNIX_TIMESTAMP(DATE_FORMAT(start_datetime, "%Y-%m-%d ?")) AS deadline_epoch
			FROM ssg_events
			INNER JOIN ssg_event_types
				ON ssg_events.type_id = ssg_event_types.id
			WHERE
				ADDTIME(start_datetime, length_time) >= NOW()
				'. $where_clause .'
			ORDER BY start_datetime ASC
			LIMIT 1';
		$event = $this->db->query($sql, $deadline_time)->row();

		//arbryt om inget nytt event hittats
		if($event == null)
			return null;

		//antal anmälda
		$event->signups_count = $this->get_signups_count($event->event_id);

		//inloggad medlems anmälan
		if($this->member->valid)
			$event->member_signup = $this->get_member_signup($event->event_id, $this->member->id);

		return $event;
	}

	/**
	 * Hämta det föregående eventet som skedde idag.
	 * Används åt debrief-knappen.
	 *
	 * @param int $member_id
	 * @param array $permission_groups Den inloggade medlemmens permission groups $member->permission_groups
	 * @return object
	 */
	public function get_debrief_event($member_id, $permission_groups = null)
	{
		// måste vara med i klanen för att se knappen
		if(!$this->permissions->has_permissions(array('rekryt', 'medlem', 'inaktiv')))
			return null;

		// variabler
		$event = new stdClass;

		//event
		$sql =
			'SELECT
				e.id, e.title,
				(SELECT COUNT(*) FROM ssg_signups s WHERE e.id = s.event_id AND s.member_id = ? AND attendance-0 < 4) AS member_signed
			FROM ssg_events e
			WHERE
				DATE_FORMAT(start_datetime, "%Y-%m-%d") = DATE_FORMAT(NOW(), "%Y-%m-%d") # idag
				AND start_datetime <= NOW() # efter starttiden
			ORDER BY start_datetime DESC
			LIMIT 1';
		$row = $this->db->query($sql, array($member_id))->row();
		
		// Om ingen positiv anmälan finns: returnera null
		return $row && $row->member_signed > 0
			? $row
			: null;
	}

	/**
	 * Hämta flera icke obligatoriska events.
	 *
	 * @param array $permission_groups Den inloggade medlemmens permission groups $member->permission_groups
	 * @return array
	 */
	public function get_other_events($member)
	{
		// ladda inte other events för gäster
		if(!$member)
			return;

		// Variabler
		$number_of_events = 3;
		$deadline_time = '00:00:00';

		// Hämta nästa highlightade events id så att det inte hämtas här
		$this->load->model('signup/Events');
		$next_event_id = $this->Events->get_next_event_id($member->permission_groups);
		
		// Visa inte gsu/asu-events för se som inte är rekryt eller S4
		// $see_gsu = false;
		// $where_clause = null;
		// if($member->permission_groups)
		// 	foreach($member->permission_groups as $group)
		// 		if($group->id == 12 || $group->id == 14) // se db-tabell phpbb_groups
		// 			$see_gsu = true;
		// if(!$see_gsu)
		// 	$where_clause .='AND ssg_events.type_id != 5'; // GSU/ASU

		//event
		$sql =
			'SELECT
				ssg_events.id AS event_id,
				ssg_events.title,
				ssg_event_types.title AS type_name,
				DATE_FORMAT(start_datetime, "%Y-%m-%d") AS start_date,
				TIME_FORMAT(start_datetime, "%H:%i") AS start_time,
				UNIX_TIMESTAMP(start_datetime) AS epoch,
				(SELECT COUNT(*) FROM ssg_signups s WHERE ssg_events.id = s.event_id AND s.member_id = ?) AS signed_up
			FROM ssg_events
			INNER JOIN ssg_event_types
				ON ssg_events.type_id = ssg_event_types.id
			WHERE
				ADDTIME(start_datetime, length_time) >= NOW()
				AND ssg_events.id != ?
			ORDER BY start_datetime ASC
			LIMIT ?';

		return $this->db->query($sql, array($member->id, $next_event_id, $number_of_events))->result();
	}

	/**
	 * Hämta antal anmälningar till event
	 *
	 * @param int $event_id
	 * @return int
	 */
	private function get_signups_count($event_id)
	{
		$sql =
			'SELECT
				COUNT(*) AS count
			FROM ssg_signups
			WHERE
				event_id = ?
				AND attendance <= 3'; //Ja, JIP & QIP
			return $this->db->query($sql, $event_id)->row()->count;
	}

	/**
	 * Hämta anmälan
	 *
	 * @param int $event_id
	 * @param int $member_id
	 * @return object
	 */
	private function get_member_signup($event_id, $member_id)
	{
		$sql =
			'SELECT
				attendance AS attendance_name,
				attendance-0 AS attendance_id
			FROM ssg_signups
			WHERE
				event_id = ?
				AND member_id = ?';
		return $this->db->query($sql, array($event_id, $member_id))->row();
	}
}