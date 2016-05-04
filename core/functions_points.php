<?php
/**
*
* @package phpBB Extension - Football Football
* @copyright (c) 2016 football (http://football.bplaced.net)
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace football\football\core;

/**
* @package football
*/

class functions_points
{
	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;


	/**
	* Constructor
	*
	* @param \phpbb\user						$user
	* @param \phpbb\db\driver\driver_interface	$db
	*
	*/

	public function __construct(\phpbb\user $user, \phpbb\db\driver\driver_interface $db)
	{
		$this->user 							= $user;
		$this->db 								= $db;
	}

	/**
	* Add points to user
	*/
	function add_points($user_id, $amount)
	{
		// Select users current points
		$sql_array = array(
			'SELECT'	=> 'user_points',
			'FROM'		=> array(
				USERS_TABLE => 'u',
			),
			'WHERE'		=> 'user_id = ' . (int) $user_id,
		);
		$sql = $this->db->sql_build_query('SELECT', $sql_array);
		$result = $this->db->sql_query($sql);
		$user_points = $this->db->sql_fetchfield('user_points');
		$this->db->sql_freeresult($result);

		// Add the points
		$data = array(
			'user_points'	=> $user_points + $amount
		);

		$sql = 'UPDATE ' . USERS_TABLE . '
				SET ' . $this->db->sql_build_array('UPDATE', $data) . '
				WHERE user_id = ' . (int) $user_id;
		$this->db->sql_query($sql);

		return;
	}

	/**
	* Substract points from user
	*/
	function substract_points($user_id, $amount)
	{
		// Select users current points
		$sql_array = array(
			'SELECT'	=> 'user_points',
			'FROM'		=> array(
				USERS_TABLE => 'u',
			),
			'WHERE'		=> 'user_id = ' . (int) $user_id,
		);
		$sql = $this->db->sql_build_query('SELECT', $sql_array);
		$result = $this->db->sql_query($sql);
		$user_points = $this->db->sql_fetchfield('user_points');
		$this->db->sql_freeresult($result);

		// Update the points
		$data = array(
			'user_points'	=> $user_points - $amount
		);

		$sql = 'UPDATE ' . USERS_TABLE . '
				SET ' . $this->db->sql_build_array('UPDATE', $data) . '
				WHERE user_id = ' . (int) $user_id;
		$this->db->sql_query($sql);

		return;
	}

	/**
	* Set the amount of points to user
	*/
	function set_points($user_id, $amount)
	{
		// Set users new points
		$data = array(
			'user_points'	=> $amount
		);

		$sql = 'UPDATE ' . USERS_TABLE . '
				SET ' . $this->db->sql_build_array('UPDATE', $data) . '
				WHERE user_id = ' . (int) $user_id;
		$this->db->sql_query($sql);

		return;
	}

	
	/**
	* Preformat numbers
	*/
	function number_format_points($num)
	{
		$decimals = 2;

		return number_format($num, $decimals, $this->user->lang['FOOTBALL_SEPARATOR_DECIMAL'], $this->user->lang['FOOTBALL_SEPARATOR_THOUSANDS']);
	}

}
