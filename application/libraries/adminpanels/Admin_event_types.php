<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Hanterar event-typer
 */
class Admin_event_types implements Adminpanel
{
	protected $CI;
	private
		$event_types,
		$event_type,
		$event_type_id,
		$event_type_count;

	public function __construct()
	{
		// Assign the CodeIgniter super-object
		$this->CI =& get_instance();
	}

	public function main($var1, $var2, $var3)
	{
		//variabler
		$this->view = $var1;

		if($this->view == null) //main
		{
			$this->event_types = $this->get_event_types();
		}
		else if($this->view == 'edit') //formulär: redigera
		{
			$this->event_type_id = $var2;
			$this->event_type = $this->CI->db->query('SELECT * FROM ssg_event_types WHERE id = ?', $this->event_type_id)->row();
		}
		else if($this->view == 'delete_confirm') //ta bort bekräftan/nekan om events_count > 0
		{
			$this->event_type_id = $var2;
			$this->event_type_count = $this->CI->db->query('SELECT COUNT(*) AS count FROM ssg_events WHERE type_id = ?', $this->event_type_id)->row()->count;
			$this->event_type = $this->CI->db->query('SELECT * FROM ssg_event_types WHERE id = ?', $this->event_type_id)->row();
		}
		else if($this->view == 'insert') //lägg till
		{
			$vars = (object)$this->CI->input->post();

			$this->insert($vars);
			
			//success
			$this->CI->alerts->add_alert('success', 'Event-typen lades till utan problem.');
			redirect('signup/admin/eventtypes');
		}
		else if($this->view == 'update') //spara ändringar
		{
			$vars = (object)$this->CI->input->post();
			
			$this->update($vars);
			
			//success
			$this->CI->alerts->add_alert('success', 'Event-typen sparades utan problem.');
			redirect('signup/admin/eventtypes');
		}
		else if($this->view == 'delete') //ta bort
		{
			$this->event_type_id = $var2;
			assert(isset($this->event_type_id) && is_numeric($this->event_type_id));
			
			$this->CI->db->delete('ssg_event_types', array('id' => $this->event_type_id));

			//success
			$this->CI->alerts->add_alert('success', 'Event-typen togs bort utan problem.');
			redirect('signup/admin/eventtypes');
		}
	}

	public function view()
	{
		if($this->view == null) //main
			$this->view_main();
		else if($this->view == 'new') //formulär: ny
		{
			$this->view_form();
		}
		else if($this->view == 'edit') //formulär: redigera
		{
			$this->view_form();
		}
		else if($this->view == 'delete_confirm')
		{
			$this->view_delete_confirm();
		}
	}

	private function view_main()
	{
		echo '<p>Här administerar du event-typerna samt deras attribut.</p>';

		echo '<a href="'. base_url('signup/admin/eventtypes/new/') .'" class="btn btn-success">Skapa ny event-typ <i class="fas fa-plus-circle"></i></a>';

		echo '<hr>';

		//Tabell
		echo '<div id="wrapper_event_types_table" class="table-responsive table-sm">';
			echo '<table class="table table-hover clickable">';
				echo '<thead class="table-borderless">';
					echo '<tr>';
						echo '<th scope="col">Titel</th>';
						echo '<th scope="col">Antal events</th>';
						echo '<th scope="col">Ta bort</th>';
					echo '</tr>';
				echo '</thead><tbody>';
					if(count($this->event_types) > 0)
						foreach($this->event_types as $event_type)
						{
							echo '<tr data-url="'. base_url('signup/admin/eventtypes/edit/'. $event_type->id) .'">';
							
								//Titel
								echo '<td class="font-weight-bold" scope="row">';
									echo $event_type->title;
								echo '</td>';
							
								//Antal events
								echo '<td>';
									echo $event_type->events_count;
								echo '</td>';
							
								//Ta bort
								echo '<td class="btn_manage">';
									echo '<a href="'. base_url('signup/admin/eventtypes/delete_confirm/'. $event_type->id) .'" class="btn btn-danger">';
										echo '<i class="far fa-trash-alt"></i>';
									echo '</a>';
								echo '</td>';

							echo '</tr>';
						}
					else
						echo '<tr><td colspan="4" class="text-center">&ndash; Inga event-typer &ndash;</td></tr>';
				echo '</tbody>';
			echo '</table>';
		echo '</div>'; //end #wrapper_event_types_table
	}

