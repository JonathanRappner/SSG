<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Visar bootstrap alerts om så anges i session-variabler.
 * T.ex. "Din anmälan sparades"
 */
class Alerts
{
	protected $CI;

	public function __construct()
	{
		// Assign the CodeIgniter super-object
		$this->CI =& get_instance();

		//assign:a array om det behövs
		if(!isset($this->CI->session->messages))
			$this->CI->session->messages = array();
	}

	/**
	 * Lägger till meddelande i kön.
	 *
	 * @param string $type Bootstrap alert-klasser (ex: danger, warning, success, primary, info)
	 * @param string $message
	 * @return void
	 */
	public function add_alert($type, $message)
	{
		$_SESSION['messages'][] = array('type' => $type, 'message' => $message); //kan inte använda CIs tillvägagångssätt av någon anledning
	}

	/**
	 * Skriver ut alla meddelanden som ligger sparade i session-variabeln.
	 *
	 * @return void
	 */
	public function print_alerts()
	{
		echo '<div id="alerts">';

		foreach($this->CI->session->messages as $message)
			echo 
			'<div class="alert alert-'. $message['type'] .' alert-dismissible fade show" role="alert">
				'. $message['message'] .'
				<button type="button" class="close" data-dismiss="alert" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>';
		
		echo '</div>';

		unset($_SESSION['messages']); //rensa meddelanden
	}
}
?>