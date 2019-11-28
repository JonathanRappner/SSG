/**
 * JS för Events-sidan.
 */
$(document).ready(function()
{
	// visa modal
	if(show_form) $("#form_popup").modal();
	
	//Anmälningslänk-klick
	$("#signup_link").click(function(){
		signup_link($(this)); // hämta länk från knappens data-link attribut
	});
});

/**
 * Kopiera länken.
 * @param {string} link 
 */
function signup_link(button_element)
{
	var link = $(button_element).data("link");
	var temp = $("<input>");
	$("footer").append(temp);
	temp.val(link).select();
	document.execCommand("copy");
	temp.remove();
}