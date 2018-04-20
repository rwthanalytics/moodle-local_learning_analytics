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
 * Learning Analytics Import script
 *
 * @package     local_learning_analytics
 * @copyright   2018 Lehr- und Forschungsgebiet Ingenieurhydrologie - RWTH Aachen University
 * @author      Marcel Behrmann <behrmann@lfi.rwth-aachen.de>
 * @author      Thomas Dondorf <dondorf@lfi.rwth-aachen.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../../config.php');

use local_learning_analytics\import;

defined('MOODLE_INTERNAL') || die;

require_login();
if (!is_siteadmin()) {
    throw new moodle_exception('Only admins can import data.');
}

global $PAGE;

$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('admin');
$PAGE->set_title(get_string('import_title', 'local_learning_analytics'));
$PAGE->set_heading(get_string('import_title', 'local_learning_analytics'));

$output = $PAGE->get_renderer('local_learning_analytics');

$import = new import();

$tableIsEmpty = $import->table_is_empty();
$estimation = $import->estimate();

$lastUserid = (int) get_config('local_learning_analytics', 'import_userid');

$PAGE->requires->css('/local/learning_analytics/static/styles_import.css');
$PAGE->requires->js_call_amd('local_learning_analytics/import', 'init', [ $lastUserid, $estimation['users'] ]);
echo $output->header();

echo $output->render_from_template('local_learning_analytics/settings_import', [
    'tableIsEmpty' => $tableIsEmpty,
    'estimation' => $estimation
]);
echo $output->footer();
