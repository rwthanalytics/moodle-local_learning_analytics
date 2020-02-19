<?php

namespace lalog_course_mobile;

use \stdClass;

const MOBILE_OS = 10000; // see devices.php of logstore plugin

class logger {

    public static function log(array $eventrecords) {
        global $DB;

        $byCourse = [];
        foreach ($eventrecords as $record) {
            if ($record->os === 0 || $record->os === '0') { // unknown os
                continue;
            }
            if (!isset($byCourse[$record->courseid])) {
                $byCourse[$record->courseid] = [0, 0]; // [desktop, mobile]
            }
            if ($record->os >= MOBILE_OS) {
                $byCourse[$record->courseid][1] += 1;
            } else {
                $byCourse[$record->courseid][0] += 1;
            }
        }
        
        // now, we first try to insert the row, this is likely to fail as there is probably already
        // a row for the course, when it fails we update the row instead
        // Unfortunately, it's not possible to do this the other way around as Moodle does not provide
        // an API to call "update_record" with a courseid, therefore this is the way with the fewest
        // database calls
        foreach ($byCourse as $courseid => $courestats) {
            try {
                $record = new stdClass();
                $record->courseid = $courseid;
                $record->desktop_events = $courestats[0];
                $record->mobile_events = $courestats[1];
                $DB->insert_record('lalog_course_mobile', $record);
            } catch (\dml_write_exception $e) { // row is already present, update instead
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
