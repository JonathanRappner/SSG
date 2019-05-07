<?php
/** 
 * Sub-vy för toppraden som innehåller navbar och "inloggad som..."-delarna.
*/
defined('BASEPATH') OR exit('No direct script access allowed');

$this->load->library("permissions");

?><div id="wrapper_top" class="row mb-3 border-bottom">
	
	<!-- Navbar -->
	<nav class="col-sm navbar navbar-expand-sm navbar-light text-nowrap">
		<ul class="navbar-nav">
			
			<li class="nav-item<?=$this->current_page == 'news' ? ' active' : null?>">
				<a class="nav-link" href="<?=base_url()?>">Hem</a>
			</li>
			
			<li class="nav-item">
				<a class="nav-link" href="<?=base_url('forum')?>">Forum</a>
			</li>
			
			<li class="nav-item">
				<a class="nav-link" href="<?=base_url('signup')?>">Anmälning</a>
			</li>
			
			<li class="nav-item">
				<a class="nav-link" href="<?=base_url('site/members')?>">Medlemmar</a>
			</li>
			
			<li class="nav-item<?=$this->current_page == 'streamers' ? ' active' : null?>">
				<a class="nav-link" href="<?=base_url('site/streamers')?>">Streamers</a>
			</li>
			
			<li class="nav-item<?=$this->current_page == 'emblem' ? ' active' : null?>">
				<a class="nav-link" href="<?=base_url('site/emblem')?>">Emblem</a>
			</li>
			
			<?php if($this->permissions->has_permissions(array('s0', 's1', 's2', 's3', 's4', 'grpchef'))):?>
			<li class="nav-item">
				<a class="nav-link" href="<?=base_url('admin')?>">Admin</a>
			</li>
			<?php endif;?>
			
			<?php if($this->member->valid):?>
			<li class="nav-item">
				<a class="nav-link" href="<?=base_url('site/logout')?>">Logga ut</a>
			</li>
			<?php endif;?>

		</ul>
	</nav>

	<?php if($this->pm_count > 0):?>
	<div class="col text-right">
		<a class="pm_alert rounded bg-danger d-inline-block py-2 px-3 mt-2" href="<?=base_url('forum/ucp.php?i=pm&folder=inbox')?>">
			<i class="fas fa-envelope mr-2"></i>
			Du har <strong><?=$this->pm_count?></strong> <?php echo $this->pm_count == 1 ? 'oläst meddelande' : 'olästa meddelanden';?>
		</a>
	</div>
	<?php endif;?>

	<!-- Inloggad som... -->
	<div class="col-3-sm text-sm-right my-1">
		
		<?php if($this->member->valid):?>
		<div id="userbox">
			<span class="d-none d-md-inline text-nowrap"><strong><?=$this->member->name;?></strong></span>
			<?php
			//grad-ikon
			if(isset($this->member->rank_id))
				echo '<img class="rank_icon d-none d-md-inline" src="'. base_url('images/rank_icons/'. $this->member->rank_icon) .'" title="'. $this->member->rank_name .'" data-toggle="tooltip" />';

			//avatar
			$avatar = !empty($this->member->avatar_url)
				? $this->member->avatar_url
				: base_url('images/unknown.png');
			echo "<img class='avatar rounded' src='$avatar' alt='Avatar'>"
			?>
			<p class="d-inline d-sm-none ml-2 text-nowrap"><strong><?=$this->member->name;?></strong></p>
		</div>
		<?php else:?>
			<div class="mt-2">
				<button class="btn btn-success" data-toggle="modal" data-target="#login_form">Logga in</button>
				<a class="btn btn-primary" href="<?=base_url('forum/ucp.php?mode=register');?>">Registrera dig</a>
			</div>
		<?php endif;?>

	</div>

</div>

<?php
//Alerts
$this->alerts->print_alerts();

//Login form
if(!$this->member->valid)
	$this->load->view('site/sub-views/login_form');
?>