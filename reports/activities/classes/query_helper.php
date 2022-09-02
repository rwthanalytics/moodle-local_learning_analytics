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
 * Version info for the Activities report
 *
 * @package     local_learning_analytics
 * @copyright   Lehr- und Forschungsgebiet Ingenieurhydrologie - RWTH Aachen University
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace lareport_activities;

defined('MOODLE_INTERNAL') || die();

use local_learning_analytics\local\outputs\plot;
use local_learning_analytics\local\outputs\table;
use local_learning_analytics\report_base;
use context_course;

class query_helper {

    public static function query_activities(int $courseid, string $filter = '', array $values = []): array {
        global $DB;

        $activity = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
        $valuesstatemt = [$courseid, CONTEXT_MODULE];

        $filtersql = '';
        if ($filter) {
            $filtersql .= ' AND ' . $filter;
            $valuesstatemt = array_merge($valuesstatemt, $values);
        }

        $query = <<<SQL
        SELECT
            cm.id AS cmid, 
            m.name AS modname,
            COUNT(log.id) hits
        FROM {modules} m
        JOIN {course_modules} cm
            ON cm.course = ?
            AND cm.module = m.id
        LEFT JOIN {context} ctx
            ON ctx.contextlevel = ?
            AND ctx.instanceid = cm.id
        LEFT JOIN {logstore_lanalytics_log} log
            ON log.courseid = cm.course
            AND log.contextid = ctx.id
        WHERE m.name <> 'label' {$filtersql}
        GROUP BY cm.id, m.name
SQL;

        return $DB->get_records_sql($query, $valuesstatemt);
    }

    public static function preview_query_most_clicked_activity(int $courseid, $privacythreshold) {
        global $CFG, $DB;

        $date = new \DateTime();
        $date->setTime(23, 59, 59); // Include today.
        $date->modify('-1 week');
        $oneweekago = $date->getTimestamp();

        # Handling of max rows to be returned
        if ($CFG->dbtype === 'sqlsrv')
            $top = 'TOP 3';
        else
            $limit = 'LIMIT 3';

        $query = <<<SQL
        SELECT {$top}
            cm.id AS cmid,
            m.name as modname,
            COUNT(*) AS hits
        FROM {modules} m
        JOIN {course_modules} cm
            ON cm.course = ?
        AND cm.module = m.id
            LEFT JOIN {context} ctx
        ON ctx.contextlevel = 70
            AND ctx.instanceid = cm.id
        LEFT JOIN {logstore_lanalytics_log} l
            ON l.courseid = cm.course
            AND l.contextid = ctx.id
        WHERE l.timecreated > ?
        GROUP BY cm.id, m.name
        HAVING count(*) >= ?
        ORDER BY hits DESC
        {$limit}
SQL;

        return $DB->get_records_sql($query, [$courseid, $oneweekago, max(1, $privacythreshold)]);
    }
}
