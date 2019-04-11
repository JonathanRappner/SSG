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

		$this->event = $this->get_event();
		$this->event->signups_count = $this->get_signups_count($this->event->event_id);
		if($this->member->valid)
			$this->event->member_signup = $this->get_member_signup($this->event->event_id, $this->member->id);
	}

	
	private function get_event()
	{
		$deadline_time = '00:00:00';

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
		return $this->db->query($sql, $deadline_time)->row();
	}

	
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