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
 * Learning Analytics Table Output
 *
 * @package     local_learning_analytics
 * @copyright   2018 Lehr- und Forschungsgebiet Ingenieurhydrologie - RWTH Aachen University
 * @author      Marcel Behrmann <behrmann@lfi.rwth-aachen.de>
 * @author      Thomas Dondorf <dondorf@lfi.rwth-aachen.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_learning_analytics\local\outputs;

use coding_exception;

use html_table;
use html_writer;

use local_learning_analytics\output_base;

class table extends output_base {

    private $table;

    public function __construct() {
        $this->table = new html_table();
    }

    public function set_header(array $header) {
        $this->table->head = $header;
    }

    /**
     * @param array $header
     * @param $report
     * @throws coding_exception
     */
    public function set_header_local(array $header, $report) {
        $this->table->head = [];

        foreach ($header as $head) {
            $this->table->head[] = get_string($head, "lareport_{$report}");
        }
    }

    public function add_row(array $row) {
        $this->table->data[] = $row;
    }

    function print(): string {
        return html_writer::table($this->table);
    }
}