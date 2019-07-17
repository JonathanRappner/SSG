<?php
defined('BASEPATH') OR exit('No direct script access allowed');

//variabler
$this->current_page = 'news';
$carousel_images = glob('images/carousel/*.jpg');
shuffle($carousel_images);

?><!DOCTYPE html>
<html lang="sv">
<head>

	<!-- CSS/JS -->
	<?php $this->load->view('site/sub-views/head');?>
	<link rel="stylesheet" href="<?=base_url('css/site/news.css')?>">
	<link rel="stylesheet" href="<?=base_url('css/site/signup-box.css')?>">
	<link rel="stylesheet" href="<?=base_url('css/site/chat.css')?>">
	<link rel="stylesheet" href="<?=base_url('css/site/latest_posts.css')?>">
	<script src="<?=base_url('js/deadline.js')?>"></script>
	<script src="<?=base_url('js/site/chat.js')?>"></script>
	<script src="<?=base_url('js/site/news.js')?>"></script>

	<title>Swedish Strategic Group</title>

	<script>
		var deadline_epoch = <?=$next_event->deadline_epoch?>;
		var carousel_images = <?=json_encode($carousel_images)?>;
	</script>

</head>
<body>

<div id="wrapper_news" class="container">

	<!-- Topp -->
	<?php $this->load->view('site/sub-views/top');?>

	<!-- Alerts -->
	<?php $this->load->view('site/sub-views/global_alerts', array('global_alerts' => $global_alerts));?>

	<!-- Bildspel -->
	<div id="carousel" class="row my-2" style="background-image:url('<?=base_url($carousel_images[0])?>');">
		<img src="<?=base_url('images/logga-vit.svg')?>">
	</div>

	<hr class="row mt-0">

	<div class="row">
		
		<!-- Vänsterkolumn -->
		<div id="leftcol" class="col-lg-9">

			<?php if($this->member->valid):?>
			<!-- Chat -->
			<div id="chat" class="row">
				<?php $this->load->view('site/sub-views/chat', array('chat_messages' => $chat_messages));?>
			</div>
			<?php endif;?>

			<!-- Nyhetsflöde -->
			<?php if(!$this->member->valid) $this->load->view('site/sub-views/new_member_welcome.php');?>
			<?php $this->load->view('site/sub-views/newsfeed');?>

		</div>

		<!-- Högerkolumn -->
		<div id="rightcol" class="col-lg-3">
			<?php $this->load->view('site/sub-views/signup_box', (array)$next_event);?>
			<?php if($this->member->valid) $this->load->view('site/sub-views/latest_posts', array('posts'=>$posts));?>
			<?php $this->load->view('site/sub-views/ts3_viewer');?>
		</div>

	</div>

	<!-- Footer -->
	<?php $this->load->view('site/sub-views/footer');?>

</div>

</body>
</html>