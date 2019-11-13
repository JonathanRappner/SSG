<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Hanterar Global Alerts, alerts till medlemmar som visas uppe-vid på hemsidan.
 */
class Admin_global_alerts implements Adminpanel
{
	protected $CI;
	private
		$alerts;

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
			$this->alerts = $this->CI->db->query('SELECT id, text, class, DATE_FORMAT(expiration_date, "%Y-%m-%d %H:%i") AS expiration_date FROM ssg_global_alerts')->result();
		}
		else if($this->view == 'insert')
		{
			$post = (object)$this->CI->input->post();

			$this->insert($post);
			
			//success
			$this->CI->alerts->add_alert('success', 'Meddelandet lades till utan problem.');
			redirect('signup/admin/'. $this->get_code());
		}
		else if($this->view == 'delete')
		{
			$global_alert_id = $var2;
			assert(isset($global_alert_id) && is_numeric($global_alert_id));
			
			$this->CI->db->delete('ssg_global_alerts', array('id' => $global_alert_id));

			//success
			$this->CI->alerts->add_alert('success', 'Meddelandet togs bort utan problem.');
			redirect('signup/admin/'. $this->get_code());
		}
	}

	public function view()
	{
		if($this->view == null) //main
			$this->view_main();
		else if($this->view == 'new')
			$this->view_form();
	}

	private function view_form()
	{
		//js
		echo '<script src="'. base_url('js/signup/form_validation.js') .'"></script>';
		echo '<script src="'. base_url('js/signup/adminpanels/global_alerts.js') .'"></script>';

		//--print--
		echo '<div id="wrapper_global_alerts_form">';

			//Rubrik
			echo '<h5>Nytt Globalt meddelande</h5>';
			
			echo '<form class="ssg_form" action="'. base_url('signup/admin/'. $this->get_code() .'/insert') .'" method="post">';
				
				//Text
				echo '<div class="form-group">';
					echo '<label for="input_text">Text</label>';
					echo '<textarea class="form-control" id="input_text" name="text" rows="3" required></textarea>';
				echo '</div>';

				//Stil
				echo '<div class="form-group">';
					echo '<label for="input_class">Stil</label>';
					echo '<select class="form-control" id="input_class" name="class" rows="3">';
						echo '<option value="primary">Blå</option>';
						echo '<option value="success">Grön</option>';
						echo '<option value="danger">Röd</option>';
						echo '<option value="warning">Gul</option>';
						echo '<option value="secondary">Grå</option>';
					echo '</select>';
				echo '</div>';

				//Slutdatum
				echo '<div class="form-group">';
					echo '<label for="input_expiration_date">Slutdatum (åååå-mm-dd hh:mm) <small>(lämna tomt om meddelandet ska visas för alltid)</small></label>';
					echo '<input class="form-control" id="input_expiration_date" name="expiration_date" value="'. date('Y-m-d', strtotime('+1 week')) .' 00:00" />';
				echo '</div>';

				//Preview
				echo '<div class="form-group">';
					echo '<label for="input_">Förhandsvisning</label>';
					echo '<div class="preview">&ndash;</div>';
				echo '</div>';

				//Submit
				echo '<button type="submit" class="btn btn-success">Skapa meddelande <i class="fas fa-plus-circle"></i></button>';

			echo '</form>';
		echo '</div>'; //end #wrapper_global_alerts_form
	}

	private function view_main()
	{
		$alert_preview_max_length = 60;

		echo '<p>Här kan du skapa och ta bort Globala meddelanden. Dessa meddelanden visas under menyraden på hemsidan för alla inloggade användare. <a href="'. base_url('images/guider/global_alert.png') .'">Exempel</a></p>';
		echo '<p class="font-italic">Gamla meddelanden göms på framsidan när de gått ut och rensas härifrån dagen efter.</p>';

		echo '<a href="'. base_url('signup/admin/global_alerts/new/') .'" class="btn btn-success">Skapa nytt Meddelande <i class="fas fa-plus-circle"></i></a>';

		echo '<hr>';

		//Tabell
		echo '<div id="wrapper_global_alerts_table" class="table-responsive table-sm">';
			echo '<table class="table table-hover">';
				echo '<thead class="table-borderless">';
					echo '<tr>';
						echo '<th scope="col">ID</th>';
						echo '<th scope="col">Text</th>';
						echo '<th scope="col">Stil</th>';
						echo '<th scope="col">Slutdatum</th>';
						echo '<th scope="col">Ta bort</th>';
					echo '</tr>';
				echo '</thead><tbody>';
					if(count($this->alerts) > 0)
						foreach($this->alerts as $alert)
						{
							echo '<tr data-url="'. base_url('signup/admin/'. $this->get_code() .'/edit/'. $alert->id) .'">';
							
								//ID
								echo '<td class="font-weight-bold" scope="row">';
									echo $alert->id;
								echo '</td>';
							
								//Text
								echo '<td>';
									echo strlen($alert->text) > $alert_preview_max_length
										? '<span title="'. $alert->text .'">'. mb_substr($alert->text, 0, $alert_preview_max_length) .'...</span>'
										: $alert->text;
								echo '</td>';
							
								//Stil
								echo '<td>';
									echo "<div class='alert alert-{$alert->class}'>{$alert->class}</div>";
								echo '</td>';
							
								//Slutdatum
								echo '<td>';
									echo ($alert->expiration_date)
										? $alert->expiration_date
										: '&ndash; Aldrig &ndash;';
								echo '</td>';
							
								//Ta bort
								echo '<td class="btn_manage">';
									echo '<a href="'. base_url('signup/admin/'. $this->get_code() .'/delete/'. $alert->id) .'" class="btn btn-danger">';
										echo '<i class="far fa-trash-alt"></i>';
									echo '</a>';
								echo '</td>';

							echo '</tr>';
						}
					else
						echo '<tr><td colspan="5" class="text-center">&ndash; Inga globala meddelanden &ndash;</td></tr>';
				echo '</tbody>';
			echo '</table>';
		echo '</div>'; //end #wrapper_event_types_table
	}

	/**
	 * Lägg till meddelande
	 *
	 * @param object $data
	 * @return void
	 */
	private function insert($vars)
	{
		$data = array(
			'text' => $vars->text,
			'class' => $vars->class,
			'expiration_date' => (mb_strlen($vars->expiration_date) > 0) ? $vars->expiration_date : null, //null om tom sträng
		);

		$this->CI->db->insert('ssg_global_alerts', $data);
	}

	public function get_code()
	{
		return 'global_alerts';
	}

	public function get_title()
	{
		return 'Globala meddelanden';
	}

	public function get_permissions_needed()
	{
		return array('s0', 's1', 's2', 's3', 's4');
	}
}
?>