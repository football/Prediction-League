<?php
/**
*
* @package phpBB Extension - Football Football
* @copyright (c) 2016 football (http://football.bplaced.net)
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

$vert = 9;
$start = 22;
$end = 28;

// Can this user view Prediction Leagues pages?
if (!$config['football_guest_view'])
{
	if ($user->data['user_id'] == ANONYMOUS)
	{
		trigger_error('NO_GUEST_VIEW');
	}
}
if (!$config['football_user_view'])
{
	// Only Prediction League member should see this page
	// Check Prediction League authorisation 
	if ( !$this->auth->acl_get('u_use_football') )
	{
		trigger_error('NO_AUTH_VIEW');
	}
}

//Football disabled?
if ($config['football_disable'])
{
	$message = (!empty($config['football_disable_msg'])) ? $config['football_disable_msg'] : 'FOOTBALL_DISABLED';
	trigger_error($message);
}

$home_id		= $this->request->variable('hid', 0);
$guest_id		= $this->request->variable('gid', 0);
$season			= $this->request->variable('s', 0);
$league			= $this->request->variable('l', 0);
$matchday		= $this->request->variable('m', 0);
$matchnumber	= $this->request->variable('mn', 0);
$group_id		= $this->request->variable('gr', '');


// Check parms
$error_message = '';
$data_history = false;
if (!$home_id OR !$guest_id OR !$season OR !$league OR !$matchday OR !$matchnumber)
{
	$data_history = false;
	if (!$home_id)
	{	
		$error_message .= sprintf($user->lang['NO_HOMETEAM']) . '<br />';
	}
	if (!$guest_id)
	{	
		$error_message .= sprintf($user->lang['NO_GUESTTEAM']) . '<br />';
	}
	if (!$season)
	{	
		$error_message .= sprintf($user->lang['NO_SEASON']) . '<br />';
	}
	if (!$league)
	{	
		$error_message .= sprintf($user->lang['NO_LEAGUE']) . '<br />';
	}
	if (!$matchday)
	{	
		$error_message .= sprintf($user->lang['NO_MATCHDAY']) . '<br />';
	}
	if (!$matchnumber)
	{	
		$error_message .= sprintf($user->lang['NO_MATCH']) . '<br />';
	}
}
else
{
	$season_info = season_info($season);
	if (sizeof($season_info))
	{
		$league_info = league_info($season, $league);
		if (sizeof($league_info))
		{
			$data_history = true;
			$data_hist = false;
			$data_home =false;
			$data_guest = false;
			$data_last_home = false;
			$data_last_away = false;
			$form_from = $matchday-5;
			$percent_home = 0;
			$percent_draw = 0;
			$percent_guest = 0;
			$stat_hist = '';
			$value_h = 0;
			$value_g = 0;
			$value_hg = 0;
			$value_gg = 0;
			$ext_path = $this->phpbb_path_helper->update_web_root_path($this->phpbb_extension_manager->get_extension_path('football/football', true));
			
			$sql = 'SELECT * 
				FROM ' . FOOTB_TEAMS . " 
				WHERE season = $season
					AND league = $league
					AND team_id = $home_id";
					
			$result = $db->sql_query($sql);
			if ($row = $db->sql_fetchrow($result))
			{
				$home_name = $row['team_name'];
				if ($row['team_symbol'] <> '')
				{
					$logo[$home_id] = "<img src=\"" . $ext_path . 'images/flags/' . $row['team_symbol'] . "\" alt=\"" . $row['team_symbol'] . "\"/>" ;
				}
				else
				{
					$logo[$home_id] = "<img src=\"" . $ext_path . "images/flags/blank.gif\" alt=\"\" width=\"28\" height=\"28\"/>" ;
				}
			}
			else
			{
				$error_message .= sprintf($user->lang['NO_HOMETEAM']) . '<br />';
				$data_history = false;
			}
			$db->sql_freeresult($result);

			$sql = 'SELECT * 
				FROM ' . FOOTB_TEAMS . " 
				WHERE season = $season
					AND league = $league
					AND team_id = $guest_id";
					
			$result = $db->sql_query($sql);
			if ($row = $db->sql_fetchrow($result))
			{
				$guest_name = $row['team_name'];
				if ($row['team_symbol'] <> '')
				{
					$logo[$guest_id] = "<img src=\"" . $ext_path . 'images/flags/' . $row['team_symbol'] . "\" alt=\"" . $row['team_symbol'] . "\"/>" ;
				}
				else
				{
					$logo[$guest_id] = "<img src=\"" . $ext_path . "images/flags/blank.gif\" alt=\"\" width=\"28\" height=\"28\"/>" ;
				}
			}
			else
			{
				$error_message .= sprintf($user->lang['NO_GUESTTEAM']) . '<br />';
				$data_history = false;
			}
			$db->sql_freeresult($result);


			// Match history
			$sql = "SELECT
					IF(mh.team_id_home = $home_id, 
						IF(mh.goals_home + 0 > mh.goals_guest, '+0', IF(mh.goals_home = mh.goals_guest, '30', '-0')), 
						IF(mh.goals_home + 0 < mh.goals_guest, '+90', IF(mh.goals_home = mh.goals_guest, '60', '-90'))) AS chart_points,
					mh.match_date AS season,
					mh.match_type AS league,
					mh.team_id_home AS home_id,
					mh.team_id_guest AS guest_id,
					th.team_name AS home_name,
					tg.team_name AS guest_name,
					mh.goals_home,
					mh.goals_guest
				FROM  " . FOOTB_MATCHES_HIST . ' AS mh
				LEFT JOIN ' . FOOTB_TEAMS . " AS th ON (th.season = $season AND th.league = $league AND th.team_id = mh.team_id_home)
				LEFT JOIN " . FOOTB_TEAMS . " AS tg ON (tg.season = $season AND tg.league = $league AND tg.team_id = mh.team_id_guest)
				WHERE  ((mh.team_id_home = $home_id AND mh.team_id_guest = $guest_id) 
					OR (mh.team_id_home = $guest_id AND mh.team_id_guest = $home_id))
				ORDER BY mh.match_date ASC";
				
			$result = $db->sql_query($sql);
			$history = $db->sql_fetchrowset($result);
			$db->sql_freeresult($result);
			$history_count = sizeof($history);
			// Match history
			$sql = "SELECT
					IF(m.team_id_home = $home_id, 
						IF(m.goals_home + 0 > m.goals_guest, '+0', IF(m.goals_home = m.goals_guest, '30', '-0')), 
						IF(m.goals_home + 0 < m.goals_guest, '+90', IF(m.goals_home = m.goals_guest, '60', '-90'))) AS chart_points,
					m.season,
					m.league,
					m.team_id_home AS home_id,
					m.team_id_guest AS guest_id,
					th.team_name AS home_name,
					tg.team_name AS guest_name,
					m.goals_home,
					m.goals_guest
				FROM  " . FOOTB_MATCHES . ' AS m
				LEFT JOIN ' . FOOTB_TEAMS . " AS th ON (th.season = $season AND th.league = $league AND th.team_id = m.team_id_home)
				LEFT JOIN " . FOOTB_TEAMS . " AS tg ON (tg.season = $season AND tg.league = $league AND tg.team_id = m.team_id_guest)
				WHERE ((m.team_id_home = $home_id AND m.team_id_guest = $guest_id) OR (m.team_id_home = $guest_id AND m.team_id_guest = $home_id)) 
					AND m.status IN (3,6) 
					AND (m.season <> $season OR m.league <> $league OR m.match_no < $matchnumber)
				ORDER BY m.season ASC, m.matchday ASC";
				
			$result = $db->sql_query($sql);
			if ($history_count != 0)
			{
				$history = array_merge($history, $db->sql_fetchrowset($result));
			}
			else
			{
				$history = $db->sql_fetchrowset($result);
			}
			$db->sql_freeresult($result);
			$history_count = sizeof($history);
			if ($history_count != 0)
			{
				$chart_points = '';
				$data_hist = true;
				$rank = 0;
				$map_total_coords = array();
				$map_total_titles = array();
				foreach ($history as $row)
				{
					$chart_points = $chart_points . $row['chart_points'] . ',';
					$coord_start = $start + ($rank * $vert);
					$coord_end = $end + ($rank * $vert);
					if (abs($row['chart_points']) > 45)
					{
						$coords =  $coord_start . ",45," . $coord_end . "," . abs($row['chart_points']);
					}
					else
					{
						$coords =  $coord_start  . "," . abs($row['chart_points']) . ","  . $coord_end . ",45";
					}
					$map_total_coords[] = $coords;
					$map_total_titles[] = $row['home_name'] . ' - ' . $row['guest_name'] . ' ' . $row['goals_home'] . ':' . $row['goals_guest'];
					$rank++ ;
					$row_class = (!($rank % 2)) ? 'bg1 row_light' : 'bg2 row_dark';
					$template->assign_block_vars('historie', array(
						'ROW_CLASS' 	=> $row_class,
						'SEASON' 		=> $row['season'],
						'LEAGUE' 		=> $row['league'],
						'HLOGO' 		=> $logo[$row['home_id']],
						'HNAME' 		=> $row['home_name'],
						'GLOGO' 		=> $logo[$row['guest_id']],
						'GNAME' 		=> $row['guest_name'],
						'GOALS_HOME' 	=> $row['goals_home'],
						'GOALS_GUEST' 	=> $row['goals_guest'],
						)
					);
				}

				// Statistic and forecast-points for historie 
				$sql = "SELECT
						SUM(IF(team_id_home = $home_id, goals_home , goals_guest)) AS hg,
						SUM(IF(team_id_home = $guest_id, goals_home , goals_guest)) AS gg,
						SUM(IF(team_id_home = $home_id, IF(goals_home + 0 > goals_guest AND goals_home <> '' AND goals_guest <> '', 1 ,0), 0)) AS hw,
						SUM(IF(team_id_home = $home_id, IF(goals_home = goals_guest AND goals_home <> '' AND goals_guest <> '', 1 ,0), 0)) AS hd,
						SUM(IF(team_id_home = $home_id, IF(goals_home + 0 < goals_guest AND goals_home <> '' AND goals_guest <> '', 1 ,0), 0)) AS hl,
						SUM(IF(team_id_home = $home_id, IF(goals_home + 0 > goals_guest, 1 ,0), IF(goals_home + 0 < goals_guest,1,0))) AS gw,
						SUM(IF(goals_home = goals_guest AND goals_home <> '' AND goals_guest <> '',1,0)) AS gd,
						SUM(IF(team_id_home = $home_id, IF(goals_home + 0 < goals_guest, 1 ,0), IF(goals_home + 0 > goals_guest,1,0))) AS gl,
						SUM(IF(team_id_home = $home_id,
								IF(goals_home + 0 > goals_guest, 3, IF(goals_home = goals_guest, 1, 0)),
								IF(goals_home + 0 < goals_guest, 4, IF(goals_home = goals_guest, 2, 0))
							)
						) AS value_h,
						SUM(IF(team_id_home = $guest_id,
								IF(goals_home + 0 > goals_guest, 3, IF(goals_home = goals_guest, 1, 0)),
								IF(goals_home + 0 < goals_guest, 4, IF(goals_home = goals_guest, 2, 0))
							)
						) AS value_g
					FROM  " . FOOTB_MATCHES_HIST . "
					WHERE  ((team_id_home = $home_id AND team_id_guest = $guest_id) 
						OR (team_id_home = $guest_id AND team_id_guest = $home_id))";
						
				$result = $db->sql_query($sql);
				$row_hist = $db->sql_fetchrow($result);
				$db->sql_freeresult($result);

				$sql = "SELECT
						SUM(IF(team_id_home = $home_id, goals_home , goals_guest)) AS hg,
						SUM(IF(team_id_home = $guest_id, goals_home , goals_guest)) AS gg,
						SUM(IF(team_id_home = $home_id, IF(goals_home + 0 > goals_guest, 1 ,0), 0)) AS hw,
						SUM(IF(team_id_home = $home_id, IF(goals_home = goals_guest AND goals_home <> '' AND goals_guest <> '', 1 ,0), 0)) AS hd,
						SUM(IF(team_id_home = $home_id, IF(goals_home + 0 < goals_guest, 1 ,0), 0)) AS hl,
						SUM(IF(team_id_home = $home_id, IF(goals_home + 0 > goals_guest, 1 ,0), IF(goals_home + 0 < goals_guest, 1, 0))) AS gw,
						SUM(IF(goals_home = goals_guest AND goals_home <> '' AND goals_guest <> '', 1, 0)) AS gd,
						SUM(IF(team_id_home = $home_id, IF(goals_home + 0 < goals_guest, 1 ,0), IF(goals_home + 0 > goals_guest, 1, 0))) AS gl,
						SUM(IF(team_id_home = $home_id,
								IF(goals_home + 0 > goals_guest, 3, IF(goals_home = goals_guest AND goals_home <> '' AND goals_guest <> '', 1, 0)),
								IF(goals_home + 0 < goals_guest, 4, IF(goals_home = goals_guest AND goals_home <> '' AND goals_guest <> '', 2, 0))
							)
						) AS value_h,
						SUM(IF(team_id_home = $guest_id,
								IF(goals_home + 0 > goals_guest, 3, IF(goals_home = goals_guest AND goals_home <> '' AND goals_guest <> '',1,0)),
								IF(goals_home + 0 < goals_guest,4, IF(goals_home = goals_guest AND goals_home <> '' AND goals_guest <> '',2,0))
							)
						) AS value_g
					FROM  " . FOOTB_MATCHES . " 
					WHERE  ((team_id_home = $home_id AND team_id_guest = $guest_id) OR (team_id_home = $guest_id AND team_id_guest = $home_id)) 
						AND	status IN (3,6) 
						AND (season <> $season OR league <> $league OR  match_no < $matchnumber)";
						
				$result = $db->sql_query($sql);
				$row = $db->sql_fetchrow($result);
				if (sizeof($row_hist))
				{
					if (sizeof($row))
					{
						$row['hg'] += $row_hist['hg'];
						$row['gg'] += $row_hist['gg'];
						$row['hw'] += $row_hist['hw'];
						$row['hd'] += $row_hist['hd'];
						$row['hl'] += $row_hist['hl'];
						$row['gw'] += $row_hist['gw'];
						$row['gd'] += $row_hist['gd'];
						$row['gl'] += $row_hist['gl'];
						$row['value_h'] += $row_hist['value_h'];
						$row['value_g'] += $row_hist['value_g'];
					}
					else
					{
						$row = $row_hist;
					}
				}
				
				if (sizeof($row))
				{
					if ($history_count <= 2)
					{
						// $history_count = 1 => draws * 1/4
						// $history_count = 2 => draws * 1/6
						$percent_draw = round($row['gd'] / ((2 * $history_count) + 2),2);
					}
					else
					{
						// $history_count > 2 => draws * 1/2
						$percent_draw = round($row['gd'] / ($history_count * 2),2);
					}
					$value_h = round($row['value_h'] / ($history_count * 2),2);
					$value_g = round($row['value_g'] / ($history_count * 2),2);

					$stat_hist = sprintf($user->lang['THIS_MATCH']) . ': ' . $row['hw'] . '/' . $row['hd'] . '/'. $row['hl'] . '&nbsp;&nbsp;&nbsp;' .
						sprintf($user->lang['TOTAL']) . ': ' . $row['gw'] . '/'. $row['gd'] . '/' . $row['gl'] . '&nbsp;&nbsp;&nbsp;' .
						sprintf($user->lang['GOALS']) . ': ' .	$row['hg'] . ':'. $row['gg'] . '&nbsp;&nbsp;&nbsp;' . 
						sprintf($user->lang['FORECAST_PTS']) . ': ' . $value_h . ':' . $value_g;

					$template->assign_vars(array(
						'STAT_HIST' => $stat_hist,
						)
					);
				}
				$db->sql_freeresult($result);

				//Charts history
				$chart_points = substr($chart_points, 0, strlen($chart_points) - 1);
				$chart= "<img src='" . generate_board_url() . '/' . $this->football_root_path
									. "includes/chart_hist.php?v1=$chart_points' alt='" . sprintf($user->lang['TOTAL']) . "' usemap='#map_total'/>";	
				$template->assign_block_vars('chart_hist_total', array(
					'CHARTIMAGE' => $chart,
					)
				);
				for ($i = 0; $i < sizeof($map_total_coords); $i++) 
				{
					$template->assign_block_vars('chart_hist_total.map_total', array(
						'COORDS' 	=> $map_total_coords[$i],
						'TITLE' 	=> $map_total_titles[$i],
						)
					);
				}
			}


			// Table total 
			
			if ($group_id == '' OR $group_id == '??')
			{
				$where_group = '';
			}
			else
			{
				$where_group = " AND m.group_id = '" . $db->sql_escape($group_id) . "'";
			}
			$sql = "SELECT
					t.*,
					SUM(1) AS matches,
					SUM(IF((m.team_id_home = t.team_id), IF(goals_home + 0 > goals_guest, 1, 0), IF(goals_home + 0 < goals_guest, 1, 0))) AS wins,
					SUM(IF(goals_home = goals_guest AND goals_home <> '' AND goals_guest <> '', 1, 0)) AS draws,
					SUM(IF((m.team_id_home = t.team_id), IF(goals_home + 0 < goals_guest, 1, 0), IF(goals_home + 0 > goals_guest, 1, 0))) AS lost,
					SUM(IF(m.team_id_home = t.team_id, 
							IF(goals_home + 0 > goals_guest, 3, IF(goals_home = goals_guest AND goals_home <> '' AND goals_guest <> '', 1, 0)), 
							IF(goals_home + 0 < goals_guest, 3, IF(goals_home = goals_guest, 1, 0))
						)
					) AS points,
					SUM(IF(m.team_id_home = t.team_id, goals_home - goals_guest, goals_guest - goals_home)) AS gdiff,
					SUM(IF(m.team_id_home = t.team_id, goals_home, goals_guest)) AS goals,
					SUM(IF(m.team_id_home = t.team_id, goals_guest, goals_home)) AS goals_against
				FROM " . FOOTB_TEAMS . " AS t
				LEFT JOIN " . FOOTB_MATCHES . " AS m ON (m.season = t.season AND m.league = t.league AND (m.team_id_home = t.team_id OR m.team_id_guest = t.team_id))
				WHERE t.season = $season 
					AND t.league = $league 
					AND (m.matchday < $matchday) 
					AND (m.status IN (3,6)) $where_group 
				GROUP BY t.team_id
				ORDER BY points DESC, gdiff DESC, goals DESC";
				
			$result = $db->sql_query($sql);
			$rank = 0;
			$current_rank = 0;
			$last_goals = 0;
			$last_goals_againts = 0;
			$last_points = 0;
			while ($row = $db->sql_fetchrow($result))
			{
				$rank++;
				if ($last_points <> $row['points'] OR $last_goals <> $row['goals'] OR $last_goals_againts <> $row['goals_against'])
				{
					$current_rank = $rank;
				}
				$last_points = $row['points'];
				$last_goals = $row['goals'];
				$last_goals_againts = $row['goals_against'];
				if ($home_id == $row['team_id'])
				{
					$data_home = true;
					$value_h += round($row['points'] / ($row['matches'] * 3), 2);
					$plfp = $current_rank . '(' . round($row['points'] / ($row['matches'] * 3), 2) . ')';
					$template->assign_block_vars('table_hometeam', array(
						'ROW_CLASS' 	=> 'bg2 row_dark',
						'TABLE' 		=> sprintf($user->lang['TABLE_TOTAL']),
						'PLFP' 			=> $plfp,
						'MATCHES' 		=> $row['matches'],
						'WINS' 			=> $row['wins'],
						'DRAW' 			=> $row['draws'],
						'LOST' 			=> $row['lost'],
						'GOALS_HOME' 	=> $row['goals'],
						'GOALS_GUEST' 	=> $row['goals_against'],
						'GDIFF' 		=> $row['gdiff'],
						'POINTS' 		=> $row['points'],
						)
					);
				}
				if ($guest_id == $row['team_id'])
				{
					$data_guest = true;
					$value_g += round($row['points'] / ($row['matches'] * 3),2);
					$plfp = $current_rank . '(' . round($row['points'] / ($row['matches'] * 3), 2) . ')';
					$template->assign_block_vars('table_guestteam', array(
						'ROW_CLASS' 	=> 'bg2 row_dark',
						'TABLE' 		=> sprintf($user->lang['TABLE_TOTAL']),
						'PLFP' 			=> $plfp,
						'MATCHES' 		=> $row['matches'],
						'WINS' 			=> $row['wins'],
						'DRAW' 			=> $row['draws'],
						'LOST' 			=> $row['lost'],
						'GOALS_HOME' 	=> $row['goals'],
						'GOALS_GUEST' 	=> $row['goals_against'],
						'GDIFF' 		=> $row['gdiff'],
						'POINTS' 		=> $row['points'],
						)
					);
				}
			}
			$db->sql_freeresult($result);

			// Hometable hometeam
			if ($data_home)
			{
				$sql = "SELECT
						t.*,
						SUM(1) AS matches,
						SUM(IF((m.team_id_home = t.team_id), IF(goals_home + 0 > goals_guest, 1, 0), IF(goals_home + 0 < goals_guest, 1, 0))) AS wins,
						SUM(IF(goals_home = goals_guest, 1, 0)) AS draws,
						SUM(IF((m.team_id_home = t.team_id), IF(goals_home + 0 < goals_guest, 1, 0), IF(goals_home + 0 > goals_guest, 1, 0))) AS lost,
						SUM(IF(m.team_id_home = t.team_id, 
								IF(goals_home + 0 > goals_guest, 3, IF(goals_home = goals_guest AND goals_home <> '' AND goals_guest <> '', 1, 0)), 
								IF(goals_home + 0 < goals_guest, 3, IF(goals_home = goals_guest, 1, 0))
							)
						) AS points,
						SUM(IF(m.team_id_home = t.team_id, goals_home - goals_guest , goals_guest - goals_home)) AS gdiff,
						SUM(IF(m.team_id_home = t.team_id, goals_home , goals_guest)) AS goals,
						SUM(IF(m.team_id_home = t.team_id, goals_guest , goals_home)) AS goals_against
					FROM " . FOOTB_TEAMS . ' AS t
					LEFT JOIN ' . FOOTB_MATCHES . " AS m ON (m.season = t.season AND m.league = t.league AND m.team_id_home = t.team_id)
					WHERE t.season = $season 
						AND t.league = $league 
						AND m.matchday < $matchday 
						AND m.status IN (3,6)
					GROUP BY t.team_id
					ORDER BY points DESC, gdiff DESC, goals DESC";

				$result = $db->sql_query($sql);
				$rank = 0;
				$current_rank = 0;
				$last_goals = 0;
				$last_goals_againts = 0;
				$last_points = 0;
				while ($row = $db->sql_fetchrow($result))
				{
					$data_home = true;
					$rank++;
					if ($last_points <> $row['points'] OR $last_goals <> $row['goals'] OR $last_goals_againts <> $row['goals_against'])
					{
						$current_rank = $rank;
					}
					$last_points = $row['points'];
					$last_goals = $row['goals'];
					$last_goals_againts = $row['goals_against'];
					if ($home_id == $row['team_id'])
					{
						$win = $row['wins'];
						$draw = $row['draws'];
						$lost = $row['lost'];
						$value_hg = round(((($win * 3) + $draw) / ($win + $draw + $lost)),2);
						$value_h += $value_hg;
						$plfp = $current_rank . '(' . $value_hg . ')';
						$template->assign_block_vars('table_hometeam', array(
							'ROW_CLASS' 	=> 'bg1 row_light',
							'TABLE' 		=> sprintf($user->lang['TABLE_HOME']),
							'PLFP' 			=> $plfp,
							'MATCHES' 		=> $row['matches'],
							'WINS' 			=> $row['wins'],
							'DRAW' 			=> $row['draws'],
							'LOST' 			=> $row['lost'],
							'GOALS_HOME' 	=> $row['goals'],
							'GOALS_GUEST' 	=> $row['goals_against'],
							'GDIFF' 		=> $row['gdiff'],
							'POINTS' 		=> $row['points'],
							)
						);
					}
				}
				$db->sql_freeresult($result);
			}
			
			//Away-Table guestteam
			if ($data_guest)
			{
				$sql = "SELECT
						t.*,
						SUM(1) AS matches,
						SUM(IF((m.team_id_home = t.team_id), IF(m.goals_home + 0 > m.goals_guest, 1, 0), IF(m.goals_home + 0 < m.goals_guest, 1, 0))) AS wins,
						SUM(IF(m.goals_home = m.goals_guest AND goals_home <> '' AND goals_guest <> '',1,0)) AS draws,
						SUM(IF((m.team_id_home = t.team_id), IF(m.goals_home + 0 < m.goals_guest, 1, 0), IF(m.goals_home + 0 > m.goals_guest, 1, 0))) AS lost,
						SUM(IF(m.team_id_home = t.team_id, 
								IF(m.goals_home + 0 > m.goals_guest, 3, IF(m.goals_home = m.goals_guest AND goals_home <> '' AND goals_guest <> '', 1, 0)), 
								IF(m.goals_home + 0 < m.goals_guest, 3, IF(m.goals_home = m.goals_guest, 1, 0))
							)
						) AS points,
						SUM(IF(m.team_id_home = t.team_id,m.goals_home - m.goals_guest , m.goals_guest - m.goals_home)) AS gdiff,
						SUM(IF(m.team_id_home = t.team_id,m.goals_home , m.goals_guest)) AS goals,
						SUM(IF(m.team_id_home = t.team_id,m.goals_guest , m.goals_home)) AS goals_against
					FROM " . FOOTB_TEAMS . ' AS t
					LEFT JOIN ' . FOOTB_MATCHES . " AS m ON (m.season = t.season AND m.league = t.league AND m.team_id_guest = t.team_id)
					WHERE t.season = $season 
						AND t.league = $league 
						AND m.matchday < $matchday 
						AND m.status IN (3,6)
					GROUP BY t.team_id
					ORDER BY points DESC, gdiff DESC, goals DESC";

				$result = $db->sql_query($sql);
				$rank = 0;
				$current_rank = 0;
				$last_goals = 0;
				$last_goals_againts = 0;
				$last_points = 0;
				while ($row = $db->sql_fetchrow($result))
				{
					$rank++;
					if ($last_points <> $row['points'] OR $last_goals <> $row['goals'] OR $last_goals_againts <> $row['goals_against'])
					{
						$current_rank = $rank;
					}
					$last_points = $row['points'];
					$last_goals = $row['goals'];
					$last_goals_againts = $row['goals_against'];
					if ($guest_id == $row['team_id'])
					{
						$data_guest = true;
						$win = $row['wins'];
						$draw = $row['draws'];
						$lost = $row['lost'];
						$value_gg = round(((($win * 4) + ($draw * 2)) / ($win + $draw + $lost)),2);
						$value_g += $value_gg;
						$plfp = $current_rank . '(' . $value_gg . ')';
						$template->assign_block_vars('table_guestteam', array(
							'ROW_CLASS' 	=> 'bg1 row_light',
							'TABLE' 		=> sprintf($user->lang['TABLE_AWAY']),
							'PLFP' 			=> $plfp,
							'MATCHES' 		=> $row['matches'],
							'WINS' 			=> $row['wins'],
							'DRAW' 			=> $row['draws'],
							'LOST' 			=> $row['lost'],
							'GOALS_HOME' 	=> $row['goals'],
							'GOALS_GUEST' 	=> $row['goals_against'],
							'GDIFF' 		=> $row['gdiff'],
							'POINTS' 		=> $row['points'],
							)
						);
					}
				}
				$db->sql_freeresult($result);
			}
			
			// Form-Table
			$points_home = 0;
			$points_guest = 0;
			$form_matches = 0;
			if ($data_home or $data_guest)
			{
				$sql = "SELECT
						t.*,
						SUM(1) AS matches,
						SUM(IF((m.team_id_home = t.team_id), IF(goals_home + 0 > goals_guest, 1, 0), IF(goals_home + 0 < goals_guest, 1, 0))) AS wins,
						SUM(IF(goals_home = goals_guest AND goals_home <> '' AND goals_guest <> '', 1, 0)) AS draws,
						SUM(IF((m.team_id_home = t.team_id), IF(goals_home + 0 < goals_guest, 1, 0), IF(goals_home + 0 > goals_guest, 1, 0))) AS lost,
						SUM(IF(m.team_id_home = t.team_id, 
								IF(goals_home + 0 > goals_guest, 3, IF(goals_home = goals_guest AND goals_home <> '' AND goals_guest <> '', 1, 0)), 
								IF(goals_home + 0 < goals_guest, 3, IF(goals_home = goals_guest, 1, 0))
							)
						) AS points,
						SUM(IF(m.team_id_home = t.team_id, goals_home - goals_guest , goals_guest - goals_home)) AS gdiff,
						SUM(IF(m.team_id_home = t.team_id, goals_home , goals_guest)) AS goals,
						SUM(IF(m.team_id_home = t.team_id, goals_guest , goals_home)) AS goals_against
					FROM " . FOOTB_TEAMS . ' AS t
					LEFT JOIN ' . FOOTB_MATCHES . " AS m ON (m.season = t.season AND m.league = t.league AND ((m.team_id_home = t.team_id) OR (m.team_id_guest = t.team_id)))
					WHERE t.season = $season 
						AND t.league = $league 
						AND m.matchday >= $form_from 
						AND m.matchday < $matchday 
						AND m.status IN (3,6)
					GROUP BY t.team_id
					ORDER BY points DESC, gdiff DESC, goals DESC";

				$result = $db->sql_query($sql);
				$rank = 0;
				$current_rank = 0;
				$last_goals = 0;
				$last_goals_againts = 0;
				$last_points = 0;
				while ($row = $db->sql_fetchrow($result))
				{
					$rank++;
					if ($last_points <> $row['points'] OR $last_goals <> $row['goals'] OR $last_goals_againts <> $row['goals_against'])
					{
						$current_rank = $rank;
					}
					$last_points = $row['points'];
					$last_goals = $row['goals'];
					$last_goals_againts = $row['goals_against'];
					if ($home_id == $row['team_id'])
					{
						$points_home = $row['points'];
						if ($row['matches'] > $form_matches)
						{
							$form_matches = $row['matches'];
						}
						$data_home = true;
						$win = $row['wins'];
						$draw = $row['draws'];
						$lost = $row['lost'];
						$value_hg = round(((($win * 2) + $draw) / ($win + $draw + $lost)),2);
						$value_h += $value_hg;
						$plfp = $current_rank . '(' . $value_hg . ')';
						$template->assign_block_vars('table_hometeam', array(
							'ROW_CLASS' 	=> 'bg2 row_dark',
							'TABLE' 		=> sprintf($user->lang['TABLE_FORM']),
							'PLFP' 			=> $plfp,
							'MATCHES' 		=> $row['matches'],
							'WINS' 			=> $row['wins'],
							'DRAW' 			=> $row['draws'],
							'LOST' 			=> $row['lost'],
							'GOALS_HOME' 	=> $row['goals'],
							'GOALS_GUEST' 	=> $row['goals_against'],
							'GDIFF' 		=> $row['gdiff'],
							'POINTS' 		=> $row['points'],
							)
						);
					}
					if ($guest_id == $row['team_id'])
					{
						$points_guest = $row['points'];
						if ($row['matches'] > $form_matches)
						{
							$form_matches = $row['matches'];
						}
						$data_guest = true;
						$win = $row['wins'];
						$draw = $row['draws'];
						$lost = $row['lost'];
						$value_gg = round(((($win * 2) + $draw) / ($win + $draw + $lost)),2);
						$value_g += $value_gg;
						$plfp = $current_rank . '(' . $value_gg . ')';
						$template->assign_block_vars('table_guestteam', array(
							'ROW_CLASS' 	=> 'bg2 row_dark',
							'TABLE' 		=> sprintf($user->lang['TABLE_FORM']),
							'PLFP' 			=> $plfp,
							'MATCHES' 		=> $row['matches'],
							'WINS' 			=> $row['wins'],
							'DRAW' 			=> $row['draws'],
							'LOST' 			=> $row['lost'],
							'GOALS_HOME' 	=> $row['goals'],
							'GOALS_GUEST' 	=> $row['goals_against'],
							'GDIFF' 		=> $row['gdiff'],
							'POINTS' 		=> $row['points'],
							)
						);
					}
				}
				$db->sql_freeresult($result);
			}
			
			$matches_played = 0;
			$draws_home = 0;
			$draws_guest = 0;
			
			// Chart hometeam
			if ($data_home)
			{
				$sql = "SELECT
							IF(m.team_id_home = t.team_id, 
								IF(goals_home + 0 > goals_guest, '+0', IF(goals_home = goals_guest, '30', '-0')), 
								IF(goals_home + 0 < goals_guest, '+90', IF(goals_home = goals_guest, '60', '-90'))) AS chart_points,
								th.team_name AS home_name,
								tg.team_name AS guest_name,
								m.goals_home,
								m.goals_guest
						FROM " . FOOTB_TEAMS . ' AS t
						LEFT JOIN ' . FOOTB_MATCHES . ' AS m ON (m.season = t.season AND m.league = t.league 
															AND (m.team_id_home = t.team_id OR m.team_id_guest = t.team_id))
						LEFT JOIN ' . FOOTB_TEAMS . " AS th ON (th.season = $season AND th.league = $league AND th.team_id = m.team_id_home)
						LEFT JOIN " . FOOTB_TEAMS . " AS tg ON (tg.season = $season AND tg.league = $league AND tg.team_id = m.team_id_guest)
						WHERE t.season = $season 
							AND t.league = $league 
							AND m.matchday <= $matchday 
							AND m.match_no <> $matchnumber 
							AND m.status IN (3,6) 
							AND t.team_id=$home_id
						ORDER BY m.match_datetime ASC";

				$result = $db->sql_query($sql);
				$trend = $db->sql_fetchrowset($result);
				$db->sql_freeresult($result);
				$trend_count = sizeof($trend);
				$chart_points = '';
				$map_home_coords = array();
				$map_home_titles = array();
				$rank = 0;
				foreach ($trend as $row)
				{
					if ($row['chart_points'] == '30' or $row['chart_points'] == '60')
					{
						$draws_home += 1;
					}
					$chart_points = $chart_points . $row['chart_points']. ',';
					$coord_start = $start + ($rank * $vert);
					$coord_end = $end + ($rank * $vert);
					if (abs($row['chart_points']) > 45)
					{
						$coords =  $coord_start . ",45," . $coord_end . "," . abs($row['chart_points']);
					}
					else
					{
						$coords =  $coord_start  . "," . abs($row['chart_points']) . ","  . $coord_end . ",45";
					}
					$map_home_coords[] = $coords;
					$map_home_titles[] = $row['home_name'] . ' - ' . $row['guest_name'] . ' ' . $row['goals_home'] . ':' . $row['goals_guest'];
					$rank++;
				}
				$chart_points = substr($chart_points, 0, strlen($chart_points) - 1);
				$chart= "<img src='" . generate_board_url() . '/' . $this->football_root_path
									. "includes/chart_hist.php?v1=$chart_points' alt='" . sprintf($user->lang['HIST_CHART']) . "' usemap='#map_home'/>";
				$template->assign_block_vars('chart_home', array(
					'CHARTIMAGE' => $chart,
					)
				);
				for ($i = 0; $i < sizeof($map_home_coords); $i++) 
				{
					$template->assign_block_vars('chart_home.map_home', array(
						'COORDS' 	=> $map_home_coords[$i],
						'TITLE' 	=> $map_home_titles[$i],
						)
					);
				}
				$matches_played = sizeof($map_home_coords);
			}
			
			//Chart guestteam
			if ($data_guest)
			{
				$sql = "SELECT
							IF(m.team_id_home = t.team_id, 
									IF(goals_home + 0 > goals_guest, '+0', IF(goals_home = goals_guest, '30', '-0')), 
									IF(goals_home + 0 < goals_guest, '+90', IF(goals_home = goals_guest, '60', '-90')
								)
							) AS chart_points,
							th.team_name AS home_name,
							tg.team_name AS guest_name,
							m.goals_home,
							m.goals_guest
						FROM " . FOOTB_TEAMS . ' AS t
						LEFT JOIN ' . FOOTB_MATCHES . ' AS m ON (m.season = t.season AND m.league = t.league 
															AND (m.team_id_home = t.team_id OR m.team_id_guest = t.team_id))
						LEFT JOIN ' . FOOTB_TEAMS . " AS th ON (th.season = $season AND th.league = $league AND th.team_id = m.team_id_home)
						LEFT JOIN " . FOOTB_TEAMS . " AS tg ON (tg.season = $season AND tg.league = $league AND tg.team_id = m.team_id_guest)
						WHERE t.season = $season 
							AND t.league = $league 
							AND m.matchday <= $matchday 
							AND m.match_no <> $matchnumber 
							AND m.status IN (3,6) 
							AND t.team_id=$guest_id
						ORDER BY m.match_datetime ASC";

				$result = $db->sql_query($sql);
				$trend = $db->sql_fetchrowset($result);
				$db->sql_freeresult($result);
				$trend_count = sizeof($trend);
				$chart_points = '';
				$map_guest_coords = array();
				$map_guest_titles = array();
				$rank = 0;
				foreach ($trend as $row)
				{
					if ($row['chart_points'] == '30' or $row['chart_points'] == '60')
					{
						$draws_guest += 1;
					}
					$chart_points = $chart_points . $row['chart_points']. ',';
					$coord_start = $start + ($rank * $vert);
					$coord_end = $end + ($rank * $vert);
					if (abs($row['chart_points']) > 45)
					{
						$coords =  $coord_start . ",45," . $coord_end . "," . abs($row['chart_points']);
					}
					else
					{
						$coords =  $coord_start  . "," . abs($row['chart_points']) . ","  . $coord_end . ",45";
					}
					$map_guest_coords[] = $coords;
					$map_guest_titles[] = $row['home_name'] . ' - ' . $row['guest_name'] . ' ' . $row['goals_home'] . ':' . $row['goals_guest'];
					$rank++;
				}
				$chart_points = substr($chart_points, 0, strlen($chart_points) - 1);
				$chart= "<img src='" . generate_board_url() . '/' . $this->football_root_path
									. "includes/chart_hist.php?v1=$chart_points' alt='" . sprintf($user->lang['HIST_CHART']) . "' usemap='#map_guest'/>";
				$template->assign_block_vars('chart_guest', array(
					'CHARTIMAGE' => $chart,
					)
				);
				for ($i = 0; $i < sizeof($map_guest_coords); $i++) 
				{
					$template->assign_block_vars('chart_guest.map_guest', array(
						'COORDS' 	=> $map_guest_coords[$i],
						'TITLE' 	=> $map_guest_titles[$i],
						)
					);
				}
				$matches_played = $matches_played + sizeof($map_guest_coords);
			}

			if ($data_home and $data_guest)
			{
				$percent_draws_season = round(($draws_home + $draws_guest) / $matches_played, 2);
			}
			else
			{
				$percent_draws_season = 0.0;
				$form_matches = 0;
			}
			if ($history_count <= 2)
			{
				switch($form_matches)
				{
					case 0:
						$percent_draw += (2 - $history_count) * 0.1;
						break;
					case 1:
						$percent_draw += (2 - $history_count) * 0.1  + round($percent_draws_season / 4, 2);
						break;
					default: 
						$percent_draw += (2 - $history_count) * 0.1  + round($percent_draws_season / 2, 2);
						break;
				}
			}
			else
			{
				if (abs($points_home - $points_guest) < 3)
				{
					$equal_form_factor = 1.1;
				}
				else
				{
					$equal_form_factor = 1.0;
				}
				switch($form_matches)
				{
					case 0:
						$percent_draw = round($percent_draw * 1.5, 2);
						break;
					case 1:
						$percent_draw = round(($percent_draw * 1.5) + ($percent_draws_season / 8), 2);
						break;
					case 2:
					case 3:
					case 4:
						$percent_draw = round(($percent_draw * $equal_form_factor) + ($percent_draws_season / 4), 2);
						break;
					default: 
						$percent_draw = round(($percent_draw * $equal_form_factor) + ($percent_draws_season / 2), 2);
						if ($percent_draw > 1.0)
						{
							$percent_draw = 1.0;
						}
						//$percent_draw = ($percent_draw * $equal_form_factor) + ($percent_draws_season / 2) ;
						break;
				}
			}
			$percent_draw = $percent_draw;
			
			// last matches hometeam
			$sql = "(SELECT
						match_date AS matchtime,
						IF(mh.team_id_home = $home_id, 'H', 'A') AS match_place,
						IF(mh.team_id_home = $home_id, tgh.team_name, thh.team_name) AS against,
						mh.goals_home,
						mh.goals_guest,
						IF(mh.team_id_home = $home_id, 
								IF(mh.goals_home + 0 > mh.goals_guest, 1, IF(mh.goals_home = mh.goals_guest, 0, 2)),
								IF(mh.goals_home + 0 < mh.goals_guest, 1, IF(mh.goals_home = mh.goals_guest, 0, 2)
							)
						) AS match_res
					FROM " . FOOTB_MATCHES_HIST . ' AS mh
					LEFT JOIN ' . FOOTB_TEAMS_HIST . ' AS thh ON thh.team_id = mh.team_id_home
					LEFT JOIN ' . FOOTB_TEAMS_HIST . " AS tgh ON tgh.team_id = mh.team_id_guest
					WHERE (mh.team_id_home = $home_id 
						OR mh.team_id_guest = $home_id)
				)
				UNION
				(SELECT
						match_datetime AS matchtime,
						IF(m.team_id_home = $home_id, 'H', 'A') AS match_place,
						IF(m.team_id_home = $home_id, tg.team_name, th.team_name) AS against,
						m.goals_home,
						m.goals_guest,
						IF(m.team_id_home = $home_id, 
								IF(m.goals_home + 0 > m.goals_guest, 1, IF(m.goals_home = m.goals_guest, 0, 2)),
								IF(m.goals_home + 0 < m.goals_guest, 1, IF(m.goals_home = m.goals_guest, 0, 2)
							)
						) AS match_res
					FROM " . FOOTB_MATCHES . ' AS m
					LEFT JOIN ' . FOOTB_TEAMS . ' AS th ON (th.season = m.season AND th.league = m.league AND th.team_id = team_id_home)
					LEFT JOIN ' . FOOTB_TEAMS . " AS tg ON (tg.season = m.season AND tg.league = m.league AND tg.team_id = team_id_guest)
					WHERE m.season = $season 
						AND m.league = $league 
						AND m.matchday <= $matchday 
						AND m.match_no <> $matchnumber 
						AND (m.team_id_home = $home_id OR m.team_id_guest = $home_id) 
						AND m.status  IN(3,6)
				)
				ORDER BY matchtime";
				
			$result = $db->sql_query($sql);
			$lastmatches = $db->sql_fetchrowset($result);
			$db->sql_freeresult($result);
			$numb_matches = sizeof($lastmatches);


			if ($numb_matches > 10)
				$start = $numb_matches - 10;
			else
				$start = 0;
			$rank = 0;
			foreach ($lastmatches as $row)
			{
				$rank++;
				if ($rank > $start)
				{
					$row_class = (!($rank % 2)) ? 'bg1 row_light' : 'bg2 row_dark';
					$match_res_style='';
					switch($row['match_res'])
					{
						case 0:
							$match_res_style = 'match_draw';
							break;
						case 1:
							$match_res_style = 'match_win';
							break;
						case 2:
							$match_res_style = 'match_lost';
							break;
					}
					$template->assign_block_vars('last_hometeam', array(
						'ROW_CLASS' 	=> $row_class,
						'MATCH_RESULT' 	=> $match_res_style,
						'PLACE' 		=> $row['match_place'],
						'AGAINST' 		=> $row['against'],
						'GOALS_HOME' 	=> $row['goals_home'],
						'GOALS_GUEST' 	=> $row['goals_guest'],
						)
					);
				}
			}

			//last matches home hometeam
			$sql = '(SELECT
						match_date AS matchtime,
						th.team_name AS against,
						mh.goals_home,
						mh.goals_guest,
						IF(mh.goals_home + 0 > mh.goals_guest, 1, IF(mh.goals_home = mh.goals_guest, 0, 2)) AS match_res
					FROM ' . FOOTB_MATCHES_HIST . ' AS mh
					LEFT JOIN ' . FOOTB_TEAMS_HIST . " AS th ON th.team_id = mh.team_id_guest
					WHERE mh.team_id_home = $home_id
				)
				UNION
				(SELECT
					match_datetime AS matchtime,
					t.team_name AS against,
					m.goals_home,
					m.goals_guest,
					IF(m.goals_home + 0 > m.goals_guest, 1, IF(m.goals_home = m.goals_guest, 0, 2)) AS match_res
					FROM " . FOOTB_MATCHES . ' AS m
					LEFT JOIN ' . FOOTB_TEAMS . " AS t ON (t.season = m.season AND t.league = m.league AND t.team_id = m.team_id_guest)
					WHERE m.season = $season 
						AND m.league = $league 
						AND m.matchday <= $matchday 
						AND m.match_no <> $matchnumber 
						AND m.team_id_home = $home_id 
						AND m.status IN (3,6)
				)
				ORDER BY matchtime";
				
			$result = $db->sql_query($sql);
			$lastmatches = $db->sql_fetchrowset($result);
			$db->sql_freeresult($result);
			$numb_matches = sizeof($lastmatches);

			if ($numb_matches > 5)
				$start = $numb_matches - 5;
			else
				$start = 0;
			$rank = 0;
			foreach ($lastmatches as $row)
			{
				$data_last_home = true;
				$rank++;
				if ($rank > $start)
				{
					$row_class = (!(($rank-$start) % 2)) ? 'bg1 row_light' : 'bg2 row_dark';
					$match_res_style='';
					switch($row['match_res'])
					{
						case 0:
							$match_res_style = 'match_draw';
							break;
						case 1:
							$match_res_style = 'match_win';
							break;
						case 2:
							$match_res_style = 'match_lost';
							break;
					}
					$template->assign_block_vars('lasthome_hometeam', array(
						'ROW_CLASS' 	=> $row_class,
						'MATCH_RESULT' 	=> $match_res_style,
						'AGAINST' 		=> $row['against'],
						'GOALS_HOME' 	=> $row['goals_home'],
						'GOALS_GUEST' 	=> $row['goals_guest'],
						)
					);
				}
			}
			
			//last game guestteam
			$sql = "(SELECT
						match_date AS matchtime,
						IF(mh.team_id_home = $guest_id, 'H', 'A') AS match_place,
						IF(mh.team_id_home = $guest_id, tgh.team_name, thh.team_name) AS against,
						mh.goals_home,
						mh.goals_guest,
						IF(mh.team_id_home = $guest_id, 
								IF(mh.goals_home + 0 > mh.goals_guest, 1, IF(mh.goals_home = mh.goals_guest, 0, 2)),
								IF(mh.goals_home + 0 < mh.goals_guest, 1, IF(mh.goals_home = mh.goals_guest, 0, 2)
							)
						) AS match_res
					FROM " . FOOTB_MATCHES_HIST . ' AS mh
					LEFT JOIN ' . FOOTB_TEAMS_HIST . ' AS thh ON thh.team_id = mh.team_id_home
					LEFT JOIN ' . FOOTB_TEAMS_HIST . " AS tgh ON tgh.team_id = mh.team_id_guest
					WHERE mh.team_id_home = $guest_id 
						OR mh.team_id_guest = $guest_id
				)
				UNION
				(SELECT
						match_datetime AS matchtime,
						IF(m.team_id_home = $guest_id, 'H', 'A') AS match_place,
						IF(m.team_id_home = $guest_id, tg.team_name, th.team_name) AS against,
						m.goals_home,
						m.goals_guest,
						IF(m.team_id_home = $guest_id, 
								IF(m.goals_home + 0 > m.goals_guest, 1, IF(m.goals_home = m.goals_guest, 0, 2)),
								IF(m.goals_home + 0 < m.goals_guest, 1, IF(m.goals_home = m.goals_guest, 0, 2)
							)
						) AS match_res
					FROM " . FOOTB_MATCHES . ' AS m
					LEFT JOIN ' . FOOTB_TEAMS . ' AS th ON (th.season = m.season AND th.league = m.league AND th.team_id = team_id_home)
					LEFT JOIN ' . FOOTB_TEAMS . " AS tg ON (tg.season = m.season AND tg.league = m.league AND tg.team_id = team_id_guest)
					WHERE m.season = $season 
						AND m.league = $league 
						AND m.matchday <= $matchday 
						AND m.match_no <> $matchnumber 
						AND (m.team_id_home = $guest_id OR m.team_id_guest = $guest_id) 
						AND m.status IN(3,6)
				)
				ORDER BY matchtime";
				
			$result = $db->sql_query($sql);
			$lastmatches = $db->sql_fetchrowset($result);
			$db->sql_freeresult($result);
			$numb_matches = sizeof($lastmatches);

			if ($numb_matches > 10)
				$start = $numb_matches - 10;
			else
				$start = 0;
			$rank = 0;
			foreach ($lastmatches as $row)
			{
				$rank++;
				if ($rank > $start)
				{
					$row_class = (!($rank % 2)) ? 'bg1 row_light' : 'bg2 row_dark';
					$match_res_style='';
					switch($row['match_res'])
					{
						case 0:
							$match_res_style = 'match_draw';
							break;
						case 1:
							$match_res_style = 'match_win';
							break;
						case 2:
							$match_res_style = 'match_lost';
							break;
					}
					$template->assign_block_vars('last_guestteam', array(
						'ROW_CLASS' 	=> $row_class,
						'MATCH_RESULT' 	=> $match_res_style,
						'PLACE' 		=> $row['match_place'],
						'AGAINST' 		=> $row['against'],
						'GOALS_HOME' 	=> $row['goals_home'],
						'GOALS_GUEST' 	=> $row['goals_guest'],
						)
					);
				}
			}

			//last matches away guestteam
			$sql = '(SELECT
						match_date AS matchtime,
						th.team_name AS against,
						mh.goals_home AS goals_home,
						mh.goals_guest,
						IF(mh.goals_home + 0 < mh.goals_guest, 1, IF(mh.goals_home = mh.goals_guest, 0, 2)) AS match_res
					FROM ' . FOOTB_MATCHES_HIST . ' As mh
					LEFT JOIN ' . FOOTB_TEAMS_HIST . " AS th ON th.team_id = mh.team_id_home
					WHERE mh.team_id_guest = $guest_id
				) 
				UNION
				(SELECT
						match_datetime AS matchtime,
						t.team_name AS against,
						m.goals_home AS goals_home,
						m.goals_guest,
						IF(m.goals_home + 0 < m.goals_guest, 1, IF(m.goals_home = m.goals_guest, 0, 2)) AS match_res
					FROM " . FOOTB_MATCHES . ' As m
					LEFT JOIN ' . FOOTB_TEAMS . " AS t ON (t.season = m.season AND t.league = m.league AND t.team_id = m.team_id_home)
					WHERE m.season = $season AND m.league = $league AND m.matchday <= $matchday AND m.match_no <> $matchnumber 
						AND m.team_id_guest = $guest_id 
						AND m.status IN (3,6)
				)
				ORDER BY matchtime";
				
			$result = $db->sql_query($sql);
			$lastmatches = $db->sql_fetchrowset($result);
			$db->sql_freeresult($result);
			$numb_matches = sizeof($lastmatches);

			if ($numb_matches > 5)
				$start = $numb_matches - 5;
			else
				$start = 0;
			$rank = 0;
			foreach ($lastmatches as $row)
			{
				$data_last_away = true;
				$rank++;
				if ($rank > $start)
				{
					$row_class = (!(($rank-$start) % 2)) ? 'bg1 row_light' : 'bg2 row_dark';
					$match_res_style='';
					switch($row['match_res'])
					{
						case 0:
							$match_res_style = 'match_draw';
							break;
						case 1:
							$match_res_style = 'match_win';
							break;
						case 2:
							$match_res_style = 'match_lost';
							break;
					}
					$template->assign_block_vars('lastaway_guestteam', array(
						'ROW_CLASS' 	=> $row_class,
						'MATCH_RESULT' 	=> $match_res_style,
						'AGAINST' 		=> $row['against'],
						'GOALS_HOME' 	=> $row['goals_home'],
						'GOALS_GUEST' 	=> $row['goals_guest'],
						)
					);
				}
			}
			
			if ($history_count == 0 and !($data_home and $data_guest))
			{
				$percent_draw = 0;
				$percent_home = 0;
				$percent_guest = 0;
			}
			else
			{
				if ($value_h + $value_g == 0)
				{
					$percent_draw = 0;
					$percent_home = 0;
					$percent_guest = 0;
				}
				else
				{		
					$percent_draw = round(($percent_draw * 100),0);
					$rest = 100 - $percent_draw;
					if ($rest > 0)
					{
						$percent_home = round(($rest * $value_h) / ($value_h + $value_g),0);
						$percent_guest = $rest - $percent_home;
					}
					else
					{
						$percent_home = 0;
						$percent_guest = 0;
					}
				}
			}

			$sql_ary = array(
				'trend'	=> $percent_home . '|' . $percent_draw. '|' . $percent_guest,
			);

			$sql = 'UPDATE ' . FOOTB_MATCHES . ' SET ' . $db->sql_build_array('UPDATE', $sql_ary) . "
				WHERE season = $season AND league = $league AND match_no = $matchnumber";
			$result = $db->sql_query($sql);
			$db->sql_freeresult($result);
			
			
			$forecast_value = 0;
			$forecast_quote = sprintf($user->lang['FORECAST_PTS']) . ': '. $value_h. ' : '. $value_g;
			$forecast_value = $value_h - $value_g;
			if($forecast_value >= -0.5 && $forecast_value <= 0.5)
			{
				$forecast = sprintf($user->lang['TENDENCY']) . ': '. sprintf($user->lang['DRAW']);
			}
			else if($forecast_value > 0.5)
			{
				$forecast = sprintf($user->lang['TENDENCY']) . ': '. sprintf($user->lang['WIN_FOR']) . ' '. $logo[$home_id]. ' '. $home_name;
			}
			else
			{	
				$forecast = sprintf($user->lang['TENDENCY']) . ': '. sprintf($user->lang['WIN_FOR']) . ' '. $logo[$guest_id]. ' '. $guest_name;;
			}
		}
		else
		{
			$data_history = false;
			$error_message .= sprintf($user->lang['NO_LEAGUE']) . '<br />';
		}
	}
	else
	{
		$data_history = false;
		$error_message .= sprintf($user->lang['NO_SEASON']) . '<br />';
	}
}
		
if ($data_history)
{
	$main_title = $logo[$home_id]. ' '. $home_name. '  :  '.  $guest_name. ' '. $logo[$guest_id];
	$template->assign_vars(array(
		'MAIN_TITLE' 		=> $main_title,
		'TEAM_HOME' 		=> $logo[$home_id]. ' '. $home_name,
		'TEAM_GUEST' 		=> $logo[$guest_id]. ' '. $guest_name,
		'FORECAST_QUOTE' 	=> $forecast_quote,
		'FORECAST' 			=> $forecast,
		'S_FOOTBALL_COPY' 	=> sprintf($user->lang['FOOTBALL_COPY'], $config['football_version'], $phpbb_root_path . 'football/'),
		'S_ERROR_MESSAGE'	=> $error_message,
		'S_DATA_HISTORY'	=> $data_history,
		'S_DATA_HIST' 		=> $data_hist,
		'S_DATA_HOME' 		=> $data_home,
		'S_DATA_GUEST' 		=> $data_guest,
		'S_DATA_LASTHOME' 	=> $data_last_home,
		'S_DATA_LASTAWAY' 	=> $data_last_away,
		)
	);

	// output page
	page_header(sprintf($user->lang['MATCH_STATS']));
}
else
{
	$template->assign_vars(array(
		'MAIN_TITLE' 		=> '',
		'TEAM_HOME' 		=> '',
		'TEAM_GUEST' 		=> '',
		'FORECAST_QUOTE' 	=> '',
		'FORECAST' 			=> '',
		'S_FOOTBALL_COPY' 	=> sprintf($user->lang['FOOTBALL_COPY'], $config['football_version'], $phpbb_root_path . 'football/'),
		'S_ERROR_MESSAGE'	=> $error_message,
		'S_DATA_HISTORY'	=> $data_history,
		'S_DATA_HIST' 		=> false,
		'S_DATA_HOME' 		=> false,
		'S_DATA_GUEST' 		=> false,
		'S_DATA_LASTHOME' 	=> false,
		'S_DATA_LASTAWAY' 	=> false,
		)
	);

	// output page
	page_header(sprintf($user->lang['MATCH_STATS']));
}

$template->set_filenames(array(
	'body' => 'hist_popup.html')
);

page_footer();
?>