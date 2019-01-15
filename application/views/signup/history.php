<?php
/** 
 * Vy för Min statistik-sidan.
*/
defined('BASEPATH') OR exit('No direct script access allowed');

//variabler
$this->current_page = 'history';

?><!DOCTYPE html>
<html lang="sv">
<head>
	<?php $this->load->view('signup/sub-views/head');?>

	<!-- Page-specific -->
	<link rel="stylesheet" href="<?php echo base_url('css/signup/admin.css');?>">

	<title>Historik</title>

</head>
<body>

<div id="wrapper" class="container">

	<!-- Top -->
	<?php $this->load->view('signup/sub-views/top');?>

	<h1>Historik</h1>
	<div class="alert alert-info alert-dismissible fade show" role="alert">
		Denna undersida är inte implementerad ännu.
	</div>
	<p>Här kommer du kunna se detaljer och anmälningar för äldre events.</p>

	<!-- Footer -->
	<?php $this->load->view('signup/sub-views/footer');?>

</div>

</body>
</html>