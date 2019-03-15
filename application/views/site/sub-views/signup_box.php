<?php
/** 
 * Anmälningsrutan
*/
defined('BASEPATH') OR exit('No direct script access allowed');

//variabler
$days_swe = array(1=>'Söndag', 'Måndag', 'Tisdag', 'Onsdag', 'Torsdag', 'Fredag', 'Lördag');
$logged_in = $this->member->valid;
$signed = isset($member_signup);



?><div id="signup_box" class="text-center col">

	<!-- rubrik -->
	<h4>Nästa event</h4>

	<!-- titel -->
	<h5><?php echo isset($forum_link) ? "<a href='$forum_link'>$title</a>" : $title;?></h5>

	<!-- veckodag -->
	<p><?php echo $days_swe[$day_of_week];?></p>

	<!-- datum och tidsspann -->
	<p><?php echo "$start_date ($start_time - $end_time)";?></p>
	
	<!-- antal anmälningar -->
	<h1 title="Antal Ja, JIP och QIP-anmälningar" data-toggle="tooltip"><?php echo $signups_count;?></h1>
	<p>Anmälda</p>

	<?php if(!$signed && $logged_in):?>
		<!-- deadline -->
		<div class="deadline" title="Du kan fortfarande anmäla dig efter deadline:en har runnit ut." data-toggle="tooltip">
			Deadline: <span></span>
		</div>
	<?php endif;?>
	
	<div class="row text-light">

		<?php if($signed):?>
			<a class="col-12 btn btn-primary" href="<?php echo base_url("signup/event/$event_id/showform");?>">Ändra anmälan (<span><?php echo $attendance_types[$member_signup->attendance_id]->text;?></span>) &raquo;</a>
		<?php elseif($logged_in):?>
			<a class="col-12 btn btn-success" href="<?php echo base_url("signup/event/$event_id/showform");?>">Anmäl dig &raquo;</a>
		<?php endif;?>

		<?php if($logged_in):?>
			<a class="col btn btn-primary" href="<?php echo base_url("signup/event/$event_id");?>">Se anmälningar</a>
			<a class="col btn btn-warning" href="<?php echo base_url('signup');?>">Se andra events</a>
			<?php if(isset($forum_link)):?>
				<a class="col btn btn-info" href="<?php echo $forum_link;?>">Läs om eventet</a>
			<?php endif;?>
		<?php endif;?>

	</div>

</div>
