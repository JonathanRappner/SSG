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

	<title>Teknikstrul-summering</title>

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
						<li class="breadcrumb-item active" aria-current="page">Teknikstrul</li>
					</ol>
				</nav>
			</div>
		</div>

		<!-- Rubrik -->
		<div class="row pb-4">
			<div class="col-12">
				<h2>Teknikstrul: <?= $event->title ?></h2>
			</div>

			<div class="col">
				Här listas alla teknikstrul-debriefs för eventet.
			</div>
		</div>

		<div id="wrapper_debriefs_table" class="row">
			<div class="col">
				<div class="px-3 py-2 mb-3 bg-white rounded shadow-sm table-responsive table-sm">
					<table class="table table-hover">
						<thead class="table-borderless">
							<tr>
								<th scope="col">Namn</th>
								<th scope="col">Grupp</th>
								<th scope="col">Närvaro</th>
								<th scope="col">Betyg</th>
								<th scope="col">Tekniskt strul</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($reviews as $review) : ?>
								<tr>
									<th scope='row' class="text-nowrap"><?= $review->member_name ?> <?= rank_icon($review->rank_icon, $review->rank_name) ?></th>
									<td class="text-nowrap"><?= group_icon($review->group_code, $review->group_name) ?> <?= $review->group_name ?></td>
									<td><span class="<?= $review->attendance->class ?>"><?= $review->attendance->text ?></span></td>
									<td>
										<div class="member_score">
											<div class="stars" style="width:<?= $review->score * 17 ?>px;"></div>
										</div>
									</td>
									<td><?= $review->text ?></td>
								</tr>
							<?php endforeach ?>
						</tbody>
					</table>
				</div>
			</div>
		</div><!-- end #wrapper_debriefs_table -->

	</div>


	<!-- Footer -->
	<?php $this->load->view('debrief/sub-views/footer') ?>

</body>

</html>