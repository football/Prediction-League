<?php
/**
*
* @package Football Football v0.94
* @copyright (c) 2015 football (http://football.bplaced.net)
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace football\football\migrations;
/**
* Primary migration
*/
class profilefield_footb_email extends \phpbb\db\migration\profilefield_base_migration
{
	public function effectively_installed()
	{
		$sql = 'SELECT COUNT(field_id) as field_count
			FROM ' . PROFILE_FIELDS_TABLE . " 
			WHERE field_name = 'footb_email'";

		// and count..
		$result = $this->db->sql_query($sql);
		$field_count = (int) $this->db->sql_fetchfield('field_count');
		$this->db->sql_freeresult($result);

		// Skip migration if custom profile field exist
		return $field_count;
	}
	
	static public function depends_on()
	{
		return array('\football\football\migrations\v094_beta');
	}
	
	public function update_data()
	{
		return array(
			array('custom', array(array($this, 'create_custom_field'))),
		);
	}
	
	protected $profilefield_name = 'footb_email';
	protected $profilefield_database_type = array('VCHAR', '');
	protected $profilefield_data = array(
		'field_name'	=> 'footb_email',
		'field_type'	=> 'profilefields.type.string',
		'field_ident'	=> 'footb_email',
		'field_length'			=> 30,
		'field_minlen'			=> 0,
		'field_maxlen'			=> 100,
		'field_novalue'			=> '',
		'field_default_value'	=> '',
		'field_validation'		=> '.*',
		'field_required'		=> 0,
		'field_show_novalue'	=> 0,
		'field_show_on_reg'		=> 1,
		'field_show_on_pm'		=> 0,
		'field_show_on_vt'		=> 0,
		'field_show_profile'	=> 1,
		'field_hide'			=> 0,
		'field_no_view'			=> 0,
		'field_active'			=> 1,
	);
	
	public function create_custom_field()
	{
		parent::create_custom_field();
		$lang_name = (strpos($this->profilefield_name, 'phpbb_') === 0) ? strtoupper(substr($this->profilefield_name, 6)) : strtoupper($this->profilefield_name);
		$lang_update = array(
			'lang_explain'			=> $lang_name . '_EXPLAIN',
		);
		$sql = 'SELECT field_id
			FROM ' . PROFILE_FIELDS_TABLE . '
			WHERE field_ident = "' . $this->profilefield_data['field_ident'] . '"';
		$result = $this->db->sql_query($sql);
		$field_id = (int) $this->db->sql_fetchfield('field_id');
		$this->db->sql_freeresult($result);
		$this->db->sql_transaction('begin');
		$sql = 'SELECT lang_id
			FROM ' . LANG_TABLE;
		$result = $this->db->sql_query($sql);
		while ($lang_id = (int) $this->db->sql_fetchfield('lang_id'))
		{
			$sql = 'UPDATE ' . PROFILE_LANG_TABLE . '
				SET ' . $this->db->sql_build_array('UPDATE', $lang_update) . '
				WHERE field_id = ' . $field_id;
			$this->db->sql_query($sql);
		}
		$this->db->sql_freeresult($result);
		$this->db->sql_transaction('commit');
	}
}