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
 * @copyright   Lehr- und Forschungsgebiet Ingenieurhydrologie - RWTH Aachen University
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

use local_learning_analytics\local\outputs\table;
use local_learning_analytics\report_base;
use local_learning_analytics\local\outputs\plot;
use local_learning_analytics\local\outputs\splitter;
use local_learning_analytics\settings;

class lareport_browser_os extends report_base {

    private static $techStrings = [
        'platform' => [
            'desktop' => 'Desktop Browser',
            'mobile' => 'Mobile Browser',
            'api' => 'App',
            'other' => 'lang/other'
        ],
        'os' => [
            'windows' => 'Microsoft Windows',
            'mac' => 'macOS',
            'linux' => 'Linux',
            'other' => 'lang/other'
        ],
        'browser' => [
            'chrome' => 'Google Chrome',
            'edge' => 'Microsoft Edge',
            'firefox' => 'Mozilla Firefox',
            'ie' => 'Internet Explorer',
            'opera' => 'Opera',
            'safari' => 'Safari',
            'other' => 'lang/other'
        ],
        'mobile' => [
            'android' => 'Android',
            'ios' => 'iOS',
            'other' => 'lang/other'
        ]
    ];

    private static $barcolors = [
        '#66b5ab',
        '#F26522',
        '#ffda6e',
        '#A9CF54',
        '#EA030E'
    ];

    private static function createseries($perc, $text, $i) {
        return [
            'y' => ['value'],
            'x' => [$perc],
            'orientation' => 'h',
            'hoverinfo' => 'none',
            'marker' => [
                'color' => (self::$barcolors[$i % count(self::$barcolors)])
            ],
            'name' => $text,
            'type' => 'bar',
            'text' => $text
        ];
    }

    private static function createplot(array $results, string $entrykey, $privacythreshold) {
        $entries = $results[$entrykey];

        $annotations = [];
        $total = 0;
        foreach ($entries as $value) {
            if ($value >= $privacythreshold) {
                $total += $value;
            }
        }

        $plot = new plot();
        $i = 0;
        $percsofar = 0;
        foreach ($entries as $key => $value) {
            if ($value < $privacythreshold) {
                continue;
            }
            $perc = round(100 * $value / $total);
            if ($perc > 3) { // Otherwise it might be too small to display.
                $annotations[] = [
                    'x' => ($percsofar + ($perc / 2)),
                    'y' => 'value',
                    'text' => $perc . '%',
                    'font' => ['color' => '#fff', 'size' => 14],
                    'showarrow' => false,
                    'xanchor' => 'center'
                ];
            }
            $text = self::$techStrings[$entrykey][$key];
            if (substr($text, 0, 5) === 'lang/') {
                $text = get_string(substr($text, 5), 'lareport_browser_os');
            }
            $annotations[] = [
                'x' => ($percsofar + ($perc / 2)),
                'y' => 'value',
                'yshift' => 16,
                'text' => $text,
                'font' => ['color' => '#000','size' => 16],
                'showarrow' => false,
                'xanchor' => 'center',
                'yanchor' => 'bottom'
            ];
            $percsofar += $perc;
            $series = self::createseries($perc, $key, $i);
            $plot->add_series($series);
            $i += 1;
        }

        $layout = new stdClass();
        $layout->barmode = 'stack';
        $layout->annotations = $annotations;
        $layout->xaxis = ['visible' => false, 'range' => [0, 100], 'fixedrange' => true ];
        $layout->yaxis = ['visible' => false, 'fixedrange' => true];
        $layout->showlegend = false;
        $layout->margin = ['l' => 0, 'r' => 0, 't' => 10, 'b' => 0];

        $plot->set_layout($layout);
        $plot->set_height(70);
        $plot->set_static_plot(true);
        if ($i === 0) {
            return [''];
        }
        $langstring = get_string('used_' . $entrykey, 'lareport_browser_os');
        return [
            "<div class='row'><div class='col-12'>",
            "<h3>{$langstring}</h3>",
            $plot,
            "</div></div><div class='w-100'><hr></div>"
        ];
    }

    private function desktop_browsers(array $browsers, $privacythreshold) {
        global $DB;

        $maxhits = 0;
        foreach ($browsers as $hits) {
            if ($hits >= $privacythreshold) {
                $maxhits += $hits;
            }
        }
        $table = new table();
        $table->set_header_local(['browser_name', 'hits'], 'lareport_browser_os');

        foreach ($browsers as $browserkey => $hits) {
            if ($hits < $privacythreshold) {
                continue;
            }
            $text = self::$techStrings['browser'][$browserkey];
            if (substr($text, 0, 5) === 'lang/') {
                $text = get_string(substr($text, 5), 'lareport_browser_os');
            }
            $table->add_row([
                $text,
                $table::fancyNumberCell($hits, $maxhits, 'orange')
            ]);
        }

        if ($maxhits === 0) {
            return ['']; // no data to show
        }

        $langstring = get_string('used_desktop_browsers', 'lareport_browser_os');
        return [
            "<div class='row'><div class='col-12'>",
            "<h3>{$langstring}</h3>",
            $table,
            "</div></div>"
        ];
    }

    public function run(array $params): array {
        $privacythreshold = settings::get_config('dataprivacy_threshold');
        global $DB;

        $courseid = $params['course'];
        $result = $DB->get_record('lalog_browser_os', ['courseid' => $courseid], '*');
        if ($result === false) {
            $result = [];
        }
        $results = [
            'platform' => [],
            'os' => [],
            'browser' => [],
            'mobile' => [],
        ];
        $maxvalue = 0;
        foreach ($result as $key => $value) {
            $separatorindex = strpos($key, '_');
            if ($separatorindex === false) {
                continue;
            }
            $maxvalue = $value < $privacythreshold ? $maxvalue : max($maxvalue, $value);
            $prefix = substr($key, 0, $separatorindex);
            $target = substr($key, $separatorindex + 1);
            $results[$prefix][$target] = $value;
        }
        arsort($results['browser']);

        if ($maxvalue === 0) {
            return [get_string('no_data_to_show', 'lareport_browser_os')];
        }

        return array_merge(
            [self::heading(get_string('pluginname', 'lareport_browser_os'))],
            self::createplot($results, 'platform', $privacythreshold),
            self::createplot($results, 'os', $privacythreshold),
            self::createplot($results, 'mobile', $privacythreshold),
            self::desktop_browsers($results['browser'], $privacythreshold)
        );
    }

    public function params(): array {
        return [
            'course' => required_param('course', PARAM_INT)
        ];
    }

}