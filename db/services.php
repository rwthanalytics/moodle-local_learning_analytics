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
 * local_learning_analytics Services and Functions
 *
 * @package     local_learning_analytics
 * @copyright   2018 Lehr- und Forschungsgebiet Ingenieurhydrologie - RWTH Aachen University
 * @author      Marcel Behrmann <behrmann@lfi.rwth-aachen.de>
 * @author      Thomas Dondorf <dondorf@lfi.rwth-aachen.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$functions = [
        'local_learning_analytics_report' => [
                'classname' => 'local_learning_analytics_external',
                'methodname' => 'report',
                'ajax' => 'true',
                'description' => 'Execute report over ajax',
                'type' => 'read',
        ],
        'local_learning_analytics_ajax_import' => [
            'classname' => 'local_learning_analytics_external',
            'methodname' => 'ajax_import',
            'ajax' => 'true',
            'description' => 'Run import via AJAX',
            'type' => 'write',
        ],
        'local_learning_analytics_ajax' => [
                'classname' => 'local_learning_analytics_external',
                'methodname' => 'ajax',
                'ajax' => true,
                'description' => 'Execute ajax method',
                'type' => 'read',
        ],
        'local_learning_analytics_keep_alive' => [
                'classname' => 'local_learning_analytics_external',
                'methodname' => 'keep_alive',
                'ajax' => true,
                'description' => 'Handle Keep Alive Information',
                'type' => 'write'
        ]
];