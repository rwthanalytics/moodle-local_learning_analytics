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

use local_learning_analytics\output_base;

defined('MOODLE_INTERNAL') || die;

/**
 * Class renderer
 */
class local_learning_analytics_renderer extends plugin_renderer_base {

    /**
     * @param array $list
     * @return string
     * @throws coding_exception
     */
    public function render_output_list(array $list) {
        $ret = "";

        foreach ($list as $output) {
            if ($output instanceof output_base) {
                $ret .= $output->print();
            } else if ($output instanceof renderable) {
                $ret .= $this->output->render($output);
            } else {
                $ret .= $output;
            }
        }

        return $ret;
    }
}