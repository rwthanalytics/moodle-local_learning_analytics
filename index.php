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
 * @copyright   2018 Lehr- und Forschungsgebiet Ingenieurhydrologie - RWTH Aachen University
 * @author      Marcel Behrmann <behrmann@lfi.rwth-aachen.de>
 * @author      Thomas Dondorf <dondorf@lfi.rwth-aachen.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_learning_analytics\local\routing\router;
use local_learning_analytics\local\routing\route;


require(__DIR__ . '/../../config.php');

defined('MOODLE_INTERNAL') || die;

require_login();

global $PAGE;

$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('report');

$reports = core_component::get_plugin_list('lareport');

/*
foreach ($reports as $report => $path) {
    $cp = $path . DIRECTORY_SEPARATOR . "lareport_${report}.php";
    if(file_exists($cp)) {
        require ($cp);
    }
}
*/

$router = new router([
    new route('/', function () {
        return 'HOME';
    }),
    new route('/courses', function() {
        return "COURSE";
    }),
    new route('/reports/:report', 'local_learning_analytics\\local\\controller\\controller_report@run')
]);

$route = $router->get_active_route();

$output = $PAGE->get_renderer('local_learning_analytics');

echo $output->header();
echo $output->render_from_template('local_learning_analytics/base', [
    'reports' => array_keys($reports),
    'content' => $route->execute(),
    'prefix' => new moodle_url('/local/learning_analytics/index.php')
]);
echo $output->footer();
