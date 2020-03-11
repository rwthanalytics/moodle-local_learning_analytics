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
 * Settings page for local_learning_analytics
 *
 * @package     local_learning_analytics
 * @copyright   Lehr- und Forschungsgebiet Ingenieurhydrologie - RWTH Aachen University
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {
    $settings = new admin_settingpage('local_learning_analytics', get_string('pluginname', 'local_learning_analytics'));

    $ADMIN->add('localplugins', $settings);

    $settings->add(new admin_setting_configtext(
        'local_learning_analytics/dataprivacy_threshold',
        get_string('dataprivacy_threshold', 'local_learning_analytics'),
        get_string('dataprivacy_threshold_description', 'local_learning_analytics'),
        '10', // default value
        PARAM_INT
    ));

    $settings->add(new admin_setting_configcheckbox(
        'local_learning_analytics/allow_dashboard_compare',
        get_string('allow_dashboard_compare', 'local_learning_analytics'),
        get_string('allow_dashboard_compare_description', 'local_learning_analytics'),
        0
    ));

}
