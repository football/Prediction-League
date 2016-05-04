<?php
/**
*
* @package Football Football v0.95
* @copyright (c) 2015 football (http://football.bplaced.net)
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace football\football\migrations;

class v095_beta extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return isset($this->config['football_breadcrumb']);
	}
	
	static public function depends_on()
	{
		return array('\football\football\migrations\v094_beta');
	}

	public function update_data()
	{
		return array(
			array('config.add', array('football_side', '0', '0')),
			array('config.add', array('football_breadcrumb', '1', '0')),
			array('config.update', array('football_version', '0.9.5', '0')),
		);
	}
}
