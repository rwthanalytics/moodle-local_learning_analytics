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
 * Learning Analytics Report Controller
 *
 * @package     local_learning_analytics
 * @copyright   2018 Lehr- und Forschungsgebiet Ingenieurhydrologie - RWTH Aachen University
 * @author      Marcel Behrmann <behrmann@lfi.rwth-aachen.de>
 * @author      Thomas Dondorf <dondorf@lfi.rwth-aachen.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_learning_analytics\local\controller;

use core_component;
use local_learning_analytics\controller_base;
use local_learning_analytics\form;
use local_learning_analytics\output_base;
use local_learning_analytics\report_base;

class controller_report  extends controller_base {
    public function run() : string {
        $path = core_component::get_plugin_directory('lareport', $this->params['report']);

        $fqp = $path . DIRECTORY_SEPARATOR . "lareport_{$this->params['report']}.php";

        if (file_exists($fqp)) {
            require ($fqp);
            $ret = '';

            $class = "lareport_{$this->params['report']}";

            /**
             * @var report_base $instance
             */
            $instance = (new $class());

            $params = $instance->get_parameter();

            /**
             *
             */
            $outputs = null;

            if (sizeof($params) > 0) {
                $fparams = new form($params);

                $ret .= $fparams->render();

                if($fparams->get_missing_count() == 0) {

                   $outputs = $instance->run($fparams->get_parameters());
                };
            } else {
                $outputs = $instance->run([]);
            }

            foreach($outputs as $output) {
                if($output instanceof output_base) {
                    $ret .= $output->print();
                } else {
                    $ret .= $output;
                }
            }

            return $ret;
        } else {
            return get_string('reports:missing', 'local_learning_analytics');
        }
    }
}