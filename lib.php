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
 * @copyright   2018 Lehr- und Forschungsgebiet Ingenieurhydrologie - RWTH Aachen University
 * @author      Marcel Behrmann <behrmann@lfi.rwth-aachen.de>
 * @author      Thomas Dondorf <dondorf@lfi.rwth-aachen.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// Callback to extend navigation
/**
 * @param global_navigation $nav
 * @throws coding_exception
 * @throws moodle_exception
 */
function local_learning_analytics_extend_navigation(global_navigation $nav) {
    global $PAGE, $COURSE;

    \local_learning_analytics\tracker::track_request();

    // Only extend navigation inside courses - 1 is the base system 'course'
    if ($PAGE->context->contextlevel === CONTEXT_COURSE) {
        $node = $nav->find($PAGE->context->instanceid, navigation_node::TYPE_COURSE);

        if ($node) {
            $node->add_node(navigation_node::create(
                    get_string('learning_analytics', 'local_learning_analytics'),
                    new moodle_url('/local/learning_analytics/course.php/reports/coursedashboard', array('course' => $COURSE->id)),
                    navigation_node::TYPE_CUSTOM,
                    null, 'learning_analytics',
                    new pix_icon('i/report', '')
                ),
                'grades'
            );
        }
    }
}
