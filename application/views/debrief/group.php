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

	<link rel="stylesheet" href="<?= base_url('css/debrief/group.css?0') ?>">

	<title><?= strtoupper($group->code) ?> - <?= $event->title ?></title>

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

		<!-- Summering -->
		<div class="row">
			<div class="col pb-3">
				<div class="card shadow-sm">
					<div class="card-body">
						
						<h5 class="card-title mb-4">
							<i class="fas fa-clipboard"></i>
							Summering
						</h5>
						
						genomsnittspoäng<br>
						medlemmars poäng<br>
						hur många är klara?<br>
						länkar för admin att skriva debrief för andra medlemmar

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

						<ul class="list-group">
							<li class="list-group-item list-group-item-success">
								<img class="mini-avatar" src="<?= $this->member->avatar_url ?? base_url('images/unknown.png') ?>" /><strong>Smorfty</strong><br>
								Kul OP. Mycket action. Slut från mig.
							</li>
							<li class="list-group-item list-group-item-success">
								<img class="mini-avatar" src="<?= base_url('images/unknown.png') ?>" /><strong>Åsa-Nisse</strong><br>
								Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.
							</li>
							<li class="list-group-item list-group-item-success">
								<img class="mini-avatar" src="<?= base_url('images/unknown.png') ?>" /><strong>Klabbarparn</strong><br>
								A simple list group item
							</li>
							<li class="list-group-item list-group-item-success">
								<img class="mini-avatar" src="<?= base_url('images/unknown.png') ?>" /><strong>Sjökvist</strong><br>
								A simple list group item
							</li>
						</ul>

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

						<ul class="list-group">
							<li class="list-group-item list-group-item-danger">
								<img class="mini-avatar" src="<?= $this->member->avatar_url ?? base_url('images/unknown.png') ?>" /><strong>Smorfty</strong><br>
								Kul OP. Mycket action. Slut från mig.
							</li>
							<li class="list-group-item list-group-item-danger">
								<img class="mini-avatar" src="<?= base_url('images/unknown.png') ?>" /><strong>Åsa-Nisse</strong><br>
								Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.
							</li>
							<li class="list-group-item list-group-item-danger">
								<img class="mini-avatar" src="<?= base_url('images/unknown.png') ?>" /><strong>Klabbarparn</strong><br>
								A simple list group item
							</li>
							<li class="list-group-item list-group-item-danger">
								<img class="mini-avatar" src="<?= base_url('images/unknown.png') ?>" /><strong>Sjökvist</strong><br>
								A simple list group item
							</li>
						</ul>

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

						<ul class="list-group">
							<li class="list-group-item list-group-item-warning">
								<img class="mini-avatar" src="<?= $this->member->avatar_url ?? base_url('images/unknown.png') ?>" /><strong>Smorfty</strong><br>
								Kul OP. Mycket action. Slut från mig.
							</li>
							<li class="list-group-item list-group-item-warning">
								<img class="mini-avatar" src="<?= base_url('images/unknown.png') ?>" /><strong>Åsa-Nisse</strong><br>
								Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.
							</li>
							<li class="list-group-item list-group-item-warning">
								<img class="mini-avatar" src="<?= base_url('images/unknown.png') ?>" /><strong>Klabbarparn</strong><br>
								A simple list group item
							</li>
							<li class="list-group-item list-group-item-warning">
								<img class="mini-avatar" src="<?= base_url('images/unknown.png') ?>" /><strong>Sjökvist</strong><br>
								A simple list group item
							</li>
						</ul>

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

						<ul class="list-group">
							<li class="list-group-item list-group-item-info">
								<img class="mini-avatar" src="<?= $this->member->avatar_url ?? base_url('images/unknown.png') ?>" /><strong>Smorfty</strong><br>
								Kul OP. Mycket action. Slut från mig.
							</li>
							<li class="list-group-item list-group-item-info">
								<img class="mini-avatar" src="<?= base_url('images/unknown.png') ?>" /><strong>Åsa-Nisse</strong><br>
								Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.
							</li>
							<li class="list-group-item list-group-item-info">
								<img class="mini-avatar" src="<?= base_url('images/unknown.png') ?>" /><strong>Klabbarparn</strong><br>
								A simple list group item
							</li>
							<li class="list-group-item list-group-item-info">
								<img class="mini-avatar" src="<?= base_url('images/unknown.png') ?>" /><strong>Sjökvist</strong><br>
								A simple list group item
							</li>
						</ul>
					</div>
				</div>

			</div>
		</div>





	</div>


	<!-- Footer -->
	<?php $this->load->view('debrief/sub-views/footer') ?>

</body>

</html>