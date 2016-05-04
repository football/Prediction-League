<?php
/**
*
* @package phpBB Extension - Football Football
* @copyright (c) 2016 football (http://football.bplaced.net)
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace football\football\acp;

class matches_module
{
	public $u_action;

	protected $db, $user, $template, $config, $phpbb_root_path, $phpbb_admin_path, $phpEx;
	protected $root_path, $request, $php_ext, $log;


	public function __construct()
	{
		global $db, $user, $request, $template;
		global $config, $phpbb_root_path, $phpbb_admin_path, $phpEx;

		$user->add_lang_ext('football/football', 'football');
		$user->add_lang_ext('football/football', 'info_acp_matches');

		$this->root_path = $phpbb_root_path . 'ext/football/football/';

		$this->config = $config;
		$this->request = $request;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->phpbb_admin_path = $phpbb_admin_path;
		$this->php_ext = $phpEx;

		if(!function_exists('season_info'))
		{
			include($this->root_path . 'includes/functions.' . $this->php_ext);
		}
		if (!defined('FOOTB_SEASONS'))
		{
			include($this->root_path . 'includes/constants.' . $this->php_ext);
		}
	}

	function main($id, $mode)
	{
		global $db, $auth, $phpbb_container, $phpbb_admin_path, $league_info;
		global $template, $user, $config, $phpbb_extension_manager, $request, $phpbb_root_path, $phpEx;
		$provider			= new \phpbb\controller\ provider();
		$symphony_request	= new \phpbb\ symfony_request($request);
		$filesystem			= new \phpbb\ filesystem();
		$helper				= new \phpbb\controller\ helper($template, $user, $config, $provider, $phpbb_extension_manager, $symphony_request, $request, $filesystem, $phpbb_root_path, $phpEx);

		$this->tpl_name = 'acp_football_matches';
		$this->page_title = 'ACP_FOOTBALL_MATCHES_MANAGE';

		$form_key = 'acp_football_matches';
		add_form_key($form_key);

		include($this->phpbb_root_path . 'includes/functions_user.' . $this->php_ext);

		// Check and set some common vars
		$action			= (isset($_POST['add'])) ? 'add' : $this->request->variable('action', '');
		$edit			= $this->request->variable('edit', 0);
		$season			= $this->request->variable('s', 0);
		$league			= $this->request->variable('l', 0);
		$matchday		= $this->request->variable('m', 0);
		$match_number	= $this->request->variable('g', 0);
		$update			= (isset($_POST['update'])) ? true : false;

		// Clear some vars
		$match_row = array();
		$error = array();

		// Grab current season
		if (!$season)
		{
			$season = curr_season();
		}

		// Grab basic data for select season
		if ($season)
		{
			$sql = 'SELECT *
				FROM ' . FOOTB_SEASONS . '
				ORDER BY season DESC';
			$result = $db->sql_query($sql);

			$season_options = '';
			while ($row = $db->sql_fetchrow($result))
			{
				$selected = ($season && $row['season'] == $season) ? ' selected="selected"' : '';
				$season_options .= '<option value="' . $row['season'] . '"' . $selected . '>' . $row['season_name_short'] . '</option>';
				if ($selected <> '')
				{
					$season_name = $row['season_name_short'];
				}
			}
			$db->sql_freeresult($result);
		}
		else
		{
			trigger_error($user->lang['NO_SEASON'] . adm_back_link($this->u_action), E_USER_WARNING);
		}

		// Grab current league
		if (!$league)
		{
			$league = current_league($season, false);
		}

		// Grab basic data for select league
		if ($league)
		{
			$sql = 'SELECT *
				FROM ' . FOOTB_LEAGUES . "
				WHERE season = $season
				ORDER BY league ASC";
			$result = $db->sql_query($sql);

			$league_options = '';
			while ($row = $db->sql_fetchrow($result))
			{
				$selected = ($league && $row['league'] == $league) ? ' selected="selected"' : '';
				$league_options .= '<option value="' . $row['league'] . '"' . $selected . '>' . $row['league_name'] . '</option>';
				if ($selected <> '')
				{
					$league_info 	    = $row;
					$league_matchdays 	= $row['matchdays'];
					$matches_matchday 	= $row['matches_on_matchday'];
					$league_name 		= $row['league_name'];
					$league_type 		= $row['league_type'];
					$ko_league 			= ($row['league_type'] == LEAGUE_KO) ? true : false;
				}
			}
			$db->sql_freeresult($result);
		}
		else
		{
			trigger_error(sprintf($user->lang['NO_LEAGUE'], $season) . adm_back_link($this->u_action . "&amp;s=$season"), E_USER_WARNING);
		}

		// Grab basic data for select matchday
		if (!$matchday)
		{
			$matchday = curr_matchday($season, $league);
		}
		$sql = 'SELECT *,
				UNIX_TIMESTAMP(delivery_date) AS unix_delivery_date
			FROM ' . FOOTB_MATCHDAYS . "
			WHERE season = $season 
				AND league = $league
			ORDER BY matchday ASC";
		$result = $db->sql_query($sql);

		$delivery2 = '';
		$delivery3 = '';
		$matchday_options = '';
		while ($row = $db->sql_fetchrow($result))
		{
			$selected = ($matchday && $row['matchday'] == $matchday) ? ' selected="selected"' : '';
			$day_name = (strlen($row['matchday_name']) > 0) ? $row['matchday_name'] : $row['matchday'] . '. ' . sprintf($user->lang['MATCHDAY']);
			$matchday_options .= '<option value="' . $row['matchday'] . '"' . $selected . '>' . $day_name . '</option>';
			if ($selected <> '')
			{
				$unix_delivery_date = $row['unix_delivery_date'];
				$delivery2 = $row['delivery_date_2'];
				$delivery3 = $row['delivery_date_3'];
				$matchday_name = $day_name;
				if ($matches_matchday)
				{
					$matches_on_matchday = $matches_matchday;
				}
				else
				{
					$matches_on_matchday = $row['matches'];
				}
			}
		}
		$db->sql_freeresult($result);
		if ($matchday_options == '')
		{
			trigger_error(sprintf($user->lang['NO_MATCHDAY'], $league_name, $season) . adm_back_link($this->u_action . "&amp;s=$season&amp;l=$league"), E_USER_WARNING);
		}

		// Grab basic data for match, if match is set and exists
		if ($match_number)
		{
			$sql = 'SELECT *
				FROM ' . FOOTB_MATCHES . "
				WHERE season = $season 
					AND league = $league 
					AND match_no = $match_number";
			$result = $db->sql_query($sql);
			$match_row = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);
		}
		
		// Which page?
		switch ($action)
		{
			case 'delete':
				if (!$season)
				{
					trigger_error($user->lang['NO_SEASON'] . adm_back_link($this->u_action), E_USER_WARNING);
				}

				if (!$league)
				{
					trigger_error($user->lang['NO_LEAGUE'] . adm_back_link($this->u_action . "&amp;s=$season"), E_USER_WARNING);
				}

				if (!$matchday)
				{
					trigger_error($user->lang['NO_MATCHDAY'] . adm_back_link($this->u_action . "&amp;s=$season&amp;l=$league"), E_USER_WARNING);
				}

				if (confirm_box(true))
				{
					$error = '';

					if (!$auth->acl_get('a_football_delete'))
					{
						trigger_error($user->lang['NO_AUTH_OPERATION'] . adm_back_link($this->u_action . "&amp;s=$season&amp;l=$league&amp;m=$matchday"), E_USER_WARNING);
					}
					if ($user->data['user_type'] != USER_FOUNDER && $this->config['football_founder_delete'])
					{
						trigger_error($user->lang['MATCHES_NO_DELETE'] . adm_back_link($this->u_action . "&amp;s=$season&amp;l=$league&amp;m=$matchday"), E_USER_WARNING);
					}

					// Delete match
					$sql = 'DELETE FROM ' . FOOTB_MATCHES . "
							WHERE season = $season AND league = $league AND match_no = $match_number";
					$db->sql_query($sql);

					// Delete bets
					$sql = 'DELETE FROM ' . FOOTB_BETS . "
							WHERE  season = $season AND league = $league AND match_no = $match_number";
					$db->sql_query($sql);

					trigger_error($user->lang['MATCH_DELETED'] . adm_back_link($this->u_action . "&amp;s=$season&amp;l=$league&amp;m=$matchday"));
				}
				else
				{
					confirm_box(false, sprintf($user->lang['MATCH_CONFIRM_DELETE'], $match_row['match_no'], $league_name, $season), build_hidden_fields(array(
						's'			=> $season,
						'l'			=> $league,
						'm'			=> $matchday,
						'g'			=> $match_number,
						'mode'		=> $mode,
						'action'	=> $action))
					);
				}
			break;

			case 'add':
				$sql = "SELECT DISTINCT user_id
					FROM " . FOOTB_BETS . " 
					WHERE season = $season 
						AND league = $league";
				$result = $db->sql_query($sql);
				$rows_users = $db->sql_fetchrowset($result);
				$db->sql_freeresult($result);
				$sql = "SELECT 
						matchday,
						matches AS matches_matchday,
						delivery_date
					FROM " . FOOTB_MATCHDAYS . " 
					WHERE season = $season 
						AND league = $league
					ORDER BY matchday ASC";
				$result = $db->sql_query($sql);
				$rows_matchdays = $db->sql_fetchrowset($result);
				$existing_matchdays = sizeof($rows_matchdays);
				$db->sql_freeresult($result);
				if ($existing_matchdays < $league_matchdays)
				{
					trigger_error($user->lang['MATCHDAY_MISSED'] . adm_back_link($this->u_action . "&amp;s=$season&amp;l=$league&amp;m=$matchday"));
				}
				else
				{
					$current_match = 0;
					$count_updates = 0;
					$sql_betary = array();
					foreach ($rows_matchdays as $current_matchday)
					{
						if ($matches_matchday)
						{
							$matches_on_matchday = $matches_matchday;
						}
						else
						{
							$matches_on_matchday = $current_matchday['matches_matchday'];
						}
						for ( $i = 1; $i <= $matches_on_matchday; $i++ )
						{
							$current_match++;
							$sql_ary = array(
								'season'				=> (int) $season,
								'league'				=> (int) $league,
								'match_no'				=> (int) $current_match,
								'team_id_home'			=> 0,
								'team_id_guest'			=> 0,
								'goals_home'			=> '',
								'goals_guest'			=> '',
								'matchday'				=> $current_matchday['matchday'],
								'status'				=> 0,
								'odd_1'					=> 0,
								'odd_x'					=> 0,
								'odd_2'					=> 0,
								'rating'				=> 0,
								'match_datetime'		=> $current_matchday['delivery_date'],
								'group_id'				=> '',
								'formula_home'			=> '',
								'formula_guest'			=> '',
								'ko_match'				=> 0,
								'goals_overtime_home'	=> '',
								'goals_overtime_guest'	=> '',
							);
							$sql = 'INSERT IGNORE INTO ' . FOOTB_MATCHES . ' ' . $db->sql_build_array('INSERT', $sql_ary);
							$db->sql_query($sql);
							if ($db->sql_affectedrows())
							{
								$count_updates++;
								foreach ($rows_users as $current_user)
								{
									$sql_betary[] = array(
										'season'				=> (int) $season,
										'league'				=> (int) $league,
										'match_no'				=> (int) $current_match,
										'user_id'				=> $current_user['user_id'],
										'goals_home'			=> '',
										'goals_guest'			=> '',
										'bet_time'				=> 0,
									);
								}
							}
						}
					}
					if (sizeof($sql_betary))
					{
						$db->sql_multi_insert(FOOTB_BETS, $sql_betary);
					}
					$message = ($count_updates > 1) ? 'MATCHES_CREATED' : 'MATCH_CREATED';
					trigger_error(sprintf($user->lang[$message],$count_updates) . adm_back_link($this->u_action . "&amp;s=$season&amp;l=$league&amp;m=$matchday"));
				}
			break; 
			case 'edit':
				$data = array();
				$error_msg = array();

				if (!sizeof($error))
				{
					if ($action == 'edit' && !$match_number)
					{
						trigger_error($user->lang['NO_MATCH'] . adm_back_link($this->u_action . "&amp;s=$season&amp;l=$league&amp;m=$matchday"), E_USER_WARNING);
					}

					$data = array(
						'mday_day'		=> 0,
						'mday_month'	=> 0,
						'mday_year'		=> 0,
						'mday_hour'		=> 0,
						'mday_min'		=> 0,
					);
					if ($match_row['match_datetime'])
					{
						list($data['mday_date'], $data['mday_time']) = explode(' ', $match_row['match_datetime']);
						list($data['mday_year'], $data['mday_month'], $data['mday_day']) = explode('-', $data['mday_date']);
						list($data['mday_hour'], $data['mday_min'], $data['mday_sec']) = explode(':', $data['mday_time']);
					}

					$data['mday_day']		= $this->request->variable('mday_day', $data['mday_day']);
					$data['mday_month']		= $this->request->variable('mday_month', $data['mday_month']);
					$data['mday_year']		= $this->request->variable('mday_year', $data['mday_year']);
					$data['mday_hour']		= $this->request->variable('mday_hour', $data['mday_hour']);
					$data['mday_min']		= $this->request->variable('mday_min', $data['mday_min']);
					$data['mday_sec']		= '00';
					$data['mday_date']		= sprintf('%02d-%02d-%04d', $data['mday_day'], $data['mday_month'], $data['mday_year']); 
					$data['mday_time']		= sprintf('%02d:%02d:%02d', $data['mday_hour'], $data['mday_min'], $data['mday_sec']);
					$match_row['match_datetime']	= sprintf('%04d-%02d-%02d', $data['mday_year'], $data['mday_month'], $data['mday_day']) . ' ' . $data['mday_time'];
					
					// Did we submit?
					if ($update)
					{
						if (!check_form_key($form_key))
						{
							trigger_error($user->lang['FORM_INVALID'] . adm_back_link($this->u_action . "&amp;s=$season&amp;l=$league&amp;m=$matchday"), E_USER_WARNING);
						}
						
						$match_row['status'] 		= utf8_normalize_nfc($this->request->variable('match_status', '', true));
						$match_row['odd_1'] 		= round($this->request->variable('odd_1', $match_row['odd_1']),2);
						$match_row['odd_x'] 		= round($this->request->variable('odd_x', $match_row['odd_x']),2);
						$match_row['odd_2'] 		= round($this->request->variable('odd_2', $match_row['odd_2']),2);
						$match_row['rating'] 		= round($this->request->variable('rating', $match_row['rating']),2);
						$match_row['team_id_home'] 	= utf8_normalize_nfc($this->request->variable('team_home', '', true));
						$match_row['team_id_guest'] = utf8_normalize_nfc($this->request->variable('team_guest', '', true));
						$match_row['formula_home'] 	= utf8_normalize_nfc($this->request->variable('formula_home', '', true));
						$match_row['formula_guest'] = utf8_normalize_nfc($this->request->variable('formula_guest', '', true));
						$match_row['ko_match'] 		= $this->request->variable('match_ko', false);
						$match_row['group_id'] 		= ($this->request->variable('group_match', false)) ? utf8_normalize_nfc($this->request->variable('match_group', '', true)) : '';
						
						if ($match_row['team_id_home'] <> '')
						{
							$team_arr = explode(';', $match_row['team_id_home']);
							$match_row['team_id_home'] = $team_arr[1];
						}
						if ($match_row['team_id_guest'] <> '')
						{
							$team_arr = explode(';', $match_row['team_id_guest']);
							$match_row['team_id_guest'] = $team_arr[1];
						}
						
						if ($data['mday_day'] <> '--' and $data['mday_month'] <> '--' and $data['mday_year'] <> '--')
						{
							$match_timestamp = mktime($data['mday_hour'], $data['mday_min'], 0, $data['mday_month'], $data['mday_day'], $data['mday_year']);
							$local_board_time = time() + (($this->config['board_timezone'] - $this->config['football_host_timezone']) * 3600); 
							if ($match_timestamp > $local_board_time AND $match_row['status'] < 3 AND $league_info['bet_in_time'] == 1)
							{
								// Bet in time and match moved to future
								$match_row['status'] = 0;
							}

							if ($match_timestamp <=  $local_board_time AND $match_row['status'] == 0 AND $league_info['bet_in_time'] == 1)
							{
								// Bet in time and match moved to past
								$match_row['status'] = 1;
							}

							if ($match_timestamp <  $unix_delivery_date AND $match_row['status'] == 0 AND !$league_info['bet_in_time'])
							{
								// No bet in time and match moved before delivery
								$error[] = $user->lang['MATCH_BEFORE_DELIVERY'];
							}
						}
						else
						{
							$error[] = $user->lang['NO_MATCH_BEGIN'];
						}

						if (!sizeof($error))
						{
							$sql_ary = array(
								'season'				=> (int) $season,
								'league'				=> (int) $league,
								'match_no'				=> (int) $match_number,
								'team_id_home'			=> (is_numeric($match_row['team_id_home'])) ? $match_row['team_id_home'] : 0,
								'team_id_guest'			=> (is_numeric($match_row['team_id_guest'])) ? $match_row['team_id_guest'] : 0,
								'goals_home'			=> (is_numeric($match_row['goals_home'])) ? $match_row['goals_home'] : '',
								'goals_guest'			=> (is_numeric($match_row['goals_guest'])) ? $match_row['goals_guest'] : '',
								'matchday'				=> $match_row['matchday'],
								'status'				=> (is_numeric($match_row['status'])) ? $match_row['status'] : 0,
								'odd_1'					=> (is_numeric($match_row['odd_1'])) ? $match_row['odd_1'] : 0,
								'odd_x'					=> (is_numeric($match_row['odd_x'])) ? $match_row['odd_x'] : 0,
								'odd_2'					=> (is_numeric($match_row['odd_2'])) ? $match_row['odd_2'] : 0,
								'rating'				=> (is_numeric($match_row['rating'])) ? $match_row['rating'] : 0,
								'match_datetime'		=> $match_row['match_datetime'],
								'group_id'				=> strlen($match_row['group_id']) ? $match_row['group_id'] : '',
								'formula_home'			=> strlen($match_row['formula_home']) ? $match_row['formula_home'] : '',
								'formula_guest'			=> strlen($match_row['formula_guest']) ? $match_row['formula_guest'] : '',
								'ko_match'				=> $match_row['ko_match'] ? $match_row['ko_match'] : 0,
								'goals_overtime_home'	=> (is_numeric($match_row['goals_overtime_home'])) ? $match_row['goals_overtime_home'] : '',
								'goals_overtime_guest'	=> (is_numeric($match_row['goals_overtime_guest'])) ? $match_row['goals_overtime_guest'] : '',
							);

							
							$var_ary = array(
								'mday_date'			=> array('date', false),
							);
							if (!($error_vals = validate_data($data, $var_ary)))
							{
								$sql = 'UPDATE ' . FOOTB_MATCHES . '
									SET ' . $db->sql_build_array('UPDATE', $sql_ary) . "
									WHERE season = $season AND league = $league AND match_no = $match_number";
								$db->sql_query($sql);

								if ($match_timestamp > $local_board_time AND $match_row['status'] == 0  AND $league_info['bet_in_time'] == 1)
								{ 
									// Bet in time and match (re)open so reopen matchday and set first delivery
									$sql_ary = array(
										'status'		=> 0,
										'delivery_date'	=> first_delivery($season, $league, $matchday),
									);
									$sql = 'UPDATE ' . FOOTB_MATCHDAYS . '
										SET ' . $db->sql_build_array('UPDATE', $sql_ary) . "
										WHERE season = $season AND league = $league AND matchday = $matchday";
									$db->sql_query($sql);
								}

								if ($match_timestamp <= $local_board_time AND $match_row['status'] <= 1  AND $league_info['bet_in_time'] == 1)
								{
									// Bet in time and match is closed so reopen matchday and set first delivery
									$first_delivery = first_delivery($season, $league, $matchday);
									if ($first_delivery <> '')
									{
										// Matchday has open matches, so set matchday status = 0 and first delivery
										$sql_ary = array(
											'status'		=> 0,
											'delivery_date'	=> first_delivery($season, $league, $matchday),
										);
										$sql = 'UPDATE ' . FOOTB_MATCHDAYS . '
											SET ' . $db->sql_build_array('UPDATE', $sql_ary) . "
											WHERE season = $season AND league = $league AND matchday = $matchday";
										$db->sql_query($sql);
									}
									else
									{
										// Matchday has no open match, so set matchday status = 1
										$sql_ary = array(
											'status'		=> 1,
										);
										$sql = 'UPDATE ' . FOOTB_MATCHDAYS . '
											SET ' . $db->sql_build_array('UPDATE', $sql_ary) . "
											WHERE season = $season AND league = $league AND matchday = $matchday";
										$db->sql_query($sql);
									}
								}
								trigger_error($user->lang['MATCH_UPDATED'] . adm_back_link($this->u_action . "&amp;s=$season&amp;l=$league&amp;m=$matchday"));
							}
							else
							{
								foreach ($error_vals as $error_val)
								{
										$error_msg[] = $user->lang[$error_val];
								}
								$error[] =  $user->lang['MATCH_UPDATE_FAILED'];
								$error = array_merge($error, $error_msg);
							}
						}
					}
				}

				$s_matchday_day_options = '<option value="0"' . ((!$data['mday_day']) ? ' selected="selected"' : '') . '>--</option>';
				for ($i = 1; $i < 32; $i++)
				{
					$selected = ($i == $data['mday_day']) ? ' selected="selected"' : '';
					$s_matchday_day_options .= "<option value=\"" . sprintf('%02d',$i) . "\"$selected>" . sprintf('%02d',$i) . "</option>";
				}

				$s_matchday_month_options = '<option value="0"' . ((!$data['mday_month']) ? ' selected="selected"' : '') . '>--</option>';
				for ($i = 1; $i < 13; $i++)
				{
					$selected = ($i == $data['mday_month']) ? ' selected="selected"' : '';
					$s_matchday_month_options .= "<option value=\"" . sprintf('%02d',$i) . "\"$selected>" . sprintf('%02d',$i) . "</option>";
				}
				$s_matchday_year_options = '';

				$s_matchday_year_options = '<option value="0"' . ((!$data['mday_year']) ? ' selected="selected"' : '') . '>--</option>';
				for ($i = $season - 1 ; $i <= $season; $i++)
				{
					$selected = ($i == $data['mday_year']) ? ' selected="selected"' : '';
					$s_matchday_year_options .= "<option value=\"$i\"$selected>$i</option>";
				}

				$s_matchday_hour_options = '';
				if (!$data['mday_hour'])
				{
					$data['mday_hour'] = 0;
				}
				for ($i = 0; $i < 24; $i++)
				{
					$selected = ($i == $data['mday_hour']) ? ' selected="selected"' : '';
					$s_matchday_hour_options .= "<option value=\"" . sprintf('%02d',$i) . "\"$selected>" . sprintf('%02d',$i) . "</option>";
				}

				$s_matchday_min_options = '';
				if (!$data['mday_min'])
				{
					$data['mday_min'] = 0;
				}
				for ($i = 0; $i < 12; $i++)
				{
					$selected = (($i * 5) == $data['mday_min']) ? ' selected="selected"' : '';
					$s_matchday_min_options .= "<option value=\"" . sprintf('%02d',($i * 5)) . "\"$selected>" . sprintf('%02d',($i * 5)) . "</option>";
				}

				// Selection status
				$edit_status = false;
				if ($match_row['status'] < 1)
				{
					$edit_status = true;
					$selected_status = ($match_row['status'] == 0) ? ' selected="selected"' : '';
					$status_options = '<option value="0"' . $selected_status . '>0</option>';
					if ($delivery2 != '')
					{
						$selected_status = ($match_row['status'] == -1) ? ' selected="selected"' : '';
						$status_options .= '<option value="-1"' . $selected_status . '>-1</option>';
						if ($delivery3 != '')
						{
							$selected_status = ($match_row['status'] == -2) ? ' selected="selected"' : '';
							$status_options .= '<option value="-2"' . $selected_status . '>-2</option>';
						}
					}
				}
				else
				{
					$status_options = '<option value="' . $row['status'] . '" selected="selected">' . $row['status'] . '</option>';
				}
				// Grab for teams for selection
				if ($ko_league) 
				{
					$where_round = " AND matchday >= $matchday";
				}
				else
				{
					$where_round = "";
				}
				$sql = 'SELECT *
					FROM ' . FOOTB_TEAMS . "
					WHERE season = $season 
						AND league = $league 
						$where_round
					ORDER BY team_name ASC";
				$result = $db->sql_query($sql);
				$team_guest_options = '<option value=" ;0">' . sprintf($user->lang['UNKNOWN']) . '</option>';
				$team_home_options = '<option value=" ;0">' . sprintf($user->lang['UNKNOWN']) . '</option>';
				$match_group = '';
				while ($row = $db->sql_fetchrow($result))
				{
					$guest_id = (empty($match_row['team_id_guest'])) ? 0 : $match_row['team_id_guest'];
					$selected_guest = ($guest_id && $row['team_id'] == $guest_id) ? ' selected="selected"' : '';
					if ($row['team_id'] == $guest_id) 
					{
						$match_group = $row['group_id'];
					}
					$team_guest_options .= '<option value="' . $row['group_id'] . ';' . $row['team_id'] . '"' . $selected_guest . '>' . $row['team_name'] . '</option>';
					$home_id = (empty($match_row['team_id_home'])) ? 0 : $match_row['team_id_home'];
					$selected_home = ($home_id && $row['team_id'] == $home_id) ? ' selected="selected"' : '';
					$team_home_options .= '<option value="' . $row['group_id'] . ';' . $row['team_id'] .'"' . $selected_home . '>' . $row['team_name'] . '</option>';
				}

				$u_back = $this->u_action . "&amp;s=$season&amp;l=$league&amp;m=$matchday";

				$template->assign_vars(array(
					'S_EDIT'				=> true,
					'S_ADD_MATCH'			=> ($action == 'add') ? true : false,
					'S_ERROR'				=> (sizeof($error)) ? true : false,
					'S_KO_LEAGUE'			=> $ko_league,
					'S_EDIT_STATUS'			=> $edit_status,
					'S_VERSION_NO'			=> $this->config['football_version'],
					'ERROR_MSG'				=> (sizeof($error)) ? implode('<br />', $error) : '',
					'SEASON'				=> $season,
					'SEASON_NAME'			=> $season_name,
					'LEAGUE'				=> $league,
					'LEAGUE_NAME'			=> $league_name,
					'MATCHDAY'				=> $matchday,
					'MATCHDAY_NAME'			=> $matchday_name,
					'MATCH_NUMBER'			=> $match_number,
					'MATCH_STATUS'			=> $match_row['status'],
					'STATUS_OPTIONS'		=> $status_options,
					'S_GROUP_CHECKED'		=> (strlen($match_row['group_id']) > 0) ? true : false,
					'MATCH_GROUP'			=> $match_group,
					'S_KO_CHECKED'			=> $match_row['ko_match'],
					'FORMULA_HOME'			=> $match_row['formula_home'],
					'FORMULA_GUEST'			=> $match_row['formula_guest'],
					'FORMULA_HOME'			=> $match_row['formula_home'],
					'TEAM_GUEST_OPTIONS'	=> $team_guest_options,
					'TEAM_HOME_OPTIONS'		=> $team_home_options,
					'S_MATCHDAY_DAY_OPTIONS'	=> $s_matchday_day_options,
					'S_MATCHDAY_MONTH_OPTIONS'	=> $s_matchday_month_options,
					'S_MATCHDAY_YEAR_OPTIONS'	=> $s_matchday_year_options,
					'S_MATCHDAY_HOUR_OPTIONS'	=> $s_matchday_hour_options,
					'S_MATCHDAY_MIN_OPTIONS'	=> $s_matchday_min_options,
					'MATCH_BEGIN_D'			=> substr($match_row['match_datetime'],8,2),
					'MATCH_BEGIN_M'			=> substr($match_row['match_datetime'],5,2),
					'MATCH_BEGIN_Y'			=> substr($match_row['match_datetime'],0,4),
					'MATCH_BEGIN_H'			=> substr($match_row['match_datetime'],11,2),
					'MATCH_BEGIN_MIN'		=> substr($match_row['match_datetime'],14,2),
					'ODD_1'					=> $match_row['odd_1'],
					'ODD_x'					=> $match_row['odd_x'],
					'ODD_2'					=> $match_row['odd_2'],
					'RATING'				=> $match_row['rating'],
					'U_BACK'				=> $u_back,
					'U_ACTION'				=> "{$this->u_action}&amp;action=$action&amp;s=$season&amp;l=$league&amp;m=$matchday",
					)
				);

				return;
			break;
		}
		
		// Get us all the matches
		$lang_dates = $user->lang['datetime'];
		$sql = "SELECT  
				m.match_no, 
				m.status,
				m.group_id,
				m.ko_match,
				m.formula_home,
				m.formula_guest,
				m.team_id_home AS home_id,
				m.team_id_guest AS guest_id,
				th.team_name AS home_name,
				tg.team_name AS guest_name,
				CONCAT(
					CASE DATE_FORMAT(m.match_datetime,'%w')
						WHEN 0 THEN '" . $lang_dates['Sun'] . "'
						WHEN 1 THEN '" . $lang_dates['Mon'] . "'
						WHEN 2 THEN '" . $lang_dates['Tue'] . "'
						WHEN 3 THEN '" . $lang_dates['Wed'] . "'
						WHEN 4 THEN '" . $lang_dates['Thu'] . "'
						WHEN 5 THEN '" . $lang_dates['Fri'] . "'
						WHEN 6 THEN '" . $lang_dates['Sat'] . "'
						ELSE 'Error' END,
					DATE_FORMAT(m.match_datetime,' %d.%m.%y  %H:%i')
				) AS match_begin
			FROM " . FOOTB_MATCHES . ' AS m
			LEFT JOIN ' . FOOTB_TEAMS . ' AS th ON (th.season = m.season AND th.league = m.league AND th.team_id = m.team_id_home)
			LEFT JOIN ' . FOOTB_TEAMS . " AS tg ON (tg.season = m.season AND tg.league = m.league AND tg.team_id = m.team_id_guest)
			WHERE m.season = $season 
				AND m.league = $league 
				AND m.matchday = $matchday
			ORDER BY m.match_datetime ASC, m.match_no ASC";
		$result = $db->sql_query($sql);
		$rows_matches = $db->sql_fetchrowset($result);
		$existing_matches = sizeof($rows_matches);
		$db->sql_freeresult($result);

		$template->assign_vars(array(
			'U_ACTION'			=> $this->u_action,
			'U_FOOTBALL' 		=> $helper->route('football_main_controller',array('side' => 'bet', 's' => $season, 'l' => $league, 'm' => $matchday)),
			'S_SEASON'			=> $season,
			'S_LEAGUE'			=> $league,
			'S_KO_LEAGUE'		=> $ko_league,
			'S_MATCHDAY'		=> $matchday,
			'S_SEASON_OPTIONS'	=> $season_options,
			'S_LEAGUE_OPTIONS'	=> $league_options,
			'S_MATCHDAY_OPTIONS'=> $matchday_options,
			'S_MATCH_ADD'		=> ($matches_on_matchday == $existing_matches) ? false:true,
			'S_VERSION_NO'		=> $this->config['football_version'],
			) 
		);

		// Check if the user is allowed to delete a match.
		if ($user->data['user_type'] != USER_FOUNDER && $this->config['football_founder_delete'])
		{
			$allow_delete = false;
		}
		else
		{
			$allow_delete = true;
		}

		$row_number = 0;
		foreach ($rows_matches as $row_match)
		{
			$row_number++;
			$row_class = (!($row_number % 2)) ? 'bg1 row_light' : 'bg2 row_dark';
			$template->assign_block_vars('match', array(
				'ROW_CLASS'		=> $row_class,
				'MATCH_NUMBER'	=> $row_match['match_no'],
				'MATCH_STATUS'	=> $row_match['status'],
				'MATCH_GROUP'	=> $row_match['group_id'],
				'MATCH_KO'		=> ($row_match['ko_match']) ? 'KO' : '',
				'MATCH_BEGIN'	=> $row_match['match_begin'],
				'MATCH_HOME'	=> ($row_match['home_name'] == '') ? $row_match['formula_home'] : $row_match['home_name'],
				'MATCH_GUEST'	=> ($row_match['guest_name'] == '') ? $row_match['formula_guest'] : $row_match['guest_name'],
				'U_EDIT'		=> "{$this->u_action}&amp;action=edit&amp;s=" . $season . "&amp;l=" .$league . "&amp;m=" .$matchday . "&amp;g=" .$row_match['match_no'],
				'U_DELETE'		=> ($allow_delete) ? "{$this->u_action}&amp;action=delete&amp;s=" . $season . "&amp;l=" . $league . "&amp;m=" . $matchday . "&amp;g=" . $row_match['match_no'] : '',
				)
			);
		}
	}
}

?>