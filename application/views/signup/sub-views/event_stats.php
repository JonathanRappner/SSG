<?php
/** 
 * Sub-vy event-statistik-rutan.
*/
defined('BASEPATH') OR exit('No direct script access allowed');

assert(isset($stats), '$stats är tom');
assert(isset($obligatory), '$obligatory är tom');
assert(isset($is_old), '$is_old är tom');

//variabler
$stats->signed_percent = $stats->signed > 0 //signed
	? round(($stats->signed/$stats->total)*100)
	: 0;
$stats->jipqip_percent = $stats->jipqip > 0 //jipqip
	? round(($stats->jipqip/$stats->total)*100)
	: 0;
$stats->noshow_percent = $stats->noshow > 0 //noshow
	? round(($stats->noshow/$stats->total)*100)
	: 0;

$last_changed_members_string = null;
if(!empty($stats->last_changed))
	foreach($stats->last_changed as $member)
		$last_changed_members_string .= '<span class="text-nowrap">'. $member->name .': ('. $member->date .')</span><br />';

?><div class="row">
		
	<h4 class="col-12">Statistik</h4>
	
	<div class="col-sm">
		
		<dl>
			<!-- Totalt -->
			<dt>Totalt:</dt>
			<dd><?php echo $stats->total;?></dd>

			<!-- Ja -->
			<dt>Ja:</dt>
			<dd><?php echo "$stats->signed ($stats->signed_percent%)";?></dd>
			
			<dt>JIPs & QIPs:</dt>
			<dd><?php echo "$stats->jipqip ($stats->jipqip_percent%)";?></dd>
			
			<dt>NOSHOWs:</dt>
			<dd><?php echo "$stats->noshow ($stats->noshow_percent%)";?></dd>
			
			<dt title="Senaste anmälan som skickades in eller ändrades." data-toggle="tooltip">Senaste anmälan:</dt>
			<dd>
				<?php
					if(!empty($stats->last_changed))
					{
						echo "<abbr title='$last_changed_members_string' data-toggle='tooltip' data-html='true'>";
						echo current($stats->last_changed)->name .': '. current($stats->last_changed)->date;
						echo '</abbr>';
					}
				?>
			</dd>
			<?php if($obligatory && !$is_old):?>
				<dt title="Antal aktiva medlemmar som inte anmält sig än." data-toggle="tooltip">Oanmälda medlemmar:</dt>
				<dd><?php echo $non_signed;?></dd>
			<?php endif;?>
		</dl>
	</div>

	<div class="col-sm">
		<dl>
			<?php foreach($stats->groups as $group):?>
				<dt>
					<?php echo group_icon($group->code);?>
					<?php echo $group->name;?>:
				</dt>
				<dd>
					<?php
						echo $group->signed;
						if($group->jipqip > 0)
							echo ' <abbr title="JIPs och QIPs" data-toggle="tooltip">(+'. $group->jipqip .')</abbr>';
					?>
				</dd>
			<?php endforeach;?>
		</dl>
	</div>
	
	
	
	

</div>