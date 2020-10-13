<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Strings for local_learning_analytics
 *
 * @package     local_learning_analytics
 * @copyright   Lehr- und Forschungsgebiet Ingenieurhydrologie - RWTH Aachen University
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Learning Analytics';

$string['learning_analytics'] = 'Learning Analytics';

$string['subplugintype_lareport'] = 'Report';
$string['subplugintype_lareport_plural'] = 'Reports';
$string['subplugintype_laoutput'] = 'Output';
$string['subplugintype_laoutput_plural'] = 'Outputs';


$string['show_full_list'] = 'Mehr anzeigen';

// Terms also used by subplugins
$string['learners'] = 'Learners';
$string['sessions'] = 'Sessions';
$string['hits'] = 'Aufrufe'; // "Aufrufe"

// Settings
$string['setting_status_description'] = 'Der Wert bestimmt ob das User Interface genutzt werden kann und ob ein Link im Navigationsmenü gezeigt wird. Standardmäßig, der Link im Navigationsmenü und die Seite selber sind nur aktiv, wenn das Loggen für den Kurs aktiviert ist. Diese Option kann z.B. genutzt werden um alle Kurse zu loggen, aber nur in bestimmten Kursen das User Interface anzuzeigen.';
$string['setting_status_option_show_if_enabled'] = 'Navigationslink zeigen, wenn Logging für den Kurs aktiviert ist';
$string['setting_status_option_show_courseids'] = 'Navigationslink zeigen, wenn der Kurs in course_ids (nächste Option) definiert ist';
$string['setting_status_option_show_always'] = 'Navigationslink in allen Kursen zeigen, selbst wenn das Loggen deaktiviert ist (nützlich falls Daten zuvor bereits geloggt wurden)';
$string['setting_status_option_hide_link'] = 'Navigationslink nicht anzeigen, aber die Seite selber aktivieren (wer den Link kennt, kann weiterhin die Seite nutzen)';
$string['setting_status_option_disable'] = 'Navigationslink nicht anzeigen und die Seite selber in allen Kursen deaktivieren';
$string['setting_status_course_customfield'] = 'Eintrag in den Kurseinstellungen hinzufügen, so dass die Kursinhaber selber entscheiden können';

$string['setting_course_ids_description'] = 'Diese Option kann zusammen mit der zweiten Einstellung für "status" genutzt werden um zu entscheiden in welchen Kursen das User Interface aktiviert sein soll.';

$string['dataprivacy_threshold_description'] = 'Bestimmt wie viele Datenpunkte ein Datensatz haben muss, bevor er angezeigt wird.';
$string['allow_dashboard_compare_description'] = 'Diese Option kann aktiviert werden um im Dashboard die Möglichkeit zu aktivieren den Kurs mit einem anderen Kurs zu vergleichen. Wird die Option aktiviert ist unter dem Zeitverlauf im Dashboard ein Link zu finden, der es erlaubt einen Kurs auszuwählen. Der wöchentliche Verlauf wird dann durch eine zweite gestrichelte Linie ergänzt.';
$string['navigation_position_beforekey_description'] = 'Erlaubt es die Position in der Navigation anzugeben, an der der Link zur Seite erscheinen soll. Standardmäßig, wir der Link vor dem ersten "section"-Link angezeigt. Beispielwert: <code>grades</code> um den Link über den Link zu den Bewertungen anzuzeigen. Um herauszufinden, wie der "key" eines Navigationslinks ist, können die Entwicklertools des Browsers genutzt werden. Hierzu einen Rechtsklick auf den gewünschten Link machen, <em>Untersuchen</em> auswählen und dann das Attribut <code>data-key</code> des entsprechenden <code>a</code>-Elementes nutzen.';
$string['setting_student_rolenames_description'] = 'Falls die Rolle <code>student</code> nicht die passende Rolle für Studierende/Nutzer ist oder es mehrere Rollen gibt, die zutreffend sind, können hier die passenden Rollen angegeben werden. Falls mehrere Rollen zutreffen, sollte ein einzelnes Komma genutzt werden um die Rollen zu trennen. Beispiel: <code>student,customrole</code>';

$string['setting_student_enrols_groupby_description'] = 'Für die Statistik "Vorher/Parallelgehört" kann durch die Option bestimmt werden, welche Kurse zusammengefasst werden sollen.';

// Help
$string['help_title'] = 'Hilfe';
$string['help_take_tour'] = 'Interaktive Vorstellung starten';
$string['help_text'] = 'Learning Analytics zeigt verschiedene Kennzahlen des Kurses.

Die dargestellen Statistiken sind live und enthalten sowohl selber erhobenen Daten des Moduls als auch Moodle-eigene Daten. Alle vom Learning Analytics-Modul erhobenen Daten werden anonymisiert erhoben und erlauben keine Rückverfolgung zu einzelnen Nutzern.';

$string['help_faq'] = 'Häufig gestellte Fragen';

$string['help_faq_personal_data_question'] = 'Warum werden keine personalisierten Statistiken, wie z.B. die Klicks pro Nutzer angezeigt?';
$string['help_faq_personal_data_answer'] = 'Aus Datenschutzgründen erhebt das Learning Analytics-Modul keine personalisierten Daten. Daher enthalten die meisten dargestellten Statistiken (z.B. die meist genutzten Aktivitäten) nur Information darüber wie oft eine Resource aufgerufen wurde, aber nicht von wie vielen Teilnehmer/innen.
Eine Ausnahme stellen die Statistiken zu Teilnehmer/innen und Tests/Aufgaben da, da hier auch Moodle-eigene Daten dargestellt werden.';

