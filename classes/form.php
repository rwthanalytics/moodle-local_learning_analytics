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
 * Learning Analytics Parameter Form
 *
 * @package     local_learning_analytics
 * @copyright   2018 Lehr- und Forschungsgebiet Ingenieurhydrologie - RWTH Aachen University
 * @author      Marcel Behrmann <behrmann@lfi.rwth-aachen.de>
 * @author      Thomas Dondorf <dondorf@lfi.rwth-aachen.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_learning_analytics;

use html_writer;

class form {

    private $inline;

    private $params = [];

    private $required = [];

    private $missing = [];

    private $missing_count = 0;

    private $optional = [];

    private $report;

    /**
     * form constructor.
     *
     * @param parameter[] $params
     * @param bool $inline
     */
    public function __construct(array $params, bool $inline, string $report) {

        $this->inline = $inline;
        $this->report = $report;

        foreach ($params as $param) {
            if ($param->is_required()) {
                $this->required[$param->get_key()] = $param;
                if (isset($_GET[$param->get_key()])) {
                    $this->params[$param->get_key()] = $param->get();
                } else {
                    $this->missing[] = $param->get_key();
                    $this->missing_count++;
                }
            } else {
                $this->optional[$param->get_key()] = $param;

                if (isset($_GET[$param])) {
                    $this->params[$param->get_key()] = $param->get();
                }
            }
        }
    }

    /**
     * @param string $report
     * @return string
     * @throws \coding_exception
     */
    public function render() {
        $class = ($this->inline) ? 'form-inline' : 'form';

        $output = html_writer::start_tag('form', ['method' => 'get', 'class' => $class]);

        foreach ($this->required as $key => $param) {
            $output .= html_writer::start_div('form-group');
            $output .= html_writer::label(get_string("parameter:{$key}", "lareport_{$this->report}"), "param_{$key}");

            switch ($param->get_type()) {
                case parameter::TYPE_STRING:
                case parameter::TYPE_NUMBER:
                    $output .= $this->render_input($param);
                    break;
                case parameter::TYPE_COURSE:
                    $output .= $this->render_course_selection($param);
                    break;
            }

            $output .= html_writer::end_div();
        }

        $output .= html_writer::tag('button', get_string('show_report', 'local_learning_analytics'), [
            'type' => 'submit',
            'class' => 'btn btn-primary'
        ]);

        $output .= html_writer::end_tag('form');
        return $output;
    }

    public function is_inline() {
        return $this->inline;
    }

    public function get_required() {
        return $this->required;
    }

    public function get_missing() {
        return $this->missing;
    }

    public function get_missing_count() {
        return $this->missing_count;
    }

    public function get_parameters() {
        return $this->params;
    }

    protected function render_input(parameter $param) : string {
        return html_writer::start_tag('input', [
                'type' => $param->get_type(),
                'class' => 'form-control',
                'name' => $param->get_key(),
                'value' => $param->get(),
                'id' => "param_{$param->get_key()}",
                'placeholder' => get_string("parameter:{$param->get_key()}", "lareport_{$this->report}")
        ]);
    }

    protected function render_select(parameter $param, array $options) : string {
        return html_writer::select($options, $param->get_key(), [
            'class' => 'form-control',
            'id' => "param_{$param->get_key()}",
        ]);
    }

    protected function render_course_selection(parameter $param) : string {
        $courses = [];

        foreach (get_courses() as $course) {
            $courses[$course->id] = $course->fullname;
        }

        return $this->render_select($param, $courses);
    }
}