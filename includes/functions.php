<?php
/**
*
* @package phpBB Extension - Football Football
* @copyright (c) 2016 football (http://football.bplaced.net)
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

/**
* @ignore
*/

if (!defined('IN_PHPBB'))
{
	exit;
}


/**
* calculate extra Points (table FOOTB_EXTRA_BETS).
*/
function calculate_extra_points($season, $league, $matchday, $finally = false)
{
	global $db, $config, $lang, $league_info;
	
	$where_status = (!$finally) ? ' AND extra_status < 3 ' : '';
	
	// get all extra bets to calculate
	$sql = 'SELECT * 
			FROM ' . FOOTB_EXTRA . " 
			WHERE season = $season 
				AND league = $league 
				AND matchday_eval = $matchday
				AND extra_status > 0
				$where_status";
	$result = $db->sql_query($sql);

	$result_ary = array();
	$extra_points_error = true;
	while($row = $db->sql_fetchrow($result))
	{
		$extra_no = $row['extra_no'];
		if ($row['result'] <> '')
		{
			switch($row['question_type'])
			{
				case '2':
				case '4':
					// multiple results
					{
						$bet_points = 'CASE bet ';
						$result_ary = explode(';', $row['result']);
						foreach($result_ary AS $result_value)
						{
							if ($result_value <> '')
							{
								$result_value = (is_numeric($result_value)) ? $result_value : "'" . $result_value . "'";
								$bet_points .= ' WHEN ' . $result_value . ' THEN ' .  $row['extra_points'] . ' ';
								$extra_points_error = false;
							}
						}
						$bet_points .= ' ELSE 0 END';
					}
					break;
				case '5':
					// difference to result
					{
						if (is_numeric($row['result']))
						{
							$bet_points = 'IF(' . $row['extra_points'] . '- ABS(bet - ' . $row['result'] . ') > 0, ' . $row['extra_points'] . '- ABS(bet - ' . $row['result'] . ')' . ', 0)';
							$extra_points_error = false;
						}
						else
						{
							$extra_points_error = true;
						}
					}
					break;
				default:
					// Case 1 and 3 and other
					// correct result
					{
						$result_value = (is_numeric($row['result'])) ? $row['result'] : "'" . $row['result'] . "'";
						$bet_points = 'CASE bet WHEN ' . $result_value . ' THEN ' .  $row['extra_points'] . ' ELSE 0 END';
						$extra_points_error = false;
					}
					break;
			}
			if (!$extra_points_error)
			{
				$sql = 'UPDATE ' . FOOTB_EXTRA_BETS . '
					SET bet_points = ' . $bet_points . "
					WHERE season = $season 
						AND league = $league 
						AND extra_no = $extra_no";
				$db->sql_query($sql);
			}
		}
	}
	$db->sql_freeresult($result);
}


/**
* Save matchday-ranking in database (table FOOTB_RANKS).
*/
function save_ranking_matchday($season, $league, $matchday, $cash = false)
{
	global $db, $config, $lang, $league_info;
	$sql = 'SELECT * FROM ' . FOOTB_MATCHDAYS . " WHERE season = $season AND league = $league AND matchday = $matchday";
	$result = $db->sql_query($sql);

	if ( $row = $db->sql_fetchrow($result))
	{
		$matchday_status = $row['status'];
		if ($row['delivery_date_2'] != '' OR ($league_info['bet_in_time'] AND $matchday_status <> 3))
			// We set status to 2 to skip deleting the ranking
			$matchday_status = 2;
		$db->sql_freeresult($result);
		if ($matchday_status < 2)
		{
			$sql = 'SELECT * FROM ' . FOOTB_MATCHES . " WHERE season = $season AND league = $league AND matchday = $matchday AND status IN (2,3)";
			$result = $db->sql_query($sql);
			if ( $row = $db->sql_fetchrow($result))
			{
				$matchday_status = 2;
			}
		}
		
		if ($matchday_status == 0 OR $matchday_status == 1)
		{
			// No matches played, so we can delete the ranking
			$sql = 'DELETE FROM ' . FOOTB_RANKS . " WHERE season = $season AND league = $league AND matchday = $matchday";
			$result = $db->sql_query($sql);
		}
		else
		{
			$matchday_wins = array();
			$matchday_wins = matchday_wins($season, $league);
			$sql = "
				SELECT
				1 AS rank,
				0.00 AS win,
				u.user_id,
				u.username AS username,
				SUM(IF(m.match_no > 0, 1, 0)) AS matches,
				SUM(IF(b.goals_home = '' OR b.goals_guest = '', 1, 0)) AS nobet,
				SUM(IF(b.goals_home <> '' AND b.goals_guest <> '',
						IF((b.goals_home = m.goals_home) AND (b.goals_guest = m.goals_guest)
							, 1
							, 0
						)
						, 0
					)
				) AS direct_hit,
				SUM(IF(b.goals_home <> '' AND b.goals_guest <> '',
						IF((b.goals_home + 0 < b.goals_guest) <> (m.goals_home + 0 < m.goals_guest) 
						OR (b.goals_home = b.goals_guest) <> (m.goals_home = m.goals_guest) 
						OR (b.goals_home + 0 > b.goals_guest) <> (m.goals_home + 0 > m.goals_guest)
							, 0
							, 1
						)
						, 0
					)
				) AS tendency,
				" . select_points('m',true) . '
				FROM ' . FOOTB_MATCHES . ' AS m
				INNER JOIN ' . FOOTB_BETS . ' AS b ON (b.season = m.season AND b.league = m.league AND b.match_no = m.match_no)
				INNER JOIN ' . USERS_TABLE . "  AS u ON (b.user_id = u.user_id)
				WHERE  m.season = $season AND m.league = $league AND m.matchday = $matchday AND m.status IN (2,3)
				GROUP BY b.user_id
				ORDER BY points DESC, nobet ASC, username ASC
				";

			$result = $db->sql_query($sql);
			
			$ranking_ary = array();
			while( $row = $db->sql_fetchrow($result))
			{
				$ranking_ary[$row['user_id']] = $row;
			}
			$db->sql_freeresult($result);
			if ( sizeof($ranking_ary) > 0 )
			{
				$sql = '
					SELECT
					eb.user_id,
					SUM(eb.bet_points) AS points
					FROM  ' . FOOTB_EXTRA . ' AS e
					INNER JOIN ' . FOOTB_EXTRA_BETS . " AS eb ON (eb.season = e.season and eb.league = e.league and eb.extra_no = e.extra_no)
					WHERE e.season = $season
						AND e.league = $league
						AND e.matchday = $matchday 
						AND e.matchday_eval = $matchday 
						AND e.extra_status > 1
					GROUP BY eb.user_id"; 
				
				$result = $db->sql_query($sql);
				while( $row = $db->sql_fetchrow($result))
				{
					$ranking_ary[$row['user_id']]['points'] += $row['points'];
				}
				$db->sql_freeresult($result);

				// sort the ranking by points
				usort($ranking_ary, '_sort_points');
			}

			$last_points = -1;
			$last_rank = 1;
			$equal_rank = array();
			$money = array();
			$i = 0; 
			foreach( $ranking_ary AS $curr_user => $curr_rank)
			{
				if ($curr_rank['nobet'] == $curr_rank['matches'] and $league_info['points_last'] == 1)
				{
					if ($last_points <> -1)
					{
						$ranking_ary[$curr_user]['points'] = $last_points;
					}
					else
					{
						$ranking_ary[$curr_user]['points'] = 0;
						$last_points = 0;
						$equal_rank[$last_rank] = 0;
					}
				}
				if ( $ranking_ary[$curr_user]['points'] != $last_points)
				{
					$ranking_ary[$curr_user]['rank'] = $i + 1;
					$last_rank = $i + 1;
					$equal_rank[$last_rank] = 1;
					if ($last_rank < sizeof($matchday_wins))
					{
						$money[$last_rank] = $matchday_wins[$last_rank];
					}
					else
					{
						$money[$last_rank] = 0;
					}
					$last_points = $ranking_ary[$curr_user]['points'];
				}
				else
				{
					$ranking_ary[$curr_user]['rank'] = $last_rank;
					$equal_rank[$last_rank] += 1;
					if ($i + 1 < sizeof($matchday_wins))
					{
						$money[$last_rank] += $matchday_wins[$i + 1];
					}
				}
				$i++;
			}
			foreach( $ranking_ary AS $curr_user => $curr_rank)
			{
				if ( round($money[$curr_rank['rank']] / $equal_rank[$curr_rank['rank']], 2) <> 0 )
				{
					$win = round($money[$curr_rank['rank']] / $equal_rank[$curr_rank['rank']], 2);
					$ranking_ary[$curr_user]['win'] = $win;
				}
				else
				{
					$win = 0;
					$ranking_ary[$curr_user]['win'] = 0;
				}
				$sql = 'REPLACE INTO ' . FOOTB_RANKS . " 
						VALUES ($season
								, $league
								, $matchday
								, " . $curr_rank['user_id'] . "
								, $matchday_status
								, " . $curr_rank['rank'] . "
								, " . $curr_rank['points'] . "
								, $win
								, 0
								, " . $curr_rank['tendency'] . "
								, " . $curr_rank['direct_hit'] . "
								,0
								,0
								)";
				$result = $db->sql_query($sql);
			}
			if ( sizeof($ranking_ary) == 0 )
			{
				$sql = 'DELETE FROM ' . FOOTB_RANKS . " 
						WHERE season = $season 
							AND league = $league 
							AND matchday = $matchday";
				$result = $db->sql_query($sql);
			}
			else
			{
				// Calculate total ranking 
				$rank_total_ary = array();
				$sql = 'SELECT
							user_id ,
							SUM(points) AS points,
							SUM(win) AS wins_total
						FROM ' . FOOTB_RANKS . " 
						WHERE season = $season 
							AND league = $league 
							AND matchday <= $matchday
						GROUP by user_id
						ORDER BY points DESC, user_id ASC";
				$result = $db->sql_query($sql);
				while( $row = $db->sql_fetchrow($result))
				{
					$rank_total_ary[$row['user_id']] = $row;
				}
				$db->sql_freeresult($result);
				
				// add extra tipp points total ranking
				$league_info = league_info($season, $league);
				if ( sizeof($rank_total_ary) > 0 )
				{
					if ( $matchday == $league_info['matchdays'])
					{					
						$win_user_most_hits = win_user_most_hits($season, $league, $matchday);
						$win_user_most_hits_away = win_user_most_hits_away($season, $league, $matchday);
						$season_wins = season_wins($season, $league, $matchday);
					}
					$sql = 'SELECT
								eb.user_id,
								SUM(eb.bet_points) AS points
							FROM  ' . FOOTB_EXTRA . ' AS e
							INNER JOIN ' . FOOTB_EXTRA_BETS . " AS eb ON (eb.season = e.season and eb.league = e.league and eb.extra_no = e.extra_no)
							WHERE e.season = $season
								AND e.league = $league
								AND e.matchday <> e.matchday_eval 
								AND e.matchday_eval <= $matchday 
								AND e.extra_status > 1
							GROUP BY eb.user_id"; 
					
					$result = $db->sql_query($sql);
					while( $row = $db->sql_fetchrow($result))
					{
						$rank_total_ary[$row['user_id']]['points'] += $row['points'];
					}
					$db->sql_freeresult($result);

					// sort the ranking by points
					usort($rank_total_ary, '_sort_points');
				}

				$index = 0;
				$last_rank = 0;
				$last_points = -1;
				foreach( $rank_total_ary AS $curr_user => $curr_rank)
				{
					$index++;
					if ( $curr_rank['points'] == $last_points)
					{
						$rank_total = $last_rank;
					}
					else
					{
						$rank_total = $index;
						$last_points = $curr_rank['points'];
						$last_rank = $rank_total;
					}
					if ($matchday == $league_info['matchdays'])
					{
						// if someone didn't bet the hole Season
						if (!isset($win_user_most_hits[$curr_rank['user_id']]['win']))
						{
							$win_user_most_hits[$curr_rank['user_id']]['win'] = 0;
						}
						if (!isset($win_user_most_hits_away[$curr_rank['user_id']]['win']))
						{
							$win_user_most_hits_away[$curr_rank['user_id']]['win'] = 0;
						}
						if (!isset($season_wins[$curr_rank['user_id']]['win']))
						{
							$season_wins[$curr_rank['user_id']]['win'] = 0;
						}
						$rank_total_ary[$curr_user]['wins_total'] = sprintf('%01.2f',$curr_rank['wins_total'] + $win_user_most_hits[$curr_rank['user_id']]['win'] + $win_user_most_hits_away[$curr_rank['user_id']]['win'] + $season_wins[$curr_rank['user_id']]['win']);
					}
					else
					{
						$rank_total_ary[$curr_user]['wins_total']  = sprintf('%01.2f',$curr_rank['wins_total']);
					}
					$curr_userid = $curr_rank['user_id'];
					$points_total = $curr_rank['points'];
					$win_total = $rank_total_ary[$curr_user]['wins_total'];
					$sql = 'UPDATE ' . FOOTB_RANKS . " 
							SET rank_total = $rank_total, 
							points_total = $points_total, 
							win_total = $win_total 
							WHERE season = $season AND league = $league AND matchday = $matchday AND user_id = $curr_userid";
					$result = $db->sql_query($sql);
				}
			}
		}
		if ($config['football_bank'])
		{
			if ($matchday_status < 3)
			{
				//Delete points
				if ($matchday == $league_info['matchdays'])
				{
					// On last matchday 
					rollback_points(POINTS_MOST_HITS, $season, $league, $matchday, $cash && ($config['football_ult_points'] == 1));
					rollback_points(POINTS_MOST_HITS_AWAY, $season, $league, $matchday, $cash && ($config['football_ult_points'] == 1));
					rollback_points(POINTS_SEASON, $season, $league, $matchday, $cash && ($config['football_ult_points'] == 1));
				}
				rollback_points(POINTS_MATCHDAY, $season, $league, $matchday, $cash && ($config['football_ult_points'] > 0));
			}
			else
			{
				//Set points on played matchday
				if ($matchday == $league_info['matchdays'] AND $config['football_bank'])
				{
					// On last matchday 
					set_points_most_hits($season, $league, $matchday, $win_user_most_hits, $cash && ($config['football_ult_points'] == 1));
					set_points_most_hits_away($season, $league, $matchday, $win_user_most_hits_away, $cash && ($config['football_ult_points'] == 1));
					set_points_season($season, $league, $matchday, $season_wins, $cash && ($config['football_ult_points'] == 1));
				}
				set_points_matchday($season, $league, $matchday, $ranking_ary, $cash && ($config['football_ult_points'] > 0));
			}
		}
		$sql = 'SELECT matchday FROM ' . FOOTB_RANKS . " 
				WHERE season = $season 
					AND league = $league 
					AND matchday > $matchday";
		$result = $db->sql_query($sql);
		if ( $next_matchday = (int) $db->sql_fetchfield('matchday'))
		{
			$db->sql_freeresult($result);
			save_ranking_matchday($season, $league, $next_matchday, $cash);			
		}
	}
}

