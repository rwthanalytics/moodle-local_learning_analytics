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
 * Version info for the Top Modules Report
 *
 * @package     local_learning_analytics
 * @copyright   2018 Lehr- und Forschungsgebiet Ingenieurhydrologie - RWTH Aachen University
 * @author      Marcel Behrmann <behrmann@lfi.rwth-aachen.de>
 * @author      Thomas Dondorf <dondorf@lfi.rwth-aachen.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

use local_learning_analytics\local\outputs\html;
use local_learning_analytics\local\parameter\parameter_course;
use local_learning_analytics\local\parameter\parameter_input;
use local_learning_analytics\parameter_base;
use local_learning_analytics\report_base;

class lareport_viewcount extends report_base {

    /**
     * @return array
     * @throws dml_exception
     */
    public function get_parameter(): array {
        global $USER;
        return [
                new parameter_course('course'),
                new parameter_input('user', 'number', parameter_base::REQUIRED_ALWAYS, FILTER_SANITIZE_NUMBER_INT),
        ];
    }

    public function get_parameter_defaults(): array {
        global $USER;

        return [
            'user' => $USER->id
        ];
    }

    public function supports_block(): bool {
        return true;
    }

    public function get_parameter_block(): array {
        global $PAGE, $USER;

        return [
            'course' => $PAGE->context->instanceid,
            'user' => $USER->id,
        ];
    }

    /**
     * @param array $params
     * @return array
     */
    public function run(array $params): array {
        global $DB;

        $output = new html();

        $data = $DB->get_record_sql("
            SELECT COUNT('id') as hits
            FROM {logstore_standard_log}
            WHERE 
              courseid = {$params['course']} AND
              userid = {$params['user']} AND
              eventname LIKE '%course_viewed'             
            GROUP BY eventname
        ");

        $output->set_content(html_writer::tag('h2', $data->hits));

        return [$output];
    }

}