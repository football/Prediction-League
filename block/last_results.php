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

$user_is_member = user_is_member($user->data['user_id'], $season, $league);
$edit_mode 		= false;
$display_group 	= false;
$display_ko 	= false;
$data_lastresults 	= false;
$curr_year 		= date("Y"); 
$matchnumber 	= 0;
$match_date 	= "";

$local_board_time = time() + ($config['football_time_shift'] * 3600); 
$sql = 'SELECT * FROM ' . FOOTB_MATCHDAYS . " WHERE status = 0 AND delivery_date < FROM_UNIXTIME('$local_board_time')";

// Calculate matches AND results of matchday
$sql = "SELECT
		m.season,
		m.league,
		m.matchday,
		m.status,
		m.match_datetime,
		LEFT(m.match_datetime, 10) AS match_date,
		l.league_name,
		t1.team_symbol AS home_symbol,
		t2.team_symbol AS guest_symbol,
		t1.team_name AS home_name,
		t2.team_name AS guest_name,
		t1.team_name_short AS home_short,
		t2.team_name_short AS guest_short,
		m.goals_home, 
		m.goals_guest,
		m.ko_match AS ko_match,
		m.goals_overtime_home AS kogoals_home,
		m.goals_overtime_guest AS kogoals_guest,
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
		) AS match_time
	FROM  " . FOOTB_MATCHES . ' AS m
	LEFT JOIN ' . FOOTB_LEAGUES . ' AS l ON (l.season = m.season AND l.league = m.league)
	LEFT JOIN ' . FOOTB_TEAMS . ' AS t1 ON (t1.season = m.season AND t1.league = m.league AND t1.team_id = m.team_id_home)
	LEFT JOIN ' . FOOTB_TEAMS . " AS t2 ON (t2.season = m.season AND t2.league = m.league AND t2.team_id = m.team_id_guest)
	WHERE m.match_datetime < FROM_UNIXTIME('$local_board_time')
	ORDER BY m.match_datetime DESC, m.league ASC
	LIMIT 100";
	
$result = $db->sql_query($sql);
$ext_path = $this->phpbb_path_helper->update_web_root_path($this->phpbb_extension_manager->get_extension_path('football/football', true));
while($row = $db->sql_fetchrow($result))
{
	$data_lastresults = true;
	$matchnumber++ ;
	$row_class = (!($matchnumber % 2)) ? 'bg1 row_light' : 'bg2 row_dark';
	
	if ($match_date <> $row['match_date'])
	{
		$match_date = ($match_date == "") ? $row['match_date'] : $match_date;
		if ($matchnumber > $config['football_display_last_results'] )
		{
			break;
		}
	}
	$homelogo 	= $row['home_symbol'];
	$homename 	= $row['home_name'];
	$homeshort 	= $row['home_short'];

	$guestlogo 	= $row['guest_symbol'];
	$guestname 	= $row['guest_name'];
	$guestshort	= $row['guest_short'];

	if ($homelogo <> '')
	{
		$logoH = "<img src=\"" . $ext_path . 'images/flags/' . $homelogo . "\" alt=\"" . $homelogo . "\" width=\"20\" height=\"20\"/>" ;
	}
	else
	{
		$logoH = "<img src=\"" . $ext_path . "images/flags/blank.gif\" alt=\"\" width=\"20\" height=\"20\"/>" ;
	}
	if ($guestlogo <> '')
	{
		$logoG = "<img src=\"" . $ext_path . 'images/flags/' . $guestlogo . "\" alt=\"" . $guestlogo . "\" width=\"20\" height=\"20\"/>" ;
	}
	else
	{
		$logoG = "<img src=\"" . $ext_path . "images/flags/blank.gif\" alt=\"\" width=\"20\" height=\"20\"/>" ;
	}


	$goals_home		= ($row['goals_home'] == '') ? '- ' : $row['goals_home'];
	$goals_guest	= ($row['goals_guest'] == '') ? ' -' : $row['goals_guest'];
	$kogoals_home	= ($row['kogoals_home'] == '') ? '- ' : $row['kogoals_home'];
	$kogoals_guest	= ($row['kogoals_guest'] == '') ? ' -' : $row['kogoals_guest'];
	$colorstyle 	= color_style($row['status']);
	
	$template->assign_block_vars('last_results', array(
		'ROW_CLASS' 	=> $row_class,
		'U_RESULTS_LINK'=> $this->helper->route('football_main_controller', array('side' => 'results', 's' =>  $row['season'], 'l' => $row['league'], 'm' => $row['matchday'])),
		'MATCH_DATE' 	=> $row['match_date'],
		'MATCH_TIME' 	=> $row['match_time'],
		'LEAGUE_NAME' 	=> $row['league_name'],
		'LOGO_HOME' 	=> $logoH,
		'LOGO_GUEST' 	=> $logoG,
		'HOME_NAME' 	=> $homename,
		'GUEST_NAME' 	=> $guestname,
		'HOME_SHORT' 	=> $homeshort,
		'GUEST_SHORT' 	=> $guestshort,
		'GOALS_HOME' 	=> $goals_home,
		'GOALS_GUEST'	=> $goals_guest,
		'COLOR_STYLE' 	=> color_style($row['status']),
		'KOGOALS_HOME' 	=> $kogoals_home,
		'KOGOALS_GUEST'	=> $kogoals_guest,
		'COLOR_STYLE' 	=> $colorstyle,
		)
	);
}
$db->sql_freeresult($result);



$sidename = sprintf($user->lang['LAST_RESULTS']);

$template->assign_vars(array(
	'S_DISPLAY_LAST_RESULTS' 	=> true,
	'S_SIDENAME' 				=> $sidename,
	'S_DATA_LAST_RESULTS' 		=> $data_lastresults,
	'S_USER_IS_MEMBER' 			=> $user_is_member,
	)
);

?>