function set_points_most_hits($season, $league, $matchday, $win_user_most_hits, $cash) 
{
	rollback_points(POINTS_MOST_HITS, $season, $league, $matchday, $cash);
	set_footb_points(POINTS_MOST_HITS, $season, $league, $matchday, $win_user_most_hits, $cash);
}

function set_points_most_hits_away($season, $league, $matchday, $win_user_most_hits_away, $cash) 
{
	rollback_points(POINTS_MOST_HITS_AWAY, $season, $league, $matchday, $cash);
	set_footb_points(POINTS_MOST_HITS_AWAY, $season, $league, $matchday, $win_user_most_hits_away, $cash);
}

function set_points_season($season, $league, $matchday, $season_wins, $cash) 
{
	rollback_points(POINTS_SEASON, $season, $league, $matchday, $cash);
	set_footb_points(POINTS_SEASON, $season, $league, $matchday, $season_wins, $cash);
}

function set_points_matchday($season, $league, $matchday, $ranking_ary, $cash) 
{
	rollback_points(POINTS_MATCHDAY, $season, $league, $matchday, $cash);
	set_footb_points(POINTS_MATCHDAY, $season, $league, $matchday, $ranking_ary, $cash);
}

function rollback_points($points_type, $season, $league, $matchday, $cash) 
{
	global $db, $functions_points;
	if ($cash)
	{
		$where_matchday = ($matchday) ? " AND matchday = $matchday" : '';
		$sql = 'SELECT *
				FROM  ' . FOOTB_POINTS . " AS p
				WHERE season = $season 
					AND league = $league 
					$where_matchday
					AND points_type = $points_type
					AND cash = 1";
		
		$result = $db->sql_query($sql);
		while( $row = $db->sql_fetchrow($result))
		{
			$functions_points->substract_points($row['user_id'], $row['points']);
		}
		$db->sql_freeresult($result);
	
	}
	$sql = 'DELETE FROM ' . FOOTB_POINTS . " WHERE season = $season AND league = $league AND matchday = $matchday AND points_type = $points_type";
	$result = $db->sql_query($sql);
}

function set_footb_points($points_type, $season, $league, $matchday, $wins, $cash) 
{
	global $db, $user, $config, $functions_points;
	switch($points_type)
	{
		case 1:
				$points_comment = sprintf($user->lang['BET_POINTS']);
			break;
		case 2:
				$points_comment = sprintf($user->lang['DEPOSIT']);
			break;
		case 3:
				$points_comment = '';
			break;
		case 4:
				$points_comment = '';
			break;
		case 5:
				$points_comment = sprintf($user->lang['WIN_HITS']);
			break;
		case 6:
				$points_comment = sprintf($user->lang['WIN_HITS02']);
			break;
		case 7:
				$points_comment = sprintf($user->lang['PAYOUT_WIN']);
			break;
	}
	
	foreach( $wins AS $curr_user => $curr_ary)
	{
		if ($config['football_ult_points'] == 2 AND $points_type == 3)
		{
			// points mode
			
			if ($curr_ary['points'] > 0 AND $curr_ary['points'] <> NULL)
			{
				$userid = (!isset($curr_ary['user_id'])) ? $curr_user : $curr_ary['user_id'];
				$points_comment = sprintf($user->lang['POINTS']);
				$factor_points = round($config['football_ult_points_factor'] * $curr_ary['points'],2);
				if ($cash)
				{
					$functions_points->add_points($userid, $factor_points);
				}
				$sql_ary = array(
					'season'		=> (int) $season,
					'league'		=> (int) $league,
					'matchday'		=> (int) $matchday,
					'points_type'	=> (int) $points_type,
					'user_id'		=> (int) $userid,
					'points'		=> $factor_points,
					'points_comment'=> $points_comment,
					'cash'			=> $cash,
				);
				$sql = 'INSERT INTO ' . FOOTB_POINTS . ' ' . $db->sql_build_array('INSERT', $sql_ary);
				$db->sql_query($sql);
			}
		}
		if ($config['football_ult_points'] <= 1 OR ($config['football_ult_points'] == 2 AND $points_type == 1))
		{
			if ($curr_ary['win'] > 0)
			{
				switch($points_type)
				{
					case 3:
							$points_comment = sprintf($user->lang['WIN_MATCHDAY']) . ' ' . $curr_ary['rank'] . '.' . sprintf($user->lang['PLACE']);
						break;
					case 4:
							$points_comment = sprintf($user->lang['WIN_SEASON']) . ' ' . $curr_ary['rank'] . '.' . sprintf($user->lang['PLACE']);
						break;
				}
				$userid = (!isset($curr_ary['user_id'])) ? $curr_user : $curr_ary['user_id'];
				if ($cash)
				{
					if ($points_type == 1 OR $points_type == 7)
					{
						// substract bets and payouts
						$functions_points->substract_points($userid, round($curr_ary['win'],2));
					}
					else
					{
						$functions_points->add_points($userid, round($curr_ary['win'],2));
					}
				}
				$sql_ary = array(
					'season'		=> (int) $season,
					'league'		=> (int) $league,
					'matchday'		=> (int) $matchday,
					'points_type'	=> (int) $points_type,
					'user_id'		=> (int) $userid,
					'points'		=> round($curr_ary['win'],2),
					'points_comment'=> $points_comment,
					'cash'			=> $cash,
				);
				$sql = 'INSERT INTO ' . FOOTB_POINTS . ' ' . $db->sql_build_array('INSERT', $sql_ary);
				$db->sql_query($sql);
			}
		}
	}
}

function _sort_points($value_a, $value_b) 
{
    if ($value_a['points'] > $value_b['points']) 
	{
        return -1;
    } 
	else 
	{
        if ($value_a['points'] == $value_b['points']) 
		{
			if (isset($value_a['nobet']))
			{
				if ($value_a['nobet'] < $value_b['nobet']) 
				{
					return -1;
				} 
				else 
				{
					if ($value_a['nobet'] == $value_b['nobet']) 
					{
						return 0;
					} 
					else 
					{
						return 1;
					}
				}
			}
			else
			{
				return 0;
			}
        } 
		else 
		{
            return 1;
        }
    }
}


/**
* Return season-array
*/
function season_info($season)
{
	global $db;
	$sql = 'SELECT * FROM ' . FOOTB_SEASONS . " WHERE season = $season";
	$result = $db->sql_query($sql);

	if ($row = $db->sql_fetchrow($result))
	{
		return $row;
	}
	else
	{
		return array();
	}
	$db->sql_freeresult($result);
}

/**
* Return league-array
*/
function league_info($season, $league)
{
	global $db;
	$sql = 'SELECT * FROM ' . FOOTB_LEAGUES . " 
			WHERE season = $season AND league = $league
			";
	$result = $db->sql_query($sql);

	if ($row = $db->sql_fetchrow($result))
	{
		return $row;
	}
	else
	{
		return array();
	}
	$db->sql_freeresult($result);
}

/**
* Return team-array
*/
function team_info($season, $league, $team_id)
{
	global $db;
	$sql = 'SELECT * FROM ' . FOOTB_TEAMS . " 
			WHERE season = $season AND league = $league AND team_id = $team_id
			";
	$result = $db->sql_query($sql);

	if ($row = $db->sql_fetchrow($result))
	{
		return $row;
	}
	else
	{
		return array();
	}
	$db->sql_freeresult($result);
}

/**
* Is user member of this league
*/
function user_is_member($userid, $season, $league)
{
	global $db;
	$sql = 'SELECT COUNT(*) AS counter
		FROM  ' . FOOTB_BETS . " 
		WHERE season = $season AND league = $league AND user_id = $userid";
	$result = $db->sql_query($sql);
	$row = $db->sql_fetchrow($result);

	if ($row['counter'] > 0)
	{
		return  true;
	}
	else
	{
		return  false;
	} 
	$db->sql_freeresult($result);
}

