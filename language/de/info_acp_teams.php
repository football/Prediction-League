<?php
/**
* 
* info_acp_football_teams [Deutsch — Du]
*
* @package language
* @version $Id: info_acp_football_teams.php 14 2011-03-15 18:03:24Z football $ 
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
	'ACP_FOOTBALL_TEAMS'					=> 'Teams',
	'ACP_FOOTBALL_TEAMS_MANAGE'				=> 'Teams verwalten',
	'ACP_FOOTBALL_TEAMS_MANAGE_EXPLAIN'		=> 'Hier kannst du je Saison Teams einer Liga zuordnen. Du kannst bestehende löschen oder ändern oder eine neues Team erstellen, sowie Typ, Anzahl der Spieltage, Gewinne, Name und Kurzbezeichnug des Teams einstellen.',
	'ACP_FOOTBALL_TEAMS_MANAGEMENT'			=> 'Tipprunde-Team-Verwaltung',
	'ADD_TEAM'								=> 'Team zur Liga hinzufügen',

	'LEAGUE'								=> 'Liga',

	'MATCHDAY'								=> 'Spieltag',

	'NEW_TEAM'								=> 'Neues Team',
	'NO_LEAGUE'								=> 'Es existiert keine Liga in der Saison %1$s. Bitte zuerst eine Liga in der Saison %1$s erstellen.',
	'NO_MATCHDAYS'							=> 'Bitte zuerst Spieltage anlegen, da die Mannschaften bei KO-Runden einem Spieltag zugeordnet werden müssen.',
	'NO_SEASON'								=> 'Es existiert keine Saison. Bitte zuerst eine Saison erstellen.',
	'NO_TEAM'								=> 'Kein Team angegeben.',
	'NO_TEAMS_CREATED'						=> 'Es wurden bislang keine Teams erstellt.',
	'NO_TEAMSYMBOL'							=> 'Kein Logo',

	'PREDICTION_LEAGUE'						=> 'Tipprunde',

	'SEASON'								=> 'Saison',
	'SELECT_LEAGUE'							=> 'Liga auswählen',

	'TEAM'									=> 'Team',
	'TEAM_ADDED'							=> 'Team erfolgreich hinzugefügt.',
	'TEAM_AWAY'								=> 'Auswärts',
	'TEAM_CONFIRM_DELETE'					=> 'Bist du sicher, dass du %1$s aus Saison: %2$s Liga: %3$s mit allen Daten (Spieltage, Spielplänen und Tipps) löschen möchtest?',
	'TEAM_CREATE_FAILED'					=> 'Team konnte nicht erstellt werden.',
	'TEAM_CREATED'							=> 'Team erfolgreich erstellt.',
	'TEAM_CURRENT'							=> 'Aktuelles Team',
	'TEAM_DEF'								=> 'Bestehende Teams',
	'TEAM_DEF_EXPLAIN'						=> 'Dies sind Teams, die von dir oder einem anderen Administrator erstellt wurden. Du kannst Teameinstellungen ändern oder Teams löschen.',
	'TEAM_DELETE'							=> 'Team entfernen',
	'TEAM_DELETED'							=> 'Team gelöscht',
	'TEAM_DETAILS'							=> 'Teamdaten',
	'TEAM_EDIT_EXPLAIN'						=> 'Hier kannst du ein bestehendes Team bearbeiten. Du kannst den Namen, die Team-Kurzbezeichnung sowie das Team-Wappen ändern.',
	'TEAM_GROUP'							=> 'Gruppenspiele in Gruppe',
	'TEAM_GROUP_EXPLAIN'					=> 'Buchstabe der Gruppe, in der die Gruppenspiele bestritten werden.',
	'TEAM_MATCHES'							=> 'Spiele',
	'TEAM_NAME'								=> 'Teamname',
	'TEAM_NAME_DOUBLE'						=> 'Der Teamname wird schon benutzt bzw. wurde doppelt vergeben.',
	'TEAM_NUMBER'							=> 'Dies ist keine gültige Teamnummer. Die Teamnummer muss zwischen 0 und 65535 liegen.',
	'TEAM_ROUND'							=> 'Qualifiziert bis Spieltag',
	'TEAM_ROUND_EXPLAIN'					=> 'Spieltag bis zu dem sich das Team vorerst qualifiziert hat. Über diesen Parameter werden bei der Spielplanerstellung nur Mannschaften zur Auswahl angeboten, die sich auch für die netsprechende Runde qualifiziert haben. Über den Menüpunkt Qualifikation kann dieser Wert automatisch nach Beendigung der Runde automatisch für alle qualifizierten Mannschaften gesetzt werden.',	
	'TEAM_SHORT'							=> 'Team Kurzbezeichnung',
	'TEAM_SHORT_DOUBLE'						=> 'Die Kurzbezeichnung wird schon benutzt bzw. wurde doppelt vergeben.',
	'TEAM_SHORT_EXPLAIN'					=> 'Maximal 10 stellige Kurzbezeichnung des Teams. Diese wird z.B. beim Export und unter Alle Tipps verwendet.',
	'TEAM_SYMBOL'							=> 'Teamwappen',
	'TEAM_SYMBOL_EXPLAIN'					=> 'Das optionale Teamwappen muss 28x28 Pixel groß sein.',
	'TEAM_TAKEN'							=> 'Dieses Team existiert bereits in dieser Liga. Bitte wähle eine andere Teamnummer aus.',
	'TEAM_UPDATE_FAILED'					=> 'Teameinstellungen konnten nicht aktualisiert werden.',
	'TEAM_UPDATED'							=> 'Teameinstellungen erfolgreich aktualisiert.',
	'TEAMS_NO_DELETE'						=> 'Du darfst kein Team löschen. Dies dürfen nur Gründungsmitglieder',
	'TOO_LARGE_TEAM'						=> 'Die angegebene Team-ID muss numerisch (1-9999) sein. Bitte eine kleinere Teamnummer eingeben.',
	'TOO_SHORT_TEAM_NAME'					=> 'Der Teamname muss mindesten 3 Zeichen lang sein.',
	'TOO_SHORT_TEAM_SHORT'					=> 'Die Kurzbezeichnung muss mindesten 1 Zeichen lang sein.',
	'TOO_SMALL_TEAM'						=> 'Die angegebene Team-ID muss numerisch (1-9999) sein. Bitte eine größere Teamnummer eingeben.',

	'WRONG_DATA_TEAM_GROUP'					=> 'Keine gültige Gruppe für die Gruppenspiele. Gültige Gruppen sind A-Z.',
));

?>