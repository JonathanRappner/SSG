<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Hanterar gruppbefattningar
 */
class Admin_grouproles implements Adminpanel
{
	protected $CI;
	private
		$view,
		$groups,
		$group,
		$group_id,
		$roles;

	public function __construct()
	{
		// Assign the CodeIgniter super-object
		$this->CI =& get_instance();
	}

	public function main($var1, $var2, $var3)
	{
		//variabler
		$this->view = $var1;

		if($this->view == 'group')
		{
			$this->group_id = $var2;
			$this->group = $this->CI->db->query('SELECT name, code FROM ssg_groups WHERE id = ? AND NOT dummy', $this->group_id)->row();
			$this->roles = $this->get_roles($this->group_id);
			assert(isset($this->group), 'Inkorrekt grupp-id.');
		}
		else if($this->view == 'add_role')
		{
			//variabler
			$group_id = $this->CI->input->post('group_id');
			$group_name = $this->CI->db->query('SELECT name FROM ssg_groups WHERE id = ?', $group_id)->row()->name;
			$role_id = $this->CI->input->post('role_id');
			$role_name = $this->CI->db->query('SELECT name FROM ssg_roles WHERE id = ?', $role_id)->row()->name;
			
			//exekvera
			$this->CI->db->insert('ssg_roles_groups', array('group_id'=>$group_id, 'role_id'=>$role_id));
			
			//success
			$this->CI->alerts->add_alert('success', "Kopplade $role_name till $group_name.");
			redirect('signup/admin/grouproles/group/'. $group_id);
		}
		else if($this->view == 'remove_role')
		{
			//variabler
			list($group_id, $role_id) = explode('-', $var2);
			$group_name = $this->CI->db->query('SELECT name FROM ssg_groups WHERE id = ?', $group_id)->row()->name;
			$role_name = $this->CI->db->query('SELECT name FROM ssg_roles WHERE id = ?', $role_id)->row()->name;
			
			//exekvera
			$this->CI->db->delete('ssg_roles_groups', array('group_id'=>$group_id, 'role_id'=>$role_id));
			
			//success
			$this->CI->alerts->add_alert('success', "Tog bort $role_name från $group_name.");
			redirect('signup/admin/grouproles/group/'. $group_id);
		}
		else //main
		{
			$this->groups = $this->get_groups();
		}
	}

	public function view()
	{
		if($this->view == null)
			$this->view_main();
		else if($this->view == 'group')
			$this->view_group();
	}

	/**
	 * Skriver ut tabell med samtliga grupper.
	 *
	 * @return void
	 */
	private function view_main()
	{
		echo '<p>Här kan du redigera vilka befattningar som går att välja i specifika grupper, i anmälningsformuläret.</p>';

		echo '<div class="row">';
			echo '<div id="wrapper_groups_table" class="table-responsive table-sm col-lg-8">';
				echo '<table class="table table-hover clickable">';
					echo '<thead class="table-borderless">';
						echo '<tr>';
							echo '<th scope="col" width="60%">Namn</th>';;
							echo '<th scope="col">Antal befattningar</th>';
							echo '<th scope="col">Aktiv</th>';
						echo '</tr>';
					echo '</thead><tbody>';
						if(count($this->groups) > 0)
							foreach($this->groups as $grp)
							{
								echo '<tr data-url="'. base_url('signup/admin/grouproles/group/'. $grp->id) .'">';
								
									//Namn
									echo '<td class="font-weight-bold" scope="row">';
										echo group_icon($grp->code) .' ';
										echo $grp->name;
									echo '</td>';
								
									//Antal befattningar
									echo '<td>';
										echo $grp->roles_count;
									echo '</td>';
									
									//Aktiv
									echo '<td>';
										echo $grp->active ? '<strong class="text-success">Ja</strong>' : '<strong class="text-danger">Nej</strong>';
									echo '</td>';

								echo '</tr>';
							}
						else
							echo '<tr><td colspan="3" class="text-center">&ndash; Inga grupper &ndash;</td></tr>';
					echo '</tbody>';
				echo '</table>';
			echo '</div>'; //end #wrapper_groups_table
		echo '</div>'; //end div.row
	}

