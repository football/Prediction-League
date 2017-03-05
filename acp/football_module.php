<?php
/**
*
* @package phpBB Extension - Football Football
* @copyright (c) 2016 football (http://football.bplaced.net)
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace football\football\acp;

class football_module
{
	var $new_config = array();
	public $u_action;

	protected $db, $user, $template, $config, $phpbb_root_path, $phpbb_admin_path, $phpEx;
	protected $root_path, $request, $php_ext, $log, $phpbb_container, $version_check;


	public function __construct()
	{
		global $db, $user, $request, $template, $phpbb_container;
		global $config, $phpbb_root_path, $phpbb_admin_path, $phpEx;

		$user->add_lang_ext('football/football', 'help_football');
		$user->add_lang_ext('football/football', 'football');
		$user->add_lang_ext('football/football', 'info_acp_football');

		$this->root_path = $phpbb_root_path . 'ext/football/football/';

		$this->config = $config;
		$this->request = $request;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->phpbb_admin_path = $phpbb_admin_path;
		$this->php_ext = $phpEx;
		$this->phpbb_container = $phpbb_container;
		$this->version_check = $this->phpbb_container->get('football.football.version.check');

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
		global $db, $auth, $phpbb_container, $phpbb_admin_path, $league_info, $phpbb_log;
		global $template, $user, $config, $phpbb_extension_manager, $request, $phpbb_root_path, $phpEx;
		
		$helper = $phpbb_container->get('controller.helper');
		
		$user->add_lang('acp/board');

		$action	= $this->request->variable('action', '');
		$submit = (isset($_POST['submit'])) ? true : false;

		$form_key = 'acp_football';
		add_form_key($form_key);

		switch ($mode)
		{
			case 'userguide':
            	$this->page_title = 'ACP_FOOTBALL_USERGUIDE';
				$this->tpl_name = 'acp_football_userguide';

				$template->assign_vars(array(
					'S_IN_FOOTBALL_USERGUIDE'	=> true,
					'U_FOOTBALL' 				=> $helper->route('football_main_controller',array('side' => 'bet')),
					'L_BACK_TO_TOP'				=> $user->lang['BACK_TO_TOP'],
					'ICON_BACK_TO_TOP'			=> '<img src="' . $phpbb_admin_path . 'images/icon_up.gif" style="vertical-align: middle;" alt="' . $user->lang['BACK_TO_TOP'] . '" title="' . $user->lang['BACK_TO_TOP'] . '" />',
					'S_VERSION_NO'				=> $this->config['football_version'],
				));

				// Pull the array data from the lang pack
				foreach ($user->lang['FOOTBALL_HELP_FAQ'] as $help_ary)
				{
					if ($help_ary[0] == '--')
					{
						$template->assign_block_vars('userguide_block', array(
							'BLOCK_TITLE'		=> $help_ary[1])
						);

						continue;
					}

					$template->assign_block_vars('userguide_block.userguide_row', array(
						'USERGUIDE_QUESTION'		=> $help_ary[0],
						'USERGUIDE_ANSWER'			=> $help_ary[1])
					);
				}
				return;
			break;
			case 'settings':
				$display_vars = array(
					'title'	=> 'ACP_FOOTBALL_SETTINGS',
					'vars'	=> array(
						'legend1'				=> 'ACP_FOOTBALL_SETTINGS',
						'football_name'				=> array('lang' => 'FOOTBALL_NAME',		'validate' => 'string',	'type' => 'text:25:25', 'explain' => true),
						'football_disable'			=> array('lang' => 'DISABLE_FOOTBALL',	'validate' => 'bool',	'type' => 'custom', 'method' => 'football_disable', 'explain' => true),
						'football_disable_msg'		=> false,
						'football_fullscreen'		=> array('lang' => 'FOOTBALL_FULLSCREEN','validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => true),
						'football_header_enable'	=> array('lang' => 'FOOTBALL_HEADER_ENABLE','validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => true),
						'football_guest_view'		=> array('lang' => 'GUEST_VIEW',		'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => true),
						'football_user_view'		=> array('lang' => 'USER_VIEW',			'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => true),
						'football_host_timezone'	=> array('lang' => 'HOST_TIMEZONE',		'validate' => 'string',	'type' => 'select', 'function' => 'phpbb_timezone_select', 'params' => array($template, $user, '{CONFIG_VALUE}', true), 'explain' => true),
						'football_info_display'		=> array('lang' => 'FOOTBALL_INFO',		'validate' => 'bool',	'type' => 'custom', 'method' => 'football_info', 'explain' => true),
						'football_info'				=> false,
						'football_win_name'			=> array('lang' => 'WIN_NAME',			'validate' => 'string',	'type' => 'text:6:6', 'explain' => true),
						'football_code'				=> array('lang' => 'FOOTBALL_CODE',		'validate' => 'string',	'type' => 'text:25:25', 'explain' => true),
						'football_style'			=> array('lang' => 'FOOTBALL_STYLE',	'validate' => 'int',	'type' => 'select', 'function' => 'style_select', 'params' => array('{CONFIG_VALUE}', false), 'explain' => false),
						'football_override_style'	=> array('lang' => 'FOOTBALL_OVERRIDE_STYLE',	'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => true),
						'football_update_source'	=> array('lang' => 'FOOTBALL_UPDATE_SOURCE',	'validate' => 'string',	'type' => 'text:80:255', 'explain' => true),
						'football_update_code'		=> array('lang' => 'FOOTBALL_UPDATE_CODE',		'validate' => 'string',	'type' => 'text:25:255', 'explain' => true),

						'legend2'				=> 'GENERAL_SETTINGS',
						'football_left_column_width'	=> array('lang' => 'LEFT_COLUMN',	'validate' => 'int',	'type' => 'text:3:3', 'explain' => true),
						'football_right_column_width'	=> array('lang' => 'RIGHT_COLUMN',	'validate' => 'int',	'type' => 'text:3:3', 'explain' => true),
						'football_display_ranks'		=> array('lang' => 'DISPLAY_RANKS',	'validate' => 'int',	'type' => 'text:3:3', 'explain' => true),
						'football_users_per_page'		=> array('lang' => 'USERS_PAGE',	'validate' => 'int',	'type' => 'text:3:3', 'explain' => true),

						'legend3'				=> 'ACP_SUBMIT_CHANGES',
					)
				);
				// show the extension version check on Settings page
				$this->version_check->check();
			break;

			case 'features':
				$display_vars = array(
					'title'	=> 'ACP_FOOTBALL_FEATURES',
					'vars'	=> array(
						'legend1'				=> 'ACP_FOOTBALL_FEATURES',
						'football_founder_delete'	=> array('lang' => 'FOUNDER_DELETE',	'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => true),
						'football_results_at_time'	=> array('lang' => 'RESULTS_AT_TIME',	'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => true),
						'football_win_hits02'		=> array('lang' => 'WIN_HITS02',		'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => true),
						'football_same_allowed'		=> array('lang' => 'SAME_ALLOWED',		'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => true),
						'football_season_start'		=> array('lang' => 'FOOTBALL_SEASON_START',	'validate' => 'int', 'type' => 'select', 'method' => 'season_select', 'params' => array('{CONFIG_VALUE}', false), 'explain' => true),
						'football_view_current'		=> array('lang' => 'VIEW_CURRENT',		'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => true),
						'football_view_bets'		=> array('lang' => 'VIEW_BETS',			'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => true),
						'football_view_tendencies'	=> array('lang' => 'VIEW_TENDENCIES',	'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => true),
						'football_bank'				=> array('lang' => 'BANK',				'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => true),
						'football_ult_points'		=> array('lang' => 'ULT_POINTS',		'validate' => 'int',	'type' => 'custom', 'method' => 'select_up_method', 'explain' => true),
						'football_ult_points_factor'=> array('lang' => 'ULT_POINTS_FACTOR',	'validate' => 'dec:3:2','type' => 'text:4:10', 'explain' => true),
						'football_remember_enable'	=> array('lang' => 'FOOTBALL_REMEMBER_ENABLE',	'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => true),
						'football_remember_next_run'=> array('lang' => 'FOOTBALL_REMEMBER_NEXT_RUN','validate' => 'int',	'type' => 'custom', 'method' => 'next_run', 'explain' => true),
			
						'legend2'				=> 'ACP_SUBMIT_CHANGES',
					)
				);
			break;

			case 'menu':
				$display_vars = array(
					'title'	=> 'ACP_FOOTBALL_MENU',
					'vars'	=> array(
						'legend1'				=> 'ACP_FOOTBALL_MENU',
						'football_breadcrumb'	=> array('lang' => 'FOOTBALL_BREADCRUMB', 'validate' => 'bool',	'type' => 'radio:yes_no', 	'explain' => true),
						'football_side'			=> array('lang' => 'FOOTBALL_SIDE',	'validate' => 'bool',	'type' => 'radio:yes_no', 	'explain' => true),
						'football_menu'			=> array('lang' => 'FOOTBALL_MENU',	'validate' => 'bool',	'type' => 'radio:yes_no', 	'explain' => true),
						'football_menu_link1'	=> array('lang' => 'MENU_LINK1',	'validate' => 'string',	'type' => 'text:80:255',	'explain' => true),
						'football_menu_desc1'	=> array('lang' => 'MENU_DESC1',	'validate' => 'string',	'type' => 'text:20:20',		'explain' => true),
						'football_menu_link2'	=> array('lang' => 'MENU_LINK2',	'validate' => 'string',	'type' => 'text:80:255',	'explain' => false),
						'football_menu_desc2'	=> array('lang' => 'MENU_DESC2',	'validate' => 'string',	'type' => 'text:20:20',		'explain' => false),
						'football_menu_link3'	=> array('lang' => 'MENU_LINK3',	'validate' => 'string',	'type' => 'text:80:255', 	'explain' => false),
						'football_menu_desc3'	=> array('lang' => 'MENU_DESC3',	'validate' => 'string',	'type' => 'text:20:20',		'explain' => false),
					)
				);
			break;

			default:
				trigger_error('NO_MODE', E_USER_ERROR);
			break;
		}

		if (isset($display_vars['lang']))
		{
			$user->add_lang($display_vars['lang']);
		}

		$this->new_config = $this->config;
		$cfg_array = (isset($_REQUEST['config'])) ? utf8_normalize_nfc($this->request->variable('config', array('' => ''), true)) : $this->new_config;
		$error = array();

		// We validate the complete config if whished
		validate_config_vars($display_vars['vars'], $cfg_array, $error);

		if ($submit && !check_form_key($form_key))
		{
			$error[] = $user->lang['FORM_INVALID'];
		}
		// Do not write values if there is an error
		if (sizeof($error))
		{
			$submit = false;
		}

		// We go through the display_vars to make sure no one is trying to set variables he/she is not allowed to...
		foreach ($display_vars['vars'] as $this->config_name => $null)
		{
			if (!isset($cfg_array[$this->config_name]) || strpos($this->config_name, 'legend') !== false)
			{
				continue;
			}
			$this->new_config[$this->config_name] = $this->config_value = $cfg_array[$this->config_name];
			
			if ($submit)
			{
				if ($this->config_name == 'football_ult_points' && $this->config_value)
				{
					$this->config->set('football_bank', 1);
				}
				if ($this->config_name == 'football_remember_enable')
				{
					$day 	= $this->request->variable('next_run_day', 0);
					$month 	= $this->request->variable('next_run_month', 0);
					$year 	= $this->request->variable('next_run_year', 0);
					$hour 	= $this->request->variable('next_run_hour', 0);
					$minute = $this->request->variable('next_run_minute', 0);

					$next_run = mktime($hour, $minute, 0, $month, $day, $year);
					$this->config->set('football_remember_next_run', $next_run);
				}
				$this->config->set($this->config_name, $this->config_value);
			}
		}

		if ($submit)
		{
			$phpbb_log->add('admin', $user->data['user_id'], $user->ip, 'LOG_FOOTBALL_' . strtoupper($mode));

			trigger_error($user->lang['CONFIG_UPDATED'] . adm_back_link($this->u_action));
		}

		$this->tpl_name = 'acp_football';
		$this->page_title = $display_vars['title'];

		$template->assign_vars(array(
			'U_FOOTBALL' 		=> $helper->route('football_main_controller',array('side' => 'bet')),
			'L_TITLE'			=> $user->lang[$display_vars['title']],
			'L_TITLE_EXPLAIN'	=> $user->lang[$display_vars['title'] . '_EXPLAIN'],
			'S_ERROR'			=> (sizeof($error)) ? true : false,
			'ERROR_MSG'			=> implode('<br />', $error),
			'U_ACTION'			=> $this->u_action,
			'S_VERSION_NO'		=> $this->config['football_version'],
			)
		);

		// Output relevant page
		foreach ($display_vars['vars'] as $this->config_key => $vars)
		{
			if (!is_array($vars) && strpos($this->config_key, 'legend') === false)
			{
				continue;
			}

			if (strpos($this->config_key, 'legend') !== false)
			{
				$template->assign_block_vars('options', array(
					'S_LEGEND'		=> true,
					'LEGEND'		=> (isset($user->lang[$vars])) ? $user->lang[$vars] : $vars,
					)
				);

				continue;
			}

			$type = explode(':', $vars['type']);

			$l_explain = '';
			if ($vars['explain'] && isset($vars['lang_explain']))
			{
				$l_explain = (isset($user->lang[$vars['lang_explain']])) ? $user->lang[$vars['lang_explain']] : $vars['lang_explain'];
			}
			else if ($vars['explain'])
			{
				$l_explain = (isset($user->lang[$vars['lang'] . '_EXPLAIN'])) ? $user->lang[$vars['lang'] . '_EXPLAIN'] : '';
			}

			$content = build_cfg_template($type, $this->config_key, $this->new_config, $this->config_key, $vars);

			if (empty($content))
			{
				continue;
			}

			$template->assign_block_vars('options', array(
				'KEY'			=> $this->config_key,
				'TITLE'			=> (isset($user->lang[$vars['lang']])) ? $user->lang[$vars['lang']] : $vars['lang'],
				'S_EXPLAIN'		=> $vars['explain'],
				'TITLE_EXPLAIN'	=> $l_explain,
				'CONTENT'		=> $content,
				)
			);

			unset($display_vars['vars'][$this->config_key]);
		}
	}

	/**
	* Football disable option and message
	*/
	function football_disable($value, $key)
	{
		global $user;

		$radio_ary = array(1 => 'YES', 0 => 'NO');

		return h_radio('config[football_disable]', $radio_ary, $value) . '<br /><input id="' . $key . '" type="text" name="config[football_disable_msg]" maxlength="255" size="80" value="' . $this->new_config['football_disable_msg'] . '" />';
	}

	/**
	* Football info option and message
	*/
	function football_info($value, $key)
	{
		global $user;

		$radio_ary = array(1 => 'YES', 0 => 'NO');

		return h_radio('config[football_info_display]', $radio_ary, $value) . '<br /><input id="' . $key . '" type="text" name="config[football_info]" maxlength="255" size="80" value="' . $this->new_config['football_info'] . '" />';
	}

	/**
	* Select ultimate points method
	*/
	function select_up_method($value, $key = '')
	{
		global $user, $config;

		$radio_ary = array(UP_NONE => 'UP_NONE', UP_WINS => 'UP_WINS', UP_POINTS => 'UP_POINTS');

		return h_radio('config[football_ult_points]', $radio_ary, $value, $key);
	}


	function season_select($default = 0)
	{
		global $user, $db;

		$sql = 'SELECT DISTINCT s.season, s.season_name_short FROM ' . FOOTB_SEASONS . ' AS s
				INNER JOIN ' . FOOTB_LEAGUES . ' AS l ON (l.season = s.season)
				INNER JOIN ' . FOOTB_MATCHDAYS . ' AS md ON (md.season = s.season AND md.league = l.league)
				WHERE 1
				ORDER BY s.season DESC';
		$result = $db->sql_query($sql);

		$selected = (0 == $default) ? ' selected="selected"' : '';
		$season_options = '<option value="0"' . $selected . '>' . $user->lang['AUTO'] . '</option>';
		while ($row = $db->sql_fetchrow($result))
		{
			$selected = ($row['season'] == $default) ? ' selected="selected"' : '';
			$season_options .= '<option value="' . $row['season'] . '"' . $selected . '>' . $row['season_name_short'] . '</option>';
		}
		$db->sql_freeresult($result);

		return $season_options;
	}

	/**
	* Adjust Cronjob EMail remember next un
	*/
	function next_run($value, $key = '')
	{
		global $user, $db;
		$next_run = getdate($this->config['football_remember_next_run']);
		
		// Days
		$day_options = '<select name="next_run_day" id="next_run_day">';
		for ($i = 1; $i < 32; $i++)
		{
			$selected = ($i == $next_run['mday']) ? ' selected="selected"' : '';
			$day_options .= '<option value="' . $i . '"' . $selected . '>' . $i . '</option>';
		}
		$day_options .= '</select>';
		
		// Months
		$month_options = '<select name="next_run_month" id="next_run_month">';
		for ($i = 1; $i < 13; $i++)
		{
			$selected = ($i == $next_run['mon']) ? ' selected="selected"' : '';
			$month_options .= '<option value="' . $i . '"' . $selected . '>' . $i . '</option>';
		}
		$month_options .= '</select>';

		// Years
		$year_options = '<select name="next_run_year" id="next_run_year">';
		for ($i = date("Y"); $i < (date("Y") + 1); $i++)
		{
			$selected = ($i == $next_run['year']) ? ' selected="selected"' : '';
			$year_options .= '<option value="' . $i . '"' . $selected . '>' . $i . '</option>';
		}
		$year_options .= '</select>';

		// Hours
		$hour_options = '<select name="next_run_hour" id="next_run_hour">';
		for ($i = 0; $i < 24; $i++)
		{
			$selected = ($i == $next_run['hours']) ? ' selected="selected"' : '';
			$hour_options .= '<option value="' . $i . '"' . $selected . '>' . $i . '</option>';
		}
		$hour_options .= '</select>';

		// Minutes
		$minute_options = '<select name="next_run_minute" id="next_run_minute">';
		for ($i = 0; $i < 60; $i++)
		{
			$selected = ($i == $next_run['minutes']) ? ' selected="selected"' : '';
			$minute_options .= '<option value="' . $i . '"' . $selected . '>' . $i . '</option>';
		}
		$minute_options .= '</select>';


		return $user->lang['DAY'] . ': ' . $day_options . ' ' . $user->lang['MONTH'] . ': ' . $month_options . ' ' . $user->lang['YEAR'] . ': ' . 
			$year_options . ' ' . $user->lang['HOURS'] . ': ' . $hour_options . ' ' . $user->lang['MINUTES'] . ': ' . $minute_options;

	}
}

?>