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
 *
 *
 * @package     local_learning_analytics
 * @copyright   Lehr- und Forschungsgebiet Ingenieurhydrologie - RWTH Aachen University
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die;

$settings_folder = new admin_category('local_learning_analytics', get_string('pluginname', 'local_learning_analytics'), false);

$ADMIN->add('localplugins', $settings_folder);
$ADMIN->add('local_learning_analytics',
        new admin_externalpage(
                'local_learning_analytics_import',
                get_string('import', 'local_learning_analytics'),
                new moodle_url('/local/learning_analytics/settings/import.php')
        )
);

$settings = null;
