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

if (!$user_sel)
{
	if (user_is_member($user->data['user_id'], $season, $league))
	{
		$user_sel =  $user->data['user_id'];
	}
}
$username = '';
$ext_path = $this->phpbb_path_helper->update_web_root_path($this->phpbb_extension_manager->get_extension_path('football/football', true));

// select user 
$sql = 'SELECT DISTINCT
			u.user_id,
			u.username
		FROM ' . FOOTB_BETS . ' AS b
		LEFT JOIN ' . USERS_TABLE . " AS u ON (u.user_id = b.user_id)
		WHERE season = $season 
			AND league = $league
		ORDER BY LOWER(u.username) ASC";
		
$result = $db->sql_query($sql);

if ($user_sel == 900)
{
	$selectid = ' selected="selected"';
	$username = 'Alle';
	$user_sel = 900;
	$where_user = "";
}
else
{
	$selectid = '';
	$where_user = "b.user_id = $user_sel AND ";
}
$template->assign_block_vars('form_user', array(
	'S_USER'		=> 900,
	'S_USERNAME' 	=> 'Alle',
	'S_SELECTEDID' 	=> $selectid,
	)
);

while($row = $db->sql_fetchrow($result))
{
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
		'S_USER'		=> $row['user_id'],
		'S_USERNAME' 	=> $row['username'],
		'S_SELECTEDID' 	=> $selectid,
		)
	);
}
$db->sql_freeresult($result);

$data_table = false;
$data_form = false;
if ($matchday > 5)
{
	$form_from = $matchday - 5;
}
else
{
	$form_from = 1;
}

$sql = '
	SELECT *
	FROM ' . FOOTB_LEAGUES . " 
	WHERE season = $season 
		AND league = $league";
		
$result = $db->sql_query($sql);
$row = $db->sql_fetchrow($result);
$league_type = $row['league_type'];
$db->sql_freeresult($result);

$text_form = sprintf($user->lang['TABLE_FORM_FROM'], $form_from);

$rank = 0;
// Select table on selected user bets
$sql = 'SELECT
		t.*,
		SUM(1) AS matches,
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
		SUM(IF(m.team_id_home = t.team_id, 
				IF(b.goals_home + 0 > b.goals_guest, 3, IF(b.goals_home = b.goals_guest, 1, 0)), 
				IF(b.goals_home + 0 < b.goals_guest, 3, IF(b.goals_home = b.goals_guest, 1, 0))
			)
		) AS points,
		SUM(IF(m.team_id_home = t.team_id, 
				IF(m.goals_home + 0 > m.goals_guest, 3, IF(m.goals_home = m.goals_guest, 1, 0)), 
				IF(m.goals_home + 0 < m.goals_guest, 3, IF(m.goals_home = m.goals_guest, 1, 0))
			)
		) AS realpoints,
		SUM(IF(m.team_id_home = t.team_id, b.goals_home - b.goals_guest, b.goals_guest - b.goals_home)) AS goals_diff,
		SUM(IF(m.team_id_home = t.team_id, b.goals_home, b.goals_guest)) AS goals,
		SUM(IF(m.team_id_home = t.team_id, b.goals_guest, b.goals_home)) AS goals_against
	FROM ' . FOOTB_TEAMS . ' AS t
	LEFT JOIN ' . FOOTB_MATCHES . ' AS m ON (m.season = t.season AND m.league = t.league 
											AND (m.team_id_home = t.team_id OR m.team_id_guest = t.team_id) AND m.group_id = t.group_id)
	LEFT JOIN ' . FOOTB_BETS . " AS b ON (b.season = t.season AND b.league = t.league AND b.match_no = m.match_no)
	WHERE $where_user 
		t.season = $season 
		AND t.league = $league 
		AND b.goals_home <> '' 
		AND b.goals_guest <> '' 
		AND m.matchday <= $matchday 
		AND m.status IN (2, 3,5,6)
	GROUP BY t.team_id
	ORDER BY t.group_id ASC, points DESC, goals_diff DESC, goals DESC";
	
