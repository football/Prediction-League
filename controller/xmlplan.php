<?php
/**
*
* @package phpBB Extension - Football Football
* @copyright (c) 2016 football (http://football.bplaced.net)
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace football\football\controller;

/**
* @ignore
*/

class xmlplan
{
	/* @var \phpbb\config\config */
	protected $config;


	/* @var \phpbb\extension\manager */
	protected $phpbb_extension_manager;

	/* @var \phpbb\path_helper */
	protected $phpbb_path_helper;

	/* @var \phpbb\db\driver\driver_interface */
	protected $db;

	/* @var \phpbb\user */
	protected $user;
	
	/* @var phpBB root path */
	protected $phpbb_root_path;

	/* @var PHP file extension	*/
	protected $php_ext;

	/* @var football includes path */
	protected $football_includes_path;

	/* @var football root path */
	protected $football_root_path;

	/**
	* Constructor
	*
	* @param \phpbb\config\config				$config
	* @param \phpbb\extension\manager	$phpbb_extension_manager
	* @param \phpbb\path_helper			$phpbb_path_helper
	* @param \phpbb\db\driver\driver_interface	$db
	* @param \phpbb\user						$user
	*/

	public function __construct(\phpbb\config\config $config, 
								\phpbb\extension\manager $phpbb_extension_manager, 
								\phpbb\path_helper $phpbb_path_helper,
								\phpbb\db\driver\driver_interface $db, 
								\phpbb\user $user, 
								$phpbb_root_path, 
								$php_ext)
	{
		$this->config 					= $config;
		$this->phpbb_extension_manager 	= $phpbb_extension_manager;
		$this->phpbb_path_helper		= $phpbb_path_helper;
		$this->db 						= $db;
		$this->user 					= $user;
		$this->phpbb_root_path 			= $phpbb_root_path;
		$this->php_ext 					= $php_ext;
		
		$this->football_includes_path 	= $phpbb_root_path . 'ext/football/football/includes/';
		$this->football_root_path 		= $phpbb_root_path . 'ext/football/football/';
	}

	public function handlexml($xmlside)
	{
		global $db, $user, $cache, $request, $season, $league, $football_root_path;
		global $config, $phpbb_root_path, $phpEx, $table_prefix, $ext_path;
		
		define('IN_FOOTBALL', true);

		$this->db 	= $db;
		$this->user = $user;
		$this->cache = $cache;
		$this->config = $config;
		$this->request = $request;
		$football_root_path = $phpbb_root_path . 'ext/football/football/';
		$ext_path = $this->phpbb_path_helper->update_web_root_path($this->phpbb_extension_manager->get_extension_path('football/football', true));		
		
		// Add football controller language file
		$this->user->add_lang_ext('football/football', 'info_acp_update');
		
		// required includes
		include($this->football_includes_path . 'constants.' . $this->php_ext);
		include($this->football_includes_path . 'functions.' . $this->php_ext);
		
		if ($config['board_disable'])
		{
			$message = (!empty($config['board_disable_msg'])) ? $config['board_disable_msg'] : 'BOARD_DISABLE';
			trigger_error($message);
		}
		
		if ($config['football_disable'])
		{
			$message = (!empty($config['football_disable_msg'])) ? $config['football_disable_msg'] : 'FOOTBALL_DISABLED';
			trigger_error($message);
		}


		include($this->football_root_path . 'xml/' . $xmlside . '.' . $this->php_ext);
	}
}
