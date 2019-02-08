<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Hanterar events
 */
class Admin_events implements Adminpanel
{
	protected $CI;
	private
		$view,
		$event_id,
		$events = array(),
		$event,
		$results_per_page = 20,
		$page,
		$total_events,
		$total_pages,
		$delete_event_id
	;

	public function __construct()
	{
		// Assign the CodeIgniter super-object
		$this->CI =& get_instance();
	}

	/**
	 * Init
	 *
	 * @param int $event_id Event-id
	 * @param int $page Sidnummer (sida 1 = 0, sida 2 = 1, osv.)
	 * @return void
	 */
	public function main($event_id = 0, $page = 0, $var3)
	{
		//moduler
		$this->CI->load->library('attendance');
		$this->CI->load->library('eventsignup');

		//post variabler finns, gå till submits
		if($this->CI->input->post('task') != null)
		{
			$this->submit($this->CI->input->post('task'));
			return;
		}
		else if($this->CI->input->get('delete_confirm') != null)
		{
			$this->view = 'delete_confirm';
			$this->delete_event = $this->CI->Events->get_event($this->CI->input->get('delete_confirm'));
			return;
		}
		else if($this->CI->input->get('delete') != null)
		{
			$this->submit('delete');
			return;
		}

		//variabler
		$this->event_id = empty($event_id) ? 0 : $event_id-0;
		$this->page = empty($page) ? 0 : $page-0;
		$this->total_events = $this->CI->db->query('SELECT COUNT(*) AS count FROM ssg_events')->row()->count;

		//moduler
		$this->CI->load->library('eventsignup');

		//ladda data
		$this->events = $this->CI->eventsignup->get_events($this->page, $this->results_per_page);
		$this->event_types = $this->CI->db->query('SELECT id, title FROM ssg_event_types ORDER BY id ASC')->result();

		//ladda event
		if(!empty($this->event_id))
			$this->event = $this->CI->eventsignup->get_event($this->event_id);
	}

	public function view()
	{
		//js
		echo 
			'<script src="'. base_url('js/signup/form_validation.js') .'"></script>
			<script src="'. base_url('js/signup/adminpanels/events.js') .'"></script>';

		if($this->view == 'delete_confirm') //delete confirmation-vy
		{
			echo 
				'<div class="row text-center">
					<h5 class="col">Är du säker på att du vill ta bort <i>'. $this->delete_event->title .' ('. $this->delete_event->start_date .')</i>?</h5>
				</div>';

			echo 
				'<div class="row text-center mt-2">
					<div class="col">
						<a href="'. base_url('signup/admin/events/?delete='. $this->delete_event->id) .'" class="btn btn-success mr-2">Ja</a>
						<a href="'. base_url('signup/admin/events/') .'" class="btn btn-danger">Nej</a>
					</div>	
				</div>';
		}
		else //vanlig vy
		{
			$this->print_form();
			echo '<hr class="my-4" />';
			$this->print_table();
		}
	}

