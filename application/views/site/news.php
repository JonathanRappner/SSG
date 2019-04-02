<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$this->current_page = 'news';
?><!DOCTYPE html>
<html lang="sv">
<head>

	<!-- CSS/JS -->
	<?php $this->load->view('site/sub-views/head');?>
	<script src="<?php echo base_url('js/deadline.js');?>"></script>

	<title>Swedish Strategic Group</title>

	<script>
		var deadline_epoch = <?php echo $deadline_epoch;?>;
		
		$(document).ready(function()
		{
			var deadline_element = $("#signup_box .deadline span");

			//kör medan vi väntar på första tick:en
			$(deadline_element).html(deadline_timer_tick(deadline_epoch));

			//starta ticks med 1000 ms fördröjning
			setInterval(function() {
				$(deadline_element).html(deadline_timer_tick(deadline_epoch));
			}, 1000);
		});
	</script>

</head>
<body>

<div id="wrapper_login" class="container">

	<!-- Top -->
	<?php $this->load->view('site/sub-views/top');?>

	<div class="row">

		<div class="col-12 rounded bg-danger text-light mb-2">
			Viktigt Meddelande!
		</div>

		<div class="col-12 rounded bg-info text-light mb-2">
			Aktiva streamers: <strong>Knatte</strong>, <strong>Fnatte</strong>, <strong>Tjatte</strong>
		</div>
		
		<!-- Left column -->
		<div class="col-9">

			<div class="row bg-warning text-light text-center" style="height:200px;">
				scummbar
			</div>

			<div class="row bg-primary text-light text-center" style="height:600px;">
				nyhets-feed
			</div>

		</div>

		<!-- Right column -->
		<div class="col-3">
			<div class="row">
				<?php $this->load->view('site/sub-views/signup_box');?>
			</div>
			<div class="row bg-info text-light" style="height:200px;">Senaste forum-inlägg</div>
			<div class="row bg-danger text-light" style="height:500px;">TS3-viewer</div>
		</div>

	</div>

	<!-- Footer -->
	<?php $this->load->view('site/sub-views/footer');?>

</div>

</body>
</html>