/**
 * JS för Events-admin.
 */
$(document).ready(function()
{
	// 
	$('#input_type').change(function(){
		if($(this).val() === '5')
			set_GSU_values();
	});
});

/**
 * Fyll i inputs med GSU-värden för ökad smidighet.
 */
function set_GSU_values()
{
	$('#input_title').val('GSU/ASU'); // titel
	$('#input_start_time').val('19:00'); // starttid
	$('#input_length_time').val('02:00'); // längd
	$('#input_forum_link').val(base_url +'forum/viewtopic.php?f=14&t=626'); // forum-länk
}