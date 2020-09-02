<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Hanterar grader
 */
class Admin_ranks implements Adminpanel
{
	protected $CI;
	private
		$rank,
		$rank_id,
		$ranks,
		$ranks_count,
		$icons;

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
			$this->ranks = $this->get_ranks();
		}
		else if($this->view == 'new') //formulär: ny
		{
			$this->icons = $this->get_icons();
		}
		else if($this->view == 'edit') //formulär: redigera
		{
			$this->rank_id = $var2;
			$this->rank = $this->CI->db->query('SELECT id, name, icon, sorting FROM ssg_ranks WHERE id = ?', $this->rank_id)->row();
			$this->icons = $this->get_icons();
		}
		else if($this->view == 'delete_confirm') //bekräfta borttagning
		{
			$this->rank_id = $var2;
			$this->ranks_count = $this->CI->db->query('SELECT COUNT(*) AS count FROM ssg_promotions WHERE rank_id = ?', $this->rank_id)->row()->count;
			$this->rank = $this->CI->db->query('SELECT name FROM ssg_ranks WHERE id = ?', $this->rank_id)->row();
		}
		else if($this->view == 'insert') //lägg till
		{
			//variabler
			$vars = (object)$this->CI->input->post();
			
			//exekvera
			$this->insert($vars);

			//success
			$this->CI->alerts->add_alert('success', 'Graden skapades utan problem.');
			redirect('signup/admin/ranks');
		}
		else if($this->view == 'update') //spara ändringar
		{
			//variabler
			$vars = (object)$this->CI->input->post();
			
			//exekvera
			$this->update($vars);

			//success
			$this->CI->alerts->add_alert('success', 'Ändringarna sparades utan problem.');
			redirect('signup/admin/ranks');
		}
		else if($this->view == 'delete') //ta bort
		{
			$this->rank_id = $var2;
			assert(isset($this->rank_id) && is_numeric($this->rank_id));
			
			$this->CI->db->delete('ssg_ranks', array('id' => $this->rank_id));

			//success
			$this->CI->alerts->add_alert('success', 'Graden togs bort utan problem.');
			redirect('signup/admin/ranks');
		}
	}

	public function view()
	{
		//js
		echo '<script src="'. base_url('js/signup/adminpanels/ranks.js') .'"></script>';

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
		echo '<a href="'. base_url('signup/admin/ranks/new/') .'" class="btn btn-success">Skapa ny grad <i class="fas fa-plus-circle"></i></a>';

		echo '<hr>';

		//Tabell
		echo '<div id="wrapper_ranks_table" class="table-responsive table-sm">';
			echo '<table class="table table-hover clickable">';
				echo '<thead class="table-borderless">';
					echo '<tr>';
						echo '<th scope="col">Namn</th>';
						echo '<th scope="col">Ikon</th>';
						echo '<th scope="col">Sortering</th>';
						echo '<th scope="col">Antal bumpningar</th>';
						echo '<th scope="col">Ta bort</th>';
					echo '</tr>';
				echo '</thead><tbody>';
					if(count($this->ranks) > 0)
						foreach($this->ranks as $rank)
						{
							echo '<tr data-url="'. base_url('signup/admin/ranks/edit/'. $rank->id) .'">';
							
								//Namn
								echo '<td class="font-weight-bold" scope="row">';
									echo $rank->name;
								echo '</td>';
							
								//Ikon
								echo '<td>';
									echo '<img src="'. base_url('images/rank_icons/'. $rank->icon) .'" height="20">';
								echo '</td>';
							
								//Sortering
								echo '<td>';
									echo $rank->sorting;
								echo '</td>';
							
								//Antal bumpningar
								echo '<td>';
									echo $rank->promotions_count;
								echo '</td>';
							
								//Ta bort
								echo '<td class="btn_manage">';
									echo '<a href="'. base_url('signup/admin/ranks/delete_confirm/'. $rank->id) .'" class="btn btn-danger">';
										echo '<i class="far fa-trash-alt"></i>';
									echo '</a>';
								echo '</td>';

							echo '</tr>';
						}
					else
						echo '<tr><td colspan="4" class="text-center">&ndash; Inga befattningar &ndash;</td></tr>';
				echo '</tbody>';
			echo '</table>';
		echo '</div>'; //end #wrapper_ranks_table
	}
	
	private function view_form()
	{
		//variabler
		$is_new = $this->rank == null;
		
		//ikoner options
		$icon_options_string = null;
		foreach($this->icons as $icon)
			$icon_options_string .= "<option". (isset($this->rank->icon) && $this->rank->icon == $icon ? ' selected' : null) .">$icon</option>";

		//--print--
		echo '<div id="wrapper_ranks_form">';

			//Rubrik
			echo $is_new
				? '<h5>Skapa ny grad</h5>'
				: '<h5>Redigera grad</h5>';
			
			echo '<form class="ssg_form" action="'. base_url('signup/admin/ranks/'. ($is_new ? 'insert' : 'update')) .'" method="post">';

				//id
				echo !$is_new
					? '<input type="hidden" name="id" value="'. $this->rank->id .'">'
					: null;
				
				//Namn
				echo  '<div class="form-group">';
					echo '<label for="input_name">Namn<span class="text-danger">*</span></label>';
					echo '<input type="text" id="input_name" name="name" class="form-control" value="'. ($is_new ? null : $this->rank->name) .'" required>';
				echo '</div>';
				
				//Ikon
				echo '<div class="form-group">';
					echo '<label for="input_icon">Ikon</label>';
					echo '<select class="form-control" id="input_icon" name="icon">'. $icon_options_string .'</select>';
				echo '</div>';
				
				//Preview
				echo '<div class="form-group">';
					echo '<label>';
						echo 'Förandsvisning';
						echo ' <small>Ikoner ska placeras i '. base_url('images/rank_icons') .'</small>';
					echo '</label>';
					echo '<div><img id="rank_icon" src=""></div>';
				echo '</div>';
				
				//Sortering
				echo  '<div class="form-group">';
					echo '<label for="input_sorting">Sortering<span class="text-danger">*</span>';
						echo ' <small>Ett lägre nummer gör att graden listas längre upp i listor.</small>';
					echo '</label>';
					echo '<input type="number" id="input_sorting" name="sorting" class="form-control" value="'. ($is_new ? 0 : $this->rank->sorting) .'" min="0" required>';
				echo '</div>';

				//Tillbaka
				echo '<a href="'. base_url('signup/admin/ranks') .'" class="btn btn-primary">&laquo; Tillbaka</a> ';

				//Submit
				echo '<button type="submit" class="btn btn-success">'. ($is_new ? 'Skapa grad <i class="fas fa-plus-circle"></i>' : 'Spara ändringar <i class="fas fa-save"></i>') .'</button>';

			echo '</form>';
		echo '</div>'; //end #wrapper_ranks_form
	}

	private function get_ranks()
	{
		$sql =
			'SELECT
				r.id, name, icon, sorting,
				COUNT(p.rank_id) AS promotions_count
			FROM ssg_ranks r
			LEFT OUTER JOIN ssg_promotions p
				ON r.id = p.rank_id
			GROUP BY r.id
			ORDER BY sorting ASC';
		return $this->CI->db->query($sql)->result();
	}

	private function get_icons()
	{
		//variabler
		$folder = 'images/rank_icons/';
		$output = array();
		$icons = scandir($folder);

		foreach($icons as $icon)
			if(!is_dir($folder . $icon))
				$output[] = $icon;
		
		return $output;
	}

	private function view_delete_confirm()
	{
		if($this->ranks_count <= 0) //går bra att ta bort
		{
			echo '<div class="row text-center">';
				echo '<h5 class="col">Är du säker på att du vill ta bort Graden: <strong>'. $this->rank->name .'</strong>?</h5>';
			echo '</div>';

			echo '<div class="row text-center mt-2">';
				echo '<div class="col">';
					echo '<a href="'. base_url('signup/admin/ranks/delete/'. $this->rank_id) .'" class="btn btn-success mr-2">Ja</a>';
					echo '<a href="'. base_url('signup/admin/ranks/') .'" class="btn btn-danger">Nej</a>';
				echo '</div>';
			echo '</div>';
		}
		else //kan inte ta bort
		{
			echo '<div class="row text-center">';
				echo '<p class="col-12">Det går inte att ta bort <strong>'. $this->rank->name .'</strong> eftersom databasen innehåller <strong>'. $this->ranks_count .'</strong> bumpningar av denna grad.</p>';
				echo '<div class="col-12">';
					echo '<a href="'. base_url('signup/admin/ranks/') .'" class="btn btn-primary">Tillbaka</a>';
				echo '<div>';
		}
	}

	private function insert($vars)
	{
		//input-sanering
		assert(isset($vars), 'Post-variabler saknas.');
		assert(!empty($vars->name), "name: $vars->name");
		assert(!empty($vars->icon), "icon: $vars->icon");
		assert(isset($vars->sorting) && is_numeric($vars->sorting), "sorting: $vars->sorting");
		
		$data = array(
			'name' => $vars->name,
			'icon' => $vars->icon,
			'sorting' => $vars->sorting,
		);
		$this->CI->db->insert('ssg_ranks', $data);
	}

	private function update($vars)
	{
		//input-sanering
		assert(isset($vars), 'Post-variabler saknas.');
		assert(!empty($vars->name), "name: $vars->name");
		assert(!empty($vars->icon), "icon: $vars->icon");
		assert(isset($vars->id) && is_numeric($vars->id), "id: $vars->id");
		assert(isset($vars->sorting) && is_numeric($vars->sorting), "sorting: $vars->sorting");

		$data = array(
			'name' => $vars->name,
			'icon' => $vars->icon,
			'sorting' => $vars->sorting,
		);
		$this->CI->db->where('id', $vars->id)->update('ssg_ranks', $data);
	}

	public function get_code()
	{
		return 'ranks';
	}

	public function get_title()
	{
		return 'Grader';
	}

	public function get_permissions_needed()
	{
		return array('s0', 's1');
	}
}
?>