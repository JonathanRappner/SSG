// JS till Nyhetssidan/Huvudsidan

$(document).ready(function()
{
	//starta deadline-timer
	deadline_timer_init(deadline_epoch);
});

/**
 * Startar deadline timer
 * @param {number} deadline_epoch 
 */
function deadline_timer_init(deadline_epoch)
{
	//kör medan vi väntar på första tick:en
	$("#deadline_text").html(deadline_timer_tick(deadline_epoch));

	//starta ticks med 1000 ms fördröjning
	setInterval(function() {
		$("#deadline_text").html(deadline_timer_tick(deadline_epoch));
	}, 1000);
}