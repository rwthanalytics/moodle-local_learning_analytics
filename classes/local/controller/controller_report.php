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
use moodle_url;
use context_course;
use local_learning_analytics\local\routing\router;

class controller_report extends controller_base {

    public function run_report_or_page($instance, bool $is_page = false): string {
        global $PAGE, $CFG;

        if ($instance) {
            $ret = '';

            $params = $instance->get_parameter();
            $fparamsList = [];

            if (count($params) > 0) {

                $fparams = new form($params, $instance->get_parameter_defaults(), $this->params['report']);

                $fparamsList = $fparams->get_parameters();

                if (!empty($fparamsList['course'])) {
                    $course = get_course($fparamsList['course']);

                    $coursecontext = context_course::instance($course->id, MUST_EXIST);
                    $coursename = empty($CFG->navshowfullcoursenames) ?
                        format_string($course->shortname, true, array('context' => $coursecontext)) :
                        format_string($course->fullname, true, array('context' => $coursecontext));

                    // TODO: Link this to LA course dashboard
                    $PAGE->navbar->add($coursename, new moodle_url('/course/view.php', ['id' => $course->id]));
                }

                $ret .= $this->renderer->render($fparams);

                if ($fparams->get_missing_count() == 0) {
                    $outputs = $instance->run($fparamsList);
                } else {
                    return get_string('error:wrong_link', 'local_learning_analytics');
                }
            } else {
                $outputs = $instance->run([]);
            }

            $reportname = get_string('pluginname', "lareport_{$this->params['report']}");
            $title = $reportname;
            $PAGE->navbar->add($reportname,
                router::report($this->params['report'], $fparamsList)
            );

            if ($is_page) {
                $pagename = get_string('pagename_' . $this->params['page'],
                    "lareport_{$this->params['report']}");
                $title = $pagename;
                $PAGE->navbar->add($pagename,
                    router::report_page($this->params['report'], $this->params['page'], $fparamsList)
                );
            }

            // TODO remove h2 tag? (least for pages)
            $ret .= html_writer::tag('h2', $title);

            $ret .= $this->renderer->render_output_list($outputs);

            return $ret;
        } else {
            return get_string('reports:missing', 'local_learning_analytics');
        }
    }

    /**
     * @return string
     * @throws \coding_exception
     */
    public function run(): string {
        $instance = self::get_report($this->params['report']);

        return $this->run_report_or_page($instance);
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

        return $this->run_report_or_page($instance, true);
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