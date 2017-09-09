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
$data_results 	= false;
$lang_dates 	= $user->lang['datetime'];
$matchnumber 	= 0;
$editstatus 	= array(1, 2, 4, 5);

$league_info = league_info($season, $league);
// Calculate matches AND results of matchday
$sql = "SELECT
		m.match_no,
		m.matchday,
		m.status,
		m.group_id,
		m.formula_home,
		m.formula_guest,
		t1.team_symbol AS home_symbol,
		t2.team_symbol AS guest_symbol,
		t1.team_id AS home_id,
		t2.team_id AS guest_id,
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
	LEFT JOIN ' . FOOTB_TEAMS . ' AS t1 ON (t1.season = m.season AND t1.league = m.league AND t1.team_id = m.team_id_home)
	LEFT JOIN ' . FOOTB_TEAMS . " AS t2 ON (t2.season = m.season AND t2.league = m.league AND t2.team_id = m.team_id_guest)
	WHERE m.season = $season 
		AND m.league = $league 
		AND m.matchday = $matchday
	ORDER BY m.match_datetime ASC, m.match_no ASC";
	
$result = $db->sql_query($sql);
$ext_path = $this->phpbb_path_helper->update_web_root_path($this->phpbb_extension_manager->get_extension_path('football/football', true));
while($row = $db->sql_fetchrow($result))
{
	$data_results = true;
	$matchnumber++ ;
	$row_class = (!($matchnumber % 2)) ? 'bg1 row_light' : 'bg2 row_dark';
	if (0 == $row['home_id'])
	{
		$home_info 		= get_team($season, $league, $row['match_no'], 'team_id_home', $row['formula_home']);
		$home_in_array 	= explode("#",$home_info);
		$homelogo 		= $home_in_array[0];
		$homeid 		= $home_in_array[1];
		$homename 		= $home_in_array[2];
		$homeshort 		= $home_in_array[2];
	}
	else
	{
		$homelogo 	= $row['home_symbol'];
		$homeid 	= $row['home_id'];
		$homename 	= $row['home_name'];
		$homeshort 	= $row['home_short'];
	}
	if (0 == $row['guest_id'])
	{
		$guest_info 	= get_team($season, $league, $row['match_no'], 'team_id_guest', $row['formula_guest']);
		$guest_in_array = explode("#",$guest_info);
		$guestlogo 		= $guest_in_array[0];
		$guestid 		= $guest_in_array[1];
		$guestname 		= $guest_in_array[2];
		$guestshort 	= $guest_in_array[2];
	}
	else
	{
		$guestlogo 	= $row['guest_symbol'];
		$guestid 	= $row['guest_id'];
		$guestname 	= $row['guest_name'];
		$guestshort	= $row['guest_short'];
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
	

	if (in_array($row['status'], $editstatus) AND $user_is_member)
	{
		$edit_mode 		= true;
		$edit_match 	= true;
		$goals_home		= $row['goals_home'];
		$goals_guest	= $row['goals_guest'];
		$kogoals_home	= $row['kogoals_home'];
		$kogoals_guest	= $row['kogoals_guest'];
	}
	else
	{
		$edit_match 	= false;
		$goals_home		= ($row['goals_home'] == '') ? '&nbsp;' : $row['goals_home'];
		$goals_guest	= ($row['goals_guest'] == '') ? '&nbsp;' : $row['goals_guest'];
		$kogoals_home	= ($row['kogoals_home'] == '') ? '&nbsp;' : $row['kogoals_home'];
		$kogoals_guest	= ($row['kogoals_guest'] == '') ? '&nbsp;' : $row['kogoals_guest'];
	}
	$template->assign_block_vars('result', array(
		'ROW_CLASS' 	=> $row_class,
		'MATCH_NUMBER' 	=> $row['match_no'],
		'MATCHDAY' 		=> $row['matchday'],
		'STATUS' 		=> $row['status'],
		'MATCH_TIME' 	=> $row['match_time'],
		'GROUP' 		=> $group_id,
		'HOME_ID' 		=> $homeid,
		'GUEST_ID' 		=> $guestid,
		'LOGO_HOME' 	=> $logoH,
		'LOGO_GUEST' 	=> $logoG,
		'HOME_NAME' 	=> $homename,
		'GUEST_NAME' 	=> $guestname,
		'HOME_SHORT' 	=> $homeshort,
		'GUEST_SHORT' 	=> $guestshort,
		'U_PLAN_HOME'	=> $this->helper->route('football_football_popup', array('popside' => 'viewplan_popup', 's' => $season, 'l' => $league,
																			'tid' => $homeid, 'mode' => 'all')),
		'U_PLAN_GUEST'	=> $this->helper->route('football_football_popup', array('popside' => 'viewplan_popup', 's' => $season, 'l' => $league,
																			'tid' => $guestid, 'mode' => 'all')),
		'GOALS_HOME' 	=> $goals_home,
		'GOALS_GUEST'	=> $goals_guest,
		'COLOR_STYLE' 	=> color_style($row['status']),
		'KOGOALS_HOME' 	=> $kogoals_home,
		'KOGOALS_GUEST'	=> $kogoals_guest,
		'S_KO_MATCH' 	=> $ko_match,
		'S_EDIT_MATCH' 	=> $edit_match,
		)
	);
}
$db->sql_freeresult($result);

// Calculate extra bets of matchday
// Start select team
$sql = 'SELECT 
			team_id AS option_value,
			team_name AS option_name
		FROM ' . FOOTB_TEAMS . "
		WHERE season = $season 
		AND league = $league 
		ORDER BY team_name ASC";
		
$result = $db->sql_query($sql);
$option_rows = $db->sql_fetchrowset($result);
$db->sql_freeresult($result);

if ($edit_mode)
{
	$template->assign_block_vars('worldfootball', array('LEAGUE' => $league-1,));
}

$sql = "SELECT e.*,
		t1.team_name AS result_team
	FROM  " . FOOTB_EXTRA . ' AS e
	LEFT JOIN ' . FOOTB_TEAMS . " AS t1 ON (t1.season = e.season AND t1.league = e.league AND t1.team_id = e.result)
	WHERE e.season = $season 
		AND e.league = $league 
		AND e.matchday_eval = $matchday
	ORDER BY e.extra_no ASC";
	
$result = $db->sql_query($sql);

$extra_results = false;
$extranumber = 0;
while ($row = $db->sql_fetchrow($result))
{
	$extra_results = true;
	$extranumber++ ;
	$row_class = (!($extranumber % 2)) ? 'bg1 row_light' : 'bg2 row_dark';

	switch($row['question_type'])
	{
		case '1':
			{
				$display_type = 1;
				$eval_title = sprintf($user->lang['EXTRA_HIT']);
			}
			break;
		case '2':
			{
				$display_type = 1;
				$eval_title = sprintf($user->lang['EXTRA_MULTI_HIT']);
			}
			break;
		case '3':
			{
				$display_type = 2;
				$eval_title = sprintf($user->lang['EXTRA_HIT']);
			}
			break;
		case '4':
		case '5':
			{
				$display_type = 2;
				$eval_title = sprintf($user->lang['EXTRA_DIFFERENCE']);
			}
			break;
		default :
			{
				$display_type = 2;
				$eval_title = '';
			}
			break;
	}

	if ($row['extra_status'] > 0 && $row['extra_status'] < 3 && $user_is_member)
	{
		$edit_mode = true;
		$result_extra = ($row['result_team'] == NULL) ? '' : $row['result_team'];

		$multiple = '';
		switch($row['question_type'])
		{
			case '2':
				{
					$multiple = ' multiple="multiple" size="3" ';
				}
				break;
			case '4':
				{
					$option_arr = array();
					for ($i = 65; $i <= 72; $i++) 
					{
						if (strstr($row['question'], chr($i) . ':'))
						{
							$option_arr[] = array(
								'option_value'	=> chr($i),
								'option_name'	=> chr($i),
							);
						}
					}
					if (sizeof($option_arr) > 1)
					{
						$display_type = 1;
						$option_rows = $option_arr;
						$result_extra = $row['result'];
						$multiple = ' multiple="multiple" size="3" ';
					}
				}
				break;
			case '3':
				{
					$option_arr = array();
					for ($i = 65; $i <= 72; $i++) 
					{
						if (strstr($row['question'], chr($i) . ':'))
						{
							$option_arr[] = array(
								'option_value'	=> chr($i),
								'option_name'	=> chr($i),
							);
						}
					}
					if (sizeof($option_arr) > 1)
					{
						$display_type = 1;
						$option_rows = $option_arr;
						$result_extra = $row['result'];
					}
				}
				break;
		}
		
		$template->assign_block_vars('extra_result', array(
			'ROW_CLASS' 		=> $row_class,
			'EXTRA_NO' 			=> $row['extra_no'],
			'S_EDIT_EXTRA' 		=> true,
			'QUESTION' 			=> $row['question'],
			'EXTRA_POINTS' 		=> $row['extra_points'],
			'EVALUATION' 		=> ($row['matchday'] == $row['matchday_eval']) ? sprintf($user->lang['MATCHDAY']) : sprintf($user->lang['TOTAL']),
			'EVALUATION_TITLE' 	=> $eval_title,
			'RESULT' 			=> ($display_type == 1) ? $result_extra : $row['result'],
			'S_DISPLAY_TYPE'	=> $display_type,
			)
		);
		
		if ($display_type == 1)
		{
			$selected = ($row['result'] == '') ? ' selected="selected"' : '';
			
			$template->assign_block_vars('extra_result.extra_option', array(
				'OPTION_VALUE' 	=> '',
				'OPTION_NAME' 	=> sprintf($user->lang['SELECT']),
				'S_SELECTED' 	=> $selected));
				
			foreach ($option_rows as $option_row)
			{
				if (strstr($row['result'], ';'))
				{
					$selected = '';
					$result_arr = explode(';', $row['result']);
					foreach($result_arr AS $result_value)
					{
						if ($result_value <> '')
						{
							if ($option_row['option_value'] == $result_value)
							{
								$selected = ' selected="selected"';
							}
						}
					}
				}
				else
				{
					$selected = ($option_row['option_value'] == $row['result']) ? ' selected="selected"' : '';
				}
				$template->assign_block_vars('extra_result.extra_option', array(
					'OPTION_VALUE' 	=> $option_row['option_value'],
					'OPTION_NAME' 	=> $option_row['option_name'],
					'S_SELECTED' 	=> $selected));
			}
		}
	}
	else
	{
		$extra_colorstyle = color_style($row['extra_status']);
		$extra_result = ($row['result'] == '') ? '&nbsp;' : $row['result'];
		$result_extra = ($row['result_team'] == NULL) ? '&nbsp;' : $row['result_team'];
		
		$template->assign_block_vars('extra_result', array(
			'ROW_CLASS' 		=> $row_class,
			'S_EDIT_EXTRA' 		=> false,
			'QUESTION' 			=> $row['question'],
			'EXTRA_POINTS' 		=> $row['extra_points'],
			'EVALUATION' 		=> ($row['matchday'] == $row['matchday_eval']) ? sprintf($user->lang['MATCHDAY']) : sprintf($user->lang['TOTAL']),
			'RESULT' 			=> ($display_type == 1) ? $result_extra : $extra_result,
			'COLOR_STYLE' 		=> $extra_colorstyle,
			)
		);
	}
}
$db->sql_freeresult($result);

$sidename = sprintf($user->lang['RESULTS']);
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
	'S_DISPLAY_RESULTS' 		=> true,
	'S_EXTRA_RESULTS' 			=> $extra_results,
	'S_SIDENAME' 				=> $sidename,
	'RESULT_EXPLAIN' 			=> $result_explain,
	'LABEL_FINALRESULT' 		=> $label_finalresult,
	'S_FORM_ACTION_RESULT' 		=> $this->helper->route('football_main_controller', array('side' => 'results', 's' => $season, 'l' => $league, 'm' => $matchday, 'action' => 'result')),
	'S_DATA_RESULTS' 			=> $data_results,
	'S_USER_IS_MEMBER' 			=> $user_is_member,
	'S_DISPLAY_GROUP'			=> $display_group,
	'S_DISPLAY_KO'				=> $display_ko,
	'S_EDIT_MODE' 				=> $edit_mode,
	)
);

?>