<?php
/**
* 
* info_acp_football_bank [Deutsch — Du]
*
* @package language
* @version $Id: info_acp_football_bank.php 6 2011-01-09 08:02:46Z football $ 
* @copyright (c) 2010 football (http://football.bplaced.net)
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
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
	'ACP_FOOTBALL_BANK'						=> 'Konten',
	'ACP_FOOTBALL_BANK_MANAGE'				=> 'Konten verwalten',
	'ACP_FOOTBALL_BANK_MANAGE_EXPLAIN'		=> 'Hier kannst du alle Tipprunden Konten verwalten. Du kannst Einsätze einziehen, Ein- und Auszahlungen verwalten und kontrollieren.',
	'ACP_FOOTBALL_BANK_MANAGEMENT'			=> 'Tipprunde-Konten-Verwaltung',

	'BET_DEPOSIT'							=> 'Einsatz einzahlen',
	'BET_DEPOSIT_EXPLAIN'					=> 'Hier kannst du Einzahlungen der Teilnehmer verbuchen. Bei Verrechnung in Ultimate Points, wird der UP Saldo entsprechend erhöht bzw. ausgeglichen. Ohne Verrechnung werden nur die jeweiligen Protokollsätze zum gewählten Spieltag erfasst.',
	'BOOK'									=> 'buchen',
	'BOOKED'								=> 'Gebucht',

	'CARRYOVER_NEW_SEASON'					=> 'Übertrag in die nächste Saison',
	'CARRYOVER_OLD_SEASON'					=> 'Übertrag aus der letzten Saison',
	'CONFIRM_BET'							=> 'Bist du sicher, dass du bei den gewählten Tippern die Einsätze abbuchen möchtest?',
	'CONFIRM_CANCEL_BET'					=> 'Bist du sicher, dass du bei den gewählten Tippern die Einsätze stornieren möchtest?',
	'CONFIRM_CANCEL_DEPOSIT'				=> 'Bist du sicher, dass du bei den gewählten Tippern die Einzahlungen stornieren möchtest?',
	'CONFIRM_CANCEL_PAY'					=> 'Bist du sicher, dass du bei den gewählten Tippern die Auszahlungen stornieren möchtest?',
	'CONFIRM_CARRYOVER_PAY'					=> 'Bist du sicher, dass du bei den gewählten Tippern ein Teil der Gewinne in die neue Saison einzahlen möchtest?',
	'CONFIRM_DEPOSIT'						=> 'Bist du sicher, dass du bei den gewählten Tippern die Einzahlungen buchen möchtest?',
	'CONFIRM_LEAGUE_BET'					=> 'Bist du sicher, dass du bei den gewählten Ligen die Einsätze abbuchen möchtest?',
	'CONFIRM_LEAGUE_DELETE_WIN'				=> 'Bist du sicher, dass du bei den gewählten Ligen alle Gewinne löschen möchtest?',
	'CONFIRM_LEAGUE_DEPOSIT'				=> 'Bist du sicher, dass du bei den gewählten Ligen die Einzahlungen buchen möchtest?',
	'CONFIRM_LEAGUE_PAY'					=> 'Bist du sicher, dass du bei den gewählten Ligen die Auszahlungen buchen möchtest?',
	'CONFIRM_PAY'							=> 'Bist du sicher, dass du bei den gewählten Tippern die Auszahlungen buchen möchtest?',

	'DEBIT_BET'								=> 'Einsatz abbuchen',
	'DEBIT_BET_EXPLAIN'						=> 'Hier kannst du Einsätze der Teilnehmer einfordern oder stornieren. Bei Verrechnung in Ultimate Points, wird der UP Saldo entsprechend belastet.',
	'DEPOSITED'								=> 'Eingezahlt',

	'FOOTBALL_BANK_OFF'						=> 'Die Tipprunden Konten-Verwaltung wurde in den Tipprunden-Funktionalitäten deaktiviert.',

	'LEAGUE_BANK'							=> 'Konten',
	'LEAGUE_BANK_EXPLAIN'					=> 'Dies sind die aktuellen Kontenbuchungen der jeweiligen Ligen. Am Ende der Saison sollten zumindest Einsatz/Gewinn und Ein-/Auszahlungen paarweise identisch sein. Aktionen auf dieser Seite werden für alle Teilnehmer ausgeführt. Einsätze und Einzahlungen werden für den 1. Spieltag, Auszahlung für den aktuellen Spieltag gebucht. Buchungen für einzelne Teilnehmer kannst du vernehmen, indem du auf den entsprechenden Betrag klickst. Mit Klick auf den Liganamen kannst du eine Übersicht als CSV-Datei downloaden.',
	'LEAGUE_BET'							=> '%s Einsatz wurde abgebucht.',
	'LEAGUE_BETS'							=> '%s Einsätze wurden abgebucht.',
	'LEAGUE_CANCEL_BET'						=> '%s Einsatzbuchung wurde storniert.',
	'LEAGUE_CANCEL_BETS'					=> '%s Einsatzbuchungen wurden storniert.',
	'LEAGUE_CANCEL_DEPOSIT'					=> '%s Einsatzzahlung wurde storniert.',
	'LEAGUE_CANCEL_DEPOSITS'				=> '%s Einsatzzahlungen wurden storniert.',
	'LEAGUE_CANCEL_PAY'						=> '%s Gewinnauszahlung wurde storniert.',
	'LEAGUE_CANCEL_PAYS'					=> '%s Gewinnauszahlungen wurden storniert.',
	'LEAGUE_CARRYOVER_PAY'					=> '%s Gewinnanteil wurde als Einzahlung in die neue Saison übertragen.',
	'LEAGUE_CARRYOVER_PAYS'					=> '%s Gewinnanteile wurden als Einzahlung in die neue Saison übertragen.',
	'LEAGUE_DELETE_WIN'						=> '%s Gewinn wurde gelöscht.',
	'LEAGUE_DELETE_WINS'					=> '%s Gewinne wurden gelöscht.',
	'LEAGUE_DEPOSIT'						=> '%s Einsatz wurde eingezahlt.',
	'LEAGUE_DEPOSITS'						=> '%s Einsätze wurden eingezahlt.',
	'LEAGUE_NAME'							=> 'Liganame',
	'LEAGUE_PAY'							=> '%s Gewinn wurde ausgezahlt.',
	'LEAGUE_PAYS'							=> '%s Gewinne wurden ausgezahlt.',

	'MEMBER_BET'							=> 'Einsätze buchen',
	'MEMBER_CANCEL_BET'						=> 'Einsätze stornieren',
	'MEMBER_CANCEL_DEPOSITED'				=> 'Einzahlungen stornieren',
	'MEMBER_CANCEL_PAID'					=> 'Auszahlungen stornieren',
	'MEMBER_CARRYOVER_PAID'					=> 'Einzahlung in nächste Saison',
	'MEMBER_DELETE_WINS'					=> 'Gewinne löschen',
	'MEMBER_DEPOSITED'						=> 'Einzahlungen buchen',
	'MEMBER_PAID'							=> 'Auszahlungen buchen',

	'NO_LEAGUE'								=> 'Keine Liga angegeben.',
	'NO_LEAGUES_SELECTED'					=> 'Es wurde keine Liga gewählt.',
	'NO_MEMBERS_SELECTED'					=> 'Es wurde kein Mitglied ausgewählt, bzw. das gewählte Mitglied ist nicht bekannt.',
	'NO_SEASON'								=> 'Es existiert keine Saison. Bitte zuerst eine Saison erstellen.',
	'NO_VALID_CALL'							=> 'Unzulässiger Aufruf! Die Aktion wird abgebrochen.',

	'PAID'									=> 'Ausgezahlt',
	'PAY_WINS'								=> 'Gewinne auszahlen',
	'PAY_WINS_EXPLAIN'						=> 'Hier kannst du Gewinne oder Teilgewinne auszahlen oder als Einsatz in die nächste Saison übertragen. Bei Verrechnung in Ultimate Points, wird der UP Saldo entsprechend belastet.',

	'SELECT_SEASON'							=> 'Saison auswählen',
	'SET_DEPOSITS'							=> 'Einzahlungen berechnen', 

	'WINS'									=> 'Gewinne',
));

?>