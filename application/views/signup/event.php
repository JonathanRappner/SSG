<?php
defined('BASEPATH') OR exit('No direct script access allowed');

//event-variabler
$this->current_page = 'event';
$title = "$event->title";
$date_and_time = "$event->start_date ($event->start_time - $event->end_time)";
$message_max_length = 50;
$is_admin = $this->permissions->has_permissions(array('s0', 's1', 'grpchef')); //om true = gör tabeller clickable

//signup-variabler
$this->member_not_signed = empty($signup); //har medlemmen anmält sig till detta event?
$this->preselects = $this->member_not_signed
	? $this->eventsignup->get_preselects($this->member->id)
	: null; //förvalda alternativ till anmälningsformuläret

//highlight:a jip, qip & noshow
$patterns = array('/(?<!\w)(jip)(?!\w)/i', '/(?<!\w)(qip)(?!\w)/i', '/(?<!\w)(noshow)(?!\w)/i');
$replacements = array('<span class="text-jip">JIP</span>', '<span class="text-qip">QIP</span>', '<span class="text-noshow">NOSHOW</span>');
foreach($signups as $s)
	$s->message_highlighted = preg_replace($patterns, $replacements, $s->message);

?><!DOCTYPE html>
<html lang="sv">
<head>
	<?php $this->load->view('signup/sub-views/head');?>

	<!-- Page-specific -->
	<link rel="stylesheet" href="<?=base_url('css/signup/event.css');?>">
	<link rel="stylesheet" href="<?=base_url('css/signup/event_stats.css');?>">
	<link rel="stylesheet" href="<?=base_url('css/signup/form.css');?>">
	<script src="<?=base_url('js/signup/clickable_table.js');?>"></script>
	<script src="<?=base_url('js/signup/event.js');?>"></script>

	<?php
		//visa formulär on load
		echo '<script>var show_form = '. ($show_form ? 'true' : 'false') .';</script>';
	?>

	<title><?=$title;?></title>

</head>
<body>

