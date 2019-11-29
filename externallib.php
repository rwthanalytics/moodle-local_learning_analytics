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
 * External Library
 *
 * @package     local_learning_analytics
 * @copyright   Lehr- und Forschungsgebiet Ingenieurhydrologie - RWTH Aachen University
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_learning_analytics\import;

defined('MOODLE_INTERNAL') || die;

class local_learning_analytics_external extends external_api {

    public static function ajax_import_parameters() {
        return new external_function_parameters([
            'action' => new external_value(PARAM_TEXT, 'action to perform'),
            'userid' => new external_value(PARAM_INT, 'Userid'),
            'offset' => new external_value(PARAM_INT, 'Database offset for user')
        ]);
    }

    public static function ajax_import_returns() {
        return new external_single_structure([
            'userid' => new external_value(PARAM_INT, 'Handled user'),
            'offset' => new external_value(PARAM_INT, 'Database offset for user'),
            'maxUserid' => new external_value(PARAM_INT, 'Highest userid in database'),
            'nextOffset' => new external_value(PARAM_INT, 'Offset for next request for the same userid'),
            'perc' => new external_value(PARAM_FLOAT, 'Current progress'), // TODO remove, this should be done via JavaScript
        ]);
    }

    public static function ajax_import(string $action, int $userid, int $offset) {

        if (!is_siteadmin()) {
            throw new moodle_exception('Only admins can import data.');
        }
        session_write_close(); // allow parallel ajax requests

        $import = new import();
        $maxUserid = $import->maxUserid();
        $nextOffset = -1;

        if ($action === 'savepoint') {
            set_config('import_userid', $userid, 'local_learning_analytics');
        } else {
            $result = $import->import_user($userid, $offset);
            if (!$result['completed']) {
                $nextOffset = $result['nextOffset'];
            }
        }
        return [
            'userid' => $userid,
            'offset' => $offset,
            'maxUserid' => $maxUserid,
            'nextOffset' => $nextOffset,
            'perc' => ($userid / $maxUserid)
        ];
    }

    public static function keep_alive_parameters() {
        return new external_function_parameters([
                'session' => new external_value(PARAM_INT, 'session id')
        ]);
    }

    public static function keep_alive_returns() {
        return new external_single_structure([]);
    }

    /**
     * @param int $session
     * @return array
     * @throws dml_exception
     */
    public static function keep_alive(int $session) {
        \local_learning_analytics\tracker::keep_alive($session);
        return [];
    }
}