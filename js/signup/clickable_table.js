/**
 * JS för Events-sidan.
 */
$(document).ready(function()
{
	// event.which
	// LMB = 1
	// MMB = 2
	// RMB = 3
	// MB4 = 4
	// MB5 = 5

	//Rad i "Andra events"-tabellen klickad.
	//Öppna i nytt fönster/tab beroende på shift/ctrl/mushjulsklick.
	$("table.clickable tbody tr").mouseup(function(event)
	{
		//hämta länk
		var url = $(this).data("url");

		if(event.which > 2) //inte LMB eller MMB
		{
			return;
		}
		else if(event.ctrlKey || event.which == 2) //ctrl eller mmb = ny tab
		{
			window.open(url, '_blank');
		}
		else if(event.shiftKey) //shift = nytt fönster
		{
			window.open(url);
		}
		else //vanligt
		{
			document.location = url;
		}
		return false;
	});

	//Stoppa mushjuls-klick vid klick på tabellen
	$("table.clickable tbody tr").mousedown(function(event)
	{
		return event.which == 1; //false om inte LMB
	});
});