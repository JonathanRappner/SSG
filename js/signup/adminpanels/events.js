/**
 * JS för Adminpanel Events
 */
$(document).ready(function()
{
	//Skapa nytt event-klick
	$("#btn_show_form").click(function(){
		$("#wrapper_events_form").show();
		$(this).hide();
	});

	//forum submit
	$("#wrapper_events_form form").submit(function(event)
	{
		//variabler
		var valid = true;
		var regex_time = /^([0-9]|0[0-9]|1[0-9]|2[0-3]):[0-5][0-9]$/;

		//validera
		valid = valid && validate_regex($("#input_start_time"), regex_time, false); //start_time
		valid = valid && validate_regex($("#input_length_time"), regex_time, false); //length_time
		
		return valid;
	});
});