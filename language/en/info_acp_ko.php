<?php
/**
*
* info_acp_ko.php [en]
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
	'ACP_FOOTBALL_KO'					=> 'KO-winner',
	'ACP_FOOTBALL_KO_MANAGE'			=> 'Calculate KO-winner',
	'ACP_FOOTBALL_KO_MANAGE_EXPLAIN'	=> 'Here you can determine KO-round winners on matches or on table ranking and enable them for the next matchdays. Only these teams are selectable on next rounds. Also it is possibly to transferr teams depending on table ranks in another KO league.',
	'ACP_FOOTBALL_KO_MANAGEMENT'		=> 'KO-round management',
	'CHOOSE_LEAGUE'						=> 'Select league',
	'ERROR_FROM_TO'						=> 'Error selecting from matchday. "From" may not lie after "to". ',
	'ERROR_TARGET'						=> 'Error selecting to matchday. The target matchday does not lie after the considered matchdays. ',
	'GROUP_RANK'						=> 'Qualifies up to table rank',
	'GROUP_RANK_EXPLAIN'				=> 'All teams up to this table rank qualify. ',
	'KO_GROUP'							=> 'Qualification on table rank',
	'KO_MATCHDAYS'						=> 'Considered Matchdays',
	'KO_MOVED'							=> 'The following teams were copied in another league',
	'KO_NEXT'							=> 'The following teams have reached the next round',
	'KO_NEXT_CHECK'						=> 'Please, check this teams, because not all rules are considered here. ',
	'MANAGE_GROUP'						=> 'Consider group ranking',
	'MATCHDAY_NEW'						=> 'The teams qualify till matchday',
	'MATCHDAY_TARGET'					=> 'Select target matchday',
	'MOVE_LEAGUE'						=> 'in league',
	'MOVE_RANK'							=> 'Transfer team on rank',
	'MOVE_RANK_EXPLAIN'					=> 'The teams on this rank are copied in the deposited league and are qualified up to given matchday. ',
	'NO_KO_NEXT'						=> 'No team was set to next round, because yet not all matches are played!',
	'PREDICTION_LEAGUE'					=> 'Prediction League',
));
