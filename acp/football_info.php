<?php
/**
*
* @package phpBB Extension - Football Football
* @copyright (c) 2016 football (http://football.bplaced.net)
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace football\football\acp;

class football_info
{
	function module()
	{
		return array(
			'filename'	=> '\football\football\acp\football_module',
			'title'		=> 'ACP_FOOTBALL_MANAGEMENT',
			'version'	=> '0.9.4',
			'modes'		=> array(
				'settings'	=> array('title' => 'ACP_FOOTBALL_SETTINGS', 'auth' => 'acl_a_football_config', 'cat' => array('ACP_FOOTBALL_CONFIGURATION')),
				'features'	=> array('title' => 'ACP_FOOTBALL_FEATURES', 'auth' => 'acl_a_football_config', 'cat' => array('ACP_FOOTBALL_CONFIGURATION')),
				'menu'		=> array('title' => 'ACP_FOOTBALL_MENU',	 'auth' => 'acl_a_football_config', 'cat' => array('ACP_FOOTBALL_CONFIGURATION')),
				'userguide'	=> array('title' => 'ACP_FOOTBALL_USERGUIDE','auth' => 'acl_a_football_plan',   'cat' => array('ACP_FOOTBALL_CONFIGURATION')),
			),
		);
	}
}
