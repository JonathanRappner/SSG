<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Hanterar uppehållsperioder
 */
class Admin_autoevents implements Adminpanel
{
	protected $CI;
	private
		$days_se,
		$view,
		$auto_events,
		$auto_event,
		$auto_event_id,
		$event_types;

	public function __construct()
	{
		// Assign the CodeIgniter super-object
		$this->CI =& get_instance();

		//svenska veckodagar
		$this->days_se = array(
			'monday' => 'måndag',
			'tuesday' => 'tisdag',
			'wednesday' => 'onsdag',
			'thursday' => 'torsdag',
			'friday' => 'fredag',
			'saturday' => 'lördag',
			'sunday' => 'söndag',
		);
	}

	public function main($var1, $var2, $var3)
	{
		//variabler
		$this->view = $var1;

		if($this->view == null) //main
		{
			$this->auto_events = $this->get_auto_events();
			$this->auto_event = $this->get_auto_event($this->auto_event_id);
			$this->event_types = $this->CI->db->query('SELECT id, title FROM ssg_event_types')->result();
		}
		else if($this->view == 'auto_event') //formulär för att redigera
		{
			$this->auto_event_id = $var2;
			$this->auto_event = $this->get_auto_event($this->auto_event_id);
			$this->event_types = $this->CI->db->query('SELECT id, title FROM ssg_event_types')->result();
		}
		else if($this->view == 'add_auto_event') //lägg till
		{
			$vars = (object)$this->CI->input->post();

			$this->add_auto_event($vars);

			//success
			$this->CI->alerts->add_alert('success', 'Auto-eventet skapades utan problem.');
			redirect('signup/admin/autoevents');
		}
		else if($this->view == 'update_auto_event') //uppdatera
		{
			$vars = (object)$this->CI->input->post();
			
			$this->update_auto_event($vars);

			//success
			$this->CI->alerts->add_alert('success', 'Ändringarna sparades utan problem.');
			redirect('signup/admin/autoevents');
		}
		else if($this->view == 'remove_auto_event') //ta bort
		{
			$auto_event_id = $var2;
			
			$this->CI->db->delete('ssg_auto_events', array('id' => $auto_event_id));

			//success
			$this->CI->alerts->add_alert('success', 'Auto-eventet togs bort utan problem.');
			redirect('signup/admin/autoevents');
		}
	}

	public function view()
	{
		//js
		echo '<script src="'. base_url('js/signup/adminpanels/auto_events.js') .'"></script>';
		
		if($this->view == null) //main
			$this->view_main();
		else if($this->view == 'auto_event')
			$this->view_auto_event();
	}

	private function view_main()
	{
		echo '<p>Här redigerar du mallarna för events som skapas automatiskt veckovis. Automatiska events skapas en månad frammåt, en gång om dagen kl. 1:00. Om ett annat event redan existerar under samma tidsspann så skapas det inte.</p>';
		
		$this->print_form();

		echo '<hr>';

		//Tabell
		echo '<div id="wrapper_auto_events_table" class="table-responsive table-sm">';
			echo '<table class="table table-hover clickable">';
				echo '<thead class="table-borderless">';
					echo '<tr>';
						echo '<th scope="col">Titel</th>';
						echo '<th scope="col">Typ</th>';
						echo '<th scope="col">Dag</th>';
						echo '<th scope="col">Tid</th>';
						echo '<th scope="col">Ta bort</th>';
					echo '</tr>';
				echo '</thead><tbody>';
					if(count($this->auto_events) > 0)
						foreach($this->auto_events as $auto_event)
						{
							echo '<tr data-url="'. base_url('signup/admin/autoevents/auto_event/'. $auto_event->id) .'">';
							
								//Titel
								echo '<td class="font-weight-bold" scope="row">';
									echo $auto_event->auto_title;
								echo '</td>';
							
								//Typ
								echo '<td>';
									echo $auto_event->type_title;
								echo '</td>';
							
								//Dag
								echo '<td>';
									echo $this->days_se[$auto_event->start_day];
								echo '</td>';
							
								//Tid
								echo '<td>';
									echo "$auto_event->start_time - $auto_event->end_time";
								echo '</td>';
							
								//Ta bort
								echo '<td class="btn_manage">';
									echo '<a href="'. base_url('signup/admin/autoevents/remove_auto_event/'. $auto_event->id) .'" class="btn btn-danger">';
										echo '<i class="far fa-trash-alt"></i>';
									echo '</a>';
								echo '</td>';

							echo '</tr>';
						}
					else
						echo '<tr><td colspan="5" class="text-center">&ndash; Inga auto-event &ndash;</td></tr>';
				echo '</tbody>';
			echo '</table>';
		echo '</div>'; //end #wrapper_auto_events_table
	}

