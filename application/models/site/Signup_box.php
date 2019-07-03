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
	 * @return object
	 */
	public function get_upcomming_event()
	{
		//variabler
		$deadline_time = '00:00:00';
		$event = new stdClass;

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
				AND ssg_event_types.display
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