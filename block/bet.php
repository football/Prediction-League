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

$edit_mode = false;
$data_group = false;
$data_bet_results = false;
$data_bet = false;
$join_league = false;
$matchnumber = 0;
$userid =  $user->data['user_id'];
$lang_dates = $user->lang['datetime'];
$user_is_member = user_is_member($userid, $season, $league);
$display_rating = false;

// Calculate multiple delivery
$display_delivery2 = false;
$display_delivery3 = false;
$delivery2 = '';
$delivery3 = '';
$sql = "SELECT
		delivery_date_2,
		delivery_date_3,
		CONCAT(
			CASE DATE_FORMAT(delivery_date_2,'%w')
				WHEN 0 THEN '" . $lang_dates['Sun'] . "'
				WHEN 1 THEN '" . $lang_dates['Mon'] . "'
				WHEN 2 THEN '" . $lang_dates['Tue'] . "'
				WHEN 3 THEN '" . $lang_dates['Wed'] . "'
				WHEN 4 THEN '" . $lang_dates['Thu'] . "'
				WHEN 5 THEN '" . $lang_dates['Fri'] . "'
				WHEN 6 THEN '" . $lang_dates['Sat'] . "'
				ELSE 'Error' END,
			DATE_FORMAT(delivery_date_2,' %d.%m.%Y %H:%i')
		) as deliverytime2,
		CONCAT(
			CASE DATE_FORMAT(delivery_date_3,'%w')
				WHEN 0 THEN '" . $lang_dates['Sun'] . "'
				WHEN 1 THEN '" . $lang_dates['Mon'] . "'
				WHEN 2 THEN '" . $lang_dates['Tue'] . "'
				WHEN 3 THEN '" . $lang_dates['Wed'] . "'
				WHEN 4 THEN '" . $lang_dates['Thu'] . "'
				WHEN 5 THEN '" . $lang_dates['Fri'] . "'
				WHEN 6 THEN '" . $lang_dates['Sat'] . "'
				ELSE 'Error' END,
			DATE_FORMAT(delivery_date_3,' %d.%m.%Y %H:%i')
		) as deliverytime3
	FROM " . FOOTB_MATCHDAYS . " 
	WHERE season = $season 
		AND league = $league 
		AND matchday = $matchday";
		
$result = $db->sql_query($sql);

if ($row = $db->sql_fetchrow($result))
{
	if ($row['delivery_date_2'] <> '')
	{
		$display_delivery2 = true;
		$delivery2 = $row['deliverytime2'];
	}
	if ($row['delivery_date_3'] <> '')
	{
		$display_delivery3 = true;
		$delivery3 = $row['deliverytime3'];
	}
}
$db->sql_freeresult($result);

// Calculate matches and bets of matchday
$sql = "SELECT
		m.league,
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
		b.goals_home AS bet_home,
		b.goals_guest AS bet_guest,
		m.goals_home, 
		m.goals_guest,
		m.trend,
		m.odd_1,
		m.odd_x,
		m.odd_2,
		m.rating,
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
		" . select_points() . '
	FROM  ' . FOOTB_MATCHES . ' AS m
	INNER JOIN ' . FOOTB_BETS . " AS b ON (b.season = m.season AND b.league = m.league AND b.match_no = m.match_no AND b.user_id = $userid)
	LEFT JOIN " . FOOTB_TEAMS . ' AS t1 ON (t1.season = m.season AND t1.league = m.league AND t1.team_id = m.team_id_home)
	LEFT JOIN ' . FOOTB_TEAMS . " AS t2 ON (t2.season = m.season AND t2.league = m.league AND t2.team_id = m.team_id_guest)
	WHERE m.season = $season 
		AND m.league = $league 
		AND m.matchday = $matchday
	GROUP BY m.match_no
	ORDER BY m.match_datetime ASC, m.match_no ASC";
	
$result = $db->sql_query($sql);
$ext_path = $this->phpbb_path_helper->update_web_root_path($this->phpbb_extension_manager->get_extension_path('football/football', true));

