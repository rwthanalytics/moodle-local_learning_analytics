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
use lareport_activities\query_helper;
use local_learning_analytics\local\routing\router;

class lareport_activities extends report_base {

    private static $markerColors = [
        'quiz' => '#A9CF54', // green
        'resource' => '#66b5ab', // blue
        'page' => '#EA030E', // red
        'url' => '#F26522', // orange
        'forum' => '#ffda6e', // yellow
        'wiki' => '#ffda6e', // yellow
    ];

    public function get_parameter(): array {
        return [
            new parameter('course', parameter::TYPE_COURSE, true, FILTER_SANITIZE_NUMBER_INT),
            new parameter('mod', parameter::TYPE_STRING, false),
        ];
    }

    public function run(array $params): array {
        $courseid = (int) $params['course'];

        $filter = '';
        $filterValues = [];
        if (!empty($params['mod'])) {
            $filter = "m.name = ?";
            $filterValues[] = $params['mod'];
        }
        $activities = query_helper::query_activities($courseid, $filter, $filterValues);

        // find max values
        $maxHits = 1;

        $hitsByTypeAssoc = [];

        foreach ($activities as $activity) {
            $maxHits = max($maxHits, (int) $activity->hits);
            if (!isset($hitsByTypeAssoc[$activity->modname])) {
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
            $url = router::report('activities', ['course' => $courseid, 'mod' => $item['type']]);
            $tableTypes->add_row([
                "<a href='{$url}'>{$item['type']}</a>",
                table::fancyNumberCell((int) $item['hits'], $maxHitsByType, 'green')
            ]);
        }

        if (empty($params['mod'])) {
            $linkToReset = router::report('activities', ['course' => $courseid]);
            $tableTypes->add_show_more_row($linkToReset);
        }

        $tableDetails = new table();
        $tableDetails->set_header_local(['activity_name', 'activity_type', 'section', 'hits'], 'lareport_activities');

        $x = [];
        $y = [];
        $markerColors = [];

        foreach ($activities as $activity) {
            $x[] = $activity->name;
            $y[] = $activity->hits;
            $markerColors[] = self::$markerColors[$activity->modname] ?? '#bbbbbb';
        }

        // reorder to show most used activities

        usort($activities, function ($act1, $act2) {
            return $act2->hits <=> $act1->hits;
        });

        $headintTopText = get_string('most_used_activities', 'lareport_activities');
        foreach ($activities as $i => $activity) {
            if ($i >= 5) { // stop when some reports are shown
                break;
            }
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

        $linkToFullList = router::report_page('activities', 'all', ['course' => $courseid]);
        $tableDetails->add_show_more_row($linkToFullList);

        $plot = new plot();
        $plot->set_height(300);
        $plot->show_toolbar(false);
        $plot->add_series([
            'type' => 'bar',
            'x' => $x,
            'y' => $y,
            'marker' => [
                'color' => $markerColors
            ]
        ]);

        return [
            $plot,
            $tableTypes,
            "<h3>{$headintTopText}</h3>",
            $tableDetails
        ];
    }
}