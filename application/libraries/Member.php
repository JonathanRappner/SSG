<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Hanterar användarlogins.
 * Laddas automatiskt på varje sida.
 * Kollar om login-session från gamla sidan existerar och kopierar till sitt egna session-system.
 * Annars visar den ett login-formulär som kollar uppgifter mot den gamla sidans databas.
 */
class Member
{
	public
		$valid = false; //Är en existerande användare inloggad?

	/**
	 * Listar ut vem som är inloggad baserat på session.
	 * Om ingen valid session hittas: avbryt och låt $this->valid förbli false.
	 */
	public function __construct()
	{
		// Assign the CodeIgniter super-object
		$this->CI =& get_instance();

		//försök hämta phpbb session id
		$this->phpbb_session_id = $this->get_phpbb_session_id();
		$phpbb_user_id = $this->get_phpbb_session_user_id($this->phpbb_session_id); //hitta phpbb_user_id
		$member_id = $this->get_phpbb_session_member($phpbb_user_id); //hitta SSG-member-id via phpBB-session


		//--försök hitta inloggad användare genom phpbb-cookie eller session-variabel--
		if($member_id) //medlem hittades via phpBB-session
		{
			$this->id = $member_id;
			$this->CI->session->member_id = $member_id;
		}
		else if(isset($this->CI->session->member_id)) //om ssg_session finns, använd den
		{
			//använd ssg_session samtidigt som phpbb-sessions eftersom phpbb-sessions tar slut fort och måste regenereras
			//medan ssg_sessions varar i typ två dagar
			$this->id = $this->CI->session->member_id;
		}
		else if($phpbb_user_id) //phpBB-session finns men det finns ingen ssg_member hittades   
		{
			$member_id = $this->create_ssg_member($phpbb_user_id);
			$this->id = $member_id;
			$this->CI->session->member_id = $member_id;
		}
		else //ingen session finns, låt $this->valid vara false så login-formuläret visas
			return;
		
		/*** Lyckad inloggning ***/
		
		// debugging
		// $this->id = 1603; ////////Nehls
		// $this->id = 1207; ////////Nabel
		// $this->id = 136;  ////////Kalle
		// $this->id = 23;   ////////Dudemeister
		// $this->id = 1709; ////////Matez
		// $this->id = 1713; ////////ThoKaWi
		// $this->id = 1472; ////////Jonasson
		// $this->id = 1441; ////////Insane_laughter
		// $this->id = 1337; ////////Cowboy
		// if($this->id == 1655)
		// 	$this->id = 1675; ////////Gibby

		//sätt laddad medlemsdata till $this
		$this->set_member_data($this->id);

		//valid
		$this->valid = true;
	}

	/**
	 * Försöker identifiera inloggad användare (ssg_member.id) med hjälp av phpbb-cookie.
	 * Ger false om ingen är inloggad.
	 *
	 * @param int $phpbb_user_id phpBB-user id
	 * @return int SSG-id-nummer.
	 */
	private function get_phpbb_session_member($phpbb_user_id)
	{
		if(!$phpbb_user_id)
			return;

		//kolla upp session i db
		$sql =
			'SELECT id
			FROM ssg_members
			WHERE phpbb_user_id = ?';
		$query = $this->CI->db->query($sql, $phpbb_user_id);

		if($query->num_rows() <= 0)
			return;

		//return:a ssg-member_id
		return $query->row()->id;
	}

	/**
	 * Hämta phpbb-session-id om det finns
	 *
	 * @return string Är null om ingen session finns.
	 */
	private function get_phpbb_session_id()
	{
		//lista ut cookie-namn
		$cookie_pre = $this->CI->db->query('SELECT config_value FROM phpbb_config WHERE config_name = "cookie_name"')->row()->config_value;
		$cookie_name = $cookie_pre .'_sid';
		
		return key_exists($cookie_name, $_COOKIE)
			? $_COOKIE[$cookie_name]
			: null;
	}

	/**
	 * Hämta phpbb_user_id från phpbb_sessions
	 *
	 * @param string $phpbb_session_id
	 * @return int phpBB-user-id
	 */
	private function get_phpbb_session_user_id($phpbb_session_id)
	{
		if(!$phpbb_session_id)
			return;

		$row = $this->CI->db->query('SELECT session_user_id FROM phpbb_sessions WHERE session_id = ?', array($phpbb_session_id))->row();

		return $row && $row->session_user_id > 1 //user_id = 1 är oinloggad användare
			? $row->session_user_id
			: null;
	}

