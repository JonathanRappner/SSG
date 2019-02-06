<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Hanterar events
 */
class Admin_groups implements Adminpanel
{
	protected $CI;
	private
		$view,
		$group_id,
		$groups,
		$group,
		$signups_count;

	public function __construct()
	{
		// Assign the CodeIgniter super-object
		$this->CI =& get_instance();
	}

	public function main($var1, $var2)
	{
		//variabler
		$this->view = $var1;

		if($this->view == null) //huvud-vy
		{
			$this->groups = $this->get_groups();
		}
		else if($this->view == 'group') //grupp-formulärs-vy
		{
			$this->group_id = $var2;
			$this->group = $this->CI->db->query('SELECT id, name, code, active, sorting FROM ssg_groups WHERE id = ?', $this->group_id)->row();
		}
		else if($this->view == 'delete_group_confirm') //ta bort grupp bekräftelse
		{
			$this->group_id = $var2;
			
			//hitta antal anmälningar som är gjorda till den här gruppen
			$this->signups_count = $this->CI->db->query('SELECT COUNT(*) as count FROM ssg_signups WHERE group_id = ?', $this->group_id)->row()->count;
			$this->group = $this->CI->db->query('SELECT name FROM ssg_groups WHERE id = ?', $this->group_id)->row();
		}
		else if($this->view == 'delete_group') //ta bort grupp
		{
			$this->group_id = $var2;
			assert(is_numeric($this->group_id), 'Grupp-id är fel.');
			
			//ta bort grupp
			//DB är inställd på att sätta tillhörande ssg_members.group_id till null.
			//Kommer ge db-fel om det finns anmälningar till den här gruppen.
			$this->CI->db->delete('ssg_groups', array('id' => $this->group_id));

			//success
			$this->CI->alerts->add_alert('success', 'Gruppen togs bort utan problem.');
			redirect('signup/admin/groups');
		}
		else if($this->view == 'add_group') //lägg till grupp
		{
			//variabler
			$vars = (object)$this->CI->input->post();

			//verkställ
			$this->add_group($vars);

			//success
			$this->CI->alerts->add_alert('success', 'Gruppen sparades utan problem.');
			redirect('signup/admin/groups');
		}
		else if($this->view == 'update_group') //uppdatera grupp
		{
			//variabler
			$vars = (object)$this->CI->input->post();

			//verkställ
			$this->update_group($vars);

			//success
			$this->CI->alerts->add_alert('success', 'Gruppen sparades utan problem.');
			redirect('signup/admin/groups');
		}
	}

	public function view()
	{
		//js
		echo 
			'<script src="'. base_url('js/signup/form_validation.js') .'"></script>
			<script src="'. base_url('js/signup/adminpanels/groups.js') .'"></script>';
		
		if($this->view == null) //huvud-vy
		{
			$this->print_form();
			echo '<hr>';
			$this->print_table();
		}
		else if($this->view == 'group') //grupp-formulärs-vy
		{
			//breadcrumbs
			echo '<nav aria-label="breadcrumb"><ol class="breadcrumb">';
				echo '<li class="breadcrumb-item"><a href="'. base_url('signup/admin/groups') .'">Grupper</a></li>';
				echo '<li class="breadcrumb-item active" aria-current="page">'. $this->group->name .'</li>';
			echo '</ol></nav>';

			//se medlemmar-knapp
			echo '<a href="'. base_url('signup/admin/members/group/'. $this->group_id) .'" class="btn btn-primary" style="font-size: 1.4rem;">Se gruppens medlemmar »</a>';

			echo '<hr>';

			$this->print_form();
		}
		else if($this->view == 'delete_group_confirm')
		{
			if($this->signups_count <= 0) //går bra att ta bort
			{
				echo '<div class="row text-center">';
					echo '<h5 class="col">Är du säker på att du vill ta bort '. $this->group->name .'?</h5>';
				echo '</div>';

				echo '<div class="row text-center mt-2">';
					echo '<div class="col">';
						echo '<a href="'. base_url('signup/admin/groups/delete_group/'. $this->group_id) .'" class="btn btn-success mr-2">Ja</a>';
						echo '<a href="'. base_url('signup/admin/groups/') .'" class="btn btn-danger">Nej</a>';
					echo '</div>';
				echo '</div>';
			}
			else //kan inte ta bort
			{
				echo '<div class="row text-center">';
					echo '<p class="col-12">Det går inte att ta bort '. $this->group->name .' eftersom databasen innehåller <strong>'. $this->signups_count .'</strong> anmälningar där gruppen är vald.</p>';
					echo '<div class="col-12">';
						echo '<a href="'. base_url('signup/admin/groups/') .'" class="btn btn-primary">Tillbaka</a>';
					echo '<div>';
				echo '</div>';
			}
		}
	}

