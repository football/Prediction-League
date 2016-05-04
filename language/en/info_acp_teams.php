<?php
/**
*
* info_acp_teams.php [en]
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
	'ACP_FOOTBALL_TEAMS'					=> 'Teams',
	'ACP_FOOTBALL_TEAMS_MANAGE'				=> 'Manage teams',
	'ACP_FOOTBALL_TEAMS_MANAGE_EXPLAIN'		=> 'Here you can assign season teams of a league. You can remove passing ones or change or create a new team, as well as type, number of the matchdays, wins, name and Kurzbezeichnug of the team put. ',
	'ACP_FOOTBALL_TEAMS_MANAGEMENT'			=> 'Team management',
	'ADD_TEAM'								=> 'Add team to the league',
	'LEAGUE'								=> 'League',
	'MATCHDAY'								=> 'Matchday',
	'NEW_TEAM'								=> 'New team',
	'NO_LEAGUE'								=> 'No league exists in the season %1$s. Please, create first a league in the season %1$s. ',
	'NO_MATCHDAYS'							=> 'Please, first matchdays put in, because the teams must be assigned with KO rounds a matchday. ',
	'NO_SEASON'								=> 'There exists no season. Please, first a season create. ',
	'NO_TEAM'								=> 'No team given. ',
	'NO_TEAMS_CREATED'						=> 'No teams were created up to now. ',
	'NO_TEAMSYMBOL'							=> 'No logo',
	'PREDICTION_LEAGUE'						=> 'Prediction League',
	'SEASON'								=> 'Season',
	'SELECT_LEAGUE'							=> 'Select league',
	'TEAM'									=> 'Team',
	'TEAM_ADDED'							=> 'Team successfully added. ',
	'TEAM_AWAY'								=> 'Away',
	'TEAM_CONFIRM_DELETE'					=> 'If you are sure, that you %1$s from season: %2$s league: %3$s with all data (matchdays, match schedules and bets) would like to remove?',
	'TEAM_CREATE_FAILED'					=> 'Team could not be created. ',
	'TEAM_CREATED'							=> 'Team successfully creates. ',
	'TEAM_CURRENT'							=> 'Topical team',
	'TEAM_DEF'								=> 'Existing Teams',
	'TEAM_DEF_EXPLAIN'						=> 'This are teams which were created by you or another administrator. You can change team settings or remove teams. ',
	'TEAM_DELETE'							=> 'Remove team',
	'TEAM_DELETED'							=> 'Team removed',
	'TEAM_DETAILS'							=> 'Team data',
	'TEAM_EDIT_EXPLAIN'						=> 'Here you can work on an existing team. You can change the name, the team-short name as well as the team coat of arms. ',
	'TEAM_GROUP'							=> 'Group matches in group',
	'TEAM_GROUP_EXPLAIN'					=> 'Letter of the group in which the group matches are denied. ',
	'TEAM_MATCHES'							=> 'Matches',
	'TEAM_NAME'								=> 'Team name',
	'TEAM_NAME_DOUBLE'						=> 'The team name is already used or was twice awarded. ',
	'TEAM_NUMBER'							=> 'This is no valid team number. The team number must lie between 0 and 65535. ',
	'TEAM_ROUND'							=> 'Qualifies till matchday',
	'TEAM_ROUND_EXPLAIN'					=> 'Matchday up to itself the team for the time being has qualified. About this parametre only the teams are offered by the programme production for the choice who have also qualified for the net-speaking round. About the menu point Qualification this value can be automatically put after ending of the round automatically for all certified teams. ',
	'TEAM_SHORT'							=> 'Team short name',
	'TEAM_SHORT_DOUBLE'						=> 'The short name is already used or was twice awarded. ',
	'TEAM_SHORT_EXPLAIN'					=> 'Maximum 10 figure short name of the team. This is used, e.g., with the export and under all bets. ',
	'TEAM_SYMBOL'							=> 'Team coats of arms',
	'TEAM_SYMBOL_EXPLAIN'					=> 'The optional team coat of arms must be 28x28 pixels. ',
	'TEAM_TAKEN'							=> 'This team already exists in this league. Please, another team number selects. ',
	'TEAM_UPDATE_FAILED'					=> 'Team settings could not be updated. ',
	'TEAM_UPDATED'							=> 'Updates team settings successfully. ',
	'TEAMS_NO_DELETE'						=> 'You may remove no team. Only foundation members are allowed to do this',
	'TOO_LARGE_TEAM'						=> 'The given Team-ID must be numerically (1-9999). Please, give a smaller team number. ',
	'TOO_SHORT_TEAM_NAME'					=> 'The team name must be long to least 3 signs. ',
	'TOO_SHORT_TEAM_SHORT'					=> 'The short name must be long to least 1 signs. ',
	'TOO_SMALL_TEAM'						=> 'The given Team-ID must be numerically (1-9999). Please, give a bigger team number. ',
	'WRONG_DATA_TEAM_GROUP'					=> 'No valid group for the group matches. Valid groups are A-Z.',
));
