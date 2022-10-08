<?php

/** 
 * Vy som listar de senaste events och vilka du har debriefat.
 */
defined('BASEPATH') or exit('No direct script access allowed');


?>
<!DOCTYPE html>
<html lang="sv">

<head>
	<?php $this->load->view('debrief/sub-views/head') ?>

	<!-- Page-specific -->
	<!-- <script src="<xxx?=base_url('js/deadline.js')?>"></script> -->
	<!-- <link rel="stylesheet" href="<xxx?=base_url('css/signup/events.css?3')?>"> -->

	<title>SSG Debrief - >>>>event titel formulär<<<<< </title>

</head>

<body>

	<!-- Top -->
	<?php $this->load->view('debrief/sub-views/top') ?>

	<!-- Huvud-wrapper -->
	<div id="wrapper" class="container">

		<div class="row">
			<div class="col">

				<!-- Breadcrumbs -->
				<div class="row">
					<div class="col">
						<nav aria-label="breadcrumb">
							<ol class="breadcrumb">
								<li class="breadcrumb-item"><a href="<?= base_url('debrief') ?>">Debriefs</a></li>
								<li class="breadcrumb-item"><a href="<?= base_url("debrief/event/{$event_id}") ?>">eventtitel här</a></li>
								<li class="breadcrumb-item active" aria-current="page">Skriv debrief</li>
							</ol>
						</nav>
					</div>
				</div>

			</div>
		</div>

		<!-- Rubrik -->
		<h2>Debrief</h2>

		<?php if (!$member_id) : ?>
			<p>debrief-formulär för event: <?= $event_id ?>, för dig själv</p>
		<?php else : ?>
			<p>debrief-formulär för event: <?= $event_id ?>, för medlem: <?= $member_id ?></p>
		<?php endif; ?>

	</div>


	<!-- Footer -->
	<?php $this->load->view('debrief/sub-views/footer') ?>

</body>

</html>