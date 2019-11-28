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

namespace lareport_coursedashboard;

use local_learning_analytics\report_page_base;
use lareport_coursedashboard\query_helper;
use local_learning_analytics\local\outputs\table;
use html_writer;
use moodle_url;
use stdClass;

defined('MOODLE_INTERNAL') || die;

class set_previous_course extends report_page_base {

    private function getUserCourses() {
        global $DB, $USER;

        if (is_siteadmin()) {
            // if user is admin, allow to set any course
            $options = $DB->get_records_sql("
            SELECT id, fullname 
            FROM {course}
            ORDER BY id DESC", [$USER->id]);
        } else {
            $options = $DB->get_records_sql("
              SELECT id, fullname 
              FROM {course} 
              WHERE id IN ( 
                SELECT instanceid 
                FROM {context}
                WHERE 
                  id IN (
                    SELECT contextid
                    FROM {role_assignments}
                    WHERE 
                      userid = ? AND roleid <= 4
                ) AND 
                contextlevel = 50
            )
            ORDER BY id DESC", [$USER->id]);
        }

        foreach ($options as $option) {
            $opts[$option->id] = $option->fullname;
        }
        return $opts;
    }

    private function setPrevCourse(int $courseid, int $prevCourseId) {
        global $DB, $USER;

        $allowedCourses = $this->getUserCourses();
    }

    public function run(array $params): array {
        global $DB;
        $selectedPrevCourse = (int) ($_POST['prev_course'] ?? -1);
        $courseid = (int) $params['course'];
        $prevId = query_helper::getCurrentPrevCourse($courseid);

        if ($selectedPrevCourse !== -1) {
            // user set a new previous course
            echo '-- ' . $selectedPrevCourse . '--';
            $userCourses = $this->getUserCourses();
            if (array_key_exists($selectedPrevCourse, $userCourses)) {
                // user is allowed to set this
                if ($prevId === -1) {
                    // there is no value set yet
                    $record = new stdClass();
                    $record->courseid  = $courseid;
                    $record->prevcourseid = $selectedPrevCourse;
                    $DB->insert_record('local_learning_analytics_pre', $record);
                } else {
                    $DB->set_field('local_learning_analytics_pre', 'prevcourseid', $selectedPrevCourse, ['courseid' => $courseid]);
                }
                $backUrl = new moodle_url('/local/learning_analytics/index.php/reports/coursedashboard', ['course' => $courseid]);
                redirect($backUrl);
                exit;
            }
        }

        $courses = $this->getUserCourses();

        $output = html_writer::start_tag('form', ['method' => 'post']);
        $output .= html_writer::start_div('form-group');
        $output .= html_writer::select(
            $courses,
            'prev_course',
            $prevId,
            false
        );
        $output .= html_writer::tag('button', get_string('set', 'lareport_coursedashboard'), [
            'type' => 'submit',
            'class' => 'btn btn-primary'
        ]);
        $output .= html_writer::end_div();
        $output .= html_writer::end_tag('form');

        return [
            $output
        ];
    }
}