/**
* Count existing matches on matchday or league
*/
function count_existing_matches($season, $league, $matchday)
{
	global $db;
	$where_matchday = ($matchday) ? " AND matchday = $matchday" : '';
	$sql = 'SELECT COUNT(*) AS counter
		FROM  ' . FOOTB_MATCHES . " 
		WHERE season = $season AND league = $league $where_matchday";
	$result = $db->sql_query($sql);
	if ($row = $db->sql_fetchrow($result))
	{
		return  $row['counter'];
		$db->sql_freeresult($result);
	}
	else
	{
		return  0;
	} 
}

/**
* Determinate if join to league is allowed
*/
function join_allowed($season, $league)
{
	global $db;
	$league_info = league_info($season, $league);
	if (! $league_info['join_by_user'])
	{
		return false;
	}
	else
	{
		if ($league_info['join_in_season'])
		{
			return true;
		}
		else
		{
			$sql = 'SELECT * FROM ' . FOOTB_MATCHDAYS . " 
					WHERE season = $season AND league = $league AND matchday = 1 AND status = 0
					";
			$result = $db->sql_query($sql);

			if ($row = $db->sql_fetchrow($result))
			{
				$db->sql_freeresult($result);
				return true;
			}
			else
			{
				return false;
			}
		}
	}
}

/**
* Calculate status or delivery of matchday
*/
function delivery($season, $league, $matchday)
{
	global $db, $user;
	$delivery = '';
	$lang_dates = $user->lang['datetime'];
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
			DATE_FORMAT(delivery_date,' %d.%m.%Y %H:%i')
		) AS deliverytime
		FROM " . FOOTB_MATCHDAYS . " 
		WHERE season = $season AND league = $league AND matchday = $matchday";
	$result = $db->sql_query($sql);

	if ($row = $db->sql_fetchrow($result))
	{
		switch($row['status'])
		{
			case 0:
					$delivery = '<strong style=\'color:green\'>' . sprintf($user->lang['STATUS_TEXT0']) . $row['deliverytime'] . '</strong>';
				break;
			case 1:
					$delivery = '<strong>' . sprintf($user->lang['STATUS_TEXT1']) . '</strong>';
				break;
			case 2:
					$delivery = '<strong>' . sprintf($user->lang['STATUS_TEXT2']) . '</strong>';
				break;
			case 3:
					$delivery =  '<strong>' . sprintf($user->lang['STATUS_TEXT3']) . '</strong>';
				break;
		}
		$db->sql_freeresult($result);
	}
	return $delivery;
}

/**
* Calculate next delivery und return delivery string
*/
function next_delivery($season, $league)
{
	global $db, $user;
	$next_delivery = '';
	$lang_dates = $user->lang['datetime'];
	$sql = "SELECT
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
				DATE_FORMAT(delivery_date,' %d.%m.%Y %H:%i')
			) AS deliverytime
			FROM " . FOOTB_MATCHDAYS . " 
			WHERE season = $season AND league = $league AND status = 0
			ORDER BY matchday ASC
			";
	$result = $db->sql_query($sql);

	if ($row = $db->sql_fetchrow($result))
	{
		$next_delivery = "<center><strong style='color:green'>". sprintf($user->lang['NEXT_DELIVERY_UNTIL']) . ' ' . $row['deliverytime'] . "</strong></center>";
		$db->sql_freeresult($result);
	}
	return $next_delivery;
}

/**
* Calculate first delivery of matchday for bet in time
*/
function first_delivery($season, $league, $matchday)
{
	global $db, $user;
	$sql = "SELECT MIN(match_datetime) AS min_match_datetime
			FROM " . FOOTB_MATCHES . " 
			WHERE season = $season AND league = $league AND matchday = $matchday AND status = 0
			ORDER BY min_match_datetime ASC
			";
	$result = $db->sql_query($sql);
	$first_delivery = '';
	if ($row = $db->sql_fetchrow($result))
	{
		$first_delivery = $row['min_match_datetime'];
		$db->sql_freeresult($result);
	}
	return $first_delivery;
}

/**
* Set matchday delivery to first open match 
*/
function set_bet_in_time_delivery($season, $league)
{
	global $db, $user, $config;
	$sql = "SELECT *
			FROM " . FOOTB_MATCHDAYS . " 
			WHERE season = $season AND league = $league AND status < 3
			";
	$result = $db->sql_query($sql);
	$first_delivery = '';
	while ($row = $db->sql_fetchrow($result))
	{
		$new_status = $row['status'];
		
		// match-status maybe changed
		$sql_ary = array(
			'status'				=> 0,
			'goals_home'			=> '',
			'goals_guest'			=> '',
			'goals_overtime_home'	=> '',
			'goals_overtime_guest'	=> '',
		);
		$local_board_time = time() + (($config['board_timezone'] - $config['football_host_timezone']) * 3600); 
		$sql = 'UPDATE ' . FOOTB_MATCHES . '
			SET ' . $db->sql_build_array('UPDATE', $sql_ary) . "
			WHERE season = $season AND league = $league AND matchday =" . $row['matchday'] . " AND match_datetime > FROM_UNIXTIME('$local_board_time')";
		$db->sql_query($sql);

		if ($db->sql_affectedrows())
		{
			// an open match exist, so set status 0
			$new_status = 0;
		}
		$first_delivery = first_delivery($season, $league, $row['matchday']);
		if ( $first_delivery <> '')
		{
			// Matchday has open matches so set matchday status = 0 and first delivery
			$sql_ary = array(
				'status'			=> $new_status,
				'delivery_date'		=> $first_delivery,
				'delivery_date_2'	=> '',
				'delivery_date_3'	=> '',
			);
			$sql = 'UPDATE ' . FOOTB_MATCHDAYS . '
				SET ' . $db->sql_build_array('UPDATE', $sql_ary) . "
				WHERE season = $season AND league = $league AND matchday =" . $row['matchday'];
			$db->sql_query($sql);
		}
	}
	$db->sql_freeresult($result);
}


/**
* return current season. The minimal seasons value with a open matchday (status = 0). 
*/
function curr_season()
{
	global $db, $lang, $user;
	$user_spec = '';
	if ($user->data['user_type']==0 OR $user->data['user_type']==3)
	{
		$curr_user = $user->data['user_id'];
		$user_spec = 'AND b.user_id = ' . $curr_user;
	}
	
	$sql = 'SELECT DISTINCT s.season 
			FROM  ' . FOOTB_SEASONS . '  AS s
			INNER JOIN ' . FOOTB_LEAGUES . ' AS l ON (l.season = s.season)
			INNER JOIN ' . FOOTB_MATCHDAYS . ' AS m ON (m.season = s.season AND m.league = l.league)
			INNER JOIN ' . FOOTB_BETS . ' AS b ON (b.season = m.season AND b.league = m.league ' . $user_spec . ') 
			WHERE m.status IN (0,1,2)
			ORDER BY s.season ASC';
	$result = $db->sql_query($sql);

	if ($row = $db->sql_fetchrow($result))
	{
		$curr_season = $row['season'];
		$db->sql_freeresult($result);
	}
	else
	{
		$sql = 'SELECT DISTINCT s.season FROM  ' . FOOTB_SEASONS . '  AS s
					INNER JOIN ' . FOOTB_LEAGUES . ' AS l ON (l.season = s.season)
					INNER JOIN ' . FOOTB_MATCHDAYS . ' AS m ON (m.season = s.season AND m.league = l.league)
					WHERE 1
					ORDER BY s.season DESC';
		$result = $db->sql_query($sql);

		if ($row = $db->sql_fetchrow($result))
		{
			$curr_season = $row['season'];
		}
		else
		{
			$sql = 'SELECT * FROM ' . FOOTB_SEASONS;
			$result = $db->sql_query($sql);

			if ($row = $db->sql_fetchrow($result))
			{
				$curr_season = $row['season'];
			}
			else
			{
				$curr_season = 0;
			}
		}
		$db->sql_freeresult($result);
	}
	return $curr_season;
}

/**
* return first (minmal) league of season 
*/
function first_league($season, $complete = true)
{
	global $db, $lang;
	$join_matchday = '';
	if ($complete)
	{
		$join_matchday = 'INNER JOIN ' . FOOTB_MATCHDAYS . ' AS m ON (m.season = l.season AND m.league = l.league) ';
	}
	$sql = 'SELECT * 
			FROM ' . FOOTB_LEAGUES . ' AS l ' .
			$join_matchday . "
			WHERE l.season = $season 
			ORDER BY l.league ASC";
	$result = $db->sql_query($sql);

	if ($row = $db->sql_fetchrow($result))
	{
		$first_league = $row['league'];
	}
	else
	{
		$first_league = 0;
	}
	$db->sql_freeresult($result);
	return $first_league;
}

