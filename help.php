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
 * Help page for Learning Analytics UI
 *
 * @package     local_learning_analytics
 * @copyright   Lehr- und Forschungsgebiet Ingenieurhydrologie - RWTH Aachen University
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../config.php');

defined('MOODLE_INTERNAL') || die;

require_login();

global $PAGE, $USER, $DB;

$courseid = required_param('course', PARAM_INT);
$context = context_course::instance($courseid, MUST_EXIST);

require_capability('local/learning_analytics:view_statistics', $context, $USER->id);
if ($courseid == SITEID) {
    throw new moodle_exception('invalidcourse');
}

$PAGE->set_context($context);
$PAGE->set_heading(get_string('pluginname', 'local_learning_analytics'));
$PAGE->set_pagelayout('course');

// Set URL to main path of analytics.
$url = new moodle_url('/local/learning_analytics/index.php/reports/coursedashboard', ['course' => $courseid]);
$PAGE->set_url($url);

// For now, all statistics are shown on course level.
$course = get_course($courseid);
$PAGE->set_course($course);

// Set title of page.
$coursename = format_string($course->fullname, true, array('context' => context_course::instance($course->id)));
$title = $coursename . ': ' . get_string('learning_analytics', 'local_learning_analytics');
$PAGE->set_title($title);

$PAGE->navbar->add('Hilfe', new \moodle_url("/local/learning_analytics/help.php", ['course' => $courseid]));

$resultinghtml = "<div class='row'><div class='col'><h2>Hilfe</h2></div></div>
<hr/>

<p>Learning Analytics gibt einen einfachen Überblick über die wichtigsten Daten des Kurses.</p>

<hr/>

<h3>Dashboard</h3>
<p>Das Dashboard stellt in der oberen Hälfte den Verlauf der Anzahl an Zugriffen über das Semester dar.
Hierbei werden je Woche die Anzahl an Zugriffen von Montag bis Sonntag gezählt.</p>
<p>Alle durch das Plugin erfassten Daten werden anonymisiert erfasst und lassen keinen Personenbezug zu.
Darüberhinaus stellt das Plugin auch von Moodle selber erfasste Daten dar, wie z.B. die Ergebnisse von Aufgaben oder Quizzen unter Quizze & Aufgaben.</p>

<hr/>

<h3>Häufig gestellte Fragen / FAQ</h3>
<ul class='faq'>
    <li>
        <p class='question'>Warum entspricht Woche 1 nicht dem tatsächlichen Start der Veranstaltung?</p>
        <p class='answer'>Lorem Ipsum...</p>
    </li>
    <li>
        <p class='question'>Wieso wird manchmal '< 10' als Wert angezeigt?</p>
        <p class='answer'>Welche Daten werden genau gespeichert?</p>
    </li>
    <li>
        <p class='question'>Wer kann die Statistiken einsehen?</p>
        <p class='answer'>Wer entwickelt das Learning-Analytics-Modul?</p>
    </li>
</ul>
";

$PAGE->requires->css('/local/learning_analytics/static/faq.css?1');
$output = $PAGE->get_renderer('local_learning_analytics');

echo $output->header();
echo $output->render_from_template('local_learning_analytics/course', [
    'content' => $resultinghtml
]);
echo $output->footer();
