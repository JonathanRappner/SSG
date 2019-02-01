<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Gemensamma metoder för Events och dess Anmälningar.
 */
class Eventsignup
{
	protected $CI;

	public function __construct()
	{
		// Assign the CodeIgniter super-object
		$this->CI =& get_instance();
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
				UNIX_TIMESTAMP(DATE_FORMAT(start_datetime, ?)) AS deadline_epoch,
				ADDTIME(start_datetime, length_time) < NOW() AS is_old
			FROM ssg_events
			LEFT JOIN ssg_members
				ON ssg_events.author = ssg_members.id
			INNER JOIN ssg_event_types
				ON ssg_events.type_id = ssg_event_types.id
			WHERE ssg_events.id = ?";
		$query = $this->CI->db->query($sql, array("%Y-%m-%d $deadline_time", $event_id));

		if($query->num_rows() <= 0)
			show_error("Ogiltigt event-id: $event_id");
		
		$event = $query->row();
		$event->signups = $this->get_signups_counts($event->id);

		//första april
		if(defined('APRIL_FOOLS'))
			$event->title .= ' '. $this->CI->april_fools->random_emojis($event->title);

		return $event;
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
		$query = $this->CI->db->query($sql, array($page * $results_per_page, $results_per_page));
		foreach ($query->result() as $event)
		{
			// hämta antal anmälningar av varje typ
			$event->signups = $this->get_signups_counts($event->id);
			$event->signed_sum = $this->CI->attendance->count_signed($event->signups); //antal positiva anmälningar
			$event->current_member_attendance = $this->get_member_attendance($event->id, $this->CI->member->id);

			//första april
			if(defined('APRIL_FOOLS')) $event->title .= ' '. $this->CI->april_fools->random_emojis($event->title);

			$events[] = $event;
		}
		
		return $events;
	}

	/**
	 * Hämtar antal anmälningar till event uppdelat efter anmälningstyp (Ja, JIP osv.)
	 *
	 * @param int $event_id
	 * @return array Ex: array('total'=> 3, '1'=>2, '2'=>1) (Nycklarna representerar Ja, JIP, osv.)
	 */
	public function get_signups_counts($event_id)
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
		$query = $this->CI->db->query($sql, $event_id);
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
		$query = $this->CI->db->query($sql, array($event_id, $member_id));

