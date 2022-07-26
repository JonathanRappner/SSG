<?php
/** 
 * Anmälningsrutan
*/
defined('BASEPATH') OR exit('No direct script access allowed');

//variabler
if(isset($next->event_id))
{
	$days_swe = array(1=>'Söndag', 'Måndag', 'Tisdag', 'Onsdag', 'Torsdag', 'Fredag', 'Lördag');
	$logged_in = $this->member->valid;
	$user_is_member = $this->permissions->has_permissions(array('rekryt', 'medlem', 'inaktiv'));
	$signed = isset($next->member_signup);
	$litteral_day = $days_swe[$next->day_of_week]; //'Söndag' / 'Onsdag'
	$attendance_string = $signed
		? '<span class="'.  $attendance_types[$next->member_signup->attendance_id]->class .'">'.  $attendance_types[$next->member_signup->attendance_id]->text .'</span>'
		: null;
}

/**
 * Relativ datum-sträng. T.ex: 'på tisdag', 'imorgon (tis)' eller '2022-08-20'
 *
 * @param array $epoch Unix timestamp.
 */
function relative_date($epoch)
{
	$now = time();
	$diff = $epoch - $now;

	$day = 86400; // sekunder på en dag
	$week = 604800; // sekunder på en vecka

	setlocale(LC_TIME, 'sv');
	$day_string = utf8_encode(strftime('%A', $epoch));

	if($diff < $day)
		return 'idag '. date('G:i', $epoch);
	else if($diff < ($day * 2))
		return 'imorgon '. date('G:i', $epoch);
	else if($diff < $week)
		return "på {$day_string}";
	else
		return date('Y-m-d', $epoch);

}
?><div class="signup_box">

	<div class="card border-0 shadow-sm">

		<!-- Header -->
		<div class="card-header bg-dark text-white">
			<?php if(XMAS):?><div class="snow_edge left"></div><div class="snow_pattern"></div><div class="snow_edge right"></div><span class="font-weight-normal">🎄</span>
			<?php elseif(CAKE):?><span class="font-weight-normal">🍰</span><?php endif;?>
			<?php if(EASTER):?><span class="font-weight-normal">🐇</span><?php endif;?>
			Kommande event <?php if(APRIL_FOOLS) echo $this->april_fools->random_emojis(microtime())?>
		</div>

		<!-- Body -->
		<div class="card-body text-center p-3">

			<?php if(isset($next->event_id)):?>

				<!-- titel -->
				<h2><?=isset($next->forum_link) ? "<a href='$next->forum_link'  title='Till briefing' data-toggle='tooltip'>$next->title</a>" : $next->title?></h2>

				<!-- veckodag, datum och tidsspann -->
				<p class="date"><?="$litteral_day, $next->start_date ($next->start_time - $next->end_time)"?></p>

				<!-- antal anmälningar -->
				<p class="count" title="Antal Ja, JIP och QIP-anmälningar" data-toggle="tooltip">
					<a href="<?=base_url("signup/event/$next->event_id")?>">
						<?=$next->signups_count?>
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
						<a class="btn_signup_edit btn btn-primary" href="<?=base_url("signup/event/{$next->event_id}/showform")?>">
							Ändra anmälan <?php if(APRIL_FOOLS) echo $this->april_fools->random_emojis(microtime(), 1)?><i class="fas fa-edit"></i>
						</a>
					<?php elseif($logged_in && $user_is_member):?>
						<a class="btn_signup_new btn btn-success" href="<?=base_url("signup/event/{$next->event_id}/showform")?>">
							Anmäl dig <?php if(APRIL_FOOLS) echo $this->april_fools->random_emojis(microtime(), 1)?><i class="fas fa-chevron-right"></i>
						</a>
					<?php endif;?>

				</div>

				<?php if($logged_in && $user_is_member):?>
					<small><a class="text-dark" href="<?=base_url('signup')?>">Anmäl dig till ett annat event.</a></small>
				<?php endif;?>

				<?php if(isset($other)):?>
					<hr />
					<div class="other_events d-flex flex-column">
						<?php foreach($other as $key=>$event):?>
							<a class="d-flex justify-content-between" href="<?=base_url("signup/event/$event->event_id/showform")?>">

								<!-- Event-titel -->
								<div class="title" title="<?=$event->title?>">
									<?=$event->title?>
								</div>

								<!-- Anmäl-knapp -->
								<div class="d-flex flex-row">
									<div class="date mr-1" title="<?=$event->start_date?>"><?='('. relative_date($event->epoch) .')'?></div>
									<div class="btn btn-sm <?=$event->signed_up ? 'btn-primary' : 'btn-success'?>" <?=$event->signed_up ? 'style="font-size:0.7rem;padding:1px 6px;"' : null?>>
										<?=$event->signed_up
											? '<i class="fas fa-pen"></i>'
											: '<i class="fas fa-chevron-right"></i>'
										?>
									</div>
								</div>

							</a>
						<?php endforeach;?>
					</div>
					
				<?php endif?>

			<?php else:?>

				<strong>Det finns inget framtida event planerat. 😢</strong>

			<?php endif;?>

		</div> <!-- end div.card-body -->

	</div> <!-- end div.card -->

</div> <!-- end #signup_box -->