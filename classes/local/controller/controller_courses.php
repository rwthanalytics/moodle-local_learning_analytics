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

class controller_courses extends controller_base {

    /**
     * @return string
     * @throws \coding_exception
     */
    public function run(): string {
        global $DB, $USER;

        $table = new table();

        $query = <<<SQL
        SELECT
            c.id,
            c.fullname AS course_fullname,
            cc.name AS category_name,
            (SELECT COUNT(DISTINCT u.id)
                FROM {user} u
                JOIN {user_enrolments} ue
                    ON ue.userid = u.id
                JOIN {enrol} e
                    ON e.id = ue.enrolid
                    WHERE u.deleted = 0
                    AND e.courseid = c.id) as students,
            (SELECT ROUND(AVG(g.finalgrade), 2)
                FROM {grade_items} gi
                JOIN {grade_grades} g
                    ON g.itemid = gi.id
                    WHERE gi.courseid = c.id
                    AND gi.itemtype = 'course'
                    AND g.finalgrade IS NOT NULL) as avg_grade,
            (SELECT COUNT(*)
                FROM {course_sections}
                WHERE visible = 1
                AND course = c.id) AS sections,
            (SELECT COUNT(*)
                FROM {course_modules}
                WHERE visible = 1
                AND course = c.id) AS activities
        FROM {course} c
        # category
        JOIN {course_categories} cc
            ON cc.id = c.category
            # AND cc.visible = 1 # TODO uncomment this, but for now leave it so we have more data to show
        # restrict to courses of the lecturer
        WHERE c.id IN (
            SELECT instanceid FROM {context}
            WHERE id IN (
                SELECT contextid
                FROM {role_assignments}
                WHERE userid = ? AND roleid <= 4
            ) AND contextlevel = 50
        )
        GROUP BY c.id
        ORDER BY cc.sortorder, c.fullname
SQL;

        $table->set_header_local(['course_name', 'category', 'students', 'avg_grade', 'sections', 'activities'], 'local_learning_analytics');

        $courses = $DB->get_records_sql($query, [$USER->id]);

        foreach ($courses as $course) {
            $table->add_row([$course->course_fullname, $course->category_name, $course->students, $course->avg_grade, $course->sections, $course->activities]);
        }

        return $table->print();
    }

}