<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Hanterar närvaro-typer
 * Ja, JIP, QIP, NOSHOW, Ej anmäld & Oanmäld frånvaro
 */
class Attendance
{
	protected $CI;
	private $types = array();

	public function __construct()
	{
		// Assign the CodeIgniter super-object
		$this->CI =& get_instance();

		$i = 1;
		$this->types[$i] = new stdClass;
		$this->types[$i]->id = $i;
		$this->types[$i]->text = 'Ja';
		$this->types[$i]->code = 'signed';
		$this->types[$i]->class = 'text-'. $this->types[$i]->code;
		$this->types[$i]->is_positive = true; //räknas som närvaro

		$i = 2;
		$this->types[$i] = new stdClass;
		$this->types[$i]->id = $i;
		$this->types[$i]->text = 'JIP';
		$this->types[$i]->code = 'jip';
		$this->types[$i]->class = 'text-'. $this->types[$i]->code;
		$this->types[$i]->is_positive = true;

		$i = 3;
		$this->types[$i] = new stdClass;
		$this->types[$i]->id = $i;
		$this->types[$i]->text = 'QIP';
		$this->types[$i]->code = 'qip';
		$this->types[$i]->class = 'text-'. $this->types[$i]->code;
		$this->types[$i]->is_positive = true;

		$i = 4;
		$this->types[$i] = new stdClass;
		$this->types[$i]->id = $i;
		$this->types[$i]->text = 'NOSHOW';
		$this->types[$i]->code = 'noshow';
		$this->types[$i]->class ='text-'.  $this->types[$i]->code;
		$this->types[$i]->is_positive = false; //räknas INTE som närvaro

		$i = 5;
		$this->types[$i] = new stdClass;
		$this->types[$i]->id = $i;
		$this->types[$i]->text = 'Ej anmäld';
		$this->types[$i]->code = 'notsigned';
		$this->types[$i]->class = 'text-'. $this->types[$i]->code;
		$this->types[$i]->is_positive = false;

		$i = 6;
		$this->types[$i] = new stdClass;
		$this->types[$i]->id = $i;
		$this->types[$i]->text = 'Oanmäld frånvaro';
		$this->types[$i]->code = 'awol';
		$this->types[$i]->class = 'text-'. $this->types[$i]->code;
		$this->types[$i]->is_positive = false;
	}

	/**
	 * Ger en kopia av typ-arrayen.
	 *
	 * @return array
	 */
	public function get_all()
	{
		return $this->types;
	}

	/**
	 * Ge valbara närvaro-alternativ.
	 *
	 * @return array
	 */
	public function get_choosable()
	{
		$att = $this->types;
		unset($att[5]); //notsigned
		unset($att[6]); //awol

		return $att;
	}

	/**
	 * Hämta enstaka typ genom att ge dess id.
	 *
	 * @param int $id
	 * @return object
	 */
	public function get_type_by_id($id)
	{
		return $this->types[$id];
	}

	/**
	 * Hämta enstaka typ genom att ge dess kod.
	 *
	 * @param string $code
	 * @return object
	 */
	public function get_type_by_code($code)
	{
		foreach ($this->types as $type)
			if($type->code == $code)
				return $type;

		return null;
	}

	/**
	 * Hämta array där key är code istället för id.
	 *
	 * @return array
	 */
	public function get_all_by_code()
	{
		$arr = array();

		foreach ($this->types as $type)
			$arr[$type->code] = $type;

		return $arr;
	}

	/**
	 * Adderar antal Ja, JIP och QIP
	 *
	 * @param array $signed_array Key: attendance-id och value: antalet anmälda
	 * @return int
	 */
	public function count_signed($signed_array)
	{
		$count = 0;

		//gå igenom alla närvaro-typer
		foreach($signed_array as $att_id => $type_count)
			if($this->types[$att_id]->is_positive) //om närvaro-typ räknas som närvaro
				$count += $type_count; //lägg till i summan

		return $count;
	}
}
?>