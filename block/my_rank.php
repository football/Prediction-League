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

$data = false;
// select user 
$sql = 'SELECT DISTINCT
		u.user_id,
		u.username
	FROM ' . FOOTB_BETS . ' AS w
	LEFT JOIN ' . USERS_TABLE . " AS u ON (u.user_id = w.user_id)
	WHERE season = $season 
		AND league = $league
	ORDER BY LOWER(u.username) ASC	";
	
	
$result = $db->sql_query($sql);
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
		'S_USER' 		=> $row['user_id'],
		'S_USERNAME' 	=> $row['username'],
		'S_SELECTEDID' 	=> $selectid,
		)
	);
}
$db->sql_freeresult($result);

$rank = 0;
// Get ranks 1-3 of selected user
$sql = 'SELECT
		season,
		COUNT(matchday) AS matchdays,
		SUM(points) AS points,
		SUM(IF(rank = 1, 1, 0)) AS rank1,
		SUM(IF(rank = 2, 1, 0)) AS rank2,
		SUM(IF(rank = 3, 1, 0)) AS rank3
	FROM ' . FOOTB_RANKS . " 
	WHERE season = $season 
		AND league = $league 
		AND user_id = $user_sel
	GROUP BY user_id, season
	ORDER BY season ASC";
	
$result = $db->sql_query($sql);

while($row = $db->sql_fetchrow($result))
{
	$rank++;
	$row_class = (!($rank % 2)) ? 'bg1 row_light' : 'bg2 row_dark';
	$template->assign_block_vars('myrank', array(
		'ROW_CLASS' 	=> $row_class,
		'SEASON' 		=> $row['season'],
		'MATCHDAYS' 	=> $row['matchdays'],
		'POINTS' 		=> $row['points'],
		'RANK1' 		=> $row['rank1'],
		'RANK2' 		=> $row['rank2'],
		'RANK3' 		=> $row['rank3'],
		)
	);
}
$db->sql_freeresult($result);

$rank = 0;
// Get all ranks 1-3 of selected user
$sql = 'SELECT
		COUNT(matchday) AS matchdays,
		rank
	FROM ' . FOOTB_RANKS . " 
	WHERE season = $season 
		AND league = $league 
		AND user_id = $user_sel
	GROUP BY user_id, rank
	ORDER BY rank ASC";
	
$result = $db->sql_query($sql);

while($row = $db->sql_fetchrow($result))
{
	$rank++;
	$row_class = (!($rank % 2)) ? 'bg1 row_light' : 'bg2 row_dark';
	$template->assign_block_vars('season_ranks', array(
		'ROW_CLASS' 	=> $row_class,
		'RANK' 			=> $row['rank'],
		'MATCHDAYS' 	=> $row['matchdays'],
		)
	);
}
$db->sql_freeresult($result);

$rank = 0;
// Get ranks 1-3 of all users
$sql = 'SELECT
		r.user_id,
		u.username,
		COUNT(r.matchday) AS matchdays,
		SUM(r.points) AS points,
		SUM(IF(r.rank =1, 1, 0)) AS rank1,
		SUM(IF(r.rank =2, 1, 0)) AS rank2,
		SUM(IF(r.rank =3, 1, 0)) AS rank3
	FROM ' . FOOTB_RANKS . " AS r
	LEFT JOIN " . USERS_TABLE . "  AS u ON (u.user_id = r.user_id)
	WHERE r.season = $season 
		AND r.league = $league
	GROUP BY r.user_id
	ORDER BY rank1 DESC, rank2 DESC, rank3 DESC";

$result = $db->sql_query($sql);
while($row = $db->sql_fetchrow($result))
{
	$data = true;
	$rank++;
	$row_class = (!($rank % 2)) ? 'bg1 row_light' : 'bg2 row_dark';
	if ($row['user_id'] == $user->data['user_id'])
	{
		$row_class = 'bg3  row_user';
	}
	$template->assign_block_vars('allranks', array(
		'ROW_CLASS' 	=> $row_class,
		'NAME' 			=> $row['username'],
		'MATCHDAYS' 	=> $row['matchdays'],
		'POINTS' 		=> $row['points'],
		'AVERAGE' 		=> round($row['points'] / $row['matchdays'],1),
		'RANK1' 		=> $row['rank1'],
		'RANK2' 		=> $row['rank2'],
		'RANK3' 		=> $row['rank3'],
		)
	);
}
$db->sql_freeresult($result);

$sidename = sprintf($user->lang['MY_RANK']);
$template->assign_vars(array(
	'S_DISPLAY_MY_RANK' 		=> true,
	'S_MATCHDAY_HIDE' 			=> true,
	'S_SIDENAME' 				=> $sidename,
	'U_LEFT' 					=> $this->helper->route('football_main_controller', array('side' => 'my_table', 's' => $season, 'l' => $league, 'm' => $matchday)),
	'LEFT_LINK' 				=> '&lt; ' . sprintf($user->lang['MY_TABLE']),
	'U_RIGHT' 					=> $this->helper->route('football_main_controller', array('side' => 'my_chart', 's' => $season, 'l' => $league, 'm' => $matchday)),
	'RIGHT_LINK' 				=> sprintf($user->lang['MY_CHART']) . ' &gt;',
	'LEFT_TITLE' 				=> sprintf($user->lang['TITLE_MY_TABLE']),
	'RIGHT_TITLE' 				=> sprintf($user->lang['TITLE_MY_CHART']),
	'S_DATA_MY_RANK' 			=> $data,
	'SEASON' 					=> $season,
	'LEAGUE' 					=> $league,
	'USERNAME' 					=> $username,
	)
);

?>