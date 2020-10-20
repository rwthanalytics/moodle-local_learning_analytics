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
 * Uninstall for local_learning_analytics
 *
 * @package     local_learning_analytics
 * @copyright   Lehr- und Forschungsgebiet Ingenieurhydrologie - RWTH Aachen University
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

function xmldb_local_learning_analytics_uninstall() {
    global $DB, $CFG;

    // Remove user tour
    $tourid = (int) get_config('local_learning_analytics', 'tourid');
    if ($tourid !== 0) {
        $tour = \tool_usertours\tour::instance($tourid);
        $tour->remove();
    }

    // remove customfield (if that option was used)
    $statussetting = get_config('local_learning_analytics', 'status');
    $customfieldid = (int) get_config('local_learning_analytics', 'customfieldid');
    if ($statussetting === 'course_customfield' && $customfieldid !== 0) {
        $field = \core_customfield\field_controller::create($customfieldid);
        $fieldid = $field->get('id');
        $category = $field->get_category();
        $fieldsincategory = count($category->get_fields());
        if ($fieldsincategory === 1) {
            // we only remove this if the admin didn't change the category and customfield
            // otherwise we leave everything as it is..
            $field->delete();
            $category->delete();
        }
    }

    return true;
}
