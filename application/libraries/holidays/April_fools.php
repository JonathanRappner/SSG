<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Första april-modul
 */
class April_fools
{
	protected $CI;
	private $emojis;

	public function __construct()
	{
		// Assign the CodeIgniter super-object
		$this->CI =& get_instance();

		$this->emojis = array('😂', '💩', '💣', '👌', '💥', '😎', '🤟', '🍹', '😢', '😁', '😝', '😧', '🐵', '👍', '🔫', '🥓', '🥪', '🍺', '🍌', '🚗', '💔');
	}

	/**
	 * Allmän css för första april.
	 *
	 * @return string Stylesheet-kod med tags.
	 */
	public function style()
	{
		echo '<link rel="stylesheet" href="'. base_url('css/holidays/april_fools.css') .'">';
	}

	/**
	 * Ger serie slumpmässiga emojis från $this->emojis.
	 * $seed ser till att du alltid får samma grupp emojis varje gång du anropar metoden.
	 *
	 * @param string $seed Seed
	 * @param int $count Antal emojis. Låt vara null för 0-3 emojis.
	 * @return void
	 */
	public function random_emojis($seed, $count = null)
	{
		$hash_hex = hash('md5', $seed); //hash av givna textsträngen, hexadecimalt
		$hash_hex_spipped = substr($hash_hex, -7, 7); //avkortad version av hex-numret (hex-nummer med sju F är max vad en signed int kan hålla)
		$hash_dec = intval($hash_hex_spipped, 16); //hex -> dec

		srand($hash_dec); //seed:a randomizer
		if(empty($count))
			$count = rand(1, 3); //slumpa fram antal emojis

		//skapa emoji-sträng
		$output = null;
		for($i=0; $i<$count; $i++)
			$output .= $this->emojis[rand(0, count($this->emojis)-1)];

		return $output;
	}
}