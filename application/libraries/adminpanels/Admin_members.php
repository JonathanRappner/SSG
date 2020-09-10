<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Hanterar medlemmar
 */
class Admin_members implements Adminpanel
{
	protected $CI;
	private
		$results_per_page = 30, //medlemslistan i huvudvyn
		$page,
		$total_members,
		$members,
		$groups,
		$group_id,
		$group,
		$ranks,
		$loaded_member,
		$promotions,
		$promotion_id;

	public function __construct()
	{
		// Assign the CodeIgniter super-object
		$this->CI =& get_instance();
	}

	/**
	 * Ladda data beroende på vyn.
	 */
	public function main($var1, $var2, $var3)
	{
		//sätt view
		$this->view = $var1 == null
			? 'main'
			: $var1;

		if($this->view == 'main') //huvud-vy
		{
			assert($var2 == null || is_numeric($var2), "Inkorrekt sidnummer: $var2");
			$this->page = $var2; //sida för medlemstabellen
			$this->total_members = $this->CI->db->query('SELECT COUNT(*) AS count FROM ssg_members WHERE group_id IS NULL')->row()->count;
			$this->members = $this->get_orphan_members($this->page, $this->results_per_page);
			$this->groups = $this->get_active_groups();
		}
		else if($this->view == 'group') //grupp-vy
		{
			assert(is_numeric($var2), "Inkorrekt grupp-id: $var2");
			$this->group_id = $var2;
			$this->group = $this->get_group($this->group_id);
			$this->members = $this->get_group_members($this->group_id);
		}
		else if($this->view == 'member') //medlems-vy
		{
			assert(is_numeric($var2), "Inkorrekt medlems-id: $var2");
			$this->ranks = $this->CI->db->query('SELECT id, name FROM ssg_ranks WHERE NOT obsolete ORDER BY sorting ASC')->result();
			$this->member_id = $var2;
			$this->loaded_member = $this->CI->member->get_member_data($this->member_id);
			$this->promotions = $this->get_promotions($this->member_id);
		}
		else if($this->view == 'delete_promotion_confirm') //ta bort bumpning, bekräftan
		{
			assert(isset($var2) && is_numeric($var2), "Inkorrekt bumpnings-id: $var2");
			$this->promotion_id = $var2;
		}
		else if($this->view == 'delete_promotion') //ta bort bumpning
		{
			$promotion_id = $var2;
			assert(isset($promotion_id) && is_numeric($promotion_id), "Inkorrekt bumpnings-id: $promotion_id");
			$member_id = $this->CI->db->query('SELECT member_id FROM ssg_promotions WHERE id = ?', $promotion_id)->row()->member_id;
			$this->delete_promotion($promotion_id, $member_id);

			//success
			$this->CI->alerts->add_alert('success', 'Bumpningen togs bort utan problem.');
			redirect('signup/admin/members/member/'. $member_id);
		}
		else if($this->view == 'add_promotion') //lägg till bumpning
		{
			//variabler
			$vars = (object)$this->CI->input->post();

			//execute
			$this->add_promotion($vars);
			
			//success
			$this->CI->alerts->add_alert('success', 'Bumpningen lades till utan problem.');
			redirect('signup/admin/members/member/'. $vars->member_id);
		}
		else if($this->view == 'update_member') //uppdatera medlemsdata
		{
			//variabler
			$vars = (object)$this->CI->input->post();

			//execute
			$this->update_member($vars);
			
			//success
			$this->CI->alerts->add_alert('success', 'Ändringarna sparades utan problem.');
			if($vars->group_id > 0)
				redirect('signup/admin/members/group/'. $vars->group_id);
			else
			redirect('signup/admin/members');
		}
	}
	
	/**
	 * Skriv ut vyer.
	 */
	public function view()
	{
		echo '<div id="wrapper_members">';

		if($this->view == 'main') //huvud-vy
			$this->view_main($this->groups, $this->members, $this->page, $this->total_members);
		else if($this->view == 'group') //grupp-vy
			$this->view_group($this->group, $this->members);
		else if($this->view == 'member') //medlems-vy
			$this->view_member($this->loaded_member, $this->promotions);
		else if($this->view == 'delete_promotion_confirm') //ta bort bumpning, bekräftan
			$this->view_delete_promotion_confirm($this->promotion_id);
		else
			echo '<p>Inkorrekt url.</p>';
		
		echo '</div>';
	}

