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

namespace lareport_weekheatmap;

use \logstore_lanalytics\report_archive;
use \lareport_weekheatmap\query_helper;

defined('MOODLE_INTERNAL') || die;

class archive extends report_archive {

    public static function values(int $courseid, int $maxtimecreated) {
        $data = query_helper::query_heatmap($courseid, $maxtimecreated);
        
        // check if at least one data point is set to not store data of never-used courses
        for ($i = 0; $i < (24*7); $i += 1) {
            if ($data[$i] !== 0) {
                return $data;
            }
        }
        return null; // there was not a single non-zero value inside
    }

    public static function merge($archive1, $archive2) {
        $merged = [];
        for ($i = 0; $i < (24*7); $i += 1) {
            $merged[$i] = $archive1[$i] + $archive2[$i];
        }
        return $merged;
    }
    
}