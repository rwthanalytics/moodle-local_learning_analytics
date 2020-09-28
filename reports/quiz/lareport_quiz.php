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
use lareport_quiz\query_helper;
use local_learning_analytics\router;
use local_learning_analytics\settings;

class lareport_quiz extends report_base {

    public function run(array $params): array {
        global $USER, $OUTPUT;
        $courseid = $params['course'];
        $privacythreshold = settings::get_config('dataprivacy_threshold');

        $x = [];
        $tries = [];
        $users = [];
        $texts = [];

        $dbdata = query_helper::query_tries($courseid);
        $modinfo = get_fast_modinfo($courseid);
        $quizzes = $modinfo->instances['quiz'];
        foreach ($quizzes as $quizid => $cm) {
            $x[] = $cm->name;
            if (isset($dbdata[$quizid])) {
                $dbinfo = $dbdata[$quizid];
                $tries[] = $dbinfo->attempts;
                $users[] = $dbinfo->users;
            } else {
                $tries[] = 0;
                $users[] = 0;
            }
        }
        
        $plot = new plot();
        $plot->show_toolbar(false);
        $plot->add_series([
            'type' => 'bar',
            'x' => $x,
            'y' => $tries,
            'name' => 'Tries' // TODO lang
        ]);
        $plot->add_series([
            'type' => 'bar',
            'x' => $x,
            'y' => $users,
            'name' => 'Users' // TODO lang
        ]);
        $layout = new stdClass();
        $layout->margin = [
            'b' => 100
        ];
        $plot->set_layout($layout);
        $plot->set_height(300);

        return [
            $plot
        ];
    }

    public function params(): array {
        return [
            'course' => required_param('course', PARAM_INT)
        ];
    }
}