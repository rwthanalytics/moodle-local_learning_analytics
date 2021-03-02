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
 * Version info for the Activities report
 *
 * @package     local_learning_analytics
 * @copyright   Lehr- und Forschungsgebiet Ingenieurhydrologie - RWTH Aachen University
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

use local_learning_analytics\local\outputs\plot;
use local_learning_analytics\local\outputs\table;
use local_learning_analytics\report_base;
use lareport_quiz_assign\query_helper;
use local_learning_analytics\router;
use local_learning_analytics\settings;

class lareport_quiz_assign extends report_base {

    public function quizzes(int $courseid, $privacythreshold) {
        $tablequiz = new table('quiztable');
        $tablequiz->set_header_local(['quiz', 'participants', 'attempts', 'overall_average', 'overall_average_first_try'], 'lareport_quiz_assign');

        $rows = [];
        $maxtries = 1;
        $maxusers = 1;

        $dbquizzes = query_helper::query_quiz($courseid);
        // $modinfo = get_fast_modinfo($courseid);
        // if (!isset($modinfo->instances['quiz'])) {
        //     return [];
        // }
        // $quizzes = $modinfo->instances['quiz'];
        $quizzes = \local_learning_analytics\demo::data('quiz_assign', 'quiz_cms');
        $hiddentext = get_string('hiddenwithbrackets');
        foreach ($quizzes as $quizid => $cm) {
            if (!$cm->uservisible) {
                continue;
            }
            $name = "<a href='{$cm->url}'>{$cm->name}</a>";
            if (!$cm->visible) {
                $name = "<span class='dimmed_text'>{$name} {$hiddentext}</span>";
            }
            if (isset($dbquizzes[$quizid])) {
                $quizinfo = $dbquizzes[$quizid];
                $rows[] = [$name, $quizinfo->attempts, $quizinfo->users, $quizinfo->result, $quizinfo->firsttryresult];
                $maxtries = max($maxtries, $quizinfo->attempts);
                $maxusers = max($maxusers, $quizinfo->users);
            } else {
                $rows[] = [$name, 0, 0, 0, 0];
            }
        }

        $dataprivacynotice = false;
        foreach ($rows as $row) {
            if ($row[2] < $privacythreshold) {
                $tablequiz->add_row([
                    $row[0], "< {$privacythreshold}", '*', '*', '*'
                ]);
                $dataprivacynotice = true;
            } else {
                $tablequiz->add_row([
                    $row[0],
                    table::fancynumbercellcolored((int) $row[2], $maxusers, '#1f77b4'),
                    table::fancynumbercellcolored((int) $row[1], $maxtries, '#ff7f0e'),
                    table::fancynumbercellcolored(min(1, $row[3]), 1, '#2ca02c', format_float(100 * $row[3], 1) . '%'),
                    table::fancynumbercellcolored(min(1, $row[4]), 1, '#bcbd22', format_float(100 * $row[4], 1) . '%')
                ]);
            }
        }

        return [
            '<h3>' . get_string('quizzes', 'lareport_quiz_assign') . '</h3>',
            $tablequiz,
            $dataprivacynotice ? '<div>* ' . get_string('data_privacy_note', 'lareport_quiz_assign', $privacythreshold) . '</div>' : '',
            '<hr>'
        ];
    }

    public function assignments(int $courseid, $privacythreshold) {
        $tableassign = new table('assigntable');
        $tableassign->set_header_local(['assignment', 'graded_submissions', 'overall_average'], 'lareport_quiz_assign');

        $rows = [];
        $maxhandins = 1;

        $dbassignments = query_helper::query_assignment($courseid);
        // $modinfo = get_fast_modinfo($courseid);
        // if (!isset($modinfo->instances['assign'])) {
        //     return [];
        // }
        // $assignments = $modinfo->instances['assign'];
        $assignments = \local_learning_analytics\demo::data('quiz_assign', 'assign_cms');
        $hiddentext = get_string('hiddenwithbrackets');
        foreach ($assignments as $assignid => $cm) {
            if (!$cm->uservisible) {
                continue;
            }
            $name = "<a href='{$cm->url}'>{$cm->name}</a>";
            if (!$cm->visible) {
                $name = "<span class='dimmed_text'>{$name} {$hiddentext}</span>";
            }
            if (isset($dbassignments[$assignid])) { // there is at least one graded assignment
                $assigninfo = $dbassignments[$assignid];
                $rows[] = [$name, $assigninfo->handins, $assigninfo->grade];
                $maxhandins = max($maxhandins, $assigninfo->handins);
            } else { // there is no graded assignment
                $rows[] = [$name, 0, 0, 0, 0];
            }
        }

        $dataprivacynotice = false;
        foreach ($rows as $row) {
            if ($row[1] < $privacythreshold) {
                $tableassign->add_row([
                    $row[0], "< {$privacythreshold}", '*'
                ]);
                $dataprivacynotice = true;
            } else {
                $tableassign->add_row([
                    $row[0],
                    table::fancynumbercellcolored((int) $row[1], $maxhandins, '#ff7f0e'),
                    table::fancynumbercellcolored($row[2], 1, '#2ca02c', format_float(100 * $row[2], 1) . '%')
                ]);
            }
        }

        return [
            '<h3>' . get_string('assignments', 'lareport_quiz_assign') . '</h3>',
            $tableassign,
            $dataprivacynotice ? '<div>* ' . get_string('data_privacy_note', 'lareport_quiz_assign', $privacythreshold) . '</div>' : '',
        ];
    }

    public function run(array $params): array {
        global $USER, $OUTPUT;
        $courseid = $params['course'];
        $privacythreshold = settings::get_config('dataprivacy_threshold');

        $quizpart = self::quizzes($courseid, $privacythreshold);
        $assignpart = self::assignments($courseid, $privacythreshold);

        $introtext = get_string('introduction', 'lareport_quiz_assign');
        if (count($quizpart) === 0 && count($assignpart) === 0) {
            $introtext .= ' ' . get_string('introduction_no_both', 'lareport_quiz_assign');
        } else if (count($quizpart) === 0) {
            $introtext .= ' ' . get_string('introduction_no_quizzes', 'lareport_quiz_assign');
        } else if (count($assignpart) === 0) {
            $introtext .= ' ' . get_string('introduction_no_assignments', 'lareport_quiz_assign');
        }

        return array_merge(
            [
                self::heading(get_string('pluginname', 'lareport_quiz_assign')),
                "<p>{$introtext}</p>"
            ],
            $quizpart,
            $assignpart
        );
    }

    public function params(): array {
        return [
            'course' => required_param('course', PARAM_INT)
        ];
    }
}