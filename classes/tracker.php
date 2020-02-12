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
 * @copyright   Lehr- und Forschungsgebiet Ingenieurhydrologie - RWTH Aachen University
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_learning_analytics;

use stdClass;

defined('MOODLE_INTERNAL') || die;

/**
 * Class tracker
 *
 * @package local_learning_analytics
 */
class tracker {
    const BROWSER = [
            'unknown' => 0,

            'Chrome' => 1,
            'Edge' => 2,
            'Firefox' => 3,
            'Internet Explorer' => 4,
            'Mobile' => 5,
            'Opera' => 6,
            'Safari' => 7,
    ];

    const OS = [
            'unknown' => 0,

            'Windows XP' => 101,
            'Windows Vista' => 102,
            'Windows 7' => 103,
            'Windows 8' => 104,
            'Windows 8.1' => 105,
            'Windows 10' => 106,

            'OSX' => 201,

            'linux' => 301,

            'iOS' => 401,
            'android' => 402,
            'mobile' => 403,
    ];

    /**
     * @throws \dml_exception
     */
    public static function track_request() {
        global $DB, $PAGE, $USER;

        /*$time = time();

        $sum = $DB->get_record('local_learning_analytics_sum', ['courseid' => $PAGE->course->id, 'userid' => $USER->id]);

        if ($sum != null) {
            $sum->hits++;
            $DB->update_record('local_learning_analytics_sum', $sum);
        } else {
            $sum = new stdClass();
            $sum->userid = $USER->id;
            $sum->courseid = $PAGE->course->id;
            $sum->hits = 1;

            $sum->id = $DB->insert_record('local_learning_analytics_sum', $sum, true);
        }


        $timestamp = $time - 2 * 60 * 60; // Two Hours

        $session = $DB->get_record_sql('SELECT * FROM {local_learning_analytics_ses} WHERE summaryid = ? AND lastaccess >= ?', [$sum->id, $timestamp]);

        if ($session) {
            $session->hits++;

            $diff = $time - $session->lastaccess;

            if($diff < 60) {
                $session->time += $diff;
            }

            $session->lastaccess = $time;

            $DB->update_record('local_learning_analytics_ses', $session);
        } else {
            $session = new stdClass();
            $session->hits = 1;
            $session->summaryid = (int)$sum->id;
            $session->firstaccess = $time;
            $session->lastaccess = $time;
            $session->time = 0;

            $session->browser = self::get_browser();
            $session->os = self::get_os();
            $session->device = $session->os < 400 ? 1 : 2;

           $session->id = $DB->insert_record('local_learning_analytics_ses', $session, true);
        }
#
        $PAGE->requires->js_call_amd('local_learning_analytics/analytics', 'init', [
                'session' => $session->id
        ]);*/

    }

    private static function get_browser() {
        $browser = '';

        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';

        $browser_array  =   array(
                '/msie|trident/i'   =>  'Internet Explorer',
                '/firefox/i'        =>  'Firefox',
                '/chrome/i'         =>  'Chrome',
                '/safari/i'         =>  'Safari',
                '/edge/i'           =>  'Edge',
                '/opera/i'          =>  'Opera',
                '/opr/i'            =>  'Opera',
                '/mobile/i'         =>  'Mobile'
        );

        foreach ($browser_array as $regex => $value)
            if (preg_match($regex, $user_agent)) {
                $browser = $value;
                break;
            }

        return self::BROWSER[$browser] ?? 0;
    }

    private static function get_os() {
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';

        $os_array     = array(
                '/windows nt 10/i'      =>  'Windows 10',
                '/windows nt 6.3/i'     =>  'Windows 8.1',
                '/windows nt 6.2/i'     =>  'Windows 8',
                '/windows nt 6.1/i'     =>  'Windows 7',
                '/windows nt 6.0/i'     =>  'Windows Vista',
                '/windows nt 5.1/i'     =>  'Windows XP',
                '/windows xp/i'         =>  'Windows XP',
                '/macintosh|mac os x/i' =>  'OSX',
                '/mac_powerpc/i'        =>  'OSX',
                '/linux/i'              =>  'Linux',
                '/ubuntu/i'             =>  'Linux',
                '/iphone/i'             =>  'iOS',
                '/ipod/i'               =>  'iOS',
                '/ipad/i'               =>  'iOS',
                '/android/i'            =>  'Android',
                '/webos/i'              =>  'Mobile'
        );

        $os_platform = '';

        foreach ($os_array as $regex => $value)
            if (preg_match($regex, $user_agent))
                $os_platform = $value;

        return self::OS[$os_platform] ?? 0;
    }
}