<?php
/**
 * Modell för events-sidan.
 */
class Events extends CI_Model
{
	/**
	 * Konstruktor
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Hitta event_id för nästa obligatoriska event.
	 *
	 * @return void
	 */
	public function get_next_event_id()
	{
		$sql =
			'SELECT ssg_events.id
			FROM ssg_events
			INNER JOIN ssg_event_types
				ON ssg_events.type_id = ssg_event_types.id
			WHERE
				ADDTIME(start_datetime, length_time) >= NOW()
				AND ssg_event_types.display
			ORDER BY start_datetime ASC
			LIMIT 1';
		
		return $this->db->query($sql)->row()->id;
	}

	/**
	 * Hämta framtida events (förutom nästa obligatoriska event)
	 *
	 * @param bool $include_next_event Inkludera nästa obligatoriska event?
	 * @return array Objekt-array.
	 */
	public function get_upcoming_events()
	{
		//variabler
		$next_event_id = $this->get_next_event_id();
		$max_results = 10;
		
		//hämta events
		$events = array();
		$sql =
			'SELECT
				ssg_events.id, ssg_events.title, author, forum_link, preview_image,
				ssg_event_types.title-0 AS type_id,
				ssg_event_types.title AS type_name,
				DATE_FORMAT(start_datetime, "%Y-%m-%d") AS start_date,
				DATE_FORMAT(ADDTIME(start_datetime, length_time), "%Y-%m-%d") AS end_date,
				TIME_FORMAT(start_datetime, "%H:%i") AS start_time,
				TIME_FORMAT(ADDTIME(start_datetime, length_time), "%H:%i") AS end_time
			FROM ssg_events
			INNER JOIN ssg_event_types
				ON ssg_events.type_id = ssg_event_types.id
			WHERE
				ADDTIME(start_datetime, length_time) >= NOW()
				AND ssg_events.id != ?
			ORDER BY start_datetime ASC
			LIMIT ?';
		$query = $this->db->query($sql, array($next_event_id, $max_results));
		foreach ($query->result() as $row)
		{
			// hämta antal anmälningar av varje typ
			$row->signups = $this->eventsignup->get_signups_counts($row->id);
			$row->current_member_attendance = $this->eventsignup->get_member_attendance($row->id, $this->member->id);

			//första april
			if(defined('APRIL_FOOLS')) $row->title .= ' '. $this->april_fools->random_emojis($row->title);

			$events[] = $row;
		}

		return $events;
	}

	/**
	 * Enhetstest för denna modell.
	 *
	 * @return void
	 */
	public function unit_test()
	{
		throw Exception('Not implemented');
		// $this->load->library('unit_test');

		// //--Smorfty--
		// //get member data
		// $member_data = $this->get_member_data(1655);
		// //name
		// $this->unit->run(
		// 	$member_data->name, //input
		// 	'Smorfty', //expected
		// 	'get_member_data()->name: Smorfty' //title
		// );

		// echo $this->unit->report();
	}
}
?>