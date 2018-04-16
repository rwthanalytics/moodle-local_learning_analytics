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
 * @copyright   2018 Lehr- und Forschungsgebiet Ingenieurhydrologie - RWTH Aachen University
 * @author      Marcel Behrmann <behrmann@lfi.rwth-aachen.de>
 * @author      Thomas Dondorf <dondorf@lfi.rwth-aachen.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_learning_analytics\local\controller;

use context_course;
use local_learning_analytics\controller_base;
use local_learning_analytics\local\outputs\table;

defined('MOODLE_INTERNAL') || die;

/**
 * Class controller_courses
 *
 * @package local_learning_analytics\local\controller
 */
class controller_courses extends controller_base {
    public function run() {
        $table = new table();

        $table->set_header([
                'Course',
                'Students',
        ]);

        $table->add_rows_ajax(self::class . '@ajax', array_map(function($e) {
            return ['content' => $e->fullname, 'id' => $e->id];
        }, get_courses()));

        return $table->print();
    }

    public function ajax($id) {
        $count = count(get_enrolled_users(context_course::instance((int) $id)));

        return [
                $count
        ];
    }
}