/**
* return current (wait for results) league of season 
*/
function current_league($season)
{
	global $db, $lang, $user;
	$user_spec = '';
	if ($user->data['user_type']==0 OR $user->data['user_type']==3)
	{
		$curr_user = $user->data['user_id'];
		$user_spec = 'AND b.user_id = ' . $curr_user;
	}
	$sql = 'SELECT DISTINCT m.league 
			FROM ' . FOOTB_MATCHES . ' AS m  
			INNER JOIN ' . FOOTB_BETS . ' AS b ON (b.season = m.season AND b.league = m.league ' . $user_spec . ")
			WHERE m.season = $season 
			AND m.status in (0,1,2)
			ORDER BY m.match_datetime ASC
			LIMIT 1";
	$result = $db->sql_query($sql);

	if ($row = $db->sql_fetchrow($result))
	{
		$current_league = $row['league'];
	}
	else
	{
		$current_league = first_league($season);
	}
	$db->sql_freeresult($result);
	return $current_league;
}

/**
* return next free team-id  
*/
function nextfree_teamid()
{
	global $db, $lang;
	$sql = 'SELECT Max(team_id) AS lastid FROM ' . FOOTB_TEAMS;  
	$result = $db->sql_query($sql);

	if ($row = $db->sql_fetchrow($result))
	{
		return ($row['lastid'] + 1);
	}
	else
	{
		return 0;
	}
	$db->sql_freeresult($result);
}

/**
* return current (in order to play) matchday of league  
*/
function curr_matchday($season, $league)
{
	global $db, $lang;
	$sql = 'SELECT * FROM ' . FOOTB_MATCHDAYS . " 
			WHERE season = $season AND league = $league AND status < 3
			ORDER BY status DESC, delivery_date ASC
			";
	$result = $db->sql_query($sql);

	if ($row = $db->sql_fetchrow($result))
	{
		$curr_matchday = $row['matchday'];
		$db->sql_freeresult($result);
	}
	else
	{
		$sql = 'SELECT * FROM ' . FOOTB_MATCHDAYS . " 
				WHERE season = $season AND league = $league AND status = 3
				ORDER BY matchday DESC
				";
		$result = $db->sql_query($sql);

		if ($row = $db->sql_fetchrow($result))
		{
			$curr_matchday = $row['matchday'];
		}
		else
		{
			$curr_matchday = 0;
		}
		$db->sql_freeresult($result);
	}
	return $curr_matchday;
}


/**
* Initialize user bets. Adds s user to a league.
*/
function join_league($season, $league, $user_id)
{
	global $db, $lang;

	$league_info = league_info($season, $league);
	$matchdays = $league_info['matchdays'];
	if ($league_info['league_type'] == LEAGUE_CHAMP)
	{
		$matches_on_matchdays = $league_info['matches_on_matchday'];
	}
	else
	{
		$matches_on_matchdays = 0;
	}
	
	$count_updates = 0;
	$matches = $matches_on_matchdays;
	$match_id = 1;
	for($m_day = 1; $m_day <= $matchdays; $m_day++)
	{
		if ($matches_on_matchdays == 0)
		{
			$sql = 'SELECT matches from ' . FOOTB_MATCHDAYS . " WHERE season = $season AND league = $league AND matchday = $m_day";
			$result = $db->sql_query($sql);
			if( $row = $db->sql_fetchrow($result))
			{
				$matches = $row['matches'];
				$db->sql_freeresult($result);
			}
			else
			{
				// Matchday doesnt exist
				$matches = 0;
			}
		}
		for($i = 1; $i<= $matches; $i++)
		{
			$sqlup = 'REPLACE INTO ' . FOOTB_BETS . " VALUES($season, $league, $match_id, $user_id, '', '', 0)";
			$resultup = $db->sql_query($sqlup);
			$match_id++;
			$count_updates++;
		}
	}
	return $count_updates;
}

// Close matchday
/**
* Close all open matches.
*/
function close_open_matchdays()
{
	global $db, $lang, $config;

	$local_board_time = time() + (($config['board_timezone'] - $config['football_host_timezone']) * 3600); 
	$sql = 'SELECT * FROM ' . FOOTB_MATCHDAYS . " WHERE status = 0 AND delivery_date < FROM_UNIXTIME('$local_board_time')";
	$result = $db->sql_query($sql);
	$toclose = $db->sql_fetchrowset($result);
	$db->sql_freeresult($result);
	foreach ($toclose as $close)
	{
		// Matchday to close
		$season = $close['season'];
		$league = $close['league'];
		$matchday = $close['matchday'];
		$league_info = league_info($season, $league);
		
		if ($league_info['bet_in_time'] == 1)
		{
			$sql_ary = array(
				'status'	=> 1,
			);
			$sql = 'UPDATE ' . FOOTB_MATCHES . '
				SET ' . $db->sql_build_array('UPDATE', $sql_ary) . "
				WHERE season = $season AND league = $league AND matchday = $matchday AND status = 0 AND match_datetime < FROM_UNIXTIME('$local_board_time')";
			$db->sql_query($sql);
			
			// Check remaining open matches
			$first_delivery = first_delivery($season, $league, $close['matchday']);
			if ($first_delivery <> '')
			{
				// Matchday has open matches so set matchday status = 0 and first delivery
				$sql_ary = array(
					'status'		=> 0,
					'delivery_date'	=> first_delivery($season, $league, $close['matchday']),
				);
				$sql = 'UPDATE ' . FOOTB_MATCHDAYS . '
					SET ' . $db->sql_build_array('UPDATE', $sql_ary) . "
					WHERE season = $season AND league = $league AND matchday = $matchday";
				$db->sql_query($sql);
			}
			else
			{
				// Matchday has no open match so close the matchday with setting status = 1
				$sql_ary = array(
					'status'	=> 1,
				);
				$sql = 'UPDATE ' . FOOTB_MATCHDAYS . '
					SET ' . $db->sql_build_array('UPDATE', $sql_ary) . "
					WHERE season = $season AND league = $league AND matchday = $matchday";
				$db->sql_query($sql);
			}
		}
		else
		{
			if ($close['delivery_date_2'] != '')
			{
				// More open matches exists, so shift delivery and status lower Null
				$sql_ary = array(
					'delivery_date'		=> $close['delivery_date_2'],
					'delivery_date_2'	=> $close['delivery_date_3'],
					'delivery_date_3'	=> '',
				);
				$sql = 'UPDATE ' . FOOTB_MATCHDAYS . '
					SET ' . $db->sql_build_array('UPDATE', $sql_ary) . "
					WHERE season = $season AND league = $league AND matchday = $matchday";
				$db->sql_query($sql);

				$sql = 'UPDATE ' . FOOTB_MATCHES . "
					SET status = status + 1 
					WHERE season = $season AND league = $league AND matchday = $matchday AND status <= 0";
				$db->sql_query($sql);
			}
			else
			{
				// Close all matches of the matchday and matchday
				$sql = 'UPDATE ' . FOOTB_MATCHDAYS . "
					SET status = 1 
					WHERE season = $season AND league = $league AND matchday = $matchday";
				$db->sql_query($sql);

				$sql = 'UPDATE ' . FOOTB_MATCHES . "
					SET status = status + 1 
					WHERE season = $season AND league = $league AND matchday = $matchday AND status <= 0";
				$db->sql_query($sql);
			}
		}
		// close extra bets
				$sql = 'UPDATE ' . FOOTB_EXTRA . "
					SET extra_status = extra_status + 1 
					WHERE season = $season AND league = $league AND matchday = $matchday AND extra_status <= 0";
				$db->sql_query($sql);
	}
}

/**
* return matchday wins in array.
*/
function matchday_wins($season, $league)
{
	global $db, $lang;
	$matchday_wins = array();

	$sql = 'SELECT * FROM ' . FOOTB_LEAGUES . " WHERE season = $season AND league = $league";
	$result = $db->sql_query($sql);
	if ($row = $db->sql_fetchrow($result))
	{
		$matchday_wins = explode(';',"0;" . $row['win_matchday']);
		$db->sql_freeresult($result);
	}
	return $matchday_wins;
}

/**
* return season wins in array.
*/
function season_wins($season, $league, $matchday)
{
	global $db, $lang;
	$season_wins = array();
	$season_user_wins = array();
	$league_info = league_info($season, $league);
	if (sizeof($league_info))
	{
		$season_wins = explode(';',"0;" . $league_info['win_season']);
		$maxmatchday = $league_info['matchdays'];
	}
	else
	{
		$season_user_wins[0]['win'] =  0;
		return $season_user_wins;
	}

	$sql = 'SELECT
				1 AS rank,
				user_id,
				SUM(points) AS points
			FROM  ' . FOOTB_RANKS . "
			WHERE season = $season 
				AND league = $league 
				AND matchday <= $matchday 
				AND status IN (2,3)
			GROUP by user_id
			ORDER BY points DESC, user_id ASC
			";
	$result = $db->sql_query($sql);
	$ranking_ary = array();
	while( $row = $db->sql_fetchrow($result))
	{
		$ranking_ary[$row['user_id']] = $row;
	}
	$db->sql_freeresult($result);

	if ( sizeof($ranking_ary) > 0 )
	{
		$sql = 'SELECT
					eb.user_id,
					SUM(eb.bet_points) AS points
				FROM  ' . FOOTB_EXTRA . ' AS e
				INNER JOIN ' . FOOTB_EXTRA_BETS . " AS eb ON (eb.season = e.season and eb.league = e.league and eb.extra_no = e.extra_no)
				WHERE e.season = $season
					AND e.league = $league
					AND e.matchday <> e.matchday_eval
					AND e.matchday_eval <= $matchday 
					AND e.extra_status > 1
				GROUP BY eb.user_id"; 
		
		$result = $db->sql_query($sql);
		while( $row = $db->sql_fetchrow($result))
		{
			$ranking_ary[$row['user_id']]['points'] += $row['points'];
		}
		$db->sql_freeresult($result);
		// sort the ranking by points
		usort($ranking_ary, '_sort_points');
	
		$last_points = -1;
		$last_rank = 1;
		$equal_rank = array();
		$money = array();
		$i = 0; 
		foreach( $ranking_ary AS $curr_user => $curr_rank)
		{
			if ( $curr_rank['points'] != $last_points)
			{
				$ranking_ary[$curr_user]['rank'] = $i + 1;
				$last_rank = $i + 1;
				$equal_rank[$last_rank] = 1;
				$last_points = $curr_rank['points'];
				if ($last_rank < sizeof($season_wins))
				{
					$money[$last_rank] = $season_wins[$last_rank];
				}
				else
				{
					$money[$last_rank] = 0;
				}
			}
			else
			{
				$ranking_ary[$curr_user]['rank'] = $last_rank;
				$equal_rank[$last_rank] += 1;
				if ($i + 1 < sizeof($season_wins))
				{
					$money[$last_rank] += $season_wins[$i + 1];
				}
			}
			$i++;
		}
		foreach( $ranking_ary AS $curr_rank)
		{
			if ( round($money[$curr_rank['rank']] / $equal_rank[$curr_rank['rank']], 2) <> 0 )
			{
				$season_user_wins[$curr_rank['user_id']]['win'] = round($money[$curr_rank['rank']] / $equal_rank[$curr_rank['rank']], 2);
			}
			else
			{
				$season_user_wins[$curr_rank['user_id']]['win'] = 0;
			}
			$season_user_wins[$curr_rank['user_id']]['rank'] = $curr_rank['rank'];
		}
		return $season_user_wins;
	}
	else
	{
		$season_user_wins[0]['win'] =  0;
		return $season_user_wins;
	}
}

/**
* return win most hits in array.
*/
function win_user_most_hits($season, $league, $matchday)
{
	global $db, $lang;

	$sql = 'SELECT * FROM ' . FOOTB_LEAGUES . " WHERE season = $season AND league = $league";
	$result = $db->sql_query($sql);

	if ($row = $db->sql_fetchrow($result))
	{
		$win_most_hits = $row['win_result'];
		$last_matchday = $row['matchdays'];
		$db->sql_freeresult($result);
	}

	$sql = 'SELECT
			b.user_id AS userid,
			COUNT(*) AS hits
			FROM ' . FOOTB_MATCHES . ' AS m
			LEFT JOIN ' . FOOTB_BETS . " AS b ON (b.season = m.season AND b.league = m.league AND b.match_no = m.match_no)
			WHERE m.season = $season AND m.league = $league AND m.matchday <= $matchday AND m.goals_home = b.goals_home AND
				m.goals_guest = b.goals_guest AND m.status IN (2,3)
			GROUP BY b.user_id
			ORDER BY hits DESC
			";
	$result = $db->sql_query($sql);

	$last_count_hits = -1;
	$count_user = 0;
	$userid_ary = array();
	$hits = array();
	$rank = array();
	$equal_rank = array();
	$win_pos_most_hits = array();
	while( $row = $db->sql_fetchrow($result))
	{
		$userid_ary[$count_user] = $row['userid'];
		$hits[$count_user] = $row['hits'];
		if ($count_user == 0)
		{
			$rank[$count_user] = 1;
			$equal_rank[$rank[$count_user]] = 1;
			$win_pos_most_hits[$rank[$count_user]] = $win_most_hits;
		}
		else
		{
			if ( $row['hits'] != $last_count_hits)
			{
				$rank[$count_user] = $count_user + 1;
				$equal_rank[$rank[$count_user]] = 1;
				$win_pos_most_hits[$rank[$count_user]] = 0;
			}
			else
			{
				$rank[$count_user] = $rank[$count_user-1];
				$equal_rank[$rank[$count_user]] += 1;
			}
		}
		$last_count_hits = $row['hits'];
		$count_user++;
	}
	$db->sql_freeresult($result);
	$win_user_most_hits = array();
	for($i = 0; $i < $count_user; $i++)
	{
		$win_user_most_hits[$userid_ary[$i]]['direct_hit'] = $hits[$i];
		if ($matchday == $last_matchday)
		{
			if (round($win_pos_most_hits[$rank[$i]]/$equal_rank[$rank[$i]],2) <> 0)
			{
				$win_user_most_hits[$userid_ary[$i]]['win'] = round($win_pos_most_hits[$rank[$i]]/$equal_rank[$rank[$i]],2);
			}
			else
			{
				$win_user_most_hits[$userid_ary[$i]]['win'] =  0;
			}
		}
		else
		{
			$win_user_most_hits[$userid_ary[$i]]['win'] =  0;
		}
	}
	if ($count_user > 0)
	{
		return $win_user_most_hits;
	}
	else
	{
		$win_user_most_hits[0]['win'] =  0;
		return $win_user_most_hits;
	}
}

/**
* return win most hits away in array.
*/
function win_user_most_hits_away($season, $league, $matchday)
{
	global $db, $lang;

	$sql = 'SELECT * FROM ' . FOOTB_LEAGUES . " WHERE season = $season AND league = $league";
	$result = $db->sql_query($sql);

	if ($row = $db->sql_fetchrow($result))
	{
		$win_most_hits_away = $row['win_result_02'];
		$last_matchday = $row['matchdays'];
		$db->sql_freeresult($result);
	}

	$sql = 'SELECT
			b.user_id AS userid,
			SUM(IF(m.goals_home <= m.goals_guest,1,0)) AS hits_away
			FROM ' . FOOTB_MATCHES . ' AS m
			LEFT JOIN ' . FOOTB_BETS . " AS b ON (b.season = m.season AND b.league = m.league AND b.match_no = m.match_no)
			WHERE m.season = $season AND m.league = $league AND m.matchday <= $matchday AND m.goals_home = b.goals_home AND
					m.goals_guest = b.goals_guest AND m.status IN (2,3)
			GROUP BY b.user_id
			ORDER BY hits_away DESC
			";
	$result = $db->sql_query($sql);

	$last_hits_away = -1;
	$count_user = 0;
	$userid_ary = array();
	$hits_away = array();
	$rank = array();
	$equal_rank = array();
	$win_pos_most_hits_away = array();
	while( $row = $db->sql_fetchrow($result))
	{
		$userid_ary[$count_user] = $row['userid'];
		$hits_away[$count_user] = $row['hits_away'];
		if ($count_user == 0)
		{
			$rank[$count_user] = 1;
			$equal_rank[$rank[$count_user]] = 1;
			$win_pos_most_hits_away[$rank[$count_user]] = $win_most_hits_away;
		}
		else
		{
			if ( $row['hits_away'] != $last_hits_away)
			{
				$rank[$count_user] = $count_user + 1;
				$equal_rank[$rank[$count_user]] = 1;
				$win_pos_most_hits_away[$rank[$count_user]] = 0;
			}
			else
			{
				$rank[$count_user] = $rank[$count_user-1];
				$equal_rank[$rank[$count_user]] += 1;
			}
		}
		$last_hits_away = $row['hits_away'];
		$count_user++;
	}
	$db->sql_freeresult($result);
	$win_user_most_hits_away = array();
	for($i = 0; $i < $count_user; $i++)
	{
		$win_user_most_hits_away[$userid_ary[$i]]['direct_hit'] = $hits_away[$i];
		if ($matchday == $last_matchday)
		{
			if (round($win_pos_most_hits_away[$rank[$i]]/$equal_rank[$rank[$i]],2) <> 0)
			{
				$win_user_most_hits_away[$userid_ary[$i]]['win'] = round($win_pos_most_hits_away[$rank[$i]]/$equal_rank[$rank[$i]],2);
			}
			else
			{
				$win_user_most_hits_away[$userid_ary[$i]]['win'] =  0;
			}
		}
		else
		{
			$win_user_most_hits_away[$userid_ary[$i]]['win'] =  0;
		}
	}
	if ($count_user > 0)
	{
		return $win_user_most_hits_away;
	}
	else
	{
		$win_user_most_hits_away[0]['win'] =  0;
		return $win_user_most_hits_away;
	}
}

/**
* return colorstyle to status.
*/
function color_style($status)
{
	switch ($status)
	{
		case 2:
				$colorstyle = 'color_provisionally';
			break;
		case 3:
				$colorstyle = 'color_finally';
			break;
		case 4:
		case 5:
		case 6:
				$colorstyle = 'color_not_rated';
			break;
		default:
				$colorstyle = '';
			break;
	}
	return $colorstyle;
}


/**
* color text on match status.
*/
function color_match($text, $status)
{
	switch($status)
	{
		case 2:
				$colormatch = "<strong style='color:red'>". $text. '</strong>';
			break;
		case 3:
				$colormatch = "<strong style='color:green'>". $text. '</strong>';
			break;
		case 4:
		case 5:
		case 6:
				$colormatch = "<strong style='color:purple'>". $text. '</strong>';
			break;
		default:
				$colormatch = $text;
			break;
	}
	return $colormatch;
}

/**
* color text on points status.
*/
function color_points($text, $status)
{
	switch($status)
	{
		case 2:
				$color_points = "<strong style='color:red'>". $text. '</strong>';
			break;
		case 3:
				$color_points = "<strong style='color:green'>". $text. '</strong>';
			break;
		default:
				$color_points = $text;
			break;
	}
	return $color_points;
}

/**
* get table order on teams with equal points.
*/
function get_order_team_compare($team_ary, $season, $league, $group, $ranks, $matchday = 999, $first = true)
{
	global $db;
	$sql = "
		SELECT
		t.*,
		SUM(IF(m.team_id_home = t.team_id, 
				IF(goals_home + 0 > goals_guest, 3, IF(goals_home = goals_guest, 1, 0)), 
				IF(goals_home + 0 < goals_guest, 3, IF(goals_home = goals_guest, 1, 0))
			)
		) - IF(t.team_id = 20 AND t.season = 2011 AND $matchday > 7, 2, 0) AS points,
		SUM(IF(m.team_id_home = t.team_id, goals_home - goals_guest, goals_guest - goals_home)) AS goals_diff,
		SUM(IF(m.team_id_home = t.team_id, goals_home, goals_guest)) AS goals
		FROM " . FOOTB_TEAMS . ' AS t
		LEFT JOIN ' . FOOTB_MATCHES . " AS m ON (m.season = t.season AND m.league = t.league AND 
												(m.team_id_home = t.team_id OR m.team_id_guest = t.team_id) AND m.group_id = t.group_id)
		WHERE t.season = $season 
		AND t.league = $league 
		AND m.matchday <= $matchday 
		AND m.status IN (2,3,5,6)
		AND m.group_id = '$group'
		AND (m.team_id_home='" . implode("' OR m.team_id_home='", $team_ary) . "') 
		AND (m.team_id_guest='" . implode("' OR m.team_id_guest='", $team_ary) . "')
		GROUP BY t.team_id
		ORDER BY t.group_id ASC, points DESC, goals_diff DESC, goals DESC";

		$result = $db->sql_query($sql);

	$tmp = array();
	$rank_ary = array();
	$rank = 0;
	$last_points = 0;
	$last_goals_diff = 0;
	$last_goals = 0;

	while( $row = $db->sql_fetchrow($result))
	{
		if ($last_points <> $row['points'] OR $last_goals_diff <> $row['goals_diff'] OR $last_goals <> $row['goals'])
		{
			$rank++;
		}
		$rank_ary[$rank][]=$row['team_id']; 
		$last_points = $row['points'];
		$last_goals_diff = $row['goals_diff'];
		$last_goals = $row['goals'];
	}
	foreach($rank_ary as $rank => $teams)
	{
		if(count($teams) > 1)
		{
			if ($first)
			{
				// Compare teams with equal ranks
				$teams = get_order_team_compare($teams, $season, $league, $group, $ranks, $matchday, false);
			}
			else
			{
				// Second compare is still equal, so look on total rank
				$teams = array_intersect($ranks, $teams);
			}
		}
		foreach($teams as $key => $team)
		{
			$tmp[] = $team;
		}
	}
    return (sizeof($tmp) == 0) ? $team_ary: $tmp;
}  

