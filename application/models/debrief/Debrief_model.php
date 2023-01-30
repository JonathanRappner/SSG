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
				start_datetime <= NOW()
			ORDER BY start_datetime DESC
			LIMIT ?, ?';
		$query = $this->db->query($sql, array($this->member->id, $page * $results_per_page, $results_per_page));
		foreach ($query->result() as $row) {
			$row->current_member_attendance = $this->eventsignup->get_member_attendance($row->id, $this->member->id);

			$events[] = $row;
		}


		return $events;
	}


	/**
	 * Overview för ett event
	 * 
	 * @param mixed $event_id
	 * @return null
	 */
	public function get_overview($event_id)
	{
		// Variabler
		$overview = new stdClass;
		$overview->groups = array();

		// Hämta alla aktiva grupper
		$sql =
			'SELECT id, code, name
			FROM ssg_groups
			WHERE
				active
				AND NOT dummy
				AND selectable
			ORDER BY
				enabler ASC,
				sorting ASC';
		$query = $this->db->query($sql);
		foreach ($query->result() as $row) {
			$group = new stdClass;
			$group->id = $row->id;
			$group->code = $row->code;
			$group->name = $row->name;

			$overview->groups[] = $group;
		}

		// Gå igenom varje grupp
		$overview->total_signups = 0;
		$overview->total_debriefs = 0;
		$overview->total_score = 0;
		foreach ($overview->groups as $grp) {
			// Hämta alla medlemmar med positiva anmälningar och dess poäng (om de har en review)
			$sql =
				'SELECT s.member_id, m.name AS member_name, d.score
				FROM ssg_signups s

				INNER JOIN ssg_members m
					ON s.member_id = m.id
				
				LEFT OUTER JOIN ssg_roles r
					ON m.role_id = r.id

				LEFT OUTER JOIN ssg_debriefs d
					ON s.member_id = d.member_id AND s.event_id = d.event_id
				WHERE
					s.event_id = ?
					AND s.group_id = ?
					AND s.attendance-0 <= 3 # endast positiva anmälningar
				ORDER BY
					r.sorting ASC';
			$query = $this->db->query($sql, array($event_id, $grp->id));
			$grp->signups = array();
			foreach ($query->result() as $row) {
				$signup = new stdClass;
				$signup->member_id = $row->member_id;
				$signup->member_name = $row->member_name;
				$signup->score = $row->score;
				$signup->score_string = $this->score_string($row->score);

				$grp->signups[] = $signup;

				$overview->total_signups++;
			}

			// Antalet anmälningar
			$grp->signups_count = count($grp->signups);

			// Genomsnittspoängen för alla debriefs
			$grp->reviews_count = 0;
			$total_score = 0;
			foreach ($grp->signups as $s) {
				if ($s->score) {
					$total_score += $s->score;
					$grp->reviews_count++;

					$overview->total_debriefs++;
					$overview->total_score += $s->score;
				}
			}
			$grp->reviews_score_avg = $grp->reviews_count > 0
				? str_replace('.', ',', round($total_score / $grp->reviews_count, 1)) // avrunda och byt punkt till kommatecken
				: '-';
		}

		// Genomsnittsbetyget för hela eventet
		$overview->score_avg = $overview->total_score > 0
			? str_replace('.', ',', round($overview->total_score / $overview->total_debriefs, 1)) // avrunda och byt punkt till kommatecken
			: '-';

		return $overview;
	}

	/**
	 * Hämta state-JSON-sträng med data för event-sidan
	 * @param int $event_id
	 * @param int $last_modified_prev Senast ändrad-datumsträng (ex: '2022-10-08 13:21:42') när klientens state uppdaterades.
	 */
	public function get_event_state($event_id, $last_modified_prev = 0)
	{
		// Kolla om några nya debriefs har skrivits sedan senast
		///////////////////////////gör detta i api:et istället
		// $sql =
		// 	'SELECT last_modified
		// 	FROM ssg_debriefs
		// 	WHERE event_id = ?
		// 	ORDER BY last_modified DESC
		// 	LIMIT 1';
		// $query = $this->db->query($sql, array($event_id));
		// $last_modified = $query->row()->last_modified ?? -1;

		// if ($last_modified < $last_modified_prev) // inga nya debriefs har skrivits
		// 	return '{}';


		// Hämta alla aktiva grupper
		$data = new stdClass;
		$data->groups = array();
		$sql =
			'SELECT id, code, name
			FROM ssg_groups
			WHERE
				active
				AND NOT dummy
				AND selectable
			ORDER BY
				enabler ASC,
				sorting ASC';
		$query = $this->db->query($sql);
		foreach ($query->result() as $row) {
			$group = new stdClass;
			$group->id = $row->id;
			$group->code = $row->code;
			$group->name = $row->name;

			$data->groups[] = $group;
		}


		// hämta signups och debrief-betyg
		foreach ($data->groups as $grp) {
			// Hämta alla medlemmar med positiva anmälningar och dess poäng (om de har en review)
			$sql =
				'SELECT
					s.member_id,
					m.name AS member_name,
					d.score
				FROM ssg_signups s

				INNER JOIN ssg_members m
					ON s.member_id = m.id
				
				LEFT OUTER JOIN ssg_roles r
					ON m.role_id = r.id

				LEFT OUTER JOIN ssg_debriefs d
					ON s.member_id = d.member_id AND s.event_id = d.event_id
				WHERE
					s.event_id = ?
					AND s.group_id = ?
					AND s.attendance-0 <= 3 # endast positiva anmälningar
				ORDER BY
					r.sorting ASC';
			$query = $this->db->query($sql, array($event_id, $grp->id));
			$grp->signups = array();
			foreach ($query->result() as $row) {
				$signup = new stdClass;
				$signup->id = $row->member_id;
				$signup->name = $row->member_name;
				$signup->score = $row->score - 0;

				$grp->signups[] = $signup;
			}
			$grp->signups = $grp->signups;
		}


		// Inloggade medlemmens signup
		$signup = $this->eventsignup->get_signup($event_id, $this->member->id);
		if ($signup) {
			$data->signup_attendance_id = $signup->attendance_id - 0;
			$data->signup_attendance_name = $signup->attendance_name ?? null;
		} else {
			$data->signup_attendance_id = 0;
			$data->signup_attendance_name = null;
		}


		return json_encode($data);
	}

	/**
	 * Hämta state-JSON-sträng med data för grupp-sidan
	 * @param int $event_id
	 * @param int $group_id
	 * @param int $last_modified_prev Senast ändrad-datumsträng (ex: '2022-10-08 13:21:42') när klientens state uppdaterades.
	 */
	public function get_group_state($event_id, $group_id, $last_modified_prev = 0)
	{
		$data = new stdClass;

		// Hämta alla medlemmar med positiva anmälningar och dess poäng (om de har en review)
		$sql =
			'SELECT
				s.member_id,
				m.name AS member_name,
				m.phpbb_user_id,
				d.score,
				d.review_good,
				d.review_bad,
				d.review_improvement,
				d.review_tech
			FROM ssg_signups s

			INNER JOIN ssg_members m
				ON s.member_id = m.id
			
			LEFT OUTER JOIN ssg_roles r
				ON m.role_id = r.id

			LEFT OUTER JOIN ssg_debriefs d
				ON s.member_id = d.member_id AND s.event_id = d.event_id
			WHERE
				s.event_id = ?
				AND s.group_id = ?
				AND s.attendance-0 <= 3 # endast positiva anmälningar
			ORDER BY
				r.sorting ASC';
		$query = $this->db->query($sql, array($event_id, $group_id));
		$data->signups = array();
		foreach ($query->result() as $row) {
			$signup = new stdClass;
			$signup->id = $row->member_id;
			$signup->name = $row->member_name;
			$signup->avatar_url = $this->member->get_phpbb_avatar($row->phpbb_user_id);
			$signup->score = $row->score - 0;
			$signup->review_good = $row->review_good ? nl2br($row->review_good) : null;
			$signup->review_bad = $row->review_good ? nl2br($row->review_bad) : null;
			$signup->review_improvement = $row->review_good ? nl2br($row->review_improvement) : null;
			$signup->review_tech = $row->review_good ? nl2br($row->review_tech) : null;

			$data->signups[] = $signup;
		}

		return json_encode($data);
	}

	/**
	 * HTML-stjärnor
	 * @param int $score Betyg
	 */
	public function score_string($score)
	{
		if (!$score)
			return null;

		$string = "<span class='score_string'>";
		for ($i = 0; $i < $score; $i++)
			$string .= '<img class="star" src="' . base_url('images/star.svg') . '" />';

		return "$string</span>";
	}

	/**
	 * Spara värden till anmälan och debrief.
	 * @param array $data POST-variabler från formuläret
	 */
	public function submit($data) {
		$data = (object)$data;

		// Parameter-hygien
		if(
			!$data
			|| !isset($data->event_id) || !is_numeric($data->event_id)
			|| !isset($data->member_id) || !is_numeric($data->member_id)
			|| !isset($data->score) || !is_numeric($data->score)
			|| !isset($data->group) || !is_numeric($data->group)
			|| !isset($data->role) || !is_numeric($data->role)
			|| !isset($data->attendance) || !is_numeric($data->attendance)
			|| !isset($data->review_good) || strlen($data->review_good) <= 0
			|| !isset($data->review_bad) || strlen($data->review_bad) <= 0
		)
		{
			if(ENVIRONMENT == 'development') echo '<pre>'. print_r($data, true) .'</pre>';
			die('Parametrar saknas eller är felaktiga.');
		}

		// Får bara ändra sin egen debrief om man inte är admin
		if($data->member_id != $this->member->id && !$this->permissions->has_permissions(array('s0', 's1', 'grpchef')))
			die('Du har inte rättigheterna för att medlemmens debrief.');

		// Uppdatera grupp och befattning som spelades
		$this->db->update(
			'ssg_signups', // table
			array('group_id' => $data->group, 'role_id' => $data->role, 'attendance' => $data->attendance), // values
			array('event_id' => $data->event_id, 'member_id' => $data->member_id) // where
		);


		// -- Skapa eller uppdatera debrief --
		
		// ta bort variabler som inte ska in i ssg_debriefs
		$group_id = $data->group;
		unset($data->group);
		unset($data->role);
		unset($data->attendance);

		// exekvera
		$this->db->replace('ssg_debriefs', $data);

		redirect(base_url("debrief/group/{$data->event_id}/{$group_id}"));
	}
}
