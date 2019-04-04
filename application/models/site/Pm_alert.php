<?php
/**
 * PM-alert i top-raden på site.
 * Hämtar antalet olästa PM i phpbb-inkorgen.
 */
class Pm_alert extends CI_Model
{

	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Hämta antalet olästa PMs i phpbb-inkorgen.
	 *
	 * @param int $member_id SSG medlems-id.
	 * @return int Antal olästa PMs
	 */
	public function get_pm_count($member_id)
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