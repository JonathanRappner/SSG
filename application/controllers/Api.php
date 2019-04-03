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

	public function index()
	{
		//data
		$data = new stdClass;
		$data->member = site_url('api/member/{member_id}');
		$data->members = site_url('api/members');
		$data->streamer = site_url('api/streamer/{member_id}');
		$data->streamers = site_url('api/streamers');

		//skriv ut
		$this->output($data);
	}

	public function member($member_id = null)
	{
		//moduler
		$this->load->model('api/members');

		//endast GET-requests och numerisk $member_id
		if($this->method != 'get' || !is_numeric($member_id))
			$this->output(null, 400); //bad request
		
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
			$this->output(null, 400); //bad request

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
			$this->output(null, 400); //bad request

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
			$this->output(null, 400); //bad request
		
		//hämta data från db
		$streamers = $this->streamers->get_streamers();

		$this->output($streamers);
	}
}