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
     * Main entry point for Learning Analytics UI
     *
     * @package     local_learning_analytics
     * @copyright   Lehr- und Forschungsgebiet Ingenieurhydrologie - RWTH Aachen University
     * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
     */

    define('CLI_SCRIPT', true);

    $insertarray = array();
    $reports = array(1 => 'coursedashboard', 2 => 'activities', 3 => 'learners', 4 => 'topmodules');
    foreach($reports as $report) {
        array_push($insertarray, set_entry($id, $report));
    }
    global $DB;
    $DB->connect($CFG->dbhost, $CFG->dbuser, $CFG->dbpass, $CFG->dbname, $CFG->prefix, ['bulkinsertsize' => 5000]);
    $DB->insert_records('local_learning_analytics_rep', $insertarray);

    function set_entry($id, $report) {
        $entr = new \stdClass();
        $entr->id = $id;
        $entr->reportname = $report;
        return $entr;
    }