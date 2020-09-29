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
use lareport_quiz\query_helper;
use local_learning_analytics\router;
use local_learning_analytics\settings;

// TODO privacythreshold
// TODO remove redundant plot code

class lareport_quiz extends report_base {

    public function quizzes(int $courseid, $privacythreshold) {
        $x = [];
        $triescount = [];
        $users = [];
        $resultstotal = [];
        $resultsfirsttry = [];

        $legend = [
            'orientation' => 'h',
            'xanchor' => 'right',
            'x' => 1,
            'y' => 1.18,
        ];
        $margin = ['l' => 80, 'r' => 0, 't' => 0, 'b' => 100];

        $dbquizzes = query_helper::query_quiz($courseid);
        $modinfo = get_fast_modinfo($courseid);
        $quizzes = $modinfo->instances['quiz'];
        $hiddentext = get_string('hiddenwithbrackets');
        foreach ($quizzes as $quizid => $cm) {
            if (!$cm->uservisible) {
                continue;
            }
            $name = $cm->name;
            if (!$cm->visible) {
                $name = $name . "<br><span style='opacity:0.75'>{$hiddentext}</span>";
            }
            $x[] = $name;
            if (isset($dbquizzes[$quizid])) {
                $quizinfo = $dbquizzes[$quizid];
                $triescount[] = $quizinfo->attempts;
                $users[] = $quizinfo->users;
                $resultstotal[] = $quizinfo->result;
                $resultsfirsttry[] = $quizinfo->firsttryresult;
            } else {
                $triescount[] = 0;
                $users[] = 0;
                $resultstotal[] = 0;
                $resultsfirsttry[] = 0;
            }
        }

        $plotuse = new plot();
        $plotuse->show_toolbar(false);
        $plotuse->add_series([
            'type' => 'bar',
            'x' => $x,
            'y' => $triescount,
            'name' => 'Tries', // TODO lang
            'marker' => [ 'color' => '#1f77b4' ]
        ]);
        $plotuse->add_series([
            'type' => 'bar',
            'x' => $x,
            'y' => $users,
            'name' => 'Users', // TODO lang
            'marker' => [ 'color' => '#ff7f0e' ]
        ]);
        $layout = new stdClass();
        $layout->margin = $margin;
        $layout->legend = $legend;
        $layout->xaxis = [ 'tickangle' => 30 ];
        $plotuse->set_layout($layout);
        $plotuse->set_height(300);
        
        $plotresults = new plot();
        $plotresults->show_toolbar(false);
        $plotresults->add_series([
            'type' => 'bar',
            'x' => $x,
            'y' => $resultstotal,
            'name' => 'Alle Versuche', // TODO lang
            'marker' => [ 'color' => '#2ca02c' ]
        ]);
        $plotresults->add_series([
            'type' => 'bar',
            'x' => $x,
            'y' => $resultsfirsttry,
            'name' => 'Erster Versuch', // TODO lang
            'marker' => [ 'color' => '#bcbd22' ]
        ]);
        $layout = new stdClass();
        $layout->margin = $margin;
        $layout->legend = $legend;
        $layout->xaxis = [ 'tickangle' => 30 ];
        $layout->yaxis = [
            'tickformat' => ',.1%',
            'range' => [0,1]
        ];
        $plotresults->set_layout($layout);
        $plotresults->set_height(300);

        // TODO only show quiz statistics if there is at least one quiz

        return [ // TODO lang
            "<h3 style='margin-bottom: 5px'>Quizze - Durchgeführte Versuche</h3>", 
            $plotuse,
            "<hr><h3 style='margin-bottom: 5px'>Quizze - Durchschnittlich erreichte Punkte</h3>", 
            $plotresults
        ];
    }

    public function assignments(int $courseid, $privacythreshold) {
        $x = [];
        $handins = [];
        $grades = [];
        
        $legend = [
            'orientation' => 'h',
            'xanchor' => 'right',
            'x' => 1,
            'y' => 1.18,
        ];
        $margin = ['l' => 80, 'r' => 0, 't' => 20, 'b' => 100];

        $dbassignments = query_helper::query_assignment($courseid);
        $modinfo = get_fast_modinfo($courseid);
        $assignments = $modinfo->instances['assign'];
        $hiddentext = get_string('hiddenwithbrackets');
        foreach ($assignments as $assignid => $cm) {
            if (!$cm->uservisible) {
                continue;
            }
            $name = $cm->name;
            if (!$cm->visible) {
                $name = $name . "<br><span style='opacity:0.75'>{$hiddentext}</span>";
            }
            $x[] = $name;
            if (isset($dbassignments[$assignid])) {
                $quizinfo = $dbassignments[$assignid];
                $handins[] = $quizinfo->handins;
                $grades[] = $quizinfo->grade;
            } else {
                $handins[] = 0;
                $grades[] = 0;
            }
        }

        $plotuse = new plot();
        $plotuse->show_toolbar(false);
        $plotuse->add_series([
            'type' => 'bar',
            'x' => $x,
            'y' => $handins,
            'name' => 'Abgaben', // TODO lang
            'marker' => [ 'color' => '#1f77b4' ]
        ]);
        $layout = new stdClass();
        $layout->margin = $margin;
        $layout->xaxis = [ 'tickangle' => 30 ];
        $plotuse->set_layout($layout);
        $plotuse->set_height(300);
        
        $plotresults = new plot();
        $plotresults->show_toolbar(false);
        $plotresults->add_series([
            'type' => 'bar',
            'x' => $x,
            'y' => $grades,
            'name' => 'Alle Versuche', // TODO lang
            'marker' => [ 'color' => '#2ca02c' ]
        ]);
        $layout = new stdClass();
        $layout->margin = $margin;
        $layout->xaxis = [ 'tickangle' => 30 ];
        $layout->yaxis = [
            'tickformat' => ',.1%',
            'range' => [0,1]
        ];
        $plotresults->set_layout($layout);
        $plotresults->set_height(300);

        // TODO only show quiz statistics if there is at least one assignment

        return [ // TODO lang
            "<h3 style='margin-bottom: 5px'>Aufgaben - Anzahl an bewerteten Abgaben</h3>", 
            $plotuse,
            "<hr><h3 style='margin-bottom: 5px'>Aufgaben - Durchschnittlich erreichte Punkte</h3>", 
            $plotresults
        ];
    }

    public function run(array $params): array {
        global $USER, $OUTPUT;
        $courseid = $params['course'];
        $privacythreshold = settings::get_config('dataprivacy_threshold');

        return array_merge(
            self::quizzes($courseid, $privacythreshold),
            self::assignments($courseid, $privacythreshold)
        );
    }

    public function params(): array {
        return [
            'course' => required_param('course', PARAM_INT)
        ];
    }
}