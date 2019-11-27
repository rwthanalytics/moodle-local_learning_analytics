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

namespace local_learning_analytics\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\context;
use core_privacy\local\request\contextlist;

defined('MOODLE_INTERNAL') || die;

/**
 * Class provider
 *
 * @package local_learning_analytics\privacy
 */
class provider implements
        \core_privacy\local\metadata\provider,
        \core_privacy\local\request\plugin\provider {

    /**
     * Returns meta data about this system.
     *
     * @param   collection $collection The initialised collection to add items to.
     * @return  collection     A listing of user data stored through this system.
     */
    public static function get_metadata(collection $collection): collection {

        $collection->add_database_table(
                'local_learning_analytics_sum',
                [
                        'id' => 'privacy:metadata:local_learning_analytics_sum:id',
                        'courseid' => 'privacy:metadata:local_learning_analytics_sum:courseid',
                        'userid' => 'privacy:metadata:local_learning_analytics_sum:userid',
                        'hits' => 'privacy:metadata:local_learning_analytics_sum:hits',
                ],
                'privacy:metadata:local_learning_analytics_sum');

        $collection->add_database_table(
                'local_learning_analytics_ses',
                [
                        'id' => 'privacy:metadata:local_learning_analytics_ses:id',
                        'summaryid' => 'privacy:metadata:local_learning_analytics_ses:summaryid',
                        'hits' => 'privacy:metadata:local_learning_analytics_ses:hits',
                        'firstaccess' => 'privacy:metadata:local_learning_analytics_ses:firstaccess',
                        'lastaccess' => 'privacy:metadata:local_learning_analytics_ses:lastaccess',
                        'device' => 'privacy:metadata:local_learning_analytics_ses:device',
                        'browser' => 'privacy:metadata:local_learning_analytics_ses:browser',
                        'os' => 'privacy:metadata:local_learning_analytics_ses:os'
                ],
                'privacy:metadata:local_learning_analytics_ses');

        return $collection;
    }

    /**
     * Get the list of contexts that contain user information for the specified user.
     *
     * @param   int $userid The user to search.
     * @return  contextlist   $contextlist  The contextlist containing the list of contexts used in this plugin.
     */
    public static function get_contexts_for_userid(int $userid): contextlist {
        $list = new contextlist();

        $sql = "SELECT ctx.id
                FROM {course} c
                JOIN (
                    SELECT DISTINCT e.courseid
                    FROM {enrol} e
                    JOIN {user_enrolments} ue ON (ue.enrolid = e.id AND ue.userid = :userid)
                ) en ON (en.courseid = c.id)
                LEFT JOIN {context} ctx ON (ctx.instanceid = c.id AND ctx.contextlevel = :contextlevel)
                WHERE c.id <> :siteid";

        $params = [
                'siteid' => SITEID,
                'userid' => $userid,
                'contextlevel' => CONTEXT_COURSE
        ];

        $list->add_from_sql($sql, $params);

        return $list;
    }

    /**
     * Export all user data for the specified user, in the specified contexts.
     *
     * @param   approved_contextlist $contextlist The approved contexts to export information for.
     */
    public static function export_user_data(approved_contextlist $contextlist) {
        // TODO: Implement export_user_data() method.
    }

    /**
     * Delete all data for all users in the specified context.
     *
     * @param   context $context The specific context to delete data for.
     */
    public static function delete_data_for_all_users_in_context(\context $context) {
        // TODO: Implement delete_data_for_all_users_in_context() method.
    }

    /**
     * Delete all user data for the specified user, in the specified contexts.
     *
     * @param   approved_contextlist $contextlist The approved contexts and user information to delete information for.
     */
    public static function delete_data_for_user(approved_contextlist $contextlist) {
        // TODO: Implement delete_data_for_user() method.
    }
}