$result = $db->sql_query($sql);
$lastGroup = '';
$sumdiff = 0;
while($row = $db->sql_fetchrow($result))
{
	if ($lastGroup != $row['group_id'])
	{
		$lastGroup = $row['group_id'];
		$rank = 0;
		$template->assign_block_vars('total', array(
			'GROUP' => sprintf($user->lang['GROUP']) . ' ' . $row['group_id'],
			)
		);
	}
	if ($league_type != 2 OR $row['group_id'] != '')
	{
		$data_table = true;
		$rank++;
		$row_class = (!($rank % 2)) ? 'bg1 row_light' : 'bg2 row_dark';
		$logo = "<img src=\"" . $ext_path . 'images/flags/' . $row['team_symbol'] . "\" alt=\"" . $row['team_symbol'] . "\" width=\"28\" height=\"28\"/>" ;
		$pdiff = $row['points'] - $row['realpoints'];
		if ($pdiff >= 0)
		{
			$sumdiff += $pdiff;
			$pdiff = ' (+' . $pdiff . ')';

		}
		else
		{
			$sumdiff -= $pdiff;
			$pdiff = ' (' . $pdiff . ')';
		}
		if ($user_sel == 900)
		{
			$pdiff = '';
		}

		$template->assign_block_vars('total', array(
			'RANK' 			=> $rank . '.',
			'ROW_CLASS' 	=> $row_class,
			'LOGO' 			=> $logo,
			'TEAM_ID' 		=> $row['team_id'],
			'TEAM' 			=> $row['team_name_short'],
			'U_PLAN_TEAM'	=> $this->helper->route('football_football_popup', array('popside' => 'viewplan_popup', 's' => $season, 'l' => $league,
																				'tid' => $row['team_id'], 'mode' => 'played')),
			'GAMES' 		=> $row['matches'],
			'WIN' 			=> $row['win'],
			'DRAW' 			=> $row['draw'],
			'LOST' 			=> $row['lost'],
			'GOALS' 		=> $row['goals'],
			'GOALS_AGAINST' => $row['goals_against'],
			'GOALS_DIFF' 	=> $row['goals_diff'],
			'POINTS' 		=> $row['points'] . $pdiff,
			)
		);
	}
}
$db->sql_freeresult($result);

$rank = 0;
// Select formtable on selected user bets
$sql = 'SELECT
		t.*,
		SUM(1) AS matches,
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
		SUM(IF(m.team_id_home = t.team_id, 
				IF(b.goals_home + 0 > b.goals_guest, 3, IF(b.goals_home = b.goals_guest, 1, 0)), 
				IF(b.goals_home + 0 < b.goals_guest, 3, IF(b.goals_home = b.goals_guest, 1, 0))
			)
		) AS points,
		SUM(IF(m.team_id_home = t.team_id, b.goals_home - b.goals_guest, b.goals_guest - b.goals_home)) AS goals_diff,
		SUM(IF(m.team_id_home = t.team_id, b.goals_home, b.goals_guest)) AS goals,
		SUM(IF(m.team_id_home = t.team_id, b.goals_guest, b.goals_home)) AS goals_against
	FROM ' . FOOTB_TEAMS . ' AS t
	LEFT JOIN ' . FOOTB_MATCHES . ' AS m ON (m.season = t.season AND m.league = t.league 
											AND (m.team_id_home = t.team_id OR m.team_id_guest = t.team_id) AND m.group_id = t.group_id)
	LEFT JOIN ' . FOOTB_BETS . " AS b ON (b.season = t.season AND b.league = t.league AND b.match_no = m.match_no)
	WHERE $where_user 
		t.season = $season 
		AND t.league = $league 
		AND b.goals_home <> '' 
		AND b.goals_guest <> '' 
		AND m.matchday >= $form_from 
		AND m.status IN (2, 3,5,6)
	GROUP BY t.team_id
	ORDER BY t.group_id ASC, points DESC, goals_diff DESC, goals DESC";
	
$result = $db->sql_query($sql);
$lastGroup = '';
while($row = $db->sql_fetchrow($result))
{
	$data_form = true;
	if ($lastGroup != $row['group_id'])
	{
		$lastGroup = $row['group_id'];
		$rank = 0;
		$template->assign_block_vars('form', array(
			'GROUP' => sprintf($user->lang['GROUP']) . ' ' . $row['group_id'],
			)
		);
	}
	if ($league_type != 2 OR $row['group_id'] != '')
	{
		$rank++;
		$row_class = (!($rank % 2)) ? 'bg1 row_light' : 'bg2 row_dark';
		$logo = "<img src=\"" . $ext_path . 'images/flags/' . $row['team_symbol'] . "\" alt=\"" . $row['team_symbol'] . "\" width=\"28\" height=\"28\"/>" ;

		$template->assign_block_vars('form', array(
			'RANK' 			=> $rank . '.',
			'ROW_CLASS' 	=> $row_class,
			'LOGO' 			=> $logo,
			'TEAM_ID' 		=> $row['team_id'],
			'TEAM' 			=> $row['team_name_short'],
			'U_PLAN_TEAM'	=> $this->helper->route('football_football_popup', array('popside' => 'viewplan_popup', 's' => $season, 'l' => $league,
																				'tid' => $row['team_id'], 'mode' => 'rest')),
			'GAMES' 		=> $row['matches'],
			'WIN' 			=> $row['win'],
			'DRAW' 			=> $row['draw'],
			'LOST' 			=> $row['lost'],
			'GOALS' 		=> $row['goals'],
			'GOALS_AGAINST' => $row['goals_against'],
			'GOALS_DIFF' 	=> $row['goals_diff'],
			'POINTS' 		=> $row['points'],
			)
		);
	}
}
$db->sql_freeresult($result);

