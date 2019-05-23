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

		//formatera länkar
		foreach($result as $row)
			$row->text = preg_replace($this->regex_url, '<span class="link">[<a href="$0" target="_blank">länk</a>]</span>', $row->text); //case insensitive replace
		
		//lägg till forum-pm-alert
		if($this->member->valid)
		{
			$pm_count = $this->get_pm_count($this->member->id);

			if($pm_count > 0)
			{
				$pm_alert = new stdClass;
				$pm_alert->id = 0;
				$pm_alert->text =
					'<a href="'. base_url('forum/ucp.php?i=pm&folder=inbox') .'" class="alert-link" target="_blank">'
					.'<i class="fas fa-envelope mr-2"></i> Du har <strong>'. $pm_count .'</strong> '
					.($pm_count == 1 ? 'oläst meddelande' : 'olästa meddelanden')
					.'</a>';
				$pm_alert->class = 'danger';

				$result[] = $pm_alert;
			}

		}

		return $result;
	}

	/**
	 * Hämta antalet olästa PMs i phpbb-inkorgen.
	 *
	 * @param int $member_id SSG medlems-id.
	 * @return int Antal olästa PMs
	 */
	private function get_pm_count($member_id)
	{
		$sql =
			'SELECT COUNT(*) count
			FROM phpbb_privmsgs_to pm
			INNER JOIN ssg_members m
				ON m.phpbb_user_id = pm.user_id
			WHERE
				m.id = ?
				AND pm_unread';
		return $this->db->query($sql, $member_id)->row()->count;
	}
}