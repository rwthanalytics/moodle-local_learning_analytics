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
use lareport_grades\regression;

class lareport_grades extends report_base {

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

        $gradeItem = $DB->get_record('grade_items', [
            'courseid' => $courseid,
            'itemtype' => 'course'
        ], 'id, grademin, grademax, scaleid');

        $gradeItemsId = $gradeItem->id;

        $yMin = $gradeItem->grademin;
        $yMax = $gradeItem->grademax;
        $yDiff = $yMax - $yMin;

        $scaleId = $gradeItem->scaleid;

        $scale = null;
        if ($scaleId !== NULL) {
            $scaleRow = $DB->get_record('scale', ['id' => $scaleId], 'scale');
            $scale = array_map('trim', explode(',', $scaleRow->scale));
        }

        $query = <<<SQL
        SELECT
            u.id, 
            u.firstname,
            u.lastname,
            COALESCE(su.hits, 0) hits,
            COUNT(ses.id) sessions,
            g.finalgrade
        FROM mdl_user u
        # enroled users
        JOIN mdl_user_enrolments ue
            ON ue.userid = u.id
        JOIN mdl_enrol e
            ON e.id = ue.enrolid
            
        # get hits
        LEFT JOIN mdl_local_learning_analytics_sum su
            ON su.userid = u.id
            AND su.courseid = e.courseid
        LEFT JOIN mdl_local_learning_analytics_ses ses
            ON ses.summaryid = su.id
        
        # get grades
        LEFT JOIN mdl_grade_grades g
            ON g.itemid = ?
            AND g.userid = u.id
        
        # only students
        JOIN mdl_context c
            ON c.instanceid = e.courseid
            AND c.contextlevel = 50
        JOIN mdl_role_assignments ra
            ON ra.userid = u.id
            AND ra.contextid = c.id
        JOIN mdl_role r
            ON ra.roleid = r.id
            AND r.shortname = 'student'
        
        WHERE u.deleted = 0
            AND e.courseid = ?
            AND g.finalgrade IS NOT NULL
        GROUP BY u.id
        ORDER BY sessions;
SQL;

        $students = $DB->get_records_sql($query, [$gradeItemsId, $courseid]);

        $plot = new plot();
        $xOrig = [];
        $yOrig = [];
        $xRandomized = [];
        $yRandomized = [];

        $xMin = 0;
        $xMax = end($students)->sessions * 1.05;
        $xDiff = $xMax - $xMin;

        $random_jitter = 0.02; // 0 -> no random, 1 -> super random (0.01-0.05 is reasonable)

        foreach ($students as $student) {
            $x = $student->sessions;
            $y = $student->finalgrade;

            $xOrig[] = $x;
            $xRandomized[] = $x + ($random_jitter * $xDiff * rand(-100, 100) / 100);

            $yOrig[] = $y;
            $yRandomized[] = $y + ($random_jitter * $yDiff * rand(-100, 100) / 100);
        }
        $plot->add_series([
            'type' => 'scatter',
            'mode' => 'markers',
            'name' => get_string('scatter_students', 'lareport_grades'),
            'x' => $xRandomized,
            'y' => $yRandomized,
            'marker' => [
                'size' => 12,
                'color' => 'rgba(102, 181, 171, 0.8)'
            ],
            'hoverinfo' => 'none'
        ]);

        $eq = regression::linear($xOrig, $yOrig);

        $plot->add_series([
            'type' => 'scatter',
            'mode' => 'lines',
            'name' => get_string('trend_line', 'lareport_grades'),
            'x' => [$xMin, $xMax],
            'y' => [
                ($eq['c'] + $xMin * $eq['m']),
                ($eq['c'] + $xMax * $eq['m'])
            ],
            'hoverinfo' => 'none'
        ]);

        $layout = new stdClass();
        $layout->xaxis = [
            'title' => get_string('number_of_sessions', 'lareport_grades'),
            'range' => [ 0, $xMax * 1.05 ]
        ];
        $layout->yaxis = [
            'title' => get_string('course_grade', 'lareport_grades'),
            'range' => [ 0, $yMax * 1.05 ]
        ];
        if ($scale !== NULL) {
            $layout->yaxis['tickvals'] = range($yMin, $yMax);
            $layout->yaxis['ticktext'] = $scale;
        }
        $layout->margin = ['t' => 10];

        $plot->set_layout($layout);
        $plot->set_height(400);

        $heading1 = get_string('correlation_of_grades_and_work', 'lareport_grades');

        return [
            "<h2>{$heading1}</h2>",
            $plot
        ];
    }

}