<?php
/**
*
* @package phpBB Extension - Football Football
* @copyright (c) 2016 football (http://football.bplaced.net)
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace football\football\acp;

class matchdays_module
{
	public $u_action;

	protected $db, $user, $template, $config, $phpbb_root_path, $phpbb_admin_path, $phpEx;
	protected $root_path, $request, $php_ext, $log;


	public function __construct()
	{
		global $db, $user, $request, $template;
		global $config, $phpbb_root_path, $phpbb_admin_path, $phpEx;

		$user->add_lang_ext('football/football', 'football');
		$user->add_lang_ext('football/football', 'info_acp_matchdays');

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
		
		$this->tpl_name = 'acp_football_matchdays';
		$this->page_title = 'ACP_FOOTBALL_MATCHDAYS_MANAGE';

		$form_key = 'acp_football_matchdays';
		add_form_key($form_key);

		include_once($this->phpbb_root_path . 'includes/functions_user.' . $this->php_ext);

		// Check and set some common vars
		$action			= (isset($_POST['add'])) ? 'add' : '';
		$action			= (isset($_POST['remove'])) ? 'remove' : $action;
		$action			= (isset($_POST['change_delivery'])) ? 'change_delivery' : $action;
		$action			= (isset($_POST['show_delivery'])) ? 'show_delivery' : $action;
		$action			= (isset($_POST['update_delivery'])) ? 'update_delivery' : $action;
		$action 		= (empty($action)) ? $this->request->variable('action', '') : $action;

		$edit		= $this->request->variable('edit', 0);
		$season		= $this->request->variable('s', 0);
		$league		= $this->request->variable('l', 0);
		$matchday	= $this->request->variable('m', 0);
		$update				= (isset($_POST['update'])) ? true : false;
		$backward_days 		= $this->request->variable('backward_days', 0);
		$backward_hours 	= $this->request->variable('backward_hours', 0);
		$backward_minutes 	= $this->request->variable('backward_minutes', 0);

		// Clear some vars
		$matchday_row = array();
		$error = array();
		$show_delivery_select = true;
		$show_delivery = false;

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
					$edit_delivery = !$row['bet_in_time'];
					$ko_league = ($row['league_type'] == LEAGUE_KO) ? true : false;
				}
			}
			$db->sql_freeresult($result);
		}
		else
		{
			trigger_error(sprintf($user->lang['NO_LEAGUE'], $season) . adm_back_link($this->u_action . "&amp;s=$season"), E_USER_WARNING);
		}

		// Grab basic data for matchday, if matchday is set and exists
		if ($matchday)
		{
			$sql = 'SELECT *
				FROM ' . FOOTB_MATCHDAYS . "
				WHERE season = $season 
					AND league = $league 
					AND matchday = $matchday";
			$result = $db->sql_query($sql);
			$matchday_row = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);
		}
		
		// Which page?
		switch ($action)
		{
			case 'update_delivery':
				$show_delivery_select = false;
				$count_updates = 0;
				$sql = "SELECT 
						matchday
					FROM " . FOOTB_MATCHDAYS . " 
					WHERE season = $season 
						AND league = $league
					ORDER BY matchday ASC";
				$result = $db->sql_query($sql);
				$rows_matchdays = $db->sql_fetchrowset($result);
				$db->sql_freeresult($result);
				foreach ($rows_matchdays as $row_matchday)
				{
					$sql_ary = array();
					$matchday = $row_matchday['matchday'];
					if ($this->request->variable('delivery_' . $matchday . '_1', false))
					{
						$sql_ary['delivery_date'] = $this->request->variable('new_delivery_' . $matchday . '_1', '');
					}
					if ($this->request->variable('delivery_' . $matchday . '_2', ''))
					{
						$sql_ary['delivery_date_2'] = $this->request->variable('new_delivery_' . $matchday . '_2', '');
					}
					if ($this->request->variable('delivery_' . $matchday . '_3', ''))
					{
						$sql_ary['delivery_date_3'] = $this->request->variable('new_delivery_' . $matchday . '_3', '');
					}
					if ( sizeof($sql_ary) )
					{
						$count_updates = $count_updates + sizeof($sql_ary);
						$sql = 'UPDATE ' . FOOTB_MATCHDAYS . '
							SET ' . $db->sql_build_array('UPDATE', $sql_ary) . "
							WHERE season = $season 
								AND league = $league 
								AND matchday = $matchday";
						$db->sql_query($sql);
					}
				}
				if ($count_updates)
				{
					trigger_error(sprintf($user->lang['UPDATE_DELIVER' . (($count_updates == 1) ? '' : 'IES')], $count_updates) . adm_back_link($this->u_action . "&amp;s=$season&amp;l=$league"));
				}
				else
				{
					trigger_error($user->lang['NO_DELIVERIES_UPDATED'] . adm_back_link($this->u_action . "&amp;s=$season&amp;l=$league"));
				}
			break;
			case 'show_delivery':
				$show_delivery_select = false;
				$show_delivery = true;
				$lang_dates = $user->lang['datetime'];
				$sql = "(SELECT md.matchday AS matchday,
							1 AS number,
							md.delivery_date,
							CASE DATE_FORMAT(md.delivery_date,'%w')
								WHEN 0 THEN '" . $lang_dates['Sun'] . "'
								WHEN 1 THEN '" . $lang_dates['Mon'] . "'
								WHEN 2 THEN '" . $lang_dates['Tue'] . "'
								WHEN 3 THEN '" . $lang_dates['Wed'] . "'
								WHEN 4 THEN '" . $lang_dates['Thu'] . "'
								WHEN 5 THEN '" . $lang_dates['Fri'] . "'
								WHEN 6 THEN '" . $lang_dates['Sat'] . "'
								ELSE 'Error' 
							END AS delivery_date_day,
							DATE_FORMAT(DATE_SUB(agg.min_delivery_date, INTERVAL '" . $backward_days . ' ' .  $backward_hours . ':' . sprintf('%02d', $backward_minutes) . "' DAY_MINUTE),'%Y-%m-%d %H:%i:%s') AS new_delivery_date,
							CASE DATE_FORMAT(DATE_SUB(agg.min_delivery_date, INTERVAL '" . $backward_days . ' ' .  $backward_hours . ':' . sprintf('%02d', $backward_minutes) . "' DAY_MINUTE), '%w')
								WHEN 0 THEN '" . $lang_dates['Sun'] . "'
								WHEN 1 THEN '" . $lang_dates['Mon'] . "'
								WHEN 2 THEN '" . $lang_dates['Tue'] . "'
								WHEN 3 THEN '" . $lang_dates['Wed'] . "'
								WHEN 4 THEN '" . $lang_dates['Thu'] . "'
								WHEN 5 THEN '" . $lang_dates['Fri'] . "'
								WHEN 6 THEN '" . $lang_dates['Sat'] . "'
								ELSE 'Error' 
							END AS new_delivery_day
					FROM " . FOOTB_MATCHDAYS . " AS md 
					INNER JOIN (SELECT season, league, matchday, min(match_datetime) AS min_delivery_date 
								FROM " . FOOTB_MATCHES . "
								WHERE season = $season 
								AND league = $league 
								AND status = 0
								GROUP BY season, league, matchday) AS agg
					WHERE md.season = agg.season 
					AND md.league = agg.league 
					AND md.matchday = agg.matchday)
					UNION
					(SELECT md2.matchday AS matchday,
							2 AS number,
							md2.delivery_date_2 AS delivery_date,
							CASE DATE_FORMAT(md2.delivery_date_2,'%w')
								WHEN 0 THEN '" . $lang_dates['Sun'] . "'
								WHEN 1 THEN '" . $lang_dates['Mon'] . "'
								WHEN 2 THEN '" . $lang_dates['Tue'] . "'
								WHEN 3 THEN '" . $lang_dates['Wed'] . "'
								WHEN 4 THEN '" . $lang_dates['Thu'] . "'
								WHEN 5 THEN '" . $lang_dates['Fri'] . "'
								WHEN 6 THEN '" . $lang_dates['Sat'] . "'
								ELSE 'Error' 
							END AS delivery_date_day,
							DATE_FORMAT(DATE_SUB(agg2.min_delivery_date_2, INTERVAL '" . $backward_days . ' ' .  $backward_hours . ':' . sprintf('%02d', $backward_minutes) . "' DAY_MINUTE),'%Y-%m-%d %H:%i:%s') AS new_delivery_date,
							CASE DATE_FORMAT(DATE_SUB(agg2.min_delivery_date_2, INTERVAL '" . $backward_days . ' ' .  $backward_hours . ':' . sprintf('%02d', $backward_minutes) . "' DAY_MINUTE), '%w')
								WHEN 0 THEN '" . $lang_dates['Sun'] . "'
								WHEN 1 THEN '" . $lang_dates['Mon'] . "'
								WHEN 2 THEN '" . $lang_dates['Tue'] . "'
								WHEN 3 THEN '" . $lang_dates['Wed'] . "'
								WHEN 4 THEN '" . $lang_dates['Thu'] . "'
								WHEN 5 THEN '" . $lang_dates['Fri'] . "'
								WHEN 6 THEN '" . $lang_dates['Sat'] . "'
								ELSE 'Error' 
							END AS new_delivery_day
					FROM " . FOOTB_MATCHDAYS . " AS md2 
					INNER JOIN (SELECT season, league, matchday, min(match_datetime) AS min_delivery_date_2 
								FROM " . FOOTB_MATCHES . "
								WHERE season = $season 
								AND league = $league 
								AND status = -1
								GROUP BY season, league, matchday) AS agg2
					WHERE md2.season = agg2.season 
					AND md2.league = agg2.league 
					AND md2.matchday = agg2.matchday)
					UNION
					(SELECT md3.matchday AS matchday,
							3 AS number,
							md3.delivery_date_3 AS delivery_date,
							CASE DATE_FORMAT(md3.delivery_date_3,'%w')
								WHEN 0 THEN '" . $lang_dates['Sun'] . "'
								WHEN 1 THEN '" . $lang_dates['Mon'] . "'
								WHEN 2 THEN '" . $lang_dates['Tue'] . "'
								WHEN 3 THEN '" . $lang_dates['Wed'] . "'
								WHEN 4 THEN '" . $lang_dates['Thu'] . "'
								WHEN 5 THEN '" . $lang_dates['Fri'] . "'
								WHEN 6 THEN '" . $lang_dates['Sat'] . "'
								ELSE 'Error' 
							END AS delivery_date_day,
							DATE_FORMAT(DATE_SUB(agg3.min_delivery_date_3, INTERVAL '" . $backward_days . ' ' .  $backward_hours . ':' . sprintf('%02d', $backward_minutes) . "' DAY_MINUTE),'%Y-%m-%d %H:%i:%s') AS new_delivery_date,
							CASE DATE_FORMAT(DATE_SUB(agg3.min_delivery_date_3, INTERVAL '" . $backward_days . ' ' .  $backward_hours . ':' . sprintf('%02d', $backward_minutes) . "' DAY_MINUTE), '%w')
								WHEN 0 THEN '" . $lang_dates['Sun'] . "'
								WHEN 1 THEN '" . $lang_dates['Mon'] . "'
								WHEN 2 THEN '" . $lang_dates['Tue'] . "'
								WHEN 3 THEN '" . $lang_dates['Wed'] . "'
								WHEN 4 THEN '" . $lang_dates['Thu'] . "'
								WHEN 5 THEN '" . $lang_dates['Fri'] . "'
								WHEN 6 THEN '" . $lang_dates['Sat'] . "'
								ELSE 'Error' 
							END AS new_delivery_day
					FROM " . FOOTB_MATCHDAYS . " AS md3 
					INNER JOIN (SELECT season, league, matchday, min(match_datetime) AS min_delivery_date_3 
								FROM " . FOOTB_MATCHES . "
								WHERE season = $season 
								AND league = $league 
								AND status = -2
								GROUP BY season, league, matchday) AS agg3
					WHERE md3.season = agg3.season 
					AND md3.league = agg3.league 
					AND md3.matchday = agg3.matchday)
					ORDER BY matchday ASC, number ASC";
				$result = $db->sql_query($sql);
				$rows_matchdays = $db->sql_fetchrowset($result);
				$db->sql_freeresult($result);
				$row_number = 0;
				foreach ($rows_matchdays as $row_matchday)
				{
					$row_number++;
					$row_class = (!($row_number % 2)) ? 'bg1 row_light' : 'bg2 row_dark';
					$template->assign_block_vars('delivery', array(
						'ROW_CLASS'				=> $row_class,
						'MATCHDAY'				=> $row_matchday['matchday'],
						'NUMBER'				=> $row_matchday['number'],
						'DELIVERY_ERROR'		=> ($row_matchday['delivery_date'] > $row_matchday['new_delivery_date']) ? true : false,
						'DELIVERY_DATE_DAY'		=> $row_matchday['delivery_date_day'],
						'DELIVERY_DATE'			=> $row_matchday['delivery_date'],
						'NEW_DELIVERY_DAY'		=> $row_matchday['new_delivery_day'],
						'NEW_DELIVERY'			=> $row_matchday['new_delivery_date'],
						)
					);
				}
			case 'change_delivery':
				$edit_delivery = false;
				$change_delivery = true;
				$backwards_days_options = '';
				for ($i = 0; $i < 7; $i++)
				{
					$selected = ($i == $backward_days) ? ' selected="selected"' : '';
					$backwards_days_options .= "<option value=\"" . $i . "\"$selected>" . $i . "</option>";
				}
				$backwards_hours_options = '';
				for ($i = 0; $i < 24; $i++)
				{
					$selected = ($i == $backward_hours) ? ' selected="selected"' : '';
					$backwards_hours_options .= "<option value=\"" . $i . "\"$selected>" . $i . "</option>";
				}
				$backwards_minutes_options = '';
				for ($i = 0; $i < 60; $i = $i + 5)
				{
					$selected = ($i == $backward_minutes) ? ' selected="selected"' : '';
					$backwards_minutes_options .= "<option value=\"" . $i . "\"$selected>" . $i . "</option>";
				}
				
				$u_back = $this->u_action . "&amp;s=$season&amp;l=$league";

				$template->assign_vars(array(
					'S_EDIT'						=> false,
					'S_CHANGE_DELIVERY'				=> true,
					'S_SHOW_DELIVERY_SELECT'		=> $show_delivery_select,
					'S_SHOW_DELIVERY'				=> $show_delivery,
					'S_BACKWARD_DAYS_OPTIONS' 		=> $backwards_days_options,
					'S_BACKWARD_HOURS_OPTIONS' 		=> $backwards_hours_options,
					'S_BACKWARD_MINUTES_OPTIONS' 	=> $backwards_minutes_options,
					'S_VERSION_NO'					=> $this->config['football_version'],
					'SEASON'						=> $season,
					'SEASON_NAME'					=> $season_name,
					'LEAGUE'						=> $league,
					'LEAGUE_NAME'					=> $league_name,
					'U_BACK'						=> $u_back,
					'U_ACTION'						=> "{$this->u_action}&amp;action=$action&amp;s=$season&amp;l=$league",
					)
				);
				return;
			break;
			case 'delete':
				if (!$season)
				{
					trigger_error($user->lang['NO_SEASON'] . adm_back_link($this->u_action), E_USER_WARNING);
				}

				if (!$league)
				{
					trigger_error($user->lang['NO_LEAGUE'] . adm_back_link($this->u_action . "&amp;s=$season"), E_USER_WARNING);
				}

				if (!$matchday)
				{
					trigger_error($user->lang['NO_MATCHDAY'] . adm_back_link($this->u_action . "&amp;s=$season&amp;l=$league"), E_USER_WARNING);
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
						trigger_error($user->lang['MATCHDAYS_NO_DELETE'] . adm_back_link($this->u_action . "&amp;s=$season&amp;l=$league"), E_USER_WARNING);
					}

					// Delete matchday
					$sql = 'DELETE FROM ' . FOOTB_MATCHDAYS . "
							WHERE season = $season AND league = $league AND matchday = $matchday";
					$db->sql_query($sql);

					// Delete bets
					$sql = 'DELETE FROM ' . FOOTB_BETS . "
							WHERE  season = $season 
								AND league = $league 
								AND match_no IN 
								(SELECT DISTINCT 
									match_no 
								FROM " . FOOTB_MATCHES . "
								WHERE season = $season 
									AND league = $league 
									AND matchday = $matchday)";
					$db->sql_query($sql);

					// Delete matches
					$sql = 'DELETE FROM ' . FOOTB_MATCHES . "
							WHERE season = $season AND league = $league AND matchday = $matchday";
					$db->sql_query($sql);

					trigger_error($user->lang['MATCHDAY_DELETED'] . adm_back_link($this->u_action . "&amp;s=$season&amp;l=$league"));
				}
				else
				{
					confirm_box(false, sprintf($user->lang['MATCHDAY_CONFIRM_DELETE'], $matchday, $season), build_hidden_fields(array(
						's'			=> $season,
						'l'			=> $league,
						'm'			=> $matchday,
						'mode'		=> $mode,
						'action'	=> $action))
					);
				}
			break;
			case 'remove':
				if (!$season)
				{
					trigger_error($user->lang['NO_SEASON'] . adm_back_link($this->u_action), E_USER_WARNING);
				}

				if (!$league)
				{
					trigger_error($user->lang['NO_LEAGUE'] . adm_back_link($this->u_action . "&amp;s=$season"), E_USER_WARNING);
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
						trigger_error($user->lang['MATCHDAYS_NO_DELETE'] . adm_back_link($this->u_action . "&amp;s=$season&amp;l=$league"), E_USER_WARNING);
					}

					// Delete matchday
					$sql = 'DELETE FROM ' . FOOTB_MATCHDAYS . "
							WHERE season = $season AND league = $league AND matchday > $league_matchdays";
					$db->sql_query($sql);

					// Delete bets
					$sql = 'DELETE FROM ' . FOOTB_BETS . "
							WHERE  season = $season 
								AND league = $league 
								AND match_no IN 
								(SELECT DISTINCT 
									match_no 
								FROM " . FOOTB_MATCHES . "
								WHERE season = $season 
									AND league = $league 
									AND matchday > $league_matchdays)";
					$db->sql_query($sql);

					// Delete matches
					$sql = 'DELETE FROM ' . FOOTB_MATCHES . "
							WHERE season = $season AND league = $league AND matchday > $league_matchdays";
					$db->sql_query($sql);

					trigger_error($user->lang['MATCHDAYS_REMOVED'] . adm_back_link($this->u_action . "&amp;s=$season&amp;l=$league"));
				}
				else
				{
					confirm_box(false, sprintf($user->lang['MATCHDAY_CONFIRM_REMOVE'], $season), build_hidden_fields(array(
						's'			=> $season,
						'l'			=> $league,
						'm'			=> $matchday,
						'mode'		=> $mode,
						'action'	=> $action))
					);
				}
			break;

			case 'add':
				$sql = "SELECT 
						matchday
					FROM " . FOOTB_MATCHDAYS . " 
					WHERE season = $season 
						AND league = $league
					ORDER BY matchday ASC";
				$result = $db->sql_query($sql);
				$rows_matchdays = $db->sql_fetchrowset($result);
				$existing_matchdays = sizeof($rows_matchdays);
				$db->sql_freeresult($result);
				if ($existing_matchdays < $league_matchdays)
				{
					$last_matchday = 0;
					$count_updates = 0;
					if ($existing_matchdays > 0)
					{
						foreach ($rows_matchdays as $row_exist)
						{
							if ($last_matchday + 1 < $row_exist['matchday'])
							{
								for ( $i = $last_matchday + 1; $i < $row_exist['matchday']; $i++ )
								{
									$count_updates++;
									$sql_ary = array(
										'season'			=> (int) $season,
										'league'			=> (int) $league,
										'matchday'			=> (int) $i,
										'status'			=> 0,
										'delivery_date'		=> '',
										'delivery_date_2'	=> '',
										'delivery_date_3'	=> '',
										'matchday_name'		=> '',
										'matches'			=> 0,
									);
									$sql = 'INSERT INTO ' . FOOTB_MATCHDAYS . ' ' . $db->sql_build_array('INSERT', $sql_ary);
									$db->sql_query($sql);
								}
							}
							$last_matchday = $row_exist['matchday'];
						}
						for ( $i = $last_matchday + 1; $i <= $league_matchdays; $i++ )
						{
							$count_updates++;
							$sql_ary = array(
								'season'			=> (int) $season,
								'league'			=> (int) $league,
								'matchday'			=> (int) $i,
								'status'			=> 0,
								'delivery_date'		=> '',
								'delivery_date_2'	=> '',
								'delivery_date_3'	=> '',
								'matchday_name'		=> '',
								'matches'			=> 0,
							);

							$sql = 'INSERT INTO ' . FOOTB_MATCHDAYS . ' ' . $db->sql_build_array('INSERT', $sql_ary);
							$db->sql_query($sql);
						}
					}
					else
					{
						for ( $i = 1; $i <= $league_matchdays; $i++ )
						{
							$count_updates++;
							$sql_ary = array(
								'season'			=> (int) $season,
								'league'			=> (int) $league,
								'matchday'			=> (int) $i,
								'status'			=> 0,
								'delivery_date'		=> '',
								'delivery_date_2'	=> '',
								'delivery_date_3'	=> '',
								'matchday_name'		=> '',
								'matches'			=> 0,
							);

							$sql = 'INSERT INTO ' . FOOTB_MATCHDAYS . ' ' . $db->sql_build_array('INSERT', $sql_ary);
							$db->sql_query($sql);
						}
					}
					$message = ($count_updates > 1) ? 'MATCHDAYS_CREATED' : 'MATCHDAY_CREATED';
					trigger_error(sprintf($user->lang[$message],$count_updates) . adm_back_link($this->u_action . "&amp;s=$season&amp;l=$league"));
				}
				else 
				{
					trigger_error($user->lang['NO_MORE_MATCHDAYS'] . adm_back_link($this->u_action . "&amp;s=$season&amp;l=$league"));
				}
				// No break for edit add
			case 'edit':
				$data = array();
				$error_msg = array();

				if (!sizeof($error))
				{
					if ($action == 'edit' && !$matchday)
					{
						trigger_error($user->lang['NO_MATCHDAY'] . adm_back_link($this->u_action . "&amp;s=$season&amp;l=$league"), E_USER_WARNING);
					}
					$data = array(
						'dday1_day'		=> 0,
						'dday1_month'	=> 0,
						'dday1_year'	=> 0,
						'dday1_hour'	=> 0,
						'dday1_min'		=> 0,
						'dday2_day'		=> 0,
						'dday2_month'	=> 0,
						'dday2_year'	=> 0,
						'dday2_hour'	=> 0,
						'dday2_min'		=> 0,
						'dday3_day'		=> 0,
						'dday3_month'	=> 0,
						'dday3_year'	=> 0,
						'dday3_hour'	=> 0,
						'dday3_min'		=> 0,
					);
					if ($matchday_row['delivery_date'])
					{
						list($data['dday1_date'], $data['dday1_time']) = explode(' ', $matchday_row['delivery_date']);
						list($data['dday1_year'], $data['dday1_month'], $data['dday1_day']) = explode('-', $data['dday1_date']);
						list($data['dday1_hour'], $data['dday1_min'], $data['dday1_sec']) = explode(':', $data['dday1_time']);
					}

					$data['dday1_day']		= $this->request->variable('dday1_day', $data['dday1_day']);
					$data['dday1_month']	= $this->request->variable('dday1_month', $data['dday1_month']);
					$data['dday1_year']		= $this->request->variable('dday1_year', $data['dday1_year']);
					$data['dday1_hour']		= $this->request->variable('dday1_hour', $data['dday1_hour']);
					$data['dday1_min']		= $this->request->variable('dday1_min', $data['dday1_min']);
					$data['dday1_sec']		= '00';
					$data['dday1_date']		= sprintf('%02d-%02d-%04d', $data['dday1_day'], $data['dday1_month'], $data['dday1_year']); 
					$data['dday1_time']		= sprintf('%02d:%02d:%02d', $data['dday1_hour'], $data['dday1_min'], $data['dday1_sec']);
					$matchday_row['delivery_date'] = sprintf('%04d-%02d-%02d', $data['dday1_year'], $data['dday1_month'], $data['dday1_day']) . ' ' . $data['dday1_time'];

					if ($matchday_row['delivery_date_2'])
					{
						list($data['dday2_date'], $data['dday2_time']) = explode(' ', $matchday_row['delivery_date_2']);
						list($data['dday2_year'], $data['dday2_month'], $data['dday2_day']) = explode('-', $data['dday2_date']);
						list($data['dday2_hour'], $data['dday2_min'], $data['dday2_sec']) = explode(':', $data['dday2_time']);
					}

					$data['dday2_day']		= $this->request->variable('dday2_day', $data['dday2_day']);
					$data['dday2_month']	= $this->request->variable('dday2_month', $data['dday2_month']);
					$data['dday2_year']		= $this->request->variable('dday2_year', $data['dday2_year']);
					$data['dday2_hour']		= $this->request->variable('dday2_hour', $data['dday2_hour']);
					$data['dday2_min']		= $this->request->variable('dday2_min', $data['dday2_min']);
					$data['dday2_sec']		= '00';
					if (!$data['dday2_day'] and !$data['dday2_month'] and !$data['dday2_year'])
					{
						$matchday_row['delivery_date_2'] = '';
						$data['dday2_date'] = '01-01-1980';
					}
					else
					{
						$data['dday2_date']		= sprintf('%02d-%02d-%04d', $data['dday2_day'], $data['dday2_month'], $data['dday2_year']); 
						$data['dday2_time']		= sprintf('%02d:%02d:%02d', $data['dday2_hour'], $data['dday2_min'], $data['dday2_sec']);
						$matchday_row['delivery_date_2'] = sprintf('%04d-%02d-%02d', $data['dday2_year'], $data['dday2_month'], $data['dday2_day']) . 
							' ' . $data['dday2_time'];

					}
					
					if ($matchday_row['delivery_date_3'])
					{
						list($data['dday3_date'], $data['dday3_time']) = explode(' ', $matchday_row['delivery_date_3']);
						list($data['dday3_year'], $data['dday3_month'], $data['dday3_day']) = explode('-', $data['dday3_date']);
						list($data['dday3_hour'], $data['dday3_min'], $data['dday3_sec']) = explode(':', $data['dday3_time']);
					}

					$data['dday3_day']		= $this->request->variable('dday3_day', $data['dday3_day']);
					$data['dday3_month']	= $this->request->variable('dday3_month', $data['dday3_month']);
					$data['dday3_year']		= $this->request->variable('dday3_year', $data['dday3_year']);
					$data['dday3_hour']		= $this->request->variable('dday3_hour', $data['dday3_hour']);
					$data['dday3_min']		= $this->request->variable('dday3_min', $data['dday3_min']);
					$data['dday3_sec']		= '00';
					if (!$data['dday3_day'] and !$data['dday3_month'] and !$data['dday3_year'])
					{
						$matchday_row['delivery_date_3'] = '';
						$data['dday3_date'] = '01-01-1980';
					}
					else
					{
						$data['dday3_date']		= sprintf('%02d-%02d-%04d', $data['dday3_day'], $data['dday3_month'], $data['dday3_year']); 
						$data['dday3_time']		= sprintf('%02d:%02d:%02d', $data['dday3_hour'], $data['dday3_min'], $data['dday3_sec']);
						$matchday_row['delivery_date_3'] = sprintf('%04d-%02d-%02d', $data['dday3_year'], $data['dday3_month'], $data['dday3_day']) . 
							' ' . $data['dday3_time'];
					}

					// Did we submit?
					if ($update)
					{
						if (!check_form_key($form_key))
						{
							trigger_error($user->lang['FORM_INVALID'] . adm_back_link($this->u_action . "&amp;s=$season&amp;l=$league"), E_USER_WARNING);
						}
						
						$matchday_row['matchday_name'] 	= utf8_normalize_nfc($this->request->variable('matchday_name', '', true));
						$matchday_row['status'] 		= utf8_normalize_nfc($this->request->variable('matchday_status', '', true));
						$matchday_row['matches'] 		= utf8_normalize_nfc($this->request->variable('matchday_matches', $matchday_row['matches'], true));

						if ($data['dday1_day'] <> '--' and $data['dday1_month'] <> '--' and $data['dday1_year'] <> '--')
						{
							$delivery_timestamp = mktime($data['dday1_hour'], $data['dday1_min'], 0, $data['dday1_month'], $data['dday1_day'], $data['dday1_year']);
							$local_board_time = time() + ($this->config['football_time_shift'] * 3600); 
							if ($delivery_timestamp > $local_board_time AND $matchday_row['status'] == 0)
							{
								// check if delivery is before all open matches 
								$sql = "SELECT 
										match_no
									FROM " . FOOTB_MATCHES . " 
									WHERE season = $season 
										AND league = $league
										AND matchday = $matchday
										AND status = 0
										AND match_datetime < FROM_UNIXTIME('$delivery_timestamp')";
								$result = $db->sql_query($sql);
								$open_matches = '';
								while ($rows_open = $db->sql_fetchrow($result))
								{
									$open_matches .= ($open_matches == '') ? $rows_open['match_no'] : ', ' . $rows_open['match_no'];
								}
								$db->sql_freeresult($result);
								if ($open_matches <> '')	
								{
									$error[] = (strpos($open_matches, ',')) ? sprintf($user->lang['OPEN_MATCHES'], $open_matches) : 
										sprintf($user->lang['OPEN_MATCH'], $open_matches);
								}
							}

							if ($delivery_timestamp > $local_board_time AND ($matchday_row['status'] == 1 OR $matchday_row['status'] == 2))
							{
								$sql_ary = array(
									'status'	=> 0,
								);
								// set all matches after delivery on status 0
								$sql = 'UPDATE ' . FOOTB_MATCHES . '
									SET ' . $db->sql_build_array('UPDATE', $sql_ary) . "
									WHERE season = $season 
										AND league = $league 
										AND matchday = $matchday
										AND status IN (1, 2)
										AND match_datetime >= FROM_UNIXTIME('$delivery_timestamp')";
								$db->sql_query($sql);
								// check on open matches 
								$sql = "SELECT 
										match_no
									FROM " . FOOTB_MATCHES . " 
									WHERE season = $season 
										AND league = $league
										AND matchday = $matchday
										AND status = 0";
								$result = $db->sql_query($sql);
								if ($rows_open = $db->sql_fetchrow($result))
								{
									// reopen matchday
									$matchday_row['status'] = 0;
								}
								$db->sql_freeresult($result);
							}
						}
						else
						{
							$error[] = $user->lang['NO_DELIVERY'];
						}

						if ($data['dday2_day'] <> '--' and $data['dday2_month'] <> '--' and $data['dday2_year'] <> '--')
						{
							$delivery2_timestamp = mktime($data['dday2_hour'], $data['dday2_min'], 0, $data['dday2_month'], $data['dday2_day'], $data['dday2_year']);
							if ($delivery2_timestamp <  $delivery_timestamp)
							{
								$error[] = $user->lang['TOO_SMALL_DELIVERY2'];
							}
						}
						else
						{
							$delivery2_timestamp = 0;
						}
						
						if ($data['dday3_day'] <> '--' and $data['dday3_month'] <> '--' and $data['dday3_year'] <> '--')
						{
							if ($delivery2_timestamp == 0)
							{
								$error[] = $user->lang['NO_DELIVERY2'];
							}
							$delivery3_timestamp = mktime($data['dday3_hour'], $data['dday3_min'], 0, $data['dday3_month'], $data['dday3_day'], $data['dday3_year']);
							if ($delivery3_timestamp <  $delivery2_timestamp)
							{
								$error[] = $user->lang['TOO_SMALL_DELIVERY3'];
							}
						}

						if ($ko_league)
						{
							$data['matches']		= $matchday_row['matches'];
						}
						else
						{
							$data['matches']		= '';
						}
						if (!sizeof($error))
						{
							$sql_ary = array(
								'season'			=> (int) $season,
								'league'			=> (int) $league,
								'matchday'			=> (int) $matchday,
								'status'			=> $matchday_row['status'],
								'delivery_date'		=> $matchday_row['delivery_date'],
								'delivery_date_2'	=> strlen($matchday_row['delivery_date_2']) ? $matchday_row['delivery_date_2'] : '',
								'delivery_date_3'	=> strlen($matchday_row['delivery_date_3']) ? $matchday_row['delivery_date_3'] : '',
								'matchday_name'		=> strlen($matchday_row['matchday_name']) ? $matchday_row['matchday_name'] : '',
								'matches'			=> strlen($matchday_row['matches']) ? $matchday_row['matches'] : 0,
							);

							$var_ary = array(
								'dday1_date'	=> array('date', false),
								'dday2_date'	=> array('date', false),
								'dday3_date'	=> array('date', false),
								'matches'	=> array('num', true, 1,99),
							);
							if (!($error_vals = validate_data($data, $var_ary)))
							{
								$sql = 'UPDATE ' . FOOTB_MATCHDAYS . '
									SET ' . $db->sql_build_array('UPDATE', $sql_ary) . "
									WHERE season = $season AND league = $league AND matchday = $matchday";
								$db->sql_query($sql);
								trigger_error($user->lang['MATCHDAY_UPDATED'] . adm_back_link($this->u_action . "&amp;s=$season&amp;l=$league"));
							}
							else
							{
								foreach ($error_vals as $error_val)
								{
										$error_msg[] = $user->lang[$error_val];
								}
								$error[] =  $user->lang['MATCHDAY_UPDATE_FAILED'];
								$error = array_merge($error, $error_msg);
							}
						}
					}
				}

				$s_delivery1_day_options = '<option value="0"' . ((!$data['dday1_day']) ? ' selected="selected"' : '') . '>--</option>';
				for ($i = 1; $i < 32; $i++)
				{
					$selected = ($i == $data['dday1_day']) ? ' selected="selected"' : '';
					$s_delivery1_day_options .= "<option value=\"" . sprintf('%02d',$i) . "\"$selected>" . sprintf('%02d',$i) . "</option>";
				}

				$s_delivery1_month_options = '<option value="0"' . ((!$data['dday1_month']) ? ' selected="selected"' : '') . '>--</option>';
				for ($i = 1; $i < 13; $i++)
				{
					$selected = ($i == $data['dday1_month']) ? ' selected="selected"' : '';
					$s_delivery1_month_options .= "<option value=\"" . sprintf('%02d',$i) . "\"$selected>" . sprintf('%02d',$i) . "</option>";
				}
				$s_delivery1_year_options = '';

				$s_delivery1_year_options = '<option value="0"' . ((!$data['dday1_year']) ? ' selected="selected"' : '') . '>--</option>';
				for ($i = $season - 1 ; $i <= $season; $i++)
				{
					$selected = ($i == $data['dday1_year']) ? ' selected="selected"' : '';
					$s_delivery1_year_options .= "<option value=\"$i\"$selected>$i</option>";
				}

				$s_delivery1_hour_options = '';
				if (!$data['dday1_hour'])
				{
					$data['dday1_hour'] = 0;
				}
				for ($i = 0; $i < 24; $i++)
				{
					$selected = ($i == $data['dday1_hour']) ? ' selected="selected"' : '';
					$s_delivery1_hour_options .= "<option value=\"" . sprintf('%02d',$i) . "\"$selected>" . sprintf('%02d',$i) . "</option>";
				}
				
				$s_delivery1_min_options = '';
				if (!$data['dday1_min'])
				{
					$data['dday1_min'] = 0;
				}
				for ($i = 0; $i < 12; $i++)
				{
					$selected = (($i * 5) == $data['dday1_min']) ? ' selected="selected"' : '';
					$s_delivery1_min_options .= "<option value=\"" . sprintf('%02d',($i * 5)) . "\"$selected>" . sprintf('%02d',($i * 5)) . "</option>";
				}
				
				$s_delivery2_day_options = '<option value="0"' . ((!$data['dday2_day']) ? ' selected="selected"' : '') . '>--</option>';
				for ($i = 1; $i < 32; $i++)
				{
					$selected = ($i == $data['dday2_day']) ? ' selected="selected"' : '';
					$s_delivery2_day_options .= "<option value=\"" . sprintf('%02d',$i) . "\"$selected>" . sprintf('%02d',$i) . "</option>";
				}

				$s_delivery2_month_options = '<option value="0"' . ((!$data['dday2_month']) ? ' selected="selected"' : '') . '>--</option>';
				for ($i = 1; $i < 13; $i++)
				{
					$selected = ($i == $data['dday2_month']) ? ' selected="selected"' : '';
					$s_delivery2_month_options .= "<option value=\"" . sprintf('%02d',$i) . "\"$selected>" . sprintf('%02d',$i) . "</option>";
				}
				$s_delivery2_year_options = '';

				$s_delivery2_year_options = '<option value="0"' . ((!$data['dday2_year']) ? ' selected="selected"' : '') . '>--</option>';
				for ($i = $season - 1 ; $i <= $season; $i++)
				{
					$selected = ($i == $data['dday2_year']) ? ' selected="selected"' : '';
					$s_delivery2_year_options .= "<option value=\"$i\"$selected>$i</option>";
				}

				$s_delivery2_hour_options = '';
				if (!$data['dday2_hour'])
				{
					$data['dday2_hour'] = 0;
				}
				for ($i = 0; $i < 24; $i++)
				{
					$selected = ($i == $data['dday2_hour']) ? ' selected="selected"' : '';
					$s_delivery2_hour_options .= "<option value=\"" . sprintf('%02d',$i) . "\"$selected>" . sprintf('%02d',$i) . "</option>";
				}
				
				$s_delivery2_min_options = '';
				if (!$data['dday2_min'])
				{
					$data['dday2_min'] = 0;
				}
				for ($i = 0; $i < 12; $i++)
				{
					$selected = (($i * 5) == $data['dday2_min']) ? ' selected="selected"' : '';
					$s_delivery2_min_options .= "<option value=\"" . sprintf('%02d',($i * 5)) . "\"$selected>" . sprintf('%02d',($i * 5)) . "</option>";
				}
				
				$s_delivery3_day_options = '<option value="0"' . ((!$data['dday3_day']) ? ' selected="selected"' : '') . '>--</option>';
				for ($i = 1; $i < 32; $i++)
				{
					$selected = ($i == $data['dday3_day']) ? ' selected="selected"' : '';
					$s_delivery3_day_options .= "<option value=\"" . sprintf('%02d',$i) . "\"$selected>" . sprintf('%02d',$i) . "</option>";
				}

				$s_delivery3_month_options = '<option value="0"' . ((!$data['dday3_month']) ? ' selected="selected"' : '') . '>--</option>';
				for ($i = 1; $i < 13; $i++)
				{
					$selected = ($i == $data['dday3_month']) ? ' selected="selected"' : '';
					$s_delivery3_month_options .= "<option value=\"" . sprintf('%02d',$i) . "\"$selected>" . sprintf('%02d',$i) . "</option>";
				}
				$s_delivery3_year_options = '';

				$s_delivery3_year_options = '<option value="0"' . ((!$data['dday3_year']) ? ' selected="selected"' : '') . '>--</option>';
				for ($i = $season - 1 ; $i <= $season; $i++)
				{
					$selected = ($i == $data['dday3_year']) ? ' selected="selected"' : '';
					$s_delivery3_year_options .= "<option value=\"$i\"$selected>$i</option>";
				}

				$s_delivery3_hour_options = '';
				if (!$data['dday3_hour'])
				{
					$data['dday3_hour'] = 0;
				}
				for ($i = 0; $i < 24; $i++)
				{
					$selected = ($i == $data['dday3_hour']) ? ' selected="selected"' : '';
					$s_delivery3_hour_options .= "<option value=\"" . sprintf('%02d',$i) . "\"$selected>" . sprintf('%02d',$i) . "</option>";
				}
				
				$s_delivery3_min_options = '';
				if (!$data['dday3_min'])
				{
					$data['dday3_min'] = 0;
				}
				for ($i = 0; $i < 12; $i++)
				{
					$selected = (($i * 5) == $data['dday3_min']) ? ' selected="selected"' : '';
					$s_delivery3_min_options .= "<option value=\"" . sprintf('%02d',($i * 5)) . "\"$selected>" . sprintf('%02d',($i * 5)) . "</option>";
				}
				
				
				$status_options = '';
				for ( $i = 0; $i < 4; $i++ )
				{
					$selected = ($matchday_row['status'] == $i) ? ' selected="selected"' : '';
					$status_options .= '<option value="' . $i . '"' . $selected . '>' . $i . '</option>';
				}
				
				// check if matches created
				$existing_matches_on_matchday = count_existing_matches($season, $league, $matchday);
		
				$u_back = $this->u_action . "&amp;s=$season&amp;l=$league";

				$template->assign_vars(array(
					'S_EDIT'				=> true,
					'S_ADD_MATCHDAY'		=> ($action == 'add') ? true : false,
					'S_ERROR'				=> (sizeof($error)) ? true : false,
					'S_KO_LEAGUE'			=> $ko_league,
					'S_EDIT_DELIVERY'		=> $edit_delivery,
					'S_UPDATE_DELIVERY'		=> false,
					'S_EDIT_MATCHES'		=> ($existing_matches_on_matchday) ? false : true,
					'ERROR_MSG'				=> (sizeof($error)) ? implode('<br />', $error) : '',
					'SEASON'				=> $season,
					'SEASON_NAME'			=> $season_name,
					'LEAGUE'				=> $league,
					'LEAGUE_NAME'			=> $league_name,
					'MATCHDAY'				=> $matchday,
					'MATCHDAY_NAME'			=> $matchday_row['matchday_name'],
					'MATCHDAY_STATUS'		=> $matchday_row['status'],
					'MATCHDAY_MATCHES'		=> $matchday_row['matches'],
					'MATCHDAY_DEL1'			=> $matchday_row['delivery_date'],
					'MATCHDAY_DEL2'			=> $matchday_row['delivery_date_2'],
					'MATCHDAY_DEL3'			=> $matchday_row['delivery_date_3'],
					'S_DELIVERY1_DAY_OPTIONS'	=> $s_delivery1_day_options,
					'S_DELIVERY1_MONTH_OPTIONS'	=> $s_delivery1_month_options,
					'S_DELIVERY1_YEAR_OPTIONS'	=> $s_delivery1_year_options,
					'S_DELIVERY1_HOUR_OPTIONS'	=> $s_delivery1_hour_options,
					'S_DELIVERY1_MIN_OPTIONS'	=> $s_delivery1_min_options,
					'S_DELIVERY2_DAY_OPTIONS'	=> $s_delivery2_day_options,
					'S_DELIVERY2_MONTH_OPTIONS'	=> $s_delivery2_month_options,
					'S_DELIVERY2_YEAR_OPTIONS'	=> $s_delivery2_year_options,
					'S_DELIVERY2_HOUR_OPTIONS'	=> $s_delivery2_hour_options,
					'S_DELIVERY2_MIN_OPTIONS'	=> $s_delivery2_min_options,
					'S_DELIVERY3_DAY_OPTIONS'	=> $s_delivery3_day_options,
					'S_DELIVERY3_MONTH_OPTIONS'	=> $s_delivery3_month_options,
					'S_DELIVERY3_YEAR_OPTIONS'	=> $s_delivery3_year_options,
					'S_DELIVERY3_HOUR_OPTIONS'	=> $s_delivery3_hour_options,
					'S_DELIVERY3_MIN_OPTIONS'	=> $s_delivery3_min_options,
					'S_VERSION_NO'				=> $this->config['football_version'],
					'U_BACK'					=> $u_back,
					'U_ACTION'					=> "{$this->u_action}&amp;action=$action&amp;s=$season&amp;l=$league",
					)
				);

				return;
			break;
		}
		
		// Get us all the matchdays
		$lang_dates = $user->lang['datetime'];
		$sql = "SELECT *,
				CONCAT(
					CASE DATE_FORMAT(delivery_date,'%w')
						WHEN 0 THEN '" . $lang_dates['Sun'] . "'
						WHEN 1 THEN '" . $lang_dates['Mon'] . "'
						WHEN 2 THEN '" . $lang_dates['Tue'] . "'
						WHEN 3 THEN '" . $lang_dates['Wed'] . "'
						WHEN 4 THEN '" . $lang_dates['Thu'] . "'
						WHEN 5 THEN '" . $lang_dates['Fri'] . "'
						WHEN 6 THEN '" . $lang_dates['Sat'] . "'
						ELSE 'Error' END,
					DATE_FORMAT(delivery_date,' %d.%m.%y  %H:%i')
				) AS match_time,
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
					DATE_FORMAT(delivery_date_2,' %d.%m.%y  %H:%i')
				) AS match_time_2,
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
					DATE_FORMAT(delivery_date_3,' %d.%m.%y  %H:%i')
				) AS match_time_3,
				DATE_FORMAT(delivery_date, '%dd') AS deliveryday,
				DATE_FORMAT(delivery_date, ' %d.%m.%Y um %H:%i ') AS delivery
			FROM " . FOOTB_MATCHDAYS . " 
			WHERE season = $season 
				AND league = $league
			ORDER BY matchday ASC";
		$result = $db->sql_query($sql);
		$rows_matchdays = $db->sql_fetchrowset($result);
		$existing_matchdays = sizeof($rows_matchdays);
		$db->sql_freeresult($result);

		$template->assign_vars(array(
			'U_ACTION'			=> $this->u_action,
			'U_FOOTBALL' 		=> $helper->route('football_football_controller',array('side' => 'bet', 's' => $season, 'l' => $league)),
			'S_SEASON'			=> $season,
			'S_LEAGUE'			=> $league,
			'S_SEASON_OPTIONS'	=> $season_options,
			'S_LEAGUE_OPTIONS'	=> $league_options,
			'S_MATCHDAY_ADD'	=> ($league_matchdays > $existing_matchdays) ? true : false,
			'S_MATCHDAYS_REMOVE'=> ($league_matchdays < $existing_matchdays) ? true : false,
			'S_VERSION_NO'		=> $this->config['football_version'],
			) 
		);

		// Check if the user is allowed to delete a matchday.
		if ($user->data['user_type'] != USER_FOUNDER && $this->config['football_founder_delete'])
		{
			$allow_delete = false;
		}
		else
		{
			$allow_delete = true;
		}

		$row_number = 0;
		foreach ($rows_matchdays as $row_matchday)
		{
			$row_number++;
			$row_class = (!($row_number % 2)) ? 'bg1 row_light' : 'bg2 row_dark';
			$template->assign_block_vars('matchdays', array(
				'ROW_CLASS'			=> $row_class,
				'MATCHDAY'			=> $row_matchday['matchday'],
				'MATCHDAY_NAME'		=> $row_matchday['matchday_name'],
				'MATCHDAY_STATUS'	=> $row_matchday['status'],
				'MATCHDAY_DELIVERY'	=> $row_matchday['match_time'],
				'MATCHDAY_DELIVERY_2'	=> $row_matchday['match_time_2'],
				'MATCHDAY_DELIVERY_3'	=> $row_matchday['match_time_3'],
				'U_EDIT'	=> "{$this->u_action}&amp;action=edit&amp;s=" . $season . "&amp;l=" .$league . "&amp;m=" .$row_matchday['matchday'],
				'U_DELETE'	=> ($allow_delete) ? "{$this->u_action}&amp;action=delete&amp;s=" . $season . "&amp;l=" . $league . "&amp;m=" . $row_matchday['matchday'] : '',
				)
			);
		}
	}
}
