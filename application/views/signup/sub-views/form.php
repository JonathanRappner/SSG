<?php
/** 
 * Sub-vy för anmälningsformulär.
*/
defined('BASEPATH') OR exit('No direct script access allowed');

//variabler
$title_long = $this->member_not_signed ? null : "$event->title ($event->type_name) &ndash; $event->start_date ($event->start_time - $event->end_time)";
$is_gsu = $event->type_id == 5; // om event är GSU/ASU: visa annorlunda formulär

//Önskad grupp-<option>s
$group_options = '';
foreach($groups as $group)
{
	$selected =
		(!$this->member_not_signed && $group->id == $signup->group_id) //redigerar: sätt till värde från db
		|| ($this->member_not_signed && $this->preselects->group_id != null && $this->preselects->group_id == $group->id) //ny: använd preselect-värde om inte null
		? 'selected'
		: null;
	
	$group_options .= "<option value='$group->id' ". $selected .">$group->name</option>\n";
}

//skapa groups_roles-array åt js
$groups_roles = array();
foreach($groups as $group) //alla grupper
{
	//ny grupp
	$groups_roles[$group->id] = array(); //skapa blank array

	//iterera genom alla gruppens roller
	if(isset($group->roles))
		foreach($group->roles as $role_id => $role)
		{
			$obj = new stdClass;
			$obj->role_id = $role_id;
			$obj->role_name = $role;

			$groups_roles[$group->id][] = $obj;
		}
}


//skriv ut js-vars
$preselect_role = $this->member_not_signed && $this->preselects->role_id != null
	? $this->preselects->role_id
	: -1;
echo "<script>\n";
echo "var default_role = $preselect_role;\n"; //js måste sätta vilken role som ska vara selected, inte php
echo $this->member_not_signed ? null : 'default_role = '. $signup->role_id .";\n";
echo 'var groups_roles = '. json_encode($groups_roles) .";\n";
echo "</script>\n";

?><script src="<?=base_url('js/signup/form.js')?>"></script>
<form class="ssg_form" action="<?=base_url('signup/submit_signup')?>" method="post">

	<input type="hidden" name="event_id" value="<?=$event->id?>" />
	
	<!-- Namn -->
	<div class="form-group">
		<label for="input_name">Namn</label>
		<input type="text" id="input_name" class="form-control" placeholder="<?=$this->member->name?>" readonly>
	</div>

	<?php if(!$is_gsu):?>
		<!-- Grupp -->
		<div class="form-group">
			<label for="input_group">Önskad grupp</label>
			<select class="form-control" id="input_group" name="group_id">
				<?=$group_options?>
			</select>
		</div>


		<!-- Befattning -->
		<div class="form-group">
			<label for="input_role">Önskad befattning</label>
			<select class="form-control" id="input_role" name="role_id">
			</select>
		</div>


		<!-- Närvaro -->
		<strong>Närvaro</strong>
		
		<div class="custom-control custom-radio">
			<input <?=(isset($signup) && $signup->attendance_id == 1) || !isset($signup) ? 'checked' : null?> type="radio" id="attendance_yes" name="attendance" value="1" class="custom-control-input">
			<label class="custom-control-label text-signed" for="attendance_yes">Ja</label>
		</div>
		<div class="custom-control custom-radio">
			<input <?=isset($signup) && $signup->attendance_id == 2 ? 'checked' : null?> type="radio" id="attendance_jip" name="attendance" value="2" class="custom-control-input">
			<label class="custom-control-label text-jip" for="attendance_jip"><abbr title="Join in progress">JIP</abbr></label>
		</div>
		<div class="custom-control custom-radio">
			<input <?=isset($signup) && $signup->attendance_id == 3 ? 'checked' : null?> type="radio" id="attendance_qip" name="attendance" value="3" class="custom-control-input">
			<label class="custom-control-label text-qip" for="attendance_qip"><abbr title="Quit in progress">QIP</abbr></label>
		</div>
		<div class="custom-control custom-radio">
			<input <?=isset($signup) && $signup->attendance_id == 4 ? 'checked' : null?> type="radio" id="attendance_noshow" name="attendance" value="4" class="custom-control-input">
			<label class="custom-control-label text-noshow" for="attendance_noshow">NOSHOW</label>
		</div>
	<?php else:?>
		<!-- Närvaro -->
		<strong>Närvaro</strong>
		<div class="custom-control custom-radio">
			<input <?=(isset($signup) && $signup->attendance_id == 1) || !isset($signup) ? 'checked' : null?> type="radio" id="attendance_yes" name="attendance" value="1" class="custom-control-input">
			<label class="custom-control-label text-signed" for="attendance_yes">Ja</label>
		</div>
		<div class="custom-control custom-radio">
			<input <?=isset($signup) && $signup->attendance_id == 4 ? 'checked' : null?> type="radio" id="attendance_noshow" name="attendance" value="4" class="custom-control-input">
			<label class="custom-control-label text-noshow" for="attendance_noshow">NOSHOW</label>
		</div>


		<input type="hidden" name="group_id" value="10">
		<input type="hidden" name="role_id" value="25">
	<?php endif?>


	<!-- Meddelande -->
	<div class="form-group">
		<label for="input_message">Meddelande</label>
		<textarea class="form-control" id="input_message" name="message" rows="3" placeholder="Övrig information angående din anmälan."><?=$signup != null ? $signup->message : null?></textarea>
	</div>

	<!-- Avbryt & Submit -->
	<div class="mt-1">

		<?php if($this->current_page != 'form'):?>
			<button type="button" class="btn btn-danger mr-2" data-dismiss="modal">Avbryt <i class="fas fa-times"></i></i></button>
		<?php endif?>

		<button type="submit" class="btn btn-success">
			<?=$this->member_not_signed ? 'Skapa anmälan <i class="fas fa-arrow-circle-right"></i>' : 'Spara ändringar <i class="fas fa-save"></i>'; ?>
		</button>

	</div>

</form>