	private function view_form()
	{
		//variabler
		$is_new = $this->event_type == null;

		//--print--
		echo '<div id="wrapper_eventtypes_form">';

			//Rubrik
			echo $is_new
				? '<h5>Skapa ny event-typ</h5>'
				: '<h5>Redigera event-typ</h5>';
			
			echo '<form class="ssg_form" action="'. base_url('signup/admin/eventtypes/'. ($is_new ? 'insert' : 'update')) .'" method="post">';

				//auto-event-id
				echo !$is_new
					? '<input type="hidden" name="id" value="'. $this->event_type->id .'">'
					: null;
				
				//Titel
				echo  '<div class="form-group">';
					echo '<label for="input_title">Titel<span class="text-danger">*</span></label>';
					echo '<input type="text" id="input_title" name="title" class="form-control" value="'. ($is_new ? null : $this->event_type->title) .'" required>';
				echo '</div>';

				//Tillbaka
				echo '<a href="'. base_url('signup/admin/eventtypes') .'" class="btn btn-primary">&laquo; Tillbaka</a> ';

				//Submit
				echo '<button type="submit" class="btn btn-success">'. ($is_new ? 'Skapa event-typ <i class="fas fa-plus-circle"></i>' : 'Spara ändringar <i class="fas fa-save"></i>') .'</button>';

			echo '</form>';
		echo '</div>'; //end #wrapper_eventtypes_form
	}

	private function view_delete_confirm()
	{
		if($this->event_type_count <= 0) //går bra att ta bort
		{
			echo '<div class="row text-center">';
				echo '<h5 class="col">Är du säker på att du vill ta bort event-typen: <strong>'. $this->event_type->title .'</strong>?</h5>';
			echo '</div>';

			echo '<div class="row text-center mt-2">';
				echo '<div class="col">';
					echo '<a href="'. base_url('signup/admin/eventtypes/delete/'. $this->event_type_id) .'" class="btn btn-success mr-2">Ja</a>';
					echo '<a href="'. base_url('signup/admin/eventtypes/') .'" class="btn btn-danger">Nej</a>';
				echo '</div>';
			echo '</div>';
		}
		else //kan inte ta bort
		{
			echo '<div class="row text-center">';
				echo '<p class="col-12">Det går inte att ta bort <strong>'. $this->event_type->title .'</strong> eftersom databasen innehåller <strong>'. $this->event_type_count .'</strong> events av denna typ.</p>';
				echo '<div class="col-12">';
					echo '<a href="'. base_url('signup/admin/eventtypes/') .'" class="btn btn-primary">Tillbaka</a>';
				echo '<div>';
			echo '</div>';
		}
	}

	private function get_event_types()
	{
		$sql =
			'SELECT
				et.id, et.title,
				COUNT(e.type_id) AS events_count
			FROM ssg_event_types et
			LEFT OUTER JOIN ssg_events e
				ON et.id = e.type_id
			GROUP BY et.id';
		return $this->CI->db->query($sql)->result();
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
		assert(!empty($vars->title), "title: $vars->title");
		
		$data = array(
			'title' => $vars->title
		);
		$this->CI->db->insert('ssg_event_types', $data);
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
		assert(!empty($vars->title), "title: $vars->title");
		assert(isset($vars->id) && is_numeric($vars->id), "id: $vars->id");

		$data = array(
			'title' => $vars->title
		);
		$this->CI->db->where('id', $vars->id)->update('ssg_event_types', $data);
	}

	public function get_code()
	{
		return 'eventtypes';
	}

	public function get_title()
	{
		return 'Event-typer';
	}

	public function get_permissions_needed()
	{
		return array('s0');
	}
}
?>