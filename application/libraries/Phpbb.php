<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Hanterar interaktioner med phpbb3-forumet
 */
class Phpbb
{
	protected $CI;
	private $phpbb_dummy_user_id = 48;

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
			strtolower($member->username),
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
	public function check_password($password, $hash)
	{
		$salt = substr($hash, 0, 29);
		$new_hash = crypt($password, $salt);

		return $new_hash === $hash;
	}


	public function get_smf_thread($topic_id)
	{
		$thread = new stdClass;

		//topic
		$sql =
			'SELECT
				t.id_member_started AS author_id,
				ssg_mem.phpbb_user_id AS author_phpbb_id,
				m.real_name AS author_name,
				t.num_views
			FROM smf_topics t
			INNER JOIN smf_members m
				ON t.id_member_started = m.id_member
			LEFT OUTER JOIN ssg_members ssg_mem
				ON t.id_member_started = ssg_mem.id
			WHERE id_topic = ?';
		$thread->topic = $this->CI->db->query($sql, $topic_id)->row();

		//posts
		$sql =
			'SELECT
				mes.id_member AS author_id,
				ssg_mem.phpbb_user_id AS author_phpbb_id,
				mem.real_name AS author_name,
				mes.poster_time AS created_time,
				mes.subject,
				mes.modified_time,
				mes.body
			FROM smf_messages mes
			INNER JOIN smf_members mem #smf_members
				ON mes.id_member = mem.id_member
			LEFT OUTER JOIN ssg_members ssg_mem #ssg_member
				ON mes.id_member = ssg_mem.id
			WHERE id_topic = ?
			ORDER BY poster_time ASC';
		$thread->posts = $this->CI->db->query($sql, $topic_id)->result();

		return $thread;
	}


	public function create_thread($thread, $forum_id)
	{
		assert(count($thread->posts) > 0);

		//sätt author_phpbb_id till dummy-user om null
		foreach($thread->posts as &$pst)
			if($pst->author_phpbb_id == null)
				$pst->author_phpbb_id = $this->phpbb_dummy_user_id;

		//skapa topic
		$data = array(
			'forum_id' => $forum_id,
			'topic_title' => $thread->posts[0]->subject,
			'topic_poster' => $thread->posts[0]->author_phpbb_id,
			'topic_time' => $thread->posts[0]->created_time,
			'topic_views' => $thread->topic->num_views,
			'topic_first_poster_name' => $thread->posts[0]->author_name,
			'topic_first_poster_colour' => 'FFFFFF',
			'topic_last_poster_id' => end($thread->posts)->author_phpbb_id,
			'topic_last_poster_name' => end($thread->posts)->author_name,
			'topic_last_poster_colour' => 'FFFFFF',
			'topic_last_post_subject' => end($thread->posts)->subject,
			'topic_last_post_time' => end($thread->posts)->created_time,
			'topic_visibility' => 1,
			'topic_posts_approved' => count($thread->posts),
		);
		$this->CI->db->insert('phpbb_topics', $data);
		$topic_id = $this->CI->db->insert_id();
		// $topic_id = 0;//////////////////////////////////

		//skapa posts
		$first_post_id = null;
		$last_post_id = null;
		foreach($thread->posts as $post)
		{
			$data = array(
				'topic_id' => $topic_id,
				'forum_id' => $forum_id,
				'poster_id' => $post->author_phpbb_id,
				'post_time' => $post->created_time,
				'post_subject' => $post->subject,
				'post_text' => $post->body,
				'post_visibility' => 1,
				'post_checksum' => md5($post->body),
				'bbcode_uid' => substr(base_convert(uniqid(), 16, 36), 0, 8), //inte 100% legit men ska nog funka bra
			);
			$post_id = $this->CI->db->insert('phpbb_posts', $data);
			// $post_id = 0;////////////////////////
			
			$last_post_id = $post_id;
			if($first_post_id == null)
				$first_post_id = $post_id;
		}

		//uppdatera topic
		$this->CI->db
			->where(array('topic_id' => $topic_id))
			->update('phpbb_topics', array('topic_first_post_id' => $first_post_id, 'topic_last_post_id' => $last_post_id));
	}
}
?>