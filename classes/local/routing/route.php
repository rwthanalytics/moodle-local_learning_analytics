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

/*
 * @package     local_learning_analytics
 * @copyright   2018 Lehr- und Forschungsgebiet Ingenieurhydrologie - RWTH Aachen University
 * @author      Marcel Behrmann <behrmann@lfi.rwth-aachen.de>
 * @author      Thomas Dondorf <dondorf@lfi.rwth-aachen.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_learning_analytics\local\routing;

class route {
    /**
     * @var string
     */
    private $url;

    /**
     * @var callable
     */
    private $handler;

    /**
     * @var string
     */
    private $reverse;

    /**
     * @var array
     */
    public $params = [];

    /**
     * route constructor.
     *
     * @param string $target
     * @param string|callable $handler
     * @throws \Exception
     */
    public function __construct(string $target, $handler) {
        $this->url = $target;

        if (is_string($handler)) {
            $parts = explode('@', $handler);

            var_dump($parts);

            $this->handler = function ($params) use ($parts) {
                $class = $parts[0];
                $method = $parts[1];
                return (new $class($params))->$method();
            };
        } else {
            $this->handler = $handler;
        }
    }

    private function get_regex() : string {
        $regex = "/";

        $parts = explode('/', $this->url);
        array_shift($parts);

        foreach ($parts as $part) {
            $p = explode(':', $part);

            if(isset($p[1])) {
                $regex .= "(?P<$p[1]>\/[a-zA-Z0-9]+)";
            } else {
                $regex .= '\/' . $part;
            }
        }
        var_dump($regex);

        return $regex . '\/?/';
    }

    public function get_handler() {
        return $this->handler;
    }

    public function execute() {
        $h = $this->handler;
        return $h($this->params);
    }

    public function match(string $url) : bool {
        if (preg_match($this->get_regex(), $url, $this->params)) {
            if($this->params[0] === $url) {
                $this->reverse = $url;
                array_shift($this->params);

                $this->params = array_map(function ($e) {
                    return substr($e, 1);
                }, $this->params);
                return true;
            } else {
                return false;
            }
        }

        return false;
    }
}