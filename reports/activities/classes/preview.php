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

namespace lareport_activities;

use \local_learning_analytics\report_preview;
use \local_learning_analytics\settings;

defined('MOODLE_INTERNAL') || die;

class preview extends report_preview {
    public static function content(array $params): array {
        $courseid = $params['course'];

        $titletext = get_string('most_clicked_module', 'lareport_activities');
        $last7days = get_string('last_7_days', 'lareport_coursedashboard');

        $privacythreshold = settings::get_config('dataprivacy_threshold');
        $strclicks = get_string('clicks', 'lareport_coursedashboard');
        $modules = query_helper::preview_query_most_clicked_activity($courseid, $privacythreshold);
        if (count($modules) === 0) {
            return [
                report_preview::boxcomplex('most_clicked_module', $titletext, self::icon(), $last7days, '-', "< {$privacythreshold} {$strclicks}", $courseid, 'activities')
            ];
        }

        $modulerows = [];
        foreach ($modules as $module) {
            $mod = \context_module::instance($module->cmid);
            $modtitle = $mod->get_context_name(false);
            $modurl = $mod->get_url();
            $modulerows[] = "<td class='c0'><a href='{$modurl}'>{$modtitle}</a></td><td class='c1'>{$module->hits} {$strclicks}</td>";
        }
        $mergedrows = implode("</tr><tr>", $modulerows);

        $link = new \moodle_url("/local/learning_analytics/index.php/reports/activities", ['course' => $courseid]);
        $icon = self::icon();
        return ["<div class='dashboardbox box-most_clicked_module'>
            <div class='dashboardbox-icon' aria-hidden='true'>{$icon}</div>
            <div id='activ_section' class='dashboardbox-header' aria-controls='activ_value'><a href='{$link}'>{$titletext}</a></div>
            <div class='dashboardbox-timespan' aria-hidden='true'>{$last7days}</div>
            <table id='activ_value' class='dashboardbox-table' aria-label='{$titletext} ($last7days})' aria-describedby='activ_section'><tr>{$mergedrows}</tr></table>
        </div>"];
    }

    private static function icon() {
        return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="-4 0 28 28">
            <rect fill="none" height="24" width="24"/><g><path d="M7.5,21H2V9h5.5V21z M14.75,3h-5.5v18h5.5V3z M22,11h-5.5v10H22V11z"/></g>
        </svg>';
    }
    
}