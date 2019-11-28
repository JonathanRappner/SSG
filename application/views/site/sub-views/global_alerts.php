<?php
/** 
 * Viktiga meddelanden-raden
*/
defined('BASEPATH') OR exit('No direct script access allowed');

if($this->member->valid && count($global_alerts) > 0):?>
<div id="global_alerts" class="container p-0">

	<?php foreach($global_alerts as $alert):?>
		<?php if(empty($this->session->dismissed_global_alerts) || !in_array($alert->id, $this->session->dismissed_global_alerts)): //visa inte dismiss:ade alerts?>
			<div class="alert alert-<?=$alert->class?> mb-2 col-12 alert-dismissible fade show" data-id="<?=$alert->id?>">
				<?=$alert->text?>
				<button type="button" class="close" data-dismiss="alert" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
		<?php endif;?>
	<?php endforeach;?>

</div>
<?php endif;?>