<div id="main_wrapper" class="container">

	<!-- Menyrad -->
	<?php $this->load->view('signup/sub-views/top');?>

	<!-- Global Alerts -->
	<?php $this->load->view('site/sub-views/global_alerts', array('global_alerts' => $global_alerts))?>

	<div id="row_event_top" class="row">
		
		<!-- Titel & knappar -->
		<div class="col-lg mb-4">
			<!-- Heading -->
			<h1>
				<?=$title;?>
				<?php if(!empty($event->author_id)): ?>
				<small class="text-muted text-nowrap"><?="av $event->author_name";?></small>
				<?php endif; ?>
			</h1>
			<h4 class="ml-2 text-muted"><?=$date_and_time;?></h4>

			<!-- Anmälningsknapp -->
			<?php if(!$event->is_old):?>
				<?php if($this->member_not_signed):?>
					<button id="btn_signup" type="button" class="btn btn-success btn_signup my-2" data-toggle="modal" data-target="#form_popup">
						Anmäl dig <i class="fas fa-arrow-circle-right"></i>
					</button>
				<?php else:?>
					<button id="btn_signup" type="button" class="btn btn-primary my-2" data-toggle="modal" data-target="#form_popup">
						Redigera anmälan <i class="fas fa-edit"></i>
					</button>
				<?php endif;?>
			<?php endif;?>

			<div><!-- newline -->
				<?php if(!empty($event->forum_link)):?>
					<!-- Läs mer -->
					<a href="<?=$event->forum_link?>" class="btn btn-primary">Läs mer <i class="fas fa-search"></i></a>
				<?php endif;?>

				<?php if(!$event->is_old):?>
					<!-- Anmälningslänk -->
					<a href="<?=base_url("signup/event/{$event->id}/showform")?>" class="btn btn-info" data-toggle="tooltip" title="Kopiera mig!">
						Anmälningslänk <i class="fas fa-link"></i>
					</a>
				<?php endif;?>
			</div>

		</div>

		<!-- Statistik -->
		<div class="col-lg">
			<?php $this->load->view('signup/sub-views/event_stats', array('stats'=>$advanced_stats, 'non_signed'=>count($non_signups), 'obligatory'=>$event->obligatory, 'is_old'=>$event->is_old));?>
		</div>

	</div>


	<!-- Anmälningar -->
	<h3 class="mt-2">Anmälningar</h3>
	<div id="wrapper_signup_table" class="table-responsive table-sm">
		<table class="table table-hover<?=$is_admin ? ' clickable' : null ?>">
			<thead class="table-borderless">
				<tr>
					<th scope="col">Namn</th>
					<th scope="col">Grupp</th>
					<th scope="col">Befattning</th>
					<th scope="col">Närvaro</th>
					<th class="column_message" scope="col">Meddelande</th>
				</tr>
			</thead>
			<tbody>

				<?php
				//Anmälningar
				$prev_group = null;
				$gray_row = false;
				foreach($signups as $s)
				{
					//variabler
					$clickable_url = base_url("signup/mypage/$s->member_id");
					$att = $this->attendance->get_type_by_id($s->attendance_id); //närvaro-objekt
					$message = mb_strlen($s->message) <= $message_max_length
						? $s->message
						: "<abbr title='$s->message' data-toggle='tooltip'>". mb_substr($s->message, 0, ($message_max_length-3)) .'...</abbr>';
					
					//ny grupp
					if($s->group_code != $prev_group)
					{
						echo '<tr class="new_group_row"'. ($is_admin ? " data-url='$clickable_url'" : null) .'>';
						$prev_group = $s->group_code;
					}
					else
						echo '<tr'. ($is_admin ? " data-url='$clickable_url'" : null) .'>';
					
					//namn
					echo
					"<th scope='row' class='truncate'>
						$s->member_name
					</th>";
					// ". (isset($s->rank_name) ? '<img class="rank_icon" src="'. base_url("images/rank_icons/$s->rank_icon") .'" title="'. $s->rank_name .'" data-toggle="tooltip" />' : null) ."
						
					//grupp med ikon
					echo
						"<td class='text-nowrap'>
							". group_icon($s->group_code) ."
							<span class='d-inline d-md-none'>". strtoupper($s->group_code) ."</span>
							<span class='d-none d-md-inline'>$s->group_name</span>
						</td>";
					
					if(isset($s->role_name_long))
						echo "<td class='truncate'><abbr title='$s->role_name_long' data-toggle='tooltip'>$s->role_name</abbr></td>";
					else
						echo "<td class='truncate'>$s->role_name</td>";
					
					
					echo "<td><span class='$att->class'>$att->text</span></td>"; //närvaro
					echo
						"<td class='truncate'>
							<abbr title='$s->message' data-toggle='tooltip'>$s->message_highlighted</abbr>
						</td>"; //message
					echo '</tr>';
				}

				if(count($signups) <= 0)
					echo '<tr><td colspan="5" class="text-center">&ndash; Inga anmälningar &ndash;</td></tr>';
				?>
			</tbody>
		</table>


		<?php if( //ej anmälda, aktiva medlemmar
				$event->obligatory
				&& !$event->is_old
				&& !empty($non_signups)
				&& $this->permissions->has_permissions(array('s0', 's1', 'grpchef'))):
		?>
			<!-- Ej anmälda, aktiva medlemmar -->
			<div id="wrapper_not_signed_table" class="table-responsive table-sm">
				<h3 class="mt-4 d-inline-block" title="Endast admins ser listan" data-toggle="tooltip">Aktiva medlemmar som inte anmält sig <i class='fas fa-question-circle'></i></h3>
				<table class="table table-hover<?=$is_admin ? ' clickable' : null ?>">
					<thead class="table-borderless">
						<tr>
							<th scope="col" class="column_non_signed_name">Namn</th>
							<th scope="col">Enhet</th>
						</tr>
					</thead>
					<tbody>
						<?php
						$prev_group = null;
						foreach($non_signups as $member):?>
							<?php
								//variabler
								$clickable_url = base_url("signup/mypage/$member->id");

								//ny tabell-rad
								if($member->group_code != $prev_group) //rad med ny grupp
								{
									echo '<tr class="new_group_row"'. ($is_admin ? " data-url='$clickable_url'" : null) .'>';
									$prev_group = $member->group_code;
								}
								else //samma grupp som förra raden
									echo '<tr'. ($is_admin ? " data-url='$clickable_url'" : null) .'>';
							?>
								<th scope="row"><?=$member->name;?></th>
								<td><?php
									echo group_icon($member->group_code);
									echo isset($member->group_name)
										? $member->group_name
										: null;
								?></td>
							</tr>
						<?php endforeach;?>
					</tbody>
				</table>
			</div>
		<?php endif;?>

	</div>

	<?php if(!$event->is_old):?>
		<!-- Modal -->
		<div class="modal fade" id="form_popup" tabindex="-1" role="dialog" aria-hidden="true">
			<div class="modal-dialog" role="document">
				<div class="modal-content">

					<div class="modal-header">
						<!-- Heading -->
						<div>
							<h5 class="modal-title"><?=$this->member_not_signed ? 'Ny anmälan' : 'Redigera anmälan';?> till</h5>
							<h4 class="modal-title"><?="$event->title";?></h4>
							<h5 class="text-muted text-nowrap"><?="($event->start_date)";?></h5>
						</div>

						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
						</button>
					</div>

					
						<div class="modal-body">
							<!-- Formulär -->
							<?php $this->load->view('signup/sub-views/form', array('event' => $event, 'signup' => $signup));?>
						</div>
				</div>
			</div>
		</div>
	<?php endif;?>

</div>


<!-- Footer -->
<?php $this->load->view('signup/sub-views/footer');?>

</body>
</html>