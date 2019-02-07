<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Hanterar anmälningar
 */
class Admin_signups implements Adminpanel
{
	protected $CI;
	private
		$results_per_page = 30, //medlemslistan i huvudvyn
		$page,
		$total_members,
		$signups;

	public function __construct()
	{
		// Assign the CodeIgniter super-object
		$this->CI =& get_instance();
	}

	public function main($var1, $var2)
	{
		//variabler
		$this->view = $var1 != null ? $var1 : 'main';
		

		if($this->view == 'main') //main
		{
			assert($var2 == null || is_numeric($var2), "Inkorrekt sidnummer: $var2");
			$this->page = $var2 != null ? $var2 : 0;
			$this->total_members = $this->CI->db->query('SELECT COUNT(*) AS count FROM ssg_members')->row()->count;
			$this->members = $this->get_members($this->page, $this->results_per_page);
		}
		else if($this->view == 'member') //formulär ny
		{
			$this->member_id = $var2;
			$this->signups = $this->get_signups($this->member_id);
		}
	}

	public function view()
	{
		if($this->view == 'main') //main
			$this->view_main();
		else if($this->view == 'member')
			$this->view_member();
	}

	private function view_main()
	{
		//Medlemstabell
		echo '<div id="wrapper_member_table" class="table-responsive table-sm">';
			echo '<table class="table table-hover clickable">';
				echo '<thead class="table-borderless">';
					echo '<tr>';
						echo '<th scope="col">Namn</th>';
						echo '<th scope="col">Grupp</th>';
						echo '<th scope="col">Antal anmälningar</th>';
					echo '</tr>';
				echo '</thead>';
				echo '<tbody>';
					if(count($this->members) > 0)
						foreach($this->members as $member)
						{
							echo '<tr data-url="'. base_url('signup/admin/signups/member/'. $member->id) .'">';
							
								//Namn
								echo '<td scope="row" class="font-weight-bold">';
									echo $member->name;
								echo '</td>';
							
								//Grupp
								echo '<td>';
									echo group_icon($member->group_code);
									echo $member->group_name;
								echo '</td>';
							
								//Antal anmälningar
								echo '<td>';
									echo $member->signups_count;
								echo '</td>';

							echo '</tr>';
						}
					else
						echo '<tr><td colspan="3" class="text-center">&ndash; Inga medlemmar &ndash;</td></tr>';
				echo '</tbody>';
			echo '</table>';

			//pagination
			echo pagination($this->page, $this->total_members, $this->results_per_page, base_url('signup/admin/signups/main/'), 'wrapper_member_table');

		echo '</div>';
	}

	private function view_member()
	{
		echo 'lista medlems signups<pre>';
		print_r($this->signups);
		echo '</pre>';
	}
	
	private function get_members($page, $results_per_page)
	{
		$sql =
			'SELECT
				m.id, m.name, is_active,
				r.name AS role_name,
				g.name AS group_name,
				g.code AS group_code,
				(SELECT COUNT(*) AS count FROM ssg_signups WHERE member_id = m.id) AS signups_count
			FROM ssg_members m
			LEFT JOIN ssg_roles r
				ON m.role_id = r.id
			LEFT JOIN ssg_groups g
				ON m.group_id = g.id
			ORDER BY
				CASE
					WHEN g.id IS NULL THEN 0
					WHEN g.id IS NOT NULL THEN 1
				END DESC,
				g.sorting ASC,
				name ASC
			LIMIT ?, ?';
		return $this->CI->db->query($sql, array($page * $results_per_page, $results_per_page))->result();
	}

	private function get_signups($member_id)
	{
		///////////grupp, befattning
		$sql =
			'SELECT
				event_id, attendance
			FROM ssg_signups
			WHERE member_id = ?';
		return $this->CI->db->query($sql, $this->member_id)->result();
	}

	/**
	 * Lägg till auto-event.
	 *
	 * @param object $vars POST-variabler.
	 * @return void
	 */
	// private function add_($vars)
	// {
	// 	//input-sanering
	// 	assert(isset($vars), 'Post-variabler saknas.');
	// 	assert(!empty($vars->title), "title: $vars->title");
	// 	assert(isset($vars->type_id) && is_numeric($vars->type_id), "type_id: $vars->type_id");
	// 	assert(!empty($vars->day) && key_exists($vars->day, $this->days_se), "day: $vars->day");
	// 	assert(!empty($vars->start_time) && preg_match('/(\d{2}:\d{2}){1}/', $vars->start_time), "start_time: $vars->start_time");
	// 	assert(!empty($vars->length_time) && preg_match('/(\d{2}:\d{2}){1}/', $vars->length_time), "start_time: $vars->length_time");
		
	// 	$data = array
	// 	(
	// 		'title' => $vars->title,
	// 		'start_day' => $vars->day,
	// 		'start_time' => $vars->start_time,
	// 		'length_time' => $vars->length_time,
	// 		'type_id' => $vars->type_id,
	// 	);
	// 	$this->CI->db->insert('ssg_auto_events', $data);
	// }

	/**
	 * Uppdatera auto-event.
	 *
	 * @param object $vars POST-variabler.
	 * @return void
	 */
	// private function update_($vars)
	// {
	// 	//input-sanering
	// 	assert(isset($vars), 'Post-variabler saknas.');
	// 	assert(isset($vars->auto_event_id) && is_numeric($vars->auto_event_id), "auto_event_id: $vars->auto_event_id");
	// 	assert(!empty($vars->title), "title: $vars->title");
	// 	assert(isset($vars->type_id) && is_numeric($vars->type_id), "type_id: $vars->type_id");
	// 	assert(!empty($vars->day) && key_exists($vars->day, $this->days_se), "day: $vars->day");
	// 	assert(!empty($vars->start_time) && preg_match('/(\d{2}:\d{2}){1}/', $vars->start_time), "start_time: $vars->start_time");
	// 	assert(!empty($vars->length_time) && preg_match('/(\d{2}:\d{2}){1}/', $vars->length_time), "start_time: $vars->length_time");

	// 	$data = array
	// 	(
	// 		'title' => $vars->title,
	// 		'start_day' => $vars->day,
	// 		'start_time' => $vars->start_time,
	// 		'length_time' => $vars->length_time,
	// 		'type_id' => $vars->type_id,
	// 	);
	// 	$this->CI->db->where('id', $vars->auto_event_id)->update('ssg_auto_events', $data);
	// }

	public function get_code()
	{
		return 'signups';
	}

	public function get_title()
	{
		return 'Anmälningar';
	}

	public function get_permissions_needed()
	{
		return array('s0', 's1', 'grpchef');
	}
}
?>