	/**
	 * Skapa ny medlem i ssg_members med data från
	 *
	 * @param [type] $phpbb_user_id
	 * @return void
	 */
	private function create_ssg_member($phpbb_user_id)
	{
		if(!$phpbb_user_id)
			return;

		//hitta medlemmens namn
		$member_name = $this->CI->db->query('SELECT username FROM phpbb_users WHERE user_id = ?', array($phpbb_user_id))->row()->username;

		//lista ut medlemmens nya id (auto increment funkar inte p.g.a. key constraints i databasen)
		$member_id = $this->CI->db->query('SELECT MAX(id) max_id FROM ssg_members')->row()->max_id + 1;

		//skapa nya användaren
		$data = array(
			'id'=>$member_id,
			'name'=>$member_name,
			'phpbb_user_id'=>$phpbb_user_id,
			'registered_date'=>date('Y-m-d')
		);
		$this->CI->db->insert('ssg_members', $data); 

		return $member_id;
	}

	/**
	 * Hämtar och sätter medlemsdata till publika variabler i denna modell.
	 * 
	* @param int $member_id
	* @return void
	*/
	private function set_member_data($member_id)
	{
		$member = $this->get_member_data($member_id);

		foreach($member as $attr_name => $attr_value)
			$this->$attr_name = $attr_value;
	}

	/**
	 * Hämtar medlems-attribut såsom smeknamn och avatar.
	 *
	 * @param int $member_id Medlems-id
	 * @return object
	 */
	public function get_member_data($member_id)
	{
		//parameter-sanering, yo
		assert(!empty($member_id));
		assert(is_numeric($member_id));

		$sql =
			'SELECT
				registered_date, uid, is_active, group_id,
				ssg_members.id,
				ssg_members.name AS name,
				ssg_groups.name AS group_name,
				ssg_groups.code AS group_code,
				role_id,
				ssg_roles.name AS role_name,
				ssg_members.phpbb_user_id
			FROM ssg_members
			LEFT JOIN ssg_groups
				ON ssg_members.group_id = ssg_groups.id
			LEFT JOIN ssg_roles
				ON ssg_members.role_id = ssg_roles.id
			WHERE ssg_members.id = ?';
		$query = $this->CI->db->query($sql, $member_id);

		//felkoll
		if(!$member_data = $query->row())
			show_error("Hittade inte medlem med id: $member_id i databasen.");

		//--Avatar--
		$member_data->avatar_url = $this->get_phpbb_avatar($member_id);

		//--Permission Groups--
		$sql =
			'SELECT ssg_permission_groups.id AS persmission_id
			FROM ssg_permission_groups_members
			INNER JOIN ssg_permission_groups
				ON ssg_permission_groups_members.permission_group_id = ssg_permission_groups.id
			WHERE member_id = ?';
		$query = $this->CI->db->query($sql, $member_id);
		$member_data->permission_groups = array();
		foreach($query->result() as $row)
			$member_data->permission_groups[] = $row->persmission_id;

		//--Rank--

		//tomma värden
		$member_data->rank_id = null;
		$member_data->rank_name = null;
		$member_data->rank_icon = null;
		$member_data->rank_date = null;
		
		$sql =
			'SELECT
				name,
				rank_id, 
				icon,
				date
			FROM ssg_promotions
			INNER JOIN ssg_ranks
				ON ssg_promotions.rank_id = ssg_ranks.id
			WHERE ssg_promotions.member_id = ?
			ORDER BY date DESC
			LIMIT 1';
		$query = $this->CI->db->query($sql, $member_id);
		$rank = $query->row();
		
		//sätt grad efter senaste bumpningen
		if(!empty($rank))
		{
			$member_data->rank_id = $rank->rank_id;
			$member_data->rank_name = $rank->name;
			$member_data->rank_icon = $rank->icon;
			$member_data->rank_date = $rank->date;
			
		}
		
		return $member_data;
	}

