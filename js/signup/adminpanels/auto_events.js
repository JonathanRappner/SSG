/**
 * JS för Adminpanel Auto-events
 */
$(document).ready(function()
{
	//Skapa nytt event-klick
	$("#btn_show_form").click(function(){
		$("#wrapper_auto_events_form").show();
		$(this).hide();
	});
});