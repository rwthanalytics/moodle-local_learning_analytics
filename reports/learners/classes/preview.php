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

namespace lareport_learners;

use \local_learning_analytics\report_preview;

defined('MOODLE_INTERNAL') || die;

class preview extends report_preview {

    public static function content(array $params): array {
        $courseid = $params['course'];
        $titletext = get_string('registered_users', 'lareport_learners');
        $subtext = get_string('total', 'lareport_coursedashboard');

        $usercounts = query_helper::preview_query_users($courseid);
        $total = $usercounts[0] + $usercounts[1];
        $diff = $usercounts[1];

        return [
            report_preview::box('registered_users', $titletext, self::icon(), $subtext, $total, $diff, $courseid, 'learners')
        ];
    }

    private static function icon() {
        return '<svg xmlns="http://www.w3.org/2000/svg" width="110" height="110"viewBox="0 0 24 24">
            <path d="M0 0h24v24H0z" fill="none"/><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8
            0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0
            2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29
            0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/>
        </svg>';
    }
    
}