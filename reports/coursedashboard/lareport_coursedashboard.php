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
use local_learning_analytics\settings;

class lareport_coursedashboard extends report_base {

    const X_MIN = -1;
    const X_MAX = 30;

    private function activiyoverweeks(int $courseid) : array {
        $course = get_course($courseid);

        $date = new DateTime();
        $date->modify('-1 week');
        $now = $date->getTimestamp();

        $date->setTimestamp($course->startdate);
        $date->modify('Monday this week'); // Get start of week.

        $endoflastweek = new DateTime();
        $endoflastweek->modify('Sunday last week');

        $weeks = query_helper::query_weekly_activity($courseid);

        $privacythreshold = settings::get_config('dataprivacy_threshold');

        $plot = new plot();
        $x = [];
        $yclicks = [];

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

            $weeknumber = ($i <= 0) ? ($i - 1) : $i;

            $x[] = $i;
            $tickvals[] = $i;
            $ticktext[] = $weeknumber;

            $clickcount = $week->clicks ?? 0;
            if ($clickcount < $privacythreshold) {
                $clickcount = 0;
            }

            $startofweektimestamp = $date->getTimestamp();
            $date->modify('+6 days');

            if ($startofweektimestamp < $now) {
                // Date is in the past.
                $yclicks[] = $clickcount;

                $weekstarttext = userdate($startofweektimestamp, $dateformat);
                $weekendtext = userdate($date->getTimestamp(), $dateformat);
                $textClicks = $clickcount;
                if ($clickcount < $privacythreshold) {
                    $textClicks = "< {$privacythreshold}";
                }

                $texts[] = "<b>{$tstrweek} {$weeknumber}</b> ({$weekstarttext} - {$weekendtext})<br><br>{$textClicks} {$strclicks}";
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

    public function run(array $params): array {
        global $PAGE, $DB, $OUTPUT, $CFG;
        $PAGE->requires->css('/local/learning_analytics/reports/coursedashboard/static/styles.css?2');

        $courseid = $params['course'];

        $helpurl = new moodle_url('/local/learning_analytics/help.php', ['course' => $courseid]);
        $icon = \html_writer::link($helpurl,
            $OUTPUT->pix_icon('e/help', get_string('help', 'lareport_coursedashboard'), 'moodle', ['class' => 'helpicon'])
        );
        $helpprefix = "<div class='headingfloater'>{$icon}</div>";


        $previewboxes = settings::get_config('dashboard_boxes');
        $splitpreviewkeys = explode(',', $previewboxes);

        $subpluginsboxes = [];
        foreach ($splitpreviewkeys as $plugininfo) {
            $pluginsplit = explode(':', $plugininfo);
            $pluginkey = $pluginsplit[0];
            $pluginsize = intval($pluginsplit[1]);
            $previewfile = "{$CFG->dirroot}/local/learning_analytics/reports/{$pluginkey}/classes/preview.php";
            if (file_exists($previewfile)) {
                include_once($previewfile);
                $previewClass = "lareport_{$pluginkey}\\preview";
                $subpluginsboxes = array_merge($subpluginsboxes, ["<div class='col-lg-{$pluginsize}'>"], $previewClass::content($params), ["</div>"]);
            }
        }

        return array_merge(
            [self::heading(get_string('pluginname', 'lareport_coursedashboard'), true, $helpprefix)],
            $this->activiyoverweeks($courseid),
            ["<div class='row reportboxes'>"],
            $subpluginsboxes,
            ["</div>"]
        );
    }

    public function params(): array {
        return [
            'course' => required_param('course', PARAM_INT)
        ];
    }

}