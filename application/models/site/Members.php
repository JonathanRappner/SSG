<?php
/**
 * Modell för medlemssidan
 */
class Members extends CI_Model
{

	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Hämta skyttegrupper eller enablergrupper samt deras medlemmar.
	 *
	 * @param bool $enablers Hämta enablers?
	 * @return object
	 */
	public function get_groups($enablers)
	{
		$where = $enablers
			? 'enabler'
			: 'NOT enabler';

		$sql =
			'SELECT
				id, name, code, description
			FROM ssg_groups
			WHERE 
				'. $where .'
				AND active
				AND NOT dummy
				AND (SELECT COUNT(*) FROM ssg_members m WHERE m.group_id = ssg_groups.id) > 0
			ORDER BY sorting ASC';
		$groups = $this->db->query($sql)->result();

		//hämta medlemmar
		foreach($groups as $group)
			$group->members = $this->get_members($group->id);

		return $groups;
	}

	/**
	 * Hämta medlemmar för specifik grupp.
	 *
	 * @param int $group_id
	 * @return object
	 */
	private function get_members($group_id)
	{
		$sql =
			'SELECT
				m.id, u.username AS name, m.phpbb_user_id, m.is_active,
				IF(r.name_long IS NULL, r.name, r.name_long) AS role_name,
				(
					SELECT ssg_ranks.sorting
					FROM ssg_promotions
					INNER JOIN ssg_ranks
						ON ssg_promotions.rank_id = ssg_ranks.id
					WHERE member_id = m.id
					ORDER BY
						date DESC,
						ssg_promotions.id DESC
					LIMIT 1
				) AS rank_sorting
			FROM ssg_members m
			LEFT OUTER JOIN ssg_roles r
				ON m.role_id = r.id
			LEFT OUTER JOIN phpbb_users u
				ON m.phpbb_user_id = u.user_id
			WHERE 
				m.group_id = ?
			ORDER BY
				m.is_active DESC,
				r.id = NULL ASC, #medlemmar utan befattning hamnar sist
				rank_sorting DESC,
				r.sorting ASC,
				m.id ASC';
		$members = $this->db->query($sql, array($group_id))->result();

		//sätt inaktiva medlemmars roll till "Supporter"
		foreach($members as $member)
			if(!$member->is_active)
				$member->role_name = 'Supporter';

		//hämta medlemsgrader
		foreach($members as $member)
		{
			$sql =
				'SELECT
					r.name, r.icon
				FROM ssg_promotions p
				INNER JOIN ssg_ranks r
					ON p.rank_id = r.id
				WHERE p.member_id = ?
				ORDER BY date DESC
				LIMIT 1';
			$row = $this->db->query($sql, $member->id)->row();

			$member->rank_name = $row ? $row->name : null;
			$member->rank_icon = $row ? $row->icon : null;
		}

		return $members;
	}
}