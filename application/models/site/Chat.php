<?php
/**
 * Modell för Chat-meddelanden
 */
class Chat extends CI_Model
{
	private $min, $hour, $day, $week, $days_swe, $message_max_length;

	public function __construct()
	{
		parent::__construct();

		// tidsspann i sekunder
		$this->min = 60;
		$this->hour = 3600;
		$this->day = 86400;
		$this->six_days = 518400;
		$this->week = 604800;
		$this->message_max_length = 1000;
		$this->days_swe = array(1=>'måndag', 'tisdag', 'onsdag', 'torsdag', 'fredag', 'lördag', 'söndag');
	}

	
	public function get_messages($message_id, $length)
	{
		return $this->api_get_messages(array('message_id' => $message_id, 'length' => $length));
	}

	/**
	 * Ger de senaste chat-meddelandena baserat på GET-variablerna length och message_id.
	 *
	 * @return array Array med objekt som ska konverteras till JSON.
	 */
	public function api_get_messages($get)
	{
		//parameter-sanering
		if(
			!key_exists('length', $get) || !is_numeric($get['length']) //om length är tom eller inkorrekt
			|| (isset($get['message_id']) && !is_numeric($get['message_id'])) //kolla bara message_id om den finns
		)
			return false;

		//GET-variabler
		$message_id = key_exists('message_id', $get) ? $get['message_id']-0 : null;
		$length = $get['length']-0;

		//om $message_id är null så behövs ingen where-sats och det senaste meddelandet ladds först
		$where_clause = $message_id != null
			? 'WHERE c.created < (SELECT created FROM ssg_chat WHERE id = '. $this->db->escape($message_id) .')'
			: null;

		$sql =
			'SELECT
				c.*,
				c.text AS text_plain,
				UNIX_TIMESTAMP(created) AS created_timestamp,
				UNIX_TIMESTAMP(last_edited) AS last_edited_timestamp,
				m.name, m.phpbb_user_id
			FROM ssg_chat c
			INNER JOIN ssg_members m
				ON c.member_id = m.id
			'. $where_clause .'
			ORDER BY c.created DESC
			LIMIT 0, '. $this->db->escape($length);
		$messages = $this->db->query($sql)->result();

		//formatering
		foreach($messages as $message)
		{
			$message->timespan_string = $this->timespan_string($message->created_timestamp, $message->last_edited_timestamp); //timespan-strängen
			$message->text = $this->format_text($message->text); //text
		}

		return $messages;
	}

	/**
	 * Hämta data relaterat till ett meddelande.
	 *
	 * @param array $get GET_variabler
	 * @return void
	 */
	public function api_get_message($get)
	{
		//parameter-sanering
		if(!key_exists('message_id', $get) || !is_numeric($get['message_id']))
			return false; //bad request
		
		$message_id = $get['message_id']-0;

		$sql =
			'SELECT
				c.*,
				UNIX_TIMESTAMP(created) AS created_timestamp,
				UNIX_TIMESTAMP(last_edited) AS last_edited_timestamp,
				m.name, m.phpbb_user_id
			FROM ssg_chat c
			INNER JOIN ssg_members m
				ON c.member_id = m.id
			WHERE c.id = ?
			ORDER BY c.created DESC';
		$row = $this->db->query($sql, $message_id)->row();

		$row->text_plain = $row->text; //////////////////temp

		return $row;
	}

	/**
	 * Lägger till chat-meddelande baserat på POST-variablen text och den inloggade användaren.
	 *
	 * @param array $vars POST-variabler
	 * @return int HTTP status code
	 */
	public function api_post($vars)
	{
		//--parameter-sanering--
		if(!isset($vars) || !key_exists('text', $vars) || strlen($vars['text']) < 1 || strlen($vars['text']) > $this->message_max_length)
			return 400; //bad request

		$text = $vars['text'];

		//sanering
		$text = trim($text);
		$text = htmlentities($text);

		//skicka text som nuvarande inloggade person
		$this->add_message($this->member->id, $text);

		return 200; //ok
	}

