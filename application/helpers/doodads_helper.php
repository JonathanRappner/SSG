<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
 * Generiska helper-funktioner till allt möjligt.
 */

/**
 * Skriver ut pagination-kontroller (Föregående, 1, 2, 3, Nästa)
 * Skriver två versioner, en för mobil med max 4 sidor listade och en för desktop med $max_pages sidor listade. 
 *
 * @param int $page Nuvarande sida
 * @param int $total_results Totala antal resultat.
 * @param int $results_per_page Resultat per sida.
 * @param string $link_prefix Länk-sträng som kommer före sidnummer.
 * @param string $scroll_to_id Element-id dit sidan ska skrolla. (Lägger till #$scroll_to_id efter alla länkar.)
 * @param int $max_pages Max antal sidor som pagination listar.
 * @return void
 */
function pagination($page, $total_results, $results_per_page, $link_prefix, $scroll_to_id = null, $max_pages = 12)
{
	return 
		pagination_base($page, $total_results, $results_per_page, $link_prefix, $scroll_to_id, $max_pages, 'd-none d-sm-none d-md-block') //stora
		. pagination_base($page, $total_results, $results_per_page, $link_prefix, $scroll_to_id, min(4, $max_pages), 'd-block d-md-none'); //lilla
}

/**
 * Skriver ut pagination-kontroller (Föregående, 1, 2, 3, Nästa)
 *
 * @param int $page Nuvarande sida
 * @param int $total_results Totala antal resultat.
 * @param int $results_per_page Resultat per sida.
 * @param string $link_prefix Länk-sträng som kommer före sidnummer.
 * @param string $scroll_to_id Element-id dit sidan ska skrolla. (Lägger till #$scroll_to_id efter alla länkar.)
 * @param int $max_pages Max antal sidor som pagination listar.
 * @param string $class Klass-sträng till nav-elementet.
 * @return void
 */
function pagination_base($page, $total_results, $results_per_page, $link_prefix, $scroll_to_id = null, $max_pages = 12, $class = null)
{
	//variabler
	$total_pages = ceil($total_results / $results_per_page);
	$link_suffix = isset($scroll_to_id) ? "#$scroll_to_id" : null;

	$output = "<nav class='$class'><ul class='pagination'>";
	
	//föregående
	$output .= $page > 0
		?
			'<li class="page-item"><a class="page-link" href="'. $link_prefix . 0 . $link_suffix .'"><i class="fas fa-step-backward"></i></a></li>
			<li class="page-item"><a class="page-link" href="'. $link_prefix . ($page-1) . $link_suffix .'"><i class="fas fa-chevron-left"></i></a></li>'
		:
			'<li class="page-item disabled"><span class="page-link"><i class="fas fa-step-backward"></i></span></li>
			<li class="page-item disabled"><span class="page-link"><i class="fas fa-chevron-left"></i></span></li>';
	
	//sidlänkar (1, 2, 3, osv.)
	if($total_pages <= $max_pages) //lista alla sidor
	{
		for($i = 0; $i < $total_pages; $i++)
			$output .= 
				'<li class="page-item'. ($page == $i ? ' active' : null) .'">
					<a class="page-link" href="'. $link_prefix . $i . $link_suffix .'">'. ($i+1) .'</a>
				</li>';
	}
	else //visa sidor runt on current page istället (max antal: $max_pages)
	{
		$start = max($page - floor($max_pages / 2), 0);
		$end = min($start + $max_pages, $total_pages);
		$start = min($start, $end-$max_pages); //om mindre än hälften av sidorna ligger efter current
		
		for($i=$start; $i<$end; $i++)
			$output .= 
				'<li class="page-item'. ($page == $i ? ' active' : null) .'">
					<a class="page-link" href="'. $link_prefix . $i . $link_suffix .'">'. ($i+1) .'</a>
				</li>';
	}
	
	//nästa
	$output .= $page < ($total_pages-1)
		?
			'<li class="page-item"><a class="page-link" href="'. $link_prefix . ($page+1) . $link_suffix .'"><i class="fas fa-chevron-right"></i></a></li>
			<li class="page-item"><a class="page-link" href="'. $link_prefix . ($total_pages-1) . $link_suffix .'"><i class="fas fa-step-forward"></i></a></i>'
		:
			'<li class="page-item disabled"><span class="page-link"><i class="fas fa-chevron-right"></i></span></li>
			<li class="page-item disabled"><span class="page-link"><i class="fas fa-step-forward"></i></span></li>';

	$output .= '</ul></nav>';


	return $output;
}

/**
 * Ger HTML-kod för grupp-ikon.
 * Kan skriva ut 16px-version och 32px-version.
 * Eftersom mobiltelefoner använder högre zoom-nivå (de är inte pixel-perfect) så ser en 16px-bild inte bra ut.
 * Istället trycker vi ihop en 32px-ikon till 16px-storlek på mobiler men inte desktop.
 *
 * @param string $group_code Grupp-kod (ex. 'fa', 'vl')
 * @param string $group_name Om grupp-namnet ska stå i tooltip, skriv in det här.
 * @param boolean $big True ger 32px-ikon, false ger 16px-ikon.
 * @return void
 */
