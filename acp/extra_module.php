<?php
/**
*
* @package phpBB Extension - Football Football
* @copyright (c) 2016 football (http://football.bplaced.net)
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace football\football\acp;

class extra_module
{
	public $u_action;

	protected $db, $user, $template, $config, $phpbb_root_path, $phpbb_admin_path, $phpEx;
	protected $root_path, $request, $php_ext, $log;


	public function __construct()
	{
		global $db, $user, $request, $template;
		global $config, $phpbb_root_path, $phpbb_admin_path, $phpEx;

		$user->add_lang_ext('football/football', 'football');
		$user->add_lang_ext('football/football', 'info_acp_extra');

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
		
		$this->tpl_name = 'acp_football_extra';
		$this->page_title = 'ACP_FOOTBALL_EXTRA_MANAGE';

		$form_key = 'acp_football_extra';
		add_form_key($form_key);

		include($this->phpbb_root_path . 'includes/functions_user.' . $this->php_ext);

		// Check and set some common vars
		$action			= (isset($_POST['add'])) ? 'add' : ((isset($_POST['remove'])) ? 'remove' : $this->request->variable('action', ''));
		$edit			= $this->request->variable('edit', 0);
		$season			= $this->request->variable('s', 0);
		$league			= $this->request->variable('l', 0);
		$matchday		= $this->request->variable('matchday', 0);
		$matchday_eval	= $this->request->variable('matchday_eval', 0);
		$extra_no		= $this->request->variable('e', 0);
		$update			= (isset($_POST['update'])) ? true : false;

		// Clear some vars
		$extra_row = array();
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
					$league_matchdays = $row['matchdays'];
					$league_name = $row['league_name'];
					$league_type = $row['league_type'];
					$ko_league = ($row['league_type'] == LEAGUE_KO) ? true : false;
				}
			}
			$db->sql_freeresult($result);
		}
		else
		{
			trigger_error(sprintf($user->lang['NO_LEAGUE'], $season) . adm_back_link($this->u_action . "&amp;s=$season"), E_USER_WARNING);
		}

		// Grab basic data for extra bets, if extra bet is set and exists
		if ($extra_no)
		{
			$sql = 'SELECT *
				FROM ' . FOOTB_EXTRA . "
				WHERE season = $season 
					AND league = $league 
					AND extra_no = $extra_no";
			$result = $db->sql_query($sql);
			$extra_row = $db->sql_fetchrow($result);
			$existing_extra = sizeof($extra_row);
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

				if (!$league)
				{
					trigger_error($user->lang['NO_LEAGUE'] . adm_back_link($this->u_action . "&amp;s=$season"), E_USER_WARNING);
				}

				if (!$extra_no)
				{
					trigger_error($user->lang['NO_EXTRA'] . adm_back_link($this->u_action . "&amp;s=$season&amp;l=$league"), E_USER_WARNING);
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
						trigger_error($user->lang['EXTRA_NO_DELETE'] . adm_back_link($this->u_action . "&amp;s=$season&amp;l=$league"), E_USER_WARNING);
					}

					// Delete extra
					$sql = 'DELETE FROM ' . FOOTB_EXTRA . "
							WHERE season = $season AND league = $league AND extra_no = $extra_no";
					$db->sql_query($sql);

					// Delete extra bets
					$sql = 'DELETE FROM ' . FOOTB_EXTRA_BETS . "
							WHERE  season = $season 
								AND league = $league 
								AND extra_no = $extra_no";
					$db->sql_query($sql);

					trigger_error($user->lang['EXTRA_DELETED'] . adm_back_link($this->u_action . "&amp;s=$season&amp;l=$league"));
				}
				else
				{
					confirm_box(false, sprintf($user->lang['EXTRA_CONFIRM_DELETE'], $extra_row['question'], $league, $season), build_hidden_fields(array(
						's'			=> $season,
						'l'			=> $league,
						'e'			=> $extra_no,
						'mode'		=> $mode,
						'action'	=> $action))
					);
				}
			break;

			case 'add':
				$sql = "SELECT 
						max(extra_no) AS max_extra_no
					FROM " . FOOTB_EXTRA . " 
					WHERE season = $season 
						AND league = $league";
				$result = $db->sql_query($sql);
				$row_extra = $db->sql_fetchrow($result);
				$existing_extra = sizeof($row_extra);
				$db->sql_freeresult($result);
				$extra_no = ($existing_extra) ? $row_extra['max_extra_no'] + 1 : 1;
				$extra_row['extra_no'] 		= $extra_no;
				$extra_row['question_type'] = $this->request->variable('question_type', 3);
				$extra_row['question'] 		= utf8_normalize_nfc($this->request->variable('question', '', true));
				$extra_row['matchday']		= $this->request->variable('matchday', 0);
				$extra_row['matchday_eval']	= $this->request->variable('matchday_eval', 0);
				$extra_row['result']		= utf8_normalize_nfc($this->request->variable('result', ''));
				$extra_row['extra_points']	= $this->request->variable('extra_points', 0);
				$extra_row['extra_status']	= $this->request->variable('extra_status', 0);
				// No break for edit add
			case 'edit':
				$error_msg = array();

				if (!sizeof($error))
				{
					if ($action == 'edit' && !$extra_no)
					{
						trigger_error($user->lang['NO_EXTRA'] . adm_back_link($this->u_action . "&amp;s=$season&amp;l=$league"), E_USER_WARNING);
					}
					
					$matchday = $extra_row['matchday'];
					$matchday_eval = $extra_row['matchday_eval'];

					$sql = 'SELECT *
						FROM ' . FOOTB_MATCHDAYS . "
						WHERE season = $season 
							AND league = $league 
						ORDER BY matchday ASC";
					$result = $db->sql_query($sql);

					$matchday_options 		= '<option value="0"' . ((!$matchday) ? ' selected="selected"' : '') . '>' . $user->lang['SELECT_MATCHDAY'] . '</option>';
					$matchday_eval_options 	= '<option value="0"' . ((!$matchday_eval) ? ' selected="selected"' : '') . '>' . $user->lang['SELECT_MATCHDAY'] . '</option>';
					while ($row = $db->sql_fetchrow($result))
					{
						if ($row['status'] == 0 or $action == 'edit')
						{
							$selected_matchday 	= ($matchday && $row['matchday'] == $matchday) ? ' selected="selected"' : '';
							$selected_eval 		= ($matchday_eval && $row['matchday'] == $matchday_eval) ? ' selected="selected"' : '';
							$day_name 			= (strlen($row['matchday_name']) > 0) ? $row['matchday_name'] : $row['matchday'] . '. ' . sprintf($user->lang['MATCHDAY']);
							$matchday_options 		.= '<option value="' . $row['matchday'] . '"' . $selected_matchday . '>' . $day_name . '</option>';
							$matchday_eval_options 	.= '<option value="' . $row['matchday'] . '"' . $selected_eval . '>' . $day_name . '</option>';
						}
					}
					$db->sql_freeresult($result);
					$question_type_options = '';
					for($i = 1; $i<= 5; $i++)
					{
						$selected = ($i == $extra_row['question_type']) ? ' selected="selected"' : '';
						$question_type_options .= '<option value="' . $i . '"' . $selected . '>' . $i . '</option>';
					}

					$extra_status_options = '';
					for($i = 0; $i<= 3; $i++)
					{
						$selected = ($i == $extra_row['extra_status']) ? ' selected="selected"' : '';
						$extra_status_options .= '<option value="' . $i . '"' . $selected . '>' . $i . '</option>';
					}
				

					// Did we submit?
					if ($update)
					{
						$data = array();
						if (!check_form_key($form_key))
						{
							trigger_error($user->lang['FORM_INVALID'] . adm_back_link($this->u_action . "&amp;s=$season&amp;l=$league"), E_USER_WARNING);
						}
						$extra_row['extra_no'] 		= $extra_no;
						$extra_row['question_type'] = $this->request->variable('question_type', $extra_row['question_type']);
						$extra_row['question'] 		= $this->request->variable('question', $extra_row['question'], true);
						$extra_row['matchday']		= $this->request->variable('matchday', $extra_row['matchday']);
						$extra_row['matchday_eval']	= $this->request->variable('matchday_eval', $extra_row['matchday_eval']);
						$extra_row['extra_points']	= $this->request->variable('extra_points', $extra_row['extra_points']);
						$extra_row['extra_status']	= $this->request->variable('extra_status', $extra_row['extra_status']);

						$data['extra_points'] 	= (int) $extra_row['extra_points'];
						$data['matchday'] 		= (int) $extra_row['matchday'];
						$data['matchday_eval'] 	= (int) $extra_row['matchday_eval'];
						
						if ($data['matchday_eval'] <  $data['matchday'])
						{
							$error[] = $user->lang['EVAL_BEFORE_DELIVERY'];
						}
						

						if (!sizeof($error))
						{
							$sql_ary = array(
								'season'			=> (int) $season,
								'league'			=> (int) $league,
								'extra_no'			=> (int) $extra_no,
								'question_type'		=> (int) $extra_row['question_type'],
								'question'			=> strlen($extra_row['question']) ? $extra_row['question'] : '',
								'matchday'			=> (int) $extra_row['matchday'],
								'matchday_eval'		=> (int) $extra_row['matchday_eval'],
								'result'			=> $extra_row['result'],
								'extra_points'		=> (int) $extra_row['extra_points'],
								'extra_status'		=> (int) $extra_row['extra_status'],
							);

							$var_ary = array(
								'extra_points'	=> array('num', false, 0, 99),
								'matchday'		=> array('num', false, 1),
								'matchday_eval'	=> array('num', false, 1),
							);
							if (!($error_vals = validate_data($data, $var_ary)))
							{
								if ($action == 'add')
								{
									$sql = 'INSERT INTO ' . FOOTB_EXTRA . ' ' . $db->sql_build_array('INSERT', $sql_ary);
									$db->sql_query($sql);
								}
								else
								{
									$sql = 'UPDATE ' . FOOTB_EXTRA . '
										SET ' . $db->sql_build_array('UPDATE', $sql_ary) . "
										WHERE season = $season AND league = $league AND extra_no = $extra_no";
									$db->sql_query($sql);
								}
								trigger_error($user->lang['EXTRA_UPDATED'] . adm_back_link($this->u_action . "&amp;s=$season&amp;l=$league"));
							}
							else
							{
								foreach ($error_vals as $error_val)
								{
										$error_msg[] = $user->lang[$error_val];
								}
								$error[] =  $user->lang['EXTRA_UPDATE_FAILED'];
								$error = array_merge($error, $error_msg);
							}
						}
					}
				}
		
				$u_back = $this->u_action . "&amp;s=$season&amp;l=$league";

				$template->assign_vars(array(
					'S_EDIT'					=> true,
					'S_ADD_EXTRA'				=> ($action == 'add') ? true : false,
					'S_ERROR'					=> (sizeof($error)) ? true : false,
					'S_EDIT_EXTRAS'				=> ($existing_extra) ? false : true,
					'S_QUESTION_TYPE_OPTIONS'	=> $question_type_options,
					'S_MATCHDAY_OPTIONS'		=> $matchday_options,
					'S_MATCHDAY_EVAL_OPTIONS' 	=> $matchday_eval_options,
					'S_EXTRA_STATUS_OPTIONS' 	=> $extra_status_options,
					'S_VERSION_NO'				=> $this->config['football_version'],
					'ERROR_MSG'					=> (sizeof($error)) ? implode('<br />', $error) : '',
					'SEASON'					=> $season,
					'SEASON_NAME'				=> $season_name,
					'LEAGUE'					=> $league,
					'LEAGUE_NAME'				=> $league_name,
					'EXTRA_NO'					=> $extra_no,
					'QUESTION_TYPE'				=> $extra_row['question_type'],
					'QUESTION'					=> $extra_row['question'],
					'MATCHDAY'					=> $extra_row['matchday'],
					'MATCHDAY_EVAL'				=> $extra_row['matchday_eval'],
					'MATCHDAY_OPTION'			=> $extra_row['matchday'],
					'MATCHDAY_EVAL'				=> $extra_row['matchday_eval'],
					'RESULT'					=> $extra_row['result'],
					'EXTRA_POINTS'				=> $extra_row['extra_points'],
					'EXTRA_STATUS'				=> $extra_row['extra_status'],
					'U_BACK'					=> $u_back,
					'U_ACTION'					=> "{$this->u_action}&amp;action=$action&amp;s=$season&amp;l=$league",
					)
				);
				return;
			break;
		}
		
		// Check open matchday in league
		$sql = 'SELECT *
			FROM ' . FOOTB_MATCHDAYS . " 
			WHERE season = $season 
				AND league = $league
				AND status <= 0";
		$result = $db->sql_query($sql);
		$open_matchdays = sizeof($db->sql_fetchrowset($result));
		$db->sql_freeresult($result);
		
		// Get us all the extra
		$sql = "SELECT e.*,
				m1.matchday_name AS matchday_name,
				m2.matchday_name AS matchday_eval_name
			FROM " . FOOTB_EXTRA . ' AS e
			LEFT JOIN ' . FOOTB_MATCHDAYS . ' AS m1 ON (m1.season = e.season AND m1.league = e.league AND m1.matchday = e.matchday) 
			LEFT JOIN ' . FOOTB_MATCHDAYS . " AS m2 ON (m2.season = e.season AND m2.league = e.league AND m2.matchday = e.matchday_eval) 
			WHERE e.season = $season 
				AND e.league = $league
			ORDER BY e.extra_no ASC";
		$result = $db->sql_query($sql);
		$rows_extra = $db->sql_fetchrowset($result);
		$db->sql_freeresult($result);

		$template->assign_vars(array(
			'U_ACTION'			=> $this->u_action,
			'U_FOOTBALL' 		=> $helper->route('football_football_controller',array('side' => 'bet', 's' => $season, 'l' => $league)),
			'S_SEASON'			=> $season,
			'S_LEAGUE'			=> $league,
			'S_SEASON_OPTIONS'	=> $season_options,
			'S_LEAGUE_OPTIONS'	=> $league_options,
			'S_EXTRA_ADD'		=> ($open_matchdays) ? true : false,
			'S_VERSION_NO'		=> $this->config['football_version'],
			) 
		);

		// Check if the user is allowed to delete a extra.
		if ($user->data['user_type'] != USER_FOUNDER && $this->config['football_founder_delete'])
		{
			$allow_delete = false;
		}
		else
		{
			$allow_delete = true;
		}

		$row_number = 0;
		foreach ($rows_extra as $row_extra)
		{
			$row_number++;
			$row_class = (!($row_number % 2)) ? 'bg1 row_light' : 'bg2 row_dark';
			$template->assign_block_vars('extras', array(
				'ROW_CLASS'			=> $row_class,
				'EXTRA_NO'			=> $row_extra['extra_no'],
				'QUESTION_TYPE'		=> $row_extra['question_type'],
				'QUESTION'			=> $row_extra['question'],
				'MATCHDAY'			=> (strlen($row_extra['matchday_name']) > 0) ? $row_extra['matchday_name'] : $row_extra['matchday'] . '. ' . sprintf($user->lang['MATCHDAY']),
				'MATCHDAY_EVAL'		=> (strlen($row_extra['matchday_name']) > 0) ? $row_extra['matchday_eval_name'] : $row_extra['matchday_eval'] . '. ' . sprintf($user->lang['MATCHDAY']),
				'EXTRA_POINTS'		=> $row_extra['extra_points'],
				'EXTRA_STATUS'		=> $row_extra['extra_status'],
				'U_EDIT'	=> "{$this->u_action}&amp;action=edit&amp;s=" . $season . "&amp;l=" .$league . "&amp;e=" .$row_extra['extra_no'],
				'U_DELETE'	=> ($allow_delete) ? "{$this->u_action}&amp;action=delete&amp;s=" . $season . "&amp;l=" . $league . "&amp;e=" . $row_extra['extra_no'] : '',
				)
			);
		}
	}
}
