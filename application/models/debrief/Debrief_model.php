<?php
/**
 * Modell för Debrief-sidan.
 */
class Debrief_model extends CI_Model
{
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Hämtar debriefing för specifikt event och medlem.
	 *
	 * @param int $event_id Event-id
	 * @param int $member_id Medlems-id
	 * @return array Objekt-array.
	 */
	public function get_debrief($event_id, $member_id)
	{
		// parameter-hygien
		assert(is_numeric($event_id));
		assert(is_numeric($member_id));

		$debrief = array();
		$sql =
			'SELECT
				score,
				review_good, review_bad, review_improvement, review_tech
			FROM ssg_debriefs d
			WHERE
				d.event_id = ?
				AND d.member_id = ?';
			$result = $this->db->query($sql, array($event_id, $member_id))->result();

		return count($result) > 0 ? $result[0] : null;
	}

	/**
	 * Hämtar gamla events $page och $results_per_page.
	 *
	 * @param int $page Sida, default 0.
	 * @param int $results_per_page Resultat per sida.
	 * @return array Objekt-array.
	 */
	public function get_old_events($page = 0, $results_per_page = 10)
	{
		// parameter-hygien
		assert(is_numeric($page));
		assert(is_numeric($results_per_page));

		// hämta events
		$events = array();
		$sql =
			'SELECT
				e.id, e.title,
				t.title AS type_name,
				DATE_FORMAT(start_datetime, "%Y-%m-%d") AS start_date,
				TIME_FORMAT(start_datetime, "%H:%i") AS start_time,
				TIME_FORMAT(ADDTIME(start_datetime, length_time), "%H:%i") AS end_time,
				IF(d.event_id, 1, 0) AS debriefed
			FROM ssg_events e
			INNER JOIN ssg_event_types t
				ON e.type_id = t.id
			LEFT OUTER JOIN ssg_debriefs d
				ON e.id = d.event_id AND d.member_id = ?
			WHERE
				ADDTIME(start_datetime, length_time) < NOW()
			ORDER BY start_datetime DESC
			LIMIT ?, ?';
		$query = $this->db->query($sql, array($this->member->id, $page * $results_per_page, $results_per_page));
		foreach ($query->result() as $row)
		{
			$row->current_member_attendance = $this->eventsignup->get_member_attendance($row->id, $this->member->id);

			$events[] = $row;
		}
		
		
		return $events;
	}


	public function get_overview($event_id)
	{
		// Variabler
		$overview = new stdClass;
		$overview->groups = array();

		$sql =
		'SELECT
			grp.code,
			COUNT(*)  AS count
		FROM ssg_signups s
		INNER JOIN ssg_groups grp
			ON
				s.group_id = grp.id
				AND grp.code IS NOT NULL # inga rekryt eller "vad som helst"-anmälningar
				AND s.attendance-0 <= 3 # endast positiva anmälningar
		WHERE s.event_id = ?
		GROUP BY s.group_id
		ORDER BY grp.sorting ASC';
		$query = $this->db->query($sql, $event_id);
		foreach ($query->result() as $row)
		{
			$group = new stdClass;
			$group->code = $row->code;
			$group->signups_count = $row->count;

			$overview->groups[] = $group;
		}

		return $overview;
	}
}