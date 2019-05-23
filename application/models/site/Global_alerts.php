<?php
/**
 * Modell för Global_alerts
 */
class Global_alerts extends CI_Model
{
	private $regex_url;

	public function __construct()
	{
		parent::__construct();

		$this->regex_url = '/https?:\/\/(www\.)?[-a-zA-Z0-9@:%._\+~#=]{2,256}\.[a-z]{2,6}\b(?:[-a-zA-Z0-9@:%_\+.,~#?&\/\/=]*)/i';
	}

	public function get_alerts()
	{
		$sql =
			'SELECT *
			FROM ssg_global_alerts
			WHERE
				expiration_date IS NULL
				OR expiration_date >= NOW()
			ORDER BY
				expiration_date IS NULL DESC,
				expiration_date DESC';
		$result = $this->db->query($sql)->result();

		foreach($result as $row)
			$row->text = preg_replace($this->regex_url, '<span class="link">[<a href="$0" target="_blank">länk</a>]</span>', $row->text); //case insensitive replace
		
		return $result;
	}
}