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
	 * Hämta alla medlemmar eller sök.
	 * @param mixed $search_phrase
	 * 
	 * @return array
	 */
	public function get_members($search_phrase)
	{
		// sök
		if($search_phrase)
		{
			$where_statement = "WHERE m.name LIKE '{$search_phrase}%'"; // används för mentions ("@smorfty") i chatten, sök på fraser som _börjar_ med sökordet
			$limit_statement = 'LIMIT 5'; // begränsa mängden resultat
		}
		else
		{
			$where_statement = null;
			$limit_statement = null;
		}

		$sql =
			"SELECT
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
			{$where_statement}
			ORDER BY
				active DESC,
				g.sorting ASC,
				r.sorting ASC
			{$limit_statement}";
		$result = $this->db->query($sql)->result();

		//hämta avatar-url:er
		foreach($result as $row)
			$row->avatar_url = $this->member->get_phpbb_avatar($row->id);
		
		return $result;
	}

	/**
	 * Uppdatera tiden när medlemmen senast tittade på chat-rutan.
	 *
	 * @param int $member_id
	 * @return null
	 */
	public function update_chat_viewed($member_id)
	{
		$this->db
			->where(array('id' => $member_id))
			->update('ssg_members', array('chat_last_viewed' => 'NOW()'));
		
		$this->db->query('UPDATE ssg_members SET chat_last_viewed = NOW() WHERE id = ?', $member_id);

		return 200;
	}
}