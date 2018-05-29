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
 * @copyright   2018 Lehr- und Forschungsgebiet Ingenieurhydrologie - RWTH Aachen University
 * @author      Marcel Behrmann <behrmann@lfi.rwth-aachen.de>
 * @author      Thomas Dondorf <dondorf@lfi.rwth-aachen.de>
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
        $startdate->modify('Monday this week'); // get start of week

        $mondayTimestamp = $startdate->format('U');

        $query = <<<SQL
        SELECT
            (FLOOR((ses.firstaccess - {$mondayTimestamp}) / (7 * 60 * 60 * 24)) + 1) AS week,
            COUNT(*) sessions,
            COUNT(DISTINCT su.userid) users,
            su.*,
            ses.*
        FROM {local_learning_analytics_sum} su
        JOIN {local_learning_analytics_ses} ses
            ON su.id = ses.summaryid
        WHERE su.courseid = ?
        GROUP BY week
        #    HAVING week > 0
        ORDER BY week;
SQL;

        return $DB->get_records_sql($query, [$courseid]);
    }

    // returns array like [100, 50] meaning 100 students were already registered since last week
    // and 50 more students join in the last days
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

    private static function click_count_helper(int $courseid, int $from, int $to) {
        global $DB;

        // TODO: we can put the active users in by using COUNT(DISTINCT su.id) users

        $query = <<<SQL
        SELECT SQL_NO_CACHE
            COUNT(DISTINCT su.id) users,
            COALESCE(SUM(ses.hits), 0) hits
        FROM {local_learning_analytics_sum} su
        JOIN {local_learning_analytics_ses} ses
            ON ses.summaryid = su.id
        WHERE su.courseid = ?
            AND ses.firstaccess > ?
            AND ses.firstaccess <= ?;
SQL;

        $res = $DB->get_records_sql($query, [$courseid, $from, $to]);
        return reset($res);
    }

    // return counts: ['users' => [previous week, this week], 'hits' => [prev, this]]
    public static function query_click_count(int $courseid) : array {

        $date = new \DateTime();
        $date->setTime(0, 0, 0); // exclude today
        $today = $date->getTimestamp();
        $date->modify('-1 week');
        $oneweekago = $date->getTimestamp();
        $date->modify('-1 week');
        $twoweeksago = $date->getTimestamp();

        $previousWeek = self::click_count_helper($courseid, $twoweeksago, $oneweekago);
        $thisWeek = self::click_count_helper($courseid, $oneweekago, $today);

        return [
            'users' => [
                $previousWeek->users,
                $thisWeek->users
            ],
            'hits' => [
                $previousWeek->hits,
                $thisWeek->hits
            ]
        ];
    }

}