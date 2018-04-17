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
use stdClass;

defined('MOODLE_INTERNAL') || die;

/**
 * Class plot
 *
 * @package local_learning_analytics\local\outputs
 */
class plot extends output_base {

    private $series = [];

    private $id;

    private $layout;

    private $params;

    private $ajax_plot_key;

    public function __construct() {
        $this->id = 'la-plot-' . random_string(4);

        $this->layout = new stdClass();
        $this->params = new stdClass();
    }

    /**
     * @param string $type
     * @param string $query
     * @param array $config
     * @return array
     * @throws \dml_exception
     */
    public static function sql_to_series(string $type, array $rows, array $config = ['x' => 'x', 'y' => 'y']) {
        global $DB;

        $trace = [
                'type' => $type,
                'x' => [],
                'y' => []
        ];

        $x = $config['x'];
        $y = $config['y'];
        foreach ($rows as $record) {
            $trace['x'][] = $record->$x;
            $trace['y'][] = $record->$y;
        }

        return $trace;
    }

    public function set_title(string $title) {
        $this->layout->title = $title;
    }

    public function show_toolbar(bool $show) {
        $this->params->displayModeBar = $show;
    }

    /**
     * @param string $type
     * @param string $query
     * @param array $config
     * @throws \dml_exception
     */
    public function add_series_from_sql(string $type, string $query, array $config = ['x' => 'x', 'y' => 'y']) {
        global $DB;
        $this->series[] = self::sql_to_series($type, $DB->get_records_sql($query), $config);
    }

    public function add_series_from_sql_records(string $type, $rows, array $config = ['x' => 'x', 'y' => 'y']) {
        $this->series[] = self::sql_to_series($type, $rows, $config);
    }

    public function add_series(array $series) {
        $this->series[] = $series;
    }

    public function load_data_ajax(string $method, string $plot_key) {
        $this->set_ajax($method, 'plot');

        $this->ajax_plot_key = $plot_key;
    }

    /**
     * @return output_external
     */
    function external(): output_external {
        return new output_external('plot', $this->print(), ['id' => $this->id]);
    }

    /**
     * @return string
     */
    function print(): string {
        global $PAGE;

        if ($this->is_ajax) {
            $this->ajax($this->ajax_plot_key, $this->id);
        } else {
            $PAGE->requires->js_call_amd('local_learning_analytics/outputs', 'plot', [
                    'id' => $this->id
            ]);
        }

        $out = html_writer::empty_tag('div', [
                'data-plot' => json_encode($this->series),
                'data-layout' => json_encode($this->layout),
                'data-params' => json_encode($this->params),
                'id' => $this->id
        ]);

        return $out;
    }
}