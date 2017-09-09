<?php
/**
*
* @package phpBB Extension - Football Football
* @copyright (c) 2016 football (http://football.bplaced.net)
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace football\football\controller;

/**
* @ignore
*/

class main
{
	/* @var \phpbb\auth\auth */
	protected $auth;

	/* @var \phpbb\config\config */
	protected $config;

	/* @var \phpbb\extension\manager */
	protected $phpbb_extension_manager;

	/* @var \phpbb\path_helper */
	protected $phpbb_path_helper;

	/* @var \phpbb\db\driver\driver_interface */
	protected $db;

	/* @var \phpbb\notification\manager */
	protected $notification_manager;

	/* @var \phpbb\log\log */
	protected $log;

	/* @var \phpbb\controller\helper */
	protected $helper;

	/* @var \phpbb\template\template */
	protected $template;

	/* @var \phpbb\user */
	protected $user;
	
	/** @var \phpbb\pagination */
	protected $pagination;
	
	/* @var phpBB root path */
	protected $phpbb_root_path;

	/* @var PHP file extension	*/
	protected $php_ext;

	/* @var football includes path */
	protected $football_includes_path;

	/* @var football root path */
	protected $football_root_path;

	/**
	* Constructor
	*
	* @param \phpbb\auth\auth					$auth
	* @param \phpbb\config\config				$config
	* @param \phpbb\extension\manager			$phpbb_extension_manager
	* @param \phpbb\notification\manager		$notification_manager
	* @param \phpbb\log\log						$log
	* @param \phpbb\path_helper					$phpbb_path_helper
	* @param \phpbb\db\driver\driver_interface	$db
	* @param \phpbb\controller\helper			$helper
	* @param \phpbb\template\template			$template
	* @param \phpbb\user						$user
	* @param \phpbb\pagination 					$pagination 
	*/

	public function __construct(\phpbb\auth\auth $auth,
								\phpbb\config\config $config, 
								\phpbb\extension\manager $phpbb_extension_manager, 
								\phpbb\notification\manager	$notification_manager,
								\phpbb\log\log $log,
								\phpbb\path_helper $phpbb_path_helper,
								\phpbb\db\driver\driver_interface $db, 
								\phpbb\controller\helper $helper, 
								\phpbb\template\template $template, 
								\phpbb\user $user, 
								\phpbb\pagination $pagination, 
								$phpbb_root_path, 
								$php_ext)
	{
		$this->auth 					= $auth;
		$this->config 					= $config;
		$this->db 						= $db;
		$this->phpbb_extension_manager 	= $phpbb_extension_manager;
		$this->notification_manager 	= $notification_manager;
		$this->log 						= $log;
		$this->phpbb_path_helper		= $phpbb_path_helper;
		$this->helper 					= $helper;
		$this->template 				= $template;
		$this->user 					= $user;
		$this->pagination 				= $pagination;
		$this->$phpbb_root_path		 	= $phpbb_root_path;
		$this->php_ext 					= $php_ext;
		
		$this->football_includes_path 	= $phpbb_root_path . 'ext/football/football/includes/';
		$this->football_root_path 		= $phpbb_root_path . 'ext/football/football/';

	}

