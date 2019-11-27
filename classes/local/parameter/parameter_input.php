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
 * @copyright   Lehr- und Forschungsgebiet Ingenieurhydrologie - RWTH Aachen University
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_learning_analytics\local\parameter;

use html_writer;
use local_learning_analytics\parameter_base;

defined('MOODLE_INTERNAL') || die;

/**
 * Class parameter_input
 *
 * @package local_learning_analytics\local\parameter
 */
class parameter_input extends parameter_base {

    protected $type;

    public function __construct(string $key, string $type, int $required = self::REQUIRED_HIDDEN, int $filter = FILTER_UNSAFE_RAW) {
        parent::__construct($key, $required, $filter);

        $this->type = $type;
    }

    public function render(): string {
        $attributes = [
                'type' => $this->type,
                'class' => 'form-control',
                'name' => $this->key,
                'value' => $this->form->get($this->key),
                'id' => "param_{$this->key}",
                'placeholder' => get_string("parameter:{$this->key}", "lareport_{$this->report_name}"),
                'required' => $this->is_required()
        ];

        if($this->is_required()) {
            $attributes['required'] = '';
        }
        return html_writer::start_tag('input', $attributes);
    }
}