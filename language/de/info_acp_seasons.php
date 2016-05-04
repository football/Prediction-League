<?php
/**
*
* info_acp_football_seasons [Deutsch — Du]
*
* @package language
* @version $Id: info_acp_football_seasons.php 6 2011-01-09 08:02:37Z football $ 
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
	'ACP_FOOTBALL_SEASONS'					=> 'Saisons',
	'ACP_FOOTBALL_SEASONS_MANAGE'			=> 'Saisons verwalten',
	'ACP_FOOTBALL_SEASONS_MANAGE_EXPLAIN'	=> 'Hier kannst du alle Saisons verwalten. Du kannst bestehende löschen oder ändern oder eine neue Saison erstellen, sowie Name und Kurzbezeichnug der Saison einstellen.',
	'ACP_FOOTBALL_SEASONS_MANAGEMENT'		=> 'Tipprunde-Saison-Verwaltung',

	'CREATE_SEASON'							=> 'Neue Saison erstellen',

	'LEAGUES'								=> 'Ligen',

	'NO_SEASON'								=> 'Keine Saison angegeben.',
	'NO_SEASONS_CREATED'					=> 'Es wurden bislang keine Saisons erstellt.',

	'PREDICTION_LEAGUE'						=> 'Tipprunde',

	'SEASON'								=> 'Saison',
	'SEASON_CLOSED'							=> 'Geschlossen',
	'SEASON_CONFIRM_DELETE'					=> 'Bist du sicher, dass du die Saison %1$s mit allen Daten (Ligen, Spieltage, Spielplänen, Tipps und Ranglisten) löschen willst?',
	'SEASON_CREATE_FAILED'					=> 'Saison konnte nicht erstellt werden.',
	'SEASON_CREATED'						=> 'Saison erfolgreich erstellt.',
	'SEASON_CURRENT'						=> 'Aktuelle Saison',
	'SEASON_DEF'							=> 'Bestehende Saisons',
	'SEASON_DEF_EXPLAIN'					=> 'Dies sind Saisons, die von dir oder einem anderen Administrator erstellt wurden. Du kannst Saisoneinstellungen ändern oder Saisons löschen.',
	'SEASON_DELETE'							=> 'Saison entfernen',
	'SEASON_DELETED'						=> 'Saison gelöscht',
	'SEASON_DETAILS'						=> 'Saisondetails',
	'SEASON_EDIT_EXPLAIN'					=> 'Hier kannst du eine bestehende Saison bearbeiten. Du kannst ihren Namen und die Kurzbezeichnung ändern.',
	'SEASON_NAME'							=> 'Saisonname',
	'SEASON_NAME_EMPTY'						=> 'Der Saisonname fehlt. Er muss mindesten 4 Zeichen lang sein.',
	'SEASON_NAME_EXPLAIN'					=> 'Langbezeichnung der Saison z.B. "Saison 2010/2011"',
	'SEASON_NAME_TAKEN'						=> 'Der angegebene Saisonname wird bereits benutzt. Bitte wähle einen anderen aus.',
	'SEASON_NUMBER'							=> 'Die angegebene Saison muss numerisch (1963-2099) sein. Bitte die Jahreszahl, in der die Saison endet eingeben.',
	'SEASON_SHORT'							=> 'Saison Kurzbezeichnung',
	'SEASON_SHORT_EMPTY'					=> 'Die Saison Kurzbezeichnung fehlt, sie muss mindesten 2 Zeichen lang sein.',
	'SEASON_SHORT_EXPLAIN'					=> 'Kurzbezeichnung der Saison, die in der Auswahlbox angezeigt wird.',
	'SEASON_SHORT_TAKEN'					=> 'Die angegebene Kurzbezeichnung der Saison wird bereits benutzt. Bitte wähle eine andere aus.',
	'SEASON_TAKEN'							=> 'Diese Saison existiert bereits. Bitte wähle eine andere Saison aus.',
	'SEASON_UPDATE_FAILED'					=> 'Saisoneinstellungen konnten nicht aktualisiert werden.',
	'SEASON_UPDATED'						=> 'Saisoneinstellungen erfolgreich aktualisiert.',
	'SEASONS_NO_DELETE'						=> 'Du darfst keine Saison löschen. Dies dürfen nur Gründungsmitglieder',
	'SEASONS_NO_LEAGUE'						=> 'Diese Saison hat keine Liga',

	'TOO_LARGE_SEASON'						=> 'Der Wert für die Saison ist zu groß. Saison von 1963 - 2099 möglich.',
	'TOO_SHORT_SEASON'						=> 'Der Saisonname muss mindesten 4 Zeichen lang sein.',
	'TOO_SHORT_SEASON_SHORT'				=> 'Die Saison Kurzbezeichnung muss mindesten 2 Zeichen lang sein.',
	'TOO_SMALL_SEASON'						=> 'Der Wert für die Saison ist zu klein. Saison von 1963 - 2099 möglich.',
));

?>