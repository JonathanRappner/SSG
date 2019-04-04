<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Ser till att viss kod körs med jämna intervaller (dagligen, veckovis osv.)
 * Den här klassen använder sig av tabellen ssg_intervals.
 * Varje gång den laddas (när en användare besöker hemsidan) så kollar den med tabellen
 * när intervallens kod senast kördes. Om perioden sedan dess är högre än dess intervall
 * så körs koden och last_performedtime i tabellen uppdateras.
 * 
 * Kom ihåg att t.ex. ett dagligt intervall inte nödvändigtvis utförs varje dag.
 * 
 * Intervall-ID:
 * 1 = Dagligen
 * 2 = Veckovis
 * 3 = Var 10:de minut
 */
class Intervals
{
	protected $CI;

	public function __construct()
	{
		// Assign the CodeIgniter super-object
		$this->CI =& get_instance();
		
		//hämta intervaller
		$intervals = $this->get_intervals();

		assert(count($intervals) >= 2);
		if($intervals[1]) $this->daily();
		if($intervals[2]) $this->weekly();
		// if($intervals[3]) $this->ten_minutes(); kör inte 10-min-metoden här, API-kontrollern använder den för att cacha streamer-data
	}

	/**
	 * Se vilka intervall som behöver utföras.
	 *
	 * @return array
	 */
	private function get_intervals()
	{
		//variabler
		$intervals = array();

		$sql =
			'SELECT
				id,
				DATEDIFF(NOW(), DATE(last_performed)) >= length AS perform #avrundat till dagar
			FROM ssg_intervals';
		$query = $this->CI->db->query($sql);
		foreach ($query->result() as $row)
			$intervals[$row->id] = $row->perform; //$interval[id] = needs performing?

		return $intervals;
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


		//metoder
		$this->CI->eventsignup->create_auto_events();
		$this->archive_old_events();
		$this->remove_outdated_recesses();

		$this->update_interval(1);
	}

	/**
	 * Körs en gång i veckan.
	 *
	 * @return void
	 */
	private function weekly()
	{
		//metoder här

		$this->update_interval(2);
	}

	/**
	 * Uppdatera när intervallen senast kördes
	 *
	 * @param int $interval_id 1=daily, 2=weekly, 3=10min
	 * @return void
	 */
	private function update_interval($interval_id)
	{
		$sql =
			"UPDATE ssg_intervals
			SET last_performed = NOW()
			WHERE id = ?";
		$this->CI->db->query($sql, array($interval_id));
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
		//TODO insert coad
	}
}
?>