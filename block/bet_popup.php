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

// Can this user view Prediction Leagues pages?
if (!$config['football_guest_view'])
{
	if ($user->data['user_id'] == ANONYMOUS)
	{
		trigger_error('NO_GUEST_VIEW');
	}
}
if (!$config['football_user_view'])
{
	// Only Prediction League member should see this page
	// Check Prediction League authorisation 
	if ( !$this->auth->acl_get('u_use_football') )
	{
		trigger_error('NO_AUTH_VIEW');
	}
}

// Football disabled?
if ($config['football_disable'])
{
	$message = (!empty($config['football_disable_msg'])) ? $config['football_disable_msg'] : 'FOOTBALL_DISABLED';
	trigger_error($message);
}

$userid		= $this->request->variable('u', 0);
$season		= $this->request->variable('s', 0);
$league		= $this->request->variable('l', 0);
$matchday	= $this->request->variable('m', 0);

$error_message = '';
$username = '?';
if (!$userid OR !$season OR !$league OR !$matchday)
{
	$data_bet = false;
	if (!$userid)
	{	
		$error_message .= sprintf($user->lang['NO_USERID']) . '<br />';
	}
	if (!$season)
	{	
		$error_message .= sprintf($user->lang['NO_SEASON']) . '<br />';
	}
	if (!$league)
	{	
		$error_message .= sprintf($user->lang['NO_LEAGUE']) . '<br />';
	}
	if (!$matchday)
	{	
		$error_message .= sprintf($user->lang['NO_MATCHDAY']) . '<br />';
	}
}
else
{
	$season_info = season_info($season);
	if (sizeof($season_info))
	{
		$league_info = league_info($season, $league);
		if (sizeof($league_info))
		{
			// Get username
			$sql = 'SELECT username
				FROM ' . USERS_TABLE . " 
				WHERE user_id = $userid ";
			$result = $db->sql_query($sql);
			if ($row = $db->sql_fetchrow($result))
			{
				$username = $row['username'];
			}
			else
			{
				$data_bet = false;
				$error_message .= sprintf($user->lang['NO_USERID']) . '<br />';
			}
			$db->sql_freeresult($result);
			
			$display_group = false;
			$lang_dates = $user->lang['datetime'];
			// Required for select_points function:
			$league_info = league_info($season, $league);

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
					b.goals_home AS bet_home,
					b.goals_guest AS bet_guest,
					m.goals_home, 
					m.goals_guest,
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
					" . select_points() . "
				FROM  " . FOOTB_MATCHES . ' AS m
				INNER JOIN ' . FOOTB_BETS . " AS b ON (b.season = m.season AND b.league = m.league AND b.match_no = m.match_no AND b.user_id = $userid)
				LEFT JOIN " . FOOTB_TEAMS . ' AS t1 ON (t1.season = m.season AND t1.league = m.league AND t1.team_id = m.team_id_home)
				LEFT JOIN ' . FOOTB_TEAMS . " AS t2 ON (t2.season = m.season AND t2.league = m.league AND t2.team_id = m.team_id_guest)
				WHERE m.season = $season 
					AND m.league = $league 
					AND m.matchday = $matchday
				GROUP BY m.match_no
				ORDER BY m.match_datetime ASC, m.match_no ASC";
				
			$result = $db->sql_query($sql);
			if ($row = $db->sql_fetchrow($result))
			{
				$data_bet = true;
				$matchnumber = 0;
				$ext_path = $this->phpbb_path_helper->update_web_root_path($this->phpbb_extension_manager->get_extension_path('football/football', true));
				do
				{
					$matchnumber++ ;
					$row_class = (!($matchnumber % 2)) ? 'bg1 row_light' : 'bg2 row_dark';
					$display_link = true;
					if (0 == $row['home_id'])
					{
						$home_info = get_team($season, $league, $row['match_no'], 'team_id_home', $row['formula_home']);
						$home_in_array = explode("#",$home_info);
						$homelogo = $home_in_array[0];
						$homeid = $home_in_array[1];
						$homename = $home_in_array[2];
					}
					else
					{
						$homelogo = $row['home_symbol'];
						$homeid = $row['home_id'];
						$homename = $row['home_name'];
					}
					if (0 == $row['guest_id'])
					{
						$guest_info = get_team($season, $league, $row['match_no'], 'team_id_guest', $row['formula_guest']);
						$guest_in_array = explode("#",$guest_info);
						$guestlogo = $guest_in_array[0];
						$guestid = $guest_in_array[1];
						$guestname = $guest_in_array[2];
					}
					else
					{
						$guestlogo = $row['guest_symbol'];
						$guestid = $row['guest_id'];
						$guestname = $row['guest_name'];
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

					if ($row['group_id'] == '')
					{
						$group_id = '&nbsp;';
					}
					else
					{
						$display_group = true;
						$group_id = $row['group_id'];
					}
					

					if ($row['status'] < 1 && !$config['football_view_bets'])
					{
						// hide bets
						$bet_home = ($row['bet_home'] == '') ? '&nbsp;' : '?';
						$bet_guest = ($row['bet_guest'] == '') ? '&nbsp;' : '?';
					}
					else
					{
						$bet_home = ($row['bet_home'] == '') ? '&nbsp;' : $row['bet_home'];
						$bet_guest = ($row['bet_guest'] == '') ? '&nbsp;' : $row['bet_guest'];
					}

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
						'BET_HOME' 		=> $bet_home,
						'BET_GUEST' 	=> $bet_guest,
						'GOALS_HOME' 	=> ($row['goals_home'] == '') ? '&nbsp;' : $row['goals_home'],
						'GOALS_GUEST'	=> ($row['goals_guest'] == '') ? '&nbsp;' : $row['goals_guest'],
						'POINTS' 		=> ($row['points'] == '') ? '&nbsp;' : $row['points'],
						'COLOR_STYLE' 	=> $colorstyle,
						)
					);
				}
				while ($row = $db->sql_fetchrow($result));
				$db->sql_freeresult($result);
			}
			else
			{
				$data_bet = false;
				$error_message .= sprintf($user->lang['NO_BETS']) . '<br />';
			}
		}
		else
		{
			$data_bet = false;
			$error_message .= sprintf($user->lang['NO_LEAGUE']) . '<br />';
		}
	}
	else
	{
		$data_bet = false;
		$error_message .= sprintf($user->lang['NO_SEASON']) . '<br />';
	}
}