	/**
	 * Listar grupper och samtliga medlemmar.
	 *
	 * @param array $groups	Till grupp-vy-länkarna.
	 * @param array $members Till medlemstabellen.
	 * @param int $page Sida för medlemstabellen.
	 * @param int $total_members Totala antalet medlemmar i tabellen.
	 * @return void
	 */
	private function view_main($groups, $members, $page, $total_members)
	{
		//variabler
		$prev_group = null;

		//gruppikonerna
		echo '<h5 class="mt-4">Gruppöversikt</h5>';
		echo '<div class="row">';
		foreach($groups as $grp)
		{
			echo '<a class="group_box col-6 col-sm-4 col-md-3" href="'. base_url('signup/admin/members/group/'. $grp->id) .'">';
			echo '<img class="group_icon" src="'. base_url('images/group_icons/'. $grp->code .'.png') .'" />';
			echo "<h6>$grp->name</h6>";
			echo '</a>';
		}
		echo '</div>';

		//medlemstabell
		echo '<hr>';
		echo '<h5 class="mt-4">Medlemmar utan grupp</h5>';
		echo '<div id="wrapper_member_table" class="table-responsive table-sm">';
		echo '<table class="table table-hover clickable">';
			echo '<thead class="table-borderless">';
				echo '<tr>';
					echo '<th scope="col">Namn</th>';
					echo '<th scope="col">Befattning</th>';
					echo '<th scope="col">Min Sida</th>';
				echo '</tr>';
			echo '</thead>';
			echo '<tbody>';
				if(count($members) > 0)
					foreach($members as $member)
					{
						echo '<tr data-url="'. base_url('signup/admin/members/member/'. $member->id) .'">';
						
							//nick
							echo "<td scope='row' class='font-weight-bold'>$member->name</td>";

							//befattning
							echo '<td>'. (isset($member->role_name) ? $member->role_name : '-') .'</td>';

							//Min sida-länk
							echo '<td class="btn_manage">';
								echo '<a class="btn btn-primary" href="'. base_url('signup/mypage/'. $member->id) .'"><i class="fas fa-search"></i></a>';
							echo '</td>';
						echo '</tr>';
					}
				else
					echo '<tr><td colspan="3" class="text-center">&ndash; Inga medlemmar &ndash;</td></tr>';
			echo '</tbody>';
			echo '</table>';

			//pagination
			echo pagination($page, $total_members, $this->results_per_page, base_url('signup/admin/members/main/'), 'wrapper_member_table');

		echo '</div>';
	}

	/**
	 * Grupp-vy
	 * Översikt av gruppens medlemmar.
	 *
	 * @param object $group Grupp-data.
	 * @param array $members Gruppens medlemmar.
	 * @return void
	 */
	private function view_group($group, $members)
	{
		//breadcrumbs
		echo '<nav aria-label="breadcrumb"><ol class="breadcrumb">';
			echo '<li class="breadcrumb-item"><a href="'. base_url('signup/admin/members') .'">Hem</a></li>';
			echo '<li class="breadcrumb-item active" aria-current="page">'. $group->name .'</li>';
		echo '</ol></nav>';

		//ikon & rubrik
		echo '<h4>';
			echo '<img class="group_heading_icon" src="'. base_url('images/group_icons/'. $group->code .'_32.png') .'" />';
			echo $group->name;
		echo '</h4>';
		
		//medlemstabellen
		echo '<div id="wrapper_member_table" class="table-responsive table-sm">';
		echo '<table class="table table-hover clickable">';
			echo '<thead class="table-borderless">';
				echo '<tr>';
					echo '<th scope="col">Namn</th>';
					echo '<th scope="col">Befattning</th>';
					echo '<th scope="col" title="Andel av anmälningar som varit: Ja, JIP eller QIP under det senaste kvartalet." data-toggle="tooltip">Närvaro <i class="fas fa-question-circle"></i></th>';
					echo '<th scope="col" title="Senast bumpad. Hovra över datum för att se tid sedan bumpning i dagar." data-toggle="tooltip">Bumpad <i class="fas fa-question-circle"></i></th>';
					echo '<th scope="col" title="Nej = Supporter" data-toggle="tooltip">Aktiv <i class="fas fa-question-circle"></i></th>';
				echo '</tr>';
			echo '</thead><tbody>';
				if(count($members) > 0)
					foreach($members as $member)
					{
						//ny grupp?
						echo '<tr data-url="'. base_url('signup/admin/members/member/'. $member->id) .'">';
						
							//nick
							echo '<td scope="row">';
								echo "<strong>$member->name</strong>";
								echo isset($member->rank_name)
									? rank_icon($member->rank_icon, $member->rank_name)
									: null;
							echo '</td>';

							//befattning
							echo '<td>'. (isset($member->role_name) ? $member->role_name : '-') .'</td>';

							//närvaro
							echo '<td class="font-weight-bold text-'. ($member->attendance > 50 ? 'success': 'danger') .'">'. $member->attendance .'%</td>';

							//bumpad
							echo isset($member->rank_date_days_ago) ? '<td title="'. $member->rank_date_days_ago .' dagar sedan" data-toggle="tooltip">' : '<td>';
								echo isset($member->rank_date) ? $member->rank_date : '?';
							echo '</td>';

							//aktiv
							echo '<td>'. ($member->is_active ? '<strong class="text-success">Ja</strong>' : '<strong class="text-danger">Nej</strong>') .'</td>';
						
						echo '</tr>';
					}
				else
					echo '<tr><td colspan="5" class="text-center">&ndash; Inga medlemmar &ndash;</td></tr>';
			echo '</tbody></table>';

		echo '</div>';
	}

