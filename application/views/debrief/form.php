<?php
/** 
 * Vy som listar de senaste events och vilka du har debriefat.
*/
defined('BASEPATH') OR exit('No direct script access allowed');


?><!DOCTYPE html>
<html lang="sv">
<head>
	<?php $this->load->view('debrief/sub-views/head')?>

	<!-- Page-specific -->
	<!-- <script src="<xxx?=base_url('js/deadline.js')?>"></script> -->
	<!-- <link rel="stylesheet" href="<xxx?=base_url('css/signup/events.css?3')?>"> -->

	<title>SSG Debrief - >>>>event titel formulär<<<<</title>

</head>
<body>

<!-- Top -->
<?php $this->load->view('debrief/sub-views/top')?>

<!-- Huvud-wrapper -->
<div id="wrapper" class="container p-0">

	<!-- Rubrik -->
	<h2>Debrief</h2>

	<?php if(!$member_id):?>
		<p>debrief-formulär för event: <?=$event_id?>, för dig själv</p>
	<?php else:?>
		<p>debrief-formulär för event: <?=$event_id?>, för medlem: <?=$member_id?></p>
	<?php endif;?>

</div>


<!-- Footer -->
<?php $this->load->view('debrief/sub-views/footer')?>

</body>
</html>