<?php
/**
*
* @package phpBB Extension - Football Football
* @copyright (c) 2016 football (http://football.bplaced.net)
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace football\football\acp;

class bank_module
{
	public $u_action;

	protected $db, $user, $template, $config, $phpbb_root_path, $phpbb_admin_path, $phpEx;
	protected $root_path, $request, $php_ext, $log;


	public function __construct()
	{
		global $db, $user, $request, $template, $phpbb_container;
		global $config, $phpbb_root_path, $phpbb_admin_path, $phpEx;

		$user->add_lang_ext('football/football', 'football');
		$user->add_lang_ext('football/football', 'info_acp_bank');

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
		global $db, $auth, $phpbb_container, $phpbb_admin_path, $league_info, $functions_points;
		global $template, $user, $config, $phpbb_extension_manager, $request, $phpbb_root_path, $phpEx;
		
		$helper = $phpbb_container->get('controller.helper');
		
		if (!$this->config['football_bank'])
		{
			trigger_error($user->lang['FOOTBALL_BANK_OFF'], E_USER_WARNING);
		}

		if ($phpbb_extension_manager->is_enabled('dmzx/ultimatepoints'))
		{
			$user->add_lang_ext('dmzx/ultimatepoints', 'common');
			// Get an instance of the ultimatepoints functions_points
			$functions_points = $phpbb_container->get('dmzx.ultimatepoints.core.functions.points');
		}
		else
		{
			// Get an instance of the football functions_points
			$functions_points = $phpbb_container->get('football.football.core.functions.points');
		}

		$this->tpl_name = 'acp_football_bank';
		$this->page_title = 'ACP_FOOTBALL_BANK_MANAGE';

		$form_key = 'acp_football_bank';
		add_form_key($form_key);

		include($this->phpbb_root_path . 'includes/functions_user.' . $this->php_ext);

		// Check and set some common vars
		$action		= (isset($_POST['add'])) ? 'add' : ((isset($_POST['addmembers'])) ? 'addmembers' : $this->request->variable('action', ''));
		$edit		= $this->request->variable('edit', 0);
		$season		= $this->request->variable('s', 0);
		$league		= $this->request->variable('l', 0);
		$matchday	= $this->request->variable('m', 0);
		$type		= $this->request->variable('t', 0);
		$start		= $this->request->variable('start', 0);

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
			case 'bet':
			case 'deposit':
			case 'delete_wins':
			case 'pay':
				switch ($action)
				{
					case 'bet':
						$type = POINTS_BET;
						$default_matchday = 1;
						$select_win = 'l.bet_points - SUM(IF(p.points_type = ' . POINTS_BET . ', p.points, 0.00)) AS win';
						$points_var = 'BET';
						break;
					case 'deposit':
						$type = POINTS_DEPOSITED;
						$default_matchday = 1;
						$select_win = 'IF(SUM(IF(p.points_type IN (' . POINTS_BET . ',' . POINTS_PAID . ' ), p.points, p.points * -1.0)) > 0,
											SUM(IF(p.points_type IN (' . POINTS_BET . ',' . POINTS_PAID . ' ), p.points, p.points * -1.0)), 0.00) AS win';
						$points_var = 'DEPOSIT';
						break;
					case 'delete_wins':
						$default_matchday = 0;
						$points_var = 'DELETE_WIN';
						break;
					case 'pay':
						$type = POINTS_PAID;
						$default_matchday = 1;
						$select_win = 'IF(SUM(IF(p.points_type IN (' . POINTS_BET . ',' . POINTS_PAID . ' ), p.points * -1.0, p.points)) > 0,
											SUM(IF(p.points_type IN (' . POINTS_BET . ',' . POINTS_PAID . ' ), p.points * -1.0, p.points)), 0.00) AS win';
						$points_var = 'PAY';
						break;
				}	

				$mark_ary	= $this->request->variable('markleague', array(0));
				$cash = $this->request->variable('cash', false);
				
				if (sizeof($mark_ary) == 0)
				{
					trigger_error($user->lang['NO_LEAGUES_SELECTED'] . adm_back_link($this->u_action), E_USER_WARNING);
				}
				if (confirm_box(true))
				{
					$count_updates = 0;
					for ($i = 0; $i < sizeof($mark_ary); $i++) 
					{
						$league = $mark_ary[$i];
						if ($action == 'delete_wins')
						{
							rollback_points(POINTS_MATCHDAY, $season, $league, 0, $cash);
							rollback_points(POINTS_SEASON, $season, $league, 0, $cash);
							rollback_points(POINTS_MOST_HITS, $season, $league, 0, $cash);
							rollback_points(POINTS_MOST_HITS_AWAY, $season, $league, 0, $cash);
							$sql = 'DELETE FROM ' . FOOTB_POINTS . " 
									WHERE season = $season 
									AND league = $league 
									AND points_type IN (" . POINTS_MATCHDAY . ',' . POINTS_SEASON . ',' . POINTS_MOST_HITS . ',' . POINTS_MOST_HITS_AWAY . ')';
							$result = $db->sql_query($sql);
							$count_updates += $db->sql_affectedrows();
							$db->sql_freeresult($result);
						}
						else
						{
							$sql = "SELECT b.user_id,
										$select_win
									FROM " . FOOTB_BETS . ' AS b  
									JOIN ' . FOOTB_LEAGUES . " AS l ON (l.season = $season AND l.league = $league )
									LEFT JOIN " . FOOTB_POINTS . " AS p ON (p.season = $season AND p.league = $league  AND p.user_id = b.user_id)
									WHERE b.season = $season 
										AND b.league = $league  
										AND b.match_no = 1
									GROUP BY b.season, b.league, b.user_id
									HAVING win > 0";
							$result = $db->sql_query($sql);
							$points_ary = $db->sql_fetchrowset($result);
							$db->sql_freeresult($result);
							if (!$default_matchday)
							{
								$matchday = (curr_matchday($season, $league) > 0) ? curr_matchday($season, $league) : 1;
							}
							else
							{
								$matchday = $default_matchday;
							}
							if (sizeof($points_ary) > 0)
							{
								$count_updates += sizeof($points_ary);
								set_footb_points($type, $season, $league, $matchday, $points_ary, $cash);
							}
						}
					}
					$back_link =  $this->u_action . '&amp;s=' . $season;
					trigger_error(sprintf($user->lang['LEAGUE_' . $points_var . ($count_updates == 1 ? '' : 'S')], $count_updates) .adm_back_link($back_link));
				}
				else
				{
					confirm_box(false, sprintf($user->lang['CONFIRM_LEAGUE_' . $points_var]), build_hidden_fields(array(
						'markleague'=> $mark_ary,
						'cash'		=> $cash,
						's'			=> $season,
						'i'			=> $id,
						'mode'		=> $mode,
						'action'	=> $action))
					);
				}
			break;
			case 'book':
				switch ($type)
				{
					case POINTS_BET:
						$points_var = 'BET';
						break;
					case POINTS_DEPOSITED:
						$points_var = 'DEPOSIT';
						break;
					case POINTS_PAID:
						$points_var = 'PAY';
						break;
				}	

				$mark_ary	= $this->request->variable('mark', array(0));
				
				if (sizeof($mark_ary) == 0)
				{
					trigger_error($user->lang['NO_MEMBERS_SELECTED'] . adm_back_link($this->u_action), E_USER_WARNING);
				}
				$cash	= $this->request->variable('cash', false);
				$newvalue_ary	= $this->request->variable('newvalue', array(0.00));
				if (sizeof($newvalue_ary) == 0)
				{
					for ($i = 0; $i < sizeof($mark_ary); $i++) 
					{
						$newvalue_ary[]	=  1.00 * str_replace (",", ".", $this->request->variable('newvalue' . $mark_ary[$i], '0.00'));
					}
				}
				if (confirm_box(true))
				{
					for ($i = 0; $i < sizeof($mark_ary); $i++) 
					{
						if ($newvalue_ary[$i] <> 0)
						{
							$points_ary[] = array('user_id' => $mark_ary[$i], 'win' => $newvalue_ary[$i]);
						}
					}
					$back_link =  $this->u_action . '&amp;action=list&amp;s=' . $season . '&amp;l=' . $league . '&amp;t=' . $type . '&amp;start=' . $start;
					if (sizeof($points_ary) > 0)
					{
						set_footb_points($type, $season, $league, $matchday, $points_ary, $cash);					
					}
					trigger_error(sprintf($user->lang['LEAGUE_' . $points_var . (sizeof($points_ary) == 1 ? '' : 'S')], sizeof($points_ary)) . adm_back_link($back_link));
					
				}
				else
				{
					confirm_box(false, sprintf($user->lang['CONFIRM_' . $points_var]), build_hidden_fields(array(
						'mark'		=> $mark_ary,
						'start'		=> $start,
						'cash'		=> $cash,
						'newvalue'	=> $newvalue_ary,
						's'			=> $season,
						'l'			=> $league,
						'm'			=> $matchday,
						't'			=> $type,
						'i'			=> $id,
						'mode'		=> $mode,
						'action'	=> $action))
					);
				}
			break;
			case 'cancel':
				switch ($type)
				{
					case POINTS_BET:
						$points_var .= 'CANCEL_BET';
						break;
					case POINTS_DEPOSITED:
						$points_var .= 'CANCEL_DEPOSIT';
						break;
					case POINTS_PAID:
						$points_var .= 'CANCEL_PAY';
						break;
				}	

				$mark_ary = $this->request->variable('mark', array(0.00));
				
				if (sizeof($mark_ary) == 0)
				{
					trigger_error($user->lang['NO_MEMBERS_SELECTED'] . adm_back_link($this->u_action), E_USER_WARNING);
				}
				$cash	= $this->request->variable('cash', false);
				if (confirm_box(true))
				{
					$count_updates = 0;
					for ($i = 0; $i < sizeof($mark_ary); $i++) 
					{
						$curr_user = $mark_ary[$i];
						if ($cash)
						{
							$sql = 'SELECT *
									FROM  ' . FOOTB_POINTS . " AS p
									WHERE season = $season 
										AND league = $league 
										AND user_id =  $curr_user
										AND points_type = $type
										AND cash = 1";
							
							$result = $db->sql_query($sql);
							while( $row = $db->sql_fetchrow($result))
							{
								if ($type == POINTS_BET OR $type == POINTS_PAID)
								{
									$functions_points->add_points($curr_user, round($row['points'],2));
								}
								if ($type == POINTS_DEPOSITED)
								{
									$functions_points->substract_points($curr_user, round($row['points'],2));
								}
							}
							$db->sql_freeresult($result);
						
						}
						$sql = 'DELETE FROM ' . FOOTB_POINTS . " 
								WHERE season = $season 
								AND league = $league 
								AND user_id =  $curr_user
								AND points_type = $type";
						$result = $db->sql_query($sql);
						$count_updates += $db->sql_affectedrows();
						$db->sql_freeresult($result);
					}
					$back_link =  $this->u_action . '&amp;action=list&amp;s=' . $season . '&amp;l=' . $league . '&amp;t=' . $type . '&amp;start=' . $start;
					trigger_error(sprintf($user->lang['LEAGUE_' . $points_var . ($count_updates == 1 ? '' : 'S')], $count_updates) . adm_back_link($back_link));
				}
				else
				{
					confirm_box(false, sprintf($user->lang['CONFIRM_' . $points_var]), build_hidden_fields(array(
						'mark'		=> $mark_ary,
						'start'		=> $start,
						'cash'		=> $cash,
						's'			=> $season,
						'l'			=> $league,
						't'			=> $type,
						'i'			=> $id,
						'mode'		=> $mode,
						'action'	=> $action))
					);
				}
			break;
			case 'carryover':
				
				if ($type <> POINTS_PAID)
				{
					trigger_error($user->lang['NO_VALID_CALL'] . adm_back_link($this->u_action), E_USER_WARNING);
				}
				$points_var .= 'CARRYOVER_PAY';
				$bet_points = $league_info['bet_points'];
				$league_info_next = league_info($season + 1, $league);
				if (sizeof($league_info_next) > 0)
				{
					$bet_points = $league_info_next['bet_points'];
				}

				$mark_ary = $this->request->variable('mark', array(0));
				
				if (sizeof($mark_ary) == 0)
				{
					trigger_error($user->lang['NO_MEMBERS_SELECTED'] . adm_back_link($this->u_action), E_USER_WARNING);
				}
				$newvalue_ary	= $this->request->variable('newvalue', array(0.00));
				if (sizeof($newvalue_ary) == 0)
				{
					for ($i = 0; $i < sizeof($mark_ary); $i++) 
					{
						$newvalue_ary[]	=  1.00 * str_replace (",", ".", $this->request->variable('newvalue' . $mark_ary[$i], '0.00'));
					}
				}
				if (confirm_box(true))
				{
					$count_updates = 0;
					for ($i = 0; $i < sizeof($mark_ary); $i++) 
					{
						$curr_user = $mark_ary[$i];
						$carryover = ($newvalue_ary[$i] >= $bet_points) ? $bet_points : $newvalue_ary[$i];
						if ($carryover > 0)
						{
							// Payout old season
							$points_comment = sprintf($user->lang['CARRYOVER_NEW_SEASON']);
							$sql_ary = array(
								'season'		=> (int) $season,
								'league'		=> (int) $league,
								'matchday'		=> (int) $matchday,
								'points_type'	=> POINTS_PAID,
								'user_id'		=> (int) $curr_user,
								'points'		=> round($carryover,2),
								'points_comment'=> $points_comment,
								'cash'			=> 1,
							);
							$sql = 'INSERT INTO ' . FOOTB_POINTS . ' ' . $db->sql_build_array('INSERT', $sql_ary);
							$db->sql_query($sql);
							
							// Deposit new season
							$points_comment = sprintf($user->lang['CARRYOVER_OLD_SEASON']);
							$sql_ary = array(
								'season'		=> (int) $season + 1,
								'league'		=> (int) $league,
								'matchday'		=> 1,
								'points_type'	=> POINTS_DEPOSITED,
								'user_id'		=> (int) $curr_user,
								'points'		=> round($carryover,2),
								'points_comment'=> $points_comment,
								'cash'			=> 1,
							);
							$sql = 'INSERT INTO ' . FOOTB_POINTS . ' ' . $db->sql_build_array('INSERT', $sql_ary);
							$db->sql_query($sql);
							$count_updates++;
						}
					}
					$back_link =  $this->u_action . '&amp;action=list&amp;s=' . $season . '&amp;l=' . $league . '&amp;t=' . $type . '&amp;start=' . $start;
					trigger_error(sprintf($user->lang['LEAGUE_' . $points_var . ($count_updates == 1 ? '' : 'S')], $count_updates) . adm_back_link($back_link));
				}
				else
				{
					confirm_box(false, sprintf($user->lang['CONFIRM_' . $points_var]), build_hidden_fields(array(
						'mark'		=> $mark_ary,
						'start'		=> $start,
						'newvalue'	=> $newvalue_ary,
						's'			=> $season,
						'l'			=> $league,
						'm'			=> $matchday,
						't'			=> $type,
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
				
				$bet_points = $league_info['bet_points'];
				

				switch ($type)
				{
					case POINTS_BET:
						$page_type = sprintf($user->lang['DEBIT_BET']);
						$page_type_explain = sprintf($user->lang['DEBIT_BET_EXPLAIN']);
						$this->page_title = 'BET_POINTS';
						$target = sprintf($user->lang['BET_POINTS']);
						$actual = sprintf($user->lang['BOOKED']);
						$sum_target = "$bet_points AS target,";
						$sum_actual = 'SUM(IF(p.points_type = ' . POINTS_BET . ', p.points, 0.00)) AS actual,';
						$new_value = $bet_points . ' - SUM(IF(p.points_type = ' . POINTS_BET . ', p.points, 0.00)) AS new_value';
						$options = array('book' => 'BET', 'cancel' => 'CANCEL_BET');
						break;
					case POINTS_DEPOSITED:
						$page_type = sprintf($user->lang['BET_DEPOSIT']);
						$page_type_explain = sprintf($user->lang['BET_DEPOSIT_EXPLAIN']);
						$this->page_title = 'DEPOSITED';
						$target = sprintf($user->lang['BET_POINTS']);
						$actual = sprintf($user->lang['DEPOSITED']);
						$sum_target = "$bet_points AS target,";
						$sum_actual = 'SUM(IF(p.points_type = ' . POINTS_DEPOSITED . ', p.points, 0.00)) AS actual,';
						$new_value = 'IF(SUM(IF(p.points_type IN (' . POINTS_BET . ',' . POINTS_PAID . ' ), p.points, p.points * -1.0)) > 0,
											SUM(IF(p.points_type IN (' . POINTS_BET . ',' . POINTS_PAID . ' ), p.points, p.points * -1.0)), 0.00) AS new_value';
						$options = array('book' => 'DEPOSITED', 'cancel' => 'CANCEL_DEPOSITED');
						break;
					case POINTS_PAID:
						$page_type = sprintf($user->lang['PAY_WINS']);
						$page_type_explain = sprintf($user->lang['PAY_WINS_EXPLAIN']);
						$this->page_title = 'PAID';
						$target = sprintf($user->lang['WINS']);
						$actual = sprintf($user->lang['PAID']);
						$sum_target = 'SUM(IF(p.points_type IN (' . 
							POINTS_MATCHDAY . ',' . POINTS_SEASON . ',' . POINTS_MOST_HITS . ',' . POINTS_MOST_HITS_AWAY . 
							'), p.points, 0.00)) AS target,';
						$sum_actual = 'SUM(IF(p.points_type = ' . POINTS_PAID . ', p.points, 0.00)) AS actual,';
						$new_value = 'IF(SUM(IF(p.points_type IN (' . POINTS_BET . ',' . POINTS_PAID . ' ), p.points * -1.0, p.points)) > 0,
											SUM(IF(p.points_type IN (' . POINTS_BET . ',' . POINTS_PAID . ' ), p.points * -1.0, p.points)), 0.00) AS new_value';
						$options = array('book' => 'PAID', 'cancel' => 'CANCEL_PAID', 'carryover' => 'CARRYOVER_PAID');
						break;
				}	

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

				foreach ($options as $option => $lang)
				{
					$s_action_options .= '<option value="' . $option . '">' . $user->lang['MEMBER_' . $lang] . '</option>';
				}
				$curr_matchday = (curr_matchday($season, $league) > 0) ? curr_matchday($season, $league) : 1;
				$matchday = ($type == 1) ? 1 : $curr_matchday;

				$base_url = $this->u_action . "&amp;action=$action&amp;s=$season&amp;l=$league&amp;m=$matchday&amp;t=$type";
				$pagination = $phpbb_container->get('pagination');
				$pagination->generate_template_pagination($base_url, 'pagination', 'start', $total_members, $this->config['football_users_per_page'], $start);

				$template->assign_vars(array(
					'S_LIST'			=> true,
					'S_SEASON'			=> $season,
					'S_LEAGUE'			=> $league,
					'S_MATCHDAY'		=> $matchday,
					'S_START'			=> $start,
					'S_SELECT_MATCHDAY'	=> ($type == POINTS_BET) ? false : true,
					'S_TYPE'			=> $type,
					'S_ACTION_OPTIONS'	=> $s_action_options,
					'S_CASH_POINTS'		=> ($phpbb_extension_manager->is_enabled('dmzx/ultimatepoints')) ? true : false,
					'S_VERSION_NO'		=> $this->config['football_version'],
					'TOTAL_MEMBERS'		=> ($total_members == 1) ? $user->lang['VIEW_BET_USER'] : sprintf($user->lang['VIEW_BET_USERS'], $total_members),
					'PAGE_NUMBER' 		=> $pagination->on_page($total_members, $this->config['football_users_per_page'], $start),
					'LEAGUE_NAME'		=> $league_info['league_name']. ' ' . $season_name,
					'PAGE_TYPE'			=> $page_type,
					'PAGE_TYPE_EXPLAIN'	=> $page_type_explain,
					'BET_POINTS'		=> $bet_points,
					'POINTS'			=> $this->config['points_name'],
					'TARGET'			=> $target,
					'ACTUAL'			=> $actual,
					'U_FOOTBALL' 		=> $helper->route('football_football_controller',array('side' => 'bank', 's' => $season, 'l' => $league)),
					'U_ACTION'			=> $this->u_action . "&amp;s=$season&amp;l=$league",
					'U_BACK'			=> $this->u_action. "&amp;s=$season&amp;l=$league",
					)
				);
				
				$user_points = '';
				if ($phpbb_extension_manager->is_enabled('dmzx/ultimatepoints') && $this->config['points_enable'])
				{
					$user_points = 'u.user_points,';
				}
				else
				{
					$user_points = "0,00 AS user_points,";
				}

				// Grab the members
				$sql = "SELECT 
						b.user_id,
						u.username,  
						$user_points
						$sum_target 
						$sum_actual
						$new_value
					FROM " . FOOTB_BETS . ' AS b
					JOIN ' . USERS_TABLE . ' AS u ON (u.user_id = b.user_id)
					LEFT JOIN ' . FOOTB_POINTS . " AS p ON (p.season = $season AND p.league = $league AND p.user_id = b.user_id)
					WHERE b.season = $season
						AND b.league = $league
						AND b.match_no = 1
					GROUP BY b.user_id
					ORDER BY u.username ASC";
				$result = $db->sql_query_limit($sql, $this->config['football_users_per_page'], $start);

				while ($row = $db->sql_fetchrow($result))
				{
					$template->assign_block_vars('member', array(
						'U_USER_EDIT'	=> append_sid("{$phpbb_admin_path}index.$phpEx", "i=users&amp;action=edit&amp;u={$row['user_id']}"),
						'U_USER_BANK' 	=> $helper->route('football_football_controller',array('side' => 'bank', 's' => $season, 'l' => $league, 'u' => $row['user_id'])),
						'USERNAME'		=> $row['username'],
						'POINTS'		=> $functions_points->number_format_points($row['user_points']),
						'TARGET'		=> $functions_points->number_format_points($row['target']),
						'ACTUAL'		=> $functions_points->number_format_points($row['actual']),
						'NEW_VALUE'		=> $functions_points->number_format_points($row['new_value']),
						'USER_ID'		=> $row['user_id'],
						)
					);
				}
				$db->sql_freeresult($result);

				return;
			break;
		}

		$options = array('bet' => 'BET', 'deposit' => 'DEPOSITED', 'delete_wins' => 'DELETE_WINS', 'pay' => 'PAID');
		$s_action_options = '';

		foreach ($options as $option => $lang)
		{
			$s_action_options .= '<option value="' . $option . '">' . $user->lang['MEMBER_' . $lang] . '</option>';
		}
		
		$template->assign_vars(array(
			'U_ACTION'					=> $this->u_action,
			'U_FOOTBALL' 				=> $helper->route('football_football_controller',array('side' => 'bank', 's' => $season)),
			'U_DLOAD_BANK_OPEN' 		=> $helper->route('football_football_download',array('downside' => 'dload_bank_open', 's' => $season)),
			'S_SEASON'					=> $season,
			'S_LIST_DEPOSITED'			=> ($this->config['football_ult_points'] == UP_POINTS) ? false : true,
			'S_LIST_PAID'				=> ($this->config['football_ult_points'] == UP_POINTS) ? false : true,
			'S_SEASON_OPTIONS'			=> $season_options,
			'S_LEAGUE_ACTION_OPTIONS'	=> $s_action_options,
			'S_CASH_POINTS'				=> ($phpbb_extension_manager->is_enabled('dmzx/ultimatepoints')) ? true : false,
			'S_VERSION_NO'				=> $this->config['football_version'],
			) 
		);
		
		// Get us all the league banks
		$sql = 'SELECT 
				l.league,
				l.league_name,
				SUM(IF(p.points_type = ' . POINTS_BET . ', p.points, 0.00)) AS bet_points,
				SUM(IF(p.points_type = ' . POINTS_DEPOSITED . ', p.points, 0.00)) AS deposited,
				SUM(IF(p.points_type IN (' . 
							POINTS_MATCHDAY . ',' . POINTS_SEASON . ',' . POINTS_MOST_HITS . ',' . POINTS_MOST_HITS_AWAY . 
							'), p.points, 0.00)) AS wins,
				SUM(IF(p.points_type = ' . POINTS_PAID . ', p.points, 0.00)) AS paid
			FROM ' . FOOTB_LEAGUES . ' AS l
			LEFT JOIN ' . FOOTB_POINTS . " AS p ON (p.season = $season AND p.league = l.league)
			WHERE l.season = $season
			GROUP BY l.league
			ORDER BY l.league ASC";
		$result = $db->sql_query($sql);
		$rows_leagues = $db->sql_fetchrowset($result);
		$db->sql_freeresult($result);

		$row_number = 0;
		foreach ($rows_leagues as $row_league)
		{
			$template->assign_block_vars('leagues', array(
				'LEAGUE'			=> $row_league['league'],
				'LEAGUE_NAME'		=> $row_league['league_name'],
				'BET_POINTS'		=> $functions_points->number_format_points($row_league['bet_points']),
				'DEPOSITED'			=> $functions_points->number_format_points($row_league['deposited']),
				'WINS'				=> $functions_points->number_format_points($row_league['wins']),
				'PAID'				=> $functions_points->number_format_points($row_league['paid']),
				'U_LIST_BET_POINTS'	=> "{$this->u_action}&amp;action=list&amp;s=" . $season . "&amp;l=" .$row_league['league'] . "&amp;t=1",
				'U_LIST_DEPOSITED'	=> "{$this->u_action}&amp;action=list&amp;s=" . $season . "&amp;l=" .$row_league['league'] . "&amp;t=2",
				'U_LIST_WINS'		=> "{$this->u_action}&amp;action=list&amp;s=" . $season . "&amp;l=" .$row_league['league'] . "&amp;t=3",
				'U_LIST_PAID'		=> "{$this->u_action}&amp;action=list&amp;s=" . $season . "&amp;l=" .$row_league['league'] . "&amp;t=7",
				'U_DLOAD_BANK' 		=> $helper->route('football_football_download', array('downside' => 'dload_bank', 's' => $season, 'l' => $row_league['league'])),
				)
			);
		}
	}
}
