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
 * Learning Analytics Mobile Logger Class
 *
 * @package     local_learning_analytics
 * @copyright   Lehr- und Forschungsgebiet Ingenieurhydrologie - RWTH Aachen University
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace lalog_course_mobile;

use \stdClass;

defined('MOODLE_INTERNAL') || die;

const MOBILE_OS = 10000; // See devices.php of logstore plugin.

class logger {

    public static function log(array $eventrecords) {
        global $DB;

        $bycourse = [];
        foreach ($eventrecords as $record) {
            if ($record->os === 0 || $record->os === '0') { // Unknown os.
                continue;
            }
            if (!isset($bycourse[$record->courseid])) {
                $bycourse[$record->courseid] = [0, 0]; // Array = [desktop, mobile].
            }
            if ($record->os >= MOBILE_OS) {
                $bycourse[$record->courseid][1] += 1;
            } else {
                $bycourse[$record->courseid][0] += 1;
            }
        }

        // Now, we first try to insert the row, this is likely to fail as there is probably already
        // a row for the course, when it fails we update the row instead
        // Unfortunately, it's not possible to do this the other way around as Moodle does not provide
        // an API to call "update_record" with a courseid, therefore this is the way with the fewest
        // database calls.
        foreach ($bycourse as $courseid => $courestats) {
            try {
                $record = new stdClass();
                $record->courseid = $courseid;
                $record->desktop_events = $courestats[0];
                $record->mobile_events = $courestats[1];
                $DB->insert_record('lalog_course_mobile', $record);
            } catch (\dml_write_exception $e) { // Row is already present, update instead.
                $sql = <<<SQL
                UPDATE {lalog_course_mobile}
                SET
                    desktop_events = desktop_events + ?,
                    mobile_events = mobile_events + ?
                WHERE courseid = ?
SQL;
                $courestats[] = $courseid;
                $DB->execute($sql, $courestats);
            }
        }
    }

    public static function truncate() {
        global $DB;
        $DB->execute("TRUNCATE {lalog_course_mobile}");
    }
}
