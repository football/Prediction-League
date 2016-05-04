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

$data_odds 	= false;
$matchnumber = 0;
$lang_dates 	= $user->lang['datetime'];

$sql = "SELECT
		m.league,
		m.match_no,
		CONCAT(
			CASE DATE_FORMAT(m.match_datetime,'%w')
				WHEN 0 THEN '" . $lang_dates['Sun'] . "'
				WHEN 1 THEN '" . $lang_dates['Mon'] . "'
				WHEN 2 THEN '" . $lang_dates['Tue'] . "'
				WHEN 3 THEN '" . $lang_dates['Wed'] . "'
				WHEN 4 THEN '" . $lang_dates['Thu'] . "'
				WHEN 5 THEN '" . $lang_dates['Fri'] . "'
				WHEN 6 THEN '" . $lang_dates['Sat'] . "'
				ELSE 'Error' END,
			DATE_FORMAT(m.match_datetime,' %d.%m. %H:%i')
		) AS match_time,
		t1.team_symbol AS home_symbol,
		t2.team_symbol AS guest_symbol,
		t1.team_name_short AS home_name,
		t2.team_name_short AS guest_name,
		t1.team_id AS home_id,
		t2.team_id AS guest_id,
		m.goals_home,
		m.goals_guest,
		m.goals_overtime_home AS kogoals_home,
		m.goals_overtime_guest AS kogoals_guest,
		m.ko_match,
		m.group_id,
		m.formula_home,
		m.formula_guest,
		m.odd_1,
		m.odd_x,
		m.odd_2,
		m.trend	FROM " . FOOTB_MATCHES . ' AS m
	LEFT JOIN ' . FOOTB_TEAMS . ' AS t1 ON (t1.season = m.season AND t1.league = m.league AND t1.team_id = m.team_id_home)
	LEFT JOIN ' . FOOTB_TEAMS . " AS t2 ON (t2.season = m.season AND t2.league = m.league AND t2.team_id = m.team_id_guest)
	WHERE  m.season = $season 
		AND m.league = $league
		AND m.matchday = $matchday
	ORDER BY m.match_datetime ASC, m.match_no ASC";
	
$result = $db->sql_query($sql);
$rows = $db->sql_fetchrowset($result);

