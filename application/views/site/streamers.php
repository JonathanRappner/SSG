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
	<script type="text/javascript" src="/ssg/new/js/model_streamers.js"></script>
	<script type="text/javascript" src="/ssg/new/js/site/streamers.js"></script>

	<title>Swedish Strategic Group - Streamers</title>

</head>
<body>

<div id="wrapper_streamers" class="container">

	<!-- Top -->
	<?php $this->load->view('site/sub-views/top');?>

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
				echo '<div class="col-6 col-md-4">';
				echo '<h3>'. group_icon($streamer->group_code, $streamer->group_name, true) . $streamer->name .'</h3>';
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