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

const MAX_LENGTH_SECTION_NAMES = 13;

class lareport_activities extends report_base {

    private static $markercolors = [
        'quiz' => '#A9CF54', // Green.
        'resource' => '#66b5ab', // Blue.
        'page' => '#EA030E', // Red.
        'url' => '#F26522', // Orange.
        'forum' => '#ffda6e', // Yellow.
        'wiki' => '#ffda6e', // Yellow.
        'assign' => '#000080', // Navy.
        'pdfannotator' => '#d55351', // pdfred.
    ];
    private static $markercolordefault = '#bbbbbb';
    private static $markercolorstext = [
        'quiz' => 'green',
        'resource' => 'blue',
        'page' => 'red',
        'url' => 'orange',
        'forum' => 'yellow',
        'wiki' => 'yellow',
        'assign' => 'navy',
        'pdfannotator' => 'pdfred',
    ];
    private static $markercolortextdefault = 'gray';

    public function run(array $params): array {
        global $USER, $OUTPUT;
        $courseid = $params['course'];
        $privacythreshold = (int) settings::get_config('dataprivacy_threshold');
        $filtertext = !empty($params['filter']) ? $params['filter'] : null;

        $filter = '';
        $filtervalues = [];
        $ismodfilteractive = !empty($params['mod']);
        if ($ismodfilteractive) {
            $filter = "m.name = ?";
            $filtervalues[] = $params['mod'];
        }
        $activities = query_helper::query_activities($courseid, $filter, $filtervalues);

        // Find max values.
        $maxhits = 0;
        $hitsbytypeassoc = [];

        $modinfo = get_fast_modinfo($courseid);
        // $allcms = $modinfo->get_cms();
        // $format = \course_get_format($courseid);

        // Generating fake data
        // $mapper = function($cm) use ($format) {
        //     $section = $cm->get_section_info();
        //     return (object) [
        //         'id' => $cm->id,
        //         'url' => '',
        //         'visible' => true,
        //         'modname' => $cm->modname,
        //         'uservisible' => $cm->uservisible,
        //         'name' => $cm->name,
        //         'section' => (object) [
        //             'id' => $section->id,
        //             'name' => $format->get_section_name($section),
        //         ]
        //     ];
        // };
        // $newdata = array_map($mapper, $allcms);
        // var_export($newdata);
        // die();

        $allcms = \local_learning_analytics\demo::data('activities', 'cms');

        $cms = [];
        foreach ($allcms as $cmid => $cm) { // filter out all modules we don't want to show, that's the list we will be working with
            if ($cm->modname === 'label' || !isset($activities[$cmid]) || !$cm->uservisible
                || ($ismodfilteractive && $cm->modname !== $params['mod'])
                || ($filtertext !== null && strpos($cm->name, $filtertext) === false)) {
                continue; // skip labels and unknown activity (should only happen if cache is messed up)
            }
            $cms[] = $cm;
        }
        $modnameshumanreadable = get_module_types_names(false);
        // $modnameshumanreadable = $modinfo->get_used_module_names();
        
        // Create plot at the top
        $x = [];
        $y = [];
        $texts = [];
        $ticktexts = [];
        $markercolors = [];
        $sections = []; // [splitposition: int, name: string]
        $lastsectionid = -1;

        $hitsstring = get_string('hits', 'lareport_activities');
        $i = 0;
        foreach ($cms as $cm) {
            $activity = $activities[$cm->id];
            $maxhits = max($maxhits, (int) $activity->hits);
            if (!isset($hitsbytypeassoc[$cm->modname])) {
                $hitsbytypeassoc[$cm->modname] = 0;
            }
            $hitsbytypeassoc[$cm->modname] += $activity->hits;
            
            // dotted lines to separate sections
            $activity = $activities[$cm->id];
            $section = $cm->section;
            if ($lastsectionid !== $section->id) {
                $sections[] = [$i, $section->name];
                $lastsectionid = $section->id;
            }
            $x[] = $i;
            $ticktexts[] = $cm->name;
            $y[] = $activity->hits < $privacythreshold ? 0 : $activity->hits;
            $hitstext = $activity->hits < $privacythreshold ? "< {$privacythreshold}" : $activity->hits;
            $typestr = $modnameshumanreadable[$cm->modname];
            $texts[] = "{$typestr}: {$cm->name}<br>{$hitstext} {$hitsstring}";
            $markercolors[] = self::$markercolors[$cm->modname] ?? self::$markercolordefault;
            $i += 1;
        }

        // Create dividers for sections (dotted line areas)
        $shapes = [];
        $annotations = [];
        for ($i = 1; $i < count($sections); $i += 1) {
            $sectionx = $sections[$i][0];
            $sectionnamefull = $sections[$i][1];
            $sectionname = $sectionnamefull;
            if (strlen($sectionname) > MAX_LENGTH_SECTION_NAMES) {
                $sectionname = mb_substr($sectionname, 0, MAX_LENGTH_SECTION_NAMES - 2) . '…';
            }
            $shapes[] = [ // dotted line to separate sections
                'type' => 'line',
                'xref' => 'x',
                'x0' => ($sectionx - 0.5),
                'x1' => ($sectionx - 0.5),
                'yref' => 'paper',
                'y0' => 0,
                'y1' => 1.3,
                'line' => [ 'color' => '#666', 'width' => 1.5, 'dash' => 'dot' ]
            ];
            $nextx = ($i !== count($sections) - 1) ? $sections[$i + 1][0] : count($cms);
            if ($i % 2 === 1) { // add grey background to every second section
                $shapes[] = [
                    'type' => 'rect',
                    'xref' => 'x',
                    'x0' => ($sectionx - 0.5),
                    'x1' => ($nextx - 0.5),
                    'yref' => 'paper',
                    'y0' => 0,
                    'y1' => 1.2,
                    'fillcolor' => 'rgba(0,0,0,0.04)',
                    'line' => [ 'width' => 0 ],
                    // 'line' => [ 'color' => '#aaa', 'width' => 1, 'dash' => 'dot' ]
                ];
            }
            $annotations[] = [ // section label text at the top
                'text' => $sectionname,
                'showarrow' => false,
                'bgcolor' => 'rgba(255,255,255,0.95)',
                'bordercolor' => '#eee',
                'borderpad' => 3,
                'xref' => 'x',
                'xanchor' => 'center',
                // 'x' => $sectionx - 0.4,
                'x' => ($sectionx + $nextx - 1) / 2,
                'yref' => 'paper',
                'yanchor' => 'top',
                'y' => 1.1,
                'hovertext' => get_string('section'). ': ' . $sectionnamefull
                // 'clicktoshow' => 'onout' // hide when clicked on the plot
            ];
        }

        // Create plot 
        $plot = new plot();
        $plot->set_height(300);
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
        $layout->shapes = $shapes;
        $layout->annotations = $annotations;
        $layout->xaxis = [
            'ticktext' => $ticktexts,
            'tickvals' => $x
        ];
        $plot->set_layout($layout);

        // filter form in the top right
        $filtervalue = $filtertext === null ? '' : htmlspecialchars($filtertext);
        $filteractlangstr = get_string('filter_by_name', 'lareport_activities');
        $filterlangstr = get_string('filter', 'lareport_activities');
        $removefilterlink = '';
        if ($filtertext !== null) {
            $removefilterlang = get_string('remove_filter', 'lareport_activities');
            $removefilterlink = " (<a href='?course={$courseid}'>{$removefilterlang}</a>)";
        }
        $filterprefix = '<form class="headingfloater" action="activities" method="get">
        <div class="form-inline">
            <label for="filterinput">' . $filteractlangstr . $removefilterlink . ':</label>
            <div class="input-group">
                <input type="text" class="form-control" name="filter" value="'. $filtervalue . '">
                <div class="input-group-append"><button class="btn btn-secondary" type="submit">' . $filterlangstr . '</button></div>
            </div>
        </div><input type="hidden" name="course" value ="'.$courseid.'"/>
        </form>';

        if ($maxhits < $privacythreshold) {
            $heading = self::heading(get_string('pluginname', 'lareport_activities'), true, $filterprefix);
            if (!empty($filtertext)) {
                return [$heading,get_string('no_data_to_show_filter', 'lareport_activities')];
            }
            return [$heading,get_string('no_data_to_show', 'lareport_activities')];
        }

        // Table for each activity type
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
        $tabletypes->set_header_local(['activity_type', 'table_header_hits'], 'lareport_activities');

        foreach ($hitsbytype as $item) {
            $icon = $OUTPUT->pix_icon('icon', '', $item['type'], array('class' => 'iconlarge activityicon'));
            $url = router::report('activities', ['course' => $courseid, 'mod' => $item['type']]);
            $hits = ($privacythreshold === 0) ? (int) $item['hits'] : (floor(((int) $item['hits']) / $privacythreshold) * $privacythreshold);
            $typestr = $modnameshumanreadable[$item['type']];
            if ($hits >= $privacythreshold) {
                $tabletypes->add_row([
                    "{$icon} <a href='{$url}'>{$typestr}</a>",
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
        $tabledetails->set_header_local(['activity_name', 'activity_type', 'section', 'table_header_hits'], 'lareport_activities');

        // Reorder to show most used activities.

        usort($cms, function ($cm1, $cm2) use ($activities) {
            return $activities[$cm2->id]->hits <=> $activities[$cm1->id]->hits;
        });

        $hiddentext = get_string('hiddenwithbrackets');
        $headinttoptext = get_string('most_used_activities', 'lareport_activities');
        foreach ($cms as $i => $cm) {
            if ($i >= 5) { // Stop when some reports are shown.
                break;
            }
            $activity = $activities[$cm->id];
            $namecell = $cm->name;
            $section = $cm->section;
            if (!$cm->visible) {
                $namecell = "<span class='dimmed_text'>{$namecell} {$hiddentext}</span>";
            }
            if ($activity->hits >= $privacythreshold) {
                $tabledetails->add_row([
                    $namecell,
                    $modnameshumanreadable[$cm->modname],
                    $section->name,
                    table::fancyNumberCell(
                        (int) $activity->hits,
                        $maxhits,
                        self::$markercolorstext[$cm->modname] ?? self::$markercolortextdefault
                    )
                ]);
            }
        }

        $linktofulllist = router::report_page('activities', 'all', ['course' => $courseid]);
        $tabledetails->add_show_more_row($linktofulllist);

        return [
            self::heading(get_string('pluginname', 'lareport_activities'), true, $filterprefix),
            $plot,
            '<hr style="margin-top:0"><p>' . get_string('introduction', 'lareport_activities') . '</p>',
            $tabletypes,
            "<h3>{$headinttoptext}</h3>",
            $tabledetails
        ];
    }

    public function params(): array {
        return [
            'course' => required_param('course', PARAM_INT),
            'mod' => optional_param('mod', null, PARAM_RAW),
            'filter' => optional_param('filter', null, PARAM_RAW)
        ];
    }
}