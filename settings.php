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

    if ($ADMIN->fulltree) {
        $statuschoices = [];
        $statuschoices['show_if_enabled'] = get_string('setting_status_option_show_if_enabled', 'local_learning_analytics');
        $statuschoices['show_courseids'] = get_string('setting_status_option_show_courseids', 'local_learning_analytics');
        $statuschoices['show_always'] = get_string('setting_status_option_show_always', 'local_learning_analytics');
        $statuschoices['hide_link'] = get_string('setting_status_option_hide_link', 'local_learning_analytics');
        $statuschoices['disable'] = get_string('setting_status_option_disable', 'local_learning_analytics');
        if ($CFG->version >= 2019052000) {
            // Moodle 3.7 supports custom course settings
            $statuschoices['course_customfield'] = get_string('setting_status_course_customfield', 'local_learning_analytics');
        }

        $settingstatus = new admin_setting_configselect(
            'local_learning_analytics/status',
            'status',
            get_string('setting_status_description', 'local_learning_analytics'),
            'show_if_enabled', // default value
            $statuschoices
        );
        $settingstatus->set_updatedcallback('\\local_learning_analytics\\settings::statusupdated');
        $settings->add($settingstatus);

        // This is only a textarea to make it more comforable entering the values
        $settings->add(new admin_setting_configtextarea(
            'local_learning_analytics/course_ids',
            'course_ids',
            get_string('setting_course_ids_description', 'local_learning_analytics'),
            '',
            PARAM_RAW,
            '60',
            '2'
        ));

        $settings->add(new admin_setting_configtext(
            'local_learning_analytics/navigation_position_beforekey',
            'navigation_position_beforekey',
            get_string('navigation_position_beforekey_description', 'local_learning_analytics'),
            '',
            PARAM_RAW
        ));

        $settings->add(new admin_setting_configtext(
            'local_learning_analytics/dataprivacy_threshold',
            'dataprivacy_threshold',
            get_string('dataprivacy_threshold_description', 'local_learning_analytics'),
            '10', // default value
            PARAM_INT
        ));

        $settings->add(new admin_setting_configtext(
            'local_learning_analytics/student_rolenames',
            'student_rolenames',
            get_string('setting_student_rolenames_description', 'local_learning_analytics'),
            'student',
            PARAM_RAW
        ));

        $settings->add(new admin_setting_configselect(
            'local_learning_analytics/student_enrols_groupby',
            'student_enrols_groupby',
            get_string('setting_student_enrols_groupby_description', 'local_learning_analytics'),
            'course.id', // default value
            [
                'id' => 'course->id',
                'shortname' => 'course->shortname',
                'fullname' => 'course->fullname',
            ]
        ));

        $settings->add(new admin_setting_configtext(
            'local_learning_analytics/dashboard_boxes',
            'dashboard_boxes',
            get_string('setting_dashboard_boxes', 'local_learning_analytics'),
            'learners:3,weekheatmap:3,quiz_assign:3,activities:3', // default value
            PARAM_RAW,
            60
        ));
    }
}
