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

$data = false;
$ext_path = $this->phpbb_path_helper->update_web_root_path($this->phpbb_extension_manager->get_extension_path('football/football', true));

$rank = 0;
// Select results and count
$sql = 'SELECT
		COUNT(DISTINCT(m.match_no)) AS count_result,
		COUNT(m.match_no) AS count_bets,
		m.goals_home,
		m.goals_guest,
		SUM(IF((b.goals_home + 0 < b.goals_guest) <> (m.goals_home + 0 < m.goals_guest) 
				OR (b.goals_home = b.goals_guest) <> (m.goals_home = m.goals_guest) 
				OR (b.goals_home + 0 > b.goals_guest) <> (m.goals_home + 0 > m.goals_guest),
				0,
				IF((b.goals_home = m.goals_home) AND (b.goals_guest = m.goals_guest), 1, 0)
			)
		)  AS hits,
		SUM(IF((b.goals_home + 0 < b.goals_guest) <> (m.goals_home + 0 < m.goals_guest) 
				OR (b.goals_home = b.goals_guest) <> (m.goals_home = m.goals_guest) 
				OR (b.goals_home + 0 > b.goals_guest) <> (m.goals_home + 0 > m.goals_guest),
				0,
				IF((b.goals_home = m.goals_home) AND (b.goals_guest = m.goals_guest), 0, 1)
			)
		)  AS tendencies,
		' . select_points('m',true) . '
	FROM ' . FOOTB_MATCHES . ' AS m
	LEFT JOIN ' . FOOTB_BETS . " AS b ON (m.season = b.season AND m.league = b.league AND m.match_no = b.match_no)
	WHERE m.season = $season 
		AND m.league = $league 
		AND b.goals_home <> '' 
		AND b.goals_guest <> '' 
		AND (m.status IN (3,6)) 
		AND m.matchday <= $matchday
	GROUP BY m.goals_home, m.goals_guest
	ORDER BY count_bets DESC";

$result = $db->sql_query($sql);
while($row = $db->sql_fetchrow($result))
{
	$data = true;
	$rank++;
	$row_class = (!($rank % 2)) ? 'bg1 row_light' : 'bg2 row_dark';
	$template->assign_block_vars('result', array(
		'ROW_CLASS' 	=> $row_class,
		'GOALS_HOME' 	=> $row['goals_home'],
		'GOALS_GUEST' 	=> $row['goals_guest'],
		'RESULTS' 		=> $row['count_result'],
		'BETS' 			=> $row['count_bets'],
		'HITS' 			=> $row['hits'],
		'TENDENCIES' 	=> $row['tendencies'],
		'TOTAL' 		=> $row['hits'] + $row['tendencies'],
		'POINTS' 		=> $row['points'],
		'AVERAGE' 		=> round($row['points'] / $row['count_bets'],1),
		)
	);
}
$db->sql_freeresult($result);
// Get goaldifferences by team
$sql = "SELECT
		t.*,
		SUM(1) AS matches,
		SUM(IF((m.team_id_home = t.team_id), IF(goals_home + 0 > goals_guest, 1, 0), IF(goals_home + 0 < goals_guest, 1, 0))) AS wins,
		SUM(IF(goals_home = goals_guest, 1, 0)) AS draw,
		SUM(IF((m.team_id_home = t.team_id), IF(goals_home + 0 > (goals_guest+2), 1, 0), IF((goals_home + 2) < goals_guest, 1, 0))) AS plus3,
		SUM(IF((m.team_id_home = t.team_id), IF(goals_home = (goals_guest+2), 1, 0), IF((goals_home + 2) = goals_guest, 1, 0))) AS plus2,
		SUM(IF((m.team_id_home = t.team_id), IF(goals_home = (goals_guest+1), 1, 0), IF((goals_home + 1) = goals_guest, 1, 0))) AS plus1,
		SUM(IF((m.team_id_home = t.team_id), IF((goals_home + 1) = goals_guest, 1, 0), IF(goals_home = (goals_guest + 1), 1, 0))) AS minus1,
		SUM(IF((m.team_id_home = t.team_id), IF((goals_home + 2) = goals_guest, 1, 0), IF(goals_home = (goals_guest + 2), 1, 0))) AS minus2,
		SUM(IF((m.team_id_home = t.team_id), IF((goals_home + 2) < goals_guest, 1, 0), IF(goals_home + 0 > (goals_guest + 2), 1, 0))) AS minus3,
		SUM(IF(m.team_id_home = t.team_id, 
				IF(goals_home + 0 > goals_guest, 3, IF(goals_home = goals_guest, 1, 0)), 
				IF(goals_home + 0 < goals_guest, 3, IF(goals_home = goals_guest, 1, 0))
			)
		) AS points,
		SUM(IF(m.team_id_home = t.team_id, goals_home - goals_guest , goals_guest - goals_home)) AS goal_diff,
		SUM(IF(m.team_id_home = t.team_id, goals_home , goals_guest)) AS goals,
		SUM(IF(m.team_id_home = t.team_id, goals_guest , goals_home)) AS goals_get
	FROM " . FOOTB_TEAMS . ' AS t
	LEFT JOIN ' . FOOTB_MATCHES . " AS m ON (m.season = t.season AND m.league = t.league AND (m.team_id_home = t.team_id OR m.team_id_guest = t.team_id))
	WHERE t.season = $season 
		AND t.league = $league 
		AND m.season = $season 
		AND m.league = $league 
		AND m.status IN (3,6) 
		AND m.matchday <= $matchday
	GROUP BY t.team_id
	ORDER BY points DESC, goal_diff DESC, goals DESC";
	
$result = $db->sql_query($sql);
$rank = 0;
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
		'MATCHES' 		=> $row['matches'],
		'PLUS3' 		=> $row['plus3'],
		'PLUS2' 		=> $row['plus2'],
		'PLUS1' 		=> $row['plus1'],
		'DRAW' 			=> $row['draw'],
		'MINUS1' 		=> $row['minus1'],
		'MINUS2'	 	=> $row['minus2'],
		'MINUS3' 		=> $row['minus3'],
		'GOALS_DIFF' 	=> $row['goal_diff'],
		'POINTS' 		=> $row['points'],
		)
	);
}
$db->sql_freeresult($result);

$sidename = sprintf($user->lang['STAT_RESULTS']);
$template->assign_vars(array(
	'S_DISPLAY_STAT_RESULTS' 	=> true,
	'S_SIDENAME' 				=> $sidename,
	'S_DATA_STAT_RESULTS' 		=> $data,
	'SEASON' 					=> $season,
	'LEAGUE' 					=> $league,
	)
);