while ($row = $db->sql_fetchrow($result))
{
	$data_bet = true;
	$matchnumber++ ;
	$row_class = (!($matchnumber % 2)) ? 'bg1 row_light' : 'bg2 row_dark';
	$display_link = true;
	$display_rating = ($display_rating || ($row['rating'] <> '0.00'));

	if (0 == $row['home_id'])
	{
		$display_link 	= false;
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
		$display_link 	= false;
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

	if ($row['status'] == -1)
	{
		$delivertag = "<strong style='color:green'>*</strong>";
	}
	else
	{
		if ($row['status'] == -2)
		{
			$delivertag = "<strong style='color:green'>**</strong>";
		}
		else
		{
			$delivertag = '';
		}
	}

	if ($row['group_id'] == '')
	{
		$group_id = '&nbsp;';
	}
	else
	{
		$data_group = true;
		$group_id = $row['group_id'];
	}

	if ($row['status'] <= 0)
	{
		$edit_mode = true;
		$template->assign_block_vars('bet_edit', array(
			'ROW_CLASS' 	=> $row_class,
			'LEAGUE_ID' 	=> $row['league'],
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
			'U_PLAN_HOME'	=> $this->helper->route('football_football_popup', array('popside' => 'viewplan_popup', 's' => $season, 'l' => $row['league'],
																				'tid' => $homeid, 'mode' => 'all')),
			'U_PLAN_GUEST'	=> $this->helper->route('football_football_popup', array('popside' => 'viewplan_popup', 's' => $season, 'l' => $row['league'],
																				'tid' => $guestid, 'mode' => 'all')),
			'BET_HOME' 		=> $row['bet_home'],
			'BET_GUEST' 	=> $row['bet_guest'],
			'DELIVERTAG' 	=> $delivertag,
			'GOALS_HOME' 	=> ($row['goals_home'] == '') ? '&nbsp;' : $row['goals_home'],
			'GOALS_GUEST'	=> ($row['goals_guest'] == '') ? '&nbsp;' : $row['goals_guest'],
			'POINTS' 		=> ($row['points'] == '') ? '&nbsp;' : $row['points'],
			'U_MATCH_STATS'	=> $this->helper->route('football_football_popup', array('popside' => 'hist_popup', 's' => $season, 'l' => $row['league'],
																				'hid' => $homeid, 'gid' => $guestid, 'm' => $row['matchday'], 
																				'mn' => $row['match_no'], 'gr' => $row['group_id'])),
			'DATA_RESULTS'	=> $data_bet_results,
			'DISPLAY_LINK'	=> $display_link,
			'TREND'			=> $row['trend'],
			'ODDS'			=> ($row['odd_1'] == '') ? '' : $row['odd_1'] . '|' . $row['odd_x'] . '|' . $row['odd_2'],
			'RATING'		=> $row['rating'],
			)
		);
	}
	else
	{
		$data_bet_results = true;
		$colorstyle = color_style($row['status']);
		
		$template->assign_block_vars('bet_view', array(
			'ROW_CLASS' 	=> $row_class,
			'LEAGUE_ID' 	=> $row['league'],
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
			'BET_HOME' 		=> ($row['bet_home'] == '') ? '&nbsp;' : $row['bet_home'],
			'BET_GUEST' 	=> ($row['bet_guest'] == '') ? '&nbsp;' : $row['bet_guest'],
			'GOALS_HOME' 	=> ($row['goals_home'] == '') ? '&nbsp;' : $row['goals_home'],
			'GOALS_GUEST'	=> ($row['goals_guest'] == '') ? '&nbsp;' : $row['goals_guest'],
			'POINTS' 		=> ($row['points'] == '') ? '&nbsp;' : $row['points'],
			'U_PLAN_HOME'	=> $this->helper->route('football_football_popup', array('popside' => 'viewplan_popup', 's' => $season, 'l' => $row['league'],
																				'tid' => $homeid, 'mode' => 'all')),
			'U_PLAN_GUEST'	=> $this->helper->route('football_football_popup', array('popside' => 'viewplan_popup', 's' => $season, 'l' => $row['league'],
																				'tid' => $guestid, 'mode' => 'all')),
			'U_MATCH_STATS'	=> $this->helper->route('football_football_popup', array('popside' => 'hist_popup', 's' => $season, 'l' => $row['league'],
																				'hid' => $homeid, 'gid' => $guestid, 'm' => $row['matchday'], 
																				'mn' => $row['match_no'], 'gr' => $row['group_id'])),
			'COLOR_STYLE' 	=> $colorstyle,
			'DISPLAY_LINK'	=> $display_link,
			'TREND'			=> $row['trend'],
			'ODDS'			=> ($row['odd_1'] == '') ? '' : $row['odd_1'] . '|' . $row['odd_x'] . '|' . $row['odd_2'],
			'RATING'		=> $row['rating'],
			)
		);
	}
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

$sql = "SELECT e.*,
		eb.bet,
		eb.bet_points,
		t1.team_name AS result_team,
		t2.team_name AS bet_team
	FROM  " . FOOTB_EXTRA . ' AS e
	LEFT JOIN ' . FOOTB_EXTRA_BETS . " AS eb ON (eb.season = e.season AND eb.league = e.league AND eb.extra_no = e.extra_no AND eb.user_id = $userid)
	LEFT JOIN " . FOOTB_TEAMS . ' AS t1 ON (t1.season = e.season AND t1.league = e.league AND t1.team_id = e.result)
	LEFT JOIN ' . FOOTB_TEAMS . " AS t2 ON (t2.season = e.season AND t2.league = e.league AND t2.team_id = eb.bet)
	WHERE e.season = $season 
		AND e.league = $league 
		AND e.matchday = $matchday
	ORDER BY e.extra_no ASC";
	
$result = $db->sql_query($sql);

$extra_bet = false;
$extra_edit = false;
$extra_results = false;
$extranumber = 0;

while ($row = $db->sql_fetchrow($result))
{
	$extra_bet = true;
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
			{
				$display_type = 2;
				$eval_title = sprintf($user->lang['EXTRA_MULTI_HIT']);
			}
			break;
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
	
	if ($row['extra_status'] <= 0)
	{
		// edit extra bets
		$extra_edit = true;
		$bet_extra = ($row['bet_team'] == NULL) ? '' : $row['bet_team'];
		
		switch($row['question_type'])
		{
			case '3':
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
						$bet_extra = $row['bet'];
					}
				}
				break;
		}
		
		$template->assign_block_vars('extra_edit', array(
			'ROW_CLASS' 		=> $row_class,
			'EXTRA_NO' 			=> $row['extra_no'],
			'QUESTION' 			=> $row['question'],
			'EXTRA_POINTS' 		=> $row['extra_points'],
			'EVALUATION' 		=> ($row['matchday'] == $row['matchday_eval']) ? sprintf($user->lang['MATCHDAY']) : sprintf($user->lang['TOTAL']),
			'EVALUATION_TITLE' 	=> $eval_title,
			'BET' 				=> ($display_type == 1) ? $bet_extra : $row['bet'],
			'S_DISPLAY_TYPE'	=> $display_type,
			)
		);
		
		if ($display_type == 1)
		{
			$selected = ($row['bet'] == '') ? ' selected="selected"' : '';
			
			$template->assign_block_vars('extra_edit.extra_option', array(
				'OPTION_VALUE' 	=> '',
				'OPTION_NAME' 	=> sprintf($user->lang['SELECT']),
				'S_SELECTED' 	=> $selected));
				
			foreach ($option_rows as $option_row)
			{
				$selected = ($row['bet'] && $option_row['option_value'] == $row['bet']) ? ' selected="selected"' : '';
				$template->assign_block_vars('extra_edit.extra_option', array(
					'OPTION_VALUE' 	=> $option_row['option_value'],
					'OPTION_NAME' 	=> $option_row['option_name'],
					'S_SELECTED' 	=> $selected));
			}
		}
	}
	else
	{
		// view extra bets
		$extra_results = true;
		$extra_colorstyle = color_style($row['extra_status']);
		$extra_result = ($row['result'] == '') ? '&nbsp;' : $row['result'];
		$result_extra = ($row['result_team'] == NULL) ? '&nbsp;' : $row['result_team'];
		$bet = ($row['bet'] == '') ? '&nbsp;' : $row['bet'];
		$bet_extra = ($row['bet_team'] == NULL) ? '&nbsp;' : $row['bet_team'];
		
		$template->assign_block_vars('extra_view', array(
			'ROW_CLASS' 	=> $row_class,
			'QUESTION' 		=> $row['question'],
			'EXTRA_POINTS' 	=> $row['extra_points'],
			'EVALUATION' 	=> ($row['matchday'] == $row['matchday_eval']) ? sprintf($user->lang['MATCHDAY']) : sprintf($user->lang['TOTAL']),
			'EVALUATION_TITLE' 	=> $eval_title,
			'RESULT' 		=> ($display_type == 1) ? $result_extra : $extra_result,
			'BET' 			=> ($display_type == 1) ? $bet_extra : $bet,
			'BET_POINTS' 	=> $row['bet_points'],
			'COLOR_STYLE' 	=> $extra_colorstyle,
			)
		);
	}
}
$db->sql_freeresult($result);