/**
* determine team items from formula.
*/
function get_team($season, $league, $matchnumber, $field, $formula)
{
	global $db, $lang, $user;
	$first_letter = substr($formula, 0, 1);
	$para = substr($formula, 2, 7);
	$para_ary = explode(";",$para);

	switch($first_letter)
	{
		case '3':
			// 3. Place Euro 2106
			$groups = substr($para_ary[0], 0, 5);
			$sql = '
				SELECT
				SUM(1) AS matches,
				SUM(IF(m.status = 3, 1, 0)) AS played
				FROM ' . FOOTB_MATCHES . " AS m
				WHERE m.season = $season AND m.league = $league AND m.group_id <> '' 
				GROUP BY m.group_id
			";
			$result = $db->sql_query($sql);

			if ( $row = $db->sql_fetchrow($result))
			{
				if ($row['matches'] == $row['played'])
				{
					$rank = 0;
					// Get table-information
					$sql = "SELECT
							t.*,
							SUM(1) AS matches,
							SUM(IF((m.team_id_home = t.team_id), IF(goals_home + 0 > goals_guest, 1, 0), IF(goals_home + 0 < goals_guest, 1, 0))) AS win,
							SUM(IF(goals_home = goals_guest, 1, 0)) AS draw,
							SUM(IF((m.team_id_home = t.team_id), IF(goals_home + 0 < goals_guest, 1, 0), IF(goals_home + 0 > goals_guest, 1, 0))) AS lost,
							SUM(IF(m.team_id_home = t.team_id, 
									IF(goals_home + 0 > goals_guest, 3, IF(goals_home = goals_guest, 1, 0)), 
									IF(goals_home + 0 < goals_guest, 3, IF(goals_home = goals_guest, 1, 0))
								)
							) AS points,
							SUM(IF(m.team_id_home = t.team_id, goals_home - goals_guest , goals_guest - goals_home)) AS goals_diff,
							SUM(IF(m.team_id_home = t.team_id, goals_home , goals_guest)) AS goals,
							SUM(IF(m.team_id_home = t.team_id, goals_guest , goals_home)) AS goals_against
						FROM " . FOOTB_TEAMS . ' AS t
						LEFT JOIN ' . FOOTB_MATCHES . " AS m ON (m.season = t.season AND m.league = t.league 
																AND (m.team_id_home = t.team_id OR m.team_id_guest = t.team_id) AND m.group_id = t.group_id)
						WHERE t.season = $season 
							AND t.league = $league 
							AND m.status IN (2,3,5,6)
						GROUP BY t.team_id
						ORDER BY t.group_id ASC, points DESC, goals_diff DESC, goals DESC, t.team_name ASC";
						
					$result = $db->sql_query($sql);

					$table_ary = array();
					$points_ary = array();
					$ranks_ary = array();
					$third_team = array();
					$third_group = array();
					$points3 = array();
					$diff3 = array();
					$goals3 = array();
					$rank = 0;
					while( $row = $db->sql_fetchrow($result))
					{
						$rank++;
						$table_ary[$row['team_id']] = $row;
						$points_ary[$row['group_id']][$row['points']][]=$row['team_id']; 
						$ranks_ary[] = $row['team_id'];
					}

					foreach($points_ary as $group_id => $points)
					{
						$rank = 0;

						//sort on points descending
						krsort($points);

						foreach($points as $point => $teams)
						{
							if(count($teams) > 1)
							{
								// Compare teams with equal points
								$teams = get_order_team_compare($teams, $season, $league, $group_id, $ranks_ary);
							}
							foreach($teams as $key => $team)
							{
								$row = $table_ary[$team];
								$rank++;
								if ($rank == 3)
								{
									$points3[$team] = $row['points'];
									$diff3[$team] = $row['goals_diff'];
									$goals3[$team] = $row['goals'];
									$third_team[$team]= $team;
									$third_group[$team]= $row['group_id'];
								}
							}		
						}     
					}
					// Sort 3. Place on points, diff, goals
					array_multisort($points3, SORT_DESC, $diff3, SORT_DESC, $goals3, SORT_DESC, $third_team, $third_group);
					$qualified_groups = array();
					for($i = 0; $i < 4; $i++)
					{
						$qualified_groups[$i] = $third_group[$i];
						$team_of[$third_group[$i]] = $third_team[$i];
					}
					asort($qualified_groups);
					$qualified_groups_string = '';
					foreach($qualified_groups as $key => $letter)
					{
						$qualified_groups_string .= $letter;
					}
					$modus = array('ABCD' => 'CDAB', 'ABCE' => 'CABE', 'ABCF' => 'CABF', 'ABDE' => 'DABE', 'ABDF' => 'DABF', 
									'ABEF' => 'EABF', 'ACDE' => 'CDAE', 'ACDF' => 'CDAF', 'ACEF' => 'CAFE', 'ADEF' => 'DAFE', 
									'BCDE' => 'CDBE', 'BCDF' => 'CDBF', 'BCEF' => 'ECBF', 'BDEF' => 'EDBF', 'CDEF' => 'CDFE');
					$form_para = array('CDE', 'ACD', 'ABF', 'BEF');
					$mode = $modus[$qualified_groups_string];
					for($i = 0; $i < 4; $i++)
					{
						$team = $team_of[substr($mode, $i, 1)];
						
						$sqlup = 'UPDATE ' . FOOTB_MATCHES . " SET team_id_guest = $team WHERE season = $season AND league = $league AND formula_guest = '3 $form_para[$i]'";
						$resultup = $db->sql_query($sqlup);
						$sqlup = 'UPDATE ' . FOOTB_TEAMS . ' SET matchday = (SELECT max(matchday) FROM ' . FOOTB_MATCHES . " 
																			 WHERE season = $season AND league = $league AND (team_id_home= $team OR team_id_guest = $team))
								  WHERE season = $season AND league = $league AND team_id = $team";
						$resultup = $db->sql_query($sqlup);
						if ($form_para[$i] == $groups)
						{
							$team_id = $team;
							$row = $table_ary[$team];
							$team_symbol = $row['team_symbol'];
							$team_name = $row['team_name'];
							$team_name_short = $row['team_name_short'];
						}
					}
					return $team_symbol . '#' . $team_id . '#' . $team_name . '#' . $team_name_short;
					
				}
				else
				{
					return '#0#' . '3. ' . sprintf($user->lang['GROUP']) . ' ' . $groups . '#' . '3. ' . sprintf($user->lang['GROUP']) . ' ' . $groups;
				}
			}
			else
			{
				return '#0#' . '3. ' . sprintf($user->lang['GROUP']) . ' ' . $groups . '#' . '3. ' . sprintf($user->lang['GROUP']) . ' ' . $groups;
			}
			break;
		case 'D':
			// Drawing
			return '#0#' . sprintf($user->lang['DRAWING']) . '#' . sprintf($user->lang['DRAWING']);
			break;
		case 'G':
			// GROUP
			$group = substr($para_ary[0], 0, 1);
			$place = substr($para_ary[0], 1, 1);
			$sql = '
				SELECT
				SUM(1) AS matches,
				SUM(IF(m.status = 3, 1, 0)) AS played
				FROM ' . FOOTB_MATCHES . " AS m
				WHERE m.season = $season AND m.league = $league AND m.group_id = '$group' 
				GROUP BY m.group_id
			";
			$result = $db->sql_query($sql);

			if ( $row = $db->sql_fetchrow($result))
			{
				if ($row['matches'] == $row['played'])
				{
					$rank = 0;
					$sql = '
						SELECT
						t.*,
						SUM(IF(m.team_id_home = t.team_id, 
								IF(goals_home + 0 > goals_guest, 3, IF(goals_home = goals_guest, 1, 0)), 
								IF(goals_home + 0 < goals_guest, 3, IF(goals_home = goals_guest, 1, 0))
							)
						) AS points,
						SUM(IF(m.team_id_home = t.team_id, goals_home - goals_guest, goals_guest - goals_home)) AS goals_diff,
						SUM(IF(m.team_id_home = t.team_id, goals_home, goals_guest)) AS goals,
						SUM(IF(m.team_id_home = t.team_id, goals_guest, goals_home)) AS goals_get
						FROM ' . FOOTB_TEAMS . ' AS t
						LEFT JOIN ' . FOOTB_MATCHES . " AS m ON (m.season = t.season AND m.league = t.league AND 
																(m.team_id_home = t.team_id OR m.team_id_guest = t.team_id) AND m.group_id = t.group_id)
						WHERE t.season = $season AND t.league = $league AND m.group_id = '$group'
						GROUP BY t.team_id
						ORDER BY points DESC, goals_diff DESC, goals DESC
					";
					$result = $db->sql_query($sql);
					$table_ary = array();
					$points_ary = array();
					$ranks_ary = array();
					while( $row = $db->sql_fetchrow($result))
					{
						$table_ary[$row['team_id']] = $row;
						$points_ary[$row['points']][]=$row['team_id'];
						$ranks_ary[] = $row['team_id'];
					}
					$db->sql_freeresult($result);

					//sort on points descending
					krsort($points_ary);

					foreach($points_ary as $point => $teams)
					{
						if(count($teams) > 1)
						{
							// Compare teams with equal points
							$teams = get_order_team_compare($teams, $season, $league, $group, $ranks_ary);
						}
						foreach($teams as $key => $team)
						{
							$row = $table_ary[$team];
							$rank++;
							if ($rank == $place)
							{
								$sqlup = 'UPDATE ' . FOOTB_MATCHES . " SET $field = $team WHERE season = $season AND league = $league AND match_no = $matchnumber";
								$resultup = $db->sql_query($sqlup);
								$sqlup = 'UPDATE ' . FOOTB_TEAMS . ' SET matchday = (SELECT max(matchday) FROM ' . FOOTB_MATCHES . " 
																					 WHERE season = $season AND league = $league AND (team_id_home= $team OR team_id_guest = $team))
										  WHERE season = $season AND league = $league AND team_id = $team";
								$resultup = $db->sql_query($sqlup);
								return $row['team_symbol'] . '#' . $team . '#' . $row['team_name'] . '#' . $row['team_name_short'];
							}
						}
					}
				}
				else
				{
					return '#0#' . $place . '. ' . sprintf($user->lang['GROUP']) . ' ' . $group . '#' . $place . '. ' . sprintf($user->lang['GROUP']) . ' ' . $group;
				}
			}
			else
			{
				return '#0#' . $place . '. ' . sprintf($user->lang['GROUP']) . ' ' . $group . '#' . $place . '. ' . sprintf($user->lang['GROUP']) . ' ' . $group;
			}
			break;
		case 'L':
			// Looser
			switch(sizeof($para_ary))
			{
				case 1:
					$sql = 'SELECT
						m.status,
						m.team_id_home AS home_id,
						m.team_id_guest AS guest_id,
						t1.team_symbol AS home_symbol,
						t2.team_symbol AS guest_symbol,
						t1.team_name AS home_name,
						t2.team_name AS guest_name,
						t1.team_name_short AS home_sname,
						t2.team_name_short AS guest_sname,
						m.goals_overtime_home,
						m.goals_overtime_guest,
						m.goals_home,
						m.goals_guest
						FROM  ' . FOOTB_MATCHES . ' AS m
						LEFT JOIN ' . FOOTB_TEAMS . ' AS t1 ON (t1.season = m.season AND t1.league = m.league AND t1.team_id = m.team_id_home)
						LEFT JOIN ' . FOOTB_TEAMS . " AS t2 ON (t2.season = m.season AND t2.league = m.league AND t2.team_id = m.team_id_guest)
						WHERE m.season = $season AND m.league = $league AND m.match_no = $para_ary[0]";
					$result = $db->sql_query($sql);

					if ($row = $db->sql_fetchrow($result))
					{
						if ((3 == $row['status']) OR (6 == $row['status']))
						{
							if ($row['goals_home'] > $row['goals_guest'] OR $row['goals_overtime_home'] > $row['goals_overtime_guest'])
							{
								$new_id = $row['guest_id'];
								$sqlup = 'UPDATE ' . FOOTB_MATCHES . " SET $field = $new_id WHERE season = $season AND league = $league AND match_no = $matchnumber";
								$resultup = $db->sql_query($sqlup);
								return $row['guest_symbol'] . '#' . $row['guest_id'] . '#' . $row['guest_name'] . '#' . $row['guest_sname'];
							}
							if ($row['goals_home'] < $row['goals_guest'] OR $row['goals_overtime_home'] < $row['goals_overtime_guest'])
							{
								$new_id = $row['home_id'];
								$sqlup = 'UPDATE ' . FOOTB_MATCHES . " SET $field = $new_id WHERE season = $season AND league = $league AND match_no = $matchnumber";
								$resultup = $db->sql_query($sqlup);
								return $row['home_symbol'] . '#' . $row['home_id'] . '#' . $row['home_name'] . '#' . $row['home_sname'];
							}
						}
						else
						{
							return '#0#' . sprintf($user->lang['LOOSER_MATCH_NO']) . $para_ary[0] . '#' . sprintf($user->lang['LOOSER']) . ' ' . $para_ary[0];
						}
					}
					break;
				default:
					return '#0#' . sprintf($user->lang['DRAWING']) . '#' . sprintf($user->lang['DRAWING']);
					break;
			}
			break;
		case 'W':
			// Winner
			switch(sizeof($para_ary))
			{
				case 1:
					$sql = 'SELECT
						m.status,
						m.team_id_home AS home_id,
						m.team_id_guest AS guest_id,
						t1.team_symbol AS home_symbol,
						t2.team_symbol AS guest_symbol,
						t1.team_name AS home_name,
						t2.team_name AS guest_name,
						t1.team_name_short AS home_sname,
						t2.team_name_short AS guest_sname,
						m.goals_overtime_home,
						m.goals_overtime_guest,
						m.goals_home,
						m.goals_guest
						FROM  ' . FOOTB_MATCHES . ' AS m
						LEFT JOIN ' . FOOTB_TEAMS . ' AS t1 ON (t1.season = m.season AND t1.league = m.league AND t1.team_id = m.team_id_home)
						LEFT JOIN ' . FOOTB_TEAMS . " AS t2 ON (t2.season = m.season AND t2.league = m.league AND t2.team_id = m.team_id_guest)
						WHERE m.season = $season AND m.league = $league AND m.match_no = $para_ary[0]";
					$result = $db->sql_query($sql);

					if ($row = $db->sql_fetchrow($result))
					{
						if ((3 == $row['status']) OR (6 == $row['status']))
						{
							if ($row['goals_home'] > $row['goals_guest'] OR $row['goals_overtime_home'] > $row['goals_overtime_guest'])
							{
								$new_id = $row['home_id'];
								$sqlup = 'UPDATE ' . FOOTB_MATCHES . " SET $field = $new_id WHERE season = $season AND league = $league AND match_no = $matchnumber";
								$resultup = $db->sql_query($sqlup);
								return $row['home_symbol'] . '#' . $row['home_id'] . '#' . $row['home_name'] . '#' . $row['home_sname'];
							}
							if ($row['goals_home'] < $row['goals_guest'] OR $row['goals_overtime_home'] < $row['goals_overtime_guest'])
							{
								$new_id = $row['guest_id'];
								$sqlup = 'UPDATE ' . FOOTB_MATCHES . " SET $field = $new_id WHERE season = $season AND league = $league AND match_no = $matchnumber";
								$resultup = $db->sql_query($sqlup);
								return $row['guest_symbol'] . '#' . $row['guest_id'] . '#' . $row['guest_name'] . '#' . $row['guest_sname'];
							}
						}
						else
						{
							if ($row['home_sname'] != '' AND $row['guest_sname'] != '')
							{
								return '#0#' . $row['home_sname'] . ' / ' . $row['guest_sname'] . '#' . $row['home_sname'] . '+' . $row['guest_sname'];
							}
							else
							{
								return '#0#' . sprintf($user->lang['WINNER_MATCH_NO']) . $para_ary[0] . '#' . sprintf($user->lang['WINNER']) . ' ' . $para_ary[0];
							}
						}
					}
					break;
				case 2:

					$sql = 'SELECT
					m.status,
					m.team_id_home AS home_id,
					m.team_id_guest AS guest_id,
					t1.team_symbol AS home_symbol,
					t2.team_symbol AS guest_symbol,
					t1.team_name AS home_name,
					t2.team_name AS guest_name,
					t1.team_name_short AS home_sname,
					t2.team_name_short AS guest_sname,
					m.goals_overtime_home,
					m.goals_overtime_guest,
					m.goals_home,
					m.goals_guest
					FROM  ' . FOOTB_MATCHES . ' AS m
					LEFT JOIN ' . FOOTB_TEAMS . ' AS t1 ON (t1.season = m.season AND t1.league = m.league AND t1.team_id = m.team_id_home)
					LEFT JOIN ' . FOOTB_TEAMS . " AS t2 ON (t2.season = m.season AND t2.league = m.league AND t2.team_id = m.team_id_guest)
					WHERE m.season = $season AND m.league = $league AND (m.match_no = $para_ary[0] OR m.match_no = $para_ary[1])
					ORDER BY m.match_no ASC";
					$result = $db->sql_query($sql);

					if ($firstleg = $db->sql_fetchrow($result))
					{
						if ($replay = $db->sql_fetchrow($result))
						{
							if (((3 == $firstleg['status']) OR (6 == $firstleg['status'])) AND ((3 == $replay['status']) OR (6 == $replay['status'])))
							{
								if ($firstleg['home_id'] == $replay['guest_id'] AND $firstleg['guest_id'] == $replay['home_id'])
								{
									if ($firstleg['goals_home'] + $replay['goals_guest'] > $firstleg['goals_guest'] + $replay['goals_home'] OR $replay['goals_overtime_guest'] > $replay['goals_overtime_home'] OR
										($firstleg['goals_home'] + $replay['goals_guest'] == $firstleg['goals_guest'] + $replay['goals_home'] AND $replay['goals_guest'] > $firstleg['goals_guest']))
									{
										$new_id = $firstleg['home_id'];
										$sqlup = 'UPDATE ' . FOOTB_MATCHES . " SET $field = $new_id WHERE season = $season AND league = $league AND match_no = $matchnumber";
										$resultup = $db->sql_query($sqlup);
										return $firstleg['home_symbol'] . '#' . $firstleg['home_id'] . '#' . $firstleg['home_name'] . '#' . $firstleg['home_sname'];
									}
									if ($firstleg['goals_home'] + $replay['goals_guest'] < $firstleg['goals_guest'] + $replay['goals_home'] OR $replay['goals_overtime_guest'] < $replay['goals_overtime_home'] OR
										($firstleg['goals_home'] + $replay['goals_guest'] == $firstleg['goals_guest'] + $replay['goals_home'] AND $firstleg['goals_guest'] > $replay['goals_guest']))
									{
										$new_id = $firstleg['guest_id'];
										$sqlup = 'UPDATE ' . FOOTB_MATCHES . " SET $field = $new_id WHERE season = $season AND league = $league AND match_no = $matchnumber";
										$resultup = $db->sql_query($sqlup);
										return $firstleg['guest_symbol'] . '#' . $firstleg['guest_id'] . '#' . $firstleg['guest_name'] . '#' . $firstleg['guest_sname'];
									}
								}
								else if ($firstleg['home_id'] == $replay['home_id'] AND $firstleg['guest_id'] == $replay['guest_id'])
								{
									if ($firstleg['goals_home'] + $replay['goals_home'] <= $firstleg['goals_guest'] + $replay['goals_guest'] OR $replay['goals_overtime_home'] < $replay['goals_overtime_guest'])
									{
										$new_id = $firstleg['guest_id'];
										$sqlup = 'UPDATE ' . FOOTB_MATCHES . " SET $field = $new_id WHERE season = $season AND league = $league AND match_no = $matchnumber";
										$resultup = $db->sql_query($sqlup);
										return $firstleg['guest_symbol'] . '#' . $firstleg['guest_id'] . '#' . $firstleg['guest_name'] . '#' . $firstleg['guest_sname'];
									}
									else
									{
										$new_id = $firstleg['home_id'];
										$sqlup = 'UPDATE ' . FOOTB_MATCHES . " SET $field = $new_id WHERE season = $season AND league = $league AND match_no = $matchnumber";
										$resultup = $db->sql_query($sqlup);
										return $firstleg['home_symbol'] . '#' . $firstleg['home_id'] . '#' . $firstleg['home_name'] . '#' . $firstleg['home_sname'];
									}
								}
								else
								{
									return '#0#' . sprintf($user->lang['MATCH_ERROR']) . '#' . sprintf($user->lang['FORMULA']) . '!';
								}
							}
							else
							{
								if ($firstleg['home_sname'] != '' AND $firstleg['guest_sname'] != '')
								{
									return '#0#' . $firstleg['home_sname'] . ' / ' . $firstleg['guest_sname'] . '#' . $firstleg['home_sname'] . '+' . $firstleg['guest_sname'];
								}
								else
								{
									return '#0#' . sprintf($user->lang['WINNER_MATCH_NO']) . ' ' . $para_ary[0] . '+' . $para_ary[1] . '#S. ' . $para_ary[0] . '+' . $para_ary[1];
								}
							}
						}
						else
						{
							return '#0#' . sprintf($user->lang['SEC_LEG_ERROR']) . '#' . sprintf($user->lang['FORMULA']) . '!';
						}
					}
					else
					{
						return '#0#' . sprintf($user->lang['FIRSTLEG_ERROR']) . '#' . sprintf($user->lang['FORMULA']) . '!';
					}
					break;
				default:
					return '#0#Los#Los';
					break;
			}
			break;
		default:
			return '#0#' . sprintf($user->lang['DRAWING']) . '#' . sprintf($user->lang['DRAWING']);
			break;
	}
}

