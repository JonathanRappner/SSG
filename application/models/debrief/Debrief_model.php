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
				
				INNER JOIN ssg_roles r
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

	public function score_string($score)
	{
		if(!$score)
			return null;

		$string = "{$score}<span class='score_string'>";
		for ($i = 0; $i < $score; $i++)
			$string .= '⭐';

		return "$string</span>";
	}
}
