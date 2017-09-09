<?php
/**
*
* @package phpBB Extension - Football Football
* @copyright (c) 2016 football (http://football.bplaced.net)
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace football\football\acp;

class bets_module
{
	public $u_action;
	public $ext_football_path;

	protected $db, $user, $template, $config, $phpbb_root_path, $phpbb_admin_path, $phpEx;
	protected $root_path, $request, $php_ext, $log;


	public function __construct()
	{
		global $db, $user, $request, $template;
		global $config, $phpbb_root_path, $phpbb_admin_path, $phpEx;

		$user->add_lang_ext('football/football', 'football');
		$user->add_lang_ext('football/football', 'info_acp_bets');


		$this->config = $config;
		$this->request = $request;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->phpbb_admin_path = $phpbb_admin_path;
		$this->php_ext = $phpEx;

	}

	function main($id, $mode)
	{
		global $db, $auth, $phpbb_container, $phpbb_admin_path, $league_info, $functions_points;
		global $template, $user, $config, $phpbb_extension_manager, $request, $phpbb_root_path, $phpEx;
				
		$helper = $phpbb_container->get('controller.helper');

		$this->ext_football_path = $phpbb_root_path . 'ext/football/football/';
		if(!function_exists('season_info'))
		{
			include($this->ext_football_path . 'includes/functions.' . $phpEx);
		}
		if (!defined('FOOTB_SEASONS'))
		{
			include($this->ext_football_path . 'includes/constants.' . $phpEx);
		}


		if ($phpbb_extension_manager->is_enabled('dmzx/ultimatepoints'))
		{
			//$this->user->add_lang_ext('football/football', 'modules/points');
			// Get an instance of the ultimatepoints functions_points
			$functions_points = $phpbb_container->get('dmzx.ultimatepoints.core.functions.points');
		}

		$this->tpl_name = 'acp_football_bets';
		$this->page_title = 'ACP_FOOTBALL_BETS_MANAGE';

		$form_key = 'acp_football_bets';
		add_form_key($form_key);

		include($this->phpbb_root_path . 'includes/functions_user.' . $this->php_ext);

		// Check and set some common vars
		$action		= (isset($_POST['bet'])) ? 'bet' : $this->request->variable('action', '');
		$season		= $this->request->variable('s', 0);
		$league		= $this->request->variable('l', 0);
		$matchday	= $this->request->variable('m', 0);
		$user_id	= $this->request->variable('u', 0);

		// Clear some vars
		$success = array();

		// Grab current season
		$curr_season = curr_season();
		if (!$season)
		{
			$season = $curr_season;
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
			$league = current_league($season);
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
		$sql = 'SELECT *
			FROM ' . FOOTB_MATCHDAYS . "
			WHERE season = $season 
				AND league = $league
			ORDER BY matchday ASC";
		$result = $db->sql_query($sql);

		$matchday_options = '';
		while ($row = $db->sql_fetchrow($result))
		{
			$selected = ($matchday && $row['matchday'] == $matchday) ? ' selected="selected"' : '';
			$day_name = (strlen($row['matchday_name']) > 0) ? $row['matchday_name'] : $row['matchday'] . '. ' . sprintf($user->lang['MATCHDAY']);
			$matchday_options .= '<option value="' . $row['matchday'] . '"' . $selected . '>' . $day_name . '</option>';
			if ($selected <> '')
			{
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

		// Grab basic data for select user
		if (!$user_id)
		{
			if (user_is_member($user->data['user_id'], $season, $league))
			{
				$user_id = $user->data['user_id'];
			}
		}
		$user_options = '';
		$sql = 'SELECT 
				DISTINCT u.user_id,
				u.username
			FROM ' . FOOTB_BETS . ' AS w
			LEFT JOIN ' . USERS_TABLE . " AS u ON (u.user_id = w.user_id)
			WHERE season = $season 
				AND league = $league
			ORDER BY LOWER(u.username) ASC";
				
		$result = $db->sql_query($sql);
		while( $row = $db->sql_fetchrow($result))
		{
			if (!$user_id)
			{
				$selected = ' selected="selected"';
				$user_id = $row['user_id'];
				
			}
			else
			{
				$selected = ($user_id && $row['user_id'] == $user_id) ? ' selected="selected"' : '';
			}
			$user_options .= '<option value="' . $row['user_id'] . '"' . $selected . '>' . $row['username'] . '</option>';
			if ($selected <> '')
			{
				$user_name = $row['username'];
			}
		}
		$db->sql_freeresult($result);

		switch($action)
		{
			case 'bet':
				{
					if ($season < $curr_season)
					{
						trigger_error("Diese Saison kann nicht mehr gespeichert werden!", E_USER_WARNING);
					}
					$sqlopen = 'SELECT * 
						FROM ' . FOOTB_MATCHES . " 
						WHERE season = $season 
							AND league = $league 
							AND matchday = $matchday";
					$resultopen = $db->sql_query($sqlopen);
					$count_matches = 0;
					$count_updates = 0;
					while( $row = $db->sql_fetchrow($resultopen))
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
									$sql = 'SELECT * 
										FROM ' . FOOTB_BETS . " 
										WHERE season = $season 
											AND league = $league 
											AND match_no = $match_no 
											AND user_id = $user_id";
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
												WHERE season = $season 
													AND league = $league 
													AND match_no = $match_no 
													AND user_id = $user_id";
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
									'goals_home'	=> '',
									'goals_guest'	=> '',
									'bet_time'		=> time(),
								);
								$sql = 'UPDATE ' . FOOTB_BETS . '
									SET ' . $db->sql_build_array('UPDATE', $sql_ary) . "
									WHERE season = $season 
										AND league = $league 
										AND match_no = $match_no 
										AND user_id = $user_id";
								$db->sql_query($sql);
							}
						}
					}
					$db->sql_freeresult($resultopen);
					if ($count_updates > 0)
					{
						if ($same AND ($count_matches > 6) AND $this->config['football_same_allowed'] == 0)
						{
							$sql_ary = array(
								'goals_home'	=> (int) $goalsh + 1,
								'bet_time'		=> time(),
							);
							$sql = 'UPDATE ' . FOOTB_BETS . '
								SET ' . $db->sql_build_array('UPDATE', $sql_ary) . "
								WHERE season = $season 
									AND league = $league 
									AND match_no = $lastmatch_no 
									AND user_id = $user_id";
							$db->sql_query($sql);
							$success[] = sprintf($user->lang['SAMESAVED'], $count_updates);
						}
						else
						{
							if ($count_updates == 1)
							{
								$success[] = sprintf($user->lang['BETSAVED']);
							}
							else
							{
								$success[] = sprintf($user->lang['BETSSAVED'], $count_updates);
							}
						}
					}
					else
					{
						$success[] = sprintf($user->lang['NO_BETS_SAVED']);
					}

					// extra bets
					$sql = 'SELECT * FROM ' . FOOTB_EXTRA . " WHERE season = $season AND league = $league  AND matchday = $matchday";
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
						$success[] = sprintf($user->lang['EXTRA_BET' . (($count_extra_updates == 1) ? '' : 'S') . '_SAVED'], $count_extra_updates);
					}
					$league_info = league_info($season, $league);
					$cash	= $this->request->variable('cash', false);
					save_ranking_matchday($season, $league, $matchday, $cash);
				}
			break;
		}

		$data_group = false;
		$matchnumber = 0;
		$lang_dates = $user->lang['datetime'];

		// Calculate matches and bets of matchday
		$sql = "SELECT
				s.league,
				s.match_no,
				s.matchday,
				s.status,
				s.group_id,
				s.formula_home,
				s.formula_guest,
				v1.team_symbol AS logo_home,
				v2.team_symbol AS logo_guest,
				v1.team_id AS hid,
				v2.team_id AS gid,
				v1.team_name AS hname,
				v2.team_name AS gname,
				w.bet_time,
				w.goals_home AS bet_home,
				w.goals_guest AS bet_guest,
				s.goals_home, 
				s.goals_guest,
				CONCAT(
					CASE DATE_FORMAT(s.match_datetime,'%w')
						WHEN 0 THEN '" . $lang_dates['Sun'] . "'
						WHEN 1 THEN '" . $lang_dates['Mon'] . "'
						WHEN 2 THEN '" . $lang_dates['Tue'] . "'
						WHEN 3 THEN '" . $lang_dates['Wed'] . "'
						WHEN 4 THEN '" . $lang_dates['Thu'] . "'
						WHEN 5 THEN '" . $lang_dates['Fri'] . "'
						WHEN 6 THEN '" . $lang_dates['Sat'] . "'
						ELSE 'Error' END,
					DATE_FORMAT(s.match_datetime,' %d.%m. %H:%i')
				) AS match_time
			FROM  " . FOOTB_MATCHES . ' AS s
			INNER JOIN ' . FOOTB_BETS . " AS w ON (w.season = s.season AND w.league = s.league AND w.match_no = s.match_no AND w.user_id = $user_id)
			LEFT JOIN " . FOOTB_TEAMS . ' AS v1 ON (v1.season = s.season AND v1.league = s.league AND v1.team_id = s.team_id_home)
			LEFT JOIN ' . FOOTB_TEAMS . " AS v2 ON (v2.season = s.season AND v2.league = s.league AND v2.team_id = s.team_id_guest)
			WHERE s.season = $season 
				AND s.league = $league 
				AND s.matchday = $matchday
			GROUP BY s.match_no
			ORDER BY s.match_datetime ASC, s.match_no ASC";
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			$matchnumber++ ;
			$class = (!($matchnumber % 2)) ? 'bg1 row_light' : 'bg2 row_dark';
			$display_link = true;

			if (0 == $row['hid'])
			{
				$display_link 	= false;
				$home_info 		= get_team($season, $league, $row['match_no'], 'team_id_home', $row['formula_home']);
				$home_in_array 	= explode("#",$home_info);
				$homelogo 		= $home_in_array[0];
				$homeid 		= $home_in_array[1];
				$homename 		= $home_in_array[2];
			}
			else
			{
				$homelogo 	= $row['logo_home'];
				$homeid 	= $row['hid'];
				$homename 	= $row['hname'];
			}

			if (0 == $row['gid'])
			{
				$display_link 	= false;
				$guest_info 	= get_team($season, $league, $row['match_no'], 'team_id_guest', $row['formula_guest']);
				$guest_in_array = explode("#",$guest_info);
				$guestlogo 		= $guest_in_array[0];
				$guestid 		= $guest_in_array[1];
				$guestname 		= $guest_in_array[2];
			}
			else
			{
				$guestlogo 	= $row['logo_guest'];
				$guestid 	= $row['gid'];
				$guestname 	= $row['gname'];
			}

			if ($homelogo <> '')
			{
				$logoH = "<img src=\"". $this->ext_football_path . 'images/flags/' . $homelogo . "\" alt=\"" . $homelogo . "\" width=\"28\" height=\"28\"/>" ;
			}
			else
			{
				$logoH = "<img src=\"". $this->ext_football_path . "images/flags/blank.gif\" alt=\"\" width=\"28\" height=\"28\"/>" ;
			}

			if ($guestlogo <> '')
			{
				$logoG = "<img src=\"". $this->ext_football_path . 'images/flags/' . $guestlogo . "\" alt=\"" . $guestlogo . "\" width=\"28\" height=\"28\"/>" ;
			}
			else
			{
				$logoG = "<img src=\"". $this->ext_football_path . "images/flags/blank.gif\" alt=\"\" width=\"28\" height=\"28\"/>" ;
			}

			if ($row['status'] == -1)
			{
				$delivertag = "<strong style='color:green'>*</strong>";
			}
			else
			{
				if ($row['status'] == -2)
				{
					$delivertag = "<strong style='color:green'>**</strong>";
				}
				else
				{
					$delivertag = '';
				}
			}

			if ($row['group_id'] == '')
			{
				$group_id = '&nbsp;';
			}
			else
			{
				$data_group = true;
				$group_id = $row['group_id'];
			}

			$template->assign_block_vars('bet_edit', array(
				'ROW_CLASS' 	=> $class,
				'LEAGUE_ID' 	=> $row['league'],
				'MATCH_NUMBER' 	=> $row['match_no'],
				'MATCHDAY' 		=> $row['matchday'],
				'STATUS' 		=> $row['status'],
				'MATCH_TIME' 	=> $row['match_time'],
				'GROUP' 		=> $group_id,
				'HOME_ID' 		=> $homeid,
				'GUEST_ID' 		=> $guestid,
				'LOGO_HOME' 	=> $logoH,
				'LOGO_GUEST' 	=> $logoG,
				'HOME_NAME' 	=> $homename,
				'GUEST_NAME' 	=> $guestname,
				'BET_HOME' 		=> $row['bet_home'],
				'BET_GUEST' 	=> $row['bet_guest'],
				'DELIVERTAG' 	=> $delivertag,
				'GOALS_HOME' 	=> ($row['goals_home'] == '') ? '&nbsp;' : $row['goals_home'],
				'GOALS_GUEST'	=> ($row['goals_guest'] == '') ? '&nbsp;' : $row['goals_guest'],
				'BET_TIME' 		=> ($row['bet_time'] == 0) ? '' : $user->format_date($row['bet_time']),
				'DISPLAY_LINK'	=> $display_link,
				)
			);
		}
		$db->sql_freeresult($resultopen);

		// Calculate extra bets of matchday
		// Start select team
		$sql = 'SELECT 
					team_id AS option_value,
					team_name AS option_name
				FROM ' . FOOTB_TEAMS . "
				WHERE season = $season 
				AND league = $league 
				ORDER BY team_name ASC";
		$result = $db->sql_query($sql);
		$option_rows = $db->sql_fetchrowset($result);
		$db->sql_freeresult($result);

		$sql = "SELECT e.*,
				eb.bet,
				eb.bet_points,
				t1.team_name AS result_team,
				t2.team_name AS bet_team
			FROM  " . FOOTB_EXTRA . ' AS e
			LEFT JOIN ' . FOOTB_EXTRA_BETS . " AS eb ON (eb.season = e.season AND eb.league = e.league AND eb.extra_no = e.extra_no AND eb.user_id = $user_id)
			LEFT JOIN " . FOOTB_TEAMS . ' AS t1 ON (t1.season = e.season AND t1.league = e.league AND t1.team_id = e.result)
			LEFT JOIN ' . FOOTB_TEAMS . " AS t2 ON (t2.season = e.season AND t2.league = e.league AND t2.team_id = eb.bet)
			WHERE e.season = $season 
				AND e.league = $league 
				AND e.matchday = $matchday
			ORDER BY e.extra_no ASC";
		$result = $db->sql_query($sql);

		$extra_bet = false;
		$extra_edit = false;
		$extra_results = false;
		$extranumber = 0;

		while ($row = $db->sql_fetchrow($result))
		{
			$extra_bet = true;
			$extranumber++ ;
			$row_class = (!($extranumber % 2)) ? 'bg1 row_light' : 'bg2 row_dark';

			switch($row['question_type'])
			{
				case '1':
					{
						$display_type = 1;
						$eval_title = sprintf($user->lang['EXTRA_HIT']);
					}
					break;
				case '2':
					{
						$display_type = 1;
						$eval_title = sprintf($user->lang['EXTRA_MULTI_HIT']);
					}
					break;
				case '3':
					{
						$display_type = 2;
						$eval_title = sprintf($user->lang['EXTRA_HIT']);
					}
					break;
				case '4':
					{
						$display_type = 2;
						$eval_title = sprintf($user->lang['EXTRA_MULTI_HIT']);
					}
					break;
				case '5':
					{
						$display_type = 2;
						$eval_title = sprintf($user->lang['EXTRA_DIFFERENCE']);
					}
					break;
				default :
					{
						$display_type = 2;
						$eval_title = '';
					}
					break;
			}
			
			// edit extra bets
			$extra_edit = true;
			$bet_extra = ($row['bet_team'] == NULL) ? '' : $row['bet_team'];

			switch($row['question_type'])
			{
				case '3':
				case '4':
					{
						$option_arr = array();
						for ($i = 65; $i <= 72; $i++) 
						{
							if (strstr($row['question'], chr($i) . ':'))
							{
								$option_arr[] = array(
									'option_value'	=> chr($i),
									'option_name'	=> chr($i),
								);
							}
						}
						if ( sizeof($option_arr) > 1 )
						{
							$display_type = 1;
							$option_rows = $option_arr;
							$bet_extra = $row['bet'];
						}
					}
					break;
			}
		
			$template->assign_block_vars('extra_edit', array(
				'ROW_CLASS' 		=> $row_class,
				'EXTRA_NO' 			=> $row['extra_no'],
				'QUESTION' 			=> $row['question'],
				'EXTRA_POINTS' 		=> $row['extra_points'],
				'EVALUATION' 		=> ($row['matchday'] == $row['matchday_eval']) ? sprintf($user->lang['MATCHDAY']) : sprintf($user->lang['TOTAL']),
				'EVALUATION_TITLE' 	=> $eval_title,
				'BET' 				=> ($display_type == 1) ? $bet_extra : $row['bet'],
				'S_DISPLAY_TYPE'	=> $display_type,
				)
			);
			
			if ($display_type == 1)
			{
				$selected = ($row['bet'] == '') ? ' selected="selected"' : '';
				
				$template->assign_block_vars('extra_edit.extra_option', array(
					'OPTION_VALUE' 	=> '',
					'OPTION_NAME' 	=> sprintf($user->lang['SELECT']),
					'S_SELECTED' 	=> $selected));
					
				foreach ($option_rows as $option_row)
				{
					$selected = ($row['bet'] && $option_row['option_value'] == $row['bet']) ? ' selected="selected"' : '';
					$template->assign_block_vars('extra_edit.extra_option', array(
						'OPTION_VALUE' 	=> $option_row['option_value'],
						'OPTION_NAME' 	=> $option_row['option_name'],
						'S_SELECTED' 	=> $selected));
				}
			}
		}
		$db->sql_freeresult($result);
		
		$legend = delivery($season, $league, $matchday);

		$template->assign_vars(array(
			'U_FOOTBALL' 			=> $helper->route('football_main_controller',array('side' => 'bet', 's' => $season, 'l' => $league, 'm' => $matchday)),
			'S_LEGEND'				=> $legend,
			'S_SUCCESS'				=> (sizeof($success)) ? true : false,
			'SUCCESS_MSG'			=> (sizeof($success)) ? implode('<br />', $success) : '',
			'S_CASH_POINTS'			=> ($phpbb_extension_manager->is_enabled('dmzx/ultimatepoints')) ? true : false,
			'S_SEASON'				=> $season,
			'S_LEAGUE'				=> $league,
			'S_MATCHDAY'			=> $matchday,
			'S_USER'				=> $user_id,
			'S_SEASON_OPTIONS'		=> $season_options,
			'S_LEAGUE_OPTIONS'		=> $league_options,
			'S_MATCHDAY_OPTIONS'	=> $matchday_options,
			'S_USER_OPTIONS'		=> $user_options,
			'S_USERS'				=> ($user_options == '') ? false : true,
			'S_EXTRA_BET'			=> $extra_bet,
			'S_VERSION_NO'			=> $this->config['football_version'],
			)
		);
	}
}
?>