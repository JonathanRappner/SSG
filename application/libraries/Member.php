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


		//försök hitta inloggad användare genom phpbb-cookie eller session-variabel
		if($id = $this->get_phpbb_session_member()) //hämta inloggad medlem från phpbb-session, via cookies (nya)
			$this->id = $id;
		else if(!empty($this->CI->session->member_id)) //hämta inloggad medlem från session-variabler (gamla standalone-session som autenticeras via loginform->smf-lösenord)
			$this->id = $this->CI->session->member_id;
		else //ingen session finns, låt $this->valid vara false så login-formuläret visas
			return;
		
			
		/*** Lyckad inloggning ***/
		
		// debugging
		// $this->id = 237; ////////Bezz
		// $this->id = 220; ////////NinjaNils
		// $this->id = 1603; ////////Nehls
		// $this->id = 1207; ////////Nabel
		// $this->id = 136;  ////////Kalle
		// $this->id = 23;   ////////Dudemeister
		// $this->id = 1001; ////////Viktor
		// $this->id = 1713; ////////ThoKaWi
		// $this->id = 1472; ////////Jonasson
		// $this->id = 1441; ////////Insane_laughter
		// $this->id = 1337; ////////Cowboy
		// if($this->id == 1655)
		// 	$this->id = 1675; ////////Gibby
		
		//kolla om användaren har rad i ssg_members, om inte: skapa en
		$this->has_member_row($this->id);

		//sätt laddad medlemsdata till $this
		$this->set_member_data($this->id);

		//valid
		$this->valid = true;
	}

	/**
	 * Försöker identifiera inloggad användare (ssg_member.id) med hjälp av phpbb-cookie.
	 * Ger false om ingen är inloggad.
	 *
	 * @return int SSG-medlems-id om inloggad, annars null.
	 */
	private function get_phpbb_session_member()
	{
		$regex_pattern = '/phpbb3_(\w){5}_sid/'; //ex: phpbb3_jyup2_sid, phpbb3_r9r3j_sid
		$phpbb_session_id = null;
		$member_id = null;

		//leta efter session-id-cookie:n och spara värdet
		foreach($_COOKIE as $key => $value)
			if(preg_match($regex_pattern, $key))
			{
				$phpbb_session_id = $value;
				break;
			}
		
		//avbryt om ingen cookie hittades
		if(empty($phpbb_session_id))
			return null;
		
		//kolla upp session i db
		$sql =
			'SELECT m.id
			FROM phpbb_sessions s
			INNER JOIN ssg_members m
				ON s.session_user_id = m.phpbb_user_id
			WHERE session_id = ?';
		$query = $this->CI->db->query($sql, $phpbb_session_id);

		if($query->num_rows() <= 0)
			return null;

		return $query->row()->id;
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
				id_member AS id,
				ssg_members.name AS name,
				ssg_groups.name AS group_name,
				ssg_groups.code AS group_code,
				role_id,
				ssg_roles.name AS role_name
			FROM smf_members
			INNER JOIN ssg_members
				ON smf_members.id_member = ssg_members.id
			LEFT JOIN ssg_groups
				ON ssg_members.group_id = ssg_groups.id
			LEFT JOIN ssg_roles
				ON ssg_members.role_id = ssg_roles.id
			WHERE id_member = ?';
		$query = $this->CI->db->query($sql, $member_id);

		//felkoll
		if(!$member_data = $query->row())
			show_error("Hittade inte medlem med id: $member_id i databasen.");

		//--Avatar--
		$member_data->avatar_url = $this->get_smf_avatar($member_id);
		// $member_data->avatar_url = $this->get_phpbb_avatar($member_id);

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
	 * Hitta medlemmens SMF-avatar.
	 * Lokal eller extern.
	 * Om ingen hittades, returnera unknown.png-bilden.
	 *
	 * @param int $member_id
	 * @return string Avatarens fulla url.
	 */
	public function get_smf_avatar($member_id)
	{
		//försök hämta avatar-url från db
		$avatar_url = $this->CI->db->query('SELECT avatar FROM smf_members WHERE id_member = ?', $member_id)->row()->avatar;
		
		if(empty($avatar_url)) //avatar-fältet är tomt, leta efter avatar-fil på servern
		{
			// leta i avs-mappen efter en avatar:
			// /avs/avatar_<member_id>_<unix_date>.jpeg
			// ex: /avs/avatar_1655_1516569554.jpeg
			$pattern_avatar = "/(?<=avatar_)$member_id/";
			$local_avatars = array(); //filnamn för denna användarens avatarer (kan finnas flera)

			//kolla genom all avatarer och leta efter match
			foreach(scandir('../avs') as $file_name)
			{
				$matches = array();
				preg_match($pattern_avatar, $file_name, $matches);
				if(count($matches) > 0)
					$local_avatars[] = $file_name;
			}

			//lokal(a) avatar(er) hittades
			if(count($local_avatars) > 0) //lokal avatar(er) hittades
			{
				sort($local_avatars); //sortera, stigande ordning
				return base_url('../avs/'. end($local_avatars)); //hämta högsta (sista) avataren, lägg till lokal-url
			}
			else //medlemmen har ingen avatar
				return base_url('images/unknown.png');
		}
		else //avatar är extern
			return $avatar_url;


		// //om ingen extern avatar är angiven: kolla om medlem har avatar liggande på servern
		// if(empty($member_data->avatar))
		// {
			

			
		// }
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
	 * Kollar om inloggningsuppgifter är korrekt.
	 * Sätter session-variabel och returnerar true.
	 *
	 * @param string $username Användarnamn
	 * @param string $password Lösenord
	 * @return bool
	 */
	public function validate_login($username, $password)
	{
		//variabler
		$salt = sha1(strtolower($username) . strip_tags($password));

		$sql =
			"SELECT id_member AS id
			FROM smf_members
			WHERE
				member_name = ? &&
				passwd = ?";
		$query = $this->CI->db->query($sql, array($username, $salt));
		
		$row = $query->row();

		if($query->num_rows() > 0) //success
		{
			$this->CI->session->member_id = $row->id;
			return true;
		}
		else
			return false;
	}

	/**
	 * Hämta medlemmars nick och id.
	 * Hämtar bara medlemmar från ssg_members.
	 * Hämtar nick från smf_members.
	 *
	 * @return array Key: id, Value: nick
	 */
	public function get_members_simple()
	{
		//variabler
		$members = array();

		$sql =
			'SELECT id, real_name
			FROM ssg_members
			INNER JOIN smf_members
				ON ssg_members.id = smf_members.id_member
			ORDER BY real_name ASC';
		$query = $this->CI->db->query($sql);
		foreach($query->result() as $row)
			$members[$row->id] = $row->real_name;
		
		return $members;
	}

	/**
	 * Har medlemmen en rad i ssg_members?
	 * Om inte; skapa en.
	 *
	 * @param int $member_id
	 * @return void
	 */
	private function has_member_row($member_id)
	{
		//--se om användaren har en rad--
		$sql =
			'SELECT id
			FROM ssg_members
			WHERE id = ?';
		$query = $this->CI->db->query($sql, $member_id);

		//om rad finns, fortsätt
		if($query->num_rows() > 0)
			return;

		//--hämta data från smf_members--
		$sql =
			'SELECT
				real_name AS name,
				FROM_UNIXTIME(date_registered, "%Y-%m-%d") AS registered_date
			FROM smf_members
			WHERE id_member = ?';
		$query = $this->CI->db->query($sql, $member_id);
		$name = $query->row()->name;
		$registered_date = $query->row()->registered_date;

		//--skapa ny rad--
		$sql =
			'INSERT INTO ssg_members(id, name, registered_date)
			VALUES (?, ?, ?)';
		$query = $this->CI->db->query($sql, array($member_id, $name, $registered_date));
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