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
 * Weekly heatmap report query helper
 *
 * @package     local_learning_analytics
 * @copyright   Lehr- und Forschungsgebiet Ingenieurhydrologie - RWTH Aachen University
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace lareport_weekheatmap;

defined('MOODLE_INTERNAL') || die();

use local_learning_analytics\local\outputs\plot;
use local_learning_analytics\local\outputs\table;
use local_learning_analytics\report_base;
use context_course;

class query_helper {

    public static function query_heatmap(int $courseid): array {
        global $DB;

        $course = get_course($courseid);
        $startdate = new \DateTime();
        $startdate->setTimestamp($course->startdate);
        $startdate->modify('Monday this week'); // Get start of week.
        $mondaytimestamp = $startdate->getTimestamp();
        // TODO Implement Daylight saving offeset
        // This does not work very well if daylight saving comes into play, then all old
        // dates will be shifted by 1 hour...

        // returns points between 0 - 167 (24*7 data points), where 0 => Mon,0-1am; 1 => Mon,1-2am; ...
        $query = <<<SQL
        SELECT
            FLOOR(((l.timecreated - ?) % (60*60*24*7)) / (60*60)) AS heatpoint,
            COUNT(1) AS value
        FROM {logstore_lanalytics_log} AS l
            WHERE l.courseid = ?
        GROUP BY heatpoint
        ORDER BY heatpoint
SQL;

        return $DB->get_records_sql($query, [$mondaytimestamp, $courseid]);
    }
}
