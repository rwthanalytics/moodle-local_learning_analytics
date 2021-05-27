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
 * Learning Analytics Base Report
 *
 * @package     local_learning_analytics
 * @copyright   Lehr- und Forschungsgebiet Ingenieurhydrologie - RWTH Aachen University
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_learning_analytics;

defined('MOODLE_INTERNAL') || die;

/**
 * Class report_preview
 *
 * @package local_learning_analytics
 */
abstract class report_preview {

    public abstract static function content(array $params): array; // returns array with content to show

    public static function box(string $titlekey, string $titletext, string $icon, $subtext, $value, int $diff, int $courseid, string $report = null) {
        $difftriangle = '';
        $difftext = $diff;
        
        if ($diff === 0) {
            $difftext = get_string('no_difference', 'lareport_coursedashboard');
        } else if ($diff > 0) {
            $difftext = '+' . $diff;
            $difftriangle = '<span class="dashboardbox-change-up" aria-hidden="true">▲</span>';
        } else {
            $difftriangle = '<span class="dashboardbox-change-down" aria-hidden="true">▼</span>';
        }

        return self::boxcomplex($titlekey, $titletext, $icon, $subtext, $value, "{$difftriangle} {$difftext}", $courseid, $report);
    }

    public static function boxcomplex(string $titlekey, string $titletext, string $icon, string $subtext, $maintext, $change, int $courseid, string $report = null) {
        $titlestr = $titletext;
        if ($report !== null) {
            $link = new \moodle_url("/local/learning_analytics/index.php/reports/{$report}", ['course' => $courseid]);
            $titlestr = "<a href='{$link}'>{$titlestr}</a>";
        }
        if ($change === '') {
            $change = '&nbsp;';
        }
        
        $comparedto = get_string('compared_to_previous_week', 'lareport_coursedashboard');
        return "
            <div class='dashboardbox box-{$titlekey}'>
                <div class='dashboardbox-icon' aria-hidden='true'>{$icon}</div>
                <div id='{$titlekey}_section' class='dashboardbox-header' role='button' aria-controls='{$titlekey}_value'>{$titlestr}</div>
                <div class='dashboardbox-timespan' aria-hidden='true'>{$subtext}</div>
                <div id='{$titlekey}_value' class='dashboardbox-title' aria-label='{$titletext} ({$subtext})' aria-describedby='{$titlekey}_section'>{$maintext}</div>
                <div class='dashboardbox-change' title='{$comparedto}'>{$change}</div>
            </div>
        ";
    }
}