<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Hanterar uppehållsperioder
 */
class Admin_recesses implements Adminpanel
{
	protected $CI;
	private
		$recesses,
		$recess,
		$recess_id;

	public function __construct()
	{
		// Assign the CodeIgniter super-object
		$this->CI =& get_instance();
	}

	public function main($var1, $var2, $var3)
	{
		//variabler
		$this->view = $var1;

		if($this->view == null)
		{
			$this->recesses = $this->get_recesses();
		}
		else if($this->view == 'edit')
		{
			$this->recess_id = $var2;
			$this->recess = $this->CI->db->query('SELECT id, start_date, length_days FROM ssg_recesses WHERE id = ?', $this->recess_id)->row();
		}
		else if($this->view == 'insert')
		{
			$vars = (object)$this->CI->input->post();

			$this->insert($vars);

			//success
			$this->CI->alerts->add_alert('success', 'Uppehållet skapades utan problem.');
			redirect('signup/admin/recesses');
		}
		else if($this->view == 'update')
		{
			$vars = (object)$this->CI->input->post();

			$this->update($vars);

			//success
			$this->CI->alerts->add_alert('success', 'Ändringarna sparades utan problem.');
			redirect('signup/admin/recesses');
		}
		else if($this->view == 'delete')
		{
			$this->recess_id = $var2;

			$this->CI->db->delete('ssg_recesses', array('id' => $this->recess_id));

			//success
			$this->CI->alerts->add_alert('success', 'Uppehållet togs bort utan problem.');
			redirect('signup/admin/recesses');
		}
	}

	public function view()
	{
		if($this->view == null)
		{
			$this->view_main();
		}
		else if($this->view == 'new')
		{
			$this->print_form();
		}
		else if($this->view == 'edit')
		{
			$this->print_form();
		}
	}

	/**
	 * Skriver ut huvud-vy
	 *
	 * @return void
	 */
	private function view_main()
	{
		echo '<p>Här administrerar du uppehållsperioder där inga automatiska events skapas. Alla datum är inkluderande. Dvs. inga auto-events skapas under start eller slutdagen du definierar.</p>';
		echo '<p>Om du skapar ett uppehåll där det redan finns events så får du ta bort dem på egen hand.</p>';

		echo '<a href="'. base_url('signup/admin/recesses/new/') .'" class="btn btn-success">Skapa nytt uppehåll <i class="fas fa-plus-circle"></i></a>';

		echo '<hr>';

		//Tabell
		echo '<div id="wrapper_recesses_table" class="table-responsive table-sm">';
			echo '<table class="table table-hover clickable">';
				echo '<thead class="table-borderless">';
					echo '<tr>';
						echo '<th scope="col">Start-datum</th>';
						echo '<th scope="col">Slut-datum</th>';
						echo '<th scope="col">Längd (dagar)</th>';
						echo '<th scope="col">Ta bort</th>';
					echo '</tr>';
				echo '</thead><tbody>';
					if(count($this->recesses) > 0)
						foreach($this->recesses as $recess)
						{
							echo '<tr data-url="'. base_url('signup/admin/recesses/edit/'. $recess->id) .'">';
							
								//Start
								echo '<td>';
									echo $recess->start_date;
								echo '</td>';
							
								//Längd
								echo '<td>';
									echo $recess->end_date;
								echo '</td>';
							
								//Längd
								echo '<td>';
									echo $recess->length_days;
								echo '</td>';
							
								//Ta bort
								echo '<td class="btn_manage">';
									echo '<a href="'. base_url('signup/admin/recesses/delete/'. $recess->id) .'" class="btn btn-danger">';
										echo '<i class="far fa-trash-alt"></i>';
									echo '</a>';
								echo '</td>';

							echo '</tr>';
						}
					else
						echo '<tr><td colspan="4" class="text-center">&ndash; Inga framtida uppehåll &ndash;</td></tr>';
				echo '</tbody>';
			echo '</table>';
		echo '</div>'; //end #wrapper_recesses_table
	}

