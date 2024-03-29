<?php
/** 
 * Nyhetsflöde
*/
defined('BASEPATH') OR exit('No direct script access allowed');

?><div id="newsfeed" class="collapsible col p-0">

	<!-- Kollaps-fade-effekt till mobil -->
	<div class="bottom_fade">
		<div class="button_wrapper">
			<button id="btn_news_expand" class="btn btn-primary">Expandera <i class="fas fa-plus"></i></button>
		</div>
	</div>
	
		<!-- Nyhets-posts -->
		<?php foreach($news->topics as $topic):?>
		<?php
			$allow_links = $topic->forum_id != 34; //skapa inte "läs mer"-länkar till "Nyhetsflödet"-forumet
			$article_link = base_url('forum/viewtopic.php?t='. $topic->id);
			
			$heading_small = $topic->poster_name
				? "<small>postat av {$topic->poster_name}, {$topic->date}</small>"
				: null;
			$heading = $allow_links
				? "<h3 class='card-header bg-dark'><a href='{$article_link}' class='text-white'>{$topic->title}". (APRIL_FOOLS ? ' '.$this->april_fools->random_emojis(microtime()) : null) ."</a>{$heading_small}</h3>"
				: "<h3 class='card-header bg-dark text-white'>{$topic->title}". (APRIL_FOOLS ? ' '. $this->april_fools->random_emojis(microtime()) : null) ."{$heading_small}</h3>";
		?>
		<div class="news_topic card mb-4 bg-white border-0 shadow-sm">
			
			<?php if(XMAS):?><div class="snow_edge left"></div><div class="snow_pattern"></div><div class="snow_edge right"></div><?php endif;?>
			
			<?=$heading?>
			
			<div class="card-body px-4 py-2 pb-4"><?=$topic->text?></div>

			<?php if($allow_links):?>
				<a href="<?=$article_link?>" class="card-footer bg-white border-0 font-weight-bold">Läs mer &raquo;</a>
			<?php endif;?>

		</div>
	<?php endforeach;?>

</div><!-- end #newsfeed -->

<!-- Pagination -->
<div id="news_pagination" class="col">
	<?=pagination($page, $news->total_results, $news->results_per_page, base_url('site/news/'), 'newsfeed', 15)?>
</div>
