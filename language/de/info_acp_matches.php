<?php
/**
*
* info_acp_football_matches [Deutsch — Du]
*
* @package language
* @version $Id: info_acp_football_matches.php 1 2010-05-17 22:09:43Z football $ 
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
	'ACP_FOOTBALL_MATCHES'					=> 'Spielpläne',
	'ACP_FOOTBALL_MATCHES_MANAGE'			=> 'Spielpläne verwalten',
	'ACP_FOOTBALL_MATCHES_MANAGE_EXPLAIN'	=> 'Hier kannst du je Saison und Liga Spielpläne verwalten. Du kannst fehlende Spielpaarung zum Spielplan hinzufügen und die Spieldaten eines Spieltags verwalten.',
	'ACP_FOOTBALL_MATCHES_MANAGEMENT'		=> 'Tipprunde-Spielplan-Verwaltung',

	'DAY'									=> 'Tag',

	'FORMULA_GUEST'							=> 'Formel Gastmannschaft',
	'FORMULA_GUEST_EXPLAIN'					=> 'Siehe Erläuterungen Formel Heimmannschaft',
	'FORMULA_HOME'							=> 'Formel Heimmannschaft',
	'FORMULA_HOME_EXPLAIN'					=> 'D = wird noch ausgelost <br />L 100 = Verlierer Spiel 100 <br />W 100 = Sieger Spiel 100 <br /> W 100;101 = Sieger Spiele 100 und 101 <br /> G A1 = 1. Gruppe A',

	'GENERATE_MATCHES'						=> 'Spielplan-Gerüst vervollständigen',

	'INVALID_MDAY_DATE'						=> 'Kein gültiges Spieldatum.',

	'KO'									=> 'KO',

	'LEAGUE'								=> 'Liga',

	'MATCH_BEFORE_DELIVERY'					=> 'Der Spielbeginn liegt vor der Tippabgabe des Spieltags. Dies ist nicht zulässig.',
	'MATCH_BEGIN'							=> 'Spielbeginn',
	'MATCH_CONFIRM_DELETE'					=> 'Bist du sicher, dass du das %1$s. Spiel aus %2$s Saison %3$s mit allen Daten (Spielplan und Tipps) löschen willst?',
	'MATCH_CREATED'							=> '%1$s Spielpaarung wurde erfolgreich erstellt.',
	'MATCH_DELETED'							=> 'Spiel wurde erfolgreich gelöscht',
	'MATCH_DETAILS'							=> 'Spieldaten',
	'MATCH_EDIT_EXPLAIN'					=> 'Hier kannst du eine bestehende Spielpaarung bearbeiten. Du kannst den Termin, die Paarung und eine Formel festlegen.',
	'MATCH_GROUP'							=> 'Gruppenspiel der Gruppe',
	'MATCH_GUEST'							=> 'Gast',
	'MATCH_HOME'							=> 'Heim',
	'MATCH_KO'								=> 'KO-Spiel',
	'MATCH_KO_EXPLAIN'						=> 'Dieses Spiel kann zu einer Verlägerung bzw. einem Elfmeterschießen führen.',
	'MATCH_NUMBER'							=> 'Spiel Nr',
	'MATCH_ODDS'							=> 'Quoten',
	'MATCH_RATING'								=> 'Gewichtung',
	'MATCH_STATUS'							=> 'Status',
	'MATCH_STATUS_EXPLAIN'					=> '&nbsp;-2=Spiel wird am 3. Abgabetermin geschlossen<br />&nbsp;-1=Spiel wird am 2. Abgabetermin geschlossen<br />&nbsp;&nbsp;0=offen (Tippabgabe möglich), wird am Abgabetermin geschlossen<br />&nbsp;&nbsp;1=keine Tippabgabe mehr möglich und noch kein Ergebnis<br />&nbsp;&nbsp;2=vorläufiges Ergebnis liegt vor<br />&nbsp;&nbsp;3=endgültiges Ergebnis liegt vor<br />4-6= wie 1-3 jedoch ohne Wertung.',
	'MATCH_UPDATE_FAILED'					=> 'Spielpaarung konnte nicht aktualisiert werden.',
	'MATCH_UPDATED'							=> 'Spielpaarung erfolgreich aktualisiert.',
	'MATCHDAY'								=> 'Spieltag',
	'MATCHDAY_MISSED'						=> 'Bevor dies Aktion durchgeführt werden kann, müssen die fehlenden Spieltag erfasst werden.',
	'MATCHES_CREATE_FAILED'					=> 'Spielplan konnte nicht vervollständigt werden.',
	'MATCHES_CREATED'						=> '%1$s Spielpaarungen wurden erfolgreich erstellt.',
	'MATCHES_DEF'							=> 'Vorhandene Spielpaarungen',
	'MATCHES_DEF_EXPLAIN'					=> 'Dies sind Spielpaarungen, die von dir oder einem anderen Administrator erstellt wurden. Du kannst die Spielpaarungen ändern oder löschen.',
	'MATCHES_NO_DELETE'						=> 'Du darfst keine Spiele löschen. Dies dürfen nur Gründungsmitglieder',
	'MONTH'									=> 'Monat',

	'NO_LEAGUE'								=> 'Es existiert keine Liga in der Saison %1$s. Bitte zuerst eine Liga in der Saison %1$s erstellen.',
	'NO_MATCH'								=> 'Diese Spielpaarung existiert nicht.',
	'NO_MATCH_BEGIN'						=> 'Der Spielbeginn fehlt.',
	'NO_MATCHDAY'							=> 'Es existiert kein Spieltag in %1$s Saison %2$s. Bitte zuerst Spieltage erstellen.',
	'NO_MATCHES_CREATED'					=> 'Es wurden bislang keine Spielpaarungen hinterlegt.',
	'NO_SEASON'								=> 'Es existiert keine Saison. Bitte zuerst eine Saison erstellen.',

	'ODD_1'									=> 'Heimsieg',
	'ODD_x'									=> 'Unentschieden',
	'ODD_2'									=> 'Auswärtssieg',

	'PREDICTION_LEAGUE'						=> 'Tipprunde',

	'SEASON'								=> 'Saison',
	'SELECT_MATCHDAY'						=> 'Spieltag wählen',

	'TOO_LARGE_MATCH_BEGIN_H'				=> 'Stundenangabe beim Spielbeginn zu groß.',
	'TOO_LARGE_MATCH_BEGIN_MIN'				=> 'Minutenangabe beim Spielbeginn zu groß.',
	'TOO_SMALL_MATCH_BEGIN_H'				=> 'Stundenangabe beim Spielbeginn zu klein.',
	'TOO_SMALL_MATCH_BEGIN_MIN'				=> 'Minutenangabe beim Spielbeginn zu klein.',

	'UNKNOWN'								=> 'noch nicht bekannt',

	'YEAR'									=> 'Jahr',
));

?>