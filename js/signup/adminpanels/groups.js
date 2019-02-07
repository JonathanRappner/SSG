/**
 * JS för Adminpanel Grupper
 */
$(document).ready(function()
{
	//Skapa nytt event-klick
	$("#btn_show_form").click(function(){
		$("#wrapper_groups_form").show();
		$(this).hide();
	});
});