	/**
	 * Hitta medlemmens phpbb3-avatar.
	 * Lokal, extern eller gravatar.
	 * Om ingen hittades, returnera unknown.png-bilden. 
	 *
	 * @param int $member_id
	 * @return string Avatarens fulla url.
	 */
	public function get_phpbb_avatar($member_id)
	{
		$sql =
			'SELECT
				phpbb.user_avatar,
				phpbb.user_avatar_type,
				phpbb.user_avatar_width
			FROM ssg_members m
			LEFT OUTER JOIN phpbb_users phpbb
				ON m.phpbb_user_id = phpbb.user_id
			WHERE m.id = ?';
		$row = $this->CI->db->query($sql, $member_id)->row();

		//avatar-länk
		if(empty($row->user_avatar_type)) //ingen avatar
			return base_url('images/unknown.png');
		else if($row->user_avatar_type == 'avatar.driver.upload') //avatar är upladdad på servern
			return base_url("forum/download/file.php?avatar={$row->user_avatar}");
		else if($row->user_avatar_type == 'avatar.driver.remote') //remote avatar
			return $row->user_avatar;
		else if($row->user_avatar_type == 'avatar.driver.gravatar') //gravatar
			return 'https://secure.gravatar.com/avatar/'. md5($row->user_avatar) .'?s='. $row->user_avatar_width;
	}

	/**
	 * Hämta medlemmars nick och id.
	 * Hämtar bara medlemmar från ssg_members.
	 *
	 * @return array Key: id, Value: nick
	 */
	public function get_members_simple()
	{
		//variabler
		$members = array();

		$sql =
			'SELECT id, name
			FROM ssg_members
			ORDER BY name ASC';
		$query = $this->CI->db->query($sql);
		foreach($query->result() as $row)
			$members[$row->id] = $row->name;
		
		return $members;
	}

	/**
	 * Enhetstest för denna modell.
	 *
	 * @return void
	 */
	public function unit_test()
	{
		$this->load->library('unit_test');

		//--Smorfty--
		//get member data
		$member_data = $this->get_member_data(1655);
		//name
		$this->unit->run(
			$member_data->name, //input
			'Smorfty', //expected
			'get_member_data()->name: Smorfty' //title
		);
		//avatar
		$this->unit->run(
			$member_data->avatar, //input
			'/ssg/new/../avs/avatar_1655_1516569554.jpeg', //expected
			'get_member_data()->avatar: Smorfty' //title
		);

		//--Kalle--
		//get member data
		$member_data = $this->get_member_data(136);
		//name
		$this->unit->run(
			$member_data->name, //input
			'Kalle', //expected
			'get_member_data()->name: Kalle' //title
		);
		//avatar
		$this->unit->run(
			$member_data->avatar, //input
			'http://t2.gstatic.com/images?q=tbn:ANd9GcSp5RYfKcHaJaCF42fEsMMOTWPW0HYa8voXct-IJHcbq2giN7rhElD98VXbNg', //expected
			'get_member_data()->avatar: Kalle' //title
		);

		//--Dudemeister--
		//get member data
		$member_data = $this->get_member_data(23);
		//name
		$this->unit->run(
			$member_data->name, //input
			'Dudemeister', //expected
			'get_member_data()->name: Dudemeister' //title
		);
		//avatar
		$this->unit->run(
			$member_data->avatar, //input
			'/ssg/new/../avs/avatar_23_1447277169.jpeg', //expected
			'get_member_data()->avatar: Dudemeister' //title
		);

		//--Viktor--
		//get member data
		$member_data = $this->get_member_data(1001);
		//name
		$this->unit->run(
			$member_data->name, //input
			'Viktor', //expected
			'get_member_data()->name: Viktor' //title
		);
		//avatar
		$this->unit->run(
			$member_data->avatar, //input
			'/ssg/new/../avs/avatar_1001_1461971366.jpeg', //expected
			'get_member_data()->avatar: Viktor' //title
		);

		//--Nehls--
		//get member data
		$member_data = $this->get_member_data(1603);
		//name
		$this->unit->run(
			$member_data->name, //input
			'Nehls', //expected
			'get_member_data()->name: Nehls' //title
		);
		//avatar
		$this->unit->run(
			$member_data->avatar, //input
			'/ssg/new/../avs/avatar_1603_1512499625.jpeg', //expected
			'get_member_data()->avatar: Nehls' //title
		);

		echo $this->unit->report();
	}
}