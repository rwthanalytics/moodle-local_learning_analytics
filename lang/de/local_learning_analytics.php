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
$string['setting_status'] = 'status';
$string['setting_status_description'] = 'Der Wert bestimmt ob das User Interface genutzt werden kann und ob ein Link im Navigationsmenü gezeigt wird. Standardmäßig, der Link im Navigationsmenü und die Seite selber sind nur aktiv, wenn das Loggen für den Kurs aktiviert ist. Diese Option kann z.B. genutzt werden um alle Kurse zu loggen, aber nur in bestimmten Kursen das User Interface anzuzeigen.';
$string['setting_status_option_show_if_enabled'] = 'Navigationslink zeigen, wenn Logging für den Kurs aktiviert ist';
$string['setting_status_option_show_courseids'] = 'Navigationslink zeigen, wenn der Kurs in course_ids (nächste Option) definiert ist';
$string['setting_status_option_show_always'] = 'Navigationslink in allen Kursen zeigen, selbst wenn das Loggen deaktiviert ist (nützlich falls Daten zuvor bereits geloggt wurden)';
$string['setting_status_option_hide_link'] = 'Navigationslink nicht anzeigen, aber die Seite selber aktivieren (wer den Link kennt, kann weiterhin die Seite nutzen)';
$string['setting_status_option_disable'] = 'Navigationslink nicht anzeigen und die Seite selber in allen Kursen deaktivieren';
$string['setting_status_course_customfield'] = 'Eintrag in den Kurseinstellungen hinzufügen, so dass die Kursinhaber selber entscheiden können';

$string['setting_course_ids'] = 'course_ids';
$string['setting_course_ids_description'] = 'Diese Option kann zusammen mit der zweiten Einstellung für "status" genutzt werden um zu entscheiden in welchen Kursen das User Interface aktiviert sein soll.';

$string['dataprivacy_threshold'] = 'dataprivacy_threshold';
$string['dataprivacy_threshold_description'] = 'Bestimmt wie viele Datenpunkte ein Datensatz haben muss, bevor er angezeigt wird.';
$string['allow_dashboard_compare'] = 'allow_dashboard_compare';
$string['allow_dashboard_compare_description'] = 'Diese Option kann aktiviert werden um im Dashboard die Möglichkeit zu aktivieren den Kurs mit einem anderen Kurs zu vergleichen. Wird die Option aktiviert ist unter dem Zeitverlauf im Dashboard ein Link zu finden, der es erlaubt einen Kurs auszuwählen. Der wöchentliche Verlauf wird dann durch eine zweite gestrichelte Linie ergänzt.';
$string['navigation_position_beforekey'] = 'navigation_position_beforekey';
$string['navigation_position_beforekey_description'] = 'Erlaubt es die Position in der Navigation anzugeben, an der der Link zur Seite erscheinen soll. Standardmäßig, wir der Link vor dem ersten "section"-Link angezeigt. Beispielwert: <code>grades</code> um den Link über den Link zu den Bewertungen anzuzeigen. Um herauszufinden, wie der "key" eines Navigationslinks ist, können die Entwicklertools des Browsers genutzt werden. Hierzu einen Rechtsklick auf den gewünschten Link machen, <em>Untersuchen</em> auswählen und dann das Attribut <code>data-key</code> des entsprechenden <code>a</code>-Elementes nutzen.';
$string['setting_student_rolenames'] = 'student_rolenames';
$string['setting_student_rolenames_description'] = 'Falls die Rolle <code>student</code> nicht die passende Rolle für Studierende/Nutzer ist oder es mehrere Rollen gibt, die zutreffend sind, können hier die passenden Rollen angegeben werden. Falls mehrere Rollen zutreffen, sollte ein einzelnes Komma genutzt werden um die Rollen zu trennen. Beispiel: <code>student,customrole</code>';

$string['setting_student_enrols_groupby'] = 'student_enrols_groupby';
$string['setting_student_enrols_groupby_description'] = 'Für die Statistik "Vorher/Parallelgehört" kann durch die Option bestimmt werden, welche Kurse zusammengefasst werden sollen.';

// Help
$string['help_title'] = 'Hilfe';
$string['help_take_tour'] = 'Interaktive Vorstellung anzeigen';
$string['help_text'] = 'Learning Analytics zeigt verschiedene Kennzahlen des Kurses.

Die dargestellen Statistiken enthalten sowohl selber erhobenen Daten des Moduls als auch Moodle-eigene Daten. Alle vom Learning Analytics-Modul erhobenen Daten werden anonymisiert erhoben und erlauben keine Rückverfolgung zu einzelnen Nutzern.

Es werden verschiedene Kennzahlen gezeigt. Das Dashboard gibt eine allgemeine Übersicht, die darüberhinaus auf vier weitere Seiten verlinkt, die weitere Statistiken anzeigen.';

$string['help_available_reports'] = 'Verfügbare Statistiken';
$string['report_coursedashboard_title'] = 'Dashboard (Hauptseite)';
$string['report_coursedashboard_description'] = 'The reports gives an overview of ...';
$string['report_learners_title'] = 'Registered users';
$string['report_learners_description'] = 'The reports gives an overview of ...';
$string['report_weekheatmap_title'] = 'Number of hits / Weekly heatmap';
$string['report_weekheatmap_description'] = 'The reports gives an overview of ...';
$string['report_quiz_assign_title'] = 'Registered users';
$string['report_quiz_assign_description'] = 'The reports gives an overview of ...';
$string['report_activities_title'] = 'Activities';
$string['report_activities_description'] = 'The reports gives an overview of ...';

$string['help_faq'] = 'Frequently Asked Questions (FAQ)';
$string['help_faq_week_start_question'] = 'Why is the first week not the actual start of the lecture?';
$string['help_faq_week_start_answer'] = 'TODO';
$string['help_faq_data_storage_question'] = 'What data is stored by the Learning Analytics service?';
$string['help_faq_data_storage_answer'] = 'TODO';
$string['help_faq_privacy_threshold_question'] = 'Why do some values show as "$a"?';
$string['help_faq_privacy_threshold_answer'] = 'TODO';
$string['help_faq_visibility_question'] = 'Who has the right to see the metrics?';
$string['help_faq_visibility_answer'] = 'TODO';
$string['help_faq_developer_question'] = 'Who develops the Learning Analytics service?';
$string['help_faq_developer_answer'] = 'TODO';

$string['tour_overview_title'] = 'Learning Analytics';
$string['tour_dashboard_boxes'] = 'The boxes at the bottom show the key metrics of your course. By clicking on the individual links you can get more information.';
$string['tour_more_information'] = 'TODO ...

If you need more information, check out the Help page.';