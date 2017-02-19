<?php
/**
*
* @package phpBB Extension - Football Football
* @copyright (c) 2016 football (http://football.bplaced.net)
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace football\football\event;

/**
* @ignore
*/
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
* Event listener
*/
class main_listener implements EventSubscriberInterface
{
	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\controller\helper */
	protected $controller_helper;

	/** @var \phpbb\template\template */
	protected $template;

	/* @var \phpbb\path_helper */
	protected $phpbb_path_helper;

	/* @var \phpbb\extension\manager */
	protected $phpbb_extension_manager;

	/** @var \phpbb\user */
	protected $user;

	/* @var string phpbb_root_path */
	protected $phpbb_root_path;

	/** @var string phpEx */
	protected $php_ext;

	/**
	* Constructor of football prediction league event listener
	*
	* @param \phpbb\auth\auth			$auth				phpBB auth object
	* @param \phpbb\config\config		$config 			phpBB config
	* @param \phpbb\controller\helper	$controller_helper	phpBB controller helper object
	* @param \phpbb\template\template	$template			phpBB template object
	* @param \phpbb\path_helper			$path_helper		phpBB path helper
	* @param \phpbb\extension\manager	$phpbb_extension_manager
	* @param \phpbb\user				$user				User object
	* @param string 					$phpbb_root_path 	phpBB root path
	* @param string						$php_ext			phpEx
	*/
	public function __construct(\phpbb\auth\auth $auth, 
								\phpbb\config\config $config, 
								\phpbb\controller\helper $helper, 
								\phpbb\template\template $template, 
								\phpbb\path_helper $path_helper, 
								\phpbb\extension\manager $phpbb_extension_manager, 
								\phpbb\user $user,
								$phpbb_root_path,								
								$php_ext)
	{
		$this->auth 					= $auth;
		$this->config 					= $config;
		$this->controller_helper		= $helper;
		$this->template 				= $template;
		$this->phpbb_path_helper 		= $path_helper;
		$this->phpbb_extension_manager 	= $phpbb_extension_manager;
		$this->user 					= $user;
		$this->phpbb_root_path 			= $phpbb_root_path;
		$this->php_ext 					= $php_ext;
		
//		include($path_helper->update_web_root_path($phpbb_extension_manager->get_extension_path('football/football', true)) . 'includes/constants.' . $php_ext);
	}

	/**
	* Assign functions defined in this class to event listeners in the core
	*
	* @return array
	*/
	static public function getSubscribedEvents()
	{
		return array(
			'core.viewonline_overwrite_location'	=> 'viewonline_page',		
			'core.user_setup'						=> 'load_language_on_setup',
			'core.page_header'						=> 'add_football_links',
			'core.session_kill_after'				=> 'detect_mobile_device',
			'core.session_create_after'				=> 'detect_mobile_device',
			'core.permissions'						=> 'add_permission_cat',
		);
	}
	
	/**
	* Show users as viewing the football prediction league on Who Is Online page
	*
	* @param object $event The event object
	* @return null
	*/
	public function viewonline_page($event)
	{
		global $db;
		if (strrpos($event['row']['session_page'], 'app.' . $this->php_ext . '/football') === 0)
		{
			$action = substr($event['row']['session_page'], 17);
			$url_parts = $this->phpbb_path_helper->get_url_parts($action, false);
			$params = (isset($url_parts['params'])) ? $url_parts['params'] : array();
			if (isset($params['l']) and isset($params['s']))
			{
				include_once($this->phpbb_root_path . 'ext/football/football/includes/constants.' . $this->php_ext);
				$season = $params['s'];
				$league =$params['l'];
				$sql = 'SELECT league_name FROM ' . FOOTB_LEAGUES . " 
						WHERE season = $season AND league = $league
						";
				$result = $db->sql_query($sql);

				if ($row = $db->sql_fetchrow($result))
				{
					$event['location'] = $this->user->lang('VIEWING_LEAGUE' . (empty($url_parts['base']) ? '' : '_' . strtoupper ($url_parts['base'])), $row['league_name']);
				}
				else
				{
					$event['location_url'] = $this->controller_helper->route('football_main_controller', array_merge(array('side' => $url_parts['base']), $url_parts['params']));
				}
				$db->sql_freeresult($result);
			}
			else
			{
				$event['location'] = $this->user->lang('VIEWING_FOOTBALL' . (empty($url_parts['base']) ? '' : '_' . strtoupper ($url_parts['base'])));
			}
			$event['location_url'] = $this->controller_helper->route('football_main_controller', array_merge(array('side' => $url_parts['base']), $url_parts['params']));
		}
	}

