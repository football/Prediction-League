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

$data = false;
$user1 = '';
$user2 = '';
$user3 = '';
$user4 = '';
$username  = '';
$username2 = '';
$username3 = '';
$username4 = '';
// Calculate rank total
$sql = 'SELECT
		r.user_id,
		u.username,
		SUM(r.points) AS points_total
	FROM ' . FOOTB_RANKS . ' AS r 
	LEFT JOIN ' . USERS_TABLE . " AS u ON (u.user_id = r.user_id)
	WHERE r.season = $season 
		AND r.league = $league 
		AND r.matchday <= $matchday
	GROUP BY r.user_id
	ORDER BY points_total DESC, LOWER(u.username) ASC";
	
$result = $db->sql_query($sql);
$current_ranks = $db->sql_fetchrowset($result);
$total_users = sizeof($current_ranks);
if ($total_users > 3 AND $total_users <= 50)
{
	$data = true;
	$middle = round($total_users / 2,0);
	// If user = leader then first = seconde
	$user_first = $current_ranks[0]['user_id'];
	if ($user_first == $user->data['user_id'])
		$user_first = $current_ranks[1]['user_id'];
	// If user = middle then middle = middle - 1
	$user_middle = $current_ranks[$middle-1]['user_id'];
	if ($user_middle == $user->data['user_id'])
		$user_middle = $current_ranks[$middle]['user_id'];
	// If user = last then last  = last but one
	$user_last = $current_ranks[$total_users - 1]['user_id'];
	if ($user_last == $user->data['user_id'])
		$user_last = $current_ranks[$total_users - 2]['user_id'];

	if (user_is_member($user->data['user_id'], $season, $league))
	{
		// Take user, leader, middle and last
		$user1 = $this->request->variable('user1', $user->data['user_id']);
		$user2 = $this->request->variable('user2', $user_first);
		$user3 = $this->request->variable('user3', $user_middle);
		$user4 = $this->request->variable('user4', $user_last);
	}
	else
	{
		// Only take leader, middle and last
		$user1 = $this->request->variable('user1', $user_first);
		$user2 = $this->request->variable('user2', $user_middle);
		$user3 = $this->request->variable('user3', $user_last);
		$user4 = $this->request->variable('user4', 0);
	}

	// Add empty choice
	$template->assign_block_vars('form_user2', array(
		'S_USERNAME' 	=> sprintf($user->lang['OPTION_USER']),
		'S_USERID' 		=> 0,
		'S_SELECTEDID2' => '',
		)
	);
	$template->assign_block_vars('form_user3', array(
		'S_USERNAME' 	=> sprintf($user->lang['OPTION_USER']),
		'S_USERID' 		=> 0,
		'S_SELECTEDID3' => '',
		)
	);
	$template->assign_block_vars('form_user4', array(
		'S_USERNAME' 	=> sprintf($user->lang['OPTION_USER']),
		'S_USERID' 		=> 0,
		'S_SELECTEDID4' => '',
		)
	);

	// Start select user
	foreach ($current_ranks as $rank_user)
	{
		$curr_userid =$rank_user['user_id'];
		if ($user1 == $curr_userid)
		{
			$selectid1 = ' selected="selected"';
			$username  = $rank_user['username'];
		}
		else
		{
			$selectid1 = '';
		}
		if ($user2 == $curr_userid)
		{
			$selectid2 = ' selected="selected"';
			$username2 = $rank_user['username'];
		}
		else
		{
			$selectid2 = '';
		}
		if ($user3 == $curr_userid)
		{
			$selectid3 = ' selected="selected"';
			$username3 = $rank_user['username'];
		}
		else
		{
			$selectid3 = '';
		}
		if ($user4 == $curr_userid)
		{
			$selectid4 = ' selected="selected"';
			$username4 = $rank_user['username'];
		}
		else
		{
			$selectid4 = '';
		}
		if ($curr_userid != $user2 AND $curr_userid != $user3 AND $curr_userid != $user4)
			$template->assign_block_vars('form_user1', array(
				'S_USERNAME' 	=> $rank_user['username'],
				'S_USERID' 		=> $curr_userid,
				'S_SELECTEDID' 	=> $selectid1));
		if ($curr_userid != $user1 AND $curr_userid != $user3 AND $curr_userid != $user4)
			$template->assign_block_vars('form_user2', array(
				'S_USERNAME' 	=> $rank_user['username'],
				'S_USERID' 		=> $curr_userid,
				'S_SELECTEDID' 	=> $selectid2));
		if ($curr_userid != $user1 AND $curr_userid != $user2 AND $curr_userid != $user4)
			$template->assign_block_vars('form_user3', array(
				'S_USERNAME' 	=> $rank_user['username'],
				'S_USERID' 		=> $curr_userid,
				'S_SELECTEDID' 	=> $selectid3));
		if ($curr_userid != $user1 AND $curr_userid != $user2 AND $curr_userid != $user3)
			$template->assign_block_vars('form_user4', array(
				'S_USERNAME' 	=> $rank_user['username'],
				'S_USERID' 		=> $curr_userid,
				'S_SELECTEDID' 	=> $selectid4));
	}

	$ranks_total_1 	= '';
	$ranks_dayl_1 	= '';
	$points_1 		= '';
	$sql = 'SELECT *
		FROM ' . FOOTB_RANKS . " 
		WHERE season = $season 
			AND league = $league 
			AND matchday <= $matchday 
			AND user_id = $user1
		ORDER BY matchday ASC";
		
	$result = $db->sql_query($sql);

	while($row = $db->sql_fetchrow($result))
	{
		$points_1 		= $points_1. $row['points']. ',';
		$ranks_total_1 	= $ranks_total_1. $row['rank_total']. ',';
		$ranks_dayl_1 	= $ranks_dayl_1. $row['rank']. ',';
	}
	$points_1 		= substr($points_1, 0, strlen($points_1) - 1);
	$ranks_total_1 	= substr($ranks_total_1, 0, strlen($ranks_total_1) - 1);
	$ranks_dayl_1 	= substr($ranks_dayl_1, 0, strlen($ranks_dayl_1) - 1);
	$db->sql_freeresult($result);

	$ranks_total_2 	= '';
	$ranks_dayl_2 	= '';
	$points_2 		= '';
	if ($user2 != 0)
	{
		$sql = 'SELECT *
			FROM ' . FOOTB_RANKS . " 
			WHERE season = $season 
				AND league = $league 
				AND matchday <= $matchday 
				AND user_id = $user2
			ORDER BY matchday ASC";
			
		$result = $db->sql_query($sql);

		while($row = $db->sql_fetchrow($result))
		{
			$points_2 		= $points_2 . $row['points']. ',';
			$ranks_total_2 	= $ranks_total_2 . $row['rank_total']. ',';
			$ranks_dayl_2 	= $ranks_dayl_2 . $row['rank']. ',';
		}
		$points_2 		= substr($points_2, 0, strlen($points_2) - 1);
		$ranks_total_2 	= substr($ranks_total_2, 0, strlen($ranks_total_2) - 1);
		$ranks_dayl_2 	= substr($ranks_dayl_2, 0, strlen($ranks_dayl_2) - 1);
		$db->sql_freeresult($result);
	}

	$ranks_total_3 	= '';
	$ranks_dayl_3 	= '';
	$points_3 		= '';
	if ($user3 != 0)
	{
		$sql = 'SELECT *
			FROM ' . FOOTB_RANKS . " 
			WHERE season = $season 
				AND league = $league 
				AND matchday <= $matchday 
				AND user_id = $user3
			ORDER BY matchday ASC";
			
		$result = $db->sql_query($sql);

		while($row = $db->sql_fetchrow($result))
		{
			$points_3 		= $points_3. $row['points']. ',';
			$ranks_total_3 	= $ranks_total_3. $row['rank_total']. ',';
			$ranks_dayl_3 	= $ranks_dayl_3. $row['rank']. ',';
		}
		$points_3 		= substr($points_3,0,strlen($points_3)-1);
		$ranks_total_3 	= substr($ranks_total_3,0,strlen($ranks_total_3)-1);
		$ranks_dayl_3 	= substr($ranks_dayl_3,0,strlen($ranks_dayl_3)-1);
		$db->sql_freeresult($result);
	}

	$ranks_total_4 	= '';
	$ranks_dayl_4 	= '';
	$points_4 		= '';
	if ($user4 != 0)
	{
		$sql = 'SELECT *
			FROM ' . FOOTB_RANKS . " 
			WHERE season = $season 
				AND league = $league 
				AND matchday <= $matchday 
				AND user_id = $user4
			ORDER BY matchday ASC";
			
		$result = $db->sql_query($sql);

		while($row = $db->sql_fetchrow($result))
		{
			$points_4 		= $points_4. $row['points']. ',';
			$sptagplatz 	= $row['rank'];
			$ranks_total_4 	= $ranks_total_4. $row['rank_total']. ',';
			$ranks_dayl_4 	= $ranks_dayl_4. $row['rank']. ',';
		}
		$points_4 		= substr($points_4,0,strlen($points_4)-1);
		$ranks_total_4 	= substr($ranks_total_4,0,strlen($ranks_total_4)-1);
		$ranks_dayl_4 	= substr($ranks_dayl_4,0,strlen($ranks_dayl_4)-1);
		$db->sql_freeresult($result);
	}

	$min = '';
	$max = '';
	if ($user1 != 0)
	{
		$sql = 'SELECT
				MIN(points) As points_min,
				MAX(points) As points_max
			FROM ' . FOOTB_RANKS . " 
			WHERE season = $season 
				AND league = $league 
				AND matchday <= $matchday
			GROUP BY matchday
			ORDER BY matchday ASC";
			
		$result = $db->sql_query($sql);

		while($row = $db->sql_fetchrow($result))
		{
			$min = $min. $row['points_min']. ',';
			$max = $max. $row['points_max']. ',';
		}
		$min = substr($min,0,strlen($min)-1);
		$max = substr($max,0,strlen($max)-1);
		$db->sql_freeresult($result);
	}
	// Create and display charts
	$chart= "<img src='". generate_board_url() . '/' . $this->football_root_path
		. "includes/chart_rank.php?t=$total_users&amp;m=$matchday&amp;v1=$ranks_total_1&amp;v2=$ranks_total_2&amp;v3=$ranks_total_3&amp;v4=$ranks_total_4&amp;c=" 
		. sprintf($user->lang['PLACE']) . "' alt='" 
		. sprintf($user->lang['CHART_TOTAL'])
		. "'/>";
	$template->assign_block_vars('chart_rank', array(
		'CHARTIMAGE' => $chart,
		)
	);
	$chart= "<img src='". generate_board_url() . '/' . $this->football_root_path
		. "includes/chart_rank.php?t=$total_users&amp;m=$matchday&amp;v1=$ranks_dayl_1&amp;v2=$ranks_dayl_2&amp;v3=$ranks_dayl_3&amp;v4=$ranks_dayl_4&amp;c=" 
		. sprintf($user->lang['PLACE']) . "' alt='" 
		. sprintf($user->lang['CHART_MATCHDAY'])
		. "'/>";
	$template->assign_block_vars('chart_matchtdays', array(
		'CHARTIMAGE' => $chart,
		)
	);
	$chart= "<img src='". generate_board_url() . '/' . $this->football_root_path
		. "includes/chart_points.php?m=$matchday&amp;v1=$points_1&amp;v2=$points_2&amp;v3=$points_3&amp;v4=$points_4&amp;min=$min&amp;max=$max&amp;c=" 
		. sprintf($user->lang['POINTS']) . ',' . sprintf($user->lang['BANDWIDTH']) . "' alt='" 
		. sprintf($user->lang['CHART_POINTS'])
		. "'/>";
	$template->assign_block_vars('chart_points', array(
		'CHARTIMAGE' => $chart,
		)
	);
}
$sidename = sprintf($user->lang['MY_CHART']);
$template->assign_vars(array(
	'S_DISPLAY_MY_CHART' 		=> true,
	'S_SIDENAME' 				=> $sidename,
	'U_LEFT' 					=> $this->helper->route('football_main_controller', array('side' => 'my_rank', 's' => $season, 'l' => $league, 'm' => $matchday)),
	'LEFT_LINK' 				=> '&lt; ' . sprintf($user->lang['MY_RANK']),
	'U_RIGHT' 					=> $this->helper->route('football_main_controller', array('side' => 'my_koeff', 's' => $season, 'l' => $league, 'm' => $matchday)),
	'RIGHT_LINK' 				=> sprintf($user->lang['MY_KOEFF']) . ' &gt;',
	'LEFT_TITLE' 				=> sprintf($user->lang['TITLE_MY_RANKS']),
	'RIGHT_TITLE' 				=> sprintf($user->lang['TITLE_MY_KOEFF']),
	'S_DATA_MY_CHART' 			=> $data,
	'SEASON' 					=> $season,
	'LEAGUE' 					=> $league,
	'S_USER1' 					=> $user1,
	'S_USER2' 					=> $user2,
	'S_USER3' 					=> $user3,
	'S_USER4' 					=> $user4,
	'USERNAME1' 				=> $username,
	'USERNAME2' 				=> $username2,
	'USERNAME3' 				=> $username3,
	'USERNAME4' 				=> $username4,
	)
);
?>