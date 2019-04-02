<?php
/**
 * API Members
 */
class Members extends CI_Model
{
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Hämta enskild medlem.
	 *
	 * @param int $member_id
	 * @return object
	 */
	public function get_member($member_id)
	{
		$sql =
			'SELECT
				m.name AS name,
				m.id AS id,
				g.name AS group_name,
				g.code AS group_code,
				m.group_id,
				role_id,
				r.name AS role_name,
				FROM_UNIXTIME(phpbb.user_regdate) AS reg_date
			FROM ssg_members m
			LEFT OUTER JOIN phpbb_users phpbb
				ON m.phpbb_user_id = phpbb.user_id
			LEFT JOIN ssg_groups g
				ON m.group_id = g.id
			LEFT JOIN ssg_roles r
				ON m.role_id = r.id
			WHERE m.id = ?';
		$row = $this->db->query($sql, $member_id)->row();

		//hämta avatar-url
		$row->avatar_url = $this->member->get_phpbb_avatar($member_id);

		return $row;
	}

	/**
	 * Hämta alla medlemmar.
	 *
	 * @return array
	 */
	public function get_members()
	{
		$sql =
			'SELECT
				m.name AS name,
				m.id AS id,
				g.name AS group_name,
				g.code AS group_code,
				m.group_id,
				role_id,
				r.name AS role_name,
				FROM_UNIXTIME(phpbb.user_regdate) AS reg_date
			FROM ssg_members m
			LEFT OUTER JOIN phpbb_users phpbb
				ON m.phpbb_user_id = phpbb.user_id
			LEFT JOIN ssg_groups g
				ON m.group_id = g.id
			LEFT JOIN ssg_roles r
				ON m.role_id = r.id
			ORDER BY
				active DESC,
				g.sorting ASC,
				r.sorting ASC';
		$result = $this->db->query($sql, $member_id)->result();

		//hämta avatar-url:er
		foreach($result as $row)
			$row->avatar_url = $this->member->get_phpbb_avatar($row->id);
		
		return $result;
	}
}