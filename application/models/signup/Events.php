<?php
/**
 * Funktioner för events (OP:ar, träningar, strölir osv.)
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
	 * Hämta enskillt event.
	 *
	 * @param int $event_id
	 * @return object
	 */
	public function get_event($event_id)
	{
		//variabler
		$deadline_time = '00:00:00';

		$sql =
			"SELECT
				ssg_events.id, ssg_events.title, forum_link, preview_image, obligatory,
				author AS author_id,
				type_id-0 AS type_id,
				ssg_event_types.title AS type_name,
				ssg_members.name AS author_name,
				TIME_FORMAT(length_time, '%H:%i') AS length_time,
				DATE_FORMAT(start_datetime, '%Y-%m-%d') AS start_date,
				DATE_FORMAT(ADDTIME(start_datetime, length_time), '%Y-%m-%d') AS end_date,
				TIME_FORMAT(start_datetime, '%H:%i') AS start_time,
				TIME_FORMAT(ADDTIME(start_datetime, length_time), '%H:%i') AS end_time,
				UNIX_TIMESTAMP(DATE_FORMAT(start_datetime, '%Y-%m-%d $deadline_time')) AS deadline_epoch,
				ADDTIME(start_datetime, length_time) < NOW() AS is_old
			FROM ssg_events
			LEFT JOIN ssg_members
				ON ssg_events.author = ssg_members.id
			INNER JOIN ssg_event_types
				ON ssg_events.type_id = ssg_event_types.id
			WHERE ssg_events.id = ?";
		$query = $this->db->query($sql, $event_id);

		if($query->num_rows() <= 0)
			show_error("Ogiltigt event-id: $event_id");
		
		$event = $query->row();
		$event->signups = $this->get_num_signups($event->id);

		return $event;
	}

	/**
	 * Hämtar nästa obligatoriska event.
	 *
	 * @return object
	 */
	public function get_next_event()
	{
		$event_id = $this->get_next_event_id();

		return $this->get_event($event_id);
	}

	/**
	 * Hämtar nästa obligatoriska events ID-nummer.
	 *
	 * @return int
	 */
	public function get_next_event_id()
	{
		//hitta event_id för nästa obligatoriska event
		$sql =
			'SELECT ssg_events.id
			FROM ssg_events
			INNER JOIN ssg_event_types
				ON ssg_events.type_id = ssg_event_types.id
			WHERE
				ADDTIME(start_datetime, length_time) >= NOW()
				AND ssg_event_types.display = 1
			ORDER BY start_datetime ASC
			LIMIT 1';
		$query = $this->db->query($sql);

		if($query->num_rows() <= 0)
			return;
		
		return $query->row()->id;
	}

	/**
	 * Hämta framtida events (förutom nästa obligatoriska event)
	 *
	 * @param bool $include_next_event Inkludera nästa obligatoriska event?
	 * @return array Objekt-array.
	 */
	public function get_upcoming_events($include_next_event = false)
	{
		//parameter-hygien
		assert(is_bool($include_next_event));

		//variabler
		$max = 10;
		if(!$include_next_event)
		{
			$next_event_id = $this->get_next_event_id();
			$sql_next_event = 'AND ssg_events.id != '. $this->db->escape($next_event_id);
		}
		else
			$sql_next_event = null;
		
		//hämta events
		$events = array();
		$sql =
			"SELECT
				ssg_events.id, ssg_events.title, author, forum_link, preview_image,
				ssg_event_types.title-0 AS type_id,
				ssg_event_types.title AS type_name,
				DATE_FORMAT(start_datetime, '%Y-%m-%d') AS start_date,
				DATE_FORMAT(ADDTIME(start_datetime, length_time), '%Y-%m-%d') AS end_date,
				TIME_FORMAT(start_datetime, '%H:%i') AS start_time,
				TIME_FORMAT(ADDTIME(start_datetime, length_time), '%H:%i') AS end_time
			FROM ssg_events
			INNER JOIN ssg_event_types
				ON ssg_events.type_id = ssg_event_types.id
			WHERE
				ADDTIME(start_datetime, length_time) >= NOW()
				$sql_next_event
			ORDER BY start_datetime ASC
			LIMIT $max";
		$query = $this->db->query($sql);
		foreach ($query->result() as $row)
		{
			// hämta antal anmälningar av varje typ
			$row->signups = $this->get_num_signups($row->id);
			$row->current_member_attendance = $this->get_member_attendance($row->id, $this->member->id);
			$events[] = $row;
		}

		return $events;
	}

	/**
	 * Hämtar events (framtida och förflutna) baserat på $page och $results_per_page.
	 *
	 * @param int $page Sida, default 0.
	 * @param int $results_per_page Resultat per sida.
	 * @param bool $sort_desc Sortera i sjunkande ordning efter startdatum?
	 * @return array Objekt-array.
	 */
	public function get_events($page = 0, $results_per_page = 10, $sort_desc = true)
	{
		//parameter-hygien
		assert(is_numeric($page));
		assert(is_numeric($results_per_page));

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
				TIME_FORMAT(ADDTIME(start_datetime, length_time), "%H:%i") AS end_time,
				ADDTIME(start_datetime, length_time) < NOW() AS is_old
			FROM ssg_events
			INNER JOIN ssg_event_types
				ON ssg_events.type_id = ssg_event_types.id
			ORDER BY start_datetime '. ($sort_desc ? 'DESC' : 'ASC') .'
			LIMIT ?, ?';
		$query = $this->db->query($sql, array($page * $results_per_page, $results_per_page));
		foreach ($query->result() as $row)
		{
			// hämta antal anmälningar av varje typ
			$row->signups = $this->get_num_signups($row->id);
			$row->signed_sum = $this->attendance->count_signed($row->signups); //antal positiva anmälningar
			$row->current_member_attendance = $this->get_member_attendance($row->id, $this->member->id);
			$events[] = $row;
		}
		
		
		return $events;
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
			$row->signups = $this->get_num_signups($row->id);
			$row->signed_sum = $this->attendance->count_signed($row->signups); //antal positiva anmälningar
			$row->current_member_attendance = $this->get_member_attendance($row->id, $this->member->id);
			$events[] = $row;
		}
		
		
		return $events;
	}

	/**
	 * Hämtar antal anmälningar till event uppdelat efter anmälningstyp (Ja, JIP osv.)
	 *
	 * @param int $event_id
	 * @return array Ex: array('total'=> 3, '1'=>2, '2'=>1) (Nycklarna representerar Ja, JIP, osv.)
	 */
	public function get_num_signups($event_id)
	{
		//variabler
		$num_signups = array();

		$sql =
			'SELECT
				attendance-0 AS attendance_id,
				COUNT(*) AS count
			FROM ssg_signups
			WHERE event_id = ?
			GROUP BY attendance';
		$query = $this->db->query($sql, $event_id);
		foreach ($query->result() as $row)
			$num_signups[$row->attendance_id] = $row->count-0;
		
		return $num_signups;
	}

	/**
	 * Hämtar närvaro för specifik medlem, för specifikt event.
	 *
	 * @param int $event_id
	 * @param int $member_id
	 * @return object Attendance-objekt
	 */
	public function get_member_attendance($event_id, $member_id)
	{
		$sql =
			'SELECT attendance-0 AS attendance
			FROM ssg_signups
			WHERE
				event_id = ? &&
				member_id = ?';
		$query = $this->db->query($sql, array($event_id, $member_id));
		$row = $query->row();

		return isset($row->attendance)
			? $this->attendance->get_type_by_id($row->attendance) //anmälan hittad, ge resultat
			: $this->attendance->get_type_by_code('notsigned'); //ingen anmälan hittad
	}

	/**
	 * Kolliderar tidsperioden med existerande events?
	 * Returnerar array med events som overlappas (max 2)
	 *
	 * @param string $start Datetime: åååå-mm-dd hh:mm:ss
	 * @param string $end Datetime: åååå-mm-dd hh:mm:ss
	 * @return array Array där key = event_id och value = event-title
	 */
	public function is_overlapping($start, $end)
	{
		//parameter-sanering, yo
		if($end < $start)
			throw new Exception("\$end ($end) ligger före \$start ($start)");

		$start = $this->db->escape($start);
		$end = $this->db->escape($end);
		$sql =
			"SELECT id, title
			FROM ssg_events
			WHERE 
				($start <= ADDTIME(start_datetime, length_time) AND $end >= ADDTIME(start_datetime, length_time)) OR
				($start <= start_datetime AND $end >= start_datetime) OR
				($start >= start_datetime AND $end <= ADDTIME(start_datetime, length_time)) OR
				($start <= start_datetime AND $end >= ADDTIME(start_datetime, length_time))
			LIMIT 2"; //limit 2 eftersom man kan kolla om ett år långt event overlappar någonting och på så sätt lista skitmånga events
		$query = $this->db->query($sql);

		$results = array();
		foreach($query->result() as $row)
			$results[$row->id] = $row->title;

		return $results;
	}

	/**
	 * Ligger $start under någon av upppehållen?
	 *
	 * @param string $start Date åååå-mm-dd
	 * @return bool
	 */
	public function date_during_recess($start)
	{
		$sql =
			'SELECT id
			FROM ssg_recesses
			WHERE
				? >= start_date
				AND ? <= DATE_ADD(start_date, INTERVAL length_days DAY)';
		$query = $this->db->query($sql, array($start, $start));

		return $query->num_rows() > 0;
	}

	/**
	 * Skapar nya events baserat på ssg_auto_events.
	 *
	 * @return void
	 */
	public function create_auto_events()
	{
		//variabler
		$multiples = 4; //antal gånger i framtiden mallarna ska appliceras (4 = skapa events fyra veckor frammåt)
		$days_in_a_week = 7; //förhoppningsvis ändras inte detta i framtiden :P

		//hämta mallar
		$templates = array();
		$sql =
			'SELECT
				id, title, start_day, type_id,
				TIME_FORMAT(start_time, "%H:%i") AS start_time,
				TIME_FORMAT(ADDTIME(start_time, length_time), "%H:%i") AS end_time,
				length_time,
				TIME_FORMAT(length_time, "%H") AS length_hours,
				TIME_FORMAT(length_time, "%i") AS length_minutes
			FROM ssg_auto_events';
		$query = $this->db->query($sql);
		foreach ($query->result() as $template)
		{
			for($i=0; $i<$multiples; $i++)
			{
				//nästa datum där mallen kommer lägga ett event
				$next_occurance_start = date('Y-m-d', strtotime("next $template->start_day +$i weeks")) .' '. $template->start_time; //strtotime == black magic
				$next_occurance_end = date('Y-m-d G:i:s', strtotime("$next_occurance_start +$template->length_hours hours $template->length_minutes minutes"));

				
				//spara variabler
				$overlap = $this->is_overlapping($next_occurance_start, $next_occurance_end);
				$during_recess = $this->date_during_recess($next_occurance_start);

				//skapa events (som inte overlappar andra events eller tar plats under uppehåll)
				if(count($overlap) <= 0 && !$during_recess) //skippa om mallen overlap:ar andra events eller ligger under uppehåll
				{
					$sql =
						"INSERT INTO ssg_events(title, start_datetime, length_time, type_id)
						VALUES (?, ?, ?, ?)";
					$this->db->query($sql, array(
						$template->title,
						$next_occurance_start,
						$template->length_time,
						$template->type_id
					));
				}
			}
		}
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