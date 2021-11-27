<?php
/** 
 * Vy för admin-sidan.
*/
defined('BASEPATH') OR exit('No direct script access allowed');

//variabler
$this->current_page = 'admin';

?><!DOCTYPE html>
<html lang="sv">
<head>
	<?php $this->load->view('signup/sub-views/head');?>

	<!-- Page-specific -->
	<link rel="stylesheet" href="<?php echo base_url('css/signup/admin.css');?>">
	<script src="<?php echo base_url('js/signup/clickable_table.js');?>"></script>

	<!-- Misc -->
	<title>Admin - <?php echo $adminpanel->get_title();?></title>

</head>
<body>

<!-- Topp -->
<?php $this->load->view('signup/sub-views/top');?>

<div id="wrapper" class="container p-0">

	<h1>Admin</h1>

	<div class="row">
		<div class="col-sm-2">
			<nav class="navbar navbar-light">
				<ul class="navbar-nav">
					<?php
						foreach($adminpanels as $panel)
						{
							echo '<li class="nav-item'. ($panel->get_code() == $adminpanel->get_code() ? ' active' : null) .'">';

							if($this->permissions->has_permissions($panel->get_permissions_needed()))
								echo '<a href="'. base_url('signup/admin/'. $panel->get_code()) .'" class="nav-link">'. $panel->get_title() .'</a>';
							else
								echo '<span class="nav-link disabled">'. $panel->get_title() .'</span>';

							echo '</li>';
						}
					?>
				</ul>
			</nav>
		</div>
		<div class="col-sm">
			<?php 
			echo '<h4>'. $adminpanel->get_title() .'</h4>';
			$adminpanel->view();
			?>
		</div>
	</div>
	
	

	<!-- Footer -->
	<?php $this->load->view('signup/sub-views/footer');?>

</div>

</body>
</html>