function group_icon($group_code, $group_name = null, $big = false)
{
	//variabler
	$no_icon = array('ja', 'ka', 'gb', 'ib'); //grupper som inte har ikoner
	$icon_string_start = base_url("images/group_icons/{$group_code}");
	$tooltip_string = isset($group_name) ? "data-toggle=\"tooltip\" title=\"{$group_name}\"" : null;

	if($group_code != null && !in_array($group_code, $no_icon))
	{
		if(!$big) //16px
			return
				"<img class=\"group_icon_16 d-none d-md-inline\" src=\"{$icon_string_start}_16.png\" {$tooltip_string} />
				<img class=\"group_icon_16 d-inline d-md-none\" src=\"{$icon_string_start}_32.png\" {$tooltip_string} />";
		else //32px
			return "<img class=\"group_icon_32 d-inline\" src=\"{$icon_string_start}_32.png\" {$tooltip_string} />";
	}
	else
		return '<i class="fas fa-question-circle"></i>';
}

/**
 * Skriver ut gradikon.
 *
 * @param string $rank_icon Gradens ikons filnamn.
 * @param string $rank_name Gradtitel. Ex: "Menig Klass I"
 * @return string HTML-kod
 */
function rank_icon($rank_icon, $rank_name)
{
	if(!$rank_icon) $rank_icon = 'inaktiv.png';
	if(!$rank_name) $rank_name = 'Inaktiv';

	return '<img class="rank_icon" src="'. base_url('images/rank_icons/'. $rank_icon) .'" title="'. $rank_name .'" data-toggle="tooltip" />';
}

/**
 * Ta bort bbcode-tags.
 * Ex: "[img]image.jpg[/img]" -> "image.jpg"
 *
 * @param string $text
 * @return string
 */
function strip_bbcode($text)
{
	return preg_replace('/[[\/\!]*?[^\[\]]*?]/si', null, $text);
}

/**
 * Ger formaterad, relativ tidssträng.
 * Ex: '(2 timmar sedan)'
 * '(i måndags 11:07)'
 *
 * @param int $date Datum (unix epoch)
 * @return void
 */
function relative_time_string($date)
{
	//tidsspann i sekunder
	$min = 60;
	$hour = 3600;
	$day = 86400;
	$six_days = 518400;

	//variabler
	$days_swe = array(1=>'måndag', 'tisdag', 'onsdag', 'torsdag', 'fredag', 'lördag', 'söndag');
	$now = time();
	$diff = abs($now - $date);
	$date_string = date('Y-m-d G:i', $date);
	$unix_day = floor(($date + date('Z')) / 86400); //dagar sedan 1970-01-01, justerat efter tidszon
	$unix_day_now = floor(($now + date('Z')) / 86400);

	if($diff < $min) //mindre än en minut sedan
		$output = 'nyss';
	else if($diff < $hour) //mer än en minut sedan (ex: '35 minuter sedan')
	{
		$minutes = floor($diff / $min);
		$units_string = $minutes == 1 ? 'minut' : 'minuter';
		$output = "$minutes $units_string sedan";
	}
	else if($diff < $day && $unix_day == $unix_day_now) //mer än en timme sedan OCH samma datum-dag (ex: 'idag 20:05')
		$output = 'idag '. date('G:i', $date);
	else if($unix_day_now - $unix_day == 1) //mer är en timme OCH förra dagen (ex: 'igår 0:22')
		$output = 'igår '. date('G:i', $date);
	else if($diff < $six_days) //mer är en dag sedan (ex: 'i fredags 13:49') (använd six_days so att det inte står "i fredags" på en fredag)
		$output = 'i '. $days_swe[date('N', $date)] . 's '. date('G:i', $date);
	else //mer än sex dagar sedan
		$output = $date_string;

	return $output;
}

/**
 * Tyder bbcode och omvlandlar till html.
 * Kanske lite väl special-gjord för newsfeed.
 *
 * @param string $text
 * @return string
 */
function bbcode_parse($text)
{
	$find = array(
		'/\n{3,}/', //tre eller fler newlines blir en <br>
		'/\n/',
		'~\[b\](.*?)\[/b\]~s',
		'~\[i\](.*?)\[/i\]~s',
		'~\[u\](.*?)\[/u\]~s',
		'~\[hr\]\[\/hr\]~s',
		'~\[quote\](.*?)\[/quote\]~s',
		'~\[size=(.*?)\](.*?)\[/size\]~s',
		'~\[font="(.*?)"\](.*?)\[/font\]~s',
		'~\[color=(.*?)\](.*?)\[/color\]~s',
		'~\[url\]((?:ftp|https?)://.*?)\[/url\]~s',
		'~\[url=(.+?)\](.+?)\[\/url\]~s',
		'~\[img\](https?://.+?)\[/img\]~s',
		'~\[media\](?:https:\/\/www.youtube.com\/watch\?v=)(.+)\[/media\]~s',
		'~\[video\](https?://.+?)\[/video\]~s',
	);

	$replace = array(
		'<br>',
		'<br>',
		'<strong>$1</strong>',
		'<i>$1</i>',
		'<span style="text-decoration:underline;">$1</span>',
		'<hr>',
		'<pre>$1</pre>',
		'<span style="font-size:2rem;">$2</span>', //alt: '<span style="font-size:$1px;">$2</span>'
		'<span style="font-family:\'$1\'">$2</span>',
		'<span style="color:$1;">$2</span>',
		'<a href="$1">$1</a>',
		'<a href="$1">$2</a>',
		'<a class="newsfeed_image" href="$1" data-toggle="lightbox"><img src="$1" alt /></a>', //bilder ska inte vara inline
		'<iframe class="youtube" frameborder="0" src="https://www.youtube.com/embed/$1"></iframe>',
		'<video autoplay loop muted><source src="$1" type="video/mp4">Din webbläsare stödjer inte videos.</video>',
	);

	return preg_replace($find, $replace, $text);
}