$rank = 0;
// Select home-table on selected user bets
$sql = 'SELECT
		t.*,
		SUM(1) AS matches,
		SUM(IF(b.goals_home + 0 > b.goals_guest, 1, 0)) AS win,
		SUM(IF(b.goals_home = b.goals_guest, 1, 0)) AS draw,
		SUM(IF(b.goals_home + 0 < b.goals_guest, 1, 0)) AS lost,
		SUM(IF(b.goals_home + 0 > b.goals_guest, 3, IF(b.goals_home = b.goals_guest, 1, 0))) AS points,
		SUM(b.goals_home - b.goals_guest) AS goals_diff,
		SUM(b.goals_home) AS goals,
		SUM(b.goals_guest) AS goals_against
	FROM ' . FOOTB_TEAMS . ' AS t
	LEFT JOIN ' . FOOTB_MATCHES . ' AS m ON (m.season = t.season AND m.league = t.league 
											AND m.team_id_home = t.team_id AND m.group_id = t.group_id)
	LEFT JOIN ' . FOOTB_BETS . " AS b ON (b.season = t.season AND b.league = t.league AND b.match_no = m.match_no)
	WHERE $where_user 
		t.season = $season 
		AND t.league = $league 
		AND b.goals_home <> '' 
		AND b.goals_guest <> '' 
		AND m.matchday <= $matchday 
		AND m.status IN (2, 3,5,6)
	GROUP BY t.team_id
	ORDER BY t.group_id ASC, points DESC, goals_diff DESC, goals DESC";
	
$result = $db->sql_query($sql);
$lastGroup = '';
while($row = $db->sql_fetchrow($result))
{
	if ($lastGroup != $row['group_id'])
	{
		$lastGroup = $row['group_id'];
		$rank = 0;
		$template->assign_block_vars('home', array(
			'GROUP' => sprintf($user->lang['GROUP']) . ' ' . $row['group_id'],
			)
		);
	}
	if ($league_type != 2 OR $row['group_id'] != '')
	{
		$rank++;
		$row_class = (!($rank % 2)) ? 'bg1 row_light' : 'bg2 row_dark';
		$logo = "<img src=\"" . $ext_path . 'images/flags/' . $row['team_symbol'] . "\" alt=\"" . $row['team_symbol'] . "\" width=\"28\" height=\"28\"/>" ;

		$template->assign_block_vars('home', array(
			'RANK' 			=> $rank . '.',
			'ROW_CLASS' 	=> $row_class,
			'LOGO' 			=> $logo,
			'TEAM_ID' 		=> $row['team_id'],
			'TEAM' 			=> $row['team_name_short'],
			'U_PLAN_TEAM'	=> $this->helper->route('football_football_popup', array('popside' => 'viewplan_popup', 's' => $season, 'l' => $league,
																				'tid' => $row['team_id'], 'mode' => 'home')),
			'GAMES' 		=> $row['matches'],
			'WIN' 			=> $row['win'],
			'DRAW' 			=> $row['draw'],
			'LOST' 			=> $row['lost'],
			'GOALS' 		=> $row['goals'],
			'GOALS_AGAINST' => $row['goals_against'],
			'GOALS_DIFF' 	=> $row['goals_diff'],
			'POINTS' 		=> $row['points'],
			)
		);
	}
}
$db->sql_freeresult($result);

