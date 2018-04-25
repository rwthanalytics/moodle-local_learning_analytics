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
 * Version info for the Sections report
 *
 * @package     local_learning_analytics
 * @copyright   2018 Lehr- und Forschungsgebiet Ingenieurhydrologie - RWTH Aachen University
 * @author      Marcel Behrmann <behrmann@lfi.rwth-aachen.de>
 * @author      Thomas Dondorf <dondorf@lfi.rwth-aachen.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

use local_learning_analytics\local\outputs\plot;
use local_learning_analytics\local\parameter\parameter_course;
use local_learning_analytics\report_base;

class lareport_sections extends report_base {

    /**
     * @return array
     * @throws dml_exception
     */
    public function get_parameter(): array {
        return [
            new parameter_course('course')
        ];
    }

    public function run(array $params): array {
        global $DB;

        $courseid = (int) $params['course'];

        $activity = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
        $context = context_course::instance($activity->id, MUST_EXIST);
        if ($activity->id == SITEID) {
            throw new moodle_exception('invalidcourse');
        }
        // only teachers and managers
        require_capability('moodle/course:update', $context);

        $query = <<<SQL
        SELECT
            IFNULL(s.name, '') AS x,
            COUNT(*) AS y
        FROM {modules} m
        JOIN {course_modules} cm
            ON cm.course = ?
            AND cm.module = m.id
        JOIN {course_sections} s
            ON s.id = cm.section
        LEFT JOIN {context} ctx
            ON ctx.path LIKE (
                SELECT CONCAT(path, '/%')
                FROM {context} ctxin
                    WHERE ctxin.contextlevel = '50'
                    AND ctxin.instanceid = cm.course
            )
            AND ctx.instanceid = cm.id
        LEFT JOIN {logstore_standard_log} log
            ON log.courseid = cm.course
            AND log.contextid = ctx.id
        WHERE m.name <> 'label'
        GROUP BY s.section
        ORDER BY s.section, cm.id;
SQL;

        $sections = $DB->get_records_sql($query, [$courseid]);

        $plot = new plot();
        $plot->add_series_from_sql_records('bar', $sections);

        return [ $plot ];
    }

}