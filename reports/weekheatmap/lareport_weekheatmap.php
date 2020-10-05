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

    public function run(array $params): array {
        global $USER, $OUTPUT, $DB;
        $courseid = $params['course'];
        $privacythreshold = settings::get_config('dataprivacy_threshold');

        $plotdata = [];
        $textdata = [];
        $xstrs = [];
        $texts = [];

        $days = array('sunday', 'saturday', 'friday', 'thursday', 'wednesday', 'tuesday', 'monday');
        $ystrs = [];
        foreach ($days as $day) {
            $ystrs[] = get_string($day, 'calendar');
        }

        $heatpoints = query_helper::query_heatmap($courseid);
        for ($d = 0; $d < 7; $d += 1) {
            // we need to start the plot at the bottom (sun -> sat -> fri -> ...)
            $startpos = (6 - $d) * 24;
            $daydata = [];
            $textdata = [];
            for ($h = 0; $h < 24; $h += 1) {
                $datapoint = empty($heatpoints[$startpos + $h]) ? 0 : $heatpoints[$startpos + $h]->value;
                $text = $datapoint;
                if ($datapoint < $privacythreshold) {
                    $text = '< ' . $privacythreshold;
                }
                $daydata[] = $datapoint;
                $hourstr = str_pad($h, 2, '0', STR_PAD_LEFT);
                $x = "{$hourstr}:00 - {$hourstr}:59 Uhr";
                $xstrs[] = $x;
                $textdata[] = "<b>{$text} Aufrufe</b><br>{$ystrs[$d]}, {$x}"; // TODO lang
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
            'colorscale' => 'YlGnBu'
        ]);
        $layout = new stdClass();
        $layout->margin = [ 't' => 10, 'r' => 20, 'l' => 80, 'b' => 80 ];
        $plot->set_layout($layout);
        $plot->set_height(450);

        // TODO lang
        return [
            self::heading(get_string('pluginname', 'lareport_weekheatmap')),
            '<p>Die folgende Heatmap zeigt die Anzahl aller Zugriffe je Wochentag und Uhrzeit.</p>', // TODO lang
            $plot
        ];
    }

    public function params(): array {
        return [
            'course' => required_param('course', PARAM_INT)
        ];
    }
}