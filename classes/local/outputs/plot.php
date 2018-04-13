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
 *
 *
 * @package     local_learning_analytics
 * @copyright   2018 Lehr- und Forschungsgebiet Ingenieurhydrologie - RWTH Aachen University
 * @author      Marcel Behrmann <behrmann@lfi.rwth-aachen.de>
 * @author      Thomas Dondorf <dondorf@lfi.rwth-aachen.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_learning_analytics\local\outputs;

use html_writer;
use local_learning_analytics\output_base;
use local_learning_analytics\output_external;

defined('MOODLE_INTERNAL') || die;

/**
 * Class plot
 *
 * @package local_learning_analytics\local\outputs
 */
class plot extends output_base {

    private $series = [];

    /**
     * @param string $type
     * @param string $query
     * @param array $config
     * @throws \dml_exception
     */
    public function add_series_from_sql(string $type, string $query, array $config = ['x' => 'x', 'y' => 'y']) {
        global $DB;

        $trace = [
                'type' => $type,
                'x' => [],
                'y' => []
        ];

        foreach ($DB->get_records_sql($query) as $record) {
            $x = $config['x'];
            $y = $config['y'];
            $trace['x'][] = $record->$x;
            $trace['y'][] = $record->$y;
        }

        $this->series[] = $trace;
    }

    /**
     * @return string
     * @throws \moodle_exception
     */
    function print(): string {
        global $PAGE;

        $id = random_string(5);

        $PAGE->requires->js_call_amd('local_learning_analytics/outputs', 'plot', [
                'id' => $id
        ]);

        $out = html_writer::empty_tag('div', [
                //'style' => 'visibility: collapse;',
                'data-plot' => json_encode($this->series),
                'id' => "plot-{$id}"
        ]);

        $out .= html_writer::empty_tag('div', [
                'class' => 'content'
        ]);

        return $out;
    }

    function external(): output_external {
        // TODO: Implement external() method.
    }
}