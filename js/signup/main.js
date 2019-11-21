// Laddas på varje Signup-sida

//Main
$(document).ready(function()
{
	//enable:a bootstrap stuff
	$('[data-toggle="tooltip"]').tooltip();
	$('.alert').alert();

	//Lightbox-klick
	$(document).on('click', '[data-toggle="lightbox"]', function(event)
	{
		event.preventDefault();
		$(this).ekkoLightbox();
	});

	//spara vilka global_alerts som har stängts, i sessions
	$("#global_alerts button.close").click(function(){
		var global_alert_id = $(this).parent('div.alert').data("id");
		
		$.ajax({
			url: base_url + "api/global_alert_dismiss/?id="+ global_alert_id,
			type: "POST"
		});
	});
});