	/**
	 * Skriv ut ny/redigera event-formulär
	 *
	 * @return void
	 */
	private function print_form()
	{
		//variabler
		$is_new = $this->event == null;
		
		//medlemmar
		$members = $this->CI->member->get_members_simple();
		$member_options = null;
		foreach($members as $id => $name)
		{
			if(
				(!$is_new && $id == $this->event->author_id) //edit = select:a author_id
				|| ($is_new && $id == $this->CI->member->id) //new = select:a inloggad medlem
			)
				$selected = 'selected';
			else
				$selected = null;

			$member_options .= "<option value='$id' $selected>$name</option>";
		}

		//event-typer
		$event_types = $this->event_types;
		$event_type_options = null;
		foreach($event_types as $type)
			$event_type_options .= "<option value='$type->id' ". (!$is_new && $type->id == $this->event->type_id ? 'selected' : null) .">$type->title</option>";
		
		
		//--print--
		echo '<div id="wrapper_events_form" '. ($is_new ? 'style="display: none;"' : null) .'>';

		//heading
		echo $is_new
			? '<h5>Skapa nytt event</h5>'
			: '<h5>Redigera event</h5>';

		echo '<form class="ssg_form" action="'. base_url('signup/admin/events') .'" method="post">';
		
		//task
		echo '<input type="hidden" name="task" value="event">';

		//event_id hidden input
		if(!$is_new)
			echo '<input type="hidden" name="event_id" value="'. $this->event->id .'">';

		//titel
		echo 
			'<div class="form-group">
				<label for="input_title">Titel<span class="text-danger">*</span></label>
				<input type="text" id="input_title" name="title" class="form-control" value="'. ($is_new ? null : $this->event->title) .'" required>
			</div>';

		//skapare
		echo 
			'<div class="form-group">
				<label for="input_author">Skapare</label>
				<select name="author_id" id="input_author" class="form-control">
					<option value="0">--Ingen--</option>
					'. $member_options .'
				</select>
			</div>';
		
		//start-datum
		echo 
			'<div class="form-group">
				<label for="input_start_date">Start-datum<span class="text-danger">*</span></label>
				<input type="date" id="input_start_date" name="start_date" class="form-control" value="'. ($is_new ? null : $this->event->start_date) .'" required>
			</div>';

		//start-tid
		echo 
			'<div class="form-group">
				<label for="input_start_time">Start-tid<span class="text-danger">*</span></label>
				<input type="time" id="input_start_time" name="start_time" class="form-control" value="'. ($is_new ? null : $this->event->start_time) .'" required>
			</div>';
		
		//längd
		echo 
			'<div class="form-group">
				<label for="input_length_time">Längd<span class="text-danger">*</span></label>
				<input type="time" id="input_length_time" name="length_time" class="form-control" value="'. ($is_new ? null : $this->event->length_time) .'" required>
			</div>';

		//typ
		echo 
			'<div class="form-group">
				<label for="input_type">Event-typ<span class="text-danger">*</span></label>
				<select name="type_id" id="input_type" class="form-control">
					'. $event_type_options .'
				</select>
			</div>';

		//forum-länk
		echo 
			'<div class="form-group">
				<label for="input_forum_link">Forum-länk</label>
				<input type="text" id="input_forum_link" name="forum_link" class="form-control" value="'. ($is_new ? null : $this->event->forum_link) .'">
			</div>';

		//förhandsvisningsbild
		echo 
			'<div class="form-group">
				<label for="input_preview_image">Förhandsvisningsbild</label>
				<input type="text" id="input_preview_image" name="preview_image" class="form-control" value="'. ($is_new ? null : $this->event->preview_image) .'" placeholder="https://coolabilder.se/bild.jpg">
			</div>';

		//submit
		echo '<button type="submit" class="btn btn-success">'. ($is_new ? 'Skapa event <i class="fas fa-plus-circle"></i>' : 'Spara ändringar <i class="far fa-edit"></i>') .'</button>';

		echo '</form></div>';

		//visa formulär-knapp
		echo '<button id="btn_show_form" class="btn btn-success" '. (!$is_new ? 'style="display: none;"' : '') .'>Skapa nytt event <i class="fas fa-plus-circle"></i></button>';
	}

	/**
	 * Skriv ut event-tabell med pagination
	 *
	 * @return void
	 */
	private function print_table()
	{
		//event-tabell
		echo '<div id="wrapper_events_table" class="table-responsive table-sm">';
		echo
			'<table class="table table-hover">
				<thead class="table-borderless">
					<tr>
						<th scope="col">Titel</th>
						<th scope="col">Typ</th>
						<th scope="col">Datum</th>
						<th scope="col">Anmälda</th>
						<th scope="col">&nbsp;</th>
					</tr>
				</thead>
				<tbody>';

		foreach($this->events as $event)
		{
			echo
				"<tr data-event_id='$event->id' class='". ($event->is_old ? 'grayed' : null) ."'>
					<th scope='row'>$event->title</th>
					<td>$event->type_name</td>
					<td><abbr title='$event->start_time - $event->end_time' data-toggle='tooltip'>$event->start_date</abbr></td>
					<td>$event->signed_sum</td>
					<td class='btn_manage'>
						<a href='". base_url("signup/event/$event->id") ."' class='btn btn-success' title='Se detaljer' data-toggle='tooltip'><i class='fas fa-search'></i></a>
						<a href='". base_url("signup/admin/events/$event->id") ."' class='btn btn-primary' title='Redigera' data-toggle='tooltip'><i class='fas fa-edit'></i></a>
						<a href='". base_url("signup/admin/events/?delete_confirm=$event->id") ."' class='btn_delete btn btn-danger' title='Ta bort' data-toggle='tooltip'><i class='far fa-trash-alt'></i></a>
					</td>
				</tr>";
		}
		echo '</tbody></table></div>';

		//pagination
		echo pagination($this->page, $this->total_events, $this->results_per_page, base_url("signup/admin/events/$this->event_id/"), 'wrapper_events_table');
	}

	/**
	 * Hanterar form-submits
	 *
	 * @param string $task
	 * @return void
	 */
	private function submit($task)
	{
		//permissions kollas redan i Signup->Admin()

		//modeller
		$this->CI->load->model('signup/Events');

		if($task == 'event' && $this->CI->input->post('event_id'))
			$this->insert_update(false); //update
		else if($task == 'event')
			$this->insert_update(true); //insert
		else if($task == 'delete')
			$this->delete_event($this->CI->input->get('delete'));
	}

