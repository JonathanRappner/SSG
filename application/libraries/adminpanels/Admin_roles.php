<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Hanterar befattningar
 */
class Admin_roles implements Adminpanel
{
	protected $CI;
	private
		$role,
		$roles,
		$role_id,
		$roles_count;

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
			$this->roles = $this->get_roles();
		}
		else if($this->view == 'edit') //formulär: redigera
		{
			$this->role_id = $var2;
			$this->role = $this->CI->db->query('SELECT * FROM ssg_roles WHERE id = ? AND NOT dummy', $this->role_id)->row();
		}
		else if($this->view == 'delete_confirm') //bekräfta borttagning
		{
			$this->role_id = $var2;
			$this->roles_count = $this->CI->db->query('SELECT COUNT(*) AS count FROM ssg_signups WHERE role_id = ?', $this->role_id)->row()->count;
			$this->role = $this->CI->db->query('SELECT * FROM ssg_roles WHERE id = ? AND NOT dummy', $this->role_id)->row();
		}
		else if($this->view == 'insert') //lägg till
		{
			//variabler
			$vars = (object)$this->CI->input->post();
			
			//exekvera
			$this->insert($vars);

			//success
			$this->CI->alerts->add_alert('success', 'Befattningen lades till utan problem.');
			redirect('signup/admin/roles');
		}
		else if($this->view == 'update') //spara ändringar
		{
			//variabler
			$vars = (object)$this->CI->input->post();
			
			//exekvera
			$this->update($vars);

			//success
			$this->CI->alerts->add_alert('success', 'Ändringarna sparades utan problem.');
			redirect('signup/admin/roles');
		}
		else if($this->view == 'delete') //ta bort
		{
			$this->role_id = $var2;
			assert(isset($this->role_id) && is_numeric($this->role_id));
			
			$this->CI->db->delete('ssg_roles', array('id' => $this->role_id));

			//success
			$this->CI->alerts->add_alert('success', 'Befattningen togs bort utan problem.');
			redirect('signup/admin/roles');
		}
	}

	public function view()
	{
		if($this->view == null) //main
			$this->view_main();
		else if($this->view == 'new') //formulär: ny
			$this->view_form();
		else if($this->view == 'edit') //formulär: redigera
			$this->view_form();
		else if($this->view == 'delete_confirm') //bekräfta borttagning
			$this->view_delete_confirm();
	}

	private function view_main()
	{
		echo '<a href="'. base_url('signup/admin/roles/new/') .'" class="btn btn-success">Skapa ny befattning <i class="fas fa-plus-circle"></i></a>';

		echo '<hr>';

		//Tabell
		echo '<div id="wrapper_roles_table" class="table-responsive table-sm">';
			echo '<table class="table table-hover clickable">';
				echo '<thead class="table-borderless">';
					echo '<tr>';
						echo '<th scope="col">Namn</th>';
						echo '<th scope="col">Namn långt</th>';
						echo '<th scope="col">Sortering</th>';
						echo '<th scope="col">Antal anmälningar</th>';
						echo '<th scope="col">Ta bort</th>';
					echo '</tr>';
				echo '</thead><tbody>';
					if(count($this->roles) > 0)
						foreach($this->roles as $role)
						{
							echo '<tr data-url="'. base_url('signup/admin/roles/edit/'. $role->id) .'">';
							
								//Namn
								echo '<td class="font-weight-bold" scope="row">';
									echo $role->name;
								echo '</td>';
							
								//Namn långt
								echo '<td>';
									echo $role->name_long;
								echo '</td>';
							
								//Sortering
								echo '<td>';
									echo $role->sorting;
								echo '</td>';
							
								//Antal anmälningar
								echo '<td>';
									echo $role->signups_count;
								echo '</td>';
							
								//Ta bort
								echo '<td class="btn_manage">';
									echo '<a href="'. base_url('signup/admin/roles/delete_confirm/'. $role->id) .'" class="btn btn-danger">';
										echo '<i class="far fa-trash-alt"></i>';
									echo '</a>';
								echo '</td>';

							echo '</tr>';
						}
					else
						echo '<tr><td colspan="5" class="text-center">&ndash; Inga befattningar &ndash;</td></tr>';
				echo '</tbody>';
			echo '</table>';
		echo '</div>'; //end #wrapper_roles_table
	}

	private function view_form()
	{
		//variabler
		$is_new = $this->role == null;

		//--print--
		echo '<div id="wrapper_roles_form">';

			//Rubrik
			echo $is_new
				? '<h5>Skapa ny befattning</h5>'
				: '<h5>Redigera befattning</h5>';
			
			echo '<form class="ssg_form" action="'. base_url('signup/admin/roles/'. ($is_new ? 'insert' : 'update')) .'" method="post">';

				//id
				echo !$is_new
					? '<input type="hidden" name="id" value="'. $this->role->id .'">'
					: null;
				
				//Namn
				echo  '<div class="form-group">';
					echo '<label for="input_name">Namn<span class="text-danger">*</span></label>';
					echo '<input type="text" id="input_name" name="name" class="form-control" value="'. ($is_new ? null : $this->role->name) .'" required>';
				echo '</div>';
				
				//Namn långt
				echo  '<div class="form-group">';
					echo '<label for="input_name_long">Namn långt</label>';
					echo '<input type="text" id="input_name_long" name="name_long" class="form-control" value="'. ($is_new ? null : $this->role->name_long) .'">';
				echo '</div>';
				
				//Sortering
				echo  '<div class="form-group">';
					echo '<label for="input_sorting">Sortering<span class="text-danger">*</span>';
						echo ' <small>Ett lägre nummer gör att befattningen listas längre upp i listor.</small>';
					echo '</label>';
					echo '<input type="number" id="input_sorting" name="sorting" class="form-control" value="'. ($is_new ? 0 : $this->role->sorting) .'" min="0" required>';
				echo '</div>';

				//Tillbaka
				echo '<a href="'. base_url('signup/admin/roles') .'" class="btn btn-primary">&laquo; Tillbaka</a> ';

				//Submit
				echo '<button type="submit" class="btn btn-success">'. ($is_new ? 'Skapa befattning <i class="fas fa-plus-circle"></i>' : 'Spara ändringar <i class="fas fa-save"></i>') .'</button>';

			echo '</form>';
		echo '</div>'; //end #wrapper_roles_form
	}

	private function view_delete_confirm()
	{
		if($this->roles_count <= 0) //går bra att ta bort
		{
			echo '<div class="row text-center">';
				echo '<h5 class="col">Är du säker på att du vill ta bort befattningen: <strong>'. $this->role->name .'</strong>?</h5>';
			echo '</div>';

			echo '<div class="row text-center mt-2">';
				echo '<div class="col">';
					echo '<a href="'. base_url('signup/admin/roles/delete/'. $this->role_id) .'" class="btn btn-success mr-2">Ja</a>';
					echo '<a href="'. base_url('signup/admin/roles/') .'" class="btn btn-danger">Nej</a>';
				echo '</div>';
			echo '</div>';
		}
		else //kan inte ta bort
		{
			echo '<div class="row text-center">';
				echo '<p class="col-12">Det går inte att ta bort <strong>'. $this->role->name .'</strong> eftersom databasen innehåller <strong>'. $this->roles_count .'</strong> anmälningar där denna befattning är vald.</p>';
				echo '<div class="col-12">';
					echo '<a href="'. base_url('signup/admin/roles/') .'" class="btn btn-primary">Tillbaka</a>';
				echo '<div>';
		}
	}

	private function get_roles()
	{
		$sql =
			'SELECT
				r.id, name, name_long, sorting,
				COUNT(s.role_id) AS signups_count
			FROM ssg_roles r
			LEFT OUTER JOIN ssg_signups s
				ON s.role_id = r.id
			WHERE NOT dummy
			GROUP BY r.id
			ORDER BY
				dummy ASC,
				sorting ASC';
		return $this->CI->db->query($sql)->result();
	}

	private function insert($vars)
	{
		//input-sanering
		assert(isset($vars), 'Post-variabler saknas.');
		assert(!empty($vars->name), "name: $vars->name");
		assert(isset($vars->sorting) && is_numeric($vars->sorting), "sorting: $vars->sorting");
		
		$data = array(
			'name' => $vars->name,
			'name_long' => $vars->name_long,
			'sorting' => $vars->sorting,
		);
		$this->CI->db->insert('ssg_roles', $data);
	}

	private function update($vars)
	{
		//input-sanering
		assert(isset($vars), 'Post-variabler saknas.');
		assert(!empty($vars->name), "name: $vars->name");
		assert(isset($vars->sorting) && is_numeric($vars->sorting), "sorting: $vars->sorting");
		assert(isset($vars->id) && is_numeric($vars->id), "id: $vars->id");

		$data = array(
			'name' => $vars->name,
			'name_long' => $vars->name_long,
			'sorting' => $vars->sorting,
		);
		$this->CI->db->where('id', $vars->id)->update('ssg_roles', $data);
	}

	public function get_code()
	{
		return 'roles';
	}

	public function get_title()
	{
		return 'Befattningar';
	}

	public function get_permissions_needed()
	{
		return array('s0', 's1');
	}
}
?>