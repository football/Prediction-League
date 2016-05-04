<?php
/**
*
* info_acp_matches.php [en]
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
	'ACP_FOOTBALL_MATCHES'					=> 'Match schedules',
	'ACP_FOOTBALL_MATCHES_MANAGE'			=> 'Manage match schedules',
	'ACP_FOOTBALL_MATCHES_MANAGE_EXPLAIN'	=> 'Here you can administer season and league programmes. You can add missing match mating to the programme and administer the match data of a matchday.',
	'ACP_FOOTBALL_MATCHES_MANAGEMENT'		=> 'Match schedules management',
	'DAY'									=> 'Day',
	'FORMULA_GUEST'							=> 'Formula Guest team',
	'FORMULA_GUEST_EXPLAIN'					=> 'See explanations formula Home team',
	'FORMULA_HOME'							=> 'Formula Home team',
	'FORMULA_HOME_EXPLAIN'					=> 'D = is still drawn lots <br /> L 100 = loser match 100 <br /> W 100 = winner match 100 <br /> W 100; 101 = winner matches 100 and 101 <br /> G A1 = 1st group A',
	'GENERATE_MATCHES'						=> 'Complete match scaffolding',
	'INVALID_MDAY_DATE'						=> 'No valid match date. ',
	'KO'									=> 'KO',
	'LEAGUE'								=> 'League',
	'MATCH_BEFORE_DELIVERY'					=> 'The match beginning lies before the tip delivery of the matchday. This is not allowed.',
	'MATCH_BEGIN'							=> 'match beginning',
	'MATCH_CONFIRM_DELETE'					=> 'If you are sure, that you %1$s. match from %2$s season %3$s with all data (programme and bets) wants to remove?',
	'MATCH_CREATED'							=> '%1$s match mating was created successfully. ',
	'MATCH_DELETED'							=> 'match was removed successfully',
	'MATCH_DETAILS'							=> 'match data',
	'MATCH_EDIT_EXPLAIN'					=> 'Here you can work on an existing match mating. You can fix the appointment, the mating and a formula. ',
	'MATCH_GROUP'							=> 'Group match of the group',
	'MATCH_GUEST'							=> 'Guest',
	'MATCH_HOME'							=> 'Home',
	'MATCH_KO'								=> 'KO match',
	'MATCH_KO_EXPLAIN'						=> 'This match can lead to a Verlägerung or a overtime shoot-out. ',
	'MATCH_NUMBER'							=> 'match of No.',
	'MATCH_ODDS'							=> 'Odds',
	'MATCH_RATING'							=> 'Rating',
	'MATCH_STATUS'							=> 'Status',
	'MATCH_STATUS_EXPLAIN'					=> '-2 = match is closed in the 3rd deadline <br />-1 = match is closed in the 2nd deadline <br /> 0 = openly (bet delivery possibly), is closed in the deadline <br /> 1 = no more bet delivery possibly and still no result <br /> 2 = temporary result lies before <br /> 3 = final result is given.',
	'MATCH_UPDATE_FAILED'					=> 'match mating could not be updated. ',
	'MATCH_UPDATED'							=> 'match mating successfully updates. ',
	'MATCHDAY'								=> 'Matchday',
	'MATCHDAY_MISSED'						=> 'Before this action can be carried out, must be grasped the missing matchday. ',
	'MATCHES_CREATE_FAILED'					=> 'Programme could not be completed. ',
	'MATCHES_CREATED'						=> '%1$s match matings were created successfully. ',
	'MATCHES_DEF'							=> 'Available match matings',
	'MATCHES_DEF_EXPLAIN'					=> 'This are match matings which were created by you or another administrator. You can change the match matings or remove. ',
	'MATCHES_NO_DELETE'						=> 'You may remove no matches. Only foundation members are allowed to do this',
	'MONTH'									=> 'Month',
	'NO_LEAGUE'								=> 'No league exists in the season %1$s. Please, create first a league in the season %1$s. ',
	'NO_MATCH'								=> 'This match mating does not exist. ',
	'NO_MATCH_BEGIN'						=> 'The match beginning is absent. ',
	'NO_MATCHDAY'							=> 'No matchday exists in %1$s season %2$s. Please, first matchdays create. ',
	'NO_MATCHES_CREATED'					=> 'No match matings were deposited up to now. ',
	'NO_SEASON'								=> 'There exists no season. Please, first a season create. ',
	'ODD_1'									=> 'Home',
	'ODD_x'									=> 'Draw',
	'ODD_2'									=> 'Away',
	'PREDICTION_LEAGUE'						=> 'Prediction League',
	'SEASON'								=> 'Season',
	'SELECT_MATCHDAY'						=> 'Matchday choose',
	'TOO_LARGE_MATCH_BEGIN_H'				=> 'Hourly information with the match beginning too largely. ',
	'TOO_LARGE_MATCH_BEGIN_MIN'				=> 'Minute information with the match beginning too largely. ',
	'TOO_SMALL_MATCH_BEGIN_H'				=> 'Hourly information with the match beginning too small. ',
	'TOO_SMALL_MATCH_BEGIN_MIN'				=> 'Minute information with the match beginning too small. ',
	'UNKNOWN'								=> 'not yet famously',
	'YEAR'									=> 'Year',
));
