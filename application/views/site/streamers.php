<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$this->current_page = 'streamers';


?><!DOCTYPE html>
<html lang="sv">
<head>

	<!-- CSS/JS -->
	<?php $this->load->view('site/sub-views/head');?>

	<!-- Custom CSS/JS -->
	<script src="https://embed.twitch.tv/embed/v1.js"></script>
	<link rel="stylesheet" href="<?php echo base_url('css/site/streamers.css');?>">

	<title>Swedish Strategic Group - Streamers</title>

	<script>
		// skapa Twitch.Embed-objekt som fyller i .twitch_container-elementen
		$(document).ready(function(){
			<?php foreach($streamers as $streamer):?>
				<?php if($streamer->prefered == 'twitch'):?>
					new Twitch.Embed(
						"twitch_<?=$streamer->member_id?>", {
							width: "100%",
							height: 170,
							channel: "<?=$streamer->channel_twitch?>",
							autoplay: false,
							layout: "video"
						}
					);
				<?php endif;?>
			<?php endforeach;?>
		});
	</script>

</head>
<body>

<!-- Top -->
<?php $this->load->view('site/sub-views/top');?>

<div id="wrapper_streamers" class="container p-0">

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
				echo '<div class="col-12 col-sm-6 col-md-4">';
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
						height='170'
						src='https://www.youtube.com/embed/live_stream?channel={$streamer->channel_youtube}'
						frameborder='0'
						allowfullscreen>
					</iframe>";
				}
				else
				{
					echo "<div id=\"twitch_{$streamer->member_id}\" class=\"twitch_container\"></div>"; // skapa tom container, twitch js fyller i senare
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