<?php
/** 
 * Viktiga meddelanden-raden
*/
defined('BASEPATH') OR exit('No direct script access allowed');

//variabler

?><?php if($this->member->valid && count($alerts) > 0):?>
<div id="alerts" class="row mb-2">

	<?php foreach($alerts as $alert):?>
	<div class="alert alert-<?=$alert->class?> col-12 alert-dismissible fade show">
		<?=$alert->text?>
		<button type="button" class="close" data-dismiss="alert" aria-label="Close">
			<span aria-hidden="true">&times;</span>
		</button>
	</div>
	<?php endforeach;?>

</div>
<?php endif;?>