/**
* KO-matches: Set team to next round.
*/
function ko_next_round($season, $league, $matchday_from, $matchday_to, $matchday_new)
{
	global $db, $user;
	$sql = 'SELECT
		t.team_id,
		t.team_name,
		SUM(1) AS matches,
		SUM(IF(m.team_id_home = t.team_id,
				IF(goals_overtime_home + goals_overtime_guest > 0,
					goals_overtime_home,
					goals_home
				),
				IF(goals_overtime_home + goals_overtime_guest > 0,
					goals_overtime_guest,
					goals_guest
				)
			)
		) AS goals,
		SUM(IF(m.team_id_home = t.team_id,
				IF(goals_overtime_home + goals_overtime_guest > 0,
					goals_overtime_guest,
					goals_guest
				),
				IF(goals_overtime_home + goals_overtime_guest > 0,
					goals_overtime_home,
					goals_home
				)
			)
		) AS goals_get,
		SUM(IF(m.team_id_home = t.team_id,
				0,
				IF(goals_overtime_home + goals_overtime_guest > 0,
					goals_overtime_guest,
					goals_guest
				)
			)
		) AS goals_away,
		SUM(IF(m.team_id_home = t.team_id,
				IF(goals_overtime_home + goals_overtime_guest > 0,
					goals_overtime_guest,
					goals_guest
				),
				0
			)
		) AS goals_away_opp
		FROM ' . FOOTB_TEAMS . ' AS t
		LEFT JOIN ' . FOOTB_MATCHES . " AS m ON (m.season = t.season AND m.league = t.league AND (m.team_id_home = t.team_id OR m.team_id_guest = t.team_id))
		WHERE t.season = $season AND t.league = $league AND m.matchday >= $matchday_from AND m.matchday <= $matchday_to AND m.status IN (3,6)
		GROUP BY t.team_id";
	$result = $db->sql_query($sql);

	$message = sprintf($user->lang['KO_NEXT']) . ': <br /><br />';
	while ($row = $db->sql_fetchrow($result))
	{
		if (($matchday_to == $matchday_from AND  $row['matches'] == 1) OR ($matchday_to != 0 AND  $row['matches'] == 2))
		{
			if (($row['goals'] > $row['goals_get']) OR
				(($row['goals'] == $row['goals_get']) AND ($row['goals_away'] > $row['goals_away_opp'])))
			{
				$team_id = $row['team_id'];
				$sqlup = 'UPDATE ' . FOOTB_TEAMS . " SET matchday = $matchday_new WHERE season = $season AND league = $league AND team_id = $team_id";
				$resultup = $db->sql_query($sqlup);
				$message .= $row['team_name'] . '<br />';
			}
		}
	}
	$db->sql_freeresult($result);

	$message .= '<br />';
	return $message;
}

