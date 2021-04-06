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
$data_all = false;
$rank = 0;
$sql = 'SELECT
		r.user_id,
		u.username,
		r.*
	FROM ' . FOOTB_RANKS . ' AS r
	LEFT JOIN ' . USERS_TABLE . "  AS u ON (u.user_id = r.user_id)
	WHERE season = $season 
		AND league = $league
	ORDER BY r.points DESC";

$result = $db->sql_query_limit($sql, 20);
while($row = $db->sql_fetchrow($result))
{
	$data = true;
	$rank++;
	$row_class = (!($rank % 2)) ? 'bg1 row_light' : 'bg2 row_dark';
	if ($row['user_id'] == $user->data['user_id'])
	{
		$row_class = 'bg3  row_user';
	}
	$template->assign_block_vars('top20', array(
		'ROW_CLASS' 	=> $row_class,
		'NAME' 			=> $row['username'],
		'MATCHDAY' 		=> $row['matchday'],
		'POINTS' 		=> $row['points'],
		'WIN' 			=> $row['win'],
		'DIRECTHITS' 	=> $row['correct_result'],
		'TENDENCIES' 	=> $row['tendencies'] - $row['correct_result'],
		'TOTAL' 		=> $row['tendencies'],
		)
	);
}
$db->sql_freeresult($result);

$rank = 0;
$sql = 'SELECT
		r.user_id,
		u.username,
		r.*
	FROM ' . FOOTB_RANKS . ' AS r
	LEFT JOIN ' . USERS_TABLE . " AS u ON (u.user_id = r.user_id)
	WHERE r.season = $season 
		AND r.league = $league 
	ORDER BY r.points ASC";

$result = $db->sql_query_limit($sql, 20);
while($row = $db->sql_fetchrow($result))
{
	$rank++;
	$row_class = (!($rank % 2)) ? 'bg1 row_light' : 'bg2 row_dark';
	if ($row['user_id'] == $user->data['user_id'])
	{
		$row_class = 'bg3  row_user';
	}
	$template->assign_block_vars('flop20', array(
		'ROW_CLASS' 	=> $row_class,
		'NAME' 			=> $row['username'],
		'MATCHDAY' 		=> $row['matchday'],
		'POINTS' 		=> $row['points'],
		'DIRECTHITS' 	=> $row['correct_result'],
		'TENDENCIES' 	=> $row['tendencies'] - $row['correct_result'],
		'TOTAL' 		=> $row['tendencies'],
		)
	);
}
$db->sql_freeresult($result);

$rank = 0;
$sql = 'SELECT
		r.user_id,
		u.username,
		r.*
	FROM ' . FOOTB_RANKS . ' AS r
	LEFT JOIN ' . USERS_TABLE . "  AS u ON (u.user_id = r.user_id)
	WHERE league = $league
	ORDER BY r.points DESC";

$result = $db->sql_query_limit($sql, 20);
while($row = $db->sql_fetchrow($result))
{
	$data_all = true;
	$rank++;
	$row_class = (!($rank % 2)) ? 'bg1 row_light' : 'bg2 row_dark';
	if ($row['user_id'] == $user->data['user_id'])
	{
		$row_class = 'bg3  row_user';
	}
	$template->assign_block_vars('alltop20', array(
		'ROW_CLASS' 	=> $row_class,
		'NAME' 			=> $row['username'],
		'SEASON' 		=> $row['season'],
		'MATCHDAY' 		=> $row['matchday'],
		'POINTS' 		=> $row['points'],
		'WIN' 			=> $row['win'],
		'DIRECTHITS' 	=> $row['correct_result'],
		'TENDENCIES' 	=> $row['tendencies'] - $row['correct_result'],
		'TOTAL' 		=> $row['tendencies'],
		)
	);
}
$db->sql_freeresult($result);

$rank = 0;
$sql = 'SELECT
		r.user_id,
		u.username,
		COUNT(points) AS count_zero
	FROM ' . FOOTB_RANKS . ' AS r
	LEFT JOIN ' . USERS_TABLE . " AS u ON (u.user_id = r.user_id)
	WHERE r.league = $league 
		AND r.points = 0
	GROUP BY r.user_id
	ORDER BY count_zero DESC";

$result = $db->sql_query_limit($sql, 20);
while($row = $db->sql_fetchrow($result))
{
	$rank++;
	$row_class = (!($rank % 2)) ? 'bg1 row_light' : 'bg2 row_dark';
	if ($row['user_id'] == $user->data['user_id'])
	{
		$row_class = 'bg3  row_user';
	}
	$template->assign_block_vars('allflop20', array(
		'ROW_CLASS' => $row_class,
		'NAME' 		=> $row['username'],
		'COUNTZERO' => $row['count_zero'],
		)
	);
}
$db->sql_freeresult($result);

$league_info = league_info($season, $league);
$sidename = sprintf($user->lang['STAT_POINTS']);
$template->assign_vars(array(
	'S_DISPLAY_STAT_POINTS' 	=> true,
	'S_MATCHDAY_HIDE' 			=> true,
	'S_SIDENAME' 				=> $sidename,
	'S_WIN' 					=> ($league_info['win_matchday'] == '0') ? false : true,
	'S_DATA_STAT_POINTS' 		=> $data,
	'S_DATA_ALL_POINTS' 		=> $data_all,
	'SEASON' 					=> $season,
	'LEAGUE' 					=> $league,
	)
);
