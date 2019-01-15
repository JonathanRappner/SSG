<?php
/**
 * Vi för Frianmälan.
 * Laddar själva formuläret rån sub-views/form.php
 */
defined('BASEPATH') OR exit('No direct script access allowed');

//variabler
$this->current_page = 'form';
$this->member_not_signed = empty($signup);
$this->preselects = $this->member_not_signed ? $this->Signups->get_preselects($member->id) : null;

?><!DOCTYPE html>
<html lang="sv">
<head>
	<?php $this->load->view('signup/sub-views/head');?>

	<!-- Page-specific -->
	<link rel="stylesheet" href="<?php echo base_url('css/signup/form.css');?>">

	<title><?php echo $this->member_not_signed ? 'Ny anmälan' : 'Redigera anmälan';?></title>

</head>
<body>

<div id="wrapper_form_main" class="container">
	
	<!-- Top -->
	<?php $this->load->view('signup/sub-views/top');?>


	<div id="wrapper_form_inner">
	
		<!-- Heading -->
		<div>
			<?php if(!$this->member_not_signed):?>
				<h5>Redigera anmälan till:</h5>
				<h4>
					<?php echo "$event->title";?>
					<small class="text-muted text-nowrap"><?php echo "($event->start_date)";?></small>
				</h4>
			<?php else:?>
				<h4>Ny anmälan</h4>
			<?php endif;?>
		</div>
	
		<!-- Formulär -->
		<?php $this->load->view('signup/sub-views/form', array('event' => $event, 'events' => $events, 'signup' => $signup));?>
		
	</div>
	
</div>


<!-- Footer -->
<?php $this->load->view('signup/sub-views/footer');?>

</body>
</html>