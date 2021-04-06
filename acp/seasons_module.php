<?php
/**
*
* @package phpBB Extension - Football Football
* @copyright (c) 2016 football (http://football.bplaced.net)
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace football\football\acp;

class seasons_module
{
	public $u_action;

	protected $db, $user, $template, $config, $phpbb_root_path, $phpbb_admin_path, $phpEx;
	protected $root_path, $request, $php_ext, $log;


	public function __construct()
	{
		global $db, $user, $request, $template;
		global $config, $phpbb_root_path, $phpbb_admin_path, $phpEx;

		$user->add_lang_ext('football/football', 'football');
		$user->add_lang_ext('football/football', 'info_acp_seasons');

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
		
		$this->tpl_name = 'acp_football_seasons';
		$this->page_title = 'ACP_FOOTBALL_SEASONS_MANAGE';

		$form_key = 'acp_football_seasons';
		add_form_key($form_key);

		include_once($phpbb_root_path . 'includes/functions_user.' . $phpEx);

		// Check and set some common vars
		$action		= (isset($_POST['add'])) ? 'add' : $this->request->variable('action', '');
		$season		= $this->request->variable('s', 0);
		$edit		= $this->request->variable('edit', 0);
		$update		= (isset($_POST['update'])) ? true : false;

		// Clear some vars
		$season_row = array();
		$error = array();

		// Grab basic data for season, if season is set and exists
		if ($season)
		{
			$sql = 'SELECT *
				FROM ' . FOOTB_SEASONS . "
				WHERE season = $season";
			$result = $db->sql_query($sql);
			$season_row = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);
		}

		// Which page?
		switch ($action)
		{
			case 'delete':
				if (!$season)
				{
					trigger_error($user->lang['NO_SEASON'] . adm_back_link($this->u_action), E_USER_WARNING);
				}

				if (confirm_box(true))
				{
					$error = '';
					if (!$auth->acl_get('a_football_delete'))
					{
						trigger_error($user->lang['NO_AUTH_OPERATION'] . adm_back_link($this->u_action), E_USER_WARNING);
					}
					if ($user->data['user_type'] != USER_FOUNDER && $this->config['football_founder_delete'])
					{
						trigger_error($user->lang['SEASONS_NO_DELETE'] . adm_back_link($this->u_action), E_USER_WARNING);
					}
					// Delete season
					$sql = 'DELETE FROM ' . FOOTB_SEASONS . "
						WHERE season = $season";
					$db->sql_query($sql);

					// Delete leagues
					$sql = 'DELETE FROM ' . FOOTB_LEAGUES . "
						WHERE season = $season";
					$db->sql_query($sql);

					// Delete matchdays
					$sql = 'DELETE FROM ' . FOOTB_MATCHDAYS . "
						WHERE season = $season";
					$db->sql_query($sql);

					// Delete matches
					$sql = 'DELETE FROM ' . FOOTB_MATCHES . "
						WHERE season = $season";
					$db->sql_query($sql);

					// Delete teams
					$sql = 'DELETE FROM ' . FOOTB_TEAMS . "
						WHERE season = $season";
					$db->sql_query($sql);

					// Delete ranks
					$sql = 'DELETE FROM ' . FOOTB_RANKS . "
						WHERE season = $season";
					$db->sql_query($sql);

					// Delete bets
					$sql = 'DELETE FROM ' . FOOTB_BETS . "
						WHERE season = $season";
					$db->sql_query($sql);

					trigger_error($user->lang['SEASON_DELETED'] . adm_back_link($this->u_action));
				}
				else
				{
					confirm_box(false, sprintf($user->lang['SEASON_CONFIRM_DELETE'], $season), build_hidden_fields(array(
						's'			=> $season,
						'mode'		=> $mode,
						'action'	=> $action))
					);
				}
			break;

			case 'add':
				if ($season >= 1963 AND $season <= 2099)
				{
					if ($season_row)
					{
						if ($edit)
						{
							$error[] =  $user->lang['SEASON_TAKEN'];
						}
						else
						{
							trigger_error($user->lang['SEASON_TAKEN'] . adm_back_link($this->u_action), E_USER_WARNING);
						}
					}
					$season_row['season_name'] = utf8_normalize_nfc($this->request->variable('season_name', '', true));
					if ($season_row['season_name'] <> '')
					{
						$sql = 'SELECT 
								season_name
							FROM ' . FOOTB_SEASONS . "
							WHERE season_name = '" . $season_row['season_name'] . "'";
						$result = $db->sql_query($sql);
						$row = $db->sql_fetchrow($result);
						$db->sql_freeresult($result);

						if ($row)
						{
							$error[] =  $user->lang['SEASON_NAME_TAKEN'];
						}
					}
					else
					{
						$intseason = ((int) $season) - 1;
						$season_row['season_name'] = $user->lang['SEASON'] . ' ' . $intseason . '/' . $season;
					}

					$season_row['season_name_short'] = utf8_normalize_nfc($this->request->variable('season_short', '', true));
					if ($season_row['season_name_short'] <> '')
					{
						$sql = 'SELECT 
								season_name_short
							FROM ' . FOOTB_SEASONS . "
							WHERE season_name_short = '" . $season_row['season_name_short'] . "'";
						$result = $db->sql_query($sql);
						$row = $db->sql_fetchrow($result);
						$db->sql_freeresult($result);

						if ($row)
						{
							$error[] =  $user->lang['SEASON_SHORT_TAKEN'];
						}
					}
					else
					{
						$intseason = ((int) $season) - 1;
						$season_row['season_name_short'] = $intseason . '/' . $season;
					}
				}
				else 
				{
					trigger_error($user->lang['SEASON_NUMBER'] . adm_back_link($this->u_action), E_USER_WARNING);
				}
				// No break for edit add
			case 'edit':
				$data = array();

				if (!sizeof($error))
				{
					if ($action == 'edit' && !$season)
					{
						trigger_error($user->lang['NO_SEASON'] . adm_back_link($this->u_action), E_USER_WARNING);
					}

					// Did we submit?
					if ($update)
					{
						if (!check_form_key($form_key))
						{
							trigger_error($user->lang['FORM_INVALID'] . adm_back_link($this->u_action), E_USER_WARNING);
							return;
						}

						$season_row['season_name'] = utf8_normalize_nfc($this->request->variable('season_name', '', true));
						if ($season_row['season_name'] <> '')
						{
							$sql = 'SELECT 
									season
								FROM ' . FOOTB_SEASONS . "
								WHERE season_name = '" . $season_row['season_name'] . "'";
							$result = $db->sql_query($sql);
							$row = $db->sql_fetchrow($result);
							$db->sql_freeresult($result);

							if ($row)
							{
								if ($row['season'] <> $season)
								{
									$error[] =  $user->lang['SEASON_NAME_TAKEN'];
								}
							}
						}
						else
						{
							$error[] =  $user->lang['SEASON_NAME_EMPTY'];
						}

						$season_row['season_name_short'] = utf8_normalize_nfc($this->request->variable('season_short', '', true));
						if ($season_row['season_name_short'] <> '')
						{
							$sql = 'SELECT 
									season
								FROM ' . FOOTB_SEASONS . "
								WHERE season_name_short = '" . $season_row['season_name_short'] . "'";
							$result = $db->sql_query($sql);
							$row = $db->sql_fetchrow($result);
							$db->sql_freeresult($result);

							if ($row)
							{
								if ($row['season'] <> $season)
								{
									$error[] =  $user->lang['SEASON_SHORT_TAKEN'];
								}
							}
						}
						else
						{
							$error[] =  $user->lang['SEASON_SHORT_EMPTY'];
						}
						if (!sizeof($error))
						{
							$sql_ary = array(
								'season'			=> (int) $season,
								'season_name'		=> $season_row['season_name'],
								'season_name_short'	=> $season_row['season_name_short'],
							);

							$data['season']			= $season;
							$data['season_name']	= $this->request->variable('season_name', '');
							$data['season_short']	= $this->request->variable('season_short', '');
							
							$var_ary = array(
								'season'		=> array('num', false, 1963, 2099),
								'season_name'	=> array('string', false, 4, 20),
								'season_short'	=> array('string', false, 2, 10),
							);
							if (!($error_vals = validate_data($data, $var_ary)))
							{
								if ($action == 'add')
								{
									$sql = 'INSERT INTO ' . FOOTB_SEASONS . ' ' . $db->sql_build_array('INSERT', $sql_ary);
									$db->sql_query($sql);
								}
								else
								{
									$sql = 'UPDATE ' . FOOTB_SEASONS . '
										SET ' . $db->sql_build_array('UPDATE', $sql_ary) . "
										WHERE season = $season";
									$db->sql_query($sql);
								}
								$message = ($action == 'edit') ? 'SEASON_UPDATED' : 'SEASON_CREATED';
								trigger_error($user->lang[$message] . adm_back_link($this->u_action));
							}
							else
							{
								foreach ($error_vals as $error_val)
								{
										$error_msg[] = $user->lang[$error_val];
								}
								$message = ($action == 'edit') ? 'SEASON_UPDATE_FAILED' : 'SEASON_CREATE_FAILED';
								$error[] =  $user->lang[$message];
								$error = array_merge($error, $error_msg);
							}
						}
					}
				}

				$u_back = $this->u_action;

				$template->assign_vars(array(
					'S_EDIT'			=> true,
					'S_ADD_SEASON'		=> ($action == 'add') ? true : false,
					'S_ERROR'			=> (sizeof($error)) ? true : false,
					'ERROR_MSG'			=> (sizeof($error)) ? implode('<br />', $error) : '',
					'SEASON'			=> $season,
					'SEASON_NAME'		=> $season_row['season_name'],
					'SEASON_SHORT'		=> $season_row['season_name_short'],
					'U_BACK'			=> $u_back,
					'U_ACTION'			=> "{$this->u_action}&amp;action=$action&amp;s=$season",
					'S_VERSION_NO'		=> $this->config['football_version'],
					)
				);
				return;
			break;
		}

		$template->assign_vars(array(
			'U_ACTION'		=> $this->u_action,
			'U_FOOTBALL' 				=> $helper->route('football_football_controller',array('side' => 'bet')),
			'S_SEASON_ADD'	=> true,
			) 
		);
		
		// Get us all the seasons
		$sql = 'SELECT 
				s.season, 
				s.season_name, 
				s.season_name_short, 
				COUNT(l.league) AS leagues
			FROM ' . FOOTB_SEASONS . ' s
			LEFT JOIN ' . FOOTB_LEAGUES . ' l on l.season = s.season
			GROUP BY s.season
			ORDER BY s.season DESC';
		$result = $db->sql_query($sql);
		$rows_seasons = $db->sql_fetchrowset($result);
		$db->sql_freeresult($result);

		// Check if the user is allowed to delete a season.
		if ($user->data['user_type'] != USER_FOUNDER && $this->config['football_founder_delete'])
		{
			$allow_delete = false;
		}
		else
		{
			$allow_delete = true;
		}

		$row_number = 0;
		foreach ($rows_seasons as $row_season)
		{
			$row_number++;
			$row_class = (!($row_number % 2)) ? 'bg1 row_light' : 'bg2 row_dark';
			$template->assign_block_vars('seasons', array(
				'ROW_CLASS'		=> $row_class,
				'SEASON'		=> $row_season['season'],
				'SEASON_NAME'	=> $row_season['season_name'],
				'SEASON_SHORT'	=> $row_season['season_name_short'],
				'LEAGUES'		=> $row_season['leagues'],
				'U_EDIT'		=> "{$this->u_action}&amp;action=edit&amp;s=" .$row_season['season'],
				'U_DELETE'		=> ($allow_delete) ? "{$this->u_action}&amp;action=delete&amp;s=" . $row_season['season'] : '',
				)
			);
		}
	}
}
