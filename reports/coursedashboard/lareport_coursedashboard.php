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

    const X_MIN = -1;
    const X_MAX = 30;

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
                    'width' => 1.5
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

        $xMin = self::X_MIN;
        $xMax = self::X_MAX;

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

        $lastWeekInPast = -100;

        for ($i = $xMin; $i <= $xMax; $i++) {
            $week = $weeks[$i] ?? new stdClass();
            $weekLY = $weeksLastYear[$i] ?? new stdClass();

            $weekNumber = ($i <= 0) ? ($i - 1) : $i;

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
                $ySessions[] = $sessionCount;
                $yUsers[] = $userCount;

                $weekstarttext = userdate($startOfWeekTimestamp, $dateformat);
                $weekendtext = userdate($date->getTimestamp(), $dateformat);
                $sessionsPerUser = ($userCount === 0) ? 0 : number_format(($sessionCount / $userCount), 1, $decsep, $thousandssep);
                $texts[] = "<b>{$strWeek} {$weekNumber}</b> ({$weekstarttext} - {$weekendtext})<br><br>{$sessionCount} {$strSessions}<br>{$userCount} {$strLearners}<br>{$sessionsPerUser} {$strSessionsPerUser}";
                $lastWeekInPast = $i;
            }

            $date->modify('+1 day');

            $shapes[] = [
                'type' => 'line',
                'xref' => 'x',
                'yref' => 'paper',
                'x0' => ($i - 0.5),
                'x1' => ($i - 0.5),
                'y0' => -0.07,
                'y1' => 1,
                'line' => [ 'color' => '#aaa', 'width' => 1 ],
                'layer' => 'below'
            ];
        }

        $shapes[] = [
            'type' => 'rect',
            'xref' => 'x',
            'yref' => 'paper',
            'x0' => (self::X_MIN - 0.5),
            'x1' => ($lastWeekInPast + 0.5),
            'y0' => -0.07,
            'y1' => 1,
            'opacity' => '0.25',
            'fillcolor' => '#ddd',
            'line' => [ 'width' => 0 ],
            'layer' => 'below'
        ];
        if ($lastWeekInPast !== self::X_MIN && $lastWeekInPast !== self::X_MAX) {
            $shapes[] = [ // Line shows in which week are currently are
                'type' => 'line',
                'xref' => 'x',
                'yref' => 'paper',
                'x0' => ($lastWeekInPast + 0.5),
                'x1' => ($lastWeekInPast + 0.5),
                'y0' => -0.07,
                'y1' => 1,
                'line' => [
                    'color' => 'rgb(0, 0, 0)',
                    'width' => 1,
                    'dash' => 'dot'
                ]
            ];
        }

        // current course
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
            ],
            'legendgroup' => 'a'
        ]);
        $plot->add_series([
            'type' => 'scatter',
            'mode' => 'lines+markers',
            'name' => get_string('learners', 'lareport_coursedashboard'),
            'x' => $x,
            'y' => $yUsers,
            'marker' => [ 'color' => 'rgb(255, 127, 14)' ],
            'line' => [ 'color' => 'rgb(255, 127, 14)' ],
            'hoverinfo' => 'none',
            'legendgroup' => 'a'
        ]);

        // compare course
        $plot->add_series([
            'type' => 'scatter',
            'mode' => 'lines+markers',
            'name' => get_string('sessions_compare', 'lareport_coursedashboard'),
            'x' => $x,
            'y' => $yLYSessions,
            'marker' => [ 'color' => 'rgb(31, 119, 180)' ],
            'line' => [ 'color' => 'rgb(31, 119, 180)', 'dash' => 'dot' ],
            'hoverinfo' => 'none',
            'opacity' => 0.35,
            'legendgroup' => 'b'
        ]);
        $plot->add_series([
            'type' => 'scatter',
            'mode' => 'lines+markers',
            'name' => get_string('learners_compare', 'lareport_coursedashboard'),
            'x' => $x,
            'y' => $yLYUsers,
            'marker' => [ 'color' => 'rgb(255, 127, 14)' ],
            'line' => [ 'color' => 'rgb(255, 127, 14)', 'dash' => 'dot' ],
            'hoverinfo' => 'none',
            'opacity' => 0.35,
            'legendgroup' => 'b'
        ]);

        $layout = new stdClass();
        $layout->margin = [
            't' => 10,
            'r' => 0,
            'l' => 40,
            'b' => 40
        ];
        $layout->xaxis = [
            'ticklen' => 0,
            'showgrid' => false,
            'zeroline' => false,
            'range' => [ ($xMin - 0.5), ($xMax + 0.5) ],
            'tickmode' => 'array',
            'tickvals' => $tickVals,
            'ticktext' => $tickText,
            'fixedrange' => true
        ];
        $layout->yaxis = [
            'range' => [ (-1 * $yMax * 0.01), $yMax ],
            'fixedrange' => true
        ];
        $layout->showlegend = true;
        $layout->legend = [
            'bgcolor' => 'rgba(255, 255, 255, 0.8)',
            'orientation' => 'v',
            'xanchor' => 'right',
            'yanchor' => 'top',
            'x' => (1 - 0.0021),
            'y' => 1,
            'bordercolor' => 'rgba(255, 255, 255, 0)',
            'borderwidth' => 10,
            'traceorder' => 'grouped'
        ];

        $layout->shapes = $shapes;

        $plot->set_layout($layout);
        $plot->set_height(300);

        $heading1 = get_string('activity_over_weeks', 'lareport_coursedashboard');

        return [
            "<h2>{$heading1}</h2>",
            $plot
        ];
    }

    private static $icons = [
        'registered_users' => '<svg xmlns="http://www.w3.org/2000/svg" width="110" height="110" viewBox="0 0 24 24"><path d="M0 0h24v24H0z" fill="none"/><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>',
        'active_learners' => '<svg xmlns="http://www.w3.org/2000/svg" width="110" height="110" viewBox="0 0 24 24">
            <path d="M0 0h24v24H0z" fill="none"/>
            <path d="M21 3H3c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h5v2h8v-2h5c1.1 0 1.99-.9 1.99-2L23 5c0-1.1-.9-2-2-2zm0 14H3V5h18v12z"/>
        </svg>',
        'click_count' => '<svg xmlns="http://www.w3.org/2000/svg" width="110" height="110" viewBox="0 0 24 24">
            <path d="M3.5 18.49l6-6.01 4 4L22 6.92l-1.41-1.41-7.09 7.97-4-4L2 16.99z"/>
            <path fill="none" d="M0 0h24v24H0z"/>
        </svg>'
    ];

    private function boxOutput(string $title, int $number, int $diff) {

        $diffTriangle = '';

        $diffText = $diff;
        if ($diff === 0) {
            $diffText = 'no difference';
        } else if ($diff > 0) {
            $diffText = '+' . $diff;
            $diffTriangle = '<span class="dashboardbox-change-up">▲</span>';
        } else {
            $diffTriangle = '<span class="dashboardbox-change-down">▼</span>';
        }

        $icon = self::$icons[$title];

        // TODO: for new bootstrap version -> make pull-left float-left
        return "
            <div class='col-sm-4'>
                <div class='dashboardbox'>
                    <div class='dashboardbox-icon'>
                        {$icon}
                    </div>
                    <div class='dashboardbox-content'>
                        <div>{$title} <span class='dashboardbox-timespan'>(last 7 days)</span></div>
                        <div class='dashboardbox-title'>{$number}</div>
                        <div class='dashboardbox-change'>{$diffTriangle} {$diffText}</div>
                    </div>
                </div>
            </div>
        ";
    }

    private function registeredUserCount(int $courseid) : array {
        $userCounts = query_helper::query_users($courseid);
        $total = $userCounts[0] + $userCounts[1];
        $diff = $userCounts[1];

        return [
            $this->boxOutput('registered_users', $total, $diff)
        ];
    }

    private function clickCount(int $courseid) : array {
        $counts = query_helper::query_click_count($courseid);

        $learners = $counts['users'];
        $hits = $counts['hits'];

        return [
            $this->boxOutput('active_learners', $learners[1], ($learners[1] - $learners[0])),
            $this->boxOutput('click_count', $hits[1], ($hits[1] - $hits[0]))
        ];
    }

    public function run(array $params): array {
        global $PAGE;
        $PAGE->requires->css('/local/learning_analytics/reports/coursedashboard/static/styles.css');

        $courseid = (int) $params['course'];

        return array_merge(
            $this->activiyOverWeeks($courseid),
            ["<div class='container-fluid'><div class='row'>"],
            $this->registeredUserCount($courseid),
            $this->clickCount($courseid),
            ["</div></div>"]
        );
    }

}