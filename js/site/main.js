// Laddas på varje undersida på huvudsidan

//Globala variabler
var base_url;

//Main
$(document).ready(function()
{
	//hämta variabler från PHP
	base_url = $("#base_url").remove().val();

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