// Calculate extra bets of matchday
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
$extranumber = 0;

while ($row = $db->sql_fetchrow($result))
{
	$extra_bet = true;
	$extranumber++ ;
	$row_class = (!($extranumber % 2)) ? 'bg1 row_light' : 'bg2 row_dark';

	if ($row['extra_status'] < 1 && !$config['football_view_bets'])
	{
		// hide bets
		$bet = ($row['bet'] == '') ? '&nbsp;' : '?';
		$bet_team = ($row['bet_team'] == NULL) ? '&nbsp;' : '?';
	}
	else
	{
		$bet = ($row['bet'] == '') ? '&nbsp;' : $row['bet'];
		$bet_team = ($row['bet_team'] == NULL) ? '&nbsp;' : $row['bet_team'];
	}
	$extra_colorstyle = color_style($row['extra_status']);

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
				$eval_title = sprintf($user->lang['EXTRA_DIFFERENCE']);
			}
			break;
		case '5':
			{
				$display_type = 2;
				$eval_title = sprintf($user->lang['EXTRA_MULTI_HIT']);
			}
			break;
		default :
			{
				$display_type = 2;
				$eval_title = '';
			}
			break;
	}
	
	$template->assign_block_vars('extra_view', array(
		'ROW_CLASS' 	=> $row_class,
		'QUESTION' 		=> $row['question'],
		'EXTRA_POINTS' 	=> $row['extra_points'],
		'EVALUATION' 	=> ($row['matchday'] == $row['matchday_eval']) ? sprintf($user->lang['MATCHDAY']) : sprintf($user->lang['TOTAL']),
		'EVALUATION_TITLE' 	=> $eval_title,
		'RESULT' 		=> ($display_type == 1) ? $row['result_team'] : $row['result'],
		'BET' 			=> ($display_type == 1) ? $bet_team : $bet,
		'BET_POINTS' 	=> $row['bet_points'],
		'COLOR_STYLE' 	=> $extra_colorstyle,
		)
	);
}
$db->sql_freeresult($result);

$sidename = sprintf($user->lang['BET']);
if ($data_bet)
{
	$template->assign_vars(array(
		'S_SIDENAME' 				=> $sidename,
		'S_USER_NAME'	 			=> $username,
		'S_ERROR_MESSAGE'			=> $error_message,
		'S_FROM'	 				=> sprintf($user->lang['FROM_DAY_SEASON'], $matchday, $season),
		'S_FOOTBALL_COPY' 			=> sprintf($user->lang['FOOTBALL_COPY'], $config['football_version'], $phpbb_root_path . 'football/'),
		'S_DATA_BET' 				=> $data_bet,
		'S_DISPLAY_GROUP'			=> $display_group,
		'S_EXTRA_BET' 				=> $extra_bet,
		)
	);

	// output page
	page_header(sprintf($user->lang['BETS_OF']) . ' ' . $username);
}
else
{
	$template->assign_vars(array(
		'S_SIDENAME' 				=> $sidename,
		'S_USER_NAME'	 			=> '',
		'S_ERROR_MESSAGE'			=> $error_message,
		'S_FROM'	 				=> '',
		'S_FOOTBALL_COPY' 			=> sprintf($user->lang['FOOTBALL_COPY'], $config['football_version'], $phpbb_root_path . 'football/'),
		'S_DATA_BET' 				=> $data_bet,
		'S_DISPLAY_GROUP'			=> false,
		)
	);

	// output page
	page_header(sprintf($user->lang['BETS_OF']));
}

$template->set_filenames(array(
	'body' => 'bet_popup.html')
);

page_footer();
