/**
 * JS för Adminpanel Events
 */
$(document).ready(function()
{
	//datepicker inställningar
	$('#input_start_date').datepicker({
		language: 'sv',
		format: 'yyyy-mm-dd'
	});

	//Skapa nytt event-klick
	$("#btn_show_form").click(function(){
		$("#wrapper_events_form").show();
		$(this).hide();
	});

	//forum submit
	$("#wrapper_events_form form").submit(function()
	{
		//variabler
		var valid = true;

		//start_time
		if($("#input_start_time").val().match(/^([0-9]|0[0-9]|1[0-9]|2[0-3]):[0-5][0-9]$/) != null)
		{
			$("#input_start_time").removeClass("invalid");
		}
		else
		{
			$("#input_start_time").addClass("invalid");
			valid = false;
		}

		//length_time
		if($("#input_length_time").val().match(/^([0-9]|0[0-9]|1[0-9]|2[0-3]):[0-5][0-9]$/) != null)
		{
			$("#input_length_time").removeClass("invalid");
		}
		else
		{
			$("#input_length_time").addClass("invalid");
			valid = false;
		}
		
		return valid;
	});
});