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

$season		= $this->request->variable('s', 0);
$league		= $this->request->variable('l', 0);

// Check parms
$error_message = '';
if (!$season OR !$league)
{
	$data_rules = false;
	if (!$season)
	{	
		$error_message .= sprintf($user->lang['NO_SEASON']) . '<br />';
	}
	if (!$league)
	{	
		$error_message .= sprintf($user->lang['NO_LEAGUE']) . '<br />';
	}
}
else
{
	$season_info = season_info($season);
	if (sizeof($season_info))
	{
		$season_name = $season_info['season_name'];
		$league_info = league_info($season, $league);
		if (sizeof($league_info))
		{
			$data_rules = true;
			$matchdays 		= $league_info['matchdays'];
			$league_name 	= $league_info['league_name'];

			if ($user->data['is_registered'] and !$user->data['is_bot'])
			{
				$win_hits 			= '';
				$win_hits02 		= '';
				$win_matchday 	= explode(';', "0;" . $league_info['win_matchday']);
				$win_season 	= explode(';',"0;" . $league_info['win_season']);
				$win_hits 		= $league_info['win_result'];
				$win_hits02 	= $league_info['win_result_02'];

				if($win_hits != '' AND $win_hits != 0)
				{
					$template->assign_block_vars('wintable', array(
						'WIN_DESC' 	=> sprintf($user->lang['WIN_HITS']),
						)
					);
					$template->assign_block_vars('wintable.entry', array(
						'ROW_CLASS' => 'bg1 row_light',
						'RANK' 		=> '1. ' . sprintf($user->lang['PLACE']),
						'WIN' 		=> $win_hits,
						)
					);
				}

				if($win_hits02 != '' AND $win_hits02 != 0 AND $config['football_win_hits02'])
				{
					$template->assign_block_vars('wintable', array(
						'WIN_DESC' 	=> sprintf($user->lang['WIN_HITS02']),
						)
					);
					$template->assign_block_vars('wintable.entry', array(
						'ROW_CLASS' => 'bg1 row_light',
						'RANK' 		=> '1. ' . sprintf($user->lang['PLACE']),
						'WIN' 		=> $win_hits02,
						)
					);
				}


				if($win_matchday[1] != '' AND $win_matchday[1] != 0)
				{
					$template->assign_block_vars('wintable', array(
						'WIN_DESC' 	=> sprintf($user->lang['WINS_MATCHDAY']),
						)
					);
					$rank = 1;
					while ($win_matchday[$rank] != '')
					{
						$row_class = (!($rank % 2)) ? 'bg1 row_light' : 'bg2 row_dark';
						$template->assign_block_vars('wintable.entry', array(
							'ROW_CLASS' => $row_class,
							'RANK' 	=> $rank . '. ' . sprintf($user->lang['PLACE']),
							'WIN' 	=> $win_matchday[$rank],
							)
						);
						$rank++ ;
						if ($rank > sizeof($win_matchday)-1)
						{
							break;
						}
					}
				}

				if($win_season[1] != '' AND $win_season[1] != 0)
				{
					$template->assign_block_vars('wintable', array(
						'WIN_DESC' 	=> sprintf($user->lang['WINS_SEASON']),
						)
					);
					$rank = 1;
					while ($win_season[$rank] != '')
					{
						$row_class = (!($rank % 2)) ? 'bg1 row_light' : 'bg2 row_dark';
						$template->assign_block_vars('wintable.entry', array(
							'ROW_CLASS' => $row_class,
							'RANK' 		=> $rank. '. ' . sprintf($user->lang['PLACE']),
							'WIN' 		=> $win_season[$rank],
							)
						);
						$rank++ ;
						if ($rank > sizeof($win_season)-1)
						{
							break;
						}
					}
				}
			}			
		}
		else
		{
			$data_rules = false;
			$error_message .= sprintf($user->lang['NO_LEAGUE']) . '<br />';
			$league_name = '';
		}
	}
	else
	{
		$data_rules = false;
		$error_message .= sprintf($user->lang['NO_SEASON']) . '<br />';
		$season_name = '';
	}
}

