<?php
/** 
 * Sub-vy för toppraden som innehåller navbar och "inloggad som..."-delarna.
*/
defined('BASEPATH') OR exit('No direct script access allowed');

//moduler
$this->load->library("permissions");

?><div id="wrapper_top" class="bg-dark mb-3 shadow-sm">

	<div class="container">
		<div class="row">
		
			<!-- Navbar -->
			<nav class="col navbar navbar-expand-sm navbar-dark text-nowrap">
				<ul class="navbar-nav">

					<li class="nav-item<?=$this->current_page == 'news' ? ' active' : null?>">
						<a class="nav-link" href="<?=base_url('site/news')?>">Hem</a>
					</li>
					
					<li class="nav-item">
						<a class="nav-link" href="<?=base_url('forum')?>">Forum</a>
					</li>
					
					<?php if($this->member->valid && $this->permissions->has_permissions(array('rekryt', 'medlem', 'inaktiv'))):?>
						<li class="nav-item">
							<a class="nav-link" href="<?=base_url('signup')?>">Events</a>
						</li>
					<?php endif;?>
					
					<li class="nav-item<?=$this->current_page == 'members' ? ' active' : null?>">
						<a class="nav-link" href="<?=base_url('site/members')?>">Medlemmar</a>
					</li>
					
					<li class="nav-item<?=$this->current_page == 'streamers' ? ' active' : null?>">
						<a class="nav-link" href="<?=base_url('site/streamers')?>">Streamers</a>
					</li>
					
					<?php if(false /****diabled*****/ && $this->member->valid && $this->permissions->has_permissions(array('rekryt', 'medlem', 'inaktiv'))):?>
						<li class="nav-item<?=$this->current_page == 'emblem' ? ' active' : null?>">
							<a class="nav-link" href="<?=base_url('site/emblem')?>">Emblem</a>
						</li>
					<?php endif;?>
					
					<?php if($this->member->valid && $this->permissions->has_permissions(array('rekryt', 'medlem', 'inaktiv'))):?>
						<li class="nav-item">
							<a class="nav-link" href="<?=base_url('forum/viewtopic.php?f=3&t=1000')?>">Modline</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" href="<?=base_url('forum/viewtopic.php?f=14&t=626')?>">Server-info</a>
						</li>
					<?php endif;?>
					
					<?php if($this->permissions->has_permissions(array('s0', 's1', 's2', 's3', 's4', 'grpchef'))):?>
					<li class="nav-item">
						<a class="nav-link" href="<?=base_url('signup/admin')?>">Admin</a>
					</li>
					<?php endif;?>
					
					<?php if($this->member->valid):?>
						<li class="nav-item">
							<a class="nav-link" href="<?=base_url('site/logout')?>">Logga ut</a>
						</li>
					<?php endif;?>

				</ul>
			</nav>


			<!-- Inloggning -->
			<div class="col-lg-4 text-xl-right pr-0 pl-sm-4 pl-xl-0 pb-2 pb-xl-0">
				
				<?php if($this->member->valid):?>
					<div id="userbox" class="mt-lg-1 mb-2 mb-lg-0">
						
						<a href="<?=base_url('forum/memberlist.php?mode=viewprofile&u='. $this->member->phpbb_user_id)?>">
							<!-- Namn -->
							<span class="text-nowrap"><strong><?=$this->member->name;?></strong></span>

							<!-- Grad -->
							<?php if(isset($this->member->rank_id)) echo rank_icon($this->member->rank_icon, $this->member->rank_name)?>

							<!-- Avatar -->
							<img class='avatar rounded' src='<?=($this->member->avatar_url ? $this->member->avatar_url : base_url('images/unknown.png'))?>' alt='Avatar'>
						</a>

					</div>
				<?php else:?>
					<div class="mt-2 mb-2 mb-lg-0">
						<a class="btn btn-success" href="<?=base_url('forum/ucp.php?mode=login&redirect=../')?>">Logga in</a>
						<a class="btn btn-primary" href="<?=base_url('forum/ucp.php?mode=register')?>">Registrera dig</a>
					</div>
				<?php endif;?>

			</div><!--end inloggning-->

		</div><!--end div.row-->
	</div><!--end div.container-->

</div> <!--end div.row-->