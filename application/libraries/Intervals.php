<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Ser till att viss kod körs med jämna intervaller (dagligen, veckovis osv.)
 * Den här klassen använder sig av tabellen ssg_intervals.
 * Varje gång den laddas (när en användare besöker hemsidan) så kollar den med tabellen
 * när intervallens kod senast kördes. Om perioden sedan dess är högre än dess intervall
 * så körs koden och last_performedtime i tabellen uppdateras.
 * 
 * Tanken är att man ska kunna köra kod om det gått mer än 10 min, en dag, en vecka osv. sedan förra intervallen.
 * Just nu körs endast dagliga intervaller.
 */
class Intervals
{
	protected $CI;

	public function __construct()
	{
		// Assign the CodeIgniter super-object
		$this->CI =& get_instance();
		
		//hämta intervaller
		$interval = $this->get_interval();

		if($interval >= 1)
			$this->daily();
	}

	/**
	 * Se vilka intervall som behöver utföras.
	 *
	 * @return array
	 */
	private function get_interval()
	{
		$sql =
			'SELECT
				DATEDIFF(NOW(), last_performed) AS length_days
			FROM ssg_intervals';
		$query = $this->CI->db->query($sql);
		return $query->row()->length_days;
	}

	/**
	 * Körs en gång varje dag.
	 *
	 * @return void
	 */
	private function daily()
	{
		//moduler
		$this->CI->load->library('eventsignup');
		$this->CI->load->model('site/chat');

		//metoder
		$this->CI->eventsignup->create_auto_events();
		$this->archive_old_events();
		$this->remove_outdated_recesses();
		$this->CI->chat->prune_messages();
		$this->remove_old_global_alerts();
		$this->set_yearly_flairs();

		$this->update_interval();
	}

	/**
	 * Uppdatera när intervallen senast kördes
	 *
	 * @return void
	 */
	private function update_interval()
	{
		$sql =
			"UPDATE ssg_intervals
			SET last_performed = NOW()
			WHERE id = 0";
		$this->CI->db->query($sql);
	}

	/**
	 * Behandlar gamla, o-arkiverade, obligatoriska events.
	 * Anmäler aktiva medlemmar som "Oanmäld frånvaro" som inte anmält sig själva.
	 *
	 * @return void
	 */
	private function archive_old_events()
	{
		//moduler
		$this->CI->load->library('eventsignup');

		//variabler
		$events = array();

		//--Hämta relevanta events--
		$sql =
			'SELECT 
				ssg_events.id
			FROM ssg_events
			INNER JOIN ssg_event_types
				ON ssg_events.type_id = ssg_event_types.id
			WHERE 
				obligatory
				AND NOT archived
				AND DATE_FORMAT(ADDTIME(start_datetime, length_time), "%Y-%m-%d %H:%i") < NOW()';
		$query = $this->CI->db->query($sql);
		foreach($query->result() as $row)
			$events[] = $row->id;

		//--Bearbeta events--
		foreach($events as $event_id) //iterera events
		{
			$non_signups = $this->CI->eventsignup->get_non_signups($event_id);
			foreach($non_signups as $member) //iterera medlemmar
			{
				//lägg till Oanmäld frånvaro för alla non_signups
				$sql =
					'INSERT INTO ssg_signups(event_id, member_id, group_id, role_id, attendance, signed_datetime, last_changed_datetime)
					VALUES(?, ?, ?, ?, ?, NOW(), NOW())';
				$query = $this->CI->db->query($sql, array(
					$event_id,
					$member->id,
					10, //'Vilken som helst'
					25, //'Vad som helst'
					6, //'Oanmäld frånvaro'
				));
			}

			//arkivera
			$sql =
				'UPDATE ssg_events
				SET archived = 1
				WHERE id = ?';
			$query = $this->CI->db->query($sql, $event_id);
		}
		
	}

	/**
	 * Rensar gamla event-uppehåll från ssg_recesses
	 *
	 * @return void
	 */
	private function remove_outdated_recesses()
	{
		$sql =
			'DELETE FROM ssg_recesses
			WHERE DATE_ADD(start_date, INTERVAL length_days DAY) < NOW()';
		$this->CI->db->query($sql);
	}

	/**
	 * Rensa gamla global alerts (ssg_alerts) vars datetime har passerat.
	 *
	 * @return void
	 */
	private function remove_old_global_alerts()
	{
		$sql =
			'DELETE FROM ssg_global_alerts
			WHERE expiration_date < NOW()';
		$this->CI->db->query($sql);
	}

	/**
	 * Delat ut forum-flairs efter medlemsår.
	 *
	 * @return void
	 */
	private function set_yearly_flairs()
	{
		$now = time();
		$seconds_in_year = 365 * 24 * 3600;
		$medals = array(1 => 11, 3 => 12, 5 => 13, 7 => 14, 10 => 15); //key: år, value = medalj-id

		//hämta users
		$sql =
			'SELECT u.user_id, u.username, u.user_regdate
			FROM phpbb_users u
			INNER JOIN ssg_members m
				ON u.user_id = m.phpbb_user_id
			WHERE
				m.is_active #endast aktiva medlemmar
				AND u.group_id != 6 #inga bots
				AND u.user_id != 1 #inte annonymous-användaren
			ORDER BY user_regdate ASC';
		$users = $this->CI->db->query($sql)->result();
		
		foreach($users as $user)
		{
			//lista ut år sedan registrering (floored)
			$seconds = $now- $user->user_regdate;
			$user->years = floor($seconds / $seconds_in_year);

			//lista ut lämplig medalj
			if($user->years >= 10)
				$user->medal = $medals[10];
			else if($user->years >= 7)
				$user->medal = $medals[7];
			else if($user->years >= 5)
				$user->medal = $medals[5];
			else if($user->years >= 3)
				$user->medal = $medals[3];
			else if($user->years >= 1)
				$user->medal = $medals[1];
		}

		//rensa gamla års-flairs
		$sql =
			'DELETE FROM phpbb_flair_users
			WHERE
				flair_id = 11
				OR flair_id = 12
				OR flair_id = 13
				OR flair_id = 14
				OR flair_id = 15';
		$this->CI->db->query($sql);

		//lägg till i phpbb_flair_users
		foreach($users as $user)
		{
			//skippa de som inte får någon medalj (varit medlem i < 1 år)
			if(!isset($user->medal))
				continue;

			//insert
			$data = array('user_id' => $user->user_id, 'flair_id' => $user->medal);
			$this->CI->db->insert('phpbb_flair_users', $data);
		}
	}
}
?>