	/**
	 * Skriver ut formulär för att redigera eller skapa ny grupp.
	 *
	 * @return void
	 */
	private function print_form()
	{
		//variabler
		$is_new = $this->group == null;

		//--print--
		echo '<div id="wrapper_groups_form" '. ($is_new ? 'style="display: none;"' : null) .'>';

			//heading
			echo $is_new
				? '<h5>Skapa ny grupp</h5>'
				: '<h5>Redigera grupp</h5>';
			
			echo '<form class="ssg_form" action="'. base_url('signup/admin/groups/'. ($is_new ? 'add_group' : 'update_group')) .'" method="post">';

				//event_id hidden input
				if(!$is_new)
					echo '<input type="hidden" name="group_id" value="'. $this->group->id .'">';

				//Namn
				echo  '<div class="form-group">';
					echo '<label for="input_name">Namn<span class="text-danger">*</span></label>';
					echo '<input type="text" id="input_name" name="name" class="form-control" value="'. ($is_new ? null : $this->group->name) .'" required>';
				echo '</div>';

				//Kod
				echo  '<div class="form-group">';
					echo '<label for="input_code">Kod<span class="text-danger">*</span> (ex: ea, sl, eb)</label>';
					echo '<input type="text" id="input_code" name="code" class="form-control" value="'. ($is_new ? null : $this->group->code) .'" required>';
				echo '</div>';

				//Sortering
				echo  '<div class="form-group">';
					echo '<label for="input_sorting">Sortering<span class="text-danger">*</span> (Ett lägre nummer gör att gruppen listas längre upp i listor.)</label>';
					echo '<input type="number" id="input_sorting" name="sorting" class="form-control" value="'. ($is_new ? 999 : $this->group->sorting) .'" required>';
				echo '</div>';

				//Aktiv
				echo '<div class="form-group form-check">';
					echo '<input class="form-check-input" type="checkbox" value="1" '. (!$is_new && $this->group->active ? 'checked' : null) .' id="active" name="active">';
					echo '<label class="form-check-label" for="active">';
						echo 'Aktiv ';
						echo '<small>(Om en grupp sparas som inaktiv så sätts dess medlemmas grupptillhörighet till "Ingen grupp")</small>';
					echo '</label>';
				echo '</div>';

				//ikon-test
				if(!$is_new)
				{
					echo '<div class="form-group">';
						echo '<label class="form-check-label">Ikon-info</label>';
						echo '<div>Varje aktiv grupp ska ha tre ikon-filer i mappen <i>'. base_url('images/group_icons/') .'</i> med namnen:</div>';
						echo '<ul>';
							echo '<li><i>&lt;grupp-kod&gt;.png</i> (512x512 pixlar)</li>';
							echo '<li><i>&lt;grupp-kod&gt;_32.png</i> (32x32 pixlar)</li>';
							echo '<li><i>&lt;grupp-kod&gt;_16.png</i> (16x16 pixlar)</li>';
						echo '</ul>';

						
						echo '<label class="form-check-label">Ikon-test</label>';
						echo '<ul>';
							echo '<li>Ikon 16 pixlar: <img src="'. base_url('images/group_icons/'. $this->group->code .'_16.png') .'" style="width: 16px;"></li>';
							echo '<li>Ikon 32 pixlar: <img src="'. base_url('images/group_icons/'. $this->group->code .'_32.png') .'" style="width: 32px;"></li>';
							echo '<li>Ikon 512 pixlar: <img src="'. base_url('images/group_icons/'. $this->group->code .'.png') .'" style="width: 128px;"></li>';
						echo '</ul>';
					echo '</div>';
				}

				//Submit
				echo '<button type="submit" class="btn btn-success">'. ($is_new ? 'Skapa grupp <i class="fas fa-plus-circle"></i>' : 'Spara ändringar <i class="fas fa-save"></i>') .'</button>';

			echo '</form>';
		echo '</div>';

		//visa formulär-knapp
		echo '<button id="btn_show_form" class="btn btn-success" '. (!$is_new ? 'style="display: none;"' : null) .'>Skapa ny grupp <i class="fas fa-plus-circle"></i></button>';
	}

