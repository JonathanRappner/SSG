<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$this->current_page = 'streamers';


?><!DOCTYPE html>
<html lang="sv">
<head>

	<!-- CSS/JS -->
	<?php $this->load->view('site/sub-views/head');?>

	<!-- Custom CSS/JS -->
	<link rel="stylesheet" href="<?php echo base_url('css/site/streamers.css');?>">

	<?php if(XMAS):?>
		<link rel="stylesheet" href="<?=base_url('css/holidays/xmas.css')?>">
	<?php endif;?>

	<title>Swedish Strategic Group - Streamers</title>

</head>
<body>

<!-- Top -->
<?php $this->load->view('site/sub-views/top');?>

<div id="wrapper_streamers" class="container">

	<!-- Alerts -->
	<?php $this->load->view('site/sub-views/global_alerts', array('global_alerts' => $global_alerts));?>

	<h1>Streamers</h1>

	<div id="intro" class="mb-4">
		<p>Många av SSG:s medlemmar streamar våra OP:ar och träningar.</p>
		<p>Här kan du se om någon av dem är live just nu eller titta i deras video-arkiv på Twitch eller YouTube.</p>
	</div>

	<div id="streamers">

		<div class="row">
			<?php
			foreach($streamers as $streamer)
			{
				echo '<div class="col-12 col-sm-6 col-md-4 col-xl-3">';
				echo '<h3>';
					echo '<a href="'. ($streamer->prefered == 'youtube' ? "https://www.youtube.com/channel/{$streamer->channel_youtube}/videos" : "https://www.twitch.tv/{$streamer->channel_twitch}/videos") .'" target="_blank">';
						echo group_icon($streamer->group_code, $streamer->group_name, true) . $streamer->name;
					echo '</a>';
				echo '</h3>';
				if($streamer->prefered == 'youtube')
				{
					echo
					"<iframe
						width='100%'
						src='https://www.youtube.com/embed/live_stream?channel={$streamer->channel_youtube}'
						frameborder='0'
						allowfullscreen>
					</iframe>";
				}
				else
				{
					echo
					"<iframe
						src='https://player.twitch.tv/?autoplay=false&channel={$streamer->channel_twitch}'
						width='100%'
						frameborder='0'
						scrolling='no'
						allowfullscreen='true'>
					</iframe>";
				}
				echo '</div>';
			}
			?>
		</div>

	</div>

	<!-- Footer -->
	<?php $this->load->view('site/sub-views/footer');?>

</div>

</body>
</html>