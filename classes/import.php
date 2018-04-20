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

/*
 * @package     local_learning_analytics
 * @copyright   2018 Lehr- und Forschungsgebiet Ingenieurhydrologie - RWTH Aachen University
 * @author      Marcel Behrmann <behrmann@lfi.rwth-aachen.de>
 * @author      Thomas Dondorf <dondorf@lfi.rwth-aachen.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_learning_analytics;

class import {

    private static $sessionTimeout = 60 * 60 * 2;

    public function __construct() {
    }

    public function table_is_empty() : bool {
        global $DB;

        $query = <<<SQL
        SELECT id FROM {local_learning_analytics_sum} LIMIT 1;
SQL;
        $results = $DB->get_records_sql($query);
        $emptyCheck = count($results);

        return $emptyCheck === 0;
    }

    public function estimate() : array {
        global $DB;

        $query = <<<SQL
        SELECT
            (SELECT id FROM {logstore_standard_log} ORDER BY id LIMIT 1) firstid,
            (SELECT id FROM {logstore_standard_log} ORDER BY id DESC LIMIT 1) lastid,
	        (SELECT id FROM {user} ORDER BY id DESC LIMIT 1) users;
SQL;

        $results = $DB->get_records_sql($query);
        $data = reset($results);

        return array(
            'users' => $data->users,
            'logs' => (1 + $data->lastid - $data->firstid)
        );
    }

    // returns last id to continue
    public function import_user(int $userid, int $offset = 0, int $line_limit = 5000) : array {
        global $DB;

        $query = <<<SQL
        SELECT
            id, timecreated, courseid
        FROM {logstore_standard_log}
        WHERE
            userid = ?
            AND courseid <> 0
        ORDER BY courseid, id
        LIMIT {$offset}, {$line_limit}
SQL;
        $lines = $DB->get_records_sql($query, [$userid]);

        if (count($lines) === 0) {
            return ['records' => 0, 'completed' => true];
        }

        $totalHits = 0;

        $courseid = -1;
        $hits = 0;
        $firstaccess = 0;
        $lastaccess = 0;
        $savedSessions = -1; // ignore first session save
        foreach ($lines as $line) {
            if ($courseid !== (int) $line->courseid || ($lastaccess + self::$sessionTimeout) < (int) $line->timecreated) {
                // new session start
                $totalHits += $hits;
                $this->saveSession($userid, $courseid, $hits, $firstaccess, $lastaccess);
                $courseid = (int) $line->courseid;
                $hits = 0;
                $firstaccess = (int) $line->timecreated;
                $savedSessions++;
            }

            $hits++;
            $lastaccess = (int) $line->timecreated;
        }

        if (count($lines) < $line_limit) {
            // also save last session as this is the last session of the user
            $totalHits += $hits;
            $this->saveSession($userid, $courseid, $hits, $firstaccess, $lastaccess);
            echo ' max LIMIT!!';
            return ['records' => $totalHits, 'completed' => true];
        }

        // TODO: if number of lines is smaller than $lines_limit, also save the last session as it has already ended

        if ($savedSessions === 0) {
            // user has a session that is bigger than our interval (this should happen only in extreme cases)
            // rerun, but increase number of lines
            echo 'EXTREME CASE!!!!'; // TODO remove
            return $this->import_user($userid, $offset, $line_limit * 2);
        }

        return ['records' => $totalHits, 'completed' => false, 'nextOffset' => ($offset + $totalHits)];
    }

    private $lastSavedUserid = -1;
    private $lastSavedCourseid = -1;
    private $lastSummaryid = -1;
    private $lastSummaryHits = 0;

    private function updateSummary($id, $hits) {
        global $DB;

        $updatedSummary = new \stdClass();
        $updatedSummary->id = $id;
        $updatedSummary->hits = $hits;
        $DB->update_record_raw('local_learning_analytics_sum', $updatedSummary);
    }

    private function saveSession($userid, $courseid, $hits, $firstaccess, $lastaccess) : void {
        global $DB;

        if ($courseid === -1) {
            return; // empty session
        }

        if ($this->lastSavedCourseid === $courseid && $this->lastSavedUserid === $userid) {
            // just reuse summaryid
            $this->lastSummaryHits = $this->lastSummaryHits + $hits;
            $this->updateSummary($this->lastSummaryid, $this->lastSummaryHits);
        } else {
            $summary = $DB->get_record_sql('SELECT id, hits
            FROM {local_learning_analytics_sum}
            WHERE courseid = ? AND userid = ?', array($courseid, $userid));

            if ($summary === false) { // no summary for course/user so far
                $newSummary = new \stdClass();
                $newSummary->courseid = $courseid;
                $newSummary->userid = $userid;
                $newSummary->hits = $hits;
                $this->lastSummaryid = $DB->insert_record_raw('local_learning_analytics_sum', $newSummary);
                $this->lastSummaryHits = $hits;
            } else {
                $this->lastSummaryHits = $summary->hits + $hits;
                $this->lastSummaryid = $summary->id;
                $this->updateSummary($this->lastSummaryid, $this->lastSummaryHits);
            }
            $this->lastSavedUserid = $userid;
            $this->lastSavedCourseid = $courseid;
        }

        $record = new \stdClass();
        $record->summaryid = $this->lastSummaryid;
        $record->hits = $hits;
        $record->firstaccess = $firstaccess;
        $record->lastaccess = $lastaccess;
        $record->device = '0';
        $record->browser = '0';
        $record->os = '0';
        $DB->insert_record('local_learning_analytics_ses', $record, false, true);
    }

}