$rank = 0;
// Select away-table on selected user bets
$sql = 'SELECT
		t.*,
		SUM(1) AS matches,
		SUM(IF(b.goals_home + 0 < b.goals_guest, 1, 0)) AS win,
		SUM(IF(b.goals_home = b.goals_guest, 1, 0)) AS draw,
		SUM(IF(b.goals_home + 0 > b.goals_guest, 1, 0)) AS lost,
		SUM(IF(b.goals_home + 0 < b.goals_guest, 3, IF(b.goals_home = b.goals_guest, 1, 0))) AS points,
		SUM(b.goals_guest - b.goals_home) AS goals_diff,
		SUM(b.goals_guest) AS goals,
		SUM(b.goals_home) AS goals_against
	FROM ' . FOOTB_TEAMS . ' AS t
	LEFT JOIN ' . FOOTB_MATCHES . ' AS m ON (m.season = t.season AND m.league = t.league AND m.team_id_guest = t.team_id AND m.group_id = t.group_id)
	LEFT JOIN ' . FOOTB_BETS . " AS b ON (b.season = t.season AND b.league = t.league AND b.match_no = m.match_no)
	WHERE $where_user 
		t.season = $season 
		AND t.league = $league 
		AND b.goals_home <> '' 
		AND b.goals_guest <> '' 
		AND m.matchday <= $matchday 
		AND m.status IN (2, 3,5,6)
	GROUP BY t.team_id
	ORDER BY t.group_id ASC, points DESC, goals_diff DESC, goals DESC";
	
$result = $db->sql_query($sql);
$lastGroup = '';
while($row = $db->sql_fetchrow($result))
{
	if ($lastGroup != $row['group_id'])
	{
		$lastGroup = $row['group_id'];
		$rank = 0;
		$template->assign_block_vars('away', array(
			'GROUP' 	=> sprintf($user->lang['GROUP']) . ' ' . $row['group_id'],
			)
		);
	}
	if ($league_type != 2 OR $row['group_id'] != '')
	{
		$rank++;
		$row_class = (!($rank % 2)) ? 'bg1 row_light' : 'bg2 row_dark';
		$logo = "<img src=\"" . $ext_path . 'images/flags/' . $row['team_symbol'] . "\" alt=\"" . $row['team_symbol'] . "\" width=\"28\" height=\"28\"/>" ;

		$template->assign_block_vars('away', array(
			'RANK' 			=> $rank . '.',
			'ROW_CLASS' 	=> $row_class,
			'LOGO' 			=> $logo,
			'TEAM_ID' 		=> $row['team_id'],
			'TEAM' 			=> $row['team_name_short'],
			'U_PLAN_TEAM'	=> $this->helper->route('football_football_popup', array('popside' => 'viewplan_popup', 's' => $season, 'l' => $league,
																				'tid' => $row['team_id'], 'mode' => 'away')),
			'GAMES' 		=> $row['matches'],
			'WIN' 			=> $row['win'],
			'DRAW' 			=> $row['draw'],
			'LOST' 			=> $row['lost'],
			'GOALS' 		=> $row['goals'],
			'GOALS_AGAINST' => $row['goals_against'],
			'GOALS_DIFF' 	=> $row['goals_diff'],
			'POINTS' 		=> $row['points'],
			)
		);
	}
}
$db->sql_freeresult($result);

$sidename = sprintf($user->lang['MY_TABLE']);
$template->assign_vars(array(
	'S_DISPLAY_MY_TABLE' 		=> true,
	'S_SIDENAME' 				=> $sidename,
	'U_LEFT' 					=> $this->helper->route('football_main_controller', array('side' => 'my_points', 's' => $season, 'l' => $league, 'm' => $matchday)),
	'LEFT_LINK' 				=> '&lt; ' . sprintf($user->lang['MY_TABLE']),
	'LEFT_LINK' 				=> '&lt; '	. sprintf($user->lang['MY_POINTS']),
	'U_RIGHT' 					=> $this->helper->route('football_main_controller', array('side' => 'my_rank', 's' => $season, 'l' => $league, 'm' => $matchday)),
	'LEFT_LINK' 				=> '&lt; ' . sprintf($user->lang['MY_TABLE']),
	'RIGHT_LINK' 				=> sprintf($user->lang['MY_RANK'])	. ' &gt;',
	'LEFT_TITLE' 				=> sprintf($user->lang['TITLE_MY_POINTS']),
	'RIGHT_TITLE' 				=> sprintf($user->lang['TITLE_MY_RANKS']),
	'S_DATA_MY_TABLE' 			=> $data_table,
	'S_DATA_FORM' 				=> $data_form,
	'SEASON' 					=> $season,
	'LEAGUE' 					=> $league,
	'TEXT_FORM' 				=> $text_form,
	'S_PDIFF' 					=> $sumdiff,
	'USERNAME' 					=> $username,
	)
);
	
?>