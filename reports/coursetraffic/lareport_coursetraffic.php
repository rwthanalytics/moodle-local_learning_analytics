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

use local_learning_analytics\local\outputs\plot;
use local_learning_analytics\parameter;
use local_learning_analytics\report_base;

defined('MOODLE_INTERNAL') || die;

/**
 * Class lareport_coursetraffic
 */
class lareport_coursetraffic extends report_base {

    public function get_parameter(): array {
        return [
                new parameter('course', parameter::TYPE_COURSE, true, FILTER_SANITIZE_NUMBER_INT),
        ];
    }

    public function supports_block(): bool {
        return true;
    }

    public function get_block_parameter(): array {
        global $PAGE;

        return [
                'course' => $PAGE->context->instanceid
        ];
    }

    /**
     * @param array $params
     * @return array
     * @throws dml_exception
     */
    public function run(array $params): array {
        $plot = new plot();

        $sql = "
            SELECT 
              DATE(FROM_UNIXTIME(timecreated)) as day,
              COUNT(timecreated) as `hits`
            FROM mdl_logstore_standard_log
            WHERE courseid = {$params['course']}
            GROUP BY day;";

        $plot->add_series_from_sql('scatter', $sql, ['x' => 'day', 'y' => 'hits']);

        return [ $plot ];
    }
}