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
$ext_path = $this->phpbb_path_helper->update_web_root_path($this->phpbb_extension_manager->get_extension_path('football/football', true));

$sql = 'SELECT *
	FROM ' . FOOTB_LEAGUES . " 
	WHERE season = $season 
		AND league = $league";
$result = $db->sql_query($sql);
$row = $db->sql_fetchrow($result);
$league_type = $row['league_type'];
$db->sql_freeresult($result);

$text_form = sprintf($user->lang['TABLE_FORM_FROM'], $form_from);

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
		) - IF(t.team_id = 20 AND t.season = 2011 AND $matchday > 7, 2, 0) AS points,
		SUM(IF(m.team_id_home = t.team_id, goals_home - goals_guest , goals_guest - goals_home)) AS goals_diff,
		SUM(IF(m.team_id_home = t.team_id, goals_home , goals_guest)) AS goals,
		SUM(IF(m.team_id_home = t.team_id, goals_guest , goals_home)) AS goals_against
	FROM " . FOOTB_TEAMS . ' AS t
	LEFT JOIN ' . FOOTB_MATCHES . " AS m ON (m.season = t.season AND m.league = t.league 
											AND (m.team_id_home = t.team_id OR m.team_id_guest = t.team_id) AND m.group_id = t.group_id)
	WHERE t.season = $season 
		AND t.league = $league 
		AND m.matchday <= $matchday 
		AND m.status IN (2,3,5,6)
	GROUP BY t.team_id
	ORDER BY t.group_id ASC, points DESC, goals_diff DESC, goals DESC, t.team_name ASC";
	
$result = $db->sql_query($sql);

$table_ary = array();
$points_ary = array();
$ranks_ary = array();
$rank = 0;
while( $row = $db->sql_fetchrow($result))
{
	$rank++;
	$table_ary[$row['team_id']] = $row;
	$points_ary[$row['group_id']][$row['points']][]=$row['team_id']; 
	$ranks_ary[] = $row['team_id'];
}

