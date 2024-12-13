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

namespace lareport_learners;

defined('MOODLE_INTERNAL') || die();

use context_course;

class query_helper {

    public static function query_learners_count(int $courseid, array $studentrolenames) : int {
        global $DB;

        // creates sql placeholder for role names (like "?,?" for two roles)
        $arraywithquestionsmarks = array_fill(0, count($studentrolenames), '?');
        $roleplaceholder = implode(',', $arraywithquestionsmarks);

        $query = <<<SQL
            SELECT COUNT(DISTINCT u.id) count
            FROM {user} u
            JOIN {user_enrolments} ue
                ON ue.userid = u.id
            JOIN {enrol} e
                ON e.id = ue.enrolid
            JOIN {context} c
                ON c.instanceid = e.courseid
                AND c.contextlevel = 50
            JOIN {role_assignments} ra
                ON ra.userid = u.id
                AND ra.contextid = c.id
            JOIN {role} r
                ON ra.roleid = r.id
                AND r.shortname IN ({$roleplaceholder})
            WHERE
                u.deleted = 0
                AND e.courseid = ?;
SQL;

        $params = array_merge($studentrolenames, [$courseid]);
        return (int) $DB->get_field_sql($query, $params, MUST_EXIST);
    }

    public static function query_courseparticipation(
        int $courseid, int $privacythreshold, array $studentrolenames, int $coursebeforecutoff, int $courseparallelcutoff,
        string $coursegroupby
    ) : array {
        global $DB, $CFG;

        $groupbychoice = 'id';
        if ($coursegroupby === 'fullname' || $coursegroupby === 'shortname') {
            $groupbychoice = $coursegroupby;
        }

        // creates sql placeholder for role names (like "?,?" for two roles)
        $arraywithquestionsmarks = array_fill(0, count($studentrolenames), '?');
        $roleplaceholder = implode(',', $arraywithquestionsmarks);

        $casevalue = "CASE WHEN co.startdate < {$coursebeforecutoff} THEN 1 WHEN co.startdate < {$courseparallelcutoff} THEN 2 ELSE 0 END";
        $selectconcat = $DB->sql_concat($casevalue, "'-'", "co.{$groupbychoice}");
        $coursename = ($groupbychoice === 'id') ? 'fullname' : $groupbychoice;

        $pgspecialcase = ($CFG->dbtype === 'pgsql') ? '' : ', co.startdate';

        # T-SQL (which is SQL Server's syntax) must have all selected columns contained in the GROUP BY clause
        if ($CFG->dbtype === 'sqlsrv')
            $group_by = "co.{$groupbychoice}, {$coursename}, {$casevalue}, co.startdate";
        else
            $group_by = "co.{$groupbychoice}, {$casevalue}";

        $query = <<<SQL
            SELECT
                {$selectconcat} AS uniqueval,-- first row needs to be unique for moodle...
                co.{$coursename} AS fullname,
                {$casevalue} AS beforeparallel,
                COUNT(DISTINCT u.id) AS users
                {$pgspecialcase}
            FROM {user} u
            JOIN {user_enrolments} ue
                ON ue.userid = u.id
            JOIN {enrol} e
                ON e.id = ue.enrolid
            JOIN {user_enrolments} ue2
                ON ue2.userid = u.id
            JOIN {enrol} e2
                ON e2.id = ue2.enrolid
                AND e2.courseid <> e.courseid
            JOIN {course} co
                ON co.id = e2.courseid
            -- only people enroled as students into the course
            JOIN {context} c
                ON c.instanceid = e.courseid
                AND c.contextlevel = 50
            JOIN {role_assignments} ra
                ON ra.userid = u.id
                AND ra.contextid = c.id
            JOIN {role} r
                ON ra.roleid = r.id
                AND r.shortname IN ({$roleplaceholder})
            WHERE u.deleted = 0
                AND e.courseid = ?
                AND co.startdate <> 0
                AND co.visible = 1
            GROUP BY {$group_by}
            HAVING COUNT(*) > ? AND {$casevalue} <> 0
            ORDER BY users DESC;
SQL;

        $threshold = max(1, $privacythreshold);
        $params = array_merge($studentrolenames, [$courseid, $threshold]);
        return $DB->get_records_sql($query, $params);
    }

    public static function query_localization(int $courseid, string $type) : array {
        global $DB;

        $query = <<<SQL
            SELECT
                u.{$type} AS x,
                COUNT(DISTINCT u.id) users
            FROM {user} u
            JOIN {user_enrolments} ue
                ON ue.userid = u.id
            JOIN {enrol} e
                ON e.id = ue.enrolid
            JOIN {context} c
                ON c.instanceid = e.courseid
                AND c.contextlevel = 50
            -- only students
            JOIN {role_assignments} ra
                ON ra.userid = u.id
                AND ra.contextid = c.id
            JOIN {role} r
                ON ra.roleid = r.id
                AND r.shortname = 'student'
            WHERE u.deleted = 0
                AND e.courseid = ?
            GROUP BY u.{$type}
            ORDER BY users DESC;
SQL;

        return $DB->get_records_sql($query, [$courseid]);
    }

    // Returns array like [100, 50] meaning 100 students were already registered since last week
    // and 50 more students join in the last days.
    public static function preview_query_users(int $courseid) : array {
        global $CFG, $DB;

        $date = new \DateTime();
        $date->modify('-1 week');

        $timestamp = $date->getTimestamp();
        $case = "CASE WHEN ue.timestart < {$timestamp} THEN 0 ELSE 1 END";

        # T-SQL (which is SQL Server's syntax) cannot GROUP BY column aliases
        if ($CFG->dbtype === 'sqlsrv')
            $group_by = $case;
        else
            $group_by = 'time';

        $query = <<<SQL
        SELECT
            {$case} AS time,
            COUNT(DISTINCT u.id) AS learners
        FROM {user} u
        JOIN {user_enrolments} ue
            ON ue.userid = u.id
        JOIN {enrol} e
            ON e.id = ue.enrolid
        WHERE u.deleted = 0
            AND e.courseid = ?
        GROUP BY {$group_by}
        ORDER BY time;
SQL;

        $res = $DB->get_records_sql($query, [$courseid]);

        return [
            $res[0]->learners ?? 0,
            $res[1]->learners ?? 0
        ];
    }
}
