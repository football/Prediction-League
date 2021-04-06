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

if (!$user_sel)
{
	if (user_is_member($user->data['user_id'], $season, $league))
	{
		$user_sel =  $user->data['user_id'];
	}
}
$username = '';
$ext_path = $this->phpbb_path_helper->update_web_root_path($this->phpbb_extension_manager->get_extension_path('football/football', true));

$data = false;
// Select user 
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
	$data = true;
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

$rank = 0;
// Select sum of users points group by team
$sql = "SELECT
		t.*,
		SUM(1) AS bets,
		SUM(IF((m.team_id_home = t.team_id), 
				IF(b.goals_home + 0 > b.goals_guest, 1, 0), 
				IF(b.goals_home + 0 < b.goals_guest, 1, 0)
			)
		) AS win,
		SUM(IF(b.goals_home = b.goals_guest, 1, 0)) AS draw,
		SUM(IF((m.team_id_home = t.team_id), 
				IF(b.goals_home + 0 < b.goals_guest, 1, 0), 
				IF(b.goals_home + 0 > b.goals_guest, 1, 0)
			)
		) AS lost,
		SUM(IF((b.goals_home + 0 < b.goals_guest) <> (m.goals_home + 0 < m.goals_guest) 
				OR (b.goals_home = b.goals_guest) <> (m.goals_home = m.goals_guest) 
				OR (b.goals_home + 0 > b.goals_guest) <> (m.goals_home + 0 > m.goals_guest),
				0,
				IF((b.goals_home = m.goals_home) AND (b.goals_guest=m.goals_guest),
					0,1
				)
			)
		)AS tendencies,
		SUM(IF((b.goals_home = m.goals_home) AND (b.goals_guest=m.goals_guest),1,0))AS hits,
		" . select_points('m',true) . "
	FROM " . FOOTB_TEAMS . ' AS t
	LEFT JOIN ' . FOOTB_MATCHES . ' AS m ON (m.season = t.season AND m.league = t.league 
											AND (m.team_id_home = t.team_id OR m.team_id_guest = t.team_id))
	LEFT JOIN ' . FOOTB_BETS . " AS b ON (b.season = t.season AND b.league = t.league AND b.match_no=m.match_no)
	WHERE t.season = $season 
		AND t.league = $league 
		AND m.status IN (3,6) 
		AND b.user_id = $user_sel 
		AND b.goals_home <> '' 
		AND b.goals_guest <> '' 
		AND m.matchday <= $matchday
	GROUP BY t.team_id
	ORDER BY points DESC";
	
$result = $db->sql_query($sql);

while($row = $db->sql_fetchrow($result))
{
	$rank++;
	$row_class = (!($rank % 2)) ? 'bg1 row_light' : 'bg2 row_dark';
	$logo = "<img src=\"" . $ext_path . 'images/flags/' . $row['team_symbol'] . "\" alt=\"" . $row['team_symbol'] . "\" width=\"28\" height=\"28\"/>" ;
	$template->assign_block_vars('points', array(
		'ROW_CLASS' 	=> $row_class,
		'RANK' 			=> $rank,
		'LOGO' 			=> $logo,
		'TEAM' 			=> $row['team_name_short'],
		'COUNT' 		=> $row['bets'],
		'WIN' 			=> $row['win'],
		'DRAW' 			=> $row['draw'],
		'LOST' 			=> $row['lost'],
		'DIRECTHITS' 	=> $row['hits'],
		'TENDENCIES' 	=> $row['tendencies'],
		'TOTAL' 		=> $row['hits'] + $row['tendencies'],
		'POINTS' 		=> $row['points'],
		)
	);
}
$db->sql_freeresult($result);

$rank = 0;
// Select sum of all users points group by team
$sql = "SELECT
		t.*,
		SUM(1) AS bets,
		SUM(IF((m.team_id_home = t.team_id), 
				IF(b.goals_home + 0 > b.goals_guest, 1, 0), 
				IF(b.goals_home + 0 < b.goals_guest, 1, 0)
			)
		) AS win,
		SUM(IF(b.goals_home = b.goals_guest, 1, 0)) AS draw,
		SUM(IF((m.team_id_home = t.team_id), 
				IF(b.goals_home + 0 < b.goals_guest, 1, 0), 
				IF(b.goals_home + 0 > b.goals_guest, 1, 0)
			)
		) AS lost,
		SUM(IF((b.goals_home + 0 < b.goals_guest) <> (m.goals_home + 0 < m.goals_guest) 
				OR (b.goals_home = b.goals_guest) <> (m.goals_home = m.goals_guest) 
				OR (b.goals_home + 0 > b.goals_guest) <> (m.goals_home + 0 > m.goals_guest),
				0,
				IF((b.goals_home = m.goals_home) AND (b.goals_guest = m.goals_guest), 0, 1)
			)
		)AS tendencies,
		SUM(IF((b.goals_home = m.goals_home) AND (b.goals_guest = m.goals_guest), 1, 0)) AS hits,
		" . select_points('m',true) . "
	FROM " . FOOTB_TEAMS . ' AS t
	LEFT JOIN ' . FOOTB_MATCHES . ' AS m ON (m.season = t.season AND m.league = t.league 
											AND (m.team_id_home = t.team_id OR m.team_id_guest = t.team_id))
	LEFT JOIN ' . FOOTB_BETS . " AS b ON (b.season = t.season AND b.league = t.league AND b.match_no=m.match_no)
	WHERE t.season = $season 
		AND t.league = $league 
		AND b.goals_home <> '' 
		AND b.goals_guest <> '' 
		AND m.status IN (3,6) 
		AND m.matchday <= $matchday
	GROUP BY t.team_id
	ORDER BY points DESC";
	
$result = $db->sql_query($sql);

while($row = $db->sql_fetchrow($result))
{
	$rank++;
	$row_class = (!($rank % 2)) ? 'bg1 row_light' : 'bg2 row_dark';
	$logo = "<img src=\"" . $ext_path . 'images/flags/' . $row['team_symbol'] . "\" alt=\"" . $row['team_symbol'] . "\" width=\"28\" height=\"28\"/>" ;
	$template->assign_block_vars('allpoints', array(
		'ROW_CLASS' 	=> $row_class,
		'RANK' 			=> $rank,
		'LOGO' 			=> $logo,
		'TEAM' 			=> $row['team_name_short'],
		'COUNT' 		=> $row['bets'],
		'WIN' 			=> $row['win'],
		'DRAW' 			=> $row['draw'],
		'LOST' 			=> $row['lost'],
		'DIRECTHITS' 	=> $row['hits'],
		'TENDENCIES' 	=> $row['tendencies'],
		'TOTAL' 		=> $row['hits'] + $row['tendencies'],
		'POINTS' 		=> $row['points'],
		)
	);
}
$db->sql_freeresult($result);

$sidename = sprintf($user->lang['MY_POINTS']);
$template->assign_vars(array(
	'S_DISPLAY_MY_POINTS' 		=> true,
	'S_SIDENAME' 				=> $sidename,
	'S_DATA_MY_POINTS' 			=> $data,
	'SEASON' 					=> $season,
	'LEAGUE' 					=> $league,
	'USERNAME' 					=> $username,
	)
);
