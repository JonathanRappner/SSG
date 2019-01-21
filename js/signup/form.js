/**
 * JS för anmälningsformuläret
 */
$(document).ready(function()
{
	//Önskad Enhet-selected ändras
	$("#input_group").change(function(event)
	{
		update_roles($(this).val());
	});
	update_roles($("#input_group").val());

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