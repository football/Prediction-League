<?php
/**
*
* info_acp_matchdays.php [en]
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
	'ACP_FOOTBALL_MATCHDAYS'					=> 'Matchdays',
	'ACP_FOOTBALL_MATCHDAYS_MANAGE'				=> 'Manage matchdays',
	'ACP_FOOTBALL_MATCHDAYS_MANAGE_EXPLAIN'		=> 'Here you can assign league matchdays of a season. You can remove passing ones or change or create a new matchday, as well as type, number of the matchdays, wins, name and Kurzbezeichnug the matchday put.',
	'ACP_FOOTBALL_MATCHDAYS_MANAGEMENT'			=> 'Matchdays management',
	'ADD_MATCHDAY'								=> 'Matchday to the league add',
	'BACKWARD_DELIVERY'							=> 'Select Period',
	'BACKWARD_DELIVERY_EXPLAIN'					=> 'Period of delivery before the first game starts.',
	'CHANGE_DELIVERY'							=> 'Change Delivery',
	'CHANGE_DELIVERY_EXPLAIN'					=> 'Here you can set new delivery dates for future matchdays according to the first kick off. However, this is no automatic transfer for future matches. With Period 0 you can check whether a deadline is after the first kick off, and correct it accordingly. If a uniform deadline be set, this function must be called multiple times with possibly different time periods to match the kick-off times.',
	'DELIVERY'									=> 'Abgabezeit',
	'DELIVERY_SET_TIME'							=> 'Abgabezeit vor dem ersten Spielbeginn',
	'DELIVERY_NUMBER'							=> 'Abgabe Nr.',
	'GENERATE_MATCHDAY'							=> 'Missing matchdays generate',
	'INVALID_DDAY1_DATE'						=> 'No valid deadline. ',
	'INVALID_DDAY2_DATE'						=> 'No valid 2nd deadline. ',
	'INVALID_DDAY3_DATE'						=> 'No valid 3rd deadline. ',
	'LEAGUE'									=> 'League',
	'MATCHDAY'									=> 'Matchday',
	'MATCHDAY_ADDED'							=> 'Matchday successfully added. ',
	'MATCHDAY_CONFIRM_DELETE'					=> 'If you are sure, that you %1$s. Matchday from the season %2$s with all data (programme and bets) wants to remove?',
	'MATCHDAY_CONFIRM_REMOVE'					=> 'Are you sure that you want to extinguish all superfluous matchdays from the season %1$s with all data (program and tips)?',
	'MATCHDAY_CREATE_FAILED'					=> 'Matchday could not be created. ',
	'MATCHDAY_CREATED'							=> '%1$s matchday was created successfully. ',
	'MATCHDAY_CURRENT'							=> 'Topical matchday',
	'MATCHDAY_DEF'								=> 'Existing matchdays',
	'MATCHDAY_DEF_EXPLAIN'						=> 'This are matchdays which were created by you or another administrator. You can change matchday settings or remove matchdays. ',
	'MATCHDAY_DELETE'							=> 'Matchday remove',
	'MATCHDAY_DELETED'							=> 'Matchday removed',
	'MATCHDAY_DELIVERY'							=> 'Deadline',
	'MATCHDAY_DELIVERY_EXPLAIN'					=> 'Appointment in him the matchday or the next matches are closed and therefore no more bet delivery is possible. If the matchday was already closed (status 1), this can become by a future deadline again open. Other status crossings are possible only by input or extinguishing of the match results of this matchday.',
	'MATCHDAY_DELIVERY2'						=> '2nd deadline ',
	'MATCHDAY_DELIVERY2_EXPLAIN'				=> 'With the achievement of the first deadline this date is set as the next deadline for the next matches. ',
	'MATCHDAY_DELIVERY3'						=> '3rd deadline',
	'MATCHDAY_DELIVERY3_EXPLAIN'				=> 'With the achievement of the 2nd deadline this date is set as the next deadline for the next matches. ',
	'MATCHDAY_DETAILS'							=> 'Matchday data',
	'MATCHDAY_EDIT_EXPLAIN'						=> 'Here you can work on an existing matchday. You can change type, number of the matchdays, wins, name and the short name. ',
	'MATCHDAY_MATCHES'							=> 'Number of the matches on this matchday',
	'MATCHDAY_MATCHES_EXPLAIN'					=> 'With KO rounds the information of the number of the matches is necessary matchday compelling. ',
	'MATCHDAY_NAME'								=> 'Matchday name',
	'MATCHDAY_NAME_DOUBLE'						=> 'The matchday name is already used or was twice awarded. ',
	'MATCHDAY_NAME_EMPTY'						=> 'The matchday name must be long least 3 letters. ',
	'MATCHDAY_NUMBER'							=> 'The given matchday must be numerically (1-99). Please, give a matchday number. ',
	'MATCHDAY_STATUS'							=> 'Status',
	'MATCHDAY_STATUS_EXPLAIN'					=> '0 = openly (bet delivery possibly) <br /> 1 = no more bet delivery possibly and still no results <br /> 2 = temporary results lie before <br /> 3 = concluded, final results are given. ',
	'MATCHDAY_UPDATE_FAILED'					=> 'Matchday settings could not be updated. ',
	'MATCHDAY_UPDATED'							=> 'Updates matchday settings successfully. ',
	'MATCHDAYS_CREATED'							=> '%1$s matchdays were created successfully. ',
	'MATCHDAYS_NO_DELETE'						=> 'You may remove no matchday. Only foundation members are allowed to do this',
	'MATCHDAYS_REMOVED'							=> 'Superfluous matchdays extinguished',
	'NEW_DELIVERY'								=> 'New delivery',
	'NO_DELIVERIES_UPDATED'						=> 'There are no deliveries updated.',
	'NO_DELIVERY'								=> 'The deadline is absent. ',
	'NO_DELIVERY2'								=> 'The 2nd deadline is absent or the 3rd deadline must be removed. ',
	'NO_LEAGUE'									=> 'No league exists in the season %1$s. Please, create first a league in the season %1$s.',
	'NO_MATCHDAYS_CREATED'						=> 'No matchdays were created up to now. ',
	'NO_MORE_MATCHDAYS'							=> 'It was added no other matchdays.',
	'NO_SEASON'									=> 'There exists no season. Please, first a season create.',
	'OPEN_MATCH'								=> 'The match No. %s lies before the elective term for filing. Hence, the term for filing is not allowed.',
	'OPEN_MATCHES'								=> 'The matches No. %s lies before the elective term for filing. Hence, the term for filing is not allowed.',
	'PREDICTION_LEAGUE'							=> 'Prediction League',
	'REMOVE_MATCHDAYS'							=> 'Delete superfluous matchdays',
	'SEASON'									=> 'Season',
	'SELECT_LEAGUE'								=> 'Select league',
	'SHOW_DELIVERY'								=> 'Show deliveries',
	'TOO_LARGE_MATCHES'							=> 'The number of the matches matchday is too big. ',
	'TOO_SMALL_DELIVERY2'						=> 'The 2nd deadline may not lie before the 1st deadline. ',
	'TOO_SMALL_DELIVERY3'						=> 'The 3rd deadline may not lie before the 2nd deadline.',
	'TOO_SMALL_MATCHES'							=> 'The number of the matches matchday is too small. ',
	'UPDATE_DELIVER'							=> '%s delivery updated.',
	'UPDATE_DELIVERIES'							=> '%s deliveries updated.',
	'UPDATE_DELIVERY'							=> 'New deliveries',
	'UPDATE_DELIVERY_EXPLAIN'					=> 'Wähle die Datensätze aus, deren Abgabezeit neu gesetzt werden soll. Rot markierte Abgabezeiten liegen nach dem neu berechneten Abgabetermin.',
));
