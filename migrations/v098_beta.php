<?php
/**
*
* @package Football Football v0.98
* @copyright (c) 2017 football (http://football.bplaced.net)
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace football\football\migrations;

class v098_beta extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return isset($this->config['football_version']) && version_compare($this->config['football_version'], '0.9.8', '>=');
	}
	
	static public function depends_on()
	{
		return array('\football\football\migrations\v097_beta');
	}

	public function update_data()
	{
		return array(
			array('config.update', array('football_version', '0.9.8', '0')),
		);
	}
}
