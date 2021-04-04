<?php
/**
*
* info_acp_football_ko [Deutsch — Du]
*
* @package language
* @version $Id: info_acp_football_ko.php 6 2011-01-09 08:02:37Z football $ 
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
	'ACP_FOOTBALL_KO'					=> 'KO-Runden Gewinner',
	'ACP_FOOTBALL_KO_MANAGE'			=> 'KO-Runden Gewinner ermitteln',
	'ACP_FOOTBALL_KO_MANAGE_EXPLAIN'	=> 'Hier kannst du zu einer KO-Runde die Gewinner anhand der Begegnung(en) oder des Tabellenplatzes ermitteln und für kommende Spieltage freischalten. Bei der Spielplanerstellung werden nur diese Mannschaften für die Spieltage zur Auswahl angeboten. Ebenso ist es möglich Mannschaften anhand des Tabellenplatzes in eine andere KO-Liga zu überführen.',
	'ACP_FOOTBALL_KO_MANAGEMENT'		=> 'Tipprunde-KO-Runden abschliessen',

	'CHOOSE_LEAGUE'						=> 'Liga wählen',

	'ERROR_FROM_TO'						=> 'Fehler bei der Spieltagswahl. "Von" darf nicht nach "bis" liegen.',
	'ERROR_TARGET'						=> 'Fehler bei der Spieltagswahl. Der Ziel-Spieltag liegt nicht nach den Spieltagen zur Ermittlung.',

	'GROUP_RANK'						=> 'Qualifiziert bis Tabellenplatz',
	'GROUP_RANK_EXPLAIN'				=> 'Alle Mannschaften bis zu diesem Tabellenplatz qualifizieren sich.',

	'KO_GROUP'							=> 'Qualifikation anhand des Tabellenplatzes',
	'KO_MATCHDAYS'						=> 'Spieltage zur Ermittlung wählen',
	'KO_MOVED'							=> 'Folgende Teams wurden in eine andere Liga kopiert',
	'KO_NEXT'							=> 'Folgende Teams haben die nächste Runde erreicht',
	'KO_NEXT_CHECK'						=> 'Bitte die Teams prüfen, da bei Punktgleichheit der direkte Vergleich zählen kann, hier aber nicht berücksichtigt wird. ',

	'MANAGE_GROUP'						=> 'Ermittlung anhand des Tabellenplatzes bei Gruppenpielen',
	'MATCHDAY_NEW'						=> 'Die Mannschaften qualifizieren sich bis Spieltag',
	'MATCHDAY_TARGET'					=> 'Ziel-Spieltag wählen',
	'MOVE_LEAGUE'						=> 'in Liga',
	'MOVE_RANK'							=> 'Transfer des Tabellenplatzes',
	'MOVE_RANK_EXPLAIN'					=> 'Die Mannschaften auf diesem Tabellenplatz werden in die hinterlegte Liga kopiert und sind bis zum angegeben Spieltag qualifiziert.',

	'NO_KO_NEXT'						=> 'Es wurde kein Team in die nächste Runde gesetzt, da noch nicht alle Spiele gespielt sind!',

	'PREDICTION_LEAGUE'					=> 'Tipprunde',
));

?>