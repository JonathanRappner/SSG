/**
 * JS för Min sida.
 */
$(document).ready(function()
{
	setup_charts();

	//
	$("#member_select").change(function(){
		window.location = base_url +"signup/mypage/"+ $(this).val();
	});

	//"visa data sedan:"-click
	$("#btn_since_date").click(function(){
		window.location = base_url +"signup/mypage/"+ member_id +"?since_date="+ $("#since_date").val();
	});

	//"Återställ"-click
	$("#btn_date_reset").click(function(){
		window.location = base_url +"signup/mypage/"+ member_id;
	});
});

/**
 * Skapar och konfigurerar pie charts
 */
function setup_charts()
{
	//skapa inga pie-charts om inga anmälningar har hittats
	if(attendance_total.counts <= 0)
	{
		return;
	}

	var options =
	{
		responsive: true,
		animation: false,
		legend: { display: false },
	};

	//chart_total
	var params_total = {
		type: 'pie',
		data: {
			datasets: [{
				data: attendance_total.counts,
				backgroundColor: attendance_total.colors,
			}],
			labels: attendance_total.labels
		},
		options: options
	};
	new Chart($("#chart_total"), params_total);

	//chart_event_types
	var params_event_types = {
		type: 'pie',
		data: {
			datasets: [{
				data: event_types.counts,
				backgroundColor: event_types.colors,
			}],
			labels: event_types.labels
		},
		options: options
	};
	new Chart($("#chart_event_types"), params_event_types);

	//chart_deadline
	var params_deadline = {
		type: 'pie',
		data: {
			datasets: [{
				data: deadline.counts,
				backgroundColor: deadline.colors,
			}],
			labels: deadline.labels
		},
		options: options
	};
	new Chart($("#chart_deadline"), params_deadline);

	//chart_groups
	var params_groups = {
		type: 'pie',
		data: {
			datasets: [{
				data: groups.counts,
				backgroundColor: groups.colors,
			}],
			labels: groups.labels
		},
		options: options
	};
	new Chart($("#chart_groups"), params_groups);

	//chart_roles
	var params_roles = {
		type: 'pie',
		data: {
			datasets: [{
				data: roles.counts,
				backgroundColor: roles.colors,
			}],
			labels: roles.labels
		},
		options: options
	};
	new Chart($("#chart_roles"), params_roles);
}