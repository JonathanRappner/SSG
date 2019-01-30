<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| Hooks
| -------------------------------------------------------------------------
| This file lets you define "hooks" to extend CI without hacking the core
| files.  Please see the user guide for info:
|
|	https://codeigniter.com/user_guide/general/hooks.html
|
*/


$hook['pre_system'][] = function()
{
    //Sätt tidszon PHP
    date_default_timezone_set('Europe/Stockholm');
};

$hook['post_controller_constructor'] = function()
{
	$CI =& get_instance();
	
    //sätt databas-tidszon till samma som php
    $CI->db->query('SET time_zone = "'. date('P') .'"');
};