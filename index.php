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

require(__DIR__ . '/../../config.php');

defined('MOODLE_INTERNAL') || die;

require_login();

global $PAGE, $USER, $DB;

$courseid = required_param('course', PARAM_INT);
$showtour = optional_param('tour', 0, PARAM_INT) === 1;
$context = context_course::instance($courseid, MUST_EXIST);

require_capability('local/learning_analytics:view_statistics', $context, $USER->id);
if ($courseid == SITEID) {
    throw new moodle_exception('invalidcourse');
}

// status: 'show_if_enabled', 'show_courseids', 'show_always', 'hide_link', 'disable'
$statussetting = get_config('local_learning_analytics', 'status');
    
if ($statussetting === 'course_customfield') {
    $customfieldid = (int) get_config('local_learning_analytics', 'customfieldid');
    $record = $DB->get_record('customfield_data', [
        'fieldid' => $customfieldid,
        'instanceid' => $courseid,
    ], 'intvalue');
    if ($record === false || $record->intvalue !== '1') {
        throw new moodle_exception('Learning Analytics is not enabled (for this course).');
    }
} else if ($statussetting === 'disable') {
    throw new moodle_exception('Learning Analytics is not enabled (for this course).');
} else if ($statussetting === 'show_always' || $statussetting === 'hide_link') {
    // just show it, don't filter anything
} else if ($statussetting === 'show_courseids') {
    // use courseids of this plugin
    $courseids = get_config('local_learning_analytics', 'course_ids');
    if ($courseids === false || $courseids === '') {
        $courseids = [];
    } else {
        $courseids = array_map('trim', explode(',', $courseids));
    }
    if (!in_array($courseid, $courseids)) {
        throw new moodle_exception('Learning Analytics is not enabled (for this course).');
    }
} else { // default setting: 'show_if_enabled'
    // check if the logstore plugin is enabled, otherwise hide link
    $logstoresstr = get_config('tool_log', 'enabled_stores');
    $logstores = $logstoresstr ? explode(',', $logstoresstr) : [];
    if (!in_array('logstore_lanalytics', $logstores)) {
        throw new moodle_exception('Learning Analytics is not enabled (for this course).');
    }
    // logging is enabled, check logging scope
    $logscope = get_config('logstore_lanalytics', 'log_scope');
    if ($logscope !== false && $logscope !== '' && $logscope !== 'all') {
        // scope is not all -> check if course should be tracked
        $courseids = get_config('logstore_lanalytics', 'course_ids');
        if ($courseids === false || $courseids === '') {
            $courseids = [];
        } else {
            $courseids = array_map('trim', explode(',', $courseids));
        }
        if (($logscope === 'include' && !in_array($courseid, $courseids))
            || ($logscope === 'exclude' && in_array($courseid, $courseids))) {
            throw new moodle_exception('Learning Analytics is not enabled (for this course).');
        }
    }
}

$PAGE->set_context($context);

// Set URL to main path of analytics.
$currentparams = ['course' => $courseid];
if ($showtour) {
    $currentparams = ['tour' => 1, 'course' => $courseid];
}
$url = new moodle_url('/local/learning_analytics/index.php/reports/coursedashboard', $currentparams);
$PAGE->set_url($url);

// For now, all statistics are shown on course level.
$course = get_course($courseid);
$PAGE->set_course($course);

// Header of page (we simply use the course name to be consitent with other pages)
$PAGE->set_pagelayout('course');
$PAGE->set_heading($course->fullname);

// title of page.
$coursename = format_string($course->fullname, true, array('context' => context_course::instance($course->id)));
$title = $coursename . ': ' . get_string('navigationlink', 'local_learning_analytics');
$PAGE->set_title($title);

$resultinghtml = router::run($_SERVER['REQUEST_URI']);

$output = $PAGE->get_renderer('local_learning_analytics');

$PAGE->requires->css('/local/learning_analytics/static/styles.css?4');
$mainoutput = $output->render_from_template('local_learning_analytics/course', [
    'content' => $resultinghtml
]);
echo $output->header();
echo $mainoutput;
echo $output->footer();