	/**
	 * Skriver ut tabell med samtliga grupper.
	 *
	 * @return void
	 */
	private function print_table()
	{
		echo '<div class="row">';
			echo '<div id="wrapper_groups_table" class="table-responsive table-sm col-lg-8">';
				echo '<table class="table table-hover clickable">';
					echo '<thead class="table-borderless">';
						echo '<tr>';
							echo '<th scope="col" width="50%">Namn</th>';
							echo '<th scope="col">Medlemmar</th>';
							echo '<th scope="col">Aktiv</th>';
							echo '<th scope="col">Ta bort</th>';
						echo '</tr>';
					echo '</thead><tbody>';
						if(count($this->groups) > 0)
							foreach($this->groups as $grp)
							{
								echo '<tr data-url="'. base_url('signup/admin/groups/group/'. $grp->id) .'">';
								
									//namn
									echo '<td class="font-weight-bold" scope="row">';
										echo group_icon($grp->code) .' ';
										echo $grp->name;
									echo '</td>';
									
									//antal medlemmar
									echo '<td>';
										echo $grp->member_count;
									echo '</td>';
									
									//aktiv
									echo '<td>';
										echo $grp->active ? '<strong class="text-success">Ja</strong>' : '<strong class="text-danger">Nej</strong>';
									echo '</td>';

									//ta bort
									echo '<td class="btn_manage">';
										echo '<a href="'. base_url("signup/admin/groups/delete_group_confirm/$grp->id") .'" class="btn btn-danger">';
											echo '<i class="far fa-trash-alt"></i>';
										echo '</a>';
									echo '</td>';

								echo '</tr>';
							}
						else
							echo '<tr><td colspan="4" class="text-center">&ndash; Inga grupper &ndash;</td></tr>';
					echo '</tbody>';
				echo '</table>';
			echo '</div>'; //end #wrapper_groups_table
		echo '</div>'; //end div.row
	}

	/**
	 * Hämta alla grupper med medlemsantal.
	 *
	 * @return void
	 */
	private function get_groups()
	{
		$sql =
			'SELECT
				grp.id, grp.name, code, active,
				COUNT(mem.id) member_count
			FROM ssg_groups grp
			LEFT JOIN ssg_members mem
				ON mem.group_id = grp.id
			WHERE NOT dummy
			GROUP BY grp.id
			ORDER BY
				active DESC,
				sorting ASC';
		return $this->CI->db->query($sql)->result();
	}

	/**
	 * Uppdatera grupp.
	 *
	 * @param object $vars POST-variabler.
	 * @return void
	 */
	private function update_group($vars)
	{
		//input-sanering
		assert(isset($vars), 'Post-variabler saknas.');
		assert(isset($vars->name) && strlen($vars->name) > 0, "Grupp-namn: $vars->name");
		assert(isset($vars->code) && strlen($vars->code) > 0, "Grupp-kod: $vars->code");
		assert(isset($vars->sorting) && is_numeric($vars->sorting), "Gruppsotertingsvärde: $vars->sorting");

		//uppdatera grupp
		$data = array
		(
			'name' => $vars->name,
			'code' => $vars->code,
			'sorting' => $vars->sorting,
			'active' => isset($vars->active) && $vars->active,
		);
		$this->CI->db->where('id', $vars->group_id)->update('ssg_groups', $data);

		//ta bort groupp-tillhörigheter om aktiv sattes till false
		if(!isset($vars->active) || !$vars->active)
		{
			$data = array('group_id' => null);
			$this->CI->db->where('group_id', $vars->group_id)->update('ssg_members', $data);
		}
	}

	/**
	 * Lägg till grupp.
	 *
	 * @param object $vars POST-variabler.
	 * @return void
	 */
	private function add_group($vars)
	{
		//input-sanering
		assert(isset($vars), 'Post-variabler saknas');
		assert(isset($vars->name) && strlen($vars->name) > 0, "Grupp-namn: $vars->name");
		assert(isset($vars->code) && strlen($vars->code) > 0, "Grupp-kod: $vars->code");
		assert(isset($vars->sorting) && is_numeric($vars->sorting) > 0, "Gruppsotertingsvärde: $vars->sorting");

		$data = array
		(
			'name' => $vars->name,
			'code' => $vars->code,
			'sorting' => $vars->sorting,
			'active' => isset($vars->active) && $vars->active,
		);
		$this->CI->db->insert('ssg_groups', $data);
	}

	public function get_code()
	{
		return 'groups';
	}

	public function get_title()
	{
		return 'Grupper';
	}

	public function get_permissions_needed()
	{
		return array('s0', 'grpchef');
	}
}
?>