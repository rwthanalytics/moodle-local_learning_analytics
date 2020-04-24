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
$string['hits'] = 'Hits'; // "Aufrufe"

// Settings
$string['setting_status'] = 'status';
$string['setting_status_description'] = 'Der Wert bestimmt ob das User Interface genutzt werden kann und ob ein Link im Navigationsmenü gezeigt wird. Standardmäßig, der Link im Navigationsmenü und die Seite selber sind nur aktiv, wenn das Loggen für den Kurs aktiviert ist. Diese Option kann z.B. genutzt werden um alle Kurse zu loggen, aber nur in bestimmten Kursen das User Interface anzuzeigen.';
$string['setting_status_option_show_if_enabled'] = 'Navigationslink zeigen, wenn Logging für den Kurs aktiviert ist';
$string['setting_status_option_show_courseids'] = 'Navigationslink zeigen, wenn der Kurs in course_ids (nächste Option) definiert ist';
$string['setting_status_option_show_always'] = 'Navigationslink in allen Kursen zeigen, selbst wenn das Loggen deaktiviert ist (nützlich falls Daten zuvor bereits geloggt wurden)';
$string['setting_status_option_hide_link'] = 'Navigationslink nicht anzeigen, aber die Seite selber aktivieren (wer den Link kennt, kann weiterhin die Seite nutzen)';
$string['setting_status_option_disable'] = 'Navigationslink nicht anzeigen und die Seite selber in allen Kursen deaktivieren';

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
