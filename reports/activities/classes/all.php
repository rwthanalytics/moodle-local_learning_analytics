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

defined('MOODLE_INTERNAL') || die;

class all extends report_page_base {

    public function run(array $params): array {
        global $USER;
        $courseid = (int) $params['course'];
        $activities = query_helper::query_activities($courseid);
        $privacythreshold = settings::get_config('dataprivacy_threshold');

        $modinfo = get_fast_modinfo($courseid);
        $allcms = $modinfo->get_cms();
        $format = \course_get_format($courseid);
        $cms = [];
        $maxhits = 1;
        foreach ($allcms as $cmid => $cm) {
            if ($cm->modname === 'label' || !isset($activities[$cmid]) || !$cm->uservisible) {
                continue; // skip labels and unknown activity (should only happen if cache is messed up)
            }
            $cms[] = $cm;
            $maxhits = max($maxhits, (int) $activities[$cm->id]->hits);
        }

        $modnameshumanreadable = $modinfo->get_used_module_names();
        
        $tabledetails = new table();
        $tabledetails->set_header_local(['activity_name', 'activity_type', 'section', 'table_header_hits'], 'lareport_activities');

        $hiddentext = get_string('hiddenwithbrackets');
        foreach ($cms as $cm) {
            if ($cm->modname === 'label' || !isset($activities[$cm->id])) {
                continue; // skip labels and unknown activity (should only happen if cache is messed up)
            }
            $activity = $activities[$cm->id];
            $namecell = $cm->name;
            $section = $cm->get_section_info();
            if (!$cm->visible) {
                $namecell = "<span class='dimmed_text'>{$namecell} {$hiddentext}</span>";
            }
            $cellcontent = ($activity->hits < $privacythreshold) ?
                " < {$privacythreshold}" : table::fancyNumberCell((int) $activity->hits, $maxhits, 'orange');
            $tabledetails->add_row([
                $namecell,
                $modnameshumanreadable[$cm->modname],
                $format->get_section_name($section),
                $cellcontent
            ]);
        }

        $headingtext = get_string('all_activities', 'lareport_activities');

        return [
            self::heading(get_string('all_activities', 'lareport_activities'), false),
            $tabledetails
        ];
    }

    public function params(): array {
        return [
            'course' => required_param('course', PARAM_INT)
        ];
    }
}