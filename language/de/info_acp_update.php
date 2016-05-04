<?php
/**
*
* info_acp_football_update [Deutsch — Du]
*
* @package language
* @version $Id: info_acp_football_update.php 14 2011-03-15 18:03:24Z football $ 
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
	'ACP_FOOTBALL_UPDATE'					=> 'Spielplan aktualisieren',
	'ACP_FOOTBALL_UPDATE_MANAGE'			=> 'Spielplan aktualisieren',
	'ACP_FOOTBALL_UPDATE_MANAGE_EXPLAIN'	=> 'Hier kannst du deine Spielpläne, mit Daten von der Demoseite oder einer anderen Tipprunden-Seite aktualisieren oder neu erstellen. Wähle zuerst eine Tipprunden-Seite und Liga aus, von der das Update stammt. Nach Auswahl der Ziel-Liga bekommst du Abweichungen zwischen dieser Liga und dem Update angezeigt und kannst wählen welche Änderungen du übernehmen möchtest. Falls du eine neue Liga erstellen möchtest, werden alle Daten übernommen. ',
	'ACP_FOOTBALL_UPDATE_MANAGEMENT'		=> 'Tipprunde-Spielplan aktualisieren',

	'ALLOW_URL_FOPEN'					=> 'Die PHP-Einstellung <var>allow_url_fopen</var> erlaubt keinen Zugriff auf andere Seiten. <br />Du musst die Updateidatei(en) downloaden und im Verzeichnis root/store speichern. ',
	'CHOOSE_LEAGUES'					=> 'Quell- und Ziel-Spielplan wählen',
	'CHOOSE_OTHER_LEAGUE'				=> 'Anderen Spielplan wählen',
	'COMPARE_UPDATE'					=> 'Vergleichen mit Datenbank',
	'CURRENT_VALUE'						=> 'Aktueller Wert',

	'DB_INSERT_SEASON'					=> '%s Saison eingefügt. ',
	'DB_INSERT_LEAGUE'					=> '%s Liga eingefügt. ',
	'DB_INSERT_MATCHDAY'				=> '%s Spieltag eingefügt. ',
	'DB_INSERT_MATCHDAYS'				=> '%s Spieltage eingefügt. ',
	'DB_INSERT_TEAM'					=> '%s Mannschaft eingefügt. ',
	'DB_INSERT_TEAMS'					=> '%s Mannschaften eingefügt. ',
	'DB_INSERT_MATCH'					=> '%s Spiel eingefügt. ',
	'DB_INSERT_MATCHES'					=> '%s Spiele eingefügt. ',
	'DB_UPDATE_BIT_DELIVER'				=> '%s Spieltags-Abgabetermin mit Tippabgabe bis zum Spielbeginn korrigiert. ',
	'DB_UPDATE_BIT_DELIVERIES'			=> '%s Spieltags-Abgabetermine mit Tippabgabe bis zum Spielbeginn korrigiert. ',
	'DB_UPDATE_DELIVER'					=> 'Abgabetermin des Spieltags %s korrigiert. ',
	'DB_UPDATE_DELIVERIES'				=> 'Abgabetermine des Spieltage %s korrigiert. ',
	'DB_UPDATE_MATCHDAY'				=> '%s Spieltag aktualisiert. ',
	'DB_UPDATE_MATCHDAYS'				=> '%s Spieltage aktualisiert. ',
	'DB_UPDATE_TEAM'					=> '%s Mannschaft aktualisiert. ',
	'DB_UPDATE_TEAMS'					=> '%s Mannschaften aktualisiert. ',
	'DB_UPDATE_MATCH'					=> '%s Spiel aktualisiert. ',
	'DB_UPDATE_MATCHES'					=> '%s Spiele aktualisiert. ',
	'DB_UPDATE_STATUS_MATCHDAY'			=> 'An %s Spieltag wurde der Status angepasst. ',
	'DB_UPDATE_STATUS_MATCHDAYS'		=> 'An %s Spieltagen wurde der Status angepasst. ',
	'DUPLICATE_TEAM'					=> 'Diese Team-ID wurde doppelt zugeordnet. ',
	
	'ERROR_OPEN_LEAGUE_XML'				=> 'Fehler! Die Liga XML-Datei konnte nicht geöffnet werden. Bitte den Link überprüfen. ',
	'ERROR_OPEN_SEASON_XML'				=> 'Fehler! Die Saison XML-Datei konnte nicht geöffnet werden. Bitte den Link überprüfen. ',
	'ERROR_READ_LEAGUE_XML'				=> 'Fehler! Die Liga XML-Datei konnte nicht gelesen werden. Bitte den Code oder die XML-Datei überprüfen. ', 
	'ERROR_READ_SEASON_XML'				=> 'Fehler! Die Saison XML-Datei konnte nicht gelesen werden. Bitte den Code überprüfen. ',
	'ERROR_LOAD_LEAGUE_XML'				=> 'Fehler! Die Liga XML-Datei konnte nicht gelesen werden. Bitte die Datei %s überprüfen. ',
	'ERROR_XML_CODE'					=> 'Fehlerhafter Code! Um die XML-Datei anzuzeigen, muss der korrekte Code beim Aufruf übergeben werden. ',
	'ERROR_XML_CREATE'					=> 'Fehler! Die XML-Datei konnte nicht erzeugt werden. ',
	
	'INSERT_LEAGUE'						=> 'Neue Liga anlegen',
	'INSERT_MATCHDAYS'					=> 'Spieltage anlegen',
	'INSERT_MATCHES'					=> 'Spielbegegnungen anlegen',
	'INSERT_SEASON'						=> 'Neue Saison anlegen',
	'INSERT_TEAMS'						=> 'Mannschaften anlegen',

	'LEAGUE_SHORTCUT'					=> 'Liga Kürzel',
	'LOAD'								=> 'Laden',
	'LOG_FOOTB_UPDATE'					=> '<strong>Football Prediction League Spielplan-Update</strong><br />» %s',

	'MAP_TEAMS'							=> 'Teams zuordnen',
	'MATCH_OF_GROUP'					=> 'Spiel in Gruppe',
	'MATCHES'							=> 'Spiele',
	'MISMATCH_LEAGUE_TYPE'				=> 'Der Ligatyp (%s) des Quell-Spielplans passt nicht zur vorhandenen Liga.',
	'MISMATCH_MATCHDAYS'				=> 'Die Anzahl der Spieltage (%s) des Quell-Spielplans passt nicht zur vorhandenen Liga.',
	'MISMATCH_DB_MATCHDAYS'				=> 'Die Anzahl der Spieltage (%s) in der Datenbank passt nicht zur vorhandenen Liga.',
	'MISMATCH_MATCHES'					=> 'Die Anzahl der Spiele (%s) an einem Spieltag des Quell-Spielplans passt nicht zur vorhandenen Liga.',
	'MISMATCH_DB_MATCHES'				=> 'Die Anzahl der Spiele (%s) in der Datenbank passt nicht zum Quell-Spielplan.',
	'MISMATCH_MOM'						=> 'Die Anzahl der Spiele je Spieltag (%s) des Quell-Spielplans passt nicht zur vorhandenen Liga.',
	'MISSING_TEAMS'						=> 'Folgende Team IDs müssen noch zugeordnet werden: %s',

	'NEW_LEAGUE'						=> 'Neue Liga',
	'NEW_LEAGUE_EXIST'					=> 'Die neue Liga %s existiert bereits. ',
	'NEW_LEAGUE_EXPLAIN'				=> 'Bei einer neuen Liga bitte hier die Liganummer angeben. Bei einer bestehenden Liga darf diese Liganummer nicht überschrieben werden, da sonst eine eventuell bestehende Liga aktualisiert wird. ',
	'NEW_TEAM'							=> 'Neues Team wird eingefügt',
	'NO_DB_CHANGES'						=> 'Es wurden keine Änderungen durchgeführt. ',
	'NO_DIFFERENCES'					=> 'Keine Unterschiede gefunden. Die Spielpläne sind identisch. ',
	'NO_XML_LEAGUE'						=> 'Im Verzeichnis root/store konnte keine Update-Dateien gefunden werden. ',
	'NO_XML_SEASON'						=> 'Es wurde keine Quell-Daten zum aktualisieren gefunden. ',
	'NO_XML_ARRAY'						=> 'Fehler! Daten nicht mehr vorhanden. Bitte wiederhole die Aktion von vorne. ',

	'OTHER_FIELDS'						=> 'Andere Felder',

	'PREDICTION_LEAGUE'					=> 'Tipprunde',

	'SEASON'							=> 'Saison',
	'SELECT_EXPLAIN'					=> 'Nur die hier markierten Datensätze werden übernommen',
	'SHOW_UPDATE'						=> 'Neue Liga anzeigen',
	'SOURCE'							=> 'Quelle',

	'TARGET'							=> 'Ziel',
	'TEAM_MAPPING'						=> 'Teamzuordnung',
	'TEAM_NAME_SHORT'					=> 'Kurzname',
	'TRANSFER_TEAM'						=> 'Team übernehmen',

	'UPDATE_LEAGUE'						=> 'Ligadaten aktualisieren',
	'UPDATE_MATCHDAYS'					=> 'Spieltagsdaten aktualisieren',
	'UPDATE_MATCHES'					=> 'Spielbegegnungen aktualisieren',
	'UPDATE_ONLY_FINAL'					=> 'Nur endgültige Ergebnisse übernehmen',
	'UPDATE_SAME_STATUS'				=> 'Ergebnisse mit gleichem Status aktualisieren',
	'UPDATE_NEG_STATUS'					=> 'Negative Status übernehmen',
	'UPDATE_TEAMS'						=> 'Mannschaftsdaten aktualisieren',
	'UPDATE_THIS'						=> 'Änderungen in diesem Feld übernehmen',

	'XML_SEASON_URL'					=> 'URL Quelle für das Update',
	'XML_SEASON_URL_EXPLAIN'			=> 'URL zu football_xml_season.php oder localhost. Bei localhost wird nach league_* Datei in root/store gesucht. ',
));

?>