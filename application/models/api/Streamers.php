<?php
/**
 * API Streamers
 */
class Streamers extends CI_Model
{
	private
		$youtube_api_key,
		$youtube_stream_url_prefix = 'https://www.youtube.com/watch?v=',
		$youtube_api_url_live_prefix = 'https://www.googleapis.com/youtube/v3/search?part=snippet&eventType=live&type=video'; //fort. &key=<key>&channelId=<channel id>

	private
		$twitch_api_key,
		$twitch_stream_url_prefix = 'https://www.twitch.tv/',
		$twitch_api_url_live_prefix = '???';

	public function __construct()
	{
		parent::__construct();

		$this->load->helper('api_keys');
		$this->youtube_api_key = api_key('youtube');
		$this->twitch_api_key = api_key('twitch');
		define('TIMESPAN_MAX', 10); //10 min mellan uppdateringar/intervaller
	}

	/**
	 * Hämta antalet minuter sedan senaste intervall.
	 * Om den är mer än 10 min: uppdatera tidpunkten.
	 *
	 * @return bool Är det tajm för en update?
	 */
	public function check_interval()
	{
		//kolla om timespan har gått över tiden
		$sql =
			'SELECT TIMESTAMPDIFF(MINUTE, last_performed, NOW()) AS timespan
			FROM ssg_intervals
			WHERE id = 3';
		$timespan = $this->db->query($sql)->row()->timespan;

		//uppdatera timespan om den har gått över tiden
		if($timespan >= TIMESPAN_MAX)
		{
			$this->db->query('UPDATE ssg_intervals SET last_performed = NOW() WHERE id = 3');
			return true; //tid sedan förra update:en
		}
		else
			return false;
		
	}


	public function update_youtube()
	{
		//--hämta alla youtube-kanal-id:n--
		$channels = array();
		foreach($this->db->query('SELECT member_id, channel_youtube FROM ssg_streamers WHERE channel_youtube IS NOT NULL')->result() as $row)
		{
			$channel = new stdClass;
			$channel->id = $row->channel_youtube;
			$channel->member_id = $row->member_id;

			$channels[] = $channel;
		}
		
		///////////////////////testing
		// $channel = new stdClass;
		// $channel->id = 'UCODDGRneUTTsCS419HWafMA'; //smorfty
		// $channel->id = 'UCKx31X4HNsuoLZjWiW7jbgQ'; //random livestreamande kanal
		// $channel->member_id = 1655; //smorfty får fejk-kanalen
		// $channels = array($channel); //kolla bara en kanal
		///////////////////////

		//--skicka requests till youtube API:n--
		require_once('src/guzzle/autoloader.php');
		$client = new \GuzzleHttp\Client();

		foreach($channels as $channel)
		{
			$member_id = $channel->member_id;
			$url = "{$this->youtube_api_url_live_prefix}&key={$this->youtube_api_key}&channelId={$channel->id}"; //skapa request-url
			
			try
			{
				$request = new \GuzzleHttp\Psr7\Request('GET', $url); //förbered request
				//skicka request och definiera funktion som körs vid response, "use()" skickar med fler parametrar till temp-funktionen
				$promise = $client->sendAsync($request)->then(function($response) use($member_id)
				{
					$this->response_youtube($response, $member_id);
				});
			}
			catch (\GuzzleHttp\Exception\ClientException $error)
			{
				echo 'fail';
			}
		}
		$promise->wait(); //vänta på alla responses
	}


	public function update_twitch()
	{
	}

	/**
	 * Körs varje gång ett response kommer från youtube API:n.
	 *
	 * @param string $response JSON-sträng
	 * @param int $member_id
	 * @return void
	 */
	private function response_youtube($response, $member_id)
	{
		$data = json_decode($response->getBody());

		//resna db-värden om negativt resultat, mata in youtube_video_id annars
		if($response->getStatusCode() == 200 && count($data->items) > 0) //success och har live livestream
			$update_vars = array('youtube_video_id' => $data->items[0]->id->videoId); //mata in video-id i db
		else //inte success eller tomt resultat
			$update_vars = array('youtube_video_id' => null); //rensa video-id

		$this->db->where('member_id', $member_id)->update('ssg_streamers', $update_vars);
	}


	private function response_twitch($response)
	{

	}

	/**
	 * Laddar streamer-data från databasen.
	 *
	 * @param int $member_id
	 * @return object
	 */
	public function get_streamer($member_id)
	{
		$sql =
			'SELECT
				s.channel_youtube,
				s.channel_twitch,
				#s.youtube_video_id,
				#s.youtube_video_id IS NOT NULL AS youtube_online,
				#twitch_online,
				s.prefered,
				m.name AS name,
				m.id AS member_id,
				g.name AS group_name,
				g.code AS group_code,
				m.group_id,
				role_id,
				r.name AS role_name
			FROM ssg_streamers s
			INNER JOIN ssg_members m
				ON s.member_id = m.id
			LEFT JOIN ssg_groups g
				ON m.group_id = g.id
			LEFT JOIN ssg_roles r
				ON m.role_id = r.id
			WHERE s.member_id = ?';
		$row = $this->db->query($sql, $member_id)->row();

		// $row->youtube_url = $this->youtube_stream_url_prefix . $row->youtube_video_id;

		return $row;
	}

	/**
	 * Laddar streamer-data från databasen.
	 *
	 * @return array
	 */
	public function get_streamers()
	{
		$sql =
			'SELECT
				s.channel_youtube,
				s.channel_twitch,
				#s.youtube_video_id,
				#s.youtube_video_id IS NOT NULL AS youtube_online,
				#twitch_online,
				s.prefered,
				m.name AS name,
				m.id AS member_id,
				g.name AS group_name,
				g.code AS group_code,
				m.group_id,
				role_id,
				r.name AS role_name
			FROM ssg_streamers s
			INNER JOIN ssg_members m
				ON s.member_id = m.id
			LEFT JOIN ssg_groups g
				ON m.group_id = g.id
			LEFT JOIN ssg_roles r
				ON m.role_id = r.id
			ORDER BY
				#online_youtube DESC,
				#online_twitch DESC,
				s.sorting ASC';
		$result = $this->db->query($sql)->result();

		// foreach($result as $row)
		// 	$row->youtube_url = $this->youtube_stream_url_prefix . $row->youtube_video_id;

		return $result;
	}
}