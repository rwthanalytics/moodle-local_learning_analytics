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
 * @copyright   Lehr- und Forschungsgebiet Ingenieurhydrologie - RWTH Aachen University
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

use local_learning_analytics\local\outputs\plot;
use local_learning_analytics\local\outputs\table;
use local_learning_analytics\report_base;
use lareport_activities\query_helper;
use local_learning_analytics\router;
use local_learning_analytics\settings;

class lareport_activities extends report_base {

    private static $markercolors = [
        'quiz' => '#A9CF54', // Green.
        'resource' => '#66b5ab', // Blue.
        'page' => '#EA030E', // Red.
        'url' => '#F26522', // Orange.
        'forum' => '#ffda6e', // Yellow.
        'wiki' => '#ffda6e', // Yellow.
    ];
    private static $markercolordefault = '#bbbbbb';
    private static $markercolorstext = [
        'quiz' => 'green', // Green.
        'resource' => 'blue', // Blue.
        'page' => 'red', // Red.
        'url' => 'orange', // Orange.
        'forum' => 'yellow', // Yellow.
        'wiki' => 'yellow', // Yellow.
    ];
    private static $markercolortextdefault = 'gray';

    public function run(array $params): array {
        global $USER;
        $courseid = $params['course'];
        $context = context_course::instance($courseid, MUST_EXIST);
        $hasupdatecap = has_capability('moodle/course:update', $context, $USER->id);
        $privacythreshold = settings::get_config('dataprivacy_threshold');

        $filter = '';
        $filtervalues = [];
        if (!empty($params['mod'])) {
            $filter = "m.name = ?";
            $filtervalues[] = $params['mod'];
        }
        $activities = query_helper::query_activities($courseid, $hasupdatecap, $filter, $filtervalues);

        // Find max values.
        $maxhits = 0;

        $hitsbytypeassoc = [];

        foreach ($activities as $activity) {
            $maxhits = max($maxhits, (int) $activity->hits);
            if (!isset($hitsbytypeassoc[$activity->modname])) {
                $hitsbytypeassoc[$activity->modname] = 0;
            }
            $hitsbytypeassoc[$activity->modname] += $activity->hits;
        }

        if ($maxhits === 0) {
            return [get_string('no_data_to_show', 'lareport_activities')];
        }

        $hitsbytype = [];
        $maxhitsbytype = 1;
        foreach ($hitsbytypeassoc as $type => $hits) {
            $hitsbytype[] = ['type' => $type, 'hits' => $hits];
            $maxhitsbytype = max($maxhitsbytype, $hits);
        }

        usort($hitsbytype, function ($item1, $item2) {
            return $item2['hits'] <=> $item1['hits'];
        });

        $tabletypes = new table();
        $tabletypes->set_header_local(['activity_type', 'hits'], 'lareport_activities');

        foreach ($hitsbytype as $item) {
            $url = router::report('activities', ['course' => $courseid, 'mod' => $item['type']]);
            $hits = ($privacythreshold === 0) ? (int) $item['hits'] : (floor(((int) $item['hits']) / $privacythreshold) * $privacythreshold);
            $typestr = get_string('modulename', $item['type']);
            if ($hits >= $privacythreshold) {
                $tabletypes->add_row([
                    "<a href='{$url}'>{$typestr}</a>",
                    table::fancyNumberCell(
                        $hits,
                        $maxhitsbytype,
                        self::$markercolorstext[$item['type']] ?? self::$markercolortextdefault
                    )
                ]);
            }
        }

        if (!empty($params['mod'])) {
            $linktoreset = router::report('activities', ['course' => $courseid]);
            $tabletypes->add_show_more_row($linktoreset);
        }

        $tabledetails = new table();
        $tabledetails->set_header_local(['activity_name', 'activity_type', 'section', 'hits'], 'lareport_activities');

        $x = [];
        $y = [];
        $texts = [];
        $markercolors = [];

        foreach ($activities as $activity) {
            $x[] = $activity->name;
            $y[] = $activity->hits < $privacythreshold ? 0 : $activity->hits;
            $texts[] = $activity->hits < $privacythreshold ? "< {$privacythreshold}" : $activity->hits;
            $markercolors[] = self::$markercolors[$activity->modname] ?? self::$markercolordefault;
        }

        // Reorder to show most used activities.

        usort($activities, function ($act1, $act2) {
            return $act2->hits <=> $act1->hits;
        });

        $modinfo = get_fast_modinfo($courseid);
        $hiddentext = get_string('hidden', 'lareport_activities');
        $headinttoptext = get_string('most_used_activities', 'lareport_activities');
        foreach ($activities as $i => $activity) {
            if ($i >= 5) { // Stop when some reports are shown.
                break;
            }
            $namecell = $activity->name;
            if ($namecell === '') {
                $namecell = $modinfo->get_cm($activity->cmid)->name;
            }
            if (!$activity->visible) {
                $namecell = "<span class='dimmed_text'>{$namecell} ({$hiddentext})</span>";
            }
            if ($activity->hits >= $privacythreshold) {
                $tabledetails->add_row([
                    $namecell,
                    get_string('modulename', $activity->modname),
                    $activity->section_name,
                    table::fancyNumberCell(
                        (int) $activity->hits,
                        $maxhits,
                        self::$markercolorstext[$activity->modname] ?? self::$markercolortextdefault
                    )
                ]);
            }
        }

        $linktofulllist = router::report_page('activities', 'all', ['course' => $courseid]);
        $tabledetails->add_show_more_row($linktofulllist);

        $plot = new plot();
        $plot->set_height(300);
        $plot->show_toolbar(false);
        $plot->add_series([
            'type' => 'bar',
            'x' => $x,
            'y' => $y,
            'text' => $texts,
            'hoverinfo' => 'text',
            'marker' => [
                'color' => $markercolors
            ]
        ]);

        $layout = new stdClass();
        $layout->margin = ['l' => 80, 'r' => 80, 't' => 20, 'b' => 100];
        $plot->set_layout($layout);

        return [
            $plot,
            $tabletypes,
            "<h3>{$headinttoptext}</h3>",
            $tabledetails
        ];
    }

    public function params(): array {
        return [
            'course' => required_param('course', PARAM_INT),
            'mod' => optional_param('mod', null, PARAM_RAW)
        ];
    }
}