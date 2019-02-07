/**
 * JS för Adminpanel Grader
 */
$(document).ready(function()
{
	//ändra gradikon när ikon-select:en ändras
	$("#input_icon").change(function(){
		update_rank_icon($(this).val());
	});
	update_rank_icon($("#input_icon").val());
});

function update_rank_icon(icon)
{
	var icon_url = base_url + "images/rank_icons/" + icon;
	$("#rank_icon").attr('src', icon_url);
}