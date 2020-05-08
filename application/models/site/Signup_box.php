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
	 * Hämta event
	 *
	 * @param array $permission_groups Den inloggade medlemmns permission groups $member->permission_groups
	 * @return object
	 */
	public function get_upcomming_event($permission_groups = null)
	{
		// Variabler
		$deadline_time = '00:00:00';
		$event = new stdClass;
		// Är rekryt eller S4? (Kan inte kra has_permissions() eftersom den alltid ger true för admins)
		$see_gsu = false;
		if($permission_groups)
			foreach($permission_groups as $group)
				if($group->id == 12 || $group->id == 14)
					$see_gsu = true;
		$where_clause = $see_gsu
			? 'AND (ssg_event_types.display OR ssg_events.type_id = 5)' // visa events med display = 1 eller av typen GSU/ASU
			: 'AND ssg_event_types.display'; // visa endast events med display = 1

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