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
	deadline_timer_tick(deadline_epoch);

	//starta ticks med 1000 ms fördröjning
	setInterval(function() {
		deadline_timer_tick(deadline_epoch);
	}, 1000);
}

/**
 * 
 * @param {int} deadline_epoch 
 */
function deadline_timer_tick(deadline_epoch)
{
	var total_seconds = deadline_epoch - Math.floor(Date.now() / 1000);

	//avbryt om deadline runnit ut
	if(total_seconds < 0)
	{
		$("#deadline").html("<span class='text-danger'><strong>Passerat!</strong></span>");
		return;
	}

	var one_day = 86400;
	var one_hour = 3600;
	var one_minute = 60;
	var days = Math.floor(total_seconds / one_day);
	var hours = Math.floor((total_seconds % one_day) / one_hour);
	var minutes = Math.floor((total_seconds % one_hour) / one_minute);
	var seconds = Math.floor(total_seconds % one_minute);
	var text_class;

	//textfärg
	if(total_seconds >= one_hour * 6)
	{
		text_class = "text-success";
	}
	else if(total_seconds > one_hour)
	{
		text_class = "text-warning";
	}
	else
	{
		text_class = "text-danger";
	}

	//singular/plural
	var days_string = days == 1 ? 'dag' : 'dagar';

	//visa dagar, timmar osv.
	if(total_seconds >= one_day) //mer än en dag
	{
		$("#deadline").html("<span class='"+ text_class +"'><strong>"+ days +" "+ days_string +"</strong></span>"); //visa bara dagar
	}
	else if(total_seconds >= one_hour) //mindre än en dag, mer än en timme
	{
		$("#deadline").html("<span class='"+ text_class +"'><strong>"+ hours +" h & "+ minutes +" min</strong></span>"); //visa timmar och minuter
	}
	else if(total_seconds < one_hour && total_seconds >= one_minute) //mindre än en timme
	{
		$("#deadline").html("<span class='"+ text_class +"'><strong>"+ minutes +" min & "+ seconds +"s</strong></span>"); //visa minuter och sekunder
	}
	else if(total_seconds < one_minute) //mindre än en minut
	{
		$("#deadline").html("<span class='"+ text_class +"'><strong>"+ seconds +"s</strong></span>"); //visa bara sekunder
	}
	// $("#deadline").html(days +" dagar "+ hours +":"+ minutes +":"+ seconds);
}