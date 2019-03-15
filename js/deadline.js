/**
 * Ger deadline-text. (ex: "5 dagar", "6h & 25 min", "Passerat!")
 * @param {int} deadline_epoch Deadline-tid i Unix-epoch-format.
 * @returns {string} Deadline html-textsträng.
 */
function deadline_timer_tick(deadline_epoch)
{
	var total_seconds = deadline_epoch - Math.floor(Date.now() / 1000);

	//avbryt om deadline runnit ut
	if(total_seconds < 0)
	{
		return "<span class='text-danger'>Passerat!</span>";
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
		return "<span class='"+ text_class +"'>"+ days +" "+ days_string +"</span>"; //visa bara dagar
	}
	else if(total_seconds >= one_hour) //mindre än en dag, mer än en timme
	{
		return "<span class='"+ text_class +"'>"+ hours +" h & "+ minutes +" min</span>"; //visa timmar och minuter
	}
	else if(total_seconds < one_hour && total_seconds >= one_minute) //mindre än en timme
	{
		return "<span class='"+ text_class +"'>"+ minutes +" min & "+ seconds +"s</span>"; //visa minuter och sekunder
	}
	else if(total_seconds < one_minute) //mindre än en minut
	{
		return "<span class='"+ text_class +"'>"+ seconds +"s</span>"; //visa bara sekunder
	}
	// return days +" dagar "+ hours +":"+ minutes +":"+ seconds;
}