$string['help_faq_week_start_question'] = 'Warum entspricht die erste Woche in der Darstellung im Dashboard nicht dem tatsächlichen Vorlesungsstart?';
$string['help_faq_week_start_answer'] = 'Die Darstellung im Dashboard richtet sich nach der Einstellung "Kursbeginn" in den Kurseinstellungen. Sollte das dort vorgegebene Datum nicht dem tatsächlichen Start der Vorlesung entsprechen, wird auch die Anzeige im Dashboard nicht korrekt sein.
Handelt es sich um Ihren Kurs, so können Sie die Einstellung auf der folgenden Seite (unter Allgemeines / Kursbeginn) korrigieren:';

$string['help_faq_data_storage_question'] = 'Welche Daten werden durch das Angebot gespeichert und dargestellt?';
$string['help_faq_data_storage_answer'] = 'Die dargestellten Daten stammen aus zwei Datenquellen.
Beide Quellen werden in der internen Moodle-Datenbank gespeichert.
Bei der ersten Datenquelle handelt es sich um interne Moodle-Datensätze, wie z.B. die Anzahl an Teilnehmer/innen im Kurs (linke Box im Dashboard).
Diese Datensätze lassen sich zum Teil auch auf anderen Moodle-Seiten einsehen und werden durch das Learning Analytics Angebot anders visualisiert.
Bei der zweiten Datenquelle handelt es sich um Daten, die eigens für das Learning Analytics Angebot erhoben werden.
Alle Daten werden anonymisiert gespeichert.
Die erhobenen Daten erlauben keine Rückverfolgung zu einzelnen Nutzern.
Konkret werden bei jedem Aufruf in Moodle folgende Daten gespeichert:';
$string['help_faq_data_storage_answer_list'] = 'Typ der Aktion (z.B. "Quizversuch gestartet") kodiert als Zahl
Uhrzeit (sekundengenau)
Betroffener Kurs (ID des Kurses) in dem die Aktion durchgeführt wurde
Betroffener Kontext (z.B. die ID des Quizzes das gestartet wurde)
Betoffenes Objekt (z.B. der Quizversuch, der gestartet wurde)
Betriebssystem und Browser (z.B. "Windows 10" und "Firefox") kodiert als Zahl (Detaillierte Browser- oder Betriebssystemversionen werden nicht gespeichert)';

$string['help_faq_privacy_threshold_question'] = 'Warum werden manche Werte als "< {$a}" angezeigt?';
$string['help_faq_privacy_threshold_answer'] = 'Aus Datenschutzgründen werden aggregierte Daten erst dargestellt, wenn mindestens {$a} Datensätze vorhanden sind.';

$string['help_faq_visibility_question'] = 'Wer kann auf die Learning Analytics-Statistiken zugreifen?';
$string['help_faq_visibility_answer'] = 'Um größtmögliche Transparenz zu gewährleisten, können die angezeigten Daten sowohl von den Managern/Inhabern als auch von den Teilnehmer/innen des Kurses eingesehen werden.';

$string['help_faq_developer_question'] = 'Durch wen wird das Angebot entwickelt und wo erhalten ich weitere Informationen?';
$string['help_faq_developer_answer'] = 'Die Entwicklung des Learning Analytics Angebot geschieht durch die RWTH Aachen University.
Die Entwicklung ist Open Source. Sie können die eingesetzten Algorithmen daher selber überprüfen. Auf den folgenden Seiten können Sie auf weitere Informationen zugreifen:';

// Tour
$string['tour_title'] = 'Learning Analytics';
$string['tour_dashboard_graph'] = 'Der Verlauf zeigt die Anzahl aller Zugriffe in der jeweiligen Woche an.

Handelt es sich um einen aktuellen Kurs, so wird der Beginn der laufenden Woche durch eine gestrichelte Linie gekennzeichnet. Zahlen der laufenden Woche werden nicht angezeigt.';
$string['tour_dashboard_boxes'] = 'Im unteren Bereich werden wichtige Kennzahlen des Kurses dargestellt.

Darüber hinaus enthält jede Box einen Link durch den weiterführende Informationen angezeigt werden können.';
$string['tour_box_learners'] = 'Die erste Anzeige gibt die Gesamtzahl an eingeschriebenen Teilnehmer/innen wieder. Unterhalb der großen Zahl, ist die Veränderung zur Vorwoche dargstellt.';
$string['tour_box_learners_link'] = 'Durch einen Klick auf den Link werden weiterführende Informationen zu den Teilnehmer/innen angezeigt.';
$string['tour_box_hits'] = 'Diese Darstellung stellt die Anzahl an Aufrufen innerhalb der letzten 7 Tage dar. Unterhalb ist die Veränderungen du den vorherigen 7 Tagen angegeben.

Durch einen Klick auf den Link lässt sich eine Heatmap aufrufen, welche die Anzahl an Aufrufen über das gesamte Semester visualisiert.';
$string['tour_box_quiz_assign'] = 'Hier wird die Anzahl an Test-Versuchen und Aufgaben-Abgaben der letzten 7 Tage angezeigt. Unterhalb ist erneut die Veränderungen du den vorherigen 7 Tagen angegeben.

Durch einen Klick auf den Link lassen sich Details zu den Tests und Aufgaben des Kurses anzeigen.';
$string['tour_activities'] = 'Die letzte Auswertung zeigt die drei meistgenutzten Aktivitäten der letzten 7 Tage.

Durch einen Klick auf den Link lassen sich Details zu den Aktivitäten des Kurses anzeigen.';

$string['tour_more_information'] = 'Die interaktive Tour ist hiermit beendet. Wir hoffen wir konnten einen guten Überblick über die Funktionen verschaffen.

Weitere Antworten zu häufig gestellten Fragen finden sich auf der Hilfeseite.';
