<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$this->current_page = 'news';
?><!DOCTYPE html>
<html lang="sv">
<head>

	<!-- CSS/JS -->
	<?php $this->load->view('site/sub-views/head');?>
	<link rel="stylesheet" href="<?=base_url('css/site/signup-box.css')?>">
	<link rel="stylesheet" href="<?=base_url('css/site/chat.css')?>">
	<script src="<?=base_url('js/deadline.js')?>"></script>
	<script src="<?=base_url('js/site/chat.js')?>"></script>

	<title>Swedish Strategic Group</title>

	<script>
		var deadline_epoch = <?=$deadline_epoch?>;
		
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

<div id="wrapper_news" class="container">

	<!-- Top -->
	<?php $this->load->view('site/sub-views/top');?>

	<div class="row">

		<?php if($this->member->valid):?>
		<!-- Meddelanden -->
		<div class="col-12 rounded bg-danger text-light mb-2">
			Viktigt meddelande!
		</div>
		<div class="col-12 rounded bg-info text-light mb-2">
			Inte lika viktigt meddelande.
		</div>
		<?php endif;?>
		
		<!-- Vänsterkolumn -->
		<div class="col-lg-9">

			<?php if($this->member->valid):?>
			<!-- Chat -->
			<div id="chat" class="row">
				<?php $this->load->view('site/sub-views/chat', array('chat_messages' => $chat_messages));?>
			</div>
			<?php endif;?>

			<!-- News -->
			<div class="row bg-warning text-light text-center" style="height:600px;">
				nyhets-feed
			</div>

		</div>

		<!-- Högerkolumn -->
		<div class="col-lg-3">
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