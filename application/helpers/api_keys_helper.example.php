<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
 * *********** Exempel-fil, byt ut nycklar! ***********
 * /

/**
 * Hämtar nycklar till externa API:er.
 *
 * @param string $key "youtube" eller "twitch"
 * @return string
 */
function api_key($key)
{
	if($key == 'youtube')
		return 'abc123';
	else if($key == 'twitch')
		return '123abc';
	else
		return null;
}