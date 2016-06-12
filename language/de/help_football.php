<?php
/**
*
* @package language
* @package phpBB Extension - Football Football
* @copyright (c) 2016 football (http://football.bplaced.net)
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

/**
* DO NOT CHANGE
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

if ( empty($lang) || !is_array($lang) )
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
//
// Some characters you may want to copy&paste:
// ’ » „ “ — …
//

$help = array(
	array(
		0 => '--',
		1 => 'Erste Schritte'
	),
	array(
		0 => 'Wie installiere ich eine Liga?',
		1 => 'Bei der Installation werden keine Spielpläne mitgeliefert. Spielpläne können über "Spielplan aktualisieren" in die Datenbank geladen werden.
Näheres dazu siehe unter diesem Stichwort.
Nach dem Einspielen eines Spielplans sollten die Einstellungen der Liga bearbeitet werden und die Abgabetermine der Spieltage überprüft und ggf. angepasst
werden. Unter Liga verwalten können anschließend einzelne Tipper oder Mitglieder einer bestehenden Gruppe in die Tipprunde aufgenommen werden. '
	),
	array(
		0 => 'Weitere Einstellungen?',
		1 => 'Für den laufenden Betrieb der Tipprunde(n) sollten die administrativen Berechtigungen überprüft bzw. entsprechenden Mitgliedern zugeordnet
werden. Zum automatischen Versand von Erinnerungs-Mails kann man einen externen Cornjob einrichten. Näheres dazu in den einzelnen Punkten dieser Hilfe. '
	),
	array(
		0 => '--',
		1 => 'Allgemeines'
	),
	array(
		0 => 'Welche Tipprunden werden unterstützt?',
		1 => 'Alle Tipprunden, die auf eine Meisterschafts- oder KO-Runde beruhen. Also Fußball-Ligen, Pokal-Wettbewerbe, Europa League, Champions League, 
EM oder WM. Auch ein Mix aus unterschiedlichen Wettbewerben wird unterstützt, dabei zeigen jedoch die Tabelle und die Statistiken nichts sinnvolles an. 
Lediglich durch den Punkte-Modus ist die Extension für Handball und andere Ligen nicht geeignet.'
	),
	array(
		0 => 'Welche Rechte existieren in diesem Extension',
		1 => 'a_football_results = Kann endgültige Spielergebnisse eingeben, ändern und löschen<br />
a_football_editbets = Kann im Adminbereich alle Tipps ansehen, eingeben und ändern<br />
a_football_plan = Kann Spielpläne erstellen und ändern. Spielplan verwalten (ohne löschen) und KO-Runden Gewinner ermitteln<br />
a_football_delete = Kann Spielpläne und Teile davon löschen. Löschberechtigung in der Spielplan-Verwaltung<br />
a_football_config = Kann Tipprundeneinstellung ändern: Tipprunden-Konfiguration<br />
a_football_points = Kann Punkte für Ultimate Points eingeben, ändern und löschen<br />
u_use_football = Kann Football Extension verwenden.'
	),
	array(
		0 => 'Welche Rollen existieren in diesem Extension?',
		1 => 'Der Football-Administrator besitzt nur das Recht endgültige Spielergebnisse einzugeben, sonst keinerlei Adminrechte. '
	),
	array(
		0 => 'Welche Rechte wurden bestehenden Rollen zugewiesen?',
		1 => 'Der Standard-Administrator  Kann endgültige Spielergebnisse eingeben, ändern und löschen. 
Der Umfassende Administrator hat sämtliche Rechte des MODS.'
	),
	array(
		0 => 'Welche Benutzerdefinierten Profilfelder wurden vom Extension erzeugt?',
		1 => 'footb_email = zusätzliche 2. E-Mail-Adresse für Erinnerungsmails <br />
footb_rem_f = Schalter für Versand der Erinnerungsmail an die Haupt-E-Mail-Adresse.<br />
footb_rem_s = Schalter für Versand der Erinnerungsmail an die 2. E-Mail-Adresse.'
	),
	array(
		0 => 'Wie aktiviere ich die Erinnerungs-Mail Funktion?',
		1 => 'Im Adminbereich muss unter den Tipprunden-Funktionalitäten der Cronjob für die Tipp-Erinnerungsmail aktiviert und der Termin der nächsten Ausführung gesetzt werden.<br />
Der Cron-Job wird beim ersten Zugriff nach diesem Termin ausgeführt und sendet eine Erinnerungs-Mail für die fehlenden Tippabgaben, die im Zeitraum 24-48 Stunden nach dem Cron-Termin liegen. 
Falls nicht ausreichend Zugriffe auf die Seite erfolgen, sollte der Cronjob automatisch z.B. von www.cronjob.de durch das Script 
"DEINE_URL/cron.php?cron_type=football.football.cron.task.football_remember" gestartet werden.<br />
Das versenden der Email erfolgt nur einmalig. Nach Aufruf des Cronjobs wird der nächste automatisch für den Folgetag eingeplant.
Mit den angehängten parametern "&mode=test&days=n" kann der Versand für die um n Tage versetzte Tippabgabe getestet werden. Dabei werden nur Mails an Board-Email-Adresse versendet.'
	),
	array(
		0 => 'Wer bekommt eine Erinnerungsmail',
		1 => 'Bei aktivierter Erinnerungsmail-Funktion, bekommen alle Tipper, die am abzugebenden Spieltag ein Spiel mit Status 0 nicht vollständig getippt 
haben eine Erinnerungsmail an die dazu freigegebene(n) E-Mail-Adresse(n). <br />
Zusätzlich wird eine E-Mail mit der Empfängerliste an die Board-E-Mail-Adresse versendet (aber nur wenn auch mindestens eine Erinnerungsmail versendet 
wurde). <br />
Der Vorgang wird zur Dokumentation im Log protokolliert.'	
	),
	array(
		0 => '--',
		1 => 'Tippabgabefrist'
	),
	array(
		0 => 'Wann wird die Tippabgabe gesperrt?',
		1 => 'Sobald jemand Daten aus den Tipprunden abruft wird überprüft, ob Spieltage mit Status 0 und abgelaufener Abgabezeit existieren. Die zu 
schließenden Spiele werden mit Status 1 versehen und sind somit nicht mehr tippbar.'
	),
	array(
		0 => 'Die Spieltage bzw. Spiele werden nicht zum Abgabezeitpunkt geschlossen',
		1 => 'Der Abgabezeitpunkt wird mit der aktuellen Host Zeit + Zeitverschiebung zwischen Board- und Host-Zeitzone verglichen. Wenn dein Hoster sich 
in der eingestellten Board-Zeitzone befindet, muss diese Zeitzone auch in den Tipprunden-Einstellungen hinterlegt sein. Wenn nicht ist hier die 
entsprechende Zeitzone zu wählen.'
	),
	array(
		0 => 'Ich kann den Abgabezeitpunkt eines Spieltags nicht bearbeiten',
		1 => 'Du hast bei den Liga-Einstellungen "Tippabgabe bis zum jeweiligen Spielbeginn" gewählt. Dadurch wird die Abgabezeit automatisch anhand der 
Spiele verwaltet. Wenn du noch bei der Spielplanerstellung bist, solltest "Tippabgabe bis zum jeweiligen Spielbeginn" erst nach Erfassung des gesamten 
Spielplans einstellen. Dann werden die Abgabezeiten automatisch korrigiert.' 
	),
	array(
		0 => 'Das Spiel hat schon begonnen, aber ich kann noch tippen',
		1 => 'Der Spieltag wurde noch nicht geschlossen, da der Abgabezeitpunkt noch in der Zukunft liegt; das erste Spiel hat aber bereits begonnen. Beim 
Speichern der Abgabezeit im Spieltag wird momentan die Zeit nicht mit den Spielzeiten abgeglichen und überprüft.'
	),
	array(
		0 => 'Überprüfungen beim Speichern der Spieltags-Einstellungen',
		1 => 'Beim Abspeichern eines Spieltags wird überprüft, ob der Status des Spieltags korrigiert werden muss. Liegt der 1. Abgabetermin in der 
Zukunft wird der Status des Spieltags auf 0 gesetzt, liegt er in der Vergangenheit, wird der Status je nach Status der dazugehörigen Spiele auf 1, 2 
oder 3 gesetzt.'
	),
	array(
		0 => 'Wie stelle ich weitere Abgabetermine bei einem Spieltag ein?',
		1 => 'Dies geht nur, wenn "Tippabgabe bis zum jeweiligen Spielbeginn" bei den Liga-Einstellungen auf Nein steht. Dann können zwei weitere 
Abgabetermine beim Spieltag hinterlegt werden. Die Spiele, die zum 2. Abgabetermin geschlossen werden sollen, müssen im Spielplan manuell auf Status -1 
gesetzt werden. Für den 3. Abgabetermin ist der Status der Spiele manuell auf -2 zu setzen.'
	),
	array(
		0 => 'Was bedeutet "Tippabgabe bis zum jeweiligen Spielbeginn"',
		1 => 'In diesem Modus kann jedes Spiel einzeln bis zum Spielbeginn getippt werden. Beim Schließen der Tippabgabe eines Spiels wird ermittelt, ob 
noch "offene" Spiele im Spieltag existieren. Wenn ja, wird die Abgabezeit des Spieltags auf den nächsten Spielbeginn gesetzt, sonst bleibt sie auf dem 
letzten Spielbeginn stehen und der Spieltag bekommt den Staus 1. Die Tippabgabe zu einer festen Zeit vor jedem Spiel wird derzeit nicht unterstützt.'
	),
	array(
		0 => '--',
		1 => 'Spielbetrieb'
	),
	array(
		0 => 'Wie speichere ich endgültige Spielergebnisse?',
		1 => 'Endgültige Spielergebnisse können nur im Adminbereich eingegeben bzw. bestätigt werden. Dabei werden nur die Ergebnisse der ausgewählten 
Spiele abgespeichert.'
	),
	array(
		0 => 'Warum kann ich keine Spielergebnisse eingeben?',
		1 => 'Wenn bei den Tipprunden Funktionalitäten "Eingabe endgültiger Spielergebnisse erst nach Spielende" auf Ja gesetzt wurde, können die 
Spielergebnisse erst nach Spielende (90 Minuten) eingegeben werden. <br /> 
Für Spiele mit Status 0 können keine Ergebnisse eingegeben werden. Hier ist zuerst der Abgabetermin des Spieltags oder der Spielbeginn des Spiels
zu kontrollieren und gegebenenfalls anzupassen, damit die Tippabgabe zu diesem Spiel automatisch gesperrt wird und das Spiel den Status 1 zur 
Ergebniseingabe erhält.'
	),
	array(
		0 => 'Ich möchte einen Spielzwischenstand speichern',
		1 => 'Spielzwischenstände können nur im Userbereich eingegeben werden. Im Adminbereich werden alle Ergebnisse als endgültige oder nicht 
gewertete Ergebnisse nach 90 Minuten abgespeichert. '
	),
	array(
		0 => 'Warum bekomme ich beim Aufruf manchmal eine andere Liga angezeigt?',
		1 => 'Beim Aufruf der Seite "Spielergebnisse eingeben" wird automatisch die Liga angezeigt, die am längsten auf eine Ergebniseingabe wartet 
bzw. deren Spiele als nächstes enden. Warten Spiele aus unterschiedlichen Ligen auf eine Ergebniseingabe bzw. -bestätigung, so gelangt man nach 
Ergebniseingabe durch erneutes Aufrufen der Seite zu den nächsten "wartenden" Spielen.'
	),
	array(
		0 => 'Wie wird der Spieltagsstatus beim Speichern der Ergebnisse gesetzt?',
		1 => 'Nach abspeichern der Ergebnisse wird der Status des Spieltags entsprechend der Spiele gesetzt. Haben alle Spiele einen Status > 2, wird der 
Spieltagsstatus auf 3 gesetzt und der Spieltag somit abgeschlossen. Sonst wird der Spieltagsstatus auf den minimalen Status >= 0 der dazugehörigen 
Spiele gesetzt.'
	),
	array(
		0 => 'Wann werden die Ranglisten aktualisiert?',
		1 => 'Nach abspeichern der Ergebnisse und setzen des Spieltagsstatus im User- oder Adminbereich wird die Spieltagsrangliste des entsprechenden 
Spieltags abgespeichert. Anschließend wird die Platzierung in der Gesamtrangliste berechnet und ebenfalls im aktuelle Spieltag abgespeichert. 
Die restlichen Daten der Gesamtrangliste werden nicht abgespeichert, sondern immer aktuell berechnet. <br />
Bei Spieltagen mit Status 0 oder 1 wird die Rangliste zu diesem Spieltag gelöscht. Sonst wird der Status der Rangliste analog zum Status des Spieltages 
gesetzt.'
	),
	array(
		0 => 'Wie lösche ich Spielergebnisse?',
		1 => 'Einfach ein Ergebnisfeld löschen oder die Löschen Option wählen und schon werden alle Ergebnisfelder gelöscht. Der Status des Spiels wird 
dabei auf 1 zurückgesetzt.'
	),
	array(
		0 => 'Wie nehme ich einzelne Spiele aus der Wertung?',
		1 => 'Durch setzten des Optionsschalters "O. Wertung" vor dem Speichern, wird das Spiel aus der Wertung genommen, so dass niemand Punkte dafür 
erhält. Liegt noch kein Ergebnis vor, wird das Spiel mit Status 4 gespeichert, sonst mit Status 6. Wird im Userbereich ein Ergebnis zu einem Spiel mit 
Status 4 eingegeben, wird der Status 5 gesetzt. '
	),
	array(
		0 => 'Wer kann alle Tipps sehen?',
		1 => 'Benutzer mit Adminrecht a_football_editbets = "Kann im Adminbereich alle Tipps ansehen, eingeben und ändern" können im Adminbereich alle 
Tipps sehen und auch editieren.'
	),
	array(
		0 => 'Was ist bei der Tippeingabe im Adminbereich zu beachten?',
		1 => 'Werden Tipps zu Spielen mit bereits vorliegenden Ergebnissen abgespeichert, wird die Rangliste nicht automatisch aktualisiert. Die 
Ranglisten berücksichtigen den neuen Tipp nicht. In der Anzeige der Einzeltipps und "Alle Tipps" werden die geänderten Tipps und die daraus 
resultierenden Punkte angezeigt. Wenn die geänderten Tipps in die Rangliste einfließen sollen, sind die Ergebnisse des Spieltags und der nachfolgenden 
Spieltage erneut abzuspeichern, so dass die Ranglisten neu berechnet werden. Spiele müssen dabei nicht ausgewählt oder verändert werden. '
	),
	array(
		0 => 'Wozu dient "KO-Runden Gewinner ermitteln"?',
		1 => 'Damit in der Spielplanverwaltung nur Teams zur Auswahl stehen, die auch die entsprechende Runde erreicht haben, muss der Spieltag beim Team 
eingetragen werden, bis zu dem das Team im Wettbewerb verbleibt. Dies, und die Überleitung der 3. Platzierten Teams der Champions League Gruppenphase in 
die Europa League, kann auf dieser Seite automatisch durchgeführt werden. Da insbesondere bei der Berechnung der Gruppen-Platzierung nicht alle Regeln im
System berücksichtigt wurden, sind eventuelle Fehler manuell zu korrigieren. Dazu werden die betroffenen Mannschaften in der Statusmeldung protokolliert.' 
	),
	array(
		0 => 'Wie ermittle ich die Gewinner einer KO-Runde?',
		1 => 'Bei einzelnen KO-Spielen oder KO-Runden mit Hin- und Rückspiel reicht es die Spieltage zur Ermittlung auszuwählen, an denen diese Spiele 
stattgefunden haben. Als Zielspieltag ist der letzte Spieltag zu wählen bis zu dem das Team mindestens im Wettbewerb bleibt.'
	),
	array(
		0 => 'Wie ermittle ich die Gruppengewinner und -zweiten einer Gruppe',
		1 => 'Bei Gruppenspielen sind alle Gruppenspieltage zur Ermittlung auszuwählen und der letzte Spieltag der nächsten Runde als Zielspieltag zu 
hinterlegen. Die Option "Ermittlung anhand des Tabellenplatzes bei Gruppenspielen" ist zu wählen und der Platz bis zu dem die Teams sich qualifizieren. 
Sollen Teams in eine andere Liga überführt werden, ist die entsprechende Liga auszuwählen, sonst nicht. Wurde keine Liga gewählt, werden Platz und 
Spieltag ignoriert und kein Team transferiert.' 
	),
	array(
		0 => 'Die Teams sind schon in der nächsten Runde eingetragen',
		1 => 'Wurden im Spielplan die Formeln W (Winner) oder L (Looser) hinterlegt, wird schon bei der Anzeige der Begegnung der Sieger bzw. Verlierer 
der in der Formel hinterlegten Partie(n) ermittelt und im Spielplan eingetragen. Trotzdem sollte "KO-Runden Gewinner ermitteln" aufgerufen werden, da 
sonst beim Speichern der Spielbegegnung im Spielplan (bei Spielverlegung oder sonstigen Änderungen), die Teams nicht zur Auswahl stehen und somit wieder 
gelöscht werden. <br /> 
Die Formel G führt nicht zu einer automatischen Ermittlung, da derzeit bei der Ermittlung der Gruppenplatzierungen nicht alle Sonderregeln bis ins 
kleinste berücksichtigt werden. Die Ermittlung muss im Adminbereich über "KO-Runden Gewinner ermitteln" durchgeführt und kontrolliert werden. '
	),
	array(
		0 => 'Tipper ohne Tipps bekommen trotzdem Punkte',
		1 => 'Du hast in den Liga-Einstellungen hinterlegt, dass Nichttipper automatisch die Punkte des letzten bekommen sollen.'
	),
	array(
		0 => '--',
		1 => 'Spielplan Verwaltung'
	),
	array(
		0 => 'Wie erstelle ich einen neuen Spielplan?',
		1 => 'Zur Spielplanerstellung sind die einzelnen Seiten zwingend von oben nach unten abzuarbeiten. Insbesondere ist die korrekte Hinterlegung 
der Anzahl der Spieltage und Spiele zu beachten, damit die Generierung im Verlauf der Spielplanerstellung funktioniert. Erst nach Generierung der 
Spiele im Spielplan können Tipper aufgenommen werden, da vorher Informationen zu der Anzahl der Spiele fehlen. Ebenso wird der Schalter "Tippabgabe 
bis zum jeweiligen Spielbeginn" erst nach Generierung der Spiele im Spielplan sichtbar. Dieser Schalter sollte erst nach Hinterlegung der 
Spielpaarungen und aller Spieltermine auf Ja gesetzt werden, damit alle Abgabetermine in den Spieltagen automatisch gesetzt werden können.'  
	),
	array(
		0 => 'Wie richte ich eine neue Saison ein?',
		1 => 'Unter "Saisons verwalten" erstellst du eine neue Saison mit der Jahreszahl, in der die Saison endet. Die Spielzeit 2010/2011 ist also als 
Saison 2011 zu erstellen. Auf der folgenden Seite werden dann entsprechende Standardwerte gesetzt. '
	),
	array(
		0 => 'Wie richte ich eine neue Liga ein?',
		1 => 'Unter "Ligen verwalten" erstellst du eine neue Liga mit Angabe einer Liganummer. Die Liganummer sollte für eine Liga über die Saisons 
identisch vergeben werden und muss nicht fortlaufen innerhalb der	Saison sein. Die 1. Liga sollte also immer die Liganummer 1, die 2. Liga immer 
die Liganummer 2 bekommen.'
	),
	array(
		0 => 'Wie richte ich die Spieltage ein?',
		1 => 'Bei einer neuen Liga müssen zuerst die fehlenden Spieltage über den Button generiert werden. Wenn der Spieltag eine abweichende 
Bezeichnung vom Standard "#. Spieltag" bekommen soll, so ist das entsprechende Feld zu füllen, sonst nicht. Wird ein Abgabetermin hinterlegt, wird 
dieser Abgabetemin bei der Generierung der Spielpaarungen als Spielbeginn eingesetzt.' 
	),
	array(
		0 => 'Wie nehme ich Teams in einer Liga auf?',
		1 => 'Bei KO-Runden müssen vorher zwingend die Spieltage erfasst werden, damit dem Team ein Spieltag zugeordnet werden kann, bis zu dem das 
Team im Wettbewerb verbleibt. Die Team-ID einer Mannschaft sollte über alle Saisons und Ligen hinweg identisch gewählt werden, damit historische 
Vergleiche in den Statistiken funktionieren. Alle Gif-Dateien im Verzeichnis /images/flags werden zur Auswahl eines Team Wappens bzw. 
einer Flagge angezeigt. Die Wappen sollten das Format 28px x 28px haben. Es wird ausdrücklich darauf hingewiesen, das Vereinswappen dem Urheberrecht 
unterliegen und nicht ohne Zustimmung des Rechteinhabers verwendet werden dürfen.'
	),
	array(
		0 => 'Wie erstelle ich die Spielpaarungen im Spielplan?',
		1 => 'Nachdem alle Spieltage und Mannschaften erfasst wurden, können die Datenbankeinträge für die Spielpaarungen generiert werden. Wurde 
vorher bei den Spieltagen ein Abgabetermin hinterlegt, wird dieser bei allen Spielen des Spieltags als Spielbeginn hinterlegt. Wurden noch keine Teams 
in einer Spielpaarung hinterlegt, so wird in der Übersicht statt des Teamnamens eine eventuell hinterlegte Berechnungsformel angezeigt. 
		'
	),
	array(
		0 => 'Kann ich einen Spielplan sukzessiv aufbauen?',
		1 => 'Ja, man kann im Verlauf der Saison weitere Spieltage in der Liga hinzufügen. Dazu trägt man unter "Ligen verwalten" die geänderte Anzahl 
der Spieltage ein und geht anschließend auf "Spieltage verwalten". Dort kann man die neuen Spieltagseinträge generieren lassen und anschließend 
editieren. Nach Eingabe der Abgabetermine und Anzahl der Spiele bei KO-Runden, müssen die Spiele im Spielplan noch generiert werden. Dazu wählt man den 
neue hinzugefügten Spieltag aus und klickt auf den Generierungs-Button. Die fehlenden Spiele werden generiert und ebenso fehlende Leereinträge aller 
Tipper in der Tipptabelle.'
	),
	array(
		0 => 'Wann kann ich in einer Liga Tipper aufnehmen?',
		1 => 'Tipper können erst nach Erstellung der Spiele im Spielplan hinzugefügt werden. Erst dann ist die Funktion in "Ligen verwalten" freigegeben.'
	),
	array(
		0 => 'Kann ich Spieltage auch wieder löschen?',
		1 => 'Ja, entweder ausgewählte Spieltage unter "Spieltage verwalten"  über die Schaltfläche "Löschen" oder die letzten Spieltage, indem man die 
Anzahl der Spieltage in der Liga reduziert und anschließend auf "Spieltage verwalten" geht und dort den dann angezeigten Button betätigt. Gelöscht wird
dabei immer der Spieltag mit allen Spielen und allen Tipps.'
	),
	array(
		0 => 'Ich kann keine Spiele bzw. Begenungen erfassen?',
		1 => 'Wenn du eine KO-Runde angelegt hast, musst du die Anzahl der Spiele bei den einzelnen Spieltagen hinterlegen. Erst dann erscheint der 
Button zum Vervollständigen des Spielplans unter Spielplan verwalten.'
	),
	array(
		0 => 'Was passiert bei einer Spielverlegung?',
		1 => 'Nach Änderung eines Spieltermins wird bei "Tippabgabe bis zum jeweiligen Spielbeginn" ggf. der Status des Spiels und des Spieltages sowie 
der dort hinterlegte Abgabetermin angepasst. '
	),
	array(
		0 => 'Was sind Sondertipps?',
		1 => 'Sondertipps sind Einzeltipps mit gesonderter Punktevergabe. Sie können zu einem Spieltag oder zu einer Liga-Saison definiert werden und 
fließen somit entweder in die Wertung zum Spieltag oder in die Gesamtwertung ein. Die Frage zu einem Sondertipp kann individuell hinterlegt werden, 
sollte aber unmissverständlich sein.'
	),
	array(
		0 => 'Welche Antwort- und Ergebnis-Möglichkeiten stehen zur Verfügung?',
		1 => 'Je nach Fragetyp steht als Antwort die Auswahl eines Teams der Liga, eine Zahl oder ein Text zur Verfügung. Das Ergebnis kann zusätzlich 
noch aus mehreren Werten bestehen, die jeweils durch ";" getrennt werden. So können Multiple Choice Fragen mit einer Antwort-Möglichkeit hinterlegt werden. 
Wenn beim Fragetyp 3 oder 4 in der Frage die Auswahlmöglichkeiten durch A: B: C: ... H: gekennzeichnet werden, erscheinen bei der Tipp- und Ergebniseingabe 
automatisch entsprechende Werte in einer Dropdown-Box.'
	),
	array(
		0 => 'Welche Frage- und Antwort-Möglichkeit bietet Fragetyp 1?',
		1 => 'Mit Fragetyp 1 kann bei Antwort und Ergebnis jeweils nur eine Mannschaft aus der Teamliste der Liga ausgewählt werden. Die Maximalpunktzahl 
wird nur bei völliger Übereinstimmung vergeben, sonst erhält der Tipper 0 Punkte. <br />
Beispiel-Frage: Wer wird Meister?'
	),
	array(
		0 => 'Welche Frage- und Antwort-Möglichkeit bietet Fragetyp 2?',
		1 => 'Fragetyp 2 ist wie Fragetyp 1, jedoch wird das Ergebnis in Textform (eine oder mehrere Team-IDs durch ";" getrennt) eingegeben. Die 
Maximalpunktzahl wird vergeben, wenn die zur Antwortauswahl zugehörige Team-ID mit einem Wert aus dem Ergebnis übereinstimmt. <br />
Beispiel-Frage: Nenne einen Absteiger (Platz 16-18 am Saisonende)'
	),
	array(
		0 => 'Welche Frage- und Antwort-Möglichkeit bietet Fragetyp 3?',
		1 => 'Bei Fragetyp 3 kann als Antwort und Ergebnis nur ein Wert (Text oder Zahl) hinterlegt werden. Die Maximalpunktzahl wird vergeben, wenn die 
Antwort mit dem Ergebnis übereinstimmt, sonst erhält der Tipper 0 Punkte. <br />
Beispiel-Frage: Wie viel rote (ohne gelb-rote) Karten werden an diesem Spieltag vergeben? A=0  B=1 C=2 D=mehr als 2'
	),
	array(
		0 => 'Welche Frage- und Antwort-Möglichkeit bietet Fragetyp 4?',
		1 => 'Bei Fragetyp 4 kann als Antwort eine Zahl oder ein Text und als Ergebnis mehrere Zahlen oder mehrere Texte (aber keine Kombination aus Zahlen 
und Texten) durch ";" getrennt eingegeben werden. Die Maximalpunktzahl wird vergeben, wenn die Zahl oder der Text in der Antwort mit einem Wert aus dem 
Ergebnis übereinstimmt. <br /> 
Beispiel-Frage: Hannover 96 A=steigt ab B=holt den Pokal C=spielt nicht international C=wird Deutscher Meister'
	),
	array(
		0 => 'Welche Frage- und Antwort-Möglichkeit bietet Fragetyp 5?',
		1 => 'Bei Fragetyp 5 kann als Antwort und Ergebnis nur eine Zahl hinterlegt werden. Die Maximalpunktzahl wird vergeben, wenn die Antwort mit dem 
Ergebnis übereinstimmt, sonst erhält der Tipper die Maximalpunktzahl abzüglich der absoluten Differenz zwischen Antwort und Ergebnis, jedoch keine negativen 
Punkte. <br />
Beispiel-Frage: Wie viel Tore fallen an diesem Spieltag?'
	),
	array(
		0 => 'In welche Wertung fließt ein Sondertipp ein?',
		1 => 'Wenn Abgabe- und Auswertungsspieltag identisch gewählt werden, fließt die Wertung in die Spieltagswertung und -rangliste ein. Wird der 
Auswertungsspieltag abweichend gewählt, fließt der Sondertipp in die Gesamtwertung und -rangliste ein.'
	),
	array(
		0 => 'Kann ich während der Saison Sondertipps anlegen?',
		1 => 'Ja, allerdings nur an Spieltagen die noch getippt werden können. Abgespeicherte Sondertipps können anschließend noch verschoben werden. Dabei 
ist jedoch der Status des Sondertipps gesondert zu prüfen und gegebenenfalls zu korrigieren, da mit Verschiebung auf einen nicht mehr tippbaren Spieltag der 
Status des Sondertipps nicht automatisch angepasst wird.'
	),
	array(
		0 => 'Wann wird ein Sondertipp für die Tippabgabe gesperrt?',
		1 => 'Die Sondertipps werden mit dem ersten Abgabetermin eines Spieltags für die Tippabgabe geschlossen. Will man z.B. einen Meistertipp bis zum 
3.Spieltag ermöglichen, so legt man die Abgabe auf den 3 Spieltag und die Auswertung auf den letzten Spieltag.'
	),
	array(
		0 => '--',
		1 => 'Tipprunden Konfiguration'
	),
	array(
		0 => 'Welche Zeitzone muss ich für die Tipprunde einstellen?',
		1 => 'Wenn der Hoster in der gleichen Zeitzone des Forums liegt, ist in der Tipprunde dieselbe Zeitzone wie im Forum zu wählen. Liegt der Hoster in 
einer anderen Zeitzone, so ist die Zeitzone der Tipprunde entsprechend der Differenz zu setzen. Dies ist notwendig, da für die Abgabe und Ergebniseingabe 
der Spiele System-Zeitfunktionen verwendet werden.'
	),
	array(
		0 => 'Wozu wird der Tipprunde Code verwendet?',
		1 => 'Damit die Erinnerungsfunktion über cron_remember.php nicht von Außenstehenden missbräuchlich verwendet werden kann, wird beim Aufruf der 
Parameter Code mit dem in der Tipprunde hinterlegten Code verglichen. Wenn man z.B. den Code 19032038 im Adminbereich hinterlegt, so sollte der Cronjob 
cron_remember.php?code=19032038 für die Erinnerungsmail aufrufen.'
	),
	array(
		0 => 'Wie kann ich die Anzeige der Ranglisten in der Hauptansicht übersichtlich gestalten?',
		1 => 'Über den Schalter "Anzahl angezeigter Tipper in den Übersichts-Ranglisten" können die Spieltags- Gesamtrangliste eingeschränkt werden. Sollte 
der Ranglistenplatz des angemeldeten nicht zu einer Anzeige führen, so wird die Platzierung unterhalb der Rangliste angefügt. '
	),
	array(
		0 => '--',
		1 => 'Kontoübersicht / Ultimate Points (UP)'
	),
	array(
		0 => 'Ist der Ultimate Points Extension erforderlich?',
		1 => 'Nein, wenn du jedoch den Tippern deiner Tipprunde einen Gesamtstand anzeigen möchtest, solltest du den Ultimate Points Extension installieren. <br />
Unabhängig vom Ultimate Points Extension werden Ligagewinne dem Konto im phpBB3 football Extension gutgeschrieben und der Admin kann Einsätze, Ein- und Auszahlungen 
verbuchen.'
	),
	array(
		0 => 'Was bewirkt der Schalter "Ultimate Points einbinden" in den Tipprunden-Funktionalitäten im Adminbereich?',
		1 => 'Dieser Schalter hat nur Auswirkungen auf die automatische Verrechnung der Gewinne im Gesamtsaldo von Ultimate Points. Hier kann man wählen, 
ob Gewinne, Punkte oder nichts aus dem phpBB3 football Extension in den Ultimate Points Saldo einfließen sollen. Die Verrechnung der erzielten Tipprundenpunkte 
kann zusätzlich noch über einen Faktor gesteuert werden. '
	),
	array(
		0 => 'Können andere meine Kontoübersicht sehen?',
		1 => 'Nur wer entsprechende Admin-Rechte besitzt, kann im Tipprundenbereich die Kontoübersicht eines anderen Tippers abfragen.'
	),
	array(
		0 => 'Wie werden Einsätze, Ein- und Auszahlungen und Gewinne verbucht?',
		1 => 'Einsätze belasten das Tipperkonto und den Gesamtsaldo von UP und können durch Einzahlungen wieder ausgeglichen werden. In der Saison 
gutgeschriebene Gewinne können vom Tipperkonto ausgezahlt werden, wobei die Auszahlung ebenfalls den Gesamtsaldo in UP belastet. Gewinne werden automatisch 
über die Gewinnverteilung der Tipprunde beim Speichern der Spielergebnisse mit dem Tipperkonto und UP (bei gesetztem Schalter) verbucht. Beim erneuten 
Abspeichern von Ergebnissen werden die alten Buchungen storniert und neu gebucht. Sollten an den nachfolgenden Spieltagen bereits Ergebnisse vorhanden sein, 
werden die dazugehörigen Ranglisten und Buchungen korrigiert. Ein- und Auszahlungen sind nur für reale Zahlungen gedacht.'
	),
	array(
		0 => 'Einsatz buchen',
		1 => 'Wenn UP installiert und beim Buchen aktiviert ist, wird der Einsatz vom UP Saldo abgezogen. Die Einsätze können für eine komplette Liga abgebucht 
werden oder für einzelne Tipper. Bei der Buchung für die gesamte Liga werden bereits vorher schon gebuchte Einsätze berücksichtigt, so dass insgesamt 
maximal der in der Liga hinterlegte Einsatz abgebucht wird. Bei Einzelabbuchung je ausgewählten Tipper werden die ausstehenden Einsätze als Vorgabe 
in die jeweiligen Eingabefelder eingetragen. Werden diese Werte verändert erfolgt keine Überprüfung auf Unter- bzw. Überzahlung.'
	),
	array(
		0 => 'Einzahlung buchen',
		1 => 'Wenn UP installiert und beim Buchen aktiviert ist, wird die Einzahlung dem Saldo gutgeschrieben. Somit können durch Einsatzbuchungen entstandene 
negative Kontostände wieder ausgeglichen werden. Einzahlungen können für eine komplette Liga gebucht werden oder für einzelne Tipper. Bei der Buchung für die 
gesamte Liga werden bereits vorher gebuchte Einzahlungen berücksichtigt, so dass insgesamt maximal der in der Liga hinterlegte Einsatz gebucht wird. 
Bei Einzelbuchung je ausgewählten Tipper werden die ausstehenden Einzahlungen als Vorgabe in die jeweiligen Eingabefelder eingetragen. Werden diese Werte 
verändert erfolgt keine Überprüfung auf Unter- bzw. Überzahlung.'
	),
	array(
		0 => 'Auszahlung buchen',
		1 => 'Wenn UP installiert und beim Buchen aktiviert ist, belastet die Auszahlung den Saldo. Somit können in der Saison erzielte Gewinne ausgezahlt werden
und der Saldo ausgeglichen werden. Auszahlungen können für eine komplette Liga gebucht werden oder für einzelne Tipper. Bei der Buchung für die 
gesamte Liga werden bereits vorher gebuchte Auszahlungen berücksichtigt, so dass insgesamt maximal der in der Liga erzielte Gewinn ausgebucht wird. 
Bei Einzelbuchung je ausgewählten Tipper werden die ausstehenden Auszahlungen als Vorgabe in die jeweiligen Eingabefelder eingetragen. Werden diese Werte 
verändert erfolgt keine Überprüfung auf Über- bzw. Unterzahlung.'
	),
	array(
		0 => 'Vorgehensweise nach dem Update auf Version 0.9.2 ',
		1 => 'Beim Update des phpBB3 Football MODs auf Version 0.92 werden alle vorhanden Ranglisten (auch aus den Vorjahren) neu berechnet und die Gewinne 
dabei automatisch im Tipperkonto verbucht. Eine Verrechnung mit dem UP-Saldo wird dabei nicht vorgenommen. Ist dies erwünscht, so muss der Schalter 
"Ultimate Points einbinden" entsprechend gesetzt werden und die Spielergebnisse des ersten Spieltags je Liga erneut abgespeichert werden. Einsätze, 
Ein- und Auszahlungen müssen manuell im Adminbereich je Saison nachverrechnet werden. Vorher sollte man entscheiden, ob diese Buchungen auch im UP-Saldo 
berücksichtigt werden sollen und dementsprechend den Schalter für UP setzen.'
	),
	array(
		0 => '--',
		1 => 'Spielplan Update'
	),
	array(
		0 => 'Vorgehensweise beim Spielplan-Update',
		1 => 'Ein Update des Spielplans kann man nur mit Daten einer anderen Tipprunde-Seite durchführen. Eine Übersicht der zur Verfügung gestellten Saisons 
und Ligen einer Seite kann über /football/xml/football_xml_season.php ausgehend vom phpbb-Rootverzeichnis abgerufen werden. Wird diese komplette Adresse
im Browser direkt eingegeben, werden die XML-Daten der Übersicht angezeigt und mit Klick auf einen Liganamen werden die aktuellen Daten der Liga aus 
der Datenbank angezeigt. Klickt man in dieser Ansicht auf das Diskettensymbol, so kann man die Daten der Liga als XML-File downloaden.<br />
Beim Starten des Updates im Adminbereich unter Spielplan-Update wird die unter Einstellungen abgespeicherte Saisonübersicht als Updatequelle eingetragen.
Ist in den Extension-Einstellungen keine Updatequelle hinterlegt, wird die Football Extension Demoseite als Quelle eingetragen. Nach dem Laden der Saisonübersicht
werden die verfügbaren Ligen zur Auswahl angeboten. Wird der Zugriff auf eine andere URL vom eigenen Hoster nicht unterstützt bzw. ist dieser nicht frei-
geschaltet, muss man die gewünschten Ligen als XML-File downloaden und unter root/store ablegen. In diesem Fall muss localhost als Updatequelle eingetragen
werden und am Besten auch in den Einstellungen für zukünftige Aufrufe abgespeichert werden.<br />
Der Zugriff auf die Saisonübersicht und die Ligadaten kann über einen Code geschützt werden. Nutzer des Updates müssen dann den Code xxxx beim Aufruf der
Saisonübersicht mit ?code=xxxx anhängen; also /football/xml/football_xml_season.php?code=xxxx aufrufen. Beim anschließenden Aufruf der Ligadaten wird dann 
der Code weitergereicht. <br />
Als Zielliga kann man eine bestehende Liga oder eine neue Liga durch Eingabe der neuen Liga-ID wählen. Um eine neue Liga einzurichten, muss vorher 
zumindest die Saison existieren bzw. im Adminbereich angelegt werden. In diesem Fall werden alle Daten der Update-Datei nach ersetzen der Saison und Liga-ID 
angezeigt und nach Bestätigung in die Datenbank übernommen.<br />
Wird eine bestehende Liga gewählt, werden zunächst Saison und Liga-ID im Update ersetzt und die Teams mit der Datenbank verglichen. Bei abweichenden Team-IDs
oder unterschiedlicher Teamanzahl, ist im nächsten Schritt ein Team-Mapping durchzuführen. Anschließend werden alle Abweichungen zwischen Update und Datenbank
angezeigt und man kann satz- und spaltenweise wählen welche Daten übernommen werden sollen. Unterschiedlich Vorbelegungen bei der Spaltenauswahl ist dabei zu
beachten und ggf. anzupassen. Nach der Übernahme der abweichenden Daten aus dem Updaten werden Abgabetermine und Status im Spieltag automatisch korrigiert und die
die Rangliste der Liga ab dem ersten Spieltag neu berechnet.<br />
Im Admin-Log wird eine Übersicht der durchgeführten Änderungen protokolliert.'
	),
	array(
		0 => 'Team Mapping',
		1 => 'Das Team Mapping ist notwendig, wenn die Team-IDs nicht identisch sind oder die Anzahl der Teams abweichend ist. Neue hinzugekommene Teams,
wie z.B. in der EL die Gruppendritten der CL, werden in der Datenbank eingefügt. Nach dem Mapping wird überprüft, ob alle bestehenden Teams der Datenbank 
zugeordnet sind. Die Team-IDs werden anhand des Mappings in den Update-Daten ausgetauscht. Neue Teams werden erst beim eigentlichen Update eingefügt.'		
	),
	array(
		0 => 'Status-Behandlung bei den Spielen',
		1 => 'Beim Erzeugen der XML-Daten werden die Status 4-6 auf die Status 1-3 geändert, d.h. im Update gibt es keine Spiele die aus der Wertung 
genommen wurden. Für die Spiele mit Spielstatus < 2 werden die Tor-Felder als auch der Status selbst nicht übernommen. Steht in der Datenbank ein Status 
größer als 3, so bleibt dieser Status erhalten und das Update-Programm behandelt den Datensatz wie mit dem korrespondierenden Status 1-3. Grundvoraussetzung 
für die Datenübernahme unter Betrachtung des Status bleibt jedoch die Selektion der entsprechenden Spalten und Datensätzen innerhalb der Spiele.' 
	),
	array(
		0 => 'Negative Status übernehmen',
		1 => 'Ist diese Option gesetzt, werden die negativen Status, aber nicht die Tore, übernommen. Dadurch ist es möglich die gestaffelten Abgabetermine
aus dem Update zu übernehmen. Die Abgabetermine sind entweder aus dem Update zu übernehmen (Spaltenoptionen im Spieltag) oder manuell im Adminbereich zu
erfassen. Bei einer Ziel-Liga mit "Tippabgabe bis zum jeweiligen Spielbeginn" darf die Option "Negative Status übernehmen" nicht gewählt werden.' 
	),
	array(
		0 => 'Ergebnisse mit gleichem Status aktualisieren',
		1 => 'Nur wenn diese Option gesetzt ist, werden die Tor-Felder aus dem Update bei gleichem Status in Update und Datenbank übernommen. Dadurch 
überschreibt das Update bereits erfasst (vorläufige) Ergebnisse in der Datenbank. Dies gilt auch für Spiele die in der Datenbank aus der Wertung genommen
wurden. Status 3 im Update überschreibt also die endgültigen Ergebnisse mit Status 6 in der Datenbank. Analog überschreibt Status 2 im Update die 
vorläufigen, aus der Wertung genommen Ergebnisse mit Status 4, dies aber nur wenn die Option "Nur endgültige Ergebnisse übernehmen" nicht gewählt ist.' 
	),
	array(
		0 => 'Nur endgültige Ergebnisse übernehmen',
		1 => 'Wenn diese Option gesetzt ist, werden nur die Tor-Felder aus dem Update-Datensatz übernommen, wenn der Status des Spiels im Update gleich
3 ist. Vorläufige Ergebnisse aus dem Update werden somit nicht berücksichtigt. Sollen diese berücksichtigt werden, so ist diese Option explizit 
auszuschalten. Sollen die vorläufigen Ergebnisse aus dem Update vorläufige Ergebnisse in der Datenbank überschreiben, ist zusätzlich die Option "Ergebnisse 
mit gleichem Status aktualisieren" zu wählen.' 
	),
	array(
		0 => 'Zwischenspeicherung im Cache',
		1 => 'Die Daten der Saisonübersicht und der Ligen werden für 5 Minuten im Cache zwischengespeichert, damit keine doppelten Zugriffe auf die
Datenquelle erfolgen. Benötigt man innerhalb dieser Zeit trotzdem aktualisierte Daten, so muss man den Cache löschen um einen erneuten Download von der
Datenquelle zu erzwingen.' 
	),
);
?>