	public function handle($side)
	{
		global $db, $user, $cache, $request, $template, $season, $league, $matchday;
		global $config, $phpbb_root_path, $phpbb_container, $log, $phpEx, $league_info;
		global $mobile_device, $mobile_browser;
		
		define('IN_FOOTBALL', true);

		$this->cache = $cache;
		$this->request = $request;

		// Add football controller language file
		$this->user->add_lang_ext('football/football', 'football');

		// required includes
		include($this->football_includes_path . 'constants.' . $this->php_ext);
		include($this->football_includes_path . 'functions.' . $this->php_ext);
		
		if ($config['board_disable'] && !$this->auth->acl_gets('a_'))
		{
			$message = (!empty($config['board_disable_msg'])) ? $config['board_disable_msg'] : 'BOARD_DISABLE';
			trigger_error($message);
		}
		
		if ($config['football_disable'])
		{
			$message = (!empty($config['football_disable_msg'])) ? $config['football_disable_msg'] : 'FOOTBALL_DISABLED';
			trigger_error($message);
			exit;
		}

		// Can this user view Prediction Leagues pages?
		if (!$config['football_guest_view'])
		{
			// No guest view, call login for guest 
			if ($user->data['user_id'] == ANONYMOUS)
			{
				login_box('', ($user->lang['LOGIN_EXPLAIN_FOOTBALL']));
			}
		}
		if (!$config['football_user_view'])
		{
			// Only Prediction League member should see these pages
			// Check Prediction League authorisation 
			if ( !$this->auth->acl_get('u_use_football') )
			{
				trigger_error('NO_AUTH_VIEW');
			}
		}

		// Display football information
		$football_info = '';
		if ($config['football_info_display'])
		{
			$football_info = (!empty($config['football_info'])) ? $config['football_info'] : '';
		}

		$view	 = $this->request->variable('view', '');
		$action  = $this->request->variable('action', '');
		
		// Obtain parameters
		// Obtain season
		$season	= $this->request->variable('s', 0);
		if ($season || ($this->config['football_season_start'] == 0))
		{
			// Check given season 
			$sql = 'SELECT * FROM ' . FOOTB_SEASONS . " WHERE season = $season";
			$result = $db->sql_query($sql);
			if(!$row = $db->sql_fetchrow($result))
			{
				$season = curr_season();
			}
			$db->sql_freeresult($result);
		}
		else
		{
			$season = $this->config['football_season_start'];
		}
		$this->season = $season;
		// End obtain season

		// Obtain league
		$maxmatchday = 0;
		$league_info = array();
		if ($season)
		{
			$league	= $this->request->variable('l', 0);
			if ($league)
			{
				// Check given league 
				$sql = 'SELECT * FROM ' . FOOTB_LEAGUES . " WHERE season = $season AND league = $league";
				$result = $db->sql_query($sql);
				if(!$row = $db->sql_fetchrow($result))
				{
					// Set starting league
					if ($config['football_view_current'])
					{
						$league = current_league($season);
					}
					else
					{
						$league = first_league($season);
					}
				}
				else
				{
					$league_info = $row;
				}
				$db->sql_freeresult($result);
			}
			else
			{
				if ($side <> 'bank')
				{
					// Set starting league
					if ($config['football_view_current'])
					{
						$league = current_league($season);
					}
					else
					{
						$league = first_league($season);
					}
				}
			}
		}
		else
		{
			$league	= 0;
		}
		// End obtain league

		// Obtain selected user
		$user_sel	= $this->request->variable('u', 0);
		if ($user_sel)
		{
			$link_user = '&amp;u=' . $user_sel;
		}
		else
		{
			$link_user = '';
		}
		// End obtain matchday

		//*****************************************************************************
		// Close open matchdays
		close_open_matchdays();

		//*****************************************************************************
		// Obtain matchday
		if ($league)
		{
			// League information not set?
			if (!sizeof($league_info))
			{
				// Get league information for this league (required for select_points)
				$league_info = league_info($season, $league);
			}
			$maxmatchday = $league_info['matchdays'];
			$curr_matchday 	= curr_matchday($season, $league);
			$matchday		= $this->request->variable('m', $curr_matchday);
			// If switched from another league with more matchdays
			if(($matchday < 1) OR ($matchday > $maxmatchday))
			{
				$matchday = $curr_matchday;
			}
		}
		else
		{
			$curr_matchday 	= 0;
			$matchday		= 0;
		}
		// End obtain matchday
		// End obtain parameters
		$request->overwrite('s', $season);
		$request->overwrite('l', $league);
		$request->overwrite('m', $matchday);

		//*****************************************************************************
		// Start execute the action
		$dbmsg = '';
		switch($action)
		{
			case 'switch':
				// Switch Style and write to user table
				$sql_ary = array(
					'football_mobile'			=> (int) !$user->data['football_mobile'],
				);
				$sql = 'UPDATE ' . SESSIONS_TABLE . ' SET ' . $db->sql_build_array('UPDATE', $sql_ary) . "
					WHERE session_id = '" . $db->sql_escape($user->session_id) . "'";
				$db->sql_query($sql);
				$user->data['football_mobile'] = (int) !$user->data['football_mobile'];
			break;
			case 'bet':
				if ($user->data['user_id'] != ANONYMOUS or $config['server_name'] == 'football.bplaced.net')
				{
					$user_id = $user->data['user_id'];
					$sql = 'SELECT * FROM ' . FOOTB_MATCHES . " WHERE season = $season AND league = $league AND matchday = $matchday AND status <= 0";
					$resultopen = $db->sql_query($sql);
					$rows = $db->sql_fetchrowset($resultopen);
					$db->sql_freeresult($resultopen);
				
					$count_matches = 0;
					$count_updates = 0;
					foreach ($rows as $row)
					{
						$match_no = $row['match_no'];
						$goalsh = $this->request->variable('goalsh' . $match_no, 'nv');
						$goalsg = $this->request->variable('goalsg' . $match_no, 'nv');
						if ($goalsh != 'nv' AND $goalsg != 'nv')
						{
							if(($goalsh != '') AND ($goalsg != ''))
							{
								if(is_numeric($goalsh) AND is_numeric($goalsg) AND $goalsh >= 0 AND $goalsg >= 0)
								{
									if (0 == $count_matches)
									{
										$sameh = $goalsh;
										$sameg = $goalsg;
										$same = 1;
									}
									else
									{
										if ($goalsh != $sameh OR $goalsg != $sameg)
											$same = 0;
									}
									$sql = 'SELECT * FROM ' . FOOTB_BETS . " WHERE season = $season AND league = $league AND match_no = $match_no and user_id = $user_id";
									$result = $db->sql_query($sql);
									$row2 = $db->sql_fetchrow($result);
									$db->sql_freeresult($result);
									if(!$row2)
									{
										$sql_ary = array(
											'season'		=> (int) $season,
											'league'		=> (int) $league,
											'match_no'		=> (int) $match_no,
											'user_id'		=> (int) $user_id,
											'goals_home'	=> (int) $goalsh,
											'goals_guest'	=> (int) $goalsg,
											'bet_time'		=> time(),
										);
										$sql = 'INSERT INTO ' . FOOTB_BETS . ' ' . $db->sql_build_array('INSERT', $sql_ary);
										$db->sql_query($sql);
										$count_updates++;
									}
									else
									{
										if($row2['goals_home'] != $goalsh OR $row2['goals_guest'] != $goalsg)
										{
											$sql_ary = array(
												'goals_home'	=> (int) $goalsh,
												'goals_guest'	=> (int) $goalsg,
												'bet_time'		=> time(),
											);
											$sql = 'UPDATE ' . FOOTB_BETS . '
												SET ' . $db->sql_build_array('UPDATE', $sql_ary) . "
												WHERE season = $season AND league = $league AND match_no = $match_no AND user_id = $user_id";
											$db->sql_query($sql);
											$count_updates++;
										}
									}
									$count_matches++;
									$lastmatch_no = $match_no;
								}
							}
							else
							{
								// Goals unset
								$sql_ary = array(
									'goals_home'			=> '',
									'goals_guest'			=> '',
									'bet_time'		=> time(),
								);
								$sql = 'UPDATE ' . FOOTB_BETS . '
									SET ' . $db->sql_build_array('UPDATE', $sql_ary) . "
									WHERE season = $season AND league = $league AND match_no = $match_no AND user_id = $user_id";
								$db->sql_query($sql);
							}
						}
					}
					if ($count_updates > 0)
					{
						if ($same AND ($count_matches > 6) AND $config['football_same_allowed'] == 0)
						{
							$sql_ary = array(
								'goals_home'	=> (int) $goalsh + 1,
								'bet_time'		=> time(),
							);
							$sql = 'UPDATE ' . FOOTB_BETS . '
								SET ' . $db->sql_build_array('UPDATE', $sql_ary) . "
								WHERE season = $season AND league = $league AND match_no = $lastmatch_no and user_id = $user_id";
							$db->sql_query($sql);
							$dbmsg = sprintf($user->lang['SAMESAVED'], $count_updates);
						}
						else
						{
							if ($count_updates == 1)
							{
								$dbmsg = sprintf($user->lang['BETSAVED']);
							}
							else
							{
								$dbmsg = sprintf($user->lang['BETSSAVED'], $count_updates);
							}
						}
					}
					else
					{
						$dbmsg = sprintf($user->lang['NO_BETS_SAVED']);
					}
					
					// extra bets
					$sql = 'SELECT * FROM ' . FOOTB_EXTRA . " WHERE season = $season AND league = $league  AND matchday = $matchday AND extra_status <= 0";
					$resultextra = $db->sql_query($sql);
					$count_extra_updates = 0;
					while( $row = $db->sql_fetchrow($resultextra))
					{
						$extra_no = $row['extra_no'];
						$extra_bet = $this->request->variable('extra' . $extra_no, 'nv');
						if ($extra_bet != 'nv')
						{
							if ($row['question_type'] == 5 && !is_numeric($extra_bet))
							{
								$extra_bet = '';
							}
							if ($extra_bet != '') 
							{
								$sql = 'SELECT * FROM ' . FOOTB_EXTRA_BETS . " WHERE season = $season AND league = $league AND extra_no = $extra_no and user_id = $user_id";
								$result = $db->sql_query($sql);
								$row2 = $db->sql_fetchrow($result);
								$db->sql_freeresult($result);
								if(!$row2)
								{
									$sql_ary = array(
										'season'		=> (int) $season,
										'league'		=> (int) $league,
										'extra_no'		=> (int) $extra_no,
										'user_id'		=> (int) $user_id,
										'bet'			=> $extra_bet,
										'bet_points'	=> 0,
									);
									$sql = 'INSERT INTO ' . FOOTB_EXTRA_BETS . ' ' . $db->sql_build_array('INSERT', $sql_ary);
									$db->sql_query($sql);
								}
								else
								{
									$sql_ary = array(
										'bet'	=> $extra_bet,
									);
									$sql = 'UPDATE ' . FOOTB_EXTRA_BETS . '
										SET ' . $db->sql_build_array('UPDATE', $sql_ary) . "
										WHERE season = $season AND league = $league AND extra_no = $extra_no AND user_id = $user_id";
									$db->sql_query($sql);
								}
								$count_extra_updates++;
							}
							else
							{
								// extra bet unset
								$sql_ary = array(
									'bet'	=> '',
								);
								$sql = 'UPDATE ' . FOOTB_EXTRA_BETS . '
									SET ' . $db->sql_build_array('UPDATE', $sql_ary) . "
									WHERE season = $season AND league = $league AND extra_no = $extra_no AND user_id = $user_id";
								$db->sql_query($sql);
							}
						}
					}
					$db->sql_freeresult($resultextra);
					if ($count_extra_updates)
					{
						$dbmsg = $dbmsg . ' ' . sprintf($user->lang['EXTRA_BET' . (($count_extra_updates == 1) ? '' : 'S') . '_SAVED'], $count_extra_updates);
					}
				}
			break;
			case 'result':
				// Save results
				$user_id = $user->data['user_id'];
				$sqlmatches = 'SELECT * FROM ' . FOOTB_MATCHES . " WHERE season = $season AND league = $league AND matchday = $matchday";
				$resultmatches = $db->sql_query($sqlmatches);
				$count_matches = 0;
				$count_clear = 0;
				$count_input = 0;
				$count_null = 0;
				while( $row = $db->sql_fetchrow($resultmatches))
				{
					$count_matches++;
					$match_no = $row['match_no'];
					$status = $row['status'];
					$this->request->variable('l', 0);
					$goalsh = $this->request->variable('goalsh' . $match_no, 'nv');
					$goalsg = $this->request->variable('goalsg' . $match_no, 'nv');
					$oldgoalsh = $this->request->variable('oldgoalsh' . $match_no, 'nv');
					$oldgoalsg = $this->request->variable('oldgoalsg' . $match_no, 'nv');
					if ($goalsh != 'nv' AND $goalsg != 'nv')
					{
						// Both variables exists
						// Read overtime goals
						$goals_ko_h = $this->request->variable('goals_ko_h' . $match_no, 'nv');
						$goals_ko_g = $this->request->variable('goals_ko_g' . $match_no, 'nv');
						$oldgoals_ko_h = $this->request->variable('oldgoals_ko_h' . $match_no, 'nv');
						$oldgoals_ko_g = $this->request->variable('oldgoals_ko_g' . $match_no, 'nv');
						if (($status <> 3) && ($status <> 6))
						{
							if(($goalsh != '') AND ($goalsg != ''))
							{
								// Goals set
								// Set new match status
								if ($status < 3)
									$status = 2;
								else
									$status = 5;
								if (is_numeric($goalsh) AND is_numeric($goalsg) AND $goalsh >= 0 AND $goalsg >= 0)
								{
									// Values
									if(!is_numeric($goals_ko_h) OR !is_numeric($goals_ko_g) OR $goals_ko_h < 0 OR $goals_ko_g < 0)
									{
										// No overtime goals
										if (($goalsh <> $oldgoalsh) OR ($goalsg <> $oldgoalsg))
										{
											// Goals changed
											$sql_ary = array(
												'goals_home'			=> (int) $goalsh,
												'goals_guest'			=> (int) $goalsg,
												'goals_overtime_home'	=> '',
												'goals_overtime_guest'	=> '',
												'status'				=> $status,
											);
											$sql = 'UPDATE ' . FOOTB_MATCHES . '
												SET ' . $db->sql_build_array('UPDATE', $sql_ary) . "
												WHERE season = $season AND league = $league AND match_no = $match_no";
											$db->sql_query($sql);
											$count_input++;
										}
									}
									else
									{
										// With overtime goals
										if (($goalsh <> $oldgoalsh) OR ($goalsg <> $oldgoalsg) OR ($goals_ko_h <> $oldgoals_ko_h) OR ($goals_ko_g <> $oldgoals_ko_g))
										{
											// Goals or overtime goals changed
											$sql_ary = array(
												'goals_home'			=> (int) $goalsh,
												'goals_guest'			=> (int) $goalsg,
												'goals_overtime_home'	=> (int) $goals_ko_h,
												'goals_overtime_guest'	=> (int) $goals_ko_g,
												'status'				=> $status,
											);
											$sql = 'UPDATE ' . FOOTB_MATCHES . '
												SET ' . $db->sql_build_array('UPDATE', $sql_ary) . "
												WHERE season = $season AND league = $league AND match_no = $match_no";
											$db->sql_query($sql);
											$count_input++;
										}
									}
								}
							}
							else
							{
								// Goals unset
								// Set new match status
								if ($status < 3)
								{
									$status = 1;
								}
								else
								{
									$status = 4;
								}
								if (($goalsh <> $oldgoalsh) OR ($goalsg <> $oldgoalsg) OR ($goals_ko_h <> $oldgoals_ko_h) OR ($goals_ko_g <> $oldgoals_ko_g))
								{
									// Goals or overtime goals unset
									$sql_ary = array(
										'goals_home'			=> '',
										'goals_guest'			=> '',
										'goals_overtime_home'	=> '',
										'goals_overtime_guest'	=> '',
										'status'				=> $status,
									);
									$sql = 'UPDATE ' . FOOTB_MATCHES . '
										SET ' . $db->sql_build_array('UPDATE', $sql_ary) . "
										WHERE season = $season AND league = $league AND match_no = $match_no";
									$db->sql_query($sql);
									$count_clear++;
								}
								$count_null++;
							}
						}
					}
				}
				$db->sql_freeresult($resultmatches);
				switch($count_input)
				{
					case '0':
						{
							$dbmsg = sprintf($user->lang['NO_RESULT_SAVE']);
						}
						break;
					case '1':
						{
							$dbmsg = sprintf($user->lang['RESULT_SAVE'], $count_input);
						}
						break;
					default:
						{
							$dbmsg = sprintf($user->lang['RESULTS_SAVE'], $count_input);
						}
						break;
				}
				switch($count_clear)
				{
					case '0':
						{
						}
						break;
					case '1':
						{
							$dbmsg .= sprintf($user->lang['RESULT_CLEARED'], $count_clear);
						}
						break;
					default:
						{
							$dbmsg .= sprintf($user->lang['RESULTS_CLEARED'], $count_clear);
						}
						break;
				}

				$sqlopen = 'SELECT * FROM ' . FOOTB_MATCHES . " WHERE season = $season AND league = $league AND matchday = $matchday AND status = 0";
				$resultopen = $db->sql_query($sqlopen);
				$row = $db->sql_fetchrowset($resultopen);
				$db->sql_freeresult($resultopen);
				if (sizeof($row) == 0)
				{
					// No open matches, so we could set matchday status
					if ($count_null == $count_matches)
					{
						$sql_ary = array(
							'status'		=> 1,
						);
						$sql = 'UPDATE ' . FOOTB_MATCHDAYS . '
							SET ' . $db->sql_build_array('UPDATE', $sql_ary) . "
							WHERE season = $season AND league = $league AND matchday = $matchday AND status < 3";
						$db->sql_query($sql);
					}
					else
					{
						$sql_ary = array(
							'status'		=> 2,
						);
						$sql = 'UPDATE ' . FOOTB_MATCHDAYS . '
							SET ' . $db->sql_build_array('UPDATE', $sql_ary) . "
							WHERE season = $season AND league = $league AND matchday = $matchday AND delivery_date_2 = '' AND status < 3";
						$db->sql_query($sql);
					}
				}

				// extra bets
				$sql = 'SELECT * FROM ' . FOOTB_EXTRA . " WHERE season = $season AND league = $league AND matchday_eval = $matchday AND extra_status > 0";
				$resultextra = $db->sql_query($sql);
				$count_extra_updates = 0;
				while( $row = $db->sql_fetchrow($resultextra))
				{
					$extra_no = $row['extra_no'];
					$extra_results = $this->request->variable('extra' . $extra_no, array('nv'));
					$extra_result = '';
					if (sizeof($extra_results) > 0)
					{
						foreach ($extra_results as $extra_selected_value)
						{
							$extra_result = ($extra_result == '') ? $extra_selected_value : $extra_result . ';' . $extra_selected_value;
						}
					}
					else
					{
						$extra_result = $this->request->variable('extra' . $extra_no, 'nv');
					}
					if ($extra_result != 'nv')
					{
						if ($row['question_type'] == 5 && !is_numeric($extra_result))
						{
							$extra_result = '';
						}
						if ($extra_result != '') 
						{
							$sql = 'SELECT * FROM ' . FOOTB_EXTRA . " WHERE season = $season AND league = $league AND extra_no = $extra_no";
							$result = $db->sql_query($sql);
							$row2 = $db->sql_fetchrow($result);
							$db->sql_freeresult($result);
							if($row2)
							{
								$sql_ary = array(
									'result'		=> $extra_result,
									'extra_status'	=> 2,
								);
								$sql = 'UPDATE ' . FOOTB_EXTRA . '
									SET ' . $db->sql_build_array('UPDATE', $sql_ary) . "
									WHERE season = $season AND league = $league AND extra_no = $extra_no";
								$db->sql_query($sql);
								$count_extra_updates++;
							}
						}
						else
						{
							// extra result unset
							$sql_ary = array(
								'result'		=> '',
								'extra_status'	=> 1,
							);
							$sql = 'UPDATE ' . FOOTB_EXTRA . '
								SET ' . $db->sql_build_array('UPDATE', $sql_ary) . "
								WHERE season = $season AND league = $league AND extra_no = $extra_no";
							$db->sql_query($sql);
						}
					}
				}
				$db->sql_freeresult($resultextra);
				if ($count_extra_updates)
				{
					$dbmsg = $dbmsg . ' ' . sprintf($user->lang['EXTRA_RESULT' . (($count_extra_updates == 1) ? '' : 'S') . '_SAVED'], $count_extra_updates);
				}
				calculate_extra_points($season, $league, $matchday);
				save_ranking_matchday($season, $league, $matchday);
			break;
			case 'join':
				join_league($season, $league, $user->data['user_id']);
			break;
			default:
			break;
		}

		// End execute the action
		//*****************************************************************************
		
		
		// Start select season
		$season_name = '';
		$sql = 'SELECT DISTINCT s.season, s.season_name, s.season_name_short FROM ' . FOOTB_SEASONS . ' AS s
				INNER JOIN ' . FOOTB_LEAGUES . ' AS l ON (l.season = s.season)
				INNER JOIN ' . FOOTB_MATCHDAYS . ' AS sp ON (sp.season = s.season AND sp.league = l.league)
				WHERE 1
				ORDER BY season DESC';
		$result = $db->sql_query($sql);
		while( $row = $db->sql_fetchrow($result))
		{
			$selected = ($season && $row['season'] == $season) ? ' selected="selected"' : '';
			if ($selected)
			{
				$season_name = htmlspecialchars($row['season_name_short']);
			}
			$template->assign_block_vars('form_season', array(
				'S_SEASON' 		=> htmlspecialchars($row['season']),
				'S_SEASONNAME' 	=> htmlspecialchars($row['season_name_short']),
				'S_SELECTED' 	=> $selected));
		}
		$db->sql_freeresult($result);

		// End select season

		//*****************************************************************************

		// Start select league
		if ($side == 'bank')
		{
			$template->assign_block_vars('form_league', array(
				'S_LEAGUE' 		=> 0,
				'S_LEAGUENAME' 	=> sprintf($user->lang['ALL_LEAGUES']),
				'S_SELECTED' 	=> $league == 0 ? ' selected="selected"' : ''
				)
			);
		}

		$league_name = '';
		$sql = 'SELECT * FROM ' . FOOTB_LEAGUES . " WHERE season = $season AND league_type >= 1";
		$result = $db->sql_query($sql);
		while( $row = $db->sql_fetchrow($result))
		{
			$selected = ($league && $row['league'] == $league) ? ' selected="selected"' : '';
			if ($selected)
			{
				$league_name = $row['league_name'];
			}
			$template->assign_block_vars('form_league', array(
				'S_LEAGUE' 		=> $row['league'],
				'S_LEAGUENAME' 	=> $row['league_name'],
				'S_SELECTED' 	=> $selected
				)
			);
		}
		$db->sql_freeresult($result);

		// End select League

		//*****************************************************************************
		// For nav_delivery
		$prev_deadline = '';
		$prev_link = '';
		$prev_class = '';
		$next_deadline = '';
		$next_link = '';
		$next_class = '';
		$current_matchday = '';

		// Start select matchday
		$matchdayname = '';
		$matchday_name = '';
		$status = 0;
		$sql = 'SELECT * FROM ' . FOOTB_LEAGUES . " WHERE season = $season AND league = $league";
		$result = $db->sql_query($sql);
		if( $row = $db->sql_fetchrow($result))
		{
			$count_matchdays = $row['matchdays'];
			$league_type = $row['league_type'];
			$db->sql_freeresult($result);
			$lang_dates = $user->lang['datetime'];
			$local_board_time = time() + ($config['football_time_shift'] * 3600); 
			$sql = "SELECT *,
					CONCAT(
						CASE DATE_FORMAT(delivery_date,'%w')
							WHEN 0 THEN '" . $lang_dates['Sun'] . "'
							WHEN 1 THEN '" . $lang_dates['Mon'] . "'
							WHEN 2 THEN '" . $lang_dates['Tue'] . "'
							WHEN 3 THEN '" . $lang_dates['Wed'] . "'
							WHEN 4 THEN '" . $lang_dates['Thu'] . "'
							WHEN 5 THEN '" . $lang_dates['Fri'] . "'
							WHEN 6 THEN '" . $lang_dates['Sat'] . "'
							ELSE 'Error' END,
						DATE_FORMAT(delivery_date,' %d.%m.%y %H:%i')
					) as deliverytime,
					IF(delivery_date < FROM_UNIXTIME('$local_board_time'),'pastlink','futurelink') AS linkclass
					FROM " . FOOTB_MATCHDAYS . " WHERE season = $season AND league = $league AND matchday <= $count_matchdays
					ORDER BY matchday ASC";
			$result = $db->sql_query($sql);
			$status = 3;
			while ($row = $db->sql_fetchrow($result))
			{
				if ($league_type == 1 and $row['matchday_name'] == '')
				{
					$matchdayname = $row['matchday'] . '.' . sprintf($user->lang['MATCHDAY']);
				}
				else
				{
					$matchdayname = $row['matchday_name'];
				}
				if ($matchdayname == '')
				{
					$matchdayname = $row['matchday'] . '.' . sprintf($user->lang['MATCHDAY']);
				}
				
				$selected = ($matchday && $row['matchday'] == $matchday) ? ' selected="selected"' : '';
				if ($selected)
				{
					$matchday_name = $matchdayname;
					$status = $row['status'];
				}
				
				if (($matchday - 1) == $row['matchday'])
				{
					$prev_deadline = $row['deliverytime'];
					$prev_link = append_sid($side,"s=$season&amp;l=$league&amp;m=" . $row['matchday'] . $link_user);
					$prev_class = $row['linkclass'];
				}

				if (($matchday + 1) == $row['matchday'])
				{
					$next_deadline = $row['deliverytime'];
					$next_link = append_sid($side,"s=$season&amp;l=$league&amp;m=" . $row['matchday'] . $link_user);
					$next_class = $row['linkclass'];
				}
				

				if ($curr_matchday == $row['matchday'])
				{
					$current = '*';
				}
				else
				{
					$current = '';
				}

				$template->assign_block_vars('form_matchday', array(
					'S_MATCHDAY' => $row['matchday'],
					'S_MATCHDAYNAME' => $matchdayname,
					'S_SELECTED' => $selected,
					'S_CURRENT' => $current));
			}
			$db->sql_freeresult($result);
		}
		// End select matchday
		
		$select_menu_options = '';
		if (!$user->data['football_mobile'])
		{
		// Start matchday list
			if ($side == 'bet')
			{
				include($this->football_root_path . 'block/side_table.' . $this->php_ext);
			}
			else
			{
				include($this->football_root_path . 'block/rank_matchday.' . $this->php_ext);
			}
		// End matchday list

		//*****************************************************************************

		// Start total list
			include($this->football_root_path . 'block/rank_total.' . $this->php_ext);
		// End total list

		//*****************************************************************************

		// Start delivery list
			include($this->football_root_path . 'block/delivery.' . $this->php_ext);
		// End delivery list

		//*****************************************************************************
		}
		// Start assign vars
		$sql_ary = array(
			'football_season'	=> (int) $season,
			'football_league'	=> (int) $league,
			'football_matchday'	=> (int) $matchday,
		);

		$sql = 'UPDATE ' . SESSIONS_TABLE . ' SET ' . $db->sql_build_array('UPDATE', $sql_ary) . "
			WHERE session_id = '" . $db->sql_escape($user->session_id) . "'";
		$result = $db->sql_query($sql);
		
		$db->sql_freeresult($result);
		$u_footb_parm = "s=$season&amp;l=$league&amp;m=$matchday";
		$u_footb_sl = "s=$season&amp;l=$league";
		$start = $this->request->variable('start', 0);
		$print_start = ($start) ? "start=$start&amp;" : '';
		
		$template->assign_vars(array(
			'U_PRINT_FOOTBALL'	=> $this->helper->route('football_main_controller', array('side' => $side, 's' => $season, 'l' => $league, 'm' => $matchday, 'view' => 'print')),
			'U_MOBILE_SWITCH'	=> $this->helper->route('football_main_controller', array('side' => $side, 's' => $season, 'l' => $league, 'm' => $matchday, 'action' => 'switch')),
			'U_SIDE_LINK'		=> $this->helper->route('football_main_controller', array('side' => $side, 's' => $season, 'l' => $league, 'm' => $matchday)),
			'L_TOP_RANKSP' 		=> sprintf($user->lang['RANKING']) . ' ' . $matchday . '. ' . sprintf($user->lang['MATCHDAY']),
			'L_TOP_RANKGESAMT' 	=> sprintf($user->lang['TOTAL_RANKING']) . ' ' . $matchday . '. ' . sprintf($user->lang['MATCHDAY']),
			'PHPBB_ROOT_PATH'	=> $this->phpbb_root_path,
			'EXT_PATH_IMAGES'	=> $this->football_root_path . 'images/',
			'S_FOOTBALL_MOBILE' => $user->data['football_mobile'],
			'S_FOOTBALL_INFO'	=> $football_info,
			'S_FOOTBALL_BANK'	=> $config['football_bank'],
			'S_FOOTBALL_COPY' 	=> sprintf($user->lang['FOOTBALL_COPY'], $config['football_version'], $this->football_root_path . 'football/'),
			'S_FOOTBALL_FULLSCREEN'	=> $config['football_fullscreen'],
			'S_VIEW' 			=> $view,
			'S_SIDE' 			=> $side,
			'S_SEASON' 			=> $season,
			'S_LEAGUE' 			=> $league,
			'S_MATCHDAY' 		=> $matchday,
			'S_USER_SEL' 		=> $user_sel,
			'S_SEASON_NAME' 	=> $season_name,
			'S_LEAGUE_NAME' 	=> $league_name,
			'S_MATCHDAY_NAME' 	=> $matchday_name,
			'S_FORMSELF' 		=> $this->helper->route('football_main_controller', array('side' => $side)),
			'S_DELIVERY' 		=> delivery($season, $league, $matchday),
			// For nav_delivery
			'S_PREV_LINK' 		=> $prev_link,
			'S_PREV_CLASS' 		=> $prev_class,
			'S_PREV_DEADLINE' 	=> $prev_deadline,
			'S_CURR_LINK' 		=> $this->helper->route('football_main_controller', array('side' => $side, 's' => $season, 'l' => $league)),
			'S_CURR_MATCHDAY' 	=> $curr_matchday,
			'S_NEXT_LINK' 		=> $next_link,
			'S_NEXT_CLASS' 		=> $next_class,
			'S_NEXT_DEADLINE' 	=> $next_deadline,
			'S_DBMSG' 			=> $dbmsg,
			'FOOTBALL_LEFT_COLUMN' 	=> $config['football_left_column_width'],
			'FOOTBALL_RIGHT_COLUMN' => $config['football_right_column_width'],

		));
		// End assign vars

		//*****************************************************************************
		if (!$matchday and $side <> 'bank')
		{
			include($this->football_root_path . 'block/under_construction.' . $this->php_ext);
		}
		else
		{
			include($this->football_root_path . 'block/' . $side . '.' . $this->php_ext);
		}

		if ($user->data['football_mobile'])
		{
			$mobile = 'mobile_';
		}
		else
		{
			$mobile = '';
			if ($config['football_display_last_users'] > 0)
			{
				include($this->football_root_path . 'block/last_users.' . $this->php_ext);
			}
			if ($config['football_display_last_results'] > 0)
			{
				include($this->football_root_path . 'block/last_results.' . $this->php_ext);				
			}
		}
		// Send data to the template file
		if ($view == 'print') 
		{
			return $this->helper->render($mobile . 'football_print.html', $this->user->lang['PREDICTION_LEAGUE']);
		} 
		else 
		{
			return $this->helper->render($mobile . 'football_body.html', $this->user->lang['PREDICTION_LEAGUE']);
		}
	}
}
