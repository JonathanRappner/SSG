<?php
/** 
 * Vy för Min statistik-sidan.
*/
defined('BASEPATH') OR exit('No direct script access allowed');

//variabler
$this->current_page = 'history';

?><!DOCTYPE html>
<html lang="sv">
<head>
	<?php $this->load->view('signup/sub-views/head')?>

	<!-- Page-specific -->
	<script src="<?=base_url('js/signup/clickable_table.js')?>"></script>

	<title>Historik</title>

</head>
<body>

<!-- Top -->
<?php $this->load->view('signup/sub-views/top')?>

<div id="wrapper" class="container">

	<h1>Historik</h1>

	<p>Här kan du se gamla events. Klicka på ett event i listan för att se fler detaljer.</p>

	<div id="wrapper_events_table" class="row table-responsive table-sm">
		<div class="px-3 py-2 mb-3 bg-white rounded shadow-sm">
			<table class="table table-hover clickable">
				<thead class="table-borderless">
					<tr>
						<th scope="col" style="width:50%;">Titel</th>
						<th scope="col">Typ</th>
						<th scope="col">Datum</th>
						<th scope="col">Anmälda</th>
						<th scope="col">Din närvaro</th>
					</tr>
				</thead>
				<tbody>
				<?php foreach($events as $event):?>
					<tr data-url='<?=base_url("signup/event/$event->id")?>'>
						<th scope='row'><?=$event->title?></th>
						<td><?=$event->type_name?></td>
						<td><abbr title='<?="$event->start_time - $event->end_time"?>' data-toggle='tooltip'><?=$event->start_date?></abbr></td>
						<td><?=$event->signed_sum?></td>
						<td><span class="text-<?=$event->current_member_attendance->code?>"><?=$event->current_member_attendance->text?></span></td>
					</tr>
				<?php endforeach?>
				</tbody>
			</table>
		</div>
	</div><!-- end #wrapper_events_table -->

	<?php
	//pagination
	echo pagination($page, $total_events, $results_per_page, base_url("signup/history/"), 'wrapper_events_table');
	?>

	<!-- Footer -->
	<?php $this->load->view('signup/sub-views/footer')?>

</div>

</body>
</html>