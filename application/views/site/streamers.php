<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$this->current_page = 'streamers';


?><!DOCTYPE html>
<html lang="sv">
<head>

	<!-- CSS/JS -->
	<?php $this->load->view('site/sub-views/head');?>

	<!-- Custom CSS/JS -->
	<link rel="stylesheet" href="<?=base_url('css/site/streamers.css')?>">

	<title>Swedish Strategic Group - Streamers</title>

</head>
<body>

<!-- Top -->
<?php $this->load->view('site/sub-views/top');?>

<div id="wrapper_streamers" class="container">

	<div id="intro" class="row pt-2 pt-lg-0 mb-4 px-4 px-lg-0">
		<h1 class="col-12 px-0">Streamers</h1>
		<p class="m-0">Många av SSG:s medlemmar streamar våra OP:ar (sön 18.00 - 22.00) och träningar (ons 19.00 - 21.00).</p>
		<p class="m-0">Här kan du se om någon av dem är live just nu eller titta i deras video-arkiv på Twitch eller YouTube.</p>
	</div>

	<div id="streamers" class="row px-4 px-lg-0">

		<?php
		foreach($streamers as $streamer)
		{
			echo '<div class="col-12 col-sm-6 col-md-4 px-0">';
				echo '<h3>';
					echo '<a href="'. ($streamer->prefered == 'youtube' ? "https://www.youtube.com/channel/{$streamer->channel_youtube}/videos" : "https://www.twitch.tv/{$streamer->channel_twitch}/videos") .'" target="_blank">';
						echo group_icon($streamer->group_code, $streamer->group_name, true) . $streamer->name;
					echo '</a>';
				echo '</h3>';
			echo '</div>';
		}
		?>

	</div>

	<!-- Footer -->
	<?php $this->load->view('site/sub-views/footer');?>

</div>

</body>
</html>