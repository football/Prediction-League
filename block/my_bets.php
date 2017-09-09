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
	FROM ' . FOOTB_BETS . ' AS b
	LEFT JOIN ' . USERS_TABLE . " AS u ON (u.user_id = b.user_id)
	WHERE season = $season AND league = $league
	ORDER BY LOWER(u.username) ASC";

$numb_users = 0;		
$result = $db->sql_query($sql);
while($row = $db->sql_fetchrow($result))
{
	$numb_users++;
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
		'S_USER' => $row['user_id'],
		'S_USERNAME' => $row['username'],
		'S_SELECTEDID' => $selectid,
		)
	);
}
$db->sql_freeresult($result);

// All bets of selected user group by bet
$rank 				= 0;
$bets_home_win 		= 0;
$bets_draw 			= 0;
$bets_guest_win 	= 0;
$win_home_win 		= 0;
$win_draw 			= 0;
$win_guest_win 		= 0;
$points_home_win 	= 0;
$points_draw 		= 0;
$points_guest_win 	= 0;

$sql = 'SELECT
		COUNT(b.match_no) AS bets,
		b.goals_home,
		b.goals_guest,
		SUM(IF((b.goals_home + 0 < b.goals_guest) <> (m.goals_home + 0 < m.goals_guest) 
				OR (b.goals_home = b.goals_guest) <> (m.goals_home = m.goals_guest)
				OR (b.goals_home + 0 > b.goals_guest) <> (m.goals_home + 0 > m.goals_guest),
				0,
				IF((b.goals_home = m.goals_home) AND (b.goals_guest = m.goals_guest), 1, 0)
			)
		) AS hits,
		SUM(IF((b.goals_home + 0 < b.goals_guest) <> (m.goals_home + 0 < m.goals_guest) 
				OR (b.goals_home = b.goals_guest) <> (m.goals_home = m.goals_guest)
				OR (b.goals_home + 0 > b.goals_guest) <> (m.goals_home + 0 > m.goals_guest),
				0,
				IF((b.goals_home = m.goals_home) AND (b.goals_guest = m.goals_guest), 0, 1)
			)
		) AS tendencies,
		' . select_points('m',true) . '
	FROM ' . FOOTB_BETS . ' AS b
	LEFT JOIN ' . FOOTB_MATCHES . " AS m ON (m.season = b.season AND m.league = b.league AND m.match_no = b.match_no)
	WHERE b.season = $season 
		AND b.league = $league 
		AND b.goals_home <> '' 
		AND b.goals_guest <> '' 
		AND m.status = 3 
		AND b.user_id = $user_sel 
		AND m.matchday <= $matchday
	GROUP by b.goals_home, b.goals_guest
	ORDER by bets DESC";
	
$result = $db->sql_query($sql);

while($row = $db->sql_fetchrow($result))
{
	if ($row['goals_home'] > $row['goals_guest'])
	{
		$bets_home_win 		+= $row['bets'];
		$win_home_win 		+= $row['hits'] + $row['tendencies'];
		$points_home_win 	+= $row['points'];
	}
	if ($row['goals_home'] == $row['goals_guest'])
	{
		$bets_draw 			+= $row['bets'];
		$win_draw 			+= $row['hits'] + $row['tendencies'];
		$points_draw 		+= $row['points'];
	}
	if ($row['goals_home'] < $row['goals_guest'])
	{
		$bets_guest_win 	+= $row['bets'];
		$win_guest_win 		+= $row['hits'] + $row['tendencies'];
		$points_guest_win 	+= $row['points'];
	}
	$rank++;
	$row_class = (!($rank % 2)) ? 'bg1 row_light' : 'bg2 row_dark';
	$template->assign_block_vars('bets', array(
		'ROW_CLASS' 	=> $row_class,
		'GOALSHOME' 	=> $row['goals_home'],
		'GOALSGUEST' 	=> $row['goals_guest'],
		'COUNT' 		=> $row['bets'],
		'DIRECTHITS' 	=> $row['hits'],
		'TENDENCIES' 	=> $row['tendencies'],
		'TOTAL' 		=> $row['hits'] + $row['tendencies'],
		'POINTS' 		=> $row['points'],
		'AVERAGE' 		=> round($row['points'] / $row['bets'],1),
		)
	);
}
$db->sql_freeresult($result);

