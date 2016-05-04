<?php
/**
*
* info_acp_leagues.php [en]
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
	'ACP_FOOTBALL_LEAGUES'					=> 'Leagues',
	'ACP_FOOTBALL_LEAGUES_MANAGE'			=> 'Manage leagues',
	'ACP_FOOTBALL_LEAGUES_MANAGE_EXPLAIN'	=> 'Here you can administer all leagues. You can remove passing ones, change or create a new league, as well as edit type, number of the matchdays, wins, name and shortname of the league. ',
	'ACP_FOOTBALL_LEAGUES_MANAGEMENT'		=> 'Leagues management',
	'ADD_GROUP'								=> 'Add Group',
	'ADD_USERS'								=> 'Users add',
	'ADD_USERS_EXPLAIN'						=> 'Here you can add new User to the Prediction League. Please, give user names per line. ',
	'ALL_USERS'								=> 'All users',
	'BET_POINTS'							=> 'Bet',
	'BET_POINTS_EXPLAIN'					=> 'bet amount before season starts',
	'BET_TYPE_KO'							=> 'Bet on KO-Matches',
	'BET_TYPE_KO_EXPLAIN'					=> 'On which result should be bet with KO matches?',
	'CHECK_HIT_WINS'						=> 'Error win direct hit. Please, correct. ',
	'CHECK_HITS02_WINS'						=> 'Error win direct hit with away points. Please, correct. ',
	'CHECK_MATCHDAY_WINS'					=> 'Error wins matchday. Please, correct. ',
	'CHECK_RULES_POST_ID'					=> 'The contribution number with the rules must be deposited on entry by the user. ',
	'CHECK_SEASON_WINS'						=> 'Error seasonal wins. Please, correct. ',
	'CREATE_LEAGUE'							=> 'Create new league',
	'LEAGUE_BET_IN_TIME'					=> 'Bet delivery up to the respective match beginning',
	'LEAGUE_BET_IN_TIME_EXPLAIN'			=> 'May the User bet up to the respective match beginning, or should the bet delivery of all matches on a matchday be possible only up to (maximum three) appointments?',
	'LEAGUE_CHAMP'							=> 'Championship',
	'LEAGUE_CONFIRM_DELETE'					=> 'Are you sure that you want to remove %1$s from the season %2$s with all data (matchdays, match schedules, bets and rankings)?',
	'LEAGUE_CREATE_FAILED'					=> 'League could not be created. ',
	'LEAGUE_CREATED'						=> 'League successfully created. ',
	'LEAGUE_CURRENT'						=> 'Current league',
	'LEAGUE_DEF'							=> 'Existing leagues',
	'LEAGUE_DEF_EXPLAIN'					=> 'This are leagues which were created by you or another administrator. You can change league settings or remove leagues. ',
	'LEAGUE_DELETE'							=> 'Remove league',
	'LEAGUE_DELETED'						=> 'League removed',
	'LEAGUE_DETAILS'						=> 'League details',
	'LEAGUE_EDIT_EXPLAIN'					=> 'Here you can edit on an existing league. You can change type, number of the matchdays, wins, name and the short name. ',
	'LEAGUE_JOIN_BY_USER'					=> 'Register to this Prediction League by users',
	'LEAGUE_JOIN_BY_USER_EXPLAIN'			=> 'Should the forum users themselves be able to join to this Prediction League, or only administrators are allowed to do this?',
	'LEAGUE_JOIN_IN_SEASON'					=> 'Register to this Prediction League during the season',
	'LEAGUE_JOIN_IN_SEASON_EXPLAIN'			=> 'May the forum users join during the running season?',
	'LEAGUE_KO'								=> 'KO round',
	'LEAGUE_MATCHDAYS'						=> 'Number of the matchdays',
	'LEAGUE_MATCHDAYS_EMPTY'				=> 'The number of the matchdays is absent. ',
	'LEAGUE_MATCHDAYS_EXPLAIN'				=> 'With the calculation of the number of matchdays is to be considered that matches of different rounds can be summarised to a matchday, so that must not be typed on single or few matches. ',
	'LEAGUE_MATCHES'						=> 'Number of matches on matchday',
	'LEAGUE_MATCHES_EXPLAIN'				=> 'KO rounds with different number of matches on matchdays, is to be set here 0. ',
	'LEAGUE_MEMBERS'						=> 'Prediction League participant',
	'LEAGUE_NAME'							=> 'League name',
	'LEAGUE_NAME_EMPTY'						=> 'The league name must be long least 3 letters. ',
	'LEAGUE_NO_MEMBER'						=> 'The league has no Users',
	'LEAGUE_NUMBER'							=> 'The given league must be numerical. Please, give a league number from 1-99. ',
	'LEAGUE_POINTS'							=> 'Points Mode of this Prediction League',
	'LEAGUE_POINTS_DIFF'					=> 'Points correct goal difference',
	'LEAGUE_POINTS_DIFF_EXPLAIN'			=> 'Points for the correct goal difference. ',
	'LEAGUE_POINTS_HIT'						=> 'Points for a coorect result',
	'LEAGUE_POINTS_HIT_EXPLAIN'				=> 'Points if the bet with the result agrees. ',
	'LEAGUE_POINTS_LAST'					=> 'Points from last on if somebody did not bet',
	'LEAGUE_POINTS_LAST_EXPLAIN'			=> 'Should the user without bet automatically receive the points of the worst user? This can be, perhaps, also 0 points. ',
	'LEAGUE_POINTS_MODE'					=> 'Points Modus',
	'LEAGUE_POINTS_MODE_EXPLAIN'			=> '1 = direct hit points and with right trend ever to divergent gate a point less, however, mind. Trend points. With draw an only easy deduction with divergent gate. <br /> 2 = like 1 deduction nevertheless full with draw gate divergence. <br /> 3 = direct hit or trend points. <br /> 4 = direct hit points, difference points or trend points. <br /> 5 = like 4 without difference points on draw. <br /> 6 = direct hit and trend points; difference points on right draw trend. ',
	'LEAGUE_POINTS_TENDENCY'				=> 'Points with right trend',
	'LEAGUE_POINTS_TENDENCY_EXPLAIN'		=> 'Points or least points with right trend. ',
	'LEAGUE_RULES_POST_ID'					=> 'Contribution number with the rules',
	'LEAGUE_RULES_POST_ID_EXPLAIN'			=> 'Post-ID with the rules to this league-Prediction League. Contribution indicated will announce to the badge the Post-Id. ',
	'LEAGUE_SHORT'							=> 'League of short name',
	'LEAGUE_SHORT_EMPTY'					=> 'The short name is absent. ',
	'LEAGUE_SHORT_EXPLAIN'					=> '3-figure abbreviation, e.g., 1SHEET, 2SHEET, German Football Association, CENTILITRE or tablespoon to the identification of the league in the download file. ',
	'LEAGUE_TAKEN'							=> 'This league already exists in this season. Please, another league number selects. ',
	'LEAGUE_TYPE'							=> 'League type',
	'LEAGUE_TYPE_EXPLAIN'					=> 'With match rounds with the matches which are able to be decided on a lengthening KO round is to be chosen here, even if single matches of this round end after 90 minutes. ',
	'LEAGUE_UPDATE_FAILED'					=> 'League settings could not be updated. ',
	'LEAGUE_UPDATED'						=> 'Updates league settings successfully. ',
	'LEAGUE_USERS_ADD'						=> 'User to the league added. ',
	'LEAGUE_USERS_REMOVE'					=> 'User from the league remotely. ',
	'LEAGUE_WIN_EXPLAIN'					=> 'With the following profit fields the wins are rank by; to give apart. So 10.50; 5 for rank 1 = 10.50 euros and rank 2 = 5 euros',
	'LEAGUE_WIN_HITS'						=> 'Win of direct hit evaluation',
	'LEAGUE_WIN_HITS_AWAY'					=> 'Win direct hit evaluation with away points',
	'LEAGUE_WIN_HITS_AWAY_EXPLAIN'			=> 'Win in euro for the first-placed of the direct hit evaluation with those the away team has dotted. ',
	'LEAGUE_WIN_HITS_EXPLAIN'				=> 'Win in euro for the first-placed of the direct hit evaluation. ',
	'LEAGUE_WIN_MATCHDAYS'					=> 'Profits matchday',
	'LEAGUE_WIN_MATCHDAYS_EXPLAIN'			=> 'Order wins matchday. ',
	'LEAGUE_WIN_SEASON'						=> 'Profits of season',
	'LEAGUE_WIN_SEASON_EXPLAIN'				=> 'Unique order wins at the end of the season. ',
	'LEAGUE_WINS'							=> 'League of wins',
	'LEAGUES_NO_DELETE'						=> 'You may remove no league. Only foundation members are allowed to do this',
	'LEAGUES_NO_TEAM'						=> 'This league has no Teams',
	'MEMBER'								=> 'User',
	'MEMBER_ALL'							=> 'All active Boardmitglieder take up',
	'MEMBER_CONFIRM_DELETE'					=> 'Are you sure that you want to remove the elective User from the Prediction League %1$s of the season %2$s with all data (bet and rank places)?',
	'MEMBER_DELETE'							=> 'User from league remove',
	'MEMBER_EXISTS'							=> 'This User is already a participant of this league-Prediction League. ',
	'MEMBER_EXPLAIN'						=> 'This is a list all User of this league. From here you can put who should be User of this league. ',
	'NO_LEAGUE'								=> 'No league given. ',
	'NO_LEAGUES_CREATED'					=> 'No leagues were created up to now. ',
	'NO_MATCHDAYS_KO'						=> 'With a KO round only the matchdays must be grasped before users of the Prediction League can join. ',
	'NO_MEMBERS_SELECTED'					=> 'No member was selected, or the elective member is not known. ',
	'NO_SEASON'								=> 'There exists no season. Please, first a season create. ',
	'SELECT_SEASON'							=> 'Season select',
	'TOO_LARGE_LEAGUE'						=> 'The value of the league is too big. These are only leagues between 1 - 99 possibly. ',
	'TOO_LARGE_LEAGUE_MATCHDAYS'			=> 'The number of the matchdays is too big. She must lie between 1 and 99. ',
	'TOO_LARGE_LEAGUE_MATCHES'				=> 'The number of the matches matchday is too big. She must lie between 0 and 99. ',
	'TOO_SHORT_LEAGUE_SHORT'				=> 'The short name of the league must be long to least 1 signs. ',
	'TOO_SHORT_SEASON'						=> 'The league name must be long to least 2 signs. ',
	'TOO_SMALL_LEAGUE'						=> 'The value of the league is too small. These are only leagues between 1 - 99 possibly. ',
	'TOO_SMALL_LEAGUE_MATCHDAYS'			=> 'The number of the matchdays is too small. She must lie between 1 and 99. ',
	'TOO_SMALL_LEAGUE_MATCHES'				=> 'The number of the matches matchday is too small. She must lie between 0 and 99. ',
));
