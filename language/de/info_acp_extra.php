<?php
/**
*
* info_acp_football_extra [Deutsch — Du]
*
* @package language
* @version $Id: info_acp_football_extra.php 6 2011-01-09 08:02:37Z football $ 
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
	'ACP_FOOTBALL_EXTRA'					=> 'Sondertipps',
	'ACP_FOOTBALL_EXTRA_MANAGE'				=> 'Sondertipps verwalten',
	'ACP_FOOTBALL_EXTRA_MANAGE_EXPLAIN'		=> 'Hier kannst du Sondertipps zu Spieltagen einer Liga zuordnen. Du kannst bestehende löschen oder ändern oder einen neuen Sondertipp erstellen, sowie Typ, Wertung, Punkte und den Endzeitpunkt der Sondertipps einstellen.',
	'ACP_FOOTBALL_EXTRA_MANAGEMENT'			=> 'Tipprunde-Sondertipp-Verwaltung',

	'EVAL_BEFORE_DELIVERY'					=> 'Der Auswertungs-Spieltag darf nicht vor dem Abgabe-Spieltag liegen.',
	'EXTRA_ADD'								=> 'Sondertipp hinzufügen',
	'EXTRA_ADDED'							=> 'Sondertipp erfolgreich hinzugefügt.',
	'EXTRA_CONFIRM_DELETE'					=> 'Bist du sicher, dass du den Sondertipp "%1$s" in der %2$s. Liga aus der Saison %3$s mit allen Tipps löschen willst?',
	'EXTRA_CREATE_FAILED'					=> 'Sondertipp konnte nicht erstellt werden.',
	'EXTRA_DEF'								=> 'Bestehende Sondertipps',
	'EXTRA_DEF_EXPLAIN'						=> 'Dies sind Sondertipps, die von dir oder einem anderen Administrator erstellt wurden. Du kannst Sondertippeinstellungen ändern oder Sondertipps löschen.',
	'EXTRA_DELETED'							=> 'Sondertipp gelöscht',
	'EXTRA_DETAILS'							=> 'Sondertippdaten',
	'EXTRA_MATCHDAY'						=> 'Abgabe',
	'EXTRA_MATCHDAY_EVAL'					=> 'Auswertung',
	'EXTRA_MATCHDAY_EVAL_EXPLAIN'			=> 'Das Ergebnis des Sondertipps wird an diesem Spieltag eingegeben. Bei unterschiedlichen Spieltagen für Abgabe und Ergebnis werden die Punkte der Gesamtwertung zugerechnet, sonst fließen sie in die Spieltagswertung mit ein.',
	'EXTRA_MATCHDAY_EXPLAIN'				=> 'Die Tippabgabe des Sondertipps wird mit dem ersten Abgabetermin dieses Spieltags gesperrt.',
	'EXTRA_NO'								=> 'Nummer',
	'EXTRA_NO_DELETE'						=> 'Du darfst keine Sondertipps löschen. Dies dürfen nur Gründungsmitglieder',
	'EXTRA_POINTS'							=> 'Mögl. Punkte',
	'EXTRA_POINTS_EXPLAIN'					=> 'Maximal zu erreichende Punkte in Abhängigkeit zum Punktemodus.',
	'EXTRA_QUESTION'						=> 'Sondertipp Frage',
	'EXTRA_QUESTION_EXPLAIN'				=> 'Freier Fragetext. Bitte eine präzise, unmissverständliche Frage zum hinterlegten Fragetyp formulieren.',
	'EXTRA_QUESTION_TYPE'					=> 'Frage- bzw. Wertungs-Typ.',
	'EXTRA_QUESTION_TYPE_EXPLAIN'			=> '1 = Auswahl eines Teams, Punkte nur bei Übereinstimmung <br />2 = Auswahl eines Teams mehrere Ergebnisse, Punkte bei einem Treffer<br /> 3 = Texteingabe, Punkte bei Übereinstimmung<br />4 = Texteingabe aber mehrere Ergebnisse, Punkte bei einem Treffer<br />5 = Zahl, Punkte abzgl. der Differenz zwischen Tipp und Ergebnis',
	'EXTRA_STATUS'							=> 'Status',
	'EXTRA_STATUS_EXPLAIN'					=> '0 = offen (Tippabgabe möglich) <br /> 1 = keine Tippabgabe mehr möglich und noch keine Ergebnisse <br /> 2 = vorläufige Ergebnisse liegen vor <br /> 3 = abgeschlossen, endgültige Ergebnisse liegen vor.',
	'EXTRA_UPDATE_FAILED'					=> 'Die Sondertippeinstellungen konnten nicht aktualisiert werden.',
	'EXTRA_UPDATED'							=> 'Sondertippeinstellungen erfolgreich aktualisiert.',

	'LEAGUE'								=> 'Liga',

	'MATCHDAY'								=> 'Spieltag',

	'NO_EXTRA'								=> 'Der gewählte Sondertipp existiert nicht. Bitte zuerst einen Sondertipp in dieser Liga der Saison erstellen.',
	'NO_EXTRA_CREATED'						=> 'Es wurden bislang keine Sondertipps erstellt.',
	'NO_LEAGUE'								=> 'Die gewählte Liga existiert nicht in der Saison %1$s. Bitte zuerst eine Liga in der Saison %1$s erstellen.',
	'NO_SEASON'								=> 'Die gewählte Saison existiert nicht. Bitte zuerst eine Saison erstellen.',

	'QUESTION_TYPE'							=> 'Frage-Typ',

	'SEASON'								=> 'Saison',
	'SELECT_LEAGUE'							=> 'Liga auswählen',
	'SELECT_MATCHDAY'						=> 'Wähle einen Spieltag',

	'TOO_LARGE_EXTRA_POINTS'				=> 'Die Punktzahl für den Sondertipp ist zu groß. Erlaubt: 0-99',
	'TOO_LARGE_MATCHDAY'					=> 'Du musst einen Abgabe-Spieltag wählen.',
	'TOO_LARGE_MATCHDAY_EVAL'				=> 'Du musst einen Auswertungs-Spieltag wählen.',
	'TOO_SMALL_EXTRA_POINTS'				=> 'Die Punktzahl für den Sondertipp ist zu klein. Erlaubt: 0-99',
	'TOO_SMALL_MATCHDAY'					=> 'Du musst einen Abgabe-Spieltag wählen.',
	'TOO_SMALL_MATCHDAY_EVAL'				=> 'Du musst einen Auswertungs-Spieltag wählen.',
));

?>