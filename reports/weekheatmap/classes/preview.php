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

namespace lareport_weekheatmap;

use \local_learning_analytics\report_preview;
use \local_learning_analytics\settings;

defined('MOODLE_INTERNAL') || die;

class preview extends report_preview {

    public static function content(array $params): array {
        $courseid = $params['course'];
        $titletext = get_string('click_count', 'lareport_weekheatmap');
        $subtext = get_string('last_7_days', 'lareport_coursedashboard');

        $counts = query_helper::preview_query_click_count($courseid);

        $hits = $counts['hits'];
        $hitsLast7Days = $hits[1];
        $hitsdiff = $hits[1] - $hits[0];
        $privacythreshold = settings::get_config('dataprivacy_threshold');
        $strclicks = get_string('clicks', 'lareport_coursedashboard');
        if ($hitsLast7Days < $privacythreshold) {
            return [ report_preview::boxcomplex('click_count', $titletext, self::icon(), $subtext, '-', "< {$privacythreshold} {$strclicks}", $courseid, 'weekheatmap') ];
        }
        
        return [
            report_preview::box('click_count', $titletext, self::icon(), $subtext, $hitsLast7Days, $hitsdiff, $courseid, 'weekheatmap')
        ];
    }

    private static function icon() {
        return '<svg xmlns="http://www.w3.org/2000/svg" width="110" height="110" viewBox="0 0 24 24">
            <path d="M3.5 18.49l6-6.01 4 4L22 6.92l-1.41-1.41-7.09 7.97-4-4L2 16.99z"/>
            <path fill="none" d="M0 0h24v24H0z"/>
        </svg>';
    }
    
}