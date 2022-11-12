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
	<script src="<?= base_url('js/signup/clickable_table.js') ?>"></script>
	<!-- <link rel="stylesheet" href="<xxx?=base_url('css/signup/events.css?3')?>"> -->

	<title>SSG Debrief</title>

</head>

<body>

	<!-- Top -->
	<?php $this->load->view('debrief/sub-views/top') ?>

	<!-- Huvud-wrapper -->
	<div id="wrapper" class="container p-0">

		<!-- Rubrik -->
		<h2>Debrief</h2>

		<p>
			Här listas alla tidigare events och om du skrivit en debrief för dem eller inte.<br>
			Klicka på ett event för att se en sammanställning av alla dess debriefs.
		</p>

		<div id="wrapper_events_table" class="row table-responsive table-sm">
			<div class="px-3 py-2 mb-3 bg-white rounded shadow-sm">
				<table class="table table-hover clickable">
					<thead class="table-borderless">
						<tr>
							<th scope="col" style="width:50%;">Titel</th>
							<th scope="col">Typ</th>
							<th scope="col">Datum</th>
							<th scope="col">Din närvaro</th>
							<th scope="col">Din debrief</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($events as $event) : ?>
							<tr data-url='<?= base_url("debrief/event/$event->id") ?>'>
								<th scope='row'><?= $event->title ?></th>
								<td><?= $event->type_name ?></td>
								<td><abbr title='<?= "$event->start_time - $event->end_time" ?>' data-toggle='tooltip'><?= $event->start_date ?></abbr></td>
								<td><span class="text-<?= $event->current_member_attendance->code ?>"><?= $event->current_member_attendance->text ?></span></td>
								<td><?php
									if ($event->current_member_attendance->is_positive) {
										echo $event->debriefed
											? '<span class="badge badge-success">Skriven</span>'
											: '<span class="badge badge-danger">Saknas</span>';
									}
								?></td>
							</tr>
						<?php endforeach ?>
					</tbody>
				</table>
			</div>
		</div><!-- end #wrapper_events_table -->

		<?= pagination($page, $total_events, $results_per_page, base_url("debrief/events/"), 'wrapper_events_table') ?>

	</div>


	<!-- Footer -->
	<?php $this->load->view('debrief/sub-views/footer') ?>

</body>

</html>