	/**
	 * Ta bort chat-meddelande.
	 * Icke admins kan bara ta bort sina egna meddelanden
	 *
	 * @param array $vars GET-variabler. Måste innehålla message_id.
	 * @return int HTTP status code
	 */
	public function api_delete($vars)
	{
		//variabel saknas eller är felaktig
		if(!isset($vars) || !key_exists('message_id', $vars) || !is_numeric($vars['message_id']))
			return 400; //bad request

		$message_id = $vars['message_id']-0;

		//hämta meddelandets skapare
		$row = $this->db->query('SELECT member_id FROM ssg_chat WHERE id = ?', $message_id)->row();
		
		//finns inget meddelande med detta id
		if($row == null)
			return 400; //bad request
		
		//får medlemmen ta bort detta meddelande?
		$this->load->library("Permissions");
		if(!$this->permissions->has_permissions(array('super', 's1')) || !$row->member_id == $this->member->id) //är inte admin eller är inte skaparen av meddelandet
			return 401; //unauthorized

		$this->db->delete('ssg_chat', array('id' => $message_id));
		
		return 200; //ok
	}

	/**
	 * Uppdatera meddelande.
	 *
	 * @param array $vars GET-variabler. Måste innehålla message_id och text.
	 * @return int HTTP status code
	 */
	public function api_put($vars)
	{
		//variabler saknas eller är felaktiga
		if(
			!isset($vars) || !key_exists('message_id', $vars) || !is_numeric($vars['message_id'])
			|| !key_exists('text', $vars) || strlen($vars['text']) < 1 || strlen($vars['text']) > $this->message_max_length
		)
			return 400; //bad request
		
		$message_id = $vars['message_id']-0;
		$text = $vars['text'];

		//hämta meddelandets skapare
		$row = $this->db->query('SELECT member_id FROM ssg_chat WHERE id = ?', $message_id)->row();

		//finns inget meddelande med detta id
		if($row == null)
			return 400; //bad request
		
		//får medlemmen uppdatera detta meddelande?
		$this->load->library("Permissions");
		if(!$this->permissions->has_permissions(array('super', 's1')) || !$row->member_id == $this->member->id) //är inte admin eller är inte skaparen av meddelandet
			return 401; //unauthorized
		
		$now = date('Y-m-d G:i:s');
		$this->db
			->where(array('id' => $message_id))
			->update('ssg_chat', array('text' => $text, 'last_edited' => $now));

		return 200;
	}

	/**
	 * Trimmar text och tar bort tags.
	 * Gör om länkar till "[länk]"
	 * Gör om smileys till emojis.
	 * Formaterar om *bold* _underline_ {italic}.
	 * Gör om JIP/QIP/NOSHOW
	 *
	 * @param string $text
	 * @return string
	 */
	public function format_text($text)
	{
		//variabler
		$regex_url = '/https?:\/\/(www\.)?[-a-zA-Z0-9@:%._\+~#=]{2,256}\.[a-z]{2,6}\b([-a-zA-Z0-9@:%_\+.~#?&\/\/=]*)/i';
		$smileys = 
			array(
				'/(?<!\w):\)/', // :)
				'/(?<!\w)=\)/', // =)
				'/(?<!\w);\)/', // ;)
				'/(?<!\w):\(/', // :(
				'/(?<!\w):P/i', // :P
				'/(?<!\w);P/i', // ;P
				'/(?<!\w)=P/i', // =P
				'/(?<!\w):D/i', // :D
				'/(?<!\w);D/i', // ;D
				'/(?<!\w):O/i', // :O
				'/(?<!\w):\'\(/', // :'(
				'/(?<!\w)XD/i', // XD
				'/(?<!\w)X\(/i', // X(
				'/(?<!\w)8\)/', // 8)
				'/(?<!\w)\^\^/', // ^^
				'/(?<!\w):X/i', // :X
				'/(?<!http|https):\/(?!\/)/i', // :/
				'/(?<!\w)(<|&lt;)3/' //<3
			);
		$emojis = 	array('🙂', '🙂', '😉', '🙁', '😋', '😜', '😋', '😀', '😁', '😮', '😢', '😂', '😣', '😎', '😊', '🤐', '😕', '❤');

		//sanering (en del meddelanden kan vara "smutsiga" och städas upp även här såsom vid input)
		$text = trim($text);
		$text = strip_tags($text); //gör troligen ingenting eftersom htmlentities() kördes vid input

		//länkar
		$text = preg_replace($regex_url, '<span class="link">[<a href="$0" target="_blank">länk</a>]</span>', $text); //case insensitive replace

		//ersätt smileys med emojis ":)" -> "🙂"
		$text = preg_replace($smileys, $emojis, $text);

		// *bold* -> <strong>bold</strong>
		$text = preg_replace('/(?:\*{1})([\w\s]+?)(?:\*{1})/', '<strong>$1</strong>', $text);
		//$0 är full match, dvs. '*tjock text*'. $1 är första matchade gruppen
		//en grupp är regex som ligger inom parenteser
		//'?:' definierar en grupp som non-capturing vilket gör att '(.+)' är den enda gruppen som fångas
		
		//_underscore_ -> <u>underscore</u>
		$text = preg_replace('/(?:_{1}?)([\w\s]+?)(?:_{1}?)/', '<u>$1</u>', $text);
		
		//{italic} -> <i>italic</i>
		$text = preg_replace('/(?:\{{1}?)([\w\s]+?)(?:\}{1}?)/', '<i>$1</i>', $text);

		//JIP/QIP/NOSHOW
		$text = preg_replace(
			array('/(?<!\w)jip(?!\w)/i', '/(?<!\w)qip(?!\w)/i', '/(?<!\w)noshow(?!\w)/i'),
			array('<span class="text-jip">JIP</span>', '<span class="text-qip">QIP</span>', '<span class="text-noshow">NOSHOW</span>'),
			$text
		);

		return $text;
	}

