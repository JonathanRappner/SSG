<?php
defined('BASEPATH') OR exit('No direct script access allowed');


?><!DOCTYPE html>
<html lang="sv">
<head>
	<?php $this->load->view('signup/sub-views/head');?>

	<title>Debrief - Inloggning</title>

</head>
<body>

<div id="wrapper_login" class="container">

	<h1 class="mt-4">Inloggning</h1>

	<p>För att komma åt debriefing:ar måste du vara registrerad på vårt forum.</p>
	<p>Du måste vara antagen som rekryt, medlem eller inaktiv medlem.</p>

	<p>
		<a class="btn btn-primary" href="<?=base_url()?>">Tillbaka</a>
		<a class="btn btn-success" href="<?=base_url('forum/ucp.php?mode=login&redirect=../debrief')?>">Logga in</a>
	</p>

	<!-- Footer -->
	<?php $this->load->view('signup/sub-views/footer');?>

</div>

</body>
</html>