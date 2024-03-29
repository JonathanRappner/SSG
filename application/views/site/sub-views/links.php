<?php
/** 
 * Länkar
*/
defined('BASEPATH') OR exit('No direct script access allowed');
?><div class="card links border-0 shadow-sm">

	<div class="card-header bg-dark text-white">
		<?php if(XMAS):?><div class="snow_edge left"></div><div class="snow_pattern"></div><div class="snow_edge right"></div><span class="font-weight-normal">🎁</span><?php endif;?>
		<?php if(CAKE):?><span class="font-weight-normal">🍰</span><?php endif;?>
		<?php if(EASTER):?><span class="font-weight-normal">🐥</span><?php endif;?>
		Community-länkar <?php if(APRIL_FOOLS) echo $this->april_fools->random_emojis(microtime())?>
	</div>

	<div class="card-body p-2">

		<a title="YouTube" href="https://www.youtube.com/channel/UCvi4V2i_c1kVXr5ilsz3-xA" target="_blank">
			<img src="<?=base_url('images/link_icons/youtube.svg')?>" class="mt-1">
		</a>

		<a title="Discord" href="https://discord.gg/W2C4TVSp9D" target="_blank">
			<img src="<?=base_url('images/link_icons/discord.svg')?>">
		</a>

		<a title="Facebook" href="https://www.facebook.com/swedishstrategicgroup/" target="_blank">
			<img src="<?=base_url('images/link_icons/facebook.svg')?>">
		</a>

		<a title="Twitter" href="https://twitter.com/SSGOfficial4" target="_blank">
			<img src="<?=base_url('images/link_icons/twitter.svg')?>" class="mt-1">
		</a>

		<a title="Instagram" href="https://www.instagram.com/swedish.strategic.group/" target="_blank">
			<img src="<?=base_url('images/link_icons/instagram.svg')?>">
		</a>

	</div>

</div>