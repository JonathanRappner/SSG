<?php
defined('BASEPATH') OR exit('No direct script access allowed');

?><!DOCTYPE html>
<html lang="sv">
<head>

	<title>Swedish Strategic Group - Karusell</title>

</head>
<body>

<div class="row">

	<?php foreach($carousel_images as $img):?>
		<div class="col">
			<img src="<?=base_url($img)?>" alt="">
		</div>
	<?php endforeach;?>

</div>

</body>
</html>