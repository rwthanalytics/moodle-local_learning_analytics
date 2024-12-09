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
 * Local plugin "Learning Analytics" - Library
 *
 * @package     local_learning_analytics
 * @copyright   Lehr- und Forschungsgebiet Ingenieurhydrologie - RWTH Aachen University
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// Callback to extend navigation for moodle 3.
/**
 * @param global_navigation $nav
 * @throws coding_exception
 * @throws moodle_exception
 */
function local_learning_analytics_extend_navigation(global_navigation $navigation) {
    global $PAGE, $COURSE, $DB;

    // Only extend navigation inside courses.
    if (isset($COURSE->id) && $COURSE->id !== SITEID) {
        // first check if user has the capability assigned
        if (!has_capability('local/learning_analytics:view_statistics', $PAGE->context)) {
            return;
        }

        // then check if the settings of the plugin
        
        // status: 'show_if_enabled', 'show_courseids', 'show_always', 'hide_link', 'disable'
        $statussetting = get_config('local_learning_analytics', 'status');
        if ($statussetting === 'course_customfield') {
            $customfieldid = (int) get_config('local_learning_analytics', 'customfieldid');
            if (!$customfieldid) { // setup went wrong, this should not happen in reality
                return;
            }
            $record = $DB->get_record('customfield_data', [
                'fieldid' => $customfieldid,
                'instanceid' => $COURSE->id,
            ], 'intvalue');
            if ($record === false || $record->intvalue !== '1') {
                return;
            }
        } else if ($statussetting === 'hide_link' || $statussetting === 'disable') {
            return;
        } else if ($statussetting === 'show_always') {
            // just show it, don't filter anything
        } else if ($statussetting === 'show_courseids') {
            // use courseids of this plugin
            $courseids = get_config('local_learning_analytics', 'course_ids');
            if ($courseids === false || $courseids === '') {
                $courseids = [];
            } else {
                $courseids = array_map('trim', explode(',', $courseids));
            }
            if (!in_array($COURSE->id, $courseids)) {
                return;
            }
        } else { // default setting: 'show_if_enabled'
            // check if the logstore plugin is enabled, otherwise hide link
            $logstoresstr = get_config('tool_log', 'enabled_stores');
            $logstores = $logstoresstr ? explode(',', $logstoresstr) : [];
            if (!in_array('logstore_lanalytics', $logstores)) {
                return;
            }
            // logging is enabled, check logging scope
            $logscope = get_config('logstore_lanalytics', 'log_scope');
            if ($logscope !== false && $logscope !== '' && $logscope !== 'all') {
                // scope is not all -> check if course should be tracked
                $courseids = get_config('logstore_lanalytics', 'course_ids');
                if ($courseids === false || $courseids === '') {
                    $courseids = [];
                } else {
                    $courseids = array_map('trim', explode(',', $courseids));
                }
                if (($logscope === 'include' && !in_array($COURSE->id, $courseids))
                    || ($logscope === 'exclude' && in_array($COURSE->id, $courseids))) {
                    return;
                }
            }
        }

        $node = $navigation->find($COURSE->id, navigation_node::TYPE_COURSE);

        $settingbeforekey = get_config('local_learning_analytics', 'navigation_position_beforekey');
        $beforekey = null;
        if ($settingbeforekey === false || $settingbeforekey === '') {
            // Find first section node, and add our node before that (to be the last non-section node)
            if (is_object($node->children)) {
                $children = $node->children->type(navigation_node::TYPE_SECTION);
                if (count($children) !== 0) {
                    $beforekey = reset($children)->key;
                }
            }
        } else { // use setting
            $beforekey = $settingbeforekey;
        }
        if ($node) {
            $node->add_node(navigation_node::create(
                    get_string('navigationlink', 'local_learning_analytics'),
                    new moodle_url('/local/learning_analytics/index.php/reports/coursedashboard', array('course' => $COURSE->id)),
                    navigation_node::TYPE_CUSTOM,
                    null, 'learning_analytics',
                    new pix_icon('i/report', '')
                ),
                $beforekey
            );
        }
    }
}

