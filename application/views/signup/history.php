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
	<?php $this->load->view('signup/sub-views/head');?>

	<!-- Page-specific -->
	<link rel="stylesheet" href="<?php echo base_url('css/signup/admin.css');?>">
	<script src="<?php echo base_url('js/signup/clickable_table.js');?>"></script>

	<title>Historik</title>

</head>
<body>

<div id="wrapper" class="container">

	<!-- Top -->
	<?php $this->load->view('signup/sub-views/top');?>

	<!-- Global Alerts -->
	<?php $this->load->view('site/sub-views/global_alerts', array('global_alerts' => $global_alerts))?>

	<h1>Historik</h1>

	<p>Här kan du se gamla events. Klicka på ett event i listan för att se fler detaljer.</p>

	<div id="wrapper_events_table" class="table-responsive table-sm">
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
				<tr data-url='<?php echo base_url("signup/event/$event->id");?>'>
					<th scope='row'><?php echo $event->title;?></th>
					<td><?php echo $event->type_name;?></td>
					<td><abbr title='<?php echo "$event->start_time - $event->end_time";?>' data-toggle='tooltip'><?php echo $event->start_date;?></abbr></td>
					<td><?php echo $event->signed_sum;?></td>
					<td><span class="text-<?php echo $event->current_member_attendance->code;?>"><?php echo $event->current_member_attendance->text;?></span></td>
				</tr>
			<?php endforeach;?>
			</tbody>
		</table>
	</div>

	<?php
	//pagination
	echo pagination($page, $total_events, $results_per_page, base_url("signup/history/"), 'wrapper_events_table');
	?>

	<!-- Footer -->
	<?php $this->load->view('signup/sub-views/footer');?>

</div>

</body>
</html>