	/**
	 * Lägg till eller uppdatera event.
	 * Validerar input och kör sedan insert_event() eller update_event().
	 *
	 * @param bool $is_insert Ny eller uppdatera?
	 * @return void
	 */
	private function insert_update($is_insert)
	{
		$data = new stdClass;

		//input-sanering
		$data->title = strip_tags(trim($this->CI->input->post('title')));
		$data->author_id = $this->CI->input->post('author_id') == 0 ? null : $this->CI->input->post('author_id');
		$data->forum_link = strlen(trim($this->CI->input->post('forum_link'))) > 0 ? strip_tags(trim($this->CI->input->post('forum_link'))) : null;
		$data->preview_image = strlen(trim($this->CI->input->post('preview_image'))) > 0 ? strip_tags(trim($this->CI->input->post('preview_image'))) : null;
		
		//tidsvariabler
		$data->length_time = $this->CI->input->post('length_time');
		$data->length_hours = substr($data->length_time, 0, strpos($data->length_time, ':'));
		$data->length_minutes = substr($data->length_time, -strpos($data->length_time, ':'), 2);
		$data->start_datetime = $this->CI->input->post('start_date') .' '. $this->CI->input->post('start_time');
		$data->end_datetime = date('Y-m-d G:i:s', strtotime("$data->start_datetime +$data->length_hours hours $data->length_minutes minutes"));

		//inte för långa events
		if(strtotime($data->length_time) >= strtotime('24:00'))
		{
			$this->CI->alerts->add_alert('danger', 'Eventet sparades inte då det är för långt: <strong>'. $length_hours .'</strong>');
			redirect('signup/admin/events');
			return;
		}

		//kör insert eller update-metod
		if($is_insert)
			$this->insert_event($data);
		else
			$this->update_event($data);
	}

	/**
	 * Lägg till event
	 *
	 * @param object $data
	 * @return void
	 */
	private function insert_event($data)
	{
		//kolla om event kommer overlap:a andra events
		if($overlaps = $this->CI->eventsignup->is_overlapping($start_datetime, $end_datetime))
		{
			$this->CI->alerts->add_alert('danger', 'Eventet skapades inte då det krockar med: <strong>'. current($overlaps) .'</strong>');
			redirect('signup/admin/events');
			return;
		}

		$sql =
			'INSERT INTO ssg_events(title, author, start_datetime, length_time, type_id, forum_link, preview_image)
			VALUES (?, ?, ?, ?, ?, ?, ?)';
		$query = $this->CI->db->query($sql, array(
			$data->title,
			$data->author_id,
			$data->start_datetime,
			$data->length_time,
			$this->CI->input->post('type_id'),
			$data->forum_link,
			$data->preview_image,
		));

		$this->CI->alerts->add_alert('success', 'Eventet skapades utan problem.');
		redirect('signup/admin/events');
	}
	
	/**
	 * Uppdatera event
	 *
	 * @param object $data
	 * @return void
	 */
	private function update_event($data)
	{
		//kolla om event kommer overlap:a andra events (räkna inte med detta event)
		$overlaps = $this->CI->eventsignup->is_overlapping($start_datetime, $end_datetime);
		
		//lägg alla overlaps utom detta event i $overlaps_sans_self
		$overlaps_sans_self = array();
		foreach($overlaps as $overlap_id => $overlap)
			if($overlap_id != $this->CI->input->post('event_id'))
				$overlaps_sans_self[$overlap_id] = $overlap;

		if($overlaps_sans_self)
		{
			$this->CI->alerts->add_alert('danger', 'Eventet sparades inte då det krockar med: <strong>'. current($overlaps_sans_self) .'</strong>');
			redirect('signup/admin/events');
			return;
		}

		$sql =
			'UPDATE ssg_events
			SET
				title = ?,
				author = ?,
				start_datetime = ?,
				length_time = ?,
				type_id = ?,
				forum_link = ?,
				preview_image = ?
			WHERE id = ?';
		$query = $this->CI->db->query($sql, array(
			$data->title,
			$data->author_id,
			$data->start_datetime,
			$data->length_time,
			$this->CI->input->post('type_id'),
			$data->forum_link,
			$data->preview_image,
			$this->CI->input->post('event_id')
		));

		$this->CI->alerts->add_alert('success', 'Ändringarna sparades utan problem.');
		redirect('signup/admin/events');
	}

	/**
	 * Ta bort event
	 *
	 * @param int $event_id
	 * @return void
	 */
	private function delete_event($event_id)
	{
		$sql =
			'DELETE FROM ssg_events
			WHERE id = ?';
		$query = $this->CI->db->query($sql, $event_id);

		$this->CI->alerts->add_alert('success', 'Eventet togs bort utan problem.');
		redirect('signup/admin/events');
	}

	public function get_code()
	{
		return 'events';
	}

	public function get_title()
	{
		return 'Events';
	}

	public function get_permissions_needed()
	{
		return array('s0', 's2', 's3', 's4');
	}
}
?>