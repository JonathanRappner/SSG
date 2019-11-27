<?php
/** 
 * Sub-vy för toppraden som innehåller navbar och "inloggad som..."-delarna.
*/
defined('BASEPATH') OR exit('No direct script access allowed');

?><div id="wrapper_top" class="bg-dark mb-3 shadow-sm">

	<div class="container">
		<div class="row">
	
			<!-- Navbar -->
			<nav class="col navbar navbar-expand-sm navbar-dark text-nowrap">
				<ul class="navbar-nav">
					<li class="nav-item">
						<a class="nav-link" href="<?php echo base_url();?>"><i class="fas fa-home"></i> SSG</a>
					</li>

					<li class="nav-item<?php echo $this->current_page == 'events' ? ' active' : null;?>">
						<a class="nav-link" href="<?php echo base_url('signup');?>">Events</a>
					</li>

					<li class="nav-item<?php echo $this->current_page == 'history' ? ' active' : null;?>">
						<a class="nav-link" href="<?php echo base_url('signup/history');?>">Historik</a>
					</li>

					<li class="nav-item<?php echo $this->current_page == 'mypage' ? ' active' : null;?>">
						<a class="nav-link" href="<?php echo base_url('signup/mypage');?>">Min sida</a>
					</li>
					
					<?php if($this->permissions->has_permissions(array('s0', 's1', 's2',  's3', 's4', 'grpchef'))):?>
						<li class="nav-item<?php echo $this->current_page == 'admin' ? ' active' : null;?>">
							<a class="nav-link" href="<?php echo base_url('signup/admin');?>">Admin</a>
						</li>
					<?php endif;?>

					<li class="nav-item">
						<a class="nav-link" href="<?php echo base_url('site/logout'); ?>">Logga ut</a>
					</li>
				</ul>
			</nav>

			<!-- Inloggad som... -->
			<div class="col-md-4 text-md-right pr-0 pl-sm-4 pl-xl-0 pb-2 pb-xl-0">
				<div id="userbox" class="mt-md-1 mb-2 mb-md-0">
					<a href="<?=base_url('forum/memberlist.php?mode=viewprofile&u='. $this->member->phpbb_user_id)?>">
						<!-- Namn -->
						<span class="text-nowrap"><strong><?=$this->member->name;?></strong></span>

						<!-- Grad -->
						<?php if(isset($this->member->rank_id)) echo rank_icon($this->member->rank_icon, $this->member->rank_name)?>

						<!-- Avatar -->
						<img class='avatar rounded' src='<?=($this->member->avatar_url ? $this->member->avatar_url : base_url('images/unknown.png'))?>' alt='Avatar'>
					</a>
				</div>
			</div>

		</div><!--end div.row-->
	</div><!--end div.container-->
</div>

<?php
//Alerts
$this->alerts->print_alerts();
?>