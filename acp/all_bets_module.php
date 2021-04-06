<?php
/**
*
* @package phpBB Extension - Football Football
* @copyright (c) 2016 football (http://football.bplaced.net)
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace football\football\acp;

class all_bets_module
{
	public $u_action;

	protected $db, $user, $template, $config, $phpbb_root_path, $phpbb_admin_path, $phpEx;
	protected $root_path, $request, $php_ext, $log;


	public function __construct()
	{
		global $db, $user, $request, $template, $helper;
		global $config, $phpbb_root_path, $phpbb_admin_path, $phpEx;

		$user->add_lang_ext('football/football', 'football');
		$user->add_lang_ext('football/football', 'info_acp_all_bets');

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
		
		$helper = $phpbb_container->get('controller.helper');
		
		$this->tpl_name = 'acp_football_all_bets';
		$this->page_title = 'ACP_FOOTBALL_ALL_BETS_VIEW';

		$form_key = 'acp_football_all_bets';
		add_form_key($form_key);

		include_once($this->phpbb_root_path . 'includes/functions_user.' . $this->php_ext);

		// Check and set some common vars
		$season		= $this->request->variable('s', 0);
		$league		= $this->request->variable('l', 0);
		$matchday	= $this->request->variable('m', 0);
		$start		= $this->request->variable('start', 0);

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
			$league = current_league($season);
		}

		$league_info = league_info($season, $league);
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
					$league_matchdays	= $row['matchdays'];
					$matches_matchday	= $row['matches_on_matchday'];
					$league_name		= $row['league_name'];
					$league_type		= $row['league_type'];
					$ko_league			= ($row['league_type'] == LEAGUE_KO) ? true : false;
				}
			}
			$db->sql_freeresult($result);
		}
		else
		{
			trigger_error(sprintf($user->lang['NO_LEAGUE'], $season) . adm_back_link($this->u_action . "&amp;m=$season"), E_USER_WARNING);
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
			trigger_error(sprintf($user->lang['NO_MATCHDAY'], $league_name, $season) . adm_back_link($this->u_action . "&amp;m=$season&amp;l=$league"), E_USER_WARNING);
		}
	
		$matches_on_matchday = false;

		$sql = 'SELECT 
				COUNT(DISTINCT  user_id) AS num_users
			FROM  ' . FOOTB_BETS . " 
			WHERE  season = $season 
				AND league = $league";
		$result = $db->sql_query($sql);
		$total_users = (int) $db->sql_fetchfield('num_users');
		$db->sql_freeresult($result);

		$sql = "SELECT
				m.match_no,
				m.status,
				m.formula_home,
				m.formula_guest,
				t1.team_name_short AS hname,
				t2.team_name_short AS gname,
				t1.team_id AS hid,
				t2.team_id AS gid,
				m.goals_home,
				m.goals_guest,
				SUM(IF(b.goals_home + 0 > b.goals_guest AND b.goals_home <> '' AND b.goals_guest <> '', 1, 0)) AS home,
				SUM(IF(b.goals_home = b.goals_guest AND b.goals_home <> '' AND b.goals_guest <> '', 1, 0)) AS draw,
				SUM(IF(b.goals_home + 0 < b.goals_guest AND b.goals_home <> '' AND b.goals_guest <> '', 1, 0)) AS guest
			FROM  " . FOOTB_MATCHES . ' AS m
			LEFT JOIN ' . FOOTB_TEAMS . ' AS t1 ON (t1.season = m.season AND t1.league = m.league AND t1.team_id = m.team_id_home)
			LEFT JOIN ' . FOOTB_TEAMS . ' AS t2 ON (t2.season = m.season AND t2.league = m.league AND t2.team_id = m.team_id_guest)
			LEFT JOIN ' . FOOTB_BETS . " AS b ON(b.season = m.season AND b.league = m.league AND b.match_no = m.match_no)
			WHERE  m.season = $season 
				AND m.league = $league 
				AND m.matchday = $matchday
			GROUP BY m.match_no
			ORDER BY m.match_datetime ASC, m.match_no ASC
				";

		$result = $db->sql_query($sql);
		$matches = $db->sql_fetchrowset($result);
		$db->sql_freeresult($result);
		$count_matches = sizeof($matches);
		if ($count_matches > 11)
		{
			$split_after = 8;
			$splits = ceil($count_matches / 8);
		}
		else
		{
			$split_after = $count_matches; 
			$splits = 1;
		}

		// Make sure $start is set to the last page if it exceeds the amount
		if ($start < 0 || $start >= $total_users)
		{
			$start = ($start < 0) ? 0 : floor(($total_users - 1) / $this->config['football_users_per_page']) * $this->config['football_users_per_page'];
		}
		else
		{
			$start = floor($start / $this->config['football_users_per_page']) * $this->config['football_users_per_page'];
		}

		$sql_start = $start * $count_matches;
		$sql_limit = $this->config['football_users_per_page'] * $count_matches;

		// handle pagination.
		$base_url = $this->u_action . "&amp;s=$season&amp;l=$league&amp;m=$matchday";
		$pagination = $phpbb_container->get('pagination');
		$pagination->generate_template_pagination($base_url, 'pagination', 'start', $total_users, $this->config['football_users_per_page'], $start);

		if ($count_matches > 0)
		{
			$matches_on_matchday = true;

			$sql = "SELECT
					u.user_id,
					u.username,
					m.status,
					b.goals_home AS bet_home,
					b.goals_guest AS bet_guest,
					" . select_points() . "
				FROM  " . FOOTB_MATCHES . ' AS m
				LEFT JOIN ' . FOOTB_BETS . ' AS b ON(b.season = m.season AND b.league = m.league AND b.match_no = m.match_no)
				LEFT JOIN ' . USERS_TABLE . " AS u ON (u.user_id=b.user_id)
				WHERE m.season = $season 
					AND m.league = $league 
					AND m.matchday = $matchday
				ORDER BY LOWER(u.username) ASC, m.match_datetime ASC, m.match_no ASC";
			$result = $db->sql_query_limit($sql, $sql_limit, $sql_start);
			$user_bets = $db->sql_fetchrowset($result);
			$db->sql_freeresult($result);
			$bet_index = 0;
			$split_index = 0;
			foreach ($user_bets AS $user_bet)
			{
				if ($bet_index == $count_matches)
				{
					$bet_index = 0;
					$split_index = 0;
				}
				if (!($bet_index % $split_after))
				{
					$split_index++;
				}
				$sum_total[$user_bet['username']] = 0;
				$bet_line[$split_index][] = $user_bet;
				$bet_index++; 
			}
		}
		$match_index = 0;
		$last_match_index = 0;
		$split_index = 0;
		$matchday_sum_total = 0;
		$colorstyle_total = ' color_finally';
		foreach ($matches AS $match)
		{
			if (!($match_index % $split_after))
			{
				if ($match_index > 0) 
				{
					$total = 0;
					$count_user = 0;
					$bet_index = 0;
					$last_match_index = 0;
					foreach ($bet_line[$split_index] AS $user_bet)
					{
						if ($bet_index == 0)
						{
							$count_user++;
							$row_class = (!($count_user % 2)) ? 'bg1 row_light' : 'bg2 row_dark';
							if ($user_bet['user_id'] == $user->data['user_id'])
							{
								$row_class = 'bg3  row_user';
							}
							$template->assign_block_vars('match_panel.user_row', array(
								'ROW_CLASS' => $row_class,
								'USER_NAME' => $user_bet['username'],
								)
							);
							$total = 0;
						}	
						$bet_index++;
						$total += ($user_bet['points'] == '') ? 0 : $user_bet['points'];
						if ($user_bet['status'] < 3)
						{
							$colorstyle_total = ' color_provisionally';
						}
						$bet_home = $user_bet['bet_home'];
						$bet_guest = $user_bet['bet_guest'];
						
						$colorstyle_bet = color_style($user_bet['status']);
						$template->assign_block_vars('match_panel.user_row.bet', array(
							'BET'			=> $bet_home. ':'. $bet_guest,
							'COLOR_STYLE'	=> $colorstyle_bet,
							'POINTS'		=> ($user_bet['points'] == '') ? '&nbsp;' : $user_bet['points'],
							)
						);

						if ($bet_index == $split_after)
						{
							$sum_total[$user_bet['username']] += $total;
							$matchday_sum_total += $total;
							$bet_index = 0;
						}
					}

					$template->assign_block_vars('match_panel.tendency_footer', array(
						'S_TOTAL' => false,
						)
					);
					foreach ($matches_tendency AS $match_tendency)
					{
						$template->assign_block_vars('match_panel.tendency_footer.tendency', array(
							'TENDENCY' => $match_tendency[0],
							'MATCH_ENTRY' => $match_tendency[1],
							)
						);
					}
				}
				$matches_tendency = array();
				$split_index++;
				if ($split_index == $splits)
				{
					$display_total = true;
				}
				else
				{
					$display_total = false;
				}
				$template->assign_block_vars('match_panel', array(
					'S_TOTAL' => $display_total,
					)
				);
			}
			if (0 == $match['hid'])
			{
				$home_info		= get_team($season, $league, $match['match_no'], 'team_id_home', $match['formula_home']);
				$home_in_array	= explode("#",$home_info);
				$homename		= $home_in_array[3];
			}
			else
			{
				$homename = $match['hname'];
			}
			if (0 == $match['gid'])
			{
				$guest_info		= get_team($season, $league, $match['match_no'], 'team_id_guest', $match['formula_guest']);
				$guest_in_array	= explode("#",$guest_info);
				$guestname		= $guest_in_array[3];
			}
			else
			{
				$guestname = $match['gname'];
			}
			$colorstyle_match = color_style($match['status']);
			$template->assign_block_vars('match_panel.match', array(
				'HOME_NAME'		=> $homename,
				'GUEST_NAME'	=> $guestname,
				'RESULT'		=> $match['goals_home']. ':'.$match['goals_guest'],
				'COLOR_STYLE'	=> $colorstyle_match,
				)
			);
			
			$matches_tendency[] = array($match['home'] . '-' . $match['draw'] . '-' . $match['guest'], $homename . '-' . $guestname);
			$match_index++;
			$last_match_index++;
		}
		if ($count_matches > 0)
		{
			$total = 0;
			$count_user = 0;
			$bet_index = 0;
			foreach ($bet_line[$split_index] AS $user_bet)
			{
				if ($bet_index == 0)
				{
					$count_user++;
					$row_class = (!($count_user % 2)) ? 'bg1 row_light' : 'bg2 row_dark';
					if ($user_bet['user_id'] == $user->data['user_id'])
					{
						$row_class = 'bg3  row_user';
					}
					$template->assign_block_vars('match_panel.user_row', array(
						'ROW_CLASS' => $row_class,
						'USER_NAME' => $user_bet['username'],
						)
					);
					$total = 0;
				}	
				$bet_index++;
				$total += ($user_bet['points'] == '') ? 0 : $user_bet['points'];
				if ($user_bet['status'] < 3)
				{
					$colorstyle_total = ' color_provisionally';
				}
				$bet_home = $user_bet['bet_home'];
				$bet_guest = $user_bet['bet_guest'];
				
				$colorstyle_bet = color_style($user_bet['status']);
				$template->assign_block_vars('match_panel.user_row.bet', array(
					'BET'			=> $bet_home. ':'. $bet_guest,
					'COLOR_STYLE'	=> $colorstyle_bet,
					'POINTS'		=> ($user_bet['points'] == '') ? '&nbsp;' : $user_bet['points'],
					)
				);

				  if ($bet_index == $last_match_index)
				  {
					 $sum_total[$user_bet['username']] += $total;
					 $matchday_sum_total += $total;
					 $template->assign_block_vars('match_panel.user_row.points', array(
						'COLOR_STYLE'	=> $colorstyle_total,
						'POINTS_TOTAL'	=> $sum_total[$user_bet['username']],
						)
					 );
					 $bet_index = 0;
				  }
			}

			$template->assign_block_vars('match_panel.tendency_footer', array(
				'S_TOTAL'		=> true,
				'COLOR_STYLE'	=> $colorstyle_total, //currently ignored
				'SUMTOTAL'		=> $matchday_sum_total,
				)
			);
			foreach ($matches_tendency AS $match_tendency)
			{
				$template->assign_block_vars('match_panel.tendency_footer.tendency', array(
					'TENDENCY' => $match_tendency[0],
					'MATCH_ENTRY' => $match_tendency[1],
					)
				);
			}
		}

		//extra bets
		// Calculate extra bets of matchday
		$sql_start = $start;
		$sql_limit = $this->config['football_users_per_page'];
		$sql = "SELECT e.*,
				t1.team_name AS result_team
			FROM  " . FOOTB_EXTRA . ' AS e
			LEFT JOIN ' . FOOTB_TEAMS . " AS t1 ON (t1.season = e.season AND t1.league = e.league AND t1.team_id = e.result)
			WHERE e.season = $season 
				AND e.league = $league 
				AND e.matchday = $matchday
			ORDER BY e.extra_no ASC";
		$result = $db->sql_query($sql);
		while ($row = $db->sql_fetchrow($result))
		{
			$extra_no = $row['extra_no'];
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
			$extra_colorstyle = color_style($row['extra_status']);

			$template->assign_block_vars('extra_panel', array(
				'QUESTION'			=> $row['question'],
				'RESULT'			=> ($display_type == 1) ? $row['result_team'] : $row['result'],
				'POINTS'			=> $row['extra_points'],
				'EVALUATION'		=> ($row['matchday'] == $row['matchday_eval']) ? sprintf($user->lang['MATCHDAY']) : sprintf($user->lang['TOTAL']),
				'EVALUATION_TITLE'	=> $eval_title,
				'COLOR_STYLE'		=> $extra_colorstyle,
				)
			);

			// Get all extra bets of matchday
			$bet_number = 0;
			$sql = "SELECT u.user_id,
					u.username,
					e.*,
					eb.bet,
					eb.bet_points,
					t2.team_name AS bet_team
				FROM " . FOOTB_BETS . ' AS b 
				LEFT JOIN ' . USERS_TABLE . ' AS u ON (u.user_id = b.user_id)
				LEFT JOIN ' . FOOTB_EXTRA . " AS e ON (e.season = b.season AND e.league = b.league AND e.matchday = $matchday AND e.extra_no = $extra_no)
				LEFT JOIN " . FOOTB_EXTRA_BETS . " AS eb ON (eb.season = b.season AND eb.league = b.league AND eb.extra_no = $extra_no AND eb.user_id = b.user_id)
				LEFT JOIN " . FOOTB_TEAMS . " AS t2 ON (t2.season = b.season AND t2.league = b.league AND t2.team_id = eb.bet)
				WHERE b.season = $season 
					AND b.league = $league 
					AND b.match_no = 1
				GROUP by b.user_id
				ORDER BY LOWER(u.username) ASC";
			$result_bet = $db->sql_query_limit($sql, $sql_limit, $sql_start);

			while ($user_row = $db->sql_fetchrow($result_bet))
			{
				$bet_number++ ;
				$row_class = (!($bet_number % 2)) ? 'bg1 row_light' : 'bg2 row_dark';
				if ($user_row['user_id'] == $user->data['user_id'])
				{
					$row_class = 'bg3  row_user';
				}

				$bet = ($user_row['bet'] == '') ? '&nbsp;' : $user_row['bet'];
				$bet_team = ($user_row['bet_team'] == NULL) ? '&nbsp;' : $user_row['bet_team'];

				
				$template->assign_block_vars('extra_panel.user_row', array(
					'ROW_CLASS'		=> $row_class,
					'USER_NAME'		=> $user_row['username'],
					'BET'			=> ($display_type == 1) ? $bet_team : $bet,
					'BET_POINTS'	=> $user_row['bet_points'],
					'COLOR_STYLE'	=> $extra_colorstyle,
					)
				);
			}
			$db->sql_freeresult($result_bet);
		}
		$db->sql_freeresult($result);
		
		$legend = delivery($season, $league, $matchday);

		$template->assign_vars(array(
			'U_FOOTBALL'				=> $helper->route('football_football_controller',array('side' => 'all_bets', 's' => $season, 'l' => $league, 'm' => $matchday)),
			'S_SEASON'					=> $season,
			'S_LEAGUE'					=> $league,
			'S_MATCHDAY'				=> $matchday,
			'S_SEASON_OPTIONS'			=> $season_options,
			'S_LEAGUE_OPTIONS'			=> $league_options,
			'S_MATCHDAY_OPTIONS'		=> $matchday_options,
			'S_DISPLAY_ALL_BETS'		=> true,
			'S_MATCHES_ON_MATCHDAY'		=> $matches_on_matchday,
			'S_SPALTEN'					=> ($count_matches * 2)+2,
			'S_VERSION_NO'				=> $this->config['football_version'],
			'TOTAL_USERS'				=> ($total_users == 1) ? $user->lang['VIEW_BET_USER'] : sprintf($user->lang['VIEW_BET_USERS'], $total_users),
			'PAGE_NUMBER'				=> $pagination->on_page($total_users, $this->config['football_users_per_page'], $start),
			)
		);
	}
}
