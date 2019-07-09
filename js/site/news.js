// JS till Nyhetssidan/Huvudsidan

$(document).ready(function()
{
	var index = 0;

	//carousel-klick
	$("#carousel").click(function(event){
		index = (index + 1) % carousel_images.length;
		$(this).css("background-image", "url("+ base_url + carousel_images[index] +")");
	});

	//click + drag markerar loggan
	$("#carousel").mousedown(function(event){
		event.preventDefault();
	});

	//expandera kollapsat nyhetsflöde
	$("#btn_news_expand").click(function(event){
		$(this).remove();
		$("#newsfeed div.bottom_fade").remove();
		$("#newsfeed").css("max-height", "initial");
		$("#newsfeed").css("margin-bottom", "20px");
	});

	//kör medan vi väntar på första tick:en
	$("#deadline_text").html(deadline_timer_tick(deadline_epoch));

	//starta ticks med 1000 ms fördröjning
	setInterval(function() {
		$("#deadline_text").html(deadline_timer_tick(deadline_epoch));
	}, 1000);
});