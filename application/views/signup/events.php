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
	<link rel="stylesheet" href="<?=base_url('css/signup/events.css?0')?>">

	<?php if(XMAS):?>
		<link rel="stylesheet" href="<?=base_url('css/holidays/xmas.css')?>">
	<?php endif;?>

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
				<?php if(XMAS):?><div class="snow_edge left"></div><div class="snow_pattern"></div><div class="snow_edge right"></div>🎄<?php endif;?>
				<a href="<?=base_url("signup/event/{$next_event->id}")?>">
					<?=$next_event->title?>
					<?php if(!empty($next_event->author_id)):?>
						<small class="text-nowrap"><?="av $next_event->author_name"?></small>
					<?php endif;?>
				</a>
			</h3>
				
			<div class="card-body row">
				<div class="col">
					<p>
						<script>var deadline_epoch = <?=$next_event->deadline_epoch?>;</script>
						<strong>Datum:</strong>
						<?="{$next_event->start_date} ({$next_event->start_time} - {$next_event->end_time})"?>
					</p>
					
					<p>
						<strong>Närvaro:</strong>
						<span class="<?=$next_event->member_attendance->class?>"><?=$next_event->member_attendance->text?></span>
					</p>
					
					<p>
						<strong>Anmälnings-deadline:</strong>
						<span id='deadline' class="text-nowrap">&nbsp;</span>
					</p>
					
					<p>
						<strong>Antal anmälda:</strong>
						<?=$this->attendance->count_signed($next_event->signups)?>
					</p>
				
					<!-- Se anmälningar & Anmäl/Redigera anmälan -->
					<div>
						<a href="<?=base_url('signup/event/'. $next_event->id)?>" class="btn btn-primary">Se anmälningar <i class="fas fa-list-ul"></i></i></a>
						<?php
							echo $next_event->member_attendance->id != $this->attendance->get_type_by_code('notsigned')->id //anmälan finns redan
								? '<a href="'. base_url("signup/event/{$next_event->id}/showform") .'" class="btn btn-primary">Redigera anmälan <i class="fas fa-edit"></i></a>'
								: '<a href="'. base_url("signup/event/{$next_event->id}/showform") .'" class="btn btn-success">Anmäl dig <i class="fas fa-arrow-circle-right"></i></a>';
						?>
					</div>
			
					<?php if(!empty($next_event->forum_link)):?>
						<!-- Läs mer -->
						<div><a href='<?=$next_event->forum_link?>' class='btn btn-primary'>Läs mer <i class='fas fa-search'></i></a></div>
					<?php endif?>
				</div><!-- end div.col (vänsterkolumn) -->
	
				<!-- Förhandsbild-kolumn -->
				<?php if(!empty($next_event->preview_image)): ?>
					<div class="col-12 col-md-7">
						<a href="<?=$next_event->preview_image?>" data-toggle="lightbox">
							<img class="img-thumbnail rounded float-right m-2" src="<?=$next_event->preview_image?>" alt="Förhandsbild">
						</a>
					</div><!-- end div.col (högerkolumn) -->
				<?php endif;?>

			</div><!-- end div.card-body -->

	</div><!-- end #wrapper_next_event -->

	
	<!--Andra events-->
	<div id="wrapper_events_table" class="card bg-white border-0 shadow-sm">
		
		<h4 class="card-header bg-dark text-white">
			<?php if(XMAS):?><div class="snow_edge left"></div><div class="snow_pattern"></div><div class="snow_edge right"></div>🎅<?php endif;?>
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