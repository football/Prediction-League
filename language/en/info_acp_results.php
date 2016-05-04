<?php
/**
*
* info_acp_results.php [en]
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
	'ACP_FOOTBALL_RESULTS'					=> 'Match results',
	'ACP_FOOTBALL_RESULTS_MANAGE'			=> 'Manage match results',
	'ACP_FOOTBALL_RESULTS_MANAGE_EXPLAIN'	=> 'Here you can confirm match results, give, remove or take from the evaluation.',
	'ACP_FOOTBALL_RESULTS_MANAGEMENT'		=> 'Matcht results management',
	'ADVICE'								=> 'Advice',
	'BETS_AND_RANKS'						=> 'Bets and ranking lists',
	'DAY'									=> 'Day',
	'DELETE'								=> 'Remove',
	'DELETE_EXPLAIN'						=> 'Remove match result and status on 1 rank. ',
	'GUEST'									=> 'Guest',
	'HOME'									=> 'Home',
	'LEAGUE'								=> 'League',
	'MATCH_BEGIN'							=> 'match beginning',
	'MATCH_STATUS_TITLE'					=> '-2=match is closed in the 3rd deadline -1=match is closed in the 2nd deadline 0=open (bet delivery possibly), is closed in the deadline 1=keine bet delivery more possibly and still no result 2=vorläufiges result lies before 3=endgültiges result lies before 4-6 = like 1-3, nevertheless, without evaluation. ',
	'MATCHDAY'								=> 'Matchday',
	'MONTH'									=> 'Month',
	'NO_LEAGUE'								=> 'No league exists in the season %1$s. Please, create first a league in the season %1$s. ',
	'NO_MATCHDAY'							=> 'No matchday exists in %1$s season %2$s. Please, first matchdays create. ',
	'NO_MATCHES_CREATED'					=> 'No match matings were deposited up to now. ',
	'NO_SEASON'								=> 'There exists no season. Please, first a season create. ',
	'NO_VALUATION'							=> 'no evaluation',
	'NO_VALUATION_EXPLAIN'					=> 'Take match from the evaluation, because this was shifted or was manipulated. ',
	'NUMBER'								=> 'No.',
	'OVERTIME'								=> 'Overtime',
	'OVERTIME_EXPLAIN'						=> 'match result incl. overtime shoot-out. ',
	'PREDICTION_LEAGUE'						=> 'Prediction League',
	'RANKING'								=> 'Ranking list',
	'RESULT'								=> 'Ergebnis',
	'RESULT_DELETED'						=> '%1$s match result was removed. ',
	'RESULT_DETAILS'						=> 'match results',
	'RESULT_EXPLAIN'						=> 'match result after 90 minutes',
	'RESULT_NO_VALUATION'					=> '%1$s match was taken from the evaluation. ',
	'RESULT_SAVED'							=> '%1$s match result was stored. ',
	'RESULTS_DELETED'						=> '%1$s match results were removed. ',
	'RESULTS_NO_VALUATION'					=> '%1$s matches were taken from the evaluation. ',
	'RESULTS_SAVED'							=> '%1$s match results were stored. ',
	'SAVE'									=> 'Store',
	'SAVE_FAILED'							=> 'The match results could not be stored. ',
	'SEASON'								=> 'Season',
	'SELECT'								=> 'Choice',
	'SELECT_EXPLAIN'						=> 'Only the matches marked here are not evaluated / removed stored/. ',
	'SELECT_MATCHDAY'						=> 'Matchday choose',
	'SET_STATUS_TO'							=> 'The status of the matchday was put on %1$s',
	'STATUS'								=> 'Status',
	'VS'									=> 'Meeting',
	'YEAR'									=> 'Year',
));
