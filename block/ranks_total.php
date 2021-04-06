<?php
/**
*
* @package phpBB Extension - Football Football
* @copyright (c) 2016 football (http://football.bplaced.net)
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

if (!defined('IN_PHPBB'))
{
	exit;
}

$start = $this->request->variable('start', 0);
$mode = $this->request->variable('mode', '');

switch ($mode)
{
	case 'alltime':
		// Statistics  
		$sql = "SELECT
					b.user_id,
					COUNT(b.match_no) AS matches,
					SUM(IF(b.goals_home <> '' AND b.goals_guest <> '', 1, 0)) AS bets,
					SUM(IF(b.goals_home <> '' AND b.goals_guest <> '',
							IF((b.goals_home + 0 < b.goals_guest) <> (m.goals_home + 0 < m.goals_guest) 
								OR (b.goals_home = b.goals_guest) <> (m.goals_home = m.goals_guest) 
								OR (b.goals_home + 0 > b.goals_guest) <> (m.goals_home + 0 > m.goals_guest),
								0,
								IF((b.goals_home = m.goals_home) AND (b.goals_guest = m.goals_guest), 0, 1)
							),
							0
						)
					) AS tendency
				FROM " . FOOTB_BETS . ' AS b
				LEFT JOIN ' . FOOTB_MATCHES . ' AS m ON (m.season = b.season AND m.league = b.league AND m.match_no = b.match_no)
				LEFT JOIN ' . FOOTB_LEAGUES . " AS l ON (l.season = b.season AND l.league = b.league)
				WHERE b.league = $league 
					AND ((b.season < $season) OR (b.season = $season AND m.matchday <= $matchday))
					AND m.status IN (2,3) 
				GROUP BY b.user_id";

		$result = $db->sql_query($sql);
		$rows = $db->sql_fetchrowset($result);
		$total_users = sizeof($rows);
		$db->sql_freeresult($result);

		foreach ($rows AS $row)
		{
			$bets_of[$row['user_id']] 		= $row['bets'];
			$nobets_of[$row['user_id']] 	= $row['matches'] - $row['bets'];
		}

		// Wins  
		$sql = 'SELECT
					r.user_id,
					sum(r.win_total) As win_total
				FROM ' . FOOTB_RANKS . ' AS r
				LEFT JOIN ' . FOOTB_LEAGUES . " AS l ON (l.season = r.season AND l.league = r.league)
				WHERE r.league = $league 
					AND ((r.season < $season AND r.matchday = l.matchdays) OR (r.season = $season AND r.matchday = $matchday))
					AND r.status IN (2,3) 
				GROUP BY r.user_id
				ORDER BY r.user_id ASC";
				
		$result = $db->sql_query($sql);

		$win_arr = array();
		while($row = $db->sql_fetchrow($result))
		{
			$win_arr[$row['user_id']] = $row['win_total'];
		}
		$db->sql_freeresult($result);

		$data_ranks = false;
		$pagination = '';

		$sql = 'SELECT
					r.user_id,
					u.username,
					min(r.status) AS status,
					sum(r.points) As points_total,
					sum(r.tendencies) As tendencies,
					sum(r.correct_result) As hits
				FROM ' . FOOTB_RANKS . ' AS r
				LEFT JOIN ' . USERS_TABLE . ' AS u ON (r.user_id = u.user_id)
				LEFT JOIN ' . FOOTB_LEAGUES . " AS l ON (l.season = r.season AND l.league = r.league)
				WHERE r.league = $league 
					AND ((r.season < $season) OR (r.season = $season AND r.matchday <= $matchday))
					AND r.status IN (2,3) 
				GROUP BY r.user_id
				ORDER BY points_total DESC, LOWER(u.username) ASC";
				
		$result = $db->sql_query($sql);

		$ranking_arr = array();
		while($row = $db->sql_fetchrow($result))
		{
			$ranking_arr[$row['user_id']] = $row;
		}
		$db->sql_freeresult($result);


		// Make sure $start is set to the last page if it exceeds the amount
		if ($start < 0 || $start >= $total_users)
		{
			$index_start = ($start < 0) ? 0 : floor(($total_users - 1) / $config['football_users_per_page']) * $config['football_users_per_page'];
		}
		else
		{
			$index_start = floor($start / $config['football_users_per_page']) * $config['football_users_per_page'];
		}
		$index_end = $index_start + $config['football_users_per_page'] - 1;

		// handle pagination.
		$base_url = $this->helper->route('football_football_controller', array('side' => 'ranks_total', 's' => $season, 'l' => $league, 'm' => $matchday, 'mode' => 'alltime'));
		$pagination = $phpbb_container->get('pagination');
		$pagination->generate_template_pagination($base_url, 'pagination', 'start', $total_users, $this->config['football_users_per_page'], $start);

		$index = 0;
		$rank = 0;
		$last_points = 0;
		$data_rank_total = false;
		foreach ($ranking_arr AS $curr_rank)
		{
			if ($curr_rank['points_total'] <> $last_points)
			{
				$rank = $index + 1;
				$last_points = $curr_rank['points_total'];
			}
			$data_ranks = true;
			if (($index_start <= $index) && ($index <= $index_end))
			{
				$row_class = (!($index % 2)) ? 'bg1 row_light' : 'bg2 row_dark';
				if ($curr_rank['user_id'] == $user->data['user_id'])
				{
					$row_class = 'bg3  row_user';
				}
				$colorstyle = color_style($curr_rank['status']);

				$template->assign_block_vars('rankstotal', array(
					'ROW_CLASS' 	=> $row_class,
					'RANK' 			=> $rank,
					'USERID' 		=> $curr_rank['user_id'],
					'USERNAME' 		=> $curr_rank['username'],
					'URL' 			=> $phpbb_root_path . 'profile.php?mode=viewprofile&u=' . $curr_rank['user_id'],
					'BETS' 			=> $bets_of[$curr_rank['user_id']],
					'NOBETS' 		=> ($nobets_of[$curr_rank['user_id']] == 0) ? '&nbsp;' : $nobets_of[$curr_rank['user_id']],
					'TENDENCIES'	=> ($curr_rank['tendencies'] == 0) ? '&nbsp;' : $curr_rank['tendencies'],
					'DIRECTHITS' 	=> ($curr_rank['hits'] == 0) ? '&nbsp;' : $curr_rank['hits'],
					'POINTS' 		=> $curr_rank['points_total'],
					'COLOR_STYLE'	=> $colorstyle,
					'WIN' 			=> $win_arr[$curr_rank['user_id']],
					)
				);
			}
			$index++;
		}

		$sidename = sprintf($user->lang['RANK_TOTAL']);
		$league_info = league_info($season, $league);
		$template->assign_vars(array(
			'S_DISPLAY_RANKS_TOTAL'		=> true,
			'S_DISPLAY_HITS02'			=> $config['football_win_hits02'],
			'S_DATA_RANKS' 				=> $data_ranks,
			'S_SIDENAME' 				=> $sidename,
			'PAGE_NUMBER' 				=> $pagination->on_page($total_users, $this->config['football_users_per_page'], $start),
			'TOTAL_USERS'				=> ($total_users == 1) ? $user->lang['VIEW_BET_USER'] : sprintf($user->lang['VIEW_BET_USERS'], $total_users),
			'S_WIN' 					=> false,
			'WIN_NAME' 					=> $config['football_win_name'],
			'S_SHOW_OTHER_LINKS'		=> true,
			'S_HEADER'					=> sprintf($user->lang['RANKING_ALL_TIME']),
			'S_LINK_RANKING'			=> $this->helper->route('football_football_controller', array('side' => 'ranks_total', 's' => $season, 'l' => $league, 'm' => $matchday)),
			'S_LINK_ALL_TIME'			=> '',
			'S_LINK_COMPARE'			=> $this->helper->route('football_football_controller', array('side' => 'ranks_total', 's' => $season, 'l' => $league, 'm' => $matchday, 'mode' => 'compare')),
			)
		);
	break;

	case 'compare':
		// Statistics  
		$sql = "SELECT
					b.season as season,
					b.user_id,
					COUNT(b.match_no) AS matches,
					SUM(IF(b.goals_home <> '' AND b.goals_guest <> '', 1, 0)) AS bets,
					SUM(IF(b.goals_home <> '' AND b.goals_guest <> '',
							IF((b.goals_home + 0 < b.goals_guest) <> (m.goals_home + 0 < m.goals_guest) 
								OR (b.goals_home = b.goals_guest) <> (m.goals_home = m.goals_guest) 
								OR (b.goals_home + 0 > b.goals_guest) <> (m.goals_home + 0 > m.goals_guest),
								0,
								IF((b.goals_home = m.goals_home) AND (b.goals_guest = m.goals_guest), 0, 1)
							),
							0
						)
					) AS tendency,
					SUM(IF(b.goals_home = m.goals_home AND b.goals_guest = m.goals_guest, 1, 0)) AS hits
				FROM " . FOOTB_BETS . ' AS b
				LEFT JOIN ' . FOOTB_MATCHES . " AS m ON (m.season = b.season AND m.league = b.league AND m.match_no = b.match_no)
				WHERE b.season <= $season
					AND b.league = $league 
					AND m.matchday <= $matchday
					AND m.status IN (2,3) 
				GROUP BY b.season, b.user_id";

		$result = $db->sql_query($sql);
		$rows = $db->sql_fetchrowset($result);
		$total_users = sizeof($rows);
		$db->sql_freeresult($result);

		foreach ($rows AS $row)
		{
			$bets_of[$row['user_id'] . '#' . $row['season']] 		= $row['bets'];
			$nobets_of[$row['user_id'] . '#' . $row['season']] 		= $row['matches'] - $row['bets'];
			$tendency_of[$row['user_id'] . '#' . $row['season']] 	= $row['tendency'];
			$hits_of[$row['user_id'] . '#' . $row['season']] 		= $row['hits'];
		}

		// Wins  
		$sql = 'SELECT
					r.season as season,
					r.user_id,
					sum(r.win_total) As win_total
				FROM ' . FOOTB_RANKS . " AS r
				WHERE r.season <= $season
					AND r.league = $league 
					AND r.matchday = $matchday
					AND r.status IN (2,3) 
				GROUP BY r.season, r.user_id
				ORDER BY r.user_id ASC, r.season ASC";
				
		$result = $db->sql_query($sql);

		$win_arr = array();
		while($row = $db->sql_fetchrow($result))
		{
			$win_arr[$row['user_id'] . '#' . $row['season']] = $row['win_total'];
		}
		$db->sql_freeresult($result);

		$data_ranks = false;
		$pagination = '';

		$sql = 'SELECT
					r.season,
					r.user_id,
					u.username,
					r.rank_total,
					r.status,
					r.points_total
				FROM ' . FOOTB_RANKS . ' AS r
				LEFT JOIN ' . USERS_TABLE . " AS u ON (r.user_id = u.user_id)
				WHERE r.season <= $season
					AND r.league = $league 
					AND r.matchday = $matchday
					AND r.status IN (2,3) 
				ORDER BY r.points_total DESC, LOWER(u.username) ASC";
				
		$result = $db->sql_query($sql);

		$ranking_arr = $db->sql_fetchrowset($result);
		$db->sql_freeresult($result);


		// Make sure $start is set to the last page if it exceeds the amount
		if ($start < 0 || $start >= $total_users)
		{
			$index_start = ($start < 0) ? 0 : floor(($total_users - 1) / $config['football_users_per_page']) * $config['football_users_per_page'];
		}
		else
		{
			$index_start = floor($start / $config['football_users_per_page']) * $config['football_users_per_page'];
		}
		$index_end = $index_start + $config['football_users_per_page'] - 1;

		// handle pagination.
		$base_url = $this->helper->route('football_football_controller', array('side' => 'ranks_total', 's' => $season, 'l' => $league, 'm' => $matchday, 'mode' => 'compare'));
		$pagination = $phpbb_container->get('pagination');
		$pagination->generate_template_pagination($base_url, 'pagination', 'start', $total_users, $this->config['football_users_per_page'], $start);

		$index = 0;
		$rank = 0;
		$last_points = 0;
		$data_rank_total = false;
		foreach ($ranking_arr AS $curr_rank)
		{
			if ($curr_rank['points_total'] <> $last_points)
			{
				$rank = $index + 1;
				$last_points = $curr_rank['points_total'];
			}
			$data_ranks = true;
			if (($index_start <= $index) && ($index <= $index_end))
			{
				$row_class = (!($index % 2)) ? 'bg1 row_light' : 'bg2 row_dark';
				if ($curr_rank['user_id'] == $user->data['user_id'])
				{
					$row_class = 'bg3  row_user';
				}
				$colorstyle = color_style($curr_rank['status']);

				$template->assign_block_vars('rankstotal', array(
					'ROW_CLASS' 	=> $row_class,
					'RANK' 			=> $rank,
					'USERID' 		=> $curr_rank['user_id'],
					'USERNAME' 		=> $curr_rank['username'],
					'SEASON' 		=> $curr_rank['season'],
					'SEASON_RANK' 	=> $curr_rank['rank_total'],
					'URL' 			=> $phpbb_root_path . 'profile.php?mode=viewprofile&u=' . $curr_rank['user_id'],
					'BETS' 			=> $bets_of[$curr_rank['user_id'] . '#' . $curr_rank['season']],
					'NOBETS' 		=> ($nobets_of[$curr_rank['user_id'] . '#' . $curr_rank['season']] == 0) ? '&nbsp;' : $nobets_of[$curr_rank['user_id'] . '#' . $curr_rank['season']],
					'TENDENCIES' 	=> ($tendency_of[$curr_rank['user_id'] . '#' . $curr_rank['season']] == 0) ? '&nbsp;' : $tendency_of[$curr_rank['user_id'] . '#' . $curr_rank['season']],
					'DIRECTHITS' 	=> ($hits_of[$curr_rank['user_id'] . '#' . $curr_rank['season']] == 0) ? '&nbsp;' : $hits_of[$curr_rank['user_id'] . '#' . $curr_rank['season']],
					'POINTS' 		=> $curr_rank['points_total'],
					'COLOR_STYLE'	=> $colorstyle,
					'WIN' 			=> $win_arr[$curr_rank['user_id'] . '#' . $curr_rank['season']],
					)
				);
			}
			$index++;
		}

		$sidename = sprintf($user->lang['RANK_TOTAL']);
		$league_info = league_info($season, $league);
		$template->assign_vars(array(
			'S_DISPLAY_RANKS_TOTAL'		=> true,
			'S_DISPLAY_HITS02'			=> $config['football_win_hits02'],
			'S_DATA_RANKS' 				=> $data_ranks,
			'S_SIDENAME' 				=> $sidename,
			'PAGE_NUMBER' 				=> $pagination->on_page($total_users, $this->config['football_users_per_page'], $start),
			'TOTAL_USERS'				=> ($total_users == 1) ? $user->lang['VIEW_BET_USER'] : sprintf($user->lang['VIEW_BET_USERS'], $total_users),
			'S_WIN' 					=> ($league_info['win_matchday'] == '0' and $league_info['win_season'] == '0') ? false : ($this->auth->acl_gets('a_')) ? true : false,
			'WIN_NAME' 					=> $config['football_win_name'],
			'S_SHOW_OTHER_LINKS'		=> true,
			'S_HEADER'					=> sprintf($user->lang['RANKING_COMPARE']),
			'S_LINK_RANKING'			=> $this->helper->route('football_football_controller', array('side' => 'ranks_total', 's' => $season, 'l' => $league, 'm' => $matchday)),
			'S_LINK_ALL_TIME'			=> $this->helper->route('football_football_controller', array('side' => 'ranks_total', 's' => $season, 'l' => $league, 'm' => $matchday, 'mode' => 'alltime')),
			'S_LINK_COMPARE'			=> '',
			)
		);
	break;

	default:

		$win_user_most_hits = array();
		$win_user_most_hits_away = array();
		$win_user_most_hits = win_user_most_hits($season, $league, $matchday);
		$win_user_most_hits_away = win_user_most_hits_away($season, $league, $matchday);

		// Statistics  
		$sql = "SELECT
					b.user_id,
					COUNT(b.match_no) AS matches,
					SUM(IF(b.goals_home <> '' AND b.goals_guest <> '', 1, 0)) AS bets,
					SUM(IF(b.goals_home <> '' AND b.goals_guest <> '',
							IF((b.goals_home + 0 < b.goals_guest) <> (m.goals_home + 0 < m.goals_guest) 
								OR (b.goals_home = b.goals_guest) <> (m.goals_home = m.goals_guest) 
								OR (b.goals_home + 0 > b.goals_guest) <> (m.goals_home + 0 > m.goals_guest),
								0,
								IF((b.goals_home = m.goals_home) AND (b.goals_guest = m.goals_guest), 0, 1)
							),
							0
						)
					) AS tendency
				FROM " . FOOTB_BETS . ' AS b
				LEFT JOIN ' . FOOTB_MATCHES . " AS m ON (m.season = b.season AND m.league = b.league AND m.match_no = b.match_no)
				WHERE b.season = $season 
					AND b.league = $league 
					AND m.status IN (2,3) 
					AND m.matchday <= $matchday
				GROUP BY b.user_id";

		$result = $db->sql_query($sql);
		$rows = $db->sql_fetchrowset($result);
		$total_users = sizeof($rows);
		$db->sql_freeresult($result);

		foreach ($rows AS $row)
		{
			$bets_of[$row['user_id']] 		= $row['bets'];
			$nobets_of[$row['user_id']] 	= $row['matches'] - $row['bets'];
			$tendency_of[$row['user_id']] 	= $row['tendency'];
		}

		$data_ranks = false;
		$pagination = '';

		$prev_rank_of = array();
		if ($matchday > 1)
		{
			// previous rank total 
			$sql = 'SELECT
					rank_total,
					user_id
				FROM ' . FOOTB_RANKS . "
				WHERE season = $season 
					AND league = $league 
					AND matchday = ($matchday-1) 
					AND status IN (2,3)
				ORDER BY rank_total ASC, user_id ASC";
			$result = $db->sql_query($sql);
			$rows = $db->sql_fetchrowset($result);
			$db->sql_freeresult($result);

			foreach ($rows AS $row)
			{
				$prev_rank_of[$row['user_id']] = $row['rank_total'];
			}
		}

		$sql = 'SELECT
					r.rank_total,
					r.user_id,
					u.username,
					u.user_colour,
					r.status,
					r.points_total,
					r.win_total
				FROM ' . FOOTB_RANKS . ' AS r
				LEFT JOIN ' . USERS_TABLE . " AS u ON (r.user_id = u.user_id)
				WHERE r.season = $season 
					AND r.league = $league 
					AND r.matchday = $matchday 
					AND r.status IN (2,3)
				GROUP BY r.user_id
				ORDER BY r.points_total DESC, LOWER(u.username) ASC";
				
		$result = $db->sql_query($sql);

		$ranking_arr = array();
		while($row = $db->sql_fetchrow($result))
		{
			$ranking_arr[$row['user_id']] = $row;
		}
		$db->sql_freeresult($result);

		// Make sure $start is set to the last page if it exceeds the amount
		if ($start < 0 || $start >= $total_users)
		{
			$index_start = ($start < 0) ? 0 : floor(($total_users - 1) / $config['football_users_per_page']) * $config['football_users_per_page'];
		}
		else
		{
			$index_start = floor($start / $config['football_users_per_page']) * $config['football_users_per_page'];
		}
		$index_end = $index_start + $config['football_users_per_page'] - 1;

		// handle pagination.
		$base_url = $this->helper->route('football_football_controller', array('side' => 'ranks_total', 's' => $season, 'l' => $league, 'm' => $matchday));
		$pagination = $phpbb_container->get('pagination');
		if ($user->data['football_mobile'])
		{
			$index_start = 0;
			$index_end = 9999;
			$pagination->generate_template_pagination($base_url, 'pagination', 'start', $total_users, $index_end, $start);
		}
		else
		{
			$pagination->generate_template_pagination($base_url, 'pagination', 'start', $total_users, $this->config['football_users_per_page'], $start);
		}

		$index = 0;
		$data_rank_total = false;
		$ext_path = $this->phpbb_path_helper->update_web_root_path($this->phpbb_extension_manager->get_extension_path('football/football', true));
		foreach ($ranking_arr AS $curr_rank)
		{
			$data_ranks = true;
			$rank = $curr_rank['rank_total'];
			
			if (($index_start <= $index) && ($index <= $index_end))
			{
				// Display page
				if (isset($prev_rank_of[$curr_rank['user_id']]))
				{
					if ($rank == $prev_rank_of[$curr_rank['user_id']])
					{
						$change_sign 	= '=';
						$change_differ 	= '';
					}
					else
					{
						if ($rank > $prev_rank_of[$curr_rank['user_id']])
						{
							$change_sign 	= '+';
							$differ 		= $rank - $prev_rank_of[$curr_rank['user_id']];
							$change_differ 	= ' (' . $differ . ')';
						}
						else
						{
							$change_sign 	= '-';
							$differ 		= $prev_rank_of[$curr_rank['user_id']] - $rank;
							$change_differ 	= ' (' . $differ . ')';
						}
					}
				}
				else
				{
					$change_sign 	= '';
					$change_differ 	= '';
				}

				$win_total = sprintf('%01.2f',$curr_rank['win_total']);
				if(!isset($win_user_most_hits[$curr_rank['user_id']]['direct_hit']))
				{
					$win_user_most_hits[$curr_rank['user_id']]['direct_hit'] = 0;
				}
				if(!isset($win_user_most_hits_away[$curr_rank['user_id']]['direct_hit']))
				{
					$win_user_most_hits_away[$curr_rank['user_id']]['direct_hit'] = 0;
				}
				$row_class = (!($index % 2)) ? 'bg1 row_light' : 'bg2 row_dark';
				if ($curr_rank['user_id'] == $user->data['user_id'])
				{
					$row_class = 'bg3  row_user';
				}
				$colorstyle = color_style($curr_rank['status']);

				$template->assign_block_vars('rankstotal', array(
					'ROW_CLASS' 	=> $row_class,
					'RANK' 			=> $rank,
					'NO_CHANGES'	=> ($change_sign == '=') ? true : false,
					'WORSENED'		=> ($change_sign == '+') ? true : false,
					'IMPROVED'		=> ($change_sign == '-') ? true : false,
					'CHANGE_SIGN' 	=> $change_sign,
					'CHANGE_DIFFER'	=> $change_differ,
					'USERID' 		=> $curr_rank['user_id'],
					'USERNAME' 		=> $curr_rank['username'],
					'U_PROFILE'		=> get_username_string('profile', $curr_rank['user_id'], $curr_rank['username'], $curr_rank['user_colour']),
					'BETS' 			=> $bets_of[$curr_rank['user_id']],
					'NOBETS' 		=> ($nobets_of[$curr_rank['user_id']] == 0) ? '&nbsp;' : $nobets_of[$curr_rank['user_id']],
					'TENDENCIES'	=> ($tendency_of[$curr_rank['user_id']] == 0) ? '&nbsp;' : $tendency_of[$curr_rank['user_id']],
					'DIRECTHITS' 	=> ($win_user_most_hits[$curr_rank['user_id']]['direct_hit'] == 0) ? '&nbsp;' : $win_user_most_hits[$curr_rank['user_id']]['direct_hit'],
					'DIRECTHITS02' 	=> ($win_user_most_hits_away[$curr_rank['user_id']]['direct_hit'] == 0) ? '&nbsp;' : $win_user_most_hits_away[$curr_rank['user_id']]['direct_hit'],
					'POINTS' 		=> $curr_rank['points_total'],
					'COLOR_STYLE'	=> $colorstyle,
					'WIN' 			=> $win_total,
					)
				);
			}
			$index++;
		}

		$sidename = sprintf($user->lang['RANK_TOTAL']);
		$league_info = league_info($season, $league);
		$template->assign_vars(array(
			'S_DISPLAY_RANKS_TOTAL'		=> true,
			'S_DISPLAY_HITS02'			=> $config['football_win_hits02'],
			'S_DATA_RANKS' 				=> $data_ranks,
			'S_SIDENAME' 				=> $sidename,
			'PAGE_NUMBER' 				=> $pagination->on_page($total_users, $this->config['football_users_per_page'], $start),
			'TOTAL_USERS'				=> ($total_users == 1) ? $user->lang['VIEW_BET_USER'] : sprintf($user->lang['VIEW_BET_USERS'], $total_users),
			'S_WIN' 					=> ($league_info['win_matchday'] == '0' and $league_info['win_season'] == '0') ? false : true,
			'WIN_NAME' 					=> $config['football_win_name'],
			'S_SHOW_OTHER_LINKS'		=> true,
			'S_LINK_RANKING'			=> '',
			'S_LINK_ALL_TIME'			=> $this->helper->route('football_football_controller', array('side' => 'ranks_total', 's' => $season, 'l' => $league, 'm' => $matchday, 'mode' => 'alltime')),
			'S_LINK_COMPARE'			=> $this->helper->route('football_football_controller', array('side' => 'ranks_total', 's' => $season, 'l' => $league, 'm' => $matchday, 'mode' => 'compare')),
			)
		);
	break;
}
