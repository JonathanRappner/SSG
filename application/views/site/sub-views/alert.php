<?php
/** 
 * Viktiga meddelanden-raden
*/
defined('BASEPATH') OR exit('No direct script access allowed');

//variabler

?><div id="alerts" class="row">

	<?php if($this->member->valid && false):?>
	<!-- Meddelanden -->
	<div class="col-12 rounded bg-danger text-light mb-2">
		Viktigt meddelande!xxxx
	</div>
	<div class="col-12 rounded bg-info text-light mb-2">
		Inte lika viktigt meddelande.
	</div>
	<?php endif;?>

</div>