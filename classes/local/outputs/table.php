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
 * Learning Analytics Table Output
 *
 * @package     local_learning_analytics
 * @copyright   Lehr- und Forschungsgebiet Ingenieurhydrologie - RWTH Aachen University
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_learning_analytics\local\outputs;

use coding_exception;

use html_table;
use html_writer;

use local_learning_analytics\output_base;

defined('MOODLE_INTERNAL') || die;

class table extends output_base {

    private $table;

    private $ajaxrows;

    public function __construct($customclass = null) {
        $this->table = new html_table();
        $classname = 'generaltable latable';
        if ($customclass) {
            $classname = $classname . ' ' . $customclass;
        }
        $this->table->attributes['class'] = $classname;
    }

    public function set_header(array $header) {
        $this->table->head = $header;
    }

    /**
     * @param array $header
     * @param string $component
     * @throws coding_exception
     */
    public function set_header_local(array $header, string $component = '') {
        $this->table->head = [];

        foreach ($header as $head) {
            $this->table->head[] = get_string($head, $component);
        }
    }

    public static function fancynumbercell(float $value, float $maxvalue, string $class, string $textvalue = null) : string {
        if ($textvalue === null) {
            $textvalue = $value;
        }
        $width = round(100 * $value / $maxvalue);
        return "${textvalue}<div class='bar'><div class='segment ${class}' style='width:${width}%'></div></div>";
    }

    public static function fancynumbercellcolored(float $value, float $maxvalue, string $color, string $textvalue = null) : string {
        if ($textvalue === null) {
            $textvalue = $value;
        }
        $width = round(100 * $value / $maxvalue);
        return "${textvalue}<div class='bar'><div class='segment' style='width:${width}%;background:${color}'></div></div>";
    }

    /**
     * @return string
     */
    public function print(): string {
        global $PAGE;

        $id = 'la_table-' . random_string(4);

        $this->table->id = $id;

        return html_writer::table($this->table);
    }

    public function add_row(array $row) {
        $this->table->data[] = $row;
    }

    public function add_show_more_row($url, $text = null) {
        if ($text === null) {
            $text = get_string('show_full_list', 'local_learning_analytics');
        }
        $cell = new \html_table_cell("<a href='{$url}'>{$text}</a>");
        $cell->colspan = count($this->table->head);
        $cell->attributes['class'] = 'showFullList';
        $this->add_row([ $cell ]);
    }
}
