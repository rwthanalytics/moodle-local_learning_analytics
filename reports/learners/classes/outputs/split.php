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

namespace lareport_learners\outputs;

use coding_exception;

use html_table;
use html_writer;

use local_learning_analytics\output_base;
use local_learning_analytics\output_external;

class split extends output_base {

    private $elements;

    public function __construct(array $elements) {
        $this->elements = $elements;
    }

    function external(): output_external {
        return new output_external(
            'html',
            $this->print(),
            []
        );
    }

    function print(): string {
        global $PAGE;

        $renderer = $PAGE->get_renderer('local_learning_analytics');
        $code1 = $renderer->render_output_list([$this->elements[0]]);
        $code2 = $renderer->render_output_list([$this->elements[1]]);
        // TODO support more than two columns

        $html = "<div class='container-fluid'>
            <div class='row'>
                <div class='col-sm-6'>
                {$code1}
                </div>
                <div class='col-sm-6'>
                {$code2}
                </div>
            </div>
        </div>";

        return $html;
    }

}