<?php
/** 
 * Modal med login-formulär till phpBB
*/
defined('BASEPATH') OR exit('No direct script access allowed');
?><form action="<?=base_url('/forum/ucp.php?mode=login')?>" method="post">
	<div class="modal fade" id="login_form" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				
				<div class="modal-header">
					<h5 class="modal-title" id="exampleModalLabel">Logga in</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Stäng">
					<span aria-hidden="true">&times;</span>
					</button>
				</div>

				<div class="modal-body">
					
					<!-- gömda inputs -->
					<input type="hidden" name="login">
					<input type="hidden" name="redirect" value="<?=base_url('site/'. $this->current_page)?>">
					<input type="checkbox" name="autologin" id="autologin" checked style="display: none;">

					<!-- Användarnamn -->
					<div class="form-group">
						<label for="input_name">Användarnamn</label>
						<input type="text" name="username" class="form-control" required>
					</div>

					<!-- Lösenord -->
					<div class="form-group">
						<label for="input_name">Lösenord</label>
						<input type="password" name="password" class="form-control" required>
					</div>

					<div>
						<a href="<?=base_url('forum/ucp.php?mode=sendpassword')?>">Jag har glömt mitt lösenord</a>
					</div>

				</div>

				<div class="modal-footer">
					
					<button type="button" class="btn btn-danger" data-dismiss="modal">
						Avbryt
						<i class="fas fa-times-circle"></i>
					</button>
					
					<button type="submit" class="btn btn-success">
						Logga in
						<i class="fas fa-chevron-circle-right"></i>
					</button>

				</div>

			</div> <!-- end div.modal-content -->
		</div> <!-- end div.modal-dialog -->
	</div> <!-- end #login_form -->
</form>