$sidename = sprintf($user->lang['FOOTBALL_RULES']);
if ($data_rules)
{
	$link_rules = append_sid($phpbb_root_path . "viewtopic.$phpEx?p=" .  $league_info["rules_post_id"]);
	$points_tendency = ($league_info['points_mode'] < 3) ? sprintf($user->lang['POINTS_TENDENCY' . $league_info['points_mode']], $league_info['points_tendency']) : sprintf($user->lang['POINTS_TENDENCY'], $league_info['points_tendency']);
	$template->assign_vars(array(
		'S_SIDENAME' 		=> $sidename,
		'S_DATA_RULES'		=> $data_rules,
		'S_BET_IN_TIME'		=> $league_info['bet_in_time'],
		'S_BET_POINTS'		=> true,
		'S_RULES_POST_ID'	=> $league_info['rules_post_id'],
		'S_ERROR_MESSAGE'	=> $error_message,
		'S_FOOTBALL_COPY' 	=> sprintf($user->lang['FOOTBALL_COPY'], $config['football_version'], $phpbb_root_path . 'football/'),
		'WIN_NAME' 			=> $config['football_win_name'],
		'JOIN_MODE'			=> ($league_info['join_by_user']) ? (($league_info['join_in_season']) ? sprintf($user->lang['JOIN_IN_SEASON']) : sprintf($user->lang['JOIN_BY_USER'])) : sprintf($user->lang['JOIN_BY_ADMIN']),
		'POINTS_HIT'		=> sprintf($user->lang['POINTS_HIT'], $league_info['points_result']) . '<br/>',
		'POINTS_TENDENCY'	=> $points_tendency . '<br/>',
		'POINTS_DIFF'		=> ($league_info['points_mode'] == 4) ? sprintf($user->lang['POINTS_DIFFERENCE'], $league_info['points_diff']) . '<br/>' : 
								(($league_info['points_mode'] == 5) ? sprintf($user->lang['POINTS_DIFFERENCE_DRAW'], $league_info['points_diff']) . '<br/>' : ''),
		'POINTS_LAST'		=> ($league_info['points_last']) ? sprintf($user->lang['POINTS_NO_BET']) . '<br/>' : '',
		'LINK_RULES'	 	=> sprintf($user->lang['LINK_RULES'], $link_rules),
		'SEASONNAME'	 	=> $season_info['season_name'],
		'LEAGUENAME'	 	=> $league_name,
		'BET_POINTS'	 	=> $league_info['bet_points'],
		)
	);

	// output page
	page_header(sprintf($user->lang['FOOTBALL_RULES'	]) . ' ' . $league_info['league_name'] . ' ' . $season_info['season_name']);
}
else
{
	$template->assign_vars(array(
		'S_SIDENAME' 		=> $sidename,
		'S_DATA_RULES'		=> $data_rules,
		'S_BET_IN_TIME'		=> false,
		'S_BET_POINTS'		=> false,
		'S_RULES_POST_ID'	=> 0,
		'S_ERROR_MESSAGE'	=> $error_message,
		'S_FOOTBALL_COPY' 	=> sprintf($user->lang['FOOTBALL_COPY'], $config['football_version'], $phpbb_root_path . 'football/'),
		'WIN_NAME' 			=> $config['football_win_name'],
		'JOIN_MODE'			=> '',
		'POINTS_HIT'		=> '',
		'POINTS_TENDENCY'	=> '',
		'POINTS_DIFF'		=> '',
		'POINTS_LAST'		=> '',
		'LINK_RULES'	 	=> '',
		'SEASONNAME'	 	=> '',
		'LEAGUENAME'	 	=> '',
		)
	);

	// output page
	page_header(sprintf($user->lang['FOOTBALL_RULES'	]));
}
$template->set_filenames(array(
	'body' => 'rules_popup.html'
	)
);
//	$template->display('popup');

page_footer();