/**
* KO-matches: Set groupleaders next round.
*/
function ko_group_next_round($season, $league, $matchday_from, $matchday_to, $matchday_new, $rank, $move_rank, $move_league, $move_matchday)
{
	global $db, $user;
	$sql = '
		SELECT
		t.*,
		SUM(1) AS matches
		FROM ' . FOOTB_TEAMS . ' AS t
		LEFT JOIN ' . FOOTB_MATCHES . " AS m ON (m.season = t.season AND m.league = t.league AND (m.team_id_home = t.team_id OR m.team_id_guest = t.team_id) AND m.group_id = t.group_id)
		WHERE t.season = $season AND t.league = $league AND m.matchday >= $matchday_from AND m.matchday <= $matchday_to AND m.status NOT IN (3,6)
		GROUP BY t.team_id
	";
	$result = $db->sql_query($sql);
	$rowset = $db->sql_fetchrowset($result);
	$db->sql_freeresult($result);

	if (sizeof($rowset) > 0)
	{
		$message = sprintf($user->lang['NO_KO_NEXT']);
		$messag_moved = '';
	}
	else
	{
		$sql = '
			SELECT
			t.*,
			SUM(1) AS matches,
			SUM(IF((m.team_id_home = t.team_id), IF(goals_home + 0 > goals_guest, 1, 0), IF(goals_home + 0 < goals_guest, 1, 0))) AS win,
			SUM(IF(goals_home = goals_guest, 1, 0)) AS draw,
			SUM(IF((m.team_id_home = t.team_id), IF(goals_home + 0 < goals_guest, 1, 0), IF(goals_home + 0 > goals_guest, 1, 0))) AS lose,
			SUM(IF(m.team_id_home = t.team_id, 
					IF(goals_home + 0 > goals_guest, 
						3, 
						IF(goals_home = goals_guest, 
							1, 
							0
						)
					), 
					IF(goals_home + 0 < goals_guest, 
						3, 
						IF(goals_home = goals_guest, 
							1, 
							0
						)
					)
				)
			) AS points,
			SUM(IF(m.team_id_home = t.team_id, goals_home - goals_guest, goals_guest - goals_home)) AS goal_diff,
			SUM(IF(m.team_id_home = t.team_id, goals_home, goals_guest)) AS goals,
			SUM(IF(m.team_id_home = t.team_id, goals_guest, goals_home)) AS goals_get
			FROM ' . FOOTB_TEAMS . ' AS t
			LEFT JOIN ' . FOOTB_MATCHES . " AS m ON (m.season = t.season AND m.league = t.league AND (m.team_id_home = t.team_id OR m.team_id_guest = t.team_id) AND m.group_id = t.group_id)
			WHERE t.season = $season AND t.league = $league AND m.matchday >= $matchday_from AND m.matchday <= $matchday_to AND m.status IN (3,6)
			GROUP BY t.team_id
			ORDER BY group_id ASC,points DESC, goal_diff DESC, goals DESC
		";
		$result = $db->sql_query($sql);
		
		$table_ary = array();
		$points_ary = array();
		$ranks_ary = array();
		while( $row = $db->sql_fetchrow($result))
		{
			$table_ary[$row['team_id']] = $row;
			$points_ary[$row['group_id']][$row['points']][]=$row['team_id']; 
			$ranks_ary[] = $row['team_id'];
		}

		$message = sprintf($user->lang['KO_NEXT_CHECK']) . ': <br /><br />';
		$message .= sprintf($user->lang['KO_NEXT']) . ': <br /><br />';
		$messag_moved = '<br /><br />' .  sprintf($user->lang['KO_MOVED']) . ': <br />';
		$group_id = 'XX';
		foreach($points_ary as $group_id => $points)
		{
			$place = 1;
			foreach($points as $point => $teams)
			{
				if(count($teams) > 1)
				{
					// Compare teams with equal points
					$teams = get_order_team_compare($teams, $season, $league, $group_id, $ranks_ary);
				}
				foreach($teams as $key => $team_id)
				{
					$row = $table_ary[$team_id];

					if ($place <= $rank)
					{
						$sqlup = 'UPDATE ' . FOOTB_TEAMS . " SET matchday = $matchday_new WHERE season = $season AND league = $league AND team_id = $team_id";
						$resultup = $db->sql_query($sqlup);
						$message .= $row['team_name'] . '<br />';
					}
					if ($move_rank > 0 AND $move_league > 0 AND $place == $move_rank)
					{
						$team_name = $row['team_name'];
						$short_name = $row['team_name_short'];
						$team_sign = $row['team_symbol'];
						$sqlinsert = 'INSERT INTO ' . FOOTB_TEAMS . " VALUES($season, $move_league, $team_id, '$team_name', '$short_name', '$team_sign', '', $move_matchday)";
						$resultinsert = $db->sql_query($sqlinsert);
						$messag_moved .= $row['team_name'] . '<br />';
					}
					$place++;
				}
			}
		}
	}
	$db->sql_freeresult($result);

	$message .= '<br /><br />';
	return $message. $messag_moved;
}

