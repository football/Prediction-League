<?php
/**
*
* @package phpBB Extension - Football Football
* @copyright (c) 2016 football (http://football.bplaced.net)
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace football\football\acp;

class results_info
{
	function module()
	{
		return array(
			'filename'	=> '\football\football\acp\results_module',
			'title'		=> 'ACP_FOOTBALL_RESULTS_MANAGEMENT',
			'version'	=> '1.0.0',
			'modes'		=> array(
				'manage'		=> array('title' => 'ACP_FOOTBALL_RESULTS_MANAGE', 'auth' => 'acl_a_football_results', 'cat' => array('ACP_FOOTBALL_RESULTS')),
			),
		);
	}
}
