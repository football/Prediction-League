<?php
/**
*
* info_acp_update.php [en]
*
* @package phpBB Extension - Football Football
* @copyright (c) 2016 football (http://football.bplaced.net)
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

/**
* DO NOT CHANGE
*/
if (!defined('IN_PHPBB'))
{
	exit;
}


if (empty($lang) || !is_array($lang))
{
	$lang = array();
}


// DEVELOPERS PLEASE NOTE
//
// All language files should use UTF-8 as their encoding and the files must not contain a BOM.
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine

$lang = array_merge($lang, array(
	'ACP_FOOTBALL_UPDATE'					=> 'Update fixture lists',
	'ACP_FOOTBALL_UPDATE_MANAGE'			=> 'Update fixture lists',
	'ACP_FOOTBALL_UPDATE_MANAGE_EXPLAIN'	=> 'Here you can update fixture lists with data from the demo page or another Football Extension site. First, choose a league of the source site that sent the update. After selecting the destination league you get differences between this league and the update displayed and can choose the changes you want to be updated. If you want to create a new league, all data is transferred. ',
	'ACP_FOOTBALL_UPDATE_MANAGEMENT'		=> 'Update fixture lists',
	'ALLOW_URL_FOPEN'						=> 'The PHP-setting <var>allow_url_fopen</var> does not allow access to other sites. <br />You have to download xml update-files and save them in root/store. ',
	'CHOOSE_LEAGUES'						=> 'Select source and target fixture list',
	'CHOOSE_OTHER_LEAGUE'					=> 'Select other fixture list',
	'COMPARE_UPDATE'						=> 'Compare with database',
	'CURRENT_VALUE'							=> 'Current database value',
	'DB_INSERT_SEASON'						=> '%s season inserted. ',
	'DB_INSERT_LEAGUE'						=> '%s league inserted. ',
	'DB_INSERT_MATCHDAY'					=> '%s matchday inserted. ',
	'DB_INSERT_MATCHDAYS'					=> '%s matchdays inserted. ',
	'DB_INSERT_TEAM'						=> '%s team inserted. ',
	'DB_INSERT_TEAMS'						=> '%s teams inserted. ',
	'DB_INSERT_MATCH'						=> '%s match inserted. ',
	'DB_INSERT_MATCHES'						=> '%s matches inserted. ',
	'DB_UPDATE_BIT_DELIVER'					=> '%s matchday delivery in league with bet in time is set. ',
	'DB_UPDATE_BIT_DELIVERIES'				=> '%s matchday deliveries in league with bet in time is set. ',
	'DB_UPDATE_DELIVER'						=> 'Matchday delivery on matchday %s is newly set. ',
	'DB_UPDATE_DELIVERIES'					=> 'Matchday deliveries on matchdays %s were newly set. ',
	'DB_UPDATE_MATCHDAY'					=> '%s matchday updated. ',
	'DB_UPDATE_MATCHDAYS'					=> '%s matchdays updated. ',
	'DB_UPDATE_TEAM'						=> '%s team updated. ',
	'DB_UPDATE_TEAMS'						=> '%s teams updated. ',
	'DB_UPDATE_MATCH'						=> '%s match updated. ',
	'DB_UPDATE_MATCHES'						=> '%s matches updated. ',
	'DB_UPDATE_STATUS_MATCHDAY'				=> 'New status is set on %s matchday. ',
	'DB_UPDATE_STATUS_MATCHDAYS'			=> 'New status were set on %s matchdays. ',
	'DUPLICATE_TEAM'						=> 'This Team-ID is set twice. ',
	'ERROR_OPEN_LEAGUE_XML'					=> 'Error! The league XML-file could not be opened. Please check the link. ',
	'ERROR_OPEN_SEASON_XML'					=> 'Error! The season XML-file could not be opened. Please check the link. ',
	'ERROR_READ_LEAGUE_XML'					=> 'Error! The league XML-file could not be read. Bitte den Code oder die XML-Datei überprüfen',
	'ERROR_READ_SEASON_XML'					=> 'Error! The season XML-file could not be read. Please check the code. ',
	'ERROR_LOAD_LEAGUE_XML'					=> 'Error! The league XML-file could not be read. Please check the file %s. ',
	'ERROR_XML_CODE'						=> 'Wrong Code! To display the XML-file, you have to append the correct code. ',
	'ERROR_XML_CREATE'						=> 'Error! Unable to create the XML-file. ',
	'INSERT_LEAGUE'							=> 'Insert new league',
	'INSERT_MATCHDAYS'						=> 'Insert matchdays',
	'INSERT_MATCHES'						=> 'Insert matches',
	'INSERT_SEASON'							=> 'Insert new season',
	'INSERT_TEAMS'							=> 'Insert new teams',
	'LEAGUE_SHORTCUT'						=> 'League shortcut',
	'LOAD'									=> 'load',
	'MAP_TEAMS'								=> 'Map teams',
	'MATCH_OF_GROUP'						=> 'Match on group',
	'MATCHES'								=> 'Matches',
	'MISMATCH_LEAGUE_TYPE'					=> 'Leaguetype (%s) source fixture list does not fit the existing league.',
	'MISMATCH_MATCHDAYS'					=> 'The number of rounds (%s) of the source fixture list does not fit the existing league.',
	'MISMATCH_DB_MATCHDAYS'					=> 'The number of rounds (%s) in the database does not fit the existing league.',
	'MISMATCH_MATCHES'						=> 'The number of matches (%s) of the source fixture list does not fit the existing league.',
	'MISMATCH_DB_MATCHES'					=> 'The number of matches (%s) in the database does not fit the existing league.',
	'MISMATCH_MOM'							=> 'The number of matchday-matches (%s) of the source fixture list does not fit the existing league.',
	'MISSING_TEAMS'							=> 'The following team IDs must still be assigned: %s',
	'NEW_LEAGUE'							=> 'New league',
	'NEW_LEAGUE_EXIST'						=> 'The new league %s already exists. ',
	'NEW_LEAGUE_EXPLAIN'					=> 'For a new league please indicate here the league number. For an existing league this league number may not be overwritten, otherwise any existing league will be updated. ',
	'NEW_TEAM'								=> 'New team to be inserted',
	'NO_DB_CHANGES'							=> 'There have been no changes. ',
	'NO_DIFFERENCES'						=> 'No differences found. The fixture lists are identical. ',
	'NO_XML_LEAGUE'							=> 'No update-files found on root/store. ',
	'NO_XML_SEASON'							=> 'No update-source found. ',
	'NO_XML_ARRAY'							=> 'Error! Data not available. Please repeat the action again. ',
	'OTHER_FIELDS'							=> 'Other fields',
	'PREDICTION_LEAGUE'						=> 'Prediction League',
	'SEASON'								=> 'Season',
	'SELECT_EXPLAIN'						=> 'Only the selected records will be taken over here',
	'SHOW_UPDATE'							=> 'Show new league',
	'SOURCE'								=> 'Source',
	'TARGET'								=> 'Target',
	'TEAM_MAPPING'							=> 'Team mapping',
	'TEAM_NAME_SHORT'						=> 'Shortname',
	'TRANSFER_TEAM'							=> 'Transfer team',
	'UPDATE_LEAGUE'							=> 'Update league',
	'UPDATE_MATCHDAYS'						=> 'Update matchdays',
	'UPDATE_MATCHES'						=> 'Update matches',
	'UPDATE_ONLY_FINAL'						=> 'Take over only final results',
	'UPDATE_SAME_STATUS'					=> 'Update results with same status',
	'UPDATE_NEG_STATUS'						=> 'Take over negative status',
	'UPDATE_TEAMS'							=> 'Update teams',
	'UPDATE_THIS'							=> 'Take over changes in this field',
	'XML_SEASON_URL'						=> 'URL for the update source',
	'XML_SEASON_URL_EXPLAIN'				=> 'URL to football_xml_season.php or localhost. In case of localhost league_*  files will be searched on root/store. ',
));