$last_group = '';
$rank = 0;
$current_rank = 0;
$last_goals = 0;
$last_goals_againts = 0;
$last_points = 0;
foreach($points_ary as $group_id => $points)
{
	$data_table = true;
	if ($last_group != $group_id)
	{
		$last_group =$group_id;
		$rank = 0;
		$last_goals = 0;
		$last_goals_againts = 0;
		$last_points = 0;
		$template->assign_block_vars('total', array(
			'GROUP' => sprintf($user->lang['GROUP']) . ' ' .$group_id,
			)
		);
	}
	
	foreach($points as $point => $teams)
	{
		if(count($teams) > 1 AND  $group_id != '')
		{
			// Compare teams with equal points and sort
			$teams = get_order_team_compare($teams, $season, $league, $group_id, $ranks_ary, $matchday);
		}
		foreach($teams as $key => $team)
		{
			$row = $table_ary[$team];
			$rank++;
			if ($last_points <> $row['points'] OR $last_goals <> $row['goals'] OR $last_goals_againts <> $row['goals_against'])
			{
				$current_rank = $rank . '.';
			}
			else
			{
				$current_rank = '';
			}
			$last_points = $row['points'];
			$last_goals = $row['goals'];
			$last_goals_againts = $row['goals_against'];
			$row_class = (!($rank % 2)) ? 'bg1 row_light' : 'bg2 row_dark';
			if ($row['team_symbol'] <> '')
			{
				$logo = "<img src=\"" . $ext_path . 'images/flags/' . $row['team_symbol'] . "\" alt=\"" . $row['team_symbol'] . "\" width=\"28\" height=\"28\"/>" ;
			}
			else
			{
				$logo = "<img src=\"" . $ext_path . "images/flags/blank.gif\" alt=\"\" width=\"28\" height=\"28\"/>" ;
			}
			  
			$template->assign_block_vars('total', array(
				'RANK' 			=> $current_rank,
				'ROW_CLASS' 	=> $row_class,
				'LOGO' 			=> $logo,
				'TEAM_ID' 		=> $row['team_id'],
				'TEAM' 			=> $row['team_name'],
				'TEAM_SHORT' 	=> $row['team_name_short'],
				'U_PLAN_TEAM'	=> $this->helper->route('football_football_popup', array('popside' => 'viewplan_popup', 's' => $season, 'l' => $row['league'],
																					'tid' => $row['team_id'], 'mode' => 'played')),
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
}

$db->sql_freeresult($result);
$rank = 0;
// Get form-table-information
$sql = 'SELECT
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
		SUM(IF(m.team_id_home = t.team_id, goals_home, goals_guest)) AS goals,
		SUM(IF(m.team_id_home = t.team_id, goals_guest, goals_home)) AS goals_against
	FROM ' . FOOTB_TEAMS . ' AS t
	LEFT JOIN ' . FOOTB_MATCHES . " AS m ON (m.season = t.season AND m.league = t.league 
											AND (m.team_id_home = t.team_id OR m.team_id_guest = t.team_id) AND m.group_id = t.group_id)
	WHERE t.season = $season 
		AND t.league = $league 
		AND m.matchday >= $form_from 
		AND m.status IN (2, 3,5,6)
	GROUP BY t.team_id
	ORDER BY t.group_id ASC, points DESC, goals_diff DESC, goals DESC, t.team_name ASC";
	
$result = $db->sql_query($sql);
$last_group = '';
$current_rank = 0;
$last_goals = 0;
$last_goals_againts = 0;
$last_points = 0;
while($row = $db->sql_fetchrow($result))
{
	if ($last_group != $row['group_id'])
	{
		$last_group = $row['group_id'];
		$rank = 0;
		$last_goals = 0;
		$last_goals_againts = 0;
		$last_points = 0;
		$template->assign_block_vars('form', array(
			'GROUP' => sprintf($user->lang['GROUP']) . ' ' . $row['group_id'],
			)
		);
	}
	if ($league_type != 2 OR $row['group_id'] != '')
	{
		$data_form = true;
		$rank++;
		if ($last_points <> $row['points'] OR $last_goals <> $row['goals'] OR $last_goals_againts <> $row['goals_against'])
		{
			$current_rank = $rank . '.';
		}
		else
		{
			$current_rank = '';
		}
		$last_points = $row['points'];
		$last_goals = $row['goals'];
		$last_goals_againts = $row['goals_against'];
		$row_class = (!($rank % 2)) ? 'bg1 row_light' : 'bg2 row_dark';
		if ($row['team_symbol'] <> '')
		{
			$logo = "<img src=\"" . $ext_path . 'images/flags/' . $row['team_symbol'] . "\" alt=\"" . $row['team_symbol'] . "\" width=\"28\" height=\"28\"/>" ;
		}
		else
		{
			$logo = "<img src=\"" . $ext_path . "images/flags/blank.gif\" alt=\"\" width=\"28\" height=\"28\"/>" ;
		}

		$template->assign_block_vars('form', array(
			'RANK' 			=> $current_rank,
			'ROW_CLASS' 	=> $row_class,
			'LOGO' 			=> $logo,
			'TEAM_ID' 		=> $row['team_id'],
			'TEAM' 			=> $row['team_name'],
			'TEAM_SHORT' 	=> $row['team_name_short'],
			'U_PLAN_TEAM'	=> $this->helper->route('football_football_popup', array('popside' => 'viewplan_popup', 's' => $season, 'l' => $row['league'],
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
// Get home-table-information
$sql = 'SELECT
		t.*,
		SUM(1) AS matches,
		SUM(IF(goals_home + 0 > goals_guest, 1, 0)) AS win,
		SUM(IF(goals_home = goals_guest, 1, 0)) AS draw,
		SUM(IF(goals_home + 0 < goals_guest, 1, 0)) AS lost,
		SUM(IF(goals_home + 0 > goals_guest, 3, IF(goals_home = goals_guest, 1, 0))) AS points,
		SUM(goals_home - goals_guest) AS goals_diff,
		SUM(goals_home) AS goals,
		SUM(goals_guest) AS goals_against
	FROM ' . FOOTB_TEAMS . ' AS t
	LEFT JOIN ' . FOOTB_MATCHES . " AS m ON (m.season = t.season AND m.league = t.league 
											AND m.team_id_home = t.team_id AND m.group_id = t.group_id)
	WHERE t.season = $season 
		AND t.league = $league 
		AND m.matchday <= $matchday 
		AND m.status IN (2, 3,5,6)
	GROUP BY t.team_id
	ORDER BY t.group_id ASC, points DESC, goals_diff DESC, goals DESC, t.team_name ASC";
	
$result = $db->sql_query($sql);
$last_group = '';
$current_rank = 0;
$last_goals = 0;
$last_goals_againts = 0;
$last_points = 0;
while($row = $db->sql_fetchrow($result))
{
	if ($last_group != $row['group_id'])
	{
		$last_group = $row['group_id'];
		$rank = 0;
		$last_goals = 0;
		$last_goals_againts = 0;
		$last_points = 0;
		$template->assign_block_vars('home', array(
			'GROUP' => sprintf($user->lang['GROUP']) . ' ' . $row['group_id'],
			)
		);
	}
	if ($league_type != 2 OR $row['group_id'] != '')
	{
		$rank++;
		if ($last_points <> $row['points'] OR $last_goals <> $row['goals'] OR $last_goals_againts <> $row['goals_against'])
		{
			$current_rank = $rank . '.';
		}
		else
		{
			$current_rank = '';
		}
		$last_points = $row['points'];
		$last_goals = $row['goals'];
		$last_goals_againts = $row['goals_against'];
		$row_class = (!($rank % 2)) ? 'bg1 row_light' : 'bg2 row_dark';
		if ($row['team_symbol'] <> '')
		{
			$logo = "<img src=\"" . $ext_path . 'images/flags/' . $row['team_symbol'] . "\" alt=\"" . $row['team_symbol'] . "\" width=\"28\" height=\"28\"/>" ;
		}
		else
		{
			$logo = "<img src=\"" . $ext_path . "images/flags/blank.gif\" alt=\"\" width=\"28\" height=\"28\"/>" ;
		}

		$template->assign_block_vars('home', array(
			'RANK' 			=> $current_rank,
			'ROW_CLASS' 	=> $row_class,
			'LOGO' 			=> $logo,
			'TEAM_ID' 		=> $row['team_id'],
			'TEAM' 			=> $row['team_name'],
			'TEAM_SHORT' 	=> $row['team_name_short'],
			'U_PLAN_TEAM'	=> $this->helper->route('football_football_popup', array('popside' => 'viewplan_popup', 's' => $season, 'l' => $row['league'],
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
// Get away-table-information
$sql = 'SELECT
		t.*,
		SUM(1) AS matches,
		SUM(IF(goals_home + 0 < goals_guest, 1, 0)) AS win,
		SUM(IF(goals_home = goals_guest, 1, 0)) AS draw,
		SUM(IF(goals_home + 0 > goals_guest, 1, 0)) AS lost,
		SUM(IF(goals_home + 0 < goals_guest, 3, IF(goals_home = goals_guest, 1, 0))) AS points,
		SUM(goals_guest - goals_home) AS goals_diff,
		SUM(goals_guest) AS goals,
		SUM(goals_home) AS goals_against
	FROM ' . FOOTB_TEAMS . ' AS t
	LEFT JOIN ' . FOOTB_MATCHES . " AS m ON (m.season = t.season AND m.league = t.league 
											AND m.team_id_guest = t.team_id AND m.group_id = t.group_id)
	WHERE t.season = $season 
		AND t.league = $league 
		AND m.matchday <= $matchday 
		AND m.status IN (2, 3,5,6)
	GROUP BY t.team_id
	ORDER BY t.group_id ASC, points DESC, goals_diff DESC, goals DESC, t.team_name ASC";
	
$result = $db->sql_query($sql);
$last_group = '';
$current_rank = 0;
$last_goals = 0;
$last_goals_againts = 0;
$last_points = 0;
while($row = $db->sql_fetchrow($result))
{
	if ($last_group != $row['group_id'])
	{
		$last_group = $row['group_id'];
		$rank = 0;
		$last_goals = 0;
		$last_goals_againts = 0;
		$last_points = 0;
		$template->assign_block_vars('away', array(
			'GROUP' => sprintf($user->lang['GROUP']) . ' ' . $row['group_id'],
			)
		);
	}
	if ($league_type != 2 OR $row['group_id'] != '')
	{
		$rank++;
		if ($last_points <> $row['points'] OR $last_goals <> $row['goals'] OR $last_goals_againts <> $row['goals_against'])
		{
			$current_rank = $rank . '.';
		}
		else
		{
			$current_rank = '';
		}
		$last_points = $row['points'];
		$last_goals = $row['goals'];
		$last_goals_againts = $row['goals_against'];
		$row_class = (!($rank % 2)) ? 'bg1 row_light' : 'bg2 row_dark';
		if ($row['team_symbol'] <> '')
		{
			$logo = "<img src=\"" . $ext_path . 'images/flags/' . $row['team_symbol'] . "\" alt=\"" . $row['team_symbol'] . "\" width=\"28\" height=\"28\"/>" ;
		}
		else
		{
			$logo = "<img src=\"" . $ext_path . "images/flags/blank.gif\" alt=\"\" width=\"28\" height=\"28\"/>" ;
		}

		$template->assign_block_vars('away', array(
			'RANK' 			=> $current_rank,
			'ROW_CLASS' 	=> $row_class,
			'LOGO' 			=> $logo,
			'TEAM_ID' 		=> $row['team_id'],
			'TEAM' 			=> $row['team_name'],
			'TEAM_SHORT' 	=> $row['team_name_short'],
			'U_PLAN_TEAM'	=> $this->helper->route('football_football_popup', array('popside' => 'viewplan_popup', 's' => $season, 'l' => $row['league'],
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
$sidename = sprintf($user->lang['TABLE']);
$template->assign_vars(array(
	'S_DISPLAY_TABLE' 			=> true,
	'S_SIDENAME' 				=> $sidename,
	'S_DATA_TABLE' 				=> $data_table,
	'S_DATA_FORM' 				=> $data_form,
	'SEASON' 					=> $season,
	'LEAGUE' 					=> $league,
	'TEXT_FORM' 				=> $text_form,
	)
);
