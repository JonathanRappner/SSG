<?php
/** 
 * Senaste foruminläggen
*/
defined('BASEPATH') OR exit('No direct script access allowed');

//variabler

?><div class="latest_posts">

	<div class="card border-0 shadow-sm">

		<div class="card-header bg-dark text-white">
			<?php if(XMAS):?><div class="snow_edge left"></div><div class="snow_pattern"></div><div class="snow_edge right"></div>🎅<?php endif;?>
			<?php if(CAKE):?>🎂<?php endif;?>
			Senaste foruminläggen <?php if(APRIL_FOOLS) echo $this->april_fools->random_emojis(microtime())?>
		</div>

		<ul class="list-group list-group-flush">
			
			<?php foreach($posts as $post):?>
				<a href="<?=$post->url?>" class="list-group-item" title="<?=$post->text?>">
					<p>
						<?=$post->forum_name?> <i class="fas fa-caret-right"></i>
						<?=$post->topic_title?><?=($post->has_unread_post ? ' <small class="new_post">Ny post!</small>' : null)?>
					</p>
					<span style="color:#<?=$post->user_color?>;"><?=$post->name?></span>
					<small>(<?=$post->relative_time_string?>)</small>
				</a>
			<?php endforeach;?>

		</ul>

	</div>

</div>