// Callback to extend navigation for moodle 4.
/**
 * @param global_navigation $nav
 * @throws coding_exception
 * @throws moodle_exception
 */
function local_learning_analytics_extend_navigation_course(\navigation_node $navigation, \stdClass $course, \context $context) {
    global $PAGE, $COURSE, $DB;

    // Only extend navigation inside courses.
    if (isset($COURSE->id) && $COURSE->id !== SITEID) {
        // first check if user has the capability assigned
        if (!has_capability('local/learning_analytics:view_statistics', $PAGE->context)) {
            return;
        }

        // then check if the settings of the plugin
        
        // status: 'show_if_enabled', 'show_courseids', 'show_always', 'hide_link', 'disable'
        $statussetting = get_config('local_learning_analytics', 'status');
        if ($statussetting === 'course_customfield') {
            $customfieldid = (int) get_config('local_learning_analytics', 'customfieldid');
            if (!$customfieldid) { // setup went wrong, this should not happen in reality
                return;
            }
            $record = $DB->get_record('customfield_data', [
                'fieldid' => $customfieldid,
                'instanceid' => $COURSE->id,
            ], 'intvalue');
            if ($record === false || $record->intvalue !== '1') {
                return;
            }
        } else if ($statussetting === 'hide_link' || $statussetting === 'disable') {
            return;
        } else if ($statussetting === 'show_always') {
            // just show it, don't filter anything
        } else if ($statussetting === 'show_courseids') {
            // use courseids of this plugin
            $courseids = get_config('local_learning_analytics', 'course_ids');
            if ($courseids === false || $courseids === '') {
                $courseids = [];
            } else {
                $courseids = array_map('trim', explode(',', $courseids));
            }
            if (!in_array($COURSE->id, $courseids)) {
                return;
            }
        } else { // default setting: 'show_if_enabled'
            // check if the logstore plugin is enabled, otherwise hide link
            $logstoresstr = get_config('tool_log', 'enabled_stores');
            $logstores = $logstoresstr ? explode(',', $logstoresstr) : [];
            if (!in_array('logstore_lanalytics', $logstores)) {
                return;
            }
            // logging is enabled, check logging scope
            $logscope = get_config('logstore_lanalytics', 'log_scope');
            if ($logscope !== false && $logscope !== '' && $logscope !== 'all') {
                // scope is not all -> check if course should be tracked
                $courseids = get_config('logstore_lanalytics', 'course_ids');
                if ($courseids === false || $courseids === '') {
                    $courseids = [];
                } else {
                    $courseids = array_map('trim', explode(',', $courseids));
                }
                if (($logscope === 'include' && !in_array($COURSE->id, $courseids))
                    || ($logscope === 'exclude' && in_array($COURSE->id, $courseids))) {
                    return;
                }
            }
        }

        $node = $navigation;
        $settingbeforekey = get_config('local_learning_analytics', 'navigation_position_beforekey');
        $beforekey = null;
        if ($settingbeforekey === false || $settingbeforekey === '') {
            // Find first section node, and add our node before that (to be the last non-section node)
            $children = $node->children->type(navigation_node::TYPE_SECTION);
            if (count($children) !== 0) {
                $beforekey = reset($children)->key;
            }
        } else { // use setting
            $beforekey = $settingbeforekey;
        }
        if ($node) {
            $node->add_node(navigation_node::create(
                    get_string('navigationlink', 'local_learning_analytics'),
                    new moodle_url('/local/learning_analytics/index.php/reports/coursedashboard', array('course' => $COURSE->id)),
                    navigation_node::TYPE_CUSTOM,
                    null, 'learning_analytics',
                    new pix_icon('i/report', '')
                ),
                $beforekey
            );
        }
    }
}
