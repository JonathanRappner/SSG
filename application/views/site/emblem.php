<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$this->current_page = 'emblem';


?><!DOCTYPE html>
<html lang="sv">
<head>

	<!-- CSS/JS -->
	<?php $this->load->view('site/sub-views/head');?>

	<title>Swedish Strategic Group - Medlemmar</title>

</head>
<body>

<div id="wrapper_members" class="container">

	<!-- Top -->
	<?php $this->load->view('site/sub-views/top');?>

	<!-- Alerts -->
	<?php $this->load->view('site/sub-views/global_alerts', array('global_alerts' => $global_alerts));?>

	<h1>Emblem</h1>

	<p>Fixa ditt ingame-emblem (även kallat XML) här.<br>Inte färdigt ännu!</p>



	<!-- Footer -->
	<?php $this->load->view('site/sub-views/footer');?>

</div>

</body>
</html>