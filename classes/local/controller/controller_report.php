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
use html_writer;
use local_learning_analytics\controller_base;
use local_learning_analytics\form;
use local_learning_analytics\output_base;
use local_learning_analytics\report_base;
use local_learning_analytics\report_page_base;
use renderable;

class controller_report extends controller_base {
    /**
     * @return string
     * @throws \coding_exception
     */
    public function run(): string {
        $instance = self::get_report($this->params['report']);

        if ($instance != null) {
            $ret = '';

            $params = $instance->get_parameter();
            $outputs = [];

            if (sizeof($params) > 0) {
                $fparams = new form($params, $instance->get_parameter_defaults(), $this->params['report']);

                $ret .= $this->renderer->render($fparams);

                if ($fparams->get_missing_count() == 0) {
                    $outputs = $instance->run($fparams->get_parameters());
                }
                // TODO: Error message when missing requireds ?
            } else {
                $outputs = $instance->run([]);
            }

            $ret .= html_writer::tag('h2', get_string('pluginname', "lareport_{$this->params['report']}"));

            $ret .= $this->renderer->render_output_list($outputs);

            return $ret;
        } else {
            return get_string('reports:missing', 'local_learning_analytics');
        }
    }

    /**
     * @param string $name
     * @return report_base
     */
    public static function get_report(string $name) {
        $path = core_component::get_plugin_directory('lareport', $name);

        $fqp = $path . DIRECTORY_SEPARATOR . "lareport_{$name}.php";

        if (file_exists($fqp)) {
            require($fqp);

            $class = "lareport_{$name}";

            /**
             * @var report_base $instance
             */
            return (new $class());
        } else {
            return null;
        }
    }

    /**
     * @return string
     * @throws \coding_exception
     */
    public function run_page(): string {

        $instance = self::get_report_page($this->params['report'], $this->params['page']);

        if ($instance) {
            $ret = '';
            $params = $instance->get_parameter();

            if (count($params) > 0) {
                $fparams = new form($params, $instance->get_parameter_defaults(), $this->params['report']);

                if ($fparams->get_missing_count() === 0) {
                    $outputs = $instance->run($fparams->get_parameters());

                } else {
                    return get_string('error:wrong_link', 'local_learning_analytics');
                }

            } else {
                $outputs = $instance->run([]);
            }

            $ret .= $this->renderer->render_output_list($outputs);

            return $ret;
        } else {
            return get_string('error:page_not_found', 'local_learning_analytics');
        }
    }

    /**
     * @param string $report
     * @param string $page
     * @return report_page_base
     */
    public static function get_report_page(string $report, string $page) {
        $namespace = "lareport_{$report}";
        $class = $page;

        $fqcn = "\\{$namespace}\\{$class}";

        if (class_exists($fqcn)) {
            $instance = new $fqcn();

            if ($instance instanceof report_page_base) {
                return $instance;
            }
        }
        return null;
    }
}