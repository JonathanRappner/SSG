<?php
defined('SIGNUP_BOX') || die("No direct access.");
/**
 * Anmälningsrutan till gamla SMF-sidan
 * include:as därifrån så alla url:er relateras därifrån.
 */

//hitta member_id i $_SESSION
function get_member_id()
{
	foreach($_SESSION as $first)
		foreach($first as $key => $second)
			if($key == 'id')
				return $second;
}

//sätt tidzon (php)
date_default_timezone_set('Europe/Stockholm');

//hämta db-variabler och koppla upp
include('Settings.php');
$mysqli = mysqli_connect($db_server, "$db_user", $db_passwd, $db_name);
mysqli_set_charset($mysqli, 'utf8');
$mysqli->query('SET time_zone = "'. date('P') .'"');

//variabler
$days_swe = array(1=>'Söndag', 'Måndag', 'Tisdag', 'Onsdag', 'Torsdag', 'Fredag', 'Lördag');
$attendance_types = array(1=>'signed', 'jip', 'qip', 'noshow', 'notsigned', 'awol',);
$member_id = get_member_id();
$deadline_time = '00:00:00';

//event
$sql =
	'SELECT
		ssg_events.id, ssg_events.title, forum_link,
		ssg_event_types.title AS type_name,
		DATE_FORMAT(start_datetime, "%Y-%m-%d") AS start_date,
		DAYOFWEEK(start_datetime) AS day_of_week,
		TIME_FORMAT(start_datetime, "%H:%i") AS start_time,
		TIME_FORMAT(ADDTIME(start_datetime, length_time), "%H:%i") AS end_time,
		UNIX_TIMESTAMP(DATE_FORMAT(start_datetime, "%Y-%m-%d '. $deadline_time .'")) AS deadline_epoch
	FROM ssg_events
	INNER JOIN ssg_event_types
		ON ssg_events.type_id = ssg_event_types.id
	WHERE
		ADDTIME(start_datetime, length_time) >= NOW()
		AND ssg_event_types.obligatory = 1
	ORDER BY start_datetime ASC
	LIMIT 1';
if($result = $mysqli->query($sql))
	if($result->num_rows > 0)
		$event = (object)$result->fetch_assoc();


//antal signups
if(isset($event->id))
{
	$sql =
		'SELECT
			COUNT(*) AS count
		FROM ssg_signups
		WHERE
			event_id = '. $event->id .'
			AND attendance <= 3'; //Ja, JIP & QIP
	$result = $mysqli->query($sql);
	$num_signups = (object)$result->fetch_assoc();
	$num_signups = !empty($num_signups->count) ? $num_signups->count : 0;
}

//inloggade medlemmens anmälan
if(isset($member_id) && isset($event->id)) //hämta bara om inloggad medlem finns och framtida event finns
{
	$sql =
		"SELECT
			attendance AS attendance_name,
			attendance-0 AS attendance_id
		FROM ssg_signups
		WHERE
			event_id = $event->id
			AND member_id = $member_id";

	if(!$result = $mysqli->query($sql))
		throw new Exception($mysqli->error);
	
	$member_signup = (object)$result->fetch_assoc();
}

//stäng uppkoppling
mysqli_close($mysqli);

// $member_signup->attendance_id = null;//////////////////toggle:a new/edit

