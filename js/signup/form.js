/**
 * JS för anmälningsformuläret
 */
$(document).ready(function()
{
	// //koppla date-picker
	// $('#input_free_start_datetime').datetimepicker();
	// $('#input_free_end').datetimepicker({format: 'LT'});
	
	
	// //Frianmälan-start-tiden ändras
	// $("#input_free_start_datetime").on("change.datetimepicker", function(e)
	// {
	// 	//inga startdatum i det förflutna
	// 	$('#input_free_start_datetime').datetimepicker('minDate', 'now');

	// 	//sluttid från inte ligga före starttid
	// 	$('#input_free_end').datetimepicker('minDate', e.date);
	// });


	//Önskad Enhet-selected ändras
	$("#input_group").change(function(event)
	{
		update_roles($(this).val());
	});
	update_roles($("#input_group").val());

	// //Göm eller visa kontroller för Frianmälan
	// $("#input_event").change(function(event)
	// {
	// 	update_free_signup($(this).val() == -1);
	// });
	// update_free_signup($("#input_event").val() == -1);

});

/**
 * Uppdaterar befattning-listan
 * @param {int} group_id Grupp-id
 */
function update_roles(group_id)
{
	//rensa role-select
	$("#input_role").html("");

	//iterera genom groups_roles[group_id] och skriv ut dess roller
	for(var i=0; i<groups_roles[group_id].length; i++)
	{
		var role = groups_roles[group_id][i];
		var selected = "";

		if(role.role_id == default_role)
		{
			selected = "selected";
		}

		$("#input_role").append("<option value='"+ role.role_id +"' "+ selected +">"+ role.role_name +"</option>\n");
	}
}


// function update_free_signup(show_free_signup)
// {
// 	if(show_free_signup)
// 	{
// 		$("#free_signup_inputs").show();
// 	}
// 	else
// 	{
// 		$("#free_signup_inputs").hide();
// 	}
// }