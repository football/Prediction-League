<?php
/**
*
* @package Football Football v0.94
* @copyright (c) 2015 football (http://football.bplaced.net)
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace football\football\migrations;

class v094_beta extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return isset($this->config['football_version']) && version_compare($this->config['football_version'], '0.9.4', '>=');
	}
	
    // first try to update the old MOD and run this migration if update didn't change version number
	static public function depends_on()
	{
		return array('\football\football\migrations\v094_beta_update');
	}

	public function update_schema()
	{
		//Create the football tables
		return array(
			'add_columns' => array(
				$this->table_prefix . 'sessions' => array(
					'football_season' 			=> array('USINT', 0),
					'football_league' 			=> array('TINT:2', 0),
					'football_matchday' 		=> array('TINT:2', 0),
					'football_mobile'			=> array('UINT:1', 0),
					'football_mobile_device'	=> array('VCHAR:255', NULL),
				),
			),
			'add_tables' => array(
				$this->table_prefix . 'footb_bets' => array(
					'COLUMNS' => array(
						'season' 				=> array('USINT', 0),
						'league' 				=> array('TINT:2', 0),
						'match_no' 				=> array('USINT', 0),
						'user_id' 				=> array('UINT', 0),
						'goals_home' 			=> array('CHAR:2',''),
						'goals_guest' 			=> array('CHAR:2',''),
						'bet_time'				=> array('UINT:11', 0),
					),
					'PRIMARY_KEY'    		=> array('season', 'league', 'match_no', 'user_id'),
				),
				$this->table_prefix . 'footb_extra' => array(
					'COLUMNS' => array(
						'season' 				=> array('USINT', 0),
						'league' 				=> array('TINT:2', 0),
						'extra_no' 				=> array('USINT', 0),
						'question_type' 		=> array('TINT:2', 0),
						'question' 				=> array('VCHAR:255', ''),
						'result' 				=> array('VCHAR:255', ''),
						'matchday' 				=> array('TINT:2', 0),
						'matchday_eval' 		=> array('TINT:2', 0),
						'extra_points' 			=> array('TINT:2', 0),
						'extra_status' 			=> array('TINT:2', 0),
					),
					'PRIMARY_KEY'    		=> array('season', 'league', 'extra_no'),
					'KEYS'					=> array(
						'matchday'			=> array('INDEX', 'matchday'),
						'eval'				=> array('INDEX', 'matchday_eval'),
					),
				),
				$this->table_prefix . 'footb_extra_bets' => array(
					'COLUMNS' => array(
						'season' 				=> array('USINT', 0),
						'league' 				=> array('TINT:2', 0),
						'extra_no' 				=> array('USINT', 0),
						'user_id' 				=> array('UINT', 0),
						'bet' 					=> array('VCHAR:255', ''),
						'bet_points' 			=> array('TINT:2', 0),
					),
					'PRIMARY_KEY'    		=> array('season', 'league', 'extra_no', 'user_id'),
				),
				$this->table_prefix . 'footb_leagues' => array(
					'COLUMNS' => array(
						'season' 				=> array('USINT', 0),
						'league' 				=> array('TINT:2', 0),
						'league_name' 			=> array('VCHAR:20', ''),
						'league_name_short' 	=> array('VCHAR:3', ''),
						'league_type' 			=> array('TINT:1', 1),
						'matchdays' 			=> array('TINT:2', 0),
						'matches_on_matchday' 	=> array('TINT:2', 0),
						'win_result' 			=> array('VCHAR:10', ''),
						'win_result_02' 		=> array('VCHAR:10', ''),
						'win_matchday' 			=> array('VCHAR:255', ''),
						'win_season' 			=> array('VCHAR:255', ''),
						'points_mode' 			=> array('TINT:2', 1),
						'points_result' 		=> array('TINT:2', 0),
						'points_tendency' 		=> array('TINT:2', 0),
						'points_diff' 			=> array('TINT:2', 0),
						'points_last' 			=> array('BOOL', 1),
						'join_by_user' 			=> array('BOOL', 0),
						'join_in_season' 		=> array('BOOL', 0),
						'bet_in_time' 			=> array('BOOL', 0),
						'rules_post_id' 		=> array('UINT', 0),
						'bet_ko_type' 			=> array('TINT:1', 1),
						'bet_points' 			=> array('DECIMAL', 0),
					),
					'PRIMARY_KEY'    		=> array('season', 'league'),
				),
				$this->table_prefix . 'footb_matchdays' => array(
					'COLUMNS' => array(
						'season' 				=> array('USINT', 0),
						'league' 				=> array('TINT:2', 0),
						'matchday' 				=> array('TINT:2', 0),
						'status' 				=> array('TINT:2', 0),
						'delivery_date' 		=> array('CHAR:19', ''),
						'delivery_date_2' 		=> array('CHAR:19', ''),
						'delivery_date_3' 		=> array('CHAR:19', ''),
						'matchday_name' 		=> array('VCHAR:30', ''),
						'matches' 				=> array('CHAR:2',''),
					),
					'PRIMARY_KEY'    		=> array('season', 'league', 'matchday'),
					'KEYS'					=> array(
						'status'			=> array('INDEX', 'status'),
						'date'				=> array('INDEX', 'delivery_date'),
					),
				),
				$this->table_prefix . 'footb_matches' => array(
					'COLUMNS' => array(
						'season' 				=> array('USINT', 0),
						'league' 				=> array('TINT:2', 0),
						'match_no' 				=> array('USINT', 0),
						'team_id_home' 			=> array('USINT', 0),
						'team_id_guest' 		=> array('USINT', 0),
						'goals_home' 			=> array('CHAR:2',''),
						'goals_guest' 			=> array('CHAR:2',''),
						'matchday' 				=> array('TINT:2', 0),
						'status' 				=> array('TINT:2', 0),
						'match_datetime' 		=> array('CHAR:19', ''),
						'group_id' 				=> array('CHAR:1', ''),
						'formula_home' 			=> array('CHAR:9', ''),
						'formula_guest' 		=> array('CHAR:9', ''),
						'ko_match' 				=> array('BOOL', 0),
						'goals_overtime_home'	=> array('CHAR:2',''),
						'goals_overtime_guest' 	=> array('CHAR:2',''),
						'trend' 				=> array('CHAR:8', ''),
						'odd_1' 				=> array('DECIMAL', 0.00),
						'odd_x' 				=> array('DECIMAL', 0.00),
						'odd_2' 				=> array('DECIMAL', 0.00),
						'rating' 				=> array('DECIMAL', 0.00),
					),
					'PRIMARY_KEY'    		=> array('season', 'league', 'match_no'),
					'KEYS'					=> array(
						'gid'				=> array('INDEX', 'team_id_guest'),
						'hid'				=> array('INDEX', 'team_id_home'),
						'md'				=> array('INDEX', 'matchday'),
					),
				),
				$this->table_prefix . 'footb_matches_hist' => array(
					'COLUMNS' => array(
						'match_date' 			=> array('CHAR:10', ''),
						'team_id_home' 			=> array('USINT', 1),
						'team_id_guest' 		=> array('USINT', 1),
						'match_type' 			=> array('VCHAR:5', ''),
						'goals_home' 			=> array('CHAR:2', ''),
						'goals_guest' 			=> array('CHAR:2', ''),
					),
					'PRIMARY_KEY'    		=> array('match_date', 'team_id_home', 'team_id_guest'),
					'KEYS'					=> array(
						'gid'				=> array('INDEX', 'team_id_guest'),
						'hid'				=> array('INDEX', 'team_id_home'),
					),
				),
				$this->table_prefix . 'footb_points' => array(
					'COLUMNS' => array(
						'points_id' 			=> array('UINT',  NULL, 'auto_increment'),
						'season' 				=> array('USINT', 0),
						'league' 				=> array('TINT:2', 0),
						'matchday' 				=> array('TINT:2', 0),
						'points_type' 			=> array('TINT:1', 0),
						'user_id' 				=> array('UINT', 0),
						'points' 				=> array('DECIMAL:20', 0.00),
						'points_comment' 		=> array('MTEXT_UNI', ''),
						'cash' 					=> array('BOOL', 0),
					),
					'PRIMARY_KEY'    		=> array('points_id'),
					'KEYS'					=> array(
						'user'				=> array('INDEX', array('season', 'league', 'user_id')),
						'matchday'			=> array('INDEX', array('season', 'league', 'matchday')),
						'type'				=> array('INDEX', array('season', 'league', 'points_type')),
					),
				),
				$this->table_prefix . 'footb_rank_matchdays' => array(
					'COLUMNS' => array(
						'season' 				=> array('USINT', 0),
						'league' 				=> array('TINT:2', 0),
						'matchday' 				=> array('TINT:2', 0),
						'user_id' 				=> array('UINT', 0),
						'status' 				=> array('TINT:2', 0),
						'rank' 					=> array('USINT', 0),
						'points' 				=> array('USINT', 0),
						'win' 					=> array('DECIMAL', 0),
						'rank_total' 			=> array('USINT', 0),
						'tendencies' 			=> array('TINT:2', 0),
						'correct_result' 		=> array('TINT:2', 0),
						'points_total' 			=> array('USINT', 0),
						'win_total' 			=> array('DECIMAL', 0),
					),
					'PRIMARY_KEY'    		=> array('season', 'league', 'matchday', 'user_id'),
				),
				$this->table_prefix . 'footb_seasons' => array(
					'COLUMNS' => array(
						'season' 				=> array('USINT', 0),
						'season_name' 			=> array('VCHAR:20', ''),
						'season_name_short' 	=> array('VCHAR:10', ''),
					),
					'PRIMARY_KEY'    		=> array('season'),
				),
				$this->table_prefix . 'footb_teams' => array(
					'COLUMNS' => array(
						'season' 				=> array('USINT', 0),
						'league' 				=> array('TINT:2', 0),
						'team_id' 				=> array('USINT', 1),
						'team_name' 			=> array('VCHAR:30', ''),
						'team_name_short' 		=> array('VCHAR:10', ''),
						'team_symbol' 			=> array('VCHAR:25', ''),
						'group_id' 				=> array('CHAR:1', ''),
						'matchday' 				=> array('TINT:2', 0),
					),
					'PRIMARY_KEY'    		=> array('season', 'league', 'team_id'),
				),
				$this->table_prefix . 'footb_teams_hist' => array(
					'COLUMNS' => array(
						'team_id' 				=> array('USINT', 0),
						'team_name' 			=> array('VCHAR:30', ''),
					),
					'PRIMARY_KEY'    		=> array('team_id'),
				),
			),
		);
	}

	public function revert_schema()
	{
		return array(
			'drop_tables'	=> array(
				$this->table_prefix . 'footb_bets',
				$this->table_prefix . 'footb_extra',
				$this->table_prefix . 'footb_extra_bets',
				$this->table_prefix . 'footb_leagues',
				$this->table_prefix . 'footb_matchdays',
				$this->table_prefix . 'footb_matches',
				$this->table_prefix . 'footb_matches_hist',
				$this->table_prefix . 'footb_points',
				$this->table_prefix . 'footb_rank_matchdays',
				$this->table_prefix . 'footb_seasons',
				$this->table_prefix . 'footb_teams',
				$this->table_prefix . 'footb_teams_hist',
			),
		);
	}

	public function update_data()
	{
		return array(
			array('config.remove', array('football_google_league')),
			array('config.remove', array('football_menu_forumdesc1')),
			array('config.remove', array('football_menu_forumdesc2')),
			array('config.remove', array('football_menu_forumdesc3')),
			array('config.remove', array('football_menu_forumdesc4')),
			array('config.remove', array('football_menu_forumdesc5')),
			array('config.remove', array('football_menu_forumdesc6')),
			array('config.remove', array('football_menu_forumid1')),
			array('config.remove', array('football_menu_forumid2')),
			array('config.remove', array('football_menu_forumid3')),
			array('config.remove', array('football_menu_forumid4')),
			array('config.remove', array('football_menu_forumid5')),
			array('config.remove', array('football_menu_forumid6')),
			array('config.add', array('football_bank', '0', '0')),
			array('config.add', array('football_code', '0000', '0')),
			array('config.add', array('football_disable', '0', '0')),
			array('config.add', array('football_disable_msg', 'Wartung der Tipprunde. Bitte später erneut versuchen.', '0')),
			array('config.add', array('football_display_ranks', '30', '0')),
			array('config.add', array('football_founder_delete', '1', '0')),
			array('config.add', array('football_fullscreen', '1', '0')),
			array('config.add', array('football_guest_view', '1', '0')),
			array('config.add', array('football_header_enable', '0', '0')),
			array('config.add', array('football_host_timezone', $this->config['board_timezone'], '0')),
			array('config.add', array('football_info', '', '0')),
			array('config.add', array('football_info_display', '0', '0')),
			array('config.add', array('football_last_backup', '0', '0')),
			array('config.add', array('football_left_column_width', '180', '0')),
			array('config.add', array('football_menu', '1', '0')),
			array('config.add', array('football_menu_desc1', 'XML Seasons', '0')),
			array('config.add', array('football_menu_desc2', '', '0')),
			array('config.add', array('football_menu_desc3', 'XML League', '0')),
			array('config.add', array('football_menu_link1', $this->config['server_protocol'] . $this->config['server_name'] . $this->config['script_path'] . '/ext/football/football/xml/seasons.php?code=0000', '0')),
			array('config.add', array('football_menu_link2', '', '0')),
			array('config.add', array('football_menu_link3', $this->config['server_protocol'] . $this->config['server_name'] . $this->config['script_path'] . '/ext/football/football/xml/league.php?code=0000', '0')),
			array('config.add', array('football_name', 'Tipprunde', '0')),
			array('config.add', array('football_override_style', '0', '0')),
			array('config.add', array('football_remember_enable', '0', '0')),
			array('config.add', array('football_remember_next_run', '0', '0')),
			array('config.add', array('football_results_at_time', '1', '0')),
			array('config.add', array('football_right_column_width', '184', '0')),
			array('config.add', array('football_same_allowed', '0', '0')),
			array('config.add', array('football_season_start', '0', '0')),
			array('config.add', array('football_ult_points', '0', '0')),
			array('config.add', array('football_ult_points_factor', '1.0', '0')),
			array('config.add', array('football_update_code', '0000', '0')),
			array('config.add', array('football_update_source', '', '0')),
			array('config.add', array('football_user_view', '1', '0')),
			array('config.add', array('football_users_per_page', '30', '0')),
			array('config.add', array('football_version', '0.9.4', '0')),
			array('config.add', array('football_view_bets', '0', '0')),
			array('config.add', array('football_view_current', '1', '0')),
			array('config.add', array('football_view_tendencies', '0', '0')),
			array('config.add', array('football_win_name', 'PTS', '0')),
			array('config.add', array('football_win_hits02', '0', '0')),
			array('config.add', array('football_style', $this->config['default_style'], '0')),

			// Add football administrator permission role
			array('permission.role_add', array('ROLE_ADMIN_FOOTBALL', 'a_', 'ROLE_DESCRIPTION_ADMIN_FOOTBALL')), // New role "ADMIN_FOOTBALL"

			// Add football permission settings
			array('permission.add', array('a_football_config', true)),
			array('permission.add', array('a_football_delete', true)),
			array('permission.add', array('a_football_editbets', true)),
			array('permission.add', array('a_football_plan', true)),
			array('permission.add', array('a_football_results', true)),
			array('permission.add', array('u_use_football', true)),
			array('permission.add', array('a_football_points', true)),

			// We give some default permissions then as well?
			array('permission.permission_set', array('ROLE_ADMIN_FULL', 'a_football_config')),
			array('permission.permission_set', array('ROLE_ADMIN_FULL', 'a_football_delete')),
			array('permission.permission_set', array('ROLE_ADMIN_FULL', 'a_football_editbets')),
			array('permission.permission_set', array('ROLE_ADMIN_FULL', 'a_football_plan')),
			array('permission.permission_set', array('ROLE_ADMIN_FULL', 'a_football_results')),
			array('permission.permission_set', array('ROLE_ADMIN_FULL', 'a_football_points')),
			array('permission.permission_set', array('ROLE_ADMIN_STANDARD', 'a_football_results')),
			array('permission.permission_set', array('ROLE_ADMIN_FOOTBALL', 'a_football_results')),
			array('permission.permission_set', array('ROLE_USER_STANDARD', 'u_use_football')),

			
			// Add a new tab named ACP_FOOTBALL 
			array('module.add', array('acp', 0, 'ACP_FOOTBALL')),

			// Add a new category named ACP_FOOTBALL_OPERATION to ACP_FOOTBALL
			array('module.add', array('acp', 'ACP_FOOTBALL', 'ACP_FOOTBALL_OPERATION')),
			
			// Add the manage mode from football_results to the ACP_FOOTBALL_OPERATION category.
			array('module.add', array('acp', 'ACP_FOOTBALL_OPERATION', array(
					'module_basename'   => '\football\football\acp\results_module',
					'module_langname'   => 'ACP_FOOTBALL_RESULTS_MANAGE',
					'module_mode'       => 'manage',
					'module_auth'       => 'acl_a_football_results',
				),
			)),

			// Add the manage mode from football_all_bets to the ACP_FOOTBALL_OPERATION category.
			array('module.add', array('acp', 'ACP_FOOTBALL_OPERATION', array(
					'module_basename'   => '\football\football\acp\all_bets_module',
					'module_langname'   => 'ACP_FOOTBALL_ALL_BETS_VIEW',
					'module_mode'       => 'manage',
					'module_auth'       => 'acl_a_football_editbets',
				),
			)),

			// Add the manage mode from football_bets to the ACP_FOOTBALL_OPERATION category.
			array('module.add', array('acp', 'ACP_FOOTBALL_OPERATION', array(
					'module_basename'   => '\football\football\acp\bets_module',
					'module_langname'   => 'ACP_FOOTBALL_BETS_MANAGE',
					'module_mode'       => 'manage',
					'module_auth'       => 'acl_a_football_editbets',
				),
			)),

			// Add the manage mode from football_ko to the ACP_FOOTBALL_OPERATION category.
			array('module.add', array('acp', 'ACP_FOOTBALL_OPERATION', array(
					'module_basename'   => '\football\football\acp\ko_module',
					'module_langname'   => 'ACP_FOOTBALL_KO_MANAGE',
					'module_mode'       => 'manage',
					'module_auth'       => 'acl_a_football_plan',
				),
			)),
			
			// Add the manage mode from football_bank to the ACP_FOOTBALL_OPERATION category.
			array('module.add', array('acp', 'ACP_FOOTBALL_OPERATION', array(
					'module_basename'   => '\football\football\acp\bank_module',
					'module_langname'   => 'ACP_FOOTBALL_BANK_MANAGE',
					'module_mode'       => 'manage',
					'module_auth'       => 'acl_a_football_points',
				),
			)),
			
			
			// Add a new category named ACP_FOOTBALL_MANAGE to ACP_FOOTBALL
			array('module.add', array('acp', 'ACP_FOOTBALL', 'ACP_FOOTBALL_MANAGE')),

			// Add the manage mode from football_seasons to the ACP_FOOTBALL_MANAGE category.
			array('module.add', array('acp', 'ACP_FOOTBALL_MANAGE', array(
					'module_basename'   => '\football\football\acp\seasons_module',
					'module_langname'   => 'ACP_FOOTBALL_SEASONS_MANAGE',
					'module_mode'       => 'manage',
					'module_auth'       => 'acl_a_football_plan',
				),
			)),
			
			// Add the manage mode from football_leagues to the ACP_FOOTBALL_MANAGE category.
			array('module.add', array('acp', 'ACP_FOOTBALL_MANAGE', array(
					'module_basename'   => '\football\football\acp\leagues_module',
					'module_langname'   => 'ACP_FOOTBALL_LEAGUES_MANAGE',
					'module_mode'       => 'manage',
					'module_auth'       => 'acl_a_football_plan',
				),
			)),
			
			// Add the manage mode from football_matchdays to the ACP_FOOTBALL_MANAGE category.
			array('module.add', array('acp', 'ACP_FOOTBALL_MANAGE', array(
					'module_basename'   => '\football\football\acp\matchdays_module',
					'module_langname'   => 'ACP_FOOTBALL_MATCHDAYS_MANAGE',
					'module_mode'       => 'manage',
					'module_auth'       => 'acl_a_football_plan',
				),
			)),
			
			// Add the manage mode from football_teams to the ACP_FOOTBALL_MANAGE category.
			array('module.add', array('acp', 'ACP_FOOTBALL_MANAGE', array(
					'module_basename'   => '\football\football\acp\teams_module',
					'module_langname'   => 'ACP_FOOTBALL_TEAMS_MANAGE',
					'module_mode'       => 'manage',
					'module_auth'       => 'acl_a_football_plan',
				),
			)),
			
			// Add the manage mode from football_matches to the ACP_FOOTBALL_MANAGE category.
			array('module.add', array('acp', 'ACP_FOOTBALL_MANAGE', array(
					'module_basename'   => '\football\football\acp\matches_module',
					'module_langname'   => 'ACP_FOOTBALL_MATCHES_MANAGE',
					'module_mode'       => 'manage',
					'module_auth'       => 'acl_a_football_plan',
				),
			)),

			// Add the manage mode from football_extra to the ACP_FOOTBALL_MANAGE category.
			array('module.add', array('acp', 'ACP_FOOTBALL_MANAGE', array(
					'module_basename'   => '\football\football\acp\extra_module',
					'module_langname'   => 'ACP_FOOTBALL_EXTRA_MANAGE',
					'module_mode'       => 'manage',
					'module_auth'       => 'acl_a_football_plan',
				),
			)),

			// Add the manage mode from football_update to the ACP_FOOTBALL_MANAGE category using the "manual" method.
			array('module.add', array('acp', 'ACP_FOOTBALL_MANAGE', array(
					'module_basename'   => '\football\football\acp\update_module',
					'module_langname'   => 'ACP_FOOTBALL_UPDATE_MANAGE',
					'module_mode'       => 'manage',
					'module_auth'       => 'acl_a_football_plan',
				),
			)),
			
			// Add a new category named ACP_FOOTBALL_CONFIGURATION to ACP_FOOTBALL
			array('module.add', array('acp', 'ACP_FOOTBALL', 'ACP_FOOTBALL_CONFIGURATION')),

			// Add the settings mode from football to the ACP_FOOTBALL_CONFIGURATION category.
			array('module.add', array('acp', 'ACP_FOOTBALL_CONFIGURATION', array(
					'module_basename'   => 'football\football\acp\football_module',
					'module_langname'   => 'ACP_FOOTBALL_SETTINGS',
					'module_mode'       => 'settings',
					'module_auth'       => 'acl_a_football_config',
				),
			)),
			
			// Add the features mode from football to the ACP_FOOTBALL_CONFIGURATION category.
			array('module.add', array('acp', 'ACP_FOOTBALL_CONFIGURATION', array(
					'module_basename'   => 'football\football\acp\football_module',
					'module_langname'   => 'ACP_FOOTBALL_FEATURES',
					'module_mode'       => 'features',
					'module_auth'       => 'acl_a_football_config',
				),
			)),
			
			// Add the menu mode from football to the ACP_FOOTBALL_CONFIGURATION category.
			array('module.add', array('acp', 'ACP_FOOTBALL_CONFIGURATION', array(
					'module_basename'   => 'football\football\acp\football_module',
					'module_langname'   => 'ACP_FOOTBALL_MENU',
					'module_mode'       => 'menu',
					'module_auth'       => 'acl_a_football_config',
				),
			)),
			
			// Add the userguide mode from football to the ACP_FOOTBALL_CONFIGURATION category.
			array('module.add', array('acp', 'ACP_FOOTBALL_CONFIGURATION', array(
					'module_basename'   => 'football\football\acp\football_module',
					'module_langname'   => 'ACP_FOOTBALL_USERGUIDE',
					'module_mode'       => 'userguide',
					'module_auth'       => 'acl_a_football_plan'
				),
			)),
		);
	}
}