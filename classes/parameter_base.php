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
 * Learning Analytics report parameter
 *
 * @package     local_learning_analytics
 * @copyright   Lehr- und Forschungsgebiet Ingenieurhydrologie - RWTH Aachen University
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_learning_analytics;

abstract class parameter_base {

    const
            REQUIRED_ALWAYS = 0,
            REQUIRED_OPTIONAL = 10,
            REQUIRED_HIDDEN = 20;

    protected $key;

    protected $required;

    protected $filter;

    protected $report_name;

    protected $default;

    /**
     * @var form
     */
    protected $form;

    public function __construct(string $key, int $required = self::REQUIRED_HIDDEN, int $filter = FILTER_UNSAFE_RAW) {
        $this->key = $key;
        $this->required = $required;
        $this->filter = $filter;
    }

    public function set_report_name(string $name) {
        $this->report_name = $name;
    }

    public function set_form(form $form) {
        $this->form = $form;
    }

    public function is_required(): bool {
        return $this->required == self::REQUIRED_ALWAYS;
    }

    public function is_hidden(): bool {
        return $this->required == self::REQUIRED_HIDDEN;
    }

    public function get_key(): string {
        return $this->key;
    }

    public function get_filter(): int {
        return $this->filter;
    }

    public function get() {
        return filter_input(INPUT_GET, $this->key, $this->filter);
    }

    public abstract function render(): string;
}