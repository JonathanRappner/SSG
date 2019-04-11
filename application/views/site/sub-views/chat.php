<?php
/**
 * Chat
*/
defined('BASEPATH') or exit('No direct script access allowed');

?><ul id="chat-list" class="chat-list">
	<?php if(!isset($chat_messages) || count($chat_messages) <= 0):?>
		<li><strong>SSG</strong>: Inga chatmeddelanden 😢</li>
	<?php else:?>
		<?php foreach($chat_messages as $message):?>
			<li data-message_id="<?=$message->id?>">
				<a href="<?=base_url('forum/memberlist.php?mode=viewprofile&u='. $message->phpbb_user_id)?>" target="_blank"><?=$message->name?></a>:
				<?=strip_tags($message->text)?>
				<p class="timespan"><?=$message->timespan_string?></p>
			</li>
		<?php endforeach;?>
	<?php endif;?>
</ul>
<div id="input_row" class="input-group">
	<input type="text" id="message" class="form-control mr-2">
	<button id="btn_info" class="btn btn-primary mr-2"><i class="fas fa-info"></i></button>
	<button id="btn_emoji" class="btn btn-primary mr-2">🙂</button>
	<button id="btn_send" class="btn btn-success">
		Skicka
		<i class="fas fa-comment"></i>
	</button>
</div>