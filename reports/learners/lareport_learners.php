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
use lareport_learners\helper;
use lareport_learners\outputs\split;

class lareport_learners extends report_base {

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
        return helper::generateCourseParticipationList($courseid, 5);
    }

    public function run(array $params): array {
        $courseid = (int) $params['course'];

        $headingTable = get_string('most_active_learners', 'lareport_learners');

        return array_merge(
            helper::generateCourseParticipationList($courseid, 5),
            [ "<h2>{$headingTable}</h2>" ],
            helper::generateLearnersList($courseid)
        );
    }

}