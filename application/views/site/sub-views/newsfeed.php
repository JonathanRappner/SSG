<?php
/** 
 * Nyhetsflöde
*/
defined('BASEPATH') OR exit('No direct script access allowed');

?><div id="newsfeed" class="collapsible row pt-2 pr-4">

	<!-- Kollaps-fade-effekt till mobil -->
	<div class="bottom_fade">
		<div class="button_wrapper">
			<button id="btn_news_expand" class="btn btn-primary">Expandera <i class="fas fa-plus"></i></button>
		</div>
	</div>
	
	<!-- Nyhets-posts -->
	<?php foreach($news->topics as $topic):?>
	<?php $article_link = base_url('forum/viewtopic.php?t='. $topic->id)?>
		<div class="news_topic col-12 px-0 pb-4 mb-4">
			
			<h3><a href="<?=$article_link?>"><?=$topic->title?></a></h3>
			
			<small><?=($topic->poster_name ? "postat av $topic->poster_name, " : null)?><?=$topic->date?></small>
			
			<div class="body"><?=$topic->text?></div>

			<a href="<?=$article_link?>" class="mt-2 d-inline-block font-weight-bold">Läs mer &raquo;</a>

		</div>

		<hr>
	<?php endforeach;?>

</div><!-- end #newsfeed -->

<!-- Pagination -->
<div id="news_pagination" class="row">
	<?=pagination($page, $news->total_results, $news->results_per_page, base_url('site/news/'), 'newsfeed', 15)?>
</div>
