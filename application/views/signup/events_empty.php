<?php
/** 
 * Vy som ersätter events-vyn om inga framtida events hittats.
*/
defined('BASEPATH') OR exit('No direct script access allowed');

//variabler
$this->current_page = 'events';

?><!DOCTYPE html>
<html lang="sv">
<head>
	<?php $this->load->view('signup/sub-views/head');?>

	<!-- Page-specific -->
	<link rel="stylesheet" href="<?php echo base_url('css/signup/events.css');?>">

	<title>SSG Anmälning</title>

</head>
<body>


<!-- Huvud-wrapper -->
<div id="wrapper" class="container">

	<!-- Top -->
	<?php $this->load->view('signup/sub-views/top');?>

	<!-- Titel -->
	<h1>SSG Anmälning</h1>
	<p>Det finns inga framtida events planerade. 😢</p>

</div>


<!-- Footer -->
<?php $this->load->view('signup/sub-views/footer');?>

</body>
</html>