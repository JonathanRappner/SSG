<?php
defined('BASEPATH') OR exit('No direct script access allowed');


?><!DOCTYPE html>
<html lang="sv">
<head>
	<?php $this->load->view('signup/sub-views/head');?>

	<!-- Page-specific -->
	<link rel="stylesheet" href="<?php echo base_url('css/signup/login.css');?>">

	<title>Inloggning</title>

</head>
<body>

<div id="wrapper_login" class="container">

	<h1 class="mt-4">Inloggning</h1>

	<?php if(isset($fail)) : ?>
	<div class="alert alert-danger" role="alert">
		Felaktiga inloggningsuppgifter.
	</div>
	<?php endif; ?>

	<p>Logga in här med samma användarnamn och lösenord du använder på huvudsidan.</p>
	<p>Om du inte redan är medlem i SSG kan du registrera dig <a href="<?php echo base_url('../?action=register');?>">Här</a>.</p>

	<form method="post" action="<?php echo base_url('/signup/login');?>">
		<input type="hidden" name="redirect" value="<?php echo current_url();?>">
		<div class="form-group">
			<label for="input_username">Användarnamn</label>
			<input type="text" class="form-control" name="username" id="input_username">
		</div>
		<div class="form-group">
			<label for="input_password">Lösenord</label>
			<input type="password" class="form-control" name="password" id="input_password">
		</div>
		<button type="submit" class="btn btn-primary">Logga in <i class="fas fa-arrow-circle-right"></i></button>
	</form>

	<!-- Footer -->
	<?php $this->load->view('signup/sub-views/footer');?>

</div>

</body>
</html>