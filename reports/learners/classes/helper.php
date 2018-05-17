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
 * @copyright   2018 Lehr- und Forschungsgebiet Ingenieurhydrologie - RWTH Aachen University
 * @author      Marcel Behrmann <behrmann@lfi.rwth-aachen.de>
 * @author      Thomas Dondorf <dondorf@lfi.rwth-aachen.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace lareport_learners;

use lareport_learners\outputs\split;
use local_learning_analytics\local\outputs\table;
use lareport_learners\query_helper;
use local_learning_analytics\local\routing\router;
use moodle_url;
use paging_bar;

defined('MOODLE_INTERNAL') || die;

class helper {

    private static $USER_PER_FULL_PAGE = 20;
    private static $USER_PREVIEW = 5;

    // if abs($other_course_startdate - $this_course_startdate) < $PARALLEL_COURSE_BUFFER
    // they are considered being parallel, otherwise before (or after)
    private static $PARALLEL_COURSE_BUFFER = 31 * 24 * 60 * 60;

    public static function generateCourseParticipationList(int $courseid, int $limit = -1) {

        $learnersCount = query_helper::query_learners_count($courseid, 'student');

        $courses = query_helper::query_courseparticipation($courseid);

        $tablePrevious = new table();
        $tablePrevious->set_header_local(['coursename', 'participated_before'],
            'lareport_learners');

        $tableParallel = new table();
        $tableParallel->set_header_local(['coursename', 'participating_now'],
            'lareport_learners');

        $courseStartdate = get_course($courseid)->startdate;

        $previousRows = 0;
        $parallelRows = 0;

        foreach ($courses as $course) {
            $perc = round(100 * $course->users / $learnersCount);

            $row = [
                $course->fullname,
                table::fancyNumberCell(
                    $perc,
                    100,
                    'red',
                    $perc . '%'
                )
            ];

            if ($course->startdate < ($courseStartdate - self::$PARALLEL_COURSE_BUFFER)) {
                if ($limit === -1 || $previousRows < $limit) {
                    $tablePrevious->add_row($row);
                    $previousRows++;
                }
            } else if ($course->startdate < ($courseStartdate + self::$PARALLEL_COURSE_BUFFER)) {
                if ($limit === -1 || $parallelRows < $limit) {
                    $tableParallel->add_row($row);
                    $parallelRows++;
                }
            } else {
                // course is happening after the current course, might be interesting for the future
            }
        }

        if ($limit !== -1) {
            $linkToFullList = router::report_page('learners', 'courseparticipation', ['course' => $courseid]);
            $tablePrevious->add_show_more_row($linkToFullList);
            $tableParallel->add_show_more_row($linkToFullList);
        }

        $headingPrevious = get_string('courses_heard_before', 'lareport_learners');
        $headingParallel = get_string('parallel_courses', 'lareport_learners');

        return [
            new split(
                ["<h3>{$headingPrevious}</h3>", $tablePrevious],
                ["<h3>{$headingParallel}</h3>", $tableParallel]
            )
        ];
    }

    public static function generateLearnersList(int $courseid, int $page = -1, string $roleFilter = ''): array {

        $fullPage = true;
        $perPage = self::$USER_PER_FULL_PAGE;

        if ($page === -1) {
            $fullPage = false;
            $page = 0;
            $perPage = self::$USER_PREVIEW;
        }

        $learnersCount = query_helper::query_learners_count($courseid, $roleFilter);

        $learners = query_helper::query_learners($courseid, $roleFilter, $page * $perPage, $perPage);
        $table = new table();
        $table->set_header_local(['firstname', 'lastname', 'role', 'firstaccess', 'lastaccess', 'hits', 'sessions'],
            'lareport_learners');

        $maxHits = reset($learners)->hits;
        $maxSessions = 1;
        foreach ($learners as $learner) {
            $maxSessions = max($maxSessions, $learner->sessions);
        }

        foreach ($learners as $learner) {
            $firstaccess = !empty($learner->firstaccess) ? userdate($learner->firstaccess) : '-';
            $lastaccess = !empty($learner->lastaccess) ? userdate($learner->lastaccess) : '-';

            $table->add_row([
                $learner->firstname,
                $learner->lastname,
                $learner->role,
                $firstaccess,
                $lastaccess,
                table::fancyNumberCell(
                    (int) $learner->hits,
                    $maxHits,
                    'green'
                ),
                table::fancyNumberCell(
                    (int) $learner->sessions,
                    $maxSessions,
                    'red'
                )
            ]);
        }

        if ($fullPage) {
            $pageParams = ['course' => $courseid];
            if ($roleFilter !== '') {
                $pageParams['role'] = $roleFilter;
            }
            $pageUrl = new moodle_url('/local/learning_analytics/index.php/reports/learners/all', $pageParams);
            $pagingbar = new paging_bar($learnersCount, $page, $perPage, $pageUrl);

            return [$pagingbar, $table, $pagingbar];
        } else {
            $linkToFullList = router::report_page('learners', 'all', ['course' => $courseid]);
            $table->add_show_more_row($linkToFullList);

            return [$table];
        }

    }
}