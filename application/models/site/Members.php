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
				m.id, m.name, m.phpbb_user_id, m.is_active,
				IF(r.name_long IS NULL, r.name, r.name_long)  AS role_name
			FROM ssg_members m
			LEFT OUTER JOIN ssg_roles r
				ON m.role_id = r.id
			WHERE 
				m.group_id = ?
			ORDER BY
				m.is_active DESC,
				r.id IS NULL ASC, #medlemmar utan befattning hamnar sist
				r.sorting ASC,
				m.id ASC';
		$members = $this->db->query($sql, array($group_id))->result();

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