	/**
	* Load football prediction league language during user setup
	*
	* @param object $event The event object
	* @return null
	*/
	public function load_language_on_setup($event)
	{
		$lang_set_ext = $event['lang_set_ext'];
		$lang_set_ext[] = array(
				'ext_name' => 'football/football',
				'lang_set' => 'football',
		);
		if (defined('ADMIN_START'))
		{
			$lang_set_ext[] = array(
				'ext_name' => 'football/football',
				'lang_set' => 'permissions_football',
			);
		}
		$event['lang_set_ext'] = $lang_set_ext;
	}

	/**
	* Add football links if user is authed to see it
	*
	* @return null
	*/
	public function add_football_links($event)
	{
		global $request, $cache, $season, $league, $matchday;
		
		$ext_path = $this->phpbb_path_helper->update_web_root_path($this->phpbb_extension_manager->get_extension_path('football/football', true));
		if (!$this->has_football_access())
		{
			return;
		}

		if (isset($this->config['football_side']) && $this->config['football_side'] && !$this->user->data['football_mobile'])
		{
			if (!($wrapped_football_name = $cache->get('wrapped_football_name')))
			{
				$wrapped_football_name = '';
				$string = utf8_decode($this->config['football_name']);
				for($i = 0; $i < strlen($string); $i++)
				{
					$wrapped_football_name .= strtoupper($string[$i]) . '<br />';
				}
				$wrapped_football_name = utf8_encode($wrapped_football_name);
				$cache->put($wrapped_football_name, $wrapped_football_name);
			}
			
			$this->template->assign_vars(array(
				'S_FOOTBALLSIDE'	=> $wrapped_football_name,
				'S_FOOTBALL_SIDE'	=> true,
			));
		}
		
		
		$football_season 	= (empty($this->user->data['football_season'])) ? 0 : $this->user->data['football_season'];
		$football_league 	= (empty($this->user->data['football_league'])) ? 0 : $this->user->data['football_league'];
		$football_matchday 	= (empty($this->user->data['football_matchday'])) ? 0 : $this->user->data['football_matchday'];
		
		$season	= $request->variable('s', $football_season);
		$league	= $request->variable('l', $football_league);
		$matchday	= $request->variable('m', $football_matchday);

		$in_football_ext = (!defined('IN_FOOTBALL')) ? false : IN_FOOTBALL;
		
		$this->template->assign_vars(array(
				'S_DISPLAY_FOOTBALL_MENU' => $this->config['football_menu'],
				'S_FOOTBALL_BREADCRUMB' => $this->config['football_breadcrumb'],
				'S_FOOTBALL_NAME'	=> $this->config['football_name'],
				'S_FOOTBALL_HEADER_LEAGUE'	 => $league,
				'S_FOOTBALL_EXT_PATH' => $ext_path,
				'S_FOOTBALL_HEADER_ENABLED'	 => $this->config['football_header_enable'] ? $in_football_ext : false,
				'U_FOOTBALL'		=> $this->controller_helper->route('football_main_controller', array('side' => 'bet')),
				'U_BET'				=> $this->controller_helper->route('football_main_controller', array('side' => 'bet', 's' => $season, 'l' => $league, 'm' => $matchday)),
				'U_ALL_BETS'		=> $this->controller_helper->route('football_main_controller', array('side' => 'all_bets', 's' => $season, 'l' => $league, 'm' => $matchday)),
				'U_RESULTS'			=> $this->controller_helper->route('football_main_controller', array('side' => 'results', 's' => $season, 'l' => $league, 'm' => $matchday)),
				'U_TABLE'			=> $this->controller_helper->route('football_main_controller', array('side' => 'table', 's' => $season, 'l' => $league, 'm' => $matchday)),
				'U_RANKS_TOTAL'		=> $this->controller_helper->route('football_main_controller', array('side' => 'ranks_total', 's' => $season, 'l' => $league, 'm' => $matchday)),
				'U_RANKS_MATCHDAY'	=> $this->controller_helper->route('football_main_controller', array('side' => 'ranks_matchday', 's' => $season, 'l' => $league, 'm' => $matchday)),
				'U_DELIVERY_LIST'	=> $this->controller_helper->route('football_main_controller', array('side' => 'delivery', 's' => $season, 'l' => $league, 'm' => $matchday)),
				'U_LAST_VISITORS'	=> $this->controller_helper->route('football_main_controller', array('side' => 'last_users', 's' => $season, 'l' => $league, 'm' => $matchday)),
				'U_FOOTBALL_BANK'	=> $this->controller_helper->route('football_main_controller', array('side' => 'bank', 's' => $season, 'l' => $league, 'm' => $matchday)),
				'U_RULES'			=> $this->controller_helper->route('football_football_popup', array('popside' => 'rules_popup', 's' => $season, 'l' => $league)),
				'U_EXPORT'			=> $this->controller_helper->route('football_football_download', array('downside' => 'dload_export', 's' => $season, 'l' => $league)),
				'U_ODDS'			=> $this->controller_helper->route('football_main_controller', array('side' => 'odds', 's' => $season, 'l' => $league, 'm' => $matchday)),
				'S_MENU_LINK1'		=> (strlen($this->config['football_menu_link1']) > 4) ? true : false,
				'U_MENU_LINK1'		=> $this->config['football_menu_link1'],
				'MENU_DESC_LINK1'	=> $this->config['football_menu_desc1'],
				'S_MENU_LINK2'		=> (strlen($this->config['football_menu_link2']) > 4) ? true : false,
				'U_MENU_LINK2'		=> $this->config['football_menu_link2'],
				'MENU_DESC_LINK2'	=> $this->config['football_menu_desc2'],
				'S_MENU_LINK3'		=> (strlen($this->config['football_menu_link3']) > 4) ? true : false,
				'U_MENU_LINK3'		=> (strpos($this->config['football_menu_link3'], 'xml/league.php') === false) ? $this->config['football_menu_link3'] : $this->config['football_menu_link3'] . "&season=$season&league=$league",
				'MENU_DESC_LINK3'	=> $this->config['football_menu_desc3'],
				'U_MY_BETS'			=> $this->controller_helper->route('football_main_controller', array('side' => 'my_bets', 's' => $season, 'l' => $league, 'm' => $matchday)),
				'U_MY_POINTS'		=> $this->controller_helper->route('football_main_controller', array('side' => 'my_points', 's' => $season, 'l' => $league, 'm' => $matchday)),
				'U_MY_TABLE'		=> $this->controller_helper->route('football_main_controller', array('side' => 'my_table', 's' => $season, 'l' => $league, 'm' => $matchday)),
				'U_MY_RANK'			=> $this->controller_helper->route('football_main_controller', array('side' => 'my_rank', 's' => $season, 'l' => $league, 'm' => $matchday)),
				'U_MY_CHART'		=> $this->controller_helper->route('football_main_controller', array('side' => 'my_chart', 's' => $season, 'l' => $league, 'm' => $matchday)),
				'U_MY_KOEFF'		=> $this->controller_helper->route('football_main_controller', array('side' => 'my_koeff', 's' => $season, 'l' => $league, 'm' => $matchday)),
				'U_STAT_RESULTS'	=> $this->controller_helper->route('football_main_controller', array('side' => 'stat_results', 's' => $season, 'l' => $league, 'm' => $matchday)),
				'U_STAT_POINTS'		=> $this->controller_helper->route('football_main_controller', array('side' => 'stat_points', 's' => $season, 'l' => $league, 'm' => $matchday)),
		));
	}