	/**
	 * Medlems-vy.
	 *
	 * @param object $member
	 * @param array $promotions
	 * @return void
	 */
	private function view_member($member, $promotions)
	{
		//variabler
		//grader options
		$rank_options_string = null;
		foreach($this->ranks as $rank)
			$rank_options_string .= "<option value='$rank->id'>$rank->name</option>";

		//grupper options
		$group_options_string = '<option value="-1">&ndash; Ingen grupp &ndash;</option>';
		$groups = $this->get_active_groups();
		foreach($groups as $group)
			$group_options_string .= "<option value='$group->id' ". ($group->id == $this->loaded_member->group_id ? 'selected' : null) .">$group->name</option>";
		
		//befattningar options
		$roles_options_string = '<option value="-1">&ndash; Ingen befattning &ndash;</option>';
		$roles = $this->CI->db->query('SELECT id, name FROM ssg_roles WHERE NOT dummy ORDER BY sorting ASC')->result();
		foreach($roles as $role)
			$roles_options_string .= "<option value='$role->id' ". ($role->id == $this->loaded_member->role_id ? 'selected' : null) .">$role->name</option>";


		//breadcrumbs
		echo '<nav aria-label="breadcrumb"><ol class="breadcrumb">';
			echo '<li class="breadcrumb-item"><a href="'. base_url('signup/admin/members') .'">Hem</a></li>';
			echo isset($member->group_id) ? '<li class="breadcrumb-item"><a href="'. base_url('signup/admin/members/group/'. $member->group_id) .'">'. $member->group_name .'</a></li>' : null;
			echo '<li class="breadcrumb-item active" aria-current="page">'. $member->name .'</li>';
		echo '</ol></nav>';

		//namn-rubrik
		echo '<h4>';
			echo "$member->name";
			echo isset($member->rank_name)
				? rank_icon($member->rank_icon, $member->rank_name, true)
				: null;
		echo '</h4>';

		//min sida-länk
		echo '<a href="'. base_url('signup/mypage/'. $member->id) .'" class="btn btn-primary" style="font-size: 1.4rem;">'. $member->name .'s &quot;Min sida&quot; &raquo;</a>';

		echo '<hr>';

		//bumpningshistorik
		echo '<h5>Bumpningar</h5>';
		echo '<div class="row">';
			echo '<div id="wrapper_promotions_table" class="table-responsive table-sm col-lg-8">';
				echo '<table class="table table-hover">';
					echo '<thead class="table-borderless">';
						echo '<tr>';
							echo '<th scope="col">Grad</th>';
							echo '<th scope="col">Datum</th>';
							echo '<th scope="col">Ta bort</th>';
						echo '</tr>';
					echo '</thead><tbody>';
						if(count($promotions) > 0)
							foreach($promotions as $pro)
							{
								echo '<tr>';
								
									//grad-namn
									echo '<td class="font-weight-bold" scope="row">';
										echo $pro->name;
										echo rank_icon($pro->icon, $pro->name);
									echo '</td>';

									//datum
									echo '<td>';
										echo isset($pro->date) ? "<span title='$pro->days_ago dagar sen' data-toggle='tooltip'>$pro->date</span>" : '?';
									echo '</td>';

									//ta bort
									echo '<td class="btn_manage">';
										echo '<a href="'. base_url("signup/admin/members/delete_promotion_confirm/$pro->id") .'" class="btn btn-danger" title="Ta bort">';
											echo '<i class="far fa-trash-alt"></i>';
										echo '</a>';
									echo '</td>';

								echo '</tr>';
							}
						else
							echo '<tr><td colspan="3" class="text-center">&ndash; Inga bumpningar &ndash;</td></tr>';
					echo '</tbody>';
				echo '</table>';
			echo '</div>'; //end #wrapper_promotions_table
		echo '</div>'; //end div.row


		echo '<hr>';


		//lägg till bumpning
		echo '<h5>Lägg till bumpning</h5>';
		echo '<form action="'. base_url('signup/admin/members/add_promotion/') .'" method="post">';
		echo '<input type="hidden" name="member_id" value="'. $this->loaded_member->id .'">';
			echo '<div class="row">';
				
				//grad
				echo '<div class="form-group col-md col-lg-4">';
					echo '<label for="rank_id">Grad</label>';
					echo '<select class="form-control" id="rank_id" name="rank_id">'. $rank_options_string .'</select>';
				echo '</div>';

				//datum
				echo '<div class="form-group col-md col-lg-4">';
					echo '<label for="rank_date" title="Får vara tom." data-toggle="tooltip">Datum <i class="fas fa-question-circle"></i></label>';
					echo '<input class="form-control" id="rank_date" name="rank_date" type="date" value="'. date('Y-m-d') .'">';
				echo '</div>';

				//submit
				echo '<div class="col-12"><button type="submit" class="btn btn-success">Lägg till <i class="fas fa-plus"></i></button></div>';

			echo '</div>'; //end div.row
		echo '</form>';


		echo '<hr>';


		// formulär
		if($this->CI->permissions->has_permissions(array('s0', 's1', 'grpchef')))
		{
			echo '<h5>Medlemsdata</h5>';
			echo '<form action="'. base_url('signup/admin/members/update_member/') .'" method="post">';
			echo '<input type="hidden" name="member_id" value="'. $this->loaded_member->id .'">';
				
				echo '<div class="col-md-6 col-lg-4">';
					
					//Grupp
					echo '<div class="form-group row">';
						echo '<label for="group">Grupp</label>';
						echo '<select class="form-control" id="group" name="group_id">'. $group_options_string .'</select>';
					echo '</div>';
					
					//Befattning
					echo '<div class="form-group row">';
						echo '<label for="role">Befattning</label>';
						echo '<select class="form-control" id="role" name="role_id">'. $roles_options_string .'</select>';
					echo '</div>';

					//UID
					echo '<div class="form-group row">';
						echo '<label for="uid">UID</label>';
						echo '<input type="text" id="uid" name="uid" value="'. $this->loaded_member->uid .'" class="form-control">';
					echo '</div>';

					//Aktiv
					echo '<div class="form-group form-check row">';
						echo '<input class="form-check-input" type="checkbox" value="1" '. ($this->loaded_member->is_active ? 'checked' : null) .' id="active" name="active">';
						echo '<label class="form-check-label" for="active" title="Inaktiv medlem = supporter. Aktiva medlemmar får oanmäld frånvaro på obligatoriska events som de inte anmält sig till." data-toggle="tooltip">';
							echo 'Aktiv <i class="fas fa-question-circle"></i>';
						echo '</label>';
					echo '</div>';

				echo '</div>'; //end div.col

				//submit
				echo '<button type="submit" class="btn btn-success">Spara <i class="fas fa-save"></i></button>';

			echo '</form>';
		}
	}

