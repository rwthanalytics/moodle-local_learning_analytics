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
 * Weekly Heatmap report
 *
 * @package     local_learning_analytics
 * @copyright   Lehr- und Forschungsgebiet Ingenieurhydrologie - RWTH Aachen University
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

use local_learning_analytics\local\outputs\plot;
use local_learning_analytics\local\outputs\table;
use local_learning_analytics\report_base;
use lareport_weekheatmap\query_helper;
use local_learning_analytics\router;
use local_learning_analytics\settings;

class lareport_weekheatmap extends report_base {

    public function run(array $params, $archive = null): array {
        global $USER, $OUTPUT, $DB;
        $courseid = $params['course'];
        $privacythreshold = settings::get_config('dataprivacy_threshold');

        $plotdata = [];
        $textdata = [];
        $xstrs = [];
        $texts = [];

        $calendar = \core_calendar\type_factory::get_calendar_instance();
        $startOfWeek = $calendar->get_starting_weekday(); // 0 -> Sunday, 1 -> Monday

        $days = array('sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday');
        if ($startOfWeek !== 0) {
            $days = array_merge(array_slice($days, $startOfWeek), array_slice($days, 0, $startOfWeek));
        }
        $days = array_reverse($days);
        
        $ystrs = [];
        foreach ($days as $day) {
            $ystrs[] = get_string($day, 'calendar');
        }
        
        // not able to use 12/24h format for now, as 12h format does not work with heatmap (as there are the same x axis values twice)
        // $timeformat = get_user_preferences('calendar_timeformat');
        // if (empty($timeformat)) {
        //     $timeformat = get_config(null, 'calendar_site_timeformat');
        // }

        $maxvalue = 0;
        $hitsstr = get_string('hits', 'lareport_weekheatmap');
        $heatpoints = query_helper::query_heatmap($courseid);
        for ($d = 0; $d < 7; $d += 1) {
            // we need to start the plot at the bottom (sun -> sat -> fri -> ...)
            $dbweekday = (6 + $startOfWeek - $d) % 7; // 0 (Sun) -> 6 (Sat) -> 5 (Fri) -> ...
            $daydata = [];
            $textdata = [];
            for ($h = 0; $h < 24; $h += 1) {
                $index = $dbweekday * 24 + $h;
                $datapoint = $heatpoints[$index];
                if ($archive) {
                    $datapoint += $archive[$index];
                }
                $text = $datapoint;
                if ($datapoint < $privacythreshold) {
                    $text = '< ' . $privacythreshold;
                    $datapoint = 0;
                }
                $daydata[] = $datapoint;
                $maxvalue = max($datapoint, $maxvalue);
                $hourstr = str_pad($h, 2, '0', STR_PAD_LEFT);
                $x = "{$hourstr}:00 - {$hourstr}:59";
                $xstrs[] = $x;
                $textdata[] = "<b>{$text} {$hitsstr}</b><br>{$ystrs[$d]}, {$x}";
            }
            $plotdata[] = $daydata;
            $texts[] = $textdata;
        }

        $plot = new plot();
        $plot->add_series([
            'type' => 'heatmap',
            'z' => $plotdata,
            'x' => $xstrs,
            'y' => $ystrs,
            'text' => $texts,
            'hoverinfo' => 'text',
            'colorscale' => [
                [0,    "#F3F3F3"],
                [.125, "#D4DFE8"],
                [.25,  "#B6CBDE"],
                [.375, "#97B7D3"],
                [.5,   "#79A3C9"],
                [.625, "#5B8FBE"],
                [.75,  "#3C7BB4"],
                [.875, "#1E67A9"],
                [1,    "#00549F"], // RWTH-blue
            ],
            'xgap' => 3,
            'ygap' => 3,
            'zmin' => 0,
            'zmax' => max(1, $maxvalue),
        ]);
        $layout = new stdClass();
        $layout->margin = [ 't' => 10, 'r' => 20, 'l' => 80, 'b' => 80 ];
        $plot->set_layout($layout);
        $plot->set_height(400);
        return [
            self::heading(get_string('pluginname', 'lareport_weekheatmap')),
            '<p>' . get_string('introduction', 'lareport_weekheatmap') . '</p>',
            $plot
        ];
    }

    public function params(): array {
        return [
            'course' => required_param('course', PARAM_INT)
        ];
    }
}