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
 * Version info for the Sections learners
 *
 * @package     local_learning_analytics
 * @copyright   Lehr- und Forschungsgebiet Ingenieurhydrologie - RWTH Aachen University
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

use local_learning_analytics\local\outputs\table;
use local_learning_analytics\report_base;
use lareport_learners\query_helper;
use lareport_learners\helper;
use local_learning_analytics\local\outputs\splitter;
use local_learning_analytics\local\outputs\plot;

class lareport_learners extends report_base {

    private static $barcolors = [
        '#66b5ab',
        '#F26522',
        '#ffda6e',
        '#A9CF54',
        '#EA030E'
    ];

    private static function createseries($perc, $text, $i) {
        return [
            'y' => ['lang'],
            'x' => [$perc],
            'orientation' => 'h',
            'hoverinfo' => 'none', // Change to "text" to provide onhover information and make sure its not static.
            'marker' => [
                'color' => (self::$barcolors[$i % count(self::$barcolors)])
            ],
            'name' => $text,
            'type' => 'bar',
            'text' => $text
        ];
    }

    private function langandcountryplot(int $courseid, string $type) {
        $learnerscount = query_helper::query_learners_count($courseid, 'student');

        $languages = query_helper::query_localization($courseid, $type);

        $plot = new plot();
        $langlist = get_string_manager()->get_list_of_languages();

        $percsofar = 0;
        $i = 0;
        foreach ($languages as $lang) {
            $perc = round(100 * $lang->users / $learnerscount);
            if ($perc > 3) { // Otherwise it might be too small to display.
                $annotations[] = [
                    'x' => ($percsofar + ($perc / 2)),
                    'y' => 'lang',
                    'text' => $perc . '%',
                    'font' => [
                        'color' => '#fff',
                        'size' => 14,
                    ],
                    'showarrow' => false,
                    'xanchor' => 'center'
                ];
            }
            if ($type === 'lang') {
                $annottext = $langlist[$lang->x];
            } else { // Then its country.
                if (empty($lang->x)) {
                    $annottext = 'Unknown'; // No country set.
                } else {
                    $annottext = get_string($lang->x, 'countries');
                }
            }
            $annotations[] = [
                'x' => ($percsofar + ($perc / 2)),
                'y' => 'lang',
                'yshift' => 15,
                'text' => $annottext,
                'font' => [
                    'color' => '#000',
                    'size' => 16,
                ],
                'showarrow' => false,
                'xanchor' => 'center',
                'yanchor' => 'bottom'
            ];
            $percsofar += $perc;
            $series = self::createseries($perc, $annottext, $i);
            $plot->add_series($series);
            $i++;
        }

        $layout = new stdClass();
        $layout->barmode = 'stack';
        $layout->annotations = $annotations;
        $layout->xaxis = ['visible' => false, 'range' => [0, 100], 'fixedrange' => true ];
        $layout->yaxis = ['visible' => false, 'fixedrange' => true];
        $layout->showlegend = false;
        $layout->margin = ['l' => 0, 'r' => 0, 't' => 10, 'b' => 0];

        $plot->set_layout($layout);
        $plot->set_height(70);
        $plot->set_static_plot(true);

        return $plot;
    }

    private function languagesandcountries(int $courseid): array {
        $heading1 = get_string('languages_of_learners', 'lareport_learners');
        $heading2 = get_string('countries_of_learners', 'lareport_learners');

        return [
            "<h3>{$heading1}</h3>",
            $this->langandcountryplot($courseid, 'lang'),
            "<h3>{$heading2}</h3>",
            $this->langandcountryplot($courseid, 'country')
        ];
    }

    public function run(array $params): array {
        $courseid = $params['course'];

        $headingtable = get_string('most_active_learners', 'lareport_learners');

        return array_merge(
            helper::generateCourseParticipationList($courseid, 10)
            // $this->languagesandcountries($courseid)
        );
    }

    public function params(): array {
        return [
            'course' => required_param('course', PARAM_INT)
        ];
    }

}