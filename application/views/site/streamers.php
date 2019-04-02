<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$this->current_page = 'streamers';


?><!DOCTYPE html>
<html lang="sv">
<head>

	<!-- CSS/JS -->
	<?php $this->load->view('site/sub-views/head');?>
	<link rel="stylesheet" href="<?php echo base_url('css/site/streamers.css');?>">
	<script>
		var streamers = <?=json_encode($streamers)?>;
	</script>
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
		<p>Här ser du vilka som streamar just nu samt länkar till deras YouTube eller Twitch-kanaler.</p>
	</div>

	<div id="streamers">

		<h3>Online</h3>
		<ul class="online"></ul>

		<h3>Offline</h3>
		<ul class="offline">
			<?php
			//skriv ut länkar, föredra youtube, js kan byta länken senare om det behövs
			foreach($streamers as $streamer)
			{
				$row = $streamer->name;
				if(isset($streamer->channel_youtube))
					$row .= " (<a href=\"https://youtube.com/channel/$streamer->channel_youtube\" class=\"channel_youtube\" target='_blank'>YouTube</a>)";
				
				if(isset($streamer->channel_twitch))
					$row .= " (<a href=\"https://twitch.tv/$streamer->channel_twitch\" class=\"channel_twitch\" target='_blank'>Twitch</a>)";

				echo '<li data-member_id="'. $streamer->member_id .'">';
					echo $row;
				echo '</li>';
			}
			?>
		</ul>

	</div>

	<!-- Footer -->
	<?php $this->load->view('site/sub-views/footer');?>

</div>

</body>
</html>