/**
* return SQL statement part to calculate points depending to mode.
*/
function select_points($creator = 'm', $sum = false)
{
	global $league_info;
	
	$points_result = $league_info['points_result'];
	$points_tendency = $league_info['points_tendency'];
	$points_diff = $league_info['points_diff'];

	switch ($league_info['points_mode'])
	{
		// hit = points_result
		// right tendency (not draw) = points_result - difference between bet und result but minimal points_tendency 
		// right tendency (draw) = points_result - difference between bet goals home und result goals home
		case 1:
			$select_part = 	($sum ? "SUM(IF(b.goals_home <> '' AND b.goals_guest <> ''," : 'IF(((m.status = 2) OR (m.status = 3)),') .
								"IF(b.goals_home <> '' AND b.goals_guest <> '',
									IF((b.goals_home + 0 < b.goals_guest) <> ($creator.goals_home + 0 < $creator.goals_guest) 
										OR (b.goals_home = b.goals_guest) <> ($creator.goals_home = $creator.goals_guest) 
										OR (b.goals_home + 0 > b.goals_guest) <> ($creator.goals_home + 0 > $creator.goals_guest),
										" .($sum ? '0' : "''") . ",
										IF((b.goals_home = $creator.goals_home) AND (b.goals_guest = $creator.goals_guest),
											$points_result,
											IF((b.goals_home = b.goals_guest),
												$points_result - ABS(b.goals_home - $creator.goals_home),
												IF((($points_result - ABS(b.goals_home - $creator.goals_home) - ABS(b.goals_guest - $creator.goals_guest)) < $points_tendency),
													$points_tendency,
													$points_result - ABS(b.goals_home - $creator.goals_home) - ABS(b.goals_guest - $creator.goals_guest)
												)
											)
										)
									),
									" .($sum ? '0' : "''") . '
								),
								' .($sum ? '0' : "''") . "
							) " .($sum ? ')' : '') . 'AS points';
			break;
		// hit = points_result, 
		// right tendency = points_result - difference between bet und result but minimal points_tendency 
		case 2:
			$select_part =  ($sum ? "SUM(IF(b.goals_home <> '' AND b.goals_guest <> ''," : 'IF(((m.status = 2) OR (m.status = 3)),') .
								"IF(b.goals_home <> '' AND b.goals_guest <> '',
									IF((b.goals_home + 0 < b.goals_guest) <> ($creator.goals_home + 0 < $creator.goals_guest) 
										OR (b.goals_home = b.goals_guest) <> ($creator.goals_home = $creator.goals_guest) 
										OR (b.goals_home + 0 > b.goals_guest) <> ($creator.goals_home + 0 > $creator.goals_guest),
										" .($sum ? '0' : "''") . ",
										IF((b.goals_home = $creator.goals_home) AND (b.goals_guest = $creator.goals_guest),
											$points_result,
											IF((b.goals_home = b.goals_guest),
												$points_result - ABS(b.goals_home - $creator.goals_home) - ABS(b.goals_guest - $creator.goals_guest),
												IF((($points_result - ABS(b.goals_home - $creator.goals_home) - ABS(b.goals_guest - $creator.goals_guest)) < $points_tendency),
													$points_tendency,
													$points_result - ABS(b.goals_home - $creator.goals_home) - ABS(b.goals_guest - $creator.goals_guest)
												)
											)
										)
									),
									" .($sum ? '0' : "''") . '
								),
								' .($sum ? '0' : "''") . "
							) " .($sum ? ')' : '') . 'AS points';
			break;
		// hit = points_result, 
		// right tendency = points_tendency 
		case 3:
			$select_part = 	($sum ? "SUM(IF(b.goals_home <> '' AND b.goals_guest <> ''," : 'IF(((m.status = 2) OR (m.status = 3)),') .
								"IF(b.goals_home <> '' AND b.goals_guest <> '',
									IF((b.goals_home + 0 < b.goals_guest) <> ($creator.goals_home + 0 < $creator.goals_guest) 
										OR (b.goals_home = b.goals_guest) <> ($creator.goals_home = $creator.goals_guest) 
										OR (b.goals_home + 0 > b.goals_guest) <> ($creator.goals_home + 0 > $creator.goals_guest),
										" .($sum ? '0' : "''") . ",
										IF((b.goals_home = $creator.goals_home) AND (b.goals_guest = $creator.goals_guest),
											$points_result,
											$points_tendency
										)
									),
									" .($sum ? '0' : "''") . '
								),
								' .($sum ? '0' : "''") . "
							) " .($sum ? ')' : '') . 'AS points';
			break;
		// hit = points_result, 
		// right goal-difference = points_diff, 
		// right tendency = points_tendency 
		case 4:
			$select_part = 	($sum ? "SUM(IF(b.goals_home <> '' AND b.goals_guest <> ''," : 'IF(((m.status = 2) OR (m.status = 3)),') .
								"IF(b.goals_home <> '' AND b.goals_guest <> '',
									IF((b.goals_home + 0 < b.goals_guest) <> ($creator.goals_home + 0 < $creator.goals_guest) 
										OR (b.goals_home = b.goals_guest) <> ($creator.goals_home = $creator.goals_guest) 
										OR (b.goals_home + 0 > b.goals_guest) <> ($creator.goals_home + 0 > $creator.goals_guest),
										" .($sum ? '0' : "''") . ",
										IF((b.goals_home = $creator.goals_home) AND (b.goals_guest = $creator.goals_guest),
											$points_result,
											IF((b.goals_home - b.goals_guest = $creator.goals_home - $creator.goals_guest),
												$points_diff,
												$points_tendency
											)
										)
									),
									" .($sum ? '0' : "''") . '
								),
								' .($sum ? '0' : "''") . "
							) " .($sum ? ')' : '') . 'AS points';
			break;
		// hit = points_result, 
		// right goal-difference (not draw) = points_diff, 
		// right tendency  = points_tendency 
		case 5:
			$select_part = 	($sum ? "SUM(IF(b.goals_home <> '' AND b.goals_guest <> ''," : 'IF(((m.status = 2) OR (m.status = 3)),') .
								"IF(b.goals_home <> '' AND b.goals_guest <> '',
									IF((b.goals_home + 0 < b.goals_guest) <> ($creator.goals_home + 0 < $creator.goals_guest) 
										OR (b.goals_home = b.goals_guest) <> ($creator.goals_home = $creator.goals_guest) 
										OR (b.goals_home + 0 > b.goals_guest) <> ($creator.goals_home + 0 > $creator.goals_guest),
										" .($sum ? '0' : "''") . ",
										IF((b.goals_home = $creator.goals_home) AND (b.goals_guest = $creator.goals_guest),
											$points_result,
											IF(((b.goals_home - b.goals_guest = $creator.goals_home - $creator.goals_guest) 
												AND ($creator.goals_home <> $creator.goals_guest)) ,
												$points_diff,
												$points_tendency
											)
										)
									),
									" .($sum ? '0' : "''") . '
								),
								' .($sum ? '0' : "''") . "
							) " .($sum ? ')' : '') . 'AS points';
			break;
		// hit = points_result, 
		// right tendency draw = points_diff, 
		// right tendency (not draw) = points_tendency 
		case 6:
			$select_part = 	($sum ? "SUM(IF(b.goals_home <> '' AND b.goals_guest <> ''," : 'IF(((m.status = 2) OR (m.status = 3)),') .
								"IF(b.goals_home <> '' AND b.goals_guest <> '',
									IF((b.goals_home + 0 < b.goals_guest) <> ($creator.goals_home + 0 < $creator.goals_guest) 
										OR (b.goals_home = b.goals_guest) <> ($creator.goals_home = $creator.goals_guest) 
										OR (b.goals_home + 0 > b.goals_guest) <> ($creator.goals_home + 0 > $creator.goals_guest),
										" .($sum ? '0' : "''") . ",
										IF((b.goals_home = $creator.goals_home) AND (b.goals_guest = $creator.goals_guest),
											$points_result,
											IF(((b.goals_home = b.goals_guest) AND ($creator.goals_home = $creator.goals_guest) ) ,
												$points_diff,
												$points_tendency
											)
										)
									),
									" .($sum ? '0' : "''") . '
								),
								' .($sum ? '0' : "''") . "
							) " .($sum ? ')' : '') . 'AS points';
			break;
	}
	return $select_part;
}
?>