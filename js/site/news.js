// JS till Nyhetssidan/Huvudsidan
var mobile_desktop_threshold = 576;
var scrollbar_width = 17;
var is_mobile;
var is_mobile_prev;

$(document).ready(function () {
	var index = 0;

	update_news_position();

	//carousel-klick
	$("#carousel").click(function (event) {
		index = (index + 1) % carousel_images.length;
		$(this).css("background-image", "url(" + base_url + carousel_images[index] + ")");
	});

	//click + drag markerar loggan
	$("#carousel").mousedown(function (event) {
		event.preventDefault();
	});

	// webbläsare går från sm till md eller tvärtom
	$(window).resize(function () {
		update_news_position();
	});

	//expandera kollapsat nyhetsflöde
	$("#btn_news_expand").click(function (event) {
		$(this).remove();
		$("#newsfeed div.bottom_fade").remove();
		$("#newsfeed").css("max-height", "initial");
		$("#newsfeed").css("margin-bottom", "20px");
	});

	//signupbox deadline countdown
	$("#deadline_text").html(deadline_timer_tick(deadline_epoch)); //kör medan vi väntar på första tick:en
	setInterval(function () { //starta ticks med 1000 ms fördröjning
		$("#deadline_text").html(deadline_timer_tick(deadline_epoch));
	}, 1000);
});

// 
function update_news_position(){

	is_mobile = ($(window).width() + scrollbar_width) < mobile_desktop_threshold;

	// ingen ändring
	if(is_mobile === is_mobile_prev){
		return;
	}

	// ändring
	if (is_mobile) { // mobile
		$("#news_container")
			.detach()
			.appendTo("#news_mobile_container");
	} else { // desktop
		$("#news_container")
			.detach()
			.appendTo("#news_desktop_container");
	}

	is_mobile_prev = is_mobile;
}