	/**
	 * Vy för enskilld grupp
	 *
	 * @return void
	 */
	private function view_group()
	{
		$roles_options_string = null;
		foreach($this->get_non_connected_roles($this->group_id) as $role)
			$roles_options_string .= "<option value='$role->id'>$role->name</option>";

		//Breadcrumbs
		echo '<nav aria-label="breadcrumb"><ol class="breadcrumb">';
			echo '<li class="breadcrumb-item"><a href="'. base_url('signup/admin/grouproles') .'">Hem</a></li>';
			echo '<li class="breadcrumb-item active" aria-current="page">'. $this->group->name .'</li>';
		echo '</ol></nav>';

		//Ikon & rubrik
		echo '<h4>';
			echo '<img class="group_heading_icon" src="'. base_url('images/group_icons/'. $this->group->code .'_32.png') .'" />';
			echo $this->group->name;
		echo '</h4>';

		//Befattningar
		echo '<div id="wrapper_roles_table" class="row table-responsive table-sm col-lg-8">';
			echo '<table class="table table-hover">';
				echo '<thead class="table-borderless">';
					echo '<tr>';
						echo '<th scope="col" width="80%">Namn</th>';;
						echo '<th scope="col">Koppla bort</th>';
					echo '</tr>';
				echo '</thead><tbody>';
					if(count($this->roles) > 0)
						foreach($this->roles as $role)
						{
							echo '<tr>';
							
								//Namn
								echo '<td class="font-weight-bold" scope="row">';
									echo $role->name;
								echo '</td>';
							
								//Ta bort
								echo '<td class="btn_manage">';
									echo '<a href="'. base_url('signup/admin/grouproles/remove_role/'. $this->group_id .'-'. $role->id) .'" class="btn btn-danger">';
										echo '<i class="fas fa-minus-square"></i>';
									echo '</a>';
								echo '</td>';

							echo '</tr>';
						}
					else
						echo '<tr><td colspan="2" class="text-center">&ndash; Inga befattningar kopplade &ndash;</td></tr>';
				echo '</tbody>';
			echo '</table>';
		echo '</div>'; //end #wrapper_roles_table

		echo '<hr>';

		//Koppla befattning
		echo '<h5>Lägg till befattnings-koppling</h5>';
		echo '<form action="'. base_url('signup/admin/grouproles/add_role/') .'" method="post">';
		echo '<input type="hidden" name="group_id" value="'. $this->group_id .'">';
			echo '<div class="row">';
				
				//grad
				echo '<div class="form-group col-md col-lg-4">';
					echo '<select class="form-control" name="role_id">'. $roles_options_string .'</select>';
				echo '</div>';

				//submit
				echo '<div class="col-12"><button type="submit" class="btn btn-success">Lägg till <i class="fas fa-plus"></i></button></div>';

			echo '</div>'; //end div.row
		echo '</form>';
	}

	/**
	 * Hämtar alla grupper.
	 *
	 * @return array
	 */
	private function get_groups()
	{
		$sql =
			'SELECT ssg_groups.id, ssg_groups.name, code, active, COUNT(ssg_roles.id) AS roles_count
			FROM ssg_groups
			LEFT OUTER JOIN ssg_roles_groups
				ON ssg_roles_groups.group_id = ssg_groups.id
			LEFT OUTER JOIN ssg_roles
				ON ssg_roles_groups.role_id = ssg_roles.id AND NOT ssg_roles.dummy
			WHERE NOT ssg_groups.dummy
			GROUP BY ssg_groups.id
			ORDER BY
				ssg_groups.active DESC,
				ssg_groups.sorting ASC';
		
		return $this->CI->db->query($sql)->result();
	}

	/**
	 * Hämta befattningar för specifik grupp.
	 *
	 * @param int $group_id
	 * @return array
	 */
	private function get_roles($group_id)
	{
		$sql =
			'SELECT id, name
			FROM ssg_roles
			WHERE
				NOT dummy
				AND id IN (SELECT role_id FROM ssg_roles_groups WHERE group_id = ?)
			ORDER BY sorting ASC';
		
		return $this->CI->db->query($sql, $group_id)->result();
	}

	/**
	 * Hämta befattningar som INTE är kopplade till specifierad grupp.
	 *
	 * @param int $group_id
	 * @return array
	 */
	private function get_non_connected_roles($group_id)
	{
		$sql =
			'SELECT id, name
			FROM ssg_roles
			WHERE
				NOT dummy
				AND id NOT IN (SELECT role_id FROM ssg_roles_groups WHERE group_id = ?)
			ORDER BY sorting ASC';
		
		return $this->CI->db->query($sql, $group_id)->result();
	}

	public function get_code()
	{
		return 'grouproles';
	}

	public function get_title()
	{
		return 'Gruppbefattningar';
	}

	public function get_permissions_needed()
	{
		return array('s0', 's1', 'grpchef');
	}
}
?>