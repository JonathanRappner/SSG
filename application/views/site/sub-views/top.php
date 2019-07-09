<?php
/** 
 * Sub-vy för toppraden som innehåller navbar och "inloggad som..."-delarna.
*/
defined('BASEPATH') OR exit('No direct script access allowed');

//moduler
$this->load->library("permissions");

?><div id="wrapper_top" class="row border-bottom">
	
	<!-- Navbar -->
	<nav class="col navbar navbar-expand-sm navbar-light text-nowrap">
		<ul class="navbar-nav">

			<!-- <a class="navbar-brand" href="<?=base_url()?>">
				<img src="<?=base_url('images/logga.svg')?>" height="20" alt="Logo">
			</a> -->

			<li class="nav-item<?=$this->current_page == 'news' ? ' active' : null?>">
				<a class="nav-link" href="<?=base_url('site/news')?>">Hem</a>
			</li>
			
			<li class="nav-item">
				<a class="nav-link" href="<?=base_url('forum')?>">Forum</a>
			</li>
			
			<li class="nav-item">
				<a class="nav-link" href="<?=base_url('signup')?>">Events</a>
			</li>
			
			<li class="nav-item">
				<a class="nav-link" href="<?=base_url('site/members')?>">Medlemmar</a>
			</li>
			
			<li class="nav-item<?=$this->current_page == 'streamers' ? ' active' : null?>">
				<a class="nav-link" href="<?=base_url('site/streamers')?>">Streamers</a>
			</li>
			
			<?php if($this->member->valid):?>
				<li class="nav-item<?=$this->current_page == 'emblem' ? ' active' : null?>">
					<a class="nav-link" href="<?=base_url('site/emblem')?>">Emblem</a>
				</li>
			<?php endif;?>
			
			<?php if($this->member->valid):?>
				<li class="nav-item">
					<a class="nav-link" href="<?=base_url('forum/viewtopic.php?f=3&t=1000')?>">Modline</a>
				</li>
			<?php endif;?>
			
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


	<!-- Inloggad som... -->
	<div class="col-lg-4 text-lg-right pr-0 pl-sm-4 pl-lg-0">
		
		<?php if($this->member->valid):?>
		<div id="userbox" class="mt-lg-1 mb-2 mb-lg-0">
			<span class="text-nowrap"><strong><?=$this->member->name;?></strong></span>
			<?php
			//grad-ikon
			if(isset($this->member->rank_id))
				echo '<img class="rank_icon" src="'. base_url('images/rank_icons/'. $this->member->rank_icon) .'" title="'. $this->member->rank_name .'" data-toggle="tooltip" />';

			//avatar
			$avatar = !empty($this->member->avatar_url)
				? $this->member->avatar_url
				: base_url('images/unknown.png');
			echo "<img class='avatar rounded' src='$avatar' alt='Avatar'>"
			?>
		</div>
		<?php else:?>
			<div class="mt-2 mb-2 mb-lg-0">
				<a class="btn btn-success" href="<?=base_url('forum/ucp.php?mode=login&redirect=../')?>">Logga in</a>
				<a class="btn btn-primary" href="<?=base_url('forum/ucp.php?mode=register')?>">Registrera dig</a>
			</div>
		<?php endif;?>

	</div>

</div>