<?php
/** 
 * Anmälningsrutan
*/
defined('BASEPATH') OR exit('No direct script access allowed');

//variabler
if(isset($event_id))
{
	$days_swe = array(1=>'Söndag', 'Måndag', 'Tisdag', 'Onsdag', 'Torsdag', 'Fredag', 'Lördag');
	$logged_in = $this->member->valid;
	$user_is_member = $this->permissions->has_permissions(array('rekryt', 'medlem', 'inaktiv'));
	$signed = isset($member_signup);
	$litteral_day = $days_swe[$day_of_week]; //'Söndag' / 'Onsdag'
	$attendance_string = $signed
		? '<span class="'.  $attendance_types[$member_signup->attendance_id]->class .'">'.  $attendance_types[$member_signup->attendance_id]->text .'</span>'
		: null;
}
?><div class="signup_box">

	<div class="card border-0 shadow-sm">

		<!-- Header -->
		<div class="card-header bg-dark text-white">
			<?php if(XMAS):?><div class="snow_edge left"></div><div class="snow_pattern"></div><div class="snow_edge right"></div>🎄<?php endif;?>
			<?php if(CAKE):?>🍰<?php endif;?>
			Nästa event
		</div>

		<!-- Body -->
		<div class="card-body text-center p-3">

			<?php if(isset($event_id)):?>

				<!-- titel -->
				<h2><?=isset($forum_link) ? "<a href='$forum_link'  title='Till briefing' data-toggle='tooltip'>$title</a>" : $title?></h2>

				<!-- veckodag, datum och tidsspann -->
				<p class="date"><?="$litteral_day, $start_date ($start_time - $end_time)"?></p>

				<!-- antal anmälningar -->
				<p class="count" title="Antal Ja, JIP och QIP-anmälningar" data-toggle="tooltip">
					<a href="<?=base_url("signup/event/$event_id")?>">
						<?=$signups_count?>
					</a>
				</p>
				<p class="mb-1"><strong>Anmälda</strong></p>

				<?php if($logged_in && $user_is_member && !$signed):?>
					<!-- deadline -->
					<div class="deadline" title="Du kan fortfarande anmäla dig efter deadline:n har runnit ut." data-toggle="tooltip">
						Deadline: <span id="deadline_text"></span>
					</div>
				<?php endif;?>

				<?php if($signed):?>
					<p class="mb-1">Din anmälan: <?=$attendance_string?></p>
				<?php endif;?>
				
				<div class="row mb-2">

					<?php if($signed):?>
						<a class="btn_signup_edit btn btn-primary" href="<?=base_url("signup/event/{$event_id}/showform")?>">Ändra anmälan <i class="fas fa-edit"></i></a>
					<?php elseif($logged_in && $user_is_member):?>
						<a class="btn_signup_new btn btn-success" href="<?=base_url("signup/event/{$event_id}/showform")?>">Anmäl dig <i class="fas fa-chevron-right"></i></a>
					<?php endif;?>

				</div>

				<?php if($logged_in && $user_is_member):?>
					<small><a class="text-dark" href="<?=base_url('signup')?>">Anmäl dig till ett annat event.</a></small>
				<?php endif;?>

			<?php else:?>

				<strong>Det finns inget framtida event planerat. 😢</strong>

			<?php endif;?>

		</div> <!-- end div.card-body -->

	</div> <!-- end div.card -->

</div> <!-- end #signup_box -->