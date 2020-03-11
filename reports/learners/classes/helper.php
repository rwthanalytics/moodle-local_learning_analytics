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

namespace lareport_learners;

use lareport_learners\outputs\splitter;
use local_learning_analytics\local\outputs\table;
use lareport_learners\query_helper;
use local_learning_analytics\router;
use moodle_url;
use paging_bar;
use local_learning_analytics\settings;

defined('MOODLE_INTERNAL') || die;

class helper {

    private static $userperfullpage = 20;
    private static $userpreview = 5;

    // If abs($other_course_startdate - $this_course_startdate) < $parallelcoursebuffer
    // they are considered being parallel, otherwise before (or after).
    private static $parallelcoursebuffer = 31 * 24 * 60 * 60;

    public static function generatecourseparticipationlist(int $courseid, int $limit = -1) {
        $privacythreshold = settings::get_config('dataprivacy_threshold');

        $learnerscount = query_helper::query_learners_count($courseid, 'student');
        $courses = query_helper::query_courseparticipation($courseid, $privacythreshold);

        $tableprevious = new table();
        $tableprevious->set_header_local(['coursename', 'participated_before'],
            'lareport_learners');

        $tableparallel = new table();
        $tableparallel->set_header_local(['coursename', 'participating_now'],
            'lareport_learners');

        $coursestartdate = get_course($courseid)->startdate;

        $previousrows = 0;
        $parallelrows = 0;
        $showexpandlink = false;

        foreach ($courses as $course) {
            $perc = round(100 * $course->users / $learnerscount);

            $row = [
                $course->fullname,
                table::fancyNumberCell(
                    $perc,
                    100,
                    'red',
                    $perc . '%'
                )
            ];

            if ($course->startdate < ($coursestartdate - self::$parallelcoursebuffer)) {
                if ($limit === -1 || $previousrows < $limit) {
                    $tableprevious->add_row($row);
                    $previousrows++;
                } else if ($previousrows >= $limit) {
                    $showexpandlink = true;
                }
            } else if ($course->startdate < ($coursestartdate + self::$parallelcoursebuffer)) {
                if ($limit === -1 || $parallelrows < $limit) {
                    $tableparallel->add_row($row);
                    $parallelrows++;
                } else if ($previousrows >= $limit) {
                    $showexpandlink = true;
                }
            }
        }

        if ($limit !== -1 && $showexpandlink) {
            $linktofulllist = router::report_page('learners', 'courseparticipation', ['course' => $courseid]);
            $tableprevious->add_show_more_row($linktofulllist);
            $tableparallel->add_show_more_row($linktofulllist);
        }

        $headingprevious = get_string('courses_heard_before', 'lareport_learners');
        $headingparallel = get_string('parallel_courses', 'lareport_learners');

        return [
            new splitter(
                ["<h3>{$headingprevious}</h3>", $tableprevious],
                ["<h3>{$headingparallel}</h3>", $tableparallel]
            ),
            get_string('above_lists_only_show_courses_with_more_than_threshold_users', 'lareport_learners', $privacythreshold)
        ];
    }
}