<?php
/** 
 * Utloggningsbekräftan
*/
defined('BASEPATH') OR exit('No direct script access allowed');

//variabler
$this->current_page = 'logout';

?><!DOCTYPE html>
<html lang="sv">
<head>
	<?php $this->load->view('signup/sub-views/head');?>

	<!-- Page-specific -->
	<link rel="stylesheet" href="<?php echo base_url('css/signup/admin.css');?>">

	<title>Logga ut?</title>

</head>
<body>

<div id="wrapper" class="container">

	<!-- Top -->
	<?php $this->load->view('signup/sub-views/top');?>
	
	<div class="row text-center">
		<h5 class="col">Är du säker på att du vill logga ut?</h5>
	</div>

	<div class="row text-center mt-2">
		<div class="col">
			<a href="<?php echo base_url('signup/logout');?>" class="btn btn-success mr-2">Ja</a>
			<a href="<?php echo $this->input->get('redirect');?>" class="btn btn-danger">Nej</a>
		</div>	
	</div>

	<!-- Footer -->
	<?php $this->load->view('signup/sub-views/footer');?>

</div>

</body>
</html>