	private function view_auto_event()
	{
		$this->print_form();
	}

	/**
	 * Skriv ut formulär för ny och redigering.
	 *
	 * @return void
	 */
	private function print_form()
	{
		//variabler
		$is_new = $this->auto_event_id == null;

		$event_types_options_string = null;
		foreach($this->event_types as $event_type)
			$event_types_options_string .= "<option ". (!$is_new && $this->auto_event->type_id == $event_type->id ? 'selected' : null) ." value='$event_type->id'>$event_type->title</option>";

		$day_options_string = null;
		foreach($this->days_se as $day_en => $day_se)
			$day_options_string .= "<option ". (!$is_new && $this->auto_event->start_day == $day_en ? 'selected' : null) ." value='$day_en'>$day_se</option>";

		//--print--
		echo '<div id="wrapper_auto_events_form" '. ($is_new ? 'style="display: none;"' : null) .'>';

			//Rubrik
			echo $is_new
				? '<h5>Skapa ny auto-event</h5>'
				: '<h5>Redigera auto-event</h5>';
			
			echo '<form class="ssg_form" action="'. base_url('signup/admin/autoevents/'. ($is_new ? 'add_auto_event' : 'update_auto_event')) .'" method="post">';

				//auto-event-id
				echo !$is_new
					? '<input type="hidden" name="auto_event_id" value="'. $this->auto_event->id .'">'
					: null;
				
				//Titel
				echo  '<div class="form-group">';
					echo '<label for="input_title">Titel<span class="text-danger">*</span></label>';
					echo '<input type="text" id="input_title" name="title" class="form-control" value="'. ($is_new ? null : $this->auto_event->title) .'" required>';
				echo '</div>';

				//Event-typ
				echo '<div class="form-group">';
					echo '<label for="input_event_type">Event-typ</label>';
					echo '<select class="form-control" id="input_event_type" name="type_id">'. $event_types_options_string .'</select>';
				echo '</div>';

				//Veckodag
				echo '<div class="form-group">';
					echo '<label for="input_day">Veckodag</label>';
					echo '<select class="form-control" id="input_day" name="day">'. $day_options_string .'</select>';
				echo '</div>';

				//Start-tid
				echo  '<div class="form-group">';
					echo '<label for="input_start_time">Start-tid<span class="text-danger">*</span></label>';
					echo '<input type="time" id="input_start_time" name="start_time" class="form-control" value="'. ($is_new ? '00:00' : $this->auto_event->start_time) .'" required>';
				echo '</div>';

				//Längd
				echo  '<div class="form-group">';
					echo '<label for="input_length_time">Längd<span class="text-danger">*</span></label>';
					echo '<input type="time" id="input_length_time" name="length_time" class="form-control" value="'. ($is_new ? '01:00' : $this->auto_event->length_time) .'" required>';
				echo '</div>';

				//Tillbaka
				if(!$is_new) echo '<a href="'. base_url('signup/admin/autoevents') .'" class="btn btn-primary">&laquo; Tillbaka</a> ';

				//Submit
				echo '<button type="submit" class="btn btn-success">'. ($is_new ? 'Skapa auto-event <i class="fas fa-plus-circle"></i>' : 'Spara ändringar <i class="fas fa-save"></i>') .'</button>';

			echo '</form>';
		echo '</div>'; //end #wrapper_auto_events_form

		//visa formulär-knapp
		echo '<button id="btn_show_form" class="btn btn-success" '. (!$is_new ? 'style="display: none;"' : null) .'>Skapa ny auto-event-mall <i class="fas fa-plus-circle"></i></button>';
	}

