// JS för chat-rutan

//globals
var is_loading;
var earliest_loaded_message_id; //id för meddelandet längst ner i listan
var message_count; //antal meddelanden som laddas åt gången
var edit_mode = false;

$(document).ready(function()
{
	message_count = $("#chat-list div.chat_row").length; //ladda alltid samma antal meddelanden som php gör
	earliest_loaded_message_id = $("#chat-list div.chat_row:last").data("message_id");
	is_loading = false; //true när ajax laddar någonting
	var update_interval = 60000; //antal millisekunder mellan uppdateringar

	//enable:a popovers för info-"knappen"
	$('[data-toggle="popover"]').popover();
	
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
			earliest_loaded_message_id != earliest_message_id //finns fler meddelanden i db att ladda
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
			if(!edit_mode)
			{
				send_message($("#message").val());
			}
			else
			{
				save_message($("#message").data("message_id"), $("#message").val());
			}
		}
	});

	//uppdatera meddelanden
	setInterval(function()
	{
		//avbryt om
		//webbläsar-fliken inte är i fokus
		//listan inte är skrollad längst upp
		//redigerar inte ett meddelande
		if(!document.hidden && get_scroll_ratio() <= 0 && !edit_mode)
		{
			refresh_messages(message_count);
		}
	}, update_interval);

	//sätt edit/delete-knappars klick-event
	set_edit_delete_events();

	//spara-knapp klick
	$("#btn_save").click(function(event)
	{
		save_message($("#message").data("message_id"), $("#message").val());
	});

	//avbryt-knapp klick
	$("#btn_abort").click(function(event)
	{
		stop_editing();
	});
});



/**
 * Ger skroll-positionen från 0 (längst upp) till 1 (längst ner).
 */
function get_scroll_ratio()
{
	var chat_list = $("#chat-list");
	return $(chat_list).scrollTop() / ($(chat_list).prop("scrollHeight") - $(chat_list).height());
}


function set_edit_delete_events()
{
	//redigera
	$("button.btn_chat_edit").click(function(event)
	{
		start_editing($(this).data("message_id"));
	});

	//redigera
	$("button.btn_chat_delete").click(function(event)
	{
		delete_message($(this).data("message_id"));
	});
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
	var url = base_url + "api/messages/?length="+ length;
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
	stop_editing(); //om man är i edit mode och tar bort meddelande eller trycker refresh så ska edit mode avbrytass
	set_edit_delete_events();

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
	var url = base_url + "api/messages/?message_id="+ message_id +"&length="+ length;
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
	set_edit_delete_events();

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
			'<div class="row chat_row" data-message_id="'+ message.id +'">'+
				
				'<div class="message_left col-10">'+
					"<a href='"+ base_url +"forum/memberlist.php?mode=viewprofile&u="+ message.phpbb_user_id +"' target='_blank'>"+
						message.name+
					"</a>: "+
					message.text+
					"<p class='timespan'>"+
						message.timespan_string+
					"</p>"+
				"</div>"+ //end message_left

				(is_admin || message.member_id == member_id //visa endast egna meddelanden om man inte är admin
					? '<div class="message_right col-2 text-right">'+
						'<button class="btn btn-primary btn_chat_edit" data-message_id="'+ message.id +'" title="Redigera meddelande"><i class="fas fa-edit"></i></button> '+
						'<button class="btn btn-danger btn_chat_delete" data-message_id="'+ message.id +'" title="Ta bort meddelande"><i class="fas fa-trash-alt"></i></button>'+
					'</div>' //end message_right
					: ""
				) +

			"</div>"
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
	if(is_loading || text.length <= 0)
	{
		return;
	}

	is_loading = true;
	$("#btn_send").prop("disabled", true); //disable:a Skicka-knapp
	$("#btn_send i").hide(); //göm pratbubbla-ikonen
	$("#btn_send div.spinner-border").css("display", "inline-block"); //visa spinner-animationen
	
	$.post(
		base_url + "api/message/", //url
		{text: text}, //data
		function(data){ send_message_response(data); } //success function
	);
}

/**
 * Återställ element efter ajax-request return:ar success.
 */
function send_message_response(data)
{
	is_loading = false;

	$("#btn_send").prop("disabled", false); //enable:a Skicka-knapp
	$("#btn_send div.spinner-border").hide(); //göm spinner-animationen
	$("#btn_send i").css("display", "inline-block"); //visa pratbubbla-ikonen
	$("#message").val(null); //rensa input

	//refresh:a
	refresh_messages(message_count);
}

/**
 * Visa confirmation-dialog och skicka delete request till API:n
 * @param {number} message_id 
 */
function delete_message(message_id)
{
	if(!confirm("Är du säker på att du vill ta bort meddelandet?"))
	{
		return;
	}

	//disable:a edit/delete-knappar
	$("div.chat_row[data-message_id="+ message_id +"] button").prop("disabled", true);

	$.ajax({
		url: base_url + "api/message/?message_id="+ message_id,
		type: "DELETE",
		success: function(data){ refresh_messages(message_count); }
	});
}

/**
 * Börja ladda meddelande som ska redigeras.
 * @param {number} message_id 
 */
function start_editing(message_id)
{
	if(is_loading)
	{
		return;
	}
	is_loading = true;

	$("#loading-animation").fadeIn(50); //visa loading animation

	//hämta data (ett meddelande)
	var url = base_url + "api/message/?message_id="+ message_id;
	$.get(url, function(data){
		setup_editing(data);
	});
}

/**
 * Ställ om input-delen till edit mode.
 * @param {string} data JSON response från API:n
 */
function setup_editing(message)
{
	//återställ
	is_loading = false;
	edit_mode = true;
	$("#loading-animation").fadeOut(50); //göm loading animation

	//fyll i text-input
	$("#message").val(message.text_plain);
	$("#message").data("message_id", message.id-0);

	//ställ om till edit-mode
	$("#btn_send").hide();
	$("#btn_save").show();
	$("#btn_abort").show();
}

/**
 * Återställer input till send-mode
 */
function stop_editing()
{
	$("#message").val(null);
	$("#message").data("message_id", null);
	$("#btn_send").show();
	$("#btn_save").hide();
	$("#btn_abort").hide();
	edit_mode = false;
}

/**
 * Spara ändrat meddelande
 * @param {number} message_id 
 * @param {string} text 
 */
function save_message(message_id, text)
{
	if(is_loading || text.length <= 0)
	{
		return;
	}
	is_loading = true;

	$("#btn_save").prop("disabled", true);
	$("#btn_abort").prop("disabled", true);
	$("#btn_save i").hide(); //göm knapp-ikonen
	$("#btn_save div.spinner-border").css("display", "inline-block"); //visa loading-animationen i knappen
	$("#message").prop("disabled", true);

	$.ajax({
		url: base_url + "api/message/?message_id="+ message_id +"&text="+ text,
		type: "PUT",
		success: function(data){ save_message_response(data); }
	});
}

/**
 * Lyckats att spara redigerat meddelande.
 * @param {string} data JSON-svar
 */
function save_message_response(data)
{
	//återställ
	is_loading = false;

	//återställ knappar efter loading är klart
	$("#btn_save").prop("disabled", false);
	$("#btn_abort").prop("disabled", false);
	$("#btn_save i").css("display", "inline-block"); //visa knapp-ikonen
	$("#btn_save div.spinner-border").hide();
	$("#message").prop("disabled", false);

	stop_editing(); //gå tillbaka till send-mode
	refresh_messages(message_count);
}