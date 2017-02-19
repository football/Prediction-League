<?php
/**
*
* @package phpBB Extension - Football Football
* @copyright (c) 2016 football (http://football.bplaced.net)
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace football\football\acp;

class teams_module
{
	public $u_action;
	public $ext_football_path;

	protected $db, $user, $template, $config, $phpbb_root_path, $phpbb_admin_path, $phpEx;
	protected $root_path, $request, $php_ext, $log;


	public function __construct()
	{
		global $db, $user, $request, $template;
		global $config, $phpbb_root_path, $phpbb_admin_path, $phpEx;

		$user->add_lang_ext('football/football', 'football');
		$user->add_lang_ext('football/football', 'info_acp_teams');

		$this->config = $config;
		$this->request = $request;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->phpbb_admin_path = $phpbb_admin_path;
		$this->php_ext = $phpEx;
	}

	function main($id, $mode)
	{
		global $db, $auth, $phpbb_container, $phpbb_admin_path, $league_info;
		global $template, $user, $config, $phpbb_extension_manager, $request, $phpbb_root_path, $phpEx;
		
		$helper = $phpbb_container->get('controller.helper');
		
		$this->ext_football_path = $phpbb_root_path . 'ext/football/football/';
		if(!function_exists('season_info'))
		{
			include($this->ext_football_path . 'includes/functions.' . $phpEx);
		}
		if (!defined('FOOTB_SEASONS'))
		{
			include($this->ext_football_path . 'includes/constants.' . $phpEx);
		}

		$this->tpl_name = 'acp_football_teams';
		$this->page_title = 'ACP_FOOTBALL_TEAMS_MANAGE';

		$form_key = 'acp_football_teams';
		add_form_key($form_key);

		include($this->phpbb_root_path . 'includes/functions_user.' . $this->php_ext);

		// Check and set some common vars
		$action		= (isset($_POST['add'])) ? 'add' : $this->request->variable('action', '');
		$edit		= $this->request->variable('edit', 0);
		$season		= $this->request->variable('s', 0);
		$league		= $this->request->variable('l', 0);
		$team		= $this->request->variable('t', 0);
		$update		= (isset($_POST['update'])) ? true : false;
		if ($action == 'add' AND $team > 0 AND !$edit)
		{
			$action = 'add_old';
		}
		if ($action == 'add' AND $team == 0 AND !$edit)
		{
			$team = nextfree_teamid();
		}
		// Clear some vars
		$team_row = array();
		$error = array();

		// Grab current season
		if (!$season)
		{
			$season = curr_season();
		}

		// Grab basic data for select season
		if ($season)
		{
			$sql = 'SELECT *
				FROM ' . FOOTB_SEASONS . '
				ORDER BY season DESC';
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

		// Grab current league
		if (!$league)
		{
			$league = first_league($season, false);
		}

		// Grab basic data for select league
		if ($league)
		{
			$sql = 'SELECT *
				FROM ' . FOOTB_LEAGUES . "
				WHERE season = $season
				ORDER BY league ASC";
			$result = $db->sql_query($sql);

			$league_options = '';
			while ($row = $db->sql_fetchrow($result))
			{
				$selected = ($league && $row['league'] == $league) ? ' selected="selected"' : '';
				$league_options .= '<option value="' . $row['league'] . '"' . $selected . '>' . $row['league_name'] . '</option>';
				if ($selected <> '')
				{
					$league_matchdays 	= $row['matchdays'];
					$league_name 		= $row['league_name'];
					$league_type 		= $row['league_type'];
					$ko_league 			= ($row['league_type'] == LEAGUE_KO) ? true : false;
				}
			}
			$db->sql_freeresult($result);
		}
		else
		{
			trigger_error(sprintf($user->lang['NO_LEAGUE'], $season) . adm_back_link($this->u_action . "&amp;s=$season"), E_USER_WARNING);
		}

		// Grab all teams for selection
		$sql = 'SELECT 
				DISTINCT team_id, 
				team_name
			FROM ' . FOOTB_TEAMS . '
			ORDER BY team_name ASC';
		$result = $db->sql_query($sql);
		$team_options = '<option value="0" selected="selected">' . sprintf($user->lang['NEW_TEAM']) . '</option>';
		while ($row = $db->sql_fetchrow($result))
		{
			$selected = '';
			$team_options .= '<option value="' . $row['team_id'] . '"' . $selected . '>' . $row['team_name'] . '</option>';
		}
		$db->sql_freeresult($result);

		// Grab basic data for team, if team is set and exists
		if ($team)
		{
			$sql = 'SELECT *
				FROM ' . FOOTB_TEAMS . "
				WHERE season = $season 
					AND league = $league 
					AND team_id = $team";
			$result = $db->sql_query($sql);
			$team_row = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);
		}
		if ($action == 'add' or $action == 'edit' )
		{
			// Grab all Teamsymbol for selection
			$teamsymbol_options = '<option value="blank.gif">blank.gif</option>';
			$folder = $this->ext_football_path . 'images/flags/';
			$directory  = opendir($folder);
			$files = array(); 
			while($file = readdir($directory))    
			{
				if( !(bool) preg_match('/.+\.(?:jpe?g|gif|png)$/i', $file) ) 
				{
					continue;
				}
				$files[] = $file;
			}
			sort($files);

			foreach( $files as $file ) 
			{
				$selected = (strtoupper($file) == strtoupper($team_row['team_symbol'])) ? ' selected="selected"' : '';
				$teamsymbol_options .= '<option value="' . $file . '"' . $selected . '>' . $file . '</option>';
			}
			closedir($directory);		
		}
		
		// Which page?
		switch ($action)
		{
			case 'delete':
				if (!$season)
				{
					trigger_error($user->lang['NO_SEASON'] . adm_back_link($this->u_action), E_USER_WARNING);
				}

				if (!$league)
				{
					trigger_error($user->lang['NO_LEAGUE'] . adm_back_link($this->u_action . "&amp;s=$season"), E_USER_WARNING);
				}

				if (!$team)
				{
					trigger_error($user->lang['NO_TEAM'] . adm_back_link($this->u_action . "&amp;s=$season&amp;l=$league"), E_USER_WARNING);
				}

				if (confirm_box(true))
				{
					$error = '';

					if (!$auth->acl_get('a_football_delete'))
					{
						trigger_error($user->lang['NO_AUTH_OPERATION'] . adm_back_link($this->u_action . "&amp;s=$season&amp;l=$league"), E_USER_WARNING);
					}
					if ($user->data['user_type'] != USER_FOUNDER && $this->config['football_founder_delete'])
					{
						trigger_error($user->lang['TEAMS_NO_DELETE'] . adm_back_link($this->u_action . "&amp;s=$season&amp;l=$league"), E_USER_WARNING);
					}

					// Delete team
					$sql = 'DELETE FROM ' . FOOTB_TEAMS . "
							WHERE season = $season AND league = $league AND team_id = $team";
					$db->sql_query($sql);

					// Delete bets
					$sql = 'DELETE FROM ' . FOOTB_BETS . "
							WHERE  season = $season 
								AND league = $league 
								AND match_no IN 
								(SELECT 
									DISTINCT match_no 
								FROM " . FOOTB_MATCHES . "
								WHERE season = $season 
									AND league = $league 
									AND (team_id_home = $team OR team_id_home = $team))";
					$db->sql_query($sql);

					// Delete matches
					$sql = 'DELETE FROM ' . FOOTB_MATCHES . "
							WHERE season = $season AND league = $league AND (team_id_home = $team OR team_id_home = $team)";
					$db->sql_query($sql);

					trigger_error($user->lang['TEAM_DELETED'] . adm_back_link($this->u_action . "&amp;s=$season&amp;l=$league"));
				}
				else
				{
					confirm_box(false, sprintf($user->lang['TEAM_CONFIRM_DELETE'], $team_row['team_name'], $season, $league), build_hidden_fields(array(
						's'			=> $season,
						'l'			=> $league,
						't'			=> $team,
						'mode'		=> $mode,
						'action'	=> $action))
					);
				}
			break;

			case 'add_old':
				if (!check_form_key($form_key))
				{
					trigger_error($user->lang['FORM_INVALID'] . adm_back_link($this->u_action . "&amp;s=$season&amp;l=$league"), E_USER_WARNING);
				}
				
				$sql = 'SELECT *
					FROM ' . FOOTB_TEAMS . "
					WHERE team_id = $team";
				$result = $db->sql_query($sql);
				$oldteam_row = $db->sql_fetchrow($result);
				$db->sql_freeresult($result);

				$matchday_options = '';
				if ($ko_league)
				{
					// Grab all matchdays for selection
					$sql = 'SELECT 
							DISTINCT matchday, 
							matchday_name
						FROM ' . FOOTB_MATCHDAYS . "
						WHERE season = $season 
							AND league = $league 
						ORDER BY  matchday ASC";
					$result = $db->sql_query($sql);
					$matchdays = 0;
					while ($row = $db->sql_fetchrow($result))
					{
						$selected = ($row['matchday'] == 1) ? ' selected="selected"' : '';
						$day_name = (strlen($row['matchday_name']) > 0) ? $row['matchday_name'] : $row['matchday'] . '. ' . sprintf($user->lang['MATCHDAY']);
						$matchday_options .= '<option value="' . $row['matchday'] . '"' . $selected . '>' . $day_name . '</option>';
						$matchdays++;
					}
					$db->sql_freeresult($result);
					if (!$matchdays)
					{
						trigger_error($user->lang['NO_MATCHDAYS'] . adm_back_link($this->u_action . "&amp;s=$season&amp;l=$league"), E_USER_WARNING);
					}
				}

				// Grab all teamsymbol for selection
				$teamsymbol_options = '<option value="blank.gif">blank.gif</option>';
				$folder = $this->ext_football_path . 'images/flags/';
				$directory  = opendir($folder);
				$files = array(); 
				while($file = readdir($directory))    
				{
					if( !(bool) preg_match('/.+\.(?:jpe?g|gif|png)$/i', $file) ) 
					{
						continue;
					}
					$files[] = $file;
				}
				sort($files);

				foreach( $files as $file ) 
				{
					$selected = (strtoupper($file) == strtoupper($oldteam_row['team_symbol'])) ? ' selected="selected"' : '';
					$teamsymbol_options .= '<option value="' . $file . '"' . $selected . '>' . $file . '</option>';
				}
				closedir($directory);		
				
				$u_back = $this->u_action . "&amp;s=$season&amp;l=$league";

				$template->assign_vars(array(
					'S_EDIT'				=> true,
					'S_ADD_TEAM'			=> true,
					'S_ERROR'				=> false,
					'S_KO_LEAGUE'			=> $ko_league,
					'S_VERSION_NO'			=> $this->config['football_version'],
					'ERROR_MSG'				=> (sizeof($error)) ? implode('<br />', $error) : '',
					'SEASON'				=> $season,
					'SEASON_NAME'			=> $season_name,
					'LEAGUE'				=> $league,
					'LEAGUE_NAME'			=> $league_name,
					'TEAM'					=> $team,
					'TEAM_NAME'				=> $oldteam_row['team_name'],
					'TEAM_SHORT'			=> $oldteam_row['team_name_short'],
					'TEAM_SYMBOL'			=> $oldteam_row['team_symbol'],
					'TEAM_SYMBOL_OPTIONS'	=> $teamsymbol_options,
					'TEAM_IMAGE'			=> ($oldteam_row['team_symbol']) ? $this->ext_football_path . 'images/flags/' . $oldteam_row['team_symbol'] : $phpbb_root_path . 'football/images/flags/blank.gif',
					'TEAM_GROUP'			=> '',
					'TEAM_MATCHDAY_OPTIONS'	=> $matchday_options,
					'PHPBB_ROOT_PATH'		=> $phpbb_root_path,
					'U_BACK'				=> $u_back,
					'U_ACTION'				=> "{$this->u_action}&amp;action=add&amp;s=$season&amp;l=$league",
					)
				);
				return;
			break;
			case 'add':
				if ($team > 0 AND $team <= 65535)
				{
					if ($team_row)
					{
						if ($edit)
						{
							$error[] =  $user->lang['TEAM_TAKEN'];
						}
						else
						{
							trigger_error($user->lang['TEAM_TAKEN'] . adm_back_link($this->u_action . "&amp;s=$season&amp;l=$league"), E_USER_WARNING);
						}
					}
					$team_row['team_name'] 			= utf8_normalize_nfc($this->request->variable('team_name', '', true));
					$team_row['team_name_short'] 	= utf8_normalize_nfc($this->request->variable('team_short', '', true));
					$team_row['team_symbol'] 		= utf8_normalize_nfc($this->request->variable('team_symbol', '', true));
					$team_row['group_id'] 			= utf8_normalize_nfc($this->request->variable('team_group', '', true));
					$team_row['matchday'] 			= utf8_normalize_nfc($this->request->variable('team_round', '', true));
				}
				else 
				{
					trigger_error($user->lang['TEAM_NUMBER'] . adm_back_link($this->u_action . "&amp;s=$season&amp;l=$league"), E_USER_WARNING);
				}
				// No break for edit the new  team
			case 'edit':

				$data = array();

				if (!sizeof($error))
				{
					if ($action == 'edit' && !$team)
					{
						trigger_error($user->lang['NO_TEAM'] . adm_back_link($this->u_action . "&amp;s=$season&amp;l=$league"), E_USER_WARNING);
					}

					// Did we submit?
					if ($update)
					{
						if (!check_form_key($form_key))
						{
							trigger_error($user->lang['FORM_INVALID'] . adm_back_link($this->u_action . "&amp;s=$season&amp;l=$league"), E_USER_WARNING);
						}
						
						$team_row['team_name'] 			= utf8_normalize_nfc($this->request->variable('team_name', '', true));
						$team_row['team_name_short'] 	= utf8_normalize_nfc($this->request->variable('team_short', '', true));
						$team_row['team_symbol'] 		= utf8_normalize_nfc($this->request->variable('team_symbol', '', true));
						$team_row['group_id'] 			= utf8_normalize_nfc($this->request->variable('team_group', '', true));
						$team_row['matchday'] 			= utf8_normalize_nfc($this->request->variable('team_round', '', true));

						$where_team_id = '';
						if ($team >= 6000 AND $team <= 6999)
						{
							$where_team_id = ' AND team_id < 7000 AND team_id > 7999';
						}
						else
						{
							if ($team >= 7000 AND $team <= 7999)
							{
								$where_team_id = ' AND team_id < 6000 AND team_id > 6999';
							}
						}
						// Check teamname
						if (strlen($team_row['team_name']) > 2)
						{
							// Check double entry for team 
							$sql = 'SELECT 
									DISTINCT team_id
								FROM ' . FOOTB_TEAMS . "
								WHERE team_name = '" . $team_row['team_name'] . "'" . $where_team_id;
							$result = $db->sql_query($sql);
							$name_rows = $db->sql_fetchrowset($result);
							$db->sql_freeresult($result);
							if (sizeof($name_rows) > 1)
							{
								$error[] =  $user->lang['TEAM_NAME_DOUBLE'];
							}
							elseif (sizeof($name_rows) == 1)
							{
								if ($name_rows[0]['team_id'] <> $team)
								{
									$error[] =  $user->lang['TEAM_NAME_DOUBLE'];
								}
							}
						}
						
						// Check teamname short
						if (strlen($team_row['team_name_short']) > 1)
						{
							// Check double entry for team 
							$sql = 'SELECT 
									DISTINCT team_id
								FROM ' . FOOTB_TEAMS . "
								WHERE team_name_short = '" . $team_row['team_name_short'] . "'" . $where_team_id;
							$result = $db->sql_query($sql);
							$short_rows = $db->sql_fetchrowset($result);
							$db->sql_freeresult($result);
							if (sizeof($short_rows) > 1)
							{
								$error[] =  $user->lang['TEAM_SHORT_DOUBLE'];
							}
							elseif (sizeof($short_rows) == 1)
							{
								if ($short_rows[0]['team_id'] <> $team)
								{
									$error[] =  $user->lang['TEAM_SHORT_DOUBLE'];
								}
							}
						}
						
						if (!sizeof($error))
						{
							$sql_ary = array(
								'season'			=> (int) $season,
								'league'			=> (int) $league,
								'team_id'			=> (int) $team,
								'team_name'			=> $team_row['team_name'],
								'team_name_short'	=> $team_row['team_name_short'],
								'team_symbol'		=> strlen($team_row['team_symbol']) ? $team_row['team_symbol'] : 'blank.gif',
								'group_id'			=> strlen($team_row['group_id']) ? $team_row['group_id'] : '',
								'matchday'			=> strlen($team_row['matchday']) ? $team_row['matchday'] : 0,
							);

							if ($ko_league)
							{
								$data['team_group']	= $team_row['group_id'];
							}
							else
							{
								$data['team_group']	= '';
							}
							$data['team']	= $team;
							$data['team_name']	= $team_row['team_name'];
							$data['team_short']	= $team_row['team_name_short'];

							$var_ary = array(
								'team'		=> array('num', false, 1, 9999),
								'team_name'	=> array('string', false, 3, 30),
								'team_short'	=> array('string', false, 1, 10),
								'team_group'	=> array(
									array('string', true, 1, 1),
									array('match', true, '#^[A-Z]#')),
							);
							if (!($error_vals = validate_data($data, $var_ary)))
							{
								if ($action == 'add')
								{
									$sql = 'INSERT INTO ' . FOOTB_TEAMS . ' ' . $db->sql_build_array('INSERT', $sql_ary);
									$db->sql_query($sql);
								}
								else
								{
									$sql = 'UPDATE ' . FOOTB_TEAMS . '
										SET ' . $db->sql_build_array('UPDATE', $sql_ary) . "
										WHERE season = $season AND league = $league AND team_id = $team";
									$db->sql_query($sql);
								}
								$message = ($action == 'edit') ? 'TEAM_UPDATED' : 'TEAM_CREATED';
								trigger_error($user->lang[$message] . adm_back_link($this->u_action . "&amp;s=$season&amp;l=$league"));
							}
							else
							{
								foreach ($error_vals as $error_val)
								{
										$error_msg[] = $user->lang[$error_val];
								}
								$message = ($action == 'edit') ? 'TEAM_UPDATE_FAILED' : 'TEAM_CREATE_FAILED';
								$error[] =  $user->lang[$message];
								$error = array_merge($error, $error_msg);
							}
						}
					}
				}

				$matchday_options = '';
				if ($ko_league)
				{
					// Grab all matchdays for selection
					$sql = 'SELECT 
							DISTINCT matchday, 
							matchday_name
						FROM ' . FOOTB_MATCHDAYS . "
						WHERE season = $season 
							AND league = $league 
						ORDER BY  matchday ASC";
					$result = $db->sql_query($sql);
					$matchdays = 0;
					while ($row = $db->sql_fetchrow($result))
					{
						$selected = ($team_row['matchday'] && $row['matchday'] == $team_row['matchday']) ? ' selected="selected"' : '';
						$matchday_name = (strlen($row['matchday_name']) > 0) ? $row['matchday_name'] : $row['matchday'] . '. ' . sprintf($user->lang['MATCHDAY']);
						$matchday_options .= '<option value="' . $row['matchday'] . '"' . $selected . '>' . $matchday_name . '</option>';
						$matchdays++;
					}
					$db->sql_freeresult($result);
					if (!$matchdays)
					{
						trigger_error($user->lang['NO_MATCHDAYS'] . adm_back_link($this->u_action . "&amp;s=$season&amp;l=$league"), E_USER_WARNING);
					}
				}

				$u_back = $this->u_action . "&amp;s=$season&amp;l=$league";

				$template->assign_vars(array(
					'S_EDIT'				=> true,
					'S_ADD_TEAM'			=> ($action == 'add') ? true : false,
					'S_ERROR'				=> (sizeof($error)) ? true : false,
					'S_KO_LEAGUE'			=> $ko_league,
					'S_VERSION_NO'			=> $this->config['football_version'],
					'ERROR_MSG'				=> (sizeof($error)) ? implode('<br />', $error) : '',
					'SEASON'				=> $season,
					'SEASON_NAME'			=> $season_name,
					'LEAGUE'				=> $league,
					'LEAGUE_NAME'			=> $league_name,
					'TEAM'					=> $team,
					'TEAM_NAME'				=> $team_row['team_name'],
					'TEAM_SHORT'			=> $team_row['team_name_short'],
					'TEAM_SYMBOL'			=> $team_row['team_symbol'],
					'TEAM_SYMBOL_OPTIONS'	=> $teamsymbol_options,
					'TEAM_IMAGE'			=> ($team_row['team_symbol']) ? $this->ext_football_path . 'images/flags/' . $team_row['team_symbol'] : $phpbb_root_path . 'football/images/flags/blank.gif',
					'TEAM_GROUP'			=> $team_row['group_id'],
					'TEAM_ROUND'			=> $team_row['matchday'],
					'TEAM_MATCHDAY_OPTIONS'	=> $matchday_options,
					'PHPBB_ROOT_PATH'		=> $phpbb_root_path,
					'U_BACK'				=> $u_back,
					'U_ACTION'				=> "{$this->u_action}&amp;action=$action&amp;s=$season&amp;l=$league",
					)
				);

				return;
			break;
		}

		$template->assign_vars(array(
			'U_ACTION'			=> $this->u_action,
			'U_FOOTBALL' 		=> $helper->route('football_main_controller',array('side' => 'bet', 's' => $season, 'l' => $league)),
			'S_SEASON'			=> $season,
			'S_LEAGUE'			=> $league,
			'S_KO_LEAGUE'		=> $ko_league,
			'S_SEASON_OPTIONS'	=> $season_options,
			'S_LEAGUE_OPTIONS'	=> $league_options,
			'S_TEAM_OPTIONS'	=> $team_options,
			'S_TEAM_ADD'		=> true,
			) 
		);
		
		// Get us all the teams
		$sql = 'SELECT t.*,
				SUM(IF(m.team_id_home = t.team_id, 1 , 0)) AS matches_home,
				SUM(IF(m.team_id_guest = t.team_id, 1 , 0)) AS matches_away
			FROM ' . FOOTB_TEAMS . ' AS t 
			LEFT JOIN ' . FOOTB_MATCHES . " AS m ON (m.season = $season AND m.league = $league AND (m.team_id_home = t.team_id OR m.team_id_guest = t.team_id)) 
			WHERE t.season = $season 
				AND t.league = $league
			GROUP BY t.team_id
			ORDER BY team_id ASC";
		$result = $db->sql_query($sql);
		$rows_teams = $db->sql_fetchrowset($result);
		$db->sql_freeresult($result);

		// Check if the user is allowed to delete a team.
		if ($user->data['user_type'] != USER_FOUNDER && $this->config['football_founder_delete'])
		{
			$allow_delete = false;
		}
		else
		{
			$allow_delete = true;
		}

		$row_number = 0;
		$matches = 0;
		foreach ($rows_teams as $row_team)
		{
			$row_number++;
			$row_class = (!($row_number % 2)) ? 'bg1 row_light' : 'bg2 row_dark';
			$matches += ($row_team['matches_home'] + $row_team['matches_away']) / 2;
			$template->assign_block_vars('teams', array(
				'ROW_CLASS'		=> $row_class,
				'TEAM'			=> $row_team['team_id'],
				'TEAM_IMAGE'	=> ($row_team['team_symbol']) ? $this->ext_football_path . 'images/flags/' . $row_team['team_symbol'] : $this->ext_football_path . 'images/flags/blank.gif',
				'TEAM_NAME'		=> $row_team['team_name'],
				'TEAM_SHORT'	=> $row_team['team_name_short'],
				'TEAM_MATCHES'	=> $row_team['matches_home'] + $row_team['matches_away'],
				'TEAM_HOME'		=> $row_team['matches_home'],
				'TEAM_GROUP'	=> $row_team['group_id'],
				'TEAM_ROUND'	=> $row_team['matchday'],
				'U_EDIT'		=> "{$this->u_action}&amp;action=edit&amp;s=" . $season . "&amp;l=" .$league . "&amp;t=" .$row_team['team_id'],
				'U_DELETE'		=> ($allow_delete) ? "{$this->u_action}&amp;action=delete&amp;s=" . $season . "&amp;l=" . $league . "&amp;t=" . $row_team['team_id'] : '',
				'S_VERSION_NO'	=> $this->config['football_version'],
				)
			);
		}
		$template->assign_vars(array(
			'S_TEAMS'			=> ($row_number) ? '(' . $row_number . ')' : '',
			'S_MATCHES'			=> ($row_number) ? $matches : '',
			) 
		);
	}
}
?>