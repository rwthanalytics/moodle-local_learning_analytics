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
        $tablequiz->set_header(['Name der Aktivität', 'Nutzer', 'Versuche', 'Punkte aller Versuche (Ø)', 'Punkte 1. Versuch (Ø)']); // TODO lang

        $rows = [];
        $maxtries = 1;
        $maxusers = 1;

        $dbquizzes = query_helper::query_quiz($courseid);
        $modinfo = get_fast_modinfo($courseid);
        if (!isset($modinfo->instances['quiz'])) {
            // TODO Text about no quizzes...
            return [];
        }
        $quizzes = $modinfo->instances['quiz'];
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
            '<h3>Quizze</h3>',
            $tablequiz,
            $dataprivacynotice ? '<div>* Aus Datenschutzgründen werden Ergebnisse von weniger als 10 Nutzern nicht gezeigt.</div>' : '', // TODO lang
            '<hr>'
        ];
    }

    public function assignments(int $courseid, $privacythreshold) {
        $tableassign = new table('assigntable');
        $tableassign->set_header(['Name der Aktivität', 'Anzahl bewerteter Versuche', 'Durchschnittlich erreichte Punkte']); // TODO lang

        $rows = [];
        $maxhandins = 1;

        $dbassignments = query_helper::query_assignment($courseid);
        $modinfo = get_fast_modinfo($courseid);
        if (!isset($modinfo->instances['assign'])) {
            // TODO Text about no assignments...
            return [];
        }
        $assignments = $modinfo->instances['assign'];
        $hiddentext = get_string('hiddenwithbrackets');
        foreach ($assignments as $assignid => $cm) {
            if (!$cm->uservisible) {
                continue;
            }
            $name = "<a href='{$cm->url}'>{$cm->name}</a>";
            if (!$cm->visible) {
                $name = "<span class='dimmed_text'>{$name} {$hiddentext}</span>";
            }
            if (isset($dbassignments[$assignid])) {
                $assigninfo = $dbassignments[$assignid];
                $rows[] = [$name, $assigninfo->handins, $assigninfo->grade];
                $maxhandins = max($maxhandins, $assigninfo->handins);
            } else {
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
            '<h3>Aufgaben</h3>',
            $tableassign,
            $dataprivacynotice ? '<div>* Aus Datenschutzgründen werden Ergebnisse von weniger als 10 Nutzern nicht gezeigt.</div>' : '' // TODO lang
        ];
    }

    public function run(array $params): array {
        global $USER, $OUTPUT;
        $courseid = $params['course'];
        $privacythreshold = settings::get_config('dataprivacy_threshold');

        return array_merge(
            self::quizzes($courseid, $privacythreshold),
            self::assignments($courseid, $privacythreshold)
        );
    }

    public function params(): array {
        return [
            'course' => required_param('course', PARAM_INT)
        ];
    }
}