	/**
	 * Vy för bekräftning av bump-bortagelser.
	 * "Är du säker du vill ta bort bumpningen?"
	 *
	 * @param int $promotion_id
	 * @return void
	 */
	private function view_delete_promotion_confirm($promotion_id)
	{
		//variabler
		$member_id = $this->CI->db->query('SELECT member_id FROM ssg_promotions WHERE id = ?', $promotion_id)->row()->member_id;

		echo '<div class="row text-center">';
			echo '<h5 class="col">Är du säker på att du vill ta bort bumpningen?</h5>';
		echo '</div>';

		echo '<div class="row text-center mt-2">';
			echo '<div class="col">';
				echo '<a href="'. base_url('signup/admin/members/delete_promotion/'. $promotion_id) .'" class="btn btn-success mr-2">Ja</a>';
				echo '<a href="'. base_url('signup/admin/members/member/'. $member_id) .'" class="btn btn-danger">Nej</a>';
			echo '</div>';
		echo '</div>';
	}

	/**
	 * Hämta alla aktiva, icke-dummy-grupper.
	 *
	 * @return array
	 */
	private function get_active_groups()
	{
		//varibaler
		$groups = array();

		$sql =
			'SELECT id, name, code
			FROM ssg_groups
			WHERE
				NOT dummy
				AND active
			ORDER BY
				active DESC,
				sorting';
		$query = $this->CI->db->query($sql);
		foreach($query->result() as $row)
			$groups[] = $row;

		return $groups;
	}

