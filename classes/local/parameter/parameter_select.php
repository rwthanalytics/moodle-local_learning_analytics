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

namespace local_learning_analytics\local\parameter;

use html_writer;
use local_learning_analytics\parameter_base;

defined('MOODLE_INTERNAL') || die;

/**
 * Class parameter_select
 *
 * @package local_learning_analytics\local\parameter
 */
class parameter_select extends parameter_base {

    /***
     * @var array
     */
    protected $options;

    public function __construct(string $key, array $options, int $required = self::REQUIRED_HIDDEN, $default = null, int $filter = FILTER_UNSAFE_RAW) {
        parent::__construct($key, $required, $default, $filter);

        $this->options = $options;
    }

    public function render() : string {
        return html_writer::select(
                $this->options,
                $this->key,
                $this->get(),
                ['' => 'choosedots'],
                [
                    'class' => 'form-control',
                    'id' => "param_{$this->key}",
                ]
        );
    }
}