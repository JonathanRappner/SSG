<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$this->current_page = 'members';


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

	<h1>Medlemmar</h1>

	<p>SSG:s medlemmar</p>



	<!-- Footer -->
	<?php $this->load->view('site/sub-views/footer');?>

</div>

</body>
</html>