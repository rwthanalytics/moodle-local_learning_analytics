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
 * Learning Analytics Parameter Form
 *
 * @package     local_learning_analytics
 * @copyright   Lehr- und Forschungsgebiet Ingenieurhydrologie - RWTH Aachen University
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_learning_analytics;

use html_writer;
use renderable;

class form implements renderable {

    private $params = [];

    private $render = [];

    private $required_count = 0;

    private $missing_count = 0;

    private $report;

    /**
     * form constructor.
     *
     * @param parameter_base[] $params
     * @param array $defaults
     * @param string $report
     */
    public function __construct(array $params, array $defaults, string $report) {
        $this->report = $report;

        $this->params = $defaults;

        $values = [];

        foreach ($params as $param) {
            $param->set_report_name($report);
            $param->set_form($this);

            if ($param->is_required()) {
                $this->render[$param->get_key()] = $param;
                $this->required_count++;

                if (isset($_GET[$param->get_key()])) {
                    $values[$param->get_key()] = $param->get();
                } else {
                    $this->missing_count++;
                }
            } else {

                if (!$param->is_hidden()) {
                    $this->render[$param->get_key()] = $param;
                }

                if (isset($_GET[$param->get_key()])) {
                    $values[$param->get_key()] = $param->get();
                }
            }
        }

        $this->params = $this->params + $values;
    }

    public function get(string $key) {
        return isset($this->params[$key]) ? $this->params[$key] : '';
    }

    public function get_render(): array {
        return $this->render;
    }

    public function get_report_name(): string {
        return $this->report;
    }

    public function get_required_count(): int {
        return $this->required_count;
    }

    public function get_missing_count(): int {
        return $this->missing_count;
    }

    public function get_parameters(): array {
        return $this->params;
    }
}