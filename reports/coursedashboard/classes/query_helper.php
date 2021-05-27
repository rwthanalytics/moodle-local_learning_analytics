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
            CASE WHEN ue.timestart < {$timestamp} THEN 0 ELSE 1 END AS time,
            COUNT(DISTINCT u.id) AS learners
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
            COUNT(*) AS hits
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

    public static function query_most_clicked_activity(int $courseid, $privacythreshold) {
        global $DB;

        $date = new \DateTime();
        $date->setTime(23, 59, 59); // Include today.
        $date->modify('-1 week');
        $oneweekago = $date->getTimestamp();

        $query = <<<SQL
        SELECT
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
        LIMIT 3
SQL;

        return $DB->get_records_sql($query, [$courseid, $oneweekago, max(1, $privacythreshold)]);
    }

    private static function helper_quiz_and_assigments(int $courseid, int $from, int $to = null) {
        global $DB;

        // assignments
        $assignquery = <<<SQL
        SELECT
            COUNT(1) AS handins
        FROM {assign} AS a
        JOIN {assign_submission} AS am
            ON am.assignment = a.id
        WHERE a.course = ?
            AND am.status = 'submitted'
            AND am.timecreated > ?
            AND am.timecreated <= ?
SQL;
        $assignresult = $DB->get_record_sql($assignquery, [$courseid, $from, $to]);

        // quizzes
        $quizquery = <<<SQL
        SELECT
            COUNT(1) AS attempts
        FROM {quiz} q
        JOIN {quiz_attempts} qa
            ON qa.quiz = q.id
        WHERE q.course = ?
            AND qa.state = 'finished'
            AND qa.timefinish > ?
            AND qa.timefinish <= ?
SQL;
        $quizresult = $DB->get_record_sql($quizquery, [$courseid, $from, $to]);

        return $assignresult->handins + $quizresult->attempts;
    }

    public static function query_quiz_and_assigments(int $courseid, $privacythreshold) {
        global $DB;

        $date = new \DateTime();
        $date->setTime(23, 59, 59); // Include today.
        $today = $date->getTimestamp();
        $date->modify('-1 week');
        $oneweekago = $date->getTimestamp();
        $date->modify('-1 week');
        $twoweeksago = $date->getTimestamp();

        $previousweek = self::helper_quiz_and_assigments($courseid, $twoweeksago, $oneweekago);
        $thisweek = self::helper_quiz_and_assigments($courseid, $oneweekago, $today);

        return [$previousweek, $thisweek];
    }

}
