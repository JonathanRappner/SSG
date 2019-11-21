$(document).ready(function()
{
	//juleljus
	$(".stage_1").hide();
	setInterval(xmas_lights_update, 1000);

	//tomten flyby
	$("body").append("<img id='tomten' src='"+ base_url +"images/holidays/tomten.png' />");
	if(Math.random() < 0.1) //1/10 channs
	{
		var wait = Math.floor(Math.random() * 60) * 1000;
		setTimeout(xmas_tomten_flyby, wait);
		console.log("Tomten flyby in: "+ (wait/1000) +" seconds!");
	}
});

/**
 * Blinka juleljusen
 */
function xmas_lights_update()
{
	//show() på den ena och hide() på den andra
	$(".stage_0, .stage_1").toggle();
}

/**
 * Tomte-siluett placeras till höger eller vänster, utanför skärmen och flyger tvärs över.
 */
function xmas_tomten_flyby()
{
	//variabler
	var tomte_width = 600;
	var tomte_height = 115;
	var tomte_travel_time = 1500;
	var fly_right = Math.random() >= 0.5; //flyga åt höger eller vänster?
	var start_left = fly_right ? -tomte_width : $(window).width();
	var start_top = Math.random() * ($(window).height()-tomte_height);
	var target_left = fly_right ? $(window).width() : -tomte_width;
	var target_top = Math.random() * ($(window).height()-tomte_height);
	var flip = fly_right ? "1" : "-1"; //flippa vertikalt vid vänster-flyby så att inte tomten tappar sina paket
	var angle = get_angle(start_left, start_top, target_left, target_top); //räkna ut vinkel baserat på travel-vector

	//placera och visa
	$("#tomten")
		.css("left", start_left +"px")
		.css("top", start_top +"px")
		.css("transform", "rotate("+ (angle) +"deg) scaleY("+ flip +")") //flippa vertikalt om så behövs
		.show();

	//animera
	$("#tomten").animate(
		{ left: target_left +"px", top: target_top +"px" },
		{
			duration: tomte_travel_time,
			easing: "linear",
			complete: function(){ $("#tomten").hide(); }
		}
	);
}

 /**
 * Lista ut vinkeln i grader.
 * Ange parametrar som json-object med x- och y-attribut.
  * @param {number} start_x 
  * @param {number} start_y 
  * @param {number} target_x 
  * @param {number} target_y 
  */
function get_angle(start_x, start_y, target_x, target_y)
{
	var angle_rad = Math.atan2(
		target_y - start_y,
		target_x - start_x
	);
	var angle_deg = angle_rad * 180 / Math.PI;
	angle_deg = (360+Math.round(angle_deg))%360;
	return angle_deg;
}