	/**
	* Detect mobile devices
	*
	* @return null
	*/
	public function detect_mobile_device()
	{
		global $db, $request, $mobile_device, $mobile;

		$mobile	= false;
		$user_agent	 = $request->server('HTTP_USER_AGENT');

		switch(true)
		{
			/** Remove Bots from the list */
			case (preg_match('/bot|yahoo|Java|httpget/i', $user_agent));
				$mobile = false;
			break;
			case (preg_match('/ipad/i', $user_agent));
				$mobile_device = 'iPad';
				$mobile = true;
			break;
			case (preg_match('/ipod/i', $user_agent));
				$mobile_device = 'iPod';
				$mobile = true;
			break;
			case (preg_match('/iphone/i', $user_agent));
				$mobile_device = 'iPhone';
				$mobile = true;
			break;
			case (preg_match('/android/i', $user_agent));
				if (preg_match('/SM-G870A|SM-G900A|SM-G900F|SM-G900H|SM-G900M|SM-G900P|SM-G900R4|SM-G900T|SM-G900V|SM-G900W8|SM-G800F/i', $user_agent))
				{
					$mobile_device = 'Samsung S5';
				}
				elseif (preg_match('/SGH-I497/i', $user_agent))
				{
					$mobile_device = 'Samsung Tablet';
				}
				elseif (preg_match('/GT-P5210|SM-T110|SM-T310/i', $user_agent))
				{
					$mobile_device = 'Samsung Tab 3';
				}
				elseif (preg_match('/SM-T335|SM-T530/i', $user_agent))
				{
					$mobile_device = 'Samsung Tab 4';
				}
				elseif (preg_match('/SM-T520/i', $user_agent))
				{
					$mobile_device = 'Samsung TabPRO';
				}
				elseif (preg_match('/SGH-I537|GT-I9505|GT-I9500|SPH-L720T/i', $user_agent))
				{
					$mobile_device = 'Samsung S4';
				}
				elseif (preg_match('/GT-I9100P/i', $user_agent))
				{
					$mobile_device = 'Samsung S2';
				}
				elseif (preg_match('/SM-N7505|SM-N9005|SM-P600/i', $user_agent))
				{
					$mobile_device = 'Samsung Note 3';
				}
				elseif (preg_match('/SM-N910C|SM-N910F/i', $user_agent))
				{
					$mobile_device = 'Samsung Note 4';
				}
				elseif (preg_match('/SM-G357FZ/i', $user_agent))
				{
					$mobile_device = 'Samsung Ace 4';
				}
				elseif (preg_match('/SM-G925P/i', $user_agent))
				{
					$mobile_device = 'Samsung S6 Edge';
				}
				elseif (preg_match('/GT-S7582/i', $user_agent))
				{
					$mobile_device = 'Samsung S Duos 2';
				}
				elseif (preg_match('/GT-I9100P/i', $user_agent))
				{
					$mobile_device = 'Samsung S2';
				}
				elseif (preg_match('/IMM76B/i', $user_agent))
				{
					$mobile_device = 'Samsung Nexus';
				}
				elseif (preg_match('/TF101/i', $user_agent))
				{
					$mobile_device = 'Asus Transformer Tablet';
				}
				elseif (preg_match('/Archos 40b/i', $user_agent))
				{
					$mobile_device = 'Archos 40b Titanium Surround';
				}
				elseif (preg_match('/A0001/i', $user_agent))
				{
					$mobile_device = 'OnePlus One';
				}
				elseif (preg_match('/Orange Nura/i', $user_agent))
				{
					$mobile_device = 'Orange Nura';
				}
				elseif (preg_match('/XT1030/i', $user_agent))
				{
					$mobile_device = 'Motorola Droid Mini';
				}
				elseif (preg_match('/TIANYU-KTOUCH/i', $user_agent))
				{
					$mobile_device = 'Tianyu K-Touch';
				}
				elseif (preg_match('/D2005|D2105/i', $user_agent))
				{
					$mobile_device = 'Sony Xperia E1 Dual';
				}
				elseif (preg_match('/C2005|D2303/i', $user_agent))
				{
					$mobile_device = 'Sony XPERIA M2';
				}
				elseif (preg_match('/C6906/i', $user_agent))
				{
					$mobile_device = 'Sony Xperia Z1';
				}
				elseif (preg_match('/D5803/i', $user_agent))
				{
					$mobile_device = 'Sony Xperia Z3';
				}
				elseif (preg_match('/ASUS_T00J/i', $user_agent))
				{
					$mobile_device = 'ASUS T00J';
				}
				elseif (preg_match('/Aquaris E5/i', $user_agent))
				{
					$mobile_device = 'Aquaris E5 HD';
				}
				elseif (preg_match('/P710/i', $user_agent))
				{
					$mobile_device = 'LG Optimus L7 II';
				}
				elseif (preg_match('/HTC Desire|626s/i', $user_agent))
				{
					$mobile_device = 'HTC Desire';
				}
				elseif (preg_match('/Nexus 4|LRX22C|LVY48F/i', $user_agent))
				{
					$mobile_device = 'Nexus 4';
				}
				elseif (preg_match('/Nexus 5|LMY48S/i', $user_agent))
				{
					$mobile_device = 'Nexus 5';
				}
				elseif (preg_match('/Nexus 7|KTU84P/i', $user_agent))
				{
					$mobile_device = 'Nexus 7';
				}
				elseif (preg_match('/Nexus 9|LMY47X/i', $user_agent))
				{
					$mobile_device = 'Nexus 9';
				}
				elseif (preg_match('/Lenovo_K50_T5/i', $user_agent))
				{
					$mobile_device = 'Lenovo K50-t5';
				}
				elseif (preg_match('/HUAWEI GRA-L09/i', $user_agent))
				{
					$mobile_device = 'HUAWEI P8';
				}
				else
				{
					$mobile_device = 'ANDROID';
				}
				$mobile = true;
			break;
			case (preg_match('/lg/i', $user_agent));
					$mobile_device = 'LG';
				$mobile = true;
			break;
			case (preg_match('/opera mini/i', $user_agent));
				$mobile_device = 'MOBILE';
				$mobile = true;
			break;
			case (preg_match('/blackberry/i', $user_agent));
				if (preg_match('/BlackBerry9900|BlackBerry9930|BlackBerry9790|BlackBerry9780|BlackBerry9700|BlackBerry9650|BlackBerry9000/i', $user_agent))
				{
					$mobile_device = 'BlackBerry Bold';
				}
				elseif (preg_match('/BlackBerry9380|BlackBerry9370|BlackBerry9360|BlackBerry9350|BlackBerry9330|BlackBerry9320|BlackBerry9300|BlackBerry9220|BlackBerry8980|BlackBerry8900|BlackBerry8530|BlackBerry8520|BlackBerry8330|BlackBerry8320|BlackBerry8310|BlackBerry8300/i', $user_agent))
				{
					$mobile_device = 'BlackBerry Curve';
				}
				elseif (preg_match('/BlackBerry9860|BlackBerry9850|BlackBerry9810|BlackBerry9800/i', $user_agent))
				{
					$mobile_device = 'BlackBerry Torch';
				}
				elseif (preg_match('/BlackBerry9900/i', $user_agent))
				{
					$mobile_device = 'BlackBerry Touch';
				}
				elseif (preg_match('/BlackBerry9105/i', $user_agent))
				{
					$mobile_device = 'BlackBerry Pearl';
				}
				elseif (preg_match('/BlackBerry8220/i', $user_agent))
				{
					$mobile_device = 'BlackBerry Pearl Flip';
				}
				elseif (preg_match('/BlackBerry PlayBook|BlackBerry Porsche|BlackBerry Passport|BlackBerry Storm|BlackBerry Storm2/', $user_agent, $match_device))
				{
					$mobile_device = $match_device[0];
				}
				else
				{
				$mobile_device = 'BlackBerry';
				}
				$mobile = true;
			break;
			case (preg_match('/(pre\/|palm os|palm|hiptop|avantgo|plucker|xiino|blazer|elaine)/i', $user_agent));
				$mobile_device = 'Palm';
				$mobile = true;
			break;
			case (preg_match('/(iris|3g_t|windows ce|windows Phone|opera mobi|windows ce; smartphone;|windows ce; iemobile)/i', $user_agent));
				$mobile_device = 'Windows Smartphone';
				$mobile = true;
			break;
			case (preg_match('/lge vx10000/i', $user_agent));
				$mobile_device = 'Voyager';
				$mobile = true;
			break;
			case (preg_match('/(mini 9.5|vx1000|lge |m800|e860|u940|ux840|compal|wireless| mobi|ahong|lg380|lgku|lgu900|lg210|lg47|lg920|lg840|lg370|sam-r|mg50|s55|g83|t66|vx400|mk99|d615|d763|el370|sl900|mp500|samu3|samu4|vx10|xda_|samu5|samu6|samu7|samu9|a615|b832|m881|s920|n210|s700|c-810|_h797|mob-x|sk16d|848b|mowser|s580|r800|471x|v120|rim8|c500foma:|160x|x160|480x|x640|t503|w839|i250|sprint|w398samr810|m5252|c7100|mt126|x225|s5330|s820|htil-g1|fly v71|s302|-x113|novarra|k610i|-three|8325rc|8352rc|sanyo|vx54|c888|nx250|n120|mtk |c5588|s710|t880|c5005|i;458x|p404i|s210|c5100|teleca|s940|c500|s590|foma|samsu|vx8|vx9|a1000|_mms|myx|a700|gu1100|bc831|e300|ems100|me701|me702m-three|sd588|s800|8325rc|ac831|mw200|brew |d88|htc\/|htc_touch|355x|m50|km100|d736|p-9521|telco|sl74|ktouch|m4u\/|me702|8325rc|kddi|phone|lg |sonyericsson|samsung|240x|x320|vx10|nokia|sony cmd|motorola|up.browser|up.link|mmp|symbian|smartphone|midp|wap|vodafone|o2|pocket|kindle|mobile|psp|treo)/i', $user_agent));
				$mobile_device = 'MOBILE';
				$mobile = true;
			break;
			case (isset($post['HTTP_X_WAP_PROFILE'])||isset($post['HTTP_PROFILE']));
				$mobile_device = 'MOBILE';
				$mobile = true;
			break;
			case (in_array(strtolower(substr($user_agent,0,4)),array('1207'=>'1207','3gso'=>'3gso','4thp'=>'4thp','501i'=>'501i','502i'=>'502i','503i'=>'503i','504i'=>'504i','505i'=>'505i','506i'=>'506i','6310'=>'6310','6590'=>'6590','770s'=>'770s','802s'=>'802s','a wa'=>'a wa','acer'=>'acer','acs-'=>'acs-','airn'=>'airn','alav'=>'alav','asus'=>'asus','attw'=>'attw','au-m'=>'au-m','aur '=>'aur ','aus '=>'aus ','abac'=>'abac','acoo'=>'acoo','aiko'=>'aiko','alco'=>'alco','alca'=>'alca','amoi'=>'amoi','anex'=>'anex','anny'=>'anny','anyw'=>'anyw','aptu'=>'aptu','arch'=>'arch','argo'=>'argo','bell'=>'bell','bird'=>'bird','bw-n'=>'bw-n','bw-u'=>'bw-u','beck'=>'beck','benq'=>'benq','bilb'=>'bilb','blac'=>'blac','c55/'=>'c55/','cdm-'=>'cdm-','chtm'=>'chtm','capi'=>'capi','cond'=>'cond','craw'=>'craw','dall'=>'dall','dbte'=>'dbte','dc-s'=>'dc-s','dica'=>'dica','ds-d'=>'ds-d','ds12'=>'ds12','dait'=>'dait','devi'=>'devi','dmob'=>'dmob','doco'=>'doco','dopo'=>'dopo','el49'=>'el49','erk0'=>'erk0','esl8'=>'esl8','ez40'=>'ez40','ez60'=>'ez60','ez70'=>'ez70','ezos'=>'ezos','ezze'=>'ezze','elai'=>'elai','emul'=>'emul','eric'=>'eric','ezwa'=>'ezwa','fake'=>'fake','fly-'=>'fly-','fly_'=>'fly_','g-mo'=>'g-mo','g1 u'=>'g1 u','g560'=>'g560','gf-5'=>'gf-5','grun'=>'grun','gene'=>'gene','go.w'=>'go.w','good'=>'good','grad'=>'grad','hcit'=>'hcit','hd-m'=>'hd-m','hd-p'=>'hd-p','hd-t'=>'hd-t','hei-'=>'hei-','hp i'=>'hp i','hpip'=>'hpip','hs-c'=>'hs-c','htc '=>'htc ','htc-'=>'htc-','htca'=>'htca','htcg'=>'htcg','htcp'=>'htcp','htcs'=>'htcs','htct'=>'htct','htc_'=>'htc_','haie'=>'haie','hita'=>'hita','huaw'=>'huaw','hutc'=>'hutc','i-20'=>'i-20','i-go'=>'i-go','i-ma'=>'i-ma','i230'=>'i230','iac'=>'iac','iac-'=>'iac-','iac/'=>'iac/','ig01'=>'ig01','im1k'=>'im1k','inno'=>'inno','iris'=>'iris','jata'=>'jata','java'=>'java','kddi'=>'kddi','kgt'=>'kgt','kgt/'=>'kgt/','kpt '=>'kpt ','kwc-'=>'kwc-','klon'=>'klon','lexi'=>'lexi','lg g'=>'lg g','lg-a'=>'lg-a','lg-b'=>'lg-b','lg-c'=>'lg-c','lg-d'=>'lg-d','lg-f'=>'lg-f','lg-g'=>'lg-g','lg-k'=>'lg-k','lg-l'=>'lg-l','lg-m'=>'lg-m','lg-o'=>'lg-o','lg-p'=>'lg-p','lg-s'=>'lg-s','lg-t'=>'lg-t','lg-u'=>'lg-u','lg-w'=>'lg-w','lg/k'=>'lg/k','lg/l'=>'lg/l','lg/u'=>'lg/u','lg50'=>'lg50','lg54'=>'lg54','lge-'=>'lge-','lge/'=>'lge/','lynx'=>'lynx','leno'=>'leno','m1-w'=>'m1-w','m3ga'=>'m3ga','m50/'=>'m50/','maui'=>'maui','mc01'=>'mc01','mc21'=>'mc21','mcca'=>'mcca','medi'=>'medi','meri'=>'meri','mio8'=>'mio8','mioa'=>'mioa','mo01'=>'mo01','mo02'=>'mo02','mode'=>'mode','modo'=>'modo','mot '=>'mot ','mot-'=>'mot-','mt50'=>'mt50','mtp1'=>'mtp1','mtv '=>'mtv ','mate'=>'mate','maxo'=>'maxo','merc'=>'merc','mits'=>'mits','mobi'=>'mobi','motv'=>'motv','mozz'=>'mozz','n100'=>'n100','n101'=>'n101','n102'=>'n102','n202'=>'n202','n203'=>'n203','n300'=>'n300','n302'=>'n302','n500'=>'n500','n502'=>'n502','n505'=>'n505','n700'=>'n700','n701'=>'n701','n710'=>'n710','nec-'=>'nec-','nem-'=>'nem-','newg'=>'newg','neon'=>'neon','netf'=>'netf','noki'=>'noki','nzph'=>'nzph','o2 x'=>'o2 x','o2-x'=>'o2-x','opwv'=>'opwv','owg1'=>'owg1','opti'=>'opti','oran'=>'oran','p800'=>'p800','pand'=>'pand','pg-1'=>'pg-1','pg-2'=>'pg-2','pg-3'=>'pg-3','pg-6'=>'pg-6','pg-8'=>'pg-8','pg-c'=>'pg-c','pg13'=>'pg13','phil'=>'phil','pn-2'=>'pn-2','pt-g'=>'pt-g','palm'=>'palm','pana'=>'pana','pire'=>'pire','pock'=>'pock','pose'=>'pose','psio'=>'psio','qa-a'=>'qa-a','qc-2'=>'qc-2','qc-3'=>'qc-3','qc-5'=>'qc-5','qc-7'=>'qc-7','qc07'=>'qc07','qc12'=>'qc12','qc21'=>'qc21','qc32'=>'qc32','qc60'=>'qc60','qci-'=>'qci-','qwap'=>'qwap','qtek'=>'qtek','r380'=>'r380','r600'=>'r600','raks'=>'raks','rim9'=>'rim9','rove'=>'rove','s55/'=>'s55/','sage'=>'sage','sams'=>'sams','sc01'=>'sc01','sch-'=>'sch-','scp-'=>'scp-','sdk/'=>'sdk/','se47'=>'se47','sec-'=>'sec-','sec0'=>'sec0','sec1'=>'sec1','semc'=>'semc','sgh-'=>'sgh-','shar'=>'shar','sie-'=>'sie-','sk-0'=>'sk-0','sl45'=>'sl45','slid'=>'slid','smb3'=>'smb3','smt5'=>'smt5','sp01'=>'sp01','sph-'=>'sph-','spv '=>'spv ','spv-'=>'spv-','sy01'=>'sy01','samm'=>'samm','sany'=>'sany','sava'=>'sava','scoo'=>'scoo','send'=>'send','siem'=>'siem','smar'=>'smar','smit'=>'smit','soft'=>'soft','sony'=>'sony','t-mo'=>'t-mo','t218'=>'t218','t250'=>'t250','t600'=>'t600','t610'=>'t610','t618'=>'t618','tcl-'=>'tcl-','tdg-'=>'tdg-','telm'=>'telm','tim-'=>'tim-','ts70'=>'ts70','tsm-'=>'tsm-','tsm3'=>'tsm3','tsm5'=>'tsm5','tx-9'=>'tx-9','tagt'=>'tagt','talk'=>'talk','teli'=>'teli','topl'=>'topl','hiba'=>'hiba','up.b'=>'up.b','upg1'=>'upg1','utst'=>'utst','v400'=>'v400','v750'=>'v750','veri'=>'veri','vk-v'=>'vk-v','vk40'=>'vk40','vk50'=>'vk50','vk52'=>'vk52','vk53'=>'vk53','vm40'=>'vm40','vx98'=>'vx98','virg'=>'virg','vite'=>'vite','voda'=>'voda','vulc'=>'vulc','w3c '=>'w3c ','w3c-'=>'w3c-','wapj'=>'wapj','wapp'=>'wapp','wapu'=>'wapu','wapm'=>'wapm','wig '=>'wig ','wapi'=>'wapi','wapr'=>'wapr','wapv'=>'wapv','wapy'=>'wapy','wapa'=>'wapa','waps'=>'waps','wapt'=>'wapt','winc'=>'winc','winw'=>'winw','wonu'=>'wonu','x700'=>'x700','xda2'=>'xda2','xdag'=>'xdag','yas-'=>'yas-','your'=>'your','zte-'=>'zte-','zeto'=>'zeto','acs-'=>'acs-','alav'=>'alav','alca'=>'alca','amoi'=>'amoi','aste'=>'aste','audi'=>'audi','avan'=>'avan','benq'=>'benq','bird'=>'bird','blac'=>'blac','blaz'=>'blaz','brew'=>'brew','brvw'=>'brvw','bumb'=>'bumb','ccwa'=>'ccwa','cell'=>'cell','cldc'=>'cldc','cmd-'=>'cmd-','dang'=>'dang','doco'=>'doco','eml2'=>'eml2','eric'=>'eric','fetc'=>'fetc','hipt'=>'hipt','http'=>'http','ibro'=>'ibro','idea'=>'idea','ikom'=>'ikom','inno'=>'inno','ipaq'=>'ipaq','jbro'=>'jbro','jemu'=>'jemu','java'=>'java','jigs'=>'jigs','kddi'=>'kddi','keji'=>'keji','kyoc'=>'kyoc','kyok'=>'kyok','leno'=>'leno','lg-c'=>'lg-c','lg-d'=>'lg-d','lg-g'=>'lg-g','lge-'=>'lge-','libw'=>'libw','m-cr'=>'m-cr','maui'=>'maui','maxo'=>'maxo','midp'=>'midp','mits'=>'mits','mmef'=>'mmef','mobi'=>'mobi','mot-'=>'mot-','moto'=>'moto','mwbp'=>'mwbp','mywa'=>'mywa','nec-'=>'nec-','newt'=>'newt','nok6'=>'nok6','noki'=>'noki','o2im'=>'o2im','opwv'=>'opwv','palm'=>'palm','pana'=>'pana','pant'=>'pant','pdxg'=>'pdxg','phil'=>'phil','play'=>'play','pluc'=>'pluc','port'=>'port','prox'=>'prox','qtek'=>'qtek','qwap'=>'qwap','rozo'=>'rozo','sage'=>'sage','sama'=>'sama','sams'=>'sams','sany'=>'sany','sch-'=>'sch-','sec-'=>'sec-','send'=>'send','seri'=>'seri','sgh-'=>'sgh-','shar'=>'shar','sie-'=>'sie-','siem'=>'siem','smal'=>'smal','smar'=>'smar','sony'=>'sony','sph-'=>'sph-','symb'=>'symb','t-mo'=>'t-mo','teli'=>'teli','tim-'=>'tim-','tosh'=>'tosh','treo'=>'treo','tsm-'=>'tsm-','upg1'=>'upg1','upsi'=>'upsi','vk-v'=>'vk-v','voda'=>'voda','vx52'=>'vx52','vx53'=>'vx53','vx60'=>'vx60','vx61'=>'vx61','vx70'=>'vx70','vx80'=>'vx80','vx81'=>'vx81','vx83'=>'vx83','vx85'=>'vx85','wap-'=>'wap-','wapa'=>'wapa','wapi'=>'wapi','wapp'=>'wapp','wapr'=>'wapr','webc'=>'webc','whit'=>'whit','winw'=>'winw','wmlb'=>'wmlb','xda-'=>'xda-',)));
				$mobile_device = 'MOBILE';
				$mobile = true;
			break;
			default;
				$mobile_device = 'Desktop';
				$mobile = false;
			break;
		}
		
		// Write to sessions table
		$sql_ary = array(
			'football_mobile'			=> $mobile,
			'football_mobile_device'	=> $db->sql_escape($mobile_device),
		);
		$sql = 'UPDATE ' . SESSIONS_TABLE . ' SET ' . $db->sql_build_array('UPDATE', $sql_ary) . "
			WHERE session_id = '" . $db->sql_escape($this->user->session_id) . "'";
		$db->sql_query($sql);
		$this->user->data['football_mobile'] = $mobile;
		$this->user->data['football_mobile_device'] = $mobile_device;
	}
	
