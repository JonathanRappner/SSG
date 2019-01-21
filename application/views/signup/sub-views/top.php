<?php
/** 
 * Sub-vy för toppraden som innehåller navbar och "inloggad som..."-delarna.
*/
defined('BASEPATH') OR exit('No direct script access allowed');

?><div id="wrapper_top" class="row mb-3 border-bottom">
	
	<!-- Navbar -->
	<nav class="col-sm navbar navbar-expand-sm navbar-light text-nowrap">
		<ul class="navbar-nav">
			<li class="nav-item">
				<a class="nav-link" href="<?php echo base_url('..');?>"><i class="fas fa-chevron-circle-left"></i></i> SSG</a>
			</li>

			<li class="nav-item<?php echo $this->current_page == 'events' ? ' active' : null;?>">
				<a class="nav-link" href="<?php echo base_url();?>">Events</a>
			</li>

			<li class="nav-item<?php echo $this->current_page == 'strolir' ? ' active' : null;?>">
				<a class="nav-link" href="<?php echo base_url('signup/strolir');?>">Strölir</a>
			</li>

			<li class="nav-item<?php echo $this->current_page == 'history' ? ' active' : null;?>">
				<a class="nav-link" href="<?php echo base_url('signup/history');?>">Historik</a>
			</li>

			<li class="nav-item<?php echo $this->current_page == 'mypage' ? ' active' : null;?>">
				<a class="nav-link" href="<?php echo base_url('signup/mypage');?>">Min sida</a>
			</li>
			
			<?php if($this->permissions->has_permissions(array('super', 's0', 's1', 's2',  's3', 's4', 'grpchef'))):?>
				<li class="nav-item<?php echo $this->current_page == 'admin' ? ' active' : null;?>">
					<a class="nav-link" href="<?php echo base_url('signup/admin');?>">Admin</a>
				</li>
			<?php endif;?>

			<li class="nav-item">
				<a class="nav-link" href="<?php echo base_url('signup/logout_confirm?redirect='. current_url()); ?>">Logga ut</a>
			</li>
		</ul>
	</nav>

	<!-- Inloggad som... -->
	<div class="col-sm text-sm-right my-1">
		<div id="userbox">
			<span class="d-none d-md-inline text-nowrap"><strong><?php echo $this->member->name;?></strong></span>
			<?php
			//grad-ikon
			if($this->member->rank_id != null)
				echo '<img class="rank_icon" src="'. base_url('images/rank_icons/'. $this->member->rank_icon) .'" title="'. $this->member->rank_name .'" data-toggle="tooltip" />';

			//avatar
			$avatar = !empty($this->member->avatar_url)
				? $this->member->avatar_url
				: base_url('images/unknown.png');
			echo "<img class='avatar rounded' src='$avatar' alt='Avatar'>"
			?>
			<p class="d-inline d-sm-none ml-2 text-nowrap"><strong><?php echo $this->member->name;?></strong></p>
		</div>
	</div>

</div>

<?php
//Alerts
$this->alerts->print_alerts();
?>