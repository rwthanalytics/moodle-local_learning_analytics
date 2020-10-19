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
 * Upgrade for lalog_browser_os
 *
 * @package     lalog_browser_os
 * @copyright   2018 Lehr- und Forschungsgebiet Ingenieurhydrologie - RWTH Aachen University
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

function xmldb_lalog_browser_os_upgrade($oldversion) {
    global $DB;

    if ($oldversion < 2020051300) {
        // There was a bug in OS/broser detection (see changelog of logstore plugin, v0.5.0), so we are better of just resetting current results
        $DB->delete_records('lalog_browser_os');

        upgrade_plugin_savepoint(true, 2020051300, 'lalog', 'browser_os');
    }

    return true;
}
