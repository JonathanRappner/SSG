// JS för chat-rutan

//globals
var is_loading;
var is_sending;
var earliest_loaded_message_id; //id för meddelandet längst ner i listan
var message_count; //antal meddelanden som laddas åt gången

$(document).ready(function()
{
	message_count = $("#chat-list li").length; //ladda alltid samma antal meddelanden som php gör
	earliest_loaded_message_id = $("#chat-list li:last").data("message_id");
	is_loading = false; //true när meddelanden laddas
	is_sending = false; //true när meddelanden skickas/uppdateras/tas bort
	var update_interval = 60000; //antal millisekunder mellan uppdateringar
	
	//chat-lista skroll
	$("#chat-list").scroll(function(event)
	{
		//göm/visa refresh-knappen
		if(get_scroll_ratio() > 0)
		{
			$("#btn_refresh").fadeIn(50);
		}
		else
		{
			$("#btn_refresh").fadeOut(50);
		}

		//ladda fler meddelanden om man skrollat till botten
		if(
			get_scroll_ratio() >= 1 && //har skrollat till botten
			!is_loading && //laddar inte redan meddelanden
			earliest_loaded_message_id < earliest_message_id //finns fler meddelanden i db att ladda
		)
		{
			event.preventDefault(); //hindrar webbläsaren från att skrolla hela rutan medan ett meddelande laddas
			append_messages(earliest_loaded_message_id, message_count);
		}
	});
	
	//refresh-knapp klick (knappen syns när man skrollat ner)
	$("#btn_refresh").click(function(event)
	{
		$("#chat-list").scrollTop(0); //skrolla upp
		$(this).hide(); //göm knappen (görs redan i $("#chat-list").scroll()-eventet men är lite för långsamt)
		refresh_messages(message_count);
	});

	//skicka-knapp klick
	$("#btn_send").click(function(event)
	{
		if($("#message").val().length > 0)
		{
			send_message($("#message").val());
		}
	});

	//enter-tryck i chat-input
	$(document).on('keypress',function(e)
	{
		if(e.which == 13 && $("#message").is(":focus")) //knappen var Enter och chat-input hade focus
		{
			send_message($("#message").val());
		}
	});

	//uppdatera meddelanden
	setInterval(function()
	{
		//avbryt om webbläsar-fliken inte är i fokus eller listan inte är skrollad längst upp
		if(!document.hidden && get_scroll_ratio() <= 0)
		{
			refresh_messages(message_count);
		}
	}, update_interval);
});



/**
 * Ger skroll-positionen från 0 (längst upp) till 1 (längst ner).
 */
function get_scroll_ratio()
{
	var chat_list = $("#chat-list");
	return $(chat_list).scrollTop() / ($(chat_list).prop("scrollHeight") - $(chat_list).height());
}

/**
 * Ladda om de senaste meddelandena.
 * @param {number} length Antal meddelanden att ladda.
 */
function refresh_messages(length)
{
	//förbered laddning
	is_loading = true;
	$("#loading-animation").fadeIn(50); //visa loading animation

	//hämta data
	var url = base_url +"api/chat/?length="+ length;
	$.get(url, function(data)
	{
		refresh_messages_response(data);
	});
}

/**
 * Svar på request som gjordes i refresh_messages()
 * @param {string} data Array med objekt.
 */
function refresh_messages_response(data)
{
	//lägg in meddelanden
	$("#chat-list").html(""); //rensa gamla meddelanden
	add_messages(data);

	//avbryt laddning
	is_loading = false;
	$("#loading-animation").fadeOut(50); //göm loading animation
}

/**
 * Ladda fler meddelanden.
 * @param {number} message_id Ladda meddelanden efter detta meddelanted.
 * @param {number} length Antal meddelanden att ladda
 */
function append_messages(message_id, length)
{
	//förbered laddning
	is_loading = true;
	$("#loading-animation").fadeIn(50); //visa loading animation

	//hämta data
	var url = base_url +"api/chat/?message_id="+ message_id +"&length="+ length;
	$.get(url, function(data){
		append_messages_response(data);
	});
}

/**
 * Svar på request som gjordes i append_messages()
 * @param {string} data Array med objekt.
 */
function append_messages_response(data)
{
	add_messages(data);

	//avbryt laddning
	is_loading = false;
	$("#loading-animation").fadeOut(50); //göm loading animation
}

/**
 * Lägger till meddelanden i #chat-list
 * @param {array} messages JSON-array med objekt.
 */
function add_messages(messages)
{
	for(var i in messages)
	{
		var message = messages[i];
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
}

/**
 * Skicka nytt text-meddelande till chat.
 * Validering utförs av server-side baserat på inloggad användare.
 * @param {string} text 
 */
function send_message(text)
{
	//avbryt som redan skickar
	if(is_sending)
	{
		return;
	}

	is_sending = true;
	$("#btn_send").prop("disabled", true); //disable:a Skicka-knapp
	$("#btn_send i").hide(); //göm pratbubbla-ikonen
	$("#btn_send div.spinner-border").css("display", "inline-block"); //visa spinner-animationen
	
	$.post(
		base_url +"api/chat/", //url
		{text: text}, //data
		function(data){ send_message_response(data); } //success function
	);
}

/**
 * Återställ element efter ajax-request return:ar success.
 */
function send_message_response(data)
{
	is_sending = false;

	$("#btn_send").prop("disabled", false); //enable:a Skicka-knapp
	$("#btn_send div.spinner-border").hide(); //göm spinner-animationen
	$("#btn_send i").css("display", "inline-block"); //visa pratbubbla-ikonen
	$("#message").val(null); //rensa input

	//refresh:a
	refresh_messages(message_count);
}