<?php
/**
 * Hanterar användarlogins.
 * Laddas automatiskt på varje sida.
 * Kollar om login-session från gamla sidan existerar och kopierar till sitt egna session-system.
 * Annars visar den ett login-formulär som kollar uppgifter mot den gamla sidans databas.
 */
class Member extends CI_Model
{
	public
		$valid = false; //Är en existerande användare inloggad?

	/**
	 * Listar ut vem som är inloggad baserat på session.
	 * Om ingen valid session hittas: avbryt och låt $this->valid förbli false.
	 */
	public function __construct()
	{
		parent::__construct();

		//är medlem redan inloggad?
		if(!empty($this->session->member_id))
			$this->id = $this->session->member_id;
		else //ingen session finns, låt $this->is_valid vara false så login-formuläret visas
			return;
		
		
		/*** Lyckad inloggning ***/
		
		// debugging
		// $this->id = 237; ////////Bezz
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
		
		//kolla om användaren har rad i ssg_members
		$this->has_member_row($this->id);

		//sätt medlemsdata
		$this->set_member_data($this->id);

		//valid
		$this->valid = true;
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
				avatar, registered_date, uid, is_active, group_id,
				id_member AS id,
				real_name AS name,
				ssg_groups.name AS group_name,
				ssg_groups.code AS group_code,
				role_id,
				ssg_roles.name AS role_name
			FROM smf_members
			LEFT JOIN ssg_members
				ON smf_members.id_member = ssg_members.id
			LEFT JOIN ssg_groups
				ON ssg_members.group_id = ssg_groups.id
			LEFT JOIN ssg_roles
				ON ssg_members.role_id = ssg_roles.id
			WHERE id_member = ?';
		$query = $this->db->query($sql, $member_id);

		//felkoll
		if(!$member_data = $query->row())
			show_error("Hittade inte medlem med id: $member_id i databasen.");

		//--Avatar--
		//om ingen extern avatar är angiven: kolla om medlem har avatar liggande på servern
		if(empty($member_data->avatar))
		{
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
			//om inte så är avatar tom eller extern
			if(count($local_avatars) > 0)
			{
				sort($local_avatars); //sortera, stigande ordning
				$member_data->avatar_url = base_url('../avs/'. end($local_avatars)); //hämta högsta (sista) avataren, lägg till lokal-url
				unset($member_data->avatar);
			}
		}

		//--Permission Groups--
		$sql =
			'SELECT ssg_permission_groups.id AS persmission_id
			FROM ssg_permission_groups_members
			INNER JOIN ssg_permission_groups
				ON ssg_permission_groups_members.permission_group_id = ssg_permission_groups.id
			WHERE member_id = ?';
		$query = $this->db->query($sql, $member_id);
		$member_data->permission_groups = array();
		foreach($query->result() as $row)
			$member_data->permission_groups[] = $row->persmission_id;

		//--Rank--
		$sql =
			'SELECT
				name,
				rank_id, 
				icon
			FROM ssg_promotions
			INNER JOIN ssg_ranks
				ON ssg_promotions.rank_id = ssg_ranks.id
			WHERE ssg_promotions.member_id = ?
			ORDER BY date DESC
			LIMIT 1';
		$query = $this->db->query($sql, $member_id);
		$member_data->promotions = array();
		$rank = $query->row();
		
		//sätt senaste grad
		if(!empty($rank))
		{
			$member_data->rank_id = $rank->rank_id;
			$member_data->rank_name = $rank->name;
			$member_data->rank_icon = $rank->icon;
			
		}
		else
		{
			$member_data->rank_id = null;
			$member_data->rank_name = null;
			$member_data->rank_icon = null;
		}
		
		return $member_data;
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
				member_name = '$username' &&
				passwd = '$salt'";
		$query = $this->db->query($sql);
		
		$row = $query->row();

		if($query->num_rows() > 0) //success
		{
			$this->session->member_id = $row->id;
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
		$query = $this->db->query($sql);
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
		$query = $this->db->query($sql, $member_id);

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
		$query = $this->db->query($sql, $member_id);
		$name = $query->row()->name;
		$registered_date = $query->row()->registered_date;

		//--skapa ny rad--
		$sql =
			'INSERT INTO ssg_members(id, name, registered_date)
			VALUES (?, ?, ?)';
		$query = $this->db->query($sql, array($member_id, $name, $registered_date));
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
?>