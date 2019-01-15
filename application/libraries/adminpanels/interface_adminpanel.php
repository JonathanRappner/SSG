<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * 
 */
interface Adminpanel
{
	public function main($var1, $var2);
	public function view();
	public function get_title();
	public function get_code();
	public function get_permissions_needed();
}
?>