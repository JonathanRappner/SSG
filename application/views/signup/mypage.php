<?php
/** 
 * Vy för Min statistik-sidan.
*/
defined('BASEPATH') OR exit('No direct script access allowed');

//variabler
$this->current_page = 'mypage';
$member_has_signups = isset($stats->attendance_total);
$link_prefix = base_url("signup/mypage/$loaded_member->id/");
$scroll_to_id = 'wrapper_signups';
$members = $this->db->query('SELECT id, name FROM ssg_members ORDER BY name ASC')->result();

//--Variabler till js--

//närvaro total
$attendance_total = new stdClass;
$attendance_total->labels = array(); //skapa tomma arrays och fyll på senare om användare har > 0 anmälningar
$attendance_total->counts = array();
$attendance_total->colors = array();

//närvaro kvartal
$attendance_quarter = new stdClass;
$attendance_quarter->labels = array();
$attendance_quarter->counts = array();
$attendance_quarter->colors = array();

//event_types
$event_types = new stdClass;
$event_types->labels = array();
$event_types->counts = array();
$event_types->colors = array();

//deadline
$deadline = new stdClass;
$deadline->labels = array('Före deadline', 'Efter deadline'); //alltid samma labels, i samma ordning
$deadline->counts = array();
$deadline->colors = array('#28a745', '#fc302b');

//grupper
$groups = new stdClass;
$groups->labels = array();
$groups->counts = array();

//roller
$roles = new stdClass;
$roles->labels = array();
$roles->counts = array();
$roles->colors = array();


//fyll i data till js, bara om användare har > 0 anmälningar
if($member_has_signups)
{
	//närvaro total
	foreach($stats->attendance_total as $att)
	{
		$attendance_total->labels[] = $att->name;
		$attendance_total->counts[] = $att->count;
		$attendance_total->colors[] = $att->color;
	}

	//närvaro kvartal
	if(!empty($stats->attendance_quarter))
		foreach($stats->attendance_quarter as $att)
		{
			$attendance_quarter->labels[] = $att->name;
			$attendance_quarter->counts[] = $att->count;
			$attendance_quarter->colors[] = $att->color;
		}

	//event_types
	foreach($stats->event_types as $att)
	{
		$event_types->labels[] = $att->title;
		$event_types->counts[] = $att->count;
		$event_types->colors[] = sprintf('#%06X', mt_rand(0, 0xFFFFFF)); //slumpad färg
	}

	//deadline
	$deadline->counts[0] = $stats->deadline->good_boy;
	$deadline->counts[1] = $stats->deadline->bad_boy;
	
	//grupper
	foreach($stats->groups as $grp)
	{
		$groups->labels[] = $grp->name;
		$groups->counts[] = $grp->count;
		$groups->colors[] = sprintf('#%06X', mt_rand(0, 0xFFFFFF));
	}
	
	//roller
	foreach($stats->roles as $role)
	{
		$roles->labels[] = $role->name;
		$roles->counts[] = $role->count;
		$roles->colors[] = sprintf('#%06X', mt_rand(0, 0xFFFFFF)); //slumpad färg
	}
}

//rättighetsgrupper-sträng
$admin_groups_arr = array();
$admin_groups = null;
foreach($loaded_member->permission_groups as $grp)
	$admin_groups_arr[] = $this->permissions->get_by_id($grp->id)->title;
$admin_groups = implode(', ', $admin_groups_arr);

//tid sedan senaste bumpning
if(isset($loaded_member->rank_date))
{
	$bump_date = strtotime($loaded_member->rank_date);
	$timespan_epoch = time() - $bump_date;
	$timespan_days = floor($timespan_epoch / (3600 * 24));
	$day_string = $timespan_days == 1
		? 'dag'
		: 'dagar';

	$bump_string = "<span title='$timespan_days $day_string sedan' data-toggle='tooltip'>$loaded_member->rank_date <i class='fas fa-question-circle'></i></span>";
}

?><!DOCTYPE html>
<html lang="sv">
<head>
	<?php $this->load->view('signup/sub-views/head');?>

	<!-- Page-specific -->
	<link rel="stylesheet" href="<?=base_url('css/signup/mypage.css');?>">
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.9/dist/css/bootstrap-select.min.css">
	<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.3/Chart.bundle.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.9/dist/js/bootstrap-select.min.js"></script>
	<script src="<?=base_url('js/signup/clickable_table.js');?>"></script>
	<script src="<?=base_url('js/signup/mypage.js');?>"></script>

	<title>Min sida</title>

	<?php
		//js-variabler
		echo 
			'<script>
				var member_id = '. $loaded_member->id .';
				var attendance_total = '. json_encode($attendance_total) .';
				var attendance_quarter = '. json_encode($attendance_quarter) .';
				var event_types = '. json_encode($event_types) .';
				var deadline = '. json_encode($deadline) .';
				var groups = '. json_encode($groups) .';
				var roles = '. json_encode($roles) .';
			</script>';
	?>

