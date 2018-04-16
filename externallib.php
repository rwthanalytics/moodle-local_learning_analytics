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
 * External Library
 *
 * @package     local_learning_analytics
 * @copyright   2018 Lehr- und Forschungsgebiet Ingenieurhydrologie - RWTH Aachen University
 * @author      Marcel Behrmann <behrmann@lfi.rwth-aachen.de>
 * @author      Thomas Dondorf <dondorf@lfi.rwth-aachen.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_learning_analytics\local\controller\controller_report;

defined('MOODLE_INTERNAL') || die;

class local_learning_analytics_external extends external_api {

    public static function report_parameters() {
        return new external_function_parameters([
                'report' => new external_value(PARAM_TEXT, 'Report to run'),
                'type' => new external_value(PARAM_TEXT, 'Run Type'),
                'params' => new external_value(PARAM_TEXT, 'Parameters', VALUE_OPTIONAL)
        ]);
    }

    public static function report_returns() {
        return new external_multiple_structure(
                new external_single_structure([
                        'type' => new external_value(PARAM_TEXT),
                        'content' => new external_value(PARAM_TEXT),
                        'params' => new external_value(PARAM_TEXT)
                ])
        );
    }

    public static function report(string $report, string $type, string $params = "{}") {

        $report = controller_report::get_report($report);

        $eparams = json_decode($params, true);

        $outputs = [];

        if ($type == 'block') {
            if ($report->supports_block()) {
                $report->is_block(true);
                // Patch Parameters
                $params = array_merge(
                        $report->get_block_parameter(),
                        $eparams
                );
                $outputs = $report->run($params);
            }
        } else {
            $outputs = $report->run($eparams);
        }

        $finalout = array_map(function($e) {
            return $e->external()->to_array();
        }, $outputs);

        return $finalout;
    }

    public static function ajax_parameters() {
        return new external_function_parameters([
                'method' => new external_value(PARAM_TEXT, 'Method to run'),
                'id' => new external_value(PARAM_TEXT, 'Identifier'),
                'params' => new external_value(PARAM_TEXT, 'serialized parameters')
        ]);
    }

    public static function ajax_returns() {
        return new external_single_structure([
                'value' => new external_value(PARAM_TEXT, 'Return value')
        ]);
    }

    public static function ajax(string $method, string $id, string $params) {

        $target = explode('@', $method);

        if(strpos($target[0], 'lareport') !== false) {
            $class = controller_report::get_report(str_replace('lareport_', '', $target[0]));
        } else {
            $class = $target[0];
            $class = (new $class([]));
        }

        $method = $target[1];

        $ret = $class->$method($id);

        return [
                'value' => base64_encode(json_encode($ret))
        ];
    }
}