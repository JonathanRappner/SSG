<?php
/** 
 * Vy som listar de senaste events och vilka du har debriefat.
*/
defined('BASEPATH') OR exit('No direct script access allowed');

// Variabler
$attendance_classes = array(
	1 => 'text-signed',
	2 => 'text-jip',
	3 => 'text-qip',
	4 => 'text-noshow',
	5 => 'text-notsigned',
	6 => 'text-awol',
);


?><!DOCTYPE html>
<html lang="sv">
<head>
	<?php $this->load->view('debrief/sub-views/head')?>

	<!-- Page-specific -->
	<!-- <script src="<xxx?=base_url('js/deadline.js')?>"></script> -->
	<!-- <link rel="stylesheet" href="<xxx?=base_url('css/signup/events.css?3')?>"> -->

	<title>SSG Debrief - <?=$event->title?></title>

</head>
<body>

<!-- Top -->
<?php $this->load->view('debrief/sub-views/top')?>

<!-- Huvud-wrapper -->
<div id="wrapper" class="container p-0">

	<div class="row">
		<div class="col">
			<!-- Rubrik -->
			<h2>Debrief - <?=$event->title?></h2>
			
			<?php if(!$signup):?>
				<div class="alert alert-warning">Du har inte anmält dig till detta event och kan inte skriva en debrief för det.</div>
			<?php elseif($signup->attendance_id > 3):?>
				<div class="alert alert-warning">Du är anmäld som <span class="<?=$attendance_classes[$signup->attendance_id]?>"><?=$signup->attendance_name?></span> till detta event och kan inte skriva en debrief för det.</div>
			<?php elseif(!$debrief):?>
				<a href="<?=base_url('debrief/form/'. $event->id)?>" class="btn btn-success">
					Skriv ny debrief <i class="fas fa-chevron-right"></i>
				</a>
			<?php else:?>
				<a href="<?=base_url('debrief/form/'. $event->id)?>" class="btn btn-primary">
					Redigera debrief <i class="fas fa-pen"></i>
				</a>
			<?php endif;?>
		</div>
	</div>

	<div class="row">
		<div class="col-12">
			<h3>Sammanfattning</h3>
			<p>?/? har skrivit sin debrief</p>
			<p>genomsnittlig poäng: ?</p>
		</div>
	</div>

	<div class="row">
		<?php foreach($overview->groups as $group):?>
			<div class="col-12">
				<h3><?=$group->code?></h3>
				<p>Debriefs: ?/<?=$group->signups_count?></p>
				<p>genomsnittlig poäng: ?</p>
			</div>
		<?php endforeach;?>
	</div>

</div>

<!-- Footer -->
<?php $this->load->view('debrief/sub-views/footer')?>

</body>
</html>