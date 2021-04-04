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
$win_user_most_hits = array();
$win_user_most_hits_away = array();
$season_wins = array();
$win_user_most_hits = win_user_most_hits($season, $league, $matchday);
$win_user_most_hits_away = win_user_most_hits_away($season, $league, $matchday);

// Statistics of matchday
$sql = "SELECT
		b.user_id,
		COUNT(b.match_no) AS matches,
		SUM(IF(b.goals_home <> '' AND b.goals_guest <> '', 1, 0)) AS bets,
		SUM(IF(b.goals_home <> '' AND b.goals_guest <> '',
				IF((b.goals_home = m.goals_home) AND (b.goals_guest = m.goals_guest), 1, 0),
				0
			)
		) AS hits,
		SUM(IF(b.goals_home <> '' AND b.goals_guest <> '',
				IF((m.goals_home <= m.goals_guest), 
					IF((b.goals_home = m.goals_home) AND (b.goals_guest = m.goals_guest), 1, 0),
					0
				),
				0
			)
		) AS hits02,
		SUM(IF(b.goals_home <> '' AND b.goals_guest <> '',
				IF((b.goals_home + 0 < b.goals_guest) <> (m.goals_home + 0 < m.goals_guest) OR 
				   (b.goals_home = b.goals_guest) <> (m.goals_home = m.goals_guest) OR 
				   (b.goals_home + 0 > b.goals_guest) <> (m.goals_home + 0 > m.goals_guest),
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
		AND m.matchday = $matchday
	GROUP BY b.user_id";

$result = $db->sql_query($sql);
$usersstats = $db->sql_fetchrowset($result);
$db->sql_freeresult($result);
foreach ($usersstats AS $userstats)
{
	$betsof[$userstats['user_id']] 			= $userstats['bets'];
	$nobetsof[$userstats['user_id']] 		= $userstats['matches'] - $userstats['bets'];
	$tendenciesof[$userstats['user_id']] 	= $userstats['tendency'];
	$hitsof[$userstats['user_id']] 			= $userstats['hits'];
	$hits02of[$userstats['user_id']] 		= $userstats['hits02'];
}

// ranks of matchday
$sql = 'SELECT
		rank,
		user_id
	FROM '. FOOTB_RANKS . "
	WHERE season = $season 
		AND league = $league 
		AND matchday = $matchday 
		AND status IN (2,3)
	ORDER BY rank ASC, user_id ASC";
$result = $db->sql_query($sql);
$ranks = $db->sql_fetchrowset($result);
$db->sql_freeresult($result);
$total_users = sizeof($ranks);
foreach ($ranks AS $rank)
{
	$rankof[$rank['user_id']] = $rank['rank'];
}

if ($matchday > 1)
{
	// rank previous matchday
	$sql = 'SELECT
			rank AS last_rang,
			user_id
		FROM '. FOOTB_RANKS . "
		WHERE season = $season 
			AND league = $league 
			AND matchday = ($matchday-1) 
			AND status IN (2,3)
		ORDER BY last_rang ASC, user_id ASC";
		
	$result = $db->sql_query($sql);
	$ranks = $db->sql_fetchrowset($result);
	$db->sql_freeresult($result);

	foreach ($ranks AS $rank)
	{
		$prevrankof[$rank['user_id']] = $rank['last_rang'];
	}
}

if ($matchday == $maxmatchday)
{
	$season_wins = season_wins($season, $league, $matchday);
}

// Make sure $start is set to the last page if it exceeds the amount
if ($start < 0 || $start >= $total_users)
{
	$sql_start = ($start < 0) ? 0 : floor(($total_users - 1) / $config['football_users_per_page']) * $config['football_users_per_page'];
}
else
{
	$sql_start = floor($start / $config['football_users_per_page']) * $config['football_users_per_page'];
}
$sql_limit = $config['football_users_per_page'];

// handle pagination.
$base_url = $this->helper->route('football_football_controller', array('side' => 'ranks_matchday', 's' => $season, 'l' => $league, 'm' => $matchday));
$pagination = $phpbb_container->get('pagination');
$pagination->generate_template_pagination($base_url, 'pagination', 'start', $total_users, $this->config['football_users_per_page'], $start);
																$data_ranks = false;
$index = 0;
$ext_path = $this->phpbb_path_helper->update_web_root_path($this->phpbb_extension_manager->get_extension_path('football/football', true));

$sql = 'SELECT
		r.rank,
		r.status,
		r.user_id,
		r.points,
		r.win AS wins,
		u.user_colour,
		u.username
	FROM '. FOOTB_RANKS . ' AS r
	LEFT Join '. USERS_TABLE . " AS u ON (r.user_id = u.user_id)
	WHERE r.season = $season 
		AND r.league = $league 
		AND r.matchday = $matchday 
		AND r.status IN (2,3)
	ORDER BY r.rank ASC, r.points DESC, LOWER(u.username) ASC";

$result = $db->sql_query_limit($sql, $sql_limit, $sql_start);
while($row = $db->sql_fetchrow($result))
{
	$index++;
	$data_ranks = true;
	if (isset($prevrankof[$row['user_id']]))
	{
		if ($rankof[$row['user_id']] == '')
		{
			$change_sign 	= '';
			$change_differ 	= '&nbsp;';
		}
		else
		{
			if ($rankof[$row['user_id']] == $prevrankof[$row['user_id']])
			{
				$change_sign 	= '=';
				$change_differ 	= '&nbsp;';
			}
			else
			{
				if ($rankof[$row['user_id']] > $prevrankof[$row['user_id']])
				{
					$change_sign 	= '+';
					$differ 		= $rankof[$row['user_id']] - $prevrankof[$row['user_id']];
					$change_differ 	= ' (' . $differ . ')';
				}
				else
				{
					$change_sign 	= '-';
					$differ 		= $prevrankof[$row['user_id']] - $rankof[$row['user_id']];
					$change_differ 	= ' (' . $differ . ')';
				}
			}
		}
	}
	else
	{
			$change_sign 	= '';
			$change_differ 	= '&nbsp;';
	}

	if ($matchday == $maxmatchday)
	{
		// if someone didn't bet the hole Season
		if(!isset($win_user_most_hits[$row['user_id']]['win']))
		{
			$win_user_most_hits[$row['user_id']]['win'] = 0;
		}
		if(!isset($win_user_most_hits_away[$row['user_id']]['win']))
		{
			$win_user_most_hits_away[$row['user_id']]['win'] = 0;
		}
		if(!isset($season_wins[$row['user_id']]['win']))
		{
			$season_wins[$row['user_id']]['win'] = 0;
		}
		$win_total = sprintf('%01.2f',$row['wins'] + $win_user_most_hits[$row['user_id']]['win'] + $win_user_most_hits_away[$row['user_id']]['win'] 
						+ $season_wins[$row['user_id']]['win']);
	}
	else
	{
		$win_total = sprintf('%01.2f',$row['wins']);
	}
	$row_class = (!($index % 2)) ? 'bg1 row_light' : 'bg2 row_dark';
	if ($row['user_id'] == $user->data['user_id'])
	{
		$row_class = 'bg3  row_user';
	}
	$colorstyle = color_style($row['status']);

	$template->assign_block_vars('rankstotal', array(
		'ROW_CLASS' 	=> $row_class,
		'RANK' 			=> $rankof[$row['user_id']],
		'NO_CHANGES'	=> ($change_sign == '=') ? true : false,
		'WORSENED'		=> ($change_sign == '+') ? true : false,
		'IMPROVED'		=> ($change_sign == '-') ? true : false,
		'CHANGE_SIGN' 	=> $change_sign,
		'CHANGE_DIFFER'	=> $change_differ,
		'USERID' 		=> $row['user_id'],
		'USERNAME' 		=> $row['username'],
		'U_PROFILE'		=> get_username_string('profile', $row['user_id'], $row['username'], $row['user_colour']),
		'BETS' 			=> $betsof[$row['user_id']],
		'NOBETS' 		=> ($nobetsof[$row['user_id']] == 0) ? '&nbsp;' : $nobetsof[$row['user_id']],
		'TENDENCIES'	=> ($tendenciesof[$row['user_id']] == 0) ? '&nbsp;' : $tendenciesof[$row['user_id']],
		'DIRECTHITS' 	=> ($hitsof[$row['user_id']] == 0) ? '&nbsp;' : $hitsof[$row['user_id']],
		'DIRECTHITS02' 	=> ($hits02of[$row['user_id']] == 0) ? '&nbsp;' : $hits02of[$row['user_id']],
		'POINTS' 		=> $row['points'],
		'COLOR_STYLE'	=> $colorstyle,
		'WIN' 			=> $win_total,
		)
	);
}
$db->sql_freeresult($result);
$league_info = league_info($season, $league);

$sidename = sprintf($user->lang['RANK_MATCHDAY']);
$template->assign_vars(array(
	'S_DISPLAY_RANKS_MATCHDAY'	=> true,
	'S_DISPLAY_HITS02'			=> $config['football_win_hits02'],
	'S_SIDENAME' 				=> $sidename,
	'S_WIN' 					=> ($league_info['win_matchday'] == '0') ? false : true,
	'S_DATA_RANKS' 				=> $data_ranks,
	'PAGE_NUMBER' 				=> $pagination->on_page($total_users, $this->config['football_users_per_page'], $start),
	'TOTAL_USERS'				=> ($total_users == 1) ? $user->lang['VIEW_BET_USER'] : sprintf($user->lang['VIEW_BET_USERS'], $total_users),
	'WIN_NAME' 					=> $config['football_win_name'],
	)
);
