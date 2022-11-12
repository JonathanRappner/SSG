<?php
/**
 * Funktioner för Min sida
 * Kan även ladda sid-vy för annan medlem.
 */
class Mypage extends CI_Model
{
	private
		$loaded_member,
		$attendance_total,
		$attendance_quarter,
		$event_types,
		$deadline,
		$groups,
		$signups,
		$page_data;
	public
		$since_date;
	
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Börja ladda data
	 *
	 * @param int $other_member_id Om annan medlems data ska laddas.
	 * @param int $page Pagination till anmälningar-tabellen.
	 * @return void
	 */
	public function init($other_member_id, $page)
	{
		//variabler
		$this->page_data = new stdClass;
		$this->page_data->results_per_page = 20;
		$this->page_data->page = $page;
		$attendance_colors = array(1=>'#28a745', '#285ca6', '#fea500', '#fc302b', '#848484', '#7B23A8');
		$this->since_date = preg_match('/^([12]\d{3}-(0[1-9]|1[0-2])-(0[1-9]|[12]\d|3[01]))$/', $this->input->get('since_date') ?? '') //visa pie-charts för ett specifikt datum
			? $this->input->get('since_date')
			: null;

		//ladda annan medlem eller använd inloggade medlemen
		if(isset($other_member_id) && $other_member_id != $this->member->id) //ladda inloggad medlem om $other_member_id är dennes egna id
		{
			if($this->permissions->has_permissions(array('s0', 's1', 'grpchef'))) //success
			{
				$this->loaded_member = $this->member->get_member_data($other_member_id);
				$this->alerts->add_alert('info', 'Du tittar på "Min sida" för en annan medlem: <strong>'. $this->loaded_member->name .'</strong>');
			}
			else //fail
			{
				$this->alerts->add_alert('danger', 'Du har inte tillräckliga rättigheter för att se andra medlemmars "Min sida".');
				redirect('signup/mypage');
			}
		}
		else
			$this->loaded_member = $this->member;

		//--Närvaro totalt--
		$where = $this->since_date ? 'AND ssg_events.start_datetime >= '. $this->db->escape($this->since_date) : null;
		$sql =
			'SELECT
				attendance-0 AS id,
				attendance AS name,
				COUNT(attendance) AS count
			FROM ssg_signups
			INNER JOIN ssg_events
				ON ssg_signups.event_id = ssg_events.id
			WHERE
				member_id = ?
				'. $where .'
			GROUP BY attendance
			ORDER BY attendance-0 ASC';
		$query = $this->db->query($sql, $this->loaded_member->id);
		foreach($query->result() as $row)
		{
			$row->color = $attendance_colors[$row->id];
			$this->attendance_total[] = $row;
		}
		
		//--Anmälningar efter deadline--
		$where = $this->since_date ? 'AND ssg_events.start_datetime >= '. $this->db->escape($this->since_date) : null;
		$sql =
			'SELECT
				signed_datetime < DATE_FORMAT(ssg_events.start_datetime, "%Y-%m-%d 00:00:00") AS good_boy
			FROM ssg_signups
			INNER JOIN ssg_events
				ON ssg_signups.event_id = ssg_events.id
			WHERE
				member_id = ?
				AND ssg_events.obligatory
				'. $where;
		$query = $this->db->query($sql, $this->loaded_member->id);
		$this->deadline = new stdClass;
		$this->deadline->good_boy = 0;
		$this->deadline->bad_boy = 0;
		foreach($query->result() as $row)
		{
			if($row->good_boy)
				$this->deadline->good_boy++;
			else
				$this->deadline->bad_boy++;
		}

		//--Operation vs. Träning--
		$where = $this->since_date ? 'AND ssg_events.start_datetime >= '. $this->db->escape($this->since_date) : null;
		$sql =
			'SELECT
				ssg_event_types.title,
				COUNT(*) AS count
			FROM ssg_signups
			INNER JOIN ssg_events
				ON ssg_signups.event_id = ssg_events.id
			INNER JOIN ssg_event_types
				ON ssg_events.type_id = ssg_event_types.id
			WHERE
				member_id = ?
				AND attendance < 4
				AND ssg_events.obligatory
				'. $where .'
			GROUP BY ssg_events.type_id
			ORDER BY attendance ASC';
		$query = $this->db->query($sql, $this->loaded_member->id);
		foreach($query->result() as $row)
			$this->event_types[] = $row;
		
		//--Anmälningar till grupp--
		$where = $this->since_date ? 'AND ssg_events.start_datetime >= '. $this->db->escape($this->since_date) : null;
		$sql =
			'SELECT
				COUNT(*) AS count,
				ssg_groups.name AS name
			FROM ssg_signups
			INNER JOIN ssg_events
				ON ssg_signups.event_id = ssg_events.id
			INNER JOIN ssg_groups
				ON ssg_signups.group_id = ssg_groups.id
			WHERE
				member_id = ?
				AND ssg_events.obligatory
				AND NOT ssg_groups.dummy
				'. $where .'
			GROUP BY ssg_signups.group_id
			ORDER BY
				count DESC';
		$query = $this->db->query($sql, $this->loaded_member->id);
		$this->groups = array();
		foreach($query->result() as $row)
			$this->groups[] = $row;
		
		//--Anmälning till befattning--
		$where = $this->since_date ? 'AND ssg_events.start_datetime >= '. $this->db->escape($this->since_date) : null;
		$sql =
			'SELECT
				COUNT(*) AS count,
				ssg_roles.name AS name
			FROM ssg_signups
			INNER JOIN ssg_events
				ON ssg_signups.event_id = ssg_events.id
			INNER JOIN ssg_roles
				ON ssg_signups.role_id = ssg_roles.id
			WHERE
				member_id = ?
				AND ssg_events.obligatory
				AND NOT ssg_roles.dummy
				'. $where .'
			GROUP BY ssg_signups.role_id
			ORDER BY
				count DESC';
		$query = $this->db->query($sql, $this->loaded_member->id);
		$this->roles = array();
		foreach($query->result() as $row)
			$this->roles[] = $row;
		
		//--Anmälningar--
		$this->signups = array();
		$sql =
			'SELECT
				ssg_events.title AS event_title,
				event_id,
				DATE_FORMAT(start_datetime, "%Y-%m-%d") AS start_date,
				ADDTIME(start_datetime, length_time) < NOW() AS is_old,
				ssg_signups.group_id,
				ssg_groups.name AS group_name,
				ssg_groups.code AS group_code,
				ssg_roles.name AS role_name,
				attendance-0 AS attendance_id
			FROM ssg_signups
			INNER JOIN ssg_events
				ON ssg_signups.event_id = ssg_events.id
			INNER JOIN ssg_groups
				ON ssg_signups.group_id = ssg_groups.id
			INNER JOIN ssg_roles
				ON ssg_signups.role_id = ssg_roles.id
			WHERE ssg_signups.member_id = ?
			ORDER BY
				ssg_events.start_datetime DESC
			LIMIT ?, ?';
		$query = $this->db->query($sql, array($this->loaded_member->id, $page * $this->page_data->results_per_page, $this->page_data->results_per_page));
		$this->signups = $query->result();
		
		//total signups
		$this->page_data->total_signups = $this->db->query('SELECT COUNT(*) AS count FROM ssg_signups WHERE member_id = ?', $this->loaded_member->id)->row()->count;
	}

	/**
	 * Get all stats gathered into one object.
	 *
	 * @return object
	 */
	public function get_stats()
	{
		$output = new stdClass;
		$output->attendance_total = $this->attendance_total;
		$output->attendance_quarter = $this->attendance_quarter;
		$output->event_types = $this->event_types;
		$output->deadline = $this->deadline;
		$output->groups = $this->groups;
		$output->roles = $this->roles;
		$output->signups = $this->signups;
		$output->page_data = $this->page_data;

		return $output;
	}

	/**
	 * Den laddade medlemmen.
	 * Medlemmen vars data visas på sidan.
	 *
	 * @return void
	 */
	public function get_loaded_member()
	{
		return $this->loaded_member;
	}
}