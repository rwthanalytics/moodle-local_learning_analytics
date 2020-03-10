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
 * @copyright   Lehr- und Forschungsgebiet Ingenieurhydrologie - RWTH Aachen University
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

use local_learning_analytics\local\outputs\plot;
use local_learning_analytics\report_base;
use lareport_coursedashboard\query_helper;

class lareport_coursedashboard extends report_base {

    const X_MIN = -1;
    const X_MAX = 30;

    private function activiyoverweeks(int $courseid, int $prevcourseid) : array {
        $course = get_course($courseid);

        $date = new DateTime();
        $date->modify('-1 week');
        $now = $date->getTimestamp();

        $date->setTimestamp($course->startdate);
        $date->modify('Monday this week'); // Get start of week.

        $endoflastweek = new DateTime();
        $endoflastweek->modify('Sunday last week');

        $weeks = query_helper::query_weekly_activity($courseid);

        // TODO Set to [] if we dont want to compare.
        $weekslastyear = [];
        if ($prevcourseid !== -1) {
            $weekslastyear = query_helper::query_weekly_activity($prevcourseid);
        }

        $plot = new plot();
        $x = [];
        $yclicks = [];
        $ylyclicks = [];

        $texts = [];

        $shapes = [
            [ // Line showing the start of the lecture.
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

        $ymax = 1;

        foreach ($weeks as $week) {
            $ymax = max($ymax, $week->clicks);
        }
        foreach ($weekslastyear as $weekly) {
            $ymax = max($ymax, $weekly->clicks);
        }
        $ymax = $ymax * 1.1;

        $xmin = self::X_MIN;
        $xmax = self::X_MAX;

        $tickvals = [];
        $ticktext = [];

        $dateformat = get_string('strftimedate', 'langconfig');
        $thousandssep = get_string('thousandssep', 'langconfig');
        $decsep = get_string('decsep', 'langconfig');

        $tstrweek = get_string('week', 'lareport_coursedashboard');
        $strclicks = get_string('clicks', 'lareport_coursedashboard');

        $date->modify(($xmin - 1) . ' week');

        $lastweekinpast = -100;

        for ($i = $xmin; $i <= $xmax; $i++) {
            $week = $weeks[$i] ?? new stdClass();
            $weekly = $weekslastyear[$i] ?? new stdClass();

            $weeknumber = ($i <= 0) ? ($i - 1) : $i;

            $x[] = $i;
            $tickvals[] = $i;
            $ticktext[] = $weeknumber;

            $clickcount = $week->clicks ?? 0;
            $ylyclicks[] = $weekly->clicks ?? 0;

            $startofweektimestamp = $date->getTimestamp();
            $date->modify('+6 days');

            if ($startofweektimestamp < $now) {
                // Date is in the past.
                $yclicks[] = $clickcount;

                $weekstarttext = userdate($startofweektimestamp, $dateformat);
                $weekendtext = userdate($date->getTimestamp(), $dateformat);
                $texts[] = "<b>{$tstrweek} {$weeknumber}</b> ({$weekstarttext} - {$weekendtext})<br><br>{$clickcount} {$strclicks}";
                $lastweekinpast = $i;
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
            'x1' => ($lastweekinpast + 0.5),
            'y0' => -0.07,
            'y1' => 1,
            'opacity' => '0.25',
            'fillcolor' => '#ddd',
            'line' => [ 'width' => 0 ],
            'layer' => 'below'
        ];
        if ($lastweekinpast !== self::X_MIN && $lastweekinpast !== self::X_MAX) {
            $shapes[] = [ // Line shows in which week are currently are.
                'type' => 'line',
                'xref' => 'x',
                'yref' => 'paper',
                'x0' => ($lastweekinpast + 0.5),
                'x1' => ($lastweekinpast + 0.5),
                'y0' => -0.07,
                'y1' => 1,
                'line' => [
                    'color' => 'rgb(0, 0, 0)',
                    'width' => 1,
                    'dash' => 'dot'
                ]
            ];
        }

        // Current course.
        $plot->add_series([
            'type' => 'scatter',
            'mode' => 'lines+markers',
            'name' => get_string('clicks', 'lareport_coursedashboard'),
            'x' => $x,
            'y' => $yclicks,
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

        // Compare course.
        if (count($weekslastyear) !== 0) {
            $plot->add_series([
                'type' => 'scatter',
                'mode' => 'lines+markers',
                'name' => get_string('clicks_compare', 'lareport_coursedashboard'),
                'x' => $x,
                'y' => $ylyclicks,
                'marker' => [ 'color' => 'rgb(31, 119, 180)' ],
                'line' => [ 'color' => 'rgb(31, 119, 180)', 'dash' => 'dot' ],
                'hoverinfo' => 'none',
                'opacity' => 0.35,
                'legendgroup' => 'b'
            ]);
        }

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
            'range' => [ ($xmin - 0.5), ($xmax + 0.5) ],
            'tickmode' => 'array',
            'tickvals' => $tickvals,
            'ticktext' => $ticktext,
            'fixedrange' => true
        ];
        $layout->yaxis = [
            'range' => [ (-1 * $ymax * 0.01), $ymax ],
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

        return [
            $plot
        ];
    }

    // Icons from: https://material.io/resources/icons/.
    private static $icons = [
        'registered_users' => '<svg xmlns="http://www.w3.org/2000/svg" width="110" height="110"viewBox="0 0 24 24">
        <path d="M0 0h24v24H0z" fill="none"/><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8
        0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0
        2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29
        0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>',
        'active_learners' => '<svg xmlns="http://www.w3.org/2000/svg" width="110" height="110" viewBox="0 0 24 24">
            <path d="M0 0h24v24H0z" fill="none"/>
            <path d="M21 3H3c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h5v2h8v-2h5c1.1 0 1.99-.9 1.99-2L23 5c0-1.1-.9-2-2-2zm0
            14H3V5h18v12z"/>
        </svg>',
        'click_count' => '<svg xmlns="http://www.w3.org/2000/svg" width="110" height="110" viewBox="0 0 24 24">
            <path d="M3.5 18.49l6-6.01 4 4L22 6.92l-1.41-1.41-7.09 7.97-4-4L2 16.99z"/>
            <path fill="none" d="M0 0h24v24H0z"/>
        </svg>',
        'mobile_use' => '<svg xmlns="http://www.w3.org/2000/svg" width="90" height="90" viewBox="0 0 24 24" style="margin-top:10px">
            <path d="M15.5 1h-8C6.12 1 5 2.12 5 3.5v17C5 21.88 6.12 23 7.5 23h8c1.38 0 2.5-1.12 2.5-2.5v-17C18 2.12 16.88 1 15.5
            1zm-4 21c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5zm4.5-4H7V4h9v14z"/>
            <path d="M0 0h24v24H0z" fill="none"/>
        </svg>',
        'most_clicked_module' => '<svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" viewBox="0 0 24 24"
        style="margin-top:15px">
            <path d="M13.5.67s.74 2.65.74 4.8c0 2.06-1.35 3.73-3.41 3.73-2.07 0-3.63-1.67-3.63-3.73l.03-.36C5.21 7.51 4 10.62 4
            14c0 4.42 3.58 8 8 8s8-3.58 8-8C20 8.61 17.41 3.8 13.5.67zM11.71 19c-1.78 0-3.22-1.4-3.22-3.14 0-1.62 1.05-2.76
            2.81-3.12 1.77-.36 3.6-1.21 4.62-2.58.39 1.29.59 2.65.59 4.04 0 2.65-2.15 4.8-4.8 4.8z"/>
            <path d="M0 0h24v24H0z" fill="none"/>
        </svg>'
    ];

    private function boxoutputraw(string $titlekey, string $maintext, string $change, int $courseid, string $report = null) {
        $icon = self::$icons[$titlekey];

        $titlestr = get_string($titlekey, 'lareport_coursedashboard');
        if ($report !== null) {
            $link = new moodle_url("/local/learning_analytics/index.php/reports/{$report}", ['course' => $courseid]);
            $titlestr = "<a href='{$link}'>{$titlestr}</a>";
            $icon = "<a href='{$link}'>{$icon}</a>";
        }

        $appendedtext = ($titlekey === 'registered_users') ? '' : " <span class='dashboardbox-timespan'>(last 7 days)</span>";
        return "
            <div class='col-sm-4'>
                <div class='dashboardbox'>
                    <div class='dashboardbox-icon'>
                        {$icon}
                    </div>
                    <div class='dashboardbox-content'>
                        <div>{$titlestr}{$appendedtext}</div>
                        <div class='dashboardbox-title'>{$maintext}</div>
                        <div class='dashboardbox-change'>{$change}</div>
                    </div>
                </div>
            </div>
        ";
    }

    private function boxoutput(string $title, int $number, int $diff, int $courseid, string $report = null) {
        $difftriangle = '';
        $difftext = $diff;
        if ($diff === 0) {
            $difftext = 'no difference';
        } else if ($diff > 0) {
            $difftext = '+' . $diff;
            $difftriangle = '<span class="dashboardbox-change-up">▲</span>';
        } else {
            $difftriangle = '<span class="dashboardbox-change-down">▼</span>';
        }

        return $this->boxoutputraw($title, $number, "{$difftriangle} {$difftext}", $courseid, $report);
    }

    private function registeredusercount(int $courseid) : array {
        $usercounts = query_helper::query_users($courseid);
        $total = $usercounts[0] + $usercounts[1];
        $diff = $usercounts[1];

        return [
            $this->boxoutput('registered_users', $total, $diff, $courseid, 'learners')
        ];
    }

    private function clickcount(int $courseid) : array {
        $counts = query_helper::query_click_count($courseid);

        $hits = $counts['hits'];

        return [
            $this->boxoutput('click_count', $hits[1], ($hits[1] - $hits[0]), $courseid, 'dummy')
        ];
    }

    private function mobileevents(int $courseid) : array {
        $percentage = query_helper::query_mobile_percentage($courseid);

        $perctext = 'N/A';
        if ($percentage !== null) {
            $perctext = round($percentage) . '%';
        }
        $posttext = get_string('mobile_use_post_text', 'lareport_coursedashboard');

        return [
            $this->boxoutputraw('mobile_use', $perctext, $posttext, $courseid)
        ];
    }

    private function mostclickedactivity(int $courseid) : array {
        $module = query_helper::query_most_clicked_activity($courseid);
        if ($module === null) {
            return [
                $this->boxoutputraw(
                    'most_clicked_module',
                    'N/A', get_string('no_clicks',
                    'lareport_coursedashboard'),
                $courseid)
            ];
        }

        $mod = \context_module::instance($module['cmid']);
        $modurl = $mod->get_url();

        $modtitle = $mod->get_context_name(false);
        $modlink = "<a href='{$modurl}'>{$modtitle}</a>";
        $strclicks = get_string('clicks', 'lareport_coursedashboard');
        $hitsstr = $module['hits'] . " {$strclicks}";

        return [ $this->boxoutputraw('most_clicked_module', $modlink, $hitsstr, $courseid, 'activities') ];
    }

    public function run(array $params): array {
        global $PAGE, $DB;
        $PAGE->requires->css('/local/learning_analytics/reports/coursedashboard/static/styles.css');

        $courseid = $params['course'];
        $previd = query_helper::getCurrentprevcourse($courseid);
        $setcomparelink = new moodle_url(
            '/local/learning_analytics/index.php/reports/coursedashboard/set_previous_course',
            ['course' => $courseid]
        );

        $setcomparelinktext = 'Set course to compare';
        if ($previd !== -1) {
            $prevcourse = $DB->get_record('course', ['id' => $previd]);
            $setcomparelinktext = "Comparing to: {$prevcourse->fullname}";
        }

        return array_merge(
            $this->activiyoverweeks($courseid, $previd),
            ["<div class='coursedashboard-compare'><a href='{$setcomparelink}'>{$setcomparelinktext}</a></div>"],
            ["<div class='container-fluid'><div class='row'>"],
            $this->registeredusercount($courseid),
            $this->clickcount($courseid),
            $this->mostclickedactivity($courseid),
            ["</div></div>"]
        );
    }

    public function params(): array {
        return [
            'course' => required_param('course', PARAM_INT)
        ];
    }

}