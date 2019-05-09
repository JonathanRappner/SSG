<?php
/**
 * Senaste foruminläggen.
 */
class Latest_posts extends CI_Model
{

	public function __construct()
	{
		parent::__construct();
	}

	public function get_latest_posts($phpbb_user_id)
	{
		//om phpbb_user inte finns, returna publika trådar

		return array(0,1,2,3,4,5, $phpbb_user_id);
	}
}