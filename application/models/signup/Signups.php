<?php
/**
 * Funktioner för anmälningar till events
 */
class Signups extends CI_Model
{

	/**
	 * Konstruktor
	 */
	public function __construct()
	{
		parent::__construct();
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
		$query = $this->db->query($sql, array($event_id, $member_id));
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
		$query = $this->db->query($sql, array($event_id));
		foreach ($query->result() as $row)
		{
			//input-sanering
			$row->message = trim(strip_tags($row->message));
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
		$query = $this->db->query($sql, $event_id);
		
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
		$query = $this->db->query($sql, $member_id);
		$last_signup = $query->row();

		if($query->num_rows() > 0)
			return $last_signup;
		
		//hitta medlemmens angivna roll & grupp
		$sql =
			"SELECT role_id, group_id
			FROM ssg_members
			WHERE id = ?";
		$query = $this->db->query($sql, $member_id);
		$member_defaults = $query->row();
		
		//även om medlemmen inte har assign:ade värden så returnerar i alla fall metoden ett korrekt obj med null-värden
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
		$query = $this->db->query($sql, $event_id);
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
		$query = $this->db->query($sql, $event_id);
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
	public function submit($values, $member_id)
	{
		//moduler
		$this->load->model('signup/Events');

		//variabler
		$member_id = $member_id == null ? $this->member->id : $member_id;
		$redirect_url = 'signup/event/'. $values['event_id'];
		$event = $this->Events->get_event($values['event_id']);

		//försöker anmäla till gammalt event
		if($event->is_old)
		{
			$this->alerts->add_alert('danger', 'Du kan inte anmäla dig till gamla events.');
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
			$this->alerts->add_alert('danger', 'Något gick snett. Din anmälan sparades inte.');
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
		$query = $this->db->query($sql, array($values['event_id'], $member_id));
		$is_new = $query->num_rows() <= 0;


		//exekvera
		if($is_new)
		{
			$sql =
				'INSERT INTO ssg_signups(event_id, member_id, group_id, role_id, attendance, signed_datetime, last_changed_datetime, message)
				VALUES(?, ?, ?, ?, ?, NOW(), NOW(), ?)';
			$query = $this->db->query($sql, array(
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
			$query = $this->db->query($sql, array(
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
		$this->alerts->add_alert('success', 'Din anmälan sparades utan problem!');

		redirect($redirect_url);
	}
}