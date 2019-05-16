<?php
/** 
 * Viktiga meddelanden-raden
*/
defined('BASEPATH') OR exit('No direct script access allowed');

//variabler

?><div id="alerts" class="row">

	<?php if($this->member->valid && false/******************/): foreach($alerts as $alert):?>
	<div class="alert alert-<?=$alert->class?> col-12 alert-dismissible fade show">
		<?=$alert->text?>
		<button type="button" class="close" data-dismiss="alert" aria-label="Close">
			<span aria-hidden="true">&times;</span>
		</button>
	</div>
	<?php endforeach; endif;?>

</div>