	/**
	 * Skriver ut formulär för både ny och redigering.
	 *
	 * @return void
	 */
	private function print_form()
	{
		//variabler
		$is_new = $this->recess == null;

		//--print--
		echo '<div id="wrapper_recesses_form">';

			//Rubrik
			echo $is_new
				? '<h5>Skapa nytt uppehåll</h5>'
				: '<h5>Redigera uppehåll</h5>';
			
			echo '<form class="ssg_form" action="'. base_url('signup/admin/recesses/'. ($is_new ? 'insert' : 'update')) .'" method="post">';

				//auto-event-id
				echo !$is_new
					? '<input type="hidden" name="id" value="'. $this->recess->id .'">'
					: null;
				
				//Start-datum
				echo  '<div class="form-group">';
					echo '<label for="input_start_date">Start-datum<span class="text-danger">*</span></label>';
					echo '<input type="date" id="input_start_date" name="start_date" class="form-control" value="'. ($is_new ? date('Y-m-d') : $this->recess->start_date) .'" required>';
				echo '</div>';
				
				//Längd
				echo  '<div class="form-group">';
					echo '<label for="input_length_days">Längd (dagar)<span class="text-danger">*</span></label>';
					echo '<input type="number" id="input_length_days" name="length_days" class="form-control" value="'. ($is_new ? 7 : $this->recess->length_days) .'" min="1" required>';
				echo '</div>';

				//Tillbaka
				echo '<a href="'. base_url('signup/admin/recesses') .'" class="btn btn-primary">&laquo; Tillbaka</a> ';

				//Submit
				echo '<button type="submit" class="btn btn-success">'. ($is_new ? 'Skapa uppehåll <i class="fas fa-plus-circle"></i>' : 'Spara ändringar <i class="fas fa-save"></i>') .'</button>';

			echo '</form>';
		echo '</div>'; //end #wrapper_recesses_form
	}

	/**
	 * Hämtar framtida uppehåll.
	 *
	 * @return array
	 */
	private function get_recesses()
	{
		$sql =
			'SELECT
				id, start_date, length_days,
				DATE_FORMAT(DATE_ADD(start_date, INTERVAL length_days DAY), "%Y-%m-%d") AS end_date
			FROM ssg_recesses
			ORDER BY start_date ASC';
		return $this->CI->db->query($sql)->result();
	}

	/**
	 * Lägg till uppehåll.
	 *
	 * @param object $vars POST-variabler.
	 * @return void
	 */
	private function insert($vars)
	{
		//input-sanering
		assert(isset($vars), 'Post-variabler saknas.');
		assert(isset($vars->start_date) && preg_match('/([12]\d{3}-(0[1-9]|1[0-2])-(0[1-9]|[12]\d|3[01]))/', $vars->start_date), "start_date: $vars->start_date");
		assert(isset($vars->length_days) && is_numeric($vars->length_days) && $vars->length_days > 0, "length_days: $vars->length_days");
		
		$data = array
		(
			'start_date' => $vars->start_date,
			'length_days' => $vars->length_days,
		);
		$this->CI->db->insert('ssg_recesses', $data);
	}

	/**
	 * Uppdatera uppehåll
	 *
	 * @param object $vars POST-variabler.
	 * @return void
	 */
	private function update($vars)
	{
		assert(isset($vars), 'Post-variabler saknas.');
		assert(isset($vars->id) && is_numeric($vars->id), "id: $vars->id");
		assert(isset($vars->start_date) && preg_match('/([12]\d{3}-(0[1-9]|1[0-2])-(0[1-9]|[12]\d|3[01]))/', $vars->start_date), "start_date: $vars->start_date");
		assert(isset($vars->length_days) && is_numeric($vars->length_days) && $vars->length_days > 0, "length_days: $vars->length_days");

		$data = array
		(
			'start_date' => $vars->start_date,
			'length_days' => $vars->length_days,
		);
		$this->CI->db->where('id', $vars->id)->update('ssg_recesses', $data);
	}

	public function get_code()
	{
		return 'recesses';
	}

	public function get_title()
	{
		return 'Uppehåll';
	}

	public function get_permissions_needed()
	{
		return array('s0');
	}
}
?>