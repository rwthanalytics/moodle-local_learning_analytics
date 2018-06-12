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
use moodle_url;

class controller_courses extends controller_base {

    const MAX_GRADE = 100;

    const SEMESTER_SECONDS = 60 * 60 * 24 * 180; // roughly a half year

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
            c.startdate,
            c.enddate,
            (SELECT COUNT(DISTINCT u.id)
                FROM {user} u
                JOIN {user_enrolments} ue
                    ON ue.userid = u.id
                JOIN {enrol} e
                    ON e.id = ue.enrolid
                    WHERE u.deleted = 0
                    AND e.courseid = c.id) as students,
            (SELECT ROUND(100 * (AVG(g.finalgrade) - gi.grademin)/gi.grademax, 1)
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
        ORDER BY c.startdate DESC, cc.sortorder, c.fullname
SQL;

        $courses = $DB->get_records_sql($query, [1490]);

        // find max values
        $maxStudents = 1;
        $maxSections = 1;
        $maxActivities = 1;

        foreach ($courses as $course) {
            $maxStudents = max($maxStudents, (int) $course->students);
            $maxSections = max($maxSections, (int) $course->sections);
            $maxActivities = max($maxActivities, (int) $course->activities);
        }

        $activeCourses = [];
        $oldCourses = [];

        $date = new \DateTime();
        $date->modify('-1 day');
        $now = $date->getTimestamp();

        $gotoCourseText = get_string('go_to_course', 'local_learning_analytics');

        foreach ($courses as $course) {
            $courseUrl = new moodle_url('/course/view.php', ['id' => $course->id]);
            $coursedashboardUrl = new moodle_url('/local/learning_analytics/index.php/reports/coursedashboard', ['course' => $course->id]);
            $learnersUrl = new moodle_url('/local/learning_analytics/index.php/reports/learners', ['course' => $course->id]);
            $gradesUrl = new moodle_url('/local/learning_analytics/index.php/reports/grades', ['course' => $course->id]);
            $sectionsUrl = new moodle_url('/local/learning_analytics/index.php/reports/sections', ['course' => $course->id]);
            $activityUrl = new moodle_url('/local/learning_analytics/index.php/reports/activities', ['course' => $course->id]);

            $avgGradeCell = '';
            if ($course->avg_grade !== null) {
                $avgGradeText = number_format($course->avg_grade, 1) . '%';
                $avgGradeCell = table::fancyNumberCell((float) $course->avg_grade, self::MAX_GRADE, 'green', "<a href='{$gradesUrl}'>{$avgGradeText}</a>");
            }

            $row = [
                "<a href='{$coursedashboardUrl}'>{$course->course_fullname}</a> <a href='{$courseUrl}' title='{$gotoCourseText}' class='courses-view-course'>↗️</a>",
                $course->category_name,
                table::fancyNumberCell((int) $course->students, $maxStudents, 'red', "<a href='{$learnersUrl}'>{$course->students}</a>"),
                $avgGradeCell,
                table::fancyNumberCell((int) $course->sections, $maxSections, 'orange', "<a href='{$sectionsUrl}'>{$course->sections}</a>"),
                table::fancyNumberCell((int) $course->activities, $maxActivities, 'blue', "<a href='{$activityUrl}'>{$course->activities}</a>")
            ];

            $enddate = (int) $course->enddate;
            $startdate = (int) $course->startdate;

            if ($enddate > $now || ($enddate === 0 && ($startdate + self::SEMESTER_SECONDS) > $now)) {
                // course is running right now
                $activeCourses[] = $row;
            } else {
                $oldCourses[] = $row;
            }
        }

        if (count($activeCourses) !== 0) {
            $table->set_header_local(['active_course_name', 'category', 'learners', 'avg_grade', 'sections', 'activities'], 'local_learning_analytics');
            foreach ($activeCourses as $oldCourseRow) {
                $table->add_row($oldCourseRow);
            }
        }

        if (count($oldCourses) !== 0) {
            if (count($activeCourses) === 0) {
                $table->set_header_local(['old_course_name', 'category', 'learners', 'avg_grade', 'sections', 'activities'], 'local_learning_analytics');
            } else {
                $oldCoursesHeaderStrings = ['old_course_name', 'category', 'learners', 'avg_grade', 'sections', 'activities'];
                $oldCoursesHeader = array_map(function (&$key) {
                    $cell = new \html_table_cell();
                    $cell->text = get_string($key, 'local_learning_analytics');
                    $cell->header = true;
                    $cell->attributes['class'] = 'p-t-2';
                    return $cell;
                }, $oldCoursesHeaderStrings);

                $table->add_row($oldCoursesHeader);
            }

            foreach ($oldCourses as $oldCourseRow) {
                $table->add_row($oldCourseRow);
            }
        }

        if (count($activeCourses) === 0 && count($oldCourses) === 0) {
            return get_string('unable_to_find_courses_for_user', 'local_learning_analytics');
        } else {
            return $table->print();
        }

    }

}