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

/*
 * Router
 *
 * @package     local_learning_analytics
 * @copyright   Lehr- und Forschungsgebiet Ingenieurhydrologie - RWTH Aachen University
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


namespace local_learning_analytics;

use moodle_url;
use core_component;
use html_writer;

class router {

    public static function run_report_or_page(
        $instance,
        $params,
        string $report_name,
        string $page_name = null
    ) : string {
        global $PAGE;

        $outputs = $instance->run($params);

        $title = get_string('pluginname', "lareport_{$report_name}");
        if ($report_name !== 'coursedashboard') {
            $PAGE->navbar->add($title, self::report($report_name, $params));
        }

        if ($page_name !== null) {
            $pagename = get_string("pagename_{$page_name}", "lareport_{$report_name}");
            $title = $pagename;
        }

        $ret = html_writer::tag('h2', $title);

        $renderer = $PAGE->get_renderer('local_learning_analytics');
        $ret .= $renderer->render_output_list($outputs);
        return $ret;
    }

    /**
     * @param string $url
     * @return string
     */
    public static function run(string $url) : string {
        global $PAGE;

        $report_regex = '/^\/reports\/([a-zA-Z0-9_]+)(\?.*)?$/';
        $report_page_regex = '/^\/reports\/([a-zA-Z0-9_]+)\/([a-zA-Z0-9_]+)(\?.*)?$/';

        $uri = new moodle_url($url);
        $slashargs = str_replace($uri->get_path(false), '', $uri->get_path(true));

        if (preg_match($report_page_regex, $slashargs, $matches)) { // page of report was called
            $report_name = $matches[1];
            $page_name = $matches[2];

            $fqcn = "\\lareport_{$report_name}\\{$page_name}";
            if (class_exists($fqcn)) {
                $page_instance = new $fqcn();
                return self::run_report_or_page($page_instance, $uri->params(), $report_name, $page_name);
            }
        } else if (preg_match($report_regex, $slashargs, $matches)) { // report was called
            $report_name = $matches[1];

            $path = core_component::get_plugin_directory('lareport', $report_name);
            $fqp = $path . DIRECTORY_SEPARATOR . "lareport_{$report_name}.php";
            if (file_exists($fqp)) {
                require($fqp);
                $class = "lareport_{$report_name}";
                $report_instance = (new $class());
                return self::run_report_or_page($report_instance, $uri->params(), $report_name);
            }
        }
        return get_string('reports:missing', 'local_learning_analytics');
    }

    private static function get_url(string $slash, array $query) : moodle_url {
        $params = empty($query) ? '' : '?' . http_build_query($query);
        return new moodle_url("/local/learning_analytics/index.php/reports{$slash}{$params}");
    }

    /**
     * @param string $report
     * @param array $params
     * @return moodle_url
     * @throws \moodle_exception
     */
    public static function report(string $report, array $params = []) : moodle_url {
        return self::get_url("/{$report}", $params);
    }

    public static function report_page(string $report, string $page, array $params = []) : moodle_url {
        return self::get_url("/$report/$page", $params);
    }
}