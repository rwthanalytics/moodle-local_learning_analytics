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

namespace lareport_quiz_assign;

use \local_learning_analytics\report_preview;
use \local_learning_analytics\settings;

defined('MOODLE_INTERNAL') || die;

class preview extends report_preview {

    public static function content(array $params): array {
        $courseid = $params['course'];
        $titletext = get_string('preview_quiz_assign', 'lareport_quiz_assign');
        $subtext = get_string('quiz_and_assignments', 'lareport_quiz_assign');

        $privacythreshold = settings::get_config('dataprivacy_threshold');
        $counts = query_helper::preview_quiz_and_assigments($courseid, $privacythreshold);

        $hitsLast7Days = $counts[1];
        $hitsdiff = $counts[1] - $counts[0];
        $quiz_not_enough_value = get_string('quiz_less_than_text', 'lareport_quiz_assign');
        if ($hitsLast7Days < $privacythreshold) {
            return [ report_preview::boxcomplex('quiz_assign', $titletext, self::icon(), $subtext, '-', "< {$privacythreshold} {$quiz_not_enough_value}", $courseid, 'quiz_assign') ];
        }
        
        return [
            report_preview::box('quiz_assign', $titletext, self::icon(), $subtext, $hitsLast7Days, $hitsdiff, $courseid, 'quiz_assign')
        ];
    }

    private static function icon() {
        return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="-4 0 28 28">
            <path d="M0 0h24v24H0z" fill="none"/>
            <path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/>
        </svg>';
    }
    
}