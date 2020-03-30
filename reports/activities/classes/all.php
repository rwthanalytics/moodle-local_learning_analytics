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
use local_learning_analytics\settings;
use \context_course;

defined('MOODLE_INTERNAL') || die;

class all extends report_page_base {

    public function run(array $params): array {
        global $USER;
        $courseid = (int) $params['course'];
        $context = context_course::instance($courseid, MUST_EXIST);
        $hasupdatecap = has_capability('moodle/course:update', $context, $USER->id);
        $activities = query_helper::query_activities($courseid, $hasupdatecap);
        $privacythreshold = settings::get_config('dataprivacy_threshold');

        // Find max values.
        $maxhits = 1;
        foreach ($activities as $activity) {
            $maxhits = max($maxhits, (int) $activity->hits);
        }

        $tabledetails = new table();
        $tabledetails->set_header_local(['activity_name', 'activity_type', 'section', 'hits'], 'lareport_activities');

        $modinfo = get_fast_modinfo($courseid);
        $hiddentext = get_string('hidden', 'lareport_activities');
        foreach ($activities as $activity) {
            $namecell = $activity->name;
            if ($namecell === '') {
                $namecell = $modinfo->get_cm($activity->cmid)->name;
            }
            if (!$activity->visible) {
                $namecell = "<span class='dimmed_text'>{$namecell} ({$hiddentext})</span>";
            }
            $cellcontent = ($activity->hits < $privacythreshold) ?
                " < {$privacythreshold}" : table::fancyNumberCell((int) $activity->hits, $maxhits, 'orange');
            $tabledetails->add_row([
                $namecell,
                get_string('modulename', $activity->modname),
                $activity->section_name,
                $cellcontent
            ]);
        }

        $headingtext = get_string('all_activities', 'lareport_activities');

        return [
            "<h3>{$headingtext}</h3>",
            $tabledetails
        ];
    }

    public function params(): array {
        return [
            'course' => required_param('course', PARAM_INT)
        ];
    }
}