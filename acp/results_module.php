<?php
/**
*
* @package phpBB Extension - Football Football
* @copyright (c) 2016 football (http://football.bplaced.net)
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace football\football\acp;

class results_module
{
	public $u_action;

	protected $db, $user, $template, $config, $phpbb_root_path, $phpbb_admin_path, $phpEx;
	protected $root_path, $request, $php_ext, $log;


	public function __construct()
	{
		global $db, $user, $request, $template;
		global $config, $phpbb_root_path, $phpbb_admin_path, $phpEx;

		$user->add_lang_ext('football/football', 'football');
		$user->add_lang_ext('football/football', 'info_acp_results');

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
		
		if ($phpbb_extension_manager->is_enabled('dmzx/ultimatepoints')) {
			// Get an instance of the ultimatepoints functions_points
			$functions_points = $phpbb_container->get('dmzx.ultimatepoints.core.functions.points');
		}
		
		$this->tpl_name = 'acp_football_results';
		$this->page_title = 'ACP_FOOTBALL_RESULTS_MANAGE';

		$form_key = 'acp_football_results';
		add_form_key($form_key);
		
		include($this->phpbb_root_path . 'includes/functions_user.' . $this->php_ext);

		// Check and set some common vars
		$action		= (isset($_POST['edit'])) ? 'edit' : $this->request->variable('action', '');
		$season		= $this->request->variable('s', 0);
		$league		= $this->request->variable('l', 0);
		$matchday	= $this->request->variable('m', 0);
		$update		= (isset($_POST['update'])) ? true : false;

		// Close matchday
		close_open_matchdays();

		// Clear some vars
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
			$sql = 'SELECT * 
					FROM ' . FOOTB_MATCHES . "  
					WHERE season = $season 
					AND status in (0,1,2,4,5)
					ORDER BY match_datetime ASC";
			$result = $db->sql_query($sql);

			if ($row = $db->sql_fetchrow($result))
			{
				$league = $row['league'];
				$matchday = $row['matchday'];
			}
			else
			{
				$league = first_league($season);
			}
			$db->sql_freeresult($result);
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
					$league_info = $row;
				}
			}
			$db->sql_freeresult($result);
		}
		else
		{
			trigger_error(sprintf($user->lang['NO_LEAGUE'], $season) . adm_back_link($this->u_action . "&amp;s=$season"), E_USER_WARNING);
		}

		// Grab basic data for select matchday
		if (!$matchday)
		{
			$matchday = curr_matchday($season, $league);
		}
		$sql = 'SELECT *
			FROM ' . FOOTB_MATCHDAYS . "
			WHERE season = $season 
				AND league = $league
			ORDER BY matchday ASC";
		$result = $db->sql_query($sql);

		$matchday_options = '';
		$worldfootball = false;
		while ($row = $db->sql_fetchrow($result))
		{
			$selected = ($matchday && $row['matchday'] == $matchday) ? ' selected="selected"' : '';
			$day_name = (strlen($row['matchday_name']) > 0) ? $row['matchday_name'] : $row['matchday'] . '. ' . sprintf($user->lang['MATCHDAY']);
			$matchday_options .= '<option value="' . $row['matchday'] . '"' . $selected . '>' . $day_name . '</option>';
			if ($selected <> '')
			{
				$matchday_name = $day_name;
				if ($league_info['matches_on_matchday'])
				{
					$matches_on_matchday = $league_info['matches_on_matchday'];
					$worldfootball = ($row['status'] > 0) ? true : false;							}
				else
				{
					$matches_on_matchday = $row['matches'];
				}
			}
		}
		$db->sql_freeresult($result);
		if ($matchday_options == '')
		{
			trigger_error(sprintf($user->lang['NO_MATCHDAY'], $league_info['league_name'], $season) . adm_back_link($this->u_action . "&amp;s=$season&amp;l=$league"), E_USER_WARNING);
		}
		$local_board_time = time() + ($this->config['football_time_shift'] * 3600); 
	
		// Which page?
		switch ($action)
		{
			case 'edit':
				if ($season < $curr_season)
				{
					trigger_error("Diese Saison kann nicht mehr gespeichert werden!", E_USER_WARNING);
				}
				$sql = "SELECT * ,
						IF( match_datetime > FROM_UNIXTIME('$local_board_time'), 1, 0) As open_match
						FROM " . FOOTB_MATCHES . "
						WHERE season = $season 
							AND league = $league 
							AND matchday = $matchday";
				$result = $db->sql_query($sql);
				$rows_matches = $db->sql_fetchrowset($result);
				$db->sql_freeresult($result);
				$matches_on_matchday = sizeof($rows_matches);
				$count_delete = 0;
				$count_no_valuation = 0;
				$count_results = 0;
				foreach ($rows_matches as $row_match)
				{
					$match_num 		= $row_match['match_no'];
					$status 		= $row_match['status'];
					$select			= $this->request->variable('select_' . $match_num, false);
					$no_valuation 	= $this->request->variable('no_valuation_' . $match_num, false);
					$open_match		= $row_match['open_match'];
					
					if ($select or $no_valuation or (!$no_valuation and $status > 3))
					{
						$goals_home		= $this->request->variable('goals_home_' . $match_num, '');
						$goals_guest 	= $this->request->variable('goals_guest_' . $match_num, '');
						$overtime_home	= $this->request->variable('overtime_home_' . $match_num, '');
						$overtime_guest = $this->request->variable('overtime_guest_' . $match_num, '');
						$delete 		= $this->request->variable('delete_' . $match_num, false);
					
						if ($delete)
						{
							$goals_home 	= '';
							$goals_guest 	= '';
							$overtime_home 	= '';
							$overtime_guest = '';
						}
						if ($no_valuation)
						{
							$status = 4;
							$count_no_valuation++;
						}
						else if ($status > 3)
						{
							$status = 3;
						}
						if(is_numeric($goals_home) && is_numeric($goals_guest) && $goals_home >= 0 && $goals_guest >= 0)
						{
							if ($status <= 3)
								$status = 3;
							else
							{
								$status = 6;
							}
							$sql_ary = array(
								'goals_home'			=> $goals_home,
								'goals_guest'			=> $goals_guest,
								'status'				=> $status,
								'goals_overtime_home'	=> (!is_numeric($overtime_home) OR !is_numeric($overtime_guest)) ? '' : $overtime_home,
								'goals_overtime_guest'	=> (!is_numeric($overtime_home) OR !is_numeric($overtime_guest)) ? '' : $overtime_guest,
							);
							$sql = 'UPDATE ' . FOOTB_MATCHES . '
								SET ' . $db->sql_build_array('UPDATE', $sql_ary) . "
								WHERE season = $season AND league = $league AND match_no = $match_num";
							$db->sql_query($sql);
							$count_results++;
						}
						else
						{
							if ($status <= 3)
							{
								if (($league_info['bet_in_time'] == 1) and $open_match)
								{
									$status = 0;
									$sql_ary = array('status' => $status);
									$sql = 'UPDATE ' . FOOTB_MATCHDAYS . '
										SET ' . $db->sql_build_array('UPDATE', $sql_ary) . "
										WHERE season = $season AND league = $league AND matchday = $matchday AND delivery_date_2 = ''";
									$db->sql_query($sql);
									$success[] =  sprintf($user->lang['SET_STATUS_TO'], $status) ;
								}
								else
								{
									$status = 1;
								}
							}
							else
							{
								$status = 4;
							}
							$sql_ary = array(
								'goals_home'			=> '',
								'goals_guest'			=> '',
								'status'				=> $status,
								'goals_overtime_home'	=> '',
								'goals_overtime_guest'	=> '',
							);
							$sql = 'UPDATE ' . FOOTB_MATCHES . '
								SET ' . $db->sql_build_array('UPDATE', $sql_ary) . "
								WHERE season = $season AND league = $league AND match_no = $match_num";
							$db->sql_query($sql);
							$count_delete++;
						}
					}
				}
				if ($count_results)
				{
					$success[] =  sprintf($user->lang['RESULT' . (($count_results == 1) ? '' : 'S') . '_SAVED'],$count_results) ;
				}
				if ($count_delete)
				{
					$success[] =  sprintf($user->lang['RESULT' . (($count_delete == 1) ? '' : 'S') . '_DELETED'],$count_delete) ;
				}
				if ($count_no_valuation)
				{
					$success[] =  sprintf($user->lang['RESULT' . (($count_delete == 1) ? '' : 'S') . '_NO_VALUATION'],$count_no_valuation) ;
				}
				
				// extra bets
				$sql = 'SELECT * FROM ' . FOOTB_EXTRA . " WHERE season = $season AND league = $league AND matchday_eval = $matchday AND extra_status > 0";
				$resultextra = $db->sql_query($sql);
				$count_extra_updates = 0;
				$count_extra_delete = 0;
				$count_extra_bets = 0;
				while( $row = $db->sql_fetchrow($resultextra))
				{
					$count_extra_bets++;
					$extra_no = $row['extra_no'];
					$extra_results = $this->request->variable('extra' . $extra_no, array('nv'));
					$extra_result = '';
					if (sizeof($extra_results) > 0)
					{
						foreach ($extra_results as $extra_selected_value)
						{
							$extra_result = ($extra_result == '') ? $extra_selected_value : $extra_result . ';' . $extra_selected_value;
						}
					}
					else
					{
						$extra_result = $this->request->variable('extra' . $extra_no, 'nv');
					}
					if ($extra_result != 'nv' && $this->request->variable('select' . $extra_no, false))
					{
						if ($row['question_type'] == 5 && !is_numeric($extra_result))
						{
							$extra_result = '';
						}
						if ($extra_result != '') 
						{
							$sql = 'SELECT * FROM ' . FOOTB_EXTRA . " WHERE season = $season AND league = $league AND extra_no = $extra_no";
							$result = $db->sql_query($sql);
							$row2 = $db->sql_fetchrow($result);
							$db->sql_freeresult($result);
							if($row2)
							{
								$sql_ary = array(
									'result'		=> $extra_result,
									'extra_status'	=> 3,
								);
								$sql = 'UPDATE ' . FOOTB_EXTRA . '
									SET ' . $db->sql_build_array('UPDATE', $sql_ary) . "
									WHERE season = $season AND league = $league AND extra_no = $extra_no";
								$db->sql_query($sql);
								$count_extra_updates++;
							}
						}
						else
						{
							// extra result unset
							$sql_ary = array(
								'result'		=> '',
								'extra_status'	=> 1,
							);
							$sql = 'UPDATE ' . FOOTB_EXTRA . '
								SET ' . $db->sql_build_array('UPDATE', $sql_ary) . "
								WHERE season = $season AND league = $league AND extra_no = $extra_no";
							$db->sql_query($sql);
							$count_extra_delete++;
						}
					}
				}
				$db->sql_freeresult($resultextra);
				if ($count_extra_updates)
				{
					$success[] =  sprintf($user->lang['EXTRA_RESULT' . (($count_extra_updates == 1) ? '' : 'S') . '_SAVED'], $count_extra_updates);
				}
				if ($count_extra_delete)
				{
					$success[] =  sprintf($user->lang['EXTRA_RESULT' . (($count_extra_delete == 1) ? '' : 'S') . '_DELETED'], $count_extra_delete);
				}
				calculate_extra_points($season, $league, $matchday, true);

				$sql = 'Select 
						MIN(status) AS min_status,
						MAX(status) AS max_status 
					FROM
					((SELECT 
						matchday_eval AS matchday,
						extra_status AS status
					FROM ' . FOOTB_EXTRA . " 
					WHERE season = $season 
						AND league = $league 
						AND matchday_eval = $matchday)
					UNION
					(SELECT 
						matchday,
						status
					FROM " . FOOTB_MATCHES . "
					WHERE season = $season 
						AND league = $league 
						AND matchday = $matchday )) AS ebm
					GROUP BY matchday";
				$result = $db->sql_query($sql);
				$row = $db->sql_fetchrow($result);
				if($row['min_status'] > 0)
				{
					if ($row['max_status'] < 3)
					{
						$new_status = $row['max_status'];
					}
					else
					{
						if ($row['min_status'] > 2)
						{
							$new_status = 3;
						}
						else
						{
							$new_status = 2;
						}
					}
					$sql_ary = array('status' => $new_status);
					$sql = 'UPDATE ' . FOOTB_MATCHDAYS . '
						SET ' . $db->sql_build_array('UPDATE', $sql_ary) . "
						WHERE season = $season AND league = $league AND matchday = $matchday AND delivery_date_2 = ''";
					$db->sql_query($sql);
					$success[] =  sprintf($user->lang['SET_STATUS_TO'], $new_status) ;
				}
				$db->sql_freeresult($result);

				$cash	= $this->request->variable('cash', false);
				save_ranking_matchday($season, $league, $matchday, $cash);

				// Patch delevirey
				if ($league_info['bet_in_time'] == 1) 
				{
					set_bet_in_time_delivery($season, $league);
				}
			break;
		}
		// Check KO matchday
		$sql = 'SELECT
				SUM(ko_match) AS ko_matchday
			FROM ' . FOOTB_MATCHES . " 
			WHERE season = $season 
				AND league = $league 
				AND matchday = $matchday
			GROUP BY matchday";
		$result = $db->sql_query($sql);
		$row = $db->sql_fetchrow($result);
		$ko_matchday = $row['ko_matchday'];
		$db->sql_freeresult($result);

		
		// Get us all the matches
		$lang_dates = $user->lang['datetime'];
		$sql = "SELECT  
				m.match_no, 
				m.status,
				m.team_id_home AS home_id,
				m.team_id_guest AS guest_id,
				m.formula_home,
				m.formula_guest,
				m.goals_home,
				m.goals_guest,
				m.goals_overtime_home,
				m.goals_overtime_guest,
				m.ko_match,
				th.team_name AS home_name,
				tg.team_name AS guest_name,
				UNIX_TIMESTAMP(m.match_datetime) AS unix_match_begin, 
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
					DATE_FORMAT(m.match_datetime,' %d.%m.%y  %H:%i')
				) AS match_begin
			FROM " . FOOTB_MATCHES . ' AS m
			LEFT JOIN ' . FOOTB_TEAMS . ' AS th ON (th.season = m.season AND th.league = m.league AND th.team_id = m.team_id_home)
			LEFT JOIN ' . FOOTB_TEAMS . " AS tg ON (tg.season = m.season AND tg.league = m.league AND tg.team_id = m.team_id_guest)
			WHERE m.season = $season 
				AND m.league = $league 
				AND m.matchday = $matchday
			ORDER BY unix_match_begin ASC, m.match_no ASC";
		$result = $db->sql_query($sql);
		$rows_matches = $db->sql_fetchrowset($result);
		$existing_matches = sizeof($rows_matches);
		$db->sql_freeresult($result);
		$legend = delivery($season, $league, $matchday);

		$row_number = 0;
		foreach ($rows_matches as $row_match)
		{
			$row_number++;
			if ($this->config['football_results_at_time'])
			{
				$edit = (($row_match['unix_match_begin'] + 6300 < time()) && $row_match['status'] > 0) ? true :false;
			}
			else
			{
				$edit = ($row_match['status'] > 0) ? true :false;
			}
			if (0 == $row_match['home_id'])
			{
				$home_info 		= get_team($season, $league, $row_match['match_no'], 'team_id_home', $row_match['formula_home']);
				$home_in_array 	= explode("#",$home_info);
				$homename 		= $home_in_array[2];
			}
			else
			{
				$homename 	= $row_match['home_name'];
			}
			if (0 == $row_match['guest_id'])
			{
				$guest_info 	= get_team($season, $league, $row_match['match_no'], 'team_id_guest', $row_match['formula_guest']);
				$guest_in_array = explode("#",$guest_info);
				$guestname 		= $guest_in_array[2];
			}
			else
			{
				$guestname 	= $row_match['guest_name'];
			}
			
			$row_class = (!($row_number % 2)) ? 'bg1 row_light' : 'bg2 row_dark';
			$template->assign_block_vars('match', array(
				'ROW_CLASS'				=> $row_class,
				'EDIT'					=> $edit,
				'SELECT_CHECKED'		=> ($row_match['status'] > 0 AND $row_match['status'] < 3) ? ' selected="selected"' : '',
				'BEGIN'					=> $row_match['match_begin'],
				'NUMBER'				=> $row_match['match_no'],
				'STATUS'				=> $row_match['status'],
				'STATUS_COLOR'			=> color_match($row_match['status'], $row_match['status']),
				'HOME_NAME'				=> $homename,
				'GUEST_NAME'			=> $guestname,
				'GOALS_HOME'			=> $row_match['goals_home'],
				'GOALS_GUEST'			=> $row_match['goals_guest'],
				'OVERTIME_HOME'			=> $row_match['goals_overtime_home'],
				'OVERTIME_GUEST'		=> $row_match['goals_overtime_guest'],
				'NO_VALUATION_CHECKED'	=> ($row_match['status'] > 3) ? ' selected="selected"' : '',
				'KO_MATCH'				=> $row_match['ko_match'],
				)
			);
		}
		if ($worldfootball  AND $season >= $curr_season)
		{
			$template->assign_block_vars('worldfootball', array(
				)

			);
		}

		// Calculate extra bets of matchday
		// Start select team
		$sql = 'SELECT 
					team_id AS option_value,
					team_name AS option_name
				FROM ' . FOOTB_TEAMS . "
				WHERE season = $season 
				AND league = $league 
				ORDER BY team_name ASC";
		$result = $db->sql_query($sql);
		$option_rows = $db->sql_fetchrowset($result);
		$db->sql_freeresult($result);

		$sql = "SELECT e.*,
				t1.team_name AS result_team
			FROM  " . FOOTB_EXTRA . ' AS e
			LEFT JOIN ' . FOOTB_TEAMS . " AS t1 ON (t1.season = e.season AND t1.league = e.league AND t1.team_id = e.result)
			WHERE e.season = $season 
				AND e.league = $league 
				AND e.matchday_eval = $matchday
			ORDER BY e.extra_no ASC";
		$result = $db->sql_query($sql);

		$extra_results = false;
		$extranumber = 0;
		while ($row = $db->sql_fetchrow($result))
		{
			$extra_results = true;
			$extranumber++ ;
			$row_class = (!($extranumber % 2)) ? 'bg1 row_light' : 'bg2 row_dark';

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
						$eval_title = sprintf($user->lang['EXTRA_MULTI_HIT']);
					}
					break;
				case '5':
					{
						$display_type = 2;
						$eval_title = sprintf($user->lang['EXTRA_DIFFERENCE']);
					}
					break;
				default :
					{
						$display_type = 2;
						$eval_title = '';
					}
					break;
			}

			if ($row['extra_status'] > 0)
			{
				$edit_mode = true;
				$result_extra = ($row['result_team'] == NULL) ? '' : $row['result_team'];

				$multiple = '';
				switch($row['question_type'])
				{
					case '2':
						{
							$multiple = ' multiple="multiple" size="3" ';
						}
						break;
					case '4':
						{
							$option_arr = array();
							for ($i = 65; $i <= 72; $i++) 
							{
								if (strstr($row['question'], chr($i) . ':'))
								{
									$option_arr[] = array(
										'option_value'	=> chr($i),
										'option_name'	=> chr($i),
									);
								}
							}
							if ( sizeof($option_arr) > 1 )
							{
								$display_type = 1;
								$option_rows = $option_arr;
								$result_extra = $row['result'];
								$multiple = ' multiple="multiple" size="3" ';
							}
						}
						break;
					case '3':
						{
							$option_arr = array();
							for ($i = 65; $i <= 72; $i++) 
							{
								if (strstr($row['question'], chr($i) . ':'))
								{
									$option_arr[] = array(
										'option_value'	=> chr($i),
										'option_name'	=> chr($i),
									);
								}
							}
							if ( sizeof($option_arr) > 1 )
							{
								$display_type = 1;
								$option_rows = $option_arr;
								$result_extra = $row['result'];
							}
						}
						break;
				}
		
				$template->assign_block_vars('extra_result', array(
					'ROW_CLASS' 		=> $row_class,
					'EXTRA_NO' 			=> $row['extra_no'],
					'S_EDIT_EXTRA' 		=> true,
					'QUESTION' 			=> $row['question'],
					'EXTRA_POINTS' 		=> $row['extra_points'],
					'EVALUATION' 		=> ($row['matchday'] == $row['matchday_eval']) ? sprintf($user->lang['MATCHDAY']) : sprintf($user->lang['TOTAL']),
					'EVALUATION_TITLE' 	=> $eval_title,
					'RESULT' 			=> ($display_type == 1) ? $result_extra : $row['result'],
					'S_DISPLAY_TYPE'	=> $display_type,
					'S_MULTIPLE'		=> $multiple,
					'S_MULTIPLE_ARR'	=> ($multiple == '') ? '' : '[]',
					'STATUS'			=> $row['extra_status'],
					'STATUS_COLOR'		=> color_match($row['extra_status'], $row['extra_status']),
					)
				);
				
				if ($display_type == 1)
				{
					$selected = ($row['result'] == '') ? ' selected="selected"' : '';
					
					$template->assign_block_vars('extra_result.extra_option', array(
						'OPTION_VALUE' 	=> '',
						'OPTION_NAME' 	=> sprintf($user->lang['SELECT']),
						'S_SELECTED' 	=> $selected));
						
					foreach ($option_rows as $option_row)
					{
						if (strstr($row['result'], ';'))
						{
							$selected = '';
							$result_arr = explode(';', $row['result']);
							foreach($result_arr AS $result_value)
							{
								if ($result_value <> '')
								{
									if ($option_row['option_value'] == $result_value)
									{
										$selected = ' selected="selected"';
									}
								}
							}
						}
						else
						{
							$selected = ($option_row['option_value'] == $row['result']) ? ' selected="selected"' : '';
						}
						$template->assign_block_vars('extra_result.extra_option', array(
							'OPTION_VALUE' 	=> $option_row['option_value'],
							'OPTION_NAME' 	=> $option_row['option_name'],
							'S_SELECTED' 	=> $selected));
					}
				}
			}
			else
			{
				$extra_colorstyle = color_style($row['extra_status']);
				$extra_result = ($row['result'] == '') ? '&nbsp;' : $row['result'];
				$result_extra = ($row['result_team'] == NULL) ? '&nbsp;' : $row['result_team'];
				
				$template->assign_block_vars('extra_result', array(
					'ROW_CLASS' 		=> $row_class,
					'S_EDIT_EXTRA' 		=> false,
					'QUESTION' 			=> $row['question'],
					'EXTRA_POINTS' 		=> $row['extra_points'],
					'EVALUATION' 		=> ($row['matchday'] == $row['matchday_eval']) ? sprintf($user->lang['MATCHDAY']) : sprintf($user->lang['TOTAL']),
					'RESULT' 			=> ($display_type == 1) ? $result_extra : $extra_result,
					'COLOR_STYLE' 		=> $extra_colorstyle,
					)
				);
			}
		}
		$db->sql_freeresult($result);
		
		switch ($league_info['bet_ko_type'])
		{
			case BET_KO_90:
				$result_explain = sprintf($user->lang['MIN90']);
				$label_finalresult = sprintf($user->lang['EXTRATIME_SHORT']) . '/' . sprintf($user->lang['PENALTY_SHORT']);
				break;
			case BET_KO_EXTRATIME:
				$result_explain = sprintf($user->lang['EXTRATIME_SHORT']);
				$label_finalresult = sprintf($user->lang['PENALTY']);
				break;
			case BET_KO_PENALTY:
				$result_explain = sprintf($user->lang['PENALTY']);
				$ko_matchday = false;
				break;
			default:
				$result_explain = sprintf($user->lang['MIN90']);
				$label_finalresult = sprintf($user->lang['EXTRATIME_SHORT']) . '/' . sprintf($user->lang['PENALTY_SHORT']);
				break;
		}
		
		$template->assign_vars(array(
			'U_ACTION'			=> $this->u_action,
			'U_FOOTBALL' 		=> $helper->route('football_main_controller',array('side' => 'results', 's' => $season, 'l' => $league, 'm' => $matchday)),
			'S_LEGEND'			=> $legend,
			'S_SUCCESS'			=> (sizeof($success)) ? true : false,
			'SUCCESS_MSG'		=> (sizeof($success)) ? implode('<br />', $success) : '',
			'RESULT_EXPLAIN' 	=> $result_explain,
			'LABEL_FINALRESULT' => (isset($label_finalresult)) ? $label_finalresult : sprintf($user->lang['EXTRATIME_SHORT']) . '/' . sprintf($user->lang['PENALTY_SHORT']),
			'S_CASH_POINTS'		=> ($phpbb_extension_manager->is_enabled('dmzx/ultimatepoints')) ? true : false,
			'S_CASH'			=> ($season >= $curr_season) ? true : false,
			'S_SEASON'			=> $season,
			'S_LEAGUE'			=> $league,
			'S_MATCHDAY'		=> $matchday,
			'S_SEASON_OPTIONS'	=> $season_options,
			'S_LEAGUE_OPTIONS'	=> $league_options,
			'S_KO_MATCHDAY'		=> $ko_matchday,
			'S_MATCHDAY_OPTIONS'=> $matchday_options,
			'S_EXTRA_RESULTS' 	=> $extra_results,
			'S_TIME' 			=> sprintf($user->lang['TIME']) . ': ' . date("d.m.Y H:i", $local_board_time),
			'S_VERSION_NO'		=> $this->config['football_version'],
			)
		);
	}
}

?>