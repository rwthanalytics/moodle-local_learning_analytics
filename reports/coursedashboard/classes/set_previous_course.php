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
use local_learning_analytics\settings;

defined('MOODLE_INTERNAL') || die;

class set_previous_course extends report_page_base {

    private function getusercourses() {
        global $DB, $USER;

        if (is_siteadmin()) {
            // If user is admin, allow to set any course.
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

    private function setprevcourse(int $courseid, int $prevcourseid) {
        global $DB, $USER;

        $allowedcourses = $this->getusercourses();
    }

    public function run(array $params): array {
        global $DB;

        $showcompare = settings::get_config('allow_dashboard_compare');
        if (!$showcompare) {
            return ['Disabled'];
        }

        $selectedprevcourse = $params['prev_course'];
        $courseid = $params['course'];
        $previd = query_helper::getCurrentPrevCourse($courseid);

        if ($selectedprevcourse !== -1) {
            // User set a new previous course.
            $usercourses = $this->getusercourses();
            if (array_key_exists($selectedprevcourse, $usercourses)) {
                // User is allowed to set this.
                if ($previd === -1) {
                    // There is no value set yet.
                    $record = new stdClass();
                    $record->courseid  = $courseid;
                    $record->prevcourseid = $selectedprevcourse;
                    $DB->insert_record('local_learning_analytics_pre', $record);
                } else {
                    $DB->set_field('local_learning_analytics_pre', 'prevcourseid', $selectedprevcourse, ['courseid' => $courseid]);
                }
                $backurl = new moodle_url('/local/learning_analytics/index.php/reports/coursedashboard', ['course' => $courseid]);
                redirect($backurl);
                exit;
            }
        }

        $courses = $this->getusercourses();

        $output = html_writer::start_tag('form', ['method' => 'post']);
        $output .= html_writer::start_div('form-group');
        $output .= html_writer::select(
            $courses,
            'prev_course',
            $previd,
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

    public function params(): array {
        return [
            'course' => required_param('course', PARAM_INT),
            'prev_course' => optional_param('prev_course', -1, PARAM_INT)
        ];
    }
}