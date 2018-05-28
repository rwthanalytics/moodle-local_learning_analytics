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
 * Version info for the Sections report
 *
 * @package     local_learning_analytics
 * @copyright   2018 Lehr- und Forschungsgebiet Ingenieurhydrologie - RWTH Aachen University
 * @author      Marcel Behrmann <behrmann@lfi.rwth-aachen.de>
 * @author      Thomas Dondorf <dondorf@lfi.rwth-aachen.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

use local_learning_analytics\local\outputs\plot;
use local_learning_analytics\local\parameter\parameter_course;
use local_learning_analytics\report_base;

class lareport_coursedashboard extends report_base {

    /**
     * @return array
     * @throws dml_exception
     */
    public function get_parameter(): array {
        return [
            new parameter_course('course')
        ];
    }

    public function run(array $params): array {
        global $DB;

        $courseid = (int) $params['course'];

        $course = get_course($courseid);

        $startdate = new DateTime();
        $startdate->setTimestamp($course->startdate);
        $startdate->modify('Monday this week'); // get start of week

        $mondayTimestamp = $startdate->format('U');

        $query = <<<SQL
        SELECT
            (FLOOR((ses.firstaccess - {$mondayTimestamp}) / (7 * 60 * 60 * 24)) + 1) AS week,
            COUNT(*) sessions,
            COUNT(DISTINCT su.userid) users,
            su.*,
            ses.*
        FROM {local_learning_analytics_sum} su
        JOIN {local_learning_analytics_ses} ses
            ON su.id = ses.summaryid
        WHERE su.courseid = ?
        GROUP BY week
        #    HAVING week > 0
        ORDER BY week;
SQL;

        $weeks = $DB->get_records_sql($query, [$courseid]);

        $plot = new plot();
        $x = [];
        $ySessions = [];
        $yUsers = [];
        $shapes = [
            [ // Line showing the start of the lecture
                'type' => 'line',
                'xref' => 'x',
                'yref' => 'paper',
                'x0' => 0.5,
                'x1' => 0.5,
                'y0' => 0,
                'y1' => 1,
                'line' => [
                    'color' => 'rgb(0, 0, 0)',
                    'width' => 1,
                    'dash' => 'dot'
                ]
            ]
        ];

        $yMax = 1;

        foreach ($weeks as $week) {
            $yMax = max($yMax, $week->sessions);
        }
        $yMax = $yMax * 1.1;

        $xMin = -1;
        $xMax = 30;

        $lastweekIndex = 0;

        $tickVals = [];
        $tickText = [];

        for ($i = $xMin; $i <= $xMax; $i++) {
            $week = $weeks[$i] ?? new stdClass();

            $x[] = $i; // $startdate->format('Y-m-d H:i:s');
            $tickVals[] = $i;
            $tickText[] = ($i <= 0) ? ($i - 1) : $i;

            $ySessions[] = $week->sessions ?? 0;
            $yUsers[] = $week->users ?? 0;
            $shapes[] = [
                'type' => 'rect',
                'xref' => 'x',
                'yref' => 'paper',
                'x0' => ($i - 0.45),
                'x1' => ($i + 0.45),
                'y0' => 0,
                'y1' => 1,
                'fillcolor' => '#eee',
                'opacity' => 0.2,
                'line' => [ 'width' => 0 ]
            ];
        }

        // TODO, show dates: $startdate->format('Y-m-d H:i:s'); $startdate->modify('+1 week');

        $plot->add_series([
            'type' => 'scatter',
            'mode' => 'lines+markers',
            'name' => get_string('sessions', 'lareport_coursedashboard'),
            'x' => $x,
            'y' => $ySessions
        ]);
        $plot->add_series([
            'type' => 'scatter',
            'mode' => 'lines+markers',
            'name' => get_string('learners', 'lareport_coursedashboard'),
            'x' => $x,
            'y' => $yUsers
        ]);

        $layout = new stdClass();
        $layout->margin = ['t' => 10];
        $layout->xaxis = [
            'ticklen' => 0,
            'showgrid' => false,
            'zeroline' => false,
            'range' => [ ($xMin - 0.5), ($xMax + 0.5) ],
            'tickmode' => 'array',
            'tickvals' => $tickVals,
            'ticktext' => $tickText,
            'title' => get_string('week', 'lareport_coursedashboard')
        ];
        $layout->yaxis = [
            'range' => [ 0, $yMax ]
        ];

        $layout->shapes = $shapes;

        $plot->set_layout($layout);
        $plot->set_height(400);

        $heading1 = get_string('activity_over_weeks', 'lareport_coursedashboard');

        return [
            "<h2>{$heading1}</h2>",
            $plot
        ];
    }

}