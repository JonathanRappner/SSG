<?php
/** 
 * Senaste foruminläggen
*/
defined('BASEPATH') OR exit('No direct script access allowed');

//variabler

?><div class="latest_posts">

	<div class="card border-0 shadow-sm">

		<div class="card-header bg-dark text-white">
			Senaste foruminläggen
		</div>

		<ul class="list-group list-group-flush">
			
			<?php foreach($posts as $post):?>
			<a href="<?=$post->url?>" class="list-group-item" data-toggle="tooltip" title="<?=$post->text?>">
				<p>[<?=$post->forum_name?>] <?=$post->topic_title?><?=($post->has_unread_post ? ' <small class="new_post">Ny post!</small>' : null)?></p>
				<span style="color:#<?=$post->user_color?>;"><?=$post->name?></span>
				<small>(<?=$post->relative_time_string?>)</small>
			</a>
			<?php endforeach;?>

		</ul>

	</div>

</div>