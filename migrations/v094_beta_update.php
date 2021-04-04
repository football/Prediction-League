<?php
/**
*
* @package Football Football v0.94
* @copyright (c) 2015 football (http://football.bplaced.net)
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace football\football\migrations;

class v094_beta_update extends \phpbb\db\migration\migration
{
	// already updated or don't update, if column session_matchday in sessions table don't exists.
	public function effectively_installed()
	{
		return !$this->db_tools->sql_column_exists($this->table_prefix . 'sessions', 'session_matchday');
	}

	static public function depends_on()
	{
		return array('\phpbb\db\migration\data\v310\extensions');
	}

	public function update_schema()
	{
		return array(
 			'drop_columns' => array(
				$this->table_prefix . 'sessions' 		=> array(
					'session_season',
					'session_league',
					'session_matchday',
				)
			),
			'add_columns' => array(
				$this->table_prefix . 'sessions' 		=> array(
					'football_season' 			=> array('USINT', 0),
					'football_league' 			=> array('TINT:2', 0),
					'football_matchday' 		=> array('TINT:2', 0),
					'football_mobile'			=> array('UINT:1', 0),
					'football_mobile_device'	=> array('VCHAR:255', NULL),
				),
				$this->table_prefix . 'footb_bets'		=> array(
					'bet_time'					=> array('UINT:11', 0),
				),
				$this->table_prefix . 'footb_matches'	=> array(
					'trend' 					=> array('CHAR:8', ''),
					'odd_1' 					=> array('DECIMAL', 0.00),
					'odd_x' 					=> array('DECIMAL', 0.00),
					'odd_2' 					=> array('DECIMAL', 0.00),
					'rating' 					=> array('DECIMAL', 0.00),
				),
			),
		);
	}

	public function revert_schema()
	{
		return array(
			'drop_columns'		=> array(
				$this->table_prefix . 'sessions' 			=> array(
					'football_season',
					'football_league',
					'football_matchday',
					'football_mobile',
					'football_mobile_device',
				),
			)
		);
	}

	public function update_data()
	{
		return array(
			// Add config values
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
			array('config.remove', array('football_side')),
			array('config.add', array('football_fullscreen', '1', '0')),
			array('config.add', array('football_header_enable', '0', '0')),
			array('config.add', array('football_last_backup', '0', '0')),
			array('config.add', array('football_menu', '1', '0')),
			array('config.add', array('football_menu_desc1', 'XML Seasons', '0')),
			array('config.add', array('football_menu_desc2', '', '0')),
			array('config.add', array('football_menu_desc3', 'XML League', '0')),
			array('config.add', array('football_menu_link1', $this->config['server_protocol'] . $this->config['server_name'] . $this->config['script_path'] . '/ext/football/football/xml/seasons.php?code=0000', '0')),
			array('config.add', array('football_menu_link2', '', '0')),
			array('config.add', array('football_menu_link3', $this->config['server_protocol'] . $this->config['server_name'] . $this->config['script_path'] . '/ext/football/football/xml/league.php?code=0000', '0')),
			array('config.add', array('football_remember_enable', '0', '0')),
			array('config.add', array('football_remember_next_run', '0', '0')),
			array('config.add', array('football_season_start', '0', '0')),
			array('config.add', array('football_update_code', '0000', '0')),
			array('config.add', array('football_update_source', '', '0')),
			array('config.add', array('football_user_view', '1', '0')),
			array('config.update', array('football_version', '0.9.4', '0')), 
			
			// Add a new tab named ACP_FOOTBALL 
			array('module.add', array('acp', 0, 'ACP_FOOTBALL')),

			// Add a new category named ACP_FOOTBALL_OPERATION to ACP_FOOTBALL
			array('module.add', array('acp', 'ACP_FOOTBALL', 'ACP_FOOTBALL_OPERATION')),
			
			// Add the manage mode from football_results to the ACP_FOOTBALL_OPERATION category.
			array('module.add', array('acp', 'ACP_FOOTBALL_OPERATION', array(
					'module_basename'	=> '\football\football\acp\results_module',
					'module_langname'	=> 'ACP_FOOTBALL_RESULTS_MANAGE',
					'module_mode'		=> 'manage',
					'module_auth'		=> 'acl_a_football_results',
				),
			)),

			// Add the manage mode from football_all_bets to the ACP_FOOTBALL_OPERATION category.
			array('module.add', array('acp', 'ACP_FOOTBALL_OPERATION', array(
					'module_basename'	=> '\football\football\acp\all_bets_module',
					'module_langname'	=> 'ACP_FOOTBALL_ALL_BETS_VIEW',
					'module_mode'		=> 'manage',
					'module_auth'		=> 'acl_a_football_editbets',
				),
			)),

			// Add the manage mode from football_bets to the ACP_FOOTBALL_OPERATION category.
			array('module.add', array('acp', 'ACP_FOOTBALL_OPERATION', array(
					'module_basename'	=> '\football\football\acp\bets_module',
					'module_langname'	=> 'ACP_FOOTBALL_BETS_MANAGE',
					'module_mode'		=> 'manage',
					'module_auth'		=> 'acl_a_football_editbets',
				),
			)),

			// Add the manage mode from football_ko to the ACP_FOOTBALL_OPERATION category.
			array('module.add', array('acp', 'ACP_FOOTBALL_OPERATION', array(
					'module_basename'	=> '\football\football\acp\ko_module',
					'module_langname'	=> 'ACP_FOOTBALL_KO_MANAGE',
					'module_mode'		=> 'manage',
					'module_auth'		=> 'acl_a_football_plan',
				),
			)),
			
			// Add the manage mode from football_bank to the ACP_FOOTBALL_OPERATION category.
			array('module.add', array('acp', 'ACP_FOOTBALL_OPERATION', array(
					'module_basename'	=> '\football\football\acp\bank_module',
					'module_langname'	=> 'ACP_FOOTBALL_BANK_MANAGE',
					'module_mode'		=> 'manage',
					'module_auth'		=> 'acl_a_football_points',
				),
			)),
			
			
			// Add a new category named ACP_FOOTBALL_MANAGE to ACP_FOOTBALL
			array('module.add', array('acp', 'ACP_FOOTBALL', 'ACP_FOOTBALL_MANAGE')),

			// Add the manage mode from football_seasons to the ACP_FOOTBALL_MANAGE category.
			array('module.add', array('acp', 'ACP_FOOTBALL_MANAGE', array(
					'module_basename'	=> '\football\football\acp\seasons_module',
					'module_langname'	=> 'ACP_FOOTBALL_SEASONS_MANAGE',
					'module_mode'		=> 'manage',
					'module_auth'		=> 'acl_a_football_plan',
				),
			)),
			
			// Add the manage mode from football_leagues to the ACP_FOOTBALL_MANAGE category.
			array('module.add', array('acp', 'ACP_FOOTBALL_MANAGE', array(
					'module_basename'	=> '\football\football\acp\leagues_module',
					'module_langname'	=> 'ACP_FOOTBALL_LEAGUES_MANAGE',
					'module_mode'		=> 'manage',
					'module_auth'		=> 'acl_a_football_plan',
				),
			)),
			
			// Add the manage mode from football_matchdays to the ACP_FOOTBALL_MANAGE category.
			array('module.add', array('acp', 'ACP_FOOTBALL_MANAGE', array(
					'module_basename'	=> '\football\football\acp\matchdays_module',
					'module_langname'	=> 'ACP_FOOTBALL_MATCHDAYS_MANAGE',
					'module_mode'		=> 'manage',
					'module_auth'		=> 'acl_a_football_plan',
				),
			)),
			
			// Add the manage mode from football_teams to the ACP_FOOTBALL_MANAGE category.
			array('module.add', array('acp', 'ACP_FOOTBALL_MANAGE', array(
					'module_basename'	=> '\football\football\acp\teams_module',
					'module_langname'	=> 'ACP_FOOTBALL_TEAMS_MANAGE',
					'module_mode'		=> 'manage',
					'module_auth'		=> 'acl_a_football_plan',
				),
			)),
			
			// Add the manage mode from football_matches to the ACP_FOOTBALL_MANAGE category.
			array('module.add', array('acp', 'ACP_FOOTBALL_MANAGE', array(
					'module_basename'	=> '\football\football\acp\matches_module',
					'module_langname'	=> 'ACP_FOOTBALL_MATCHES_MANAGE',
					'module_mode'		=> 'manage',
					'module_auth'		=> 'acl_a_football_plan',
				),
			)),

			// Add the manage mode from football_extra to the ACP_FOOTBALL_MANAGE category.
			array('module.add', array('acp', 'ACP_FOOTBALL_MANAGE', array(
					'module_basename'	=> '\football\football\acp\extra_module',
					'module_langname'	=> 'ACP_FOOTBALL_EXTRA_MANAGE',
					'module_mode'		=> 'manage',
					'module_auth'		=> 'acl_a_football_plan',
				),
			)),

			// Add the manage mode from football_update to the ACP_FOOTBALL_MANAGE category using the "manual" method.
			array('module.add', array('acp', 'ACP_FOOTBALL_MANAGE', array(
					'module_basename'	=> '\football\football\acp\update_module',
					'module_langname'	=> 'ACP_FOOTBALL_UPDATE_MANAGE',
					'module_mode'		=> 'manage',
					'module_auth'		=> 'acl_a_football_plan',
				),
			)),
			
			// Add a new category named ACP_FOOTBALL_CONFIGURATION to ACP_FOOTBALL
			array('module.add', array('acp', 'ACP_FOOTBALL', 'ACP_FOOTBALL_CONFIGURATION')),

			// Add the settings mode from football to the ACP_FOOTBALL_CONFIGURATION category.
			array('module.add', array('acp', 'ACP_FOOTBALL_CONFIGURATION', array(
					'module_basename'	=> 'football\football\acp\football_module',
					'module_langname'	=> 'ACP_FOOTBALL_SETTINGS',
					'module_mode'		=> 'settings',
					'module_auth'		=> 'acl_a_football_config',
				),
			)),
			
			// Add the features mode from football to the ACP_FOOTBALL_CONFIGURATION category.
			array('module.add', array('acp', 'ACP_FOOTBALL_CONFIGURATION', array(
					'module_basename'	=> 'football\football\acp\football_module',
					'module_langname'	=> 'ACP_FOOTBALL_FEATURES',
					'module_mode'		=> 'features',
					'module_auth'		=> 'acl_a_football_config',
				),
			)),
			
			// Add the menu mode from football to the ACP_FOOTBALL_CONFIGURATION category.
			array('module.add', array('acp', 'ACP_FOOTBALL_CONFIGURATION', array(
					'module_basename'	=> 'football\football\acp\football_module',
					'module_langname'	=> 'ACP_FOOTBALL_MENU',
					'module_mode'		=> 'menu',
					'module_auth'		=> 'acl_a_football_config',
				),
			)),
			
			// Add the userguide mode from football to the ACP_FOOTBALL_CONFIGURATION category.
			array('module.add', array('acp', 'ACP_FOOTBALL_CONFIGURATION', array(
					'module_basename'	=> 'football\football\acp\football_module',
					'module_langname'	=> 'ACP_FOOTBALL_USERGUIDE',
					'module_mode'		=> 'userguide',
					'module_auth'		=> 'acl_a_football_plan'
				),
			)),
		);
	}
}
