<?php
/**
*
* info_acp_football_results [Deutsch — Du]
*
* @package language
* @version $Id: info_acp_football_results.php 6 2011-01-09 08:02:37Z football $ 
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
	'ACP_FOOTBALL_RESULTS'					=> 'Spielergebnisse',
	'ACP_FOOTBALL_RESULTS_MANAGE'			=> 'Spielergebnisse eingeben',
	'ACP_FOOTBALL_RESULTS_MANAGE_EXPLAIN'	=> 'Hier kannst du Spielergebnisse bestätigen, eingeben, löschen oder aus der Wertung nehmen.',
	'ACP_FOOTBALL_RESULTS_MANAGEMENT'		=> 'Tipprunde-Spielergebnis-Verwaltung',
	'ADVICE'								=> 'Hinweis',

	'BETS_AND_RANKS'						=> 'Tipps und Ranglisten der',

	'DAY'									=> 'Tag',
	'DELETE'								=> 'Löschen',
	'DELETE_EXPLAIN'						=> 'Spielergebnis löschen und Status auf 1 setzen.',

	'GUEST'									=> 'Gast',

	'HOME'									=> 'Heim',

	'LEAGUE'								=> 'Liga',

	'MATCH_BEGIN'							=> 'Spielbeginn',
	'MATCH_STATUS_TITLE'					=> '-2=Spiel wird am 3. Abgabetermin geschlossen  -1=Spiel wird am 2. Abgabetermin geschlossen   0=offen (Tippabgabe möglich), wird am Abgabetermin geschlossen   1=keine Tippabgabe mehr möglich und noch kein Ergebnis    2=vorläufiges Ergebnis liegt vor    3=endgültiges Ergebnis liegt vor    4-6= wie 1-3 jedoch ohne Wertung.',
	'MATCHDAY'								=> 'Spieltag',
	'MONTH'									=> 'Monat',

	'NO_LEAGUE'								=> 'Es existiert keine Liga in der Saison %1$s. Bitte zuerst eine Liga in der Saison %1$s erstellen.',
	'NO_MATCHDAY'							=> 'Es existiert kein Spieltag in %1$s Saison %2$s. Bitte zuerst Spieltage erstellen.',
	'NO_MATCHES_CREATED'					=> 'Es wurden bislang keine Spielpaarungen hinterlegt.',
	'NO_SEASON'								=> 'Es existiert keine Saison. Bitte zuerst eine Saison erstellen.',
	'NO_VALUATION'							=> 'o. Wertung',
	'NO_VALUATION_EXPLAIN'					=> 'Spiel aus der Wertung nehmen, da dieses verschoben oder manipuliert wurde.',
	'NUMBER'								=> 'Nr',

	'OVERTIME'								=> 'Verlängerung',
	'OVERTIME_EXPLAIN'						=> 'Spielergebnis inkl. Verlängerung und Elfmeterschiessen.',

	'PREDICTION_LEAGUE'						=> 'Tipprunde',

	'RANKING'								=> 'Rangliste',
	'RESULT'								=> 'Ergebnis',
	'RESULT_DELETED'						=> '%1$s Spielergebnis wurde gelöscht.',
	'RESULT_DETAILS'						=> 'Spielergebnisse',
	'RESULT_EXPLAIN'						=> 'Spielergebnis nach 90 Minuten oder Spielzwischenstand',
	'RESULT_NO_VALUATION'					=> '%1$s Spiel wurde aus der Wertung genommen.',
	'RESULT_SAVED'							=> '%1$s Spielergebnis wurde gespeichert.',
	'RESULTS_DELETED'						=> '%1$s Spielergebnisse wurden gelöscht.',
	'RESULTS_NO_VALUATION'					=> '%1$s Spiele wurden aus der Wertung genommen.',
	'RESULTS_SAVED'							=> '%1$s Spielergebnisse wurden gespeichert.',

	'SAVE'									=> 'Speichern',
	'SAVE_FAILED'							=> 'Die Spielergebnisse konnten nicht gespeichert werden.',
	'SEASON'								=> 'Saison',
	'SELECT'								=> 'Auswahl',
	'SELECT_EXPLAIN'						=> 'Nur die hier markierten Spiele werden gespeichert/nicht gewertet/gelöscht.',
	'SELECT_MATCHDAY'						=> 'Spieltag wählen',
	'SET_STATUS_TO'							=> 'Der Status des Spieltages wurde auf %1$s gesetzt',
	'STATUS'								=> 'Status',

	'VS'									=> 'Begegnung',

	'YEAR'									=> 'Jahr',
));

?>