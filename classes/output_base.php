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
 * Learning Analytics Base Output
 *
 * @package     local_learning_analytics
 * @copyright   2018 Lehr- und Forschungsgebiet Ingenieurhydrologie - RWTH Aachen University
 * @author      Marcel Behrmann <behrmann@lfi.rwth-aachen.de>
 * @author      Thomas Dondorf <dondorf@lfi.rwth-aachen.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_learning_analytics;

defined('MOODLE_INTERNAL') || die;

abstract class output_base {

    protected $is_ajax;

    private $ajax_method;

    private $ajax_type;

    private $ajax_params;

    abstract function print() : string;

    abstract function external() : output_external;

    public function set_ajax(string $method, string $type, array $params = []) {
        $this->is_ajax = true;
        $this->ajax_type = $type;
        $this->ajax_method = $method;
        $this->ajax_params = $params;
    }

    public function ajax(string $id, string $target, array $params = []) {
        global $PAGE;

        $PAGE->requires->js_call_amd('local_learning_analytics/outputs', 'ajax', [
            'id' => $id,
            'method' => $this->ajax_method,
            'type' => $this->ajax_type,
            'target' => $target,
            'params' => $params
        ]);
    }
}