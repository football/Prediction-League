<?php
/**
*
* @package phpBB Extension - Football Football
* @copyright (c) 2016 football (http://football.bplaced.net)
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace football\football\acp;

class leagues_module
{
	public $u_action;

	protected $db, $user, $template, $config, $phpbb_root_path, $phpbb_admin_path, $phpEx;
	protected $root_path, $request, $php_ext, $log;


	public function __construct()
	{
		global $db, $user, $request, $template;
		global $config, $phpbb_root_path, $phpbb_admin_path, $phpEx;

		$user->add_lang_ext('football/football', 'football');
		$user->add_lang_ext('football/football', 'info_acp_leagues');

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
		
		$this->tpl_name = 'acp_football_leagues';
		$this->page_title = 'ACP_FOOTBALL_LEAGUES_MANAGE';

		$form_key = 'acp_football_leagues';
		add_form_key($form_key);

		include($phpbb_root_path . 'includes/functions_user.' . $phpEx);

		// Check and set some common vars
		$action		= (isset($_POST['add'])) ? 'add' : ((isset($_POST['addmembers'])) ? 'addmembers' : $this->request->variable('action', ''));
		$edit		= $this->request->variable('edit', 0);
		$season		= $this->request->variable('s', 0);
		$league		= $this->request->variable('l', 0);
		$group_id	= $this->request->variable('g', 0);
		$start		= $this->request->variable('start', 0);
		$update		= (isset($_POST['update'])) ? true : false;

		// Clear some vars
		$league_info = array();
		$error = array();

		// Grab current season
		if (!$season)
		{
			$season = curr_season();
		}
		// Grab basic data for season
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
					$season_name = $row['season_name'];
				}
			}
			$db->sql_freeresult($result);
		}
		else
		{
			trigger_error($user->lang['NO_SEASON'] . adm_back_link($this->u_action . "&amp;s=$season"), E_USER_WARNING);
		}

		// Grab basic data for league, if league is set and exists
		if ($league)
		{
			$league_info = league_info($season, $league);
		}

		// Which page?
		switch ($action)
		{
			case 'addmembers':
				if (!$league)
				{
					trigger_error($user->lang['NO_LEAGUE'] . adm_back_link($this->u_action), E_USER_WARNING);
				}
				
				$usernames	= $this->request->variable('usernames', '', true);
				if ($usernames)
				{
					$username_ary = array_unique(explode("\n", $usernames));

					// Add user/s to league
					// We need both username and user_id info
					$user_id_ary = false;
					$result = user_get_id_name($user_id_ary, $username_ary);

					if (!sizeof($user_id_ary) || $result !== false)
					{
						trigger_error($user->lang['NO_MEMBERS_SELECTED'] . adm_back_link($this->u_action . "&amp;action=list&amp;s=$season&amp;l=$league"), E_USER_WARNING);
					}
				}
				else
				{
					if ($group_id)
					{
						$sql = 'SELECT u.user_id, u.username
							FROM ' . USERS_TABLE . ' u, ' . USER_GROUP_TABLE . ' ug
							WHERE ug.group_id = ' . $group_id . '
								AND ug.user_pending = 0
								AND u.user_id = ug.user_id
								AND u.user_type IN (' . USER_NORMAL . ', ' . USER_FOUNDER . ')
							ORDER BY u.user_id';
					}
					else
					{
						$sql = 'SELECT user_id, username
							FROM ' . USERS_TABLE . '
							WHERE user_type IN (' . USER_NORMAL . ', ' . USER_FOUNDER . ')
							ORDER BY user_id';
					}
					$result = $db->sql_query($sql);
					if (!($row = $db->sql_fetchrow($result)))
					{
						$db->sql_freeresult($result);
						trigger_error($user->lang['NO_MEMBERS_SELECTED'] . adm_back_link($this->u_action . "&amp;action=list&amp;s=$season&amp;l=$league"), E_USER_WARNING);
					}

					do
					{
						$username_ary[$row['user_id']] = $row['username'];
						$user_id_ary[] = $row['user_id'];
					}
					while ($row = $db->sql_fetchrow($result));
					$db->sql_freeresult($result);
				}


				if ($league_info['league_type'] == LEAGUE_KO)
				{
					// Check matchdays
					$sql = 'SELECT 
							COUNT(matchday) AS matchdays
						FROM ' . FOOTB_MATCHDAYS . "
						WHERE season = $season 
							AND league = $league";
					$result = $db->sql_query($sql);
					$matchdays = (int) $db->sql_fetchfield('matchdays');
					$db->sql_freeresult($result);
					if ($matchdays < $league_info['matchdays'])
					{
						trigger_error($user->lang['NO_MATCHDAYS_KO'] . adm_back_link($this->u_action), E_USER_WARNING);
					}
				}
				
				foreach ($user_id_ary as $user_id)
				{
					// Test user is member 
					$sql = 'SELECT 
							COUNT(user_id) AS total_bets
						FROM ' . FOOTB_BETS . "
						WHERE season = $season 
							AND league = $league 
							AND user_id = $user_id";
					$result = $db->sql_query($sql);
					$total_bets = (int) $db->sql_fetchfield('total_bets');
					$db->sql_freeresult($result);
					
					if ($total_bets > 0) 
					{
						$error[] =  $user->lang['MEMBER_EXISTS'];
					}
					else
					{
						$count_updates = join_league($season, $league, $user_id);
					}
				}
				
				$back_link =  $this->u_action . '&amp;action=list&amp;s=' . $season . '&amp;l=' . $league;
				trigger_error($user->lang['LEAGUE_USERS_ADD'] . adm_back_link($back_link));
			break;
			case 'deletemembers':
				if (!$league)
				{
					trigger_error($user->lang['NO_LEAGUE'] . adm_back_link($this->u_action), E_USER_WARNING);
				}

				$mark_ary	= $this->request->variable('mark', array(0));
				if (sizeof($mark_ary) == 0)
				{
					trigger_error($user->lang['NO_MEMBERS_SELECTED'] . adm_back_link($this->u_action), E_USER_WARNING);
				}
				
				if (confirm_box(true))
				{
					if (!$auth->acl_get('a_football_delete'))
					{
						trigger_error($user->lang['NO_AUTH_OPERATION'] . adm_back_link($this->u_action . "&amp;s=$season"), E_USER_WARNING);
					}
					if ($user->data['user_type'] != USER_FOUNDER && $this->config['football_founder_delete'])
					{
						trigger_error($user->lang['SEASONS_NO_DELETE'] . adm_back_link($this->u_action . "&amp;s=$season"), E_USER_WARNING);
					}
					// Delete bets
					$sql = 'DELETE FROM ' . FOOTB_BETS . "
						WHERE season = $season 
							AND league = $league
							AND " . $db->sql_in_set('user_id', $mark_ary);
					$db->sql_query($sql);
					// Delete ranks
					$sql = 'DELETE FROM ' . FOOTB_RANKS . "
						WHERE season = $season 
							AND league = $league
							AND " . $db->sql_in_set('user_id', $mark_ary);
					$db->sql_query($sql);
					// Delete bank statements
					$sql = 'DELETE FROM ' . FOOTB_POINTS . "
						WHERE season = $season 
							AND league = $league
							AND " . $db->sql_in_set('user_id', $mark_ary);
					$db->sql_query($sql);

					$back_link =  $this->u_action . '&amp;action=list&amp;s=' . $season . '&amp;l=' . $league;
					trigger_error($user->lang['LEAGUE_USERS_REMOVE'] . adm_back_link($back_link));
				}
				else
				{
					confirm_box(false, sprintf($user->lang['MEMBER_CONFIRM_DELETE'], $league_info['league_name'], $season), build_hidden_fields(array(
						'mark'		=> $mark_ary,
						's'			=> $season,
						'l'			=> $league,
						'i'			=> $id,
						'mode'		=> $mode,
						'action'	=> $action))
					);
				}
			break;
			case 'list':

				if (!$league)
				{
					trigger_error($user->lang['NO_LEAGUE'] . adm_back_link($this->u_action), E_USER_WARNING);
				}

				$this->page_title = 'LEAGUE_MEMBERS';

				// Total number of league members 
				$sql = 'SELECT 
						COUNT(DISTINCT user_id) AS total_members
					FROM ' . FOOTB_BETS . "
					WHERE season = $season 
						AND league = $league";
				$result = $db->sql_query($sql);
				$total_members = (int) $db->sql_fetchfield('total_members');
				$db->sql_freeresult($result);

				$s_action_options = '';
				$options = array('deletemembers' => 'DELETE');

				foreach ($options as $option => $lang)
				{
					$s_action_options .= '<option value="' . $option . '">' . $user->lang['MEMBER_' . $lang] . '</option>';
				}
				
				// Exclude bots and guests...
				$sql = 'SELECT group_id
					FROM ' . GROUPS_TABLE . "
					WHERE group_name IN ('BOTS', 'GUESTS')";
				$result = $db->sql_query($sql);

				$exclude = array();
				while ($row = $db->sql_fetchrow($result))
				{
					$exclude[] = $row['group_id'];
				}
				$db->sql_freeresult($result);
				$select_list = '<option value="0"' . ((!$group_id) ? ' selected="selected"' : '') . '>' . $user->lang['ALL_USERS'] . '</option>';
				$select_list .= group_select_options($group_id, $exclude);
				$base_url = $this->u_action . "&amp;action=list&amp;s=$season&amp;l=$league";
				$pagination = $phpbb_container->get('pagination');
				$pagination->generate_template_pagination($base_url, 'pagination', 'start', $total_members, $this->config['football_users_per_page'], $start);
				
				$template->assign_vars(array(
					'S_LIST'			=> true,
					'S_ACTION_OPTIONS'	=> $s_action_options,
					'TOTAL_MEMBERS'		=> ($total_members == 1) ? $user->lang['VIEW_BET_USER'] : sprintf($user->lang['VIEW_BET_USERS'], $total_members),
					'PAGE_NUMBER' 		=> $pagination->on_page($total_members, $this->config['football_users_per_page'], $start),
					'LEAGUE_NAME'		=> $league_info['league_name']. ' ' . $season_name,
					'U_ACTION'			=> $this->u_action . "&amp;s=$season&amp;l=$league",
					'U_BACK'			=> $this->u_action. "&amp;s=$season&amp;l=$league",
					'U_FIND_USERNAME'	=> append_sid("{$phpbb_root_path}memberlist.$phpEx", 'mode=searchuser&amp;form=list&amp;field=usernames'),
					'U_DEFAULT_ALL'		=> "{$this->u_action}&amp;action=addmembers&amp;s=$season&amp;l=$league&amp;g=0",
					'S_GROUP_OPTIONS'	=> $select_list,
					'S_VERSION_NO'		=> $this->config['football_version'],
					)
				);

				// Grab the members
				$sql = 'SELECT 
						DISTINCT u.user_id, 
						u.username, 
						u.username_clean, 
						u.user_regdate 
					FROM ' . FOOTB_BETS . ' b, ' . USERS_TABLE . " u 
					WHERE b.season = $season AND b.league = $league 
						AND u.user_id = b.user_id
					ORDER BY u.username_clean";
				$result = $db->sql_query_limit($sql, $this->config['football_users_per_page'], $start);

				while ($row = $db->sql_fetchrow($result))
				{
					$template->assign_block_vars('member', array(
						'U_USER_EDIT'	=> append_sid("{$phpbb_admin_path}index.$phpEx", "i=users&amp;action=edit&amp;u={$row['user_id']}"),
						'USERNAME'		=> $row['username'],
						'JOINED'		=> ($row['user_regdate']) ? $user->format_date($row['user_regdate']) : ' - ',
						'USER_ID'		=> $row['user_id'],
						)
					);
				}
				$db->sql_freeresult($result);

				return;
			break;
			case 'delete':
				if (!$league)
				{
					trigger_error($user->lang['NO_LEAGUE'] . adm_back_link($this->u_action . "&amp;s=$season"), E_USER_WARNING);
				}

				if (confirm_box(true))
				{
					$error = '';

					if ($user->data['user_type'] != USER_FOUNDER && $this->config['football_founder_delete'])
					{
						trigger_error($user->lang['LEAGUES_NO_DELETE'] . adm_back_link($this->u_action . "&amp;s=$season"), E_USER_WARNING);
					}

					// Delete league
					$sql = 'DELETE FROM ' . FOOTB_LEAGUES . "
							WHERE season = $season 
								AND league = $league";
					$db->sql_query($sql);

					// Delete matchdays
					$sql = 'DELETE FROM ' . FOOTB_MATCHDAYS . "
							WHERE season = $season 
								AND league = $league";
					$db->sql_query($sql);

					// Delete matches
					$sql = 'DELETE FROM ' . FOOTB_MATCHES . "
							WHERE season = $season 
								AND league = $league";
					$db->sql_query($sql);

					// Delete teams
					$sql = 'DELETE FROM ' . FOOTB_TEAMS . "
							WHERE season = $season 
								AND league = $league";
					$db->sql_query($sql);

					// Delete ranks
					$sql = 'DELETE FROM ' . FOOTB_RANKS . "
							WHERE season = $season 
								AND league = $league";
					$db->sql_query($sql);

					// Delete bets
					$sql = 'DELETE FROM ' . FOOTB_BETS . "
							WHERE season = $season 
								AND league = $league";
					$db->sql_query($sql);

					trigger_error($user->lang['LEAGUE_DELETED'] . adm_back_link($this->u_action . "&amp;s=$season"));
				}
				else
				{
					confirm_box(false, sprintf($user->lang['LEAGUE_CONFIRM_DELETE'], $league_info['league_name'], $season), build_hidden_fields(array(
						's'			=> $season,
						'l'			=> $league,
						'mode'		=> $mode,
						'action'	=> $action))
					);
				}
			break;

			case 'add':
				if ($league > 0 AND $league <= 99)
				{
					if ($league_info)
					{
						if ($edit)
						{
							$error[] =  $user->lang['LEAGUE_TAKEN'];
						}
						else
						{
							trigger_error($user->lang['LEAGUE_TAKEN'] . adm_back_link($this->u_action . "&amp;s=$season"), E_USER_WARNING);
						}
					}
					$league_info['league_name'] 		= utf8_normalize_nfc($this->request->variable('league_name', '', true));
					$league_info['league_name_short'] 	= utf8_normalize_nfc($this->request->variable('league_short', '', true));
					$league_info['league_type'] 		= $this->request->variable('league_type', 1, true);
					$league_info['bet_ko_type'] 		= $this->request->variable('bet_ko_type', 1, true);
					$league_info['matchdays'] 			= $this->request->variable('league_matchdays', 34, true);
					$league_info['matches_on_matchday'] = $this->request->variable('league_matches', 9, true);
					$league_info['bet_points'] 			= round($this->request->variable('bet_points', 0), 2);
					$league_info['win_result'] 			= $this->request->variable('league_win_hits', 0, true);
					$league_info['win_result_02'] 		= $this->request->variable('league_win_hits_away', 0, true);
					$league_info['win_matchday'] 		= $this->request->variable('league_win_matchdays', 0, true);
					$league_info['win_season'] 			= $this->request->variable('league_win_season', 0, true);
					$league_info['points_mode'] 		= $this->request->variable('league_points_mode', 1, true);
					$league_info['points_result'] 		= $this->request->variable('league_points_hit', 0, true);
					$league_info['points_tendency'] 	= $this->request->variable('league_points_tendency', 0, true);
					$league_info['points_diff'] 		= $this->request->variable('league_points_diff', 0, true);
					$league_info['points_last'] 		= $this->request->variable('league_points_last', 1, true);
					$league_info['join_by_user'] 		= $this->request->variable('league_join_by_user', 0, true);
					$league_info['join_in_season'] 		= $this->request->variable('league_join_in_season', 0, true);
					$league_info['bet_in_time'] 		= $this->request->variable('league_bet_in_time', 0, true);
					$league_info['rules_post_id'] 		= $this->request->variable('league_rules_post_id', 0, true);
				}
				else 
				{
					trigger_error($user->lang['LEAGUE_NUMBER'] . adm_back_link($this->u_action . "&amp;s=$season"), E_USER_WARNING);
				}
				// No break for edit add
			case 'edit':

				$data = array();

				if (!sizeof($error))
				{
					if ($action == 'edit' && !$league)
					{
						trigger_error($user->lang['NO_LEAGUE'] . adm_back_link($this->u_action . "&amp;s=$season"), E_USER_WARNING);
					}

					// Did we submit?
					if ($update)
					{
						if (!check_form_key($form_key))
						{
							trigger_error($user->lang['FORM_INVALID'] . adm_back_link($this->u_action . "&amp;s=$season"), E_USER_WARNING);
						}
						
						$league_info['league_name'] 		= utf8_normalize_nfc($this->request->variable('league_name', '', true));
						$league_info['league_name_short'] 	= utf8_normalize_nfc($this->request->variable('league_short', '', true));
						$league_info['league_type'] 		= $this->request->variable('league_type', $league_info['league_type'], true);
						$league_info['bet_ko_type'] 		= $this->request->variable('bet_ko_type', $league_info['bet_ko_type'], true);
						$league_info['matchdays'] 			= $this->request->variable('league_matchdays', $league_info['matchdays'], true);
						$league_info['matches_on_matchday'] = $this->request->variable('league_matches', $league_info['matches_on_matchday'], true);
						$league_info['bet_points'] 			= round($this->request->variable('bet_points', $league_info['bet_points']),2);
						$league_info['win_result'] 			= $this->request->variable('league_win_hits', $league_info['win_result'], true);
						$league_info['win_result_02'] 		= $this->request->variable('league_win_hits_away', $league_info['win_result_02'], true);
						$league_info['win_matchday'] 		= $this->request->variable('league_win_matchdays', $league_info['win_matchday'], true);
						$league_info['win_season'] 			= $this->request->variable('league_win_season', $league_info['win_season'], true);
						$league_info['points_mode'] 		= $this->request->variable('league_points_mode', $league_info['points_mode'], true);
						$league_info['points_result'] 		= $this->request->variable('league_points_hit', $league_info['points_result'], true);
						$league_info['points_tendency'] 	= $this->request->variable('league_points_tendency', $league_info['points_tendency'], true);
						$league_info['points_diff'] 		= $this->request->variable('league_points_diff', $league_info['points_diff'], true);
						$league_info['points_last'] 		= $this->request->variable('league_points_last', $league_info['points_last'], true);
						$league_info['join_by_user'] 		= $this->request->variable('league_join_by_user', $league_info['join_by_user'], true);
						$league_info['join_in_season'] 		= $this->request->variable('league_join_in_season', $league_info['join_in_season'], true);
						$league_info['bet_in_time'] 		= $this->request->variable('league_bet_in_time', $league_info['bet_in_time'], true);
						$league_info['rules_post_id'] 		= $this->request->variable('league_rules_post_id', $league_info['rules_post_id'], true);

						if (!$league_info['rules_post_id'] and $league_info['join_by_user'] == 1)
						{
							$error[] = $user->lang['CHECK_RULES_POST_ID'];
						}

						if (!is_numeric($league_info['win_result']) or $league_info['win_result'] < 0)
						{
							$error[] = $user->lang['CHECK_HIT_WINS'];
						}

						if (!is_numeric($league_info['win_result_02']) or $league_info['win_result_02'] < 0)
						{
							$error[] = $user->lang['CHECK_HITS02_WINS'];
						}

						$matchday_wins = explode(';',$league_info['win_matchday']);
						foreach ($matchday_wins as $matchday_win)
						{
							if (!is_numeric($matchday_win) or $matchday_win < 0)
							{
								$error[] = $user->lang['CHECK_MATCHDAY_WINS'];
								break;
							}
						}

						$season_wins = explode(';',$league_info['win_season']);
						foreach ($season_wins as $season_win)
						{
							if (!is_numeric($season_win) or $season_win < 0)
							{
								$error[] = $user->lang['CHECK_SEASON_WINS'];
								break;
							}
						}

						if (!sizeof($error))
						{
							$sql_ary = array(
								'season'				=> (int) $season,
								'league'				=> (int) $league,
								'league_name'			=> $league_info['league_name'],
								'league_name_short'		=> $league_info['league_name_short'],
								'league_type'			=> $league_info['league_type'],
								'bet_ko_type'			=> $league_info['bet_ko_type'],
								'matchdays'				=> $league_info['matchdays'],
								'matches_on_matchday'	=> ($league_info['league_type'] == LEAGUE_KO) ? 0 : $league_info['matches_on_matchday'],
								'win_result'			=> $league_info['win_result'],
								'win_result_02'			=> $league_info['win_result_02'],
								'win_matchday'			=> $league_info['win_matchday'],
								'win_season'			=> $league_info['win_season'],
								'points_mode'			=> $league_info['points_mode'],
								'points_result'			=> (is_numeric($league_info['points_result'])) ? $league_info['points_result'] : 0,
								'points_tendency'		=> (is_numeric($league_info['points_tendency'])) ? $league_info['points_tendency'] : 0,
								'points_diff'			=> (is_numeric($league_info['points_diff'])) ? $league_info['points_diff'] : 0,
								'points_last'			=> $league_info['points_last'],
								'join_by_user'			=> $league_info['join_by_user'],
								'join_in_season'		=> $league_info['join_in_season'],
								'bet_in_time'			=> $league_info['bet_in_time'],
								'rules_post_id'			=> (is_numeric($league_info['rules_post_id'])) ? $league_info['rules_post_id'] : 0,
								'bet_points'			=> $league_info['bet_points'],
							);

							$data['league']				= $league;
							$data['league_name']		= $league_info['league_name'];
							$data['league_short']		= $league_info['league_name_short'];
							$data['league_matchdays']	= $league_info['matchdays'];
							$data['league_matches']		= ($league_info['league_type'] == LEAGUE_KO) ? 0 : $league_info['matches_on_matchday'];
							
							$var_ary = array(
								'league'			=> array('num', false, 1, 99),
								'league_name'		=> array('string', false, 2, 20),
								'league_short'		=> array('string', false, 1, 3),
								'league_matchdays'	=> array('num', false, 0, 99),
								'league_matches'	=> array('num', false, 0, 99),
							);
							if (!($error_vals = validate_data($data, $var_ary)))
							{
								if ($action == 'add')
								{
									$sql = 'INSERT INTO ' . FOOTB_LEAGUES . ' ' . $db->sql_build_array('INSERT', $sql_ary);
									$db->sql_query($sql);
								}
								else
								{
									$sql = 'UPDATE ' . FOOTB_LEAGUES . '
										SET ' . $db->sql_build_array('UPDATE', $sql_ary) . "
										WHERE season = $season 
											AND league = $league";
									$db->sql_query($sql);
								}
								if ($league_info['bet_in_time'])
								{
									set_bet_in_time_delivery($season, $league);
								}
								$message = ($action == 'edit') ? 'LEAGUE_UPDATED' : 'LEAGUE_CREATED';
								trigger_error($user->lang[$message] . adm_back_link($this->u_action . "&amp;s=$season"));
							}
							else
							{
								foreach ($error_vals as $error_val)
								{
										$error_msg[] = $user->lang[$error_val];
								}
								$message = ($action == 'edit') ? 'LEAGUE_UPDATE_FAILED' : 'LEAGUE_CREATE_FAILED';
								$error[] =  $user->lang[$message];
								$error = array_merge($error, $error_msg);
							}
						}
					}
				}
				$type_champ				= ($league_info['league_type'] == LEAGUE_CHAMP) ? ' checked="checked"' : '';
				$type_ko				= ($league_info['league_type'] == LEAGUE_KO) ? ' checked="checked"' : '';
				$bet_ko_90				= ($league_info['bet_ko_type'] == BET_KO_90) ? ' checked="checked"' : '';
				$bet_ko_extratime		= ($league_info['bet_ko_type'] == BET_KO_EXTRATIME) ? ' checked="checked"' : '';
				$bet_ko_penalty			= ($league_info['bet_ko_type'] == BET_KO_PENALTY) ? ' checked="checked"' : '';
				$mode_options = '';
				for($i = 1; $i <= 6; $i++)
				{
					$selected = ($i == $league_info['points_mode']) ? ' selected="selected"' : '';
					$mode_options .= '<option value="' . $i . '"' . $selected . '>' . $i . '</option>';
				}

				// check if matches created
				$existing_matches_on_league = count_existing_matches($season, $league, 0);
				
				$u_back = $this->u_action . "&amp;s=$season";

				$template->assign_vars(array(
					'S_EDIT'				=> true,
					'S_ADD_LEAGUE'			=> ($action == 'add') ? true : false,
					'S_ERROR'				=> (sizeof($error)) ? true : false,
					'S_EDIT_MATCHES'		=> ($existing_matches_on_league) ? false : true,
					'ERROR_MSG'				=> (sizeof($error)) ? implode('<br />', $error) : '',
					'SEASON'				=> $season,
					'BET_IN_TIME_YES' 		=> ($league_info['bet_in_time'] == 1) ? ' checked="checked"' : '',
					'BET_IN_TIME_NO' 		=> ($league_info['bet_in_time'] == 0) ? ' checked="checked"' : '',
					'BET_TYPE_KO_90'		=> BET_KO_90,
					'BET_TYPE_KO_EXTRATIME'	=> BET_KO_EXTRATIME,
					'BET_TYPE_KO_PENALTY'	=> BET_KO_PENALTY,
					'BET_KO_90'				=> $bet_ko_90,
					'BET_KO_EXTRATIME'		=> $bet_ko_extratime,
					'BET_KO_PENALTY'		=> $bet_ko_penalty,
					'JOIN_BY_USER_YES' 		=> ($league_info['join_by_user'] == 1) ? ' checked="checked"' : '',
					'JOIN_BY_USER_NO' 		=> ($league_info['join_by_user'] == 0) ? ' checked="checked"' : '',
					'JOIN_IN_SEASON_YES' 	=> ($league_info['join_in_season'] == 1) ? ' checked="checked"' : '',
					'JOIN_IN_SEASON_NO' 	=> ($league_info['join_in_season'] == 0) ? ' checked="checked"' : '',
					'LEAGUE'				=> $league,
					'LEAGUE_NAME'			=> $league_info['league_name'],
					'LEAGUE_SHORT'			=> $league_info['league_name_short'],
					'LEAGUE_TYPE_CHAMP'		=> LEAGUE_CHAMP,
					'LEAGUE_TYPE_KO'		=> LEAGUE_KO,
					'LEAGUE_CHAMP'			=> $type_champ,
					'LEAGUE_KO'				=> $type_ko,
					'LEAGUE_MATCHDAYS'		=> $league_info['matchdays'],
					'LEAGUE_MATCHES'		=> $league_info['matches_on_matchday'],
					'LEAGUE_POINTS_MODE_OPTIONS' => $mode_options,
					'LEAGUE_POINTS_HIT'		=> $league_info['points_result'],
					'LEAGUE_POINTS_TENDENCY'=> $league_info['points_tendency'],
					'LEAGUE_POINTS_DIFF'	=> $league_info['points_diff'],
					'LEAGUE_RULES_POST_ID'	=> $league_info['rules_post_id'],
					'BET_POINTS'			=> $league_info['bet_points'],
					'LEAGUE_WIN_HITS'		=> $league_info['win_result'],
					'LEAGUE_WIN_HITS_AWAY'	=> $league_info['win_result_02'],
					'LEAGUE_WIN_MATCHDAYS'	=> $league_info['win_matchday'],
					'LEAGUE_WIN_SEASON'		=> $league_info['win_season'],
					'POINTS_LAST_YES' 		=> ($league_info['points_last'] == 1) ? ' checked="checked"' : '',
					'POINTS_LAST_NO' 		=> ($league_info['points_last'] == 0) ? ' checked="checked"' : '',
					'U_BACK'				=> $u_back,
					'U_ACTION'				=> "{$this->u_action}&amp;action=$action&amp;s=$season",
					)
				);
				return;
			break;
		}

		$template->assign_vars(array(
			'U_ACTION'			=> $this->u_action,
			'U_FOOTBALL' 		=> $helper->route('football_main_controller',array('side' => 'bet', 's' => $season)),
			'S_SEASON'			=> $season,
			'S_SEASON_OPTIONS'	=> $season_options,
			'S_LEAGUE_ADD'		=> true,
			'S_VERSION_NO'		=> $this->config['football_version'],
			) 
		);
		
		// Get us all the leagues
		$sql = 'SELECT 
				l.season,
				l.league,
				l.league_name,
				l.league_name_short,
			COUNT(DISTINCT b.user_id) AS members
			FROM ' . FOOTB_LEAGUES . ' AS l
			LEFT JOIN ' . FOOTB_BETS . " AS b ON (b.season = l.season AND b.league = l.league)
			WHERE l.season = $season
			GROUP BY league
			ORDER BY league ASC";
		$result = $db->sql_query($sql);
		$rows_leagues = $db->sql_fetchrowset($result);
		$db->sql_freeresult($result);

		// Check if the user is allowed to delete a league.
		if ($user->data['user_type'] != USER_FOUNDER && $this->config['football_founder_delete'])
		{
			$allow_delete = false;
		}
		else
		{
			$allow_delete = true;
		}

		$row_number = 0;
		foreach ($rows_leagues as $row_league)
		{
			// check if matches created
			$existing_matches_on_league = count_existing_matches($row_league['season'], $row_league['league'], 0);

			$row_number++;
			$row_class = (!($row_number % 2)) ? 'bg1 row_light' : 'bg2 row_dark';
			$template->assign_block_vars('leagues', array(
				'ROW_CLASS'		=> $row_class,
				'SEASON'		=> $row_league['season'],
				'LEAGUE'		=> $row_league['league'],
				'LEAGUE_NAME'	=> $row_league['league_name'],
				'LEAGUE_SHORT'	=> $row_league['league_name_short'],
				'MEMBERS'		=> $row_league['members'],
				'S_MEMBER'		=> ($existing_matches_on_league) ? true : false,
				'U_LIST'		=> "{$this->u_action}&amp;action=list&amp;s=" . $season . "&amp;l=" .$row_league['league'],
				'U_EDIT'		=> "{$this->u_action}&amp;action=edit&amp;s=" . $season . "&amp;l=" .$row_league['league'],
				'U_DELETE'		=> ($allow_delete) ? "{$this->u_action}&amp;action=delete&amp;s=" . $season . "&amp;l=" . $row_league['league'] : '',
				)
			);
		}
	}
}
?>