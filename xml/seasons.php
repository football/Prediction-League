<?php
/**
*
* @package phpBB Extension - Football Football
* @copyright (c) 2016 football (http://football.bplaced.net)
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

/*
* Automatically write the seasons and leagues as XML-file
*/

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

//Check Access Code
global $code;
$code = $request->variable('code', '');
if (strcmp($code, trim($config['football_update_code'])) <> 0)
{
	trigger_error('ERROR_XML_CODE');
}

$string = xml_seasons();

if ( $string == '')
{
	trigger_error('ERROR_XML_CREATE');
}

header ("content-type: text/xml");
echo $string;

function xml_seasons()
{
	global $db, $phpbb_root_path, $phpEx, $table_prefix, $code, $ext_path;
	
	$xml_seasons = '';
	$sql = 'SELECT s.season, s.season_name_short, l.league, l.league_name 
		FROM ' . FOOTB_SEASONS . ' AS s
		JOIN ' . FOOTB_LEAGUES . ' AS l ON (l.season = s.season)
		WHERE 1
		ORDER BY s.season DESC, l.league ASC;';
	$last_season = 0;
	$data = false;
	if ( $result = $db->sql_query($sql) )
	{
		$xml_seasons = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
		$xml_seasons .= '<?xml-stylesheet type="text/xsl" href="seasons-data.prosilver.xsl"?>' . "\n";
		$xml_seasons .= '<!--NOTICE: Please open this file in your web browser. If presented with a security warning, you may safely tell it to allow the blocked content.-->' . "\n";
		$xml_seasons .= '<seasons-data xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns="http://football.bplaced.net/ext/football/football/xml/seasons-data-0.9.4.xsd">' . "\n";
		$xml_seasons .= '	<code>' . $code . "</code>\n";

		while($row = $db->sql_fetchrow($result))
		{
			if ( $row['season'] <> $last_season )
			{
				if ($data)
				{
					$xml_seasons .= '	</season>' . "\n";
				}
				$xml_seasons .= '	<season>' . "\n";
				$xml_seasons .= "		<season_id>" . $row['season'] . "</season_id>" . "\n";
				$xml_seasons .= "		<season_name_short>" . $row['season_name_short'] . "</season_name_short>" . "\n";
				$data = true;
				$last_season = $row['season'];
			}
			$xml_seasons .= '		<league>' . "\n";
			$xml_seasons .= "			<league_id>" . $row['league'] . "</league_id>" . "\n";
			$xml_seasons .= "			<league_name>" . $row['league_name'] . "</league_name>" . "\n";
			$xml_seasons .= '		</league>' . "\n";
		}
		if ($data)
		{
			$xml_seasons .= '	</season>' . "\n";
		}
		$xml_seasons .= '</seasons-data>' . "\n";
	}
	$db->sql_freeresult($result);
	return $xml_seasons;
}
