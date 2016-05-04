<?php
/**
*
* @package phpBB Extension - Football Football
* @copyright (c) 2016 football (http://football.bplaced.net)
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

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

$mode		= $this->request->variable('mode', '');
$season		= $this->request->variable('s', 0);
$league		= $this->request->variable('l', 0);
$team_id	= $this->request->variable('tid', 0);

switch($mode)
{
	case 'played':
		$mode_desc = sprintf($user->lang['PLAYED_MATCHES']);
		$where = ' AND m.status IN (3,6) ';
		$data_results = true;
	break;
	case 'rest':
		$mode_desc = sprintf($user->lang['REST_MATCHES']);
		$where = ' AND m.status IN (0,1,2,4,5) ';
		$data_results = false;
		break;
	case 'home':
		$mode_desc = sprintf($user->lang['HOME_MATCHES']);
		$where = " AND m.team_id_home = $team_id AND m.status IN (3,6) ";
		$data_results = true;
	break;
	case 'away':
		$mode_desc = sprintf($user->lang['AWAY_MATCHES']);
		$where = " AND m.team_id_guest = $team_id AND m.status IN (3,6) ";
		$data_results = true;
	break;
	// ALL is Default
	default:
		$mode_desc = sprintf($user->lang['ALL_MATCHES']);
		$where = '';
		$data_results = true;
	break;
}

// Check parms
$error_message = '';
if (!$season OR !$league OR !$team_id)
{
	$data_plan = false;
	if (!$season)
	{	
		$error_message .= sprintf($user->lang['NO_SEASON']) . '<br />';
	}
	if (!$league)
	{	
		$error_message .= sprintf($user->lang['NO_LEAGUE']) . '<br />';
	}
	if (!$team_id)
	{	
		$error_message .= sprintf($user->lang['NO_TEAM_ID']) . '<br />';
	}
}
else
{
	$data_group = false;
	$lang_dates = $user->lang['datetime'];


	// Calculate matches and bets of matchday
	$sql = "SELECT
			IF(m.team_id_home = $team_id, 'H', 'A') AS match_place,
			IF(((m.status=3) OR (m.status=6)), 
				IF(m.team_id_home = $team_id, 
					IF(m.goals_home + 0 > m.goals_guest, 'match_win', IF(m.goals_home = m.goals_guest, 'match_draw', 'match_lost')),
					IF(m.goals_home + 0 < m.goals_guest, 'match_win', IF(m.goals_home = m.goals_guest, 'match_draw', 'match_lost'))),
				'') AS match_style,
			m.match_no,
			m.matchday,
			m.status,
			m.group_id,
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
		LEFT JOIN ' . FOOTB_TEAMS . ' AS t1 ON (t1.season = m.season AND t1.league = m.league AND t1.team_id=m.team_id_home)
		LEFT JOIN ' . FOOTB_TEAMS . " AS t2 ON (t2.season = m.season AND t2.league = m.league AND t2.team_id=m.team_id_guest)
		WHERE m.season = $season 
			AND m.league = $league 
			AND (m.team_id_home = $team_id OR m.team_id_guest = $team_id) 
			$where
		GROUP BY m.match_no
		ORDER BY m.match_datetime ASC, m.match_no ASC";
	$result = $db->sql_query($sql);
	if ($row = $db->sql_fetchrow($result))
	{
		$data_plan = true;
		$matchnumber = 0;
		$ext_path = $this->phpbb_path_helper->update_web_root_path($this->phpbb_extension_manager->get_extension_path('football/football', true));
		do
		{
			$matchnumber++ ;
			$row_class = (!($matchnumber % 2)) ? 'bg1 row_light' : 'bg2 row_dark';
			$display_link = true;
			$homelogo = $row['home_symbol'];
			$guestlogo = $row['guest_symbol'];
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
				$data_group = true;
				$group_id = $row['group_id'];
			}

			if ($row['match_place'] == 'H')
			{
				$color_home = $row['match_style'];
				$color_guest = '';
			}
			else
			{
				$color_home = '';
				$color_guest = $row['match_style'];
			}
			$color_goals = $row['match_style'];
			
			$template->assign_block_vars('match', array(
				'ROW_CLASS' 	=> $row_class,
				'MATCH_TIME' 	=> $row['match_time'],
				'GROUP' 		=> $group_id,
				'LOGO_HOME' 	=> $logoH,
				'LOGO_GUEST' 	=> $logoG,
				'HOME_NAME' 	=> $row['home_short'],
				'GUEST_NAME' 	=> $row['guest_short'],
				'GOALS_HOME' 	=> ($row['goals_home'] == '') ? '&nbsp;' : $row['goals_home'],
				'GOALS_GUEST'	=> ($row['goals_guest'] == '') ? '&nbsp;' : $row['goals_guest'],
				'COLOR_HOME' 	=> $color_home,
				'COLOR_GUEST' 	=> $color_guest,
				'COLOR_GOALS' 	=> $color_goals,
				)
			);
		}
		while ($row = $db->sql_fetchrow($result));
		$db->sql_freeresult($result);
	}
	else
	{
		$data_plan = false;
	}

	$season_info = season_info($season);
	if (sizeof($season_info) == 0)
	{	
		$error_message .= sprintf($user->lang['NO_SEASON']) . '<br />';
		$season_name = '';
	}
	else
	{
		$season_name = $season_info["season_name"];

		$league_info = league_info($season, $league);
		if (sizeof($league_info) == 0)
		{	
			$error_message .= sprintf($user->lang['NO_LEAGUE']) . '<br />';
			$league_name = '';
			
		}
		else
		{
			$league_name = $league_info["league_name"];
		
			$team_info = team_info($season, $league, $team_id);
			if (sizeof($team_info) == 0)
			{	
				$error_message .= sprintf($user->lang['NO_TEAM_ID']) . '<br />';
				$team_name = '';
				$logo = '';
			}
			else
			{
				$team_name = $team_info["team_name"];
				$logo = "<img src=\"" . $ext_path . 'images/flags/' . $team_info["team_symbol"] . "\" alt=\"" . $team_info["team_symbol"] . "\" width=\"28\" height=\"28\"/>" ;
			}
		}
	}
}

$sidename = sprintf($user->lang['PLAN']);
if ($data_plan)
{
	$template->assign_vars(array(
		'S_SIDENAME' 		=> $sidename,
		'S_DATA_PLAN' 		=> $data_plan,
		'S_DATA_GROUP'		=> $data_group,
		'S_ERROR_MESSAGE'	=> $error_message,
		'MODE_DESC'	 		=> $mode_desc,
		'LOGO'	 			=> $logo,
		'TEAM'	 			=> $team_name,
		'SEASON'	 		=> $season_name,
		'LEAGUE'	 		=> $league_name,
		'S_FOOTBALL_COPY' 	=> sprintf($user->lang['FOOTBALL_COPY'], $config['football_version'], $phpbb_root_path . 'football/'),
		'S_DATA_RESULTS'	=> $data_results,
		)
	);

	// output page
	page_header($mode_desc . ' ' . $team_name);
}
else
{
	$template->assign_vars(array(
		'S_SIDENAME' 		=> $sidename,
		'S_DATA_PLAN' 		=> $data_plan,
		'S_DATA_GROUP'		=> false,
		'S_ERROR_MESSAGE'	=> $error_message,
		'MODE_DESC'	 		=> $mode_desc,
		'LOGO'	 			=> '',
		'TEAM'	 			=> '',
		'SEASON'	 		=> '',
		'LEAGUE'	 		=> '',
		'S_FOOTBALL_COPY' 	=> sprintf($user->lang['FOOTBALL_COPY'], $config['football_version'], $phpbb_root_path . 'football/'),
		'S_DATA_RESULTS'	=> false,
		)
	);

	// output page
	page_header($mode_desc);
}
$template->set_filenames(array(
	'body' => 'viewplan_popup.html')
);

page_footer();

?>