</head>
<body>

<!-- Topp -->
<?php $this->load->view('signup/sub-views/top');?>

<div id="wrapper" class="container">

	<!-- Global Alerts -->
	<?php $this->load->view('site/sub-views/global_alerts', array('global_alerts' => $global_alerts))?>

	<div class="row">

		<div class="col-lg">
			<h1>
				Min sida
				<small class="text-muted"><?=$loaded_member->name;?></small>
			</h1>
		</div>

		<?php if($this->permissions->has_permissions(array('s0', 's1', 'grpchef'))):?>
		<!-- Se annan medlem -->
		<div class="wrapper_member_select col-lg text-lg-right">
			<label for="member_select" class="font-weight-bold" data-toggle="tooltip" title="Endast administratörer kan se andra medlemmars sidor.">Välj medlem <i class="fas fa-question-circle"></i>:</label>
			<select id="member_select" class="selectpicker ml-2 text-dark" data-live-search="true">
				<?php foreach($members as $member):?>
					<option value="<?=$member->id?>" <?=$member->id == $loaded_member->id ? 'selected' : null?>><?=$member->name?></option>
				<?php endforeach;?>
			</select>
		</div>
		<?php endif;?>

	</div>

	<!-- Medlemsinfo -->
	<div id="wrapper_info" class="row">
		<dl class="col">
			<dt>Namn:</dt>
			<dd><?=isset($loaded_member->name) ? $loaded_member->name : '-'?></dd>

			<dt>Enhet:</dt>
			<dd><?=isset($loaded_member->group_name) ? group_icon($loaded_member->group_code) . $loaded_member->group_name : '-'?></dd>

			<dt>Befattning:</dt>
			<dd><?=isset($loaded_member->role_name) ? $loaded_member->role_name : '-'?></dd>

			<dt>Grad:</dt>
			<dd><?=isset($loaded_member->rank_name) ? '<img class="rank_icon" src="'. base_url("images/rank_icons/$loaded_member->rank_icon") .'" />'. $loaded_member->rank_name : '-'?></dd>

			<dt>Senast bumpad:</dt>
			<dd><?=isset($bump_string) ? $bump_string : '?'?></dd>

			<dt>Aktiv:</dt>
			<dd><?=$loaded_member->is_active ? 'Ja': 'Nej'?></dd>

			<dt>UID:</dt>
			<dd><?=isset($loaded_member->uid) ? $loaded_member->uid : '-'?></dd>

			<dt>Registreringsdatum:</dt>
			<dd><?=isset($loaded_member->registered_date) ? $loaded_member->registered_date : '-'?></dd>

			<dt>Behörighetsgrupper:</dt>
			<dd><?=count($admin_groups_arr) > 0 ? $admin_groups : 'Inga'?></dd>
		</dl>
	</div>

	<!-- Statistik-boxar -->
	<div id="wrapper_stats">
		
		<div class="row">
			<!-- Rubrik -->
			<div class="col-lg">
				<h3 class="d-inline" title="Data sedan november 2014." data-toggle="tooltip">
					Statistik
					<?php if(!$since_date):?>
						<i class="fas fa-question-circle"></i>
					<?php else:?>
						<small class="text-secondary">(sedan <?=$since_date?>)</small>
					<?php endif;?>
				</h3>
			</div>

			<div id="wrapper_since_date" class="form-group col-lg text-lg-right mt-2">
				<label for="since_date" class="font-weight-bold">Visa data sedan:</label>
				<input id="since_date" type="date" min="2014-11-01" max="<?=date('Y-m-d')?>" class="form-control ml-2" value="<?=$since_date?>">
				<button id="btn_since_date" class="btn btn-primary ml-2">Visa <i class="fas fa-search"></i></button>
				<?php if($since_date):?><button id="btn_date_reset" class="btn btn-danger ml-2">Återställ <i class="fas fa-times-circle"></i></button><?php endif;?>
			</div>
		</div>

		<?php if($member_has_signups):?>
		<div class="row">
			<!-- Anmälningar (totalt) -->
			<div class="statbox col-sm-6 col-lg-4">
				<h6>Anmälningar</h6>
				<canvas id="chart_total"></canvas>
				<dl>
					<?php for($i=0; $i < count($attendance_total->labels); $i++):?>
						<dt><?='<span style="color: '. $attendance_total->colors[$i] .';">&#9632;</span> '. $attendance_total->labels[$i];?></strong>:</dt>
						<dd><?=$attendance_total->counts[$i]?></dd>
					<?php endfor;?>
				</dl>
			</div>

			<!-- Anmälningar efter deadline -->
			<div class="statbox col-sm-6 col-lg-4">
				<h6>Anmälningar efter deadline</h6>
				<canvas id="chart_deadline"></canvas>
				<dl>
					<dt><span style='color: <?=$deadline->colors[0];?>'>&#9632;</span> Före deadline</strong>:</dt>
					<dd><?=$deadline->counts[0];?></dd>

					<dt><span style='color: <?=$deadline->colors[1];?>'>&#9632;</span> Efter deadline</strong>:</dt>
					<dd><?=$deadline->counts[1];?></dd>
				</dl>
			</div>

			<!-- Anmälningar till eventtyper -->
			<div class="statbox col-sm-6 col-lg-4">
				<h6 title="Räknar bara med positiva anmälningar till obligatoriska event." data-toggle="tooltip">
					Anmälningar till eventtyper
					<i class="fas fa-question-circle"></i>
				</h6>
				<canvas id="chart_event_types"></canvas>
				<dl>
				<?php for($i=0; $i < count($event_types->labels); $i++):?>
						<dt><?='<span style="color: '. $event_types->colors[$i] .';">&#9632;</span> '. $event_types->labels[$i];?></strong>:</dt>
						<dd><?=$event_types->counts[$i];?></dd>
					<?php endfor;?>
				</dl>
			</div>

			<!-- Anmälning till enhet -->
			<div class="statbox col-sm-6 col-lg-4">
				<h6 title="Räknar inte med &quot;Vilken som helst&quot;-anmälningar" data-toggle="tooltip">
					Anmälning till enhet
					<i class="fas fa-question-circle"></i>
				</h6>
				<canvas id="chart_groups"></canvas>
				<dl>
					<?php for($i=0; $i < count($groups->labels); $i++):?>
						<dt><?='<span style="color: '. $groups->colors[$i] .';">&#9632;</span> '. $groups->labels[$i];?></strong>:</dt>
						<dd><?=$groups->counts[$i];?></dd>
					<?php endfor;?>
				</dl>
			</div>

			<!-- Anmälning till befattning -->
			<div class="statbox col-sm-6 col-lg-4">
				<h6 title="Räknar inte med &quot;Vad som helst&quot;-anmälningar" data-toggle="tooltip">
					Anmälning till befattning
					<i class="fas fa-question-circle"></i>
				</h6>
				<canvas id="chart_roles"></canvas>
				<dl>
					<?php for($i=0; $i < count($roles->labels); $i++):?>
						<dt><?='<span style="color: '. $roles->colors[$i] .';">&#9632;</span> '. $roles->labels[$i];?></strong>:</dt>
						<dd><?=$roles->counts[$i];?></dd>
					<?php endfor;?>
				</dl>
			</div>
		</div>
		<?php else:?>
			<div class="col text-center mb-4" style="font-size: 1.4rem;">&ndash; Inga anmälningar hittades 😢 &ndash;</div>
		<?php endif;?>
		
	</div>
	
	
	<!-- Anmälningar -->
	<div id="wrapper_signups" class="table-responsive table-sm">
		<h3>Anmälningar</h3>
		<table class="table table-hover clickable">
			<thead class="table-borderless">
				<tr>
					<th scope="col">Event</th>
					<th scope="col">Datum</th>
					<th scope="col">Enhet</th>
					<th scope="col">Befattning</th>
					<th scope="col">Närvaro</th>
				</tr>
			</thead>
			<tbody>

				<?php
				//anmälnings
				$prev_group = null;
				$gray_row = false;
				foreach($stats->signups as $s)
				{
					//variabler
					$att = $this->attendance->get_type_by_id($s->attendance_id); //närvaro-objekt
					
					echo '<tr data-url="'. base_url("signup/event/$s->event_id") .'">';
					
					//event-namn
					echo "<th scope='row' class='truncate'>$s->event_title</th>";
					
					//datum
					echo "<td>$s->start_date</td>";
					
					//enhet
					echo
						"<td class='text-nowrap'>
							". group_icon($s->group_code) ."
							<span class='d-inline d-md-none'>". strtoupper($s->group_code) ."</span>
							<span class='d-none d-md-inline'>$s->group_name</span>
						</td>";
					
					//befattning
					echo isset($s->role_name_long)
						? "<td class='truncate'><abbr title='$s->role_name_long' data-toggle='tooltip'>$s->role_name</abbr></td>"
						: "<td class='truncate'>$s->role_name</td>";
					
					//närvaro
					echo "<td><span class='$att->class'>$att->text</span></td>";

					echo '</tr>';
				}

				if(count($stats->signups) <= 0)
					echo '<tr><td colspan="5" class="text-center">&ndash; Inga anmälningar &ndash;</td></tr>';
				?>
			</tbody>
		</table>

		<?php 
		if($member_has_signups)
			echo pagination($stats->page_data->page, $stats->page_data->total_signups, $stats->page_data->results_per_page, $link_prefix, $scroll_to_id);
		?>
		
	</div>

	<!-- Footer -->
	<?php $this->load->view('signup/sub-views/footer');?>

</div>

</body>
</html>