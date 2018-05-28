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
use lareport_coursedashboard\query_helper;

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

    private function activiyOverWeeks(int $courseid) : array {
        $course = get_course($courseid);

        $date = new DateTime();
        $date->modify('-1 week');
        $now = $date->getTimestamp();

        $date->setTimestamp($course->startdate);
        $date->modify('Monday this week'); // get start of week

        $endOfLastWeek = new DateTime();
        $endOfLastWeek->modify('Sunday last week');

        $weeks = query_helper::query_weekly_activity($courseid);

        $weeksLastYear = query_helper::query_weekly_activity(72);

        $plot = new plot();
        $x = [];
        $ySessions = [];
        $yUsers = [];

        $yLYSessions = [];
        $yLYUsers = [];

        $texts = [];

        $shapes = [
            [ // Line showing the start of the lecture
                'type' => 'line',
                'xref' => 'x',
                'yref' => 'paper',
                'x0' => 0.5,
                'x1' => 0.5,
                'y0' => -0.07,
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
        foreach ($weeksLastYear as $weekLY) {
            $yMax = max($yMax, $weekLY->sessions);
        }
        $yMax = $yMax * 1.1;

        $xMin = -1;
        $xMax = 30;

        $tickVals = [];
        $tickText = [];

        $dateformat = get_string('strftimedate', 'langconfig');
        $thousandssep = get_string('thousandssep', 'langconfig');
        $decsep = get_string('decsep', 'langconfig');

        $strWeek = get_string('week', 'lareport_coursedashboard');
        $strLearners = get_string('learners', 'local_learning_analytics');
        $strSessions = get_string('sessions', 'local_learning_analytics');
        $strSessionsPerUser = get_string('sessions_per_user', 'lareport_coursedashboard');

        $date->modify(($xMin - 1) . ' week');

        for ($i = $xMin; $i <= $xMax; $i++) {
            $week = $weeks[$i] ?? new stdClass();
            $weekLY = $weeksLastYear[$i] ?? new stdClass();

            $weekNumber = ($i <= 0) ? ($i - 1) : $i;

            $opacity = 0.1; // opacity of background stripes

            $x[] = $i; //
            $tickVals[] = $i;
            $tickText[] = $weekNumber;

            $sessionCount = $week->sessions ?? 0;
            $yLYSessions[] = $weekLY->sessions ?? 0;
            $userCount = $week->users ?? 0;
            $yLYUsers[] = $weekLY->users ?? 0;

            $startOfWeekTimestamp = $date->getTimestamp();
            $date->modify('+6 days');

            if ($startOfWeekTimestamp < $now) {
                // date is in the past
                $opacity = 0.75;

                $ySessions[] = $sessionCount;
                $yUsers[] = $userCount;

                $weekstarttext = userdate($startOfWeekTimestamp, $dateformat);
                $weekendtext = userdate($date->getTimestamp(), $dateformat);
                $sessionsPerUser = ($userCount === 0) ? 0 : number_format(($sessionCount / $userCount), 1, $decsep, $thousandssep);
                $texts[] = "<b>{$strWeek} {$weekNumber}</b> ({$weekstarttext} - {$weekendtext})<br><br>{$userCount} {$strLearners}<br>{$sessionCount} {$strSessions}<br>{$sessionsPerUser} {$strSessionsPerUser}";
            }

            $date->modify('+1 day');

            $shapes[] = [
                'type' => 'rect',
                'xref' => 'x',
                'yref' => 'paper',
                'x0' => ($i - 0.46),
                'x1' => ($i + 0.46),
                'y0' => -0.07,
                'y1' => 1,
                'fillcolor' => '#ddd',
                'opacity' => $opacity,
                'line' => [ 'width' => 0 ],
                'layer' => 'below'
            ];
        }

        // sessions series
        $plot->add_series([
            'type' => 'scatter',
            'mode' => 'lines+markers',
            'name' => get_string('sessions', 'lareport_coursedashboard'),
            'x' => $x,
            'y' => $ySessions,
            'text' => $texts,
            'marker' => [ 'color' => 'rgb(31, 119, 180)' ],
            'line' => [ 'color' => 'rgb(31, 119, 180)' ],
            'hoverinfo' => 'text',
            'hoverlabel' => [
                'bgcolor' => '#eee',
                'font' => [
                    'size' => 15
                ]
            ]
        ]);
        $plot->add_series([
            'type' => 'scatter',
            'mode' => 'lines+markers',
            'name' => get_string('sessions_compare', 'lareport_coursedashboard'),
            'x' => $x,
            'y' => $yLYSessions,
            'marker' => [ 'color' => 'rgba(31, 119, 180, 0.35)' ],
            'line' => [ 'color' => 'rgba(31, 119, 180, 0.35)', 'dash' => 'dot' ],
            'hoverinfo' => 'none'
        ]);

        // users series
        $plot->add_series([
            'type' => 'scatter',
            'mode' => 'lines+markers',
            'name' => get_string('learners', 'lareport_coursedashboard'),
            'x' => $x,
            'y' => $yUsers,
            'marker' => [ 'color' => 'rgb(255, 127, 14)' ],
            'line' => [ 'color' => 'rgb(255, 127, 14)' ],
            'hoverinfo' => 'none'
        ]);
        $plot->add_series([
            'type' => 'scatter',
            'mode' => 'lines+markers',
            'name' => get_string('learners_compare', 'lareport_coursedashboard'),
            'x' => $x,
            'y' => $yLYUsers,
            'marker' => [ 'color' => 'rgba(255, 127, 14, 0.35)' ],
            'line' => [ 'color' => 'rgba(255, 127, 14, 0.35)', 'dash' => 'dot' ],
            'hoverinfo' => 'none'
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
            'range' => [ (-1 * $yMax * 0.01), $yMax ]
        ];

        $layout->shapes = $shapes;

        $plot->set_layout($layout);
        $plot->set_height(350);

        $heading1 = get_string('activity_over_weeks', 'lareport_coursedashboard');

        return [
            "<h2>{$heading1}</h2>",
            $plot
        ];
    }

    private function boxOutput(string $title, int $number, int $diff) {

        $diffText = $diff;
        if ($diff === 0) {
            $diffText = 'no difference';
        } else if ($diff > 0) {
            $diffText = '+' . $diff;
        }

        return [
            "{$title}: {$number} ({$diffText})<br />"
        ];
    }

    private function registeredUserCount(int $courseid) : array {
        $userCounts = query_helper::query_users($courseid);
        $total = $userCounts[0] + $userCounts[1];
        $diff = $userCounts[1];

        return $this->boxOutput('registered_users', $total, $diff);
    }

    private function clickCount(int $courseid) : array {
        $counts = query_helper::query_click_count($courseid);

        return $this->boxOutput('click_count', $counts[1], ($counts[1] - $counts[0]));
    }

    public function run(array $params): array {
        $courseid = (int) $params['course'];

        return array_merge(
            $this->activiyOverWeeks($courseid),
            $this->registeredUserCount($courseid),
            $this->clickCount($courseid)
        );
    }

}