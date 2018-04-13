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
 * @copyright   2018 Lehr- und Forschungsgebiet Ingenieurhydrologie - RWTH Aachen University
 * @author      Marcel Behrmann <behrmann@lfi.rwth-aachen.de>
 * @author      Thomas Dondorf <dondorf@lfi.rwth-aachen.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_learning_analytics;

class parameter {

    const TYPE_STRING = 0;

    protected $key;

    protected  $type;

    protected  $required;

    protected  $filter;

    public function __construct(string $key, int $type, bool $required = false, int $filter = FILTER_UNSAFE_RAW) {
        $this->key = $key;
        $this->type = $type;
        $this->required = $required;
        $this->filter = $filter;
    }

    public function is_required() {
        return $this->required;
    }

    public function get_key(){
        return $this->key;
    }

    public function get_type() {
        return $this->type;
    }

    public function get_filter() {
        return $this->filter;
    }

    public function get() {
        return filter_input(INPUT_GET, $this->key, $this->filter);
    }
}