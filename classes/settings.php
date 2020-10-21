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
 * Learning Analytics Settings Values
 *
 * @package     local_learning_analytics
 * @copyright   Lehr- und Forschungsgebiet Ingenieurhydrologie - RWTH Aachen University
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_learning_analytics;

// Unfortunately, the customfields do not support language strings (as they are inserted into the database)
// So, instead we have to use strings that are english/german
const STRING_CATEGORY_NAME = 'Learning Analytics';
const STRING_CATEGORY_DESCRIPTION = 'This category was automatically created by the Learning Analytics plugin (local_learning_analytics). You should not manually delete this.';

// We use multilang strings here with an "invalid" divider in between. That way, if multilang strings are enabled, the corresponding language is used, otherwise
// both languages (including the divider) will be shown.
const STRING_FIELD_NAME = '<span lang="en" class="multilang">Enable Usage Statistics</span><span lang="invalid" class="multilang"> / </span><span lang="de" class="multilang">Zugriffsstatistiken aktivieren</span>';
const STRING_FIELD_DESCRIPTION = '<span lang="en" class="multilang">Enabling adds the link "Usage Statistics" to your course navigation.</span><span lang="invalid" class="multilang"> / ' .
    "\r\n" . '</span><span lang="de" class="multilang">Die Aktivierung fügt der Kursnavigation den Link "Zugriffsstatistiken" hinzu.</span>';

defined('MOODLE_INTERNAL') || die;

abstract class settings {

    const DEFAULTS = [
        'dataprivacy_threshold' => 10,
        'allow_dashboard_compare' => 0,
        'student_rolenames' => 'student',
    ];

    public static function get_config(string $configkey) {
        $value = get_config('local_learning_analytics', $configkey);
        if ($value === false || $value === '') {
            return self::DEFAULTS[$configkey];
        }
        return $value;
    }

    public static function statusupdated() {
        // The status setting was changed via the administration
        // In case it was changed from `course_setting` or to `course_setting`, we need to cleanup
        // the custom course entries.

        global $CFG, $DB;
        if ($CFG->version < 2019052000) {
            // The option is only supported since Moodle version 3.7 (because of customfields)
            return;
        }

        $statussetting = get_config('local_learning_analytics', 'status');
        $customfieldid = (int) get_config('local_learning_analytics', 'customfieldid');
        if ($statussetting === 'course_customfield' && empty($customfieldid)) {
            // changed from another value to "customfield", so create customfield

            // Create customfield category for courses
            $handler = \core_course\customfield\course_handler::create();
            $category = \core_customfield\category_controller::create(0, (object)[
                'name' => STRING_CATEGORY_NAME,
                'description' => STRING_CATEGORY_DESCRIPTION,
            ], $handler);
            \core_customfield\api::save_category($category);

            // Create customfield entry (inside of category created above)
            $field = \core_customfield\field_controller::create(0, (object)[
                'name' => STRING_FIELD_NAME,
                'shortname' => 'learning_analytics_enable',
                'description' => STRING_FIELD_DESCRIPTION,
                'type' => 'checkbox'
            ], $category);
            $formdata = \core_customfield\api::prepare_field_for_config_form($field);
            $formdata->configdata = [
                'required' => '0',
                'uniquevalues' => '0',
                'checkbydefault' => '0',
                'locked' => '0',
                'visibility' => '0',
            ];
            \core_customfield\api::save_field_configuration($field, $formdata);
            $fieldid = $field->get('id');

            set_config('customfieldid', $fieldid, 'local_learning_analytics');

            // Set `customfield_data` for each course from courses in `course_ids` setting
            $courseids = get_config('local_learning_analytics', 'course_ids');
            if ($courseids !== false && $courseids !== '') {
                // set existing course ids as 
                $courseids = array_unique(array_map('trim', explode(',', $courseids)));

                $datatoinsert = [];
                $now = time();
                foreach ($courseids as $courseid) {
                    $coursecontext = \context_course::instance($courseid);
                    $record = new \stdClass();
                    $record->fieldid  = $fieldid;
                    $record->instanceid = $courseid;
                    $record->intvalue = 1;
                    $record->value = 1;
                    $record->valueformat = 0;
                    $record->contextid = $coursecontext->id;
                    $record->timecreated = $now;
                    $record->timemodified = $now;
                    $datatoinsert[] = $record;
                }
                
                $DB->insert_records('customfield_data', $datatoinsert);
            }
        } else if ($statussetting !== 'course_customfield' && !empty($customfieldid)) {
            // changed from "customfield" to non-"customfield" so remove customfield entry
            $field = \core_customfield\field_controller::create($customfieldid);
            $fieldid = $field->get('id');
            $category = $field->get_category();
            $fieldsincategory = count($category->get_fields());
            if ($fieldsincategory === 1) {
                // we only really work here if the admin didn't change the category and customfield

                // The code below does not work, but I'll keep it here for the next commit...
                // Problem is that Moodle updates the course_ids value after we set it here and then
                // Moodle overwrites our just-saved-value...
                // // Merge data from customfield_data into our `course_ids` text field
                // $oldcourseids = get_config('local_learning_analytics', 'course_ids');
                // if ($oldcourseids === false || $oldcourseids === '') {
                //     $oldcourseids = [];
                // } else {
                //     $oldcourseids = array_map('trim', explode(',', $oldcourseids));
                // }
                
                // $newcourseids = [];
                // $rows = $DB->get_records('customfield_data', [ 'fieldid' => $fieldid ], '', 'id,instanceid');
                // foreach ($rows as $row) {
                //     $newcourseids[] = $row->instanceid;
                // }
                
                // $mergedarrays = array_unique(array_merge($oldcourseids, $newcourseids));
                // $mergedarraysstr = implode(',', $mergedarrays);
                // set_config('course_ids', $mergedarraysstr, 'local_learning_analytics');

                // we only delete if the admin did not change anything (by adding other fields to the category)
                $field->delete();
                $category->delete();
            }
            unset_config('customfieldid', 'local_learning_analytics');
        }
    }
}
