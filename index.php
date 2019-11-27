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

use local_learning_analytics\local\routing\router;
use local_learning_analytics\local\routing\route;
use local_learning_analytics\local\parameter\parameter_course;


require(__DIR__ . '/../../config.php');

defined('MOODLE_INTERNAL') || die;

require_login();

global $PAGE;

$courseIdReader = new parameter_course('course');
$course_id = $courseIdReader->get();
$context = context_course::instance($course_id, MUST_EXIST);

$PAGE->set_context($context);
$PAGE->set_heading(get_string('pluginname', 'local_learning_analytics'));
$PAGE->set_pagelayout('course');

$course = get_course($course_id);
$PAGE->set_course($course);


$PAGE->navbar->add(get_string('learning_analytics', 'local_learning_analytics'),
    new moodle_url('/local/learning_analytics/index.php'));

$reports = core_component::get_plugin_list('lareport');

$router = new router([
    new route('/', function () {
        return 'HOME';
    }, 'home'),
    new route('/courses', 'local_learning_analytics\\local\\controller\\controller_courses@run', 'courses'),
    new route('/reports/:report', 'local_learning_analytics\\local\\controller\\controller_report@run', 'reports'),
    new route('/reports/:report/:page', 'local_learning_analytics\\local\\controller\\controller_report@run_page', 'reports'),
]);

$route = $router->get_active_route();

$output = $PAGE->get_renderer('local_learning_analytics');

$PAGE->requires->css('/local/learning_analytics/static/styles.css');
$mainOutput =  $output->render_from_template('local_learning_analytics/base', [
    'reports' => array_keys($reports),
    'content' => $route->execute(),
    'prefix' => new moodle_url('/local/learning_analytics/index.php'),
    'active_home' => $router->is_active_route('home') ? 'active' : '',
    'active_courses' => $router->is_active_route('courses') ? 'active' : '',
    'active_reports' => $router->is_active_route('reports') ? 'active' : '',
]);

echo $output->header();
echo $mainOutput;
echo $output->footer();
