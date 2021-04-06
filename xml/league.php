<?php
/**
*
* @package phpBB Extension - Football Football
* @copyright (c) 2016 football (http://football.bplaced.net)
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

/*
* Automatically write the league as XML-file
*/

if (!defined('IN_PHPBB'))
{
	// Stuff required to work with phpBB3
	define('IN_PHPBB', true);
	$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './../../../../';
	$phpEx = substr(strrchr(__FILE__, '.'), 1);
	include($phpbb_root_path . 'common.' . $phpEx);
 
	// Start session management
	$user->session_begin();
	$auth->acl($user->data);
	$user->setup();
	$user->add_lang_ext('football/football', 'info_acp_update');
	include('../includes/constants.' . $phpEx);	

	if ($config['board_disable'])
	{
		$message = (!empty($config['board_disable_msg'])) ? $config['board_disable_msg'] : 'BOARD_DISABLE';
		trigger_error($message);
	}
	
	$season = $request->variable('season', 0);
	$league = $request->variable('league', 0);
	if (!$season or !$league)
	{
		exit;
	}
	
	$download = $request->variable('d', false);
	$xml_string = xml_data($season, $league);
	
	if ( $xml_string == '')
	{
		trigger_error('Fehler! Die XML-Datei konnte nicht erzeugt werden.');
	}

	if ($download)
	{
		// Download XML-File
		$filename = 'league_' . $season . '_' . $league . '.xml';
		$fp = fopen('php://output', 'w');
		
		header('Pragma: no-cache');
		header("Content-Type: application/xml name=\"" . basename($filename) . "\"");
		header("Content-disposition: attachment; filename=\"" . basename($filename) . "\"");
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Cache-Control: private', false);
		header('Pragma: public');
		
		fwrite($fp, $xml_string);
		fclose($fp);
		exit_handler();
	}
	else
	{
		// XML header
		header ("content-type: text/xml");
		echo $xml_string;
	}
}

function xml_data($season, $league)
{
	global $db, $phpbb_root_path, $phpEx, $table_prefix;

	$xml_data = '';
	
	$xml_league_data = xml_table($season, $league, 'FOOTB_SEASONS');
	$xml_league_data .= xml_table($season, $league, 'FOOTB_LEAGUES');
	$xml_league_data .= xml_table($season, $league, 'FOOTB_MATCHDAYS');
	$xml_league_data .= xml_table($season, $league, 'FOOTB_TEAMS');
	$xml_league_data .= xml_table($season, $league, 'FOOTB_MATCHES');

	if ( $xml_league_data <> '' )
	{
		$xml_data = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
		$xml_data .= '<?xml-stylesheet type="text/xsl" href="league-data.prosilver.xsl"?>' . "\n";
		$xml_data .= '<!--NOTICE: Please open this file in your web browser. If presented with a security warning, you may safely tell it to allow the blocked content.-->' . "\n";
		$xml_data .= '<league-data xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns="http://football.bplaced.net/ext/football/football/xml/league-data-0.9.4.xsd">' . "\n";
		$xml_data .= $xml_league_data;
		$xml_data .= '</league-data>';
	}
	return $xml_data;
}


function xml_table($season, $league, $table)
{
	global $db, $phpbb_root_path, $phpEx, $table_prefix;
	
	$xml_table = '';
	$skip_fields = array("trend", "odd_1", "odd_x", "odd_2", "rating");

	$table_name = constant($table);
	$where_league = ($table == 'FOOTB_SEASONS') ? '' : " AND league = $league";
	$sql = 'SELECT *
		FROM ' . $table_name . "
		WHERE season = $season
		$where_league
		ORDER BY 1, 2, 3;";
	if ( $result = $db->sql_query($sql) )
	{
		while($row = $db->sql_fetchrow($result))
		{
			$xml_table .= "	<" . strtolower($table) . ">" . "\n";
			foreach($row as $fieldname => $value)
			{
				switch ($fieldname)
				{
					case 'win_result':
					case 'win_result_02':
					case 'win_matchday':
					case 'win_season':
					case 'points_last':
					case 'join_by_user':
					case 'join_in_season':
					case 'rules_post_id':
					case 'bet_points':
						{
							$value = 0;
						}
					break;
					case 'status':
						{
							// only match status 0-3
							$value = ($value > 3) ? $value - 3 : $value;
						}
					break;
				}
				if (!in_array($fieldname, $skip_fields, TRUE) )
				{
					if (!isset($value) || is_null($value))
					{
						$xml_table .= "		<$fieldname>'NULL'</$fieldname>" . "\n";
					}
					else
					{
						$xml_table .= "		<$fieldname>" . $value . "</$fieldname>" . "\n";
					}
				}
			}
			$xml_table .= "	</" . strtolower($table) . ">" . "\n";
		}
	}
	$db->sql_freeresult($result);
	return $xml_table;
}
