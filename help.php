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

// delete tour settings from user, so that he can take the tour again
$tourid = 5; // TODO find correct tour id
$DB->delete_records('user_preferences', [
    'userid' => $USER->id,
    'name' => \tool_usertours\tour::TOUR_LAST_COMPLETED_BY_USER . $tourid
]);
$DB->delete_records('user_preferences', [
    'userid' => $USER->id,
    'name' => \tool_usertours\tour::TOUR_REQUESTED_BY_USER . $tourid
]);

$PAGE->requires->css('/local/learning_analytics/static/help.css?1');
$output = $PAGE->get_renderer('local_learning_analytics');

$helptext = array_values(
    array_filter(
        explode("\n", get_string('help_text', 'local_learning_analytics')),
        function(string $value) { return trim($value) !== ''; }
    )
);
$reports = ['coursedashboard', 'learners', 'weekheatmap', 'quiz_assign', 'activities'];
$reportstrs = [];
foreach ($reports as $report) {
    $reportstrs[] = [
        'title' => get_string('report_' . $report . '_title', 'local_learning_analytics'),
        'description' => get_string('report_' . $report . '_description', 'local_learning_analytics')
    ];
}

$faqs = ['week_start', 'data_storage', 'privacy_threshold', 'visibility', 'developer'];
$faqstrs = [];
foreach ($faqs as $faq) {
    $faqstrs[] = [
        'question' => get_string('help_faq_' . $faq . '_question', 'local_learning_analytics'),
        'answer' => get_string('help_faq_' . $faq . '_answer', 'local_learning_analytics')
    ];
}

echo $output->header();
echo $output->render_from_template('local_learning_analytics/help', [
    'courseid' => $courseid,
    'helptext' => $helptext,
    'reports' => $reportstrs,
    'faq' => $faqstrs,
]);
echo $output->footer();
