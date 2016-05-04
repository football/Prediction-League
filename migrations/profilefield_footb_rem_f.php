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
class profilefield_footb_rem_f extends \phpbb\db\migration\profilefield_base_migration
{
	public function effectively_installed()
	{
		$sql = 'SELECT COUNT(field_id) as field_count
			FROM ' . PROFILE_FIELDS_TABLE . " 
			WHERE field_name = 'footb_rem_f'";

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
			array('custom', array(array($this, 'create_language_entries'))),
		);
	}
	
	protected $profilefield_name = 'footb_rem_f';
	protected $profilefield_database_type = array('UINT:2', 2);
	protected $profilefield_data = array(
		'field_name'	=> 'footb_rem_f',
		'field_type'	=> 'profilefields.type.bool',
		'field_ident'	=> 'footb_rem_f',
		'field_length'			=> 1,
		'field_minlen'			=> 0,
		'field_maxlen'			=> 0,
		'field_novalue'			=> 0,
		'field_default_value'	=> 2,
		'field_validation'		=> '',
		'field_required'		=> 0,
		'field_show_novalue'	=> 0,
		'field_show_on_reg'		=> 1,
		'field_show_on_pm'		=> 0,
		'field_show_on_vt'		=> 1,
		'field_show_profile'	=> 1,
		'field_hide'			=> 0,
		'field_no_view'			=> 0,
		'field_active'			=> 1,
		'field_is_contact'		=> 0,
	);
	
	protected $profilefield_language_data = array(
		array(
			'option_id'	=> 0,
			'field_type'	=> 'profilefields.type.bool',
			'lang_value'	=> 'Yes',
		),
		array(
			'option_id'	=> 1,
			'field_type'	=> 'profilefields.type.bool',
			'lang_value'	=> 'No',
		),
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
	
	public function create_language_entries()
	{
		parent::create_language_entries();
		// Update German language values
		$sql = 'SELECT field_id
			FROM ' . PROFILE_FIELDS_TABLE . '
			WHERE field_ident = "' . $this->profilefield_data['field_ident'] . '"';
		$result = $this->db->sql_query($sql);
		$field_id = (int) $this->db->sql_fetchfield('field_id');
		$this->db->sql_freeresult($result);
		$sql = 'SELECT lang_id
			FROM ' . LANG_TABLE . '
			WHERE lang_local_name like "Deutsch%"';
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$sql = 'UPDATE ' . PROFILE_FIELDS_LANG_TABLE . '
				SET lang_value = "Ja" 
				WHERE field_id = ' . $field_id . '
				AND lang_id = ' . $row['lang_id'] . '
				AND option_id = 0';
			$this->db->sql_query($sql);
			$sql = 'UPDATE ' . PROFILE_FIELDS_LANG_TABLE . '
				SET lang_value = "Nein" 
				WHERE field_id = ' . $field_id . '
				AND lang_id = ' . $row['lang_id'] . '
				AND option_id = 1';
			$this->db->sql_query($sql);
		}
		$this->db->sql_freeresult($result);
	}
}