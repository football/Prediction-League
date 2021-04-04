<?php
/**
* 
* info_acp_football_leagues [Deutsch — Du]
*
* @package language
* @version $Id: info_acp_football_leagues.php 12 2011-03-13 08:03:31Z football $ 
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
	'ACP_FOOTBALL_LEAGUES'					=> 'Ligen',
	'ACP_FOOTBALL_LEAGUES_MANAGE'			=> 'Ligen verwalten',
	'ACP_FOOTBALL_LEAGUES_MANAGE_EXPLAIN'	=> 'Hier kannst du alle Ligen verwalten. Du kannst bestehende löschen oder ändern oder eine neue Liga erstellen, sowie Typ, Anzahl der Spieltage, Gewinne, Name und Kurzbezeichnug der Liga einstellen. ',
	'ACP_FOOTBALL_LEAGUES_MANAGEMENT'		=> 'Tipprunde-Liga-Verwaltung',
	'ADD_GROUP'								=> 'Gruppe hinzufügen',
	'ADD_USERS'								=> 'Benutzer hinzufügen',
	'ADD_USERS_EXPLAIN'						=> 'Hier kannst du neue Tipper zur Liga-Tipprunde hinzufügen. Bitte gib je einen Benutzernamen pro Zeile ein. ',
	'ALL_USERS'								=> 'Alle Mitglieder',

	'BET_POINTS'							=> 'Einsatz',
	'BET_POINTS_EXPLAIN'					=> 'Einsatz zur Teilnahme am Saisonanfang',
	'BET_TYPE_KO'							=> 'Tipp bei KO-Spielen',
	'BET_TYPE_KO_EXPLAIN'					=> 'Auf welches Ergebnis soll bei KO-Spielen getippt werden?',

	'CHECK_HIT_WINS'						=> 'Gewinn Volltreffer-Wertung fehlerhaft. Bitte korrigieren. ',
	'CHECK_HITS02_WINS'						=> 'Gewinn Volltreffer-Wertung mit Auswärtspunkten fehlerhaft. Bitte korrigieren. ',
	'CHECK_MATCHDAY_WINS'					=> 'Gewinne je Spieltag fehlerhaft. Bitte korrigieren. ',
	'CHECK_RULES_POST_ID'					=> 'Bei Beitritt durch den User muss die Beitragsnummer mit den Regeln hinterlegt werden. ',
	'CHECK_SEASON_WINS'						=> 'Saisongewinne fehlerhaft. Bitte korrigieren. ',
	'CREATE_LEAGUE'							=> 'Neue Liga erstellen',

	'LEAGUE_BET_IN_TIME'					=> 'Tippabgabe bis zum jeweiligen Spielbeginn',
	'LEAGUE_BET_IN_TIME_EXPLAIN'			=> 'Dürfen die Tipper bis zum jeweiligen Spielbeginn tippen, oder soll die Tippabgabe der Spiele eines Spieltags nur bis zu einem (maximal drei) Termin(en) möglich sein?',
	'LEAGUE_CHAMP'							=> 'Meisterschaft',
	'LEAGUE_CONFIRM_DELETE'					=> 'Bist du sicher, dass du die %1$s aus der Saison %2$s mit allen Daten (Spieltage, Spielplänen, Tipps und Ranglisten) löschen willst?',
	'LEAGUE_CREATE_FAILED'					=> 'Liga konnte nicht erstellt werden. ',
	'LEAGUE_CREATED'						=> 'Liga erfolgreich erstellt. ',
	'LEAGUE_CURRENT'						=> 'Aktuelle Liga',
	'LEAGUE_DEF'							=> 'Bestehende Ligen',
	'LEAGUE_DEF_EXPLAIN'					=> 'Dies sind Ligen, die von dir oder einem anderen Administrator erstellt wurden. Du kannst Ligaeinstellungen ändern oder Ligen löschen. ',
	'LEAGUE_DELETE'							=> 'Liga entfernen',
	'LEAGUE_DELETED'						=> 'Liga gelöscht',
	'LEAGUE_DETAILS'						=> 'Ligadetails',
	'LEAGUE_EDIT_EXPLAIN'					=> 'Hier kannst du eine bestehende Liga bearbeiten. Du kannst Typ, Anzahl der Spieltage, Gewinne, Namen und die Kurzbezeichnung ändern. ',
	'LEAGUE_JOIN_BY_USER'					=> 'Beitritt in diese Tipprunde durch User',
	'LEAGUE_JOIN_BY_USER_EXPLAIN'			=> 'Sollen die Forum-User selbst dieser Tipprunde beitreten können, oder soll die Aufnahme in diese Tipprunde nur durch den Admin erfolgen?',
	'LEAGUE_JOIN_IN_SEASON'					=> 'Beitritt in diese Tipprunde während der Saison',
	'LEAGUE_JOIN_IN_SEASON_EXPLAIN'			=> 'Dürfen die Forum-User während der laufenden Saison dieser Tipprunde beitreten?',
	'LEAGUE_KO'								=> 'KO-Runde',
	'LEAGUE_MATCHDAYS'						=> 'Anzahl der Spieltage',
	'LEAGUE_MATCHDAYS_EMPTY'				=> 'Die Anzahl der Spieltage fehlt. ',
	'LEAGUE_MATCHDAYS_EXPLAIN'				=> 'Bei der Berechnung der Anzahl der Spieltage ist zu berücksichtigen, dass Spiele unterschiedlicher Runden zu einem Spieltag zusammengefasst werden können, damit nicht auf einzelne oder wenige Spiele getippt werden muss. ',
	'LEAGUE_MATCHES'						=> 'Anzahl der Spiele je Spieltag',
	'LEAGUE_MATCHES_EXPLAIN'				=> 'Bei KO-Runden mit unterschiedlicher Anzahl von Spielen an den Spieltagen, ist hier 0 einzutragen und die Anzahl der Spiele beim Spieltag zu erfassen. ',
	'LEAGUE_MEMBERS'						=> 'Tipprunden-Teilnehmer',
	'LEAGUE_NAME'							=> 'Liganame',
	'LEAGUE_NAME_EMPTY'						=> 'Der Liganame muss mindesten 3 Buchstaben lang sein. ',
	'LEAGUE_NO_MEMBER'						=> 'Die Liga hat zur Zeit keine Tipper',
	'LEAGUE_NUMBER'							=> 'Die angegebene Liga muss numerisch sein. Bitte eine Liganummer von 1-99 eingeben. ',
	'LEAGUE_POINTS'							=> 'Punkte Modus dieser Liga-Tipprunde',
	'LEAGUE_POINTS_DIFF'					=> 'Punkte bei richtiger Tordifferenz',
	'LEAGUE_POINTS_DIFF_EXPLAIN'			=> 'Punkte für richtige Tordifferenz. ',
	'LEAGUE_POINTS_HIT'						=> 'Punkte für einen Volltreffer',
	'LEAGUE_POINTS_HIT_EXPLAIN'				=> 'Punkte, wenn der Tipp mit dem Ergebnis übereinstimmt. ',
	'LEAGUE_POINTS_LAST'					=> 'Punkte des letzten für Nichttipper',
	'LEAGUE_POINTS_LAST_EXPLAIN'			=> 'Sollen die Nichttipper automatisch die Punkte des schlechtesten Tippers erhalten? Dies können eventuell auch 0 Punkte sein. ',
	'LEAGUE_POINTS_MODE'					=> 'Punkte Modus',
	'LEAGUE_POINTS_MODE_EXPLAIN'			=> '1 = Volltrefferpunkte und bei richtiger Tendenz je abweichendem Tor ein Punkt weniger aber mind. Tendenzpunkte. Bei Unentschieden nur einfacher Abzug bei abweichendem Tor. <br /> 2 = wie 1 jedoch bei Unentschieden voller Abzug je Torabweichung. <br /> 3 = Volltreffer- oder Tendenzpunkte. <br /> 4 = Volltreffer-, Differenz- oder Tendenzpunkte. <br /> 5 = wie 4 aber bei Remis keine Diffenzpunkte. <br /> 6 = Volltreffer- oder Tendenzpunkte; Differenzpunkte bei Unentschieden-Tendenz. ',
	'LEAGUE_POINTS_TENDENCY'				=> 'Punkte bei richtiger Tendenz',
	'LEAGUE_POINTS_TENDENCY_EXPLAIN'		=> 'Punkte bzw. Mindestpunkte bei richtiger Tendenz. ',
	'LEAGUE_RULES_POST_ID'					=> 'Beitragsnummer mit den Regeln',
	'LEAGUE_RULES_POST_ID_EXPLAIN'			=> 'Post-ID mit den Regeln zu dieser Liga-Tipprunde. Die Post-Id wird beim Button Beitrag melden angezeigt. ',
	'LEAGUE_SHORT'							=> 'Liga Kurzbezeichnung',
	'LEAGUE_SHORT_EMPTY'					=> 'Die Kurzbezeichnung fehlt. ',
	'LEAGUE_SHORT_EXPLAIN'					=> 'Dreistelliges Kürzel z.B. 1BL, 2BL, DFB, CL oder EL zur Identifizierung der Liga in der Download-Datei. ',
	'LEAGUE_TAKEN'							=> 'Diese Liga existiert bereits in dieser Saison. Bitte wähle eine andere Liganummer aus. ',
	'LEAGUE_TYPE'							=> 'Ligatyp',
	'LEAGUE_TYPE_EXPLAIN'					=> 'Bei Spielrunden mit Spielen, die über eine Verlängerung entschieden werden können ist hier KO-Runde zu wählen, auch wenn einzelne Spiele dieser Runde nach 90 Minuten enden. ',
	'LEAGUE_UPDATE_FAILED'					=> 'Ligaeinstellungen konnten nicht aktualisiert werden. ',
	'LEAGUE_UPDATED'						=> 'Ligaeinstellungen erfolgreich aktualisiert. ',
	'LEAGUE_USERS_ADD'						=> 'Tipper zur Liga hinzugefügt. ',
	'LEAGUE_USERS_REMOVE'					=> 'Tipper aus der Liga entfernt. ',
	'LEAGUE_WIN_EXPLAIN'					=> 'Bei den folgenden Gewinnfeldern sind die Gewinne je Platz durch ; getrennt einzugeben. Also 10.50;5 für Platz 1 = 10,50 Euro und Platz 2 = 5 Euro',
	'LEAGUE_WIN_HITS'						=> 'Gewinn Volltreffer-Wertung',
	'LEAGUE_WIN_HITS_AWAY'					=> 'Gewinn Volltreffer-Wertung mit Auswärtspunkten',
	'LEAGUE_WIN_HITS_AWAY_EXPLAIN'			=> 'Gewinn in Euro für den Erstplatzierten der Volltreffer-Wertung bei denen die Auswärtsmannschaft gepunktet hat. ',
	'LEAGUE_WIN_HITS_EXPLAIN'				=> 'Gewinn in Euro für den Erstplatzierten der Volltreffer-Wertung. ',
	'LEAGUE_WIN_MATCHDAYS'					=> 'Gewinne je Spieltag',
	'LEAGUE_WIN_MATCHDAYS_EXPLAIN'			=> 'Platzierungsgewinne je Spieltag. ',
	'LEAGUE_WIN_SEASON'						=> 'Gewinne Saison',
	'LEAGUE_WIN_SEASON_EXPLAIN'				=> 'Einmalige Platzierungsgewinne am Ende der Saison. ',
	'LEAGUE_WINS'							=> 'Liga Gewinne',
	'LEAGUES_NO_DELETE'						=> 'Du darfst keine Liga löschen. Dies dürfen nur Gründungsmitglieder',
	'LEAGUES_NO_TEAM'						=> 'Diese Liga hat keine Teams',

	'MEMBER'								=> 'Tipper',
	'MEMBER_ALL'							=> 'Alle aktive Boardmitglieder aufnehmen',
	'MEMBER_CONFIRM_DELETE'					=> 'Bist du sicher, dass du die gewählten Tipper aus der Tipprunde %1$s der Saison %2$s mit allen Daten (Tipps, Ranglistenplätze und Kontoauszug) löschen willst?',
	'MEMBER_DELETE'							=> 'Tipper aus Liga entfernen',
	'MEMBER_EXISTS'							=> 'Dieser Tipper ist bereits Teilnehmer dieser Liga-Tipprunde. ',
	'MEMBER_EXPLAIN'						=> 'Dies ist eine Liste aller Tipper dieser Liga. Von hier aus kannst du einstellen, wer Tipper dieser Liga sein soll. ',

	'NO_LEAGUE'								=> 'Keine Liga angegeben. ',
	'NO_LEAGUES_CREATED'					=> 'Es wurden bislang keine Ligen erstellt. ',
	'NO_MATCHDAYS_KO'						=> 'Bei einer KO-Runde müssen erst die Spieltage erfasst werden bevor User der Tipprunde beitreten können. ',
	'NO_MEMBERS_SELECTED'					=> 'Es wurde kein Mitglied ausgewählt, bzw. das gewählte Mitglied ist nicht bekannt. ',
	'NO_SEASON'								=> 'Es existiert keine Saison. Bitte zuerst eine Saison erstellen. ',

	'SELECT_SEASON'							=> 'Saison auswählen',

	'TOO_LARGE_LEAGUE'						=> 'Der Wert für die Liga ist zu groß. Es sind nur Ligen zwischen 1 und 99 möglich. ',
	'TOO_LARGE_LEAGUE_MATCHDAYS'			=> 'Die Anzahl der Spieltage ist zu groß. Sie muss zwischen 1 und 99 liegen. ',
	'TOO_LARGE_LEAGUE_MATCHES'				=> 'Die Anzahl der Spiele je Spieltag ist zu groß. Sie muss zwischen 0 und 99 liegen. ',
	'TOO_SHORT_LEAGUE_SHORT'				=> 'Die Kurzbezeichnung der Liga muss mindesten 1 Zeichen lang sein. ',
	'TOO_SHORT_SEASON'						=> 'Der Liganame muss mindesten 2 Zeichen lang sein. ',
	'TOO_SMALL_LEAGUE'						=> 'Der Wert für die Liga ist zu klein. Es sind nur Ligen zwischen 1 und 99 möglich. ',
	'TOO_SMALL_LEAGUE_MATCHDAYS'			=> 'Die Anzahl der Spieltage ist zu klein. Sie muss zwischen 1 und 99 liegen. ',
	'TOO_SMALL_LEAGUE_MATCHES'				=> 'Die Anzahl der Spiele je Spieltag ist zu klein. Sie muss zwischen 0 und 99 liegen. ',
));

?>