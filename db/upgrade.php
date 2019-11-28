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
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2018041801) {
        $table = new xmldb_table('local_learning_analytics_sum');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE);
        $table->add_field('courseid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);
        $table->add_field('hits', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        $index = new xmldb_index('courseid_userid_idx', XMLDB_INDEX_UNIQUE, array('courseid', 'userid'));
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }

        $table2 = new xmldb_table('local_learning_analytics_ses');
        $table2->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE);
        $table2->add_field('summaryid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);
        $table2->add_field('hits', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);
        $table2->add_field('firstaccess', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);
        $table2->add_field('lastaccess', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);
        $table2->add_field('device', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);
        $table2->add_field('browser', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);
        $table2->add_field('os', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);
        $table2->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        if (!$dbman->table_exists($table2)) {
            $dbman->create_table($table2);
        }

        $index2 = new xmldb_index('summaryid_idx', XMLDB_INDEX_NOTUNIQUE, array('summaryid'));
        if (!$dbman->index_exists($table2, $index2)) {
            $dbman->add_index($table2, $index2);
        }

        upgrade_plugin_savepoint(true, 2018041801, 'local', 'learning_analytics');
    }

    if($oldversion < 2018051603) {
        $table = new xmldb_table('local_learning_analytics_ses');
        $field = new xmldb_field('time', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0);

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_plugin_savepoint(true, 2018051603, 'local', 'learning_analytics');
    }

    if($oldversion < 2019021801) {
        $table = new xmldb_table('local_learning_analytics_pre');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE);
        $table->add_field('courseid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);
        $table->add_field('prevcourseid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        $index3 = new xmldb_index('courseid_prevcourseid_idx', XMLDB_INDEX_UNIQUE, array('courseid', 'prevcourseid'));
        if (!$dbman->index_exists($table, $index3)) {
            $dbman->add_index($table, $index3);
        }

        upgrade_plugin_savepoint(true, 2019021801, 'local', 'learning_analytics');
    }

    return true;
}
