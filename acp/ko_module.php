<?php
/**
*
* @package phpBB Extension - Football Football
* @copyright (c) 2016 football (http://football.bplaced.net)
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace football\football\acp;

class ko_module
{
	public $u_action;

	protected $db, $user, $template, $config, $phpbb_root_path, $phpbb_admin_path, $phpEx;
	protected $root_path, $request, $php_ext, $log;


	public function __construct()
	{
		global $db, $user, $request, $template;
		global $config, $phpbb_root_path, $phpbb_admin_path, $phpEx;

		$user->add_lang_ext('football/football', 'football');
		$user->add_lang_ext('football/football', 'info_acp_ko');

		$this->root_path = $phpbb_root_path . 'ext/football/football/';

		$this->config = $config;
		$this->request = $request;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->phpbb_admin_path = $phpbb_admin_path;
		$this->php_ext = $phpEx;

		if(!function_exists('season_info'))
		{
			include($this->root_path . 'includes/functions.' . $this->php_ext);
		}
		if (!defined('FOOTB_SEASONS'))
		{
			include($this->root_path . 'includes/constants.' . $this->php_ext);
		}
	}

	function main($id, $mode)
	{
		global $db, $auth, $phpbb_container, $phpbb_admin_path, $league_info;
		global $template, $user, $config, $phpbb_extension_manager, $request, $phpbb_root_path, $phpEx;
		
		$helper = $phpbb_container->get('controller.helper');
		
		$this->tpl_name = 'acp_football_ko';
		$this->page_title = 'ACP_FOOTBALL_KO_MANAGE';

		$form_key = 'acp_football_ko';
		add_form_key($form_key);

		include_once($this->phpbb_root_path . 'includes/functions_user.' . $this->php_ext);

		// Check and set some common vars
		$action				= (isset($_POST['update'])) ? 'update' : $this->request->variable('action', '');
		$season				= $this->request->variable('s', 0);
		$league				= $this->request->variable('l', 0);
		$matchday_from		= $this->request->variable('matchday_from', 0);
		$matchday_to		= $this->request->variable('matchday_to', 0);
		$matchday_new		= $this->request->variable('matchday_new', 0);
		$check_rank			= $this->request->variable('check_rank', 0);
		$rank				= $this->request->variable('rank', 2);
		$move_rank			= $this->request->variable('move_rank', 3);
		$move_league		= $this->request->variable('move_league', 0);
		$move_matchday		= $this->request->variable('move_matchday', 8);

		// Clear some vars
		$error = array();
		$success = array();

		$curr_season = curr_season();
		// Grab current season
		if (!$season)
		{
			$season = $curr_season;
		}

		// Grab basic data for select season
		if ($season)
		{
			$sql = 'SELECT 
					DISTINCT s.*
				FROM ' .  FOOTB_SEASONS . ' AS s
				LEFT JOIN ' . FOOTB_LEAGUES . ' AS l ON (l.season = s.season)
				WHERE l.league_type = 2
				ORDER BY s.season DESC';
			$result = $db->sql_query($sql);

			$season_options = '';
			while ($row = $db->sql_fetchrow($result))
			{
				$selected = ($season && $row['season'] == $season) ? ' selected="selected"' : '';
				$season_options .= '<option value="' . $row['season'] . '"' . $selected . '>' . $row['season_name_short'] . '</option>';
				if ($selected <> '')
				{
					$season_name = $row['season_name_short'];
				}
			}
			$db->sql_freeresult($result);
		}
		else
		{
			trigger_error($user->lang['NO_SEASON'] . adm_back_link($this->u_action), E_USER_WARNING);
		}

		// Grab basic data for select league
		$sql = 'SELECT *
			FROM ' . FOOTB_LEAGUES . '
			WHERE season = ' . $season . ' 
				AND league_type = ' . LEAGUE_KO . '
			ORDER BY league ASC';
		$result = $db->sql_query($sql);

		$league_options = '';
		if ($move_league == 0)
		{
			$league_move_options = '<option value="0" selected="selected">' . sprintf($user->lang['CHOOSE_LEAGUE']) . '</option>';
		}
		else
		{
			$league_move_options = '<option value="0">' . sprintf($user->lang['CHOOSE_LEAGUE']) . '</option>';
		}
		while ($row = $db->sql_fetchrow($result))
		{
			// Grab current league
			if (!$league)
			{
				$league = $row['league'];
			}
			$selected = ($league && $row['league'] == $league) ? ' selected="selected"' : '';
			$league_options .= '<option value="' . $row['league'] . '"' . $selected . '>' . $row['league_name'] . '</option>';
			if ($selected <> '')
			{
				$league_matchdays = $row['matchdays'];
				$matches_matchday = $row['matches_on_matchday'];
				$league_name 	  = $row['league_name'];
			}
			else
			{
				$selected_move = ($move_league && $row['league'] == $move_league) ? ' selected="selected"' : '';
				$league_move_options .= '<option value="' . $row['league'] . '"' . $selected_move . '>' . $row['league_name'] . '</option>';
			}
		}
		$db->sql_freeresult($result);
		
		if (!$league)
		{
			trigger_error(sprintf($user->lang['NO_LEAGUE'], $season) . adm_back_link($this->u_action . "&amp;s=$season"), E_USER_WARNING);
		}

		// Grab basic data for select matchday
		if (!$matchday_from)
		{
			$matchday_from = curr_matchday($season, $league);
			if ($matchday_from > 1)
			{
				$matchday_from = $matchday_from - 1;
			}
		}
		if (!$matchday_to)
		{
			$matchday_to = $matchday_from;
		}
		if (!$matchday_new)
		{
			$matchday_new = $matchday_to + 1;
		}
		
		$sql = 'SELECT *
			FROM ' . FOOTB_MATCHDAYS . "
			WHERE season = $season 
				AND league = $league 
			ORDER BY matchday ASC";
		$result = $db->sql_query($sql);

		$matchday_from_options = '';
		$matchday_to_options = '';
		$matchday_new_options = '';
		while ($row = $db->sql_fetchrow($result))
		{
			$selected_from 		= ($matchday_from && $row['matchday'] == $matchday_from) ? ' selected="selected"' : '';
			$selected_to 		= ($matchday_to && $row['matchday'] == $matchday_to) ? ' selected="selected"' : '';
			$selected_new 		= ($matchday_new && $row['matchday'] == $matchday_new) ? ' selected="selected"' : '';
			$day_name 			= (strlen($row['matchday_name']) > 0) ? $row['matchday_name'] : $row['matchday'] . '. ' . sprintf($user->lang['MATCHDAY']);
			$matchday_from_options .= '<option value="' . $row['matchday'] . '"' . $selected_from . '>' . $day_name . '</option>';
			$matchday_to_options .= '<option value="' . $row['matchday'] . '"' . $selected_to . '>' . $day_name . '</option>';
			$matchday_new_options .= '<option value="' . $row['matchday'] . '"' . $selected_new . '>' . $day_name . '</option>';
		}
		$db->sql_freeresult($result);
		if ($matchday_from_options == '')
		{
			trigger_error(sprintf($user->lang['NO_MATCHDAY'], $league_name, $season) . adm_back_link($this->u_action . "&amp;s=$season&amp;l=$league"), E_USER_WARNING);
		}
	
		// Which page?
		switch ($action)
		{
			case 'update':
				{
					if ($matchday_from > $matchday_to) 
					{
						$error[] =  sprintf($user->lang['ERROR_FROM_TO']);
					}
					if ($matchday_new <= $matchday_to) 
					{
						$error[] =  sprintf($user->lang['ERROR_TARGET']);
					}
					if (!sizeof($error))
					{
						if (1 == $check_rank)
						{
							$success = ko_group_next_round($season, $league, $matchday_from, $matchday_to, $matchday_new, $rank, $move_rank, $move_league, $move_matchday);
						}
						else
						{
							$success = ko_next_round($season, $league, $matchday_from, $matchday_to, $matchday_new);
						}
						trigger_error($success . adm_back_link($this->u_action));
					}
				}
			break;
		}

		$template->assign_vars(array(
			'U_ACTION'				=> $this->u_action,
			'U_FOOTBALL' 			=> $helper->route('football_football_controller',array('side' => 'bet', 's' => $season, 'l' => $league)),
			'S_ERROR'				=> (sizeof($error)) ? true : false,
			'ERROR_MSG'				=> (sizeof($error)) ? implode('<br />', $error) : '',
			'S_SUCCESS'				=> (sizeof($success)) ? true : false,
			'SUCCESS_MSG'			=> (sizeof($success)) ? implode('<br />', $success) : '',
			'S_SEASON_OPTIONS'		=> $season_options,
			'S_LEAGUE_OPTIONS'		=> $league_options,
			'S_SEASON'				=> $season,
			'S_LEAGUE'				=> $league,
			'S_MATCHDAY_FROM_OPTIONS'=> $matchday_from_options,
			'S_MATCHDAY_TO_OPTIONS'	=> $matchday_to_options,
			'S_MATCHDAY_NEW_OPTIONS'=> $matchday_new_options,
			'S_CHECK_RANK' 			=> $check_rank,
			'S_RANK' 				=> $rank,
			'S_MOVE_RANK' 			=> $move_rank,
			'S_MOVE_LEAGUE_OPTIONS'	=> $league_move_options,
			'S_MOVE_MATCHDAY' 		=> $move_matchday,
			'S_VERSION_NO'			=> $this->config['football_version'],
			)
		);
	}
}
