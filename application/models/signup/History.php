<?php
/**
 * Modell för Historik-sidan.
 */
class History extends CI_Model
{
	public function __construct()
	{
		parent::__construct();
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
		//parameter-hygien
		assert(is_numeric($page));
		assert(is_numeric($results_per_page));

		//hämta events
		$events = array();
		$sql =
			'SELECT
				ssg_events.id, ssg_events.title,
				ssg_event_types.title AS type_name,
				DATE_FORMAT(start_datetime, "%Y-%m-%d") AS start_date,
				TIME_FORMAT(start_datetime, "%H:%i") AS start_time,
				TIME_FORMAT(ADDTIME(start_datetime, length_time), "%H:%i") AS end_time
			FROM ssg_events
			INNER JOIN ssg_event_types
				ON ssg_events.type_id = ssg_event_types.id
			WHERE
				ADDTIME(start_datetime, length_time) < NOW()
			ORDER BY start_datetime DESC
			LIMIT ?, ?';
		$query = $this->db->query($sql, array($page * $results_per_page, $results_per_page));
		foreach ($query->result() as $row)
		{
			// hämta antal anmälningar av varje typ
			$row->signups = $this->eventsignup->get_signups_counts($row->id);
			$row->signed_sum = $this->attendance->count_signed($row->signups); //antal positiva anmälningar
			$row->current_member_attendance = $this->eventsignup->get_member_attendance($row->id, $this->member->id);
			
			//första april
			if(APRIL_FOOLS) $row->title .= ' '. $this->april_fools->random_emojis($row->title);

			$events[] = $row;
		}
		
		
		return $events;
	}
}