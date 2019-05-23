<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
 * --Status Codes--
 * 200 ok
 * 201 created
 * 400 bad request
 * 401 unauthorized
 * 403 forbidden
 * 404 not found
 * 405 method not allowed (inkorrekt funktion)
 * 500 internal server error
 * 503 service unavailable
*/

/*
 * --HTTP Methods--
 * GET		Read
 * POST		Create
 * PUT		Update
 * DELETE	Remove
*/


class API extends CI_Controller
{
	private 
		$method; //get/post/put/delete etc.


	public function __construct()
	{
		parent::__construct();

		//GET, POST, PUT, DELETE, etc.
		$this->method = $this->input->method();
	}

	public function index()
	{
		//$data konverteras till json-objekt
		$data = new stdClass;
		$data->member = site_url('GET api/member/{int}');
		$data->members = site_url('GET api/members');
		$data->streamer = site_url('GET api/streamer/{int}');
		$data->streamers = site_url('GET api/streamers');
		$data->message = site_url('GET, POST, PUT & DELETE api/message/?message_id={int}[&text={string}]');
		$data->messages = site_url('GET api/messages/?length={int}[&message_id={int}] message_id = get messages after this message');
		$data->global_alert_dismiss = site_url('POST api/global_alert_dismiss/?id={int}');

		//skriv ut
		$this->output($data);
	}

	/**
	 * Skriv ut json-data.
	 *
	 * @param object $data Objekt med attribut.
	 * @param int $status_code HTTP-status-kod
	 * @return void
	 */
	private function output($data, $status_code = 200)
	{
		header('Content-Type: application/json', true, $status_code);
		
		echo isset($data)
			? json_encode($data, JSON_UNESCAPED_SLASHES)
			: '{}';
	}

	public function member($member_id = null)
	{
		//moduler
		$this->load->model('api/members');

		//endast GET-requests och numerisk $member_id
		if($this->method != 'get' || !is_numeric($member_id))
		{
			$this->output(null, 400); //bad request
			return;
		}
		
		//hämta data
		$member = $this->members->get_member($member_id);

		//skriv ut
		$this->output($member);
	}
	
	public function members()
	{
		//moduler
		$this->load->model('api/members');

		//endast GET-requests
		if($this->method != 'get')
		{
			$this->output(null, 400); //bad request
			return;
		}

		//hämta data
		$members = $this->members->get_members();

		$this->output($members);
	}

	public function streamer($member_id = null)
	{
		//moduler
		$this->load->model('api/streamers');

		//endast GET-requests och numerisk $member_id
		if($this->method != 'get' || !is_numeric($member_id))
		{
			$this->output(null, 400); //bad request
			return;
		}

		$do_update = $this->streamers->check_interval();

		//om gammal data, uppdatera
		if($do_update)
		{
			//hämta data från youtube/twitch och spara i db
			//////////////avstängt temporärt
			// $this->streamers->update_youtube();
			// $this->streamers->update_twitch();
		}

		//hämta data från db
		$streamer = $this->streamers->get_streamer($member_id);
		// $streamer->minutes_since_update = $minutes_since_update; //////////////avstängt temporärt

		$this->output($streamer);
	}

	public function streamers()
	{
		//moduler
		$this->load->model('api/streamers');

		$do_update = $this->streamers->check_interval();

		//om gammal data, uppdatera
		if($do_update)
		{
			//hämta data från youtube/twitch och spara i db
			//////////////avstängt temporärt
			// $this->streamers->update_youtube();
			// $this->streamers->update_twitch();
		}

		//endast GET-requests
		if($this->method != 'get')
		{
			$this->output(null, 400); //bad request
			return;
		}
		
		//hämta data från db
		$streamers = $this->streamers->get_streamers();

		$this->output($streamers);
	}

	public function messages()
	{
		//endast inloggade medlemmar får se meddelanden
		if(!$this->member->valid)
		{
			$this->output(null, 401); //unauthorized
			return;
		}

		$this->load->model('site/chat');

		//kör funktion baserat på HTTP-metod
		switch ($this->method)
		{
			//GET
			case 'get':
				$messages = $this->chat->api_get_messages($this->input->get());
				if($messages)
					$this->output($messages);
				else
					$this->output(null, 400); //bad request
			break;
			
			default:
				$this->output(null, 400); //bad request
			break;
		}
	}

	public function message()
	{
		//endast inloggade medlemmar får hantera meddelanden
		if(!$this->member->valid)
		{
			$this->output(null, 401); //unauthorized
			return;
		}
		
		$this->load->model('site/chat');

		//kör funktion baserat på HTTP-metod
		switch ($this->method)
		{
			//GET
			case 'get':
				$message = $this->chat->api_get_message($this->input->get());
				if($message)
					$this->output($message, 200);
				else
					$this->output(null, 400); //bad request
			break;

			//POST
			case 'post':
				$status = $this->chat->api_post($this->input->post());
				$this->output(null, $status);
			break;

			//PUT
			case 'put':
				$status = $this->chat->api_put($this->input->get());
				$this->output(null, $status);
			break;

			//DELETE
			case 'delete':
				$status = $this->chat->api_delete($this->input->get());
				$this->output(null, $status);
			break;
			
			default:
				$this->output(null, 400); //bad request
			break;
		}
	}

	public function global_alert_dismiss()
	{
		//--input-sanering--

		//endast inloggade medlemmar får hantera meddelanden
		if(!$this->member->valid)
		{
			$this->output(null, 401); //unauthorized
			return;
		}

		//endast post
		if(!$this->method == 'post')
		{
			$this->output(null, 400); //bad request
			return;
		}

		//saknar id eller inte numeriskt
		if(!$this->input->get('id') || !is_numeric($this->input->get('id')))
		{
			$this->output(null, 400); //bad request
			return;
		}
		$id = $this->input->get('id');


		//--do the thingy--

		//tilldela array om så behövs
		if(empty($this->session->dismissed_global_alerts))
			$this->session->dismissed_global_alerts = array();

		$array = $this->session->dismissed_global_alerts;

		//avbryt om id redan finns i array
		if(in_array($id, $array))
			return;

		//måste göra det på det här dumma sättet because reasons
		$array[] = $id;
		$this->session->dismissed_global_alerts = $array;
	}
}
