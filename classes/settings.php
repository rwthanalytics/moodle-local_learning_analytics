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

defined('MOODLE_INTERNAL') || die;

abstract class settings {

    const DEFAULTS = [
        'dataprivacy_threshold' => 10,
        'dashboard_boxes' => 'learners:3,weekheatmap:3,quiz_assign:3,activities:3',
        'student_rolenames' => 'student',
    ];

    public static function get_config(string $configkey) {
        $value = get_config('local_learning_analytics', $configkey);
        if ($value === false || $value === '') {
            return self::DEFAULTS[$configkey];
        }
        return $value;
    }

    // This function is used to get a language string in the language of the Moodle page (not of the user who is doing the change)
    // This is used by customfields, as they do not support language strings (as they are inserted into the database)
    private static function site_lang_string($langkey) {
        $pagelanguage = get_config('core', 'lang'); // read the site default language
        if (!$pagelanguage) { // default to english
            $pagelanguage = 'en';
        }
        // this will always fall back to english in case no other language string exist
        return get_string_manager()->get_string($langkey, 'local_learning_analytics', null, $pagelanguage);
    }

    // If the multilang-filter is activated, this will concat the languages with "invalid" dividers in between.
    // That way, if multilang strings are disabled later, both languages (including the divider) will be shown.
    // If the multilang-filter is not activated, this will simply return the language string in the language
    // of the page.
    private static function multilang_string($langkey) {
        $stringfilters = filter_get_string_filters();
        $isMultilangEnabled = isset($stringfilters['multilang']); // checks if the filter is applied to content and headers
        if ($isMultilangEnabled) {
            $strmanager = get_string_manager();
            $translations = $strmanager->get_list_of_translations();
            // used to see if the returned value is actually the "default" (english) translation
            $defaultCheck = $strmanager->get_string('customfield_field_description', 'local_learning_analytics', null, 'en');
            $values = [];
            foreach ($translations as $langKey => $langName) {
                $langValue = $strmanager->get_string('customfield_field_description', 'local_learning_analytics', null, $langKey);
                if ($langValue === $defaultCheck && $langKey !== 'en') { // skip over non-existing strings (that fall back to English)
                    continue;
                }
                $value = $strmanager->get_string($langkey, 'local_learning_analytics', null, $langKey);
                $values[] = "<span lang=\"{$langKey}\" class=\"multilang\">{$value}</span>";
            }
            return implode('<span lang="invalid" class="multilang"> / </span>', $values);
        } else {
            return self::site_lang_string($langkey);
        }
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
                'name' => self::site_lang_string('customfield_category_name'),
                'description' => self::site_lang_string('customfield_category_description'),
            ], $handler);
            \core_customfield\api::save_category($category);

            // Create customfield entry (inside of category created above)
            $field = \core_customfield\field_controller::create(0, (object)[
                'name' => self::multilang_string('customfield_field_name'),
                'shortname' => 'learning_analytics_enable',
                'description' => self::multilang_string('customfield_field_description'),
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
