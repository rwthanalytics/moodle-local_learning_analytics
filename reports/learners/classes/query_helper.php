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
 * @copyright   2018 Lehr- und Forschungsgebiet Ingenieurhydrologie - RWTH Aachen University
 * @author      Marcel Behrmann <behrmann@lfi.rwth-aachen.de>
 * @author      Thomas Dondorf <dondorf@lfi.rwth-aachen.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace lareport_learners;

defined('MOODLE_INTERNAL') || die();

use context_course;

class query_helper {

    public static function query_learners(int $courseid, string $role_filter = ''): array {
        global $DB;

        $activity = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
        $context = context_course::instance($activity->id, MUST_EXIST);
        if ($activity->id == SITEID) {
            throw new moodle_exception('invalidcourse');
        }
        // only teachers and managers
        require_capability('moodle/course:update', $context);

        $sqlParams = [$courseid];

        $roleFilterSql = '';
        if ($role_filter !== '') {
            $roleFilterSql = <<<SQL
            JOIN mdl_context c
                ON c.instanceid = e.courseid
                AND c.contextlevel = 50
            JOIN mdl_role_assignments ra
                ON ra.userid = u.id
                AND ra.contextid = c.id
            JOIN mdl_role r
                ON ra.roleid = r.id
                AND r.shortname = ?
SQL;
            $sqlParams = [$role_filter, $courseid];
        }

        $query = <<<SQL
        SELECT SQL_NO_CACHE
            u.id, 
            u.firstname,
            u.lastname,
            (SELECT GROUP_CONCAT(r.shortname SEPARATOR ', ')
                FROM mdl_role_assignments ra
                JOIN mdl_context c
                    ON c.id = ra.contextid
                LEFT JOIN mdl_role r
                    ON ra.roleid = r.id
                WHERE ra.userid = u.id
                    AND c.instanceid = e.courseid
                GROUP BY ra.userid
            ) role,
            (SELECT ses2.firstaccess
                FROM mdl_local_learning_analytics_ses ses2
                WHERE ses2.summaryid = su.id
                ORDER BY ses2.id
                LIMIT 1) firstaccess,
            (SELECT ses3.lastaccess
                FROM mdl_local_learning_analytics_ses ses3
                WHERE ses3.summaryid = su.id
                ORDER BY ses3.id DESC
                LIMIT 1) lastaccess,
            COALESCE(su.hits, 0) hits,
            COUNT(ses.id) sessions
        FROM mdl_user u
        JOIN mdl_user_enrolments ue
            ON ue.userid = u.id
        JOIN mdl_enrol e
            ON e.id = ue.enrolid
        LEFT JOIN mdl_local_learning_analytics_sum su
            ON su.userid = u.id
            AND su.courseid = e.courseid
        LEFT JOIN mdl_local_learning_analytics_ses ses
            ON ses.summaryid = su.id
        {$roleFilterSql}
        WHERE u.deleted = 0
            AND e.courseid = ?
        GROUP BY u.id
        ORDER BY su.hits DESC
SQL;

        return $DB->get_records_sql($query, $sqlParams);
    }
}