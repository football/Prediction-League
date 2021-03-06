<?php
/**
*
* @package phpBB Extension - Football Football
* @copyright (c) 2016 football (http://football.bplaced.net)
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace football\football\acp;

class bets_info
{
	function module()
	{
		return array(
			'filename'	=> '\football\football\acp\bets_module',
			'title'		=> 'ACP_FOOTBALL_BETS_MANAGEMENT',
			'version'	=> '0.9.4',
			'modes'		=> array(
				'manage'	=> array('title' => 'ACP_FOOTBALL_BETS_MANAGE', 'auth' => 'acl_a_football_editbets', 'cat' => array('ACP_FOOTBALL_BETS')),
			),
		);
	}
}
