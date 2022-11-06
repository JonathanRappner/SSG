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

	<title><?= $event->title ?></title>

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

				<?php if (!$signup) : /* igen anmälan */ ?>
					<div class="alert alert-warning">Du har inte anmält dig till detta event och kan inte skriva en debrief för det.</div>
				<?php elseif ($signup->attendance_id > 3) : /* anmälan är negativ */ ?>
					<div class="alert alert-warning">Du är anmäld som <span class="<?= $attendance_classes[$signup->attendance_id] ?>"><?= $signup->attendance_name ?></span> till detta event och kan inte skriva en debrief för det.</div>
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

		<!-- Sammanfattning -->
		<div class="row">
			<div class="col pb-3">

				<div class="card shadow-sm">
					<div class="card-body">
						<h5 class="card-title">Sammanfattning</h5>
						<p class="card-text">
							<strong>Debriefs skrivna:</strong> <?= $overview->total_debriefs ?>/<?= $overview->total_signups ?><br>
							<strong>Eventets genomsnittsbetyg:</strong> <?= $overview->score_avg ?>
						</p>
					</div>
				</div>

			</div>
		</div>

		<!-- Grupp -->
		<div class="row">
			<?php foreach ($overview->groups as $grp) : ?>

				<div class="col-md-4 py-2 d-flex align-items-stretch">

					<div class="card shadow-sm w-100">
						<?php if (count($grp->signups) > 0):?>
							<a class="card-link" href="<?=base_url("debrief/group/{$event->id}/{$grp->id}")?>">
						<?php endif; ?>
						<div class="card-body">

							<!-- Grupp-card titel -->
							<h5 class="card-title">
								<?= group_icon($grp->code, $grp->name, true) ?>
								<?= $grp->name ?>
							</h5>

							<!-- Grupp-card text -->
							<div class="card-text">
								<?php if (count($grp->signups) > 0) : ?>

									<!-- Gruppsummering -->
									<p>
										<strong>Debriefs skrivna:</strong> <?= $grp->reviews_count ?>/<?= $grp->signups_count ?><br>
										<strong>Gruppens genomsnittsbetyg:</strong> <?= $grp->reviews_score_avg ?>
									</p>

									<!-- Betyg från gruppens medlemmar -->
									<strong>Betyg:</strong>
									<ul class="member_scores">
										<?php foreach ($grp->signups as $s) : ?>
											<li><strong><?= $s->member_name ?></strong>: <?= $s->score_string ?? '-' ?></li>
										<?php endforeach; ?>
									</ul>

								<?php else : ?>
									<p>Ingen från denna grupp har anmält sig till detta event.</p>
								<?php endif; ?>
							</div>

						</div>

						<?php if (count($grp->signups) > 0):?></a><?php endif; ?>
					</div>

				</div>

			<?php endforeach; ?>
		</div>

	</div>

	<!-- Footer -->
	<?php $this->load->view('debrief/sub-views/footer') ?>

</body>

</html>