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
 * Version info for the Course Dashboard
 *
 * @package     local_learning_analytics
 * @copyright   Lehr- und Forschungsgebiet Ingenieurhydrologie - RWTH Aachen University
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace lareport_coursedashboard;

defined('MOODLE_INTERNAL') || die();

class query_helper {

    public static function query_weekly_activity(int $courseid) : array {
        global $CFG, $DB;

        $course = get_course($courseid);

        $startdate = new \DateTime();
        $startdate->setTimestamp($course->startdate);
        $startdate->modify('Monday this week'); // Get start of week.

        $mondaytimestamp = $startdate->format('U');
        $week = "(FLOOR((l.timecreated - {$mondaytimestamp}) / (7 * 60 * 60 * 24)) + 1)";

        # T-SQL (which is SQL Server's syntax) cannot GROUP BY column aliases
        if ($CFG->dbtype === 'sqlsrv')
            $group_by = $week;
        else
            $group_by = 'week';

        $query = <<<SQL
        SELECT {$week}
        AS WEEK,
        COUNT(*) clicks
        FROM {logstore_lanalytics_log} l
        WHERE l.courseid = ?
        GROUP BY {$group_by}
        ORDER BY week;
SQL;

        return $DB->get_records_sql($query, [$courseid]);
    }

}