?><style>
	#signup_box{
		font-family: Arial;
		text-align: center;
		padding-bottom: 4px;
	}

	#signup_box .title{
		font-size: 1.2rem;
		line-height: normal;
		font-weight: bold;
		margin-bottom: 10px;
	}
	#signup_box .title a{ color: #000; text-decoration: none; }

	#btn_signup, #btn_edit{
		display: block;
		line-height: 1.5;
		color: #fff;
		border: 1px solid transparent;
		margin: 0 8px;
		padding: .375rem .75rem;
		border-radius: .3rem;
	}

	#btn_signup{
		font-size:2rem;
		background-color: #28a745;
		border-color: #28a745;
	}
	#btn_signup:hover{
		background-color: #128C2D;
		text-decoration: none;
	}

	#btn_edit{
		font-size:1.5rem;
		background-color: #3A99FC;
		border-color: #3A99FC;
	}
	#btn_edit:hover{
		background-color: #1284FA;
		text-decoration: none;
	}

	/* Veckodag */
	#signup_box .day{
		font-size: 1.5rem;
		font-weight: bold;
		margin: 10px 0 2px 0;;
		line-height: normal;
	}

	/* Datum + klockslag */
	#signup_box .date{
	}

	#signup_box .signed_count{
		color: #000;
		font-size: 4rem;
		font-weight: bold;
		line-height: normal;
		display: block;
	}

	/* Antal anmälda-siffra */
	#signup_box .signed{
		color: #000;
		font-weight: bold;
		font-size: 1.0rem;
		display: block;
	}
	#signup_box .signed_count:hover,
	#signup_box .signed:hover{
		text-decoration: none;
	}
	#signup_box .member-signed{
		margin-top: 12px;
		font-size: 0.8rem;
	}

	/* Deadline */
	#signup_box .deadline{
		font-weight: bold;
		margin-top: 12px;
		font-size: 1.2rem;
	}

	/* "Anmäl dig till ett annat event" */
	#signup_box .signup_other{
		display: inline-block;
		margin-top: 14px;
	}

	.text-jip, .text-qip, .text-noshow, .text-signed, .text-notsigned, .text-awol{font-weight: bold;}
	.text-signed{ color: #28a745; }
	.text-jip{ color: #285ca6; }
	.text-qip{ color: #fea500; }
	.text-noshow{ color: #fff; background-color: #fc302b; padding:4px; }
	.text-notsigned{ color: #848484;}
	.text-awol{ color: #fff; background-color: #7B23A8; padding:4px; }
	.text-success{ color: #28a745; }
	.text-warning{ color: #ffc107; }
	.text-danger{ color: #dc3545; }
</style>
	
<script>
	$(document).ready(function()
	{
		var deadline_epoch = $("#deadline_epoch").remove().val();

		//kör medan vi väntar på första tick:en
		deadline_timer_tick(deadline_epoch);

		//starta ticks med 1000 ms fördröjning
		setInterval(function() {
			deadline_timer_tick(deadline_epoch);
		}, 1000);
	});

	function deadline_timer_tick(deadline_epoch)
	{
		var total_seconds = deadline_epoch - Math.floor(Date.now() / 1000);
		var deadline = $("#signup_box .deadline span");

		//avbryt om deadline runnit ut
		if(total_seconds < 0)
		{
			$(deadline).html("<span class='text-danger'><strong>Passerat!</strong></span>");
			return;
		}

		var one_day = 86400;
		var one_hour = 3600;
		var one_minute = 60;
		var days = Math.floor(total_seconds / one_day);
		var hours = Math.floor((total_seconds % one_day) / one_hour);
		var minutes = Math.floor((total_seconds % one_hour) / one_minute);
		var seconds = Math.floor(total_seconds % one_minute);
		var text_class;

		//textfärg
		if(total_seconds >= one_hour * 6)
		{
			text_class = "text-success";
		}
		else if(total_seconds > one_hour)
		{
			text_class = "text-warning";
		}
		else
		{
			text_class = "text-danger";
		}

		//singular/plural
		var days_string = days == 1 ? 'dag' : 'dagar';

		//visa dagar, timmar osv.
		if(total_seconds >= one_day) //mer än en dag
		{
			$(deadline).html("<span class='"+ text_class +"'>"+ days +" "+ days_string +"</span>"); //visa bara dagar
		}
		else if(total_seconds >= one_hour) //mindre än en dag, mer än en timme
		{
			$(deadline).html("<span class='"+ text_class +"'>"+ hours +" h & "+ minutes +" min</span>"); //visa timmar och minuter
		}
		else if(total_seconds < one_hour && total_seconds >= one_minute) //mindre än en timme
		{
			$(deadline).html("<span class='"+ text_class +"'>"+ minutes +" min & "+ seconds +"s</span>"); //visa minuter och sekunder
		}
		else if(total_seconds < one_minute) //mindre än en minyt
		{
			$(deadline).html("<span class='"+ text_class +"'>"+ seconds +"s</span>"); //visa bara sekunder
		}
	}
</script>

<?php if(isset($event)):?>
	<input id="deadline_epoch" value="<?php echo $event->deadline_epoch;?>" type="hidden">
	<div id="signup_box">
		<!-- titel -->
		<?php if(isset($event->forum_link)):?>
			<div class="title"><a href="<?php echo $event->forum_link;?>"><?php echo $event->title;?></a></div>
		<?php else:?>
			<div class="title"><?php echo $event->title;?></div>
		<?php endif;?>
		
		<!-- knapp -->
		<a href="new/signup/form/<?php echo $event->id;?>" id="<?php echo isset($member_signup->attendance_id) ? 'btn_edit' : 'btn_signup';?>">
			<?php echo isset($member_signup->attendance_id) ? 'Ändra anmälan &raquo;' : 'Anmäl dig &raquo;';?>	
		</a>

		<!-- veckodag -->
		<div class="day"><?php echo $days_swe[$event->day_of_week];?></div>

		<!-- datum och tid -->
		<div class="date"><?php echo "$event->start_date ($event->start_time - $event->end_time)";?></div>

		<!-- antal anmälda -->
		<a class="signed_count" href="new/signup/event/<?php echo $event->id;?>"><?php echo $num_signups;?></a>
		<a class="signed" href="new/signup/event/<?php echo $event->id;?>">Anmälda</a>

		<?php if(isset($member_signup->attendance_id)):?>
			<!-- din anmälan -->
			<div class="member-signed">
				Din anmälan:
				<span class="text-<?php echo $attendance_types[$member_signup->attendance_id];?>"><?php echo $member_signup->attendance_name;?></span>
			</div>
		<?php else:?>
			<!-- deadline -->
			<div class="deadline">
				Deadline:
				<span></span>
			</div>
		<?php endif;?>

		<!-- annat event -->
		<a href="new/signup" class="signup_other">Anmäl dig till ett annat event.</a>
	</div>
<?php else:?>
	<a href="new/signup">Anmäl dig till events här!</a>
<?php endif;?>