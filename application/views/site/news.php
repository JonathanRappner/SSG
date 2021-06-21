<?php
defined('BASEPATH') OR exit('No direct script access allowed');

//variabler
$this->current_page = 'news';
$user_is_member = $this->permissions->has_permissions(array('rekryt', 'medlem', 'inaktiv'));
$carousel_images = glob('images/carousel/*.webp');
shuffle($carousel_images);

?><!DOCTYPE html>
<html lang="sv">
<head>

	<!-- Generic head -->
	<?php $this->load->view('site/sub-views/head');?>

	<!-- Page-specific CSS/JS -->
	<link rel="stylesheet" href="<?=base_url('css/site/news.css?1')?>">
	<link rel="stylesheet" href="<?=base_url('css/site/signup-box.css')?>">
	<link rel="stylesheet" href="<?=base_url('css/site/chat.css?1')?>">
	<link rel="stylesheet" href="<?=base_url('css/site/latest_posts.css')?>">
	<script src="<?=base_url('js/site/news.js')?>"></script>

	<?php if($user_is_member):?>
		<script src="<?=base_url('js/deadline.js')?>"></script>
		<script src="<?=base_url('js/site/chat.js')?>"></script>
	<?php endif;?>
	
	<title>Swedish Strategic Group - Swedish Arma 3 Clan</title>

	<script>
		<?php if($user_is_member):?>var deadline_epoch = <?=$next_event->deadline_epoch?>;<?php endif;?>
		var carousel_images = <?=json_encode($carousel_images)?>;
	</script>

</head>
<body>

<!-- Topp -->
<?php $this->load->view('site/sub-views/top');?>

<div id="wrapper_news" class="container">

	<?php if(XMAS):?>
		<!-- Juleljus -->
		<div class="xmas_lights_wrapper">
			<img src="<?=base_url("images/holidays/xmas_lights_0.png")?>" class="xmas_lights stage_0">
			<img src="<?=base_url("images/holidays/xmas_lights_1.png")?>" class="xmas_lights stage_1">
		</div>
	<?php endif;?>

	<!-- Bildspel -->
	<div id="carousel" class="row px-3 py-2 px-lg-4 py-lg-3 mb-0 mb-lg-4 shadow-sm d-flex align-items-center" style="background-image:url('<?=base_url($carousel_images[0])?>');">
		<img src="<?=base_url('images/logo-gray.svg')?>">
		<h1 class="m-0 ml-2 ml-sm-3 ml-lg-4">Swedish Strategic Group</h1>
		<?php if(XMAS):?><div class="holiday_icon">🎅</div><?php endif;?>
		<?php if(CAKE):?><div class="holiday_icon">🎂</div><?php endif;?>
		<?php if(EASTER):?><div class="holiday_icon">🐣</div><?php endif;?>
	</div>

	<div class="row">
		
		<!-- Vänsterkolumn -->
		<div id="leftcol" class="col-lg-9 pl-0 pr-0 pr-lg-2">

			<?php if($this->member->valid && $user_is_member):?>
				<!-- Chat -->
				<?php $this->load->view('site/sub-views/chat', array('chat_messages' => $chat_messages));?>
			<?php endif;?>

			<!-- Nyhetsflöde -->
			<?php if(!$this->member->valid) $this->load->view('site/sub-views/new_member_welcome.php');?>
			<?php $this->load->view('site/sub-views/newsfeed');?>

		</div>

		<!-- Högerkolumn -->
		<div id="rightcol" class="col-lg-3 p-0">
			<?php $this->load->view('site/sub-views/signup_box', (array)$next_event);?>
			<?php if($this->member->valid) $this->load->view('site/sub-views/latest_posts', array('posts'=>$posts));?>
			<?php $this->load->view('site/sub-views/links');?>
			<?php $this->load->view('site/sub-views/ts3_viewer');?>
		</div>

	</div>

	<!-- Footer -->
	<?php $this->load->view('site/sub-views/footer');?>

</div>

</body>
</html>