<?php
/**
 * Chat
*/
defined('BASEPATH') or exit('No direct script access allowed');

$this->load->library("Permissions");


?><script>
	var earliest_message_id = <?=$earliest_message_id?>;
	var member_id = <?=$this->member->id?>;
	var is_admin = <?=$this->permissions->has_permissions(array('super', 's1')) ? 'true' : 'false'?>; //No, this won't actually give you admin powers. It's just for cosmetic stuff.
</script>

<div class="status">

	<div id="loading-animation" class="spinner-border text-secondary"></div>

	<button id="btn_refresh" class="btn btn-primary">
		<i class="fas fa-sync-alt"></i>
	</button>

</div>

<div id="chat-list" class="chat-list row">
	<?php if(!isset($chat_messages) || count($chat_messages) <= 0):?>
		<li><strong>SSG</strong>: Inga chatmeddelanden 😢</li>
	<?php else:?>
		<?php foreach($chat_messages as $message):?>
			<div class="row chat_row" data-message_id="<?=$message->id?>">
				<div class="message_left col">
					<a href="<?=base_url('forum/memberlist.php?mode=viewprofile&u='. $message->phpbb_user_id)?>" target="_blank"><?=$message->name?></a>:
					<?=$message->text?>
					<p class="timespan"><?=$message->timespan_string?></p>
				</div>
				<?php if($this->permissions->has_permissions(array('super', 's0')) || $message->member_id == $this->member->id):?>
				<div class="message_right col-2 text-right">
					<button class="btn btn-primary btn_chat_edit" data-message_id="<?=$message->id?>" title="Redigera meddelande"><i class="fas fa-edit"></i></button>
					<button class="btn btn-danger btn_chat_delete" data-message_id="<?=$message->id?>" title="Ta bort meddelande"><i class="fas fa-trash-alt"></i></button>
				</div>
				<?php endif;?>
			</div>
		<?php endforeach;?>
	<?php endif;?>
</div>
<div id="input_row" class="input-group">
	<input type="text" id="message" class="form-control mr-2">
	
	<button id="btn_info" class="btn btn-primary mr-2"><i class="fas fa-info"></i></button>
	
	<button id="btn_emoji" class="btn btn-primary mr-2">🙂</button>
	
	<button id="btn_send" class="btn btn-success">
		Skicka
		<i class="fas fa-comment"></i>
		<div class="spinner-border spinner-border-sm"></div>
	</button>
	
	<button id="btn_abort" class="btn btn-danger mr-2">
		Avbryt
		<i class="fas fa-times"></i>
	</button>
	
	<button id="btn_save" class="btn btn-success">
		Spara
		<i class="fas fa-save"></i>
		<div class="spinner-border spinner-border-sm"></div>
	</button>
</div>