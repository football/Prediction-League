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

class popup
{
	/* @var \phpbb\auth\auth */
	protected $auth;

	/* @var \phpbb\config\config */
	protected $config;

	/* @var \phpbb\extension\manager */
	protected $phpbb_extension_manager;

	/* @var \phpbb\path_helper */
	protected $phpbb_path_helper;

	/* @var \phpbb\db\driver\driver_interface */
	protected $db;

	/* @var \phpbb\controller\helper */
	protected $helper;

	/* @var \phpbb\template\template */
	protected $template;

	/* @var \phpbb\user */
	protected $user;
	
	/* @var \phpbb\pagination */
	protected $pagination;
	
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
	* @param \phpbb\auth\auth			$auth
	* @param \phpbb\config\config		$config
	* @param \phpbb\extension\manager	$phpbb_extension_manager
	* @param \phpbb\path_helper			$phpbb_path_helper
	* @param \phpbb\db\driver\driver_interface	$db
	* @param \phpbb\controller\helper	$helper
	* @param \phpbb\template\template	$template
	* @param \phpbb\user				$user
	* @param \phpbb\pagination 			$pagination 
	*/

	public function __construct(\phpbb\auth\auth $auth,
								\phpbb\config\config $config, 
								\phpbb\extension\manager $phpbb_extension_manager, 
								\phpbb\path_helper $phpbb_path_helper,
								\phpbb\db\driver\driver_interface $db, 
								\phpbb\controller\helper $helper, 
								\phpbb\template\template $template, 
								\phpbb\user $user, 
								\phpbb\pagination $pagination, 
								$phpbb_root_path, 
								$php_ext)
	{
		$this->auth 		= $auth;
		$this->config 		= $config;
		$this->db 			= $db;
		$this->phpbb_extension_manager 	= $phpbb_extension_manager;
		$this->phpbb_path_helper		= $phpbb_path_helper;
		$this->helper 		= $helper;
		$this->template 	= $template;
		$this->user 		= $user;
		$this->pagination 	= $pagination;
		$this->phpbb_root_path 	= $phpbb_root_path;
		$this->php_ext 		= $php_ext;
		
		$this->football_includes_path = $phpbb_root_path . 'ext/football/football/includes/';
		$this->football_root_path = $phpbb_root_path . 'ext/football/football/';

	}

	public function handlepopup($popside)
	{
		global $db, $user, $cache, $request, $template, $season, $league, $matchday;
		global $config, $phpbb_root_path, $phpbb_container, $phpEx, $league_info;
		
		define('IN_FOOTBALL', true);

		$this->db 	= $db;
		$this->user = $user;
		$this->cache = $cache;
		$this->template = $template;
		$this->config = $config;
		$this->request = $request;
		
		// Add football controller language file
		$this->user->add_lang_ext('football/football', 'football');

		// required includes
		include($this->football_includes_path . 'constants.' . $this->php_ext);
		include($this->football_includes_path . 'functions.' . $this->php_ext);

		if ($config['board_disable'] && !$this->auth->acl_gets('a_'))
		{
			$message = (!empty($config['board_disable_msg'])) ? $config['board_disable_msg'] : 'BOARD_DISABLE';
			trigger_error($message);
		}
		
		if ($config['football_disable'])
		{
			$message = (!empty($config['football_disable_msg'])) ? $config['football_disable_msg'] : 'FOOTBALL_DISABLED';
			trigger_error($message);
		}

		// Can this user view Prediction Leagues pages?
		if (!$config['football_guest_view'])
		{
			// No guest view, call login for guest 
			if ($user->data['user_id'] == ANONYMOUS)
			{
				login_box('', ($user->lang['LOGIN_EXPLAIN_FOOTBALL']));
			}
		}
		if (!$config['football_user_view'])
		{
			// Only Prediction League member should see these pages
			// Check Prediction League authorisation 
			if ( !$this->auth->acl_get('u_use_football') )
			{
				trigger_error('NO_AUTH_VIEW');
			}
		}

		$season	= $this->request->variable('s', 0);
		$league	= $this->request->variable('l', 0);
		$matchday = $this->request->variable('m', $curr_matchday);
		$league_info = array();
		$league_info = league_info($season, $league);

		if ($config['football_override_style'])
		{
			$user->data['user_style'] = $config['football_style'];
		}
				
		include($this->football_root_path . 'block/' . $popside . '.' . $this->php_ext);

		
		// Send data to the template file
		return $this->helper->render($popside . '.html', $this->user->lang['PREDICTION_LEAGUE']);
	}
}
