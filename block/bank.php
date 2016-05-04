<?php
/**
*
* @package phpBB Extension - Football Football
* @copyright (c) 2016 football (http://football.bplaced.net)
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

// Check Prediction League authorisation 
if ( !$this->auth->acl_get('u_use_football') )
{
	trigger_error('NO_AUTH_VIEW');

}

global $phpbb_extension_manager;
if ($phpbb_extension_manager->is_enabled('dmzx/ultimatepoints'))
{
	$this->user->add_lang_ext('dmzx/ultimatepoints', 'common');
	// Get an instance of the ultimatepoints functions_points
	$functions_points = $phpbb_container->get('dmzx.ultimatepoints.core.functions.points');
}
else
{
	// Get an instance of the football functions_points
	$functions_points = $phpbb_container->get('football.football.core.functions.points');
}

if (!$user_sel)
{
	if (user_is_member($user->data['user_id'], $season, $league) or $league == 0)
	{
		$user_sel =  $user->data['user_id'];
	}
}

$username = '';
$member = true;
if ($this->auth->acl_get('a_football_points'))
{
	$where_user = '';
	$multi_view = true;
}
else
{
	$multi_view = false;
	if (user_is_member($user->data['user_id'], $season, $league))
	{
		$where_user = ' AND b.user_id = ' . $user->data['user_id'] . ' ';
		$user_sel =  $user->data['user_id'];
	}
	else
	{
		if ($league)
		{
			$member = false;
		}
	}
}
$where_league = '';
if ($league)
{
	$where_league = " AND b.league = $league";
}

$data = false;
// Select user 
$total_users = 0;
$sql = 'SELECT DISTINCT
		u.user_id,
		u.username
	FROM ' . FOOTB_BETS . ' AS b
	LEFT JOIN ' . USERS_TABLE . " AS u ON (u.user_id = b.user_id)
	WHERE season = $season 
		$where_league
		$where_user
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
		
$where_season = '';
if ($season)
{
	$where_season = " AND fp.season = $season";
}

$where_league = '';
$order_by = 'ORDER BY fp.points_type ASC, fp.matchday ASC, fp.league ASC';

if ($league)
{
	$where_league = " AND fp.league = $league";
	$order_by = 'ORDER BY fp.league ASC, fp.matchday ASC, fp.points_type ASC';
}

// The different book types
$types	=	array(
	0			=>	'--',
	1			=>	sprintf($user->lang['FOOTBALL_BET_POINTS']),
	2			=>	$user->lang['FOOTBALL_DEPOSIT'],
	3			=>	sprintf($user->lang['FOOTBALL_WIN']),
	4			=>	$user->lang['FOOTBALL_WIN'],
	5			=>	$user->lang['FOOTBALL_WIN'],
	6			=>	$user->lang['FOOTBALL_WIN'],
	7			=>	$user->lang['FOOTBALL_PAYOUT'],
);

// Grab the football points
$sql = 'SELECT fp.season,
			s.season_name, 
			s.season_name_short,
			fp.league,
			l.league_name,
			l.league_name_short,
			fp.matchday,
			md.matchday_name,
			fp.points_type,
			fp.points,
			fp.points_comment,
			fp.cash
		FROM ' . FOOTB_POINTS . ' AS fp
		INNER JOIN ' . FOOTB_SEASONS . ' AS s ON (s.season = fp.season)
		INNER JOIN ' . FOOTB_LEAGUES . ' AS l ON (l.season = fp.season AND l.league = fp.league)
		INNER JOIN ' . FOOTB_MATCHDAYS . ' AS md ON (md.season = fp.season AND md.league = fp.league AND md.matchday = fp.matchday)
		WHERE user_id = ' . (int) $user_sel . "
		$where_season
		$where_league 
		$order_by";
$result = $db->sql_query($sql);

$current_balance = 0.00;
$count = 0;
// Start looping all the football points
while ($row = $db->sql_fetchrow($result))
{
	$count = $count + 1;
	if ($row['points_type'] == POINTS_BET OR $row['points_type'] == POINTS_PAID)
	{
		$points_sign = '-';
		$points_style = " color: red;";
		$current_balance -= $row['points'];
	}
	else
	{
		$points_sign = '+';
		$points_style = " color: green;";
		$current_balance += $row['points'];
	}
	// Add the items to the template
	$template->assign_block_vars('football', array(
		'SEASON'		=>	$season,
		'SEASON_NAME'	=>	$season_name,
		'LEAGUE'		=>	$row['league'],
		'LEAGUE_NAME'	=>	$row['league_name'],
		'LEAGUE_SHORT'	=>	$row['league_name_short'],
		'MATCHDAY'		=>	$row['matchday'],
		'MATCHDAY_NAME'	=>	($row['matchday_name'] == '') ? $row['matchday'] . '.' . sprintf($user->lang['FOOTBALL_MATCHDAY']) : $row['matchday_name'],
		'MATCHDAY_SHORT'=>	$row['matchday'] . '.' . sprintf($user->lang['MATCHDAY_SHORT']),
		'POINTS_SIGN'	=>	$points_sign,
		'POINTS_STYLE'	=>	$points_style,
		'POINTS_TYPE'	=>	$types[$row['points_type']],
		'S_CASH'		=>	$row['cash'],
		'POINTS'		=>	$functions_points->number_format_points($row['points']),
		'COMMENT'		=>	nl2br($row['points_comment']),
	));
}
$db->sql_freeresult($result);

if ($current_balance < 0)
{
	$points_style = " color: red;";
}
else
{
	$points_style = " color: green;";
}

$template->assign_block_vars('football', array(
	'SEASON'		=>	$season,
	'SEASON_NAME'	=>	'',
	'LEAGUE'		=>	$league,
	'LEAGUE_NAME'	=>	'',
	'MATCHDAY'		=>	'',
	'MATCHDAY_NAME'	=>	'',
	'POINTS_SIGN'	=>	'',
	'POINTS_STYLE'	=>	$points_style,
	'POINTS_TYPE'	=>	'',
	'S_CASH'		=>	1,
	'POINTS'		=>	$functions_points->number_format_points($current_balance),
	'COMMENT'		=>	($league == 0) ? sprintf($user->lang['FOOTBALL_BALANCES']) : sprintf($user->lang['FOOTBALL_BALANCE']),
));

$sidename = sprintf($user->lang['FOOTBALL_BANK']);
$template->assign_vars(array(
	'S_DISPLAY_BANK' 			=> true,
	'S_MATCHDAY_HIDE' 			=> true,
	'S_MEMBER' 					=> $member,
	'S_SIDENAME' 				=> $sidename,
	'S_MULTI_VIEW' 				=> $multi_view,
	'L_TOTAL_ENTRIES'			=> ($count == 1) ? $count . ' ' .sprintf($user->lang['FOOTBALL_RECORD']) : $count . ' ' .sprintf($user->lang['FOOTBALL_RECORDS']),
	'U_LEFT' 					=> $this->helper->route('football_main_controller', array('side' => 'ranks_total', 's' => $season, 'l' => $league, 'm' => $matchday)),
	'LEFT_LINK' 				=> '&lt; ' . sprintf($user->lang['RANK_TOTAL']),
	'U_RIGHT' 					=> $this->helper->route('football_main_controller', array('side' => 'my_bets', 's' => $season, 'l' => $league, 'm' => $matchday)),
	'RIGHT_LINK' 				=> sprintf($user->lang['MY_BETS']) . ' &gt;',
	'LEFT_TITLE' 				=> sprintf($user->lang['TITLE_RANK_TOTAL']),
	'RIGHT_TITLE' 				=> sprintf($user->lang['TITLE_MY_BETS']),
	'USERNAME' 					=> $username,
	'POINTS'					=> $config['football_win_name'],	
	)
);

?>