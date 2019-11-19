<?php
/**
 * Modell för nyhetssidan/huvudsidan
 */
class News extends CI_Model
{

	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Hämta nyhetsartiklar.
	 *
	 * @param int $page Sida (börjar på 0, default: 0)
	 * @param int $results_per_page Resultat per sida (default: 5)
	 * @return array Objektarray
	 */
	public function get_news($page = 0, $results_per_page = 5)
	{
		//variabler
		$text_max_length = 1200;
		$news = new stdClass;
		$news->results_per_page = $results_per_page;

		//where-sats, begränsa vilka forum som ska kollas
		$where = 'WHERE forum_id = 5 OR forum_id = 34';

		//topics
		$sql =
			'SELECT SQL_CALC_FOUND_ROWS
				topics.topic_id AS id, #topic_id
				forum_id,
				topic_title AS title, #title
				members.name AS poster_name, #poster_name
				DATE_FORMAT(FROM_UNIXTIME(topic_time), "%Y-%m-%d %H:%i") AS date #date
			FROM phpbb_topics topics
			LEFT OUTER JOIN ssg_members members
				ON topics.topic_poster = members.phpbb_user_id
			WHERE
				(forum_id = 5
				OR forum_id = 34)
				AND topic_delete_time = 0
			ORDER BY topics.topic_time DESC
			LIMIT ?, ?';
		$news->topics = $this->db->query($sql, array($results_per_page * $page, $results_per_page))->result();

		//hämta totala antalet topics
		$news->total_results = $this->db->query('SELECT FOUND_ROWS() as total_results')->row()->total_results;


		//hitta första posten i varje topic och hämta texten
		foreach($news->topics as $topic)
		{
			$sql =
				'SELECT post_text AS text
				FROM phpbb_posts
				WHERE topic_id = ?
				ORDER BY post_time ASC
				LIMIT 1';
			$topic->text = $this->db->query($sql, $topic->id)->row()->text;

			//ta bort html, bbcode-text blir kvar
			$topic->text = strip_tags($topic->text);
			
			//om text är för lång: kapa
			if(strlen($topic->text) > $text_max_length)
				$topic->text = mb_substr($topic->text, 0, $text_max_length) .'...';

			//ta bort specifik bbcode
			$topic->text = str_replace(
				array('[center]', '[/center]'),
				null,
				$topic->text
			);

			//parse:a bbcode till html
			$topic->text = bbcode_parse($topic->text); //helper-funktion
		}

		return $news;
	}

	/**
	 * Hämtar previews för de senaste foruminläggen.
	 * Visar enbart posts som den inloggade eller ej inloggade användaren haar tillgång till.
	 *
	 * @param int $length Antal posts (default: 5)
	 * @return array
	 */
	public function get_latest_posts($length = 5)
	{
		if(!is_numeric($length))
			throw new Exception("\$length ogiltig: {$length}");

		$sql =
			'SELECT
				forum.forum_name,
				topic.topic_id,
				topic.topic_title,
				latest_post.post_id,
				users.username name,
				users.user_colour AS user_color,
				latest_post.post_text AS text,
				latest_post.post_time AS post_timestamp,
				FROM_UNIXTIME(latest_post.post_time) AS post_datetime,
				(SELECT COUNT(*) FROM phpbb_posts WHERE topic_id = topic.topic_id AND post_time < latest_post.post_time) AS no_of_earlier_posts
			FROM phpbb_topics topic
			INNER JOIN phpbb_forums forum
				ON topic.forum_id = forum.forum_id
			INNER JOIN phpbb_posts latest_post #latest_post
				ON topic.topic_last_post_id = latest_post.post_id
			INNER JOIN phpbb_users users #users
				ON latest_post.poster_id = users.user_id
			WHERE
				topic.forum_id IN (SELECT forum_id FROM phpbb_acl_groups WHERE auth_role_id != 16 AND group_id IN (SELECT group_id FROM phpbb_user_group WHERE user_id = ?))
				AND topic_delete_time = 0
			ORDER BY latest_post.post_time DESC
			LIMIT ?';
		$topics = $this->db->query($sql, array($this->member->phpbb_user_id, $length))->result();
		
		$posts_per_page = $this->db->query('SELECT config_value FROM phpbb_config WHERE config_name = "posts_per_page"')->row()->config_value;
		foreach($topics as $topic)
		{
			//lista ut vilken sida posten ligger på
			//(egentligen vilken nummerordning första posten har på den sida som gäller)
			//ex: post 17 ska ha start 10, post 31 ska ha start 30 (om 10 post_per_page dvs.)
			$topic->start = floor($topic->no_of_earlier_posts / $posts_per_page) * $posts_per_page; //avrunda ner till närmsta tiotal

			//länk till post (ex: "/forum/viewtopic.php?t=105&start=10#p549")
			$topic->url = base_url("forum/viewtopic.php?t={$topic->topic_id}". ($topic->start > 0 ? "&start={$topic->start}": null) ."#p{$topic->post_id}");

			//sanera text-preview
			$topic->text = preg_replace('/\n|<br \/>/', ' ', $topic->text); //byta ut newlines till mellanrum
			$topic->text = strip_tags($topic->text); //ta bort html-tags
			$topic->text = strip_bbcode($topic->text); //ta bort bbcode-tags
			$topic->text = strlen($topic->text) > 128 ? mb_substr($topic->text, 0, 128) .'.' : $topic->text; //korta ner lång text

			//relativ tidssträng
			$topic->relative_time_string = relative_time_string($topic->post_timestamp);

			//kolla om senaste posten är läst
			$topic->has_unread_post = $this->is_post_new($this->member->phpbb_user_id, $topic->post_id);
		}

		return $topics;
	}

	/**
	 * Är specifierad post ny/oläst för specifierad user?
	 *
	 * @param int $user_id phpBB user-id
	 * @param int $post_id
	 * @return boolean
	 */
	private function is_post_new($user_id, $post_id)
	{
		//$lastmark (tiden där "Markera alla trådar som lästa"-länken klickades)
		$lastmark = $this->db->query('SELECT user_lastmark FROM phpbb_users WHERE user_id = ?', array($user_id))->row()->user_lastmark;

		//post ang. posten
		$row = $this->db->query('SELECT forum_id, topic_id, post_time FROM phpbb_posts WHERE post_id = ?', array($post_id))->row();
		$post_time = $row->post_time;
		$forum_id = $row->forum_id;
		$topic_id = $row->topic_id;

		//----kolla om posten är oläst----

		//om posten gjordes före $lastmark så är den inte ny
		//$lastmark sätts antingen när användaren trycker på "Markera alla trådar som lästa"
		//eller automatiskt av phpBB när man manuellt har läst alla posts
		if($post_time < $lastmark)
			return false;

		//ligger posten i en track:ad topic?
		$query = $this->db->query(
			'SELECT 0 FROM phpbb_topics_track WHERE user_id = ? AND topic_id = ? AND mark_time >= ?',
			array($user_id, $topic_id, $post_time)
		);
		if($query->num_rows() > 0)
			return false; //ja det gör den, då är den inte ny

		//ligger posten i ett track:at forum?
		$query = $this->db->query(
			'SELECT 0 FROM phpbb_forums_track WHERE user_id = ? AND forum_id = ? AND mark_time >= ?',
			array($user_id, $forum_id, $post_time)
		);
		if($query->num_rows() > 0)
			return false; //ja det gör den, då är den inte ny

		return true; //den är inte trackad någonstans och efter $lastmark, då är den ny!
	}
}