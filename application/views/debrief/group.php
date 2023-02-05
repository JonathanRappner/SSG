<?php

/** 
 * Vy som för en grupps debrief över ett specifikt event.
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

	<link rel="stylesheet" href="<?= base_url('css/debrief/group.css?1') ?>">
	<script src="https://cdn.jsdelivr.net/npm/lodash@4.17.21/lodash.min.js"></script>
	<script src="<?= base_url('js/debrief/group.js?3') ?>"></script>

	<title><?= strtoupper($group->code) ?> - <?= $event->title ?></title>

	<script>
		// sätt initial state-variabel
		let state = <?= $init_state ?>;
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
						<li class="breadcrumb-item"><a href="<?= base_url("debrief/event/{$event->id}") ?>"><?= $event->title ?></a></li>
						<li class="breadcrumb-item active" aria-current="page"><?= $group->name ?></li>
					</ol>
				</nav>
			</div>
		</div>

		<!-- Rubrik + Alert + Skriv debrief-knapp -->
		<div class="row">
			<div class="col mb-3">

				<div class="mb-3">
					<h2 class="mb-0">
						Debrief - <?= $event->title ?>
					</h2>
					<h4 class="subtitle">
						<?= group_icon($group->code, $group->name, true) ?>
						<?= $group->name ?>
					</h4>
				</div>

				<?php if (!$signup) : /* igen anmälan */ ?>
					<div class="alert alert-warning">Du har inte anmält dig till detta event och kan inte skriva en debrief.</div>
				<?php elseif ($signup->attendance_id > 3) : /* anmälan är negativ */ ?>
					<div class="alert alert-warning">Du är anmäld som <span class="<?= $attendance_classes[$signup->attendance_id] ?>"><?= $signup->attendance_name ?></span> till detta event och kan inte skriva en debrief.</div>
				<?php elseif (!$debrief) : /* ingen debrief */ ?>
					<a href="<?= base_url('debrief/form/' . $event->id) ?>" class="btn btn-success">
						Skriv din debrief <i class="fas fa-chevron-right"></i>
					</a>
				<?php else : /* debrief är skriven */ ?>
					<a href="<?= base_url('debrief/form/' . $event->id) ?>" class="btn btn-primary">
						Redigera din debrief <i class="fas fa-pen"></i>
					</a>
				<?php endif; ?>

			</div>
		</div>

		<!-- Summering -->
		<div class="row">

			<!-- Medlemmars betyg -->
			<div class="col pb-3">
				<div class="card shadow-sm h-100">
					<div class="card-body">
						<div id="member_scores" class="pl-3 mb-0"></div>
					</div>
				</div>
			</div>
			
			<!-- Debriefs skrivna -->
			<div class="col pb-3">
				<div class="card shadow-sm h-100">
					<div class="card-body h-100 d-flex justify-content-center align-items-center">
						<div class="d-flex flex-column text-center">
							<h1 id="value_total_debriefs"></h1>
							<span>Debriefs skrivna</span>
						</div>
					</div>
				</div>
			</div>

			<!-- Genomsnittsbetyg -->
			<div class="col pb-3">
				<div class="card shadow-sm h-100">
					<div class="card-body h-100 d-flex justify-content-center align-items-center">
						<div class="d-flex flex-column text-center">
							<h1 id="value_average_score"></h1>
							<span>Genomsnittsbetyg</span>
						</div>
					</div>
				</div>
			</div>

		</div>

		<!-- Bra -->
		<div class="row">
			<div class="col pb-3">

				<div class="card shadow-sm">
					<div class="card-body">

						<h5 class="card-title mb-4">
							<i class="fas fa-thumbs-up"></i>
							<span class="text-success">Vad har varit bra / roligt</span>
						</h5>

						<ul id="reviews_good" class="list-group"></ul>

					</div>
				</div>

			</div>
		</div>


		<!-- Dåligt -->
		<div class="row">
			<div class="col pb-3">

				<div class="card shadow-sm">
					<div class="card-body">

						<h5 class="card-title mb-4">
							<i class="fas fa-thumbs-down"></i>
							<span class="text-danger">Vad har varit dåligt / tråkigt?</span>
						</h5>

						<ul id="reviews_bad" class="list-group"></ul>

					</div>
				</div>

			</div>
		</div>

		<!-- Bättre -->
		<div class="row">
			<div class="col pb-3">
				<div class="card shadow-sm">
					<div class="card-body">

						<h5 class="card-title mb-4">
							<i class="fas fa-yin-yang"></i>
							<span class="text-warning">Vad kan vi göra bättre?</span>
						</h5>

						<ul id="reviews_improvement" class="list-group"></ul>

					</div>
				</div>

			</div>
		</div>

		<!-- Tekniskt strul -->
		<div class="row">
			<div class="col pb-3">

				<div class="card shadow-sm">
					<div class="card-body">
						<h5 class="card-title mb-4">
							<i class="fas fa-tools"></i>
							<span class="text-info">Tekniskt strul</span>
						</h5>

						<ul id="reviews_tech" class="list-group"></ul>

					</div>
				</div>

			</div>
		</div>

		<!-- Media -->
		<div class="row">
			<div class="col pb-3">

				<div class="card shadow-sm">
					<div class="card-body">
						<h5 class="card-title mb-4">
							<i class="fas fa-film"></i>
							<span class="text-primary">Stream, screenshots och klipp</span>
						</h5>

						<ul id="reviews_media" class="list-group"></ul>

					</div>
				</div>

			</div>
		</div>

	</div>


	<!-- Footer -->
	<?php $this->load->view('debrief/sub-views/footer') ?>

</body>

</html>