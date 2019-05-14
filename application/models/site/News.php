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

		//WHERE-sats:
		//WHERE postens forum_id finns i denna lista:
		//	lista med forum_id:s där group_id finns i denna lista:
		//		lista med group_id:s för user_id = ? (lista med alla grupper som medlemmen är med i)
		$where = $this->member->valid
			? 'WHERE topics.forum_id IN (SELECT forum_id FROM phpbb_acl_groups WHERE group_id IN (SELECT group_id FROM phpbb_user_group WHERE user_id = '. $this->db->escape($this->member->phpbb_user_id) .'))'
			: 'WHERE topics.forum_id IN (SELECT forum_id FROM phpbb_acl_groups WHERE group_id = 1)'; //group_id 2 = GUEST
		$sql =
			'SELECT
				posts.post_id,
				posts.topic_id,
				members.name,
				users.user_colour AS user_color,
				topics.topic_title,
				posts.post_text AS text,
				posts.post_time AS post_timestamp,
				FROM_UNIXTIME(posts.post_time) AS post_datetime,
				(SELECT COUNT(*) FROM phpbb_posts WHERE topic_id = posts.topic_id AND post_time < posts.post_time) AS no_of_earlier_posts
			FROM phpbb_posts posts
			INNER JOIN ssg_members members
				ON posts.poster_id = members.phpbb_user_id
			INNER JOIN phpbb_topics topics
				ON posts.topic_id = topics.topic_id
			INNER JOIN phpbb_users users
				ON posts.poster_id = users.user_id
			'. $where .'
			ORDER BY post_time DESC
			LIMIT ?';
		$posts = $this->db->query($sql, array($length))->result();
		
		$posts_per_page = 10;
		foreach($posts as $post)
		{
			//lista ut vilken sida posten ligger på
			//(egentligen vilken nummerordning första posten har på den sida som gäller)
			//ex: post 17 ska ha start 10, post 31 ska ha start 30
			$post->start = floor($post->no_of_earlier_posts / $posts_per_page) * $posts_per_page; //avrunda ner till närmsta tiotal

			//länk till post (ex: "/forum/viewtopic.php?t=105&start=10#p549")
			$post->url = base_url("forum/viewtopic.php?t={$post->topic_id}". ($post->start > 0 ? "&start={$post->start}": null) ."#p{$post->post_id}");

			//sanera text-preview
			$post->text = preg_replace('/\n|<br \/>/', ' ', $post->text); //byta ut newlines mot mellanrum
			$post->text = strip_tags($post->text); //ta bort html-tags
			$post->text = strip_bbcode($post->text); //ta bort bbcode-tags
			$post->text = strlen($post->text) > 128 ? mb_substr($post->text, 0, 128) .'...' : $post->text; //korta ner lång text

			//relativ tidssträng
			$post->relative_time_string = relative_time_string($post->post_timestamp);
		}


		return $posts;
	}
}