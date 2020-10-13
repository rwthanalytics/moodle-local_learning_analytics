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
 * Upgrade for local_learning_analytics
 *
 * @package     local_learning_analytics
 * @copyright   Lehr- und Forschungsgebiet Ingenieurhydrologie - RWTH Aachen University
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

function xmldb_local_learning_analytics_upgrade($oldversion) {
    global $DB, $CFG;

    // Update TOUR to latest version
    if ($oldversion < 2020100700) { // always update this to the latest version when the usertour was changed
        // Remove old tour first (if there is one)
        $tourid = (int) get_config('local_learning_analytics', 'tourid');
        if ($tourid !== 0) { // delete any old tours before updating the tour
            $oldtour = \tool_usertours\tour::instance($tourid);
            $oldtour->remove(); // delete old tour
        }
        
        // Then add the tour
        $tourpath = $CFG->dirroot . '/local/learning_analytics/templates/usertour.json';
        $tourjson = file_get_contents($tourpath);
        $tour = \tool_usertours\manager::import_tour_from_json($tourjson);
        set_config('tourid', $tour->get_id(), 'local_learning_analytics');
        upgrade_plugin_savepoint(true, 2020100700, 'local', 'learning_analytics');
    }

    return true;
}
