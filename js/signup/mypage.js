/**
 * JS för Min sida.
 */
$(document).ready(function()
{
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

	//chart_quarter
	var params_quarter = {
		type: 'pie',
		data: {
			datasets: [{
				data: attendance_quarter.counts,
				backgroundColor: attendance_quarter.colors,
			}],
			labels: attendance_quarter.labels
		},
		options: options
	};
	new Chart($("#chart_quarter"), params_quarter);

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
});