<?php
defined('BASEPATH') OR exit('No direct script access allowed');


?><!DOCTYPE html>
<html lang="sv">
<head>
	<?php $this->load->view('signup/sub-views/head');?>

	<title>Events</title>

</head>
<body>

<div id="wrapper_login" class="container">

	<h1 class="mt-4">Inloggning</h1>

	<p>För att komma åt events och se dina anmälningar måste du vara antagen som rekryt, medlem eller inaktiv medlem.</p>
	<p>Om du vill ansöka om att gå med i klanen gör du det <a href="<?=base_url('forum/viewforum.php?f=4')?>">Här</a>.</p>

	<p>
		<a class="btn btn-primary" href="<?=base_url()?>">Tillbaka</a>
	</p>

	<!-- Footer -->
	<?php $this->load->view('signup/sub-views/footer');?>

</div>

</body>
</html>