$league_info = league_info($season, $league);
$ext_path = $this->phpbb_path_helper->update_web_root_path($this->phpbb_extension_manager->get_extension_path('football/football', true));
//while($row = $db->sql_fetchrow($result))
foreach ($rows as $row)
{
	$data_odds = true;
	$matchnumber++ ;
	$row_class = (!($matchnumber % 2)) ? 'bg1 row_light' : 'bg2 row_dark';
	if (0 == $row['home_id'])
	{
		$home_info 		= get_team($season, $league, $row['match_no'], 'team_id_home', $row['formula_home']);
		$home_in_array 	= explode("#",$home_info);
		$homelogo 		= $home_in_array[0];
		$homeid 		= $home_in_array[1];
		$homename 		= $home_in_array[2];
	}
	else
	{
		$homelogo 	= $row['home_symbol'];
		$homeid 	= $row['home_id'];
		$homename 	= $row['home_name'];
	}
	if (0 == $row['guest_id'])
	{
		$guest_info 	= get_team($season, $league, $row['match_no'], 'team_id_guest', $row['formula_guest']);
		$guest_in_array = explode("#",$guest_info);
		$guestlogo 		= $guest_in_array[0];
		$guestid 		= $guest_in_array[1];
		$guestname 		= $guest_in_array[2];
	}
	else
	{
		$guestlogo 	= $row['guest_symbol'];
		$guestid 	= $row['guest_id'];
		$guestname 	= $row['guest_name'];
	}
	if ($homelogo <> '')
	{
		$logoH = "<img src=\"" . $ext_path . 'images/flags/' . $homelogo . "\" alt=\"" . $homelogo . "\" width=\"28\" height=\"28\"/>" ;
	}
	else
	{
		$logoH = "<img src=\"" . $ext_path . "images/flags/blank.gif\" alt=\"\" width=\"28\" height=\"28\"/>" ;
	}
	if ($guestlogo <> '')
	{
		$logoG = "<img src=\"" . $ext_path . 'images/flags/' . $guestlogo . "\" alt=\"" . $guestlogo . "\" width=\"28\" height=\"28\"/>" ;
	}
	else
	{
		$logoG = "<img src=\"" . $ext_path . "images/flags/blank.gif\" alt=\"\" width=\"28\" height=\"28\"/>" ;
	}

	if ($row['ko_match'])
	{
		$display_ko = true;
		$ko_match = true;
	}
	else
	{
		$ko_match = false;
	}

	if ($row['group_id'] == '')
	{
		$group_id = '&nbsp;';
	}
	else
	{
		$display_group = true;
		$group_id = $row['group_id'];
	}

	$edit_match 	= false;
	$goals_home		= ($row['goals_home'] == '') ? '&nbsp;' : $row['goals_home'];
	$goals_guest	= ($row['goals_guest'] == '') ? '&nbsp;' : $row['goals_guest'];
	$kogoals_home	= ($row['kogoals_home'] == '') ? '&nbsp;' : $row['kogoals_home'];
	$kogoals_guest	= ($row['kogoals_guest'] == '') ? '&nbsp;' : $row['kogoals_guest'];

	$template->assign_block_vars('odds', array(
		'ROW_CLASS' 	=> $row_class,
		'MATCH_NUMBER' 	=> $row['match_no'],
		'MATCHDAY' 		=> $matchday,
		'MATCH_TIME' 	=> $row['match_time'],
		'GROUP' 		=> $group_id,
		'HOME_ID' 		=> $homeid,
		'GUEST_ID' 		=> $guestid,
		'LOGO_HOME' 	=> $logoH,
		'LOGO_GUEST' 	=> $logoG,
		'HOME_NAME' 	=> $homename,
		'GUEST_NAME' 	=> $guestname,
		'U_PLAN_HOME'	=> $this->helper->route('football_football_popup', array('popside' => 'viewplan_popup', 's' => $season, 'l' => $league,
																			'tid' => $homeid, 'mode' => 'all')),
		'U_PLAN_GUEST'	=> $this->helper->route('football_football_popup', array('popside' => 'viewplan_popup', 's' => $season, 'l' => $league,
																			'tid' => $guestid, 'mode' => 'all')),
		'GOALS_HOME' 	=> $goals_home,
		'GOALS_GUEST'	=> $goals_guest,
		'COLOR_STYLE' 	=> '',
		'KOGOALS_HOME' 	=> $kogoals_home,
		'KOGOALS_GUEST'	=> $kogoals_guest,
		'S_KO_MATCH' 	=> $ko_match,
		'TREND' 		=> $row['trend'],
		'U_MATCH_STATS'	=> $this->helper->route('football_football_popup', array('popside' => 'hist_popup', 's' => $season, 'l' => $league,
																			'hid' => $homeid, 'gid' => $guestid, 'm' => $matchday, 
																			'mn' => $row['match_no'], 'gr' => $row['group_id'])),
		'ODD_1' 		=> $row['odd_1'],
		'ODD_X' 		=> $row['odd_x'],
		'ODD_2' 		=> $row['odd_2'],
		)
	);
}
$db->sql_freeresult($result);

$sidename = 'Chancen';
switch ($league_info['bet_ko_type'])
{
	case BET_KO_90:
		$result_explain = sprintf($user->lang['MIN90']);
		$label_finalresult = sprintf($user->lang['EXTRATIME_SHORT']) . '/' . sprintf($user->lang['PENALTY_SHORT']);
		break;
	case BET_KO_EXTRATIME:
		$result_explain = sprintf($user->lang['EXTRATIME_SHORT']);
		$label_finalresult = sprintf($user->lang['PENALTY']);
		break;
	case BET_KO_PENALTY:
		$result_explain = sprintf($user->lang['PENALTY']);
		$display_ko = false;
		break;
	default:
		$result_explain = sprintf($user->lang['MIN90']);
		$label_finalresult = sprintf($user->lang['EXTRATIME_SHORT']) . '/' . sprintf($user->lang['PENALTY_SHORT']);
		break;
}

$template->assign_vars(array(
	'S_DISPLAY_ODDS' 			=> true,
	'S_SIDENAME' 				=> $sidename,
	'RESULT_EXPLAIN' 			=> $result_explain,
	'LABEL_FINALRESULT' 		=> $label_finalresult,
	'U_LEFT' 					=> $this->helper->route('football_main_controller', array('side' => 'bet', 's' => $season, 'l' => $league, 'm' => $matchday)),
	'LEFT_LINK' 				=> '&lt; ' . sprintf($user->lang['BET']),
	'U_RIGHT' 					=> $this->helper->route('football_main_controller', array('side' => 'table', 's' => $season, 'l' => $league, 'm' => $matchday)),
	'RIGHT_LINK' 				=> sprintf($user->lang['TABLE']) . ' &gt;',
	'LEFT_TITLE' 				=> sprintf($user->lang['TITLE_BET']),
	'RIGHT_TITLE' 				=> sprintf($user->lang['TITLE_TABLE']),
	'S_DATA_ODDS' 				=> $data_odds,
	)
);

?>