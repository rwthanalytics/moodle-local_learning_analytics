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
 * Learning Analytics Browser/OS Logger Class
 *
 * @package     local_learning_analytics
 * @copyright   Lehr- und Forschungsgebiet Ingenieurhydrologie - RWTH Aachen University
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace lalog_browser_os;

use \stdClass;
use logstore_lanalytics\devices;

defined('MOODLE_INTERNAL') || die;

const MOBILE_OS = 10000; // See devices.php of logstore plugin.

const UNKNOWN = 0;
const MOODLE_API = devices::OS['Moodle API'];

const WINDOWS = devices::OS['Windows'];
const MAC = devices::OS['macOS'];
const LINUX = devices::OS['Linux'];

const CHROME = devices::BROWSER['Chrome'];
const EDGE = devices::BROWSER['Edge'];
const FIREFOX = devices::BROWSER['Firefox'];
const IE = devices::BROWSER['Internet Explorer'];
const OPERA = devices::BROWSER['Opera'];
const SAFARI = devices::BROWSER['Safari'];

const IOS = devices::OS['iOS'];
const ANDROID = devices::OS['Android'];

const RANGE = 1000;

class logger {

    public static function log(array $events) {
        global $DB;

        $bycourse = [];
        foreach ($events as $event) {
            $courseid = $event['courseid'];
            if (!isset($bycourse[$courseid])) {
                $bycourse[$courseid] = [
                    'platform_desktop' => 0,
                    'platform_mobile' => 0,
                    'platform_api' => 0,
                    'platform_other' => 0,

                    // only for desktop
                    'os_windows' => 0,
                    'os_mac' => 0,
                    'os_linux' => 0,
                    'os_other' => 0,

                    // only for desktop
                    'browser_chrome' => 0,
                    'browser_edge' => 0,
                    'browser_firefox' => 0,
                    'browser_ie' => 0,
                    'browser_opera' => 0,
                    'browser_safari' => 0,
                    'browser_other' => 0,

                    'mobile_android' => 0,
                    'mobile_ios' => 0,
                    'mobile_other' => 0
                ];
            }
            $os = $event['os'];
            $browser = $event['browser'];

            $platform = 'other';
            if ($os >= MOBILE_OS) {
                $platform = 'mobile';
            } else if ($os === MOODLE_API) {
                $platform = 'api';
            } else if ($os !== UNKNOWN) {
                $platform = 'desktop';
            }
            $bycourse[$courseid]['platform_' . $platform] += 1;

            if ($platform === 'desktop') {
                $desktopos = 'other';
                if ($os >= WINDOWS && $os < (WINDOWS + RANGE)) {
                    $desktopos = 'windows';
                } else if ($os >= MAC && $os < (MAC + RANGE)) {
                    $desktopos = 'mac';
                } else if ($os >= LINUX && $os < (LINUX + RANGE)) {
                    $desktopos = 'linux';
                }
                $bycourse[$courseid]['os_' . $desktopos] += 1;

                $desktopbrowser = 'other';
                if ($browser >= CHROME && $browser < (CHROME + RANGE)) {
                    $desktopbrowser = 'chrome';
                } else if ($browser >= EDGE && $browser < (EDGE + RANGE)) {
                    $desktopbrowser = 'edge';
                } else if ($browser >= FIREFOX && $browser < (FIREFOX + RANGE)) {
                    $desktopbrowser = 'firefox';
                } else if ($browser >= IE && $browser < (IE + RANGE)) {
                    $desktopbrowser = 'ie';
                } else if ($browser >= OPERA && $browser < (OPERA + RANGE)) {
                    $desktopbrowser = 'opera';
                } else if ($browser >= SAFARI && $browser < (SAFARI + RANGE)) {
                    $desktopbrowser = 'safari';
                }
                $bycourse[$courseid]['browser_' . $desktopbrowser] += 1;
            } else if ($platform === 'mobile') {
                $mobileos = 'other';
                if ($os >= IOS && $os < (IOS + RANGE)) {
                    $mobileos = 'ios';
                } else if ($os >= ANDROID && $os < (ANDROID + RANGE)) {
                    $mobileos = 'android';
                }
                $bycourse[$courseid]['mobile_' . $mobileos] += 1;
            }
        }

        // Now, we first try to insert the row, this is likely to fail as there is probably already
        // a row for the course, when it fails we update the row instead
        // Unfortunately, it's not possible to do this the other way around as Moodle does not provide
        // an API to call "update_record" with a courseid, therefore this is the way with the fewest
        // database calls.
        foreach ($bycourse as $courseid => $courestats) {
            try {
                $record = (object) $courestats;
                $record->courseid = $courseid;
                $DB->insert_record('lalog_browser_os', $record);
            } catch (\dml_write_exception $e) { // Row is already present, update instead.
                $updates = [];
                foreach ($courestats as $key => $increase) {
                    $updates[] = " {$key} = {$key} + {$increase}";
                }
                $sql = "UPDATE {lalog_browser_os} SET "
                    . implode(', ', $updates)
                    . " WHERE courseid = ?";
                $DB->execute($sql, [$courseid]);
            }
        }
    }

    public static function truncate() {
        global $DB;
        $DB->execute("TRUNCATE {lalog_browser_os}");
    }
}