// Tendencies of all results
$sql = "SELECT
		SUM(IF(goals_home + 0 > goals_guest,1,0)) AS SUM_HOME_WIN,
		SUM(IF(goals_home = goals_guest,1,0)) AS SUM_DRAW,
		SUM(IF(goals_home + 0 < goals_guest,1,0)) AS SUM_GUEST_WIN
	FROM " . FOOTB_MATCHES . " 
	WHERE season = $season 
		AND league = $league 
		AND status = 3 
		AND matchday <= $matchday";
		
$result = $db->sql_query($sql);

while($row = $db->sql_fetchrow($result))
{
	$template->assign_block_vars('bets_wdl', array(
		'ROW_CLASS' 	=> 'bg2 row_dark',
		'SCORE' 		=> sprintf($user->lang['PLAYED']),
		'HOMEWIN' 	=> $row['SUM_HOME_WIN'],
		'DRAW' 		=> $row['SUM_DRAW'],
		'GUESTWIN' 	=> $row['SUM_GUEST_WIN'],
		)
	);
	// Muliply with user of this league
	$template->assign_block_vars('bets_wdl_all', array(
		'ROW_CLASS' 	=> 'bg2 row_dark',
		'SCORE' 		=> sprintf($user->lang['PLAYED']),
		'HOMEWIN' 	=> $row['SUM_HOME_WIN'] * $numb_users,
		'DRAW' 		=> $row['SUM_DRAW'] * $numb_users,
		'GUESTWIN' 	=> $row['SUM_GUEST_WIN'] * $numb_users,
		)
	);
}
$db->sql_freeresult($result);

// Count tendencies (bets of selected user)
$template->assign_block_vars('bets_wdl', array(
	'ROW_CLASS' 	=> 'bg1 row_light',
	'SCORE' 		=> sprintf($user->lang['GUESSED']),
	'HOMEWIN' 	=> $bets_home_win,
	'DRAW' 		=> $bets_draw,
	'GUESTWIN' 	=> $bets_guest_win,
	)
);
// Scored with tendency (bets of selected user)
$template->assign_block_vars('bets_wdl', array(
	'ROW_CLASS' 	=> 'bg2 row_dark',
	'SCORE' 		=> sprintf($user->lang['SCORED']),
	'HOMEWIN' 	=> $win_home_win,
	'DRAW' 		=> $win_draw,
	'GUESTWIN' 	=> $win_guest_win,
	)
);
// Points with tendency (bets of selected user)
$template->assign_block_vars('bets_wdl', array(
	'ROW_CLASS' 	=> 'bg1 row_light',
	'SCORE' 		=> sprintf($user->lang['POINTS']),
	'HOMEWIN' 	=> $points_home_win,
	'DRAW' 		=> $points_draw,
	'GUESTWIN' 	=> $points_guest_win,
	)
);

// All bets of all users group by bet
$rank 				= 0;
$bets_home_win 		= 0;
$bets_draw 			= 0;
$bets_guest_win 	= 0;
$win_home_win 		= 0;
$win_draw 			= 0;
$win_guest_win 		= 0;
$points_home_win 	= 0;
$points_draw 		= 0;
$points_guest_win 	= 0;

