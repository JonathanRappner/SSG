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
	<link rel="stylesheet" href="<?=base_url('css/signup/events.css')?>">

	<title>SSG Anmälning</title>

</head>
<body>


<!-- Huvud-wrapper -->
<div id="wrapper" class="container">

	<!-- Top -->
	<?php $this->load->view('signup/sub-views/top')?>

	<!-- Global Alerts -->
	<?php $this->load->view('site/sub-views/global_alerts', array('global_alerts' => $global_alerts))?>

	<!-- Rubrik + frianmälan -->
	<div class="row mt-4 mb-2">

		<!-- Titel -->
		<div class="col-sm">
			<h1 class="mt-1">SSG Anmälning</h1>
		</div>

	</div>
	

	<!-- Nästa event -->
	<h3>Nästa event:</h3>
	<div id="wrapper_next_event" class="container border rounded p-4 mb-4<?=empty($next_event->preview_image) ? ' next_event_no_img' : null?>">

		<input id="deadline_epoch" value="<?=$next_event->deadline_epoch?>" type="hidden">
		
		<div class="row">

			<div class="col">
				<h4>
					<?=$next_event->title?>
					<?php if(!empty($next_event->author_id)): ?>
					<small class="text-muted text-nowrap"><?="av $next_event->author_name"?></small>
					<?php endif; ?>
				</h4>
				
				<p>
					<strong>Datum:</strong>
					<?="$next_event->start_date ($next_event->start_time - $next_event->end_time)"?>
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
				<div class="mb-2">
					<a href="<?=base_url('signup/event/'. $next_event->id)?>" class="btn btn-primary">Se anmälningar <i class="fas fa-list-ul"></i></i></a>
					<?php
						echo $next_event->member_attendance->id != $this->attendance->get_type_by_code('notsigned')->id //anmälan finns redan
							? '<a href="'. base_url("signup/event/$next_event->id/showform") .'" class="btn btn-primary">Redigera anmälan <i class="fas fa-edit"></i></a>'
							: '<a href="'. base_url("signup/event/$next_event->id/showform") .'" class="btn btn-success">Anmäl dig <i class="fas fa-arrow-circle-right"></i></a>';
					?>
				</div>

				<?php if(!empty($next_event->forum_link)):?>
					<!-- Läs mer -->
					<div><a href='<?=$next_event->forum_link?>' class='btn btn-primary'>Läs mer <i class='fas fa-search'></i></a></div>
				<?php endif?>

			</div>

			<!-- Förhandsbild-kolumn -->
			<?php if(!empty($next_event->preview_image)): ?>
			<div class="col-md-7">
				<a href="<?=$next_event->preview_image?>" data-toggle="lightbox">
					<img class="img-thumbnail rounded float-right m-2" src="<?=$next_event->preview_image?>" alt="Förhandsbild">
				</a>
			</div>
			<?php endif; ?>

		</div>
	</div>

	
	<!--Events-->
	<h3>Andra events:</h3>
	<div id="wrapper_events_table" class="table-responsive table-sm">
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
	</div>

</div>


<!-- Footer -->
<?php $this->load->view('signup/sub-views/footer')?>

</body>
</html>