	/**
	 * Hämtar id för det tidigaste meddelandet.
	 *
	 * @return int
	 */
	public function get_last_message_id()
	{
		return $this->db->query('SELECT id FROM ssg_chat ORDER BY created ASC LIMIT 1')->row()->id;
	}

	/**
	 * Ger formaterad, relativ tidssträng.
	 * Ex: '(2 timmar sedan)'
	 * '(i måndags 11:07)'
	 *
	 * @param int $date Skapad datum (unix epoch)
	 * @param int $edited Senast redigerad (unix epoch)
	 * @return void
	 */
	public function timespan_string($date, $last_edited)
	{
		$edited = isset($last_edited);
		if($edited)
			$date = $last_edited; //använd last_edited om meddelandet har blivit redigerat

		$now = time();
		$diff = abs($now - $date);
		$date_string = date('Y-m-d G:i', $date);
		$edited_prefix = $edited ? 'redigerad: ' : null;

		if($diff < $this->min) //mindre än en minut sedan
			$output = 'nyss';
		else if($diff < $this->hour) //mer än en minut sedan (ex: '35 minuter sedan')
		{
			$minutes = floor($diff / $this->min);
			$units_string = $minutes == 1 ? 'minut' : 'minuter';
			$output = "$minutes $units_string sedan";
		}
		else if($diff < $this->day) //mer än en timme sedan (ex: 'idag 20:05')
			$output = 'idag '. date('G:i', $date);
		else if($diff < ($this->day * 2)) //mer är en OCH mindre än två dagar sedan (ex: 'igår 0:22')
			$output = 'igår '. date('G:i', $date);
		else if($diff < $this->six_days) //mer är en dag sedan (ex: 'i fredags 13:49') (använd six_days so att det inte står "i fredags" på en fredag)
			$output = 'i '. $this->days_swe[date('N', $date)] . 's '. date('G:i', $date);
		else //mer än sex dagar sedan
			$output = $date_string;

		return "<span title='$date_string' data-toggle='tooltip'>({$edited_prefix}$output)</span>";
	}

	/**
	 * Lägger till meddelande i databasen
	 *
	 * @param int $member_id
	 * @param string $text
	 * @param int $time Unix epoch-tid
	 * @return void
	 */
	public function add_message($member_id, $text, $time = null)
	{
		//$time är by default NU
		if($time == null)
			$time = time();

		$data = array(
			'member_id' => $member_id,
			'created' => date('Y-m-d G:i:s', $time),
			'text' => $text
		);

		$this->db->insert('ssg_chat', $data);
	}

	/**
	 * Tar bort gamla meddelanden från ssg_chat.
	 * Ser till att det inte finns mer än $max_messages i databasen.
	 *
	 * @return void
	 */
	public function prune_messages()
	{
		$max_messages = 512; //idunno, justera värdet om du vill

		$sql =
			'SELECT created FROM ssg_chat
			ORDER BY created DESC
			LIMIT ?, 1';
		$row = $this->db->query($sql, $max_messages)->row();

		//no pruning needed
		if(!$row)
			return;
		
		//datum för nyaste meddelandet som ligger utanför "pruning range" och ska tas bort
		$first_message_to_prune = $row->created;

		$sql =
			'DELETE FROM ssg_chat
			WHERE created <= ?';
		$this->db->query($sql, $first_message_to_prune);
	}

	/**
	 * Importerar meddelanden från smf-chat/shout/scummbar
	 *
	 * @return void
	 */
	public function import_shouts()
	{
		
	}
}