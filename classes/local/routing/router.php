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
 * Router
 *
 * @package     local_learning_analytics
 * @copyright   2018 Lehr- und Forschungsgebiet Ingenieurhydrologie - RWTH Aachen University
 * @author      Marcel Behrmann <behrmann@lfi.rwth-aachen.de>
 * @author      Thomas Dondorf <dondorf@lfi.rwth-aachen.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


namespace local_learning_analytics\local\routing;

use moodle_url;

class router {

    /**
     * @var route[]
     */
    private $routes;

    private $active_route;

    /**
     * router constructor.
     *
     * @param route[] $routes
     */
    public function __construct(array $routes) {
        $this->routes = $routes;
    }

    /**
     * @return route
     * @throws \moodle_exception
     */
    public function get_active_route() : route {
        $uri = new moodle_url($_SERVER['REQUEST_URI']);

        $slashargs = str_replace(
                $uri->get_path(false),
                '',
                $uri->get_path(true)
        );

        foreach ($this->routes as $route) {
            if ($route->match($slashargs)) {
                $this->active_route = $route;
                break;
            }
        }

        return $this->active_route ?? $this->routes[0];
    }

    /**
     * @param string $name
     * @return bool
     * @throws \moodle_exception
     */
    public function is_active_route(string $name) : bool {
        return $this->get_active_route()->get_name() == $name;
    }

    private static function get_url(string $slash, array $query) : moodle_url {
        $params = empty($query) ? '' : '?' . http_build_query($query);
        return new moodle_url("/local/learning_analytics/index.php/reports{$slash}{$params}");
    }

    /**
     * @param string $report
     * @param array $params
     * @return moodle_url
     * @throws \moodle_exception
     */
    public static function report(string $report, array $params = []) : moodle_url {
        return self::get_url("/{$report}", $params);
    }

    public static function report_page(string $report, string $page, array $params = []) : moodle_url {
        return self::get_url("/$report/$page", $params);
    }
}