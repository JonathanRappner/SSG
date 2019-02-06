<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Hanterar befattningar
 */
class Admin_roles implements Adminpanel
{
	protected $CI;
	private
		$foo;

	public function __construct()
	{
		// Assign the CodeIgniter super-object
		$this->CI =& get_instance();
	}

	public function main($var1, $var2)
	{
		//variabler
		$this->view = $var1;

		if($this->view == null) //main
		{
			
		}
		else if($this->view == '') //
		{
		}
	}

	public function view()
	{
		//js
		///////echo '<script src="'. base_url('js/signup/adminpanels/auto_events.js') .'"></script>';
		
		if($this->view == null) //main
			$this->view_main();
		else if($this->view == '')
			$this->view_main();
	}

	private function view_main()
	{
		echo '<p>Här kan du bla bla bla.</p>';///////
	}

	/**
	 * Lägg till auto-event.
	 *
	 * @param object $vars POST-variabler.
	 * @return void
	 */
	// private function add_($vars)
	// {
	// 	//input-sanering
	// 	assert(isset($vars), 'Post-variabler saknas.');
	// 	assert(!empty($vars->title), "title: $vars->title");
	// 	assert(isset($vars->type_id) && is_numeric($vars->type_id), "type_id: $vars->type_id");
	// 	assert(!empty($vars->day) && key_exists($vars->day, $this->days_se), "day: $vars->day");
	// 	assert(!empty($vars->start_time) && preg_match('/(\d{2}:\d{2}){1}/', $vars->start_time), "start_time: $vars->start_time");
	// 	assert(!empty($vars->length_time) && preg_match('/(\d{2}:\d{2}){1}/', $vars->length_time), "start_time: $vars->length_time");
		
	// 	$data = array
	// 	(
	// 		'title' => $vars->title,
	// 		'start_day' => $vars->day,
	// 		'start_time' => $vars->start_time,
	// 		'length_time' => $vars->length_time,
	// 		'type_id' => $vars->type_id,
	// 	);
	// 	$this->CI->db->insert('ssg_auto_events', $data);
	// }

	/**
	 * Uppdatera auto-event.
	 *
	 * @param object $vars POST-variabler.
	 * @return void
	 */
	// private function update_($vars)
	// {
	// 	//input-sanering
	// 	assert(isset($vars), 'Post-variabler saknas.');
	// 	assert(isset($vars->auto_event_id) && is_numeric($vars->auto_event_id), "auto_event_id: $vars->auto_event_id");
	// 	assert(!empty($vars->title), "title: $vars->title");
	// 	assert(isset($vars->type_id) && is_numeric($vars->type_id), "type_id: $vars->type_id");
	// 	assert(!empty($vars->day) && key_exists($vars->day, $this->days_se), "day: $vars->day");
	// 	assert(!empty($vars->start_time) && preg_match('/(\d{2}:\d{2}){1}/', $vars->start_time), "start_time: $vars->start_time");
	// 	assert(!empty($vars->length_time) && preg_match('/(\d{2}:\d{2}){1}/', $vars->length_time), "start_time: $vars->length_time");

	// 	$data = array
	// 	(
	// 		'title' => $vars->title,
	// 		'start_day' => $vars->day,
	// 		'start_time' => $vars->start_time,
	// 		'length_time' => $vars->length_time,
	// 		'type_id' => $vars->type_id,
	// 	);
	// 	$this->CI->db->where('id', $vars->auto_event_id)->update('ssg_auto_events', $data);
	// }

	public function get_code()
	{
		return 'roles';
	}

	public function get_title()
	{
		return 'Befattningar';
	}

	public function get_permissions_needed()
	{
		return array('s0');
	}
}
?>