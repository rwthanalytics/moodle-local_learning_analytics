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
 * @copyright   2018 Lehr- und Forschungsgebiet Ingenieurhydrologie - RWTH Aachen University
 * @author      Marcel Behrmann <behrmann@lfi.rwth-aachen.de>
 * @author      Thomas Dondorf <dondorf@lfi.rwth-aachen.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

use local_learning_analytics\local\outputs\plot;
use local_learning_analytics\local\outputs\table;
use local_learning_analytics\parameter;
use local_learning_analytics\report_base;

class lareport_activities extends report_base {

    public function get_parameter(): array {
        return [
            new parameter('course', parameter::TYPE_COURSE, true, FILTER_SANITIZE_NUMBER_INT),
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
            COALESCE(modq.name, modr.name, modas.name, modurl.name, modf.name, modpage.name, modquest.name, modfolder.name, modwiki.name) AS name,
            m.name as modname,
            cm.id AS cmid,
            cm.instance AS objectid,
            s.name AS section_name,
            s.section AS section_pos,
            m.visible,
            COUNT(*) hits
        FROM {modules} m
        JOIN {course_modules} cm
            ON cm.course = ?
            AND cm.module = m.id
        JOIN {course_sections} s
            ON s.id = cm.section
        LEFT JOIN {quiz} modq
            ON modq.id = cm.instance
            AND m.name = 'quiz'
        LEFT JOIN {resource} modr
            ON modr.id = cm.instance
            AND m.name = 'resource'
        LEFT JOIN {assign} modas
            ON modas.id = cm.instance
            AND m.name = 'assign'
        LEFT JOIN {url} modurl
            ON modurl.id = cm.instance
            AND m.name = 'url'
        LEFT JOIN {forum} modf
            ON modf.id = cm.instance
            AND m.name = 'forum'
        LEFT JOIN {page} modpage
            ON modpage.id = cm.instance
            AND m.name = 'page'
        LEFT JOIN {questionnaire} modquest
            ON modquest.id = cm.instance
            AND m.name = 'questionnaire'
        LEFT JOIN {folder} modfolder
            ON modfolder.id = cm.instance
        LEFT JOIN {wiki} modwiki
            ON modwiki.id = cm.instance
            AND m.name = 'wiki'
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
        GROUP BY cm.id
        ORDER BY section_pos, cm.id
SQL;

        $activities = $DB->get_records_sql($query, [$courseid]);

        // find max values
        $maxHits = 1;

        $hitsByTypeAssoc = [];

        foreach ($activities as $activity) {
            $maxHits = max($maxHits, (int) $activity->hits);
            if (!$hitsByTypeAssoc[$activity->modname]) {
                $hitsByTypeAssoc[$activity->modname] = 0;
            }
            $hitsByTypeAssoc[$activity->modname] += $activity->hits;
        }
        $hitsByType = [];
        $maxHitsByType = 1;
        foreach ($hitsByTypeAssoc as $type => $hits) {
            $hitsByType[] = ['type' => $type, 'hits' => $hits];
            $maxHitsByType = max($maxHitsByType, $hits);
        }

        usort($hitsByType, function ($item1, $item2) {
            return $item2['hits'] <=> $item1['hits'];
        });

        $tableTypes = new table();
        $tableTypes->set_header_local(['activity_type', 'hits'], 'lareport_activities');

        foreach ($hitsByType as $item) {
            $tableTypes->add_row([
                $item['type'],
                table::fancyNumberCell((int) $item['hits'], $maxHitsByType, 'green')
            ]);
        }

        $tableDetails = new table();
        $tableDetails->set_header_local(['activity_name', 'activity_type', 'section', 'hits'], 'lareport_activities');

        foreach ($activities as $activity) {
            $nameCell = $activity->name;
            if (!$activity->visible) {
                $nameCell = '<del>${$nameCell}</del>';
            }
            $tableDetails->add_row([
                $nameCell,
                $activity->modname,
                $activity->section_name,
                table::fancyNumberCell((int) $activity->hits, $maxHits, 'orange')
            ]);
        }

        $plot = new plot();
        $plot->set_height(300);
        $plot->add_series_from_sql_records('bar', $activities, ['x' => 'name', 'y' => 'hits']);

        return [
            $plot,
            $tableTypes,
            '<h3>Detailed activities</h3>',
            $tableDetails
        ];
    }
}