<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Debrief extends CI_Controller
{
	
	public function index()
	{
		echo "lista med alla senaste events och vilka du har debriefat";
	}


	public function event($event_id = null)
	{
		echo "debrief-översikt för event: {$event_id}, alla grupper";
	}


	public function group($event_id = null, $group_id = null)
	{
		echo "debrief-översikt för event: {$event_id} och gruppen: {$group_id}";
	}


	public function form($event_id = null, $member_id = null)
	{
		if(!$member_id)
			echo "debrief-formulär för event: {$event_id}, för dig själv";
		else
			echo "debrief-formulär för event: {$event_id}, för medlem: {$member_id}";
	}
}