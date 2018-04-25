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

namespace local_learning_analytics\local\parameter;

defined('MOODLE_INTERNAL') || die;

/**
 * Class parameter_course
 *
 * @package local_learning_analytics\local\parameter
 */
class parameter_course extends parameter_select {

    private $restricted;

    /**
     * parameter_course constructor.
     *
     * @param string $key
     * @param bool $restricted
     * @throws \dml_exception
     */
    public function __construct(string $key, bool $restricted = false) {
        global $DB, $USER;

        $options = $DB->get_records_sql("
          SELECT id, fullname 
          FROM {course} 
          WHERE id IN ( 
            SELECT instanceid 
            FROM {context}
            WHERE 
              id IN (
                SELECT contextid
                FROM {role_assignments}
                WHERE 
                  userid = ? AND 
                  roleid <= 4
            ) AND 
            contextlevel = 50
          )", [$USER->id]);

        $opts = [];

        foreach ($options as $option) {
            $opts[$option->id] = $option->fullname;
        }

        parent::__construct($key, $opts, self::REQUIRED_ALWAYS, FILTER_SANITIZE_NUMBER_INT);

        $this->restricted = $restricted;
    }

    public function get() {
        $value = (int)parent::get();

        if ($this->restricted) {
            // Check capability
        }

        return $value;
    }


}