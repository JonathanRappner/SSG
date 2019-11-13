/**
 * JS för Adminpanel Globala meddelanden
 */
$(document).ready(function()
{
	//Text ändrad
	$("#input_text").bind('input propertychange', function(){
		update_preview($("#input_text").val(), $("#input_class").val());
	});

	//Stil-select ändrad
	$("#input_class").change(function(){
		update_preview($("#input_text").val(), $("#input_class").val());
	});

	//Submit
	$("#wrapper_global_alerts_form form").submit(function(event)
	{
		//variabler
		var valid = true;
		var regex_expiration_date = /^\d\d\d\d-(0?[1-9]|1[0-2])-(0?[1-9]|[12][0-9]|3[01]) (00|[0-9]|1[0-9]|2[0-3]):([0-9]|[0-5][0-9])$/; //åååå-mm-dd hh:mm

		//validera
		valid = valid && validate_regex($("#input_expiration_date"), regex_expiration_date, true); //Slutdatum, får vara tom
		
		return valid;
	});
});

/**
 * Uppdaterar förhandsvisnings-alert:en
 * @param {string} text Meddelandetext
 * @param {string} class_name Globala meddelandets class
 */
function update_preview(text, class_name)
{
	if(text.length <= 0)
	{
		$(".preview").html("&ndash;");
		return;
	}

	var regex_url = /https?:\/\/(www\.)?[-a-zA-Z0-9@:%._\+~#=]{2,256}\.[a-z]{2,6}\b(?:[-a-zA-Z0-9@:%_\+.,~#?&\/\/=]*)/ig;
	text = text.replace(regex_url, "<span>[<a href='$&' target='_blank'>länk</a>]</span>", text);

	//Skapa ny alert
	$(".preview").html("<div class='alert alert-"+ class_name +"'>"+ text +"</div>");
}