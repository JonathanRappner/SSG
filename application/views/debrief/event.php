<?php

/** 
 * Vy för alla gruppers debrief från ett event.
 */
defined('BASEPATH') or exit('No direct script access allowed');

// Variabler
$attendance_classes = array(
	1 => 'text-signed',
	2 => 'text-jip',
	3 => 'text-qip',
	4 => 'text-noshow',
	5 => 'text-notsigned',
	6 => 'text-awol',
);


?>
<!DOCTYPE html>
<html lang="sv">

<head>
	<?php $this->load->view('debrief/sub-views/head') ?>

	<link rel="stylesheet" href="<?= base_url('css/debrief/event.css?0') ?>">
	<script src="https://cdn.jsdelivr.net/npm/lodash@4.17.21/lodash.min.js"></script>
	<script src="<?= base_url('js/debrief/event.js?0') ?>"></script>

	<title><?= $event->title ?></title>

	<script>
		// sätt initial state-variabel
		let state = <?= $init_state ?>;
		const member_id = <?= $member_id ?>;
		const attendance_classes = <?= json_encode($attendance_classes) ?>;
	</script>

</head>

<body>

	<!-- Top -->
	<?php $this->load->view('debrief/sub-views/top') ?>

	<!-- Huvud-wrapper -->
	<div id="wrapper" class="container">

		<!-- Breadcrumbs -->
		<div class="row">
			<div class="col">
				<nav aria-label="breadcrumb">
					<ol class="breadcrumb">
						<li class="breadcrumb-item"><a href="<?= base_url('debrief') ?>">Debriefs</a></li>
						<li class="breadcrumb-item active" aria-current="page"><?= $event->title ?></li>
					</ol>
				</nav>
			</div>
		</div>

		<!-- Rubrik + Alert + Skriv debrief-knapp -->
		<div class="row">
			<div class="col mb-3">

				<h2 class="mb-3">Debrief - <?= $event->title ?></h2>

				<!-- Ingen anmälan -->
				<div id="alert_no_signup" class="alert alert-warning d-none">Du har inte anmält dig till detta event och kan inte skriva en debrief.</div>

				<!-- Negativ anmälan -->
				<div id="alert_negative_signup" class="alert alert-warning d-none">Du är anmäld som <span></span> till detta event och kan inte skriva en debrief.</div>

				<!-- Knappar -->
				<div class="d-flex justify-content-between">
					<!-- Ny/Redigera debrief-knapp -->
					<a href="<?= base_url('debrief/form/' . $event->id) ?>" id="btn_form" class="btn d-none"></a>

					<!-- Teknikstrul-summering -->
					<a href="<?= base_url('debrief/tech/' . $event->id) ?>" id="btn_tech" class="btn btn-info">Teknikstrul-summering <i class="fas fa-tools"></i></a>
				</div>

			</div>
		</div>

		<!-- Sammanfattning -->
		<div class="row">

			<!-- Debriefs skrivna -->
			<div class="col-6 pb-3">
				<div class="card shadow-sm">
					<div class="card-body text-center">
						<h1 id="value_total_debriefs"></h1>
						<span>Debriefs skrivna</span>
					</div>
				</div>
			</div>

			<!-- Genomsnittsbetyg -->
			<div class="col-6 pb-3">
				<div class="card shadow-sm">
					<div class="card-body text-center">
						<h1 id="value_average_score"></h1>
						<span>Genomsnittsbetyg</span>
					</div>
				</div>
			</div>

		</div>

		<!-- Grupp -->
		<div class="row">
			<?php foreach ($groups as $grp) : ?>

				<div id="grp_card_<?= $grp->code ?>" class="grp_card col-md-4 py-2 d-flex align-items-stretch">

					<div class="card shadow-sm w-100">
						<a class="card-link" href="<?= base_url("debrief/group/{$event->id}/{$grp->id}") ?>">
							<div class="card-body h-100">

								<!-- Grupp-card titel -->
								<h5 class="card-title">
									<?= group_icon($grp->code, $grp->name, true) ?>
									<?= $grp->name ?>
								</h5>

								<!-- Grupp-card text -->
								<div class="card-text grp_has_signups">

									<div class="row text-center mb-3">
										<h1 class="avg_score col-12">-</h1>
										<small class="col-12">Genomsnittsbetyg</small>
									</div>

									<!-- Betyg från gruppens medlemmar -->
									<div class="member_scores pl-3 mb-0"></div>

								</div>

								<div class="card-text grp_no_signups h-75 d-flex align-items-center">
									<span>Ingen från denna grupp har anmält sig till eventet.</span>
								</div>

							</div>
						</a>
					</div>

				</div>

			<?php endforeach; ?>
		</div>

	</div>

	<!-- Footer -->
	<?php $this->load->view('debrief/sub-views/footer') ?>

</body>

</html>