	/**
	 * Hämta gruppinfo.
	 *
	 * @param int $group_id
	 * @return object
	 */
	private function get_group($group_id)
	{
		//varibaler
		$group = new stdClass;

		$sql =
			'SELECT id, name, code
			FROM ssg_groups
			WHERE id = ?
			ORDER BY
				active DESC,
				sorting';
		$query = $this->CI->db->query($sql, $group_id);
		return $query->row();
	}

	/**
	 * Hämta medlemmar i en specifik grupp.
	 *
	 * @param int $group_id
	 * @return array
	 */
	private function get_group_members($group_id)
	{
		//varibaler
		$members = array();
		
		//medlem
		$sql =
			'SELECT
				ssg_members.id, ssg_members.name, is_active,
				roles.name AS role_name
			FROM ssg_members
			LEFT JOIN ssg_roles roles
				ON ssg_members.role_id = roles.id
			WHERE group_id = ?
			ORDER BY
				is_active DESC,
				roles.sorting ASC,
				name';
		$query = $this->CI->db->query($sql, $group_id);
		foreach($query->result() as $row)
			$members[] = $row;

		//ranks
		foreach($members as $member)
		{
			$sql =
				'SELECT
					proms.id, name, icon, date,
					DATEDIFF(NOW(), proms.date) AS days_ago
				FROM ssg_promotions proms
				INNER JOIN ssg_ranks ranks
					ON proms.rank_id = ranks.id
				WHERE proms.member_id = ?
				ORDER BY date DESC
				LIMIT 1';
			$query = $this->CI->db->query($sql, $member->id);
			foreach($query->result() as $row)
			{
				$member->rank_id = $row->id;
				$member->rank_name = $row->name;
				$member->rank_icon = $row->icon;
				$member->rank_date = $row->date;
				$member->rank_date_days_ago = $row->days_ago;
			}
		}

		//närvaro
		foreach($members as $member)
		{
			//variabler
			$attendance = array();
			$positive = 0; //antal positiva anmälningar
			$negative = 0; //antal negativa anmälningar

			$sql =
				'SELECT 
					attendance-0 AS id,
					attendance AS name,
					COUNT(attendance) AS count
				FROM ssg_signups
				INNER JOIN ssg_events events
					ON ssg_signups.event_id = events.id
				INNER JOIN ssg_event_types event_types
					ON events.type_id = event_types.id
				WHERE
					event_types.obligatory
					AND member_id = ?
					AND events.start_datetime >= DATE_SUB(NOW(), INTERVAL 3 MONTH) #senaste kvartalet
				GROUP BY attendance';
			$query = $this->CI->db->query($sql, $member->id);
			foreach($query->result() as $row)
			{
				if($row->id <= 3) //Ja, JIP eller QIP == true
					$positive += $row->count;
				else //NOSHOW eller Oanmäld frånvaro
					$negative += $row->count;
			}

			//räkna ut andel positiva anmälningar i procent
			$total = $positive + $negative;
			$member->attendance = $total > 0
				? floor(($positive / $total) * 100)
				: 0;
		}

		return $members;
	}

	/**
	 * Hämta medlemmar utan grupp.
	 *
	 * @param int $page
	 * @param int $results_per_page
	 * @return array
	 */
	private function get_orphan_members($page, $results_per_page)
	{
		//varibaler
		$members = array();
		
		$sql =
			'SELECT
				ssg_members.id, ssg_members.name, is_active,
				ssg_roles.name AS role_name
			FROM ssg_members
			LEFT JOIN ssg_roles
				ON ssg_members.role_id = ssg_roles.id
			WHERE group_id IS NULL
			ORDER BY name ASC
			LIMIT ?, ?';
		$query = $this->CI->db->query($sql, array($page * $results_per_page, $results_per_page));
		foreach($query->result() as $row)
			$members[] = $row;

		return $members;
	}

