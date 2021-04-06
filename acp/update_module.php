<?php
/**
*
* @package phpBB Extension - Football Football
* @copyright (c) 2016 football (http://football.bplaced.net)
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace football\football\acp;

class update_module
{
	var $team_ary = array();
	var $xml_ary = array();
	var $footb_matchdays = array('season', 'league', 'matchday', 'status', 'delivery_date', 'delivery_date_2', 'delivery_date_3', 'matchday_name', 'matches');
	var $footb_teams = array('season', 'league', 'team_id', 'team_name', 'team_name_short', 'team_symbol', 'group_id', 'matchday');
	var $footb_matches = array('season', 'league', 'match_no', 'team_id_home', 'team_id_guest', 'goals_home', 'goals_guest', 'matchday', 'status', 
							'match_datetime', 'group_id', 'formula_home', 'formula_guest', 'ko_match', 'goals_overtime_home', 'goals_overtime_guest');
	var $no_result_fields = array('team_id_home' => 0, 
									'team_id_guest' => 0, 
									'match_datetime' => 0, 
									'group_id' => 0, 
									'formula_home' => 0, 
									'formula_guest' => 0, 
									'ko_match' => 0
								);
	public $u_action;
	public $ext_football_path;

	protected $db, $user, $template, $config, $phpbb_root_path, $phpbb_admin_path, $phpEx;
	protected $root_path, $request, $php_ext, $log;


	public function __construct()
	{
		global $db, $auth, $phpbb_container, $phpbb_admin_path, $league_info, $functions_points;
		global $template, $user, $config, $phpbb_extension_manager, $request, $phpbb_root_path, $phpEx;
		
		$helper = $phpbb_container->get('controller.helper');
		
		$user->add_lang_ext('football/football', 'info_acp_update');

		$this->db = $db;
		$this->user = $user;
		$this->template = $template;
		$this->config = $config;
		$this->request = $request;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->phpbb_admin_path = $phpbb_admin_path;
		$this->php_ext = $phpEx;
		if ($phpbb_extension_manager->is_enabled('dmzx/ultimatepoints')) {
			// Get an instance of the ultimatepoints functions_points
			$functions_points = $phpbb_container->get('dmzx.ultimatepoints.core.functions.points');
		}
	}

	public function key_compare_func($key1, $key2)
	{
		if ($key1 == $key2)
			return 0;
		else if ($key1 > $key2)
			return 1;
		else
			return -1;
	}

	public function sort_teams($value_a, $value_b) 
	{
		if ($value_a['team_id'] > $value_b['team_id']) 
		{
			return 1;
		} 
		else 
		{
			if ($value_a['team_id'] == $value_b['team_id']) 
			{
				return 0;
			} 
			else 
			{
				return -1;
			}
		}
	}
	
	function main($id, $mode)
	{
		global $db, $auth, $phpbb_container, $phpbb_admin_path, $league_info, $functions_points, $phpbb_log;
		global $template, $user, $config, $phpbb_extension_manager, $request, $phpbb_root_path, $phpEx, $cache;
		
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

		$this->tpl_name = 'acp_football_update';
		$this->page_title = 'ACP_FOOTBALL_UPDATE_MANAGE';

		$form_key = 'acp_football_update';
		add_form_key($form_key);

		include_once($this->phpbb_root_path . 'includes/functions_user.' . $this->php_ext);

		// Check and set some common vars
		$action			= (isset($_POST['load_xml_season'])) ? 'load_xml_season' : '';
		$action			= (isset($_POST['team_mapping'])) ? 'team_mapping' : $action;
		$action			= (isset($_POST['map_teams'])) ? 'map_teams' : $action;
		$action			= (isset($_POST['load_xml_league'])) ? 'load_xml_league' : $action;
		$action			= (isset($_POST['choose'])) ? 'choose' : $action;
		$action			= (isset($_POST['list'])) ? 'list' : $action;
		$action			= (isset($_POST['insert'])) ? 'insert' : $action;
		$action			= (isset($_POST['update'])) ? 'update' : $action;
		$action 		= (empty($action)) ? $this->request->variable('action', '') : $action;
		$xml_season_url	= $this->request->variable('xml_season_url', '');
		$xml_league_url	= $this->request->variable('xml_league_url', '');
		$xseason		= $this->request->variable('xs', 0);
		$xleague		= $this->request->variable('xl', 0);
		$xcode			= $this->request->variable('xcode', '');
		$season			= $this->request->variable('s', 0);
		$league			= $this->request->variable('l', 0);
		$league_name	= $this->request->variable('league_name', '');
		$new_league		= $this->request->variable('new_league', 0);
		$insert_season	= $this->request->variable('insert_season', false);
		$insert_league	= $this->request->variable('insert_league', false);
		$list			= $this->request->variable('list', false);
		$this->xml_ary	= json_decode(urldecode($this->request->variable('xml_ary', '')),true);
		$display_team_mapping = false;
		// Clear some vars
		$error = array();
		$success = array();
		$missing_team_ids_ary = array();
		$curr_season = curr_season();
		$choose_league = false;
		$xseason_options = '';
		$xleague_options = '';
		$season_options = '';
		$league_options = '';
		
		// Grab current season
		$season = ($season) ? $season : $curr_season;
	
		// Which action?
		switch ($action)
		{
			case 'load_xml_season':
			case 'choose':
				if (!$xml_season_url or strtoupper($xml_season_url) == 'LOCALHOST')
				{
					$xml_season_url = 'localhost';
					// Search files on localhost
					$files = glob($phpbb_root_path . "/store/league*.xml");  
					if ($files)  
					{
						$first_season = ($xseason) ? false : true;
						$first_league = ($xleague) ? false : true;
						foreach (glob($phpbb_root_path . "/store/league*.xml") AS $filename) 
						{
							if ($xml = @simplexml_load_file($filename))
							{
								if ($first_season)
								{
									$selected = ' selected="selected"';
									$xseason = $xml->footb_seasons->season;
									$first_season = false;
								}
								else
								{
									$selected = ($xseason && $xml->footb_seasons->season == $xseason) ? ' selected="selected"' : '';
								}
								$xseason_options .= '<option value="' . $xml->footb_seasons->season . '"' . $selected . '>' . $xml->footb_seasons->season_name_short . '</option>';
								if ($selected <> '')
								{
									$xseason_name = $xml->footb_seasons->season_name_short;
									if ($first_league)
									{
										$selected = ' selected="selected"';
										$first_league = false;
									}
									else
									{
										$selected = ($xleague && $xml->footb_leagues->league == $xleague) ? ' selected="selected"' : '';
									}
									$xleague_options .= '<option value="' . $xml->footb_leagues->league . '"' . $selected . '>' . $xml->footb_leagues->league_name . '</option>';
									if ($selected <> '')
									{
										$league_name = $xml->footb_leagues->league_name;
									}
								}
							}
							else
							{
								trigger_error(sprintf($user->lang['ERROR_LOAD_LEAGUE_XML'], strstr($filename, "\\")) . adm_back_link($this->u_action), E_USER_WARNING);
							}
						}
					}
					else
					{
						trigger_error(sprintf($user->lang['NO_XML_LEAGUE']) . adm_back_link($this->u_action), E_USER_WARNING);
					}
				}
				else
				{
					if (!($xml_str = $cache->get('football_xml_season')))
					{
						if ($xml_str = @file_get_contents($xml_season_url))
						{
							if (strpos($xml_str, '<seasons-data xmlns'))
							{
								$cache->put('football_xml_season', $xml_str, 300);
							}
							else
							{
								trigger_error(sprintf($user->lang['ERROR_READ_SEASON_XML']) . adm_back_link($this->u_action), E_USER_WARNING);
							}
						}
						else
						{
							trigger_error(sprintf($user->lang['ERROR_OPEN_SEASON_XML']) . adm_back_link($this->u_action), E_USER_WARNING);
						} 						
					}
					
					if($xml_season = new \SimpleXMLElement($xml_str)) 
					{   
						$first_league = ($xleague) ? false : true;
						$xcode = $xml_season->code;
						foreach($xml_season->season AS $tag_season) 
						{ 
							$xseason = ($xseason) ? $xseason : $tag_season->season_id;
							$selected = ($xseason && $tag_season->season_id == $xseason) ? ' selected="selected"' : '';
							$xseason_options .= '<option value="' . $tag_season->season_id . '"' . $selected . '>' . $tag_season->season_name_short . '</option>';
							if ($selected <> '')
							{
								foreach($tag_season->league AS $tag_league) 
								{ 
									$selected = ($xleague && $tag_league->league_id == $xleague) ? ' selected="selected"' : '';
									if ($first_league)
									{
										$selected = ' selected="selected"';
										$xleague =  $tag_league->league_id;
										$first_league = false;
									}
									$xleague_options .= '<option value="' . $tag_league->league_id . '"' . $selected . '>' . $tag_league->league_name . '</option>';
									if ($selected <> '')
									{
										$league_name = $tag_league->league_name;
									}
								}
							}
						}
					}
					else
					{
						trigger_error($user->lang['NO_XML_SEASON'] . adm_back_link($this->u_action), E_USER_WARNING);
					}

				}
				// Grab basic data for select season
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
				
				// Grab basic data for select league
				$sql = 'SELECT *
					FROM ' . FOOTB_LEAGUES . '
					WHERE season = ' . $season . ' 
					ORDER BY league ASC';
				$result = $db->sql_query($sql);

				$league_options = '';
				if (!$league)
				{
					$league_options = '<option value="0" selected="selected">' . sprintf($user->lang['NEW_LEAGUE']) . '</option>';
					$new_league = $xleague;
				}
				else
				{
					$league_options = '<option value="0">' . sprintf($user->lang['NEW_LEAGUE']) . '</option>';
					$new_league = $league;
				}
				while ($row = $db->sql_fetchrow($result))
				{
					// Grab current league
					$selected = ($league && $row['league'] == $league) ? ' selected="selected"' : '';
					$league_options .= '<option value="' . $row['league'] . '"' . $selected . '>' . $row['league_name'] . '</option>';
					if ($selected <> '')
					{
						$league_name 	  = $row['league_name'];
					}
				}
				$db->sql_freeresult($result);
				
				$choose_league = true;
				$list = false;
			break;
			
			case 'team_mapping':
			case 'map_teams':
			case 'load_xml_league':
				if ($xseason == 0 or $xleague == 0) 
				{
					trigger_error(sprintf($user->lang['NO_XML_SEASON']) . adm_back_link($this->u_action), E_USER_WARNING);
				}
				
				if ($season == 0)
				{
					// No Season exists
					$season = $xseason;
					$insert_season = true;
					// No league exists, so we want to insert the source league
					$league = $xleague;
					$insert_league = true;
				}
				else
				{
					if ($league == 0)
					{
						// New league so try to create the source league
						$league = ($new_league) ? $new_league : $xleague;
						$insert_league = true;
						// Selected source league exist 
						if (sizeof(league_info($season, $league)))
						{
							trigger_error(sprintf($user->lang['NEW_LEAGUE_EXIST'], $league) . adm_back_link($this->u_action), E_USER_WARNING);
						}
					}
				}

				if (!$xml_season_url or strtoupper($xml_season_url) == 'LOCALHOST')
				{
					$xml_season_url = 'localhost';
					$xml_league_url = $phpbb_root_path . '/store/league_' . $xseason . '_' . $xleague . '.xml';
				}
				else
				{
					$xml_league_url = substr($xml_season_url, 0, strrpos($xml_season_url, "/") + 1) . 
									"league.php?season=" . $xseason . "&league=" . $xleague . "&code=" . $xcode;
				}
				$cache_league = 'football_xml_league_' . $xseason . '_' . $xleague;
				$this->xml_ary = $this->xml2array($xml_league_url, $cache_league);
				
				$team_id_map_ary = array();
				$duplicate_team_ids_ary = array();
				if ($action == 'map_teams')
				{	
					if ($this->check_teams($season, $league, $team_id_map_ary, $duplicate_team_ids_ary, $missing_team_ids_ary)) 
					{
						$action = 'team_mapping';					
					}
				}
				
				if ($action == 'team_mapping')
				{
					if ($this->compare_teams($season, $league, $team_id_map_ary, $duplicate_team_ids_ary)) 
					{
						$display_team_mapping = true;
						break;
					}
				}

				if (!$insert_league)
				{
					$league_info = league_info($season, $league);
					$sql = 'SELECT COUNT(matchday) as matchdays
							FROM ' . FOOTB_MATCHDAYS . " 
							WHERE season = $season 
							AND league = $league";
					$result = $db->sql_query($sql);
					if ($row = $db->sql_fetchrow($result))
					{
						if ($row['matchdays'] <> $league_info['matchdays'])
						{
							$error[] = sprintf($user->lang['MISMATCH_MATCHDAYS'], $row['matchdays']);
						}
					}
					else
					{
						$error[] = sprintf($user->lang['MISMATCH_MATCHDAYS'], $row['matchdays']);
					}
					$db->sql_freeresult($result);
					
					$sql = 'SELECT COUNT(match_no) as matches
							FROM ' . FOOTB_MATCHES . " 
							WHERE season = $season 
							AND league = $league";
					$result = $db->sql_query($sql);
					if ($row = $db->sql_fetchrow($result))
					{
						if ($row['matches'] <> sizeof($this->xml_ary['footb_matches']))
						{
							$error[] = sprintf($user->lang['MISMATCH_MATCHES'], $row['matches']);
						}
					}
					else
					{
						$error[] = sprintf($user->lang['MISMATCH_MATCHES'], $row['matches']);
					}
					$db->sql_freeresult($result);
					
					if (!sizeof($error))
					{
						// Compare Update with existing database and switch season and league
						$this->compare_table('FOOTB_LEAGUES', $season, $league, '', true, $error);				
						$this->compare_table('FOOTB_MATCHDAYS', $season, $league, '', true, $error);				
						$this->compare_table('FOOTB_TEAMS', $season, $league, '', true, $error);				
						$this->compare_table('FOOTB_MATCHES', $season, $league,'', true, $error);	
					}
				}
				if (!sizeof($error))
				{
					foreach ($this->xml_ary['footb_teams'] AS $key => $value) 
					{
						$this->team_ary[$value['team_id']] = $value['team_name'];
					}
					$this->team_ary[0] = '';
					
					$choose_league = false;
					$list = true;
					if ($insert_season)
					{
						// Set default values for new season
						$this->xml_ary[strtolower('footb_seasons')][0]['season'] = "$season";
						$this->xml_ary[strtolower('footb_seasons')][0]['season_name'] = sprintf($user->lang['SEASON']) . " " . ($season - 1) . "/" . $season;
						$this->xml_ary[strtolower('footb_seasons')][0]['season_name_short'] = $season - 1 . "/" . $season;
						$this->show_xml($this->xml_ary, 'FOOTB_SEASONS', $season, 0);						
					}
					if ($insert_league)
					{
						// Show complete update
						$this->show_xml($this->xml_ary, 'FOOTB_LEAGUES', $season, $league);						
						$this->show_xml($this->xml_ary, 'FOOTB_MATCHDAYS', $season, $league);						
						$this->show_xml($this->xml_ary, 'FOOTB_TEAMS', $season, $league);						
						$this->show_xml($this->xml_ary, 'FOOTB_MATCHES', $season, $league);						
					}
					else
					{
						// Display differences between update and database
						$this->compare_table('FOOTB_MATCHDAYS', $season, $league, 'matchday', false, $error);				
						$this->compare_table('FOOTB_TEAMS', $season, $league, 'team_id', false, $error);				
						$this->compare_table('FOOTB_MATCHES', $season, $league, 'match_no', false, $error);				
					}
				}
			break;
			case 'insert':
				if ($insert_season)
				{
					if ($count_inserts = $this->insert_league('FOOTB_SEASONS'))
					{
						$success[] = sprintf($user->lang['DB_INSERT_SEASON'], $count_inserts);
					}
				}
				if ($count_inserts = $this->insert_league('FOOTB_LEAGUES'))
				{
					$success[] = sprintf($user->lang['DB_INSERT_LEAGUE'], $count_inserts);
				}
				if ($count_inserts = $this->insert_league('FOOTB_MATCHDAYS'))
				{
					$success[] = sprintf($user->lang['DB_INSERT_MATCHDAY' . (($count_inserts == 1) ? '' : 'S')], $count_inserts);
				}
				if ($count_inserts = $this->insert_league('FOOTB_TEAMS'))
				{
					$success[] = sprintf($user->lang['DB_INSERT_TEAM' . (($count_inserts == 1) ? '' : 'S')], $count_inserts);
				}
				if ($count_inserts = $this->insert_league('FOOTB_MATCHES'))
				{
					$success[] = sprintf($user->lang['DB_INSERT_MATCH' . (($count_inserts == 1) ? '' : 'ES')], $count_inserts);
				}
				$log_message = implode('<br />', $success);
				$phpbb_log->add('admin', $user->data['user_id'], $user->ip, 'LOG_FOOTB_UPDATE', false, array($league_name . ' ' . $season . '<br />' . $log_message));
			break;
			case 'update':
				if ($count_updates = $this->update_league('FOOTB_MATCHDAYS', $season, $league, 'matchday'))
				{
					$success[] = sprintf($user->lang['DB_UPDATE_MATCHDAY' . (($count_updates == 1) ? '' : 'S')], $count_updates);
				}
				if ($count_updates = $this->update_league('FOOTB_TEAMS', $season, $league, 'team_id'))
				{
					$success[] = sprintf($user->lang['DB_UPDATE_TEAM' . (($count_updates == 1) ? '' : 'S')], $count_updates);
				}
				if ($count_updates = $this->insert_new_teams($season, $league))
				{
					$success[] = sprintf($user->lang['DB_INSERT_TEAM' . (($count_updates == 1) ? '' : 'S')], $count_updates);
				}
				if ($count_updates = $this->update_league('FOOTB_MATCHES', $season, $league, 'match_no'))
				{
					$success[] = sprintf($user->lang['DB_UPDATE_MATCH' . (($count_updates == 1) ? '' : 'ES')], $count_updates);
				}
				
				// set delivery for bet in time
				$league_info = league_info($season, $league);
				if ($league_info['bet_in_time'])
				{
					$sql = 'REPLACE INTO ' . FOOTB_MATCHDAYS . " (season, league, matchday, delivery_date, delivery_date_2, delivery_date_3, matchday_name, matches)
							SELECT m.season, m.league, m.matchday, min(m.match_datetime) AS delivery_date, '' AS delivery_date_2, '' AS delivery_date_3, md.matchday_name, md.matches 
							FROM " . FOOTB_MATCHES . ' AS m
							JOIN ' . FOOTB_LEAGUES . ' AS l ON (l.season = m.season AND l.league = m.league)
							JOIN ' . FOOTB_MATCHDAYS . " AS md ON (md.season = m.season AND md.league = m.league AND md.matchday = m.matchday)
							WHERE m.season = $season 
							AND m.league = $league
							AND l.bet_in_time = 1 
							AND m.status = 0 
							GROUP BY m.season, m.league, m.matchday";
					$db->sql_query($sql);
					$count_updates = $db->sql_affectedrows();
					if ($count_updates)
					{
						$success[] =  sprintf($user->lang['DB_UPDATE_BIT_DELIVER' . (($count_updates == 1) ? '' : 'IES')], $count_updates) ;
					}
				}
				else
				{
					// set first delivery
					$effected_matchdays = '';
					$count_updates = 0;
					$sql = 'SELECT md.matchday
							FROM ' . FOOTB_MATCHDAYS . ' AS md 
							INNER JOIN (SELECT season, league, matchday, min(match_datetime) AS min_delivery_date 
									FROM ' . FOOTB_MATCHES . " 
									WHERE season = $season AND league = $league AND status = 0
									GROUP BY season, league, matchday) AS agg
							WHERE md.season = agg.season 
							AND md.league = agg.league 
							AND md.matchday = agg.matchday 
							AND md.delivery_date > agg.min_delivery_date
							ORDER BY md.matchday";
					$result = $db->sql_query($sql);
					while ($row = $db->sql_fetchrow($result))
					{
						$count_updates++;
						$effected_matchdays = ($effected_matchdays == '') ? $row['matchday'] : $effected_matchdays . ', ' . $row['matchday'];
					}
					$db->sql_freeresult($result);
					if ($effected_matchdays <> '')
					{
						$sql = 'REPLACE INTO ' . FOOTB_MATCHDAYS . ' (season, league, matchday, status, delivery_date, delivery_date_2, delivery_date_3, matchday_name, matches)
								SELECT md.season
									, md.league
									, md.matchday
									, md.status
									, min_delivery_date AS delivery_date
									, md.delivery_date_2
									, md.delivery_date_3
									, md.matchday_name
									, md.matches 
								FROM ' . FOOTB_MATCHDAYS . ' AS md 
								INNER JOIN (SELECT season, league, matchday, min(match_datetime) AS min_delivery_date 
										FROM ' . FOOTB_MATCHES . " 
										WHERE season = $season AND league = $league AND status = 0
										GROUP BY season, league, matchday) AS agg
								WHERE md.season = agg.season 
								AND md.league = agg.league 
								AND md.matchday = agg.matchday 
								AND md.delivery_date > agg.min_delivery_date";
						$db->sql_query($sql);
						$success[] =  sprintf($user->lang['DB_UPDATE_DELIVER' . (($count_updates == 1) ? '' : 'IES')], $effected_matchdays) ;
					}
					// set second delivery
					$effected_matchdays = '';
					$count_updates = 0;
					$sql = 'SELECT md.matchday
							FROM ' . FOOTB_MATCHDAYS . ' AS md 
							INNER JOIN (SELECT season, league, matchday, min(match_datetime) AS min_delivery_date 
									FROM ' . FOOTB_MATCHES . " 
									WHERE season = $season AND league = $league AND status = -1
									GROUP BY season, league, matchday) AS agg
							WHERE md.season = agg.season 
							AND md.league = agg.league 
							AND md.matchday = agg.matchday 
							AND md.delivery_date_2 > agg.min_delivery_date
							ORDER BY md.matchday";
					$result = $db->sql_query($sql);
					while ($row = $db->sql_fetchrow($result))
					{
						$count_updates++;
						$effected_matchdays = ($effected_matchdays == '') ? $row['matchday'] : $effected_matchdays . ', ' . $row['matchday'];
					}
					$db->sql_freeresult($result);
					if ($effected_matchdays <> '')
					{
						$sql = 'REPLACE INTO ' . FOOTB_MATCHDAYS . ' (season, league, matchday, status, delivery_date, delivery_date_2, delivery_date_3, matchday_name, matches)
								SELECT md.season
									, md.league
									, md.matchday
									, md.status
									, md.delivery_date
									, min_delivery_date AS delivery_date_2
									, md.delivery_date_3
									, md.matchday_name
									, md.matches 
								FROM ' . FOOTB_MATCHDAYS . ' AS md 
								INNER JOIN (SELECT season, league, matchday, min(match_datetime) AS min_delivery_date 
										FROM ' . FOOTB_MATCHES . " 
										WHERE season = $season AND league = $league AND status = -1
										GROUP BY season, league, matchday) AS agg
								WHERE md.season = agg.season 
								AND md.league = agg.league 
								AND md.matchday = agg.matchday 
								AND md.delivery_date_2 > agg.min_delivery_date";
						$db->sql_query($sql);
						$success[] =  sprintf($user->lang['DB_UPDATE_DELIVER' . (($count_updates == 1) ? '' : 'IES')], $effected_matchdays) ;
					}
					// set third delivery
					$effected_matchdays = '';
					$count_updates = 0;
					$sql = 'SELECT md.matchday
							FROM ' . FOOTB_MATCHDAYS . ' AS md 
							INNER JOIN (SELECT season, league, matchday, min(match_datetime) AS min_delivery_date 
									FROM ' . FOOTB_MATCHES . " 
									WHERE season = $season AND league = $league AND status = -2
									GROUP BY season, league, matchday) AS agg
							WHERE md.season = agg.season 
							AND md.league = agg.league 
							AND md.matchday = agg.matchday 
							AND md.delivery_date_3 > agg.min_delivery_date
							ORDER BY md.matchday";
					$result = $db->sql_query($sql);
					while ($row = $db->sql_fetchrow($result))
					{
						$count_updates++;
						$effected_matchdays = ($effected_matchdays == '') ? $row['matchday'] : $effected_matchdays . ', ' . $row['matchday'];
					}
					$db->sql_freeresult($result);
					if ($effected_matchdays <> '')
					{
						$sql = 'REPLACE INTO ' . FOOTB_MATCHDAYS . ' (season, league, matchday, status, delivery_date, delivery_date_2, delivery_date_3, matchday_name, matches)
								SELECT md.season
									, md.league
									, md.matchday
									, md.status
									, md.delivery_date
									, md.delivery_date_2
									, min_delivery_date AS delivery_date_3
									, md.matchday_name
									, md.matches 
								FROM ' . FOOTB_MATCHDAYS . ' AS md 
								INNER JOIN (SELECT season, league, matchday, min(match_datetime) AS min_delivery_date 
										FROM ' . FOOTB_MATCHES . " 
										WHERE season = $season AND league = $league AND status = -2
										GROUP BY season, league, matchday) AS agg
								WHERE md.season = agg.season 
								AND md.league = agg.league 
								AND md.matchday = agg.matchday 
								AND md.delivery_date_3 > agg.min_delivery_date";
						$db->sql_query($sql);
						$success[] =  sprintf($user->lang['DB_UPDATE_DELIVER' . (($count_updates == 1) ? '' : 'IES')], $effected_matchdays) ;
					}
				}
					
				// check status of matchdays
				$local_board_time = time() + ($this->config['football_time_shift'] * 3600); 
				$sql = $sql = 'UPDATE ' . FOOTB_MATCHDAYS . " AS target 
						INNER JOIN  
						( 
						SELECT md.season
							, md.league
							, md.matchday
							, IF( md.delivery_date > now(), 
								0, 
								IF(ISNULL(min(e.extra_status)), 
									IF(min(m.status) = 1 AND max(m.status) > 1,
										2,
										GREATEST(min(m.status), 0)),
									IF(LEAST(min(m.status), min(e.extra_status)) = 1 AND GREATEST(max(m.status), max(e.extra_status)) > 1,
										2,
										GREATEST(LEAST(min(m.status), min(e.extra_status)), 0)))) As new_status
						FROM " . FOOTB_MATCHDAYS . ' AS md 
						LEFT JOIN ' . FOOTB_MATCHES . ' AS m ON (m.season = md.season AND m.league = md.league AND m.matchday = md.matchday)
						LEFT JOIN ' . FOOTB_EXTRA . " AS e ON (e.season = md.season AND e.league = md.league AND e.matchday_eval = md.matchday)
						WHERE md.season = $season AND md.league = $league
						GROUP BY md.season, md.league, md.matchday) AS source
						ON target.season = source.season AND target.league = source.league AND target.matchday = source.matchday
						SET target.status = source.new_status";
				$db->sql_query($sql);
				$count_updates = $db->sql_affectedrows();
				if ($count_updates)
				{
					$success[] =  sprintf($user->lang['DB_UPDATE_STATUS_MATCHDAY' . (($count_updates == 1) ? '' : 'S')], $count_updates);
				}
				if (sizeof($success))
				{
					$cash = ($phpbb_extension_manager->is_enabled('dmzx/ultimatepoints')) ? true : false;
					save_ranking_matchday($season, $league, 1, $cash);
					$log_message = implode('<br />', $success);
					$phpbb_log->add('admin', $user->data['user_id'], $user->ip, 'LOG_FOOTB_UPDATE', false, array($league_info['league_name'] . ' ' . $season . '<br />' . $log_message));
				}
				else
				{
					$success[] = sprintf($user->lang['NO_DB_CHANGES']);
					$phpbb_log->add('admin', $user->data['user_id'], $user->ip, 'LOG_FOOTB_UPDATE', false, array($success[0]));
				}
			break;
		}
		$allow_url_fopen = (int) @ini_get('allow_url_fopen');
		if ($allow_url_fopen)
		{
			if (trim($this->config['football_update_source']) == '')
			{
				$xml_season_url = ($xml_season_url == '') ? 'http://football.bplaced.net/ext/football/football/xml/seasons.php' : $xml_season_url;
			}
			else
			{
				$xml_season_url = trim($this->config['football_update_source']);
			}
		}
		else
		{
			$xml_season_url = 'localhost';
		}

		$template->assign_vars(array(
			'U_ACTION'				=> $this->u_action,
			'U_CHOOSE_ACTION'		=> $this->u_action . "&amp;action=choose",			
			'U_FOOTBALL' 			=> $helper->route('football_football_controller',array('side' => 'bet', 's' => $season, 'l' => $league)),
			'S_ERROR'				=> (sizeof($error)) ? true : false,
			'ERROR_MSG'				=> (sizeof($error)) ? implode('<br />', $error) : '',
			'S_SUCCESS'				=> (sizeof($success)) ? true : false,
			'SUCCESS_MSG'			=> (sizeof($success)) ? implode('<br />', $success) : '',
			'S_MISSING_TEAMS'		=> (sizeof($missing_team_ids_ary)) ? sprintf($user->lang['MISSING_TEAMS'], implode(', ', $missing_team_ids_ary)) : '',
			'S_ALLOW_URL_FOPEN'		=> ($allow_url_fopen) ? true : false,
			'S_CHOOSE'				=> $choose_league,
			'S_TEAMS'				=> $display_team_mapping,
			'S_LIST'				=> $list,
			'S_INSERT_SEASON'		=> $insert_season,
			'S_INSERT_LEAGUE'		=> $insert_league,
			'S_CHECK_SAME_STATUS'	=> '',
			'S_CHECK_ONLY_FINAL'	=> 'checked="checked"',
			'S_VERSION_NO'			=> $this->config['football_version'],
			'DO_MATCHDAYS'			=> ($insert_league) ? sprintf($user->lang['INSERT_MATCHDAYS']) : sprintf($user->lang['UPDATE_MATCHDAYS']),
			'DO_TEAMS'				=> ($insert_league) ? sprintf($user->lang['INSERT_TEAMS']) : sprintf($user->lang['UPDATE_TEAMS']),
			'DO_MATCHES'			=> ($insert_league) ? sprintf($user->lang['INSERT_MATCHES']) : sprintf($user->lang['UPDATE_MATCHES']),
			'XML_SEASON_URL'		=> $xml_season_url,
			'XML_LEAGUE_URL'		=> $xml_league_url,
			'XML_ARY'				=> (is_array($this->xml_ary) && sizeof($this->xml_ary)) ? urlencode(json_encode($this->xml_ary)) : '',
			'S_XSEASON_OPTIONS'		=> $xseason_options,
			'S_XLEAGUE_OPTIONS'		=> $xleague_options,
			'S_XSEASON'				=> $xseason,
			'S_XLEAGUE'				=> $xleague,
			'S_XCODE'				=> $xcode,
			'S_SEASON_OPTIONS'		=> $season_options,
			'S_LEAGUE_OPTIONS'		=> $league_options,
			'S_SEASON'				=> $season,
			'S_LEAGUE'				=> $league,
			'S_LEAGUE_NAME'			=> $league_name,
			'NEW_LEAGUE'			=> $new_league,
			)
		);
	}

	function xml2array($xml_league_url, $cache_league)
	{
		global $cache;
				
		if (!($xml_str = $cache->get($cache_league)))
		{
			if ($xml_str = @file_get_contents($xml_league_url))
			{
				if (strpos($xml_str, '<league-data xmlns'))
				{
					$cache->put($cache_league, $xml_str, 900);
				}
				else
				{
					trigger_error(sprintf($user->lang['ERROR_READ_LEAGUE_XML']) . adm_back_link($this->u_action), E_USER_WARNING);
				}
			}
			else
			{
				trigger_error(sprintf($user->lang['ERROR_OPEN_LEAGUE_XML']) . adm_back_link($this->u_action), E_USER_WARNING);
			} 						
		}
		$xml_league = new \SimpleXMLElement($xml_str); 

		$xml_table = array();
		foreach ($xml_league->children() AS $node) 
		{
			$xml_entry = array();
			foreach($node->children() AS $cnode) 
			{
				$xml_entry[$cnode->getName()] =  sprintf("%s", $cnode);
			}
			$xml_table[$node->getName()][] = $xml_entry;
			$this->xml_ary[$node->getName()] = $xml_table[$node->getName()];
		}
		return $this->xml_ary;
	}

	function check_teams($season, $league, &$team_id_map_ary, &$duplicate_team_ids_ary, &$missing_team_ids_ary)
	{
		global $config, $db, $user, $auth, $template, $cache;
		global $phpbb_root_path, $phpbb_admin_path, $phpEx;

		$team_id_ary = array();
		foreach ($this->xml_ary['footb_teams'] AS $xml_team)
		{
			$new_team_id = $this->request->variable('team_id_db_' . $xml_team['team_id'], 0);
			$team_id_map_ary[$xml_team['team_id']] = $new_team_id;
			if (in_array($new_team_id, $team_id_ary) or $new_team_id == 0)
			{
				$duplicate_team_ids_ary[] = $new_team_id;
			}
			else
			{
				$team_id_ary[] = $new_team_id;
			}
		}
		$table_name = constant('FOOTB_TEAMS');
		// All database teams selected?
		$sql = 'SELECT  team_id
				FROM ' . $table_name . "
				WHERE season =  $season 
				AND league = $league 
				ORDER BY team_id";
		$result = $db->sql_query($sql);
		while ($row = $db->sql_fetchrow($result))
		{
			if (!in_array($row['team_id'], $team_id_ary))
			{
				$missing_team_ids_ary[] = $row['team_id'];
			}
		}
		$db->sql_freeresult($result);
		if (sizeof($duplicate_team_ids_ary) or sizeof($missing_team_ids_ary))
		{
			// Display Mapping
			return true;
		}
		else
		{
			// set new IDs in xml_ary
			foreach ($this->xml_ary['footb_teams'] AS $key => $xml_team)
			{
				$this->xml_ary['footb_teams'][$key]['team_id'] = $team_id_map_ary[$xml_team['team_id']];
			}
			usort($this->xml_ary['footb_teams'], array($this, 'sort_teams'));

			foreach ($this->xml_ary['footb_matches'] AS $key => $xml_team)
			{
				$this->xml_ary['footb_matches'][$key]['team_id_home'] = $team_id_map_ary[$xml_team['team_id_home']];
				$this->xml_ary['footb_matches'][$key]['team_id_guest'] = $team_id_map_ary[$xml_team['team_id_guest']];
			}
			return false;
		}
	}
	
	
	function compare_teams($season, $league, $team_id_map_ary, $duplicate_team_ids_ary)
	{
		global $config, $db, $user, $auth, $template, $cache;
		global $phpbb_root_path, $phpbb_admin_path, $phpEx;

		$i = 0;
		$j = 0;
		$db_teams = array();
		$different_teams = false;
		$same_quantity = false;
		$table_name = constant('FOOTB_TEAMS');
		// Grab basic data for select league
		$sql = 'SELECT  team_id
						, team_name
						, team_name_short
						, team_symbol
				FROM ' . $table_name . "
				WHERE season =  $season 
				AND league = $league 
				ORDER BY team_name";
		$result = $db->sql_query($sql);
		$rows = $db->sql_fetchrowset($result);
		$db->sql_freeresult($result);
		$same_quantity = (sizeof($rows) == sizeof($this->xml_ary['footb_teams'])) ? true : false;

		if ($same_quantity)
		{
			foreach ($rows AS $row)
			{
				$db_teams[$row['team_id']] = $row;
			}
		}
		else
		{
			$different_teams = true;
			$sql = 'SELECT DISTINCT team_id
						, team_name
						, team_name_short
						, team_symbol
					FROM ' . $table_name . "
					ORDER BY team_name";
			$result = $db->sql_query($sql);
			while ($row = $db->sql_fetchrow($result))
			{
				$db_teams[$row['team_id']] = $row;
			}
			$db->sql_freeresult($result);
		}
		$row_number = 0;
		foreach ($this->xml_ary['footb_teams'] AS $xml_team)
		{
			$team_options = '';
			$team_id = (sizeof($team_id_map_ary)) ? $team_id_map_ary[$xml_team['team_id']] : $xml_team['team_id'];
			if (!$same_quantity)
			{
				if (array_key_exists($team_id, $db_teams))
				{
					$team_options = '<option value="' . $xml_team['team_id'] . '">(' . $xml_team['team_id'] . ') ' . sprintf($user->lang['TRANSFER_TEAM']) . '</option>';
				}
				else
				{
					$team_options = '<option value="' . $xml_team['team_id'] . '" selected="selected">(' . $xml_team['team_id'] . ') ' . sprintf($user->lang['TRANSFER_TEAM']) . '</option>';
				}
			}
			foreach ($db_teams AS $db_team_id => $db_team_data)
			{
				$selected = ($db_team_id == $team_id) ? ' selected="selected"' : '';
				$team_options .= '<option value="' . $db_team_id . '"' . $selected . '>' . '(' . $db_team_id . ') ' . $db_team_data['team_name'] . '</option>';
			}
			if (!array_key_exists($team_id, $db_teams) or sizeof($duplicate_team_ids_ary))
			{
				$different_teams = true;
			}
			$row_number++;
			$row_class = (!($row_number % 2)) ? 'bg1 row_light' : 'bg2 row_dark';
			$template->assign_block_vars('teams', array(
				'ROW_CLASS'				=> $row_class,
				'TEAM_ID_XML'			=> $xml_team['team_id'],
				'TEAM_IMAGE_XML'		=> ($xml_team['team_symbol']) ? $this->ext_football_path . 'images/flags/' . $xml_team['team_symbol'] : $phpbb_root_path . 'football/images/flags/blank.gif',
				'TEAM_NAME_XML'			=> $xml_team['team_name'],
				'TEAM_NAME_SHORT_XML'	=> $xml_team['team_name_short'],
				'TEAM_OPTIONS'			=> $team_options,
				'DUPLICATE_TEAM'		=> (in_array($team_id, $duplicate_team_ids_ary)) ? true : false,
				
			));
		}
		if ($different_teams)
		{
			// Display Mapping
			return true;
		}
		else
		{
			return false;
		}
	}

	function compare_table($table, $season, $league, $index_field, $check, &$error)
	{
		global $config, $db, $user, $auth, $template, $cache;
		global $phpbb_root_path, $phpbb_admin_path, $phpEx;

		$i = 0;
		$j = 0;
		$tpl = '';
		$order = ($table == 'FOOTB_MATCHES') ? array('season' => 0, 'league' => 0, 'match_no' => 1, 'team_id_home' => 4, 'team_id_guest' => 5, 
													'goals_home' => 6, 'goals_guest' => 7, 'matchday' => 0, 'status' => 12, 'match_datetime' => 2, 
													'group_id' => 3, 'formula_home' => 10, 'formula_guest' => 11, 'ko_match' => 13, 
													'goals_overtime_home' => 8, 'goals_overtime_guest' => 9) : array();
		$table_name = constant($table);
		// Grab basic data for select league
		$sql = 'SELECT *
			FROM ' . $table_name . "
			WHERE season =  $season 
			AND league = $league 
			ORDER BY 1, 2, 3";
		$result = $db->sql_query($sql);
		while ($row = $db->sql_fetchrow($result))
		{
			if ($table == 'FOOTB_TEAMS')
			{
				while ($i < sizeof($this->xml_ary[strtolower($table)]) and $this->xml_ary[strtolower($table)][$i]['team_id'] <> $row['team_id'])
				{
					// New team
					if ($check)
					{
						$this->xml_ary[strtolower($table)][$i]['season'] = "$season";
						$this->xml_ary[strtolower($table)][$i]['league'] = "$league";
					}
					else
					{
						$row_class = (!($j % 2)) ? 'bg1 row_light' : 'bg2 row_dark';
						$id = 'insert_team';
						$tpl .= $this->print_differences(array(), $this->xml_ary[strtolower($table)][$i], $this->xml_ary[strtolower($table)][$i], $id, $row_class, array());
						$j++;
					}
					$i++;
				}
			}
			$diff = array_diff_assoc($this->xml_ary[strtolower($table)][$i], $row);
			if ($check)
			{
				foreach ($diff AS $key => $value) 
				{
					switch($key)
					{
						case 'league':
							$this->xml_ary[strtolower($table)][$i]['league'] = "$league";
						break;
						case 'league_type':
							$error[] = sprintf($user->lang['MISMATCH_LEAGUE_TYPE'], $value);
						break;
						case 'matchdays':
							$error[] = sprintf($user->lang['MISMATCH_MATCHDAYS'], $value);
						break;
						case 'matches_on_matchday':
							$error[] = sprintf($user->lang['MISMATCH_MOM'], $value);
						break;
						case 'matches':
							$error[] = sprintf($user->lang['MISMATCH_MATCHES'], $value);
						break;
						case 'season':
							$this->xml_ary[strtolower($table)][$i]['season'] = "$season";
						break;
					}
				}
			}
			else
			{
				if (sizeof($diff)) 
				{
					$row_class = (!($j % 2)) ? 'bg1 row_light' : 'bg2 row_dark';
					$id = $table . '_' . $row[$index_field];
					$tpl .= $this->print_differences($row, $this->xml_ary[strtolower($table)][$i], $diff, $id, $row_class, $order);
					$j++;
				}			
			}
			$i++;
		}
		$db->sql_freeresult($result);
		if (!$check and $tpl <> '')
		{
			$template->assign_block_vars(strtolower($table), array(
				'TPL'			=> $tpl,
			));
		}
	}

	
	function show_xml(&$xml_ary, $table, $season, $league)
	{
		global $config, $db, $user, $auth, $template, $cache;
		global $phpbb_root_path, $phpbb_admin_path, $phpEx;

		$j = 0;
		$tpl = '';
		switch ($table)
		{
			case 'FOOTB_LEAGUES':
				$order = array('season' => 0, 'league' => 0, 'league_name' => 0, 'league_name_short' => 2, 'league_type' => 4, 
								'matchdays' => 6, 'matches_on_matchday' => 8, 'win_result' => 10, 'win_result_02' => 10, 'win_matchday' => 10, 
								'win_season' => 10, 'points_mode' => 10, 'points_result' => 12, 'points_tendency' => 12, 
								'points_diff' => 12, 'points_last' => 12, 'join_by_user' => 12, 'join_in_season' => 12, 
								'bet_in_time' => 12, 'rules_post_id' => 14, 'bet_ko_type' => 14, 'bet_points' => 16);
			break;
			case 'FOOTB_MATCHES':
				$order = array('season' => 0, 'league' => 0, 'match_no' => 1, 'team_id_home' => 4, 'team_id_guest' => 5, 
								'goals_home' => 6, 'goals_guest' => 7, 'matchday' => 0, 'status' => 12, 'match_datetime' => 2, 
								'group_id' => 3, 'formula_home' => 10, 'formula_guest' => 11, 'ko_match' => 13, 
								'goals_overtime_home' => 8, 'goals_overtime_guest' => 9);
			break;
			default:
				 $order = array();
		}
		foreach ($this->xml_ary[strtolower($table)] AS $key => $xml_row)
		{
			$this->xml_ary[strtolower($table)][$key]['season'] = "$season";
			if ($league)
			{
				$this->xml_ary[strtolower($table)][$key]['league'] = "$league";
			}
			$row_class = (!($j % 2)) ? 'bg1 row_light' : 'bg2 row_dark';
			$tpl .= $this->print_xml_data($xml_row, $row_class, $order);
			$j++;
		}
		$template->assign_block_vars(strtolower($table), array(
			'TPL'			=> $tpl,
		));
	}

	
	/**
	* Print differences beetween Database and XML-Data
	*/
	function print_differences($table_row, $xml_row, $diff, $id, $row_class, $order)
	{
		global $user, $league_info;

		if (sizeof($table_row) AND substr($id, 0, 11) == 'FOOTB_TEAMS')
		{
			if ($xml_row['team_symbol'] == '')
			{
				unset($diff['team_symbol']);
				if (!sizeof($diff))
				{	
					return '';
				}
			}
		}
		if (sizeof($table_row) AND substr($id, 0, 13) == 'FOOTB_MATCHES')
		{
			if ($xml_row['status'] > 0 AND ($xml_row['status'] == $table_row['status'] - 3))
			{
				unset($diff['status']);
				if (!sizeof($diff))
				{	
					return '';
				}
			}
			if ($xml_row['status'] < 0 AND $league_info['bet_in_time'])
			{
				if ($table_row['status'] == 0)
				{
					unset($diff['status']);
					if (!sizeof($diff))
					{	
						return '';
					}
				}
				else
				{
					$diff['status'] = 0;
				}
			}
		}
		$tpl = '';
		$tpl_ary = array();
		if (sizeof($diff))
		{
			if (sizeof($table_row))
			{
				$tpl .= '<input type="hidden" name="row_' . $id . '" value="' . urlencode(json_encode($diff)) . '" />';
				// match status update and database
				if (substr($id, 0, 13) == 'FOOTB_MATCHES')
				{
					$tpl .= '<input type="hidden" name="status_' . $id . '" value="' . $xml_row['status'] . '" />';
					$tpl .= '<input type="hidden" name="db_status_' . $id . '" value="' . $table_row['status'] . '" />';
					if ($xml_row['status'] > 0 AND ($xml_row['status'] == $table_row['status'] +3))
					{
						unset($diff['status']);
					}
				}
				
			}
			else
			{
				// Insert team
				$tpl .= '<input type="hidden" name="' . $id . '[]" value="' . urlencode(json_encode($diff)) . '" />';
			}
		}

		$tpl .= '	<tr class="' . $row_class . '">';
		foreach ($xml_row AS $key => $value)
		{
			if (array_key_exists($key, $diff))
			{
				if (sizeof($table_row))
				{
					$color_open = '<span title= "' . sprintf($user->lang['CURRENT_VALUE']) . ': ' . utf8_htmlspecialchars($table_row[$key]) . '" style="color: red;">* ';
					$color_close = '</span>';
				}
				else
				{
					$color_open = '<span title= "' . sprintf($user->lang['NEW_TEAM']) . '" style="color: red;">* ';
					$color_close = '</span>';
				}
			}
			else
			{
				$color_open = '';
				$color_close = '';
			}
			if (sizeof($order))
			{
				$value = (substr($key, 0, 7) == 'team_id') ? $value . ' ' . $this->team_ary[$value] : $value;
				$tpl_ary[$order[$key]] = ($order[$key] % 2) ? $color_open . utf8_htmlspecialchars($value) . $color_close . '&nbsp;</td>' :
				'<td>' . $color_open . utf8_htmlspecialchars($value) . $color_close . '<br />';			
			}
			else
			{
				if ($key <> 'season' and $key <> 'league')
				{
					// Write table fields
					if (sizeof($table_row))
					{
						$tpl .= '<td title= "' . utf8_htmlspecialchars($table_row[$key]) . '">' .
							$color_open . utf8_htmlspecialchars($value) . $color_close . 
								'</td>';		
					}
					else
					{
						$tpl .= '<td title= "' . sprintf($user->lang['NEW_TEAM']) . '">' .
							$color_open . utf8_htmlspecialchars($value) . $color_close . 
								'</td>';		
					}
				}
			}
		}
		if (sizeof($order))
		{
			ksort($tpl_ary);
			$tpl .= implode(" ", $tpl_ary);
		}
		if (sizeof($table_row))
		{
			$tpl .= '<td style="text-align:center"><input name="' . $id . '" id="' . $id . '" type="checkbox" class="radio" checked="checked" /></td>	</tr>';		
		}
		else
		{
			$tpl .= '<td style="text-align:center"><input name="' . $id . '" type="checkbox" class="radio" checked="checked" disabled /></td></tr>';		
		}

		return $tpl;
	}

	
	/**
	* Print differences beetween Database and XML-Data
	*/
	function print_xml_data($xml_row, $row_class, $order)
	{
		global $user;

		$tpl = '';
		$tpl_ary = array();

		$tpl .= '<tr class="' . $row_class . '">';
		foreach ($xml_row AS $key => $value)
		{
			if (sizeof($order))
			{
				$value = (substr($key, 0, 7) == 'team_id') ? $value . ' ' . $this->team_ary[$value] : $value;
				$tpl_ary[$order[$key]] = ($order[$key] % 2) ? utf8_htmlspecialchars($value) . '&nbsp;</td>' :
				'<td>' . utf8_htmlspecialchars($value) . '<br />';			
			}
			else
			{
				if ($key <> 'season' and $key <> 'league')
				{
					// Write XML-table fields
					$tpl .= '<td>' . utf8_htmlspecialchars($value) . '</td>';
				}
			}
		}
		if (sizeof($order))
		{
			ksort($tpl_ary);
			$tpl .= implode(" ", $tpl_ary);
		}
			$tpl .= '	</tr>';				

		return $tpl;
	}
	
	/**
	* Insert table into database 
	*/
	function insert_league($table)
	{
		global $db, $user;
		
		$count_inserts = 0;
		$table_name = constant($table);
		foreach ($this->xml_ary[strtolower($table)] AS $sql_ary)
		{
			$sql = 'INSERT IGNORE INTO ' . $table_name . ' ' . $db->sql_build_array('INSERT', $sql_ary);
			$db->sql_query($sql);
			if ($db->sql_affectedrows())
			{
				$count_inserts++;
			}
		}
		return $count_inserts;
	}
	
	/**
	* Update database 
	*/
	function update_league($table, $season, $league, $index_field)
	{
		global $db, $user;
		
		$count_updates = 0;
		$table_name = constant($table);
		$selected_fields = $this->selected_fields($table);
		if (!sizeof($selected_fields))
		{
			return 0;
		}
		
		if ($table == 'FOOTB_MATCHES')
		{
			$update_neg_status = $this->request->variable('update_neg_status', false);
			$update_same_status = $this->request->variable('update_same_status', false);
			$update_only_final = $this->request->variable('update_only_final', false);
		}
		
		// Grab key data for update
		$sql = "SELECT $index_field AS index_field
			FROM " . $table_name . "
			WHERE season =  $season 
			AND league = $league 
			ORDER BY 1";
		$result = $db->sql_query($sql);
		while ($row = $db->sql_fetchrow($result))
		{
			if ($this->request->variable($table . '_' . $row['index_field'], false))
			{
				
				$diff_ary = json_decode(urldecode($this->request->variable('row_' . $table . '_' . $row['index_field'], '')),true);
				$sql_ary = array_intersect_ukey($diff_ary, $selected_fields, 'self::key_compare_func');
				if ($table == 'FOOTB_MATCHES')
				{
					$update_status = $this->request->variable('status_FOOTB_MATCHES_' . $row['index_field'], 0);
					$db_status = $this->request->variable('db_status_FOOTB_MATCHES_' . $row['index_field'], 0);
					switch ($update_status)
					{
						case -2:
						case -1:
							if (! $update_neg_status)
							{
								// remove resultfields and status
								$sql_ary = array_intersect_ukey($sql_ary, $this->no_result_fields, 'self::key_compare_func');
							}
						break;
						case 0:
						case 1:
							// remove resultfields and status
							$sql_ary = array_intersect_ukey($sql_ary, $this->no_result_fields, 'self::key_compare_func');
						break;
						case 2:
							if ($db_status <= 0 or $db_status == 3 or $db_status == 6 or $update_only_final or
								(($db_status == 2 or $db_status == 5 ) and ! $update_same_status) )
							{
								// remove provisional results and status
								$sql_ary = array_intersect_ukey($sql_ary, $this->no_result_fields, 'self::key_compare_func');
							}
							else
							{
								if ($db_status == 4 or $db_status == 5)
								{
									$sql_ary['status'] = 5;
								}
							}
						break;
						case 3:
							if (($db_status == 3 or $db_status == 6 ) and !$update_same_status)
							{
								// don't replace final results
								$sql_ary = array_intersect_ukey($sql_ary, $this->no_result_fields, 'self::key_compare_func');
							}
							else
							{
								if ($db_status > 3)
								{	
									$sql_ary['status'] = 6;
								}
							}
						break;
					}
				}
				if (sizeof($sql_ary))
				{
					$sql = 'UPDATE ' . $table_name . '
						SET ' . $db->sql_build_array('UPDATE', $sql_ary) . "
						WHERE season = $season AND league = $league AND  $index_field = " . $row['index_field'];
					$db->sql_query($sql);
					if ($db->sql_affectedrows())
					{
						$count_updates++;
					}
				}
			}
		}
		$db->sql_freeresult($result);
		return $count_updates;
	}

	/**
	* Insert new teams into database 
	*/
	function insert_new_teams($season, $league)
	{
		global $db, $user;
		
		$count_updates = 0;
		$table_name = constant('FOOTB_TEAMS');
		$insert_ary = $this->request->variable('insert_team', array(''));
		if (sizeof($insert_ary) == 0)
		{
			return 0;
		}
		foreach ($insert_ary AS $insert)
		{
			$sql_ary = json_decode(urldecode($insert),true);
			$sql = 'INSERT INTO ' . $table_name . ' ' . $db->sql_build_array('INSERT', $sql_ary);
			$db->sql_query($sql);
			if ($db->sql_affectedrows())
			{
				$count_updates++;
			}
		}
		return $count_updates;
	}

	
	/**
	* get selected fields 
	*/
	function selected_fields($table)
	{
		global $db, $user;
		
		$tablename = strtolower($table);
		$var_praefix = 'update_' . substr($tablename, 6) . '_';
		$selected_fields = array();
		$table_fields = array();
		
		// Grab fields of table
		$table_fields = $this->$tablename;
		foreach ($table_fields AS $table_field)
		{
			switch ($table_field)
			{
				case 'formula_home':
				case 'formula_guest':
					$tag_name = 'formula';
				break;
				case 'goals_home':
				case 'goals_guest':
					$tag_name = 'goals';
				break;
				case 'goals_overtime_home':
				case 'goals_overtime_guest':
					$tag_name = 'goals_overtime';
				break;
				default:
					$tag_name = $table_field;
				break;
			}
			if ($this->request->variable($var_praefix . $tag_name, false))
			{
				$selected_fields[$table_field] = 0;
			}
		}
		return $selected_fields;
	}
}
