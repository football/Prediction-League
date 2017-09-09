<?php
/**
* 
* info_acp_football [Deutsch — Du]
*
* @package language
* @version $Id: info_acp_football.php 12 2011-03-13 08:03:31Z football $ 
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

// Football Settings
$lang = array_merge($lang, array(
	'ACP_FOOTBALL'						=> 'Fussball',
	'ACP_FOOTBALL_MANAGEMENT'			=> 'Tipprunde-Verwalten',
	'ACP_FOOTBALL_OPERATION'			=> 'Spielbetrieb',
	'ACP_FOOTBALL_MANAGE'				=> 'Spielplan Verwaltung',
	
	'ACP_FOOTBALL_CONFIGURATION'		=> 'Tipprunden-Konfiguration',
	'ACP_FOOTBALL_SETTINGS'				=> 'Tipprunden-Einstellungen',
	'ACP_FOOTBALL_SETTINGS_EXPLAIN'		=> 'Hier kannst du einige grundlegende Einstellungen der Tipprunde vornehmen, ihr einen passenden Namen und eine Beschreibung geben und, neben anderen Werten, die Standard-Einstellungen für die Anzeige anpassen. ',

	'DISABLE_FOOTBALL'					=> 'Tipprunde deaktivieren',
	'DISABLE_FOOTBALL_EXPLAIN'			=> 'Hiermit sperrst du die Tipprunde für alle Benutzer. Wenn du möchtest, kannst du eine kurze Nachricht (bis zu 255 Zeichen) angeben. ',
	'DISPLAY_LAST_USERS'				=> 'Anzahl anzuzeigender letzter Besucher des Forums',
	'DISPLAY_LAST_USERS_EXPLAIN'		=> 'Limitiert die Anzeige der letzten Besucher im selbigen Tipprunden-Block. Mit 0 wird die Anzeige des Blocks unterdrückt. ',
	'DISPLAY_LAST_RESULTS'				=> 'Anzahl maximal anzuzeigender letzter Spielergebnisse',
	'DISPLAY_LAST_RESULTS_EXPLAIN'		=> 'Limitiert die Anzeige der letzten Spielergebnisse im selbigen Tipprunden-Block. Finden mehr Spiele am letzten Tag statt, werden alle Ergebnisse dieses Tages angezeigt. Mit 0 wird die Anzeige des Blocks unterdrückt. ',
	'DISPLAY_RANKS'						=> 'Anzahl angezeigter Tipper in den Übersichts-Ranglisten',
	'DISPLAY_RANKS_EXPLAIN'				=> 'Limitiert die Anzeige der Spieltags- und Gesamtrangliste in der Hauptansicht. Die eigene Platzierung wird ggf. unten angehängt. ',

	'FOOTBALL_CODE'						=> 'Tipprunden Code',
	'FOOTBALL_CODE_EXPLAIN'				=> 'Zugangscode zur Tipprunde z.B. für Cronjobs. ',
	'FOOTBALL_FULLSCREEN'				=> 'Tipprunden-Seiten im Fullscreen-Modus',
	'FOOTBALL_FULLSCREEN_EXPLAIN'		=> 'Hiermit kannst Du, abweichend vom Forum, die Tipprundenseiten mit minimalen Seitenrändern anzeigen lassen. ',
	'FOOTBALL_HEADER_ENABLE'			=> 'Header-Bild je Liga verwenden',
	'FOOTBALL_HEADER_ENABLE_EXPLAIN'	=> 'Hiermit kannst Du festlegen, ob je Liga die zugehöriges Bild im Header angezeigt werden soll. ',
	'FOOTBALL_INFO'						=> 'Tipprunden Information anzeigen',
	'FOOTBALL_INFO_EXPLAIN'				=> 'Hiermit kannst Du eine kurze Information (bis zu 255 Zeichen) oberhalb der Tippdaten anzeigen lassen. ',
	'FOOTBALL_NAME'						=> 'Name der Tipprunde',
	'FOOTBALL_NAME_EXPLAIN'				=> 'Name der im Tipprunden-Menu verwendet wird, um zur Hauptseite zu gelangen. ',
	'FOOTBALL_OVERRIDE_STYLE'			=> 'Style überschreiben',
	'FOOTBALL_OVERRIDE_STYLE_EXPLAIN'	=> 'Verwendet für die Tipprunde immer den Standard-Tipprunden-Style. ',
	'FOOTBALL_STYLE'					=> 'Standard Tipprunden Style',
	'FOOTBALL_UPDATE_SOURCE'			=> 'Update Quelle',
	'FOOTBALL_UPDATE_SOURCE_EXPLAIN'	=> 'Link zur Quelle des Updates. Falls leer, wird automatisch <var>http://football.bplaced.net/football/xml/football_xml_season.php</var> gewählt. ',
	'FOOTBALL_UPDATE_CODE'				=> 'Update Code',
	'FOOTBALL_UPDATE_CODE_EXPLAIN'		=> 'Nur mit diesem Code können Updates von dieser Seite heruntergeladen werden. ',

	'GUEST_VIEW'						=> 'Tipprunde für Gäste sichtbar',
	'GUEST_VIEW_EXPLAIN'				=> 'Soll die Tipprunde für Gäste sichtbar sein?',

	'USER_VIEW'							=> 'Tipprunde nur für Teilnehmer sichtbar',
	'USER_VIEW_EXPLAIN'					=> 'Soll die Tipprunde nur für Tipprunden-Teilnehmer sichtbar sein?',

	'TIME_SHIFT'						=> 'Zeitverschiebung',
	'TIME_SHIFT_EXPLAIN'				=> 'Differenz in Stunden zur Board Zeitzone wenn dein Host in einer anderen Zeitzone steht, damit die Tippabgabe korrekt funktioniert. ',

	'LEFT_COLUMN'						=> 'Spaltenbreite Links in Pixeln',
	'LEFT_COLUMN_EXPLAIN'				=> 'Optimale Breite 180 Pixel. Dieser Wert sollte nicht unterschritten werden. ',

	'PREDICTION_LEAGUE'					=> 'Tipprunde',

	'RIGHT_COLUMN'						=> 'Spaltenbreite Rechts in Pixeln',
	'RIGHT_COLUMN_EXPLAIN'				=> 'Optimal 180 Pixel. Dieser Wert wird durch externe Einblendungen ggf. übersteuert. ',

	'USERS_PAGE'						=> 'Tipper pro Seite',
	'USERS_PAGE_EXPLAIN'				=> 'Anzahl der Tipper je Seite in der Hauptrangliste und Alle Tipps. ',
	
	'WIN_NAME'							=> 'Gewinn Name (oder Währung)',
	'WIN_NAME_EXPLAIN'					=> 'Der Name, der für die Gewinne in der Tipprunde angezeigt werden soll',

	'ANNOUNCEMENT_TOPIC'				=> 'Release Ankündigung',
	'CURRENT_VERSION'					=> 'Derzeitige Version',
	'DOWNLOAD_LATEST'					=> 'Neueste Version herunterladen',
	'LATEST_VERSION'					=> 'Neueste Version',
	'NOT_UP_TO_DATE'					=> '%s ist nicht aktuell',
	'RELEASE_ANNOUNCEMENT'				=> 'Ankündigungsthema',
	'UP_TO_DATE'						=> '%s ist aktuell',
	'FOOTBALL_VERSION_CHECK'			=> 'Football Prediction League Extension Version Check',

));

// Football Features 
$lang = array_merge($lang, array(
	'ACP_FOOTBALL_FEATURES'				=> 'Tipprunden-Funktionalitäten',
	'ACP_FOOTBALL_FEATURES_EXPLAIN'		=> 'Hier kannst du einige Funktionen der Tipprunde aktivieren bzw. deaktivieren',

	'AUTO'								=> 'Auto',

	'BANK'								=> 'Tipprunden Konto verwalten',
	'BANK_EXPLAIN'						=> 'Sollen Tipprunden Konten mit Einsätzen und Gewinnen gepflegt werden?',

	'FOOTBALL_SEASON_START'				=> 'Starte in Saison',
	'FOOTBALL_SEASON_START_EXPLAIN'		=> 'Starte in dieser oder in der automatisch ermittelten Saison, wenn noch keine ausgewählt wurde. ',

	'FOOTBALL_REMEMBER_ENABLE'			=> 'Cronjob für die Tipp-Erinnerungsmail aktivieren',
	'FOOTBALL_REMEMBER_ENABLE_EXPLAIN'	=> 'Hier kannst Du angeben, ob 1 Tag vor Tippabgabe eine Erinnerungsmail versendet werden soll.',

	'FOOTBALL_REMEMBER_NEXT_RUN'		=> 'Nächster Cronjoblauf für die Tipp-Erinnerungsmail',
	'FOOTBALL_REMEMBER_NEXT_RUN_EXPLAIN'=> 'Hier kannst Du einstellen, wann der Cronjob für die Tipp-Erinnerungsmail frühestens wieder aufgerufen wird. Nach Ausführung wird der Cronjob für den Folgetag zur gleichen Uhrzeit neu eingeplant.',

	'FOUNDER_DELETE'					=> 'Nur Gründungsmitglieder dürfen löschen',
	'FOUNDER_DELETE_EXPLAIN'			=> 'Löschung von Spielplandaten wie Saisons, Ligen, Teams, Spieltage und Spielpläne nur auf Gründungsmitglieder beschränken. ',

	'RESULTS_AT_TIME'					=> 'Eingabe endgültiger Spielergebnisse erst nach Spielende',
	'RESULTS_AT_TIME_EXPLAIN'			=> 'Eingabe der endgültigen Spielergebnisse im Adminbereich erst nach Spielende freigeben. Die Eingabe von vorläufigen Spielergebnissen durch die Tipper ist hiervon unabhängig. ',

	'SAME_ALLOWED'						=> 'Alle Tipps an einem Spieltag gleich',
	'SAME_ALLOWED_EXPLAIN'				=> 'Identische Tipps (Oma-Tipps) bei allen Spielen eines Spieltags erlauben bzw. verbieten. ',

	'ULT_POINTS'						=> 'Ultimate Points einbinden',
	'ULT_POINTS_EXPLAIN'				=> 'Soll es möglich sein, die Punkte oder Gewinne in Ultimate Points verrechnen zu lassen? Dazu muss Ultimate Points installiert sein. ',
	'ULT_POINTS_FACTOR'					=> 'Ultimate Points Faktor',
	'ULT_POINTS_FACTOR_EXPLAIN'			=> 'Faktor mit dem die Punkte je Spieltag in Ultimate Points gutgeschrieben werden. ',
	'UP_NONE'							=> 'Keine',
	'UP_POINTS'							=> 'Punkte',
	'UP_WINS'							=> 'Gewinne',

	'VIEW_BETS'							=> 'Tipps immer Anzeigen',
	'VIEW_BETS_EXPLAIN'					=> 'Sollen alle Tipps sofort angezeigt werden? Wenn Nein, dann werden sie erst nach Tippabgabeschluss angezeigt. ',
	'VIEW_CURRENT'						=> 'Anzeige startet in aktueller Liga',
	'VIEW_CURRENT_EXPLAIN'				=> 'Soll beim ersten Aufruf die Liga angezeigt werden, in der die nächsten Spiele stattfinden? Sonst wird immer die erste Liga angezeigt',
	'VIEW_TENDENCIES'					=> 'Tipp-Tendenzen immer Anzeigen',
	'VIEW_TENDENCIES_EXPLAIN'			=> 'Sollen alle Tipp-Tendenzen sofort angezeigt werden? Wenn Nein, dann werden sie erst nach Tippabgabeschluss angezeigt. ',

	'WIN_HITS02'						=> 'Gewinne Volltreffer-Wertung mit Auswärtspunkten',
	'WIN_HITS02_EXPLAIN'				=> 'Sollen Gewinne der Volltreffer-Wertung mit Auswärtspunkten angezeigt werden? Wenn Nein, muss sichergestellt werden, dass dazu keine Gewinne in den bestehenden Ligen hinterlegt wurden. ',
));

// Football Menu
$lang = array_merge($lang, array(
	'ACP_FOOTBALL_MENU'					=> 'Tipprunden-Menu',
	'ACP_FOOTBALL_MENU_EXPLAIN'			=> 'Hier kannst du Links für das Tipprunden-Menu hinterlegen. Die Linkbeschriftung kannst du frei wählen. ',

	'FOOTBALL_BREADCRUMB'				=> 'Tipprunden breadcrumb anzeigen',
	'FOOTBALL_BREADCRUMB_EXPLAIN'		=> 'Soll ein Breadcrumb-Link für die Tipprunden angezeigt werden? ',
	'FOOTBALL_SIDE'						=> 'Tipprunden Sidebar anzeigen',
	'FOOTBALL_SIDE_EXPLAIN'				=> 'Soll die Tipprunden Sidebar mit den Menüeinträgen angezeigt werden? ',
	'FOOTBALL_MENU'						=> 'Tipprunden Menu anzeigen',
	'FOOTBALL_MENU_EXPLAIN'				=> 'Soll das Tipprunden Dropdown Menü in der Navigationsleiste angezeigt werden? ',
	'MENU_DESC1'						=> 'Beschriftung http-Link 1',
	'MENU_DESC1_EXPLAIN'				=> 'Die Beschriftung des Links darf keine unzulässigen Sonderzeichen enthalten und höchsten 20 Zeichen lang sein. ',
	'MENU_DESC2'						=> 'Beschriftung http-Link 2',
	'MENU_DESC3'						=> 'Beschriftung http-Link 3',
	'MENU_LINK1'						=> 'Http-Link 1',
	'MENU_LINK1_EXPLAIN'				=> 'Bitte hier nur http Links eingeben. ',
	'MENU_LINK2'						=> 'Http-Link 2',
	'MENU_LINK3'						=> 'Http-Link 3',
	'MENU_NO_LINK'						=> 'Kein Link hinterlegt',
));


// Football Help
$lang = array_merge($lang, array(
	'ACP_FOOTBALL_USERGUIDE'			=> 'Benutzerhilfe',
	'ACP_FOOTBALL_USERGUIDE_EXPLAIN'	=> 'Hier findest du Hilfe für die Einstellungen des phpBB3 Football MODs.<br />Wenn du Fragen hast, dann schaue bitte immer zuerst hier!',
));


#// Football Logs
$lang = array_merge($lang, array(
	'LOG_FOOTBALL_FEATURES'				=> '<strong>Tipprunden-Funktionalitäten geändert</strong>',
	'LOG_FOOTBALL_MENU'					=> '<strong>Tipprunden-Menu geändert</strong>',
	'LOG_FOOTBALL_SETTINGS'				=> '<strong>Tipprunde-Einstellungen geändert</strong>',
	'LOG_PL_BACKUP'						=> '<strong>Tipprunden Backup</strong>',

	'LOG_FOOTBALL_MSG_TEST'				=> 'Aufruf am %s.',
	'LOG_FOOTBALL_MSG_TEST_TRAVEL'		=> 'Aufruf mit Zeitreise zum %s.',
	
	'LOG_FOOTBALL_REMEMBER_CRON'		=> '<strong>Cronjob Football remember ausgeführt</strong><br />» %s',
	'LOG_FOOTBALL_REMEMBER_CRON_TEST'	=> '<strong>Cronjob Football remember Testaufruf</strong><br />» %s',

	'FOOTBALL_REMEMBER_SUBJECT'			=> 'Bitte %1$s %2$d. Spieltag tippen',
	'FOOTBALL_REMEMBER_SUBJECT_BOARD'	=> 'Versendete Erinnerungs-Mails %1$s %2$d',
	'FOOTBALL_REMEMBER_ERROR_EMAIL'		=> '%1$s  Erinnerungs-Mail an: %2$s gescheitert',
	'FOOTBALL_REMEMBER_ERROR_EMAIL_BOARD'=> '%1$s Versandliste Erinnerungs-Mail an: %2$s gescheitert',
	'FOOTBALL_REMEMBER_NOBODY'			=> 'Es muss niemand erinnert werden.',
	'FOOTBALL_REMEMBER_NO_DELIVERY'		=> 'Keine anstehende Tippabgabe im Zeitraum.',
	'FOOTBALL_REMEMBER_SEND'			=> '%1$s  Erinnerungs-Mail an: %2$s',

));

?>