$league_info = league_info($season, $league);
$bet_explain = '';
switch ($league_info['bet_ko_type'])
{
	case BET_KO_90:
		$bet_explain = sprintf($user->lang['MIN90']);
		break;
	case BET_KO_EXTRATIME:
		$bet_explain = sprintf($user->lang['EXTRATIME_SHORT']);
		break;
	case BET_KO_PENALTY:
		$bet_explain = sprintf($user->lang['PENALTY']);
		break;
	default:
		$bet_explain = sprintf($user->lang['MIN90']);
		break;
}

$link_rules = '';
if (!$data_bet AND join_allowed($season, $league) AND $user->data['user_id'] != ANONYMOUS)
{
	if ($league_info["rules_post_id"])
	{
		$join_league = true;
		$link_rules = append_sid($phpbb_root_path . "viewtopic.$phpEx?p=" .  $league_info["rules_post_id"]);
	}
	else
	{
		$link_rules = '';
	}
}
 
$sidename = sprintf($user->lang['BET']);
$template->assign_vars(array(
	'S_DISPLAY_BET' 			=> true,
	'S_SIDENAME' 				=> $sidename,
	'BET_EXPLAIN' 				=> $bet_explain,
	'JOIN_LEAGUE'	 			=> ($link_rules == '') ? '' : sprintf($user->lang['JOIN_LEAGUE'], $link_rules),
	'S_FORM_ACTION_BET' 		=> $this->helper->route('football_football_controller', array('side' => 'bet', 's' => $season, 'l' => $league, 'm' => $matchday, 'action' => 'bet')),
	'S_FORM_ACTION_JOIN' 		=> $this->helper->route('football_football_controller', array('side' => 'bet', 's' => $season, 'l' => $league, 'm' => $matchday, 'action' => 'join')),
	'S_USER_IS_MEMBER' 			=> $user_is_member,
	'S_DATA_BET' 				=> $data_bet,
	'S_DATA_GROUP'				=> $data_group,
	'S_DATA_BET_RESULTS'		=> $data_bet_results,
	'S_EDIT_MODE' 				=> $edit_mode,
	'S_DISPLAY_DELIVERY2' 		=> $display_delivery2,
	'S_DISPLAY_DELIVERY3' 		=> $display_delivery3,
	'S_DELIVERY2' 				=> $delivery2,
	'S_DELIVERY3' 				=> $delivery3,
	'S_JOIN_LEAGUE' 			=> $join_league,
	'S_EXTRA_BET'				=> $extra_bet,
	'S_EXTRA_RESULTS' 			=> $extra_results,
	'S_EXTRA_EDIT' 				=> $extra_edit,
	'S_DISPLAY_RATING'			=> $display_rating,
	)
);
