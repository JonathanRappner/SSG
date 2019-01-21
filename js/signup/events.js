/**
 * JS för Events-sidan.
 */
$(document).ready(function()
{
	//hämta variabler från PHP
	var deadline_epoch = $("#deadline_epoch").remove().val();

	//starta deadline-timer
	deadline_timer_init(deadline_epoch);
});

/**
 * Startar deadline timer
 * @param {int} deadline_epoch 
 */
function deadline_timer_init(deadline_epoch)
{
	//kör medan vi väntar på första tick:en
	$("#deadline").html(deadline_timer_tick(deadline_epoch));

	//starta ticks med 1000 ms fördröjning
	setInterval(function() {
		$("#deadline").html(deadline_timer_tick(deadline_epoch));
	}, 1000);
}