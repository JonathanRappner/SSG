<?php
/** 
 * Sub-vy för toppraden som innehåller navbar och "inloggad som..."-delarna.
*/
defined('BASEPATH') OR exit('No direct script access allowed');

?><div id="wrapper_top" class="bg-dark mb-3 shadow-sm">

	<div class="container">
		<div class="row">
	
			<!-- Navbar -->
			<nav class="col col-md-9 col-xl-8 px-0 navbar navbar-expand-md navbar-dark text-nowrap">
				
				<!-- Mobil navbar-knapp -->
				<button class="navbar-toggler ml-2" type="button" data-toggle="collapse" data-target="#nav_main" aria-controls="nav_main" aria-expanded="false" aria-label="Toggle navigation">
					<span class="navbar-toggler-icon"></span>
				</button>
			
				<div class="collapse navbar-collapse" id="nav_main">
					<ul class="navbar-nav">
						<li class="nav-item">
							<a class="nav-link" href="<?php echo base_url();?>"><i class="fas fa-home"></i> SSG</a>
						</li>
	
						<li class="nav-item<?php echo $this->current_page == 'events' ? ' active' : null;?>">
							<a class="nav-link" href="<?php echo base_url('signup');?>">Events</a>
						</li>
	
						<li class="nav-item">
							<a class="nav-link" href="<?php echo base_url('debrief');?>">Debriefs</a>
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
				</div>
			</nav>

			<!-- Inloggad som... -->
			<div id="userbox_wrapper" class="col col-md-3 col-xl-4 pr-2 pr-lg-0 pb-2 text-right mt-2">
				<div id="userbox" class="mt-lg-1 mb-2 mb-lg-0">
					<a href="<?=base_url('forum/memberlist.php?mode=viewprofile&u='. $this->member->phpbb_user_id)?>">
						<!-- Namn -->
						<span class="text-nowrap"><strong><?=$this->member->name;?></strong></span>

						<!-- Grad -->
						<?php if(isset($this->member->rank_id)) echo rank_icon($this->member->rank_icon, $this->member->rank_name, true)?>

						<!-- Avatar -->
						<img class='avatar rounded' src='<?=($this->member->avatar_url ? $this->member->avatar_url : base_url('images/unknown.png'))?>' alt='Avatar'>
					</a>
				</div>
			</div>

		</div><!--end div.row-->
	</div><!--end div.container-->
</div><!--end #wrapper_top-->

<?php
//Globala Meddelanden (ex. "100 mb på syncen, tanka nu!")
$this->load->view('site/sub-views/global_alerts', array('global_alerts' => $global_alerts));

//Alerts (ex. "Ändringarna sparades utan problem")
$this->alerts->print_alerts();
?>