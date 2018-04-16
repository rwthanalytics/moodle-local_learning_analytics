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
 * Learning Analytics Report Controller
 *
 * @package     local_learning_analytics
 * @copyright   2018 Lehr- und Forschungsgebiet Ingenieurhydrologie - RWTH Aachen University
 * @author      Marcel Behrmann <behrmann@lfi.rwth-aachen.de>
 * @author      Thomas Dondorf <dondorf@lfi.rwth-aachen.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_learning_analytics\local\controller;

use local_learning_analytics\controller_base;

use local_learning_analytics\local\outputs\table;
use context_course;
use moodle_exception;

class controller_courses extends controller_base {

    /**
     * @return string
     * @throws \coding_exception
     */
    public function run(): string {
        global $DB, $USER;

        $table = new table();

        $query = <<<SQL
        SELECT `id`, `fullname`
        FROM {course}
        WHERE id IN (
            SELECT instanceid FROM {context}
            WHERE id IN (
                SELECT `contextid`
                FROM `{role_assignments}`
                WHERE
                    userid = ? AND
                    `roleid` <= 4
            ) AND contextlevel = 50
        )
SQL;
        //  TODO: add something like this: AND `startdate` <= UNIX_TIMESTAMP() AND `enddate` > UNIX_TIMESTAMP()

        $table->set_header(['course_name', 'blabla', 'blabla', 'blabla', 'blabla', 'blabla', 'blabla']);

        $courses = $DB->get_records_sql($query, [$USER->id]);

        $table->add_rows_ajax(self::class . '@ajax', array_map(function($e) {
            return ['content' => $e->fullname, 'id' => $e->id];
        }, $courses));

        foreach ($courses as $course) {
            $table->data[] = [$course->fullname, $course->id];
        }

        return $table->print();

    }

    /**
     * @param string $id
     * @return array
     * @throws \dml_exception
     * @throws \required_capability_exception
     * @throws moodle_exception
     */
    public function ajax(string $id) {
        global $DB;
        $courseid = (int) $id;

        $course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
        $context = context_course::instance($course->id, MUST_EXIST);

        if ($course->id == SITEID) {
            throw new moodle_exception('invalidcourse');
        }
        // only teachers and managers
        require_capability('moodle/course:update', $context);

        $courseQuery = <<<SQL
    SELECT
      c.id AS courseid,
      cc.name AS category_name,
    (SELECT COUNT(DISTINCT u.id)
        FROM {user} u
            JOIN {user_enrolments} ue ON ue.userid = u.id
            JOIN {enrol} e ON (e.id = ue.enrolid AND e.courseid = ${courseid})
            WHERE 1 = 1 AND u.deleted = 0) as student_count,
    (SELECT ROUND(AVG(g.finalgrade), 2)
        FROM mdl_grade_items gi
        JOIN mdl_grade_grades g
            ON g.itemid = gi.id
            WHERE gi.itemtype = 'course'
                AND g.finalgrade IS NOT NULL
                AND gi.courseid = ${courseid}) as avg_grade,
    (SELECT COUNT(*) FROM {course_sections} WHERE visible = 1 AND course = ${courseid}) AS sections,
    (SELECT COUNT(*) FROM {course_modules} WHERE visible = 1 AND course = ${courseid}) AS activities
    FROM {course} c
    JOIN {course_categories} cc ON cc.id = c.category
      WHERE c.id = ${courseid};
SQL;

        return array_values(json_decode(json_encode(array_values($DB->get_records_sql($courseQuery, []))[0]), true));
    }
}