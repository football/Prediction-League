<?php
/**
*
* @package Football Football v0.9.9
* @copyright (c) 2017 football (http://football.bplaced.net)
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace football\football\migrations;

class v099_beta extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return isset($this->config['football_version']) && version_compare($this->config['football_version'], '0.9.9', '>=');
	}
	
	static public function depends_on()
	{
		return array('\football\football\migrations\v098_beta');
	}

	public function update_data()
	{
		return array(
			array('config.remove', array('football_host_timezone')),
			array('config.add', array('football_time_shift', '1', '0')),
			array('config.add', array('football_display_last_users', '5', '0')),
			array('config.add', array('football_display_last_results', '0', '0')),
			array('config.update', array('football_version', '0.9.9', '0')),
		);
	}
}
