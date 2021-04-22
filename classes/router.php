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
use local_learning_analytics\event\report_viewed;

defined('MOODLE_INTERNAL') || die;

class router {

    public static function run_report_or_page(
        $instance,
        $params,
        string $reportname,
        string $pagename = null
    ) : string {
        global $PAGE;

        $outputs = $instance->run($params);

        $title = get_string('pluginname', "lareport_{$reportname}");
        if ($reportname !== 'coursedashboard') { // TODO dont hardcode this, set default for main report somewhere.
            $PAGE->navbar->add($title, self::report($reportname, $params));
        }

        $ret = "<div class='container-fluid'>";
        $renderer = $PAGE->get_renderer('local_learning_analytics');
        $ret .= $renderer->render_output_list($outputs);
        $ret .= "</div>";

        $eventother = ['report' => $reportname];
        if ($pagename) {
            $eventother['page'] = $pagename;
        }
        $event = report_viewed::create(array(
            'contextid' => $PAGE->context->id,
            'objectid' => NULL,
            'other' => $eventother
        ));
        $event->add_record_snapshot('course', $PAGE->course);
        $event->trigger();

        return $ret;
    }

    /**
     * @param string $url
     * @return string
     */
    public static function run(string $url) : string {
        global $PAGE;

        $reportregex = '/^\/reports\/([a-zA-Z0-9_]+)(\?.*)?$/';
        $reportpageregex = '/^\/reports\/([a-zA-Z0-9_]+)\/([a-zA-Z0-9_]+)(\?.*)?$/';

        $uri = new moodle_url($url);
        $slashargs = str_replace($uri->get_path(false), '', $uri->get_path(true));

        $instance = null;
        $reportname = null;
        $pagename = null;
        if (preg_match($reportpageregex, $slashargs, $matches)) { // Page of report was called.
            $reportname = $matches[1];
            $pagename = $matches[2];
            $fqcn = "\\lareport_{$reportname}\\{$pagename}";
            if (class_exists($fqcn)) {
                $instance = new $fqcn();
            }
        } else if (preg_match($reportregex, $slashargs, $matches)) { // Report was called.
            $reportname = $matches[1];
            $path = core_component::get_plugin_directory('lareport', $reportname);
            $fqp = $path . DIRECTORY_SEPARATOR . "lareport_{$reportname}.php";
            if (file_exists($fqp)) {
                require($fqp);
                $class = "lareport_{$reportname}";
                $instance = (new $class());
            }
        }
        if ($instance) {
            $params = $instance->params();
            return self::run_report_or_page($instance, $params, $reportname, $pagename);
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