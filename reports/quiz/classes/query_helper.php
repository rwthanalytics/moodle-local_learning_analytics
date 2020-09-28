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

namespace lareport_quiz;

defined('MOODLE_INTERNAL') || die();

use local_learning_analytics\local\outputs\plot;
use local_learning_analytics\local\outputs\table;
use local_learning_analytics\report_base;
use context_course;

class query_helper {

    public static function query_tries(int $courseid): array {
        global $DB;

        $query = <<<SQL
        SELECT
            qa.quiz,
            COUNT(DISTINCT userid) users,
            COUNT(1) attempts
        FROM {grade_items} gi
        JOIN {quiz_attempts} qa
        ON qa.quiz = gi.iteminstance
            AND qa.state = 'finished'
        WHERE gi.courseid = ?
            AND gi.itemtype = 'mod'
            AND gi.itemmodule = 'quiz'
        GROUP BY qa.quiz
SQL;

        return $DB->get_records_sql($query, [$courseid]);
    }
}