$sql = 'SELECT
		COUNT(b.match_no) AS bets,
		b.goals_home,
		b.goals_guest,
		SUM(IF((b.goals_home + 0 < b.goals_guest) <> (m.goals_home + 0 < m.goals_guest) 
				OR (b.goals_home = b.goals_guest) <> (m.goals_home = m.goals_guest) 
				OR (b.goals_home + 0 > b.goals_guest) <> (m.goals_home + 0 > m.goals_guest),
				0,
				IF((b.goals_home = m.goals_home) AND (b.goals_guest = m.goals_guest), 1, 0)
			)
		) AS hits,
		SUM(IF((b.goals_home + 0 < b.goals_guest) <> (m.goals_home + 0 < m.goals_guest) 
				OR (b.goals_home = b.goals_guest) <> (m.goals_home = m.goals_guest) 
				OR (b.goals_home + 0 > b.goals_guest) <> (m.goals_home + 0 > m.goals_guest),
				0,
				IF((b.goals_home = m.goals_home) AND (b.goals_guest = m.goals_guest), 0, 1)
			)
		) AS tendencies,
		' . select_points('m',true) . '
	FROM ' . FOOTB_BETS . ' AS b
	LEFT JOIN ' . FOOTB_MATCHES . " AS m ON (m.season = b.season AND m.league = b.league AND m.match_no = b.match_no)
	WHERE b.season = $season 
		AND b.league = $league 
		AND b.goals_home <> '' 
		AND b.goals_guest <> '' 
		AND m.status = 3 
		AND m.matchday <= $matchday
	GROUP by b.goals_home, b.goals_guest
	ORDER by bets DESC";
	
$result = $db->sql_query($sql);

while($row = $db->sql_fetchrow($result))
{
	if ($row['goals_home'] > $row['goals_guest'])
	{
		$bets_home_win 		+= $row['bets'];
		$win_home_win 		+= $row['hits'] + $row['tendencies'];
		$points_home_win 	+= $row['points'];
	}
	if ($row['goals_home'] == $row['goals_guest'])
	{
		$bets_draw 			+= $row['bets'];
		$win_draw 			+= $row['hits'] + $row['tendencies'];
		$points_draw 		+= $row['points'];
	}
	if ($row['goals_home'] < $row['goals_guest'])
	{
		$bets_guest_win 	+= $row['bets'];
		$win_guest_win 		+= $row['hits'] + $row['tendencies'];
		$points_guest_win 	+= $row['points'];
	}
	$rank++;
	$row_class = (!($rank % 2)) ? 'bg1 row_light' : 'bg2 row_dark';
	$template->assign_block_vars('allbets', array(
		'ROW_CLASS' 	=> $row_class,
		'GOALSHOME' 	=> $row['goals_home'],
		'GOALSGUEST' 	=> $row['goals_guest'],
		'COUNT' 		=> $row['bets'],
		'DIRECTHITS' 	=> $row['hits'],
		'TENDENCIES' 	=> $row['tendencies'],
		'TOTAL' 		=> $row['hits'] + $row['tendencies'],
		'POINTS' 		=> $row['points'],
		'AVERAGE' 	=> round($row['points'] / $row['bets'],1),
		)
	);
}
$db->sql_freeresult($result);

// Count tendencies (bets of all user)
$template->assign_block_vars('bets_wdl_all', array(
	'ROW_CLASS' 	=> 'bg2 row_dark',
	'SCORE' 		=> sprintf($user->lang['GUESSED']),
	'HOMEWIN' 	=> $bets_home_win,
	'DRAW' 		=> $bets_draw,
	'GUESTWIN' 	=> $bets_guest_win,
	)
);
// Scored with tendency (bets of all user)
$template->assign_block_vars('bets_wdl_all', array(
	'ROW_CLASS' 	=> 'bg1 row_light',
	'SCORE' 		=> sprintf($user->lang['SCORED']),
	'HOMEWIN' 	=> $win_home_win,
	'DRAW' 		=> $win_draw,
	'GUESTWIN' 	=> $win_guest_win,
	)
);
// Points with tendency (bets of all user)
$template->assign_block_vars('bets_wdl_all', array(
	'ROW_CLASS' 	=> 'bg2 row_dark',
	'SCORE' 		=> sprintf($user->lang['POINTS']),
	'HOMEWIN' 	=> $points_home_win,
	'DRAW' 		=> $points_draw,
	'GUESTWIN' 	=> $points_guest_win,
	)
);

$sidename = sprintf($user->lang['MY_BETS']);
$template->assign_vars(array(
	'S_DISPLAY_MY_BETS' 		=> true,
	'S_SIDENAME' 				=> $sidename,
	'S_DATA_MY_BETS' 			=> $data,
	'SEASON' 					=> $season,
	'LEAGUE' 					=> $league,
	'USERNAME' 					=> $username,
	)
);

?>