<?php
defined('BASEPATH') OR exit('No direct script access allowed');

//event-variabler
$this->current_page = 'event';
$title = "$event->title";
$date_and_time = "$event->start_date ($event->start_time - $event->end_time)";
setlocale(LC_ALL, 'sv_SE'); //så att strftime() ger svenska strings
$day_string = utf8_encode(strftime("%A", strtotime($event->start_date))); //måndag, tisdag, osv.
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
	<?php $this->load->view('signup/sub-views/head')?>

	<!-- Page-specific -->
	<link rel="stylesheet" href="<?=base_url('css/signup/event.css?2')?>">
	<link rel="stylesheet" href="<?=base_url('css/signup/event_stats.css?0')?>">
	<link rel="stylesheet" href="<?=base_url('css/signup/form.css')?>">
	<script src="<?=base_url('js/signup/clickable_table.js')?>"></script>
	<script src="<?=base_url('js/signup/event.js?0')?>"></script>

	<?php if(XMAS):?>
		<link rel="stylesheet" href="<?=base_url('css/holidays/xmas.css')?>">
	<?php endif;?>

	<!-- visa formulär on load -->
	<script>var show_form = <?=json_encode($show_form)?>;</script>

	<title><?=$title?></title>

</head>
<body>

<!-- Topp -->
<?php $this->load->view('signup/sub-views/top')?>

<div id="main_wrapper" class="container p-0">

	<!-- Titel & knappar + Statistik -->
	<div id="row_event_top" class="row mb-4">
		
		<!-- Titel & knappar -->
		<div id="event_info" class="col-lg mb-3 mb-lg-0">

			<div class="card border-0 shadow-sm">

				<h4 class="card-header bg-dark text-white">
					<?php if(XMAS):?><div class="snow_edge left"></div><div class="snow_pattern"></div><div class="snow_edge right"></div>❄<?php endif;?>
					Event
				</h4>

				<?php if(!empty($event->preview_image)):?>
					<!-- infobox background -->
					<style>
						#event_info div.card-body{
							background-image:
								linear-gradient(rgba(255,255,255,.5), rgba(255,255,255,.85), white 75%),
								url('<?=$event->preview_image?>');
						}
					</style>
				<?php endif?>
				<div class="card-body py-3">
					<div class="row">

						<h3 id="event_title" class="col-12 pb-4">
							<?=$title?>
							<?php if(!empty($event->author_id)):?>
								<small class="text-nowrap text-dark"><?="av $event->author_name"?></small>
							<?php endif?>
						</h3>

						<!-- Event-info -->
						<dl class="col-12 mb-0">
							<dt>Datum:</dt>
							<dd><?=$event->start_date?> (<?=$day_string?>)</dd>

							<dt>Start:</dt>
							<dd><?=$event->start_time?></dd>

							<dt>Slut:</dt>
							<dd><?=$event->end_time?></dd>
						</dl>

						<?php if(!$event->is_old):?>
							<!-- Anmälningsknapp -->
							<div class="col-12">
								<?php if($this->member_not_signed):?>
									<button id="btn_signup" type="button" class="btn btn-success btn_signup my-2" data-toggle="modal" data-target="#form_popup">
										Anmäl dig <i class="fas fa-arrow-circle-right"></i>
									</button>
								<?php else:?>
									<button id="btn_signup" type="button" class="btn btn-primary btn_edit my-2" data-toggle="modal" data-target="#form_popup">
										Redigera anmälan <i class="fas fa-edit"></i>
									</button>
								<?php endif?>
							</div>
						<?php endif?>

						<?php if(!empty($event->forum_link) || !$event->is_old):?>
							<!-- Småknappar -->
							<div class="col-12">
								<?php if(!empty($event->forum_link)):?>
									<!-- Läs mer -->
									<a href="<?=$event->forum_link?>" class="btn btn-info">Läs mer <i class="fas fa-search"></i></a>
								<?php endif?>
		
								<?php if(!$event->is_old):?>

									<!-- Anmälningslänk -->
									<button id="signup_link" class="btn btn-danger" data-link="<?=base_url("signup/event/{$event->id}/showform")?>" title="Klicka på mig för att kopiera länken." role="button" data-toggle="tooltip">
										Anmälningslänk <i class="fas fa-link"></i>
									</button>

								<?php endif?>
							</div>
						<?php endif?>
					</div><!-- end div.row -->
				</div><!-- end div.card-body -->

			</div><!-- end div.card -->

		</div>

		<!-- Statistik -->
		<div class="col-lg">
			<?php $this->load->view('signup/sub-views/event_stats', array('stats'=>$advanced_stats, 'non_signed'=>count($non_signups), 'obligatory'=>$event->obligatory, 'is_old'=>$event->is_old))?>
		</div>

	</div>

	<!-- Anmälningar -->
	<div class="wrapper_signups_table card border-0 shadow-sm">

		<h4 class="card-header bg-dark text-white">
			<?php if(XMAS):?><div class="snow_edge left"></div><div class="snow_pattern"></div><div class="snow_edge right"></div>🎁<?php endif;?>
			Anmälningar
		</h4>

		<div class="card-body table-responsive table-sm px-2 pt-1 pb-0">
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
		</div><!--end div.body-->

	</div><!--end div.wrapper_signups_table-->

	<!-- Ej anmälda -->
	<?php if( //ej anmälda, aktiva medlemmar
		$event->obligatory
		&& !$event->is_old
		&& !empty($non_signups)
		&& $this->permissions->has_permissions(array('s0', 's1', 'grpchef'))):
	?>
		<div class="wrapper_signups_table card border-0 shadow-sm mt-4">
			<h4 class="card-header bg-dark text-white">
				<?php if(XMAS):?><div class="snow_edge left"></div><div class="snow_pattern"></div><div class="snow_edge right"></div>🕯<?php endif;?>
				<span title="Endast S0, S1 och gruppchefer ser listan." data-toggle="tooltip">Aktiva medlemmar som inte anmält sig <i class='fas fa-question-circle'></i></span>
			</h4>

			<div class="card-body table-responsive table-sm px-2 pt-1 pb-0">
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
								<th scope="row"><?=$member->name?></th>
								<td><?php
									echo group_icon($member->group_code);
									echo isset($member->group_name)
										? $member->group_name
										: null;
								?></td>
							</tr>
						<?php endforeach?>
					</tbody>
				</table>
			</div><!--end div-card-body-->

		</div><!--end div.wrapper_signups_table-->
	<?php endif?>

	<?php if(!$event->is_old):?>
		<!-- Modal -->
		<div class="modal fade" id="form_popup" tabindex="-1" role="dialog" aria-hidden="true">
			<div class="modal-dialog" role="document">
				<div class="modal-content">

					<div class="modal-header">
						<!-- Heading -->
						<div>
							<h5 class="modal-title"><?=$this->member_not_signed ? 'Ny anmälan' : 'Redigera anmälan'?> till</h5>
							<h4 class="modal-title"><?="$event->title"?></h4>
							<h5 class="text-muted text-nowrap"><?="($event->start_date)"?></h5>
						</div>

						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
						</button>
					</div>

					
						<div class="modal-body">
							<!-- Formulär -->
							<?php $this->load->view('signup/sub-views/form', array('event' => $event, 'signup' => $signup))?>
						</div>
				</div>
			</div>
		</div>
	<?php endif?>

</div>


<!-- Footer -->
<?php $this->load->view('signup/sub-views/footer')?>

</body>
</html>