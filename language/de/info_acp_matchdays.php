<?php
/**
*
* info_acp_football_matchdays [Deutsch — Du]
*
* @package language
* @version $Id: info_acp_football_matchdays.php 12 2011-03-13 08:03:31Z football $ 
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
	'ACP_FOOTBALL_MATCHDAYS'					=> 'Spieltage',
	'ACP_FOOTBALL_MATCHDAYS_MANAGE'				=> 'Spieltage verwalten',
	'ACP_FOOTBALL_MATCHDAYS_MANAGE_EXPLAIN'		=> 'Hier kannst du je Liga Spieltage einer Saison zuordnen. Du kannst bestehende löschen oder ändern oder einen neuen Spieltag erstellen, sowie Typ, Anzahl der Spieltage, Gewinne, Name und Kurzbezeichnug der Spieltag einstellen.',
	'ACP_FOOTBALL_MATCHDAYS_MANAGEMENT'			=> 'Tipprunde-Spieltags-Verwaltung',
	'ADD_MATCHDAY'								=> 'Spieltag zur Liga hinzufügen',

	'BACKWARD_DELIVERY'							=> 'Zeitspanne wählen',
	'BACKWARD_DELIVERY_EXPLAIN'					=> 'Zeitspanne der Abgabe vor dem ersten Spielbeginn.',

	'CHANGE_DELIVERY'							=> 'Abgabetermine ändern',
	'CHANGE_DELIVERY_EXPLAIN'					=> 'Hier kannst du einmalig die zukünftigen Abgabetermine der Spieltage in Abhängigkeit zum ersten Spielbeginn neu festlegen. Hierdurch wird jedoch kein Automatismus bei zukünftigen Spielverlegungen eingerichtet. Mit Zeitspanne 0 kannst du überprüfen, ob ein Abgabetermin nach dem ersten Spielbeginn liegt und diesen entsprechend korrigieren. Soll ein einheitlicher Abgabetermin gesetzt werden, muss diese Funktion eventuell mehrfach mit unterschiedlichen Zeitspannen passend zu den Anstoßzeiten aufgerufen werden.',
	
	'DELIVERY'									=> 'Abgabezeit',
	'DELIVERY_SET_TIME'							=> 'Abgabezeit vor dem ersten Spielbeginn',
	'DELIVERY_NUMBER'							=> 'Abgabe Nr.',
	
	'GENERATE_MATCHDAY'							=> 'Fehlende Spieltage generieren',
	
	'INVALID_DDAY1_DATE'						=> 'Kein gültiger Abgabetermin.',
	'INVALID_DDAY2_DATE'						=> 'Kein gültiger 2. Abgabetermin.',
	'INVALID_DDAY3_DATE'						=> 'Kein gültiger 3. Abgabetermin.',

	'LEAGUE'									=> 'Liga',

	'MATCHDAY'									=> 'Spieltag',
	'MATCHDAY_ADDED'							=> 'Spieltag erfolgreich hinzugefügt.',
	'MATCHDAY_CONFIRM_DELETE'					=> 'Bist du sicher, dass du den %1$s. Spieltag aus der Saison %2$s mit allen Daten (Spielplan und Tipps) löschen willst?',
	'MATCHDAY_CONFIRM_REMOVE'					=> 'Bist du sicher, dass du alle überflüssigen Spieltage aus der Saison %1$s mit allen Daten (Spielplan und Tipps) löschen willst?',
	'MATCHDAY_CREATE_FAILED'					=> 'Spieltag konnte nicht erstellt werden.',
	'MATCHDAY_CREATED'							=> '%1$s Spieltag wurde erfolgreich erstellt.',
	'MATCHDAY_CURRENT'							=> 'Aktuelle Spieltag',
	'MATCHDAY_DEF'								=> 'Bestehende Spieltage',
	'MATCHDAY_DEF_EXPLAIN'						=> 'Dies sind Spieltage, die von dir oder einem anderen Administrator erstellt wurden. Du kannst Spieltageinstellungen ändern oder Spieltage löschen.',
	'MATCHDAY_DELETE'							=> 'Spieltag entfernen',
	'MATCHDAY_DELETED'							=> 'Spieltag gelöscht',
	'MATCHDAY_DELIVERY'							=> 'Abgabetermin',
	'MATCHDAY_DELIVERY_EXPLAIN'					=> 'Termin an dem der Spieltag bzw. die nächsten Spiele geschlossen werden und somit keine Tippabgabe mehr möglich ist. Wurde der Spieltag bereits geschlossen (Status 1), kann dieser durch ein zukünftigen Abgabetermin wieder geöffnet werden. Andere Statusübergänge sind nur durch Eingabe bzw. Löschen der Spielergebnisse dieses Spieltags möglich.',
	'MATCHDAY_DELIVERY2'						=> '2. Abgabetermin ',
	'MATCHDAY_DELIVERY2_EXPLAIN'				=> 'Beim Erreichen des ersten Abgabetermins wird dieser Termin als nächster Abgabetermin für die nächsten Spiele gesetzt.',
	'MATCHDAY_DELIVERY3'						=> '3. Abgabetermin',
	'MATCHDAY_DELIVERY3_EXPLAIN'				=> 'Beim Erreichen des 2. Abgabetermins wird dieser Termin als nächster Abgabetermin für die nächsten Spiele gesetzt.',
	'MATCHDAY_DETAILS'							=> 'Spieltagsdaten',
	'MATCHDAY_EDIT_EXPLAIN'						=> 'Hier kannst du einen bestehenden Spieltag bearbeiten. Du kannst Typ, Anzahl der Spieltage, Gewinne, Namen und die Kurzbezeichnung ändern.',
	'MATCHDAY_MATCHES'							=> 'Anzahl der Spiele an diesem Spieltag',
	'MATCHDAY_MATCHES_EXPLAIN'					=> 'Bei KO-Runden ist die Angabe der Anzahl der Spiele je Spieltag zwingend erforderlich.',
	'MATCHDAY_NAME'								=> 'Spieltagsbezeichnung',
	'MATCHDAY_NAME_DOUBLE'						=> 'Die Spieltagsbezeichnung wird schon benutzt bzw. wurde doppelt vergeben.',
	'MATCHDAY_NAME_EMPTY'						=> 'Die Spieltagsbezeichnung muss mindesten 3 Buchstaben lang sein.',
	'MATCHDAY_NUMBER'							=> 'Die angegebene Spieltag muss numerisch (1-99) sein. Bitte eine Spieltagnummer eingeben.',
	'MATCHDAY_STATUS'							=> 'Status',
	'MATCHDAY_STATUS_EXPLAIN'					=> '0 = offen (Tippabgabe möglich) <br /> 1 = keine Tippabgabe mehr möglich und noch keine Ergebnisse <br /> 2 = vorläufige Ergebnisse liegen vor <br /> 3 = abgeschlossen, endgültige Ergebnisse liegen vor.',
	'MATCHDAY_UPDATE_FAILED'					=> 'Spieltageinstellungen konnten nicht aktualisiert werden.',
	'MATCHDAY_UPDATED'							=> 'Spieltageinstellungen erfolgreich aktualisiert.',
	'MATCHDAYS_CREATED'							=> '%1$s Spieltage wurden erfolgreich erstellt.',
	'MATCHDAYS_NO_DELETE'						=> 'Du darfst keine Spieltag löschen. Dies dürfen nur Gründungsmitglieder',
	'MATCHDAYS_REMOVED'							=> 'Überflüssige Spieltage gelöscht',

	'NEW_DELIVERY'								=> 'Neue Abgabezeit',
	'NO_DELIVERIES_UPDATED'						=> 'Es wurden keine Abgabetermine geändert.',
	'NO_DELIVERY'								=> 'Der Abgabetermin fehlt.',
	'NO_DELIVERY2'								=> 'Der 2. Abgabetermin fehlt oder der 3. Abgabetermin muss gelöscht werden.',
	'NO_LEAGUE'									=> 'Es existiert keine Liga in der Saison %1$s. Bitte zuerst eine Liga in der Saison %1$s erstellen.',
	'NO_MATCHDAYS_CREATED'						=> 'Es wurden bislang keine Spieltage erstellt.',
	'NO_MORE_MATCHDAYS'							=> 'Es wurde keine weiteren Spieltage hinzugefügt.',
	'NO_SEASON'									=> 'Es existiert keine Saison. Bitte zuerst eine Saison erstellen.',

	'OPEN_MATCH'								=> 'Das Spiel Nr. %s liegt vor dem gewählten Abgabetermin. Der Abgabetermin ist daher nicht zulässig.',
	'OPEN_MATCHES'								=> 'Die Spiele Nr. %s liegen vor dem gewählten Abgabetermin. Der Abgabetermin ist daher nicht zulässig.',

	'PREDICTION_LEAGUE'							=> 'Tipprunde',

	'REMOVE_MATCHDAYS'							=> 'Überflüssige Spieltage entfernen',

	'SEASON'									=> 'Saison',
	'SELECT_LEAGUE'								=> 'Liga auswählen',
	'SHOW_DELIVERY'								=> 'Abgabezeiten anzeigen',

	'TOO_LARGE_MATCHES'							=> 'Die Anzahl der Spiele je Spieltag ist zu groß.',
	'TOO_SMALL_DELIVERY2'						=> 'Der 2. Abgabetermin darf nicht vor dem 1. Abgabetermin liegen.',
	'TOO_SMALL_DELIVERY3'						=> 'Der 3. Abgabetermin darf nicht vor dem 2. Abgabetermin liegen.',
	'TOO_SMALL_MATCHES'							=> 'Die Anzahl der Spiele je Spieltag ist zu klein.',

	'UPDATE_DELIVER'							=> '%s Abgabetermin geändert.',
	'UPDATE_DELIVERIES'							=> '%s Abgabetermine geändert.',
	'UPDATE_DELIVERY'							=> 'Neue Abgabezeiten',
	'UPDATE_DELIVERY_EXPLAIN'					=> 'Wähle die Datensätze aus, deren Abgabezeit neu gesetzt werden soll. Rot markierte Abgabezeiten liegen nach dem neu berechneten Abgabetermin.',
));

?>