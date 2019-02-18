<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Hanterar interaktioner med phpbb3-forumet
 */
class Phpbb
{
	protected $CI;

	public function __construct()
	{
		// Assign the CodeIgniter super-object
		$this->CI =& get_instance();
	}

	/**
	 * Kopiera data från SFM till PHPBB
	 * Avbryter om användaren redan är kopierad.
	 *
	 * @param int $smf_user_id Id i smf_members / ssg_members
	 * @param string $password Användarens inmatade lösenord
	 * @return void
	 */
	public function add_user_from_smf($smf_user_id, $password)
	{
		//hämta data från smf_members och ssg_members
		$sql =
			'SELECT
				LOWER(member_name) AS username_clean,
				real_name AS username,
				email_address,
				date_registered,
				birthdate,
				ssg_members.phpbb_user_id
			FROM smf_members
			INNER JOIN ssg_members
				ON smf_members.id_member = ssg_members.id
			WHERE id_member = ?';
		$member = $this->CI->db->query($sql, $smf_user_id)->row();

		//avsluta om ingen medlem hittades eller phpbb-id redan finns
		if(empty($member) || !empty($member->phpbb_user_id))
			return;
		
		//lägg till phpbb-användare
		$phpbb_user_id = $this->create_user(
			$member->username,
			$member->username_clean,
			$password,
			$member->email_address,
			$member->date_registered,
			$member->birthdate
		);

		//spara phpbb-användar-id i ssg_members
		$this->CI->db
			->where(array('id' => $smf_user_id))
			->update('ssg_members', array('phpbb_user_id' => $phpbb_user_id));
	}

	/**
	 * Lägg till användare i phpbb.
	 *
	 * @param string $username Visningsnamn (nick som syns på sidan)
	 * @param string $username_clean Användarnamn (för inloggning)
	 * @param string $password
	 * @param string $email
	 * @param int $registration_time Unix-format. Registrerings-datum.
	 * @param string $birthday yyyy-mm-dd
	 * @return int Insert-id
	 */
	private function create_user($username, $username_clean, $password, $email, $registration_time, $birthday)
	{
		$birthday_phpbb = !empty($birthday) && $birthday != '0001-01-01'
			? substr($birthday, 8, 2) .'-'. substr($birthday, 5, 2) .'-'. substr($birthday, 0, 4) //1987-02-26 -> 26-02-1987
			: '';
		
		$data = array(
			'username'              => $username,
			'username_clean'        => $username_clean,
			'user_password'         => $this->create_hash($password),
			'user_email'            => $email,
			'user_email_hash'		=> sprintf('%u', crc32(strtolower($email))) . strlen($email),
			'user_timezone'         => 'Europe/Stockholm',
			'user_lang'             => 'sv',
			'user_regdate'          => $registration_time,
			'user_birthday'         => $birthday_phpbb,
			'user_style'         	=> 2,
			'user_dateformat'		=> '|D d M Y|, H:i',
			'group_id'				=> 13,
			'user_passchg'			=> time(),
		);

		//phpbb_users
		$this->CI->db->insert('phpbb_users', $data);
		$phpbb_user_id = $this->CI->db->insert_id();
		
		//user_group-kopplingar
		$this->CI->db->insert('phpbb_user_group', array('user_id'=>$phpbb_user_id, 'group_id'=> 2, 'user_pending'=> 0)); //registered
		$this->CI->db->insert('phpbb_user_group', array('user_id'=>$phpbb_user_id, 'group_id'=> 7, 'user_pending'=> 0)); //newly registered
		$this->CI->db->insert('phpbb_user_group', array('user_id'=>$phpbb_user_id, 'group_id'=> 13, 'user_pending'=> 0)); //medlem
		
		//phpbb_config
		$this->CI->db //newest user id
			->where(array('config_name' => 'newest_user_id'))
			->update('phpbb_config', array('config_value' => $phpbb_user_id));
		$this->CI->db //newest username
			->where(array('config_name' => 'newest_username'))
			->update('phpbb_config', array('config_value' => $username));
		$this->CI->db //newest user color
			->where(array('config_name' => 'newest_user_colour'))
			->update('phpbb_config', array('config_value' => 'FFFFFF'));
		$this->CI->db->query('UPDATE phpbb_config SET config_value = config_value + 1 WHERE config_name = "num_users"'); //num_users

		
		return $phpbb_user_id;
	}

	/**
	 * Skapa ett nytt hash av ett lösenord med slumpat salt för phpbb.
	 *
	 * @param string $password
	 * @return string
	 */
	public function create_hash($password)
	{
		return password_hash($password, PASSWORD_DEFAULT);
	}

	/**
	 * Kollar om angivet $password stämmer med $hash.
	 *
	 * @param string $password
	 * @param string $hash
	 * @return bool
	 */
	public function check($password, $hash)
	{
		$salt = substr($hash, 0, 29);
		$new_hash = crypt($password, $salt);

		return $new_hash === $hash;
	}
}
?>