	/**
	 * Check if user should be able to access football prediction league
	 *
	 * @return bool True of user should be able to access it, false if not
	 */
	protected function has_football_access()
	{
		// Can this user view Prediction Leagues pages?
		if (!$this->config['football_guest_view'])
		{
			if ($this->user->data['user_id'] == ANONYMOUS)
			{
				return false;
			}
		}
		if (!$this->config['football_user_view'])
		{
			return $this->auth->acl_get('u_use_football') && ! $this->config['football_disable'];
		}
		return ! $this->config['football_disable'];
	}

	public function add_permission_cat($event)
	{
		$perm_cat = $event['categories'];
		$perm_cat['football'] = 'ACP_FOOTBALL';
		$event['categories'] = $perm_cat;

		$permission = $event['permissions'];
		$permission['u_use_football']		= array('lang' => 'ACL_U_USE_FOOTBALL',			'cat' => 'football');
		$permission['a_football_config']	= array('lang' => 'ACL_A_FOOTBALL_CONFIG',		'cat' => 'football');
		$permission['a_football_delete']	= array('lang' => 'ACL_A_FOOTBALL_DELETE',		'cat' => 'football');
		$permission['a_football_editbets']	= array('lang' => 'ACL_A_FOOTBALL_EDITBETS',	'cat' => 'football');
		$permission['a_football_plan']		= array('lang' => 'ACL_A_FOOTBALL_PLAN',		'cat' => 'football');
		$permission['a_football_results']	= array('lang' => 'ACL_A_FOOTBALL_RESULTS',		'cat' => 'football');
		$permission['a_football_points']	= array('lang' => 'ACL_A_FOOTBALL_POINTS',		'cat' => 'football');
		$event['permissions'] = $permission;
	}	
}
