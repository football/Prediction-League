<?php
/**
*
* info_acp_seasons.php [en]
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
	'ACP_FOOTBALL_SEASONS'					=> 'Seasons',
	'ACP_FOOTBALL_SEASONS_MANAGE'			=> 'Manage Seasons',
	'ACP_FOOTBALL_SEASONS_MANAGE_EXPLAIN'	=> 'Here you can administer all seasons. You can remove passing ones or change or create a new season, as well as edit name and shortname of the season.',
	'ACP_FOOTBALL_SEASONS_MANAGEMENT'		=> 'Seasons management',
	'CREATE_SEASON'							=> 'Create new season',
	'LEAGUES'								=> 'Leagues',
	'NO_SEASON'								=> 'No season given.',
	'NO_SEASONS_CREATED'					=> 'No seasons were created up to now. ',
	'PREDICTION_LEAGUE'						=> 'Prediction League',
	'SEASON'								=> 'Season',
	'SEASON_CLOSED'							=> 'Unanimously',
	'SEASON_CONFIRM_DELETE'					=> 'Are you sure that you want to remove the season %1$s with all data (leagues, matchdays, match schedules, bets and ranking lists)?',
	'SEASON_CREATE_FAILED'					=> 'Season could not be created. ',
	'SEASON_CREATED'						=> 'Season successfully creates. ',
	'SEASON_CURRENT'						=> 'Topical season',
	'SEASON_DEF'							=> 'Existing seasons',
	'SEASON_DEF_EXPLAIN'					=> 'This are seasons which were created by you or another administrator. You can change seasonal settings or remove seasons. ',
	'SEASON_DELETE'							=> 'Season remove',
	'SEASON_DELETED'						=> 'Season removed',
	'SEASON_DETAILS'						=> 'Season details',
	'SEASON_EDIT_EXPLAIN'					=> 'Here you can work on an existing season. You can change her name and the short name. ',
	'SEASON_NAME'							=> 'Seasonal name',
	'SEASON_NAME_EMPTY'						=> 'The seasonal name is absent. He must be long to least 4 signs. ',
	'SEASON_NAME_EXPLAIN'					=> 'Long name of the season, e.g., "season 2010/2011"',
	'SEASON_NAME_TAKEN'						=> 'The given seasonal name is already used. Please, selects another. ',
	'SEASON_NUMBER'							=> 'The given season must be numerically (in 1963-2099). Please, the annual number, in that the season ends give. ',
	'SEASON_SHORT'							=> 'Season short name',
	'SEASON_SHORT_EMPTY'					=> 'The season short name is absent, she must be long to least 2 signs. ',
	'SEASON_SHORT_EXPLAIN'					=> 'Short name of the season which is indicated in the choice box. ',
	'SEASON_SHORT_TAKEN'					=> 'The given short name of the season is already used. Please, another selects. ',
	'SEASON_TAKEN'							=> 'This season already it exists. Please, another season selects. ',
	'SEASON_UPDATE_FAILED'					=> 'Seasonal settings could not be updated. ',
	'SEASON_UPDATED'						=> 'Updates seasonal settings successfully. ',
	'SEASONS_NO_DELETE'						=> 'You may remove no season. Only foundation members are allowed to do this',
	'SEASONS_NO_LEAGUE'						=> 'This season has no league',
	'TOO_LARGE_SEASON'						=> 'The value of the season is too big. Season of 1963 - in 2099 possibly. ',
	'TOO_SHORT_SEASON'						=> 'The seasonal name must be long to least 4 signs. ',
	'TOO_SHORT_SEASON_SHORT'				=> 'The season Short name must be long to least 2 signs. ',
	'TOO_SMALL_SEASON'						=> 'The value of the season is too small. Season of 1963 - in 2099 possibly.',
));
