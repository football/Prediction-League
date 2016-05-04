<?php
/**
*
* info_acp_bank.php [en]
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
	'ACP_FOOTBALL_BANK'					=> 'Accounts ',
	'ACP_FOOTBALL_BANK_MANAGE'			=> 'Manage Accounts',
	'ACP_FOOTBALL_BANK_MANAGE_EXPLAIN'	=> 'Here you can manage all Prediction League accounts. You can draw bets, administer payments and payment and control.',
	'ACP_FOOTBALL_BANK_MANAGEMENT'		=> 'Accounts Management',
	'BET_DEPOSIT'						=> 'Deposit Prediction League bets',
	'BET_DEPOSIT_EXPLAIN'				=> 'Here you can register payments of the users. By settlement in Ultimate Points, the balance UP is raised accordingly or compensated. Without settlement only the respective protocol sentences are grasped to the elective matchday.',
	'BOOK'								=> 'book',
	'BOOKED'							=> 'booked',
	'CARRYOVER_NEW_SEASON'				=> 'Carrzover to new season',
	'CARRYOVER_OLD_SEASON'				=> 'Amount carried over from the last season',
	'CONFIRM_BET'						=> 'Are you sure that you would like to debit the bet from the elective users',
	'CONFIRM_CANCEL_BET'				=> 'Are you sure that you would like to cancel the bets with the elective users?',
	'CONFIRM_CANCEL_DEPOSIT'			=> 'Are you sure that you would like to cancel the payments with the elective users?',
	'CONFIRM_CANCEL_PAY'				=> 'Are you sure that you would like to cancel the payment with the elective users?',
	'CONFIRM_CARRYOVER_PAY'				=> 'Are you sure that you would like to deposit with the elective users a part of the profits at the new season?',
	'CONFIRM_DEPOSIT'					=> 'Are you sure that you would like to book the payments with the elective users?',
	'CONFIRM_LEAGUE_BET'				=> 'Are you sure that you would like to debit the bets from the elective leagues?',
	'CONFIRM_LEAGUE_DELETE_WIN'			=> 'Are you sure that you would like to delete all wins with the elective leagues?',
	'CONFIRM_LEAGUE_DEPOSIT'			=> 'Are you sure that you would like to book the payments with the elective leagues?',
	'CONFIRM_LEAGUE_PAY'				=> 'Are you sure that you would like to book the payment with the elective leagues?',
	'CONFIRM_PAY'						=> 'Are you sure that you would like to book the payment with the elective users?',
	'DEBIT_BET'							=> 'Debit bet',
	'DEBIT_BET_EXPLAIN'					=> 'Here you can call bets of the participants or cancel bets. By settlement in Ultimate Points, the balance UP is loaded accordingly.',
	'DEPOSITED'							=> 'Deposited',
	'FOOTBALL_BANK_OFF'					=> 'The tip rounds account management was deactivated in the tip rounds functionalities.',
	'LEAGUE_BANK'						=> 'Accounts',
	'LEAGUE_BANK_EXPLAIN'				=> 'This are the topical account reservations of the respective leagues. At the end of the season bets/wins and deposits/payments should be identical in pairs. Actions on this side are explained for all participants. Bets and payments become for the 1-st matchday, booked payment for the topical matchday. You can hear reservations for single participants, while you click on the suitable amount. With click on the league name you can download an overview as CSV file.',
	'LEAGUE_BET'						=> '%s bet was debited.',
	'LEAGUE_BETS'						=> '%s bets were debited.',
	'LEAGUE_CANCEL_BET'					=> '%s bet was cancelled.',
	'LEAGUE_CANCEL_BETS'				=> '%s bets were cancelled.',
	'LEAGUE_CANCEL_DEPOSIT'				=> '%s bet payment was cancelled.',
	'LEAGUE_CANCEL_DEPOSITS'			=> '%s bet payments were cancelled.',
	'LEAGUE_CANCEL_PAY'					=> '%s profit payment was cancelled.',
	'LEAGUE_CANCEL_PAYS'				=> '%s profit payments were cancelled.',
	'LEAGUE_CARRYOVER_PAY'				=> '%s share in the profits was transferred as a payment into the new season.',
	'LEAGUE_CARRYOVER_PAYS'				=> '%s Shares in the profits were transferred as a payment into the new season.',
	'LEAGUE_DELETE_WIN'					=> '%s win was deleted.',
	'LEAGUE_DELETE_WINS'				=> '%s wins were deleted.',
	'LEAGUE_DEPOSIT'					=> '%s bet was booked.',
	'LEAGUE_DEPOSITS'					=> '%s bets were booked.',
	'LEAGUE_NAME'						=> 'Leaguename',
	'LEAGUE_PAY'						=> '%s profit was paid.',
	'LEAGUE_PAYS'						=> '%s profits were paid.',
	'MEMBER_BET'						=> 'Bet book',
	'MEMBER_CANCEL_BET'					=> 'Bet cancel',
	'MEMBER_CANCEL_DEPOSITED'			=> 'Bet payments cancel',
	'MEMBER_CANCEL_PAID'				=> 'Profit payments cancel',
	'MEMBER_CARRYOVER_PAID'				=> 'Payment in the next season',
	'MEMBER_DELETE_WINS'				=> 'Delete wins',
	'MEMBER_DEPOSITED'					=> 'Bet payment book',
	'MEMBER_PAID'						=> 'Profit payment book',
	'NO_LEAGUE'							=> 'No league given.',
	'NO_LEAGUES_SELECTED'				=> 'No league was chosen.',
	'NO_MEMBERS_SELECTED'				=> 'No member was selected, or the elective member is not known.',
	'NO_SEASON'							=> 'There exists no season. Please, first a season provide.',
	'NO_VALID_CALL'						=> 'Illegal call! The action is canceled.',
	'PAID'								=> 'Paid',
	'PAY_WINS'							=> 'Pay profits',
	'PAY_WINS_EXPLAIN'					=> 'Here you can pay profits or partial profits or transfer as an application into the next season. By settlement in Ultimate Points, the balance UP is loaded accordingly.',
	'SELECT_SEASON'						=> 'Select season',
	'SET_DEPOSITS'						=> 'Calculate deposits',
	'WINS'								=> 'Wins',
));
