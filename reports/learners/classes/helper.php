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

use local_learning_analytics\local\outputs\splitter;
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
        $studentrolenames = explode(',', settings::get_config('student_rolenames'));
        $coursegroupby = get_config('local_learning_analytics', 'student_enrols_groupby');

        $coursestartdate = get_course($courseid)->startdate;
        $coursebeforecutoff = $coursestartdate - self::$parallelcoursebuffer;
        $courseparallelcutoff = $coursestartdate + self::$parallelcoursebuffer;

        $learnerscount = max(1, query_helper::query_learners_count($courseid, $studentrolenames));
        $courses = query_helper::query_courseparticipation(
            $courseid, $privacythreshold, $studentrolenames, $coursebeforecutoff, $courseparallelcutoff, $coursegroupby
        );

        $tableprevious = new table();
        $tableprevious->set_header_local(['coursename', 'participated_before'],
            'lareport_learners');

        $tableparallel = new table();
        $tableparallel->set_header_local(['coursename', 'participating_now'],
            'lareport_learners');

        $previousrowscount = 0;
        $parallelrowscount = 0;
        $showexpandlink = false;

        foreach ($courses as $course) {
            $perc = round(100 * $course->users / $learnerscount);
            
            $row = [
                format_string($course->fullname),
                table::fancyNumberCell(
                    $perc,
                    100,
                    'red',
                    $perc . '%'
                )
            ];

            if ($course->beforeparallel === '1') {
                if ($limit === -1 || $previousrowscount < $limit) {
                    $tableprevious->add_row($row);
                    $previousrowscount++;
                } else if ($previousrowscount >= $limit) {
                    $showexpandlink = true;
                }
            } else if ($course->beforeparallel === '2') {
                if ($limit === -1 || $parallelrowscount < $limit) {
                    $tableparallel->add_row($row);
                    $parallelrowscount++;
                } else if ($parallelrowscount >= $limit) {
                    $showexpandlink = true;
                }
            }
        }

        if ($limit !== -1 && $showexpandlink) {
            $linktofulllist = router::report_page('learners', 'courseparticipation', ['course' => $courseid]);
            $tableprevious->add_show_more_row($linktofulllist);
            $tableparallel->add_show_more_row($linktofulllist);
        }

        if ($previousrowscount === 0) {
            $tableprevious = get_string('no_courses_heard_before', 'lareport_learners');
        }
        if ($parallelrowscount === 0) {
            $tableparallel = get_string('no_courses_heard_in_parallel', 'lareport_learners');
        }

        $headingprevious = get_string('courses_heard_before', 'lareport_learners');
        $headingparallel = get_string('parallel_courses', 'lareport_learners');

        return [
            new splitter(
                ["<h3>{$headingprevious}</h3>", $tableprevious],
                ["<h3>{$headingparallel}</h3>", $tableparallel]
            )
        ];
    }
}
