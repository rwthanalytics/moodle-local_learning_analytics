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
 * Version info for the Sections learners
 *
 * @package     local_learning_analytics
 * @copyright   2018 Lehr- und Forschungsgebiet Ingenieurhydrologie - RWTH Aachen University
 * @author      Marcel Behrmann <behrmann@lfi.rwth-aachen.de>
 * @author      Thomas Dondorf <dondorf@lfi.rwth-aachen.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

use local_learning_analytics\local\outputs\table;
use local_learning_analytics\local\parameter\parameter_course;
use local_learning_analytics\local\parameter\parameter_input;
use local_learning_analytics\local\parameter\parameter_select;
use local_learning_analytics\parameter_base;
use local_learning_analytics\report_base;
use lareport_learners\query_helper;
use local_learning_analytics\local\routing\router;
use lareport_learners\learners_list;

class lareport_learners extends report_base {

    // if abs($other_course_startdate - $this_course_startdate) < $PARALLEL_COURSE_BUFFER
    // they are considered being parallel, otherwise before (or after)
    private static $PARALLEL_COURSE_BUFFER = 31 * 24 * 60 * 60;

    /**
     * @return array
     * @throws dml_exception
     */
    public function get_parameter(): array {
        return [
                new parameter_course('course', false)
        ];
    }

    private function courseParticipation(int $courseid): array {
        $learnersCount = query_helper::query_learners_count($courseid, 'student');

        $courses = query_helper::query_courseparticipation($courseid);

        $tablePrevious = new table();
        $tablePrevious->set_header_local(['coursename', 'participated_before'],
            'lareport_learners');

        $tableParallel = new table();
        $tableParallel->set_header_local(['coursename', 'participating_now'],
            'lareport_learners');

        $courseStartdate = get_course($courseid)->startdate;

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
                $tablePrevious->add_row($row);
            } else if ($course->startdate < ($courseStartdate + self::$PARALLEL_COURSE_BUFFER)) {
                $tableParallel->add_row($row);
            } else {
                // course is happening after the current course, might be interesting for the future
            }
        }

        // TODO: shorten list, but make another page to see full list

        return [$tablePrevious, $tableParallel];
    }

    public function run(array $params): array {
        $courseid = (int) $params['course'];

        $headingTable = get_string('most_active_learners', 'lareport_learners');

        return array_merge(
            $this->courseParticipation($courseid),
            [ "<h2>{$headingTable}</h2>" ],
            learners_list::generate($courseid)
        );
    }

}