// Streamers-relaterade funktioner


function check_online_youtube()
{
	for(var i in streamers)
	{
		var streamer = streamers[i];

		if(streamer.channel_youtube)
		{
			var url = youtube_api + "search/?key="+ youtube_key +"&part=snippet&eventType=live&type=video&channelId="+ streamer.channel_youtube;

			// $.get(url, function(data){
			// 	console.log(streamer.name +": "+ data);
			// });

			$.get(url, function(data) {
				console.log(data);
			});
		}
	}
}