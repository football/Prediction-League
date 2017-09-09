<?php
/**
*
* @package phpBB Extension - Football Football
* @copyright (c) 2016 football (http://football.bplaced.net)
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

if (!defined('IN_PHPBB') OR !defined('IN_FOOTBALL'))
{
	exit;
}

$start = $this->request->variable('start', 0);
$matches_on_matchday = false;

if (!$user_sel)
{
	if (user_is_member($user->data['user_id'], $season, $league))
	{
		$user_sel =  $user->data['user_id'];
	}
}
$username = '';

$data = false;
// Select user 
$total_users = 0;
$sql = 'SELECT DISTINCT
		u.user_id,
		u.username
	FROM ' . FOOTB_BETS . ' AS b
	LEFT JOIN ' . USERS_TABLE . " AS u ON (u.user_id = b.user_id)
	WHERE season = $season 
		AND league = $league
	ORDER BY LOWER(u.username) ASC";
	
$result = $db->sql_query($sql);

while($row = $db->sql_fetchrow($result))
{
	$total_users++;
	if ($user_sel == $row['user_id'] OR !$user_sel)
	{
		$selectid = ' selected="selected"';
		$username = $row['username'];
		$user_sel = $row['user_id'];
	}
	else
	{
		$selectid = '';
	}
	$template->assign_block_vars('form_user', array(
		'S_USER' 		=> $row['user_id'],
		'S_USERNAME' 	=> $row['username'],
		'S_SELECTEDID' 	=> $selectid,
		)
	);
}
$db->sql_freeresult($result);
// Select matches with results and tendencies
$sql = "SELECT
		m.match_no,
		m.status,
		m.formula_home,
		m.formula_guest,
		t1.team_name_short AS home_name,
		t2.team_name_short AS guest_name,
		t1.team_id AS home_id,
		t2.team_id AS guest_id,
		m.goals_home,
		m.goals_guest,
		SUM(IF(b.goals_home + 0 > b.goals_guest AND b.goals_home <> '' AND b.goals_guest <> '', 1, 0)) AS home,
		SUM(IF(b.goals_home = b.goals_guest AND b.goals_home <> '' AND b.goals_guest <> '', 1, 0)) AS draw,
		SUM(IF(b.goals_home + 0 < b.goals_guest AND b.goals_home <> '' AND b.goals_guest <> '', 1, 0)) AS guest
	FROM  " . FOOTB_MATCHES . ' AS m
	LEFT JOIN ' . FOOTB_TEAMS . ' AS t1 ON (t1.season=m.season AND t1.league = m.league AND t1.team_id = m.team_id_home)
	LEFT JOIN ' . FOOTB_TEAMS . ' AS t2 ON (t2.season=m.season AND t2.league = m.league AND t2.team_id = m.team_id_guest)
	LEFT JOIN ' . FOOTB_BETS . " AS b ON(b.season = m.season AND b.league = m.league AND b.match_no = m.match_no)
	WHERE  m.season = $season 
		AND m.league = $league 
		AND m.matchday = $matchday
	GROUP BY m.match_no
	ORDER BY m.match_datetime ASC, m.match_no ASC";

$result = $db->sql_query($sql);
$matches = $db->sql_fetchrowset($result);
$db->sql_freeresult($result);
$count_matches = sizeof($matches);
if ($count_matches > 11)
{
	$split_after = 8;
	$splits = floor($count_matches / 8);
}
else
{
	$split_after = $count_matches; 
	$splits = 1;
}

// Make sure $start is set to the last page if it exceeds the amount
if ($start < 0 || $start >= $total_users)
{
	$start = ($start < 0) ? 0 : floor(($total_users - 1) / $config['football_users_per_page']) * $config['football_users_per_page'];
}
else
{
	$start = floor($start / $config['football_users_per_page']) * $config['football_users_per_page'];
}

$sql_start = $start * $count_matches;
$sql_limit = $config['football_users_per_page'] * $count_matches;

// handle pagination.
$base_url = $this->helper->route('football_main_controller', array('side' => 'my_koeff', 's' => $season, 'l' => $league, 'm' => $matchday, 'u' => "$user_sel"));
$pagination = $phpbb_container->get('pagination');
$pagination->generate_template_pagination($base_url, 'pagination', 'start', $total_users, $this->config['football_users_per_page'], $start);

$bet_line = array();
if ($count_matches > 0)
{
	$matches_on_matchday = true;
	// 	Select user bets and points on user results
	$sql = "SELECT
			u.user_id,
			u.username,
			m.status,
			b.goals_home AS bet_home,
			b.goals_guest AS bet_guest,
			" . select_points("bu") . "
		FROM  " . FOOTB_MATCHES . ' AS m
		LEFT JOIN ' . FOOTB_BETS . ' AS b ON(b.season = m.season AND b.league = m.league AND b.match_no = m.match_no)
		LEFT JOIN ' . FOOTB_BETS . " AS bu ON(bu.season = m.season AND bu.league = m.league AND bu.match_no = m.match_no AND bu.user_id = $user_sel)
		LEFT JOIN " . USERS_TABLE . "  AS u ON (u.user_id = b.user_id)
		WHERE  m.season = $season 
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
		$data = true;
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
				if ($user_bet['status'] < 1 && !$config['football_view_bets'])
				{
					// hide bets
					$bet_home = ($user_bet['bet_home'] == '') ? '&nbsp;' : '?';
					$bet_guest = ($user_bet['bet_guest'] == '') ? '&nbsp;' : '?';
				}
				else
				{
					$bet_home = $user_bet['bet_home'];
					$bet_guest = $user_bet['bet_guest'];
				}
				
				$colorstyle_bet = color_style($user_bet['status']);
				$template->assign_block_vars('match_panel.user_row.bet', array(
					'BET' 			=> $bet_home. ':'. $bet_guest,
					'COLOR_STYLE' 	=> $colorstyle_bet,
					'POINTS' 		=> ($user_bet['points'] == '') ? '&nbsp;' : $user_bet['points'],
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
					'TENDENCY' => $match_tendency,
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
	if (0 == $match['home_id'])
	{
		$home_info 		= get_team($season, $league, $match['match_no'], 'team_id_home', $match['formula_home']);
		$home_in_array 	= explode("#",$home_info);
		$homename 		= $home_in_array[3];
	}
	else
	{
		$homename = $match['home_name'];
	}
	if (0 == $match['guest_id'])
	{
		$guest_info 	= get_team($season, $league, $match['match_no'], 'team_id_guest', $match['formula_guest']);
		$guest_in_array = explode("#",$guest_info);
		$guestname 		= $guest_in_array[3];
	}
	else
	{
		$guestname = $match['guest_name'];
	}
	$colorstyle_match = color_style($match['status']);
	$template->assign_block_vars('match_panel.match_entry', array(
		'HOME_NAME' 	=> $homename,
		'GUEST_NAME' 	=> $guestname,
		'RESULT' 		=> $match['goals_home'] . ':' . $match['goals_guest'],
		'COLOR_STYLE' 	=> $colorstyle_match,
		)
	);
	if ($match['status'] < 1 && !$config['football_view_tendencies'])
	{
		// hide tendencies
		$matches_tendency[] =  '?-?-?';
	}
	else
	{
		$matches_tendency[] = $match['home'] . '-' . $match['draw'] . '-' . $match['guest'];
	}
	$match_index++;
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
		if ($user_bet['status'] < 1)
		{
			if ($user_bet['bet_home'] == '')
			{
				$bet_home = '';
			}
			else
			{
				$bet_home = '?';
			}
			if ($user_bet['bet_guest'] == '')
			{
				$bet_guest = '';
			}
			else
			{
				$bet_guest = '?';
			}
		}
		else
		{
			$bet_home = $user_bet['bet_home'];
			$bet_guest = $user_bet['bet_guest'];
		}
		
		$colorstyle_bet = color_style($user_bet['status']);
		$template->assign_block_vars('match_panel.user_row.bet', array(
			'BET' 			=> $bet_home. ':'. $bet_guest,
			'COLOR_STYLE' 	=> $colorstyle_bet,
			'POINTS' 		=> ($user_bet['points'] == '') ? '&nbsp;' : $user_bet['points'],
			)
		);

		if ($bet_index == $split_after)
		{
			$sum_total[$user_bet['username']] += $total;
			$matchday_sum_total += $total;
			$template->assign_block_vars('match_panel.user_row.points', array(
				'COLOR_STYLE' 	=> $colorstyle_total,
				'POINTS_TOTAL' 	=> $sum_total[$user_bet['username']],
				)
			);
			$bet_index = 0;
		}
	}

	$template->assign_block_vars('match_panel.tendency_footer', array(
		'S_TOTAL' 		=> true,
		'COLOR_STYLE' 	=> $colorstyle_total, //currently ignored
		'SUMTOTAL' 		=> $matchday_sum_total,
		)
	);
	foreach ($matches_tendency AS $match_tendency)
	{
		$template->assign_block_vars('match_panel.tendency_footer.tendency', array(
			'TENDENCY' => $match_tendency,
			)
		);
	}
}

$sidename = sprintf($user->lang['MY_KOEFF']);
$template->assign_vars(array(
	'S_DISPLAY_MY_KOEFF' 		=> true,
	'S_SIDENAME' 				=> $sidename,
	'S_MATCHES_ON_MATCHDAY' 	=> $matches_on_matchday,
	'S_SPALTEN' 				=> ($count_matches * 2) + 2,
	'PAGE_NUMBER' 				=> $pagination->on_page($total_users, $this->config['football_users_per_page'], $start),
	'TOTAL_USERS'				=> ($total_users == 1) ? $user->lang['VIEW_BET_USER'] : sprintf($user->lang['VIEW_BET_USERS'], $total_users),
	'USERNAME' 					=> $username,
	)
);

?>