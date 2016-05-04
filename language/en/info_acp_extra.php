<?php
/**
*
* info_acp_extra.php [en]
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
	'ACP_FOOTBALL_EXTRA'					=> 'Special tips',
	'ACP_FOOTBALL_EXTRA_MANAGE'				=> 'Manage special tips',
	'ACP_FOOTBALL_EXTRA_MANAGE_EXPLAIN'		=> 'Here you can assign special tips to matchdays of a league. You can extinguish passing ones or change or provide a new special tip, as well as type, evaluation, points and the end time point of the special tips put.',
	'ACP_FOOTBALL_EXTRA_MANAGEMENT'			=> 'Special tips',
	'EVAL_BEFORE_DELIVERY'					=> 'The evaluation matchday may not lie before the delivery matchday.',
	'EXTRA_ADD'								=> 'Add Special tip',
	'EXTRA_ADDED'							=> 'Special tip successfully added.',
	'EXTRA_CONFIRM_DELETE'					=> 'Are you certainly, that you the special tip "%1$s" in %2$s. League from the season %3$s with all tips wants to extinguish?',
	'EXTRA_CREATE_FAILED'					=> 'Special tips could not be provided.',
	'EXTRA_DEF'								=> 'Existing Special tips',
	'EXTRA_DEF_EXPLAIN'						=> 'These are special tips which were provided by you or another administrator. You can change or delete them.',
	'EXTRA_DELETED'							=> 'Special tips deleted',
	'EXTRA_DETAILS'							=> 'Special tips data',
	'EXTRA_MATCHDAY'						=> 'Delivery',
	'EXTRA_MATCHDAY_EVAL'					=> 'Evaluation',
	'EXTRA_MATCHDAY_EVAL_EXPLAIN'			=> 'The result of the special tip is given in this matchday. With different matchdays for delivery and result the points of the whole evaluation are added, but they flow in onto the matchday evaluation with.',
	'EXTRA_MATCHDAY_EXPLAIN'				=> 'The tip delivery of the special tip is closed with the first term for filing of this matchday.',
	'EXTRA_NO'								=> 'Number',
	'EXTRA_NO_DELETE'						=> 'You may extinguish no special tips. Only foundation members are allowed to do this',
	'EXTRA_POINTS'							=> 'Possible points',
	'EXTRA_POINTS_EXPLAIN'					=> 'At most points to be reached in dependence to the point mode.',
	'EXTRA_QUESTION'						=> 'Special tip question',
	'EXTRA_QUESTION_EXPLAIN'				=> 'More freely interrogative text. Please, formulate an exact, unequivocal question to the deposited interrogative type.',
	'EXTRA_QUESTION_TYPE'					=> 'Questiontype.',
	'EXTRA_QUESTION_TYPE_EXPLAIN'			=> '1 = choice of a team, points only with correspondence <br/> 2 = choice of a team several results, points with a hit <br/> 3 = text input, points with correspondence <br/> 4 = text input, however, several results, points with a hit<br/> 5 = number, points abzgl. of the difference between tip and result',
	'EXTRA_STATUS'							=> 'Status',
	'EXTRA_STATUS_EXPLAIN'					=> '0 = openly (tip delivery possibly) <br/> 1 = no more tip delivery possibly and still no results <br/> 2 = temporary results lie before <br/> 3 = concluded, final results are.',
	'EXTRA_UPDATE_FAILED'					=> 'The special tip settings could not be updated.',
	'EXTRA_UPDATED'							=> 'Special tip settings updates successfully.',
	'LEAGUE'								=> 'League',
	'MATCHDAY'								=> 'Matchday',
	'NO_EXTRA'								=> 'The selected special tip does not exist. Please, provide first a special tip in this league of the season.',
	'NO_EXTRA_CREATED'						=> 'No special tips were provided up to now.',
	'NO_LEAGUE'								=> 'The se楣ted league does not exist in the season %1$s. Please, provide first a league in the season %1$s.',
	'NO_SEASON'								=> 'The selected season does not exist. Please, first a season provide.',
	'QUESTION_TYPE'							=> 'Questiontype',
	'SEASON'								=> 'Season',
	'SELECT_LEAGUE'							=> 'Select League',
	'SELECT_MATCHDAY'						=> 'Select Matchday',
	'TOO_LARGE_EXTRA_POINTS'				=> 'The score for the special tip is too large. Permits: 0-99',
	'TOO_LARGE_MATCHDAY'					=> 'You must choose a delivery matchday.',
	'TOO_LARGE_MATCHDAY_EVAL'				=> 'You must choose an evaluation matchday.',
	'TOO_SMALL_EXTRA_POINTS'				=> 'The score for the special tip is too small. Permits: 0-99',
	'TOO_SMALL_MATCHDAY'					=> 'You must choose a delivery matchday.',
	'TOO_SMALL_MATCHDAY_EVAL'				=> 'You must choose an evaluation matchday.',
));
