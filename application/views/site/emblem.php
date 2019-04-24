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

	<h1>Emblem</h1>

	<p>Fixa ditt ingame-emblem här.</p>



	<!-- Footer -->
	<?php $this->load->view('site/sub-views/footer');?>

</div>

</body>
</html>