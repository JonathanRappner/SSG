// JS för chat-rutan

//globals
var is_loading; //antal meddelanden som laddas per ajax-request
var earliest_loaded_message_id; //id för meddelandet längst ner i listan

$(document).ready(function()
{
	var message_count = $("#chat-list li").length; //ladda alltid samma antal meddelanden som php gör
	earliest_loaded_message_id = $("#chat-list li:last").data("message_id");
	is_loading = false; //true när meddelanden laddas
	
	//chat-lista skroll
	$("#chat-list").scroll(function(event)
	{
		var scroll_ratio = get_scroll_ratio($(this).scrollTop(), $(this).height(), $(this).prop("scrollHeight"));
		if(
			scroll_ratio >= 1 && //har skrollat till botten
			!is_loading && //laddar inte redan meddelanden
			earliest_loaded_message_id < earliest_message_id //finns fler meddelanden i db att ladda
		)
		{
			append_messages(earliest_loaded_message_id, message_count);
		}
	});

	//skicka-knapp klick
	$("#btn_send").click(function(event)
	{
		$(this).prop("disabled", true);
		$("i", this).hide();
		$("div.spinner-border", this).css("display", "inline-block");
	});
});



/**
 * Ger skroll-positionen från 0 (längst upp) till 1 (längst ner).
 * @param {int} scroll_pos_top Positionen längst upp. Börjar på 0 och ökar när man skrollar ner. Blir som mest (total_height - visible_height) eftersom toppraden aldrig når botten.
 * @param {int} visible_height Skroll-elementets synliga dels höjd.
 * @param {int} total_height Hela skoll-elementets höjd.
 */
function get_scroll_ratio(scroll_pos_top, visible_height, total_height)
{
	return scroll_pos_top / (total_height - visible_height);
}

/**
 * Ladda fler meddelanden.
 * @param {int} message_id Ladda meddelanden efter detta meddelanted.
 * @param {int} length Antal meddelanden att ladda
 */
function append_messages(message_id, length)
{
	is_loading = true;
	$("#chat div.status").fadeIn(50); //visa loading animation

	var url = base_url +"api/chat_messages/?message_id="+ message_id +"&length="+ length;

	$.get(url, function(data){
		append_messages_response(data);
	});
}

/**
 * Hantera svar från api.
 * @param {array} data 
 */
function append_messages_response(data)
{
	for(var i in data)
	{
		var message = data[i];
		$("#chat-list").append
		(
			"<li data-message_id="+ message.id +">"+
				"<a href='"+ base_url +"forum/memberlist.php?mode=viewprofile&u="+ message.phpbb_user_id +"' target='_blank'>"+
					message.name+
				"</a>: "+
				message.text+
				"<p class='timespan'>"+
					message.timespan_string+
				"</p>"+
			"</li>"
		);
		earliest_loaded_message_id = message.id-0;
	}
	is_loading = false;
	$("#chat div.status").fadeOut(50); //göm loading animation
}