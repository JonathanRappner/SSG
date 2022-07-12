<?php
/** 
 * Vy som listar nästkommande samt senare events.
*/
defined('BASEPATH') OR exit('No direct script access allowed');

//variabler
$this->current_page = 'events';

?><!DOCTYPE html>
<html lang="sv">
<head>
	<?php $this->load->view('signup/sub-views/head')?>

	<!-- Page-specific -->
	<script src="<?=base_url('js/deadline.js')?>"></script>
	<script src="<?=base_url('js/signup/events.js')?>"></script>
	<script src="<?=base_url('js/signup/clickable_table.js')?>"></script>
	<link rel="stylesheet" href="<?=base_url('css/signup/events.css?3')?>">

	<title>SSG Anmälning</title>

</head>
<body>

<!-- Top -->
<?php $this->load->view('signup/sub-views/top')?>

<!-- Huvud-wrapper -->
<div id="wrapper" class="container p-0">

	<!-- Rubrik -->
	<h2>Nästa event:</h2>

	<!-- Nästa event -->
	<div id="wrapper_next_event" class="card mb-4 bg-white border-0 shadow-sm<?=empty($next_event->preview_image) ? ' next_event_no_img' : null?>">
		
			<h3 class="card-header bg-dark text-white">
				<?php if(XMAS):?><div class="snow_edge left"></div><div class="snow_pattern"></div><div class="snow_edge right"></div>⛄<?php endif;?>
				<?php if(EASTER):?>🐣<?php endif;?>
				<a href="<?=base_url("signup/event/{$next_event->id}")?>">
					<?=$next_event->title?>
					<?php if(!empty($next_event->author_id)):?>
						<small class="text-nowrap"><?="av $next_event->author_name"?></small>
					<?php endif;?>
				</a>
			</h3>
				
			<div class="card-body">
				<div class="row">
					<div class="col-lg">

						<dl class="row">
							<script>var deadline_epoch = <?=$next_event->deadline_epoch?>;</script>
							<dt class="col-5">Datum:</dt>
							<dd class="col-7"><?="{$next_event->start_date} ({$next_event->start_time} - {$next_event->end_time})"?></dd>
		
							<dt class="col-5">Närvaro:</dt>
							<dd class="col-7"><span class="<?=$next_event->member_attendance->class?>"><?=$next_event->member_attendance->text?></span></dd>

							<dt class="col-5">Obligatoriskt:</dt>
							<dd class="col-7" id="obligatory"><?=$next_event->obligatory ? '<span class="text-success font-weight-bold">Ja</span>' : '<span class="text-danger font-weight-bold">Nej</span>'?></dd>

							<dt class="col-5">Anmälnings-deadline:</dt>
							<dd class="col-7" id="deadline">&nbsp;</dd>

							<dt class="col-5">Antal anmälda:</dt>
							<dd class="col-7"><?=$this->attendance->count_signed($next_event->signups)?></dd>
						</dl>
					
						<!-- Anmäl/Redigera anmälan -->
						<?php
							echo $next_event->member_attendance->id != $this->attendance->get_type_by_code('notsigned')->id //anmälan finns redan
								? '<a href="'. base_url("signup/event/{$next_event->id}/showform") .'" id="btn_edit" class="btn btn-primary">Redigera anmälan <i class="fas fa-edit"></i></a>'
								: '<a href="'. base_url("signup/event/{$next_event->id}/showform") .'" id="btn_signup" class="btn btn-success">Anmäl dig <i class="fas fa-arrow-circle-right"></i></a>';
						?>
				
						<!-- Se anmälningar & Läs mer -->
						<div>
							<a href="<?=base_url('signup/event/'. $next_event->id)?>" class="btn btn-secondary">
								Se anmälningar
								<i class="fas fa-list-ul"></i>
							</a>

							<?php if(!empty($next_event->forum_link)):?>
								<!-- Läs mer -->
								<a href='<?=$next_event->forum_link?>' class='btn btn-info'>Läs mer <i class='fas fa-search'></i></a>
							<?php endif?>
						</div>
					</div><!-- end div.col (vänsterkolumn) -->
		
					<!-- Förhandsbild-kolumn -->
					<?php if(!empty($next_event->preview_image)): ?>
						<div class="col-lg">
							<?php if(preg_match('/(\.mp4)$/i', $next_event->preview_image)): // mp4-video?>
								<video class="img-thumbnail rounded float-right m-2" autoplay loop muted><source src="<?=$next_event->preview_image?>" type="video/mp4"></video>
							<?php else: // vanlig bild?>
								<a href="<?=$next_event->preview_image?>" data-toggle="lightbox">
									<img class="img-thumbnail rounded float-right m-2" src="<?=$next_event->preview_image?>" alt="Förhandsbild">
								</a>
							<?php endif;?>	
						</div><!-- end div.col (högerkolumn) -->
					<?php endif?>
				</div><!-- end div.row -->

			</div><!-- end div.card-body -->

	</div><!-- end #wrapper_next_event -->

	
	<!--Andra events-->
	<div id="wrapper_events_table" class="card bg-white border-0 shadow-sm">
		
		<h4 class="card-header bg-dark text-white">
			<?php if(XMAS):?><div class="snow_edge left"></div><div class="snow_pattern"></div><div class="snow_edge right"></div>🎅<?php endif;?>
			<?php if(EASTER):?>🐇<?php endif;?>
			Andra events
		</h4>

		<div class="card-body p-2 table-responsive table-sm">
			<table class="table table-hover clickable">
				<thead class="table-borderless">
					<tr>
						<th scope="col">Titel</th>
						<th scope="col">Typ</th>
						<th scope="col">Datum</th>
						<th scope="col">Anmälda</th>
						<th scope="col" class="text-nowrap">Din närvaro</th>
					</tr>
				</thead>
				<tbody>

					<?php
					//andra events-tabell-rader
					foreach($upcoming_events as $event)
					{
						//variabler
						$att = $event->current_member_attendance; //närvaro-objekt
						$signed_count = $this->attendance->count_signed($event->signups); //antal anmälda

						echo '<tr data-url="'. base_url("signup/event/$event->id") .'">';
						echo "	<th scope=\"row\">$event->title</th>"; //titel
						echo "	<td>$event->type_name</td>"; //typ
						echo "	<td><abbr title=\"$event->start_time - $event->end_time\" data-toggle=\"tooltip\">$event->start_date</abbr></td>"; //datum
						echo "	<td>$signed_count</td>"; //anmälda
						echo "	<td><span class=\"$att->class\">$att->text</span></td>"; //din anmälan
						echo '</tr>';
					}
					?>
				</tbody>
			</table>
		</div><!-- end div.card-body -->

	</div><!-- end div.card -->

</div>


<!-- Footer -->
<?php $this->load->view('signup/sub-views/footer')?>

</body>
</html>