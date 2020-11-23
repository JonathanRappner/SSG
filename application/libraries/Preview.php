<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Hanterar data för twitter/facebook/ogp.me-previews av länkar till sidan.
 */
class Preview
{
	protected $CI;
	private
		$title,
		$description,
		$url,
		$domain,
		$image_url;

	public function __construct()
	{
		// Assign the CodeIgniter super-object
		$this->CI =& get_instance();

		//sätt default-värden
		$this->title = 'Swedish Strategic Group';
		$this->description = 'SSG grundades 2009 och är en stabil och väletablerad ArmA-klan. Vi är den största organiserade klanen i Sverige och är inriktade på taktisk realism.';
		$this->domain = 'https://www.ssg-clan.se/';
		$this->url = $this->domain . uri_string();
		$this->image_url = 'https://www.ssg-clan.se/images/preview_ssg.png';
	}

	/**
	 * Sätt preview-variabler
	 *
	 * @param string $title Titel
	 * @param string $description Beskrivning
	 * @param string $image_url Förhandsvisningsbild
	 * @return void
	 */
	public function set_data($title, $description, $image_url)
	{
		if(isset($title)) $this->title = $title;
		if(isset($description)) $this->description = $description;
		if(isset($image_url)) $this->image_url = $image_url;
	}

	public function get_data()
	{
		$object = new stdClass;

		$object->title = $this->title;
		$object->description = $this->description;
		$object->domain = $this->domain;
		$object->url = $this->url;
		$object->image_url = $this->image_url;

		return $object;
	}
}
?>