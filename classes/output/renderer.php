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

use local_learning_analytics\form;
use local_learning_analytics\local\parameter\parameter_select;
use local_learning_analytics\output_base;
use local_learning_analytics\parameter_base;

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

    /**
     * @param form $form
     * @return string
     * @throws coding_exception
     */
    public function render_form(form $form) {
        $output = html_writer::start_tag('form', ['method' => 'get', 'class' => 'la_params_form form-inline m-0 pull-right']);

        /**
         * @var $param parameter_base
         */
        foreach ($form->get_render() as $key => $param) {
            $output .= html_writer::start_div('form-group');

            $output .= html_writer::label(get_string("parameter:{$key}", "lareport_{$form->get_report_name()}"), "param_{$key}", '', ['class' => 'sr-only']);
            $output .= $param->render();

            $output .= html_writer::end_div();
        }

        $this->page->requires->js_call_amd('local_learning_analytics/form', 'init', []);

        /*

        $output .= html_writer::tag('button', get_string('show_report', 'local_learning_analytics'), [
                'type' => 'submit',
                'class' => 'btn btn-primary'
        ]);

        */

        $output .= html_writer::end_tag('form');
        return $output;
    }
}