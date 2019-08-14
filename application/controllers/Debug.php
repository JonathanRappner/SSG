<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Debug extends CI_Controller
{
	
	public function __construct()
	{
		parent::__construct();

		if(!$this->member->valid || $this->member->id != 1655) //Smorfty only
			show_404();
	}

	public function index()
	{
		echo '<p><a href="debug/import_chat">Importera chat</a></p>';
		echo '<hr>';

		
	}

	/**
	 * Importera senaste chat-meddelanden.
	 *
	 * @return void
	 */
	public function import_chat()
	{
		$this->load->model('site/chat');
		$latest_chat = $this->db->query('SELECT created FROM ssg_chat ORDER BY created DESC LIMIT 1')->row()->created;
		$this->chat->import_shouts($latest_chat);

		echo "<p>Importerade chat från {$latest_chat} och frammåt</p>";
	}

	public function foobar()
	{
		//--använd /forum/import.php för forum-imports--


		//--kolla SMF-topics som även finns i phpBB och har nyare posts än phpBB-varianten, efter specifikt datum--
		// $date_limit = strtotime('2019-07-09 17:15');
		// $smf_topics = $this->db->query('SELECT t.id_topic, first.subject, first.poster_time AS first_poster_time, last.poster_time AS last_poster_time FROM smf_topics t INNER JOIN smf_messages last ON t.id_last_msg = last.id_msg INNER JOIN smf_messages first ON t.id_first_msg = first.id_msg WHERE last.poster_time > ? ORDER BY t.id_board', array($date_limit))->result();
		// foreach($smf_topics as $smf_topic)
		// {
		// 	$phpbb_topic = $this->db->query('SELECT t.topic_id, last.post_time, t.forum_id FROM phpbb_topics t INNER JOIN phpbb_posts last ON t.topic_last_post_id = last.post_id WHERE t.topic_time = ?', array($smf_topic->first_poster_time))->row();
		// 	if($phpbb_topic && $smf_topic->last_poster_time > $phpbb_topic->post_time)
		// 		echo "$smf_topic->subject (SMF-topic-id: $smf_topic->id_topic) (phpBB-topic-id: $phpbb_topic->topic_id) (forum-id: $phpbb_topic->forum_id)<br>"; // (smf: $smf_topic->last_poster_time phpBB: $phpbb_topic->post_time)
		// }

		//--kolla vilka topics som postades efter specifierat datum--
		// $date_limit = strtotime('2019-07-09 17:15');
		// $new_topics = $this->db->query('SELECT subject, poster_time, t.id_board AS board_id FROM smf_topics t INNER JOIN smf_messages m ON t.id_first_msg = m.id_msg LEFT OUTER JOIN phpbb_topics ON m.poster_time = phpbb_topics.topic_time WHERE poster_time > ? AND phpbb_topics.topic_id IS NULL ORDER BY t.id_board ASC, poster_time ASC', array($date_limit))->result();
		// foreach($new_topics as $topic)
		// 	echo "$topic->subject (Board: $topic->board_id)<br>";

		//sätt phpBB_ranks efter ssg_promotions
		// $members = $this->db->query('SELECT id, phpbb_user_id FROM ssg_members WHERE phpbb_user_id IS NOT NULL')->result();
		// foreach($members as $member)
		// {
		// 	$row = $this->db->query('SELECT p.rank_id FROM ssg_promotions p WHERE member_id = ? ORDER BY p.date DESC LIMIT 1', array($member->id))->row();
		// 	if($row)
		// 	{
		// 		$this->db
		// 			->where('user_id', $member->phpbb_user_id)
		// 			->update('phpbb_users', array('user_rank' => $row->rank_id));
		// 	}
		// }
	}
}
