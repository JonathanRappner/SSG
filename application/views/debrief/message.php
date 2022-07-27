<?php
/** 
 * Vy som listar de senaste events och vilka du har debriefat.
*/
defined('BASEPATH') OR exit('No direct script access allowed');


?><!DOCTYPE html>
<html lang="sv">
<head>
	<?php $this->load->view('debrief/sub-views/head')?>

	<title><?=$title?></title>

</head>
<body>

<!-- Top -->
<?php $this->load->view('debrief/sub-views/top')?>

<!-- Huvud-wrapper -->
<div id="wrapper" class="container p-0">

	<!-- Rubrik -->
	<h2><?=$title?></h2>
	
	<p><?=$message?></p>

</div>


<!-- Footer -->
<?php $this->load->view('debrief/sub-views/footer')?>

</body>
</html>