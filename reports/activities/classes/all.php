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

namespace lareport_activities;

use local_learning_analytics\report_page_base;
use lareport_activities\query_helper;
use local_learning_analytics\local\outputs\table;

defined('MOODLE_INTERNAL') || die;

class all extends report_page_base {

    public function run(array $params): array {

        $courseid = (int) $params['course'];
        $activities = query_helper::query_activities($courseid);

        // find max values
        $maxHits = 1;
        foreach ($activities as $activity) {
            $maxHits = max($maxHits, (int) $activity->hits);
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

        $headingText = get_string('all_activities', 'lareport_activities');

        return [
            "<h2>{$headingText}</h2>",
            $tableDetails
        ];
    }

    public function params(): array {
        return [
            'course' => required_param('course', PARAM_INT)
        ];
    }
}