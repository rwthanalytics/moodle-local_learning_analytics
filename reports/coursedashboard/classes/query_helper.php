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

    public static function getcurrentprevcourse(int $courseid): int {
        global $DB;

        $previd = $DB->get_record('local_learning_analytics_pre', ['courseid' => $courseid]);
        if (isset($previd->prevcourseid)) {
            return $previd->prevcourseid;
        } else {
            return -1;
        }
    }

    public static function query_weekly_activity(int $courseid) : array {
        global $DB;

        $course = get_course($courseid);

        $startdate = new \DateTime();
        $startdate->setTimestamp($course->startdate);
        $startdate->modify('Monday this week'); // Get start of week.

        $mondaytimestamp = $startdate->format('U');

        $query = <<<SQL
        SELECT (FLOOR((l.timecreated - {$mondaytimestamp}) / (7 * 60 * 60 * 24)) + 1)
        AS WEEK,
        COUNT(*) clicks
        FROM {logstore_lanalytics_log} l
        WHERE l.courseid = ?
        GROUP BY week
        ORDER BY week;
SQL;

        return $DB->get_records_sql($query, [$courseid]);
    }

    // Returns array like [100, 50] meaning 100 students were already registered since last week
    // and 50 more students join in the last days.
    public static function query_users(int $courseid) : array {
        global $DB;

        $date = new \DateTime();
        $date->modify('-1 week');

        $timestamp = $date->getTimestamp();

        $query = <<<SQL
        SELECT
            1 + FLOOR((ue.timestart - {$timestamp}) / 5000000000) AS time,
            COUNT(u.id) AS learners
        FROM {user} u
        JOIN {user_enrolments} ue
            ON ue.userid = u.id
        JOIN {enrol} e
            ON e.id = ue.enrolid
        WHERE u.deleted = 0
            AND e.courseid = ?
        GROUP BY time
        ORDER BY time;
SQL;

        $res = $DB->get_records_sql($query, [$courseid]);

        return [
            $res[0]->learners ?? 0,
            $res[1]->learners ?? 0
        ];
    }

    private static function click_count_helper(int $courseid, int $from, int $to = null) {
        global $DB;

        $query = <<<SQL
        SELECT
            COUNT(*) hits
        FROM {logstore_lanalytics_log}
        WHERE
            courseid = ?
            AND timecreated > ?
            AND timecreated <= ?
SQL;

        $res = $DB->get_records_sql($query, [$courseid, $from, $to]);
        return reset($res);
    }

    public static function query_click_count(int $courseid) : array {

        $date = new \DateTime();
        $date->setTime(23, 59, 59); // Include today.
        $today = $date->getTimestamp();
        $date->modify('-1 week');
        $oneweekago = $date->getTimestamp();
        $date->modify('-1 week');
        $twoweeksago = $date->getTimestamp();

        $previousweek = self::click_count_helper($courseid, $twoweeksago, $oneweekago);
        $thisweek = self::click_count_helper($courseid, $oneweekago, $today);

        return [
            'hits' => [
                $previousweek->hits,
                $thisweek->hits
            ]
        ];
    }

    public static function query_mobile_percentage(int $courseid) {
        global $DB;

        $query = <<<SQL
        SELECT
            desktop_events, mobile_events
        FROM {lalog_course_mobile}
        WHERE
            courseid = ?
SQL;

        $row = $DB->get_record_sql($query, [$courseid]);
        if (!$row) {
            return null;
        }

        $mobile = (float) $row->mobile_events;
        $desktop = (float) $row->desktop_events;

        $percentage = 100 * $mobile / max(1.0, $desktop + $mobile);
        return $percentage;
    }

    public static function query_most_clicked_activity(int $courseid, $privacythreshold) {
        global $DB;

        $date = new \DateTime();
        $date->setTime(23, 59, 59); // Include today.
        $date->modify('-1 week');
        $oneweekago = $date->getTimestamp();

        $query = <<<SQL
        SELECT SQL_NO_CACHE
            m.name as modname,
            cm.id AS cmid,
            COUNT(*) hits
        FROM mdl_modules m
        JOIN mdl_course_modules cm
            ON cm.course = ?
        AND cm.module = m.id
            LEFT JOIN mdl_context ctx
        ON ctx.contextlevel = 70
            AND ctx.instanceid = cm.id
        LEFT JOIN mdl_logstore_lanalytics_log l
            ON l.courseid = cm.course
            AND l.contextid = ctx.id
        WHERE l.timecreated > ?
        GROUP BY cm.id
        HAVING hits > ?
        ORDER BY hits DESC
        LIMIT 1
SQL;

        $row = $DB->get_record_sql($query, [$courseid, $oneweekago, $privacythreshold]);
        if (!$row) {
            return null;
        }

        return [
            'modname' => $row->modname,
            'cmid' => $row->cmid,
            'hits' => $row->hits
        ];
    }

}