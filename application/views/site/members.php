<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$this->current_page = 'members';

?><!DOCTYPE html>
<html lang="sv">
<head>

	<!-- CSS/JS -->
	<?php $this->load->view('site/sub-views/head');?>
	<link rel="stylesheet" href="<?=base_url('css/site/members.css?0')?>">

	<title>Swedish Strategic Group - Medlemmar</title>

</head>
<body>

<!-- Top -->
<?php $this->load->view('site/sub-views/top');?>

<div id="wrapper_members" class="container">

	<h1>Medlemmar</h1>

	<p>SSGs spelare är uppdelade i olika grupper som spelar tillsammans under varje OP eller träning.<br>Här finner du alla aktiva medlemmar samt <span class="hint" title="Spelare som spelar med oss oregelbundet." data-toggle="tooltip">supporters</span>.</p>

	<!-- Skyttegrupper -->
	<div class="row mb-2">

		<div class="col-12">
			<h2>Skyttegrupper</h2>
			<p>Dessa är de vanliga grupperna i SSG.</p>
		</div>

		<?php foreach($groups_skytte as $group):?>
			<div class="col-md-4 mb-sm-2">
				<h3><?=group_icon($group->code, null, true). $group->name?><?=($group->description ? " <small>({$group->description})</small>" : null)?></h3>
				<ul class="member_list">
					<?php print_members($group->members)?>
				</ul>

			</div>
		<?php endforeach;?>

	</div><!--end skyttegrupper row-->


	<!-- Enablers -->
	<div class="row">

		<div class="col-12">
			<h2>Enablers</h2>
			<p>Enablergrupperna är stödgrupper som hjälper skyttegrupperna att strida effektivt.</p>
		</div>

		<?php foreach($groups_enablers as $group):?>
			<div class="col-md-6">
				<h3><?=group_icon($group->code, null, true). $group->name?><?=($group->description ? " <small>({$group->description})</small>" : null)?></h3>
				<ul class="member_list">
					<?php print_members($group->members)?>
				</ul>
			</div>
		<?php endforeach;?>

	</div><!--end enabler row-->

	<!-- Footer -->
	<?php $this->load->view('site/sub-views/footer');?>

</div>

</body>
</html><?php

/**
 * Skriver ut medlemslista i <ul>
 *
 * @param array $members
 * @return void
 */
function print_members($members)
{
	foreach($members as $member)
	{
		echo '<li>'; //behöver inte avslutar i HTML5, sådeså!

		//start på länk-tag om phpbb_user_id finns
		if($member->phpbb_user_id)
			echo '<a href="'. base_url('forum/memberlist.php?mode=viewprofile&u='. $member->phpbb_user_id) .'">';

		//rank_icon + name + role_name
		echo rank_icon($member->rank_icon, $member->rank_name);
		echo $member->name;
		
		//slut på länk-tag om phpbb_user_id finns
		if($member->phpbb_user_id)
			echo '</a>';


		echo $member->role_name
			? " <small>({$member->role_name})</small>"
			: null;
	}
}