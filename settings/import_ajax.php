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

// get current import state
$state = get_config('local_learning_analytics', 'import_userid');
// $state = get_config('local_learning_analytics', 'import_offset');

die(gettype($state) . ' - ' . ($state === false));

$userid = optional_param('userid', 0, PARAM_INT);
$offset = optional_param('offset', 0, PARAM_INT);

$import = new import();

$result = $import->import_user($userid, $offset);

echo ' // ' . json_encode($result);