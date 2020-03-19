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
 * Main entry point for Learning Analytics UI
 *
 * @package     local_learning_analytics
 * @copyright   Lehr- und Forschungsgebiet Ingenieurhydrologie - RWTH Aachen University
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_learning_analytics\router;
use local_learning_analytics\event\report_viewed;

require(__DIR__ . '/../../config.php');

defined('MOODLE_INTERNAL') || die;

require_login();

global $PAGE, $USER;

$courseid = required_param('course', PARAM_INT);
$context = context_course::instance($courseid, MUST_EXIST);

require_capability('local/learning_analytics:view_statistics', $context, $USER->id);
$PAGE->set_context($context);
$PAGE->set_heading(get_string('pluginname', 'local_learning_analytics'));
$PAGE->set_pagelayout('course');

// Set URL to main path of analytics
$url = new moodle_url('/local/learning_analytics/index.php/reports/coursedashboard', ['course' => $courseid]);
$PAGE->set_url($url);

// For now, all statistics are shown on course level
$course = get_course($courseid);
$PAGE->set_course($course);

// Set title of page
$coursename = format_string($course->fullname, true, array('context' => context_course::instance($course->id)));
$title = $coursename . ': ' . get_string('learning_analytics', 'local_learning_analytics');
$PAGE->set_title($title);

$resulting_html = router::run($_SERVER['REQUEST_URI']);

$output = $PAGE->get_renderer('local_learning_analytics');

$PAGE->requires->css('/local/learning_analytics/static/styles.css');
$mainOutput =  $output->render_from_template('local_learning_analytics/course', [
    'content' => $resulting_html
]);

echo $output->header();
echo $mainOutput;
echo $output->footer();

$coursecontextid = $context->id;
$sql1 = <<<SQL
    SELECT path
    FROM mdl_context
    WHERE id = '$coursecontextid'
SQL;
$coursepath = $DB->get_record_sql($sql1);
$sqlhelper = '%'.$context->path.'/'.'%';
$sql2 = <<<SQL
    SELECT id
    FROM mdl_context
    WHERE path LIKE '$sqlhelper'
SQL;
$realcontextid = $DB->get_record_sql($sql2)->id;
$event = report_viewed::create(array(
    'contextid' => $realcontextid
));
$event->add_record_snapshot('course', $PAGE->course);
$event->trigger();
