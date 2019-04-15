/**
 * JS för Adminpanel Signups
 */
$(document).ready(function()
{
	//forum submit
	$("#wrapper_signups_form form").submit(function(event)
	{
		//variabler
		var valid = true;
		var regex_datetime = /^(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})$/;

		//validera
		valid = valid && validate_regex($("#input_signed_datetime"), regex_datetime, false); //input_signed_datetime
		valid = valid && validate_regex($("#input_last_changed_datetime"), regex_datetime, false); //input_last_changed_datetime
		
		return valid;
	});
});