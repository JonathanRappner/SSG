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
});