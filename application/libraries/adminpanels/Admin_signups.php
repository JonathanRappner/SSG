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
		$signups,
		$signup,
		$events,
		$member_id,
		$member,
		$groups,
		$roles;

	public function __construct()
	{
		// Assign the CodeIgniter super-object
		$this->CI =& get_instance();
	}

	public function main($var1, $var2, $var3)
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
		else if($this->view == 'member') //medlems-vy: lista anmälningar
		{
			$this->member_id = $var2;
			$this->member = $this->CI->db->query('SELECT id, name FROM ssg_members WHERE id = ?', $this->member_id)->row();
			$this->page = $var3 != null ? $var3 : 0;
			$this->signups = $this->get_signups($this->member_id, $this->page, $this->results_per_page);
			$this->total_signups = $this->CI->db->query('SELECT COUNT(*) AS count FROM ssg_signups WHERE member_id = ?', $this->member_id)->row()->count;
		}
		else if($this->view == 'new') //formulär: ny
		{
			$this->member_id = $var2;
			$this->member = $this->CI->db->query('SELECT id, name FROM ssg_members WHERE id = ?', $this->member_id)->row();
			$this->groups = $this->get_groups();
			$this->roles = $this->get_roles();
			$this->events = $this->get_events($this->member_id);
		}
		else if($this->view == 'edit') //formulär: redigera
		{
			list($event_id, $this->member_id) = explode('-', $var2);
			$this->member = $this->CI->db->query('SELECT id, name FROM ssg_members WHERE id = ?', $this->member_id)->row();
			$this->signup = $this->get_signup($event_id, $this->member_id);
			$this->groups = $this->get_groups();
			$this->roles = $this->get_roles();
		}
		else if($this->view == 'insert') //skapa ny
		{
			//variabler
			$vars = (object)$this->CI->input->post();

			//exekvera
			$this->insert($vars);

			//success
			$this->CI->alerts->add_alert('success', 'Anmälan skapades utan problem.');
			redirect('signup/admin/signups');
		}
		else if($this->view == 'update') //spara ändringar
		{
			//variabler
			$vars = (object)$this->CI->input->post();

			//exekvera
			$this->update($vars);

			//success
			$this->CI->alerts->add_alert('success', 'Ändringarna sparades utan problem.');
			redirect('signup/admin/signups');
		}
		else if($this->view == 'delete_confirm') //borttagning bekräftan
		{
			list($event_id, $this->member_id) = explode('-', $var2);
			$this->event = $this->CI->db->query('SELECT id, title, DATE_FORMAT(start_datetime, "%Y-%m-%d") AS start_date FROM ssg_events WHERE id = ?', $event_id)->row();
			$this->member = $this->CI->db->query('SELECT id, name FROM ssg_members WHERE id = ?', $this->member_id)->row();
		}
		else if($this->view == 'delete') //ta bort
		{
			list($event_id, $this->member_id) = explode('-', $var2);
			assert(isset($event_id) && is_numeric($event_id));
			assert(isset($this->member_id) && is_numeric($this->member_id));
			
			$this->CI->db->delete('ssg_signups', array('event_id'=> $event_id, 'member_id'=>$this->member_id));

			//success
			$this->CI->alerts->add_alert('success', 'Anmälan togs bort utan problem.');
			redirect('signup/admin/signups');
		}
	}

	public function view()
	{
		if($this->view == 'main') //main
			$this->view_main();
		else if($this->view == 'member')
			$this->view_member();
		else if($this->view == 'new') //formulär: ny
			$this->view_form();
		else if($this->view == 'edit') //formulär: redigera
			$this->view_form();
		else if($this->view == 'delete_confirm') //borttagning bekräftan
			$this->view_delete_confirm();
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
		//moduler
		$this->CI->load->library('attendance');

		//breadcrumbs
		echo '<nav aria-label="breadcrumb"><ol class="breadcrumb">';
			echo '<li class="breadcrumb-item"><a href="'. base_url('signup/admin/signups') .'">Hem</a></li>';
			echo '<li class="breadcrumb-item active" aria-current="page">'. $this->member->name .'</li>';
		echo '</ol></nav>';

		echo '<a href="'. base_url('signup/admin/signups/new/'. $this->member->id) .'" class="btn btn-success">Skapa ny anmälan <i class="fas fa-plus-circle"></i></a>';

		echo '<hr>';
		
		echo '<h5>'. $this->member->name .'s anmälningar</h5>';

		//Anmälningar
		echo '<div id="wrapper_signups_table" class="table-responsive table-sm">';
			echo '<table class="table table-hover clickable">';
				echo '<thead class="table-borderless">';
					echo '<tr>';
						echo '<th scope="col">Event</th>';
						echo '<th scope="col">Datum</th>';
						echo '<th scope="col">Grupp</th>';
						echo '<th scope="col">Befattning</th>';
						echo '<th scope="col">Närvaro</th>';
						echo '<th scope="col">Ta bort</th>';
					echo '</tr>';
				echo '</thead>';
				echo '<tbody>';
					if(count($this->signups) > 0)
						foreach($this->signups as $signup)
						{
							$attendance = $this->CI->attendance->get_type_by_id($signup->attendance_id);
							echo '<tr data-url="'. base_url('signup/admin/signups/edit/'. $signup->event_id .'-'. $this->member_id) .'">';
							
								//Event
								echo '<td scope="row" class="font-weight-bold">';
									echo $signup->event_title;
								echo '</td>';
							
								//Datum
								echo '<td>';
									echo $signup->start_date;
								echo '</td>';
							
								//Grupp
								echo '<td>';
									echo group_icon($signup->group_code);
									echo $signup->group_name;
								echo '</td>';
							
								//Befattning
								echo '<td>';
									echo $signup->role_name;
								echo '</td>';
							
								//Närvaro
								echo '<td>';
									echo "<span class='text-$attendance->code'>$attendance->text</span>";
								echo '</td>';
							
								//Ta bort
								echo '<td class="btn_manage">';
									echo '<a href="'. base_url('signup/admin/signups/delete_confirm/'. $signup->event_id .'-'. $this->member_id) .'" class="btn btn-danger">';
										echo '<i class="far fa-trash-alt"></i>';
									echo '</a>';
								echo '</td>';

							echo '</tr>';
						}
					else
						echo '<tr><td colspan="5" class="text-center">&ndash; Inga anmälningar &ndash;</td></tr>';
				echo '</tbody>';
			echo '</table>';

			//pagination
			echo pagination($this->page, $this->total_signups, $this->results_per_page, base_url("signup/admin/signups/member/$this->member_id/"), 'wrapper_signup_table');

		echo '</div>'; //end #wrapper_signups_table
	}

	private function view_form()
	{
		//moduler
		$this->CI->load->library('attendance');

		//--variabler--
		$is_new = $this->signup == null;
		
		//attendance
		$attendance_options_string = null;
		$attendance_types = $this->CI->attendance->get_choosable();
		$attendance_types[] = (object)array('id'=>6, 'text'=>'Oanmäld frånvaro', 'code'=>'awol'); //fulhax
		foreach($attendance_types as $att)
			$attendance_options_string .= "<option". (!$is_new && $this->signup->attendance_id == $att->id ? ' selected' : null) ." value='$att->id'>$att->text</option>";
		//groups
		$group_options_string = null;
		foreach($this->groups as $group)
			$group_options_string .= "<option". (!$is_new && $this->signup->group_id == $group->id ? ' selected' : null) ." value='$group->id'>$group->name</option>";
		//roles
		$role_options_string = null;
		foreach($this->roles as $role)
			$role_options_string .= "<option". (!$is_new && $this->signup->role_id == $role->id ? ' selected' : null) ." value='$role->id'>$role->name</option>";
		//events
		$events_options_string = null;
		if($is_new) foreach($this->events as $event)
			$events_options_string .= "<option value='$event->id'>$event->title</option>";

		//breadcrumbs
		echo '<nav aria-label="breadcrumb"><ol class="breadcrumb">';
			echo '<li class="breadcrumb-item"><a href="'. base_url('signup/admin/signups') .'">Hem</a></li>';
			echo '<li class="breadcrumb-item"><a href="'. base_url('signup/admin/signups/member/'. $this->member->id) .'">'. $this->member->name .'</a></li>';
			echo '<li class="breadcrumb-item active" aria-current="page">'. ($is_new ? 'Ny': 'Redigera') .' anmälan</li>';
		echo '</ol></nav>';


		//--print--
		echo '<div id="wrapper_signups_form">';

			//Rubrik
			echo $is_new
				? '<h5>Skapa ny anmälan</h5>'
				: '<h5>Redigera anmälan</h5>';
			
			echo '<form class="ssg_form" action="'. base_url('signup/admin/signups/'. ($is_new ? 'insert' : 'update')) .'" method="post">';

				//id
				echo !$is_new
					? '<input type="hidden" name="event_id" value="'. $this->signup->event_id .'"><input type="hidden" name="member_id" value="'. $this->member_id .'">'
					: '<input type="hidden" name="member_id" value="'. $this->member_id .'">';
				
				//Event
				if($is_new)
				{
					echo '<div class="form-group">';
						echo '<label for="input_event">Event <small>Visar endast events som medlemmen inte redan anmält sig till.</small></label>';
						echo '<select class="form-control" id="input_event" name="event_id">'. $events_options_string .'</select>';
					echo '</div>';
				}
				
				//Närvaro
				echo '<div class="form-group">';
					echo '<label for="input_attendance">Närvaro</label>';
					echo '<select class="form-control" id="input_attendance" name="attendance_id">'. $attendance_options_string .'</select>';
				echo '</div>';
				
				//Grupp
				echo '<div class="form-group">';
					echo '<label for="input_group">Grupp</label>';
					echo '<select class="form-control" id="input_group" name="group_id">'. $group_options_string .'</select>';
				echo '</div>';
				
				//Befattning
				echo '<div class="form-group">';
					echo '<label for="input_role">Befattning</label>';
					echo '<select class="form-control" id="input_role" name="role_id">'. $role_options_string .'</select>';
				echo '</div>';
				
				//Anmälning skapad
				echo  '<div class="form-group">';
					echo '<label for="input_signed_datetime">Anmälning skapad<span class="text-danger">*</span> <small>(åååå-mm-dd hh:mm:ss)</small></label>';
					echo '<input type="text" id="input_signed_datetime" name="signed_datetime" class="form-control" value="'. ($is_new ? date('Y-m-d G:i:s') : $this->signup->signed_datetime) .'" required>';
				echo '</div>';
				
				//Senast ändrad
				echo  '<div class="form-group">';
					echo '<label for="input_last_changed_datetime">Senast ändrad<span class="text-danger">*</span> <small>(åååå-mm-dd hh:mm:ss)</small></label>';
					echo '<input type="text" id="input_last_changed_datetime" name="last_changed_datetime" class="form-control" value="'. ($is_new ? date('Y-m-d G:i:s') : $this->signup->last_changed_datetime) .'" required>';
				echo '</div>';
				
				//Meddelande
				echo  '<div class="form-group">';
					echo '<label for="input_message">Meddelande</label>';
					echo '<input type="text" id="input_message" name="message" class="form-control" value="'. ($is_new ? null : $this->signup->message) .'">';
				echo '</div>';

				//Submit
				echo '<button type="submit" class="btn btn-success">'. ($is_new ? 'Skapa anmälan <i class="fas fa-plus-circle"></i>' : 'Spara ändringar <i class="fas fa-save"></i>') .'</button>';

			echo '</form>';
		echo '</div>'; //end #wrapper_signups_form
	}

	private function view_delete_confirm()
	{
		echo '<div class="row text-center">';
			echo '<h5 class="col">Är du säker på att du vill ta bort anmälan av: <strong>'. $this->member->name .'</strong> till eventet: <strong>'. $this->event->title .' ('. $this->event->start_date .')</strong>?</h5>';
		echo '</div>';

		echo '<div class="row text-center mt-2">';
			echo '<div class="col">';
				echo '<a href="'. base_url('signup/admin/signups/delete/'. $this->event->id .'-'. $this->member_id) .'" class="btn btn-success mr-2">Ja</a>';
				echo '<a href="'. base_url('signup/admin/signups/') .'" class="btn btn-danger">Nej</a>';
			echo '</div>';
		echo '</div>';
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

	private function get_signups($member_id, $page, $results_per_page)
	{
		$sql =
			'SELECT
				s.event_id,
				attendance-0 AS attendance_id,
				g.name AS group_name,
				g.code AS group_code,
				r.name AS role_name,
				e.title AS event_title,
				DATE_FORMAT(e.start_datetime, "%Y-%m-%d") AS start_date
			FROM ssg_signups s
			INNER JOIN ssg_events e
				ON s.event_id = e.id
			INNER JOIN ssg_groups g
				ON s.group_id = g.id
			INNER JOIN ssg_roles r
				ON s.role_id = r.id
			WHERE member_id = ?
			ORDER BY e.start_datetime DESC
			LIMIT ?, ?';
		return $this->CI->db->query($sql, array($this->member_id, $page * $results_per_page, $results_per_page))->result();
	}

	private function get_signup($event_id, $member_id)
	{
		$sql =
			'SELECT
				event_id, member_id, group_id, role_id, message, signed_datetime, last_changed_datetime,
				attendance-0 AS attendance_id
			FROM ssg_signups s
			INNER JOIN ssg_events e
				ON s.event_id = e.id
			WHERE
				event_id = ?
				AND member_id = ?';
		return $this->CI->db->query($sql, array($event_id, $member_id))->row();
	}

	/**
	 * Hämta events som $member_id inte är anmäld till.
	 *
	 * @param int $member_id
	 * @return array
	 */
	private function get_events($member_id)
	{
		$sql =
			'SELECT
				id,
				CONCAT(
					title,
					" (",
					DATE_FORMAT(start_datetime, "%Y-%m-%d"),
					")"
				) AS title
			FROM ssg_events
			WHERE id NOT IN (SELECT event_id FROM ssg_signups WHERE member_id = ?)
			ORDER BY start_datetime DESC';
		return $this->CI->db->query($sql, $member_id)->result();
	}

	private function get_groups()
	{
		return $this->CI->db->query('SELECT id, name FROM ssg_groups ORDER BY sorting ASC')->result();
	}

	private function get_roles()
	{
		return $this->CI->db->query('SELECT id, name FROM ssg_roles ORDER BY sorting ASC')->result();
	}

	/**
	 * Lägg till auto-event.
	 *
	 * @param object $vars POST-variabler.
	 * @return void
	 */
	private function insert($vars)
	{
		//input-sanering
		assert(isset($vars), 'Post-variabler saknas.');
		assert(!empty($vars->event_id) && is_numeric($vars->event_id), "event_id: $vars->event_id");
		assert(!empty($vars->member_id) && is_numeric($vars->member_id), "member_id: $vars->member_id");
		assert(!empty($vars->role_id) && is_numeric($vars->role_id), "role_id: $vars->role_id");
		assert(!empty($vars->attendance_id) && is_numeric($vars->attendance_id), "attendance_id: $vars->attendance_id");
		assert(!empty($vars->signed_datetime) && preg_match('/(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})/', $vars->signed_datetime), "signed_datetime: $vars->signed_datetime");
		assert(!empty($vars->last_changed_datetime) && preg_match('/(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})/', $vars->last_changed_datetime), "last_changed_datetime: $vars->last_changed_datetime");
		
		$data = array(
			'event_id' => $vars->event_id,
			'member_id' => $vars->member_id,
			'group_id' => $vars->group_id,
			'role_id' => $vars->role_id,
			'attendance' => $vars->attendance_id,
			'signed_datetime' => $vars->signed_datetime,
			'last_changed_datetime' => $vars->last_changed_datetime,
			'message' => strip_tags(trim($vars->message)),
		);
		$this->CI->db->insert('ssg_signups', $data);
	}

	/**
	 * Uppdatera auto-event.
	 *
	 * @param object $vars POST-variabler.
	 * @return void
	 */
	private function update($vars)
	{
		//input-sanering
		assert(isset($vars), 'Post-variabler saknas.');
		assert(!empty($vars->event_id) && is_numeric($vars->event_id), "event_id: $vars->event_id");
		assert(!empty($vars->member_id) && is_numeric($vars->member_id), "member_id: $vars->member_id");
		assert(!empty($vars->role_id) && is_numeric($vars->role_id), "role_id: $vars->role_id");
		assert(!empty($vars->attendance_id) && is_numeric($vars->attendance_id), "attendance_id: $vars->attendance_id");
		assert(!empty($vars->signed_datetime) && preg_match('/(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})/', $vars->signed_datetime), "signed_datetime: $vars->signed_datetime");
		assert(!empty($vars->last_changed_datetime) && preg_match('/(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})/', $vars->last_changed_datetime), "last_changed_datetime: $vars->last_changed_datetime");
		
		$data = array(
			'group_id' => $vars->group_id,
			'role_id' => $vars->role_id,
			'attendance' => $vars->attendance_id,
			'signed_datetime' => $vars->signed_datetime,
			'last_changed_datetime' => $vars->last_changed_datetime,
			'message' => strip_tags(trim($vars->message)),
		);
		$this->CI->db->where(array('event_id'=> $vars->event_id, 'member_id'=>$vars->member_id))->update('ssg_signups', $data);
	}

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