		return $query->num_rows() > 0
			? $this->CI->attendance->get_type_by_id($query->row()->attendance) //anmälan hittad, ge resultat
			: $this->CI->attendance->get_type_by_code('notsigned'); //ingen anmälan hittad
	}

	/**
	 * Hämta specifik anmälan.
	 *
	 * @param int $event_id
	 * @param int $member_id
	 * @return object
	 */
	public function get_signup($event_id, $member_id)
	{
		$sql =
			"SELECT
				event_id, group_id, role_id, signed_datetime, last_changed_datetime, message,
				attendance AS attendance_name,
				attendance-0 AS attendance_id
			FROM ssg_signups
			WHERE
				event_id = ?
				AND member_id = ?";
		$query = $this->CI->db->query($sql, array($event_id, $member_id));
		$signup = $query->row();

		//input-sanering
		if(isset($signup->message))
			$signup->message = trim(strip_tags($signup->message)); //egentligen ska aldrig html-tags komma in i db
		
		return $signup;
	}

	/**
	 * Hämta anmälningar till specifikt event
	 *
	 * @param int $event_id
	 * @return array
	 */
	public function get_signups($event_id)
	{
		//variabler
		$signups = array();

		$sql =
			"SELECT
				message,
				member_id,
				ssg_members.name AS member_name,
				ssg_signups.group_id,
				ssg_groups.name AS group_name,
				ssg_groups.code AS group_code,
				ssg_signups.role_id,
				ssg_roles.name AS role_name,
				ssg_roles.name_long AS role_name_long,
				(SELECT ssg_ranks.name FROM ssg_promotions INNER JOIN ssg_ranks ON ssg_promotions.rank_id = ssg_ranks.id WHERE member_id = ssg_signups.member_id AND NOT ssg_ranks.dummy ORDER BY date DESC LIMIT 1) AS rank_name,
				(SELECT ssg_ranks.icon FROM ssg_promotions INNER JOIN ssg_ranks ON ssg_promotions.rank_id = ssg_ranks.id WHERE member_id = ssg_signups.member_id AND NOT ssg_ranks.dummy ORDER BY date DESC LIMIT 1) AS rank_icon,
				attendance-0 AS attendance_id,
				attendance AS attendance_text,
				DATE_FORMAT(signed_datetime, '%Y-%m-%d') AS signed_date,
				TIME_FORMAT(signed_datetime, '%H:%i') AS signed_time,
				DATE_FORMAT(last_changed_datetime, '%Y-%m-%d') AS last_changed_date,
				TIME_FORMAT(last_changed_datetime, '%H:%i') AS last_changed_time
			FROM ssg_signups
			INNER JOIN ssg_members
				ON ssg_signups.member_id = ssg_members.id
			INNER JOIN ssg_groups
				ON ssg_signups.group_id = ssg_groups.id
			INNER JOIN ssg_roles
				ON ssg_signups.role_id = ssg_roles.id
			WHERE ssg_signups.event_id = ?
			ORDER BY
				CASE
					WHEN attendance = 4 THEN 1 #noshow näst sist
					WHEN attendance = 6 THEN 2 #awol sist
					ELSE 0 #positiva anmälningar först
				END ASC,
				ssg_groups.sorting ASC,
				ssg_roles.sorting ASC";
		$query = $this->CI->db->query($sql, array($event_id));
		foreach ($query->result() as $row)
		{
			//input-sanering
			$row->message = trim(strip_tags($row->message));

			//första april
			if(defined('APRIL_FOOLS') && !empty($row->message)) $row->message .= ' '. $this->CI->april_fools->random_emojis($row->message);

			$signups[] = $row;
		}

		return $signups;
	}

	/**
	 * Hämta lista med aktiva medlemmar som inte anmält sig till $event_id
	 *
	 * @param int $event_id
	 * @return array
	 */
	public function get_non_signups($event_id)
	{
		//variabler
		$members = array();

		$sql =
			'SELECT
				ssg_members.id,
				ssg_members.name,
				ssg_groups.code AS group_code,
				ssg_groups.name AS group_name
			FROM ssg_members
			LEFT OUTER JOIN ssg_groups
				ON ssg_members.group_id = ssg_groups.id
			LEFT OUTER JOIN ssg_roles
				ON ssg_members.role_id = ssg_roles.id
			WHERE
				is_active
				AND ssg_members.id NOT IN (SELECT member_id FROM ssg_signups WHERE event_id = ?)
			ORDER BY
				ssg_groups.sorting ASC,
				ssg_roles.sorting ASC';
		$query = $this->CI->db->query($sql, $event_id);
		
		foreach ($query->result() as $row)
			$members[] = $row;
		
		return $members;
	}

	/**
	 * Hämta lämpliga förval (role & group) till anmälningsformuläret.
	 * Gäller endast formulär för nya anmälningar.
	 * Försöker hitta vad medlemmen anmälde som sist.
	 * Annars hämtar den medlemmens angivna grupp/roll
	 * och om inte det finns ger den null-värden.
	 *
	 * @param int $member_id
	 * @return object Objekt med attributen role_id och group_id.
	 */
	public function get_preselects($member_id)
	{
		//senast gjorda/ändrade anmälningen
		$sql =
			"SELECT role_id, group_id
			FROM ssg_signups
			WHERE member_id = ?
			ORDER BY last_changed_datetime DESC
			LIMIT 1";
		$query = $this->CI->db->query($sql, $member_id);
		$last_signup = $query->row();

		if($query->num_rows() > 0)
			return $last_signup;
		
		//hitta medlemmens angivna roll & grupp
		$sql =
			"SELECT role_id, group_id
			FROM ssg_members
			WHERE id = ?";
		$query = $this->CI->db->query($sql, $member_id);
		$member_defaults = $query->row();
		
		//även om medlemmen inte har assign:ade värden så returnerar i alla fall metoden ett tomt objekt
		return $member_defaults;
	}

	/**
	 * Hämta detaljerad statistik för event.
	 *
	 * @param int $event_id
	 * @return object
	 */
	public function get_advanced_stats($event_id)
	{
		//variabler
		$stats = new stdClass;
		$stats->total = 0;
		$stats->signed = 0;
		$stats->jipqip = 0;
		$stats->noshow = 0;
		$stats->latest = null;
		$stats->groups = array();

		//totalt
		$sql =
			'SELECT
				attendance AS attendance_name,
				attendance-0 AS attendance_id,
				group_id,
				ssg_groups.name AS group_name,
				ssg_groups.code AS group_code,
				ssg_groups.dummy
			FROM ssg_signups
			INNER JOIN ssg_groups
				ON ssg_signups.group_id = ssg_groups.id
			WHERE event_id = ?
			ORDER BY ssg_groups.sorting ASC';
		$query = $this->CI->db->query($sql, $event_id);
		foreach($query->result() as $signup)
		{
			//--anmälningar per typ--
			$stats->total++;
			$stats->signed += ($signup->attendance_id == 1) ? 1 : 0;
			$stats->jipqip += ($signup->attendance_id == 2 || $signup->attendance_id == 3) ? 1 : 0;
			$stats->noshow += ($signup->attendance_id == 4) ? 1 : 0;

			//spara ingen grupp-statistik (och skapa inga array-positioner i $stats->groups) om annat än ja/jip/qip
			if($signup->attendance_id > 3)
				continue;

			//--anmälningar per grupp--
			//skapa objekt på array-index om det inte redan finns
			if(!$signup->dummy && !isset($stats->groups[$signup->group_id])) //inga dummies eller NOSHOWs
			{
				$group = new stdClass;
				$group->name = $signup->group_name;
				$group->code = $signup->group_code;
				$group->signed = 0;
				$group->jipqip = 0;
				$stats->groups[$signup->group_id] = $group;
			}
			else if($signup->dummy && !isset($stats->groups['misc'])) //skapa gruppen 'Övrigt' för dummy-grupp-anmälningar
			{
				$group = new stdClass;
				$group->name = 'Övrigt';
				$group->code = null;
				$group->signed = 0;
				$group->jipqip = 0;
				$stats->groups['misc'] = $group;
			}
			
			//grupp-stats
			$index = !$signup->dummy ? $signup->group_id : 'misc';
			$stats->groups[$index]->signed += ($signup->attendance_id == 1) ? 1 : 0;
			$stats->groups[$index]->jipqip += ($signup->attendance_id == 2 || $signup->attendance_id == 3) ? 1 : 0;
		}

		//senaste anmälning/ändring
		$stats->last_changed = array();
		$sql =
			'SELECT
				name,
				DATE_FORMAT(last_changed_datetime, "%Y-%m-%d - %H:%i") AS date
			FROM ssg_signups
			INNER JOIN ssg_members
				ON ssg_signups.member_id = ssg_members.id
			WHERE
				event_id = ?
				AND attendance < 4
			ORDER BY last_changed_datetime DESC
			LIMIT 5';
		$query = $this->CI->db->query($sql, $event_id);
		foreach($query->result() as $row)
		{
			$member = new stdClass;
			$member->name = $row->name;
			$member->date = $row->date;

			$stats->last_changed[] = $member;
		}

		return $stats;
	}

	/**
	 * Alla anmälnings-submits går hit.
	 * Metoden listar ut om det handlar om en ny anmälan eller redigering.
	 *
	 * @param array $values Post-variabler
	 * @param int $member_id Om null laddas inloggade medlems-id.
	 * @return void
	 */
	public function submit_signup($values, $member_id)
	{
		//variabler
		$member_id = $member_id == null
			? $this->CI->member->id
			: $member_id;
		$redirect_url = 'signup/event/'. $values['event_id'];
		$event = $this->get_event($values['event_id']);

		//försöker anmäla till gammalt event
		if($event->is_old)
		{
			$this->CI->alerts->add_alert('danger', 'Du kan inte anmäla dig till gamla events.');
			redirect($redirect_url);
			return;
		}

		//input-validering
		if(
			!isset($values['event_id']) || !is_numeric($values['event_id'])
			|| !isset($values['group_id']) || !is_numeric($values['group_id'])
			|| !isset($values['role_id']) || !is_numeric($values['role_id'])
			|| !isset($values['attendance']) || !is_numeric($values['attendance'])
		)
		{
			log_message('error', "Input-sanering misslyckades i modell: Signups->submit()\n". print_r($values, true));
			$this->CI->alerts->add_alert('danger', 'Något gick snett. Din anmälan sparades inte.');
			redirect($redirect_url);
			return;
		}

		//input-sanering
		$values['message'] = strip_tags(trim($values['message'])); //ta bort html och trimma
		$values['message'] = str_replace(array("'", '"'), array('&apos;', '&quot;'), $values['message']); //fixa ' och "
		$values['message'] = strlen($values['message']) <= 0 ? null : $values['message']; //'' till null

		//kolla om en anmälan redan finns och kalla på new eller update-metoden
		$sql =
			'SELECT null
			FROM ssg_signups
			WHERE
				event_id = ?
				AND member_id = ?';
		$query = $this->CI->db->query($sql, array($values['event_id'], $member_id));
		$is_new = $query->num_rows() <= 0;


		//exekvera
		if($is_new)
		{
			$sql =
				'INSERT INTO ssg_signups(event_id, member_id, group_id, role_id, attendance, signed_datetime, last_changed_datetime, message)
				VALUES(?, ?, ?, ?, ?, NOW(), NOW(), ?)';
			$query = $this->CI->db->query($sql, array(
				$values['event_id'],
				$member_id,
				$values['group_id'],
				$values['role_id'],
				$values['attendance'],
				$values['message'],
			));
		}
		else
		{
			$sql =
				'UPDATE ssg_signups
				SET 
					event_id = ?,
					member_id = ?,
					group_id = ?,
					role_id = ?,
					attendance = ?,
					last_changed_datetime = NOW(),
					message = ?
				WHERE
					event_id = ?
					AND member_id = ?';
			$query = $this->CI->db->query($sql, array(
				$values['event_id'],
				$member_id,
				$values['group_id'],
				$values['role_id'],
				$values['attendance'],
				$values['message'],
				$values['event_id'],
				$member_id,
			));
		}
		
		//lägg till success-alert
		$this->CI->alerts->add_alert('success', 'Din anmälan sparades utan problem!');

		redirect($redirect_url);
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

		$start = $this->CI->db->escape($start);
		$end = $this->CI->db->escape($end);
		$sql =
			"SELECT id, title
			FROM ssg_events
			WHERE 
				($start <= ADDTIME(start_datetime, length_time) AND $end >= ADDTIME(start_datetime, length_time)) OR
				($start <= start_datetime AND $end >= start_datetime) OR
				($start >= start_datetime AND $end <= ADDTIME(start_datetime, length_time)) OR
				($start <= start_datetime AND $end >= ADDTIME(start_datetime, length_time))
			LIMIT 2"; //limit 2 eftersom man kan kolla om ett år långt event overlappar någonting och på så sätt lista skitmånga events
		$query = $this->CI->db->query($sql);

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
		$query = $this->CI->db->query($sql, array($start, $start));

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
		$query = $this->CI->db->query($sql);
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
					$this->CI->db->query($sql, array(
						$template->title,
						$next_occurance_start,
						$template->length_time,
						$template->type_id
					));
				}
			}
		}
	}
}
?>