	/**
	 * Hämta alla befodringar (gradändringar)
	 *
	 * @param int $member_id
	 * @return array
	 */
	private function get_promotions($member_id)
	{
		//varibaler
		$promotions = array();

		$sql =
			'SELECT
				ssg_promotions.id, date, name, icon,
				DATEDIFF(NOW(), ssg_promotions.date) AS days_ago
			FROM ssg_promotions
			INNER JOIN ssg_ranks
				ON ssg_promotions.rank_id = ssg_ranks.id
			WHERE member_id = ?
			ORDER BY date DESC';
		$query = $this->CI->db->query($sql, $member_id);
		foreach($query->result() as $row)
			$promotions[] = $row;

		return $promotions;
	}
	
	/**
	 * Ta bort bumpning
	 *
	 * @param int $promotion_id
	 * @param int $member_id Används i redirect:en på slutet.
	 * @return void
	 */
	private function delete_promotion($promotion_id, $member_id)
	{
		$sql =
			'DELETE FROM ssg_promotions
			WHERE id = ?';
		$query = $this->CI->db->query($sql, $promotion_id);

		//uppdatera forum-grad
		$this->update_forum_rank($member_id);
	}

	/**
	 * Lägger till bumpning
	 *
	 * @param object $vars POST-variablerna
	 * @return void
	 */
	private function add_promotion($vars)
	{
		//tom sträng till null
		$vars->rank_date = empty($vars->rank_date) ? null : $vars->rank_date;

		$data = array(
			'member_id' => $vars->member_id,
			'date' => $vars->rank_date,
			'rank_id' => $vars->rank_id
		);
		$query = $this->CI->db->insert('ssg_promotions', $data);

		//uppdatera forum-grad
		$this->update_forum_rank($vars->member_id);
	}

	/**
	 * Uppdaterar forum-grad enligt medlemmens senaste bumbning.
	 *
	 * @param int $member_id
	 * @return void
	 */
	private function update_forum_rank($member_id)
	{
		//hämta senaste bumpningsgrad
		$query = $this->CI->db->query('SELECT rank_id FROM ssg_promotions WHERE member_id = ? ORDER BY date DESC LIMIT 1', $member_id);
		if(!$query->row()) //avbryt om inga bumpningar finns kvar
			return;
		$rank_id = $query->row()->rank_id;

		//hämta medlemmens phpbb_user_id
		$query = $this->CI->db->query('SELECT phpbb_user_id FROM ssg_members WHERE id = ?', $member_id);
		if(!$query->row()) //avbryt om medlemmen inte finns i forumet
			return;
		$phpbb_user_id = $query->row()->phpbb_user_id;

		//sätt forum-grad
		$this->CI->db->where('user_id', $phpbb_user_id);
		$this->CI->db->update('phpbb_users', array('user_rank'=>$rank_id));
	}

	/**
	 * Sparar ändringar på medlem.
	 *
	 * @param object $vars POST-variablerna
	 * @return void
	 */
	private function update_member($vars)
	{
		//--ssg_member--
		$data = array(
			'group_id' => $vars->group_id > 0 ? $vars->group_id : null,
			'role_id' => $vars->role_id > 0 ? $vars->role_id : null,
			'uid' => $vars->uid,
			'is_active' => $vars->active,
		);
		$this->CI->db->where('id', $vars->member_id);
		$this->CI->db->update('ssg_members', $data);

		//--ssg_permission_groups_members--
		//bara super och s0 får ändra rättigheter
		if(!$this->CI->permissions->has_permissions('s0'))
			return;

		//ta bort gamla rättigheter
		$this->CI->db->delete('ssg_permission_groups_members', array('member_id'=>$vars->member_id));

		//lägg till rättigheter
		if(!empty($vars->permission))
			foreach($vars->permission as $perm_id)
			{
				$data = array('member_id'=>$vars->member_id, 'permission_group_id'=>$perm_id);
				$this->CI->db->insert('ssg_permission_groups_members', $data);
			}
	}

	public function get_code()
	{
		return 'members';
	}

	public function get_title()
	{
		return 'Medlemmar';
	}

	public function get_permissions_needed()
	{
		//åtkomst: admins
		return array('s0', 's1', 's4', 'grpchef');
	}
}
?>