	/**
	 * Auto events
	 *
	 * @return array
	 */
	private function get_auto_events()
	{
		$sql =
			'SELECT 
				ae.id, start_day, type_id,
				ae.title AS auto_title,
				et.title AS type_title,
				TIME_FORMAT(start_time, "%H:%i") AS start_time,
				TIME_FORMAT(ADDTIME(start_time, length_time), "%H:%i") AS end_time
			FROM ssg_auto_events ae
			INNER JOIN ssg_event_types et
				ON ae.type_id = et.id';
		return $this->CI->db->query($sql)->result();
	}

	/**
	 * Hämta enskildt auto-event för formuläret.
	 *
	 * @param int $auto_event_id
	 * @return object
	 */
	private function get_auto_event($auto_event_id)
	{
		$sql =
			'SELECT 
				id, start_day, type_id, title,
				TIME_FORMAT(start_time, "%H:%i") AS start_time,
				TIME_FORMAT(length_time, "%H:%i") AS length_time
			FROM ssg_auto_events
			WHERE id = ?';
		return $this->CI->db->query($sql, $auto_event_id)->row();
	}

	/**
	 * Lägg till auto-event.
	 *
	 * @param object $vars POST-variabler.
	 * @return void
	 */
	private function add_auto_event($vars)
	{
		//input-sanering
		assert(isset($vars), 'Post-variabler saknas.');
		assert(!empty($vars->title), "title: $vars->title");
		assert(isset($vars->type_id) && is_numeric($vars->type_id), "type_id: $vars->type_id");
		assert(!empty($vars->day) && key_exists($vars->day, $this->days_se), "day: $vars->day");
		assert(!empty($vars->start_time) && preg_match('/(\d{2}:\d{2}){1}/', $vars->start_time), "start_time: $vars->start_time");
		assert(!empty($vars->length_time) && preg_match('/(\d{2}:\d{2}){1}/', $vars->length_time), "start_time: $vars->length_time");
		
		$data = array
		(
			'title' => $vars->title,
			'start_day' => $vars->day,
			'start_time' => $vars->start_time,
			'length_time' => $vars->length_time,
			'type_id' => $vars->type_id,
		);
		$this->CI->db->insert('ssg_auto_events', $data);
	}

	/**
	 * Uppdatera auto-event.
	 *
	 * @param object $vars POST-variabler.
	 * @return void
	 */
	private function update_auto_event($vars)
	{
		//input-sanering
		assert(isset($vars), 'Post-variabler saknas.');
		assert(isset($vars->auto_event_id) && is_numeric($vars->auto_event_id), "auto_event_id: $vars->auto_event_id");
		assert(!empty($vars->title), "title: $vars->title");
		assert(isset($vars->type_id) && is_numeric($vars->type_id), "type_id: $vars->type_id");
		assert(!empty($vars->day) && key_exists($vars->day, $this->days_se), "day: $vars->day");
		assert(!empty($vars->start_time) && preg_match('/(\d{2}:\d{2}){1}/', $vars->start_time), "start_time: $vars->start_time");
		assert(!empty($vars->length_time) && preg_match('/(\d{2}:\d{2}){1}/', $vars->length_time), "start_time: $vars->length_time");

		$data = array
		(
			'title' => $vars->title,
			'start_day' => $vars->day,
			'start_time' => $vars->start_time,
			'length_time' => $vars->length_time,
			'type_id' => $vars->type_id,
		);
		$this->CI->db->where('id', $vars->auto_event_id)->update('ssg_auto_events', $data);
	}

	public function get_code()
	{
		return 'autoevents';
	}

	public function get_title()
	{
		return 'Auto-events';
	}

	public function get_permissions_needed()
	{
		return array('s0');
	}
}
?>